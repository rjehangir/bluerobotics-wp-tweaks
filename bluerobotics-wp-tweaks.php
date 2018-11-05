<?php
/**
 * Plugin Name: Blue Robotics WP Tweaks
 * Plugin URI: http://bluerobotics.com
 * Description: Small and miscellaneous tweaks to Wordpress and WooCommerce for the bluerobotics.com website.
 * Author: Rustom Jehangir
 * Author URI: http://rstm.io
 * Version: 1.0.1
 *
 * Copyright: (c) 2018 Rustom Jehangir
 *
 * @author    Rustom Jehangir
 * @copyright Copyright (c) 2018, Rustom Jehangir
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * External files for organization.
 */
include('po-number-field.php');
include('br-pricing-tab.php');

/**
 * Display number sold for launch
 *
 * @since 1.0.0
*/
function brov2_func( $atts ) {
	$units_sold = get_post_meta( 8104, 'total_sales', true )+get_post_meta( 8104, 'unrecorded_sales', true );
	return sprintf( __( '%s', 'woocommerce' ), $units_sold );
}
add_shortcode( 'brov2_sold', 'brov2_func' );

/**
 * Bundle composite product weights for shipping purposes
 *
 * @since 1.0.0
*/
add_filter( 'woocommerce_composited_product_has_bundled_weight', 'wc_cp_bundled_weight', 10, 4 );
function wc_cp_bundled_weight( $has_bundled_weight, $product, $component_id, $composite ) {
	return true;
}

/**
 * Add Widget for WooCommerce Product Search
 *
 * @since 1.0.0
*/
function add_search_nav_item($items, $args) {
  if (!is_admin() && ($args->theme_location == 'primary_navigation' || $args->theme_location == 'forum_navigation' || $args->theme_location == 'store_navigation') ) {
    $items .= '<script language="javascript" type="text/javascript">function SetSearchFocus() {window.setTimeout(function () {document.getElementById("product-search-field-0").focus();}, 0);}</script>';
	$items .= '<li class="dropdown">';
    $items .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" onclick="SetSearchFocus()"><i class="fa fa-search" aria-hidden="true"></i></a>';
    $items .= '<ul class="dropdown-menu multi-column columns-2" role="menu" style="margins:20px"><li>';
    $items .= do_shortcode("[widget id='woocommerce_product_search_widget-2']");
	$items .= '</li></ul></li>';
  }
  return $items;
}
add_filter( 'wp_nav_menu_items', 'add_search_nav_item', 10, 2 );

/**
 * Prevent virtual items from showing up in the PDF packing slips
 *
 * @since 1.0.0
*/
add_filter( 'wpo_wcpdf_order_items_data', 'wpo_wcpdf_remove_downloadable_items', 10, 3 );
function wpo_wcpdf_remove_downloadable_items ( $items_data, $order, $document_type ) {
    if ( $document_type == 'packing-slip' ) {
        foreach ($items_data as $key => $item) {
            // Check if product is downloadable or virtual
            if ( !empty($item['product']) && ( $item['product']->is_downloadable() || $item['product']->is_virtual() ) ) {
                // if true - remove item from packing slip
                unset( $items_data[$key] );
            }
        }
    }
    return $items_data;
}

/**
 * Change text on WooCommerce order button.
 *
 * @since 1.0.0
*/
add_filter( 'woocommerce_order_button_text', 'woo_custom_order_button_text' ); 
function woo_custom_order_button_text() {
    return __( 'Place order (please wait for order to be placed)', 'woocommerce' ); 
}

/**
 * Remove related products on single product page. Called after init.
 *
 * @since 1.0.0
*/
function remove_related_products_action() {
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
}

/**
 * Check for distributor role.
 *
 * @since 1.0.0
*/
function is_distributor() {
	return current_user_can('distributor');
}

/*add_filter( 'rp_wcdpd_volume_pricing_table_display', 'rusty_test_display', 10, 4 );
function rusty_test_display($isIt, $data, $product, $is_variable) {
	if ($product->get_type() == 'composite') {
		if ( is_distributor() ) {
			echo '<p style="font-weight:600">(Distributor pricing appears in cart.)</p>';
		} {
			//echo '<p style="font-weight:600">(Quantity pricing appears in cart.)</p>';
		}
		return false;
	} else {
		return true;
	}
}*/

add_filter('rp_wcdpd_volume_pricing_table_title','rusty_change_table_title',10,4);
function rusty_change_table_title($label, $product, $data, $is_variable) {
	// Return the "public note" from the quantity discount plugin
	return $data[0]['rule']['public_note'];
}

// Display the discounts as a percentage for composite products only
add_filter('rp_wcdpd_volume_pricing_table_range_value','rusty_change_composite_value',10,5);
function rusty_change_composite_value($display_value, $raw_value, $product, $rule, $quantity_range) {
	if ($product->get_type() == 'composite') {
		if ( $quantity_range['pricing_value'] == "0" ) {
			return "-";
		}
		return $quantity_range['pricing_value'] . "%";
		return "See cart";
	}
	return $display_value;
}

/*add_filter('rp_wcdpd_volume_pricing_table_data', 'rusty_change_data', 10, 3);
function rusty_change_data($data, $product, $rule) {
	$new_data = array(array("range_label"=>"1-2","range_price"=>"-","price_raw"=>"","from"=>0),array("range_label"=>"3+","range_price"=>"-10%","price_raw"=>"","from"=>0));
	return $new_data;
}*/

/**
 * Turn off Table Output Caching for all tables by default.
 *
 * @since 1.0.0
 *
 * @param array $render_options Render Options.
 * @param array $table          Table.
 *
 * @return array Modified Render Options.
 */
function tablepress_turn_off_output_caching( $render_options, $table ) {
	$render_options['cache_table_output'] = false;
	return $render_options;
}
add_filter( 'tablepress_table_render_options', 'tablepress_turn_off_output_caching', 10, 2 );

/**
 * Trim zeros in price decimals
 *
 * @since 1.0.0
*/
function round_prices_on_store($trim) {
	if ( is_product_category() || is_shop() ) {
			return true;
	}
}
//add_filter( 'woocommerce_price_trim_zeros', 'round_prices_on_store', 10, 1);

/**
 * Hide categories from WordPress category widget
 *
 * @since 1.0.0
*/
add_filter( 'woocommerce_product_categories_widget_args', 'woo_product_cat_widget_args' );
function woo_product_cat_widget_args( $cat_args ) {
    $cat_args['exclude'] = array('406','431','113','20','428');
    return $cat_args;
}

/**
 * Format order number with invoice number settings
 */
add_filter( 'wpo_wcpdf_raw_document_number', 'wpo_wcpdf_raw_document_number', 10, 4 );
function wpo_wcpdf_raw_document_number( $number, $settings, $document, $order ) {
    if ( $document->get_type() == 'invoice' ) {
        $number = $order->get_order_number();
    }
 
    return $number;
}

/**
 * Add distributor information box when logged in.
 */
add_action( 'woocommerce_archive_description', 'distributor_info_box', 15 );
function distributor_info_box() {
	if ( is_distributor() || current_user_can('administrator') ) {
		$id=27225;
		$post = get_page($id);
		$content = apply_filters('the_content', $post->post_content);

		echo '<div class="well well-sm distributor-info-well">';
		echo $content;
		echo '</div>';
	}
}

/**
 * Change some text strings
 *
 * @since 1.0.0
*/
function change_text_strings( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {
        case '(can be backordered)':
            $translated_text = __( '', 'woocommerce' );
            break;
	}
    return $translated_text;
}
add_filter( 'gettext', 'change_text_strings', 10, 3 );

/**
 * Function to call actions that must be done after init, such as removing filters.
 *
 * @since 1.0.0
*/
function after_init() {
	remove_related_products_action();
}
add_action( 'init', 'after_init', 11 );

?>
