<?php
/**
 * ============================================================================
 * MODULE : Historique des Réservations (Shortcode)
 * ============================================================================
 *
 * Affiche toutes les réservations RSVP de l'utilisateur connecté,
 * séparées en deux sections :
 *   1. À venir — triées par date croissante (le plus proche en premier)
 *   2. Souvenirs — événements passés (les plus récents en premier)
 *
 * Shortcode : [cozy_mes_reservations]
 *
 * @package CozyGaming
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * -----------------------------------------------
 * 1. SHORTCODE PRINCIPAL
 * -----------------------------------------------
 */

/**
 * Affiche l'historique complet des réservations RSVP de l'utilisateur
 *
 * @return string HTML de la section
 */
function cozy_shortcode_mes_reservations() {

    // L'utilisateur doit être connecté
    if ( ! is_user_logged_in() ) {
        return '<div class="cozy-reservations__login-required">'
             . '<p>🔒 Connecte-toi pour voir tes réservations.</p>'
             . '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Se connecter</a>'
             . '</div>';
    }

    $user_id = get_current_user_id();

    // Récupérer tous les attendees RSVP de l'utilisateur (status = "yes")
    $attendee_ids = get_posts( array(
        'post_type'      => 'tribe_rsvp_attendees',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_tribe_tickets_attendee_user_id',
                'value' => $user_id,
                'type'  => 'NUMERIC',
            ),
            array(
                'key'   => '_tribe_rsvp_status',
                'value' => 'yes',
            ),
        ),
    ) );

    // Construire la liste des réservations avec les données de l'événement
    $reservations = array();

    foreach ( $attendee_ids as $attendee_id ) {
        $event_id = get_post_meta( $attendee_id, '_tribe_rsvp_event', true );

        if ( empty( $event_id ) ) {
            continue;
        }

        $event = get_post( $event_id );
        if ( ! $event || 'publish' !== $event->post_status ) {
            continue;
        }

        $start_date = get_post_meta( $event_id, '_EventStartDate', true );
        $end_date   = get_post_meta( $event_id, '_EventEndDate', true );

        if ( empty( $start_date ) ) {
            continue;
        }

        $reservations[] = array(
            'attendee_id' => $attendee_id,
            'event_id'    => $event_id,
            'event'       => $event,
            'start_date'  => $start_date,
            'end_date'    => $end_date,
            'timestamp'   => strtotime( $start_date ),
        );
    }

    // Séparer en « à venir » et « passés »
    $now      = current_time( 'timestamp' );
    $upcoming = array();
    $past     = array();

    foreach ( $reservations as $r ) {
        // On utilise la date de fin si disponible, sinon la date de début
        $reference_time = ! empty( $r['end_date'] ) ? strtotime( $r['end_date'] ) : $r['timestamp'];

        if ( $reference_time >= $now ) {
            $upcoming[] = $r;
        } else {
            $past[] = $r;
        }
    }

    // Trier les à venir par date croissante (le plus proche en premier)
    usort( $upcoming, function( $a, $b ) {
        return $a['timestamp'] - $b['timestamp'];
    } );

    // Trier les passés par date décroissante (le plus récent en premier)
    usort( $past, function( $a, $b ) {
        return $b['timestamp'] - $a['timestamp'];
    } );

    // Générer le HTML
    ob_start();
    ?>
    <div class="cozy-reservations">

        <?php // --- Section À venir --- ?>
        <div class="cozy-reservations__section cozy-reservations__section--upcoming">
            <h3 class="cozy-reservations__section-title">
                🎮 Mes prochains événements
                <span class="cozy-reservations__count"><?php echo count( $upcoming ); ?></span>
            </h3>

            <?php if ( empty( $upcoming ) ) : ?>
                <div class="cozy-reservations__empty">
                    <p>Aucune réservation à venir pour le moment.</p>
                    <a href="<?php echo esc_url( tribe_get_events_link() ); ?>" class="cozy-reservations__browse-link">
                        📅 Découvrir les événements
                    </a>
                </div>
            <?php else : ?>
                <div class="cozy-reservations__list">
                    <?php foreach ( $upcoming as $r ) : ?>
                        <?php echo cozy_render_reservation_card( $r, 'upcoming' ); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php // --- Section Souvenirs --- ?>
        <div class="cozy-reservations__section cozy-reservations__section--past">
            <h3 class="cozy-reservations__section-title">
                📸 Mes souvenirs
                <span class="cozy-reservations__count"><?php echo count( $past ); ?></span>
            </h3>

            <?php if ( empty( $past ) ) : ?>
                <div class="cozy-reservations__empty cozy-reservations__empty--past">
                    <p>Pas encore de souvenirs… Inscris-toi à un événement !</p>
                </div>
            <?php else : ?>
                <div class="cozy-reservations__list">
                    <?php foreach ( $past as $r ) : ?>
                        <?php echo cozy_render_reservation_card( $r, 'past' ); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_mes_reservations', 'cozy_shortcode_mes_reservations' );


/**
 * -----------------------------------------------
 * 2. RENDU D'UNE CARTE DE RÉSERVATION
 * -----------------------------------------------
 */

/**
 * Génère le HTML d'une carte de réservation
 *
 * @param array  $reservation Les données de la réservation
 * @param string $context     'upcoming' ou 'past'
 * @return string HTML de la carte
 */
function cozy_render_reservation_card( $reservation, $context = 'upcoming' ) {
    $event_id   = $reservation['event_id'];
    $event      = $reservation['event'];
    $start_date = $reservation['start_date'];
    $end_date   = $reservation['end_date'];

    // Formater les dates en français
    $start_dt    = new DateTime( $start_date );
    $end_dt      = ! empty( $end_date ) ? new DateTime( $end_date ) : null;

    // Jour et mois pour l'affichage « calendrier »
    $day   = $start_dt->format( 'd' );
    $month = cozy_get_french_month_short( (int) $start_dt->format( 'n' ) );
    $year  = $start_dt->format( 'Y' );

    // Heures
    $start_time = $start_dt->format( 'H:i' );
    $end_time   = $end_dt ? $end_dt->format( 'H:i' ) : '';

    // Format lisible complet
    $date_display = cozy_format_event_date_french( $start_dt, $end_dt );

    // Image mise en avant
    $thumbnail = get_the_post_thumbnail_url( $event_id, 'medium' );

    // Lien vers l'événement
    $permalink = get_permalink( $event_id );

    // Modes de communication (si le module est actif)
    $comm_badges = '';
    if ( function_exists( 'cozy_display_comm_mode_list' ) ) {
        ob_start();
        cozy_display_comm_mode_list( $event_id );
        $comm_badges = ob_get_clean();
    }

    // Nombre de participants
    $attendees_count = 0;
    if ( class_exists( 'Tribe__Tickets__RSVP' ) ) {
        $rsvp = Tribe__Tickets__RSVP::get_instance();
        $attendees_count = $rsvp->get_attendees_count_going( $event_id );
    }

    // Calcul du « dans X jours » pour les événements à venir
    $countdown = '';
    if ( 'upcoming' === $context ) {
        $now_dt   = new DateTime( current_time( 'mysql' ) );
        $diff     = $now_dt->diff( $start_dt );
        $days_left = (int) $diff->format( '%r%a' );

        if ( $days_left === 0 ) {
            $countdown = "Aujourd'hui !";
        } elseif ( $days_left === 1 ) {
            $countdown = 'Demain';
        } elseif ( $days_left > 1 ) {
            $countdown = 'Dans ' . $days_left . ' jours';
        }
    }

    ob_start();
    ?>
    <a href="<?php echo esc_url( $permalink ); ?>" class="cozy-reservations__card cozy-reservations__card--<?php echo esc_attr( $context ); ?>">
        
        <?php // --- Vignette calendrier --- ?>
        <div class="cozy-reservations__date-badge">
            <span class="cozy-reservations__date-day"><?php echo esc_html( $day ); ?></span>
            <span class="cozy-reservations__date-month"><?php echo esc_html( $month ); ?></span>
            <?php if ( (int) $year !== (int) date( 'Y' ) ) : ?>
                <span class="cozy-reservations__date-year"><?php echo esc_html( $year ); ?></span>
            <?php endif; ?>
        </div>

        <?php // --- Contenu de la carte --- ?>
        <div class="cozy-reservations__card-body">
            <div class="cozy-reservations__card-header">
                <h4 class="cozy-reservations__event-title"><?php echo esc_html( $event->post_title ); ?></h4>
                <?php if ( ! empty( $countdown ) ) : ?>
                    <span class="cozy-reservations__countdown"><?php echo esc_html( $countdown ); ?></span>
                <?php endif; ?>
            </div>

            <div class="cozy-reservations__card-meta">
                <span class="cozy-reservations__meta-item cozy-reservations__meta-time">
                    🕐 <?php echo esc_html( $date_display ); ?>
                </span>

                <?php if ( $attendees_count > 0 ) : ?>
                    <span class="cozy-reservations__meta-item cozy-reservations__meta-players">
                        👥 <?php printf( _n( '%d joueur', '%d joueurs', $attendees_count, 'cozy-gaming' ), $attendees_count ); ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $comm_badges ) ) : ?>
                <div class="cozy-reservations__comm-modes">
                    <?php echo $comm_badges; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php // --- Image de fond (si disponible) --- ?>
        <?php if ( ! empty( $thumbnail ) ) : ?>
            <div class="cozy-reservations__thumbnail" style="background-image: url('<?php echo esc_url( $thumbnail ); ?>')"></div>
        <?php endif; ?>

    </a>
    <?php
    return ob_get_clean();
}


/**
 * -----------------------------------------------
 * 3. FONCTIONS UTILITAIRES (DATES EN FRANÇAIS)
 * -----------------------------------------------
 */

/**
 * Retourne l'abréviation française d'un mois
 *
 * @param int $month_number Numéro du mois (1-12)
 * @return string Abréviation
 */
function cozy_get_french_month_short( $month_number ) {
    $months = array(
        1  => 'janv.',
        2  => 'févr.',
        3  => 'mars',
        4  => 'avr.',
        5  => 'mai',
        6  => 'juin',
        7  => 'juil.',
        8  => 'août',
        9  => 'sept.',
        10 => 'oct.',
        11 => 'nov.',
        12 => 'déc.',
    );

    return $months[ $month_number ] ?? '';
}

/**
 * Retourne le nom complet français d'un jour de la semaine
 *
 * @param int $day_number Numéro du jour (0=dimanche, 1=lundi, …)
 * @return string Nom du jour
 */
function cozy_get_french_day( $day_number ) {
    $days = array(
        0 => 'Dimanche',
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
    );

    return $days[ $day_number ] ?? '';
}

/**
 * Formate une plage de dates d'événement en français lisible
 *
 * @param DateTime      $start Date de début
 * @param DateTime|null $end   Date de fin (optionnelle)
 * @return string Date formatée
 */
function cozy_format_event_date_french( $start, $end = null ) {
    $day_name   = cozy_get_french_day( (int) $start->format( 'w' ) );
    $day        = $start->format( 'j' );
    $month_name = cozy_get_french_month_short( (int) $start->format( 'n' ) );
    $year       = $start->format( 'Y' );
    $start_time = $start->format( 'H:i' );

    $result = $day_name . ' ' . $day . ' ' . $month_name;

    // Ajouter l'année si ce n'est pas l'année en cours
    if ( (int) $year !== (int) date( 'Y' ) ) {
        $result .= ' ' . $year;
    }

    $result .= ' à ' . $start_time;

    // Si même jour, afficher juste l'heure de fin
    if ( $end ) {
        if ( $start->format( 'Y-m-d' ) === $end->format( 'Y-m-d' ) ) {
            $result .= ' – ' . $end->format( 'H:i' );
        } else {
            // Événement sur plusieurs jours
            $end_day_name   = cozy_get_french_day( (int) $end->format( 'w' ) );
            $end_day        = $end->format( 'j' );
            $end_month_name = cozy_get_french_month_short( (int) $end->format( 'n' ) );
            $result .= ' → ' . $end_day_name . ' ' . $end_day . ' ' . $end_month_name . ' à ' . $end->format( 'H:i' );
        }
    }

    return $result;
}


/**
 * -----------------------------------------------
 * 4. CHARGEMENT DES ASSETS
 * -----------------------------------------------
 */

/**
 * Enqueue les styles du module réservations
 */
function cozy_reservations_enqueue_assets() {
    // Charger sur toutes les pages front (le shortcode peut être n'importe où)
    wp_enqueue_style(
        'cozy-reservations',
        get_stylesheet_directory_uri() . '/assets/css/cozy-reservations.css',
        array(),
        '1.0.0'
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_reservations_enqueue_assets' );
