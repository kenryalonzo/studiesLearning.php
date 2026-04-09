<?php
/**
 * studies-learning Theme Customizer
 *
 * @package studies-learning
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function studies_learning_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'studies_learning_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'studies_learning_customize_partial_blogdescription',
			)
		);
	}

	// Add Hero Section
	$wp_customize->add_section(
		'hero_section',
		array(
			'title'    => esc_html__( 'Studies Learning Hero', 'studies-learning' ),
			'priority' => 30,
		)
	);

	// Manuscript Text
	$wp_customize->add_setting(
		'hero_manuscript',
		array(
			'default'           => 'vous êtes sur',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'hero_manuscript',
		array(
			'label'    => esc_html__( 'Manuscript Text', 'studies-learning' ),
			'section'  => 'hero_section',
			'type'     => 'text',
		)
	);

	// Word 1
	$wp_customize->add_setting(
		'hero_word_1',
		array(
			'default'           => 'studies',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'hero_word_1',
		array(
			'label'    => esc_html__( 'First Title Word', 'studies-learning' ),
			'section'  => 'hero_section',
			'type'     => 'text',
		)
	);

	// Word 2
	$wp_customize->add_setting(
		'hero_word_2',
		array(
			'default'           => 'learning',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'hero_word_2',
		array(
			'label'    => esc_html__( 'Second Title Word', 'studies-learning' ),
			'section'  => 'hero_section',
			'type'     => 'text',
		)
	);

	// Banner Title
	$wp_customize->add_setting(
		'banner_title',
		array(
			'default'           => 'Trouvez la formation idéale avec votre assistant intelligent',
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'banner_title',
		array(
			'label'    => esc_html__( 'Banner Title', 'studies-learning' ),
			'section'  => 'hero_section',
			'type'     => 'textarea',
		)
	);
}
add_action( 'customize_register', 'studies_learning_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function studies_learning_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function studies_learning_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function studies_learning_customize_preview_js() {
	wp_enqueue_script( 'studies-learning-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), _S_VERSION, true );
}
add_action( 'customize_preview_init', 'studies_learning_customize_preview_js' );
