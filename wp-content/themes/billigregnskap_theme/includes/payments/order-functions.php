<?php

define( 'APPTHEMES_ORDER_PTYPE', 'transaction' );
define( 'APPTHEMES_ORDER_CONNECTION', 'order-connection' );

add_action( 'init', 'appthemes_setup_orders', 10 );
add_action( 'appthemes_first_run', 'appthemes_upgrade_order_statuses' );

/**
 * Order Statuses
 */
define( 'APPTHEMES_ORDER_PENDING', 'tr_pending' );
define( 'APPTHEMES_ORDER_FAILED', 'tr_failed' );
define( 'APPTHEMES_ORDER_COMPLETED', 'tr_completed' );
define( 'APPTHEMES_ORDER_ACTIVATED', 'tr_activated' );

/**
 * Sets up the order system
 * @return void
 */
function appthemes_setup_orders() {

	$args = array(
		'labels' => array(
			'name' => __( 'Orders', APP_TD ),
			'singular_name' => __( 'Order', APP_TD ),
			'add_new' => __( 'Add New', APP_TD ),
			'add_new_item' => __( 'Add New Order', APP_TD ),
			'edit_item' => __( 'Edit Order', APP_TD ),
			'new_item' => __( 'New Order', APP_TD ),
			'view_item' => __( 'View Order', APP_TD ),
			'search_items' => __( 'Search Orders', APP_TD ),
			'not_found' => __( 'No orders found', APP_TD ),
			'not_found_in_trash' => __( 'No orders found in Trash', APP_TD ),
			'parent_item_colon' => __( 'Parent Order:', APP_TD ),
			'menu_name' => __( 'Orders', APP_TD ),
		),
		'hierarchical' => false,
		'supports' => array( 'author', 'custom-fields' ),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => 'app-payments',
		'rewrite' => array('slug' => 'order')
	);
	register_post_type( APPTHEMES_ORDER_PTYPE, apply_filters( 'appthemes_order_ptype_args', $args ) );

	$statuses = array(
		APPTHEMES_ORDER_PENDING => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', APP_TD ),
		APPTHEMES_ORDER_FAILED => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', APP_TD ),
		APPTHEMES_ORDER_COMPLETED => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', APP_TD ),
		APPTHEMES_ORDER_ACTIVATED => _n_noop( 'Activated <span class="count">(%s)</span>', 'Activated <span class="count">(%s)</span>', APP_TD ),
	);

	foreach( $statuses as $status => $translate_string ){
		register_post_status( $status, array(
			'public' => true,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => $translate_string
		));
	}

	$args = appthemes_payments_get_args();
	$initial_ptypes = $args['items_post_types'];

	if( !is_array( $initial_ptypes ) ){
		$initial_ptypes = array( $initial_ptypes );
	}

	$post_types = apply_filters( 'appthemes_order_item_posts_types', $initial_ptypes );
	p2p_register_connection_type( array(
		'name' => APPTHEMES_ORDER_CONNECTION,
		'from' => APPTHEMES_ORDER_PTYPE,
		'to' => $post_types,
		'cardinality' => 'many-to-many',
		'admin_box' => false,
		'prevent_duplicates' => false,
		'self_connections' => true,
	) );

}

/**
 * Creates a blank order object, or upgrades a draft order
 * @param  APP_Draft_order $draft_order A draft order to be upgraded
 * @return APP_Order                    An order object representing the new order
 */
function appthemes_new_order( $draft_order = false ) {

	if( $draft_order ){
		return APP_Draft_Order::upgrade( $draft_order );
	}else{
		return APP_Order::create();
	}
}

/**
 * Returns an instance of APP_Order for the given Order ID
 * @param  int $order_id An order ID
 * @return APP_Order     An order object representing the order
 */
function appthemes_get_order( $order_id ) {
	return APP_Order::retrieve( $order_id );
}

/**
 * Returns items connected via APPTHEMES_ORDER_CONNECTION
 * @param  int $id   ID of post connected
 * @return array     See WP_Query
 */
function _appthemes_orders_get_connected( $id ){
	$args = array(
		'connected_type' => APPTHEMES_ORDER_CONNECTION,
		'post_status' => 'any',
		'nopaging' => true
	);

	$type = get_post_type( $id );
	if( $type == APPTHEMES_ORDER_PTYPE ){
		$args['connected_from'] = $id;
	}else{
		$args['connected_to'] = $id;
	}

	return new WP_Query( $args );
}