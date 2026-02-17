<?php
/**
 * ============================================================================
 * MODULE : Participants Publics (Avatars automatiques sur single event)
 * ============================================================================
 *
 * Affiche automatiquement la section des participants inscrits (avatars,
 * noms, badges sociaux, codes ami) sur chaque page single d'événement
 * (/event/nom-de-levenement), sans avoir besoin d'ajouter un bloc.
 *
 * Ce module est autonome : il requête directement les attendees RSVP
 * depuis la base de données, indépendamment du système de templates
 * d'Event Tickets.
 *
 * @package CozyGaming
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * -----------------------------------------------
 * 1. INJECTION AUTOMATIQUE SUR LA SINGLE EVENT
 * -----------------------------------------------
 * Se hook sur 'tribe_events_single_event_after_the_content'
 * pour s'afficher après la description de l'événement.
 */

/**
 * Affiche la section des participants sur la page single event
 */
function cozy_display_public_attendees() {
    $event_id = get_the_ID();

    // Vérifier qu'on est bien sur un événement
    if ( ! $event_id || get_post_type( $event_id ) !== 'tribe_events' ) {
        return;
    }

    // Récupérer tous les participants RSVP confirmés pour cet événement
    $attendee_posts = get_posts( array(
        'post_type'      => 'tribe_rsvp_attendees',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'   => '_tribe_rsvp_event',
                'value' => $event_id,
            ),
        ),
        'fields' => 'ids',
    ) );

    // On ne masque pas si pas de participants → affichage "aucun inscrit"
    $total = count( $attendee_posts );

    ?>
    <div class="cozy-public-attendees" id="cozy-participants">
        <div class="cozy-public-attendees__header">
            <h3 class="cozy-public-attendees__title">
                🎮
                <?php if ( $total > 0 ) : ?>
                    <?php printf(
                        _n( '%d joueur inscrit', '%d joueurs inscrits', $total, 'cozy-gaming' ),
                        $total
                    ); ?>
                <?php else : ?>
                    Aucun joueur inscrit pour le moment
                <?php endif; ?>
            </h3>
            <?php if ( $total === 0 ) : ?>
                <p class="cozy-public-attendees__empty">Sois le premier à réserver ta place ! 🎲</p>
            <?php endif; ?>
        </div>

        <?php if ( $total > 0 ) : ?>

            <!-- Pile d'avatars -->
            <div class="cozy-public-attendees__stack">
                <?php
                $shown = 0;
                foreach ( $attendee_posts as $attendee_id ) :
                    if ( $shown >= 10 ) break;
                    $user_id = get_post_meta( $attendee_id, '_tribe_tickets_attendee_user_id', true );
                ?>
                    <div class="cozy-public-attendees__avatar" title="<?php echo esc_attr( cozy_get_attendee_name( $attendee_id ) ); ?>">
                        <?php echo get_avatar( ! empty( $user_id ) ? $user_id : 0, 48 ); ?>
                    </div>
                <?php
                    $shown++;
                endforeach;

                if ( $total > 10 ) : ?>
                    <div class="cozy-public-attendees__more">
                        +<?php echo ( $total - 10 ); ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
    <?php
}

// Priorité 15 pour s'afficher APRÈS les liens iCal (priorité 10)
// mais AVANT la meta box (qui vient après le hook before_the_meta)
add_action( 'tribe_events_single_event_after_the_content', 'cozy_display_public_attendees', 15 );


/**
 * -----------------------------------------------
 * 2. FONCTION UTILITAIRE
 * -----------------------------------------------
 */

/**
 * Récupère le nom d'un attendee RSVP de manière sûre
 *
 * @param int $attendee_id L'ID du post attendee
 * @return string Le nom du participant
 */
function cozy_get_attendee_name( $attendee_id ) {
    // Essayer via la classe RSVP du plugin
    if ( class_exists( 'Tribe__Tickets__RSVP' ) ) {
        $name = get_post_meta( $attendee_id, tribe( Tribe__Tickets__RSVP::class )->full_name, true );
        if ( ! empty( $name ) ) {
            return $name;
        }
    }

    // Fallback : essayer via le meta classique
    $name = get_post_meta( $attendee_id, '_tribe_rsvp_full_name', true );
    if ( ! empty( $name ) ) {
        return $name;
    }

    // Dernier fallback : le titre du post
    return get_the_title( $attendee_id );
}


/**
 * -----------------------------------------------
 * 3. ENQUEUE DES STYLES DÉDIÉS
 * -----------------------------------------------
 */

function cozy_public_attendees_enqueue_styles() {
    // Charger uniquement sur les pages single d'événements
    if ( ! is_singular( 'tribe_events' ) ) {
        return;
    }

    wp_enqueue_style(
        'cozy-public-attendees',
        get_stylesheet_directory_uri() . '/assets/css/cozy-public-attendees.css',
        array(),
        '1.2.0'
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_public_attendees_enqueue_styles' );
