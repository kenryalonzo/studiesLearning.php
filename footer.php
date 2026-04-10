<?php
/**
 * The template for displaying the footer — "Nuage Ancré"
 * Premium glassmorphism footer for Studies Learning.
 *
 * @package studies-learning
 */
?>

	<footer id="colophon" class="site-footer" role="contentinfo">

		<!-- ── Upper glass panel ─────────────────────────────── -->
		<div class="footer-glass">
			<div class="footer-inner">

				<!-- Column A – Brand -->
				<div class="footer-brand">

					<?php if ( has_custom_logo() ) : ?>
					<div class="footer-logo-wrap">
						<?php the_custom_logo(); ?>
					</div>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-logo-wrap" aria-label="<?php bloginfo( 'name' ); ?> – Accueil">
						<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/logo.png" alt="Studies Learning" class="footer-logo-img">
						<span class="footer-site-name">Studies <span>Learning</span></span>
					</a>
				<?php endif; ?>

					<p class="footer-tagline">Apprendre sans limites.</p>

					<p class="footer-description">
						La plateforme de formations qui accompagne chaque apprenant vers son plein potentiel, à son propre rythme.
					</p>
				</div>

				<!-- Column B – Navigation -->
				<nav class="footer-nav" aria-label="Menu secondaire du footer">
					<p class="footer-nav-heading">Navigation</p>

					<?php
					if ( has_nav_menu( 'footer' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'footer',
							'menu_class'     => 'footer-nav-list',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => false,
						) );
					} else {
						// Hardcoded fallback
						$footer_links = array(
							'À propos'          => home_url( '/a-propos' ),
							'Formations'        => home_url( '/formations' ),
							'Devenir formateur' => home_url( '/devenir-formateur' ),
							'SL University'     => home_url( '/sl-university' ),
							'SL Business'       => home_url( '/sl-business' ),
							'Blog'              => home_url( '/blog' ),
							'Contact'           => home_url( '/contact' ),
						);
						echo '<ul class="footer-nav-list">';
						foreach ( $footer_links as $label => $url ) {
							printf(
								'<li><a href="%s">%s</a></li>',
								esc_url( $url ),
								esc_html( $label )
							);
						}
						echo '</ul>';
					}
					?>
				</nav>

				<!-- Column C – Newsletter + Social -->
				<div class="footer-right">

					<!-- Newsletter -->
					<div class="footer-newsletter">
						<span class="footer-newsletter-label">Restez inspiré</span>
						<form class="footer-newsletter-form" novalidate>
							<input
								type="email"
								class="footer-newsletter-input"
								placeholder="votre@email.com"
								aria-label="Votre adresse e-mail"
							/>
							<button
								type="submit"
								class="footer-newsletter-btn"
								aria-label="S'abonner à la newsletter"
							>
								<i class="ph ph-arrow-right" aria-hidden="true"></i>
							</button>
						</form>
						<p class="footer-newsletter-success" aria-live="polite">
							✓ &nbsp;Merci ! Vous êtes inscrit(e).
						</p>
					</div>

					<!-- Social icons -->
					<div class="footer-social">
						<span class="footer-social-heading">Suivez-nous</span>
						<div class="footer-social-icons">

							<a
								href="https://www.linkedin.com/company/studies-learning"
								class="footer-social-link"
								aria-label="Studies Learning sur LinkedIn"
								target="_blank"
								rel="noopener noreferrer"
							>
								<i class="ph ph-linkedin-logo" aria-hidden="true"></i>
							</a>

							<a
								href="https://twitter.com/studieslearning"
								class="footer-social-link"
								aria-label="Studies Learning sur X (Twitter)"
								target="_blank"
								rel="noopener noreferrer"
							>
								<i class="ph ph-x-logo" aria-hidden="true"></i>
							</a>

							<a
								href="https://www.instagram.com/studieslearning"
								class="footer-social-link"
								aria-label="Studies Learning sur Instagram"
								target="_blank"
								rel="noopener noreferrer"
							>
								<i class="ph ph-instagram-logo" aria-hidden="true"></i>
							</a>

							<a
								href="https://www.youtube.com/@studieslearning"
								class="footer-social-link"
								aria-label="Studies Learning sur YouTube"
								target="_blank"
								rel="noopener noreferrer"
							>
								<i class="ph ph-youtube-logo" aria-hidden="true"></i>
							</a>

						</div>
					</div>

				</div>
				<!-- /Column C -->

			</div><!-- /footer-inner -->
		</div><!-- /footer-glass -->

		<!-- ── Bottom legal bar ──────────────────────────────── -->
		<div class="footer-bottom">
			<div class="footer-bottom-inner">

				<div class="footer-legal-links" aria-label="Liens légaux">
					<a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>">Mentions légales</a>
					<span class="footer-legal-sep" aria-hidden="true">·</span>
					<a href="<?php echo esc_url( home_url( '/politique-de-confidentialite' ) ); ?>">Politique de confidentialité</a>
					<span class="footer-legal-sep" aria-hidden="true">·</span>
					<a href="<?php echo esc_url( home_url( '/cgv' ) ); ?>">CGV</a>
				</div>

				<p class="footer-copyright">
					&copy; <?php echo esc_html( date( 'Y' ) ); ?> Studies Learning. Tous droits réservés.
				</p>

			</div>
		</div><!-- /footer-bottom -->

	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
