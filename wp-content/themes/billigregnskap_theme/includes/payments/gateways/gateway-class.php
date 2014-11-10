<?php

/**
 * Base class for Payment Gateways
 */
abstract class APP_Gateway {

	/**
	 * Unique identifier for this gateway
	 * @var string
	 */
	private $identifier;

	/**
	 * Display names used for this Gateway
	 * @var array
	 */
	private $display;

	/**
	 * Creates the Gateway class with the required information to display it
	 *
	 * @param string  $display_name The display name
	 * @param string  $identifier   The unique indentifier used to indentify your payment type
	 */
	public function __construct( $identifier, $args ) {

		$defaults = array(
			'dropdown' => $identifier,
			'admin' => $identifier,
		);

		$args = wp_parse_args( $args, $defaults );

		$this->display = array(
			'dropdown' => $args['dropdown'],
			'admin' => $args['admin'],
		);

		$this->identifier = $identifier;
	}

	/**
	 * Returns an array representing the form to output for admin configuration
	 * @return array scbForms style form array
	 */
	public abstract function form();

	/**
	 * Processes an order payment
	 * @param  APP_Order $order   The order to be processed
	 * @param  array $options 	  An array of user-entered options 
	 *   							corresponding to the values provided in form()
	 * @return void
	 */
	public abstract function process( $order, $options );

	/**
	 * Provides the display name for this Gateway
	 *
	 * @return string
	 */
	public final function display_name( $type = 'dropdown' ) {
		return $this->display[$type];
	}

	/**
	 * Provides the unique identifier for this Gateway
	 *
	 * @return string
	 */
	public final function identifier() {
		return $this->identifier;
	}

}
