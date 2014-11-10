<?php

add_action( 'admin_menu', 'appthemes_remove_orders_meta_boxes' );

/**
 * Removes Wordpress default metaboxes from the page
 * @return void
 */
function appthemes_remove_orders_meta_boxes() {

	remove_meta_box( 'submitdiv', APPTHEMES_ORDER_PTYPE, 'side' );
	remove_meta_box( 'postcustom', APPTHEMES_ORDER_PTYPE, 'normal' );
	remove_meta_box( 'slugdiv', APPTHEMES_ORDER_PTYPE, 'normal' );

	remove_meta_box( 'authordiv', APPTHEMES_ORDER_PTYPE, 'normal');

}

/**
 * Controls the Order Summary Meta Box
 */
class APP_Order_Items extends APP_Meta_Box{

	/**
	 * Sets up the meta box with Wordpress
	 * See APP_Meta_Box::__construct()
	 */
	function __construct(){
		parent::__construct( 'order-summary', 'Order Summary', APPTHEMES_ORDER_PTYPE, 'standalone', 'default' );
	}

	/**
	 * Displays a order summary table
	 * @param  object $post Wordpress Post object
	 * @return void
	 */
	function display( $post ){
		
		$order = appthemes_get_order( $_GET['post'] );
		
		?>
		<style type="text/css">
			#admin-order-summary tbody td{
				padding-top: 10px;
				padding-bottom: 10px;
			}
			#normal-sortables{
				display: none;
			}
		</style>
		<?php
		
		$table = new APP_Admin_Order_Summary_Table( $order );
		$table->show( array(
			'class' => 'widefat',
			'id' => 'admin-order-summary'
		) );

	}

}

class APP_Admin_Order_Summary_Table extends APP_Order_Summary_Table{

	protected function header( $items ){

		$cells = array(
			__( 'Order Summary', APP_TD ),
			__( 'Price', APP_TD ),
			__( 'Affects', APP_TD ),
		);

		return html( 'tr', array(), $this->cells( $cells, 'th' ) );

	}

	protected function footer( $items ){

		$cells = array(
			__( 'Total', APP_TD ),
			APP_Currencies::get_price( $this->order->get_total(), $this->currency ),
			''
		);

		return html( 'tr', array(), $this->cells( $cells, 'th' ) );

	}

	protected function row( $item ){

		if( ! APP_Item_Registry::is_registered( $item['type'] ) ){
			return html( 'tr', array(), html( 'td', array(
				'colspan' => '3',
				'style' => 'font-style: italic;'
			), __('This item could not be recognized. It might be from another theme or an uninstalled plugin.', APP_TD ) ) );
		}

		$cells = array(
			APP_Item_Registry::get_title( $item['type'] ),
			APP_Currencies::get_price( $item['price'], $this->currency ),
			html( 'a', array(
				'href' => get_permalink($item['post_id'])
			), $item['post']->post_title )
		);

		return html( 'tr', array(), $this->cells( $cells ) );

	}

}

/**
 * Controls the Order Status Meta Box
 */
class APP_Order_Status extends APP_Meta_Box{

	/**
	 * Sets up the meta box with Wordpress
	 * See APP_Meta_Box::__construct()
	 */
	function __construct(){
		parent::__construct( 'order-status', 'Order Status', APPTHEMES_ORDER_PTYPE, 'side', 'default' );
	}

	/**
	 * Displays the order status summary
	 * @param  object $post Wordpress Post object
	 * @return void       
	 */
	function display( $post ){
		
		$order = appthemes_get_order( $post->ID );
		?>
		<style type="text/css">
			#admin-order-status th{
				padding-right: 10px;
				text-align: right;
				width: 40%;
			}
		</style>
		<table id="admin-order-status">
			<tbody>
				<tr>
					<th><?php _e( 'ID', APP_TD ); ?>: </th>
					<td><?php echo $order->get_ID(); ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Status', APP_TD ); ?>: </th>
					<td><?php echo $order->get_display_status(); ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Gateway', APP_TD ); ?>: </th>
					<td>
					<?php
					$gateway_id = $order->get_gateway();
			
					if ( !empty( $gateway_id ) ) {
						$gateway = APP_Gateway_Registry::get_gateway( $gateway_id );
						if( $gateway ){
							echo $gateway->display_name( 'admin' );
						}else{
							_e( 'Unknown', APP_TD );
						}
					}else{
						_e( 'Undecided', APP_TD );
					}
					?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Currency', APP_TD ); ?>: </th>
					<td><?php echo APP_Currencies::get_currency_string( $order->get_currency() ); ?></td>
				</tr>
			</tbody>
		</table>
		<?php

	}

}

/**
 * Controls the Order Author Meta box
 */
class APP_Order_Author extends APP_Meta_Box{

	/**
	 * Sets up the meta box with Wordpress
	 * See APP_Meta_Box::__construct()
	 */
	function __construct(){
		parent::__construct( 'order-author', 'Order Author', APPTHEMES_ORDER_PTYPE, 'side', 'default' );
	}

	/**
	 * Displays the order author box
	 * @param  object $post Wordpress Post object
	 * @return void
	 */
	function display( $post ){
		
		$order = appthemes_get_order( $post->ID );
		?>
		<style type="text/css">
			#admin-order-author{
				padding-left: 10px;
				text-align: left;
			}
			.avatar{
				float: left;
			}
		</style>
		<?php echo get_avatar( $order->get_author(), 72 ); ?>
		<table id="admin-order-author">
			<?php $user = get_userdata( $order->get_author() ); ?>
			<tbody>
				<tr>
					<td><?php 
					
					$username = $user->user_login;
					$display_name = $user->display_name; 

					if( $username == $display_name )
						echo $username;
					else
						echo $display_name . ' (' . $username . ') ';

					?></td>
				</tr>
				<tr>
					<td><?php echo $user->user_email; ?></td>
				</tr>
				<tr>
					<td><?php echo $order->get_ip_address(); ?></td>
				</tr>
			</tbody>
		</table>
		<div class="clear"></div>
		<?php

	}

}



new APP_Order_Items();
new APP_Order_Status();
new APP_Order_Author();