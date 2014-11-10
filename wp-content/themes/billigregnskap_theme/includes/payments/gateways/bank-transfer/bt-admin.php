<?php

class APP_Bank_Transfer_Queue extends APP_Meta_Box {
	
	public function __construct(){
		parent::__construct( 'bank-transfer-queue', __( 'Bank Transfer', APP_TD ), APPTHEMES_ORDER_PTYPE, 'side', 'high' );
	}

	function conditional(){

		if( !isset( $_GET['post'] ) && !isset( $_POST['post_ID'] ) )
			return; 

		if( isset( $_GET['post'] ) )
			$post_id = $_GET['post'];
		else
			$post_id = $_POST['post_ID'];

		$order = appthemes_get_order( $post_id );
		if( !$this->check_order( $order ) )
			return false;

		return true;

	}

	function display( $post ){

		echo html( 'p', array(), __( 'You must mark this transfer as completed before the purchase can proceed.', APP_TD ) );

		echo html( 'input', array(
			'type' => 'submit',
			'class' => 'button-primary',
			'value' => __( 'Mark as Completed', APP_TD ),
			'name' => 'complete_order',
			'style' => 'padding-left: 10px; padding-right: 10px; margin-bottom: 10px;',
		));

	}

	function save( $post_id, $post ){

		$order = appthemes_get_order( $post_id );
		if( !$this->check_order( $order ) )
			return;

		remove_action( 'save_post', array( $this, 'save' ), 10, 2);
		if( isset( $_POST['complete_order'] ) )
			$order->complete();

	}

	function check_order( $order ){

		if( !$order || $order->get_status() !=  APPTHEMES_ORDER_PENDING )
			return false;

		if( $order->get_gateway() != 'bank-transfer' )
			return false;

		return true;
	}

}