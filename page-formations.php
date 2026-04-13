<?php
/**
 * Template Name: Formations
 * Template Post Type: page
 *
 * @package studies-learning
 */

get_header();

$paged_from_query = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 0;
$paged_from_page  = get_query_var( 'page' ) ? absint( get_query_var( 'page' ) ) : 0;
$paged            = max( 1, $paged_from_query, $paged_from_page );

$filters      = studies_get_formations_filters_from_request();
$course_query = studies_get_formations_query( $filters, $paged );

$categories = get_terms(
	array(
		'taxonomy'   => 'course_category',
		'hide_empty' => true,
		'number'     => 30,
	)
);
if ( is_wp_error( $categories ) || empty( $categories ) ) {
	$categories = array();
}
$authors     = studies_get_formations_authors();
$levels      = studies_get_course_level_labels();

$current_args = array();
if ( ! empty( $filters['cat'] ) ) {
	$current_args['cat'] = absint( $filters['cat'] );
}
if ( ! empty( $filters['author'] ) ) {
	$current_args['author'] = absint( $filters['author'] );
}
if ( ! empty( $filters['level'] ) ) {
	$current_args['level'] = sanitize_key( $filters['level'] );
}
if ( ! empty( $filters['price'] ) ) {
	$current_args['price'] = sanitize_key( $filters['price'] );
}

$build_filter_url = function( $updates = array(), $remove = array() ) use ( $current_args ) {
	$args = $current_args;

	foreach ( $updates as $key => $value ) {
		$is_empty_string = is_string( $value ) && '' === trim( $value );
		$is_empty_number = is_numeric( $value ) && 0 === (int) $value;

		if ( null === $value || $is_empty_string || $is_empty_number ) {
			unset( $args[ $key ] );
		} else {
			$args[ $key ] = $value;
		}
	}

	if ( ! empty( $remove ) ) {
		foreach ( $remove as $key ) {
			unset( $args[ $key ] );
		}
	}

	unset( $args['paged'] );
	unset( $args['page'] );

	return add_query_arg( $args, get_permalink() );
};

$active_chips = array();
if ( ! empty( $filters['cat'] ) ) {
	$term = get_term( $filters['cat'], 'course_category' );
	if ( $term && ! is_wp_error( $term ) ) {
		$active_chips[] = array(
			'label' => 'Catégorie: ' . $term->name,
			'url'   => $build_filter_url( array(), array( 'cat' ) ),
		);
	}
}
if ( ! empty( $filters['author'] ) ) {
	$author = get_user_by( 'id', $filters['author'] );
	if ( $author ) {
		$active_chips[] = array(
			'label' => 'Auteur: ' . $author->display_name,
			'url'   => $build_filter_url( array(), array( 'author' ) ),
		);
	}
}
if ( ! empty( $filters['level'] ) && isset( $levels[ $filters['level'] ] ) ) {
	$active_chips[] = array(
		'label' => 'Niveau: ' . $levels[ $filters['level'] ],
		'url'   => $build_filter_url( array(), array( 'level' ) ),
	);
}
if ( ! empty( $filters['price'] ) ) {
	$price_label     = 'free' === $filters['price'] ? 'Gratuit' : 'Payant';
	$active_chips[] = array(
		'label' => 'Prix: ' . $price_label,
		'url'   => $build_filter_url( array(), array( 'price' ) ),
	);
}
?>

<main id="primary" class="site-main formations-page">
	<section class="formations-hero">
		<div class="formations-shell">
			<p class="formations-kicker">Catalogue complet</p>
			<h1 class="formations-title">Explorez toutes nos <span>formations</span></h1>
			<p class="formations-intro">Affinez votre recherche avec des filtres intelligents et trouvez le parcours qui correspond à vos objectifs.</p>
		</div>
	</section>

	<section class="formations-content">
		<div class="formations-shell">
			<?php
			$total_found = (int) $course_query->found_posts;
			?>
			<div class="formations-results-meta">
				<p class="formations-count">
					<strong><?php echo esc_html( $total_found ); ?></strong> <?php echo 1 === $total_found ? 'formation trouvée' : 'formations trouvées'; ?>
				</p>
			</div>
			<button class="formations-mobile-filter-toggle" type="button" data-open-filters aria-expanded="false" aria-controls="formations-filters-panel" aria-label="Ouvrir le panneau de filtres">
				<i class="ph ph-sliders-horizontal" aria-hidden="true"></i>
				<span>Filtrer les formations</span>
			</button>

			<div id="formations-filters-panel" class="formations-filters" aria-label="Filtres des formations">
				<div class="formations-filters-head">
					<h2>Filtres</h2>
					<a class="formations-reset-link" href="<?php echo esc_url( $build_filter_url( array(), array( 'cat', 'author', 'level', 'price' ) ) ); ?>">Réinitialiser</a>
				</div>

				<div class="formations-filter-group">
					<h3>Catégories</h3>
					<div class="formations-bubbles formations-bubbles--categories" role="list">
						<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
							<?php $cat_index = 0; ?>
							<?php foreach ( $categories as $cat_term ) : ?>
								<?php
								$is_active = (int) $filters['cat'] === (int) $cat_term->term_id;
								$cat_url   = $is_active ? $build_filter_url( array(), array( 'cat' ) ) : $build_filter_url( array( 'cat' => $cat_term->term_id ) );
								$is_hidden = $cat_index > 9;
								?>
								<a
									class="formations-bubble <?php echo $is_active ? 'is-active' : ''; ?> <?php echo $is_hidden ? 'is-extra' : ''; ?>"
									href="<?php echo esc_url( $cat_url ); ?>"
									<?php echo $is_hidden ? 'data-extra-category="true"' : ''; ?>
								>
									<?php echo esc_html( $cat_term->name ); ?>
								</a>
								<?php $cat_index++; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<?php if ( count( $categories ) > 10 ) : ?>
						<button type="button" class="formations-see-more" data-toggle-categories>
							Voir plus
						</button>
					<?php endif; ?>
				</div>

				<div class="formations-filter-group">
					<h3>Auteurs</h3>
					<div class="formations-authors" role="list">
						<?php foreach ( $authors as $author_item ) : ?>
							<?php
							$author_id  = (int) $author_item->ID;
							$is_active  = (int) $filters['author'] === $author_id;
							$author_url = $is_active ? $build_filter_url( array(), array( 'author' ) ) : $build_filter_url( array( 'author' => $author_id ) );
							?>
							<a class="formations-author <?php echo $is_active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $author_url ); ?>">
								<span class="formations-author-avatar"><?php echo get_avatar( $author_id, 44, '', $author_item->display_name ); ?></span>
								<span class="formations-author-name"><?php echo esc_html( $author_item->display_name ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="formations-filter-row">
					<div class="formations-filter-group">
						<h3>Niveaux</h3>
						<div class="formations-bubbles" role="list">
							<?php foreach ( $levels as $level_key => $level_label ) : ?>
								<?php
								$is_active = $filters['level'] === $level_key;
								$level_url = $is_active ? $build_filter_url( array(), array( 'level' ) ) : $build_filter_url( array( 'level' => $level_key ) );
								?>
								<a class="formations-bubble formations-bubble--level <?php echo $is_active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $level_url ); ?>">
									<?php echo esc_html( $level_label ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="formations-filter-group">
						<h3>Prix</h3>
						<div class="formations-bubbles formations-bubbles--price" role="list">
							<?php
							$is_free_active = 'free' === $filters['price'];
							$is_paid_active = 'paid' === $filters['price'];
							?>
							<a class="formations-bubble formations-bubble--price-tag <?php echo $is_free_active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $is_free_active ? $build_filter_url( array(), array( 'price' ) ) : $build_filter_url( array( 'price' => 'free' ) ) ); ?>">Gratuit</a>
							<a class="formations-bubble formations-bubble--price-tag <?php echo $is_paid_active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $is_paid_active ? $build_filter_url( array(), array( 'price' ) ) : $build_filter_url( array( 'price' => 'paid' ) ) ); ?>">Payant</a>
						</div>
					</div>
				</div>

				<?php if ( ! empty( $active_chips ) ) : ?>
					<div class="formations-active-filters" aria-live="polite">
						<?php foreach ( $active_chips as $chip ) : ?>
							<a class="formations-active-chip" href="<?php echo esc_url( $chip['url'] ); ?>">
								<?php echo esc_html( $chip['label'] ); ?>
								<span aria-hidden="true">×</span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="formations-grid-wrap" data-grid-animate>
				<?php if ( $course_query->have_posts() ) : ?>
					<div class="formations-grid">
						<?php while ( $course_query->have_posts() ) : ?>
							<?php
							$course_query->the_post();
							$post_id    = get_the_ID();
							$thumb_id   = get_post_thumbnail_id( $post_id );
							$price_raw  = get_post_meta( $post_id, '_lp_price', true );
							$level_raw  = sanitize_key( get_post_meta( $post_id, '_lp_level', true ) );
							$duration   = get_post_meta( $post_id, '_lp_duration', true );
							$course_cat = wp_get_post_terms( $post_id, 'course_category', array( 'number' => 1 ) );

							$course_cat_name = '';
							if ( ! is_wp_error( $course_cat ) && ! empty( $course_cat ) ) {
								$course_cat_name = $course_cat[0]->name;
							}

							$level_label = 'Tous niveaux';
							if ( isset( $levels[ $level_raw ] ) ) {
								$level_label = $levels[ $level_raw ];
							} elseif ( false !== strpos( $level_raw, 'begin' ) ) {
								$level_label = $levels['beginner'];
							} elseif ( false !== strpos( $level_raw, 'inter' ) ) {
								$level_label = $levels['intermediate'];
							} elseif ( false !== strpos( $level_raw, 'advan' ) ) {
								$level_label = $levels['advanced'];
							}

							$is_free       = '' === $price_raw || (float) $price_raw <= 0;
							$price_display = $is_free ? 'Gratuit' : number_format_i18n( (float) $price_raw, 0 ) . ' €';
							$excerpt       = get_the_excerpt();
							if ( empty( $excerpt ) ) {
								$excerpt = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 18 );
							}
							?>
							<article <?php post_class( 'formations-card' ); ?>>
								<a class="formations-card-media" href="<?php the_permalink(); ?>">
									<?php if ( $thumb_id ) : ?>
										<?php echo wp_get_attachment_image( $thumb_id, 'medium_large', false, array( 'class' => 'formations-card-image', 'loading' => 'lazy', 'alt' => get_the_title() ) ); ?>
									<?php else : ?>
										<span class="formations-card-fallback"><i class="ph ph-graduation-cap"></i></span>
									<?php endif; ?>
								</a>

								<div class="formations-card-body">
									<div class="formations-card-top">
										<span class="formations-card-category"><?php echo esc_html( $course_cat_name ); ?></span>
										<span class="formations-card-level"><?php echo esc_html( $level_label ); ?></span>
									</div>
									<h3 class="formations-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p class="formations-card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
									<div class="formations-card-footer">
										<span class="formations-card-duration">
											<i class="ph ph-clock"></i>
											<?php echo esc_html( ! empty( $duration ) ? $duration : 'Durée flexible' ); ?>
										</span>
										<span class="formations-card-price <?php echo $is_free ? 'is-free' : ''; ?>"><?php echo esc_html( $price_display ); ?></span>
									</div>
								</div>
							</article>
						<?php endwhile; ?>
					</div>

					<?php
					$pagination = paginate_links(
						array(
							'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
							'format'    => '',
							'current'   => $paged,
							'total'     => (int) $course_query->max_num_pages,
							'type'      => 'list',
							'prev_text' => '←',
							'next_text' => '→',
							'add_args'  => $current_args,
						)
					);
					?>
					<?php if ( $pagination ) : ?>
						<nav class="formations-pagination" aria-label="Pagination des formations">
							<?php echo wp_kses_post( $pagination ); ?>
						</nav>
					<?php endif; ?>
				<?php else : ?>
					<div class="formations-empty">
						<div class="formations-empty-icon"><i class="ph ph-books" aria-hidden="true"></i></div>
						<p>Aucune formation ne correspond aux filtres sélectionnés.</p>
						<a href="<?php echo esc_url( $build_filter_url( array(), array( 'cat', 'author', 'level', 'price' ) ) ); ?>">
							<i class="ph ph-arrow-counter-clockwise" aria-hidden="true"></i> Effacer les filtres
						</a>
					</div>
				<?php endif; ?>
				<?php wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
