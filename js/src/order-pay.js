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

				$container.css( 'filter', 'blur(5px)' );

				$container.load(
					url + ' ' + selector,
					function() {
						$container.css( 'filter', 'none' );

						window.history.replaceState( {}, '', url );
					}
				);
			} );
		}
	);
} )();
