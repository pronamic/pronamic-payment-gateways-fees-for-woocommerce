<?php
/**
 * Pronamic Payment Gateways Fees for WooCommerce
 *
 * @package   PronamicWooCommercePaymentGatewaysFees
 * @author    Pronamic
 * @copyright 2023 Pronamic
 * 
 * @wordpress-plugin
 * Plugin Name: Pronamic Payment Gateways Fees for WooCommerce
 * Description: This WordPress plugin adds settings to all WooCommerce gateways to add a fixed and/or variable (percentage) fee.
 * Version:     1.0.0
 * Author:      Pronamic
 * Author URI:  https://www.pronamic.eu/
 * Text Domain: pronamic-payment-gateways-fees-for-woocommerce
 * Domain Path: /languages/
 * License:     Proprietary
 * License URI: https://www.pronamic.shop/product/pronamic-payment-gateways-fees-for-woocommerce/
 * Update URI:  https://wp.pronamic.directory/plugins/pronamic-payment-gateways-fees-for-woocommerce/
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
		load_plugin_textdomain( 'pronamic-payment-gateways-fees-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}
);

\Pronamic\WooCommercePaymentGatewaysFees\Plugin::instance()->setup();
