<?php

/**
 * Represents a purchase order made up of items.
 */
class APP_Order {

	/**
	 * Stores instances of APP_Order by ID
	 * @var array
	 */
	static private $instances = array();

	/**
	 * Prepares and returns a new Order
	 * @return APP_Order New Order object
	 */
	static public function create( $description = '' ) {

		if( empty( $description ) )
			$description = __( 'Transaction', APP_TD );

		$order_id = wp_insert_post( array(
			'post_title' => $description,
			'post_content' => __( 'Transaction Data', APP_TD ),
			'post_type' => APPTHEMES_ORDER_PTYPE,
			'post_status' => APPTHEMES_ORDER_PENDING, 
		) );

		$currency = APP_Gateway_Registry::get_options()->currency_code;

		add_post_meta( $order_id, 'ip_address', $_SERVER['REMOTE_ADDR'], true );
		add_post_meta( $order_id, 'currency', $currency, true );

		wp_update_post(array(
			'ID' => $order_id,
			'post_name' => $order_id
		));

		return self::retrieve( $order_id );
	}

	/**
	 * Retrieves an existing order by ID
	 * @param  int 	$order_id Order ID
	 * @return APP_Order Object representing the order
	 */
	static public function retrieve( $order_id ) {

		if ( isset( self::$instances[ $order_id ] ) )
			return self::$instances[ $order_id ];

		$order_data = get_post( $order_id );
		if ( !$order_data || $order_data->post_type != APPTHEMES_ORDER_PTYPE )
			return false;

		$items = array();
		foreach ( _appthemes_orders_get_connected( $order_id )->posts as $post ) {
			$meta = p2p_get_meta( $post->p2p_id );
			$items[] = array(
				'type' => $meta['type'][0],
				'price' => $meta['price'][0],
				'post_id' => $post->ID,
				'post' => $post
			);
		}

		$order = new APP_Order(
			$order_data,
			get_post_meta( $order_id, "ip_address", true ),
			$items
		);

		self::$instances[ $order_id ] = $order;
		return $order;
	}

	/**
	 * Order ID, defined by Wordpress when
	 * creating Order
	 * @var int
	 */
	protected $id;

	/**
	 * Order description
	 * @var  string
	 */
	protected $description;

	/**
	 * User ID who created this order
	 * @var int
	 */
	protected $author;

	/**
	 * IP Address used to create the order
	 * @var string
	 */
	protected $ip_addres;

	/**
	 * Gateway selected to process this order
	 * @var string
	 */
	protected $gateway = '';

	/**
	 * State of the order. 
	 * See order-functions.php for possible states
	 * @var string
	 */
	protected $state = APPTHEMES_ORDER_COMPLETED;

	/**
	 * Currency the order is using
	 * @var string
	 */
	protected $currency = 'USD';

	/**
	 * Total cost of the items added to the order.
	 * Automatically generated with refresh_total()
	 * @var integer
	 */
	protected $total = 0;

	/**
	 * List of items in the currency order
	 * @var array
	 */
	protected $items = array();

	/**
	 * Sets up the order objects
	 * @param object $post       Post object returned from get_post() 
	 * 								See (http://codex.wordpress.org/Function_Reference/get_post)
	 * @param string $gateway    Gateway indentifier for this order to use
	 * @param string $ip_address IP_Address used to create the order
	 * @param string $currency   Currency code for currency currently being used. Should 
	 * 								be registered with APP_Currencies
	 * @param array $items       Array of items currently attached to the order
	 */
	public function __construct( $post, $ip_address, $items ) {

		$this->post = $post;
		$this->id = $this->post->ID;
		$this->description = $this->post->post_title;
		$this->author = $this->post->post_author;
		$this->state = $post->post_status;

		$gateway = get_post_meta( $post->ID, 'gateway', true);
		if ( $gateway )
			$this->gateway = $gateway;

		$currency = get_post_meta( $post->ID, 'currency', true);
		if ( $currency )
			$this->currency = $currency;

				$ip_address = get_post_meta( $post->ID, 'ip_address', true);
		if ( $ip_address )
			$this->ip_address = $ip_address;

		$this->items = $items;

		$this->refresh_total();

	}

	/**
	 * Returns the Order ID
	 * @return int Order ID
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Returns the Order description
	 * @return string The order description
	 */
	public function get_description() {
		return $this->description;
	}

	public function set_description( $description ){
		wp_update_post( array(
			'ID' => $this->get_id(),
			'post_title' => $description
		) );
		$this->description = $description;
	}

	/**
	 * Returns the User ID of the creator of the order
	 * @return int User ID
	 */
	public function get_author() {
		return $this->author;
	}

	/**
	 * Returns the IP Address used to create the order
	 * @return string IP Address
	 */
	public function get_ip_address() {
		return $this->ip_address;
	}

	/**
	 * Returns the URL to redirect to for processing the order
	 * @return string URL
	 */
	public function get_return_url() {
		return get_permalink( $this->id );
	}

	/**
	 * Returns the URL to redirect to for selecting a new gateway
	 * @return string URL
	 */
	public function get_cancel_url() {
		return add_query_arg( "cancel", 1, $this->get_return_url() );
	}

	/**
	 * Returns the first item in an order, or another as specified
	 * @param  integer $index The index number of the item to return
	 * @return array          An associative array of information about the item
	 *							'type' => Item Type (see add_item())
	 *							'price' => The cost of the given item
	 *							'post_id' => The post that this item affects
	 *							'post' => An object contain information about the post
	 * 								See (http://codex.wordpress.org/Function_Reference/get_post)
	 */ 
	public function get_item( $index = 0 ) {
		if ( isset( $this->items[ $index ] ) )
			return $this->items[ $index ];
		else
			return false;
	}

	/**
	 * Returns an array of all the items in an order that match a given 
	 * type, or all items in the order.
	 * @param  string $item_type (optional) Item Type to filter by
	 * @return array            An array of items matching the criteria
	 */
	public function get_items( $item_type = '' ) {
		if ( empty( $item_type ) )
			return $this->items;

		$results = array();
		foreach ( $this->items as $item ){
			if ( $item['type'] == $item_type )
				$results[] = $item;
		}

		return $results;
	}

	/**
	 * Adds an item to the order.
	 * @param string $type    A string representing the type of item being added
	 * @param int $price  	  The price of the item
	 * @param int $post_id    The post that this item affects
	 */
	public function add_item( $type, $price, $post_id ) {

		$p2p_id = p2p_type( APPTHEMES_ORDER_CONNECTION )->connect( $this->id, $post_id );
		$item = array(
			'type' => $type,
			'price' => $price,
			'post_id' => $post_id,
			'post' => get_post( $post_id )
		);

		foreach ( array( 'type', 'price' ) as $field )
			p2p_add_meta( $p2p_id, $field, $item[ $field ] );

		$this->items[] = $item;
		$this->refresh_total();

	}

	/**
	 * Returns the gateway that should be used to 
	 * process this order
	 * @return string The Gateway Identifer. See APP_Gateway
	 */
	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 * Sets the gateway this order should be processed with
	 * @param string $gateway_id The Gateway Identifier. See APP_gateway
	 */
	public function set_gateway( $gateway_id ) {

		if ( $gateway = APP_Gateway_Registry::get_gateway( $gateway_id ) ){
			update_post_meta( $this->id, 'gateway', $gateway_id );
			$this->gateway = $gateway->identifier();
			return true;
		}

		return false;
	}

	/**
	 * Clears this order from being assocaited with a gateway.
	 * Used to prompt the user to select a new gateway
	 */
	public function clear_gateway() {

		$this->gateway = '';
		update_post_meta( $this->id, 'gateway', $this->gateway );

	}

	/**
	 * Returns the total price of the order
	 * @return int Total price of the order
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * Recalculates the total of the order.
	 * See get_total() for results
	 * @return void
	 */
	public function refresh_total() {

		$this->total = 0;
		foreach ( $this->items as $item )
			$this->total += (int) $item['price'];

		update_post_meta( $this->id, 'total_price', $this->total );

	}

	/**
	 * Sets the currency to be used in this order.
	 * Changing this does not affect any of the prices used in the order
	 * @param string $currency_code Currency code used to identify the 
	 * 								currency. Must be registered with APP_Currencies
	 * @return boolean True if currency was changed, false on error
	 */
	public function set_currency( $currency_code ) {
		if ( APP_Currencies::is_valid( $currency_code) )
		    $this->currency = $currency_code;
		else
		    return false;

		return true;
	}

	/**
	 * Returns the current currency's code. See APP_Currency
	 * @return string Current currency's code
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * Returns the current state of the order
	 * @return string State of the order. See order-functions.php for valid statuses
	 */
	public function get_status() {
		return $this->state;
	}

	/**
	 * Returns a version of the current state for display.
	 * @return string Current state, localized for display
	 */
	public function get_display_status() {

		$statuses = array(
			APPTHEMES_ORDER_PENDING => __( 'Pending', APP_TD ),
			APPTHEMES_ORDER_FAILED => __( 'Failed', APP_TD ),
			APPTHEMES_ORDER_COMPLETED => __( 'Completed', APP_TD ),
			APPTHEMES_ORDER_ACTIVATED => __( 'Activated', APP_TD ),
		);

		$status = $this->get_status();
		return $statuses[ $status ];

	}

	/**
	 * Sets the order as completed. Causes action 'appthemes_transaction_completd'
	 * @return void
	 */
	public function complete() {
		$this->set_status( APPTHEMES_ORDER_COMPLETED );
	}

	/**
	 * Sets the order as activated. Causes action 'appthemes_transaction_activated'
	 * @return void
	 */
	public function activate() {
		$this->set_status( APPTHEMES_ORDER_ACTIVATED );
	}

	/**
	 * Sets the order statues and sends out correct action hooks.
	 * New order status must be different than old status
	 * @param string $status Valid status for order. See order-functions.php
	 * 							for valid statuses
	 */	
	protected function set_status( $status ) {

		if ( $this->state == $status )
			return;

		wp_update_post( array(
			"ID" => $this->id,
			"post_status" => $status
		) );

		$this->state = $status;

		$statuses = array(
			APPTHEMES_ORDER_PENDING => 'pending',
			APPTHEMES_ORDER_FAILED => 'failed',
			APPTHEMES_ORDER_COMPLETED => 'completed',
			APPTHEMES_ORDER_ACTIVATED => 'activated'
		);

		do_action( 'appthemes_transaction_' . $statuses[ $status ], $this );
	}

}
