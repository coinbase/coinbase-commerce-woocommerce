<?php

class WC_Coinbase_Test extends WP_UnitTestCase {
	/**
	 * Test that Coinbase payment gateway has loaded.
	 */
	public function test_gateway_loaded() {
		$this->assertArrayHasKey( 'coinbase', WC()->payment_gateways()->payment_gateways() );
	}

	/**
	 * Create $product_count simple products and store them in $this->products.
	 *
	 * @param int $product_count Number of products to create.
	 */
	protected function create_products( $product_count = 30 ) {
		$this->products = array();
		for ( $i = 0; $i < $product_count; $i++ ) {
			$product = WC_Helper_Product::create_simple_product();
			$product->set_name( 'Dummy Product ' . $i );
			$this->products[] = $product;

		}

		// To test limit length properly, we need a product with a name that is shorter than 127 chars,
		// but longer than 127 chars when URL-encoded.
		if ( array_key_exists( 0, $this->products ) ) {
			$this->products[0]->set_name( 'Dummy Product 😎😎😎😎😎😎😎😎😎😎😎' );
		}

	}

	/**
	 * Add products from $this->products to $order as items, clearing existing order items.
	 *
	 * @param WC_Order $order Order to which the products should be added.
	 * @param array    $prices Array of prices to use for created products. Leave empty for default prices.
	 */
	protected function add_products_to_order( $order, $prices = array() ) {
		// Remove previous items.
		foreach ( $order->get_items() as $item ) {
			$order->remove_item( $item->get_id() );
		}

		// Add new products.
		$prod_count = 0;
		foreach ( $this->products as $product ) {
			$item = new WC_Order_Item_Product();
			$item->set_props( array(
				'product'  => $product,
				'quantity' => 3,
				'subtotal' => $prices ? $prices[ $prod_count ] : wc_get_price_excluding_tax( $product, array( 'qty' => 3 ) ),
				'total'    => $prices ? $prices[ $prod_count ] : wc_get_price_excluding_tax( $product, array( 'qty' => 3 ) ),
			) );

			$item->save();
			$order->add_item( $item );

			$prod_count++;
		}

	}

	/**
	 * Test multiple gateway icons.
	 */
	public function test_multiple_icons() {
		$payment_gateway = WC()->payment_gateways->payment_gateways()['coinbase'];

		// Test 2 icons are loaded for bitcoin and litecoin.
		update_option( 'coinbase_payment_methods', array( 'bitcoin', 'litecoin' ) );
		$icons   = $payment_gateway->get_icon();
		$matches = array();
		$num     = preg_match_all( '/<img .*? src="(.*?)".*?\/>/', $icons, $matches );

		$this->assertSame( 2, $num );
		$this->assertStringEndsWith( 'bitcoin.png', $matches[1][0] );
		$this->assertStringEndsWith( 'litecoin.png', $matches[1][1] );

		// Cleanup
		delete_option( 'coinbase_payment_methods' );
	}

	/**
	 * Test unrecognised methods are ignored.
	 */
	public function test_ignore_icons() {
		$payment_gateway = WC()->payment_gateways->payment_gateways()['coinbase'];

		// Test 1 icon is loaded for bitcoin and notacoin is ignored.
		update_option( 'coinbase_payment_methods', array( 'bitcoin', 'notacoin' ) );
		$icons   = $payment_gateway->get_icon();
		$matches = array();
		$num     = preg_match_all( '/<img .*? src="(.*?)".*?\/>/', $icons, $matches );

		$this->assertSame( 1, $num );
		$this->assertStringEndsWith( 'bitcoin.png', $matches[1][0] );

		// Cleanup
		delete_option( 'coinbase_payment_methods' );
	}

	/**
	 * Test an order checkout
	 */
	public function test_order_checkout() {
		// User set up.
		$this->user = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $this->user );

		// Create products.
		$this->create_products( 5 );

		$this->order = WC_Helper_Order::create_order( $this->user );
		$this->add_products_to_order( $this->order, array() );

		// Set payment method to Coinbase.
		$payment_gateway = WC()->payment_gateways->payment_gateways()['coinbase'];
		$this->order->set_payment_method( $payment_gateway );

		$this->order->calculate_shipping();
		$this->order->calculate_totals();

		add_filter( 'pre_http_request', array( $this, 'pre_http_request_charge_success' ) );
		$result = $payment_gateway->process_payment( $this->order->get_id() );
		remove_filter( 'pre_http_request', array( $this, 'pre_http_request_charge_success' ) );

		$this->assertSame( 'success', $result['result'] );

		// Remove order and created products.
		WC_Helper_Order::delete_order( $this->order->get_id() );
	}

	/**
	 * Return successful result for charge success.
	 */
	public function pre_http_request_charge_success() {
		return array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode( array(
				'data' => array(
					'code'       => 'ABC123',
					'hosted_url' => 'https://foo.example/test',
				),
			) ),
		);
	}
}
