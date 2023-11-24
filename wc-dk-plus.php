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


add_action( 'init', 'initilize');

function initilize(){
    //Instantiate the admin page class
    $wc_dkplus_settings = new WC_DK_PLUS_Settings();
}


/**
 * Make request when order status is completed
 * 
 * @param int $order_id Order ID. 
 */
function make_get_reqeust( $order_id ){
    
    
    $plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

    if ( ! in_array( $plugin_path, wp_get_active_and_valid_plugins() ) ) {
        return;
    }
     
    // Get an instance of the WC_Order object
    $order = wc_get_order( $order_id );
    
    if ( ! $order ) {
        return;
    }
    
    $order_data = $order->get_data(); 
    
    

    $body = array(
        "Customer" => array(
            "Number"    => $order_data['customer_id'],
            "Name"      => $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'],
            "Address1"  => $order_data['billing']['address_1'],
            "Address2"  => $order_data['billing']['address_2'],
        ),
        "Options" => array(
            "OriginalPrices" => 0
        ),
        "Date" => $order->get_date_created(),
        "Currency" => "ISK",
        "Exchange" => 1,
        "Payments" => array(
            array(
                "ID" => 14,
                "Name" => $order_data['payment_method_title'],
                "Amount" => 1000
            )
        )
    );  
    
    foreach ($order->get_items() as $item_id => $item) {
        
        $product_id = $item->get_product_id();
        $variation_id = $item->get_variation_id();
        $product = $item->get_product(); // see link above to get $product info
        $product_name = $item->get_name();
        $quantity = $item->get_quantity();
        $subtotal = $item->get_subtotal();
        $total = $item->get_total();
        $tax = $item->get_subtotal_tax();
        $tax_class = $item->get_tax_class();
        $tax_status = $item->get_tax_status();
        $allmeta = $item->get_meta_data();
        $somemeta = $item->get_meta('_whatever', true);
        $item_type = $item->get_type(); // e.g. "line_item", "fee"
    
        // Add each product as a new element in the "Lines" array
        $body['Lines'][] = array(
            "ItemCode" => $product_id,
            "Text" => $product_name,
            "Quantity" => $quantity,
            "Reference" => "ABCD",
            "IncludingVAT" => false,
            "Price" => $subtotal,
            "Discount" => 0,
            "DiscountAmount" => 0,
            "Dim1" => ""
        );
    }

    //prettyPrint($order_data);
    
    prettyPrint($body);


    
    exit;
        
        
        
        // $order_details = [
        //     'order_id' => $order->get_id(),
        //     'order_number' => $order->get_order_number(),
        //     'order_date' => date('Y-m-d H:i:s', strtotime(get_post($order->get_id())->post_date)),
        //     'status' => $order->get_status(),
        //     'shipping_total' => $order->get_total_shipping(),
        //     'shipping_tax_total' => wc_format_decimal($order->get_shipping_tax(), 2),
        //     'fee_total' => wc_format_decimal($fee_total, 2),
        //     'fee_tax_total' => wc_format_decimal($fee_tax_total, 2),
        //     'tax_total' => wc_format_decimal($order->get_total_tax(), 2),
        //     'cart_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_cart_discount(), 2),
        //     'order_discount' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_order_discount(), 2),
        //     'discount_total' => wc_format_decimal($order->get_total_discount(), 2),
        //     'order_total' => wc_format_decimal($order->get_total(), 2),
        //     'order_currency' => $order->get_currency(),
        //     'payment_method' => $order->get_payment_method(),
        //     'shipping_method' => $order->get_shipping_method(),
        //     'customer_id' => $order->get_user_id(),
        //     'customer_user' => $order->get_user_id(),
        //     'customer_email' => ($a = get_userdata($order->get_user_id() )) ? $a->user_email : ”,
        //     'billing_first_name' => $order->get_billing_first_name(),
        // ];
    
    
        // $request = [
        //     'user_agent'    => 'WooocommerceDKPlus/0.0.1', 
        //     'endpoint'      => 'https://api.dkplus.is/api/v1/sales/invoice/2',
        //     'requestType'   => 'GET'
        // ];
        
        // $conn = new WC_DK_PLUS_API();
        // $conn->http_request( $request );
}

add_action( 'woocommerce_order_status_completed', 'make_get_reqeust' );


function prettyPrint($a) {
    echo "<pre>";
    print_r($a);
    echo "</pre>";
}