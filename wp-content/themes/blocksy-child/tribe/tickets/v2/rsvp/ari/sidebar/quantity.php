<?php
/**
 * Block: RSVP - Quantity Selector (Surcharge Cozy Gaming)
 * 
 * Cette surcharge masque le sélecteur de quantité car chaque membre
 * ne peut réserver qu'UNE seule place pour lui-même.
 *
 * @var Tribe__Tickets__Ticket_Object $rsvp The rsvp ticket object.
 */

// On affiche simplement un message indiquant que c'est 1 place par personne
?>
<div class="tribe-tickets__rsvp-ar-quantity cozy-single-reservation">
    <span class="tribe-common-h7 tribe-common-h--alt">
        <?php esc_html_e( 'Ta place', 'cozy-gaming' ); ?>
    </span>
    
    <div class="cozy-single-ticket-info" style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
        <span class="tribe-common-h4" style="background: #f0f0f0; padding: 8px 16px; border-radius: 6px; font-weight: bold;">1</span>
        <span style="color: #666; font-size: 14px;">place réservée</span>
    </div>
    
    <!-- Input caché pour la soumission du formulaire -->
    <input 
        type="hidden" 
        name="tribe_tickets[<?php echo esc_attr( absint( $rsvp->ID ) ); ?>][quantity]" 
        value="1"
    />
</div>
