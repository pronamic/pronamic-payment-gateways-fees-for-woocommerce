<?php
/**
 * Pronamic WooCommerce Payment Gateways Fees
 *
 * @package   PronamicWooCommercePaymentGatewaysFees
 * @author    Pronamic
 * @copyright 2023 Pronamic
 * 
 * @wordpress-plugin
 * Plugin Name: Pronamic WooCommerce Payment Gateways Fees
 * Description: This plugin suggests WooCommerce payment gateway fees plugins.
 * Version:     1.0.0
 * Author:      Pronamic
 * Author URI:  https://www.pronamic.eu/
 * Text Domain: pronamic-woocommerce-payment-gateways-fees
 * Domain Path: /languages/
 * License:     Proprietary
 * License URI: https://www.pronamic.shop/product/pronamic-woocommerce-payment-gateways-fees/
 * Update URI:  https://wp.pronamic.directory/plugins/pronamic-woocommerce-payment-gateways-fees/
 */

/**
 * Autoload.
 */
require_once __DIR__ . '/vendor/autoload_packages.php';

/**
 * Bootstrap.
 */
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( 'pronamic-woocommerce-payment-gateways-fees', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}
);

\Pronamic\WooCommercePaymentGatewaysFees\Plugin::instance()->setup();
