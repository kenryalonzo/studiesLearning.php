<?php
/**
 * studies-learning functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package studies-learning
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function studies_learning_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on studies-learning, use a find and replace
		* to change 'studies-learning' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'studies-learning', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'studies-learning' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'studies_learning_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'studies_learning_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function studies_learning_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'studies_learning_content_width', 640 );
}
add_action( 'after_setup_theme', 'studies_learning_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function studies_learning_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'studies-learning' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'studies-learning' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'studies_learning_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function studies_learning_scripts() {
	wp_enqueue_style( 'studies-learning-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'studies-learning-style', 'rtl', 'replace' );

	// Google Fonts
	wp_enqueue_style( 'studies-learning-fonts', 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Caveat:wght@400;700&family=Great+Vibes&family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@300;400;500&display=swap', array(), null );

	// Phosphor Icons
	wp_enqueue_script( 'phosphor-icons', 'https://unpkg.com/@phosphor-icons/web', array(), null, false );

	// Swiper.js
	wp_enqueue_style( 'swiper-style', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0' );
	wp_enqueue_script( 'swiper-script', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true );

	wp_enqueue_script( 'studies-learning-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );
	wp_enqueue_script( 'studies-learning-main', get_template_directory_uri() . '/js/main.js', array(), _S_VERSION, true );
	
	// Custom Courses Section Assets
	wp_enqueue_style( 'studies-learning-courses', get_template_directory_uri() . '/css/courses-section.css', array(), _S_VERSION );
	wp_enqueue_script( 'studies-learning-courses-js', get_template_directory_uri() . '/js/courses-slider.js', array('swiper-script'), _S_VERSION, true );
	wp_enqueue_script( 'studies-learning-courses-filter', get_template_directory_uri() . '/js/courses-filter.js', array('jquery', 'studies-learning-courses-js'), _S_VERSION, true );

    // Search Banner Assets
    wp_enqueue_style( 'studies-learning-search-banner', get_template_directory_uri() . '/css/search-banner.css', array(), _S_VERSION );
    wp_enqueue_script( 'studies-learning-search-autocomplete', get_template_directory_uri() . '/js/search-autocomplete.js', array('jquery'), _S_VERSION, true );

    // Category Slider Assets
    wp_enqueue_style( 'studies-learning-categories-slider', get_template_directory_uri() . '/css/categories-slider.css', array(), _S_VERSION );
    wp_enqueue_style( 'studies-learning-featured-packages', get_template_directory_uri() . '/css/featured-packages.css', array(), _S_VERSION );

    // Localize both scripts
    $ajax_data = array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'studies_ajax_nonce' )
    );
    wp_localize_script( 'studies-learning-courses-filter', 'studiesAjax', $ajax_data );
    wp_localize_script( 'studies-learning-search-autocomplete', 'studiesSearchAjax', $ajax_data );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'studies_learning_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Menu fallback for studies-learning
 */
function studies_learning_menu_fallback() {
	echo '<ul class="nav-links">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '" class="active">Accueil</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/formations' ) ) . '">Formations</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/devenir-formateur' ) ) . '">Devenir formateur</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/sl-university' ) ) . '">SL university</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/sl-business' ) ) . '">SL business</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/blog' ) ) . '">Blog</a></li>';
	echo '</ul>';
}


/**
 * AJAX Handler for filtering courses
 */
/**
 * AJAX Handler for filtering courses (Version Native)
 */
function studies_filter_courses_handler() {
    check_ajax_referer( 'studies_ajax_nonce', 'nonce' );

    global $wpdb;

    $category_id = isset($_POST['category']) ? intval($_POST['category']) : 0;
    $level = isset($_POST['level']) ? sanitize_text_field($_POST['level']) : '';
    $price_raw = isset($_POST['price']) ? sanitize_text_field($_POST['price']) : '';

    // Construction de la requête SQL native
    $where = ["p.post_type = 'lp_course'", "p.post_status = 'publish'"];
    $joins = "";
    $params = [];

    if ($category_id > 0) {
        $joins .= " INNER JOIN {$wpdb->prefix}term_relationships tr ON p.ID = tr.object_id";
        $joins .= " INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
        $where[] = "tt.term_id = %d";
        $params[] = $category_id;
    }

    // Pour le niveau et le prix, on filtrera en PHP après avoir récupéré les IDs pour éviter trop de JOINs complexes
    // Ou on peut faire des JOINs sur postmeta. Faisons simple et efficace.
    
    $where_sql = implode(' AND ', $where);
    $query = "SELECT DISTINCT p.ID FROM {$wpdb->prefix}posts p $joins WHERE $where_sql ORDER BY p.post_date DESC LIMIT 100";
    
    if (!empty($params)) {
        $post_ids = $wpdb->get_col($wpdb->prepare($query, ...$params));
    } else {
        $post_ids = $wpdb->get_col($query);
    }

    if (empty($post_ids)) {
        echo '<div class="no-courses-found">Aucune formation ne correspond à vos critères.</div>';
        wp_die();
    }

    $count = 0;
    foreach ($post_ids as $post_id) {
        if ($count >= 10) break;

        $price = get_post_meta($post_id, '_lp_price', true);
        $course_level = strtolower(get_post_meta($post_id, '_lp_level', true) ?: get_post_meta($post_id, 'niveau_public_formation', true));
        
        // Filtrage PHP pour Niveau et Prix
        $level_pass = true;
        if (!empty($level)) {
            $level_map = ['Débutant' => 'begin', 'Intermédiaire' => 'inter', 'Avancé' => 'expert'];
            $req_lvl = isset($level_map[$level]) ? $level_map[$level] : strtolower($level);
            if (strpos($course_level, $req_lvl) === false && strpos($course_level, strtolower($level)) === false) {
                $level_pass = false;
            }
        }
        if (!$level_pass) continue;

        if ($price_raw === 'gratuit' && (!empty($price) && $price > 0)) continue;
        if ($price_raw === 'payant' && (empty($price) || $price == 0)) continue;

        $count++;
        
        // Récupération des données pour l'affichage
        $title = get_the_title($post_id);
        $url = get_permalink($post_id);
        $is_free = (empty($price) || $price == 0);
        $price_display = $is_free ? 'Gratuit' : (floor($price) == $price ? number_format($price, 0, '.', ' ') : number_format($price, 2, '.', ' ')) . ' €';
        
        // Récupérer les catégories
        $categories = wp_get_post_terms( $post_id, 'course_category' );
        $category_name = '';
        $cat_image_id = 0;

        if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
            $category = $categories[0]; // WP_Term object
            $category_name = $category->name;
            $cat_image_id = get_term_meta( $category->term_id, 'category_image', true );
        }

        // Image : thumbnail prioritaire, sinon image de catégorie, sinon fallback
        $thumb_id = get_post_meta( $post_id, '_thumbnail_id', true );
        if ( ! empty( $thumb_id ) ) {
            $image_url = wp_get_attachment_url( $thumb_id );
        } elseif ( ! empty( $cat_image_id ) ) {
            $image_url = wp_get_attachment_url( $cat_image_id );
        } else {
            $image_url = get_template_directory_uri() . '/assets/img/hero/ban_3_bg.png';
        }

        $duration = get_post_meta($post_id, 'duree_formation', true) ?: 'NC';
        ?>
        <div class="swiper-slide">
            <div class="eduma-course-card">
                <div class="course-thumb">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                    <div class="course-overlay">
                        <a href="<?php echo esc_url($url); ?>" class="read-more-btn">VOIR PLUS</a>
                    </div>
                </div>
                <div class="course-content">
                    <div class="course-author"><?php echo esc_html($category_name); ?></div>
                    <h3 class="course-title-link">
                        <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a>
                    </h3>
                    <div class="course-info-footer">
                        <div class="info-left">
                             <span title="Durée"><i class="ph ph-clock"></i> <?php echo esc_html($duration); ?></span>
                             <span title="Niveau"><i class="ph ph-chart-bar"></i> <?php echo esc_html($course_level ?: 'Tous'); ?></span>
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
        <?php
    }

    if ($count === 0) {
        echo '<div class="no-courses-found">Aucune formation ne correspond à vos critères.</div>';
    }

    wp_die();
}
add_action('wp_ajax_filter_courses', 'studies_filter_courses_handler');
add_action('wp_ajax_nopriv_filter_courses', 'studies_filter_courses_handler');

/**
 * AJAX Handler for Real-Time Course Search
 */
/**
 * AJAX Handler for Real-Time Course Search (Version Native)
 */
function studies_search_formations_handler() {
    check_ajax_referer( 'studies_ajax_nonce', 'nonce' );

    global $wpdb;

    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

    if (strlen($term) < 2) {
        wp_send_json_success([]);
    }

    $results = studies_search_formations( $term, 6 );

    if ( empty( $results ) ) {
        wp_send_json_success( [] );
    }

    wp_send_json_success( $results );
}
add_action('wp_ajax_search_formations', 'studies_search_formations_handler');
add_action('wp_ajax_nopriv_search_formations', 'studies_search_formations_handler');

/**
 * Récupère les formations réelles avec support des filtres.
 */
function studies_get_latest_formations( $limit = 5, $filters = [] ) {
    global $wpdb;

    $category = isset($filters['category']) ? $filters['category'] : '';
    $level    = isset($filters['level']) ? $filters['level'] : '';
    $price    = isset($filters['price']) ? $filters['price'] : '';

    $args = array(
        'post_type'      => 'lp_course',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids'
    );

    if ( ! empty( $category ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'course_category',
                'field'    => 'term_id',
                'terms'    => $category,
            ),
        );
    }

    $meta_query = array();

    if ( ! empty( $level ) ) {
        $meta_query[] = array(
            'key'     => '_lp_level',
            'value'   => $level,
            'compare' => '=',
        );
    }

    if ( ! empty( $price ) ) {
        if ( $price === 'gratuit' ) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_lp_price',
                    'value'   => '0',
                    'compare' => '=',
                ),
                array(
                    'key'     => '_lp_price',
                    'compare' => 'NOT EXISTS',
                ),
            );
        } elseif ( $price === 'payant' ) {
            $meta_query[] = array(
                'key'     => '_lp_price',
                'value'   => '0',
                'compare' => '>',
            );
        }
    }

    if ( ! empty( $meta_query ) ) {
        $args['meta_query'] = $meta_query;
    }

    $query = new WP_Query( $args );
    $post_ids = $query->posts;

    if ( empty( $post_ids ) ) {
        return [];
    }

    $formations = [];
    foreach ( $post_ids as $id ) {
        $post = get_post( $id );
        if ( ! $post ) continue;

        // Récupération stricte des métadonnées LearnPress
        $price    = get_post_meta( $id, '_lp_price', true );
        $raw_level = strtolower(get_post_meta( $id, '_lp_level', true ) ?: get_post_meta( $id, 'niveau_public_formation', true ));
        $duration = get_post_meta( $id, '_lp_duration', true ) ?: get_post_meta( $id, 'duree_formation', true );
        
        // Traduction du niveau pour l'affichage (DB contient "beginner", "inter", "expert", etc.)
        $display_level = 'Tous niveaux';
        if (strpos($raw_level, 'begin') !== false || strpos($raw_level, 'debut') !== false) $display_level = 'Débutant';
        elseif (strpos($raw_level, 'inter') !== false || strpos($raw_level, 'medium') !== false) $display_level = 'Intermédiaire';
        elseif (strpos($raw_level, 'expert') !== false || strpos($raw_level, 'avanc') !== false) $display_level = 'Avancé';
        $thumb_id = get_post_meta( $id, '_thumbnail_id', true );
        
        // Récupérer les catégories
        $categories = wp_get_post_terms( $id, 'course_category' );
        $category_name = '';
        $cat_image_id = 0;

        if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
            $category = $categories[0]; // WP_Term object
            $category_name = $category->name;
            $cat_image_id = get_term_meta( $category->term_id, 'category_image', true );
        }

        // Image : thumbnail prioritaire, sinon image de catégorie, sinon fallback
        if ( ! empty( $thumb_id ) ) {
            $image_url = wp_get_attachment_url( $thumb_id );
        } elseif ( ! empty( $cat_image_id ) ) {
            $image_url = wp_get_attachment_url( $cat_image_id );
        } else {
            $image_url = get_template_directory_uri() . '/assets/img/hero/ban_3_bg.png';
        }

        $formations[] = (object) [
            'id'            => $id,
            'title'         => $post->post_title,
            'content'       => $post->post_content,
            'excerpt'       => wp_trim_words( $post->post_content, 20 ),
            'price'         => $price,
            'level'         => $display_level,
            'duration'      => $duration,
            'image'         => $image_url,
            'category_name' => $category_name,
            'url'           => get_permalink( $id ),
        ];
    }

    return $formations;
}

/**
 * Recherche réelle de formations pour l'autocomplétion.
 */
function studies_search_formations( $keyword, $limit = 6 ) {
    global $wpdb;
    $like_term = '%' . $wpdb->esc_like( $keyword ) . '%';

    $sql = $wpdb->prepare(
        "SELECT ID FROM {$wpdb->prefix}posts p
         WHERE p.post_type = 'lp_course' 
         AND p.post_status = 'publish'
         AND (p.post_title LIKE %s OR p.post_content LIKE %s)
         ORDER BY 
            CASE 
                WHEN p.post_title LIKE %s THEN 1
                ELSE 2
            END,
            p.post_date DESC
         LIMIT %d",
        $like_term, $like_term, $keyword . '%', $limit
    );

    $post_ids = $wpdb->get_col( $sql );
    if ( empty( $post_ids ) ) return [];

    $results = [];
    foreach ( $post_ids as $id ) {
        $price = get_post_meta( $id, '_lp_price', true );
        $level = get_post_meta( $id, '_lp_level', true ) ?: get_post_meta( $id, 'niveau_public_formation', true );
        $terms = wp_get_post_terms( $id, 'course_category' );
        
        $thumb_id = get_post_meta( $id, '_thumbnail_id', true );
        $image_url = '';
        if ( ! empty( $thumb_id ) ) {
            $image_url = wp_get_attachment_url( $thumb_id );
        } elseif ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            $cat_image_id = get_term_meta( $terms[0]->term_id, 'category_image', true );
            if ( $cat_image_id ) {
                $image_url = wp_get_attachment_url( $cat_image_id );
            }
        }
        if ( empty( $image_url ) ) {
            $image_url = get_template_directory_uri() . '/assets/img/hero/ban_3_bg.png';
        }
        
        $results[] = [
            'id'       => $id,
            'title'    => get_the_title( $id ),
            'url'      => get_permalink( $id ),
            'category' => ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : 'Formation',
            'level'    => $level,
            'price'    => ( $price == 0 || empty($price) ) ? 'Gratuit' : number_format( (float)$price, 0, '.', ' ' ) . '€',
            'image'    => $image_url,
            'is_free'  => (empty($price) || $price == 0)
        ];
    }
    return $results;
}
/**
 * AJAX Handler for Course Filtering
 */


/**
 * Helper: Récupère les catégories de cours avec leurs données formatées.
 */
function studies_get_course_categories( $args = [] ) {
    global $wpdb;
    
    // Require count > 0 if hide_empty is configured, else fetch all.
    // For this specific integration, we just fetch active categories natively.
    $query = "SELECT t.term_id as id, t.name, t.slug, tt.count 
              FROM {$wpdb->prefix}terms t 
              INNER JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_id 
              WHERE tt.taxonomy = 'course_category' AND tt.count > 0 
              ORDER BY tt.count DESC LIMIT 20";
              
    $results = $wpdb->get_results($query, ARRAY_A);

    if ( empty( $results ) ) {
        return [];
    }

    $categories = [];
    foreach ( $results as $term ) {
        $image_id = get_term_meta( $term['id'], 'category_image', true );
        $image_url = '';
        if ( ! empty( $image_id ) ) {
            $image_url = wp_get_attachment_image_url( $image_id, 'medium' );
        }
        if ( empty( $image_url ) ) {
            $image_url = get_template_directory_uri() . '/assets/img/default-course.jpg';
        }

        $link = get_term_link( (int)$term['id'], 'course_category' );
        if ( is_wp_error( $link ) ) {
            $link = site_url( '/course-category/' . $term['slug'] );
        }

        $categories[] = [
            'id'        => $term['id'],
            'name'      => $term['name'],
            'slug'      => $term['slug'],
            'count'     => $term['count'],
            'link'      => $link,
            'image_url' => $image_url
        ];
    }
    return $categories;
}

/**
 * Helper: Récupère les packages mis en avant.
 * En l'absence de meta ou CPT dédié, on utilise les derniers lp_course.
 */
function studies_get_featured_packages( $count = 4 ) {
    $args = [
        'post_type'      => 'lp_course',
        'posts_per_page' => $count,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    ];
    $query = new WP_Query( $args );
    $packages = [];

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();
            $price = get_post_meta( $id, '_lp_price', true );
            
            // Format price handling both free and paid courses gracefully
            $price_format = ( empty( $price ) || $price == 0 ) ? 'Gratuit' : number_format( (float)$price, 2, ',', ' ' ) . ' €';

            // Get thumbnail or default fallback
            $image_url = get_the_post_thumbnail_url( $id, 'medium_large' );
            if ( empty( $image_url ) ) {
                $image_url = get_template_directory_uri() . '/assets/img/default-course.jpg';
            }

            // Get a single category for taxonomy display, default to empty
            $cat_name = '';
            $terms = wp_get_post_terms( $id, 'course_category' );
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                $cat_name = $terms[0]->name;
            }

            $packages[] = [
                'id'           => $id,
                'title'        => get_the_title(),
                'excerpt'      => wp_trim_words( get_the_excerpt(), 15, '...' ),
                'price'        => $price,
                'price_format' => $price_format,
                'image_url'    => $image_url,
                'link'         => get_permalink(),
                'category'     => $cat_name
            ];
        }
        wp_reset_postdata();
    }

    return $packages;
}
