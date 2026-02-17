<?php 

// ============================================================================
// CHARGEMENT DES MODULES COZY GAMING
// ============================================================================

// Module : Profils sociaux (Discord & Twitch)
require_once get_stylesheet_directory() . '/inc/cozy-social-profiles.php';

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
    </style>
    <?php
});