<?php

function get_the_order(){
	$queried_order = get_queried_object();
	return appthemes_get_order( $queried_order->ID );
}

function the_order_summary(){
	$order = get_the_order();
	$table = new APP_Order_Summary_Table( $order );
	$table->show();
}

function the_order_return_url(){
	$order = get_the_order();
	return $order->get_return_url();
}

function process_the_order(){
	$order = get_the_order();
	appthemes_process_gateway( $order->get_gateway(), $order );
}

?>