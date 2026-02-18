<?php
/**
 * Pied de page du thème.
 *
 * @package CozyGaming
 */
?>

<!-- ============================
     FOOTER
     ============================ -->
<footer id="site-footer" class="cozy-footer">
    <div class="cozy-footer__inner cozy-container">

        <!-- Colonnes de widgets -->
        <div class="cozy-footer__widgets">
            <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
                <div class="cozy-footer__col">
                    <?php dynamic_sidebar( 'footer-1' ); ?>
                </div>
            <?php else : ?>
                <div class="cozy-footer__col">
                    <h4 class="cozy-footer-widget__title">🎮 Cozy Gaming</h4>
                    <p>Une communauté bienveillante de passionné·e·s de jeux vidéo cozy.</p>
                </div>
            <?php endif; ?>

            <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
                <div class="cozy-footer__col">
                    <?php dynamic_sidebar( 'footer-2' ); ?>
                </div>
            <?php else : ?>
                <div class="cozy-footer__col">
                    <h4 class="cozy-footer-widget__title">Liens utiles</h4>
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'footer',
                        'menu_class'     => 'cozy-footer__menu',
                        'container'      => false,
                        'fallback_cb'    => false,
                        'depth'          => 1,
                    ] );
                    ?>
                </div>
            <?php endif; ?>

            <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
                <div class="cozy-footer__col">
                    <?php dynamic_sidebar( 'footer-3' ); ?>
                </div>
            <?php else : ?>
                <div class="cozy-footer__col">
                    <h4 class="cozy-footer-widget__title">Rejoins-nous</h4>
                    <p>Discord, Twitch et bien plus…<br>Viens comme tu es ! 🕹️</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Copyright -->
        <div class="cozy-footer__bottom">
            <p>
                &copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>.
                <?php esc_html_e( 'Tous droits réservés.', 'cozy-gaming' ); ?>
                <span class="cozy-footer__credit">
                    Fait avec 💜 par la communauté.
                </span>
            </p>
        </div>

    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
