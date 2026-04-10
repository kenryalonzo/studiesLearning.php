<?php
/**
 * Template part for displaying the premium FAQ section ("Les cartes qui respirent")
 *
 * @package studies-learning
 */

$faqs = studies_get_faqs();
if ( empty( $faqs ) ) {
    return;
}
?>

<section class="studies-faq-section" data-aos="fade-up">
    <div class="eduma-container">
        <div class="eduma-section-header faq-header">
            <div class="header-center">
                <p class="eduma-subtitle">On vous répond</p>
                <h2 class="eduma-title">Des questions ? <span style="color: #0047ff;">Consultez notre FAQ</span></h2>
            </div>
        </div>

        <div class="studies-faq-grid">
            <?php foreach ( $faqs as $index => $faq ) : ?>
                <div class="faq-item" data-aos="fade-up" data-aos-delay="<?php echo esc_attr( $index * 100 ); ?>">
                    <button class="faq-question" aria-expanded="false" aria-controls="faq-answer-<?php echo esc_attr( $index ); ?>">
                        <span class="faq-question-text"><?php echo esc_html( $faq['question'] ); ?></span>
                        <span class="faq-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </span>
                    </button>
                    <div class="faq-answer-wrapper" id="faq-answer-<?php echo esc_attr( $index ); ?>">
                        <div class="faq-answer-inner">
                            <div class="faq-answer-content">
                                <?php echo wp_kses_post( $faq['answer'] ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
