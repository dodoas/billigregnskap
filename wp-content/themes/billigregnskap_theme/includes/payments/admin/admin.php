<?php

if( is_admin() ){
	add_action( 'admin_menu', 'appthemes_admin_menu_setup', 11 );
	add_action( 'admin_print_styles', 'appthemes_payments_menu_sprite' );
	add_action( 'admin_print_styles', 'appthemes_payments_icon' );
	add_action( 'init', 'appthemes_register_payments_settings', 12);
	add_action( 'admin_print_styles', 'appthemes_order_table_hide_elements' );

	add_filter( 'manage_' . APPTHEMES_ORDER_PTYPE . '_posts_columns', 'va_order_manage_columns' );
	add_filter( 'manage_edit-' . APPTHEMES_ORDER_PTYPE . '_sortable_columns', 'va_order_manage_sortable_columns' );
	add_action( 'manage_' . APPTHEMES_ORDER_PTYPE . '_posts_custom_column', 'va_order_add_column_data', 10, 2 );
}

/**
 * Registers the payment settings page
 * @return void
 */
function appthemes_register_payments_settings(){
	new APP_Payments_Settings_Admin( APP_Gateway_Registry::get_options() );
}

/**
 * Adds the Orders Top Level Menu
 * @return void
 */
function appthemes_admin_menu_setup(){
	add_menu_page( __( 'Orders', APP_TD ), __( 'Payments', APP_TD ), 'manage_options', 'app-payments', null, appthemes_framework_image( 'payments.png' ), 4 );
}

/**
 * Adds the Payments Menu Sprite to the CSS for admin pages
 * @return void
 */
function appthemes_payments_menu_sprite() {
	$sprite_url = appthemes_framework_image( 'payments.png' );

echo <<<EOB
<style type="text/css">

#toplevel_page_app-payments div.wp-menu-image {
	background-image: url('$sprite_url');
	background-position: -31px 7px !important;
	background-repeat: no-repeat;
}

#toplevel_page_app-payments div.wp-menu-image img {
	display: none;
}

#toplevel_page_app-payments:hover div.wp-menu-image,
#toplevel_page_app-payments.wp-has-current-submenu div.wp-menu-image {
	background-position: -1px 7px !important;
}
</style>
EOB;

}

/**
 * Adds the Payments Icon for certain pages
 * @return void
 */
function appthemes_payments_icon(){
	$url = appthemes_framework_image( 'payments-med.png' );
?>
<style type="text/css">
	.icon32-posts-pricing-plan,
	.icon32-posts-transaction {
		background-image: url('<?php echo $url; ?>');
		background-position: -12px -5px !important;
	}
</style>
<?php
}

/**
 * Sets the columns for the orders page
 * @param  array $columns Currently available columns
 * @return array          New column order
 */
function va_order_manage_columns( $columns ) {

	$columns['order'] = __( 'Order', APP_TD );
	$columns['order_author'] = __( 'Author', APP_TD );
	$columns['item'] = __( 'Item', APP_TD );
	$columns['price'] = __( 'Price', APP_TD );
	$columns['order_date'] = __( 'Date', APP_TD );
	$columns['payment'] = __( 'Payment', APP_TD );
	
	unset( $columns['cb'] );
	unset( $columns['title'] );
	unset( $columns['author'] );
	unset( $columns['date'] );
	return $columns;

}

/**
 * Sets the columns for the orders page
 * @param  array $columns Currently available columns
 * @return array          New column order
 */
function va_order_manage_sortable_columns( $columns ) {
	$columns['order'] = 'ID';
	$columns['order_date'] = 'date';
	$columns['order_author'] = 'author';
	$columns['price'] = 'price';
	$columns['payment'] = 'gateway';
	return $columns;

}


/**
 * Outputs column data for orders
 * @param  string $column_index Name of the column being processed
 * @param  int $post_id         ID of order being dispalyed
 * @return void               
 */
function va_order_add_column_data( $column_index, $post_id ) {

	$order = appthemes_get_order( $post_id );

	switch( $column_index ){
	
		case 'order' : 
			echo '<a href="' . get_edit_post_link( $post_id ) . '">' . $order->get_ID() . '</a>';
			break;

		case 'order_author':
			$user = get_userdata( $order->get_author() );
			echo $user->display_name;
			echo '<br>';
			echo $order->get_ip_address();
			break;

		case 'item' :
			
			$count = count( $order->get_items() );
			$string = _n( 'Purchased %s item', 'Purchased %s items', $count, APP_TD );

			printf( $string, $count );
			break;
			
		case 'price':
			$currency = $order->get_currency();
			if( !empty( $currency ) ){
				echo APP_Currencies::get_price( $order->get_total(), $order->get_currency() );
			}else{
				echo APP_Currencies::get_price( $order->get_total() );
			}
			break;
			
		case 'payment':
		
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
				return;
			}
			
			echo '</br>';
			
			$status = $order->get_display_status();
			if( $status != 'completed' ){
				echo '<strong>' . ucfirst( $status ) . '</strong>';
			}else{
				echo ucfirst( $status );
			}
			
			break;
			
		case 'status':
			echo ucfirst( $order->get_status() );
			break;
		
		case 'order_date':
			$order_post = get_post( $order->get_ID() );
			if ( '0000-00-00 00:00:00' == $order_post->post_date ) {
				$t_time = $h_time = __( 'Unpublished' );
				$time_diff = 0;
			} else {
				$t_time = get_the_time( __( 'Y/m/d g:i:s A' ) );
				$m_time = $order_post->post_date;
				$time = get_post_time( 'G', true, $order_post );

				$time_diff = time() - $time;

				if ( $time_diff > 0 && $time_diff < 24*60*60 )
					$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
				else
					$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
			}
			echo '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
				
			break;
	}

}

/**
 * Hides elements of listing page
 * @return void
 */
function appthemes_order_table_hide_elements(){

	if( is_admin() ){

		if( is_admin() && 'edit.php' == $GLOBALS['pagenow'] && isset( $_GET['post_type']) && $_GET['post_type'] == APPTHEMES_ORDER_PTYPE ){
			?>
			<style type="text/css">
				.top .actions:first-child, .bottom .actions:first-child{
					display: none;
				}
			</style>
			<?php
		}
	?><style type="text/css">
		a[href="post-new.php?post_type=transaction"]{
				display: none;
		}
		</style><?php 
	}
}

