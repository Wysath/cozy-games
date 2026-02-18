<?php
/**
 * ============================================================================
 * MODULE : Profils Sociaux (Discord & Twitch)
 * ============================================================================
 * 
 * Permet aux membres de lier leur pseudo Discord et/ou Twitch
 * à leur profil WordPress. Ces informations sont affichées sur
 * les pages d'événements pour faciliter la mise en relation.
 * 
 * @package CozyGaming
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * -----------------------------------------------
 * 1. CHAMPS DANS LE PROFIL ADMIN (back-office)
 * -----------------------------------------------
 * Ajoute les champs Discord et Twitch dans la page
 * de profil utilisateur du back-office WordPress.
 */

/**
 * Affiche les champs Discord et Twitch dans le profil admin
 * 
 * @param WP_User $user L'objet utilisateur affiché
 */
function cozy_social_profile_fields( $user ) {
    $discord = get_user_meta( $user->ID, 'cozy_discord', true );
    $twitch  = get_user_meta( $user->ID, 'cozy_twitch', true );
    ?>
    <h3>🎮 Profil Gaming — Cozy Gaming</h3>
    <table class="form-table">
        <tr>
            <th><label for="cozy_discord">Pseudo Discord</label></th>
            <td>
                <input 
                    type="text" 
                    name="cozy_discord" 
                    id="cozy_discord" 
                    value="<?php echo esc_attr( $discord ); ?>" 
                    class="regular-text"
                    placeholder="Ex : monpseudo"
                />
                <p class="description">Ton nom d'utilisateur Discord (sans le #).</p>
            </td>
        </tr>
        <tr>
            <th><label for="cozy_twitch">Chaîne Twitch</label></th>
            <td>
                <input 
                    type="text" 
                    name="cozy_twitch" 
                    id="cozy_twitch" 
                    value="<?php echo esc_attr( $twitch ); ?>" 
                    class="regular-text"
                    placeholder="Ex : machainetwitch"
                />
                <p class="description">Ton nom de chaîne Twitch (juste le pseudo, sans l'URL).</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'cozy_social_profile_fields' );
add_action( 'edit_user_profile', 'cozy_social_profile_fields' );


/**
 * Sauvegarde les champs Discord et Twitch depuis le profil admin
 * 
 * @param int $user_id L'ID de l'utilisateur sauvegardé
 */
function cozy_save_social_profile_fields( $user_id ) {
    // Vérification des permissions
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    // Sanitize et sauvegarde Discord
    if ( isset( $_POST['cozy_discord'] ) ) {
        $discord = cozy_sanitize_discord( $_POST['cozy_discord'] );
        update_user_meta( $user_id, 'cozy_discord', $discord );
    }

    // Sanitize et sauvegarde Twitch
    if ( isset( $_POST['cozy_twitch'] ) ) {
        $twitch = cozy_sanitize_twitch( $_POST['cozy_twitch'] );
        update_user_meta( $user_id, 'cozy_twitch', $twitch );
    }
}
add_action( 'personal_options_update', 'cozy_save_social_profile_fields' );
add_action( 'edit_user_profile_update', 'cozy_save_social_profile_fields' );


/**
 * -----------------------------------------------
 * 2. VALIDATION & SANITIZATION
 * -----------------------------------------------
 */

/**
 * Sanitize un pseudo Discord
 * Format autorisé : lettres, chiffres, underscores, points (2-32 caractères)
 * 
 * @param string $value La valeur à sanitize
 * @return string La valeur nettoyée ou vide si invalide
 */
function cozy_sanitize_discord( $value ) {
    $value = sanitize_text_field( $value );
    
    // Si vide, on retourne vide (le champ est facultatif)
    if ( empty( $value ) ) {
        return '';
    }

    // Format Discord actuel : 2-32 caractères, lettres, chiffres, underscores, points
    if ( preg_match( '/^[a-zA-Z0-9_.]{2,32}$/', $value ) ) {
        return $value;
    }

    // Ancien format Discord avec discriminant : pseudo#1234
    if ( preg_match( '/^.{2,32}#[0-9]{4}$/', $value ) ) {
        return $value;
    }

    return '';
}

/**
 * Sanitize un pseudo Twitch
 * Format autorisé : lettres, chiffres, underscores (4-25 caractères)
 * 
 * @param string $value La valeur à sanitize
 * @return string La valeur nettoyée ou vide si invalide
 */
function cozy_sanitize_twitch( $value ) {
    $value = sanitize_text_field( $value );
    
    if ( empty( $value ) ) {
        return '';
    }

    // Si l'utilisateur a mis l'URL complète, on extrait le pseudo
    if ( preg_match( '#twitch\.tv/([a-zA-Z0-9_]+)#', $value, $matches ) ) {
        $value = $matches[1];
    }

    // Format Twitch : 4-25 caractères, lettres, chiffres, underscores
    if ( preg_match( '/^[a-zA-Z0-9_]{4,25}$/', $value ) ) {
        return strtolower( $value );
    }

    return '';
}


/**
 * -----------------------------------------------
 * 3. SHORTCODE FRONT-END [cozy_profil_social]
 * -----------------------------------------------
 * Affiche un formulaire sur le front permettant
 * au membre connecté de gérer ses liens sociaux.
 */

/**
 * Shortcode pour afficher/éditer le profil social en front-end
 * 
 * @return string Le HTML du formulaire
 */
function cozy_shortcode_profil_social() {
    // L'utilisateur doit être connecté
    if ( ! is_user_logged_in() ) {
        return '<div class="cozy-social-login-required">
            <p>Tu dois être <strong>connecté(e)</strong> pour gérer ton profil gaming. 
            <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Connecte-toi ici</a>.</p>
        </div>';
    }

    $user_id = get_current_user_id();
    $discord = get_user_meta( $user_id, 'cozy_discord', true );
    $twitch  = get_user_meta( $user_id, 'cozy_twitch', true );
    $user    = wp_get_current_user();

    ob_start();
    ?>
    <div class="cozy-social-profile" id="cozy-social-profile">
        <div class="cozy-social-profile__header">
            <h3>🎮 Mon Profil Gaming</h3>
            <p>Lie tes comptes Discord et Twitch pour que les autres membres puissent te retrouver facilement lors des événements !</p>
        </div>

        <form id="cozy-social-form" class="cozy-social-profile__form" method="post">
            <?php wp_nonce_field( 'cozy_save_social', 'cozy_social_nonce' ); ?>

            <div class="cozy-social-profile__field">
                <label for="cozy_discord_front">
                    <span class="cozy-social-profile__icon cozy-social-profile__icon--discord">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/></svg>
                    </span>
                    Discord
                </label>
                <div class="cozy-social-profile__input-wrapper">
                    <input 
                        type="text" 
                        id="cozy_discord_front" 
                        name="cozy_discord" 
                        value="<?php echo esc_attr( $discord ); ?>" 
                        placeholder="Ton pseudo Discord"
                        maxlength="32"
                        autocomplete="off"
                    />
                    <?php if ( ! empty( $discord ) ) : ?>
                        <span class="cozy-social-profile__status cozy-social-profile__status--linked">✓ Lié</span>
                    <?php else : ?>
                        <span class="cozy-social-profile__status cozy-social-profile__status--unlinked">Non lié</span>
                    <?php endif; ?>
                </div>
                <p class="cozy-social-profile__hint">Ton nom d'utilisateur Discord (ex : monpseudo)</p>
            </div>

            <div class="cozy-social-profile__field">
                <label for="cozy_twitch_front">
                    <span class="cozy-social-profile__icon cozy-social-profile__icon--twitch">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z"/></svg>
                    </span>
                    Twitch
                </label>
                <div class="cozy-social-profile__input-wrapper">
                    <input 
                        type="text" 
                        id="cozy_twitch_front" 
                        name="cozy_twitch" 
                        value="<?php echo esc_attr( $twitch ); ?>" 
                        placeholder="Ton pseudo Twitch"
                        maxlength="25"
                        autocomplete="off"
                    />
                    <?php if ( ! empty( $twitch ) ) : ?>
                        <span class="cozy-social-profile__status cozy-social-profile__status--linked">✓ Lié</span>
                    <?php else : ?>
                        <span class="cozy-social-profile__status cozy-social-profile__status--unlinked">Non lié</span>
                    <?php endif; ?>
                </div>
                <p class="cozy-social-profile__hint">Ton nom de chaîne Twitch (ex : machainetwitch)</p>
            </div>

            <div class="cozy-social-profile__actions">
                <button type="submit" class="cozy-social-profile__btn">
                    💾 Sauvegarder mon profil
                </button>
                <span class="cozy-social-profile__message" id="cozy-social-message"></span>
            </div>
        </form>

        <?php if ( ! empty( $discord ) || ! empty( $twitch ) ) : ?>
        <div class="cozy-social-profile__preview">
            <h4>Aperçu de ton profil</h4>
            <div class="cozy-social-profile__preview-card">
                <div class="cozy-social-profile__preview-avatar">
                    <?php echo get_avatar( $user_id, 64 ); ?>
                </div>
                <div class="cozy-social-profile__preview-info">
                    <strong><?php echo esc_html( $user->display_name ); ?></strong>
                    <div class="cozy-social-profile__preview-links">
                        <?php if ( ! empty( $discord ) ) : ?>
                            <span class="cozy-social-badge cozy-social-badge--discord" title="Discord">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/></svg>
                                <?php echo esc_html( $discord ); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( ! empty( $twitch ) ) : ?>
                            <a href="https://twitch.tv/<?php echo esc_attr( $twitch ); ?>" target="_blank" rel="noopener" class="cozy-social-badge cozy-social-badge--twitch" title="Voir la chaîne Twitch">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z"/></svg>
                                <?php echo esc_html( $twitch ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_profil_social', 'cozy_shortcode_profil_social' );


/**
 * -----------------------------------------------
 * 4. TRAITEMENT AJAX DU FORMULAIRE
 * -----------------------------------------------
 */

/**
 * Enqueue les scripts et styles pour le profil social
 */
function cozy_social_enqueue_assets() {
    // On charge les assets sur tout le front (ils sont légers)
    // Le JS n'agit que si le formulaire est présent
    wp_enqueue_style(
        'cozy-social-profiles',
        get_template_directory_uri() . '/assets/css/cozy-social-profiles.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'cozy-social-profiles',
        get_template_directory_uri() . '/assets/js/cozy-social-profiles.js',
        array(),
        '1.0.0',
        true
    );

    wp_localize_script( 'cozy-social-profiles', 'cozySocial', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'cozy_save_social' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'cozy_social_enqueue_assets' );


/**
 * Traitement AJAX pour sauvegarder les profils sociaux
 */
function cozy_ajax_save_social_profile() {
    // Vérification du nonce
    if ( ! check_ajax_referer( 'cozy_save_social', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => 'Erreur de sécurité. Recharge la page et réessaie.' ) );
    }

    // L'utilisateur doit être connecté
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Tu dois être connecté(e) pour modifier ton profil.' ) );
    }

    $user_id = get_current_user_id();
    $errors  = array();

    // Traitement Discord
    $discord_raw = isset( $_POST['cozy_discord'] ) ? $_POST['cozy_discord'] : '';
    $discord     = cozy_sanitize_discord( $discord_raw );
    
    if ( ! empty( $discord_raw ) && empty( $discord ) ) {
        $errors[] = 'Le pseudo Discord n\'est pas valide. Utilise uniquement des lettres, chiffres, underscores et points (2-32 caractères).';
    }

    // Traitement Twitch
    $twitch_raw = isset( $_POST['cozy_twitch'] ) ? $_POST['cozy_twitch'] : '';
    $twitch     = cozy_sanitize_twitch( $twitch_raw );
    
    if ( ! empty( $twitch_raw ) && empty( $twitch ) ) {
        $errors[] = 'Le pseudo Twitch n\'est pas valide. Utilise uniquement des lettres, chiffres et underscores (4-25 caractères).';
    }

    // S'il y a des erreurs de validation, on les retourne
    if ( ! empty( $errors ) ) {
        wp_send_json_error( array( 'message' => implode( '<br>', $errors ) ) );
    }

    // Sauvegarde en base de données
    update_user_meta( $user_id, 'cozy_discord', $discord );
    update_user_meta( $user_id, 'cozy_twitch', $twitch );

    // Préparer la réponse avec les statuts mis à jour
    wp_send_json_success( array(
        'message' => 'Profil gaming sauvegardé avec succès ! 🎮',
        'discord' => $discord,
        'twitch'  => $twitch,
    ) );
}
add_action( 'wp_ajax_cozy_save_social', 'cozy_ajax_save_social_profile' );


/**
 * -----------------------------------------------
 * 5. FONCTION UTILITAIRE : RÉCUPÉRER LES BADGES SOCIAUX
 * -----------------------------------------------
 * Utilisée par les templates pour afficher les badges
 * Discord/Twitch d'un membre.
 */

/**
 * Retourne le HTML des badges sociaux d'un utilisateur
 * 
 * @param int $user_id L'ID de l'utilisateur
 * @return string Le HTML des badges ou une chaîne vide
 */
function cozy_get_social_badges( $user_id ) {
    if ( empty( $user_id ) ) {
        return '';
    }

    $discord = get_user_meta( $user_id, 'cozy_discord', true );
    $twitch  = get_user_meta( $user_id, 'cozy_twitch', true );

    if ( empty( $discord ) && empty( $twitch ) ) {
        return '';
    }

    $html = '<div class="cozy-social-badges">';

    if ( ! empty( $discord ) ) {
        $html .= '<span class="cozy-social-badge cozy-social-badge--discord" title="Discord : ' . esc_attr( $discord ) . '">';
        $html .= '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/></svg>';
        $html .= ' ' . esc_html( $discord );
        $html .= '</span>';
    }

    if ( ! empty( $twitch ) ) {
        $html .= '<a href="https://twitch.tv/' . esc_attr( $twitch ) . '" target="_blank" rel="noopener" class="cozy-social-badge cozy-social-badge--twitch" title="Voir la chaîne Twitch">';
        $html .= '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z"/></svg>';
        $html .= ' ' . esc_html( $twitch );
        $html .= '</a>';
    }

    $html .= '</div>';

    return $html;
}
