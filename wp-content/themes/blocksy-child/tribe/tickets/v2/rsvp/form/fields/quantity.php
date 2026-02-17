<?php
/**
 * Block: RSVP - Form Quantity (Surcharge Cozy Gaming)
 *
 * Remplace l'input numérique par un chiffre fixe « 1 » à titre informatif.
 * Chez Cozy Gaming, chaque membre ne réserve qu'une seule place.
 *
 * Override de : tribe/tickets/v2/rsvp/form/fields/quantity.php
 *
 * @var Tribe__Tickets__Ticket_Object $rsvp  The rsvp ticket object.
 * @var string                        $going Whether the user is going or not.
 */
?>
<div class="tribe-common-b1 tribe-tickets__form-field tribe-tickets__form-field--quantity-fixed">
	<label class="tribe-common-b2--min-medium tribe-tickets__form-field-label">
		<?php esc_html_e( 'Nombre de participants', 'cozy-gaming' ); ?>
	</label>
	<span class="cozy-quantity-fixed">1</span>

	<input
		type="hidden"
		name="tribe_tickets[<?php echo esc_attr( absint( $rsvp->ID ) ); ?>][quantity]"
		value="1"
	>
</div>
