<?php
/**
 * ============================================================================
 * MODULE : Modes de Communication (Tags visuels événements)
 * ============================================================================
 *
 * Ajoute une taxonomie personnalisée "Mode de communication" aux événements
 * pour indiquer si l'événement est en vocal, texte ou écoute libre.
 * Les tags sont affichés visuellement sur chaque événement avec des icônes.
 *
 * @package CozyGaming
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * -----------------------------------------------
 * 1. ENREGISTREMENT DE LA TAXONOMIE
 * -----------------------------------------------
 */

/**
 * Enregistre la taxonomie "Mode de communication" pour les événements
 */
function cozy_register_comm_mode_taxonomy() {
    $labels = array(
        'name'                       => 'Modes de communication',
        'singular_name'              => 'Mode de communication',
        'menu_name'                  => 'Mode de comm.',
        'all_items'                  => 'Tous les modes',
        'edit_item'                  => 'Modifier le mode',
        'view_item'                  => 'Voir le mode',
        'update_item'                => 'Mettre à jour le mode',
        'add_new_item'               => 'Ajouter un mode',
        'new_item_name'              => 'Nom du nouveau mode',
        'search_items'               => 'Rechercher un mode',
        'not_found'                  => 'Aucun mode trouvé',
        'no_terms'                   => 'Aucun mode',
        'items_list'                 => 'Liste des modes',
        'back_to_items'              => '← Retour aux modes',
    );

    $args = array(
        'labels'            => $labels,
        'public'            => true,
        'hierarchical'      => false, // Comme des tags, pas des catégories
        'show_in_rest'      => true,  // Compatible Gutenberg
        'show_admin_column' => true,  // Colonne visible dans la liste des événements
        'show_ui'           => true,
        'rewrite'           => array( 'slug' => 'mode-communication' ),
    );

    // Rattacher aux événements The Events Calendar
    register_taxonomy( 'cozy_comm_mode', 'tribe_events', $args );
}
add_action( 'init', 'cozy_register_comm_mode_taxonomy' );


/**
 * -----------------------------------------------
 * 2. TERMES PAR DÉFAUT
 * -----------------------------------------------
 * Insère les modes prédéfinis à l'activation du thème
 */

/**
 * Crée les termes par défaut de la taxonomie
 */
function cozy_insert_default_comm_modes() {
    // S'assurer que la taxonomie existe
    if ( ! taxonomy_exists( 'cozy_comm_mode' ) ) {
        cozy_register_comm_mode_taxonomy();
    }

    $default_modes = array(
        'vocal'        => array(
            'name'        => '🎙️ Vocal',
            'slug'        => 'vocal',
            'description' => 'Événement avec communication vocale — micro requis.',
        ),
        'texte'        => array(
            'name'        => '⌨️ Texte uniquement',
            'slug'        => 'texte',
            'description' => 'Événement avec communication par tchat écrit uniquement.',
        ),
        'ecoute-libre' => array(
            'name'        => '🎧 Écoute libre',
            'slug'        => 'ecoute-libre',
            'description' => 'Tu peux écouter le vocal sans micro — aucune obligation de parler.',
        ),
    );

    foreach ( $default_modes as $mode ) {
        if ( ! term_exists( $mode['slug'], 'cozy_comm_mode' ) ) {
            wp_insert_term(
                $mode['name'],
                'cozy_comm_mode',
                array(
                    'slug'        => $mode['slug'],
                    'description' => $mode['description'],
                )
            );
        }
    }
}
add_action( 'after_switch_theme', 'cozy_insert_default_comm_modes' );

// Aussi au chargement de l'admin pour la première fois (au cas où le thème est déjà actif)
add_action( 'admin_init', function() {
    if ( get_option( 'cozy_comm_modes_initialized' ) ) {
        return;
    }
    cozy_insert_default_comm_modes();
    update_option( 'cozy_comm_modes_initialized', true );
} );


/**
 * -----------------------------------------------
 * 3. CONFIGURATION DES ICÔNES ET COULEURS
 * -----------------------------------------------
 * Chaque mode a une icône, une couleur de fond et une couleur de texte
 */

/**
 * Retourne la configuration visuelle d'un mode de communication
 *
 * @param string $slug Le slug du terme
 * @return array La configuration (icône, couleur bg, couleur texte)
 */
function cozy_get_comm_mode_config( $slug ) {
    $configs = array(
        'vocal' => array(
            'icon'     => '🎙️',
            'bg'       => '#fff0f0',
            'color'    => '#c0392b',
            'border'   => '#f5c6cb',
            'label'    => 'Vocal',
        ),
        'texte' => array(
            'icon'     => '⌨️',
            'bg'       => '#e8f4fd',
            'color'    => '#2980b9',
            'border'   => '#bee5eb',
            'label'    => 'Texte uniquement',
        ),
        'ecoute-libre' => array(
            'icon'     => '🎧',
            'bg'       => '#f0f0fe',
            'color'    => '#6c5ce7',
            'border'   => '#d8dafe',
            'label'    => 'Écoute libre',
        ),
    );

    return isset( $configs[ $slug ] ) ? $configs[ $slug ] : array(
        'icon'   => '💬',
        'bg'     => '#f5f5f5',
        'color'  => '#666',
        'border' => '#ddd',
        'label'  => '',
    );
}


/**
 * -----------------------------------------------
 * 4. AFFICHAGE SUR LES PAGES ÉVÉNEMENTS (SINGLE)
 * -----------------------------------------------
 * Affiche les badges de mode de comm. sur la page individuelle
 * de l'événement, juste avant le contenu.
 */

/**
 * Affiche les badges de mode de comm. sur la page single d'un événement
 */
function cozy_display_comm_mode_single() {
    $event_id = get_the_ID();

    if ( ! $event_id || get_post_type( $event_id ) !== 'tribe_events' ) {
        return;
    }

    $modes = wp_get_post_terms( $event_id, 'cozy_comm_mode' );

    if ( is_wp_error( $modes ) || empty( $modes ) ) {
        return;
    }

    echo '<div class="cozy-comm-modes cozy-comm-modes--single">';
    foreach ( $modes as $mode ) {
        $config = cozy_get_comm_mode_config( $mode->slug );
        printf(
            '<span class="cozy-comm-badge" style="background:%s; color:%s; border:1px solid %s;" title="%s">%s %s</span>',
            esc_attr( $config['bg'] ),
            esc_attr( $config['color'] ),
            esc_attr( $config['border'] ),
            esc_attr( $mode->description ),
            $config['icon'],
            esc_html( $config['label'] ?: $mode->name )
        );
    }
    echo '</div>';
}
add_action( 'tribe_events_single_event_before_the_content', 'cozy_display_comm_mode_single', 5 );


/**
 * -----------------------------------------------
 * 5. AFFICHAGE SUR LES LISTES D'ÉVÉNEMENTS (ARCHIVES)
 * -----------------------------------------------
 * Affiche un petit badge sur les miniatures dans les vues
 * liste, calendrier et jour.
 */

/**
 * Affiche les badges sur les listes d'événements (v2)
 *
 * @param int $event_id L'ID de l'événement
 */
function cozy_display_comm_mode_list( $event_id = null ) {
    if ( ! $event_id ) {
        $event_id = get_the_ID();
    }

    if ( ! $event_id ) {
        return;
    }

    $modes = wp_get_post_terms( $event_id, 'cozy_comm_mode' );

    if ( is_wp_error( $modes ) || empty( $modes ) ) {
        return;
    }

    echo '<div class="cozy-comm-modes cozy-comm-modes--list">';
    foreach ( $modes as $mode ) {
        $config = cozy_get_comm_mode_config( $mode->slug );
        printf(
            '<span class="cozy-comm-badge cozy-comm-badge--compact" style="background:%s; color:%s; border:1px solid %s;" title="%s">%s</span>',
            esc_attr( $config['bg'] ),
            esc_attr( $config['color'] ),
            esc_attr( $config['border'] ),
            esc_attr( $config['label'] ?: $mode->name ),
            $config['icon']
        );
    }
    echo '</div>';
}

// Hook sur les vues liste/jour/mois de TEC v2
add_action( 'tribe_template_after_include:events/v2/list/event/title', function( $file, $name, $template ) {
    $event_id = $template->get( 'event' )->ID ?? get_the_ID();
    cozy_display_comm_mode_list( $event_id );
}, 10, 3 );

add_action( 'tribe_template_after_include:events/v2/day/event/title', function( $file, $name, $template ) {
    $event_id = $template->get( 'event' )->ID ?? get_the_ID();
    cozy_display_comm_mode_list( $event_id );
}, 10, 3 );

// Hook aussi sur le contenu pour les thèmes qui n'utilisent pas les hooks template
add_filter( 'tribe_events_event_schedule_details', function( $schedule, $event_id ) {
    // Ne pas ajouter en double si déjà affiché via template hooks
    if ( did_action( 'tribe_template_after_include:events/v2/list/event/title' ) ) {
        return $schedule;
    }
    
    $modes = wp_get_post_terms( $event_id, 'cozy_comm_mode' );
    if ( is_wp_error( $modes ) || empty( $modes ) ) {
        return $schedule;
    }

    $badges = '';
    foreach ( $modes as $mode ) {
        $config = cozy_get_comm_mode_config( $mode->slug );
        $badges .= sprintf(
            '<span class="cozy-comm-badge cozy-comm-badge--inline" style="background:%s; color:%s; border:1px solid %s;">%s</span>',
            esc_attr( $config['bg'] ),
            esc_attr( $config['color'] ),
            esc_attr( $config['border'] ),
            $config['icon']
        );
    }

    return $schedule . ' ' . $badges;
}, 10, 2 );


/**
 * -----------------------------------------------
 * 6. ENQUEUE DES STYLES
 * -----------------------------------------------
 */

function cozy_comm_mode_enqueue_styles() {
    wp_enqueue_style(
        'cozy-comm-modes',
        get_template_directory_uri() . '/assets/css/cozy-comm-modes.css',
        array(),
        '1.1.0'
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_comm_mode_enqueue_styles' );
