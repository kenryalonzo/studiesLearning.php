<?php
/**
 * Template part for displaying the courses section (Eduma Style Refined)
 *
 * @package studies-learning
 */

 // Récupération des formations les plus récentes (5) via le helper natif
$courses = studies_get_latest_formations(5);

// Récupération des catégories via l'API WordPress native
$categories = get_terms([
    'taxonomy' => 'course_category',
    'hide_empty' => false,
]);
?>

<?php if (!empty($courses)) : ?>
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
                        <?php foreach ($categories as $cat) : 
                            if (is_wp_error($cat)) continue;
                            $cat = (object) $cat; // S'assure que c'est un objet (sécurité contre WP_Error/arrays)
                        ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
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
                        <option value="">Tous les tarifs</option>
                        <option value="gratuit">Gratuit</option>
                        <option value="payant">Payant</option>
                    </select>
                </div>
                
                <button id="reset-filters" class="reset-btn" title="Réinitialiser" style="display : none;"><i class="ph ph-arrow-counter-clockwise"></i></button>
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
                        $is_free = (empty($course->price) || $course->price == 0);
                        $price_val = (float)$course->price;
                        $price_display = $is_free ? 'Gratuit' : (floor($price_val) == $price_val ? number_format($price_val, 0, '.', ' ') : number_format($price_val, 2, '.', ' ')) . ' €';
                    ?>
                        <div class="swiper-slide">
                            <div class="eduma-course-card">
                                <div class="course-thumb">
                                    <?php if ($course->image) : ?>
                                        <img src="<?php echo esc_url($course->image); ?>" alt="<?php echo esc_attr($course->title); ?>">
                                    <?php else : ?>
                                        <div class="thumb-placeholder"><i class="ph ph-graduation-cap"></i></div>
                                    <?php endif; ?>
                                    <div class="course-overlay">
                                        <a href="<?php echo esc_url($course->url); ?>" class="read-more-btn">VOIR PLUS</a>
                                    </div>
                                </div>
                                
                                <div class="course-content">
                                    <div class="course-author"><?php echo esc_html($course->category_name); ?></div>
                                    <h3 class="course-title-link">
                                        <a href="<?php echo esc_url($course->url); ?>"><?php echo esc_html($course->title); ?></a>
                                    </h3>
                                    
                                    <p class="course-excerpt"><?php echo esc_html($course->excerpt); ?></p>
                                    
                                    <div class="course-info-footer">
                                        <div class="info-left">
                                            <?php if ($course->duration) : ?>
                                                <span title="Durée"><i class="ph ph-clock"></i> <?php echo esc_html($course->duration); ?></span>
                                            <?php endif; ?>
                                            <?php if ($course->level) : ?>
                                                <span title="Niveau"><i class="ph ph-chart-bar"></i> <?php echo esc_html($course->level); ?></span>
                                            <?php endif; ?>
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
<?php endif; ?>
