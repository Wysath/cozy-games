<?php
/**
 * ============================================================================
 * MODULE : Charte de Bienveillance (RSVP)
 * ============================================================================
 *
 * Ajoute une case à cocher obligatoire au formulaire RSVP pour que
 * chaque joueur s'engage à respecter la charte de bienveillance
 * de la communauté Cozy Gaming avant de s'inscrire.
 *
 * La charte est affichée :
 *   - Dans le formulaire RSVP (checkbox obligatoire)
 *   - Sur la page single d'événement (bandeau rappel)
 *
 * @package CozyGaming
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * -----------------------------------------------
 * 1. CONTENU DE LA CHARTE
 * -----------------------------------------------
 * Centralisé ici pour être réutilisé partout.
 */

/**
 * Retourne les règles de la charte de bienveillance
 *
 * @return array Liste des règles avec icône et texte
 */
function cozy_get_charter_rules() {
    return apply_filters( 'cozy_charter_rules', array(
        array(
            'icon' => '💜',
            'text' => 'Je m\'engage à être bienveillant·e et respectueux·se envers tous les participants.',
        ),
        array(
            'icon' => '🚫',
            'text' => 'Aucune forme de toxicité, harcèlement, discrimination ou moquerie ne sera tolérée.',
        ),
        array(
            'icon' => '🤝',
            'text' => 'J\'accepte que tout le monde joue à son rythme, quel que soit son niveau.',
        ),
        array(
            'icon' => '🎧',
            'text' => 'Je respecte le mode de communication choisi pour l\'événement.',
        ),
        array(
            'icon' => '🛡️',
            'text' => 'En cas de problème, je préviens un animateur plutôt que de répondre à la provocation.',
        ),
    ) );
}


/**
 * -----------------------------------------------
 * 2. AFFICHAGE DE LA CHARTE SUR LA SINGLE EVENT
 * -----------------------------------------------
 * Un bandeau rappel affiché avant le formulaire RSVP.
 */

/**
 * Affiche la charte de bienveillance sur la page de l'événement
 */
function cozy_display_charter_single() {
    $event_id = get_the_ID();

    if ( ! $event_id || get_post_type( $event_id ) !== 'tribe_events' ) {
        return;
    }

    $rules = cozy_get_charter_rules();
    ?>
    <div class="cozy-charter" id="cozy-charte">
        <div class="cozy-charter__header">
            <h4 class="cozy-charter__title">🕊️ Charte de bienveillance</h4>
            <p class="cozy-charter__subtitle">Chez Cozy Gaming, on joue dans la bonne humeur !</p>
        </div>

        <ul class="cozy-charter__rules">
            <?php foreach ( $rules as $rule ) : ?>
                <li class="cozy-charter__rule">
                    <span class="cozy-charter__rule-icon"><?php echo $rule['icon']; ?></span>
                    <span class="cozy-charter__rule-text"><?php echo esc_html( $rule['text'] ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

// S'afficher AVANT le contenu, après les modes de comm (priorité 5)
add_action( 'tribe_events_single_event_before_the_content', 'cozy_display_charter_single', 8 );


/**
 * -----------------------------------------------
 * 3. CHECKBOX DANS LE FORMULAIRE RSVP
 * -----------------------------------------------
 * Via une surcharge du template fields.php pour ajouter
 * la checkbox après les champs classiques.
 * 
 * La validation JS empêche l'envoi si non cochée.
 * La validation PHP empêche la création du billet.
 */

/**
 * Validation côté serveur : vérifier que la charte est acceptée
 * Se hook juste avant le traitement de l'ordre RSVP
 */
function cozy_validate_charter_acceptance() {
    // Vérifier que la charte est cochée
    if ( empty( $_POST['cozy_charter_accepted'] ) ) {
        // Rediriger avec une erreur
        $post_id = get_the_ID();
        if ( ! $post_id && isset( $_POST['post_id'] ) ) {
            $post_id = absint( $_POST['post_id'] );
        }
        // Trouver l'event_id via les tickets
        if ( ! $post_id && isset( $_POST['tribe_tickets'] ) ) {
            $tickets = $_POST['tribe_tickets'];
            $first   = reset( $tickets );
            if ( isset( $first['ticket_id'] ) ) {
                $ticket_id = absint( $first['ticket_id'] );
                $post_id   = get_post_meta( $ticket_id, '_tribe_rsvp_for_event', true );
            }
        }

        if ( $post_id ) {
            $url = add_query_arg( 'rsvp_error', 'charter', get_permalink( $post_id ) );
            wp_redirect( esc_url_raw( $url ) );
            exit;
        }
    }
}
add_action( 'tribe_tickets_rsvp_before_order_processing', 'cozy_validate_charter_acceptance' );


/**
 * -----------------------------------------------
 * 4. SCRIPT DE VALIDATION FRONT-END
 * -----------------------------------------------
 * Désactive le bouton Submit tant que la charte n'est pas cochée.
 */

/**
 * Enqueue le script de validation de la charte
 */
function cozy_charter_enqueue_assets() {
    if ( ! is_singular( 'tribe_events' ) ) {
        return;
    }

    wp_enqueue_style(
        'cozy-charter',
        get_stylesheet_directory_uri() . '/assets/css/cozy-charter.css',
        array(),
        '1.5.0'
    );

    // Script inline pour la validation
    wp_add_inline_script( 'jquery', cozy_charter_inline_script() );
}
add_action( 'wp_enqueue_scripts', 'cozy_charter_enqueue_assets' );


/**
 * Retourne le script JS inline pour la validation de la charte
 *
 * @return string Le code JavaScript
 */
function cozy_charter_inline_script() {
    return "
    document.addEventListener('DOMContentLoaded', function() {
        // Observer pour détecter l'ouverture du formulaire RSVP (chargement dynamique)
        var observer = new MutationObserver(function(mutations) {
            var checkbox = document.getElementById('cozy-charter-checkbox');
            if (checkbox) {
                initCharterValidation(checkbox);
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });

        // Vérifier immédiatement aussi
        var checkbox = document.getElementById('cozy-charter-checkbox');
        if (checkbox) {
            initCharterValidation(checkbox);
        }

        function initCharterValidation(checkbox) {
            var form = checkbox.closest('form');
            if (!form || form.dataset.charterInit) return;
            form.dataset.charterInit = 'true';

            var submitBtn = form.querySelector('.tribe-tickets__rsvp-form-button[type=\"submit\"]');
            if (!submitBtn) return;

            function toggleSubmit() {
                submitBtn.disabled = !checkbox.checked;
                submitBtn.classList.toggle('cozy-charter-disabled', !checkbox.checked);
            }

            toggleSubmit();
            checkbox.addEventListener('change', toggleSubmit);

            form.addEventListener('submit', function(e) {
                if (!checkbox.checked) {
                    e.preventDefault();
                    checkbox.closest('.cozy-charter-field').classList.add('cozy-charter-field--error');
                }
            });
        }
    });
    ";
}
