<?php
/**
 * En-tête du thème.
 *
 * Contient le <head>, la barre de navigation principale
 * et l'ouverture de la balise <body>.
 *
 * @package CozyGaming
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main-content">
    <?php esc_html_e( 'Aller au contenu', 'cozy-gaming' ); ?>
</a>

<!-- ============================
     HEADER
     ============================ -->
<header id="site-header" class="cozy-header">
    <div class="cozy-header__inner cozy-container">

        <!-- Logo / Nom du site -->
        <div class="cozy-header__brand">
            <?php if ( has_custom_logo() ) : ?>
                <div class="cozy-header__logo">
                    <?php the_custom_logo(); ?>
                </div>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cozy-header__site-name" rel="home">
                    <?php bloginfo( 'name' ); ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Navigation principale -->
        <nav id="site-navigation" class="cozy-header__nav" aria-label="<?php esc_attr_e( 'Menu principal', 'cozy-gaming' ); ?>">
            <?php
            wp_nav_menu( [
                'theme_location' => 'primary',
                'menu_class'     => 'cozy-nav__list',
                'container'      => false,
                'fallback_cb'    => false,
                'depth'          => 2,
            ] );
            ?>
        </nav>

        <!-- Actions header (connexion / profil) -->
        <div class="cozy-header__actions">
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>" class="cozy-header__user" title="Mon profil">
                    <?php echo get_avatar( get_current_user_id(), 32 ); ?>
                    <span class="cozy-header__username"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="cozy-btn cozy-btn--sm">
                    <?php esc_html_e( 'Connexion', 'cozy-gaming' ); ?>
                </a>
            <?php endif; ?>

            <!-- Bouton menu mobile -->
            <button id="cozy-menu-toggle" class="cozy-header__menu-toggle" aria-expanded="false" aria-controls="site-navigation" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'cozy-gaming' ); ?>">
                <span class="cozy-hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
        </div>

    </div>
</header>
