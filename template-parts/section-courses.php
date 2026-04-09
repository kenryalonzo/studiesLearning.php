<?php
/**
 * Template part for displaying the courses section
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
    LIMIT 5
");

// Mode Démo : Si aucune formation n'est trouvée, on affiche des données fictives pour visualiser le design
if (empty($courses)) {
    $courses = array(
        (object) array(
            'titre' => 'Développement Web Full Stack',
            'prix' => 0,
            'categorie' => 'Programmation',
            'nb_lecons' => 45,
            'nb_inscrits' => 1250,
            'niveau' => 'Intermédiaire',
            'image_categorie' => '',
            'description' => 'Maîtrisez HTML5, CSS3, JavaScript et React. Une formation intensive de 0 à expert pour devenir développeur web opérationnel.',
            'is_demo' => true
        ),
        (object) array(
            'titre' => 'Design UI/UX avec Figma',
            'prix' => 49.99,
            'categorie' => 'Design',
            'nb_lecons' => 32,
            'nb_inscrits' => 850,
            'niveau' => 'Débutant',
            'image_categorie' => '',
            'description' => 'Apprenez à concevoir des interfaces subimes et intuitives. De la théorie des couleurs au prototypage animé sur Figma.',
            'is_demo' => true
        ),
        (object) array(
            'titre' => 'Intelligence Artificielle & Python',
            'prix' => 0,
            'categorie' => 'Data Science',
            'nb_lecons' => 60,
            'nb_inscrits' => 2100,
            'niveau' => 'Avancé',
            'image_categorie' => '',
            'description' => 'Explorez le machine learning et les réseaux de neurones. Manipulez les données avec Pandas, Numpy et Scikit-Learn.',
            'is_demo' => true
        ),
        (object) array(
            'titre' => 'Marketing Digital Stratégique',
            'prix' => 29.99,
            'categorie' => 'Business',
            'nb_lecons' => 28,
            'nb_inscrits' => 540,
            'niveau' => 'Débutant',
            'image_categorie' => '',
            'description' => 'Boostez votre visibilité en ligne. Stratégies SEO, publicités Meta & Google, et analyse de données marketing.',
            'is_demo' => true
        ),
        (object) array(
            'titre' => 'Photographie de Portrait Paisible',
            'prix' => 19.99,
            'categorie' => 'Arts',
            'nb_lecons' => 15,
            'nb_inscrits' => 320,
            'niveau' => 'Tous niveaux',
            'image_categorie' => '',
            'description' => 'Capturez l\'essence de vos sujets. Gestion de la lumière naturelle, cadrage et retouche émotionnelle sur Lightroom.',
            'is_demo' => true
        )
    );
}
?>

<section class="courses-section-premium">
    <div class="container">
        <div class="section-header-v2" data-aos="fade-up">
            <h2 class="section-title-premium">Explorez les <span>meilleurs cours</span></h2>
            <p class="section-subtitle">Une sélection exclusive de formations conçues par des experts pour propulser votre carrière au niveau supérieur.</p>
        </div>

        <div class="courses-slider-wrapper">
            <div class="swiper courses-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($courses as $course) : 
                        $is_free = (empty($course->prix) || $course->prix == 0);
                        $price_display = $is_free ? 'Gratuit' : number_format($course->prix, 2, ',', ' ') . ' €';
                        $image_cat = !empty($course->image_categorie) ? $course->image_categorie : get_template_directory_uri() . '/assets/img/placeholder-category.png';
                        $niveau = !empty($course->niveau) ? $course->niveau : 'Tous niveaux';
                        $desc_demo = isset($course->is_demo) ? $course->description : 'Plongez dans l\'univers de ' . esc_html($course->titre) . '. Une formation complète conçue pour vous faire progresser rapidement.';
                    ?>
                        <div class="swiper-slide">
                            <div class="course-card-inner">
                                <!-- Card Front -->
                                <div class="course-card-front">
                                    <div class="course-category-badge">
                                        <img src="<?php echo esc_url($image_cat); ?>" alt="<?php echo esc_attr($course->categorie); ?>" class="cat-icon">
                                        <span><?php echo esc_html($course->categorie); ?></span>
                                    </div>
                                    
                                    <h3 class="course-title"><?php echo esc_html($course->titre); ?></h3>
                                    
                                    <div class="course-metas">
                                        <div class="meta-item">
                                            <i class="ph ph-book-open"></i>
                                            <span><?php echo esc_html($course->nb_lecons); ?> leçons</span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="ph ph-users"></i>
                                            <span><?php echo esc_html($course->nb_inscrits); ?> étudiants</span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="ph ph-chart-bar"></i>
                                            <span><?php echo esc_html($niveau); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="course-footer">
                                        <div class="course-price <?php echo $is_free ? 'price-free' : ''; ?>">
                                            <?php echo esc_html($price_display); ?>
                                        </div>
                                        <div class="course-btn">
                                            <i class="ph ph-arrow-right"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card Back (Flip Effect) -->
                                <div class="course-card-back">
                                    <h4>Détails du cursus</h4>
                                    <p><?php echo esc_html($desc_demo); ?></p>
                                    <ul class="course-highlights">
                                        <li><i class="ph ph-sparkle"></i> Accès illimité 24/7</li>
                                        <li><i class="ph ph-certificate"></i> Certification incluse</li>
                                        <li><i class="ph ph-circles-three-plus"></i> Projets pratiques</li>
                                    </ul>
                                    <a href="#" class="btn-primary-soft">Découvrir le programme</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Navigation -->
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next custom-nav"></div>
                <div class="swiper-button-prev custom-nav"></div>
            </div>
        </div>
    </div>
</section>
