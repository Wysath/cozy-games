<?php
/**
 * ============================================================================
 * COZY GAMING — functions.php
 * ============================================================================
 *
 * Thème WordPress sur mesure pour la guilde Cozy Grove.
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

    // --- Google Fonts (Space Grotesk + DM Serif Display + Nunito) ---
    wp_enqueue_style(
        'cozy-google-fonts',
        'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Serif+Display:ital@0;1&family=Nunito:wght@400;500;600;700;800&display=swap',
        [],
        null
    );

    // --- Lucide Icons ---
    wp_enqueue_script(
        'lucide-icons',
        'https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js',
        [],
        null,
        true
    );

    // --- CSS ---
    wp_enqueue_style(
        'cozy-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [ 'cozy-google-fonts' ],
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

// Charger les templates du plugin Cozy Events
add_filter( 'template_include', function( $template ) {
    if ( is_singular('cozy_event') ) {
        $plugin_tpl = defined('COZY_EVENTS_PATH') ? COZY_EVENTS_PATH . 'templates/single-event.php' : '';
        if ( $plugin_tpl && file_exists($plugin_tpl) ) return $plugin_tpl;
    }
    if ( is_post_type_archive('cozy_event') ) {
        $plugin_tpl = defined('COZY_EVENTS_PATH') ? COZY_EVENTS_PATH . 'templates/archive-event.php' : '';
        if ( $plugin_tpl && file_exists($plugin_tpl) ) return $plugin_tpl;
    }
    return $template;
});

// 3. Tes personnalisations CSS/JS supplémentaires ici
add_action( 'wp_enqueue_scripts', 'cozy_enqueue_assets' );


// ============================================================================
// 4. CHARGEMENT DES MODULES COZY GAMING
// ============================================================================

// Module : Profils sociaux (Discord & Twitch)
require_once get_template_directory() . '/inc/cozy-social-profiles.php';

// Module : Codes ami par jeu
require_once get_template_directory() . '/inc/cozy-friend-codes.php';

// Module : Personnalisation Login & Inscription
require_once get_template_directory() . '/inc/cozy-login.php';

// Module : Galerie Setups Gaming (Pinterest masonry)
require_once get_template_directory() . '/inc/cozy-setups.php';

// Module : Articles Gaming (fiche jeu, notes, verdict)
require_once get_template_directory() . '/inc/cozy-articles.php';

// Module : Collection de Jeux (shortcode [cozy_game_collection])
require_once get_template_directory() . '/inc/cozy-game-collection.php';

// Module : Homepage shortcodes (hero, events, articles, stats, CTA)
require_once get_template_directory() . '/inc/cozy-homepage.php';

// Module : Widgets Dashboard personnalisés par rôle
require_once get_template_directory() . '/inc/cozy-dashboard-widgets.php';


// ============================================================================
// 5. RÔLE PERSONNALISÉ : ANIMATEUR COZY
// ============================================================================

function cozy_add_custom_role() {
    if ( ! get_role( 'animateur_cozy' ) ) {
        add_role(
            'animateur_cozy',
            'Animateur Cozy',
            [
                'read'         => true,
                'edit_posts'   => false,
                'delete_posts' => false,
                'publish_posts'=> false,
                'upload_files' => true,
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
// 6. HELPERS DU THÈME
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