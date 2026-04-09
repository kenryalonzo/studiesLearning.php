<?php
/**
 * Template part for displaying the courses section (Eduma Style Refined)
 *
 * @package studies-learning
 */

global $wpdb;

// Requête SQL optimisée avec les colonnes réelles fournies par l'utilisateur
$courses = $wpdb->get_results("
    SELECT 
        f.id_formation, 
        f.titre, 
        f.prix, 
        f.date_creation,
        f.niveau_formation AS niveau,
        f.nb_lessons AS nb_lecons,
        t.nom_thematique AS categorie,
        t.image_url AS image_categorie,
        (SELECT COUNT(*) FROM sl_formation_students s WHERE s.id_formation = f.id_formation) AS nb_inscrits
    FROM sl_formation f
    LEFT JOIN sl_thematique t ON f.id_thematique = t.id_thematique
    WHERE f.statut = 'publiée'
    ORDER BY f.date_creation DESC
    LIMIT 10
");

// Mode Démo : Si aucune formation n'est trouvée, on affiche des données fictives pour visualiser le design
if (empty($courses)) {
    $courses = array(
        (object) array(
            'titre' => 'Introduction LearnPress – LMS plugin',
            'prix' => 0,
            'categorie' => 'Keny White',
            'nb_lecons' => 15,
            'nb_inscrits' => 311,
            'niveau' => 'Débutant',
            'image_categorie' => ''
        ),
        (object) array(
            'titre' => 'Créez un site LMS avec LearnPress',
            'prix' => 0,
            'categorie' => 'Keny White',
            'nb_lecons' => 14,
            'nb_inscrits' => 94,
            'niveau' => 'Intermédiaire',
            'image_categorie' => ''
        ),
        (object) array(
            'titre' => 'Vendre des cours en présentiel avec LearnPress',
            'prix' => 100.00,
            'categorie' => 'Keny White',
            'nb_lecons' => 20,
            'nb_inscrits' => 0,
            'niveau' => 'Tous niveaux',
            'image_categorie' => ''
        ),
        (object) array(
            'titre' => 'Comment enseigner un cours en ligne',
            'prix' => 55.00,
            'categorie' => 'Keny White',
            'nb_lecons' => 0,
            'nb_inscrits' => 28,
            'niveau' => 'Avancé',
            'image_categorie' => ''
        )
    );
}

// Récupération des catégories pour le filtre
$categories = $wpdb->get_results("SELECT id_thematique, nom_thematique FROM sl_thematique ORDER BY nom_thematique ASC");
?>

<section class="eduma-courses-section">
    <div class="eduma-container">
        <!-- Section Header: Title left, Navigation right -->
        <div class="eduma-section-header">
            <div class="header-left">
                <h2 class="eduma-title">Cours les plus populaires</h2>
                <div class="title-separator"></div>
                <p class="eduma-subtitle">Apprentissage illimité, plus de possibilités</p>
            </div>
            
            <!-- Filter Bar -->
            <div class="courses-filters-bar">
                <button class="filter-btn active" data-filter="all">Tous</button>
                
                <div class="filter-group">
                    <select id="filter-category" class="filter-select">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat) : ?>
                            <option value="<?php echo esc_attr($cat->id_thematique); ?>"><?php echo esc_html($cat->nom_thematique); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select id="filter-level" class="filter-select">
                        <option value="">Tous les niveaux</option>
                        <option value="Débutant">Débutant</option>
                        <option value="Intermédiaire">Intermédiaire</option>
                        <option value="Avancé">Avancé</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select id="filter-price" class="filter-select">
                        <option value="">Tous les types</option>
                        <option value="gratuit">Gratuit</option>
                        <option value="payant">Payant</option>
                    </select>
                </div>
                
                <button id="reset-filters" class="reset-btn" style="display: none;"><i class="ph ph-x"></i></button>
            </div>

            <div class="header-right">
                <div class="swiper-navigation-custom">
                    <div class="swiper-button-prev-custom"><i class="ph ph-caret-left"></i></div>
                    <div class="swiper-button-next-custom"><i class="ph ph-caret-right"></i></div>
                </div>
            </div>
        </div>

        <!-- Carousel -->
        <div class="eduma-slider-wrapper">
            <!-- Loading Overlay -->
            <div class="courses-loader" style="display: none;">
                <div class="spinner"></div>
            </div>
            
            <div class="swiper eduma-courses-swiper">
                <div class="swiper-wrapper" id="courses-ajax-container">
                    <?php foreach ($courses as $course) : 
                        $is_free = (empty($course->prix) || $course->prix == 0);
                        $price_display = $is_free ? 'Gratuit' : ($course->prix == floor($course->prix) ? number_format($course->prix, 0, '.', ' ') : number_format($course->prix, 2, '.', ' ')) . ' €';
                        $image_placeholder = get_template_directory_uri() . '/assets/img/hero/ban_3_bg.png'; 
                    ?>
                        <div class="swiper-slide">
                            <div class="eduma-course-card">
                                <div class="course-thumb">
                                    <img src="<?php echo $image_placeholder; ?>" alt="<?php echo esc_attr($course->titre); ?>">
                                    <div class="course-overlay">
                                        <a href="#" class="read-more-btn">VOIR PLUS</a>
                                    </div>
                                </div>
                                
                                <div class="course-content">
                                    <div class="course-author"><?php echo esc_html($course->categorie); ?></div>
                                    <h3 class="course-title-link">
                                        <a href="#"><?php echo esc_html($course->titre); ?></a>
                                    </h3>
                                    
                                    <div class="course-info-footer">
                                        <div class="info-left">
                                            <span><i class="ph ph-file-text"></i> <?php echo esc_html($course->nb_lecons); ?></span>
                                            <span><i class="ph ph-users"></i> <?php echo esc_html($course->nb_inscrits); ?></span>
                                        </div>
                                        <div class="info-right">
                                            <span class="price-tag <?php echo $is_free ? 'is-free' : ''; ?>">
                                                <?php echo esc_html($price_display); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="eduma-footer-actions">
            <a href="#" class="view-more-global">VOIR TOUTES LES FORMATIONS</a>
        </div>
    </div>
</section>
