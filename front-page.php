<?php
/**
 * The front page template file
 *
 * @package studies-learning
 */

get_header();
?>

<main id="primary" class="site-main">
    <section class="hero-premium">
        <div class="hero-content">
            <span class="manuscript-text-v2"><?php echo esc_html( get_theme_mod( 'hero_manuscript', 'vous êtes sur' ) ); ?></span>
            <h1 class="hero-title-v2">
                <span class="word-studies"><?php echo esc_html( get_theme_mod( 'hero_word_1', 'studies' ) ); ?></span> 
                <span class="word-learning"><?php echo esc_html( get_theme_mod( 'hero_word_2', 'learning' ) ); ?></span>
            </h1>
        </div>

        <div class="modern-banner-container revealed">
            <div class="banner-inner" style="background-image: url('<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/hero/ban_3_bg.png');">
                <video class="split-video" autoplay muted loop playsinline>
                    <source src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/hero/studies_ban_bg.mp4" type="video/mp4" />
                </video>
                <div class="split-overlay"></div>
                
                <div class="banner-grid">
                    <!-- Left Side: Text & CTAs -->
                    <div class="banner-left">
                        <div class="text-glow-blob"></div>
                        <h2 class="banner-main-title" style="margin-bottom: 50px; margin-top: 60px;">
                            <?php 
                            $banner_title = get_theme_mod( 'banner_title', 'Trouvez la <span>formation idéale</span> avec votre assistant intelligent' );
                            echo wp_kses_post( $banner_title );
                            ?>
                        </h2>
                        
                        <div class="banner-actions">
                            <button class="btn-primary-modern">
                                <i class="ph ph-sparkle"></i> Commencer maintenant &rarr;
                            </button>
                        </div>

                        <div class="banner-features-slider-wrapper">
                            <div class="banner-features-mini">
                                <div class="feature-item"><span><i class="ph ph-pencil-line"></i> Pédagogie Innovante</span></div>
                                <div class="feature-item"><span><i class="ph ph-users"></i> +15k Apprenants Actifs</span></div>
                                <div class="feature-item"><span><i class="ph ph-bank"></i> Mentors Experts</span></div>
                                <!-- Duplicate for seamless scroll -->
                                <div class="feature-item"><span><i class="ph ph-pencil-line"></i> Pédagogie Innovante</span></div>
                                <div class="feature-item"><span><i class="ph ph-users"></i> +15k Apprenants Actifs</span></div>
                                <div class="feature-item"><span><i class="ph ph-bank"></i> Mentors Experts</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Center: Robot & Speech Bubble -->
                    <div class="banner-center">
                        <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/robot-assistant.png" alt="Robot Assistant" class="robot-image" />
                        <div class="speech-bubble-modern">
                            Je vous aide à trouver la meilleure formation en quelques clics ✨
                        </div>
                    </div>

                    <!-- Right Side: Path Cards Slider & Social Proof -->
                    <div class="banner-right">
                        <div class="text-glow-blob"></div>
                        <div class="path-cards-container">
                            <h3 class="path-title">Formations à la une</h3>
                            
                            <div class="path-cards-slider" id="pathCardsSlider">
                                <!-- Développement Web -->
                                <div class="path-card">
                                    <div class="path-icon web icon-red"><i class="ph ph-code"></i></div>
                                    <div class="path-info">
                                        <h4>Développement Web</h4>
                                        <p>HTML, CSS, JavaScript</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon web icon-indigo"><i class="ph ph-code"></i></div>
                                    <div class="path-info">
                                        <h4>React & Node.js</h4>
                                        <p>Full Stack Developer</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon web icon-blue"><i class="ph ph-code"></i></div>
                                    <div class="path-info">
                                        <h4>Python</h4>
                                        <p>Backend & Scripts</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>

                                <!-- Data & Intelligence -->
                                <div class="path-card">
                                    <div class="path-icon data icon-red"><i class="ph ph-database"></i></div>
                                    <div class="path-info">
                                        <h4>Data & Intelligence</h4>
                                        <p>Analyse, IA, Data Science</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon data icon-indigo"><i class="ph ph-database"></i></div>
                                    <div class="path-info">
                                        <h4>Machine Learning</h4>
                                        <p>IA & Deep Learning</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon data icon-blue"><i class="ph ph-database"></i></div>
                                    <div class="path-info">
                                        <h4>Power BI</h4>
                                        <p>Visualisation de données</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>

                                <!-- Design & Création -->
                                <div class="path-card">
                                    <div class="path-icon design icon-red"><i class="ph ph-paint-brush-broad"></i></div>
                                    <div class="path-info">
                                        <h4>Design & Création</h4>
                                        <p>UI/UX, Graphisme, Vidéo</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon design icon-indigo"><i class="ph ph-paint-brush-broad"></i></div>
                                    <div class="path-info">
                                        <h4>Figma</h4>
                                        <p>Design d'interfaces</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon design icon-blue"><i class="ph ph-paint-brush-broad"></i></div>
                                    <div class="path-info">
                                        <h4>Motion Design</h4>
                                        <p>Animation & Vidéo</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>

                                <!-- Agriculture -->
                                <div class="path-card">
                                    <div class="path-icon agriculture icon-red"><i class="ph ph-plant"></i></div>
                                    <div class="path-info">
                                        <h4>Agriculture</h4>
                                        <p>Phytotechnie, Agroécologie</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon agriculture icon-indigo"><i class="ph ph-plant"></i></div>
                                    <div class="path-info">
                                        <h4>Maraîichage</h4>
                                        <p>Culture légumière</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon agriculture icon-blue"><i class="ph ph-plant"></i></div>
                                    <div class="path-info">
                                        <h4>Grandes Cultures</h4>
                                        <p>Céréales & Oléagineux</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>

                                <!-- Comptabilité -->
                                <div class="path-card">
                                    <div class="path-icon compta icon-red"><i class="ph ph-calculator"></i></div>
                                    <div class="path-info">
                                        <h4>Comptabilité</h4>
                                        <p>Gestion, Fiscalité</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon compta icon-indigo"><i class="ph ph-calculator"></i></div>
                                    <div class="path-info">
                                        <h4>Gestion Trésorerie</h4>
                                        <p>Planification financière</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon compta icon-blue"><i class="ph ph-calculator"></i></div>
                                    <div class="path-info">
                                        <h4>Paie & RH</h4>
                                        <p>Salaires & Bulletins</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>

                                <!-- Élevage -->
                                <div class="path-card">
                                    <div class="path-icon elevage icon-red"><i class="ph ph-cow"></i></div>
                                    <div class="path-info">
                                        <h4>Élevage</h4>
                                        <p>Bovins, Ovins, Caprins</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon elevage icon-indigo"><i class="ph ph-cow"></i></div>
                                    <div class="path-info">
                                        <h4>Aviculture</h4>
                                        <p>Poules & Pintades</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                                <div class="path-card">
                                    <div class="path-icon elevage icon-blue"><i class="ph ph-cow"></i></div>
                                    <div class="path-info">
                                        <h4>Apiculture</h4>
                                        <p>Élevage d'abeilles</p>
                                    </div>
                                    <i class="ph ph-caret-right"></i>
                                </div>
                            </div>
                        </div>
                        <div class="social-proof-modern">
                            <div class="avatar-group">
                                <img src="https://i.pravatar.cc/150?u=1" alt="user">
                                <img src="https://i.pravatar.cc/150?u=2" alt="user">
                                <img src="https://i.pravatar.cc/150?u=3" alt="user">
                                <img src="https://i.pravatar.cc/150?u=4" alt="user">
                            </div>
                            <div class="proof-text">
                                <span class="stars">★</span> <strong>+15k apprenants</strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 4 Spheres moving across the whole banner -->
                <div class="floating-sphere sphere-1"><i class="ph ph-graduation-cap"></i></div>
                <div class="floating-sphere sphere-2"><i class="ph ph-lightbulb"></i></div>
                <div class="floating-sphere sphere-3"><i class="ph ph-laptop"></i></div>
                <div class="floating-sphere sphere-4"><i class="ph ph-rocket-launch"></i></div>
            </div>
        </div>
    </section>
    <?php get_template_part( 'template-parts/section', 'courses' ); ?>

    <?php get_template_part( 'template-parts/section-search-banner' ); ?>

    <?php get_template_part( 'template-parts/section-categories-slider' ); ?>
</main>

<?php
get_footer();
