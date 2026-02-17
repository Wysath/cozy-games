<?php 

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

//Limiter la réservation RSVP aux utilisateurs à 1 seul billet
add_filter( 'tribe_tickets_rsvp_max_tickets', function ( $max_tickets, $event_id, $user_id ) {
    // Vérifie si l'utilisateur est connecté
    if ( is_user_logged_in() ) {
        // Récupère les billets réservés par l'utilisateur pour cet événement
        $reserved_tickets = tribe_tickets_get_user_reserved_tickets( $event_id, $user_id );

        // Si l'utilisateur a déjà réservé un billet, limite à 0 billets supplémentaires
        if ( ! empty( $reserved_tickets ) ) {
            return 0;
        }
    }
    // Sinon, permet de réserver jusqu'à 1 billet
    return 1;
}, 10, 3 );