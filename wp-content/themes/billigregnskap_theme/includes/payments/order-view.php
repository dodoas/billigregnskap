<?php
class APP_Order_Summary extends APP_View {

	function condition() {
		return is_singular( APPTHEMES_ORDER_PTYPE );
	}

	function template_include( $template ) {
		
		$order = get_the_order();

		$currentuser = wp_get_current_user();
		if ( $order->get_author() != $currentuser->ID ) {
			return locate_template( '404.php' );
		}

		if ( $order->get_total() == 0 ) {

			if( count( $order->get_items() ) > 0 )
				$order->complete();

			return locate_template( 'order-summary.php' );
		}

		if( $order->get_status() != APPTHEMES_ORDER_PENDING ){
			return locate_template( 'order-summary.php' );
		}

		$gateway = $this->resolve_gateway( $order );
		if( empty( $gateway ) ){
			return locate_template( 'order-checkout.php' );
		}else{
			return locate_template( 'order-gateway.php' );
		}

	}

	function resolve_gateway( $order ){

		if( isset( $_GET['cancel'] ) ){
			$order->clear_gateway();
		}

		$gateway = $order->get_gateway();
		if ( !empty( $_POST['payment_gateway'] ) && empty( $gateway ) ) {
			$order->set_gateway( $_POST['payment_gateway'] );
		}

		return $order->get_gateway();

	}

}

class APP_Order_Summary_Table extends APP_Table{

	protected $order, $currency;

	public function __construct( $order ){

		$this->order = $order;
		$this->currency = $order->get_currency();

	}

	public function show( $attributes = array() ){
		echo $this->table( $this->order->get_items(), $attributes );
	}

	protected function footer( $items ){

		$cells = array(
			__( 'Total', APP_TD ),
			APP_Currencies::get_price( $this->order->get_total(), $this->currency )
		);

		return html( 'tr', array(), $this->cells( $cells, 'td' ) );

	}

	protected function row( $item ){

		if( ! APP_Item_Registry::is_registered( $item['type'] ) ){
			return '';
		}

		$cells = array(
			APP_Item_Registry::get_title( $item['type'] ),
			APP_Currencies::get_price( $item['price'], $this->currency )
		);

		return html( 'tr', array(), $this->cells( $cells ) );

	}

}