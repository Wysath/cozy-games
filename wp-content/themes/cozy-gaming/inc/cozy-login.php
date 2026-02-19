<?php
/**
 * ============================================================================
 * MODULE : Personnalisation Login & Inscription
 * ============================================================================
 *
 * Personnalise les pages de connexion et d'inscription WordPress
 * avec le branding Cozy Grove : logo, couleurs, styles, et
 * améliorations UX (champ mot de passe à l'inscription, etc.)
 *
 * @package CozyGaming
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * -----------------------------------------------
 * 1. ACTIVER L'INSCRIPTION DES UTILISATEURS
 * -----------------------------------------------
 * S'assure que l'option WordPress « Tout le monde peut s'inscrire »
 * est activée (réglage dans Réglages > Général).
 */
function cozy_ensure_registration_enabled() {
    if ( ! get_option( 'users_can_register' ) ) {
        update_option( 'users_can_register', 1 );
    }
    // Rôle par défaut : subscriber (le plus restrictif)
    if ( get_option( 'default_role' ) !== 'subscriber' ) {
        update_option( 'default_role', 'subscriber' );
    }
}
add_action( 'admin_init', 'cozy_ensure_registration_enabled' );


/**
 * -----------------------------------------------
 * 2. AJOUTER UN CHAMP MOT DE PASSE À L'INSCRIPTION
 * -----------------------------------------------
 * Par défaut, WordPress envoie un lien par email pour
 * définir le mot de passe. On ajoute un champ directement
 * dans le formulaire pour une meilleure expérience.
 */

/**
 * Affiche les champs mot de passe dans le formulaire d'inscription
 */
function cozy_register_form_password_fields() {
    ?>
    <p>
        <label for="pass1"><?php esc_html_e( 'Mot de passe', 'cozy-gaming' ); ?></label>
        <input type="password" name="pass1" id="pass1" class="input" size="20" autocomplete="new-password" required="required" />
    </p>
    <p>
        <label for="pass2"><?php esc_html_e( 'Confirmer le mot de passe', 'cozy-gaming' ); ?></label>
        <input type="password" name="pass2" id="pass2" class="input" size="20" autocomplete="new-password" required="required" />
    </p>
    <p class="cozy-password-hint">
        <?php esc_html_e( 'Le mot de passe doit contenir au moins 8 caractères.', 'cozy-gaming' ); ?>
    </p>
    <?php
}
add_action( 'register_form', 'cozy_register_form_password_fields' );

/**
 * Valide les champs mot de passe à l'inscription
 *
 * @param WP_Error $errors               L'objet d'erreurs
 * @param string   $sanitized_user_login  Le login sanitizé
 * @param string   $user_email            L'email de l'utilisateur
 * @return WP_Error
 */
function cozy_register_validate_password( $errors, $sanitized_user_login, $user_email ) {
    if ( empty( $_POST['pass1'] ) || empty( $_POST['pass2'] ) ) {
        $errors->add( 'empty_password', '<strong>Erreur</strong> : Merci de saisir un mot de passe.' );
        return $errors;
    }

    if ( $_POST['pass1'] !== $_POST['pass2'] ) {
        $errors->add( 'password_mismatch', '<strong>Erreur</strong> : Les mots de passe ne correspondent pas.' );
        return $errors;
    }

    if ( strlen( $_POST['pass1'] ) < 8 ) {
        $errors->add( 'password_too_short', '<strong>Erreur</strong> : Le mot de passe doit contenir au moins 8 caractères.' );
        return $errors;
    }

    return $errors;
}
add_filter( 'registration_errors', 'cozy_register_validate_password', 10, 3 );

/**
 * Définit le mot de passe choisi par l'utilisateur après inscription
 *
 * @param int $user_id L'ID du nouvel utilisateur
 */
function cozy_register_set_password( $user_id ) {
    if ( ! empty( $_POST['pass1'] ) ) {
        wp_set_password( $_POST['pass1'], $user_id );

        // Désactiver l'envoi de l'email de définition de mot de passe
        // car l'utilisateur l'a déjà défini
        update_user_meta( $user_id, 'default_password_nag', false );
    }
}
add_action( 'user_register', 'cozy_register_set_password' );

/**
 * Masquer le message par défaut « Un email vous sera envoyé pour définir votre mot de passe »
 * puisqu'on a ajouté le champ directement dans le formulaire.
 */
function cozy_hide_default_password_notice() {
    ?>
    <style>#reg_passmail { display: none !important; }</style>
    <?php
}
add_action( 'login_head', 'cozy_hide_default_password_notice' );


/**
 * Rediriger vers la page d'accueil après inscription réussie
 * au lieu de la page de login avec message "check email".
 *
 * Note : le filtre `registration_redirect` ne transmet qu'un argument ($redirect_to).
 */
function cozy_registration_redirect( $redirect_to ) {
    // Connexion automatique après inscription
    $user_login = isset( $_POST['user_login'] ) ? sanitize_user( $_POST['user_login'] ) : '';
    $user       = get_user_by( 'login', $user_login );

    if ( $user && ! empty( $_POST['pass1'] ) ) {
        wp_set_auth_cookie( $user->ID, true );
        return home_url();
    }

    return $redirect_to;
}
add_filter( 'registration_redirect', 'cozy_registration_redirect' );


/**
 * -----------------------------------------------
 * 3. BRANDING : LOGO ET LIENS
 * -----------------------------------------------
 * Remplace le logo WordPress par celui de Cozy Grove
 * et modifie les liens/textes associés.
 */

/**
 * Redirige le lien du logo vers la page d'accueil (au lieu de wordpress.org)
 */
function cozy_login_headerurl() {
    return home_url();
}
add_filter( 'login_headerurl', 'cozy_login_headerurl' );

/**
 * Change le texte du lien logo
 */
function cozy_login_headertext() {
    return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'cozy_login_headertext' );

/**
 * Personnalise le titre de la page login/register
 */
function cozy_login_title( $login_title, $title ) {
    return $title . ' — ' . get_bloginfo( 'name' );
}
add_filter( 'login_title', 'cozy_login_title', 10, 2 );


/**
 * -----------------------------------------------
 * 4. STYLES PERSONNALISÉS DES FORMULAIRES
 * -----------------------------------------------
 * Injecte les styles Cozy Grove sur la page wp-login.php
 */
function cozy_login_enqueue_styles() {
    wp_enqueue_style(
        'cozy-login-styles',
        get_template_directory_uri() . '/assets/css/cozy-login.css',
        array( 'login' ),
        '1.3.0'
    );
}
add_action( 'login_enqueue_scripts', 'cozy_login_enqueue_styles' );


/**
 * -----------------------------------------------
 * 5. AJOUTER DES CLASSES AU BODY LOGIN
 * -----------------------------------------------
 */
function cozy_login_body_class( $classes ) {
    $classes[] = 'cozy-gaming-login';
    return $classes;
}
add_filter( 'login_body_class', 'cozy_login_body_class' );


/**
 * -----------------------------------------------
 * 6. PERSONNALISER LE MESSAGE D'INSCRIPTION
 * -----------------------------------------------
 */
function cozy_login_message( $message ) {
    // Sur la page d'inscription
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'register' ) {
        $message = '<p class="message cozy-register-message"><i data-lucide="gamepad-2"></i> Rejoins la guilde Cozy Grove !<br><small>Crée ton compte pour t\'inscrire aux événements.</small></p>';
    }
    return $message;
}
add_filter( 'login_message', 'cozy_login_message' );


/**
 * -----------------------------------------------
 * 7. SÉCURITÉ : MASQUER LES MESSAGES D'ERREUR TROP PRÉCIS
 * -----------------------------------------------
 * Empêche les attaquants de savoir si un identifiant existe ou non.
 */
function cozy_login_error_messages( $error ) {
    global $errors;

    if ( ! is_wp_error( $errors ) ) {
        return $error;
    }

    $err_codes = $errors->get_error_codes();

    // Sur la page de connexion uniquement, masquer les détails
    if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'register' ) {
        if ( in_array( 'invalid_username', $err_codes ) || in_array( 'incorrect_password', $err_codes ) ) {
            return '<strong>Erreur</strong> : Identifiant ou mot de passe incorrect.';
        }
    }

    return $error;
}
add_filter( 'login_errors', 'cozy_login_error_messages' );
