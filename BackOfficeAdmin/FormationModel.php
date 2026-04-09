<?php
namespace BackOfficeAdmin;

//Pour gérer les Formations

require_once 'BaseModel.php';
require_once __DIR__ . '/../includes/functions.php'; // Fonctions utiles

class FormationModel extends BaseModel
{

    private $tablePrefix = 'kyd4_'; // Préfixe de ta base WordPress

    public function __construct()
    {
        parent::__construct(); // initialise $this->db
    }


    // ==========================================
    // GESTION DES CATÉGORIES DE FORMATIONS
    // ==========================================

    // Vérifie si un slug existe déjà
    public function slugExists(string $slug): bool
    {
        $sql = "SELECT COUNT(*) AS cnt FROM {$this->tablePrefix}terms WHERE slug = :slug";
        $stmt = $this->executeQuery($sql, [':slug' => $slug]);
        $row = $stmt->fetch();
        return ((int)$row['cnt'] > 0);
    }


    // Génère un slug unique pour WordPress
    public function generateSlug(string $name): string
    {
        $baseSlug = $this->sanitize_title($name); // fonction WordPress-like
        $slug = $baseSlug;
        $counter = 1;

        // Vérifier l'unicité du slug
        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }


    // Fonction utilitaire pour créer un slug WordPress-like
    public function sanitize_title(string $title): string
    {
        // Convertir en minuscules
        $slug = strtolower($title);

        // Remplacer les caractères spéciaux
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);

        // Remplacer les espaces et caractères non alphanumériques par des tirets
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Supprimer les tirets en début/fin
        $slug = trim($slug, '-');

        return $slug;
    }


    // Récupère toutes les catégories (pour un dropdown)
    public function getFormationCategories()
    {
        $sql = "SELECT t.term_id as id, t.name, t.slug, tt.description, tt.count
                    FROM {$this->tablePrefix}terms t
                    INNER JOIN {$this->tablePrefix}term_taxonomy tt ON t.term_id = tt.term_id
                    WHERE tt.taxonomy = 'course_category'
                    ORDER BY t.name ASC";

        $stmt = $this->executeQuery($sql);
        return $stmt->fetchAll();
    }


    // Récupère une catégorie par son ID
    public function getFormationCategoryById(int $id)
    {
        $sql = "SELECT t.term_id as id, t.name, t.slug, tt.description, tt.count, tt.parent
                    FROM {$this->tablePrefix}terms t
                    INNER JOIN {$this->tablePrefix}term_taxonomy tt ON t.term_id = tt.term_id
                    WHERE t.term_id = :id AND tt.taxonomy = 'course_category'";

        $stmt = $this->executeQuery($sql, [':id' => $id]);
        return $stmt->fetch() ?: null;
    }


    // Compte toutes les catégories (actives ou non)
    public function countFormationCategories()
    {
        $sql = "SELECT COUNT(*) AS cnt 
                    FROM {$this->tablePrefix}terms t
                    INNER JOIN {$this->tablePrefix}term_taxonomy tt ON t.term_id = tt.term_id
                    WHERE tt.taxonomy = 'course_category'";

        $stmt = $this->executeQuery($sql);
        $row = $stmt->fetch();
        return (int)$row['cnt'];
    }


    // Récupérer toutes les catégories actives (ou non), avec le nombre de formations associées.
    public function getFormationCategoryStats($limit, $offset)
    {
        $sql = "SELECT t.term_id as id, t.name, t.slug, tt.description, tt.parent, tt.count as formation_count
                    FROM {$this->tablePrefix}terms t
                    INNER JOIN {$this->tablePrefix}term_taxonomy tt ON t.term_id = tt.term_id
                    WHERE tt.taxonomy = 'course_category'
                    ORDER BY t.name ASC
                    LIMIT ? OFFSET ?";

        if (!$this->pdo) {
            return [];
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, (int)$limit, \PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("SQL Error in getFormationCategoryStats: " . $e->getMessage());
            return [];
        }
    }


    // Crée une catégorie de formation
    public function createFormationCategory(array $data): int|false
    {
        try {
            $this->pdo->beginTransaction();

            // 1) Générer le slug
            $slug = $this->generateSlug($data['name']);

            // 2) Insérer dans kyd4_terms
            $this->executeQuery(
                "INSERT INTO {$this->tablePrefix}terms (name, slug, term_group)
                    VALUES (:name, :slug, 0)",
                [
                    ':name' => $data['name'],
                    ':slug' => $slug
                ]
            );
            $termId = (int)$this->pdo->lastInsertId();

            // 3) Insérer dans kyd4_term_taxonomy
            $this->executeQuery(
                "INSERT INTO {$this->tablePrefix}term_taxonomy 
                    (term_id, taxonomy, description, parent, count)
                    VALUES (:term_id, 'course_category', :description, :parent, 0)",
                [
                    ':term_id' => $termId,
                    ':description' => $data['description'] ?? '',
                    ':parent' => $data['parent'] ?? 0
                ]
            );

            $this->pdo->commit();
            return $termId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur création catégorie: " . $e->getMessage());
            throw $e;
        }
    }


    // Vérifie si une catégorie existe déjà (même nom)
    public function formationCategoryExists(string $name): bool
    {
        $sql = "SELECT COUNT(*) AS cnt
                    FROM {$this->tablePrefix}terms t
                    INNER JOIN {$this->tablePrefix}term_taxonomy tt ON t.term_id = tt.term_id
                    WHERE t.name = :name AND tt.taxonomy = 'course_category'";

        $stmt = $this->executeQuery($sql, [':name' => $name]);
        $row = $stmt->fetch();
        return ((int)$row['cnt'] > 0);
    }


    // Supprime une catégorie de formation. Respecte les contraintes WordPress
    public function deleteFormationCategory(int $categoryId): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 1) Vérifier s'il y a des formations associées
            $count = $this->countFormationsInCategory($categoryId);
            if ($count > 0) {
                // Option: déplacer vers "Non classé" ou interdire la suppression
                $this->pdo->rollBack();
                return false; // ou throw new Exception("Catégorie contient des formations");
            }

            // 2) Supprimer de term_taxonomy
            $this->executeQuery(
                "DELETE FROM {$this->tablePrefix}term_taxonomy 
                    WHERE term_id = :id AND taxonomy = 'course_category'",
                [':id' => $categoryId]
            );

            // 3) Supprimer de terms
            $this->executeQuery(
                "DELETE FROM {$this->tablePrefix}terms WHERE term_id = :id",
                [':id' => $categoryId]
            );

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur suppression catégorie: " . $e->getMessage());
            return false;
        }
    }


    // Met à jour une catégorie de formation
    public function updateFormationCategory(int $id, array $data): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 1) Générer nouveau slug si nom changé
            $newSlug = $this->generateSlug($data['name']);

            // 2) Mettre à jour terms
            $this->executeQuery(
                "UPDATE {$this->tablePrefix}terms 
                    SET name = :name, slug = :slug 
                    WHERE term_id = :id",
                [
                    ':name' => $data['name'],
                    ':slug' => $newSlug,
                    ':id' => $id
                ]
            );

            // 3) Mettre à jour term_taxonomy
            $this->executeQuery(
                "UPDATE {$this->tablePrefix}term_taxonomy 
                    SET description = :description, parent = :parent
                    WHERE term_id = :id AND taxonomy = 'course_category'",
                [
                    ':description' => $data['description'] ?? '',
                    ':parent' => $data['parent'] ?? 0,
                    ':id' => $id
                ]
            );

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur mise à jour catégorie: " . $e->getMessage());
            return false;
        }
    }


    // Compte le nombre de formations dans une catégorie
    // UTILISE kyd4_term_relationships pour compter les liaisons
    public function countFormationsInCategory(int $categoryId): int
    {
        $sql = "SELECT COUNT(*) AS cnt
                    FROM {$this->tablePrefix}term_relationships tr
                    INNER JOIN {$this->tablePrefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$this->tablePrefix}posts p ON tr.object_id = p.ID
                    WHERE tt.term_id = :cat_id AND tt.taxonomy = 'course_category'
                                               AND p.post_type = 'lp_course'
                                               AND p.post_status = 'publish'";

        $stmt = $this->executeQuery($sql, [':cat_id' => $categoryId]);
        $row = $stmt->fetch();
        return (int)$row['cnt'];
    }


    // Récupère les formations d'une catégorie
    // UTILISE kyd4_term_relationships pour les liaisons
    public function getFormationsByCategory(int $categoryId, $limit = 10, $offset = 0): array
    {
        $sql = "SELECT p.ID, p.post_title, p.post_status, p.post_date
                    FROM {$this->tablePrefix}posts p
                    INNER JOIN {$this->tablePrefix}term_relationships tr ON p.ID = tr.object_id
                    INNER JOIN {$this->tablePrefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    WHERE tt.term_id = :cat_id 
                    AND tt.taxonomy = 'course_category'
                    AND p.post_type = 'lp_course'
                    ORDER BY p.post_date DESC
                    LIMIT :limit OFFSET :offset";

        $params = [':cat_id' => $categoryId, ':limit' => (int)$limit, ':offset' => (int)$offset];
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }



    // ==========================================
    // GESTION DES FORMATIONS 
    // ==========================================
    public function linkCategorieToCourse($author_id, $categoriesId)
    {
        try {
            $count = 0;
            foreach ($categoriesId as  $categoryId) {

                $sql = "INSERT INTO {$this->tablePrefix}term_relationships(object_id, term_taxonomy_id) VALUES(:object_id, :categoryId)";
                $stmt = $this->pdo->prepare($sql);
                if ($stmt->execute([
                    ':object_id' => $author_id,
                    ':categoryId' => $categoryId
                ])) {
                    $count++;
                }
            }
            if ($count == count($categoriesId)) {
                return true;
            }
        } catch (\PDOException $e) {
            error_log("Erreur l'hors de l'ajout de catégorie" . $e->getMessage());
        }
    }
    public function insertPost($post_data)
    {
        try {
            // Valeurs par défaut
            $defaults = [
                'post_author' => 1,
                'post_date' => date('Y-m-d H:i:s'),
                'post_date_gmt' => gmdate('Y-m-d H:i:s'),
                'post_content' => '',
                'post_title' => '',
                'post_excerpt' => '',
                'post_status' => 'draft',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => '',
                'to_ping' => '',
                'pinged' => '',
                'post_modified' => date('Y-m-d H:i:s'),
                'post_modified_gmt' => gmdate('Y-m-d H:i:s'),
                'post_content_filtered' => '',
                'post_parent' => 0,
                'guid' => '',
                'menu_order' => 0,
                'post_type' => 'lp_course',
                'post_mime_type' => '',
                'comment_count' => 0
            ];
            $data = array_merge($defaults, $post_data);
            if (empty($data['post_name'])) {
                $data['post_name'] = $this->generateSlugCourse($data['post_title']);
            }

            // Construire la requête
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));

            $sql = "INSERT INTO {$this->tablePrefix}posts ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);

            // Exécuter la requête
            if ($stmt->execute($data)) {
                return $this->pdo->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erreur insertion post: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ajouter des métadonnées à un post
     * 
     * @param int $post_id - ID du post
     * @param array $meta_data - Tableau associatif [meta_key => meta_value]
     * @return bool - Succès de l'opération
     */
    public function addPostMeta($post_id, $meta_data)
    {
        try {
            $this->pdo->beginTransaction();

            foreach ($meta_data as $meta_key => $meta_value) {
                // Convertir en string si c'est un array/object
                if (is_array($meta_value) || is_object($meta_value)) {
                    $meta_value = serialize($meta_value);
                }

                $sql = "INSERT INTO {$this->tablePrefix}postmeta (post_id, meta_key, meta_value) 
                        VALUES (:post_id, :meta_key, :meta_value)";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'post_id' => $post_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur insertion postmeta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les formations les plus récentes
     * @param int $limit
     * @param array $filters (category_id, level, price_type)
     * @return array
    */
    public function getLatestFormations(int $limit = 5, array $filters = []): array
    {
        $where = ["p.post_type = 'lp_course'", "p.post_status = 'publish'"];
        $join = "";
        $params = [];

        // Filtre catégorie
        if (!empty($filters['category_id'])) {
            $join .= " INNER JOIN {$this->tablePrefix}term_relationships tr ON p.ID = tr.object_id";
            $join .= " INNER JOIN {$this->tablePrefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
            $where[] = "tt.term_id = :cat_id AND tt.taxonomy = 'course_category'";
            $params[':cat_id'] = (int)$filters['category_id'];
        }

        // Filtre niveau (meta_key = 'niveau_public_formation' ou '_lp_level')
        if (!empty($filters['level'])) {
            $join .= " LEFT JOIN {$this->tablePrefix}postmeta pm_level ON p.ID = pm_level.post_id AND pm_level.meta_key = 'niveau_public_formation'";
            $where[] = "pm_level.meta_value = :level";
            $params[':level'] = $filters['level'];
        }

        // Filtre prix (gratuit/payant)
        if (isset($filters['price_type'])) {
            $join .= " LEFT JOIN {$this->tablePrefix}postmeta pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_lp_price'";
            if ($filters['price_type'] === 'free') {
                $where[] = "(pm_price.meta_value = '0' OR pm_price.meta_value IS NULL)";
            } else {
                $where[] = "pm_price.meta_value > 0";
            }
        }

        $sql = "SELECT p.ID, p.post_title, p.post_date
                FROM {$this->tablePrefix}posts p
                $join
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.post_date DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $postIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Récupérer les données complètes de chaque formation
        $formations = [];
        foreach ($postIds as $postId) {
            $formation = $this->getPostWithMeta($postId);
            if ($formation) {
                // Ajouter les infos de catégorie et image
                $formation['category'] = $this->getFormationCategoryById($formation['categories'][0] ?? null);
                $formations[] = $formation;
            }
        }
        return $formations;
    }

    /**
     * Recherche des formations par mot-clé
    */
    public function searchFormations(string $keyword, int $limit = 8): array
    {
        $sql = "SELECT p.ID, p.post_title, p.post_content
                FROM {$this->tablePrefix}posts p
                WHERE p.post_type = 'lp_course' 
                AND p.post_status = 'publish'
                AND (p.post_title LIKE :keyword OR p.post_content LIKE :keyword)
                ORDER BY 
                    CASE WHEN p.post_title LIKE :keyword_exact THEN 1 ELSE 2 END,
                    p.post_date DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $keywordParam = '%' . $keyword . '%';
        $stmt->bindValue(':keyword', $keywordParam);
        $stmt->bindValue(':keyword_exact', $keyword . '%');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $formations = [];
        foreach ($results as $row) {
            $formation = $this->getPostWithMeta($row['ID']);
            if ($formation) {
                $formation['category'] = $this->getFormationCategoryById($formation['categories'][0] ?? null);
                $formations[] = $formation;
            }
        }
        return $formations;
    }

    /**
     * Créer un post complet avec métadonnées
     * 
     * @param array $post_data - Données du post
     * @param array $meta_data - Métadonnées du post
     * @return int|false - ID du post créé ou false
     */
    public function createCompletePost($post_data, $meta_data = [])
    {
        $categories = $post_data['categories'];
        unset($post_data['categories']);
        $post_id = $this->insertPost($post_data);
        $this->linkCategorieToCourse($post_id, $categories);

        if ($post_id && !empty($meta_data)) {
            // Ajouter les métadonnées système par défaut
            $default_meta = [
                '_edit_lock' => time() . ':1',
                '_edit_last' => $post_data['post_author'] ?? 1
            ];

            $all_meta = array_merge($default_meta, $meta_data);

            if (!$this->addPostMeta($post_id, $all_meta)) {
                error_log("Échec ajout métadonnées pour post ID: $post_id");
            }
        }
        return $post_id;
    }

    /**
     * Créer une formation spécifiquement
     *
     * @param array $formation_data - Données de la formation
     * @return int|false - ID de la formation créée
     */
    public function createFormation($formation_data)
    {
        // Construire le contenu HTML formaté pour LearnPress
        $postContent = $this->buildLearnPressContent($formation_data);

        // Vérifier si l'auteur est un administrateur
        $author_id = $formation_data['author'] ?? 1;
        $is_admin_author = $this->isUserAdministrator($author_id);

        // Données du post principal
        $post_data = [
            'post_title' => $formation_data['title'],
            'categories' => $formation_data['categories'],
            'post_content' => $postContent,
            'post_excerpt' => $formation_data['description'] ?? '',
            'post_type' => 'lp_course', // Ajuste selon ton post_type
            'post_status' => $formation_data['status'] ?? 'pending',
            'post_author' => $author_id
        ];

        // Métadonnées spécifiques aux formations
        $meta_data = [
            'submitted_for_validation' => '1' // Marquer comme soumis à validation
        ];

        // Image de catégorie automatique - toujours assigner si pas d'image spécifique
        if (!isset($formation_data['image_id']) || empty($formation_data['image_id'])) {
            if (!empty($formation_data['categories'])) {
                $categories = is_array($formation_data['categories']) ? $formation_data['categories'] : [$formation_data['categories']];

                // Chercher la première catégorie qui a une image (ou utiliser la première avec fallback)
                $selected_category = null;
                $cat_image = null;

                foreach ($categories as $cat_id) {
                    $image = $this->getTermMeta($cat_id, 'category_image'); // Vérifier image spécifique d'abord
                    if ($image) {
                        $selected_category = $cat_id;
                        $cat_image = $image;
                        break; // Priorité aux catégories avec image spécifique
                    }
                }

                // Si aucune catégorie n'a d'image spécifique, prendre la première avec fallback
                if (!$cat_image && !empty($categories)) {
                    $selected_category = $categories[0];
                    $cat_image = $this->getCategoryImage($selected_category);
                }

                if ($cat_image) {
                    // Créer un attachment WordPress et l'associer comme thumbnail
                    $attachment_id = $this->createAttachmentFromImagePath($cat_image);
                    if ($attachment_id) {
                        $meta_data['_thumbnail_id'] = $attachment_id;
                        $meta_data['_category_image_source'] = $selected_category; // Stocker quelle catégorie a fourni l'image
                    }
                }
            }
        }

        // Convertir le prix en EUR si nécessaire pour LearnPress
        $lp_price = $formation_data['price'];
        if ($formation_data['devise'] == 'XAF') {
            $lp_price /= 650;
            $lp_price = number_format($lp_price, 2);
        }

        // Préparer les données pour les métadonnées personnalisées
        if ($formation_data['devise'] == 'XAF') {
            $formation_data['price']  /= 650;
            $formation_data['price'] = number_format($formation_data['price'], 2);
        }
        $formation_data['devise'] = 'EUR';

        // Traiter les données d'intervention si elles existent
        if (isset($formation_data['intervention']) && is_array($formation_data['intervention'])) {
            for ($i = 0; $i < count($formation_data['intervention']); $i++) {
                $formation_data['intervention'][$i] =  $formation_data['intervention'][$i]['day'] . ', ' . $formation_data['intervention'][$i]['hour'];
            }
            $formation_data['intervention'] = implode('|', $formation_data['intervention']);
        } else {
            $formation_data['intervention'] = '';
        }

        // Ajouter les métadonnées LearnPress pour l'affichage frontend
        $meta_data['_lp_price'] = $lp_price;
        $meta_data['_lp_currency'] = $formation_data['devise'] ?? 'EUR'; // Devise du cours
        $meta_data['_lp_sale_price'] = ''; // Prix soldé (vide si pas de promotion)
        $meta_data['_lp_duration'] = $formation_data['duree_final'] ?? '';
        $meta_data['_lp_max_students'] = $formation_data['max_stud'] ?? 0;
        $meta_data['_lp_students'] = 0; // Nombre d'étudiants inscrits (initialisé à 0)
        $meta_data['_lp_featured'] = 'no'; // Pas en vedette par défaut
        $meta_data['_lp_course_author'] = $formation_data['author'] ?? 1;

        // Métadonnées LearnPress avancées pour affichage complet
        // Prérequis : séparer par ligne et créer un tableau
        $requirements = [];
        if (!empty($formation_data['prerequis'])) {
            $prereq_lines = explode("\n", trim($formation_data['prerequis']));
            foreach ($prereq_lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    // Supprimer les puces existantes
                    $line = preg_replace('/^[-•*]\s*/', '', $line);
                    $requirements[] = $line;
                }
            }
        }
        $meta_data['_lp_requirements'] = $requirements;

        $meta_data['_lp_target_audiences'] = !empty($formation_data['level']) ? [$formation_data['level']] : []; // Public cible

        // Modules comme fonctionnalités clés
        $key_features = [];
        if (!empty($formation_data['modules'])) {
            if (is_array($formation_data['modules'])) {
                $modules = $formation_data['modules'];
            } else {
                $modules = explode(', ', $formation_data['modules']);
            }
            foreach ($modules as $module) {
                $module = trim($module);
                if (!empty($module)) {
                    $key_features[] = $module;
                }
            }
        }
        $meta_data['_lp_key_features'] = $key_features;

        // Formats pédagogiques comme métadonnée personnalisée pour LearnPress
        $formats = [];
        if (!empty($formation_data['formats'])) {
            if (is_array($formation_data['formats'])) {
                $format_list = $formation_data['formats'];
            } else {
                $format_list = explode(', ', $formation_data['formats']);
            }
            foreach ($format_list as $format) {
                $format = trim($format);
                if (!empty($format)) {
                    $formats[] = $format;
                }
            }
        }
        $meta_data['_lp_course_formats'] = $formats; // Métadonnée personnalisée pour les formats
        $meta_data['_lp_level'] = $formation_data['level'] ?? ''; // Niveau du cours
        $meta_data['_lp_faqs'] = []; // FAQ (vide pour l'instant)

        // Métadonnées LearnPress pricing complètes
        $meta_data['_lp_regular_price'] = $lp_price; // Prix régulier (même que prix principal)
        $meta_data['_lp_course_price'] = $lp_price; // Prix du cours
        $meta_data['_lp_free'] = ($lp_price == 0) ? 'yes' : 'no'; // Cours gratuit ou non
        
        // Lien de redirection pour formations gratuites
        if (isset($formation_data['free_redirect_link']) && !empty($formation_data['free_redirect_link'])) {
            $meta_data['_sl_free_redirect_link'] = $formation_data['free_redirect_link'];
        }

        // Métadonnées LearnPress content drip
        $meta_data['_lp_content_drip_drip_type'] = 'specific_date'; // Type de content drip

        // Métadonnées LearnPress social/forums
        $meta_data['_lp_course_forum'] = ''; // ID du forum (vide par défaut)
        $meta_data['_lp_coming_soon_msg'] = 'This course will be coming soon'; // Message coming soon
        $meta_data['_lp_coming_soon_countdown'] = 'no'; // Countdown désactivé
        $meta_data['_lp_coming_soon_end_time'] = ''; // Date de fin coming soon
        $meta_data['_lp_coming_soon_metadata'] = 'no'; // Métadonnées coming soon
        $meta_data['_lp_coming_soon_showtext'] = 'no'; // Afficher texte coming soon

        // Métadonnées LearnPress advanced
        $meta_data['_lp_offline_lesson_count'] = 0; // Nombre de leçons offline
        $meta_data['_lp_course_status'] = 'publish'; // Statut du cours
        $meta_data['_lp_cert'] = ''; // Certificat (vide)
        $meta_data['_lp_address'] = ''; // Adresse (vide)

        // Métadonnées LearnPress assessment
        $meta_data['_lp_block_expire_duration'] = 'no'; // Durée d'expiration du bloc

        // Autres métadonnées LearnPress importantes
        $meta_data['_lp_course_result'] = 'evaluate_lesson'; // Mode d'évaluation
        $meta_data['_lp_passing_condition'] = 50; // Condition de réussite (%)
        $meta_data['_lp_payment'] = 'yes'; // Paiement activé
        $meta_data['_lp_required_enroll'] = 'yes'; // Inscription requise
        $meta_data['_lp_no_required_enroll'] = 'no'; // Pas d'inscription gratuite
        $meta_data['_lp_has_finish'] = 'yes'; // Possibilité de finir le cours
        $meta_data['_lp_block_finished'] = 'yes'; // Bloquer après fin
        $meta_data['_lp_course_repurchase_option'] = 'reset'; // Option de rachat
        $meta_data['_lp_allow_course_repurchase'] = 'no'; // Permettre le rachat
        $meta_data['_lp_coming_soon'] = 'no'; // Pas en coming soon
        $meta_data['_lp_offline_course'] = 'no'; // Cours en ligne
        $meta_data['_lp_bbpress_forum_enable'] = isset($formation_data['forum']) && $formation_data['forum'] === 'yes' ? 'yes' : 'no'; // Forum activé
        $meta_data['_lp_bbpress_forum_enrolled_user'] = isset($formation_data['forum']) && $formation_data['forum'] === 'yes' ? 'yes' : 'no'; // Forum pour inscrits
        $meta_data['_lp_submission'] = 'yes'; // Soumission activée
        $meta_data['_lp_hide_students_list'] = 'no'; // Afficher la liste des étudiants
        $meta_data['_lp_content_drip_enable'] = 'no'; // Content drip désactivé
        $meta_data['_lp_prerequisite_allow_purchase'] = 'no'; // Prérequis pour achat
        $meta_data['_lp_retake_count'] = 0; // Nombre de tentatives
        $meta_data['_lp_deliver_type'] = 'private_1_1'; // Type de livraison

        // Ajouter les métadonnées de formation personnalisées si elles existent
        $formation_fields = [
            'duree_formation' => 'duree_final',
            'prix_formation' => 'price',
            'devise' => 'devise',
            'niveau_public_formation' => 'level',
            'objectif_formation' => 'objectif',
            'langue_formation' => 'lang',
            'type_formation' => 'type_course',
            'type_projet' => 'projet_type',
            /* 'public_cible' => 'public_cible',le formulaire récupère seulement le niveau du public */
            'programme_formation' => 'modules',
            'evaluation_formation' => 'exam',
            'format_formation' => 'formats',
            'suivi_du_formateur' => 'follow',
            'activation_forum' => 'forum',
            'activation_chat' => 'chat',
            'intervention' => 'intervention',
            'recommandation' => 'recommandation',
            'activation_visioconf' => 'visioconference',
            'prerequis_formation' => 'prerequis',
            'nombre_participants_max' => 'max_stud', //
            /* 'formateur' => 'formateur' */
        ];

        foreach ($formation_fields as $meta_key => $data_key) {
            if (isset($formation_data[$data_key])) {
                $meta_data[$meta_key] = $formation_data[$data_key];
            }
        }


        return $this->createCompletePost($post_data, $meta_data);
    }

    /**
     * Mettre à jour un post existant
     * 
     * @param int $post_id - ID du post
     * @param array $post_data - Nouvelles données
     * @return bool - Succès de l'opération
     */
    public function updatePost($post_id, $post_data)
    {
        try {
            // Ajouter la date de modification
            $post_data['post_modified'] = date('Y-m-d H:i:s');
            $post_data['post_modified_gmt'] = gmdate('Y-m-d H:i:s');

            // Construire la requête UPDATE
            $set_clauses = [];
            foreach ($post_data as $column => $value) {
                $set_clauses[] = "$column = :$column";
            }

            $sql = "UPDATE {$this->tablePrefix}posts SET " . implode(', ', $set_clauses) . " WHERE ID = :post_id";

            $stmt = $this->pdo->prepare($sql);
            $post_data['post_id'] = $post_id;

            return $stmt->execute($post_data);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour post: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour ou ajouter une métadonnée
     * 
     * @param int $post_id - ID du post
     * @param string $meta_key - Clé de la métadonnée
     * @param mixed $meta_value - Valeur de la métadonnée
     * @return bool - Succès de l'opération
     */
    public function updatePostMeta($post_id, $meta_key, $meta_value)
    {
        try {
            // Vérifier si la métadonnée existe
            $sql = "SELECT meta_id FROM {$this->tablePrefix}postmeta 
                    WHERE post_id = :post_id AND meta_key = :meta_key";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id, 'meta_key' => $meta_key]);

            // Sérialiser si nécessaire
            if (is_array($meta_value) || is_object($meta_value)) {
                $meta_value = serialize($meta_value);
            }

            if ($stmt->fetch()) {
                // Mettre à jour
                $sql = "UPDATE {$this->tablePrefix}postmeta 
                        SET meta_value = :meta_value 
                        WHERE post_id = :post_id AND meta_key = :meta_key";
            } else {
                // Insérer
                $sql = "INSERT INTO {$this->tablePrefix}postmeta (post_id, meta_key, meta_value) 
                        VALUES (:post_id, :meta_key, :meta_value)";
            }

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'post_id' => $post_id,
                'meta_key' => $meta_key,
                'meta_value' => $meta_value
            ]);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour postmeta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour ou ajouter une métadonnée à un terme (catégorie)
     * 
     * @param int $term_id - ID du terme
     * @param string $meta_key - Clé de la métadonnée
     * @param mixed $meta_value - Valeur de la métadonnée
     * @return bool - Succès de l'opération
     */
    public function updateTermMeta($term_id, $meta_key, $meta_value)
    {
        try {
            // Vérifier si la métadonnée existe
            $sql = "SELECT meta_id FROM {$this->tablePrefix}termmeta 
                    WHERE term_id = :term_id AND meta_key = :meta_key";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['term_id' => $term_id, 'meta_key' => $meta_key]);

            // Sérialiser si nécessaire
            if (is_array($meta_value) || is_object($meta_value)) {
                $meta_value = serialize($meta_value);
            }

            if ($stmt->fetch()) {
                // Mettre à jour
                $sql = "UPDATE {$this->tablePrefix}termmeta 
                        SET meta_value = :meta_value 
                        WHERE term_id = :term_id AND meta_key = :meta_key";
            } else {
                // Insérer
                $sql = "INSERT INTO {$this->tablePrefix}termmeta (term_id, meta_key, meta_value) 
                        VALUES (:term_id, :meta_key, :meta_value)";
            }

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'term_id' => $term_id,
                'meta_key' => $meta_key,
                'meta_value' => $meta_value
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour termmeta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer une métadonnée d'un terme
     * 
     * @param int $term_id - ID du terme
     * @param string $meta_key - Clé de la métadonnée
     * @return mixed|null - Valeur ou null
     */
    public function getTermMeta($term_id, $meta_key)
    {
        try {
            $sql = "SELECT meta_value FROM {$this->tablePrefix}termmeta 
                    WHERE term_id = :term_id AND meta_key = :meta_key";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['term_id' => $term_id, 'meta_key' => $meta_key]);
            $result = $stmt->fetch();

            if ($result) {
                $value = $result['meta_value'];
                if ($this->is_serialized($value)) {
                    $value = unserialize($value);
                }
                return $value;
            }
            return null;
        } catch (\PDOException $e) {
            error_log("Erreur récupération termmeta: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Uploader une image de catégorie avec validation et redimensionnement
     *
     * @param array $file - Fichier de $_FILES
     * @param int $term_id - ID de la catégorie
     * @return string|false - Chemin relatif de l'image ou false
     */
    public function uploadCategoryImage($file, $term_id)
    {
        try {
            // Debug logging
            error_log("Upload attempt for category $term_id");
            error_log("File info: " . print_r($file, true));

            // 1. Validation du fichier
            if (!isset($file['tmp_name'])) {
                error_log("Aucun fichier tmp_name");
                return false;
            }

            // Pour le développement/debugging, accepter aussi les fichiers locaux
            // En production, garder seulement is_uploaded_file()
            if (!is_uploaded_file($file['tmp_name']) && !file_exists($file['tmp_name'])) {
                error_log("Fichier ni uploadé ni existant localement: " . $file['tmp_name']);
                return false;
            }

            // 2. Validation du type MIME
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $file['tmp_name']);
            finfo_close($file_info);

            if (!in_array($mime_type, $allowed_types)) {
                error_log("Type de fichier non autorisé: $mime_type");
                return false;
            }

            // 3. Validation de la taille (max 10MB pour les images de catégories)
            $max_size = 10 * 1024 * 1024; // 10MB
            if ($file['size'] > $max_size) {
                error_log("Fichier trop volumineux: " . $file['size'] . " bytes (max: $max_size)");
                return false;
            }

            // 4. Créer le dossier d'upload
            $upload_dir = __DIR__ . '/../../uploads/categories/';
            error_log("Upload dir: $upload_dir");
            if (!is_dir($upload_dir)) {
                $mkdir_result = mkdir($upload_dir, 0755, true);
                error_log("Created upload dir: " . ($mkdir_result ? 'success' : 'failed'));
            }

            // 5. Générer le nom de fichier unique
            $ext = $this->getImageExtension($mime_type);
            $filename = 'category_' . $term_id . '_' . time() . '.' . $ext;
            $target_file = $upload_dir . $filename;
            error_log("Target file: $target_file");

            // 6. Traiter l'image (redimensionner si nécessaire)
            $processed_image = $this->processCategoryImage($file['tmp_name'], $target_file, $mime_type);
            if (!$processed_image) {
                error_log("Échec du traitement de l'image");
                return false;
            }

            error_log("Upload successful: uploads/categories/$filename");
            return 'uploads/categories/' . $filename;

        } catch (\Exception $e) {
            error_log("Erreur upload image catégorie: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir l'extension d'image à partir du type MIME
     *
     * @param string $mime_type
     * @return string
     */
    private function getImageExtension($mime_type)
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        return $extensions[$mime_type] ?? 'jpg';
    }

    /**
     * Traiter et redimensionner l'image de catégorie
     *
     * @param string $source_path - Chemin du fichier source
     * @param string $target_path - Chemin du fichier cible
     * @param string $mime_type - Type MIME
     * @return bool - Succès du traitement
     */
    private function processCategoryImage($source_path, $target_path, $mime_type)
    {
        try {
            // Vérifier si GD est disponible
            if (!function_exists('imagecreatetruecolor')) {
                error_log("Extension GD non disponible pour le traitement d'image");
                // Copier simplement le fichier sans traitement
                return copy($source_path, $target_path);
            }

            // Créer l'image source
            switch ($mime_type) {
                case 'image/jpeg':
                case 'image/jpg':
                    $source_image = \imagecreatefromjpeg($source_path);
                    break;
                case 'image/png':
                    $source_image = \imagecreatefrompng($source_path);
                    break;
                case 'image/gif':
                    $source_image = \imagecreatefromgif($source_path);
                    break;
                case 'image/webp':
                    $source_image = \imagecreatefromwebp($source_path);
                    break;
                default:
                    return false;
            }

            if (!$source_image) {
                return false;
            }

            // Obtenir les dimensions originales
            $original_width = \imagesx($source_image);
            $original_height = \imagesy($source_image);

            // Dimensions cibles (800x600 max, maintenir le ratio)
            $max_width = 800;
            $max_height = 600;

            // Calculer les nouvelles dimensions en gardant le ratio
            $ratio = min($max_width / $original_width, $max_height / $original_height, 1);

            $new_width = round($original_width * $ratio);
            $new_height = round($original_height * $ratio);

            // Créer l'image redimensionnée
            $resized_image = \imagecreatetruecolor($new_width, $new_height);

            // Préserver la transparence pour PNG/GIF
            if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
                \imagecolortransparent($resized_image, \imagecolorallocatealpha($resized_image, 0, 0, 0, 127));
                \imagealphablending($resized_image, false);
                \imagesavealpha($resized_image, true);
            }

            // Redimensionner
            \imagecopyresampled(
                $resized_image, $source_image,
                0, 0, 0, 0,
                $new_width, $new_height,
                $original_width, $original_height
            );

            // Sauvegarder l'image
            $success = false;
            switch ($mime_type) {
                case 'image/jpeg':
                case 'image/jpg':
                    $success = \imagejpeg($resized_image, $target_path, 85); // Qualité 85%
                    break;
                case 'image/png':
                    $success = \imagepng($resized_image, $target_path, 8); // Compression 8/9
                    break;
                case 'image/gif':
                    $success = \imagegif($resized_image, $target_path);
                    break;
                case 'image/webp':
                    $success = \imagewebp($resized_image, $target_path, 85);
                    break;
            }

            // Libérer la mémoire
            \imagedestroy($source_image);
            \imagedestroy($resized_image);

            return $success;

        } catch (\Exception $e) {
            error_log("Erreur traitement image: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer l'image d'une catégorie (avec fallback par défaut)
     *
     * @param int $term_id
     * @return string|null
     */
    public function getCategoryImage($term_id)
    {
        $image = $this->getTermMeta($term_id, 'category_image');
        if ($image) {
            return $image;
        }

        // Retourner une image par défaut générique
        return $this->getDefaultCategoryImage();
    }

    /**
     * Obtenir l'image de catégorie par défaut
     *
     * @return string
     */
    private function getDefaultCategoryImage()
    {
        // Utiliser l'image d'illustration de catégorie par défaut
        $default_image = __DIR__ . '/../../uploads/categories/category_illustration.jpg';

        if (file_exists($default_image)) {
            return 'uploads/categories/category_illustration.jpg';
        }

        // Fallback vers d'autres images si celle-ci n'existe pas
        $fallback_images = [
            __DIR__ . '/../../assets/images/default_category.jpg',
            __DIR__ . '/../../assets/images/informatique_category.jpg'
        ];

        foreach ($fallback_images as $image_path) {
            if (file_exists($image_path)) {
                return str_replace(__DIR__ . '/../../', '', $image_path);
            }
        }

        // Si aucune image par défaut n'existe, retourner null
        return null;
    }


    /**
     * Créer un attachment WordPress depuis un chemin d'image
     *
     * @param string $image_path - Chemin relatif de l'image
     * @return int|false - ID de l'attachment créé ou false
     */
    public function createAttachmentFromImagePath($image_path)
    {
        try {
            // Normalize the path to remove double uploads prefix
            $image_path = preg_replace('#^uploads/uploads/#', 'uploads/', $image_path);

            // Le chemin est relatif à la racine du site web
            $full_path = __DIR__ . '/../../' . $image_path;
            if (!file_exists($full_path)) {
                error_log("Image file does not exist: $full_path");
                return false;
            }

            // Obtenir les informations du fichier
            $file_info = pathinfo($full_path);
            $filename = $file_info['basename'];

            // Construire l'URL complète pour le GUID
            // Le site est à /Studies-learning/, donc l'URL complète est nécessaire
            $site_url = 'https://studieslearning-tkaw0oailp.live-website.com/Studies-learning';
            $file_url = $site_url . '/' . $image_path;

            // Créer le post attachment
            $attachment_data = [
                'post_title' => $filename,
                'post_content' => '',
                'post_status' => 'inherit',
                'post_mime_type' => mime_content_type($full_path),
                'guid' => $file_url,
                'post_type' => 'attachment'
            ];

            $attachment_id = $this->insertPost($attachment_data);
            if (!$attachment_id) {
                error_log("Failed to create attachment post");
                return false;
            }

            // Ajouter les métadonnées d'attachment
            $attachment_meta = [
                '_wp_attached_file' => $image_path,
                '_wp_attachment_metadata' => [
                    'width' => 800, // Valeurs par défaut
                    'height' => 600,
                    'file' => $image_path,
                    'sizes' => []
                ]
            ];

            foreach ($attachment_meta as $key => $value) {
                $this->updatePostMeta($attachment_id, $key, $value);
            }

            return $attachment_id;

        } catch (Exception $e) {
            error_log("Error creating attachment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un post et ses métadonnées
     * 
     * @param int $post_id - ID du post
     * @return bool - Succès de l'opération
     */
    public function deletePost($post_id)
    {
        try {
            $this->pdo->beginTransaction();

            // Supprimer les métadonnées
            $sql = "DELETE FROM {$this->tablePrefix}postmeta WHERE post_id = :post_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id]);

            // Supprimer le post
            $sql = "DELETE FROM {$this->tablePrefix}posts WHERE ID = :post_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur suppression post: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Récupérer la(les) catégorie(s) du cours
     * @param mixed $post_id
     * @return void
     */
    public function getPostCategories($post_id): array|bool
    {
        try {
            $sql = "SELECT tr.term_taxonomy_id, t.name FROM {$this->tablePrefix}term_relationships as tr
                    INNER JOIN {$this->tablePrefix}term_taxonomy as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$this->tablePrefix}terms as t ON tt.term_id = t.term_id
                    WHERE object_id = :post_id ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id]);
            $categoriesId = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            if ($categoriesId) {
                return $categoriesId;
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Get post categories : " . $e->getMessage());
            return false;
        }
    }
    /*   public function getCategoryImage($catId){
        try {
            $sql = 
        } catch (\Throwable $th) {
            //throw $th;
        }
    } */

    /**
     * Récupérer un post avec ses métadonnées
     * 
     * @param int $post_id - ID du post
     * @return array|false - Données du post ou false
     */
    public function getPostWithMeta($post_id)
    {
        try {
            // Récupérer le post
            $sql = "SELECT * FROM {$this->tablePrefix}posts WHERE ID = :post_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$post) {
                return false;
            }
            $categories = $this->getPostCategories($post_id);
            // Récupérer les métadonnées
            $sql = "SELECT meta_key, meta_value FROM {$this->tablePrefix}postmeta WHERE post_id = :post_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id]);
            $meta = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            $post['meta'] = $meta;
            $post['categories'] = $categories;
            return $post;
        } catch (PDOException $e) {
            error_log("Erreur récupération post: " . $e->getMessage());
            return false;
        }
    }

    public function getAllPost()
    {
        $sql = "SELECT ID FROM `kyd4_posts` WHERE post_type = 'lp_course' AND (post_status = 'publish' OR  post_status = 'draft')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $postIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $posts = [];
        $count = 0;
        foreach ($postIds as $postId) {
            $postWithMeta = $this->getPostWithMeta($postId);
            $posts[$count] = $postWithMeta;
            $count++;
        }
        return $posts;
    }

    public function validatePost($post_id)
    {
        // Update post status to publish
        $result = $this->updatePost($post_id, ['post_status' => 'publish']);

        if ($result) {
            // Get formation details for notifications
            $post = $this->getPostWithMeta($post_id);
            $formationTitle = $post['post_title'] ?? 'Formation inconnue';
            $formateurId = $post['post_author'] ?? null;

            // Rebuild content with styled HTML for consistency
            $formationData = $this->mapExistingFormationToLearnPress($post);
            $styledContent = $this->buildLearnPressContent($formationData);
            $this->updatePost($post_id, ['post_content' => $styledContent]);

            // Assign category image if not already present
            $existing_thumbnail = $this->getPostMeta($post_id, '_thumbnail_id');
            if (!$existing_thumbnail) {
                $categories = $post['categories'] ?? [];
                if (!empty($categories)) {
                    // Get first category ID
                    $category_ids = array_keys($categories);
                    $category_id = $category_ids[0];

                    $cat_image = $this->getCategoryImage($category_id);
                    if ($cat_image) {
                        $attachment_id = $this->createAttachmentFromImagePath($cat_image);
                        if ($attachment_id) {
                            $this->updatePostMeta($post_id, '_thumbnail_id', $attachment_id);
                            $this->updatePostMeta($post_id, '_category_image_source', $category_id);
                        }
                    }
                }
            }

            // Send notification to formateur
            if ($formateurId) {
                require_once __DIR__ . '/../../core/services/NotificationService.php';
                $notificationService = new \NotificationService();
                $notificationService->notifyFormationValidated($formateurId, $formationTitle);
            }
        }

        return $result;
    }
    public function getPostIdOfUser($user_id)
    {
        try {
            $sql = "SELECT ID FROM {$this->tablePrefix}posts WHERE post_author = :post_author";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_author' => $user_id]);
            $postIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return isset($postIds) ? $postIds : false;
        } catch (\PDOException $e) {
            error_log("Erreur dans la récupération des posts : " . $e->getMessage());
            return false;
        }
    }
    public function userPostCount($userId)
    {
        return count($this->getPostIdOfUser(($userId)));
    }

    /**
     * Compte le nombre de formations publiées d'un utilisateur
     */
    public function getPublishedFormationsCountByUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM {$this->tablePrefix}posts
                WHERE post_author = :user_id AND post_type = 'lp_course' AND post_status = 'publish'";
        $stmt = $this->executeQuery($sql, [':user_id' => $userId]);
        $row = $stmt->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Compte le nombre de formations publiées d'un utilisateur
     */
    public function countPublishedFormationsByUser($userId)
    {
        $sql = "SELECT COUNT(*) AS cnt FROM {$this->tablePrefix}posts
                WHERE post_author = :user_id AND post_type = 'lp_course' AND post_status = 'publish'";
        $stmt = $this->executeQuery($sql, [':user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['cnt'] : 0;
    }

    /**
     * Générer un slug à partir d'un titre
     * 
     * @param string $title - Titre du post
     * @return string - Slug généré
     */
    private function generateSlugCourse($title)
    {
        // Convertir en minuscules
        $slug = strtolower($title);

        // Remplacer les caractères spéciaux français
        $accents = [
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ÿ' => 'y',
            'ç' => 'c',
            'ñ' => 'n'
        ];
        $slug = strtr($slug, $accents);

        // Remplacer les espaces et caractères spéciaux par des tirets
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Supprimer les tirets en début/fin
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * Vérifier si un slug existe déjà
     * 
     * @param string $slug - Slug à vérifier
     * @param int $exclude_id - ID à exclure de la vérification
     * @return bool - True si le slug existe
     */
    public function slugExistCourse($slug, $exclude_id = 0)
    {
        $sql = "SELECT ID FROM {$this->tablePrefix}posts WHERE post_name = :slug";

        if ($exclude_id > 0) {
            $sql .= " AND ID != :exclude_id";
        }

        $stmt = $this->pdo->prepare($sql);
        $params = ['slug' => $slug];

        if ($exclude_id > 0) {
            $params['exclude_id'] = $exclude_id;
        }

        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Générer un slug unique
     *
     * @param string $base_slug - Slug de base
     * @param int $exclude_id - ID à exclure
     * @return string - Slug unique
     */
    public function generateUniqueSlug($base_slug, $exclude_id = 0)
    {
        $slug = $base_slug;
        $counter = 1;

        while ($this->slugExistCourse($slug, $exclude_id)) {
            $slug = $base_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Récupérer les informations de promotion active d'une formation
     *
     * @param int $formationId - ID de la formation
     * @return array|null - Informations de promotion ou null
     */
    public function getActivePromotion($formationId)
    {
        $post = $this->getPostWithMeta($formationId);
        if (!$post) {
            return null;
        }

        $meta = $post['meta'];

        // Vérifier si la promotion est active
        if (!isset($meta['promotion_status']) || $meta['promotion_status'] !== 'active') {
            return null;
        }

        // Vérifier si la promotion n'est pas expirée
        if (!isset($meta['promotion_end_date']) || strtotime($meta['promotion_end_date']) < time()) {
            return null;
        }

        return [
            'discount_percent' => (int)($meta['promotion_discount_percent'] ?? 0),
            'start_date' => $meta['promotion_start_date'] ?? null,
            'end_date' => $meta['promotion_end_date'] ?? null,
            'original_price' => (float)($meta['prix_formation'] ?? 0),
            'currency' => $meta['devise'] ?? 'EUR',
            'time_remaining' => strtotime($meta['promotion_end_date']) - time()
        ];
    }

    /**
     * Calculer le prix promotionnel
     *
     * @param float $originalPrice - Prix original
     * @param int $discountPercent - Pourcentage de remise
     * @return float - Prix après remise
     */
    public function calculatePromoPrice($originalPrice, $discountPercent)
    {
        return round($originalPrice * (1 - $discountPercent / 100), 2);
    }

    /**
     * Récupérer le type d'une formation
     *
     * @param int $formationId - ID de la formation
     * @return string - Type de formation ('individual' ou 'enterprise')
     */
    public function getFormationType(int $formationId): string
    {
        $sql = "SELECT meta_value FROM {$this->tablePrefix}postmeta
                WHERE post_id = :post_id AND meta_key = 'formation_type'";
        $stmt = $this->executeQuery($sql, [':post_id' => $formationId]);
        $result = $stmt->fetch();

        return $result ? $result['meta_value'] : 'individual';
    }

    /**
     * Définir le type d'une formation
     *
     * @param int $formationId - ID de la formation
     * @param string $type - Type ('individual' ou 'enterprise')
     * @return bool - Succès de l'opération
     */
    public function setFormationType(int $formationId, string $type): bool
    {
        if (!in_array($type, ['individual', 'enterprise'])) {
            return false;
        }

        return $this->updatePostMeta($formationId, 'formation_type', $type);
    }

    /**
     * Basculer le type d'une formation
     *
     * @param int $formationId - ID de la formation
     * @return string|false - Nouveau type ou false en cas d'erreur
     */
    public function toggleFormationType(int $formationId): string|false
    {
        $currentType = $this->getFormationType($formationId);
        $newType = ($currentType === 'individual') ? 'enterprise' : 'individual';

        if ($this->setFormationType($formationId, $newType)) {
            return $newType;
        }

        return false;
    }

    /**
     * Vérifier si un utilisateur est administrateur
     *
     * @param int $userId - ID de l'utilisateur
     * @return bool - True si administrateur
     */
    public function isUserAdministrator(int $userId): bool
    {
        try {
            $sql = "SELECT meta_value FROM {$this->tablePrefix}usermeta
                    WHERE user_id = :user_id AND meta_key = '{$this->tablePrefix}capabilities'";
            $stmt = $this->executeQuery($sql, [':user_id' => $userId]);
            $capabilities = $stmt->fetch();

            if ($capabilities && $capabilities['meta_value']) {
                $caps = unserialize($capabilities['meta_value']);
                return isset($caps['administrator']) && $caps['administrator'] == 1;
            }

            return false;
        } catch (Exception $e) {
            error_log("Erreur vérification admin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer une métadonnée d'un post
     *
     * @param int $post_id - ID du post
     * @param string $meta_key - Clé de la métadonnée
     * @return mixed|null - Valeur de la métadonnée ou null
     */
    public function getPostMeta(int $post_id, string $meta_key)
    {
        try {
            $sql = "SELECT meta_value FROM {$this->tablePrefix}postmeta
                    WHERE post_id = :post_id AND meta_key = :meta_key";
            $stmt = $this->executeQuery($sql, [
                ':post_id' => $post_id,
                ':meta_key' => $meta_key
            ]);
            $result = $stmt->fetch();

            if ($result) {
                // Désérialiser si nécessaire
                $value = $result['meta_value'];
                if ($this->is_serialized($value)) {
                    $value = unserialize($value);
                }
                return $value;
            }

            return null;
        } catch (Exception $e) {
            error_log("Erreur récupération postmeta: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifie si une formation appartient à une école/université
     *
     * @param int $formationId - ID de la formation
     * @return bool - True si c'est une formation université
     */
    public function isUniversityCourse(int $formationId): bool {
        try {
            $sql = "SELECT meta_value FROM {$this->tablePrefix}postmeta 
                    WHERE post_id = :post_id AND meta_key = 'school_user_id' 
                    LIMIT 1";
            $stmt = $this->executeQuery($sql, [':post_id' => $formationId]);
            $result = $stmt->fetch();
            return !empty($result['meta_value']);
        } catch (Exception $e) {
            error_log("Erreur vérification university course: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère l'ID de l'école si la formation appartient à une université
     *
     * @param int $formationId - ID de la formation
     * @return int|null - ID de l'école ou null
     */
    public function getSchoolUserId(int $formationId): ?int {
        try {
            $sql = "SELECT meta_value FROM {$this->tablePrefix}postmeta 
                    WHERE post_id = :post_id AND meta_key = 'school_user_id' 
                    LIMIT 1";
            $stmt = $this->executeQuery($sql, [':post_id' => $formationId]);
            $result = $stmt->fetch();
            
            if (!empty($result['meta_value'])) {
                return (int)$result['meta_value'];
            }
            return null;
        } catch (Exception $e) {
            error_log("Erreur récupération school_user_id: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifier si une valeur est sérialisée
     *
     * @param mixed $data - Données à vérifier
     * @return bool - True si sérialisé
     */
    private function is_serialized($data)
    {
        // Vérification basique pour les données sérialisées PHP
        if (!is_string($data)) {
            return false;
        }

        $data = trim($data);
        if (empty($data)) {
            return false;
        }

        // Vérifier le format de sérialisation PHP
        if (preg_match('/^a:\d+:\{/', $data) || preg_match('/^O:\d+:"/', $data) || preg_match('/^s:\d+:"/', $data)) {
            return true;
        }

        return false;
    }

    /**
     * Récupérer les formations par type
     *
     * @param string $type - Type de formation ('individual', 'enterprise', ou 'all')
     * @return array - Liste des formations
     */
    public function getFormationsByType(string $type = 'all'): array
    {
        $sql = "SELECT p.ID, p.post_title, p.post_content, p.post_status, p.post_date,
                       pm1.meta_value as prix_formation,
                       pm2.meta_value as devise,
                       pm3.meta_value as formation_type
                FROM {$this->tablePrefix}posts p
                LEFT JOIN {$this->tablePrefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'prix_formation'
                LEFT JOIN {$this->tablePrefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'devise'
                LEFT JOIN {$this->tablePrefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'formation_type'
                WHERE p.post_type = 'lp_course' AND p.post_status = 'publish'";

        $params = [];
        if ($type !== 'all') {
            $sql .= " AND pm3.meta_value = :type";
            $params[':type'] = $type;
        }

        $sql .= " ORDER BY p.post_title ASC";

        $stmt = $this->executeQuery($sql, $params);
        $formations = $stmt->fetchAll();

        // Nettoyer et formater les données
        foreach ($formations as &$formation) {
            $formation['prix_formation'] = (float)($formation['prix_formation'] ?: 0);
            $formation['devise'] = $formation['devise'] ?: 'EUR';
            $formation['formation_type'] = $formation['formation_type'] ?: 'individual';
            // Nettoyer la description HTML
            $formation['description_clean'] = strip_tags($formation['post_content']);
        }

        return $formations;
    }

    /**
     * Compter les formations par type
     *
     * @param string $type - Type de formation ('individual', 'enterprise', ou 'all')
     * @return int - Nombre de formations
     */
    public function countFormationsByType(string $type = 'all'): int
    {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->tablePrefix}posts p
                LEFT JOIN {$this->tablePrefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = 'formation_type'
                WHERE p.post_type = 'lp_course' AND p.post_status = 'publish'";

        $params = [];
        if ($type !== 'all') {
            $sql .= " AND pm.meta_value = :type";
            $params[':type'] = $type;
        }

        $stmt = $this->executeQuery($sql, $params);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    /**
     * Mapper une formation existante vers le format LearnPress
     *
     * @param array $formation - Données de la formation existante
     * @return array - Données mappées pour LearnPress
     */
    public function mapExistingFormationToLearnPress(array $formation): array
    {
        $meta = $formation['meta'] ?? [];

        // Extraire les données des métadonnées existantes
        $formationData = [
            'title' => $formation['post_title'] ?? '',
            'description' => $formation['post_content'] ?? '',
            'price' => $meta['prix_formation'] ?? 0,
            'devise' => $meta['devise'] ?? 'EUR',
            'duree_final' => $meta['duree_formation'] ?? '',
            'level' => $meta['niveau_public_formation'] ?? 'beginner',
            'lang' => $meta['langue_formation'] ?? 'FR',
            'type_course' => $meta['type_formation'] ?? 'formation_qual',
            'modules' => $meta['programme_formation'] ?? '',
            'objectif' => $meta['objectif_formation'] ?? '',
            'prerequis' => $meta['prerequis_formation'] ?? '',
            'formats' => $meta['format_formation'] ?? '',
            'max_stud' => $meta['nombre_participants_max'] ?? 0,
            'exam' => $meta['evaluation_formation'] ?? 'no',
            'follow' => $meta['suivi_du_formateur'] ?? 'no',
            'forum' => $meta['activation_forum'] ?? 'no',
            'chat' => $meta['activation_chat'] ?? 'no',
            'visioconference' => $meta['activation_visioconf'] ?? 'no',
            'intervention' => $meta['intervention'] ?? '',
            'recommandation' => $meta['recommandation'] ?? '',
            'author' => $formation['post_author'] ?? 1,
            
            // === CHAMPS UNIVERSITÉ ===
            'school_user_id' => $meta['school_user_id'] ?? null,
            'type_formation_universite' => $meta['type_formation_universite'] ?? '',
            'public_cible_universite' => $meta['public_cible_universite'] ?? '',
            'alignement_projet' => $meta['alignement_projet'] ?? ''
        ];

        return $formationData;
    }

    /**
     * Appliquer le mapping LearnPress complet à une formation existante
     *
     * @param int $formationId - ID de la formation
     * @param array $formationData - Données mappées
     * @return bool - Succès de l'opération
     */
    public function applyLearnPressMapping(int $formationId, array $formationData): bool
    {
        try {
            // Vérifier si l'auteur est un administrateur
            $is_admin_author = $this->isUserAdministrator($formationData['author'] ?? 1);
            // Convertir le prix en EUR si nécessaire pour LearnPress
            $lp_price = $formationData['price'];
            if ($formationData['devise'] == 'XAF') {
                $lp_price /= 650;
                $lp_price = number_format($lp_price, 2);
            }

            // Métadonnées LearnPress pour l'affichage frontend
            $meta_data = [
                '_lp_price' => $lp_price,
                '_lp_currency' => $formationData['devise'] ?? 'EUR', // Devise du cours
                '_lp_sale_price' => '', // Prix soldé (vide si pas de promotion)
                '_lp_duration' => $formationData['duree_final'] ?? '',
                '_lp_max_students' => $formationData['max_stud'] ?? 0,
                '_lp_students' => 0, // Nombre d'étudiants inscrits (initialisé à 0)
                '_lp_featured' => 'no', // Pas en vedette par défaut
                '_lp_course_author' => $formationData['author'] ?? 1,
            ];

            // Métadonnées LearnPress avancées pour affichage complet
            // Prérequis : séparer par ligne et créer un tableau
            $requirements = [];
            if (!empty($formationData['prerequis'])) {
                $prereq_lines = explode("\n", trim($formationData['prerequis']));
                foreach ($prereq_lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        // Supprimer les puces existantes
                        $line = preg_replace('/^[-•*]\s*/', '', $line);
                        $requirements[] = $line;
                    }
                }
            }
            $meta_data['_lp_requirements'] = $requirements;
    
            // Métadonnées spéciales pour les formations admin
            if ($is_admin_author) {
                $meta_data['_studies_learning_admin_course'] = '1'; // Marquer comme formation admin
                $meta_data['_studies_learning_display_author'] = 'Studies Learning'; // Nom d'affichage personnalisé
            }

            $meta_data['_lp_target_audiences'] = !empty($formationData['level']) ? [$formationData['level']] : []; // Public cible

            // Modules comme fonctionnalités clés
            $key_features = [];
            if (!empty($formationData['modules'])) {
                $modules = explode(', ', $formationData['modules']);
                foreach ($modules as $module) {
                    $module = trim($module);
                    if (!empty($module)) {
                        $key_features[] = $module;
                    }
                }
            }
            $meta_data['_lp_key_features'] = $key_features;

            // Formats pédagogiques comme métadonnée personnalisée pour LearnPress
            $formats = [];
            if (!empty($formationData['formats'])) {
                $format_list = explode(', ', $formationData['formats']);
                foreach ($format_list as $format) {
                    $format = trim($format);
                    if (!empty($format)) {
                        $formats[] = $format;
                    }
                }
            }
            $meta_data['_lp_course_formats'] = $formats; // Métadonnée personnalisée pour les formats
            $meta_data['_lp_level'] = $formationData['level'] ?? ''; // Niveau du cours
            $meta_data['_lp_faqs'] = []; // FAQ (vide pour l'instant)

            // Métadonnées LearnPress pricing complètes
            $meta_data['_lp_regular_price'] = $lp_price; // Prix régulier (même que prix principal)
            $meta_data['_lp_course_price'] = $lp_price; // Prix du cours
            $meta_data['_lp_free'] = ($lp_price == 0) ? 'yes' : 'no'; // Cours gratuit ou non

            // Métadonnées LearnPress content drip
            $meta_data['_lp_content_drip_drip_type'] = 'specific_date'; // Type de content drip

            // Métadonnées LearnPress social/forums
            $meta_data['_lp_course_forum'] = ''; // ID du forum (vide par défaut)
            $meta_data['_lp_coming_soon_msg'] = 'This course will be coming soon'; // Message coming soon
            $meta_data['_lp_coming_soon_countdown'] = 'no'; // Countdown désactivé
            $meta_data['_lp_coming_soon_end_time'] = ''; // Date de fin coming soon
            $meta_data['_lp_coming_soon_metadata'] = 'no'; // Métadonnées coming soon
            $meta_data['_lp_coming_soon_showtext'] = 'no'; // Afficher texte coming soon

            // Métadonnées LearnPress advanced
            $meta_data['_lp_offline_lesson_count'] = 0; // Nombre de leçons offline
            $meta_data['_lp_course_status'] = 'publish'; // Statut du cours
            $meta_data['_lp_cert'] = ''; // Certificat (vide)
            $meta_data['_lp_address'] = ''; // Adresse (vide)

            // Métadonnées LearnPress assessment
            $meta_data['_lp_block_expire_duration'] = 'no'; // Durée d'expiration du bloc

            // Métadonnées Eduma (ThimPress) essentielles
            $meta_data['thim_course_media_intro'] = ''; // Média d'intro
            $meta_data['thim_mtb_bg_opacity'] = '1'; // Opacité background
            $meta_data['thim_mtb_custom_layout'] = '0'; // Layout personnalisé
            $meta_data['thim_mtb_hide_breadcrumbs'] = '0'; // Masquer fil d'ariane
            $meta_data['thim_mtb_hide_title_and_subtitle'] = '0'; // Masquer titre/sous-titre
            $meta_data['thim_mtb_layout'] = 'full-content'; // Layout page
            $meta_data['thim_mtb_no_padding'] = '0'; // Padding
            $meta_data['thim_mtb_using_custom_heading'] = '0'; // En-tête personnalisé

            // Métadonnées Eduma statistiques
            $meta_data['count_items'] = '0'; // Nombre d'éléments
            $meta_data['student_count'] = '0'; // Nombre d'étudiants
            $meta_data['total_sales'] = '0'; // Ventes totales
            $meta_data['real_student_enrolled'] = '0'; // Étudiants réellement inscrits
            $meta_data['lp_course_rating_average'] = '0'; // Note moyenne

            // Métadonnées du thème ThimPress pour compatibilité
            $meta_data['thim_course_language'] = $formationData['lang'] ?? 'FR'; // Langue
            $meta_data['thim_course_skill_level'] = $formationData['level'] ?? 'beginner'; // Niveau de compétence
            $meta_data['thim_course_duration'] = $formationData['duree_final'] ?? ''; // Durée du thème

            // Autres métadonnées LearnPress importantes
            $meta_data['_lp_course_result'] = 'evaluate_lesson'; // Mode d'évaluation
            $meta_data['_lp_passing_condition'] = 50; // Condition de réussite (%)
            $meta_data['_lp_payment'] = 'yes'; // Paiement activé
            $meta_data['_lp_required_enroll'] = 'yes'; // Inscription requise
            $meta_data['_lp_no_required_enroll'] = 'no'; // Pas d'inscription gratuite
            $meta_data['_lp_has_finish'] = 'yes'; // Possibilité de finir le cours
            $meta_data['_lp_block_finished'] = 'yes'; // Bloquer après fin
            $meta_data['_lp_course_repurchase_option'] = 'reset'; // Option de rachat
            $meta_data['_lp_allow_course_repurchase'] = 'no'; // Permettre le rachat
            $meta_data['_lp_coming_soon'] = 'no'; // Pas en coming soon
            $meta_data['_lp_offline_course'] = 'no'; // Cours en ligne
            $meta_data['_lp_bbpress_forum_enable'] = isset($formationData['forum']) && $formationData['forum'] === 'yes' ? 'yes' : 'no'; // Forum activé
            $meta_data['_lp_bbpress_forum_enrolled_user'] = isset($formationData['forum']) && $formationData['forum'] === 'yes' ? 'yes' : 'no'; // Forum pour inscrits
            $meta_data['_lp_submission'] = 'yes'; // Soumission activée
            $meta_data['_lp_hide_students_list'] = 'no'; // Afficher la liste des étudiants
            $meta_data['_lp_content_drip_enable'] = 'no'; // Content drip désactivé
            $meta_data['_lp_prerequisite_allow_purchase'] = 'no'; // Prérequis pour achat
            $meta_data['_lp_retake_count'] = 0; // Nombre de tentatives
            $meta_data['_lp_deliver_type'] = 'private_1_1'; // Type de livraison

            // Métadonnées spéciales pour les formations admin
            if ($is_admin_author) {
                $meta_data['_studies_learning_admin_course'] = '1'; // Marquer comme formation admin
                $meta_data['_studies_learning_display_author'] = 'Studies Learning'; // Nom d'affichage personnalisé
            }

            // === MÉTADONNÉES UNIVERSITÉ ===
            // Mapping des champs spécifiques aux universités vers LearnPress
            if (!empty($formationData['school_user_id'])) {
                $meta_data['_lp_school_user_id'] = $formationData['school_user_id'];
            }

            if (!empty($formationData['type_formation_universite'])) {
                $meta_data['_lp_university_course_type'] = $formationData['type_formation_universite'];
            }

            if (!empty($formationData['public_cible_universite'])) {
                // Encoder en JSON si c'est un array
                $audience = $formationData['public_cible_universite'];
                if (is_array($audience)) {
                    $audience = json_encode($audience);
                }
                $meta_data['_lp_university_target_audience'] = $audience;
            }

            if (!empty($formationData['alignement_projet'])) {
                $meta_data['_lp_project_alignment'] = $formationData['alignement_projet'];
            }

            // Ajouter les métadonnées de formation personnalisées si elles existent
            $formation_fields = [
                'duree_formation' => 'duree_final',
                'prix_formation' => 'price',
                'devise' => 'devise',
                'niveau_public_formation' => 'level',
                'objectif_formation' => 'objectif',
                'langue_formation' => 'lang',
                'type_formation' => 'type_course',
                'type_projet' => 'projet_type',
                'programme_formation' => 'modules',
                'evaluation_formation' => 'exam',
                'format_formation' => 'formats',
                'suivi_du_formateur' => 'follow',
                'activation_forum' => 'forum',
                'activation_chat' => 'chat',
                'intervention' => 'intervention',
                'recommandation' => 'recommandation',
                'activation_visioconf' => 'visioconference',
                'prerequis_formation' => 'prerequis',
                'nombre_participants_max' => 'max_stud',
                'formation_type' => 'formation_type'
            ];

            foreach ($formation_fields as $meta_key => $data_key) {
                if (isset($formationData[$data_key])) {
                    $meta_data[$meta_key] = $formationData[$data_key];
                }
            }

            // Appliquer toutes les métadonnées
            foreach ($meta_data as $meta_key => $meta_value) {
                $this->updatePostMeta($formationId, $meta_key, $meta_value);
            }

            return true;

        } catch (Exception $e) {
            error_log("Erreur lors du mapping LearnPress pour la formation $formationId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Construire le contenu HTML simple et lisible pour LearnPress
     * Présentation claire avec titres en bleu clair et listes bien structurées
     *
     * @param array $formation_data - Données de la formation
     * @return string - Contenu HTML simple
     */
    public function buildLearnPressContent(array $formation_data): string
    {
        $is_admin_author = isset($formation_data['author']) && $this->isUserAdministrator($formation_data['author']);

        $content = '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; line-height: 1.6; color: #1d1e20ff;">';

        // Auteur pour formations admin
        if ($is_admin_author) {
            $content .= '<div style="background: #f8fafc; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; ">';
            $content .= '<p style="margin: 0; font-size: 1rem; color: #4b5563;"><i class="fas fa-user-graduate" style="color: #2774ccff; margin-right: 0.5rem;"></i><strong style="color: #2774ccff;">Formateur:</strong> Studies Learning</p>';
            $content .= '</div>';
        }

        // Description principale
        if (!empty($formation_data['description'])) {
            $content .= '<h2 style="color: #2774ccff; font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #dbeafe;"><i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>Description</h2>';
            $content .= '<p style="margin: 0 0 2rem 0; font-size: 1rem; color: #4b5563; line-height: 1.7;">' . nl2br(htmlspecialchars($formation_data['description'])) . '</p>';
        }

        // Objectifs
        if (!empty($formation_data['objectif'])) {
            $content .= '<h2 style="color: #2774ccff; font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #dbeafe;"><i class="fas fa-bullseye" style="margin-right: 0.5rem;"></i>Objectifs</h2>';
            $content .= '<div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">';
            $content .= '<p style=\'margin: 0; font-size: 1rem; color: #4b5563; line-height: 1.7;\'>' . nl2br(htmlspecialchars($formation_data['objectif'])) . '</p>';
            $content .= '</div>';
        }

        // Prérequis
        if (!empty($formation_data['prerequis'])) {
            $content .= '<h2 style="color: #2774ccff; font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #dbeafe;"><i class="fas fa-clipboard-check" style="margin-right: 0.5rem;"></i>Prérequis</h2>';
            $content .= '<ul style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; list-style: none; padding-left: 0;">';

            $prereq_lines = explode("\n", trim($formation_data['prerequis']));
            foreach ($prereq_lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $line = preg_replace('/^[-•*]\s*/', '', $line);
                    $content .= '<li style="margin-bottom: 0.5rem; padding-left: 1.5rem; position: relative; color: #262a30ff;">';
                    $content .= '<span style="position: absolute; left: 0; color: #2774ccff; font-weight: bold;">•</span>';
                    $content .= htmlspecialchars($line);
                    $content .= '</li>';
                }
            }
            $content .= '</ul>';
        }

        // Modules/Programme
        if (!empty($formation_data['modules'])) {
            $content .= '<h2 style="color: #2774ccff; font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #dbeafe;"><i class="fas fa-book-open" style="margin-right: 0.5rem;"></i>Programme de formation</h2>';
            $content .= '<div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">';
            $content .= '<ol style="margin: 0; padding-left: 1.5rem; color: #4b5563;">';

            // Handle array or string
            if (is_array($formation_data['modules'])) {
                $module_lines = $formation_data['modules'];
            } else {
                // Split on comma or pipe for flexibility
                $module_lines = preg_split('/[,|]/', $formation_data['modules']);
            }
            $module_number = 1;
            foreach ($module_lines as $module) {
                $module = trim($module);
                if (!empty($module)) {
                    $content .= '<li style="margin-bottom: 0.5rem; font-weight: 500;">Module ' . $module_number . ' : ' . htmlspecialchars($module) . '</li>';
                    $module_number++;
                }
            }
            $content .= '</ol>';
            $content .= '</div>';
        }

        // Formats pédagogiques
        if (!empty($formation_data['formats'])) {
            $content .= '<h2 style="color: #2774ccff; font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #dbeafe;"><i class="fas fa-chalkboard-teacher" style="margin-right: 0.5rem;"></i>Formats pédagogiques</h2>';
            $content .= '<ul style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; list-style: none; padding-left: 0;">';

            // Handle array or string
            if (is_array($formation_data['formats'])) {
                $format_lines = $formation_data['formats'];
            } else {
                // Split on comma or pipe for flexibility
                $format_lines = preg_split('/[,|]/', $formation_data['formats']);
            }
            foreach ($format_lines as $format) {
                $format = trim($format);
                if (!empty($format)) {
                    $content .= '<li style="margin-bottom: 0.5rem; padding-left: 1.5rem; position: relative; color: #4b5563;">';
                    $content .= '<span style="position: absolute; left: 0; color: #2774ccff; font-weight: bold;">•</span>';
                    $content .= htmlspecialchars($format);
                    $content .= '</li>';
                }
            }
            $content .= '</ul>';
        }

        // Informations pratiques
        $content .= '<h2 style="color: #2774ccff; font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #dbeafe;"><i class="fas fa-info" style="margin-right: 0.5rem;"></i>Informations pratiques</h2>';
        $content .= '<div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">';
        $content .= '<ul style="list-style: none; padding: 0; margin: 0;">';

        if (!empty($formation_data['duree_final'])) {
            $content .= '<li style="margin-bottom: 0.5rem; color: #191c20ff;"><strong style="color: #2774ccff;"><i class="fas fa-clock" style="margin-right: 0.5rem;"></i>Durée:</strong> ' . htmlspecialchars($formation_data['duree_final']) . '</li>';
        }

        if (!empty($formation_data['level'])) {
            $levels = [
                'all' => 'Tout public',
                'beginner' => 'Débutant',
                'medium' => 'Intermédiaire',
                'Pro' => 'Professionnel',
                'bac' => 'Niveau Bac',
                'bac+1' => 'Bac +1',
                'bac+2' => 'Bac +2',
                'bac+3' => 'Bac +3',
                'bac+4' => 'Bac +4',
                'bac+5' => 'Bac +5',
                'doctorat' => 'Doctorat'
            ];
            $levelText = $levels[$formation_data['level']] ?? $formation_data['level'];
            $content .= '<li style="margin-bottom: 0.5rem; color: #1e2024ff;"><strong style="color: #2774ccff;"><i class="fas fa-graduation-cap" style="margin-right: 0.5rem;"></i>Niveau:</strong> ' . htmlspecialchars($levelText) . '</li>';
        }

        if (!empty($formation_data['lang'])) {
            $langs = ['FR' => 'Français', 'ANG' => 'Anglais', 'PORT' => 'Portugais', 'ALL' => 'Allemand'];
            $langText = $langs[$formation_data['lang']] ?? $formation_data['lang'];
            $content .= '<li style="margin-bottom: 0.5rem; color: #1e2024ff;"><strong style="color: #2774ccff;"><i class="fas fa-language" style="margin-right: 0.5rem;"></i>Langue:</strong> ' . htmlspecialchars($langText) . '</li>';
        }

        if (!empty($formation_data['type_course'])) {
            $types = [
                'formation_certif' => 'Formation certifiante',
                'formation_qual' => 'Formation qualifiante',
                'preparation_concours' => 'Préparation aux concours',
                'preparation_diplome' => 'Préparation à un diplôme',
                'bloc' => 'Bloc de compétences',
                'cooc' => 'COOC',
                'formation_metier' => 'Formation métier',
                'jeu_pedagogique' => 'Jeu pédagogique',
                'mobile_learning' => 'Mobile Learning',
                'mooc' => 'MOOC'
            ];
            $typeText = $types[$formation_data['type_course']] ?? $formation_data['type_course'];
            $content .= '<li style="margin-bottom: 0.5rem; color: #18191bff;"><strong style="color: #2774ccff;"><i class="fas fa-certificate" style="margin-right: 0.5rem;"></i>Type:</strong> ' . htmlspecialchars($typeText) . '</li>';
        }

        if (!empty($formation_data['max_stud'])) {
            $content .= '<li style="margin-bottom: 0.5rem; color: #1c1e20ff;"><strong style="color: #2774ccff;"><i class="fas fa-users" style="margin-right: 0.5rem;"></i>Capacité:</strong> ' . htmlspecialchars($formation_data['max_stud']) . ' participants maximum</li>';
        }

        $content .= '</ul>';
        $content .= '</div>';

        $content .= '</div>';

        return trim($content);
    }

    public function validateAndProcessFormationData(array $data): array {
        $errors = [];
        $processed = [];

        // Validation langue (obligatoire) - Accepter toutes les langues
        $langue = $data['lang'] ?? "" ;

        if (empty($langue)) {
            $errors[] = 'La langue d\'enseignement est obligatoire';
        } else {
            $processed['langue'] = htmlspecialchars($langue, ENT_QUOTES, 'UTF-8');
            $langues_predfinies = ['Français', 'Anglais', 'Espagnol', 'Allemand', 'Italien', 'Arabe', 'Chinois'];
            if (!in_array($langue, $langues_predfinies)) {
                $processed['langue_personnalisee'] = htmlspecialchars($langue, ENT_QUOTES, 'UTF-8');
            }
        }

        // Validation niveaux (multi-select)
        $niveaux_selectionnes = $data['niveaux'] ?? [];
        $niveaux_autorises = ['debutant', 'intermediaire', 'avance', 'expert'];

        if (empty($niveaux_selectionnes)) {
            $niveaux_selectionnes = ['debutant'];
        }

        $niveaux_valides = array_intersect($niveaux_selectionnes, $niveaux_autorises);
        if (empty($niveaux_valides)) {
            $niveaux_valides = ['debutant'];
        }
        $processed['niveaux_json'] = json_encode(array_values($niveaux_valides));

        // Validation accessibilité handicap (obligatoire)
        $gestion_handicap = $data['gestion_handicap'] ?? 'non';
        if (!in_array($gestion_handicap, ['oui', 'non'])) {
            $errors[] = 'Veuillez indiquer si votre formation gère les handicaps';
            $gestion_handicap = 'non';
        }
        $processed['gestion_handicap'] = $gestion_handicap;

        if ($gestion_handicap === 'oui') {
            $handicap_types = $data['handicap_types'] ?? [];
            $handicap_types_autorises = [
                'moteur', 'sensoriel', 'psychique', 'cognitif', 'mental', 'maladies_invalidantes'
            ];

            if (empty($handicap_types)) {
                $errors[] = 'Veuillez sélectionner au moins un type de handicap géré';
            } else {
                $handicap_types_valides = array_intersect($handicap_types, $handicap_types_autorises);
                if (empty($handicap_types_valides)) {
                    $errors[] = 'Types de handicaps invalides';
                } else {
                    $processed['handicap_types'] = json_encode(array_values($handicap_types_valides));
                }
            }

            $handicap_explication = trim($data['handicap_explication'] ?? '');
            if (empty($handicap_explication)) {
                $errors[] = 'Veuillez expliquer comment vous gérez les handicaps';
            } else {
                $processed['handicap_explication'] = htmlspecialchars($handicap_explication, ENT_QUOTES, 'UTF-8');
            }
        }

        // Validation différenciation (obligatoire)
        $differenciation = trim($data['differenciation_formation'] ?? '');
        if (empty($differenciation)) {
            $errors[] = 'Veuillez expliquer ce qui distingue votre formation des autres';
        } else {
            $processed['differenciation_formation'] = htmlspecialchars($differenciation, ENT_QUOTES, 'UTF-8');
        }

        // Validation contrôle continu (obligatoire)
        $controle_continu = $data['controle_continu'] ?? 'non';
        if (!in_array($controle_continu, ['oui', 'non'])) {
            $errors[] = 'Veuillez indiquer si votre formation inclut un contrôle continu';
            $controle_continu = 'non';
        }
        $processed['controle_continu'] = $controle_continu;

        // Validation type examen (si examen final oui)
        $exam = $data['exam'] ?? 'no';
        if ($exam === 'yes') {
            $type_examen = $data['type_examen'] ?? [];
            if (empty($type_examen)) {
                $errors[] = 'Veuillez sélectionner au moins un type d\'examen';
            } else {
                $processed['type_examen'] = json_encode($type_examen);
                if (in_array('autres', $type_examen)) {
                    $type_examen_autre = trim($data['type_examen_autre'] ?? '');
                    if (empty($type_examen_autre)) {
                        $errors[] = 'Veuillez préciser le type d\'examen pour "Autres"';
                    } else {
                        $processed['type_examen_autre'] = htmlspecialchars($type_examen_autre, ENT_QUOTES, 'UTF-8');
                    }
                }
            }
        }

        // Validation message d'encouragement (obligatoire)
        $recommandation = trim($data['recommandation'] ?? '');
        if (empty($recommandation)) {
            $errors[] = 'Le message d\'encouragement pour les étudiants est obligatoire';
        } else {
            $processed['recommandation'] = htmlspecialchars($recommandation, ENT_QUOTES, 'UTF-8');
        }

        // Validation titre
        $titre = trim($data['title'] ?? '');
        if (empty($titre)) {
            $errors[] = 'Le titre de la formation est obligatoire';
        } else {
            $processed['title'] = htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
        }

        // Validation description
        $description = trim($data['description'] ?? '');
        if (empty($description)) {
            $errors[] = 'La description de la formation est obligatoire';
        } else {
            $processed['description'] = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        }

        // Validation objectif
        $objectif = trim($data['objectif'] ?? '');
        if (empty($objectif)) {
            $errors[] = 'L\'objectif de la formation est obligatoire';
        } else {
            $processed['objectif'] = htmlspecialchars($objectif, ENT_QUOTES, 'UTF-8');
        }

        // Validation prix - Permet les formations gratuites (prix = 0)
        $prix = $data['price'] ?? null;
        if ($prix === null || $prix === '' || !is_numeric($prix) || $prix < 0) {
            $errors[] = 'Le prix de la formation est obligatoire et doit être un nombre positif ou zéro pour une formation gratuite';
        } else {
            $processed['price'] = (float)$prix;
        }

        // Validation durée
        $duree = trim($data['duree'] ?? '');
        if (empty($duree) || !is_numeric($duree) || $duree <= 0) {
            $errors[] = 'La durée de la formation est obligatoire et doit être un nombre positif';
        } else {
            $processed['duree'] = (int)$duree;
        }

        // Validation unité durée
        $unite_duree = trim($data['unite_duree'] ?? '');
        $unites_autorisees = ['hrs', 'days', 'weeks', 'months'];
        if (empty($unite_duree) || !in_array($unite_duree, $unites_autorisees)) {
            $errors[] = 'L\'unité de durée est obligatoire';
        } else {
            $processed['unite_duree'] = $unite_duree;
        }

        // Validation type de formation
        $type_course = trim($data['type_course'] ?? '');
        $types_autorises = ['formation_certif', 'formation_qual', 'mooc', 'cooc', 'formation_metier'];
        if (empty($type_course) || !in_array($type_course, $types_autorises)) {
            $errors[] = 'Le type de formation est obligatoire';
        } else {
            $processed['type_course'] = $type_course;
        }

        // Validation catégories
        $categories = $data['categories'] ?? [];
        if (empty($categories) || !is_array($categories)) {
            $errors[] = 'Au moins une catégorie doit être sélectionnée';
        } else {
            $processed['categories'] = array_map('intval', $categories);
        }

        // Validation modules
        $modules_raw = $data['modules'] ?? '';
        $modules = is_array($modules_raw) ? implode(', ', $modules_raw) : $modules_raw;
        $modules = trim($modules);
        if (empty($modules)) {
            $errors[] = 'Au moins un module doit être défini';
        } else {
            $processed['modules'] = htmlspecialchars($modules, ENT_QUOTES, 'UTF-8');
        }

        // Validation formats pédagogiques
        $formats = $data['formats'] ?? [];
        if (empty($formats) || !is_array($formats)) {
            $formats = ['Cours_ecrit'];
        }
        $processed['formats'] = array_map(function($format) {
            return htmlspecialchars($format, ENT_QUOTES, 'UTF-8');
        }, $formats);

        // Validation prérequis
        $prerequis_raw = $data['prerequis'] ?? '';
        $prerequis = is_array($prerequis_raw) ? implode(', ', $prerequis_raw) : $prerequis_raw;
        $prerequis = trim($prerequis);
        if (empty($prerequis)) {
            $errors[] = 'Les prérequis de la formation sont obligatoires';
        } else {
            $processed['prerequis'] = htmlspecialchars($prerequis, ENT_QUOTES, 'UTF-8');
        }

        // Validation évaluation
        $evaluation = trim($data['exam'] ?? '');
        if (empty($evaluation)) {
            $errors[] = 'Le mode d\'évaluation est obligatoire';
        } else {
            $processed['exam'] = htmlspecialchars($evaluation, ENT_QUOTES, 'UTF-8');
        }

        // Validation suivi formateur
        $suivi = trim($data['follow'] ?? '');
        if (empty($suivi)) {
            $errors[] = 'Le mode de suivi du formateur est obligatoire';
        } else {
            $processed['follow'] = htmlspecialchars($suivi, ENT_QUOTES, 'UTF-8');
        }

        // Validation forum
        $forum = trim($data['forum'] ?? 'no');
        if (!in_array($forum, ['yes', 'no'])) {
            $forum = 'no';
        }
        $processed['forum'] = $forum;

        // Validation chat
        $chat = trim($data['chat'] ?? 'no');
        if (!in_array($chat, ['yes', 'no'])) {
            $chat = 'no';
        }
        $processed['chat'] = $chat;

        // Validation visioconférence
        $visioconference = trim($data['visioconference'] ?? 'yes');
        if (!in_array($visioconference, ['yes', 'no'])) {
            $visioconference = 'yes';
        }
        $processed['visioconference'] = $visioconference;
        
        // Validation Type de projet
        $projet_type = trim($data['projet_type'] ?? 'particular');
        $processed['projet_type'] = $projet_type;

        if (!empty($errors)) {
            throw new \Exception('Erreurs de validation: ' . implode(', ', $errors));
        }

        return $processed;
    }
}

