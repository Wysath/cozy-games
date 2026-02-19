<?php
function cozy_events_register_cpt() {
    // --- CPT : Événement ---
    register_post_type( 'cozy_event', [
        'labels' => [
            'name'          => 'Événements',
            'singular_name' => 'Événement',
            'add_new_item'  => 'Ajouter un événement',
            'edit_item'     => 'Modifier l\'événement',
            'menu_name'     => 'Cozy Events',
        ],
        'public'        => true,
        'has_archive'   => true,
        'show_in_rest'  => true,
        'supports'      => ['title', 'editor', 'thumbnail'],
        'menu_icon'     => 'dashicons-calendar-alt',
        'rewrite'       => ['slug' => 'events'],
    ]);

    // --- Taxonomy : Jeux (partagée entre événements et articles) ---
    register_taxonomy( 'cozy_game', array( 'cozy_event', 'post' ), [
        'labels' => [
            'name'                       => 'Jeux',
            'singular_name'              => 'Jeu',
            'menu_name'                  => '🎮 Jeux',
            'all_items'                  => 'Tous les jeux',
            'edit_item'                  => 'Modifier le jeu',
            'view_item'                  => 'Voir le jeu',
            'update_item'                => 'Mettre à jour le jeu',
            'add_new_item'               => 'Ajouter un jeu',
            'new_item_name'              => 'Nom du nouveau jeu',
            'search_items'               => 'Rechercher un jeu',
            'popular_items'              => 'Jeux populaires',
            'separate_items_with_commas' => 'Séparer les jeux par des virgules',
            'add_or_remove_items'        => 'Ajouter ou retirer des jeux',
            'choose_from_most_used'      => 'Choisir parmi les jeux les plus utilisés',
            'not_found'                  => 'Aucun jeu trouvé',
            'back_to_items'              => '← Retour aux jeux',
        ],
        'hierarchical'      => false, // Comme les étiquettes (tags) : autocomplete
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'jeu'],
    ]);

    // --- Taxonomy : Type d\'événement ---
    register_taxonomy( 'cozy_event_type', 'cozy_event', [
        'labels' => [
            'name'          => 'Types d\'événement',
            'singular_name' => 'Type',
        ],
        'hierarchical'  => true,
        'public'        => true,
        'show_in_rest'  => true,
        'rewrite'       => ['slug' => 'event-type'],
    ]);
}
add_action( 'init', 'cozy_events_register_cpt' );
