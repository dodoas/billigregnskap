<?php

add_action( 'after_setup_theme', '_appthemes_load_payments', 999 );

function _appthemes_load_payments() {
	if ( !current_theme_supports( 'app-payments' ) )
		return;

	require APP_FRAMEWORK_DIR . '/load-p2p.php';
	require_once APP_FRAMEWORK_DIR . '/includes/tables.php';
	require_once APP_FRAMEWORK_DIR . '/admin/class-meta-box.php';
	require_once APP_FRAMEWORK_DIR . '/admin/class-tabs-page.php';

	// Utilities
	require dirname( __FILE__ ) . '/currencies.php';
	require dirname( __FILE__ ) . '/utils.php';

	// Orders
	require dirname( __FILE__ ) . '/order-class.php';
	require dirname( __FILE__ ) . '/order-draft-class.php';
	require dirname( __FILE__ ) . '/order-receipt-class.php';
	require dirname( __FILE__ ) . '/order-functions.php';
	require dirname( __FILE__ ) . '/order-upgrade.php';

	require dirname( __FILE__ ) . '/order-tags.php';
	require dirname( __FILE__ ) . '/order-view.php';
	require dirname( __FILE__ ) . '/item-registry.php';

	if( is_admin() ){

		require dirname( __FILE__ ) . '/admin/admin.php';
		require dirname( __FILE__ ) . '/admin/orders.php';
		require dirname( __FILE__ ) . '/admin/settings.php';
		require dirname( __FILE__ ) . '/gateways/bank-transfer/bt-admin.php';

		new APP_Bank_Transfer_Queue;
	}

	/// Gateways
	require dirname( __FILE__ ) . '/gateways/gateway-class.php';
	require dirname( __FILE__ ) . '/gateways/boomerang-class.php';
	require dirname( __FILE__ ) . '/gateways/gateway-registry.php';
	require dirname( __FILE__ ) . '/gateways/gateway-functions.php';

	require dirname( __FILE__ ) . '/gateways/paypal/paypal.php';
	require dirname( __FILE__ ) . '/gateways/paypal/paypal-pdt.php';

	require dirname( __FILE__ ) . '/gateways/bank-transfer/bt-gateway.php';

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		require dirname( __FILE__ ) . '/gateways/test.php';
	}

	new APP_Order_Summary;

	extract( appthemes_payments_get_args(), EXTR_SKIP );

	if( !empty( $items ) ){
		foreach( $items as $item ){

			if( !isset( $item['type'] ) || !isset( $item['title'] ) )
				continue;

			if( !isset( $item['meta'] ) )
				$item['meta'] = array();

			APP_Item_Registry::register( $item['type'], $item['title'], $item['meta'] );

		}
	}

	if( $options ){
		APP_Gateway_Registry::register_options( $options );
	}
	else{

		$defaults = array(
			'currency_code' => 'USD',
			'gateways' => array(
				'enabled' => array()
			)
		);

		$options = new scbOptions( 'payments', false, $defaults );
		APP_Gateway_Registry::register_options( $options );
	}
}

function appthemes_payments_get_args(){

	if( !current_theme_supports( 'app-payments' ) )
		return array();

	list($args) = get_theme_support( 'app-payments' );
	$defaults = array(
		'items' => array(),
		'items_post_types' => array( 'post' ),
		'options' => false
	);

	return wp_parse_args( $args, $defaults );

}

