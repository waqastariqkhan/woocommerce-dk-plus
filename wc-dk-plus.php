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
require_once WC_DK_PLUS_DIR . 'includes/class-wc-dk-plus.php';

add_action( 'init', 'build_opt_landsearch_xml_feed');

function build_opt_landsearch_xml_feed(){
    $xml = new WC_DK_PLUS();
    $xml->init();
}