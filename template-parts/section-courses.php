<?php
/**
 * Template part for displaying the courses section (Eduma Style Refined)
 *
 * @package studies-learning
 */

// Inclusion du modèle de formation
require_once get_template_directory() . '/BackOfficeAdmin/FormationModel.php';
$formationModel = new \BackOfficeAdmin\FormationModel();

// Récupération des formations les plus récentes (10 max)
$courses = $formationModel->getLatestFormations(10);

// Récupération des catégories pour le filtre
$categories = $formationModel->getFormationCategories();
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
                            <option value="<?php echo esc_attr($cat['id']); ?>"><?php echo esc_html($cat['name']); ?></option>
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
                        $price = $course['meta']['_lp_price'] ?? 0;
                        $is_free = (empty($price) || $price == 0);
                        $price_display = $is_free ? 'Gratuit' : (floor($price) == $price ? number_format($price, 0, '.', ' ') : number_format($price, 2, '.', ' ')) . ' €';
                        
                        // Gestion de l'image (Thumbnail WordPress ou Fallback catégorie)
                        $thumbnail_id = $course['meta']['_thumbnail_id'] ?? null;
                        $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : '';
                        
                        if (empty($image_url)) {
                            $cat_id = array_key_first($course['categories'] ?? []);
                            $fallback_path = $formationModel->getCategoryImage($cat_id);
                            $image_url = $fallback_path ? get_template_directory_uri() . '/' . $fallback_path : get_template_directory_uri() . '/assets/img/hero/ban_3_bg.png';
                        }
                        
                        $course_url = get_permalink($course['ID']);
                        $level = $course['meta']['niveau_public_formation'] ?? ($course['meta']['_lp_level'] ?? 'Tous niveaux');
                        $lessons = $course['meta']['_lp_lesson_count'] ?? 0;
                        $students = $course['meta']['_lp_students'] ?? 0;
                        $category_name = $course['category']['name'] ?? 'Formation';
                    ?>
                        <div class="swiper-slide">
                            <div class="eduma-course-card">
                                <div class="course-thumb">
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($course['post_title']); ?>">
                                    <div class="course-overlay">
                                        <a href="<?php echo esc_url($course_url); ?>" class="read-more-btn">VOIR PLUS</a>
                                    </div>
                                </div>
                                
                                <div class="course-content">
                                    <div class="course-author"><?php echo esc_html($category_name); ?></div>
                                    <h3 class="course-title-link">
                                        <a href="<?php echo esc_url($course_url); ?>"><?php echo esc_html($course['post_title']); ?></a>
                                    </h3>
                                    
                                    <div class="course-info-footer">
                                        <div class="info-left">
                                            <span><i class="ph ph-file-text"></i> <?php echo esc_html($lessons); ?></span>
                                            <span><i class="ph ph-users"></i> <?php echo esc_html($students); ?></span>
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
