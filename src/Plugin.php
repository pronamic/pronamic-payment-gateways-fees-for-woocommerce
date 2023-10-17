<?php
/**
 * Pronamic Payment Gateways Fees for WooCommerce Plugin
 *
 * @package   PronamicWooCommercePaymentGatewaysFees
 * @author    Pronamic
 * @copyright 2023 Pronamic
 */

namespace Pronamic\WooCommercePaymentGatewaysFees;

use Pronamic\WordPress\Number\Number;
use WC_Cart;
use WC_Order_Item_Fee;
use WC_Payment_Gateway;

/**
 * Pronamic Payment Gateways Fees for WooCommerce Plugin class
 */
class Plugin {
	/**
	 * Instance of this class.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Total.
	 * 
	 * @var string|null
	 */
	private $total;

	/**
	 * Return an instance of this class.
	 *
	 * @return self A single instance of this class.
	 */
	public static function instance() {
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

		\add_action( 'woocommerce_cart_calculate_fees', [ $this, 'woocommerce_cart_calculate_fees' ] );

		\add_action( 'woocommerce_after_calculate_totals', [ $this, 'woocommerce_after_calculate_totals' ] );

		\add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		\add_action( 'woocommerce_checkout_create_order_fee_item', [ $this, 'woocommerce_checkout_create_order_fee_item' ], 10, 2 );

		\add_action( 'woocommerce_pay_order_after_submit', [ $this, 'woocommerce_pay_order_after_submit' ] );
		\add_action( 'woocommerce_before_pay_action', [ $this, 'woocommerce_before_pay_action' ] );

		\add_action( 'before_woocommerce_pay_form', [ $this, 'before_woocommerce_pay_form' ], 10, 3 );
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

		$file = '../js/dist/checkout.min.js';

		\wp_register_script(
			'pronamic-payment-gateways-fees-for-woocommerce-checkout',
			\plugins_url( $file, __FILE__ ),
			[
				'jquery',
			],
			\hash_file( 'crc32b', __DIR__ . '/' . $file ),
			true
		);

		$file = '../js/dist/order-pay.min.js';

		\wp_register_script(
			'pronamic-payment-gateways-fees-for-woocommerce-order-pay',
			\plugins_url( $file, __FILE__ ),
			[
				'jquery',
			],
			\hash_file( 'crc32b', __DIR__ . '/' . $file ),
			true
		);
	}

	/**
	 * Enqueue scripts.
	 * 
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! \is_checkout() ) {
			return;
		}

		\wp_enqueue_script( 'pronamic-payment-gateways-fees-for-woocommerce-checkout' );
	}

	/**
	 * Add fees setting field the specified fields.
	 * 
	 * @link https://woocommerce.com/document/settings-api/
	 * @link https://github.com/woocommerce/woocommerce/blob/473a53d54243c6b749a4532112eea4ac8667447f/plugins/woocommerce/includes/shipping/legacy-local-pickup/class-wc-shipping-legacy-local-pickup.php#L133-L143
	 * @link https://github.com/woocommerce/woocommerce/blob/473a53d54243c6b749a4532112eea4ac8667447f/plugins/woocommerce/includes/shipping/legacy-flat-rate/includes/settings-flat-rate.php#L41-L51
	 * @link https://github.com/woocommerce/woocommerce/blob/473a53d54243c6b749a4532112eea4ac8667447f/plugins/woocommerce/includes/gateways/cod/class-wc-gateway-cod.php#L118-L130
	 * @link https://github.com/mollie/WooCommerce/blob/e070103d8d899832e23ac2aff7dd120254e2eede/src/Settings/General/MollieGeneralSettings.php
	 * @lin khttps://github.com/woocommerce/woocommerce/blob/8ce50fb4198599c6b125035cdfd0787df5aaddc1/plugins/woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 * @param array $fields Fields.
	 * @return array
	 */
	public function add_fees_setting( $fields ) {
		$fields['pronamic_fees_title'] = [
			'title'       => \__( 'Fees', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'type'        => 'title',
			'description' => \__( 'Please note that you are not always allowed to charge surcharges for payment methods.', 'pronamic-payment-gateways-fees-for-woocommerce' ),
		];

		$fields['pronamic_fees_fixed_name'] = [
			'title'       => \__( 'Fixed fee name', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'type'        => 'text',
			'default'     => \__( 'Payment gateway fee', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'placeholder' => \__( 'Payment gateway fee', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'description' => \__( 'Fee name to show to customer. To display each fee on different lines in cart (and checkout), you must set different names. If names are equal they will be merged into single line.', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'desc_tip'    => true,
		];

		$fields['pronamic_fees_fixed_amount'] = [
			'title' => \__( 'Fixed amount', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'type'  => 'price',
		];

		$fields['pronamic_fees_percentage_name'] = [
			'title'       => \__( 'Variable fee name', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'type'        => 'text',
			'default'     => \__( 'Payment gateway fee', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'placeholder' => \__( 'Payment gateway fee', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'description' => \__( 'Fee name to show to customer. To display each fee on different lines in cart (and checkout), you must set different names. If names are equal they will be merged into single line.', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'desc_tip'    => true,
		];

		$fields['pronamic_fees_percentage_value'] = [
			'title'             => \__( 'Percentage value', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'type'              => 'number',
			'custom_attributes' => [
				'step' => 'any',
			],
		];

		$fields['pronamic_fees_tax_class'] = [
			'title'   => \__( 'Tax class', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'type'    => 'select',
			'options' => \wc_get_product_tax_class_options(),
		];

		$fields['pronamic_fees_no_fee_order_total_above'] = [
			'title'       => \__( 'No fee for order total above', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'type'        => 'price',
			'description' => \__( 'Enter the amount at which the fee will no longer be applied. If left blank, there will be no threshold.', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'desc_tip'    => true,
		];

		$fields['pronamic_fees_max_amount'] = [
			'title'       => \__( 'Maximum amount', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'type'        => 'price',
			'description' => \__( 'Enter the maximum fee amount. If left blank, there will be no maximum.', 'pronamic-payment-gateways-fees-for-woocommerce' ),
			'desc_tip'    => true,
		];

		return $fields;
	}

	/**
	 * Get chosen payment method.
	 * 
	 * @return string
	 */
	private function get_chosen_payment_method() {
		if ( ! \WC()->session ) {
			return '';
		}

		$value = \WC()->session->get( 'chosen_payment_method' );

		if ( ! \is_string( $value ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * Get chosen gateway.
	 * 
	 * @return WC_Payment_Gateway|null
	 */
	private function get_chosen_gateway() {
		$gateway_id = $this->get_chosen_payment_method();

		$gateways = \WC()->payment_gateways()->get_available_payment_gateways();

		if ( \array_key_exists( $gateway_id, $gateways ) ) {
			return $gateways[ $gateway_id ];
		}

		return null;
	}

	/**
	 * Get fees from gateway.
	 * 
	 * @param WC_Payment_Gateway $gateway Gateway.
	 * @param float              $total   Order total amunt.
	 * @return array
	 */
	private function get_gateway_fees( $gateway, $total ) {
		$fees = [];

		$no_fee_order_total_above = (string) $gateway->get_option( 'pronamic_fees_no_fee_order_total_above' );

		if ( \is_numeric( $no_fee_order_total_above ) && $total > $no_fee_order_total_above ) {
			return $fees;
		}

		$fee_max_value = (string) $gateway->get_option( 'pronamic_fees_max_amount' );

		$fee_fixed_name  = (string) $gateway->get_option( 'pronamic_fees_fixed_name' );
		$fee_fixed_value = (string) $gateway->get_option( 'pronamic_fees_fixed_amount' );

		$fee_percentage_name  = (string) $gateway->get_option( 'pronamic_fees_percentage_name' );
		$fee_percentage_value = (string) $gateway->get_option( 'pronamic_fees_percentage_value' );

		$fee_fixed = Number::from_string( '0' );

		if ( \is_numeric( $fee_fixed_value ) ) {
			if ( \is_numeric( $fee_max_value ) ) {
				$fee_fixed_value = \min( $fee_max_value, $fee_fixed_value );

				$fee_max_value = $fee_max_value - $fee_fixed_value;
			}

			$fee_fixed = Number::from_string( $fee_fixed_value );
		}

		$fee_variable = Number::from_string( '0' );

		if ( \is_numeric( $fee_percentage_value ) ) {
			$value = $total / 100 * $fee_percentage_value;

			if ( \is_numeric( $fee_max_value ) ) {
				$value = \min( $fee_max_value, $value );
			}

			$fee_variable = Number::from_string( $value );
		}

		$fee_total = Number::from_string( '0' );
		$fee_total = $fee_total->add( $fee_fixed );
		$fee_total = $fee_total->add( $fee_variable );

		if ( $fee_fixed_name === $fee_percentage_name ) {
			if ( ! $fee_total->is_zero() ) {
				$fees[] = [
					'id'        => 'pronamic_payment_gateway_fee',
					'name'      => $fee_fixed_name,
					'amount'    => (string) $fee_total,
					'tax_class' => $gateway->get_option( 'pronamic_fees_tax_class' ),
					'taxable'   => true,
				];
			}
		}

		if ( $fee_fixed_name !== $fee_percentage_name ) {
			if ( ! $fee_fixed->is_zero() ) {
				$fees[] = [
					'id'        => 'pronamic_payment_gateway_fee_fixed',
					'name'      => $fee_fixed_name,
					'amount'    => (string) $fee_fixed,
					'tax_class' => $gateway->get_option( 'pronamic_fees_tax_class' ),
					'taxable'   => true,
				];
			}

			if ( ! $fee_variable->is_zero() ) {
				$fees[] = [
					'id'        => 'pronamic_payment_gateway_fee_variable',
					'name'      => $fee_percentage_name,
					'amount'    => (string) $fee_variable,
					'tax_class' => $gateway->get_option( 'pronamic_fees_tax_class' ),
					'taxable'   => true,
				];
			}
		}

		return $fees;
	}

	/**
	 * WooCommerce cart calculate fees.
	 * 
	 * @param WC_Cart $cart Cart.
	 * @return void
	 */
	public function woocommerce_cart_calculate_fees( $cart ) {
		if ( null === $this->total ) {
			return;         
		}

		$gateway = $this->get_chosen_gateway();

		if ( null === $gateway ) {
			return;
		}

		$fees = $this->get_gateway_fees( $gateway, $this->total );

		foreach ( $fees as $fee ) {
			$cart->fees_api()->add_fee( $fee );
		}
	}

	/**
	 * WooCommerce after calculate totals.
	 * 
	 * @param WC_Cart $cart Cart.
	 * @return void
	 */
	public function woocommerce_after_calculate_totals( $cart ) {
		\remove_action( 'woocommerce_after_calculate_totals', [ $this, 'woocommerce_after_calculate_totals' ] );

		$this->total = $cart->get_total( '' );

		$cart->calculate_totals();

		\add_action( 'woocommerce_after_calculate_totals', [ $this, 'woocommerce_after_calculate_totals' ] );
	}

	/**
	 * Create order fee items from cart fee items.
	 * 
	 * We mark the fees of this plugin via item meta so that we can remove
	 * them later.
	 * 
	 * @link https://github.com/woocommerce/woocommerce/blob/8.2.0/plugins/woocommerce/includes/class-wc-checkout.php#L556-L590
	 * @param WC_Order_Item_Fee $item    Order item fee.
	 * @param string            $fee_key Fee key.
	 */
	public function woocommerce_checkout_create_order_fee_item( $item, $fee_key ) {
		if ( ! \str_starts_with( $fee_key, 'pronamic_payment_gateway_fee' ) ) {
			return;
		}

		$item->update_meta_data( '_pronamic_payment_gateway_fee_key', $fee_key );
	}

	/**
	 * Before WooCommerce pay form.
	 * 
	 * @link https://github.com/woocommerce/woocommerce/blob/8.2.0/plugins/woocommerce/includes/shortcodes/class-wc-shortcode-checkout.php#L210-L219
	 * @param WC_Order $order             The order that is being paid for.
	 * @param string   $order_button_text The text for the submit button.
	 * @param array    $available_gateways All available gateways.
	 * @return void
	 */
	public function before_woocommerce_pay_form( $order, $order_button_text, $available_gateways ) {
		\wp_enqueue_script( 'pronamic-payment-gateways-fees-for-woocommerce-order-pay' );

		$gateway_key = \array_key_first( $available_gateways );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is not necessary because this action/parameter is public.
		if ( \array_key_exists( 'pronamic_payment_gateway_fee', $_GET ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is not necessary because this action/parameter is public.
			$gateway_key = \sanitize_text_field( \wp_unslash( $_GET['pronamic_payment_gateway_fee'] ) );
		}

		if ( ! \array_key_exists( $gateway_key, $available_gateways ) ) {
			return;
		}

		$gateway = $available_gateways[ $gateway_key ];

		$order->set_payment_method( $gateway );

		\WC()->session->set( 'chosen_payment_method', $gateway_key );

		\WC()->payment_gateways()->set_current_gateway( $available_gateways );

		$this->update_order_fees( $order, $gateway );
	}

	/**
	 * WooCommerce pay order after submit.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/8.2.0/plugins/woocommerce/templates/checkout/form-pay.php#L104
	 * @return void
	 */
	public function woocommerce_pay_order_after_submit() {
		\wp_nonce_field( 'pronamic_payment_gateways_fees_for_woocommerce_pay', 'pronamic_payment_gateways_fees_for_woocommerce_nonce' );
	}

	/**
	 * We update the payment gateways fees just before the pay action.
	 * 
	 * @link https://github.com/woocommerce/woocommerce/blob/8.2.0/plugins/woocommerce/includes/class-wc-form-handler.php#L383-L475
	 * @param WC_Order $order Order.
	 * @return void
	 */
	public function woocommerce_before_pay_action( $order ) {
		if ( ! $order->needs_payment() ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_payment_gateways_fees_for_woocommerce_nonce', $_POST ) ) {
			return;
		}

		$nonce = \sanitize_text_field( \wp_unslash( $_POST['pronamic_payment_gateways_fees_for_woocommerce_nonce'] ) );

		if ( ! \wp_verify_nonce( $nonce, 'pronamic_payment_gateways_fees_for_woocommerce_pay' ) ) {
			return;
		}

		if ( ! \array_key_exists( 'payment_method', $_POST ) ) {
			return;
		}

		$payment_method_id = \sanitize_text_field( \wp_unslash( $_POST['payment_method'] ) );

		$available_gateways = \WC()->payment_gateways->get_available_payment_gateways();

		if ( ! \array_key_exists( $payment_method_id, $available_gateways ) ) {
			return;
		}

		$gateway = $available_gateways[ $payment_method_id ];

		$this->update_order_fees( $order, $gateway );
	}

	/**
	 * Update order fees.
	 * 
	 * @param WC_Order           $order   Order.
	 * @param WC_Payment_Gateway $gateway Gateway.
	 * @return void
	 */
	private function update_order_fees( $order, $gateway ) {
		$fees_old = \array_filter(
			$order->get_fees(),
			function ( $item ) {
				$fee_key = (string) $item->get_meta( '_pronamic_payment_gateway_fee_key' );

				return '' !== $fee_key;
			}
		);

		foreach ( $fees_old as $item ) {
			$order->remove_item( $item->get_id() );
		}

		$order->calculate_totals();

		$fees_new = $this->get_gateway_fees( $gateway, $order->get_total() );

		foreach ( $fees_new as $fee ) {
			$item_fee = new WC_Order_Item_Fee();

			$item_fee->set_name( $fee['name'] );
			$item_fee->set_amount( $fee['amount'] );
			$item_fee->set_total( $fee['amount'] );
			$item_fee->set_tax_class( $fee['tax_class'] );
			$item_fee->set_tax_status( 'taxable' );

			$item_fee->update_meta_data( '_pronamic_payment_gateway_fee_key', $fee['id'] );

			$order->add_item( $item_fee );
		}

		$order->calculate_totals();
	}
}
