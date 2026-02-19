<?php
/**
 * ============================================================================
 * COZY EVENTS — Charte de Bienveillance
 * ============================================================================
 *
 * Gestion de la charte communautaire que les membres doivent accepter
 * avant de pouvoir s'inscrire à un événement.
 *
 * @package CozyEvents
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Retourne les règles de la charte.
 *
 * @return array
 */
function cozy_get_charter_rules() {
    return apply_filters( 'cozy_charter_rules', array(
        array(
            'icon'  => 'heart',
            'emoji' => '💜',
            'text'  => 'Je m\'engage à être bienveillant·e envers tous les membres, quels que soient leur niveau ou leur expérience.',
        ),
        array(
            'icon'  => 'shield-check',
            'emoji' => '🛡️',
            'text'  => 'Aucune forme de toxicité, harcèlement ou discrimination ne sera tolérée.',
        ),
        array(
            'icon'  => 'clock',
            'emoji' => '⏰',
            'text'  => 'Chacun·e va à son rythme — pas de pression, pas de jugement. Le fun avant tout.',
        ),
        array(
            'icon'  => 'users',
            'emoji' => '🤝',
            'text'  => 'Je respecte les animateurs et les autres participant·es pendant les événements.',
        ),
        array(
            'icon'  => 'message-circle',
            'emoji' => '💬',
            'text'  => 'Je communique de manière respectueuse et constructive, en cas de problème je contacte l\'équipe.',
        ),
    ) );
}


/**
 * Vérifie si un utilisateur a accepté la charte.
 *
 * @param int $user_id
 * @return bool
 */
function cozy_has_accepted_charter( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    return (bool) get_user_meta( $user_id, 'cozy_charter_accepted', true );
}


/**
 * Retourne la date d'acceptation de la charte.
 *
 * @param int $user_id
 * @return string|false
 */
function cozy_get_charter_accepted_date( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    return get_user_meta( $user_id, 'cozy_charter_accepted', true );
}


/**
 * AJAX : Accepter la charte.
 */
function cozy_ajax_accept_charter() {
    check_ajax_referer( 'cozy_events_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Vous devez être connecté.' ) );
    }

    $user_id = get_current_user_id();
    update_user_meta( $user_id, 'cozy_charter_accepted', current_time( 'mysql' ) );

    wp_send_json_success( array(
        'message' => 'Merci ! La charte a été acceptée. Tu peux maintenant t\'inscrire aux événements. 🌿',
    ) );
}
add_action( 'wp_ajax_cozy_accept_charter', 'cozy_ajax_accept_charter' );


/**
 * Rend le HTML de la charte (pour la zone d'inscription).
 *
 * @param int $event_id
 * @return string HTML
 */
function cozy_render_charter_block( $event_id ) {
    $rules = cozy_get_charter_rules();

    ob_start();
    ?>
    <div class="cozy-charter" id="cozy-charter-block">
        <div class="cozy-charter__header">
            <h3 class="cozy-charter__title">
                <i data-lucide="scroll-text"></i>
                Charte de Bienveillance
            </h3>
            <p class="cozy-charter__subtitle">
                Avant de t'inscrire, merci de prendre connaissance de nos règles communautaires.
            </p>
        </div>

        <ul class="cozy-charter__rules">
            <?php foreach ( $rules as $rule ) : ?>
                <li class="cozy-charter__rule">
                    <i data-lucide="<?php echo esc_attr( $rule['icon'] ); ?>" class="lucide"></i>
                    <span><?php echo esc_html( $rule['text'] ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="cozy-charter-field">
            <div class="cozy-charter-field__summary">
                <i data-lucide="check-circle" class="lucide"></i>
                <span class="cozy-charter-field__title">Engagement</span>
            </div>
            <label class="cozy-charter-field__label">
                <input type="checkbox" id="cozy-charter-checkbox">
                <span class="cozy-charter-field__text">
                    J'ai lu et j'accepte la charte de bienveillance de Cozy Grove
                </span>
            </label>
        </div>

        <button class="cozy-btn cozy-btn--primary cozy-charter__accept-btn" id="cozy-accept-charter-btn" data-event-id="<?php echo esc_attr( $event_id ); ?>" disabled>
            <i data-lucide="check"></i>
            Accepter et continuer
        </button>

        <div id="cozy-charter-message" style="display:none;"></div>
    </div>
    <?php
    return ob_get_clean();
}
