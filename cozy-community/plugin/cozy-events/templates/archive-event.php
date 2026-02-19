<?php
/**
 * Template pour la liste des événements (archive).
 *
 * Deux vues : liste (grille de cartes) et calendrier mensuel.
 *
 * @package CozyEvents
 * @since 1.3.0
 */

get_header();

// ── Récupérer TOUS les événements publiés pour le calendrier ──
$all_events = get_posts( array(
    'post_type'      => 'cozy_event',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value',
    'meta_key'       => '_cozy_event_date',
    'order'          => 'ASC',
) );

$today          = date( 'Y-m-d' );
$calendar_data  = array();
$upcoming_count = 0;

foreach ( $all_events as $ev ) {
    $date = get_post_meta( $ev->ID, '_cozy_event_date', true );
    if ( empty( $date ) ) continue;

    $time        = get_post_meta( $ev->ID, '_cozy_event_time', true );
    $is_troc     = get_post_meta( $ev->ID, '_cozy_event_is_troc', true );
    $games       = get_the_terms( $ev->ID, 'cozy_game' );
    $types       = get_the_terms( $ev->ID, 'cozy_event_type' );
    $places_left = function_exists( 'cozy_get_places_left' ) ? cozy_get_places_left( $ev->ID ) : -1;
    $count       = function_exists( 'cozy_get_registrants' ) ? count( cozy_get_registrants( $ev->ID ) ) : 0;

    if ( $date >= $today ) $upcoming_count++;

    $calendar_data[] = array(
        'id'         => $ev->ID,
        'title'      => get_the_title( $ev ),
        'date'       => $date,
        'time'       => $time ?: '',
        'url'        => get_permalink( $ev ),
        'game'       => ( ! is_wp_error( $games ) && ! empty( $games ) ) ? $games[0]->name : '',
        'type'       => ( ! is_wp_error( $types ) && ! empty( $types ) ) ? $types[0]->name : '',
        'isTroc'     => (bool) $is_troc,
        'placesLeft' => $places_left,
        'count'      => $count,
        'thumb'      => get_the_post_thumbnail_url( $ev, 'thumbnail' ) ?: '',
    );
}

// Passer les données au JS pour le calendrier
wp_localize_script( 'cozy-events', 'cozyCalendar', array(
    'events' => $calendar_data,
) );
?>
<main id="main-content" class="cozy-main">
    <div class="cozy-container">
        <div class="cozy-events-archive">

            <!-- ── En-tête : titre + bascule vue ── -->
            <div class="cozy-events-archive__header">
                <div class="cozy-events-archive__title-row">
                    <h1 class="cozy-events-archive__title">
                        <i data-lucide="calendar"></i>
                        Événements
                    </h1>
                    <?php if ( $upcoming_count > 0 ) : ?>
                        <span class="cozy-events-archive__count"><?php echo $upcoming_count; ?> à venir</span>
                    <?php endif; ?>
                </div>

                <div class="cozy-events-archive__views">
                    <button class="cozy-events-view-btn cozy-events-view-btn--active" data-view="list" type="button">
                        <i data-lucide="layout-list"></i>
                        <span>Liste</span>
                    </button>
                    <button class="cozy-events-view-btn" data-view="calendar" type="button">
                        <i data-lucide="calendar-days"></i>
                        <span>Calendrier</span>
                    </button>
                </div>
            </div>

            <!-- ── Vue Liste ── -->
            <div class="cozy-events-view" id="cozy-events-list">
                <?php echo do_shortcode( '[cozy_events limit="20"]' ); ?>
            </div>

            <!-- ── Vue Calendrier ── -->
            <div class="cozy-events-view" id="cozy-events-calendar" style="display: none;">
                <div class="cozy-calendar">
                    <div class="cozy-calendar__nav">
                        <button class="cozy-calendar__nav-btn" id="cozy-cal-prev" type="button">
                            <i data-lucide="chevron-left"></i>
                        </button>
                        <h3 class="cozy-calendar__month" id="cozy-cal-title"></h3>
                        <button class="cozy-calendar__nav-btn" id="cozy-cal-next" type="button">
                            <i data-lucide="chevron-right"></i>
                        </button>
                    </div>
                    <div class="cozy-calendar__grid" id="cozy-cal-grid"></div>
                </div>
            </div>

        </div>
    </div>
</main>
<?php get_footer(); ?>
