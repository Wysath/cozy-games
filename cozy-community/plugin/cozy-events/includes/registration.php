<?php
/**
 * Helpers
 */
function cozy_get_registrants( $event_id ) {
    return get_post_meta( $event_id, '_cozy_event_registrants', true ) ?: [];
}

function cozy_is_registered( $event_id, $user_id ) {
    $regs = cozy_get_registrants( $event_id );
    foreach ( $regs as $r ) {
        if ( (int)$r['user_id'] === (int)$user_id ) return true;
    }
    return false;
}

function cozy_get_places_left( $event_id ) {
    $places = (int) get_post_meta( $event_id, '_cozy_event_places', true );
    if ( $places === 0 ) return -1; // illimité
    $count = count( cozy_get_registrants($event_id) );
    return max(0, $places - $count);
}

/**
 * AJAX : S'inscrire
 */
add_action( 'wp_ajax_cozy_register', function() {
    check_ajax_referer( 'cozy_events_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error(['message' => 'Vous devez être connecté pour vous inscrire.']);
    }

    $event_id  = (int) $_POST['event_id'];
    $user_id   = get_current_user_id();
    $troc_note = sanitize_textarea_field( $_POST['troc_note'] ?? '' );

    if ( cozy_is_registered($event_id, $user_id) ) {
        wp_send_json_error(['message' => 'Vous êtes déjà inscrit(e) à cet événement.']);
    }

    $places_left = cozy_get_places_left($event_id);
    if ( $places_left === 0 ) {
        wp_send_json_error(['message' => 'Désolé, il n\'y a plus de places disponibles.']);
    }

    $regs = cozy_get_registrants($event_id);
    $regs[] = [
        'user_id'    => $user_id,
        'troc_note'  => $troc_note,
        'registered' => current_time('mysql'),
    ];
    update_post_meta( $event_id, '_cozy_event_registrants', $regs );

    // Email de confirmation
    $user  = get_userdata($user_id);
    $event = get_post($event_id);
    wp_mail(
        $user->user_email,
        '🎮 Inscription confirmée : ' . get_the_title($event_id),
        "Bonjour {$user->display_name},\n\nVotre inscription à l'événement \"{$event->post_title}\" est confirmée !\n\nÀ bientôt dans la guilde Cozy Grove 🌿"
    );

    $new_left = cozy_get_places_left($event_id);
    wp_send_json_success([
        'message'      => 'Inscription confirmée ! Un email vous a été envoyé.',
        'places_left'  => $new_left === -1 ? null : $new_left,
        'count'        => count($regs),
    ]);
});

/**
 * AJAX : Se désinscrire
 */
add_action( 'wp_ajax_cozy_unregister', function() {
    check_ajax_referer( 'cozy_events_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error(['message' => 'Non autorisé.']);
    }

    $event_id = (int) $_POST['event_id'];
    $user_id  = get_current_user_id();
    $regs     = cozy_get_registrants($event_id);

    $new_regs = array_filter($regs, fn($r) => (int)$r['user_id'] !== $user_id);
    $new_regs = array_values($new_regs); // ré-indexer
    update_post_meta( $event_id, '_cozy_event_registrants', $new_regs );

    $new_left = cozy_get_places_left($event_id);
    wp_send_json_success([
        'message'     => 'Vous avez été désinscrit(e) de l\'événement.',
        'places_left' => $new_left === -1 ? null : $new_left,
        'count'       => count($new_regs),
    ]);
});
