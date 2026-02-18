<?php
/**
 * Template Part : Aucun résultat.
 *
 * @package CozyGaming
 */
?>

<div class="cozy-empty">
    <p class="cozy-empty__icon">🎮</p>
    <h2 class="cozy-empty__title"><?php esc_html_e( 'Rien à afficher ici…', 'cozy-gaming' ); ?></h2>
    <p class="cozy-empty__text">
        <?php esc_html_e( 'Pas encore de contenu disponible. Reviens bientôt, de belles choses se préparent !', 'cozy-gaming' ); ?>
    </p>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cozy-btn cozy-btn--primary">
        <?php esc_html_e( 'Retour à l\'accueil', 'cozy-gaming' ); ?>
    </a>
</div>
