<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package studies-learning
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'studies-learning' ); ?></a>

	<header id="masthead" class="site-header">
		<nav class="navbar-premium">
			<div class="logo">
				<?php
				if ( has_custom_logo() ) {
					the_custom_logo();
				} else {
					?>
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/logo.png" alt="logo" class="logo-img">
					<span class="logo-text">Studies <span class="highlight">Learning</span></span>
					<?php
				}
				?>
			</div>
			
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-1',
					'menu_id'        => 'primary-menu',
					'container'      => false,
					'menu_class'     => 'nav-links',
				)
			);
			?>

			<div class="nav-actions">
				<a href="#" class="btn-campus">Campus</a>
			</div>
		</nav>
	</header><!-- #masthead -->
