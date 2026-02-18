<?php
/**
 * Block: RSVP
 * Attendees - Attendee (Surcharge Cozy Gaming)
 *
 * Affiche les détails du participant avec ses badges sociaux
 * Discord et Twitch pour faciliter la mise en relation.
 *
 * @var Tribe__Tickets__Ticket_Object $rsvp The rsvp ticket object.
 * @var array $attendees List of attendees for the given order.
 * @var int $attendee_id The attendee ID.
 */

if ( empty( $attendees ) || empty( $attendee_id ) ) {
    return;
}

// Récupérer le nom du participant
$attendee_name = get_post_meta( $attendee_id, tribe( Tribe__Tickets__RSVP::class )->full_name, true );
if ( empty( $attendee_name ) ) {
    return;
}

// Récupérer l'ID utilisateur WordPress associé à ce participant
$attendee_user_id = get_post_meta( $attendee_id, '_tribe_tickets_attendee_user_id', true );

?>
<div class="tec-tickets__attendees-list-item-attendee-details tribe-common-b1 cozy-attendee-card">
    <div class="cozy-attendee-card__main">
        <?php if ( ! empty( $attendee_user_id ) ) : ?>
            <div class="cozy-attendee-card__avatar">
                <?php echo get_avatar( $attendee_user_id, 36 ); ?>
            </div>
        <?php endif; ?>
        
        <div class="cozy-attendee-card__info">
            <div class="tec-tickets__attendees-list-item-attendee-details-name tribe-common-b1--bold">
                <?php echo esc_html( $attendee_name ); ?>
            </div>

            <?php
            // Afficher les badges sociaux si l'utilisateur a lié ses comptes
            if ( ! empty( $attendee_user_id ) && function_exists( 'cozy_get_social_badges' ) ) {
                echo cozy_get_social_badges( $attendee_user_id );
            }

            // Afficher les codes ami du participant
            if ( ! empty( $attendee_user_id ) && function_exists( 'cozy_get_friend_code_badges' ) ) {
                echo cozy_get_friend_code_badges( $attendee_user_id );
            }
            ?>
        </div>
    </div>
</div>
