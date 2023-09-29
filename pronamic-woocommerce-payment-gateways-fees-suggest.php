<?php
/**
 * Pronamic WooCommerce Payment Gateways Fees Suggest
 *
 * @package   PronamicWooCommercePaymentGatewaysFeesSuggest
 * @author    Pronamic
 * @copyright 2023 Pronamic
 * 
 * @wordpress-plugin
 * Plugin Name: Pronamic WooCommerce Payment Gateways Fees Suggest
 * Description: This plugin suggests WooCommerce payment gateway fees plugins.
 * Version:     1.0.0
 * Author:      Pronamic
 * Author URI:  https://www.pronamic.eu/
 * Text Domain: pronamic-woocommerce-payment-gateways-fees-suggest
 * Domain Path: /languages/
 * License:     Proprietary
 * License URI: https://www.pronamic.shop/product/pronamic-woocommerce-payment-gateways-fees-suggest/
 * Update URI:  https://wp.pronamic.directory/plugins/pronamic-woocommerce-payment-gateways-fees-suggest/
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
		load_plugin_textdomain( 'pronamic-woocommerce-payment-gateways-fees-suggest', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}
);

\Pronamic\WooCommercePaymentGatewaysFeesSuggest\Plugin::instance()->setup();
