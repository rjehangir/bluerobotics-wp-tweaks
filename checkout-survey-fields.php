<?php
/**
 * This file adds customer type and application fields to the checkout page and admin page. This
 * field can be accessed at the _br_survey_application and _br_survey_customer_type meta data fields.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Add field(s) to collect customer industry for analytics.
 */
add_action( 'woocommerce_after_order_notes', 'br_checkout_survey_fields' );

function br_checkout_survey_fields( $checkout ) {

    woocommerce_form_field( 'br_survey_customer_type', array(
        'type'          => 'select',
        'class'         => array('br-customer-type-field-class form-row-wide'),
        'label'         => __('I\'m buying these items for:'),
        'required'      => true,
        'options'       => array(
                'None'      => __( 'Select an option'),
                'Myself'     => __( 'Myself'),
                'Business'   => __( 'A business' ),
                'School'     => __( 'A school or institution' ),
                'Government' => __( 'A government organization' ),
                'Secret'     => __( 'It\'s top secret!' )
            )
        ), $checkout->get_value( 'br_survey_application' ));

    woocommerce_form_field( 'br_survey_application', array(
        'type'          => 'textarea',
        'class'         => array('br-application-field-class form-row-wide'),
        'label'         => __('What are you using these items for?'),
        'placeholder'   => __('I\'m using these parts for...'),
        ), $checkout->get_value( 'br_survey_application' ));

}

/**
 * Update the order meta with field values
 */
add_action( 'woocommerce_checkout_update_order_meta', 'br_checkout_survey_field_update_order_meta' );

function br_checkout_survey_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['br_survey_customer_type'] ) ) {
        update_post_meta( $order_id, '_br_survey_customer_type', sanitize_text_field( $_POST['br_survey_customer_type'] ) );
    }
    if ( ! empty( $_POST['br_survey_application'] ) ) {
        update_post_meta( $order_id, '_br_survey_application', sanitize_text_field( $_POST['br_survey_application'] ) );
    }
}

/**
 * Add the fields to the admin page.
 */
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'br_checkout_survey_field_display_admin_order_meta' );        
function br_checkout_survey_field_display_admin_order_meta( $order ){        
    $br_survey_application = get_post_meta( $order->id, '_br_survey_application', true );
    $br_survey_customer_type = get_post_meta( $order->id, '_br_survey_customer_type', true );
    ?>
    <div class="address">
        <p<?php if( !$br_survey_customer_type ) echo ' class=""' ?>>
            <strong>Customer type:</strong>
            <?php echo ( $br_survey_customer_type ) ? $br_survey_customer_type : 'None' ?>
        </p>
    </div>
    <div class="address">
        <p<?php if( !$br_survey_application ) echo ' class=""' ?>>
            <strong>Customer application:</strong>
            <?php echo ( $br_survey_application ) ? $br_survey_application : 'None' ?>
        </p>
    </div>
    <div class="edit_address"><?php /*
        woocommerce_wp_text_input( array( 
            'id' => 'br_po_input',
            'label' => 'Customer PO Number:', 
            'wrapper_class' => 'form-field-wide',
            'value' => $br_po_number,
            'desc_tip' => true
        ) ); */
        ?></div><?php
}

/**
 * Save the fields.
 */
add_action( 'woocommerce_process_shop_order_meta', 'br_checkout_survey_process_shop_order_meta' );
function br_checkout_survey_process_shop_order_meta( $ord_id ) {
    if ( !empty( $_POST['br_survey_customer_type'] ) ) {
        update_post_meta( $ord_id, '_br_survey_customer_type', sanitize_text_field( $_POST[ 'br_survey_customer_type' ] ) );
    }
    if ( !empty( $_POST['br_survey_application'] ) ) {
        update_post_meta( $ord_id, '_br_survey_application', sanitize_text_field( $_POST[ 'br_survey_application' ] ) );
    }
}


?>
