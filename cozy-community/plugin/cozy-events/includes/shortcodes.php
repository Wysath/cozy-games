<?php
/**
 * [cozy_events limit="6" game="animal-crossing" type="troc"]
 */
add_shortcode( 'cozy_events', function( $atts ) {
    $atts = shortcode_atts([
        'limit' => 6,
        'game'  => '',
        'type'  => '',
    ], $atts );

    $args = [
        'post_type'      => 'cozy_event',
        'post_status'    => 'publish',
        'posts_per_page' => (int) $atts['limit'],
        'orderby'        => 'meta_value',
        'meta_key'       => '_cozy_event_date',
        'order'          => 'ASC',
        'meta_query'     => [
            'relation' => 'OR',
            [
                'key'     => '_cozy_event_date',
                'value'   => date( 'Y-m-d' ),
                'compare' => '>=',
                'type'    => 'DATE',
            ],
            [
                'key'     => '_cozy_event_date',
                'value'   => '',
                'compare' => '=',
            ],
            [
                'key'     => '_cozy_event_date',
                'compare' => 'NOT EXISTS',
            ],
        ],
    ];

    // Filtres par taxonomy
    $tax_query = [];
    if ( $atts['game'] ) {
        $tax_query[] = ['taxonomy' => 'cozy_game', 'field' => 'slug', 'terms' => $atts['game']];
    }
    if ( $atts['type'] ) {
        $tax_query[] = ['taxonomy' => 'cozy_event_type', 'field' => 'slug', 'terms' => $atts['type']];
    }
    if ( $tax_query ) $args['tax_query'] = $tax_query;

    $query = new WP_Query($args);

    ob_start();
    if ( $query->have_posts() ) :
        echo '<div class="cozy-events-grid">';
        while ( $query->have_posts() ) : $query->the_post();
            $event_id    = get_the_ID();
            $date        = get_post_meta($event_id, '_cozy_event_date', true);
            $time        = get_post_meta($event_id, '_cozy_event_time', true);
            $places_left = cozy_get_places_left($event_id);
            $count       = count(cozy_get_registrants($event_id));
            $is_troc     = get_post_meta($event_id, '_cozy_event_is_troc', true);
            $games       = get_the_terms($event_id, 'cozy_game');
            $types       = get_the_terms($event_id, 'cozy_event_type');
            ?>
            <div class="cozy-event-card">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="cozy-event-card__thumb">
                        <?php echo get_the_post_thumbnail( $event_id, 'medium' ); ?>
                    </div>
                <?php endif; ?>
                <div class="cozy-event-card__body">
                    <div class="cozy-event-card__meta">
                        <?php if ( ! is_wp_error( $types ) && ! empty( $types ) ) : ?>
                            <span class="cozy-badge cozy-badge--type"><?php echo esc_html( $types[0]->name ); ?></span>
                        <?php endif; ?>
                        <?php if ( $is_troc ) : ?>
                            <span class="cozy-badge cozy-badge--troc">
                                <i data-lucide="repeat" class="lucide"></i> Troc
                            </span>
                        <?php endif; ?>
                    </div>
                    <h3><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
                    <?php if ( $date ) : ?>
                        <p class="cozy-event-card__date">
                            <i data-lucide="calendar" class="lucide"></i>
                            <?php echo esc_html( date_i18n( 'j F Y', strtotime( $date ) ) ); ?>
                            <?php if ( $time ) : ?> à <?php echo esc_html( $time ); ?><?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php if ( ! is_wp_error( $games ) && ! empty( $games ) ) : ?>
                        <p class="cozy-event-card__games">
                            <i data-lucide="gamepad-2" class="lucide"></i>
                            <?php echo esc_html( implode( ', ', wp_list_pluck( $games, 'name' ) ) ); ?>
                        </p>
                    <?php endif; ?>
                    <p class="cozy-event-card__places">
                        <?php if ( $places_left === 0 ) : ?>
                            <span class="cozy-badge cozy-badge--full">Complet</span>
                        <?php elseif ( $places_left === -1 ) : ?>
                            <i data-lucide="users" class="lucide"></i>
                            <?php echo esc_html( $count ); ?> inscrit(s)
                        <?php else : ?>
                            <i data-lucide="users" class="lucide"></i>
                            <?php echo esc_html( $places_left ); ?> place(s) restante(s)
                        <?php endif; ?>
                    </p>
                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="cozy-btn cozy-btn--primary cozy-btn--sm">Voir l'événement</a>
                </div>
            </div>
            <?php
        endwhile;
        echo '</div>';
        wp_reset_postdata();
    else:
        echo '<p class="cozy-no-events">Aucun événement à venir pour le moment. 🌱</p>';
    endif;

    return ob_get_clean();
});


/**
 * [cozy_mes_reservations]
 *
 * Affiche la liste des événements auxquels l'utilisateur connecté
 * est inscrit. Séparation événements à venir / passés.
 */
add_shortcode( 'cozy_mes_reservations', function( $atts ) {
    if ( ! is_user_logged_in() ) {
        return '<div class="cozy-reservations__login">
            <p><i data-lucide="log-in"></i> <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Connecte-toi</a> pour voir tes réservations.</p>
        </div>';
    }

    $user_id = get_current_user_id();
    $today   = date( 'Y-m-d' );

    // Récupérer TOUS les événements publiés qui ont des inscrits
    $all_events = get_posts( array(
        'post_type'      => 'cozy_event',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_cozy_event_registrants',
                'compare' => 'EXISTS',
            ),
        ),
    ) );

    $upcoming = array();
    $past     = array();

    foreach ( $all_events as $event ) {
        // Vérifier si l'utilisateur est inscrit
        if ( ! function_exists( 'cozy_is_registered' ) || ! cozy_is_registered( $event->ID, $user_id ) ) {
            continue;
        }

        $date = get_post_meta( $event->ID, '_cozy_event_date', true );

        if ( empty( $date ) || $date >= $today ) {
            $upcoming[] = $event;
        } else {
            $past[] = $event;
        }
    }

    // Trier upcoming par date ASC, past par date DESC
    usort( $upcoming, function( $a, $b ) {
        $da = get_post_meta( $a->ID, '_cozy_event_date', true ) ?: '9999-12-31';
        $db = get_post_meta( $b->ID, '_cozy_event_date', true ) ?: '9999-12-31';
        return strcmp( $da, $db );
    } );
    usort( $past, function( $a, $b ) {
        $da = get_post_meta( $a->ID, '_cozy_event_date', true ) ?: '0000-00-00';
        $db = get_post_meta( $b->ID, '_cozy_event_date', true ) ?: '0000-00-00';
        return strcmp( $db, $da );
    } );

    ob_start();
    ?>
    <div class="cozy-reservations">
        <div class="cozy-reservations__header">
            <h3 class="cozy-reservations__title">
                <i data-lucide="ticket"></i>
                Mes réservations
            </h3>
        </div>

        <?php // ── Événements à venir ── ?>
        <div class="cozy-reservations__section">
            <h4 class="cozy-reservations__section-title">
                <i data-lucide="calendar-check" class="lucide"></i>
                À venir
                <?php if ( ! empty( $upcoming ) ) : ?>
                    <span class="cozy-reservations__count"><?php echo count( $upcoming ); ?></span>
                <?php endif; ?>
            </h4>

            <?php if ( empty( $upcoming ) ) : ?>
                <div class="cozy-reservations__empty">
                    <p>Aucune réservation à venir.</p>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'cozy_event' ) ); ?>" class="cozy-btn cozy-btn--outline cozy-btn--sm">
                        <i data-lucide="search"></i>
                        Découvrir les événements
                    </a>
                </div>
            <?php else : ?>
                <div class="cozy-reservations__list">
                    <?php foreach ( $upcoming as $event ) :
                        echo cozy_render_reservation_card( $event, $user_id, false );
                    endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php // ── Événements passés ── ?>
        <?php if ( ! empty( $past ) ) : ?>
            <div class="cozy-reservations__section cozy-reservations__section--past">
                <h4 class="cozy-reservations__section-title">
                    <i data-lucide="clock" class="lucide"></i>
                    Passés
                    <span class="cozy-reservations__count"><?php echo count( $past ); ?></span>
                </h4>
                <div class="cozy-reservations__list">
                    <?php foreach ( $past as $event ) :
                        echo cozy_render_reservation_card( $event, $user_id, true );
                    endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
} );


/**
 * Rend une carte de réservation pour le profil utilisateur.
 *
 * @param WP_Post $event   L'événement
 * @param int     $user_id L'ID de l'utilisateur
 * @param bool    $is_past Si l'événement est passé
 * @return string HTML
 */
function cozy_render_reservation_card( $event, $user_id, $is_past = false ) {
    $event_id = $event->ID;
    $date     = get_post_meta( $event_id, '_cozy_event_date', true );
    $time     = get_post_meta( $event_id, '_cozy_event_time', true );
    $link     = get_post_meta( $event_id, '_cozy_event_link', true );
    $is_troc  = get_post_meta( $event_id, '_cozy_event_is_troc', true );
    $games    = get_the_terms( $event_id, 'cozy_game' );
    $types    = get_the_terms( $event_id, 'cozy_event_type' );

    // Trouver la note troc de l'utilisateur
    $troc_note = '';
    $registrants = cozy_get_registrants( $event_id );
    foreach ( $registrants as $reg ) {
        if ( (int) $reg['user_id'] === $user_id && ! empty( $reg['troc_note'] ) ) {
            $troc_note = $reg['troc_note'];
            break;
        }
    }

    $classes = 'cozy-reservations__card';
    if ( $is_past ) {
        $classes .= ' cozy-reservations__card--past';
    }

    ob_start();
    ?>
    <div class="<?php echo esc_attr( $classes ); ?>">
        <div class="cozy-reservations__card-header">
            <div class="cozy-reservations__card-badges">
                <?php if ( ! is_wp_error( $types ) && ! empty( $types ) ) : ?>
                    <span class="cozy-badge cozy-badge--type"><?php echo esc_html( $types[0]->name ); ?></span>
                <?php endif; ?>
                <?php if ( $is_troc ) : ?>
                    <span class="cozy-badge cozy-badge--troc">
                        <i data-lucide="repeat" class="lucide"></i> Troc
                    </span>
                <?php endif; ?>
                <?php if ( $is_past ) : ?>
                    <span class="cozy-badge cozy-badge--past">Terminé</span>
                <?php endif; ?>
            </div>
        </div>

        <h4 class="cozy-reservations__card-title">
            <a href="<?php echo esc_url( get_permalink( $event ) ); ?>">
                <?php echo esc_html( get_the_title( $event ) ); ?>
            </a>
        </h4>

        <div class="cozy-reservations__card-infos">
            <?php if ( $date ) : ?>
                <span class="cozy-reservations__card-info">
                    <i data-lucide="calendar" class="lucide"></i>
                    <?php echo esc_html( date_i18n( 'j F Y', strtotime( $date ) ) ); ?>
                    <?php if ( $time ) : ?> à <?php echo esc_html( $time ); ?><?php endif; ?>
                </span>
            <?php endif; ?>
            <?php if ( ! is_wp_error( $games ) && ! empty( $games ) ) : ?>
                <span class="cozy-reservations__card-info">
                    <i data-lucide="gamepad-2" class="lucide"></i>
                    <?php echo esc_html( implode( ', ', wp_list_pluck( $games, 'name' ) ) ); ?>
                </span>
            <?php endif; ?>
            <?php if ( $link && ! $is_past ) : ?>
                <span class="cozy-reservations__card-info">
                    <i data-lucide="link" class="lucide"></i>
                    <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">Rejoindre</a>
                </span>
            <?php endif; ?>
        </div>

        <?php if ( $troc_note ) : ?>
            <div class="cozy-reservations__card-troc">
                <i data-lucide="repeat" class="lucide"></i>
                <span><?php echo esc_html( $troc_note ); ?></span>
            </div>
        <?php endif; ?>

        <?php if ( ! $is_past ) : ?>
            <div class="cozy-reservations__card-actions">
                <a href="<?php echo esc_url( get_permalink( $event ) ); ?>" class="cozy-btn cozy-btn--primary cozy-btn--sm">
                    <i data-lucide="eye"></i>
                    Voir l'événement
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
