<?php
/**
 * This file adds PO number fields to the checkout page and admin page. This
 * field can be accessed at the _br_po_number meta data field.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Add the field to the checkout
 */
add_action( 'woocommerce_before_order_notes', 'br_po_number_field' );

function br_po_number_field( $checkout ) {

	echo '<div id="br_po_checkout_field">';

    woocommerce_form_field( 'po_number', array(
        'type'          => 'text',
        'class'         => array('br-po-field-class form-row-wide'),
        'label'         => __('Purchase Order (PO) Number'),
        'placeholder'   => __(''),
        'validate'      => array(),
        ), $checkout->get_value( 'po_number' ));

    echo '</div>';
}

/**
 * Update the order meta with field value
 */
add_action( 'woocommerce_checkout_update_order_meta', 'br_po_checkout_field_update_order_meta' );

function br_po_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['po_number'] ) ) {
        update_post_meta( $order_id, '_br_po_number', sanitize_text_field( $_POST['po_number'] ) );
    }
}

/**
 * Add the editable field to the admin page.
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'br_po_checkout_field_display_admin_order_meta' );	 	
function br_po_checkout_field_display_admin_order_meta( $order ){	 	 
	$br_po_number = get_post_meta( $order->get_order_number(), '_br_po_number', true );
	?>
	<div class="address">
		<p<?php if( !$br_po_number ) echo ' class=""' ?>>
			<strong>Customer PO Number:</strong>
			<?php echo ( $br_po_number ) ? $br_po_number : 'None.' ?>
		</p>
	</div>
	<div class="edit_address"><?php
		woocommerce_wp_text_input( array( 
			'id' => 'br_po_input',
			'label' => 'Customer PO Number:', 
			'wrapper_class' => 'form-field-wide',
			'value' => $br_po_number,
			'desc_tip' => true
		) );
        ?></div><?php
}
 
/**
 * Save the field.
 */
add_action( 'woocommerce_process_shop_order_meta', 'br_po_process_shop_order_meta' );
function br_po_process_shop_order_meta( $ord_id ){
	update_post_meta( $ord_id, 'br_po_input', wc_clean( $_POST[ '_br_po_number' ] ) );
}

?>
