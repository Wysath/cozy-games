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

// Module : Archive Articles avec filtres (shortcode [cozy_articles_archive])
require_once get_template_directory() . '/inc/cozy-articles-archive.php';

// Module : Page contact 
require_once get_template_directory() . '/inc/cozy-contact.php';


// ============================================================================
// 5. DÉSACTIVATION DES TAXONOMIES NATIVES (category & post_tag)
// ============================================================================
// Les articles utilisent cozy_article_type et cozy_game comme taxonomies.
// Les catégories et étiquettes natives de WordPress sont redondantes.

function cozy_unregister_native_taxonomies() {
    unregister_taxonomy_for_object_type( 'category', 'post' );
    unregister_taxonomy_for_object_type( 'post_tag', 'post' );
}
add_action( 'init', 'cozy_unregister_native_taxonomies', 99 );

/**
 * Masque les menus admin résiduels des taxonomies natives.
 */
function cozy_hide_native_taxonomy_menus() {
    remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=category' );
    remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
}
add_action( 'admin_menu', 'cozy_hide_native_taxonomy_menus' );


// ============================================================================
// 6. RÔLES PERSONNALISÉS
// ============================================================================

/**
 * Enregistre les rôles personnalisés :
 *   - animateur_cozy     : gestion des événements uniquement
 *   - gestionnaire_setups : gestion des setups uniquement
 */
function cozy_add_custom_roles() {
    // ── Animateur Cozy — accès événements uniquement ──
    if ( ! get_role( 'animateur_cozy' ) ) {
        add_role(
            'animateur_cozy',
            'Animateur Cozy',
            [
                'read'                    => true,
                'upload_files'            => true,

                // Événements (cozy_event)
                'edit_cozy_events'        => true,
                'edit_others_cozy_events' => true,
                'publish_cozy_events'     => true,
                'delete_cozy_events'      => true,
                'delete_others_cozy_events' => true,
                'edit_published_cozy_events' => true,
                'delete_published_cozy_events' => true,

                // Taxonomies événements (cozy_game, cozy_event_type)
                'manage_cozy_game'        => true,
                'edit_cozy_game'          => true,
                'delete_cozy_game'        => true,
                'assign_cozy_game'        => true,
                'manage_cozy_event_type'  => true,
                'edit_cozy_event_type'    => true,
                'delete_cozy_event_type'  => true,
                'assign_cozy_event_type'  => true,
            ]
        );
    }

    // ── Gestionnaire Setups — accès setups uniquement ──
    if ( ! get_role( 'gestionnaire_setups' ) ) {
        add_role(
            'gestionnaire_setups',
            'Gestionnaire Setups',
            [
                'read'                     => true,
                'upload_files'             => true,

                // Setups (cozy_setup)
                'edit_cozy_setups'         => true,
                'edit_others_cozy_setups'  => true,
                'publish_cozy_setups'      => true,
                'delete_cozy_setups'       => true,
                'delete_others_cozy_setups' => true,
                'edit_published_cozy_setups' => true,
                'delete_published_cozy_setups' => true,
            ]
        );
    }
}
add_action( 'after_switch_theme', 'cozy_add_custom_roles' );

/**
 * Force la mise à jour des rôles même sans changer de thème.
 * Compare un numéro de version et recrée les rôles si nécessaire.
 */
function cozy_maybe_update_roles() {
    $current_version = '2.0';
    if ( get_option( 'cozy_roles_version' ) !== $current_version ) {
        // Supprimer les anciennes versions pour recréer proprement
        remove_role( 'animateur_cozy' );
        remove_role( 'gestionnaire_setups' );
        cozy_add_custom_roles();

        // Accorder les capabilities custom à l'admin
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            // Événements
            foreach ( [ 'edit_cozy_event', 'read_cozy_event', 'delete_cozy_event',
                        'edit_cozy_events', 'edit_others_cozy_events', 'publish_cozy_events',
                        'read_private_cozy_events', 'delete_cozy_events',
                        'delete_private_cozy_events', 'delete_published_cozy_events',
                        'delete_others_cozy_events', 'edit_private_cozy_events',
                        'edit_published_cozy_events' ] as $cap ) {
                $admin->add_cap( $cap );
            }
            // Taxonomies événements
            foreach ( [ 'manage_cozy_game', 'edit_cozy_game', 'delete_cozy_game', 'assign_cozy_game',
                        'manage_cozy_event_type', 'edit_cozy_event_type',
                        'delete_cozy_event_type', 'assign_cozy_event_type' ] as $cap ) {
                $admin->add_cap( $cap );
            }
            // Setups
            foreach ( [ 'edit_cozy_setup', 'read_cozy_setup', 'delete_cozy_setup',
                        'edit_cozy_setups', 'edit_others_cozy_setups', 'publish_cozy_setups',
                        'read_private_cozy_setups', 'delete_cozy_setups',
                        'delete_private_cozy_setups', 'delete_published_cozy_setups',
                        'delete_others_cozy_setups', 'edit_private_cozy_setups',
                        'edit_published_cozy_setups' ] as $cap ) {
                $admin->add_cap( $cap );
            }
        }

        update_option( 'cozy_roles_version', $current_version );
    }
}
add_action( 'admin_init', 'cozy_maybe_update_roles' );

function cozy_remove_custom_roles() {
    remove_role( 'animateur_cozy' );
    remove_role( 'gestionnaire_setups' );
}
add_action( 'switch_theme', 'cozy_remove_custom_roles' );


/**
 * Restreint le menu admin selon le rôle.
 * - Animateur Cozy : ne voit que les événements
 * - Gestionnaire Setups : ne voit que les setups
 */
function cozy_restrict_admin_menus() {
    $user = wp_get_current_user();

    if ( in_array( 'animateur_cozy', $user->roles, true ) ) {
        // Supprimer tout sauf Événements, Médias et Profil
        remove_menu_page( 'index.php' );              // Tableau de bord
        remove_menu_page( 'edit.php' );                // Articles
        remove_menu_page( 'edit.php?post_type=page' ); // Pages
        remove_menu_page( 'edit-comments.php' );       // Commentaires
        remove_menu_page( 'themes.php' );              // Apparence
        remove_menu_page( 'plugins.php' );             // Extensions
        remove_menu_page( 'users.php' );               // Utilisateurs
        remove_menu_page( 'tools.php' );               // Outils
        remove_menu_page( 'options-general.php' );     // Réglages
        remove_menu_page( 'edit.php?post_type=cozy_setup' ); // Setups
    }

    if ( in_array( 'gestionnaire_setups', $user->roles, true ) ) {
        // Supprimer tout sauf Setups, Médias et Profil
        remove_menu_page( 'index.php' );              // Tableau de bord
        remove_menu_page( 'edit.php' );                // Articles
        remove_menu_page( 'edit.php?post_type=page' ); // Pages
        remove_menu_page( 'edit-comments.php' );       // Commentaires
        remove_menu_page( 'themes.php' );              // Apparence
        remove_menu_page( 'plugins.php' );             // Extensions
        remove_menu_page( 'users.php' );               // Utilisateurs
        remove_menu_page( 'tools.php' );               // Outils
        remove_menu_page( 'options-general.php' );     // Réglages
        remove_menu_page( 'edit.php?post_type=cozy_event' ); // Événements
    }
}
add_action( 'admin_menu', 'cozy_restrict_admin_menus', 999 );

/**
 * Redirige les rôles restreints vers leur page admin par défaut.
 */
function cozy_redirect_admin_dashboard() {
    if ( ! is_admin() ) {
        return;
    }

    global $pagenow;
    $user = wp_get_current_user();

    // Rediriger le tableau de bord vers la bonne section
    if ( $pagenow === 'index.php' ) {
        if ( in_array( 'animateur_cozy', $user->roles, true ) ) {
            wp_redirect( admin_url( 'edit.php?post_type=cozy_event' ) );
            exit;
        }
        if ( in_array( 'gestionnaire_setups', $user->roles, true ) ) {
            wp_redirect( admin_url( 'edit.php?post_type=cozy_setup' ) );
            exit;
        }
    }
}
add_action( 'admin_init', 'cozy_redirect_admin_dashboard' );


// ============================================================================
// 7. HELPERS DU THÈME
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


// ============================================================================
// 8. SÉCURITÉ — DURCISSEMENT DU THÈME
// ============================================================================

/**
 * Désactive l'énumération des utilisateurs.
 * Empêche /?author=1 de révéler les logins.
 */
add_action( 'template_redirect', function() {
    if ( is_author() && ! is_user_logged_in() ) {
        wp_redirect( home_url(), 301 );
        exit;
    }
});

/**
 * Bloque l'accès aux utilisateurs via l'API REST pour les non-connectés.
 */
add_filter( 'rest_endpoints', function( $endpoints ) {
    if ( ! is_user_logged_in() ) {
        if ( isset( $endpoints['/wp/v2/users'] ) ) {
            unset( $endpoints['/wp/v2/users'] );
        }
        if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
            unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
        }
    }
    return $endpoints;
});

/**
 * Masque la version de WordPress dans le <head>.
 */
remove_action( 'wp_head', 'wp_generator' );

/**
 * En-têtes HTTP de sécurité.
 */
add_action( 'send_headers', function() {
    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'X-XSS-Protection: 1; mode=block' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
});

/**
 * Limite les types de fichiers uploadables (supprime les dangereux).
 */
add_filter( 'upload_mimes', function( $mimes ) {
    unset( $mimes['exe'] );
    unset( $mimes['swf'] );
    unset( $mimes['php'] );
    unset( $mimes['phtml'] );
    unset( $mimes['php3'] );
    unset( $mimes['php4'] );
    unset( $mimes['php5'] );
    unset( $mimes['phps'] );
    return $mimes;
});