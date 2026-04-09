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

    wp_localize_script( 'studies-learning-courses-filter', 'studiesAjax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'studies_filter_nonce' )
    ));

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
function studies_filter_courses_handler() {
    check_ajax_referer( 'studies_filter_nonce', 'nonce' );

    global $wpdb;

    $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
    $level = isset($_POST['level']) ? sanitize_text_field($_POST['level']) : '';
    $price_type = isset($_POST['price']) ? sanitize_text_field($_POST['price']) : '';

    $where = ["f.statut = 'publiée'"];
    $params = [];

    if ($category > 0) {
        $where[] = "f.id_thematique = %d";
        $params[] = $category;
    }

    if (!empty($level)) {
        $where[] = "f.niveau_formation = %s";
        $params[] = $level;
    }

    if ($price_type === 'gratuit') {
        $where[] = "(f.prix = 0 OR f.prix IS NULL)";
    } elseif ($price_type === 'payant') {
        $where[] = "f.prix > 0";
    }

    $where_sql = implode(' AND ', $where);
    
    $query = "
        SELECT 
            f.id_formation, f.titre, f.prix, f.niveau_formation AS niveau, f.nb_lessons AS nb_lecons,
            t.nom_thematique AS categorie,
            (SELECT COUNT(*) FROM sl_formation_students s WHERE s.id_formation = f.id_formation) AS nb_inscrits
        FROM sl_formation f
        LEFT JOIN sl_thematique t ON f.id_thematique = t.id_thematique
        WHERE $where_sql
        ORDER BY f.date_creation DESC
        LIMIT 10
    ";

    if (!empty($params)) {
        $courses = $wpdb->get_results($wpdb->prepare($query, ...$params));
    } else {
        $courses = $wpdb->get_results($query);
    }

    if (empty($courses)) {
        echo '<div class="no-courses-found">Aucune formation ne correspond à vos critères.</div>';
        wp_die();
    }

    foreach ($courses as $course) : 
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
        <?php
    endforeach;

    wp_die();
}
add_action('wp_ajax_filter_courses', 'studies_filter_courses_handler');
add_action('wp_ajax_nopriv_filter_courses', 'studies_filter_courses_handler');
