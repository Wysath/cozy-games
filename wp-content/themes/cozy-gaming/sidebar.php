<?php
/**
 * Template : Sidebar.
 *
 * @package CozyGaming
 */

if ( ! is_active_sidebar( 'sidebar-main' ) ) {
    return;
}
?>

<aside id="sidebar" class="cozy-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Barre latérale', 'cozy-gaming' ); ?>">
    <?php dynamic_sidebar( 'sidebar-main' ); ?>
</aside>
