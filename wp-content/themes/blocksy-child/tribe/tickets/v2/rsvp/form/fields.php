<?php
/**
 * Block: RSVP
 * Form fields (Surcharge Cozy Gaming)
 *
 * Ajoute la checkbox de la charte de bienveillance
 * après les champs classiques du formulaire RSVP.
 *
 * Override de : tribe/tickets/v2/rsvp/form/fields.php
 *
 * @since 4.12.3
 * @version 4.12.3
 */

$this->template( 'v2/rsvp/form/fields/name', [ 'rsvp' => $rsvp, 'going' => $going ] );
$this->template( 'v2/rsvp/form/fields/email', [ 'rsvp' => $rsvp, 'going' => $going ] );
$this->template( 'v2/rsvp/form/fields/quantity', [ 'rsvp' => $rsvp, 'going' => $going ] );

// Ajouter la checkbox de la charte de bienveillance
if ( function_exists( 'cozy_get_charter_rules' ) ) :
    $rules = cozy_get_charter_rules();
?>
<div class="cozy-charter-field" id="cozy-charter-field">
    <div class="cozy-charter-field__summary">
        <span class="cozy-charter-field__icon">🕊️</span>
        <span class="cozy-charter-field__title">Charte de bienveillance</span>
    </div>

    <ul class="cozy-charter-field__rules">
        <?php foreach ( $rules as $rule ) : ?>
            <li><?php echo $rule['icon'] . ' ' . esc_html( $rule['text'] ); ?></li>
        <?php endforeach; ?>
    </ul>

    <label class="cozy-charter-field__label">
        <input
            type="checkbox"
            name="cozy_charter_accepted"
            id="cozy-charter-checkbox"
            value="1"
            required
        >
        <span class="cozy-charter-field__checkmark"></span>
        <span class="cozy-charter-field__text">
            J'ai lu et j'accepte la charte de bienveillance de Cozy Gaming ✨
        </span>
    </label>
</div>
<?php endif; ?>
