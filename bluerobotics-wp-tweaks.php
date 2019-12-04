<?php
/**
 * Plugin Name: Blue Robotics WP Tweaks
 * Plugin URI: http://bluerobotics.com
 * Description: Small and miscellaneous tweaks to Wordpress and WooCommerce for the bluerobotics.com website.
 * Author: Rustom Jehangir
 * Author URI: http://rstm.io
 * Version: 1.1.5
 *
 * Copyright: (c) 2019 Rustom Jehangir
 *
 * @author    Rustom Jehangir
 * @copyright Copyright (c) 2019, Rustom Jehangir
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * External files for organization.
 */
include('po-number-field.php');
include('stock-status.php');
include('payment-terms-field.php');
include('checkout-survey-fields.php');
include('very-short-descriptions.php');
include('warranty-tracking.php');

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
//add_filter( 'woocommerce_order_button_text', 'woo_custom_order_button_text' ); 
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

/**
 * Check for distributor role.
 *
 * @since 1.0.0
*/
function is_administrator() {
    return current_user_can('administrator');
}

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
 * Add link to distributor portal from the account page when logged in.
 */
add_filter ( 'woocommerce_account_menu_items', 'distributor_portal_myaccount_link' );
function distributor_portal_myaccount_link( $menu_links ){
 	if ( is_distributor() || current_user_can('administrator') ) {
		$new = array( 'distributor-portal-endpoint' => 'Distributor Portal' );
	 
		// or in case you need 2 links
		// $new = array( 'link1' => 'Link 1', 'link2' => 'Link 2' );
	 
		// array_slice() is good when you want to add an element between the other ones
		$menu_links = array_slice( $menu_links, 0, 1, true ) 
		+ $new 
		+ array_slice( $menu_links, 1, NULL, true );
	 
	 
		return $menu_links;
 	}
 	return $menu_links;
}
 
add_filter( 'woocommerce_get_endpoint_url', 'distributor_myaccount_portal_hook_endpoint', 10, 4 );
function distributor_myaccount_portal_hook_endpoint( $url, $endpoint, $value, $permalink ){
 
	if( $endpoint === 'distributor-portal-endpoint' ) {
 
		// ok, here is the place for your custom URL, it could be external
		$url = site_url().'/distributor-portal/';
 
	}
	return $url;
 
}

/**
 * Add an "other" information column to the product view.
 */
add_filter( 'manage_edit-product_columns', 'br_product_info_add_column' );
function br_product_info_add_column( $columns ) {
    $columns['other'] = 'Other';
    return $columns;
}

/**
 * Populate the "other" information column with helpful data.
 */
add_action( 'manage_product_posts_custom_column', 'br_product_info_column_contents' );
function br_product_info_column_contents( $column ) {
   
    global $post;
 
    if ( 'other' === $column ) {
 		echo get_post_meta( $post->ID, 'total_sales', true ).' sold total';
 		if ( get_post_meta( $post->ID, '_br_pricing_distributor_pricing', true ) == '' ) {
 			echo '<br /><span class="dashicons dashicons-no"></span>Distributor Price Not Set';
 		} elseif ( get_post_meta( $post->ID, '_br_pricing_distributor_pricing', true ) == 'other' ) {
 			echo '<br /><span class="dashicons dashicons-admin-settings"></span>Manual Distributor Pricing';
 		} elseif ( get_post_meta( $post->ID, '_br_pricing_distributor_pricing', true ) == 'no_discount' ) {
 			echo '<br /><strong>0%</strong> Distributor Discount';
 		} else {
 			//echo '<span class="dashicons dashicons-no"></span>Distributor Price. ';
 		}
        if ( get_post_meta( $post->ID, '_br_very_short_description', true ) == '' ) {
            echo '<br /><span class="dashicons dashicons-no"></span>Very short description not set';
        }
    }
}

/**
 * Notify customers of backordered items in the cart.
 */
add_action( 'woocommerce_before_checkout_form', 'br_checkout_add_cart_backorder_notice' );

function br_checkout_add_cart_backorder_notice() {
    $message = 'You have a backordered product in your cart! Your order may not ship immediately. Return to the <a href="/cart/">cart</a> to remove any backordered items if necessary.';

    if ( br_check_cart_has_backorder_product() ) 
        wc_add_notice( $message, 'error' );

}

function br_check_cart_has_backorder_product() {
    foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
        $cart_product =  wc_get_product( $values['data']->get_id() );

        if( $cart_product->is_on_backorder() )
            return true;
    }

    return false;
}

/**
 * Set checkout address field to 35 characters max length for shipstation.
 */
add_filter( 'woocommerce_checkout_fields' , 'br_checkout_address_length' );

function br_checkout_address_length( $fields ) { 
	$fields['billing']['billing_address_1']['maxlength'] = 35; 
	$fields['billing']['billing_address_2']['maxlength'] = 35; 
	$fields['shipping']['shipping_address_1']['maxlength'] = 35; 
	$fields['shipping']['shipping_address_2']['maxlength'] = 35; 
	return $fields;
}

/**
 * Set shipping city to Singapore if the country is Singapore.
 */
add_action( 'wp_footer', 'br_singapore_shipping_fix' );
function br_singapore_shipping_fix() {
    // Only checkout page
    if( is_checkout() && ! is_wc_endpoint_url() ):
    ?>
    <script type="text/javascript">
        jQuery(function($){
            // Utility function to convert billing or shipping city and postcode checkout fields based on state
            function checkForSingapore() {
                if ( document.getElementById('shipping_country').value == 'SG' ) {
                	document.getElementById('shipping_city').value = 'Singapore';
                }
                if ( document.getElementById('billing_country').value == 'SG' ) {
                	document.getElementById('billing_city').value = 'Singapore';
                }
            }

            // 1. Once DOM is loaded
            checkForSingapore();

            // 2. On "state" field change event
            $('shipping_country').on('change', function() {
                checkForSingapore();
            });
            $('billing_country').on('change', function() {
                checkForSingapore();
            });
        });
    </script>
    <?php
    endif;
};

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
