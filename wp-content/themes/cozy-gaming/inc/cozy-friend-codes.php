<?php
/**
 * ============================================================================
 * MODULE : Codes Ami par Plateforme / Service
 * ============================================================================
 *
 * Permet aux membres d'enregistrer leurs identifiants gaming
 * pour chaque plateforme ou service de jeu (Switch, PSN, Xbox,
 * Steam, Riot, EA, Epic, etc.) depuis leur profil.
 *
 * Les codes sont affichés sur les pages d'événements
 * pour faciliter la mise en relation entre joueurs.
 *
 * Logique anti-erreur :
 * - Liste de plateformes prédéfinies
 * - Placeholder avec le format attendu
 * - Validation du format côté serveur
 * - Champ NON obligatoire
 *
 * @package CozyGaming
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * -----------------------------------------------
 * 1. CONFIGURATION : PLATEFORMES & FORMATS
 * -----------------------------------------------
 * Chaque plateforme a un slug, un nom, une icône Lucide,
 * une couleur de marque, un placeholder et un pattern.
 */

/**
 * Retourne la liste des plateformes/services gaming supportés
 *
 * @return array Liste des plateformes configurées
 */
function cozy_get_supported_platforms() {
    return apply_filters( 'cozy_supported_platforms', array(
        'nintendo-switch' => array(
            'name'        => 'Nintendo Switch',
            'icon'        => 'gamepad-2',
            'color'       => '#e60012',
            'placeholder' => 'SW-1234-5678-9012',
            'pattern'     => '/^SW-\d{4}-\d{4}-\d{4}$/',
            'help'        => 'Format : SW-XXXX-XXXX-XXXX',
        ),
        'psn'             => array(
            'name'        => 'PlayStation (PSN)',
            'icon'        => 'gamepad-2',
            'color'       => '#003087',
            'placeholder' => 'MonPseudo_PSN',
            'pattern'     => '/^[a-zA-Z][a-zA-Z0-9_\-]{2,15}$/',
            'help'        => 'ID PSN (3-16 caractères, commence par une lettre)',
        ),
        'xbox'            => array(
            'name'        => 'Xbox (Gamertag)',
            'icon'        => 'gamepad-2',
            'color'       => '#107c10',
            'placeholder' => 'MonGamertag',
            'pattern'     => '/^[a-zA-Z][a-zA-Z0-9 ]{0,14}$/',
            'help'        => 'Gamertag Xbox (1-15 caractères)',
        ),
        'steam'           => array(
            'name'        => 'Steam',
            'icon'        => 'monitor',
            'color'       => '#1b2838',
            'placeholder' => 'MonPseudoSteam ou code ami',
            'pattern'     => '/^[a-zA-Z0-9_\-]{2,32}$/',
            'help'        => 'Pseudo Steam ou code ami numérique',
        ),
        'riot-games'      => array(
            'name'        => 'Riot Games',
            'icon'        => 'swords',
            'color'       => '#d32936',
            'placeholder' => 'Pseudo#TAG',
            'pattern'     => '/^.{2,16}#[a-zA-Z0-9]{2,5}$/',
            'help'        => 'Riot ID : Pseudo#TAG (ex: Player#EUW)',
        ),
        'ea'              => array(
            'name'        => 'EA (Origin)',
            'icon'        => 'zap',
            'color'       => '#ff4747',
            'placeholder' => 'MonPseudoEA',
            'pattern'     => '/^[a-zA-Z0-9_\-]{4,16}$/',
            'help'        => 'Pseudo EA / Origin (4-16 caractères)',
        ),
        'epic-games'      => array(
            'name'        => 'Epic Games',
            'icon'        => 'rocket',
            'color'       => '#2f2d2e',
            'placeholder' => 'MonPseudoEpic',
            'pattern'     => '/^[a-zA-Z0-9_\-\s]{3,16}$/',
            'help'        => 'Pseudo Epic Games (3-16 caractères)',
        ),
        'battle-net'      => array(
            'name'        => 'Battle.net',
            'icon'        => 'shield',
            'color'       => '#00aeff',
            'placeholder' => 'Pseudo#12345',
            'pattern'     => '/^.{2,12}#\d{4,6}$/',
            'help'        => 'Format : Pseudo#12345',
        ),
        'ubisoft'         => array(
            'name'        => 'Ubisoft Connect',
            'icon'        => 'compass',
            'color'       => '#0070ff',
            'placeholder' => 'MonPseudoUbi',
            'pattern'     => '/^[a-zA-Z0-9_\-\.]{3,16}$/',
            'help'        => 'Pseudo Ubisoft Connect (3-16 caractères)',
        ),
        'discord'         => array(
            'name'        => 'Discord',
            'icon'        => 'message-circle',
            'color'       => '#5865f2',
            'placeholder' => 'monpseudo',
            'pattern'     => '/^.{2,32}$/',
            'help'        => 'Pseudo Discord (nouveau format sans #)',
        ),
    ) );
}


/**
 * -----------------------------------------------
 * 2. GESTION DES DONNÉES UTILISATEUR
 * -----------------------------------------------
 * Les codes ami sont stockés en user_meta sous forme
 * de tableau sérialisé : cozy_friend_codes
 * Structure : array( 'slug-plateforme' => 'CODE-AMI', ... )
 */

/**
 * Récupère les codes ami d'un utilisateur
 *
 * @param int $user_id L'ID de l'utilisateur
 * @return array Les codes ami indexés par slug de plateforme
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
 * Valide un code ami selon le format attendu de la plateforme
 *
 * @param string $platform_slug Le slug de la plateforme
 * @param string $code          Le code à valider
 * @return bool True si le code est valide ou vide
 */
function cozy_validate_friend_code( $platform_slug, $code ) {
    if ( empty( $code ) ) {
        return true;
    }

    $platforms = cozy_get_supported_platforms();

    if ( ! isset( $platforms[ $platform_slug ] ) ) {
        return false;
    }

    return (bool) preg_match( $platforms[ $platform_slug ]['pattern'], $code );
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
    $codes     = cozy_get_friend_codes( $user->ID );
    $platforms = cozy_get_supported_platforms();
    ?>
    <h3>🎮 Codes Ami — Cozy Grove</h3>
    <p class="description">Identifiants gaming du membre par plateforme. Tous les champs sont facultatifs.</p>
    <table class="form-table">
        <?php foreach ( $platforms as $slug => $platform ) : ?>
        <tr>
            <th>
                <label for="cozy_fc_<?php echo esc_attr( $slug ); ?>">
                    <?php echo esc_html( $platform['name'] ); ?>
                </label>
            </th>
            <td>
                <input
                    type="text"
                    name="cozy_friend_codes[<?php echo esc_attr( $slug ); ?>]"
                    id="cozy_fc_<?php echo esc_attr( $slug ); ?>"
                    value="<?php echo esc_attr( $codes[ $slug ] ?? '' ); ?>"
                    class="regular-text"
                    placeholder="<?php echo esc_attr( $platform['placeholder'] ); ?>"
                />
                <p class="description"><?php echo esc_html( $platform['help'] ); ?></p>
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

    $platforms = cozy_get_supported_platforms();
    $new_codes = array();

    foreach ( $_POST['cozy_friend_codes'] as $slug => $code ) {
        $code = sanitize_text_field( $code );

        if ( ! isset( $platforms[ $slug ] ) ) {
            continue;
        }

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
 * 4a. CATÉGORIES DE PLATEFORMES
 * -----------------------------------------------
 * Regroupe les plateformes en blocs déroulants pour
 * réduire la hauteur perçue du formulaire.
 */
function cozy_get_platform_categories() {
    return array(
        'console' => array(
            'label'     => 'Consoles',
            'icon'      => 'gamepad-2',
            'platforms' => array( 'nintendo-switch', 'psn', 'xbox' ),
        ),
        'pc' => array(
            'label'     => 'PC & Launchers',
            'icon'      => 'monitor',
            'platforms' => array( 'steam', 'epic-games', 'ea', 'ubisoft', 'battle-net', 'riot-games' ),
        ),
        'social' => array(
            'label'     => 'Social & Communauté',
            'icon'      => 'message-circle',
            'platforms' => array( 'discord' ),
        ),
    );
}


/**
 * -----------------------------------------------
 * 4b. SHORTCODE FRONT-END [cozy_codes_ami]
 * -----------------------------------------------
 * Formulaire front-end avec catégories déroulantes.
 */
function cozy_shortcode_friend_codes() {
    if ( ! is_user_logged_in() ) {
        return '<div class="cozy-social-login-required">
            <p>Tu dois être <strong>connecté(e)</strong> pour gérer tes codes ami.
            <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Connecte-toi ici</a>.</p>
        </div>';
    }

    $user_id    = get_current_user_id();
    $codes      = cozy_get_friend_codes( $user_id );
    $platforms  = cozy_get_supported_platforms();
    $categories = cozy_get_platform_categories();

    ob_start();
    ?>
    <div class="cozy-friend-codes" id="cozy-friend-codes">

        <!-- En-tête -->
        <div class="cozy-friend-codes__header">
            <span class="cozy-friend-codes__header-icon"><i data-lucide="gamepad-2"></i></span>
            <div class="cozy-friend-codes__header-text">
                <h3>Mes Codes Ami</h3>
                <p>Ajoute tes identifiants gaming pour que les autres joueurs puissent te retrouver. Tous les champs sont <strong>facultatifs</strong>.</p>
            </div>
        </div>

        <form id="cozy-friend-codes-form" class="cozy-friend-codes__form" method="post">
            <?php wp_nonce_field( 'cozy_save_friend_codes', 'cozy_fc_nonce' ); ?>

            <?php foreach ( $categories as $cat_slug => $category ) :
                $cat_platforms = array_filter(
                    $category['platforms'],
                    fn( $slug ) => isset( $platforms[ $slug ] )
                );
                if ( empty( $cat_platforms ) ) continue;

                // Compter les codes remplis dans cette catégorie
                $filled = 0;
                foreach ( $cat_platforms as $s ) {
                    if ( ! empty( $codes[ $s ] ?? '' ) ) $filled++;
                }
            ?>
            <details class="cozy-friend-codes__category"<?php if ( $filled > 0 ) echo ' open'; ?>>
                <summary class="cozy-friend-codes__category-label">
                    <i data-lucide="<?php echo esc_attr( $category['icon'] ); ?>"></i>
                    <span><?php echo esc_html( $category['label'] ); ?></span>
                    <?php if ( $filled > 0 ) : ?>
                        <span class="cozy-friend-codes__category-count"><?php echo $filled; ?></span>
                    <?php endif; ?>
                    <i data-lucide="chevron-down" class="cozy-friend-codes__chevron"></i>
                </summary>

                <div class="cozy-friend-codes__grid">
                    <?php foreach ( $cat_platforms as $slug ) :
                        $platform     = $platforms[ $slug ];
                        $current_code = $codes[ $slug ] ?? '';
                        $has_code     = ! empty( $current_code );
                    ?>
                    <div class="cozy-friend-codes__field" data-platform="<?php echo esc_attr( $slug ); ?>">
                        <div class="cozy-friend-codes__platform-row">
                            <span class="cozy-friend-codes__platform-icon" style="background:<?php echo esc_attr( $platform['color'] ); ?>;">
                                <i data-lucide="<?php echo esc_attr( $platform['icon'] ); ?>"></i>
                            </span>
                            <div class="cozy-friend-codes__platform-info" title="<?php echo esc_attr( $platform['name'] . ' — ' . $platform['help'] ); ?>">
                                <strong><?php echo esc_html( $platform['name'] ); ?></strong>
                                <small><?php echo esc_html( $platform['help'] ); ?></small>
                            </div>
                            <?php if ( $has_code ) : ?>
                                <span class="cozy-friend-codes__status cozy-friend-codes__status--saved" title="Code enregistré">✓</span>
                            <?php endif; ?>
                        </div>
                        <div class="cozy-friend-codes__input-wrapper">
                            <input
                                type="text"
                                id="cozy_fc_front_<?php echo esc_attr( $slug ); ?>"
                                name="cozy_friend_codes[<?php echo esc_attr( $slug ); ?>]"
                                value="<?php echo esc_attr( $current_code ); ?>"
                                placeholder="<?php echo esc_attr( $platform['placeholder'] ); ?>"
                                autocomplete="off"
                                data-pattern="<?php echo esc_attr( $platform['pattern'] ); ?>"
                            >
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </details>
            <?php endforeach; ?>

            <!-- Actions -->
            <div class="cozy-friend-codes__actions">
                <button type="submit" class="cozy-friend-codes__btn" id="cozy-fc-save">
                    <i data-lucide="save"></i> Sauvegarder mes codes
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
        '2.0.0'
    );

    wp_enqueue_script(
        'cozy-friend-codes',
        get_template_directory_uri() . '/assets/js/cozy-friend-codes.js',
        array(),
        '2.0.0',
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

    $user_id   = get_current_user_id();
    $platforms = cozy_get_supported_platforms();
    $errors    = array();
    $saved     = array();

    if ( ! isset( $_POST['cozy_friend_codes'] ) || ! is_array( $_POST['cozy_friend_codes'] ) ) {
        cozy_save_friend_codes( $user_id, array() );
        wp_send_json_success( array(
            'message' => 'Codes ami mis à jour !',
            'codes'   => array(),
        ) );
    }

    foreach ( $_POST['cozy_friend_codes'] as $slug => $code ) {
        $code = sanitize_text_field( $code );

        if ( ! isset( $platforms[ $slug ] ) ) {
            continue;
        }

        if ( empty( $code ) ) {
            continue;
        }

        if ( ! cozy_validate_friend_code( $slug, $code ) ) {
            $errors[] = sprintf(
                '<strong>%s</strong> : format invalide. %s',
                esc_html( $platforms[ $slug ]['name'] ),
                esc_html( $platforms[ $slug ]['help'] )
            );
            continue;
        }

        $saved[ $slug ] = $code;
    }

    if ( ! empty( $errors ) ) {
        wp_send_json_error( array( 'message' => implode( '<br>', $errors ) ) );
    }

    cozy_save_friend_codes( $user_id, $saved );

    wp_send_json_success( array(
        'message' => 'Codes ami sauvegardés avec succès !',
        'codes'   => $saved,
    ) );
}
add_action( 'wp_ajax_cozy_save_friend_codes', 'cozy_ajax_save_friend_codes' );


/**
 * -----------------------------------------------
 * 6. AFFICHAGE DES CODES AMI SUR LES ÉVÉNEMENTS
 * -----------------------------------------------
 * Affiche les identifiants gaming des participants
 * sur la page de l'événement sous forme de badges.
 */

/**
 * Retourne le HTML des codes ami d'un utilisateur
 * Optionnellement filtré par plateforme si un slug est fourni.
 *
 * @param int         $user_id       L'ID de l'utilisateur
 * @param string|null $platform_slug Optionnel : filtrer par plateforme
 * @return string Le HTML des codes ou chaîne vide
 */
function cozy_get_friend_code_badges( $user_id, $platform_slug = null ) {
    if ( empty( $user_id ) ) {
        return '';
    }

    $codes = cozy_get_friend_codes( $user_id );
    if ( empty( $codes ) ) {
        return '';
    }

    $platforms = cozy_get_supported_platforms();
    $html      = '';

    foreach ( $codes as $slug => $code ) {
        if ( ! isset( $platforms[ $slug ] ) ) {
            continue;
        }

        if ( $platform_slug && $slug !== $platform_slug ) {
            continue;
        }

        $platform = $platforms[ $slug ];
        $html .= sprintf(
            '<span class="cozy-fc-badge" style="--fc-color: %s;" title="%s">
                <i data-lucide="%s"></i>
                <span class="cozy-fc-badge__name">%s</span>
                <code>%s</code>
            </span>',
            esc_attr( $platform['color'] ),
            esc_attr( $platform['name'] ),
            esc_attr( $platform['icon'] ),
            esc_html( $platform['name'] ),
            esc_html( $code )
        );
    }

    if ( empty( $html ) ) {
        return '';
    }

    return '<div class="cozy-fc-badges">' . $html . '</div>';
}
