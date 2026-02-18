<?php
/**
 * ============================================================================
 * MODULE : Content Warnings (Avertissements de contenu)
 * ============================================================================
 *
 * Ajoute une taxonomie personnalisée « Content Warning » aux événements
 * pour prévenir les joueurs de contenus potentiellement sensibles
 * dans les jeux ou activités prévues (violence, horreur, jumpscares, etc.).
 *
 * Les warnings sont affichés :
 *   - Sur la page single de l'événement (bandeau visible)
 *   - Sur les listes d'événements (badges compacts)
 *
 * @package CozyGaming
 * @since 1.5.0
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
 * Enregistre la taxonomie « Content Warning » pour les événements
 */
function cozy_register_content_warning_taxonomy() {
    $labels = array(
        'name'                       => 'Content Warnings',
        'singular_name'              => 'Content Warning',
        'menu_name'                  => 'Content Warnings',
        'all_items'                  => 'Tous les avertissements',
        'edit_item'                  => 'Modifier l\'avertissement',
        'view_item'                  => 'Voir l\'avertissement',
        'update_item'                => 'Mettre à jour',
        'add_new_item'               => 'Ajouter un avertissement',
        'new_item_name'              => 'Nom du nouvel avertissement',
        'search_items'               => 'Rechercher',
        'not_found'                  => 'Aucun avertissement trouvé',
        'no_terms'                   => 'Aucun avertissement',
        'items_list'                 => 'Liste des avertissements',
        'back_to_items'              => '← Retour aux avertissements',
    );

    $args = array(
        'labels'            => $labels,
        'public'            => true,
        'hierarchical'      => false,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'show_ui'           => true,
        'rewrite'           => array( 'slug' => 'content-warning' ),
    );

    register_taxonomy( 'cozy_content_warning', 'tribe_events', $args );
}
add_action( 'init', 'cozy_register_content_warning_taxonomy' );


/**
 * -----------------------------------------------
 * 2. TERMES PAR DÉFAUT
 * -----------------------------------------------
 * Insère les content warnings prédéfinis à l'activation du thème.
 */

/**
 * Crée les termes par défaut
 */
function cozy_insert_default_content_warnings() {
    if ( ! taxonomy_exists( 'cozy_content_warning' ) ) {
        cozy_register_content_warning_taxonomy();
    }

    $defaults = array(
        'violence' => array(
            'name'        => '⚔️ Violence',
            'slug'        => 'violence',
            'description' => 'Le jeu contient des scènes de violence (combats, armes, etc.).',
        ),
        'horreur' => array(
            'name'        => '👻 Horreur',
            'slug'        => 'horreur',
            'description' => 'Le jeu contient des éléments d\'horreur ou d\'épouvante.',
        ),
        'jumpscares' => array(
            'name'        => '😱 Jumpscares',
            'slug'        => 'jumpscares',
            'description' => 'Le jeu contient des jumpscares (apparitions soudaines effrayantes).',
        ),
        'langage-cru' => array(
            'name'        => '🤬 Langage cru',
            'slug'        => 'langage-cru',
            'description' => 'Le jeu contient un langage grossier ou vulgaire.',
        ),
        'themes-sensibles' => array(
            'name'        => '⚠️ Thèmes sensibles',
            'slug'        => 'themes-sensibles',
            'description' => 'Le jeu aborde des thèmes pouvant être sensibles (mort, maladie, etc.).',
        ),
        'contenu-suggestif' => array(
            'name'        => '🔞 Contenu suggestif',
            'slug'        => 'contenu-suggestif',
            'description' => 'Le jeu contient des éléments suggestifs ou de la nudité partielle.',
        ),
        'clignotements' => array(
            'name'        => '⚡ Clignotements',
            'slug'        => 'clignotements',
            'description' => 'Le jeu contient des effets visuels intenses (flashs, lumières stroboscopiques) pouvant affecter les personnes photosensibles.',
        ),
        'sons-forts' => array(
            'name'        => '🔊 Sons forts',
            'slug'        => 'sons-forts',
            'description' => 'Le jeu contient des bruits soudains ou très forts.',
        ),
    );

    foreach ( $defaults as $warning ) {
        if ( ! term_exists( $warning['slug'], 'cozy_content_warning' ) ) {
            wp_insert_term(
                $warning['name'],
                'cozy_content_warning',
                array(
                    'slug'        => $warning['slug'],
                    'description' => $warning['description'],
                )
            );
        }
    }
}
add_action( 'after_switch_theme', 'cozy_insert_default_content_warnings' );

add_action( 'admin_init', function() {
    if ( get_option( 'cozy_content_warnings_initialized' ) ) {
        return;
    }
    cozy_insert_default_content_warnings();
    update_option( 'cozy_content_warnings_initialized', true );
} );


/**
 * -----------------------------------------------
 * 3. CONFIGURATION DES ICÔNES ET COULEURS
 * -----------------------------------------------
 */

/**
 * Retourne la configuration visuelle d'un content warning
 *
 * @param string $slug Le slug du terme
 * @return array Configuration (icône, couleurs)
 */
function cozy_get_content_warning_config( $slug ) {
    $configs = array(
        'violence' => array(
            'icon'   => '⚔️',
            'bg'     => '#fff3e0',
            'color'  => '#e65100',
            'border' => '#ffcc80',
            'label'  => 'Violence',
        ),
        'horreur' => array(
            'icon'   => '👻',
            'bg'     => '#f3e5f5',
            'color'  => '#7b1fa2',
            'border' => '#ce93d8',
            'label'  => 'Horreur',
        ),
        'jumpscares' => array(
            'icon'   => '😱',
            'bg'     => '#fce4ec',
            'color'  => '#c62828',
            'border' => '#ef9a9a',
            'label'  => 'Jumpscares',
        ),
        'langage-cru' => array(
            'icon'   => '🤬',
            'bg'     => '#fff8e1',
            'color'  => '#f57f17',
            'border' => '#fff176',
            'label'  => 'Langage cru',
        ),
        'themes-sensibles' => array(
            'icon'   => '⚠️',
            'bg'     => '#fff9c4',
            'color'  => '#f9a825',
            'border' => '#fff59d',
            'label'  => 'Thèmes sensibles',
        ),
        'contenu-suggestif' => array(
            'icon'   => '🔞',
            'bg'     => '#ffebee',
            'color'  => '#b71c1c',
            'border' => '#ef9a9a',
            'label'  => 'Contenu suggestif',
        ),
        'clignotements' => array(
            'icon'   => '⚡',
            'bg'     => '#fffde7',
            'color'  => '#f57f17',
            'border' => '#fff9c4',
            'label'  => 'Clignotements',
        ),
        'sons-forts' => array(
            'icon'   => '🔊',
            'bg'     => '#e3f2fd',
            'color'  => '#1565c0',
            'border' => '#90caf9',
            'label'  => 'Sons forts',
        ),
    );

    return isset( $configs[ $slug ] ) ? $configs[ $slug ] : array(
        'icon'   => '⚠️',
        'bg'     => '#fff9c4',
        'color'  => '#f9a825',
        'border' => '#fff59d',
        'label'  => '',
    );
}


/**
 * -----------------------------------------------
 * 4. AFFICHAGE SUR LA PAGE SINGLE EVENT
 * -----------------------------------------------
 * Bandeau d'avertissement visible et clair,
 * affiché avant le contenu de l'événement.
 */

/**
 * Affiche les content warnings sur la page single d'un événement
 */
function cozy_display_content_warnings_single() {
    $event_id = get_the_ID();

    if ( ! $event_id || get_post_type( $event_id ) !== 'tribe_events' ) {
        return;
    }

    $warnings = wp_get_post_terms( $event_id, 'cozy_content_warning' );

    if ( is_wp_error( $warnings ) || empty( $warnings ) ) {
        return;
    }

    ?>
    <div class="cozy-cw cozy-cw--single" id="cozy-content-warnings">
        <div class="cozy-cw__header">
            <span class="cozy-cw__header-icon">⚠️</span>
            <span class="cozy-cw__header-title">Content Warnings</span>
        </div>

        <div class="cozy-cw__badges">
            <?php foreach ( $warnings as $warning ) :
                $config = cozy_get_content_warning_config( $warning->slug );
            ?>
                <span
                    class="cozy-cw__badge"
                    style="background: <?php echo esc_attr( $config['bg'] ); ?>; color: <?php echo esc_attr( $config['color'] ); ?>; border: 1px solid <?php echo esc_attr( $config['border'] ); ?>;"
                    title="<?php echo esc_attr( $warning->description ); ?>"
                >
                    <?php echo $config['icon']; ?>
                    <?php echo esc_html( $config['label'] ?: $warning->name ); ?>
                </span>
            <?php endforeach; ?>
        </div>

        <?php if ( count( $warnings ) > 0 ) : ?>
            <p class="cozy-cw__description">
                <?php
                $descriptions = array();
                foreach ( $warnings as $warning ) {
                    if ( ! empty( $warning->description ) ) {
                        $descriptions[] = $warning->description;
                    }
                }
                if ( ! empty( $descriptions ) ) {
                    echo esc_html( implode( ' ', $descriptions ) );
                }
                ?>
            </p>
        <?php endif; ?>
    </div>
    <?php
}

// Priorité 6 : après les modes de comm (5) et avant la charte (8)
add_action( 'tribe_events_single_event_before_the_content', 'cozy_display_content_warnings_single', 6 );


/**
 * -----------------------------------------------
 * 5. AFFICHAGE SUR LES LISTES D'ÉVÉNEMENTS
 * -----------------------------------------------
 * Badges compacts sur les vues liste/jour/mois.
 */

/**
 * Affiche les badges CW compacts dans les listes d'événements
 *
 * @param int|null $event_id L'ID de l'événement
 */
function cozy_display_content_warnings_list( $event_id = null ) {
    if ( ! $event_id ) {
        $event_id = get_the_ID();
    }

    if ( ! $event_id ) {
        return;
    }

    $warnings = wp_get_post_terms( $event_id, 'cozy_content_warning' );

    if ( is_wp_error( $warnings ) || empty( $warnings ) ) {
        return;
    }

    echo '<div class="cozy-cw cozy-cw--list">';
    foreach ( $warnings as $warning ) {
        $config = cozy_get_content_warning_config( $warning->slug );
        printf(
            '<span class="cozy-cw__badge cozy-cw__badge--compact" style="background:%s; color:%s; border:1px solid %s;" title="%s">%s</span>',
            esc_attr( $config['bg'] ),
            esc_attr( $config['color'] ),
            esc_attr( $config['border'] ),
            esc_attr( $config['label'] ?: $warning->name ),
            $config['icon']
        );
    }
    echo '</div>';
}

// Hook sur les vues liste/jour TEC v2 — après les modes de comm
add_action( 'tribe_template_after_include:events/v2/list/event/title', function( $file, $name, $template ) {
    $event_id = $template->get( 'event' )->ID ?? get_the_ID();
    cozy_display_content_warnings_list( $event_id );
}, 11, 3 );

add_action( 'tribe_template_after_include:events/v2/day/event/title', function( $file, $name, $template ) {
    $event_id = $template->get( 'event' )->ID ?? get_the_ID();
    cozy_display_content_warnings_list( $event_id );
}, 11, 3 );


/**
 * -----------------------------------------------
 * 6. ENQUEUE DES STYLES
 * -----------------------------------------------
 */

function cozy_content_warnings_enqueue_styles() {
    wp_enqueue_style(
        'cozy-content-warnings',
        get_template_directory_uri() . '/assets/css/cozy-content-warnings.css',
        array(),
        '1.5.0'
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_content_warnings_enqueue_styles' );
