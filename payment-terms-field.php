<?php
/**
 * This file adds a payment terms fields to the admin page. This
 * field can be accessed at the _br_payment_terms meta data field.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Update the order meta with field value
 */
add_action( 'woocommerce_checkout_update_order_meta', 'br_payment_field_set_on_checkout' );

function br_payment_field_set_on_checkout( $order_id ) {
	if ( $_POST['payment_method'] == 'bacs' ) {
    	update_post_meta( $order_id, '_br_payment_terms', 'Unknown' );
    } else if ( $_POST['payment_method'] == 'gpls-rfq' ) {
    	update_post_meta( $order_id, '_br_payment_terms', 'Unknown' );
    } else {
    	update_post_meta( $order_id, '_br_payment_terms', 'Prepaid / Immediate Payment' );
    }
}

/**
 * Add the editable field to the admin page.
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'br_payment_terms_field_display_admin_order_meta' );	 	
function br_payment_terms_field_display_admin_order_meta( $order ){	 	

	$options = array(
				'Unknown' => 'Unknown',
				'Prepaid / Immediate Payment' => 'Prepaid / Immediate Payment',
				'Prepaid on Credit Card' => 'Prepaid on Credit Card',
				'Net 15' => 'Net 15',
				'Net 30' => 'Net 30',
				'Net 45' => 'Net 45',
				'Net 60' => 'Net 60',
				'Net 90' => 'Net 90');

	$br_payment_terms = get_post_meta( $order->id, '_br_payment_terms', true );
	?>
	<div class="address">
		<p<?php if( !$br_payment_terms ) echo ' class=""' ?>>
			<strong>Payment Terms (does not affect Odoo terms):</strong>
			<?php echo ( $br_payment_terms ) ? $options[$br_payment_terms] : 'None' ?>
		</p>
	</div>
	<div class="edit_address"><?php
		woocommerce_wp_select( array( 
			'id' => 'br_payment_terms_input',
			'label' => 'Payment Terms:', 
			'wrapper_class' => 'form-field-wide',
			'value' => $br_payment_terms,
			'options' => $options
		) );
        ?></div><?php
}

/**
 * Save the field.
 */
add_action( 'woocommerce_process_shop_order_meta', 'br_payment_terms_process_shop_order_meta', 20, 1 );
function br_payment_terms_process_shop_order_meta( $ord_id ) {
	if ( !empty( $_POST['br_payment_terms_input'] ) ) {
		update_post_meta( $ord_id, '_br_payment_terms', sanitize_text_field( $_POST[ 'br_payment_terms_input' ] ) );
	}
}


?>
