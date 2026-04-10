<?php
/**
 * Template part for displaying the featured packages section
 *
 * @package studies-learning
 */

$packages = studies_get_featured_packages( 4 );
if ( empty( $packages ) ) {
    return;
}
?>

<section class="studies-featured-packages-section" data-aos="fade-up">
    <div class="eduma-container">
        <div class="eduma-section-header package-header">
            <div class="header-center">
                <p class="eduma-subtitle">Offres exclusives</p>
                <h2 class="eduma-title">Explorez les meilleurs <span style="color: #0047ff;">packages</span></h2>
            </div>
        </div>

        <div class="studies-packages-grid">
            <?php foreach ( $packages as $pkg ) : ?>
                <div class="studies-package-card">
                    <a href="<?php echo esc_url( $pkg['link'] ); ?>" class="package-img-link">
                        <img src="<?php echo esc_url( $pkg['image_url'] ); ?>" alt="<?php echo esc_attr( $pkg['title'] ); ?>" class="package-img" loading="lazy">
                        <?php if ( ! empty( $pkg['category'] ) ) : ?>
                            <span class="package-cat-badge"><?php echo esc_html( $pkg['category'] ); ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <div class="package-content">
                        <h3 class="package-title">
                            <a href="<?php echo esc_url( $pkg['link'] ); ?>"><?php echo esc_html( $pkg['title'] ); ?></a>
                        </h3>
                        <p class="package-excerpt"><?php echo esc_html( $pkg['excerpt'] ); ?></p>
                        
                        <div class="package-footer">
                            <div class="package-price">
                                <?php echo esc_html( $pkg['price_format'] ); ?>
                            </div>
                            <a href="<?php echo esc_url( $pkg['link'] ); ?>" class="package-action-btn">Voir détails</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="packages-section-footer">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'lp_course' ) ); ?>" class="btn-all-packages">
                Découvrir tous les packages &rarr;
            </a>
        </div>
    </div>
</section>
