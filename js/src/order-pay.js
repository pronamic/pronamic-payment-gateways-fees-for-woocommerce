const url = new URL( window.location );

jQuery( ( $ ) => {
	$( document ).ready( function() {
		$( '#order_review .shop_table' ).wrap( '<div id="pronamic-order"></div>' );
	} );

	$( 'body' ).on( 'change', 'input[name="payment_method"]', function () {
		url.searchParams.set( 'pronamic_gateway_fee', this.value );

		$pronamic_order = $( "#pronamic-order" );

		$pronamic_order.css( 'filter', 'blur(5px)' );

		$pronamic_order.load(
			url + " #order_review .shop_table",
			function() {
				$pronamic_order.css( 'filter', 'none' );

				window.history.replaceState( {}, '', url );
			}
		);
	} );
} );
