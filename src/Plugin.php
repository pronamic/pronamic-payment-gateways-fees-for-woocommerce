<?php
/**
 * Pronamic WooCommerce Payment Gateways Fees Plugin
 *
 * @package   PronamicWooCommercePaymentGatewaysFees
 * @author    Pronamic
 * @copyright 2023 Pronamic
 */

namespace Pronamic\WooCommercePaymentGatewaysFees;

/**
 * Pronamic WooCommerce Payment Gateways Fees Plugin class
 */
class Plugin {
	/**
	 * Instance of this class.
	 *
	 * @since 4.7.1
	 * @var self
	 */
	protected static $instance = null;

	private $total;

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

		\add_action( 'woocommerce_cart_calculate_fees', [ $this, 'woocommerce_cart_calculate_fees' ] );

		\add_action( 'woocommerce_after_calculate_totals', [ $this, 'woocommerce_after_calculate_totals' ] );
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

			$description = $this->get_description( $payment_gateway );

			if ( '' !== $description ) {
				$payment_gateway->description .= '<p>' . $description . '</p>';
			}
		}
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
			'title'       => \__( 'Fees', 'pronamic-woocommerce-payment-gateways-fees' ),
			'type'        => 'title',
			'description' => \__( 'Please note that you are not always allowed to charge surcharges for payment methods.', 'pronamic-woocommerce-payment-gateways-fees' ),
		];

		$fields['pronamic_fees_fixed_title'] = [
			'title'       => \__( 'Fixed fee title', 'pronamic-woocommerce-payment-gateways-fees' ),
			'type'        => 'text',
			'default'     => \__( 'Gateway fee', 'pronamic-woocommerce-payment-gateways-fees' ),
			'placeholder' => \__( 'Gateway fee', 'pronamic-woocommerce-payment-gateways-fees' ),
		];

		$fields['pronamic_fees_fixed_amount'] = [
			'title' => \__( 'Fixed amount', 'pronamic-woocommerce-payment-gateways-fees' ),
			'type'  => 'price',
		];

		$fields['pronamic_fees_percentage_title'] = [
			'title'       => \__( 'Percentage fee title', 'pronamic-woocommerce-payment-gateways-fees' ),
			'type'        => 'text',
			'default'     => \__( 'Gateway fee', 'pronamic-woocommerce-payment-gateways-fees' ),
			'placeholder' => \__( 'Gateway fee', 'pronamic-woocommerce-payment-gateways-fees' ),
		];

		$fields['pronamic_fees_percentage_value'] = [
			'title'             => \__( 'Percentage value', 'pronamic-woocommerce-payment-gateways-fees' ),
			'type'              => 'number',
			'custom_attributes' => [
				'step' => 'any',
			],
		];

		$fields['pronamic_fees_tax_class'] = [
			'title'   => \__( 'Tax class', 'pronamic-woocommerce-payment-gateways-fees' ),
			'type'    => 'select',
			'options' => \wc_get_product_tax_class_options(),
		];

		$fields['pronamic_fees_no_fee_order_total_above'] = [
			'title'       => \__( 'No fee for order total above', 'pronamic-woocommerce-payment-gateways-fees' ),
			'type'        => 'price',
			'description' => \__( 'Enter the amount at which the fee will no longer be applied. If left blank, there will be no threshold.', 'pronamic-woocommerce-payment-gateways-fees' ),
			'desc_tip'    => true,
		];

		return $fields;
	}

	public function get_chosen_payment_method() {
		if ( ! WC()->session ) {
			return '';
		}

		$value = WC()->session->get( 'chosen_payment_method' );

		if ( ! is_string( $value ) ) {
			return '';
		}

		return $value;
	}

	public function get_chosen_gateway() {
		$gateway_id = $this->get_chosen_payment_method();

		$gateways = \WC()->payment_gateways()->get_available_payment_gateways();

		if ( array_key_exists( $gateway_id, $gateways ) ) {
			return $gateways[ $gateway_id ];
		}

		return null;
	}

	private function get_description( $gateway ) {
		$amount     = (string) $gateway->get_option( 'pronamic_fees_fixed_amount' );
		$percentage = (string) $gateway->get_option( 'pronamic_fees_percentage_value' );

		if ( '' === $amount && '' === $percentage ) {
			return '';
		}

		if ( '' !== $amount && '' === $percentage ) {
			return \sprintf(
				\__( '+ %s fee might apply', 'pronamic-woocommerce-payment-gateways-fees' ),
				\wc_price( $amount )
			);
		}

		if ( '' === $amount && '' !== $percentage ) {
			return \sprintf(
				\__( '+ %s%% fee might apply', 'pronamic-woocommerce-payment-gateways-fees' ),
				\number_format_i18n( $percentage, 2 )
			);
		}

		return \sprintf(
			\__( '+ %1$s + %2$s%% fee might apply', 'pronamic-woocommerce-payment-gateways-fees' ),
			\wc_price( $amount ),
			\number_format_i18n( $percentage, 2 )
		);
	}

	private function get_fee_amount( $cart, $gateway ) {
		$fee = '0';

		$amount = (string) $gateway->get_option( 'pronamic_fees_fixed_amount' );

		if ( \is_numeric( $amount ) ) {
			$fee += $amount;
		}

		$percentage = (string) $gateway->get_option( 'pronamic_fees_percentage_value' );

		if ( \is_numeric( $percentage ) ) {
			$total = $this->total;

			$fee += $total / 100 * $percentage;
		}

		return $fee;
	}

	public function woocommerce_cart_calculate_fees( $cart ) {
		if ( null === $this->total ) {
			return;			
		}

		$gateway = $this->get_chosen_gateway();

		if ( null === $gateway ) {
			return;
		}

		$no_fee_order_total_above = (string) $gateway->get_option( 'pronamic_fees_no_fee_order_total_above' );

		if ( is_numeric( $no_fee_order_total_above ) && $this->total > $no_fee_order_total_above ) {
			return;
		}

		$fee_total = '0';

		$fee_fixed_title  = (string) $gateway->get_option( 'pronamic_fees_fixed_title' );
		$fee_fixed_amount = (string) $gateway->get_option( 'pronamic_fees_fixed_amount' );

		$fee_percentage_title = (string) $gateway->get_option( 'pronamic_fees_percentage_title' );
		$fee_percentage_value = (string) $gateway->get_option( 'pronamic_fees_percentage_value' );

		$fee_percentage_amount = '';

		if ( \is_numeric( $fee_percentage_value ) ) {
			$fee_percentage_amount = $this->total / 100 * $fee_percentage_value;
		}

		if ( \is_numeric( $fee_fixed_amount ) ) {
			$fee_total += $fee_fixed_amount;
		}

		if ( \is_numeric( $fee_percentage_amount ) ) {
			$fee_total += $fee_percentage_amount;
		}

		if ( $fee_fixed_title === $fee_percentage_title ) {
			if ( \is_numeric( $fee_total ) ) {
				$cart->fees_api()->add_fee(
					[
						'id'        => 'pronamic_gateway_fee',
						'name'      => $fee_fixed_title,
						'amount'    => $fee_total,
						'tax_class' => $gateway->get_option( 'pronamic_fees_tax_class' ),
						'taxable'   => true,
					]
				);
			}
		}

		if ( $fee_fixed_title !== $fee_percentage_title ) {
			if ( \is_numeric( $fee_fixed_amount ) ) {
				$cart->fees_api()->add_fee(
					[
						'id'        => 'pronamic_gateway_fee_fixed',
						'name'      => $fee_fixed_title,
						'amount'    => $fee_fixed_amount,
						'tax_class' => $gateway->get_option( 'pronamic_fees_tax_class' ),
						'taxable'   => true,
					]
				);
			}

			if ( \is_numeric( $fee_percentage_amount ) ) {
				$cart->fees_api()->add_fee(
					[
						'id'        => 'pronamic_gateway_fee_percentage',
						'name'      => $fee_percentage_title,
						'amount'    => $fee_percentage_amount,
						'tax_class' => $gateway->get_option( 'pronamic_fees_tax_class' ),
						'taxable'   => true,
					]
				);
			}
		}
	}

	public function woocommerce_after_calculate_totals( $cart ) {
		\remove_action( 'woocommerce_after_calculate_totals', [ $this, 'woocommerce_after_calculate_totals' ] );

		$this->total = $cart->get_total( '' );

		$cart->calculate_totals();

		\add_action( 'woocommerce_after_calculate_totals', [ $this, 'woocommerce_after_calculate_totals' ] );
	}
}
