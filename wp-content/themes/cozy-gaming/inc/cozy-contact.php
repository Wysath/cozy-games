<?php
/**
 * Shortcode [cozy_contact_form]
 * Page de contact Cozy Grove — v2.0
 */
function cozy_contact_form_shortcode() {
    $success = false;
    $error   = '';

    // Traitement du formulaire
    if ( isset( $_POST['cozy_contact_submit'] ) ) {

        // 1. Vérification nonce (sécurité CSRF)
        if ( ! isset( $_POST['cozy_contact_nonce'] ) || ! wp_verify_nonce( $_POST['cozy_contact_nonce'], 'cozy_contact_action' ) ) {
            $error = 'Erreur de sécurité. Recharge la page et réessaie.';

        // 2. Honeypot anti-spam (champ caché qui doit rester vide)
        } elseif ( ! empty( $_POST['cozy_website'] ) ) {
            $success = true; // Faux succès pour ne pas alerter les bots

        } else {
            $name    = sanitize_text_field( $_POST['cozy_contact_name'] ?? '' );
            $email   = sanitize_email( $_POST['cozy_contact_email'] ?? '' );
            $subject = sanitize_text_field( $_POST['cozy_contact_subject'] ?? '' );
            $message = sanitize_textarea_field( $_POST['cozy_contact_message'] ?? '' );

            if ( ! $name || ! $email || ! $message || ! is_email( $email ) ) {
                $error = 'Veuillez remplir tous les champs obligatoires correctement.';
            } else {
                $to      = get_option( 'admin_email' );
                $headers = array(
                    'Content-Type: text/html; charset=UTF-8',
                    'Reply-To: ' . $name . ' <' . $email . '>',
                );
                $mail_subject = $subject
                    ? '[Cozy Grove] ' . $subject
                    : '[Cozy Grove] Nouveau message de contact';

                $body = '
                    <p><strong>Nom :</strong> ' . esc_html( $name ) . '</p>
                    <p><strong>Email :</strong> ' . esc_html( $email ) . '</p>
                    ' . ( $subject ? '<p><strong>Sujet :</strong> ' . esc_html( $subject ) . '</p>' : '' ) . '
                    <p><strong>Message :</strong></p>
                    <p>' . nl2br( esc_html( $message ) ) . '</p>
                ';

                $sent = wp_mail( $to, $mail_subject, $body, $headers );
                if ( $sent ) {
                    $success = true;
                } else {
                    $error = 'Une erreur est survenue lors de l\'envoi. Réessaie dans quelques instants.';
                }
            }
        }
    }

    ob_start();
    ?>
    <div class="cozy-contact">

        <!-- État succès -->
        <?php if ( $success ) : ?>
            <div class="cozy-contact__success">
                <span class="cozy-contact__success-icon"><i data-lucide="check-circle"></i></span>
                <div>
                    <p class="cozy-contact__success-title">Message envoyé !</p>
                    <p class="cozy-contact__success-text">Merci pour ton message. On te répond au plus vite 🌿</p>
                </div>
            </div>

        <?php else : ?>

            <?php if ( $error ) : ?>
                <div class="cozy-contact__error">
                    <i data-lucide="alert-triangle"></i>
                    <?php echo esc_html( $error ); ?>
                </div>
            <?php endif; ?>

            <form class="cozy-contact__form" method="post" novalidate>

                <?php wp_nonce_field( 'cozy_contact_action', 'cozy_contact_nonce' ); ?>

                <!-- Honeypot — doit rester vide -->
                <div class="cozy-contact__honeypot" aria-hidden="true">
                    <input type="text" name="cozy_website" value="" tabindex="-1" autocomplete="off">
                </div>

                <!-- Ligne nom + email -->
                <div class="cozy-contact__row">
                    <div class="cozy-contact__group">
                        <label class="cozy-contact__label" for="cozy_contact_name">
                            Nom <span class="cozy-contact__required">*</span>
                        </label>
                        <input
                            class="cozy-contact__input"
                            type="text"
                            id="cozy_contact_name"
                            name="cozy_contact_name"
                            placeholder="Ton prénom ou pseudo"
                            value="<?php echo esc_attr( $_POST['cozy_contact_name'] ?? '' ); ?>"
                            required
                        >
                    </div>
                    <div class="cozy-contact__group">
                        <label class="cozy-contact__label" for="cozy_contact_email">
                            Email <span class="cozy-contact__required">*</span>
                        </label>
                        <input
                            class="cozy-contact__input"
                            type="email"
                            id="cozy_contact_email"
                            name="cozy_contact_email"
                            placeholder="ton@email.com"
                            value="<?php echo esc_attr( $_POST['cozy_contact_email'] ?? '' ); ?>"
                            required
                        >
                    </div>
                </div>

                <!-- Sujet -->
                <div class="cozy-contact__group">
                    <label class="cozy-contact__label" for="cozy_contact_subject">
                        Sujet <span class="cozy-contact__optional">(optionnel)</span>
                    </label>
                    <input
                        class="cozy-contact__input"
                        type="text"
                        id="cozy_contact_subject"
                        name="cozy_contact_subject"
                        placeholder="Ex : Question sur un événement"
                        value="<?php echo esc_attr( $_POST['cozy_contact_subject'] ?? '' ); ?>"
                    >
                </div>

                <!-- Message -->
                <div class="cozy-contact__group">
                    <label class="cozy-contact__label" for="cozy_contact_message">
                        Message <span class="cozy-contact__required">*</span>
                    </label>
                    <textarea
                        class="cozy-contact__textarea"
                        id="cozy_contact_message"
                        name="cozy_contact_message"
                        placeholder="Dis-nous tout… 🍂"
                        rows="6"
                        maxlength="2000"
                        required
                    ><?php echo esc_textarea( $_POST['cozy_contact_message'] ?? '' ); ?></textarea>
                    <span class="cozy-contact__hint">2000 caractères max</span>
                </div>

                <!-- Submit -->
                <div class="cozy-contact__footer">
                    <button type="submit" name="cozy_contact_submit" class="cozy-contact__submit">
                        <i data-lucide="send"></i> Envoyer le message
                    </button>
                    <span class="cozy-contact__note">
                        <span class="cozy-contact__required">*</span> Champs obligatoires
                    </span>
                </div>

            </form>

        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_contact_form', 'cozy_contact_form_shortcode' );

function cozy_contact_enqueue_assets() {
    wp_enqueue_style(
        'cozy-contact',
        get_template_directory_uri() . '/assets/css/cozy-contact.css',
        array(),
        '1.7.0'
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_contact_enqueue_assets' );
