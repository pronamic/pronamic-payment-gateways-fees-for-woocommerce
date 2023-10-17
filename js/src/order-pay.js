( function() {
	const selector = '#order_review .shop_table';
	const url      = new URL( window.location );

	document.addEventListener(
		'DOMContentLoaded',
		function() {
			const shop_table = document.querySelector( selector );

			if ( ! shop_table ) {
				return;
			}

			const container = document.createElement( 'div' );

			shop_table.before( container );

			container.append( shop_table );

			$container = jQuery( container );

			jQuery( 'body' ).on( 'change', 'input[name="payment_method"]', function () {
				url.searchParams.set( 'pronamic_payment_gateway_fee', this.value );

				/**
				 * Block.
				 * 
				 * @see https://github.com/woocommerce/woocommerce/blob/8.2.0/plugins/woocommerce/client/legacy/js/frontend/cart.js#L34-L49
				 * @see https://github.com/woocommerce/woocommerce/blob/8.2.0/plugins/woocommerce/client/legacy/js/jquery-blockui/jquery.blockUI.js#L74-L96
				 */
				$container.block( {
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				} );

				$container.load(
					url + ' ' + selector,
					function() {
						/**
						 * Unblock.
						 * 
						 * @see https://github.com/woocommerce/woocommerce/blob/8.2.0/plugins/woocommerce/client/legacy/js/jquery-blockui/jquery.blockUI.js#L98-L107
						 * @see https://github.com/woocommerce/woocommerce/blob/8.2.0/plugins/woocommerce/client/legacy/js/frontend/cart.js#L51-L58
						 */
						$container.unblock();

						window.history.replaceState( {}, '', url );
					}
				);
			} );
		}
	);
} )();
