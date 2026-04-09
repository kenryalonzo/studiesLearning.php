<?php
/**
 * Template part for the Premium Search Banner
 *
 * @package studies-learning
 */
?>

<section class="search-banner-premium">
    <div class="search-banner-container">
        <div class="search-banner-content" data-aos="fade-up">
            <h2>Que souhaitez-vous apprendre aujourd'hui ?</h2>
            
            <div class="search-input-wrapper">
                <i class="ph ph-magnifying-glass search-icon-fixed"></i>
                <input type="text" 
                       id="formation-search-input" 
                       class="search-input-premium" 
                       placeholder="Rechercher une formation (ex: Développement Web, Design...)" 
                       autocomplete="off">
                
                <div class="search-loader"></div>
                
                <!-- Suggestions Dropdown -->
                <div id="search-results-dropdown" class="search-results-dropdown"></div>
            </div>
        </div>
    </div>
</section>
