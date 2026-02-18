<?php
/**
 * Template : Erreur 404.
 *
 * @package CozyGaming
 */

get_header(); ?>

<main id="main-content" class="cozy-main">
    <div class="cozy-container cozy-container--narrow">

        <div class="cozy-404">
            <p class="cozy-404__icon">🎮</p>
            <h1 class="cozy-404__title">404</h1>
            <h2 class="cozy-404__subtitle"><?php esc_html_e( 'Oups ! Page introuvable', 'cozy-gaming' ); ?></h2>
            <p class="cozy-404__text">
                <?php esc_html_e( 'On dirait que cette page a pris un warp pipe vers une autre dimension… Pas de panique, tu peux revenir à l\'accueil !', 'cozy-gaming' ); ?>
            </p>
            <div class="cozy-404__actions">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cozy-btn cozy-btn--primary">
                    <?php esc_html_e( 'Retour à l\'accueil', 'cozy-gaming' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" class="cozy-btn cozy-btn--outline">
                    <?php esc_html_e( 'Voir les événements', 'cozy-gaming' ); ?>
                </a>
            </div>
        </div>

    </div>
</main>

<?php get_footer(); ?>
