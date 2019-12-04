<?php
/**
 * This file allows a store manager to mark an order item as a warranty replacement.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Set up tab data fields.
 *
 * @since 1.0.0
*/
// add_action('woocommerce_product_options_general_product_data', 'br_very_short_description_field');
// function br_very_short_description_field() {
//     woocommerce_wp_text_input( array(
//       'id'                => '_br_very_short_description',
//       'value'             => get_post_meta( get_the_ID(), '_br_very_short_description', true ),
//       'label'             => 'Very Short Description',
//       'description'       => 'Max 60 chars, including &lt;br /&gt;. No period, please!',
//       'custom_attributes' => array('maxlength'=>60)
//     ) );
// }

/**
 * Save pricing fields to post meta data.
 *
 * @since 1.0.0
*/
// function br_save_very_short_description_field($post_id) {
//     // Save fields
//     $br_very_short_description = $_POST['_br_very_short_description'];
//     if (!empty($br_very_short_description)) {
//         update_post_meta($post_id, '_br_very_short_description', esc_attr($br_very_short_description));
//     }
// }
// add_action( 'woocommerce_process_product_meta', 'br_save_very_short_description_field'  );

// Add custom column headers here
add_action('woocommerce_admin_order_item_headers', 'my_woocommerce_admin_order_item_headers');
function my_woocommerce_admin_order_item_headers() {
    // set the column name
    $column_name = 'Warranty Replacement';

    // display the column name
    echo '<th>' . $column_name . '</th>';
}

// Add custom column values here
add_action('woocommerce_admin_order_item_values', 'my_woocommerce_admin_order_item_values', 10, 3);
function my_woocommerce_admin_order_item_values($_product, $item, $item_id = null) {
    // get the post meta value from the associated product
    $is_warranty = false;

    $meta_data = $item->get_formatted_meta_data();

    foreach ( $meta_data as $key => $value ) {
      if ( $meta_data[$key]->key == 'warranty_replacement' ) {
        if ( $meta_data[$key]->value == 'on' ) {
          $is_warranty = true;
        }
      }
    }

    $value = '';

    if ( $is_warranty ) {
      $value .= '<div class="view"><span class="dashicons dashicons-yes"></span></div>';
    } else {
      $value .= '<div class="view"></div>';
    }

    //$checkbox_name = 'br_is_warranty_item_checkbox_'.$item_id;
    $checkbox_name = '['.$item_id.'][new-warranty]';
    $checked = $is_warranty ? 'checked' : '';

    $value .= '<div class="edit" style="display: none;">
                    <input type="hidden" name="meta_key'.$checkbox_name.'" value="warranty_replacement" />
                    <input type="checkbox" class="br_is_warranty_item_checkbox" name="meta_value'.$checkbox_name.'" '.$checked.'/>
                </div>';

    // display the value
    echo '<td>' . $value . '</td>';
}

/**
 * Save the field.
 */
add_action( 'woocommerce_before_save_order_item', 'br_warranty_save_item_meta', 20, 1 );
function br_warranty_save_item_meta( $item ) {
  // $is_warranty = false;
  // if ( isset($_POST['br_is_warranty_item_checkbox_'.$item->get_id()]) ) {
  //   $is_warranty = $_POST['br_is_warranty_item_checkbox_'.$item->get_id()];
  // }

  //$value = print_r($item,true);
  // $value = 'br_is_warranty_item_checkbox_'.$item->get_id().' '.$is_warranty;

  // if ( $is_warranty ) {
  //   $item->update_meta_data('test', 'true');
  // } else {
  //   $item->update_meta_data('test', $value);
  // }

  //$item->update_meta_data('warranty_replacement', '');

  return $item;
}


?>
