<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/waqastariqkhan
 *
 * @wordpress-plugin
 * @package           None
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
	exit;
}


define( 'WC_DK_PLUS_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_DK_PLUS_URL', plugin_dir_url( __FILE__ ) );



// Include the necessary files.
require_once WC_DK_PLUS_DIR . 'includes/class-wc-dk-plus-api.php';
require_once WC_DK_PLUS_DIR . 'admin/wc-dk-plus-admin-settings.php';

/**
 * Register Settings for DK Plus Plugin
 */
function wc_dk_plus_register_settings() {
	new WC_DK_PLUS_Settings();
}
add_action( 'init', 'wc_dk_plus_register_settings' );


/**
 * Make request when order status is completed
 *
 * @param int $order_id Order ID.
 */
function wc_dk_plus_generate_invoice( $order_id ) {

	$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

	if ( ! in_array( $plugin_path, wp_get_active_and_valid_plugins() ) ) {
		return;
	}

	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return;
	}

	$order_data    = $order->get_data();
	$wc_order_date = $order->get_date_created();

	if ( method_exists( $wc_order_date, 'getTimestamp' ) ) {
		$timestamp   = $wc_order_date->getTimestamp();
		$date_object = new DateTime();
		$date_object->setTimestamp( $timestamp );
	} else {
		$date_object = new DateTime();
	}

	$formatted_date = $date_object->format( 'Y-m-d H:i:s' );

	$body = array(
		'Reference'   => $order_id,
		'Customer'    => array(
			'Number'   => empty( $order_data['customer_id'] ) ? $order_id : $order_data['customer_id'],
			'Name'     => $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'],
			'Address1' => $order_data['billing']['address_1'],
			'Address2' => $order_data['billing']['address_2'],
		),
		'SalesPerson' => 'vefur',
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

		$product      = $item->get_product();
		$product_name = $product->get_name();
		$sku          = $product->get_sku();
		$quantity     = (int) $item->get_quantity();
		$subtotal     = $item->get_subtotal();
		$total        = (int) ceil( $item->get_total() / $quantity ) ;

		$body['Lines'][] = array(
			'ItemCode'       => $sku,
			'Text'           => $product_name,
			'Quantity'       => $quantity,
			'IncludingVAT'   => true,
			'Price'          => $total,
			'Discount'       => 0,
			'DiscountAmount' => 0,
		);
	}

	if ( $order->get_shipping_method() === 'Heimsending/Delivery' ) {
		$body['Lines'][] = array(
			'ItemCode'     => 'heimsending/delivery',
			'Text'         => $order->get_shipping_method(),
			'Quantity'     => 1,
			'IncludingVAT' => true,
			'Price'        => $order->get_shipping_total(),
		);

	}

	$payload = wp_json_encode( $body, JSON_PRETTY_PRINT );

	$request = array(
		'user_agent'   => 'WooocommerceDKPlus/0.0.1',
		'endpoint'     => 'https://api.dkplus.is/api/v1/sales/invoice/?post=true',
		'request_type' => 'POST',
	);

	$conn     = new WC_DK_PLUS_API();
	$response = $conn->http_request( $request, $payload );

	$file_path = WC_DK_PLUS_DIR . '/log.txt';

	if ( $response['error'] ) {
		file_put_contents( $file_path, 'API Error: ' . $response['message'] . "\n", FILE_APPEND | LOCK_EX );
	} else {
		file_put_contents( $file_path, 'Invoice for DK Plus with Order ID: ' . $order_id . "\n", FILE_APPEND | LOCK_EX );
	}

	return $response;
}

add_action( 'woocommerce_order_status_processing', 'wc_dk_plus_generate_invoice' );

/**
 * Send wocoommerce published products to the DK System
 */
function wc_dk_plus_create_products() {

	if ( ! empty( $_GET['dk_product_import'] ) ) {

		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}
		$existing_products = get_dk_existing_products();

		$products = wc_get_products(
			array(
				'status' => 'publish',
				'limit'  => -1,
			)
		);

		$file_path = WC_DK_PLUS_DIR . '/log.txt';

		if ( file_exists( $file_path ) ) {
			unlink( $file_path );
		}

		touch( $file_path );

		prettyPrint( $existing_products );

		foreach ( $products as $product ) {

			if ( empty( $product->get_sku() ) ) {
				file_put_contents( $file_path, 'Products without SKU ID: ' . $product->get_id() . "\n", FILE_APPEND | LOCK_EX );
				continue;
			}

			if ( in_array( $product->get_sku(), $existing_products ) ) {
				file_put_contents( $file_path, "Existing DK Products' SKU: " . $product->get_sku() . "\n", FILE_APPEND | LOCK_EX );
				continue;
			}

			$included_vat = $product->get_price();

			$base_price = calculate_base_price( $included_vat, 11 );

			$body = array(
				'ItemCode'    => $product->get_sku(),
				'Description' => $product->get_title(),
				'TaxPercent'  => 11,
				'UnitPrice1'  => $base_price,
			);

			$payload = wp_json_encode( $body, JSON_PRETTY_PRINT );

			$request = array(
				'user_agent'   => 'WooocommerceDKPlus/0.0.1',
				'endpoint'     => 'https://api.dkplus.is/api/v1/Product/',
				'request_type' => 'POST',
			);

			$conn     = new WC_DK_PLUS_API();
			$response = $conn->http_request( $request, $payload );

			if ( $response['error'] ) {
				file_put_contents( $file_path, 'API Error: ' . $response['message'] . "\n", FILE_APPEND | LOCK_EX );
			} else {
				file_put_contents( $file_path, 'Product Added in DK with SKU: ' . $product->get_sku() . "\n", FILE_APPEND | LOCK_EX );
			}
		}
		echo 'completed: check log in /log.txt';
		exit;
	}
}

add_action( 'init', 'wc_dk_plus_create_products' );

/**
 * Get a list of existing products in the DK System
 */
function get_dk_existing_products() {
	$request            = array(
		'user_agent'   => 'WooocommerceDKPlus/0.0.1',
		'endpoint'     => 'https://api.dkplus.is/api/v1/Product?inactive=false',
		'request_type' => 'GET',
	);
	$conn               = new WC_DK_PLUS_API();
	$payload            = array();
	$response           = $conn->http_request( $request, $payload );
	$extracted_products = $response['data'];
	$item_codes         = array();
	foreach ( $extracted_products as $product ) {
		if ( ! property_exists( $product, 'Deleted' ) ) {
				$item_codes[] = $product->ItemCode;
		}
	}
	return $item_codes;
}

/**
 * Calculate base price of the product excluding VAT
 *
 * @param int $price_including_vat price including vat.
 * @param int $vat_rate total VAT percentage.
 */
function calculate_base_price( $price_including_vat, $vat_rate ) {
	$vat_rate   = $vat_rate / 100;
	$base_price = (int) $price_including_vat / ( 1 + $vat_rate );
	$base_price = round( $base_price, 2 );
	return $base_price;
}
