<?php
/**
 * Block: RSVP
 * Attendees List (Surcharge Cozy Gaming)
 *
 * Affiche la liste des participants inscrits à un événement
 * avec une pile d'avatars et les détails de chaque joueur.
 *
 * @var Tribe__Tickets__Ticket_Object $rsvp       The rsvp ticket object.
 * @var array                         $attendees   List of attendees for the given RSVP.
 */

if ( empty( $attendees ) ) {
    return;
}

// Récupérer les infos utilisateur pour la pile d'avatars
$attendee_users = [];
foreach ( $attendees as $attendee ) {
    $attendee_id   = $attendee['attendee_id'] ?? 0;
    $user_id       = get_post_meta( $attendee_id, '_tribe_tickets_attendee_user_id', true );
    $name          = get_post_meta( $attendee_id, tribe( Tribe__Tickets__RSVP::class )->full_name, true );

    if ( empty( $name ) ) {
        continue;
    }

    $attendee_users[] = [
        'attendee_id' => $attendee_id,
        'user_id'     => $user_id,
        'name'        => $name,
    ];
}

$total = count( $attendee_users );
if ( $total === 0 ) {
    return;
}

$max_stack = 8;
?>
<div class="tribe-tickets__rsvp-attendees-list cozy-rsvp-attendees">
    <h4 class="cozy-rsvp-attendees__title">
        🎮 <?php printf( _n( '%d joueur inscrit', '%d joueurs inscrits', $total, 'cozy-gaming' ), $total ); ?>
    </h4>

    <?php // --- Pile d'avatars --- ?>
    <div class="cozy-rsvp-attendees__stack">
        <?php foreach ( array_slice( $attendee_users, 0, $max_stack ) as $i => $att ) : ?>
            <span class="cozy-rsvp-attendees__avatar" title="<?php echo esc_attr( $att['name'] ); ?>">
                <?php
                if ( ! empty( $att['user_id'] ) ) {
                    echo get_avatar( $att['user_id'], 44 );
                } else {
                    echo get_avatar( 0, 44 );
                }
                ?>
            </span>
        <?php endforeach; ?>

        <?php if ( $total > $max_stack ) : ?>
            <span class="cozy-rsvp-attendees__more">+<?php echo $total - $max_stack; ?></span>
        <?php endif; ?>
    </div>
</div>
