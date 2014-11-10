<?php 

/**
 * 	Helper class for redirect based gateways
 */
abstract class APP_Boomerang extends APP_Gateway {

	public function process( $order, $options ) {

		if ( $this->is_returning() && $this->is_valid( $order, $options ) ) {
			$this->complete_order( $order );
		}
		else {
			$this->create_form( $order, $options );
		}

	}

	abstract protected function create_form( $order, $options );

	protected function is_valid( $order, $options ){
		return true;
	}
	
	protected function redirect( $form_attributes, $values, $message = '' ){

		$defaults = array(
			'action' => '',
			'name' => $this->identifier(),
			'id' => $this->identifier(),
			'method' => 'POST'
		);
		$form_attributes = wp_parse_args( $form_attributes, $defaults );

		$form = $this->get_form_inputs( $values );
		$form .= html( 'input', array(
			'type' => 'submit',
			'style' => 'display: none;'
		) );

		if ( empty( $message ) )
			$message = __( 'You are now being redirected.', APP_TD );

		$form .= html( 'span', array( 'class' => 'redirect-text' ),  $message );

		echo html( 'form', $form_attributes, $form );
		echo html( 'script', array(), 'jQuery(function(){ document.' . $form_attributes['name'] . '.submit(); });' );

	}

	/**
	 * Returns the HTML
	 * @param  [type] $values [description]
	 * @return [type]         [description]
	 */
	protected function get_form_inputs( $values ){

		$form = '';
		foreach ( $values as $name => $value ){

			$attributes = array(
				'type' => 'hidden',
				'name' => $name,
				'value' => $value
			);

			$form .= html( 'input', $attributes, '' );

		}

		return $form;

	}

	/**
	 * Checks if the user is returning via a nonce'd url
	 * @return boolean True if the nonce is valid, false otherwise
	 */
	protected function is_returning(){
		return isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], $this->identifier() );
	}

	/**
	 * Creates a nonce'd URL to redirect back to
	 * @param  APP_Order $order Order being redirected back to
	 * @return string           Return URL
	 */
	protected function get_return_url( $order ){
		return wp_nonce_url( $order->get_return_url(), $this->identifier() );
	}

	protected function get_cancel_url( $order ){
		return $order->get_cancel_url();
	}

	/**
	 * Displays an error for the user
	 * @param  string $message Message to display
	 * @return void
	 */
	protected function fail_order( $message ){
		appthemes_display_notice( 'error', $message );
	}

	/**
	 * Completes an order and redirects the user to the listing
	 * @param  APP_Order $order Order to complete
	 * @return void
	 */
	protected function complete_order( $order ){
		$order->complete();
	}

	/**
	 * Redirects a user via javascript
	 * @param  string $url  URL to redirect to
	 * @param  string $text Message to display to user
	 * @return void
	 */
	protected function js_redirect( $url, $text ){

		$attributes = array(
			'class' => 'redirect-text'
		);

		echo html( 'span', $attributes, $text );
		echo html( 'script', array(), 'jQuery(function(){ location.href="' . $url . '" });' );

	}

}