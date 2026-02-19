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
            <section class="cozy-home-values" data-cozy-reveal>
                <div class="cozy-home-values__header">
                    <span class="cozy-home-values__label">Code de la guilde</span>
                    <h2 class="cozy-home-values__title">
                        Une guilde fondée sur la <em>bienveillance</em>
                    </h2>
                    <p class="cozy-home-values__subtitle">
                        Chez Cozy Grove, chaque aventurier·ère est le bienvenu, quel que soit son niveau ou son style de jeu.
                    </p>
                </div>
                <div class="cozy-home-values__grid">
                    <div class="cozy-home-values__card" data-cozy-reveal data-cozy-delay="100">
                        <div class="cozy-home-values__card-icon" style="--val-color: var(--cozy-amber);">
                            <i data-lucide="heart"></i>
                        </div>
                        <h3>Bienveillance</h3>
                        <p>Respect, écoute et bonne humeur sont nos maîtres mots. Pas de toxicité ici.</p>
                    </div>
                    <div class="cozy-home-values__card" data-cozy-reveal data-cozy-delay="200">
                        <div class="cozy-home-values__card-icon" style="--val-color: var(--cozy-moss);">
                            <i data-lucide="users"></i>
                        </div>
                        <h3>Inclusivité</h3>
                        <p>Ouvert à tous les profils de joueur·ses, du débutant au compétitif, sans jugement.</p>
                    </div>
                    <div class="cozy-home-values__card" data-cozy-reveal data-cozy-delay="300">
                        <div class="cozy-home-values__card-icon" style="--val-color: var(--cozy-gold);">
                            <i data-lucide="sparkles"></i>
                        </div>
                        <h3>Fun & Cozy</h3>
                        <p>L'objectif c'est de passer un bon moment, en pyjama ou en LAN, toujours détendus.</p>
                    </div>
                    <div class="cozy-home-values__card" data-cozy-reveal data-cozy-delay="400">
                        <div class="cozy-home-values__card-icon" style="--val-color: var(--cozy-ember);">
                            <i data-lucide="shield-check"></i>
                        </div>
                        <h3>Safe Space</h3>
                        <p>Un espace sécurisé avec une charte de bienveillance et des animateurs attentifs.</p>
                    </div>
                </div>
            </section>
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
