<?php
/**
 * Template : Page d'accueil (front-page.php)
 *
 * Rendu optimisé full-width de la homepage Cozy Grove.
 * Utilise les shortcodes existants dans des sections dédiées
 * avec un layout adapté (hero full-width, sections alternées).
 *
 * @package CozyGaming
 * @since 2.2.0
 */

get_header(); ?>

<main id="main-content" class="cozy-main cozy-main--homepage">

    <?php
    // ─────────────────────────────────────────
    // 1. HERO — Pleine largeur
    // ─────────────────────────────────────────
    ?>
    <div class="cozy-homepage__hero">
        <?php echo do_shortcode( '[cozy_hero]' ); ?>
    </div>

    <?php
    // ─────────────────────────────────────────
    // 2. STATS COMMUNAUTÉ — Bandeau accrocheur
    // ─────────────────────────────────────────
    ?>
    <div class="cozy-homepage__stats">
        <?php echo do_shortcode( '[cozy_community_stats]' ); ?>
    </div>

    <?php
    // ─────────────────────────────────────────
    // 3. PROCHAINS ÉVÉNEMENTS
    // ─────────────────────────────────────────
    ?>
    <div class="cozy-homepage__section cozy-container">
        <?php echo do_shortcode( '[cozy_upcoming_events max="4"]' ); ?>
    </div>

    <?php
    // ─────────────────────────────────────────
    // 4. SÉPARATEUR DÉCORATIF
    // ─────────────────────────────────────────
    ?>
    <div class="cozy-homepage__divider" aria-hidden="true">
        <div class="cozy-homepage__divider-line"></div>
        <span class="cozy-homepage__divider-icon">
            <i data-lucide="gamepad-2"></i>
        </span>
        <div class="cozy-homepage__divider-line"></div>
    </div>

    <?php
    // ─────────────────────────────────────────
    // 5. DERNIERS ARTICLES
    // ─────────────────────────────────────────
    ?>
    <div class="cozy-homepage__section cozy-container">
        <?php echo do_shortcode( '[cozy_latest_posts count="3"]' ); ?>
    </div>

    <?php
    // ─────────────────────────────────────────
    // 6. APERÇU SETUPS GAMING — Galerie compacte
    // ─────────────────────────────────────────
    ?>
    <div class="cozy-homepage__setups-preview">
        <div class="cozy-container">
            <?php echo cozy_homepage_setups_preview(); ?>
        </div>
    </div>

    <?php
    // ─────────────────────────────────────────
    // 7. BANDEAU VALEURS / À PROPOS
    // ─────────────────────────────────────────
    ?>
    <div class="cozy-homepage__values">
        <div class="cozy-container">
            <?php echo do_shortcode( '[cozy_values]' ); ?>
        </div>
    </div>

    <?php
    // ─────────────────────────────────────────
    // 8. BANDEAU CTA — Inscription (visiteurs)
    // ─────────────────────────────────────────
    ?>
    <div class="cozy-homepage__cta cozy-container">
        <?php echo do_shortcode( '[cozy_join_cta]' ); ?>
    </div>

</main>

<?php get_footer(); ?>
