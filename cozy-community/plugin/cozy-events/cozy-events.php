<?php
/**
 * Plugin Name: Cozy Events
 * Description: Gestion d'événements pour la guilde Cozy Grove — inscriptions, charte de bienveillance, taxonomies jeux & types.
 * Version: 1.1.0
 * Author: Cozy Grove
 * Text Domain: cozy-events
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'COZY_EVENTS_VERSION', '1.2.0' );
define( 'COZY_EVENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'COZY_EVENTS_URL',  plugin_dir_url( __FILE__ ) );

// ── Modules ──
require_once COZY_EVENTS_PATH . 'includes/cpt.php';
require_once COZY_EVENTS_PATH . 'includes/meta-boxes.php';
require_once COZY_EVENTS_PATH . 'includes/registration.php';
require_once COZY_EVENTS_PATH . 'includes/charter.php';
require_once COZY_EVENTS_PATH . 'includes/shortcodes.php';

// ── Flush rewrite rules au changement de version ──
add_action( 'init', function() {
    $stored = get_option( 'cozy_events_version' );
    if ( $stored !== COZY_EVENTS_VERSION ) {
        flush_rewrite_rules();
        update_option( 'cozy_events_version', COZY_EVENTS_VERSION );
    }
}, 99 );

// ── Assets front-end ──
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'cozy-events',
        COZY_EVENTS_URL . 'assets/style.css',
        [],
        COZY_EVENTS_VERSION
    );
    wp_enqueue_script(
        'cozy-events',
        COZY_EVENTS_URL . 'assets/script.js',
        [ 'jquery' ],
        COZY_EVENTS_VERSION,
        true
    );
    wp_localize_script( 'cozy-events', 'cozyEvents', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'cozy_events_nonce' ),
    ] );
} );

// ── Activation : flush rewrite rules ──
register_activation_hook( __FILE__, function() {
    cozy_events_register_cpt();
    flush_rewrite_rules();
} );

// ── Désactivation : flush rewrite rules ──
register_deactivation_hook( __FILE__, function() {
    flush_rewrite_rules();
} );
