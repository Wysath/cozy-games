<?php
/**
 * ============================================================================
 * MODULE : Codes Ami par Jeu
 * ============================================================================
 *
 * Permet aux membres d'enregistrer leurs codes ami pour chaque jeu
 * depuis leur profil. Les codes sont affichés sur les pages d'événements
 * pour faciliter la mise en relation entre joueurs.
 *
 * Logique anti-erreur : 
 * - Liste de jeux prédéfinis (pas de saisie libre du nom du jeu)
 * - Placeholder avec le format attendu du code selon la plateforme
 * - Validation du format côté serveur
 * - Champ NON obligatoire
 *
 * @package CozyGaming
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * -----------------------------------------------
 * 1. CONFIGURATION : LISTE DES JEUX ET FORMATS
 * -----------------------------------------------
 * Chaque jeu a un slug, un nom, une plateforme,
 * un placeholder d'exemple et un pattern de validation.
 */

/**
 * Retourne la liste des jeux supportés avec leurs infos de validation
 *
 * @return array Liste des jeux configurés
 */
function cozy_get_supported_games() {
    return apply_filters( 'cozy_supported_games', array(
        'animal-crossing'   => array(
            'name'        => 'Animal Crossing: New Horizons',
            'platform'    => 'Nintendo Switch',
            'icon'        => '🏝️',
            'placeholder' => 'SW-1234-5678-9012',
            'pattern'     => '/^SW-\d{4}-\d{4}-\d{4}$/',
            'help'        => 'Format : SW-XXXX-XXXX-XXXX',
        ),
        'pokemon-sv'       => array(
            'name'        => 'Pokémon Écarlate / Violet',
            'platform'    => 'Nintendo Switch',
            'icon'        => '⚡',
            'placeholder' => 'SW-1234-5678-9012',
            'pattern'     => '/^SW-\d{4}-\d{4}-\d{4}$/',
            'help'        => 'Format : SW-XXXX-XXXX-XXXX',
        ),
        'mario-kart'       => array(
            'name'        => 'Mario Kart 8 Deluxe',
            'platform'    => 'Nintendo Switch',
            'icon'        => '🏎️',
            'placeholder' => 'SW-1234-5678-9012',
            'pattern'     => '/^SW-\d{4}-\d{4}-\d{4}$/',
            'help'        => 'Format : SW-XXXX-XXXX-XXXX',
        ),
        'stardew-valley'   => array(
            'name'        => 'Stardew Valley',
            'platform'    => 'Multi (Steam / Switch)',
            'icon'        => '🌾',
            'placeholder' => 'Pseudo Steam ou SW-1234-5678-9012',
            'pattern'     => '/^(SW-\d{4}-\d{4}-\d{4}|[a-zA-Z0-9_\-]{2,32})$/',
            'help'        => 'Pseudo Steam OU code ami Switch',
        ),
        'minecraft'        => array(
            'name'        => 'Minecraft',
            'platform'    => 'Multi',
            'icon'        => '⛏️',
            'placeholder' => 'MonPseudo',
            'pattern'     => '/^[a-zA-Z0-9_]{3,16}$/',
            'help'        => 'Ton pseudo Minecraft (3-16 caractères)',
        ),
        'sky-cotl'         => array(
            'name'        => 'Sky: Children of the Light',
            'platform'    => 'Multi',
            'icon'        => '🕯️',
            'placeholder' => 'Lien d\'invitation ou pseudo',
            'pattern'     => '/^.{2,100}$/',
            'help'        => 'Lien QR ou pseudo en jeu',
        ),
        'overcooked'       => array(
            'name'        => 'Overcooked! 2',
            'platform'    => 'Multi (Steam / Switch)',
            'icon'        => '🍳',
            'placeholder' => 'Pseudo Steam ou SW-1234-5678-9012',
            'pattern'     => '/^(SW-\d{4}-\d{4}-\d{4}|[a-zA-Z0-9_\-]{2,32})$/',
            'help'        => 'Pseudo Steam OU code ami Switch',
        ),
        'it-takes-two'     => array(
            'name'        => 'It Takes Two',
            'platform'    => 'Steam / EA',
            'icon'        => '💕',
            'placeholder' => 'Pseudo Steam ou EA',
            'pattern'     => '/^[a-zA-Z0-9_\-\s]{2,32}$/',
            'help'        => 'Pseudo Steam ou EA (2-32 caractères)',
        ),
    ) );
}


/**
 * -----------------------------------------------
 * 2. GESTION DES DONNÉES UTILISATEUR
 * -----------------------------------------------
 * Les codes ami sont stockés en user_meta sous forme
 * de tableau sérialisé : cozy_friend_codes
 * Structure : array( 'slug-jeu' => 'CODE-AMI', ... )
 */

/**
 * Récupère les codes ami d'un utilisateur
 *
 * @param int $user_id L'ID de l'utilisateur
 * @return array Les codes ami indexés par slug de jeu
 */
function cozy_get_friend_codes( $user_id ) {
    $codes = get_user_meta( $user_id, 'cozy_friend_codes', true );
    return is_array( $codes ) ? $codes : array();
}

/**
 * Sauvegarde les codes ami d'un utilisateur
 *
 * @param int   $user_id L'ID de l'utilisateur
 * @param array $codes   Les codes ami à sauvegarder
 * @return bool Succès ou échec
 */
function cozy_save_friend_codes( $user_id, $codes ) {
    return update_user_meta( $user_id, 'cozy_friend_codes', $codes );
}

/**
 * Valide un code ami selon le format attendu du jeu
 *
 * @param string $game_slug Le slug du jeu
 * @param string $code      Le code à valider
 * @return bool True si le code est valide ou vide
 */
function cozy_validate_friend_code( $game_slug, $code ) {
    // Un champ vide est valide (non obligatoire)
    if ( empty( $code ) ) {
        return true;
    }

    $games = cozy_get_supported_games();

    if ( ! isset( $games[ $game_slug ] ) ) {
        return false;
    }

    return (bool) preg_match( $games[ $game_slug ]['pattern'], $code );
}


/**
 * -----------------------------------------------
 * 3. CHAMPS DANS LE PROFIL ADMIN (back-office)
 * -----------------------------------------------
 */

/**
 * Affiche les champs de codes ami dans le profil admin
 *
 * @param WP_User $user L'utilisateur affiché
 */
function cozy_friend_codes_admin_fields( $user ) {
    $codes = cozy_get_friend_codes( $user->ID );
    $games = cozy_get_supported_games();
    ?>
    <h3>🎮 Codes Ami — Cozy Gaming</h3>
    <p class="description">Codes ami du membre pour chaque jeu. Ces champs sont facultatifs.</p>
    <table class="form-table">
        <?php foreach ( $games as $slug => $game ) : ?>
        <tr>
            <th>
                <label for="cozy_fc_<?php echo esc_attr( $slug ); ?>">
                    <?php echo $game['icon']; ?> <?php echo esc_html( $game['name'] ); ?>
                </label>
            </th>
            <td>
                <input 
                    type="text" 
                    name="cozy_friend_codes[<?php echo esc_attr( $slug ); ?>]" 
                    id="cozy_fc_<?php echo esc_attr( $slug ); ?>"
                    value="<?php echo esc_attr( $codes[ $slug ] ?? '' ); ?>" 
                    class="regular-text"
                    placeholder="<?php echo esc_attr( $game['placeholder'] ); ?>"
                />
                <p class="description"><?php echo esc_html( $game['help'] ); ?> — <?php echo esc_html( $game['platform'] ); ?></p>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php
}
add_action( 'show_user_profile', 'cozy_friend_codes_admin_fields' );
add_action( 'edit_user_profile', 'cozy_friend_codes_admin_fields' );


/**
 * Sauvegarde les codes ami depuis le profil admin
 *
 * @param int $user_id L'ID de l'utilisateur
 */
function cozy_save_friend_codes_admin( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    if ( ! isset( $_POST['cozy_friend_codes'] ) || ! is_array( $_POST['cozy_friend_codes'] ) ) {
        return;
    }

    $games     = cozy_get_supported_games();
    $new_codes = array();

    foreach ( $_POST['cozy_friend_codes'] as $slug => $code ) {
        $code = sanitize_text_field( $code );

        if ( ! isset( $games[ $slug ] ) ) {
            continue;
        }

        // Valider le format si un code est saisi
        if ( ! empty( $code ) && ! cozy_validate_friend_code( $slug, $code ) ) {
            continue; 
        }

        if ( ! empty( $code ) ) {
            $new_codes[ $slug ] = $code;
        }
    }

    cozy_save_friend_codes( $user_id, $new_codes );
}
add_action( 'personal_options_update', 'cozy_save_friend_codes_admin' );
add_action( 'edit_user_profile_update', 'cozy_save_friend_codes_admin' );


/**
 * -----------------------------------------------
 * 4. SHORTCODE FRONT-END [cozy_codes_ami]
 * -----------------------------------------------
 * Formulaire front-end pour que les membres gèrent
 * leurs codes ami sans passer par l'admin.
 */

/**
 * Shortcode pour gérer les codes ami en front-end
 *
 * @return string Le HTML du formulaire
 */
function cozy_shortcode_friend_codes() {
    if ( ! is_user_logged_in() ) {
        return '<div class="cozy-social-login-required">
            <p>Tu dois être <strong>connecté(e)</strong> pour gérer tes codes ami. 
            <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Connecte-toi ici</a>.</p>
        </div>';
    }

    $user_id = get_current_user_id();
    $codes   = cozy_get_friend_codes( $user_id );
    $games   = cozy_get_supported_games();

    ob_start();
    ?>
    <div class="cozy-friend-codes" id="cozy-friend-codes">
        <div class="cozy-friend-codes__header">
            <h3>🎮 Mes Codes Ami</h3>
            <p>Ajoute tes codes ami pour que les autres joueurs puissent te retrouver facilement dans chaque jeu. Tous les champs sont <strong>facultatifs</strong>.</p>
        </div>

        <form id="cozy-friend-codes-form" class="cozy-friend-codes__form" method="post">
            <?php wp_nonce_field( 'cozy_save_friend_codes', 'cozy_fc_nonce' ); ?>

            <?php foreach ( $games as $slug => $game ) : 
                $current_code = $codes[ $slug ] ?? '';
                $has_code = ! empty( $current_code );
            ?>
            <div class="cozy-friend-codes__field" data-game="<?php echo esc_attr( $slug ); ?>">
                <label for="cozy_fc_front_<?php echo esc_attr( $slug ); ?>">
                    <span class="cozy-friend-codes__game-icon"><?php echo $game['icon']; ?></span>
                    <span class="cozy-friend-codes__game-info">
                        <strong><?php echo esc_html( $game['name'] ); ?></strong>
                        <small><?php echo esc_html( $game['platform'] ); ?></small>
                    </span>
                </label>
                <div class="cozy-friend-codes__input-wrapper">
                    <input 
                        type="text" 
                        id="cozy_fc_front_<?php echo esc_attr( $slug ); ?>"
                        name="cozy_friend_codes[<?php echo esc_attr( $slug ); ?>]"
                        value="<?php echo esc_attr( $current_code ); ?>"
                        placeholder="<?php echo esc_attr( $game['placeholder'] ); ?>"
                        autocomplete="off"
                        data-pattern="<?php echo esc_attr( $game['pattern'] ); ?>"
                    />
                    <?php if ( $has_code ) : ?>
                        <span class="cozy-friend-codes__status cozy-friend-codes__status--saved">✓</span>
                    <?php endif; ?>
                </div>
                <p class="cozy-friend-codes__hint"><?php echo esc_html( $game['help'] ); ?></p>
            </div>
            <?php endforeach; ?>

            <div class="cozy-friend-codes__actions">
                <button type="submit" class="cozy-friend-codes__btn">
                    💾 Sauvegarder mes codes
                </button>
                <span class="cozy-friend-codes__message" id="cozy-fc-message"></span>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_codes_ami', 'cozy_shortcode_friend_codes' );


/**
 * -----------------------------------------------
 * 5. TRAITEMENT AJAX
 * -----------------------------------------------
 */

/**
 * Enqueue les scripts et styles pour les codes ami
 */
function cozy_friend_codes_enqueue_assets() {
    wp_enqueue_style(
        'cozy-friend-codes',
        get_template_directory_uri() . '/assets/css/cozy-friend-codes.css',
        array(),
        '1.1.0'
    );

    wp_enqueue_script(
        'cozy-friend-codes',
        get_template_directory_uri() . '/assets/js/cozy-friend-codes.js',
        array(),
        '1.1.0',
        true
    );

    wp_localize_script( 'cozy-friend-codes', 'cozyFriendCodes', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'cozy_save_friend_codes' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'cozy_friend_codes_enqueue_assets' );


/**
 * Traitement AJAX pour sauvegarder les codes ami
 */
function cozy_ajax_save_friend_codes() {
    if ( ! check_ajax_referer( 'cozy_save_friend_codes', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => 'Erreur de sécurité. Recharge la page et réessaie.' ) );
    }

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Tu dois être connecté(e).' ) );
    }

    $user_id = get_current_user_id();
    $games   = cozy_get_supported_games();
    $errors  = array();
    $saved   = array();

    if ( ! isset( $_POST['cozy_friend_codes'] ) || ! is_array( $_POST['cozy_friend_codes'] ) ) {
        // Aucun code envoyé : on vide tout
        cozy_save_friend_codes( $user_id, array() );
        wp_send_json_success( array(
            'message' => 'Codes ami mis à jour ! 🎮',
            'codes'   => array(),
        ) );
    }

    foreach ( $_POST['cozy_friend_codes'] as $slug => $code ) {
        $code = sanitize_text_field( $code );

        if ( ! isset( $games[ $slug ] ) ) {
            continue;
        }

        if ( empty( $code ) ) {
            continue; // Champ vide = pas de code pour ce jeu
        }

        if ( ! cozy_validate_friend_code( $slug, $code ) ) {
            $errors[] = sprintf(
                '<strong>%s %s</strong> : format invalide. %s',
                $games[ $slug ]['icon'],
                esc_html( $games[ $slug ]['name'] ),
                esc_html( $games[ $slug ]['help'] )
            );
            continue;
        }

        $saved[ $slug ] = $code;
    }

    // S'il y a des erreurs, on ne sauvegarde pas
    if ( ! empty( $errors ) ) {
        wp_send_json_error( array( 'message' => implode( '<br>', $errors ) ) );
    }

    cozy_save_friend_codes( $user_id, $saved );

    wp_send_json_success( array(
        'message' => 'Codes ami sauvegardés avec succès ! 🎮',
        'codes'   => $saved,
    ) );
}
add_action( 'wp_ajax_cozy_save_friend_codes', 'cozy_ajax_save_friend_codes' );


/**
 * -----------------------------------------------
 * 6. AFFICHAGE DES CODES AMI SUR LES ÉVÉNEMENTS
 * -----------------------------------------------
 * Affiche les codes ami pertinents des participants
 * sur la page de l'événement. On utilise les catégories
 * d'événement ou les tags pour détecter le jeu concerné.
 */

/**
 * Retourne le HTML des codes ami d'un utilisateur
 * Optionnellement filtré par jeu si un slug de jeu est fourni.
 *
 * @param int         $user_id   L'ID de l'utilisateur
 * @param string|null $game_slug Optionnel : filtrer par jeu
 * @return string Le HTML des codes ou chaîne vide
 */
function cozy_get_friend_code_badges( $user_id, $game_slug = null ) {
    if ( empty( $user_id ) ) {
        return '';
    }

    $codes = cozy_get_friend_codes( $user_id );
    if ( empty( $codes ) ) {
        return '';
    }

    $games = cozy_get_supported_games();
    $html  = '';

    foreach ( $codes as $slug => $code ) {
        if ( ! isset( $games[ $slug ] ) ) {
            continue;
        }

        // Si on filtre par jeu, ne montrer que celui-ci
        if ( $game_slug && $slug !== $game_slug ) {
            continue;
        }

        $game = $games[ $slug ];
        $html .= sprintf(
            '<span class="cozy-fc-badge" title="%s — %s">%s <code>%s</code></span>',
            esc_attr( $game['name'] ),
            esc_attr( $game['platform'] ),
            $game['icon'],
            esc_html( $code )
        );
    }

    if ( empty( $html ) ) {
        return '';
    }

    return '<div class="cozy-fc-badges">' . $html . '</div>';
}
