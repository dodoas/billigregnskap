<?php

/**
 * Helps process PayPal PDT Payments
 */
class APP_Paypal_PDT{

	/**
	 * Holds an array of user-submitted options
	 * @var array
	 */
	static private $options;

	/**
	 * API URLs to connect to
	 * @var array
	 */
	static private $post_urls = array(
		'sandbox' => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
		'live' => 'https://www.paypal.com/cgi-bin/webscr'
	);

	/**
	 * Sets up the class for processing payments
	 * @param  [type] $options [description]
	 * @return [type]          [description]
	 */
	static public function init( $options ){
		self::$options = $options;
	}
	
	/**
	 * Checks whether the current response is a valid transaction key
	 * @param  APP_Order  $order   Order being processed
	 * @param  array  $options     User inputted options
	 * @return boolean              True if transaction key is valid, false if invalid
	 */
	static public function is_valid_transaction( $order, $options ){
		
		$transaction_id = $_GET['tx'];
		$identity_token = $options['pdt_key'];
		
		if( !self::validate_transaction( $order, $transaction_id, $identity_token ) ){
			return false;
		}
		
		wp_update_post( array(
			"ID" => $order->get_id(),
			"post_content" => $transaction_id
		));
		
		return true;
	}
	
	/**
	 * Validates a transaction id by checking the PayPal API
	 * @param  APP_Order $order          Order being processed
	 * @param  string $transaction_id    Transaction ID being tested
	 * @param  string $identity_token    Secret Identity Token
	 * @return boolean                   True if valid, false if not
	 */
	static private function validate_transaction( $order, $transaction_id, $identity_token ) {

		$data = array(
			'cmd' => '_notify-synch',
			'tx' => $transaction_id,
			'at' => $identity_token
		);

		$response = self::get_transaction( $data );
		if( $response == false )
			return false;
		
		// Check that the transaction is for the right order id
		if( $response['item_number'] != $order->get_id() )
			return false;

		return true;

	}
	
	/**
	 * Retrieves transaction data from PayPal
	 * @param  array  $data     Data to send to PayPal
	 * @param  boolean $sandbox True if using sandbox servers, false if production
	 * @return mixed            Array of values returned about the transaction. Returns
	 *  							empty if given invalid transaction key
	 */	
	static private function get_transaction( $data, $sandbox = false ){

		$url = ( self::is_sandbox() ) ? self::$post_urls['sandbox'] : self::$post_urls['live'];	
		$options = array(
			'method' => 'POST',
			'body' => $data,
			'sslverify' => false,
		);

		$response =  self::get_url( $url, $options );
	
		$values = array();
		if ( strpos( $response, 'SUCCESS' ) !== 0 )
			return $values;
			
		$lines = explode( "\n", $response );

		foreach($lines as $string){
		
			$key_value_string = explode( '=', $string );
			
			if( array_key_exists(1, $key_value_string ) )
				$value = $key_value_string[1];
			else
				$value = '';
			
			$values[ $key_value_string[0] ] = $value;
		
		}
		
		return $values;
	}
	
	/**
	 * Checks if PDT is enabled on this site
	 * @return boolean True if enabled, false if not
	 */
	static public function is_enabled(){
		return !empty( self::$options['pdt_enabled'] );
	}

	/**
	 * Checks if this request is in a format PDT can handle
	 * @return boolean True if handlable, false otherwise
	 */
	static public function can_be_handled(){
		return isset( $_GET['tx'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'paypal');
	}
	
	/**
	 * Checks if gateway is set to sandbox-mode
	 * @return boolean True if sandboxed, false otherwise
	 */
	static public function is_sandbox(){
		return !empty( self::$options['sandbox_enabled'] );
	}
	
	/**
	 * Returns the body of a requested URL
	 * @param  string $url     The URL to grab
	 * @param  array  $options The data to send to the site
	 * @return string          The response to the request
	 */
	static private function get_url( $url, $options ){
		$response = wp_remote_post( $url, $options );
		return $response['body'];
	}

}

?>