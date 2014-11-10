<?php

class APP_Order_Receipt{

	/**
	 * Retrieves an existing order by ID
	 * @param  int 	$order_id Order ID
	 * @return APP_Order Object representing the order
	 */
	static public function retrieve( $order_id ) {

		$order = APP_Order::retrieve( $order_id );
		return new APP_Order_Receipt( $order );
	}

	protected $order;

	public function __construct( $order ) {
		$this->order = $order;
	}

	/**
	 * Returns the Order ID
	 * @return int Order ID
	 */
	public function get_id() {
		return $this->order->get_id();
	}

	/**
	 * Returns the Order description
	 * @return string The order description
	 */
	public function get_description() {
		return $this->order->get_description();
	}

	/**
	 * Returns the User ID of the creator of the order
	 * @return int User ID
	 */
	public function get_author() {
		return $this->order->get_author();
	}

	/**
	 * Returns the IP Address used to create the order
	 * @return string IP Address
	 */
	public function get_ip_address() {
		return $this->order->get_ip_address();
	}

	/**
	 * Returns the URL to redirect to for processing the order
	 * @return string URL
	 */
	public function get_return_url() {
		return $this->order->get_return_url();
	}

	/**
	 * Returns the URL to redirect to for selecting a new gateway
	 * @return string URL
	 */
	public function get_cancel_url() {
		return $this->order->get_cancel_url();
	}

	/**
	 * Returns the total price of the order
	 * @return int Total price of the order
	 */
	public function get_total() {
		return $this->order->get_total();
	}

	/**
	 * Returns the current currency's code. See APP_Currency
	 * @return string Current currency's code
	 */
	public function get_currency() {
		return $this->order->get_currency();
	}

	/**
	 * Sets the order as completed. Causes action 'appthemes_transaction_completd'
	 * @return void
	 */
	public function complete() {
		$this->order->complete();
	}

}