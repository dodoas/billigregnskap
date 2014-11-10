<?php

/**
 * Represents an order that has not been written to the database yet.
 * All methods used this class will not write to the database
 */
class APP_Draft_Order extends APP_Order{

	/**
	 * Upgrrades a Draft Order to a Main Order
	 * @param  APP_Draft_Order $draft_order The draft order to Upgrade
	 * @return APP_Order              		An upgraded order
	 */
	public static function upgrade( $draft_order ){

		$order = APP_Order::create();

		$order->set_gateway( $draft_order->get_gateway() );
		$order->set_currency( $draft_order->get_currency() );

		foreach( $draft_order->get_items() as $item)
			$order->add_item( $item['type'], $item['price'], $item['post_id'] );

		return $order;

	}

	/**
	 * Records the current IP Address of the user
	 */
	public function __construct() {
		$this->ip_address = $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * See APP_Order::add_item() for more information
	 * @param string $type    A string representing the type of item being added
	 * @param int $price  	  The price of the item
	 * @param int $post_id    The post that this item affects
	 */
	public function add_item( $type, $price, $post_id ) {
		$item = array(
			'type' => $type,
			'price' => $price,
			'post_id' => $post_id,
			'post' => get_post( $post_id )
		);
		$this->items[] = $item;
		$this->refresh_total();
	}
	
	/**
	 * See APP_Order::set_gateway() for more information
	 * @param string $gateway_id The Gateway Identifier. See APP_gateway
	 */
	public function set_gateway( $gateway_id ) {

		if ( APP_Gateway_Registry::get_gateway( $gateway_id ) ){
			$this->gateway = $gateway->identifier();
			return true;
		}

		return false;

	}
	
	/**
	 * See APP_Order::clear_gateway()
	 * @return boolean True
	 */
	public function clear_gateway(){
		$this->gateway = '';
		return true;		
	}
	
	/**
	 * See APP_Order::refresh_total()
	 * @return void
	 */
	public function refresh_total() {
		$this->total = 0;
		foreach( $this->items as $item )
			$this->total += (int) $item['price'];
	}

	/**
	 * See APP_Order::set_status()
	 * @param string $status Valid status for order. See order-functions.php
	 * 							for valid statuses
	 */	
	protected function set_status( $state ) {
	    
		if ( $this->state == $status )
			return;

		$this->state = $status;

	}
	
}
