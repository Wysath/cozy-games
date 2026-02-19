<?php
// Enregistrement des meta boxes
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'cozy_event_details',
        'Détails de l\'événement',
        'cozy_event_details_cb',
        'cozy_event',
        'normal',
        'high'
    );
    add_meta_box(
        'cozy_event_registrations',
        'Inscriptions',
        'cozy_event_registrations_cb',
        'cozy_event',
        'side'
    );
});

// Rendu du formulaire admin
function cozy_event_details_cb( $post ) {
    wp_nonce_field( 'cozy_event_save', 'cozy_event_nonce' );
    $date      = get_post_meta( $post->ID, '_cozy_event_date',     true );
    $time      = get_post_meta( $post->ID, '_cozy_event_time',     true );
    $places    = get_post_meta( $post->ID, '_cozy_event_places',   true );
    $link      = get_post_meta( $post->ID, '_cozy_event_link',     true );
    $is_troc   = get_post_meta( $post->ID, '_cozy_event_is_troc',  true );
    ?>
    <table class="form-table">
        <tr>
            <th><label for="cozy_event_date">Date</label></th>
            <td><input type="date" id="cozy_event_date" name="cozy_event_date" value="<?= esc_attr($date) ?>" /></td>
        </tr>
        <tr>
            <th><label for="cozy_event_time">Heure</label></th>
            <td><input type="time" id="cozy_event_time" name="cozy_event_time" value="<?= esc_attr($time) ?>" /></td>
        </tr>
        <tr>
            <th><label for="cozy_event_places">Nombre de places (0 = illimité)</label></th>
            <td><input type="number" id="cozy_event_places" name="cozy_event_places" min="0" value="<?= esc_attr($places ?: 0) ?>" /></td>
        </tr>
        <tr>
            <th><label for="cozy_event_link">Lien (Discord / Lieu)</label></th>
            <td><input type="text" id="cozy_event_link" name="cozy_event_link" value="<?= esc_attr($link) ?>" style="width:100%" /></td>
        </tr>
        <tr>
            <th><label for="cozy_event_is_troc">Event Troc/Échange</label></th>
            <td><input type="checkbox" id="cozy_event_is_troc" name="cozy_event_is_troc" value="1" <?php checked($is_troc, '1') ?> />
            <small>Active le champ "Ce que j'apporte / cherche" à l'inscription</small></td>
        </tr>
    </table>
    <?php
}

// Liste des inscrits dans la sidebar admin
function cozy_event_registrations_cb( $post ) {
    $registrants = get_post_meta( $post->ID, '_cozy_event_registrants', true ) ?: [];
    $places      = (int) get_post_meta( $post->ID, '_cozy_event_places', true );
    $count       = count( $registrants );

    echo '<p><strong>' . $count . '</strong> inscrit(s)';
    if ( $places > 0 ) echo ' / ' . $places . ' places';
    echo '</p>';

    if ( empty($registrants) ) {
        echo '<p><em>Aucune inscription pour l\'instant.</em></p>';
        return;
    }

    echo '<ul style="margin:0;padding-left:16px;">';
    foreach ( $registrants as $reg ) {
        $user = get_userdata( $reg['user_id'] );
        echo '<li>' . esc_html($user->display_name);
        if ( !empty($reg['troc_note']) ) {
            echo '<br><small style="color:#888">' . esc_html($reg['troc_note']) . '</small>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

// Sauvegarde des metas
add_action( 'save_post_cozy_event', function( $post_id ) {
    if (
        ! isset( $_POST['cozy_event_nonce'] ) ||
        ! wp_verify_nonce( $_POST['cozy_event_nonce'], 'cozy_event_save' ) ||
        defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
    ) return;

    $fields = ['cozy_event_date', 'cozy_event_time', 'cozy_event_places', 'cozy_event_link'];
    foreach ( $fields as $f ) {
        if ( isset($_POST[$f]) ) {
            update_post_meta( $post_id, '_' . $f, sanitize_text_field($_POST[$f]) );
        }
    }
    update_post_meta( $post_id, '_cozy_event_is_troc', isset($_POST['cozy_event_is_troc']) ? '1' : '0' );
});
