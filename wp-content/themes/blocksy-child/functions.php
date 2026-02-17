<?php 

// ============================================================================
// CHARGEMENT DES MODULES COZY GAMING
// ============================================================================

// Module : Profils sociaux (Discord & Twitch)
require_once get_stylesheet_directory() . '/inc/cozy-social-profiles.php';

// Module : Modes de communication (tags visuels événements)
require_once get_stylesheet_directory() . '/inc/cozy-comm-modes.php';

// Module : Codes ami par jeu
require_once get_stylesheet_directory() . '/inc/cozy-friend-codes.php';

// Module : Participants publics (affichage auto sur les single events)
require_once get_stylesheet_directory() . '/inc/cozy-public-attendees.php';

// Module : Personnalisation Login & Inscription
require_once get_stylesheet_directory() . '/inc/cozy-login.php';

// Module : Historique des réservations (shortcode profil)
require_once get_stylesheet_directory() . '/inc/cozy-reservations.php';

// Module : Charte de bienveillance (checkbox RSVP)
require_once get_stylesheet_directory() . '/inc/cozy-charter.php';

// Module : Content Warnings (avertissements de contenu)
require_once get_stylesheet_directory() . '/inc/cozy-content-warnings.php';

// Module : Galerie Setups Gaming (Pinterest masonry)
require_once get_stylesheet_directory() . '/inc/cozy-setups.php';

// Module : Articles Gaming (fiche jeu, notes, verdict)
require_once get_stylesheet_directory() . '/inc/cozy-articles.php';

// ============================================================================
// FIX : Page « Mes Réservations » (/tickets) — Blocksy + TEC Views V2
// ============================================================================
// Le Page Template de TEC V2 crée un « mocked post » de type page, ce qui fait
// échouer le check is_singular('tribe_events') dans intercept_content().
// On réinjecte manuellement le template orders.php quand eventDisplay=tickets.
// ============================================================================
add_filter( 'the_content', 'cozy_fix_tickets_page_content', 8 );
function cozy_fix_tickets_page_content( $content ) {
    // Vérifier qu'on est sur la page /tickets
    $display = get_query_var( 'eventDisplay', false );
    if ( 'tickets' !== $display ) {
        return $content;
    }

    // L'utilisateur doit être connecté
    if ( ! is_user_logged_in() ) {
        return $content;
    }

    // Éviter les boucles infinies
    static $already_running = false;
    if ( $already_running ) {
        return $content;
    }
    $already_running = true;

    // Vérifier que les classes du plugin existent
    if ( ! class_exists( 'Tribe__Tickets__Templates' ) ) {
        $already_running = false;
        return $content;
    }

    // Charger les assets nécessaires
    tribe_asset_enqueue_group( 'tribe-tickets-page-assets' );

    // Charger le template orders.php du plugin
    ob_start();
    include Tribe__Tickets__Templates::get_template_hierarchy( 'tickets/orders.php' );
    $content = ob_get_clean();

    $already_running = false;
    return $content;
}

//chargement de la feuille de style du thème parent et du thème enfant
function cozy_gaming_child_enqueue_styles() {
    wp_enqueue_style( 'blocksy-parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'cozy-gaming-child-style', get_stylesheet_uri(), array('blocksy-parent-style') );
}
add_action( 'wp_enqueue_scripts', 'cozy_gaming_child_enqueue_styles' );

//Ajout d'un role personnalisé
function cozy_add_custom_role(){
    //on vérifie si le rôle existe déjà pour éviter les doublons
    if ( ! get_role ('animateur_cozy')){
        add_role(
            'animateur_cozy',
            'Animateur Cozy',
            array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'publish_posts' => false,
                'upload_files' => true,
                // Permissions spécifiques pour les événements et les organisateurs
                'edit_tribe_events' => true, 
                'publish_tribe_events' => true,
                'delete_tribe_events' => true, 
                'edit_tribe_venues' => true,
                'edit_tribe_organizers' => true, 
            )
        );
    }
}
//le rôle est crée à l'activation du thème enfant
add_action('after_switch_theme', 'cozy_add_custom_role');

//Suppression du rôle personnalisé à la désactivation du thème enfant
function cozy_remove_custom_role(){
    remove_role('animateur_cozy');
}
//le rôle est supprimé à la désactivation du thème enfant
add_action('switch_theme', 'cozy_remove_custom_role');

/**
 * ============================================================================
 * GESTION DES RÉSERVATIONS RSVP - 1 PLACE PAR MEMBRE
 * ============================================================================
 * Chez Cozy Gaming, chaque membre ne peut réserver qu'une seule place
 * pour lui-même. Pas de réservation pour des invités.
 */

// Limiter le nombre maximum de billets qu'un utilisateur peut réserver à 1
add_filter( 'tribe_tickets_get_ticket_max_purchase', function( $max_purchase, $ticket_id ) {
    // Forcer à 1 billet maximum pour tous les RSVP
    return 1;
}, 10, 2 );

// Validation côté serveur : forcer la quantité à 1 avant création du billet
add_filter( 'tribe_tickets_rsvp_attendee_data', function( $attendee_data ) {
    // Forcer la quantité à 1, peu importe ce qui a été envoyé
    if ( isset( $attendee_data['quantity'] ) && $attendee_data['quantity'] > 1 ) {
        $attendee_data['quantity'] = 1;
    }
    return $attendee_data;
}, 10, 1 );

// Intercepter et valider les données RSVP avant traitement
add_filter( 'tribe_tickets_rsvp_tickets_to_generate', function( $tickets_data, $ticket_id, $event_id ) {
    // S'assurer qu'il n'y a qu'un seul ticket généré
    if ( is_array( $tickets_data ) && count( $tickets_data ) > 1 ) {
        $tickets_data = array_slice( $tickets_data, 0, 1 );
    }
    return $tickets_data;
}, 10, 3 );

// Ajouter du CSS pour masquer les boutons + et - si le JS les affiche quand même
add_action( 'wp_head', function() {
    ?>
    <style>
        /* Masquer les contrôles de quantité RSVP */
        .tribe-tickets__rsvp-ar-quantity-input .tribe-tickets__rsvp-ar-quantity-minus,
        .tribe-tickets__rsvp-ar-quantity-input .tribe-tickets__rsvp-ar-quantity-plus {
            display: none !important;
        }
        
        /* Style du message de confirmation */
        .cozy-single-ticket-info {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
        }

        /* Quantité fixe (pas d'input, juste un chiffre) */
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
});