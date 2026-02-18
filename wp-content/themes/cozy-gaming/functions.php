<?php
/**
 * ============================================================================
 * COZY GAMING — functions.php
 * ============================================================================
 *
 * Thème WordPress sur mesure pour l'association Cozy Gaming.
 * Ce fichier configure les supports du thème, enregistre les menus,
 * charge les assets et inclut tous les modules communautaires.
 *
 * @package CozyGaming
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Version du thème (cache-busting)
define( 'COZY_THEME_VERSION', '1.0.0' );


// ============================================================================
// 1. CONFIGURATION DU THÈME (after_setup_theme)
// ============================================================================

function cozy_theme_setup() {

    // Langue
    load_theme_textdomain( 'cozy-gaming', get_template_directory() . '/languages' );

    // Support titre dynamique dans <head>
    add_theme_support( 'title-tag' );

    // Vignettes d'articles (thumbnails)
    add_theme_support( 'post-thumbnails' );

    // Tailles d'images personnalisées
    add_image_size( 'cozy-card', 400, 300, true );       // Cartes événements/articles
    add_image_size( 'cozy-hero', 1200, 500, true );      // Bannière hero
    add_image_size( 'cozy-thumbnail', 150, 150, true );  // Vignettes

    // Logo personnalisé
    add_theme_support( 'custom-logo', [
        'height'      => 80,
        'width'       => 250,
        'flex-height' => true,
        'flex-width'  => true,
    ] );

    // HTML5
    add_theme_support( 'html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ] );

    // Alignement large & pleine largeur Gutenberg
    add_theme_support( 'align-wide' );

    // Responsive embeds
    add_theme_support( 'responsive-embeds' );

    // Style de l'éditeur Gutenberg
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/css/editor-style.css' );

    // Menus de navigation
    register_nav_menus( [
        'primary'   => __( 'Menu principal', 'cozy-gaming' ),
        'footer'    => __( 'Menu pied de page', 'cozy-gaming' ),
        'mobile'    => __( 'Menu mobile', 'cozy-gaming' ),
    ] );
}
add_action( 'after_setup_theme', 'cozy_theme_setup' );


// ============================================================================
// 2. ZONES DE WIDGETS (SIDEBARS)
// ============================================================================

function cozy_register_sidebars() {
    register_sidebar( [
        'name'          => __( 'Barre latérale', 'cozy-gaming' ),
        'id'            => 'sidebar-main',
        'description'   => __( 'Zone de widgets sur les pages avec sidebar.', 'cozy-gaming' ),
        'before_widget' => '<div id="%1$s" class="cozy-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="cozy-widget__title">',
        'after_title'   => '</h3>',
    ] );

    register_sidebar( [
        'name'          => __( 'Pied de page — Colonne 1', 'cozy-gaming' ),
        'id'            => 'footer-1',
        'before_widget' => '<div id="%1$s" class="cozy-footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="cozy-footer-widget__title">',
        'after_title'   => '</h4>',
    ] );

    register_sidebar( [
        'name'          => __( 'Pied de page — Colonne 2', 'cozy-gaming' ),
        'id'            => 'footer-2',
        'before_widget' => '<div id="%1$s" class="cozy-footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="cozy-footer-widget__title">',
        'after_title'   => '</h4>',
    ] );

    register_sidebar( [
        'name'          => __( 'Pied de page — Colonne 3', 'cozy-gaming' ),
        'id'            => 'footer-3',
        'before_widget' => '<div id="%1$s" class="cozy-footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="cozy-footer-widget__title">',
        'after_title'   => '</h4>',
    ] );
}
add_action( 'widgets_init', 'cozy_register_sidebars' );


// ============================================================================
// 3. CHARGEMENT DES ASSETS (CSS + JS)
// ============================================================================

function cozy_enqueue_assets() {
    // --- CSS ---
    wp_enqueue_style(
        'cozy-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [],
        COZY_THEME_VERSION
    );

    wp_enqueue_style(
        'cozy-components',
        get_template_directory_uri() . '/assets/css/components.css',
        [ 'cozy-main' ],
        COZY_THEME_VERSION
    );

    wp_enqueue_style(
        'cozy-header',
        get_template_directory_uri() . '/assets/css/header.css',
        [ 'cozy-main' ],
        COZY_THEME_VERSION
    );

    wp_enqueue_style(
        'cozy-footer',
        get_template_directory_uri() . '/assets/css/footer.css',
        [ 'cozy-main' ],
        COZY_THEME_VERSION
    );

    // --- JS ---
    wp_enqueue_script(
        'cozy-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        COZY_THEME_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_enqueue_assets' );


// ============================================================================
// 4. CHARGEMENT DES MODULES COZY GAMING
// ============================================================================

// Module : Profils sociaux (Discord & Twitch)
require_once get_template_directory() . '/inc/cozy-social-profiles.php';

// Module : Modes de communication (tags visuels événements)
require_once get_template_directory() . '/inc/cozy-comm-modes.php';

// Module : Codes ami par jeu
require_once get_template_directory() . '/inc/cozy-friend-codes.php';

// Module : Participants publics (affichage auto sur les single events)
require_once get_template_directory() . '/inc/cozy-public-attendees.php';

// Module : Personnalisation Login & Inscription
require_once get_template_directory() . '/inc/cozy-login.php';

// Module : Historique des réservations (shortcode profil)
require_once get_template_directory() . '/inc/cozy-reservations.php';

// Module : Charte de bienveillance (checkbox RSVP)
require_once get_template_directory() . '/inc/cozy-charter.php';

// Module : Content Warnings (avertissements de contenu)
require_once get_template_directory() . '/inc/cozy-content-warnings.php';

// Module : Galerie Setups Gaming (Pinterest masonry)
require_once get_template_directory() . '/inc/cozy-setups.php';

// Module : Articles Gaming (fiche jeu, notes, verdict)
require_once get_template_directory() . '/inc/cozy-articles.php';

// Module : Dashboard personnalisé par rôle (wp-admin)
require_once get_template_directory() . '/inc/cozy-dashboard.php';


// ============================================================================
// 5. FIX : Page « Mes Réservations » (/tickets) — TEC Views V2
// ============================================================================

add_filter( 'the_content', 'cozy_fix_tickets_page_content', 8 );
function cozy_fix_tickets_page_content( $content ) {
    $display = get_query_var( 'eventDisplay', false );
    if ( 'tickets' !== $display ) {
        return $content;
    }

    if ( ! is_user_logged_in() ) {
        return $content;
    }

    static $already_running = false;
    if ( $already_running ) {
        return $content;
    }
    $already_running = true;

    if ( ! class_exists( 'Tribe__Tickets__Templates' ) ) {
        $already_running = false;
        return $content;
    }

    tribe_asset_enqueue_group( 'tribe-tickets-page-assets' );

    ob_start();
    include Tribe__Tickets__Templates::get_template_hierarchy( 'tickets/orders.php' );
    $content = ob_get_clean();

    $already_running = false;
    return $content;
}


// ============================================================================
// 6. RÔLE PERSONNALISÉ : ANIMATEUR COZY
// ============================================================================

function cozy_add_custom_role() {
    if ( ! get_role( 'animateur_cozy' ) ) {
        add_role(
            'animateur_cozy',
            'Animateur Cozy',
            [
                'read'                   => true,
                'edit_posts'             => false,
                'delete_posts'           => false,
                'publish_posts'          => false,
                'upload_files'           => true,
                'edit_tribe_events'      => true,
                'publish_tribe_events'   => true,
                'delete_tribe_events'    => true,
                'edit_tribe_venues'      => true,
                'edit_tribe_organizers'  => true,
            ]
        );
    }
}
add_action( 'after_switch_theme', 'cozy_add_custom_role' );

function cozy_remove_custom_role() {
    remove_role( 'animateur_cozy' );
}
add_action( 'switch_theme', 'cozy_remove_custom_role' );


// ============================================================================
// 7. RÉSERVATIONS RSVP — 1 PLACE PAR MEMBRE
// ============================================================================

add_filter( 'tribe_tickets_get_ticket_max_purchase', function( $max_purchase, $ticket_id ) {
    return 1;
}, 10, 2 );

add_filter( 'tribe_tickets_rsvp_attendee_data', function( $attendee_data ) {
    if ( isset( $attendee_data['quantity'] ) && $attendee_data['quantity'] > 1 ) {
        $attendee_data['quantity'] = 1;
    }
    return $attendee_data;
}, 10, 1 );

add_filter( 'tribe_tickets_rsvp_tickets_to_generate', function( $tickets_data, $ticket_id, $event_id ) {
    if ( is_array( $tickets_data ) && count( $tickets_data ) > 1 ) {
        $tickets_data = array_slice( $tickets_data, 0, 1 );
    }
    return $tickets_data;
}, 10, 3 );

// CSS inline pour RSVP
add_action( 'wp_head', function() {
    ?>
    <style>
        .tribe-tickets__rsvp-ar-quantity-input .tribe-tickets__rsvp-ar-quantity-minus,
        .tribe-tickets__rsvp-ar-quantity-input .tribe-tickets__rsvp-ar-quantity-plus {
            display: none !important;
        }
        .cozy-single-ticket-info {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
        }
        .cozy-quantity-fixed {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f0ecf5;
            color: #2c3e50;
            font-weight: 700;
            font-size: 1.1em;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            margin-top: 4px;
        }
    </style>
    <?php
} );


// ============================================================================
// 8. HELPERS DU THÈME
// ============================================================================

/**
 * Retourne l'URL du logo ou un texte par défaut.
 */
function cozy_get_logo_url() {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        return wp_get_attachment_image_url( $custom_logo_id, 'full' );
    }
    return '';
}

/**
 * Affiche l'excerpt avec une longueur personnalisée.
 */
function cozy_excerpt( $length = 25 ) {
    $text = get_the_excerpt();
    $words = explode( ' ', $text );
    if ( count( $words ) > $length ) {
        $words = array_slice( $words, 0, $length );
        $text  = implode( ' ', $words ) . '…';
    }
    echo esc_html( $text );
}

/**
 * Pagination personnalisée.
 */
function cozy_pagination() {
    the_posts_pagination( [
        'mid_size'  => 2,
        'prev_text' => '← ' . __( 'Précédent', 'cozy-gaming' ),
        'next_text' => __( 'Suivant', 'cozy-gaming' ) . ' →',
        'class'     => 'cozy-pagination',
    ] );
}
