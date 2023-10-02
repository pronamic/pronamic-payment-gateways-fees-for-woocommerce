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

		\add_filter( 'woocommerce_generate_pronamic_subtitle_html', [ $this, 'generate_pronamic_subtitle_html'], 10, 3 );

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
			'title' => \__( 'Fees suggest', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'  => 'title',
		];

		$fields['pronamic_fee_fixed_subtitle'] = [
			'title' => \__( 'Fixed', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'  => 'pronamic_subtitle',
		];

		$fields['pronamic_fee_title'] = [
			'title'       => \__( 'Title', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'        => 'text',
			'default'     => \__( 'Gateway Fee', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'placeholder' => \__( 'Gateway Fee', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
		];

		$fields['pronamic_fee_fixed'] = [
			'title' => \__( 'Fixed', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'  => 'price',
		];

		$fields['pronamic_fee_percentage'] = [
			'title' => \__( 'Percentage', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'  => 'number',
		];

		$fields['pronamic_fee_tax_class'] = [
			'title'   => \__( 'Tax class', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'    => 'select',
			'options' => \wc_get_product_tax_class_options(),
		];

		$fields['pronamic_fees_minimum_cart_amount'] = [
			'title'       => \__( 'Minimum cart amount', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'        => 'price',
			'description' => \__( 'Minimum cart amount for adding the fee (or discount). Ignored if set to zero.', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'desc_tip'    => true,
		];

		$fields['pronamic_fees_maximum_cart_amount'] = [
			'title'       => \__( 'Maximum cart amount', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'        => 'price',
			'description' => \__( 'Maximum cart amount for adding the fee (or discount). Ignored if set to zero.', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'desc_tip'    => true,
		];

		$fields['pronamic_fees_minimum_fee_amount'] = [
			'title'       => \__( 'Minimum fee amount', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'        => 'price',
			'description' => \__( 'Minimum fee (or discount). Ignored if set to zero.', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'desc_tip'    => true,
		];

		$fields['pronamic_fees_maximum_fee_amount'] = [
			'title'       => \__( 'Maximum fee amount', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'type'        => 'price',
			'description' => \__( 'Maximum fee (or discount). Ignored if set to zero.', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			'desc_tip'    => true,
		];

		return $fields;
	}

	/**
	 * Generate subtitle HTML.
	 * 
	 * @link https://github.com/woocommerce/woocommerce/blob/8ce50fb4198599c6b125035cdfd0787df5aaddc1/plugins/woocommerce/includes/abstracts/abstract-wc-settings-api.php#L842-L870
	 */
	public function generate_pronamic_subtitle_html( $html, $key, $data ) {
		$data = \wp_parse_args(
			$data,
			[
				'title' => '',
			]
		);

		$html .= '</table>';
		$html .= '<h4>' . $data['title'] . '</h4>';
		$html .= '<table class="form-table">';

		return $html;
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
		$fixed      = (string) $gateway->get_option( 'pronamic_fee_fixed' );
		$percentage = (string) $gateway->get_option( 'pronamic_fee_percentage' );

		if ( '' === $fixed && '' === $percentage ) {
			return '';
		}

		if ( '' !== $fixed && '' === $percentage ) {
			return \sprintf(
				\__( '+ %s fee might apply', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
				\wc_price( $fixed )
			);
		}

		if ( '' === $fixed && '' !== $percentage ) {
			return \sprintf(
				\__( '+ %s%% fee might apply', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
				\number_format_i18n( $percentage, 2 )
			);
		}

		return \sprintf(
			\__( '+ %1$s + %2$s%% fee might apply', 'pronamic-woocommerce-payment-gateways-fees-suggest' ),
			\wc_price( $fixed ),
			\number_format_i18n( $percentage, 2 )
		);
	}

	private function get_fee_amount( $cart, $gateway ) {
		$fee = '0';

		$fixed = (string) $gateway->get_option( 'pronamic_fee_fixed' );

		if ( \is_numeric( $fixed ) ) {
			$fee += $fixed;
		}

		$percentage = (string) $gateway->get_option( 'pronamic_fee_percentage' );

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

		$cart->fees_api()->add_fee(
			[
				'id'        => 'pronamic_gateway_fee',
				'name'      => $gateway->get_option( 'pronamic_fee_title' ),
				'amount'    => $this->get_fee_amount( $cart, $gateway ),
				'tax_class' => $gateway->get_option( 'pronamic_fee_tax_class' ),
				'taxable'   => true,
			]
		);		
	}

	public function woocommerce_after_calculate_totals( $cart ) {
		\remove_action( 'woocommerce_after_calculate_totals', [ $this, 'woocommerce_after_calculate_totals' ] );

		$this->total = $cart->get_total( '' );

		$cart->calculate_totals();

		\add_action( 'woocommerce_after_calculate_totals', [ $this, 'woocommerce_after_calculate_totals' ] );
	}
}
