<?php

/**
 * Keeps track of all registered gateways and their options
 */
class APP_Gateway_Registry{

	/**
	 * Options object containing the Gateway's options
	 * @var scbOptions
	 */
	public static $options;

	/**
	 * Currently registered gateways
	 * @var array
	 */
	public static $gateways;
	
	/**
	 * Registers a gateway by creating a new instance of it
	 * @param  string $class_name Class to create an instance of
	 * @return void
	 */
	public static function register_gateway( $class_name ){

		$instance = new $class_name;
		$identifier = $instance->identifier();

		self::$gateways[$identifier] = $instance;

	}
	
	/**
	 * Returns an instance of a registered gateway
	 * @param  string $gateway_id Identifier of a registered gateway
	 * @return mixed              Instance of the gateway, or false on error
	 */
	public static function get_gateway( $gateway_id ){

		if ( !self::is_gateway_registered( $gateway_id ) )
			return false;

		return self::$gateways[$gateway_id];

	}
	
	/**
	 * Returns an array of registered gateways
	 * @return array Registered gatewasys
	 */
	public static function get_gateways(){

		return self::$gateways;

	}

	/**
	 * Checks if a given gateway is registered
	 * @param  string  $gateway_id Identifier for registered gateway
	 * @return boolean             True if the gateway is registered, false otherwise
	 */
	public static function is_gateway_registered( $gateway_id ){

		return isset( self::$gateways[ $gateway_id ] );

	}
	
	/**
	 * Returns an array of active gateways
	 * @return array Active gateways
	 */
	public static function get_active_gateways(){

		$gateways = array();
		foreach ( self::$gateways as $gateway ) {

			if ( !self::is_gateway_enabled( $gateway->identifier() ) )
				continue;

			$gateways[ $gateway->identifier() ] = $gateway;
		}

		return $gateways;

	}

	/**
	 * Checks if a given gateway is enabled
	 * @param  string  $gateway_id Identifier for registered gateway
	 * @return boolean             True if the gateway is enabled, false otherwise
	 */
	public static function is_gateway_enabled( $gateway_id ){

		if( ! isset( self::$options->gateways['enabled'] ) ){
			self::$options->gateways['enabled'] = array();
		}
		
		$enabled_gateways = self::$options->gateways['enabled'];
		return isset( $enabled_gateways[$gateway_id] ) && $enabled_gateways[$gateway_id];
		
	}
	
	/**
	 * Registers an instance of scbOptions as the options handler
	 * Warning: Only use if you know what you're doing
	 * 
	 * @param  scbOptions $options Instance of scbOptions
	 * @return void
	 */
	public static function register_options( scbOptions $options ){

		self::$options = $options;

	}
	
	/**
	 * Returns the registered instance of the options handler
	 * @return scbOptions
	 */
	public static function get_options(){

		return self::$options;

	}
	
	/**
	 * Returns the options for the given registered gateway
	 * @param  string $gateway_id Identifier for registered gateway
	 * @return array              Associative array of options. See APP_Gateway::form()
	 */
	public static function get_gateway_options( $gateway_id ){

		return self::$options->get( array( 'gateways', $gateway_id ), array() );

	}

}

?>
