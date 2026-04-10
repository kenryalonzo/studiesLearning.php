<?php
/**
 * Template part for the Premium Search Banner – "Explorez & Découvrez"
 * 2-column layout: search prompt on the left, live results on the right.
 *
 * @package studies-learning
 */
?>

<section class="search-banner-premium" aria-label="Recherche de formations">
    <div class="search-banner-container">

        <!-- ── Left column – heading + input ─────────────────── -->
        <div class="search-col-left">
            <p class="search-eyebrow">Explorez · Découvrez · Évoluez</p>
            <h2 class="search-heading">Que souhaitez-vous<br>apprendre <em>aujourd'hui</em>&nbsp;?</h2>

            <div class="search-input-wrapper">
                <i class="ph ph-magnifying-glass search-icon-fixed" aria-hidden="true"></i>
                <input
                    type="text"
                    id="formation-search-input"
                    class="search-input-premium"
                    placeholder="Ex : Développement Web, Design, Python…"
                    autocomplete="off"
                    aria-label="Rechercher une formation"
                    aria-controls="search-results-panel"
                    aria-autocomplete="list"
                >
                <div class="search-loader" aria-hidden="true"></div>
            </div>

            <!-- Hint tags -->
            <div class="search-hint-tags" aria-label="Suggestions populaires">
                <span class="search-hint-label">Populaire :</span>
                <button class="search-hint-tag" type="button">Design UI/UX</button>
                <button class="search-hint-tag" type="button">Python</button>
                <button class="search-hint-tag" type="button">Comptabilité</button>
                <button class="search-hint-tag" type="button">Agriculture</button>
            </div>
        </div>

        <!-- ── Right column – live results panel ─────────────── -->
        <div class="search-col-right">
            <div
                id="search-results-panel"
                class="search-results-panel"
                role="region"
                aria-live="polite"
                aria-label="Résultats de recherche en temps réel"
            >
                <!-- Empty state -->
                <div class="search-empty-state">
                    <div class="search-empty-icon">
                        <i class="ph ph-books" aria-hidden="true"></i>
                    </div>
                    <p class="search-empty-title">Commencez à taper…</p>
                    <p class="search-empty-sub">Vos résultats apparaîtront ici en temps réel.</p>
                </div>
            </div>
        </div>

    </div><!-- /search-banner-container -->
</section>
