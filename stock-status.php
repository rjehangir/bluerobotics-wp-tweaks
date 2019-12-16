<?php
/**
 * This file provides the customer with better stock status information.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Set up the fields for regular products
 *
 * @since 1.0.0
*/
add_action('woocommerce_product_options_inventory_product_data', 'br_in_stock_date_field');
function br_in_stock_date_field() {
  global $product;

  echo '<p class="form-field">
    <label for="br_in_stock_date_field">Expected in-stock date</label><input type="text" name="br_in_stock_date_field" class="br_in_stock_date_field short" placeholder="" value="'.get_post_meta( get_the_ID(), '_br_in_stock_date_field', true ).'"></input></p>';

  echo '<script>
      jQuery(document).ready(function( $ ) {
          $( ".br_in_stock_date_field").datepicker( {
            minDate: 0, 
          } );
       } );
  </script>';

  $show_stock_checked = get_post_meta( get_the_ID(), '_br_always_show_stock_field', true ) == 'on' ? 'checked' : '';

  echo '<p class="form-field">
    <label for="br_always_show_stock_field">Always show stock count</label><input type="checkbox" name="br_always_show_stock_field" class="br_always_show_stock_field" '.$show_stock_checked.'></input></p>';
}

/**
 * Save the fields for regular products
 *
 * @since 1.0.0
*/
function br_save_in_stock_date_field($post_id) {
    // Save fields
    $in_stock_date_field = $_POST['br_in_stock_date_field'];
    update_post_meta($post_id, '_br_in_stock_date_field', esc_attr($in_stock_date_field));

    $br_always_show_stock_field = $_POST['br_always_show_stock_field'];
    update_post_meta($post_id, '_br_always_show_stock_field', esc_attr($br_always_show_stock_field));
}
add_action( 'woocommerce_process_product_meta', 'br_save_in_stock_date_field'  );

/**
 * Set up the in-stock date field for variable products
 *
 * @since 1.0.0
*/
add_action( 'woocommerce_variation_options_inventory', 'br_variation_in_stock_date_field', 10, 3 );
function br_variation_in_stock_date_field( $loop, $variation_data, $variation ) {
  $field_id = 'br_in_stock_date_field_'.$variation->ID;
  echo '<p class="form-field">
    <label for="'.$field_id.'">Expected in-stock date</label><input type="text" id="'.$field_id.'" name="'.$field_id.'" class="br_in_stock_date_field short" value="'.get_post_meta( $variation->ID, '_br_in_stock_date_field', true ).'"></p>';

  echo '<script>
      jQuery(document).ready(function( $ ) {
          $( ".br_in_stock_date_field").datepicker( {
            minDate: 0, 
          } );
       } );
  </script>';
}

/**
 * Save the in-stock date field for variable products
 *
 * @since 1.0.0
*/
add_action( 'woocommerce_save_product_variation', 'br_save_variation_in_stock_date_field', 10, 2 );
function br_save_variation_in_stock_date_field( $variation_id, $i ) {
  $in_stock_date_field = $_POST['br_in_stock_date_field_'.$variation_id];
  update_post_meta( $variation_id, '_br_in_stock_date_field', esc_attr( $in_stock_date_field ) );
}

/**
 * Adjust the availability text on the product page:
 *  1. Show expected date.
 *  2. Show real stock to distributors.
 *
 * @since 1.0.0
*/
add_filter( 'woocommerce_get_availability_text', 'br_custom_availability_text', 10, 2 );
function br_custom_availability_text( $availability, $product ) {
  $stock = $product->get_stock_quantity();
  $stock = max($stock,0);
  $in_stock_date = get_post_meta( $product->get_id(), '_br_in_stock_date_field', true );

  // Check if it's a measurement product
  if ( ! class_exists( 'WC_Price_Calculator_Product' ) ) {
    return;
  }
  $is_measurement = WC_Price_Calculator_Product::calculator_enabled( $product );

  // Clear in stock date if necessary
  if ( $stock > 0 && $product->managing_stock() ) {
    $in_stock_date = '';
    update_post_meta( $product->get_id(), '_br_in_stock_date_field', '' );
  }

  // Show stock if "always show stock count" is selected
  $product_id = $product->get_id();
  $product = wc_get_product($product_id);
  if ( $product->get_parent_id() > 0 ) {
    $product_id = $product->get_parent_id();
  }
  $show_stock = get_post_meta( $product_id, '_br_always_show_stock_field', true ) == 'on';

  // Show stock differently for distributors and if show_stock is true
  if ( is_distributor() || is_administrator() || $show_stock ) {
    if ( $product->is_in_stock() && $product->managing_stock() ) {
      if ( !$product->is_on_backorder() ) {
        if ( !$is_measurement ) {
          $availability = __( $stock . ' in stock', 'woocommerce' );
        }
      }
    }
  }

  // Show the expected date, if applicable
  if ( !$product->is_in_stock() || $product->is_on_backorder()) {
    if ( $in_stock_date != '' ) {
      $availability .= '<br />(Expected back by '.$in_stock_date.')';
    }
  }
  return $availability;
}


?>
