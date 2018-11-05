<?php
/**
 * This file adds a BR pricing tab to allow selection of quantity pricing categories
 * and exceptions to typical rules.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Add a custom tab BR pricing tab.
 *
 * @since 1.0.0
*/
add_filter( 'woocommerce_product_data_tabs', 'add_br_pricing_tab', 10, 1 );
function add_br_pricing_tab( $product_data_tabs ) {
	$product_data_tabs['br-pricing-tab'] = array(
		'label'  => __( 'BR Pricing', 'woocommerce' ),
		'target' => 'br_pricing_data',
		'class'  => array( '' ),
	);
	return $product_data_tabs;
}

/**
 * Change admin menu icon.
 *
 * @since 1.0.0
*/
add_action('admin_head', 'br_pricing_css_icon');
function br_pricing_css_icon() {
	echo '<style>
	#woocommerce-product-data ul.wc-tabs li.br-pricing-tab_options a:before{
		content: "\f323";
	}
	</style>';
}

/**
 * Set up tab data fields.
 *
 * @since 1.0.0
*/
add_action('woocommerce_product_data_panels', 'br_pricing_data_fields');
function br_pricing_data_fields() {
    global $post;

    // Note the 'id' attribute needs to match the 'target' parameter set above
    ?> <div id = 'br_pricing_data'
    class = 'panel woocommerce_options_panel' > <?php
        ?> <div class = 'options_group' > <?php
  // Distributor pricing
  woocommerce_wp_select(
    array(
      'id' => '_br_pricing_distributor_pricing',
      'label' => __( 'Distributor Pricing', 'woocommerce' ),
      'description' => 'Stored in meta field _br_pricing_distributor_pricing',
      'options' => array(
         'other' => __( 'I will make a direct rule in the Pricing & Discounts page', 'woocommerce' ),
         'no_discount' => __( 'No Distributor Discount', 'woocommerce' ),
         'distributor_10_percent' => __( '10% Distributor Discount', 'woocommerce' ),
         'distributor_20_percent' => __( '20% Distributor Discount', 'woocommerce' ),
         'distributor_30_percent' => __( '30% Distributor Discount (standard for most products)', 'woocommerce' ),
      )
    )
  );

  // Quantity pricing
  woocommerce_wp_select(
    array(
      'id' => '_br_pricing_quantity_pricing',
      'label' => __( 'Quantity Pricing', 'woocommerce' ),
      'description' => 'Stored in meta field _br_pricing_quantity_pricing',
      'options' => array(
         'none' => __( 'No Quantity Pricing (default)', 'woocommerce' ),
         'other' => __( 'I will make a direct rule in the Pricing & Discounts page', 'woocommerce' ),
         'standard_10plus' => __( 'Standard Quantity Pricing - Up to 10+', 'woocommerce' ),
         'standard_25plus' => __( 'Standard Quantity Pricing - Up to 25+', 'woocommerce' ),
         'standard_50plus' => __( 'Standard Quantity Pricing - Up to 50+', 'woocommerce' ),
         'standard_100plus' => __( 'Standard Quantity Pricing - Up to 100+', 'woocommerce' ),
         'standard_250plus' => __( 'Standard Quantity Pricing - Up to 250+', 'woocommerce' ),
         'penetrator_250plus' => __( 'Penetrator Quantity Pricing - Up to 250+', 'woocommerce' ),
      )
    )
  );
        ?> </div>

    	<p>The distributor and quantity discounts are set up in the <a href="/wp-admin/admin.php?page=rp_wcdpd_settings">Dynamic Pricing & Discounts</a> plugin. Each discount references a product meta data field that is applied with the above selection. For special items, rules can be made directly as well.
		</p>
    </div><?php
}

/**
 * Save pricing fields to post meta data.
 *
 * @since 1.0.0
*/
function br_pricing_save_proddata_custom_fields($post_id) {
    // Save fields
    $br_pricing_distributor_pricing = $_POST['_br_pricing_distributor_pricing'];
    if (!empty($br_pricing_distributor_pricing)) {
        update_post_meta($post_id, '_br_pricing_distributor_pricing', esc_attr($br_pricing_distributor_pricing));
    }

    $br_pricing_quantity_pricing = $_POST['_br_pricing_quantity_pricing'];
    if (!empty($br_pricing_quantity_pricing)) {
        update_post_meta($post_id, '_br_pricing_quantity_pricing', esc_attr($br_pricing_quantity_pricing));
    }
}
add_action( 'woocommerce_process_product_meta', 'br_pricing_save_proddata_custom_fields'  );


?>
