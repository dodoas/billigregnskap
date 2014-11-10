<?php

/**
 * Registers a gateway with the APP_Gateway_Registry
 * @param  string $class_name Name of the class to be used as a Gateway
 * @return void
 */
function appthemes_register_gateway( $class_name ) {

	APP_Gateway_Registry::register_gateway( $class_name );	
	
}

/**
 * Runs the process() method on a currently active gateway
 * @param  string $gateway_id Identifier of currently active gateway
 * @param  APP_Order $order   Order to be processed
 * @return boolean            False on error
 */
function appthemes_process_gateway( $gateway_id, $order ) {

	$receipt_order = APP_Order_Receipt::retrieve( $order->get_id() );
	$options = APP_Gateway_Registry::get_gateway_options( $gateway_id );
	$gateway = APP_Gateway_Registry::get_gateway( $gateway_id );

	if( APP_Gateway_Registry::is_gateway_enabled( $gateway_id ) || current_user_can( 'manage_options') )
		$gateway->process( $receipt_order, $options );
	else
		return false;
	
}

/**
 * Displays a dropdown form with currently active gateways
 * @param  string $input_name Name of the input field
 * @return void
 */
function appthemes_list_gateway_dropdown( $input_name = 'payment_gateway' ) {

	$gateways = array();
	foreach ( APP_Gateway_Registry::get_gateways() as $gateway ) {

		if ( !APP_Gateway_registry::is_gateway_enabled( $gateway->identifier() ) ) {

			if( current_user_can( 'manage_options' ) )
				$gateways[ $gateway->identifier() ] = $gateway->display_name( 'dropdown' ) . __( ' (disabled)');

			continue;
		}
		$gateways[ $gateway->identifier() ] = $gateway->display_name( 'dropdown' );
	}

	if( empty( $gateways ) ){
		$gateways[''] = __( '-- No Gateways are currently available. --', APP_TD );
	}

	echo scbForms::input( array(
		'type' => 'select',
		'name' => $input_name,
		'values' => $gateways,
		'extra' => array( 'class' => 'required' )
	) );
}