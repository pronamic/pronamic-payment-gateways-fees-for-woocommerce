<?php
/**
 * Pronamic WooCommerce Payment Gateways Fees Suggest Plugin
 *
 * @package   PronamicWooCommercePaymentGatewaysFeesSuggest
 * @author    Pronamic
 * @copyright 2023 Pronamic
 */

namespace Pronamic\WooCommercePaymentGatewaysFeesSuggest;

/**
 * Pronamic WooCommerce Payment Gateways Fees Suggest Plugin class
 */
class Plugin {
	/**
	 * Instance of this class.
	 *
	 * @since 4.7.1
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return self A single instance of this class.
	 */
	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		if ( \has_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] ) ) {
			return;
		}

		\add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
	}

	/**
	 * Plugins loaded.
	 * 
	 * @return void
	 */
	public function plugins_loaded() {
		if ( ! \function_exists( 'WC' ) ) {
			return;
		}

		\add_action( 'init', [ $this, 'init' ], 1000 );
	}

	/**
	 * Init.
	 * 
	 * @return void
	 */
	public function init() {
		$payment_gateways = \WC()->payment_gateways()->payment_gateways();

		foreach ( $payment_gateways as $payment_gateway ) {
			\add_filter( 'woocommerce_settings_api_form_fields_' . $payment_gateway->id, [ $this, 'add_fees_setting' ] );
		}
	}

	/**
	 * Add fees setting field the specified fields.
	 * 
	 * @link https://woocommerce.com/document/settings-api/
	 * @link https://github.com/woocommerce/woocommerce/blob/473a53d54243c6b749a4532112eea4ac8667447f/plugins/woocommerce/includes/shipping/legacy-local-pickup/class-wc-shipping-legacy-local-pickup.php#L133-L143
	 * @link https://github.com/woocommerce/woocommerce/blob/473a53d54243c6b749a4532112eea4ac8667447f/plugins/woocommerce/includes/shipping/legacy-flat-rate/includes/settings-flat-rate.php#L41-L51
	 * @link https://github.com/woocommerce/woocommerce/blob/473a53d54243c6b749a4532112eea4ac8667447f/plugins/woocommerce/includes/gateways/cod/class-wc-gateway-cod.php#L118-L130
	 * @param array $fields Fields.
	 * @return array
	 */
	public function add_fees_setting( $fields ) {
		$fields['pronamic_fees_title'] = [
			'title' => \__( 'Fees suggest', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'  => 'title',
		];

		return $fields;
	}
}
