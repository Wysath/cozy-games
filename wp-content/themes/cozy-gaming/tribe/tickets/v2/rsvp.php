<?php
/**
 * Block: RSVP (Surcharge pour l'association Cozy Gaming)
 *
 * @var Tribe__Tickets__Editor__Template $this
 * @var WP_Post|int                      $post_id       The post object or ID.
 * @var boolean                          $has_rsvps     True if there are RSVPs.
 * @var array                            $active_rsvps  An array containing the active RSVPs.
 * @var string                           $block_html_id The unique HTML id for the block.
 */

// Protection anti-récursion
static $cozy_rsvp_rendering = false;
if ( $cozy_rsvp_rendering ) {
    return;
}
$cozy_rsvp_rendering = true;

// We don't display anything if there is no RSVP.
if ( ! $has_rsvps ) {
    $cozy_rsvp_rendering = false;
    return false;
}

// Bail if there are no active RSVP.
if ( empty( $active_rsvps ) ) {
    $cozy_rsvp_rendering = false;
    return;
}

// 
$current_user_id = get_current_user_id();
$deja_inscrit = false;

// Si l'utilisateur est connecté, on vérifie ses billets
if ( $current_user_id ) {
    
    // On cherche dans la BDD s'il existe un "billet" (attendee) lié à cet utilisateur POUR cet événement
    $billets_utilisateur = get_posts( array(
        'post_type'      => 'tribe_rsvp_attendees', // Le type de post interne du plugin
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => '_tribe_rsvp_event', // L'ID de l'événement en cours
                'value' => $post_id,
            ),
            array(
                'key'   => '_tribe_tickets_attendee_user_id', // L'ID du membre
                'value' => $current_user_id,
            ),
        ),
        'fields' => 'ids',
    ) );

    // Si on trouve un billet, ça veut dire qu'il est déjà inscrit
    if ( ! empty( $billets_utilisateur ) ) {
        $deja_inscrit = true;
    }
}
?>

<div id="<?php echo esc_attr( $block_html_id ); ?>" class="tribe-common event-tickets">
    
    <?php if ( $deja_inscrit ) : ?>
        
        <div class="cozy-message-succes" style="background-color: #e8f5e9; border-left: 5px solid #4caf50; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
            <h3 style="color: #2e7d32; margin-top: 0;">Place réservée !</h3>
            <p style="margin-bottom: 0;">Prépare ton plaid et ta boisson chaude, ton inscription à cet événement est validée. On se voit très vite !</p>
        </div>

    <?php elseif ( ! is_user_logged_in() ) : ?>

        <div class="cozy-message-login" style="background-color: #fff3e0; border-left: 5px solid #ff9800; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
            <p style="margin-bottom: 0;">Tu dois être <strong>membre de la communauté</strong> pour t'inscrire aux événements. <a href="<?php echo wp_login_url(); ?>">Connecte-toi ici</a>.</p>
        </div>

    <?php else : ?>

        <?php foreach ( $active_rsvps as $rsvp ) : ?>
            <div class="tribe-tickets__rsvp-wrapper" data-rsvp-id="<?php echo esc_attr( $rsvp->ID ); ?>">
                <?php $this->template( 'v2/components/loader/loader' ); ?>
                <?php $this->template( 'v2/rsvp/content', [ 'rsvp' => $rsvp ] ); ?>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>
<?php $cozy_rsvp_rendering = false; ?>