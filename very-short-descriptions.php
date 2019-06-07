<?php
/**
 * This file adds a very short description field that can be used in the theme.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Set up tab data fields.
 *
 * @since 1.0.0
*/
add_action('woocommerce_product_options_general_product_data', 'br_very_short_description_field');
function br_very_short_description_field() {
    woocommerce_wp_text_input( array(
      'id'                => '_br_very_short_description',
      'value'             => get_post_meta( get_the_ID(), '_br_very_short_description', true ),
      'label'             => 'Very Short Description',
      'description'       => 'Max 60 chars, including &lt;br /&gt;. No period, please!',
      'custom_attributes' => array('maxlength'=>60)
    ) );
}

/**
 * Save pricing fields to post meta data.
 *
 * @since 1.0.0
*/
function br_save_very_short_description_field($post_id) {
    // Save fields
    $br_very_short_description = $_POST['_br_very_short_description'];
    if (!empty($br_very_short_description)) {
        update_post_meta($post_id, '_br_very_short_description', esc_attr($br_very_short_description));
    }
}
add_action( 'woocommerce_process_product_meta', 'br_save_very_short_description_field'  );


?>
