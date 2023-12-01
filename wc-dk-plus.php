<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/waqastariqkhan
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce DK Plus - Invoice Generator
 * Plugin URI:        https://github.com/waqastariqkhan/woocommerce-dk-plus
 * Description:       Generate an invoice in the DK plus management system when an order is placed in the Woocommerce
 * Version:           1.0.0
 * Author:            Waqas Tariq
 * Author URI:        https://github.com/waqastariqkhan
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-dk-plus
 * Domain Path:       /languages
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


define( 'WC_DK_PLUS_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_DK_PLUS_URL', plugin_dir_url( __FILE__ ) );



// Include the necessary files.
require_once WC_DK_PLUS_DIR . 'includes/class-wc-dk-plus-api.php';
require_once WC_DK_PLUS_DIR . 'admin/wc-dk-plus-admin-settings.php';


add_action( 'init', 'initilize' );

function initilize() {
	// Instantiate the admin page class
	$wc_dkplus_settings = new WC_DK_PLUS_Settings();
}


/**
 * Make request when order status is completed
 *
 * @param int $order_id Order ID.
 */
function make_get_reqeust( $order_id ) {

	$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

	if ( ! in_array( $plugin_path, wp_get_active_and_valid_plugins() ) ) {
		return;
	}

	// Get an instance of the WC_Order object
	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return;
	}

	$order_data    = $order->get_data();
	$WC_order_date = $order->get_date_created();

	if ( method_exists( $WC_order_date, 'getTimestamp' ) ) {
		$timestamp   = $WC_order_date->getTimestamp();
		$date_object = new DateTime();
		$date_object->setTimestamp( $timestamp );
	} else {
		$date_object = new DateTime();
	}

	$formatted_date = $date_object->format( 'Y-m-d H:i:s' );

	$body = array(
		'Reference'   => $order_id,
		'Customer'    => array(
			'Number'   => $order_data['customer_id'],
			'Name'     => $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'],
			'Address1' => $order_data['billing']['address_1'],
			'Address2' => $order_data['billing']['address_2'],
		),
		'SalesPerson' => 'Waqas',
		'Options'     => array(
			'OriginalPrices' => 0,
		),
		'Date'        => $formatted_date,
		'Currency'    => 'ISK',
		'Exchange'    => 1,
		'Payments'    => array(
			array(
				'ID'     => 14,
				'Name'   => $order_data['payment_method_title'],
				'Amount' => $order->get_total(),
			),
		),
	);

	if ( ! $order->get_items() ) {
		return;
	}

	foreach ( $order->get_items() as $item_id => $item ) {

		$product_id   = $item->get_product_id();
		$variation_id = $item->get_variation_id();
		$product      = $item->get_product(); // see link above to get $product info
		$product_name = $item->get_name();
		$quantity     = $item->get_quantity();
		$subtotal     = $item->get_subtotal();
		$total        = $item->get_total();

		// Add each product as a new element in the "Lines" array
		$body['Lines'][] = array(
			'ItemCode'       => $product_id,
			'Text'           => $product_name,
			'Quantity'       => $quantity,
			'IncludingVAT'   => false,
			'Price'          => $total,
			'Discount'       => 0,
			'DiscountAmount' => 0,
		);
	}

	$payload = json_encode( $body, JSON_PRETTY_PRINT );

	$request = array(
		'user_agent'   => 'WooocommerceDKPlus/0.0.1',
		'endpoint'     => 'https://api.dkplus.is/api/v1/sales/invoice/?post=true',
		'request_type' => 'POST',
	);

	$conn     = new WC_DK_PLUS_API();
	$response = $conn->http_request( $request, $payload );
    
	return $response;
}

add_action( 'woocommerce_order_status_completed', 'make_get_reqeust' );


function prettyPrint( $a ) {
	echo '<pre>';
	print_r( $a );
	echo '</pre>';
}
