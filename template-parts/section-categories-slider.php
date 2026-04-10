<?php
/**
 * Template part for displaying the categories slider
 *
 * @package studies-learning
 */

$categories = studies_get_course_categories();
if ( empty( $categories ) ) {
    return;
}
?>

<section class="studies-categories-section" data-aos="fade-up">
    <div class="eduma-container">
        <div class="eduma-section-header" style="justify-content: center; text-align: center; border-bottom: none; margin-bottom: 2rem;">
            <div class="header-center">
                <p class="eduma-subtitle" style="text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-bottom: 10px;">Choisissez parmi ces options</p>
                <h2 class="eduma-title">Catégories de <span style="color: #0047ff;">cours</span></h2>
            </div>
        </div>

        <div class="studies-cat-slider-container">
            <div class="swiper studies-cat-slider">
                <div class="swiper-wrapper">
                    <?php foreach ( $categories as $cat ) : ?>
                        <div class="swiper-slide">
                            <a href="<?php echo esc_url( $cat['link'] ); ?>" class="studies-cat-card">
                                <div class="cat-img-wrapper">
                                    <img src="<?php echo esc_url( $cat['image_url'] ); ?>" alt="<?php echo esc_attr( $cat['name'] ); ?>" loading="lazy">
                                </div>
                                <div class="cat-info">
                                    <h3 class="cat-title"><?php echo esc_html( $cat['name'] ); ?></h3>
                                    <?php if ( $cat['count'] > 0 ) : ?>
                                        <span class="cat-count"><?php echo esc_html( $cat['count'] ); ?> cours</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Initialize the slider inline to guarantee Swiper settings exactly as requested -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
        new Swiper('.studies-cat-slider', {
            slidesPerView: 'auto',
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 0,
                disableOnInteraction: false
            },
            speed: 3000,
            allowTouchMove: true,
            breakpoints: {
                640: { slidesPerView: 2 },
                1024: { slidesPerView: 4 }
            }
        });
    }
});
</script>
