<?php

add_action( 'wp_loaded', 'va_handle_listing_purchase' );
add_action( 'wp_loaded', 'va_handle_claim_listing_purchase' );

add_action( 'va_create_listing_order', 'va_handle_addons', 10, 2 );

add_action( 'va_listing_validate_purchase_fields', 'va_check_listing_plan' );

function va_handle_listing_purchase() {
	global $va_options;

	if ( !isset( $_POST['action'] ) || 'purchase-listing' != $_POST['action'] )
		return;
	
	if ( !current_user_can( 'edit_listings' ) )
		return;

	$errors = apply_filters( 'va_listing_validate_purchase_fields', va_get_listing_error_obj() );
	if( $errors->get_error_codes() ){
		return false;
	}

	$order = appthemes_new_order();
	$listing = get_post( $_POST['ID'] );

	do_action( 'va_create_listing_order', $order, $listing );

	wp_redirect( $order->get_return_url() );
	exit;
}

function va_handle_claim_listing_purchase() {
	global $va_options;

	if ( !isset( $_POST['action'] ) || 'claim-listing' != $_POST['action'] )
		return;

	if ( !current_user_can( 'edit_listings' ) )
		return;

	check_admin_referer( 'va_claim_listing' );

	if ( !$va_options->listing_charge) {
		VA_Listing_Claim::handle_no_charge_claim_listing($_POST['ID']);
	}
}

function va_check_listing_plan( $errors ){
	
	$post = get_post( $_POST['ID'] );
	if( ( $post && ! _va_needs_publish( $post ) ) && ! _va_is_claimable( $_POST['ID'] ) ){
		return $errors;
	}

	if( empty( $_POST['plan'] ) ){
		$errors->add( 'no-plan', __( 'No plan was chosen.', APP_TD ) );
		return $errors;
	}

	add_action( 'va_create_listing_order', 'va_handle_plan', 9, 2 );
	return $errors;	
}

function va_handle_plan( $order, $listing ){

	$plan = get_post( intval( $_POST['plan'] ) );
	if( ! $plan ){
		return false;
	}
	$plan_data = get_post_custom( $plan->ID );

	$order->add_item( $plan->post_name, $plan_data['price'][0], $_POST['ID'] );

}

function va_handle_addons( $order, $listing ){

	foreach( array( VA_ITEM_FEATURED_HOME, VA_ITEM_FEATURED_CAT ) as $addon ){

		$already_featured = (bool) get_post_meta( $listing->ID, $addon, true );
		if( $already_featured == false && !empty( $_POST[$addon.'_'.intval($_POST['plan'])] ) ){

			$price = APP_Item_Registry::get_meta( $addon, 'price' );
			$order->add_item( $addon, $price, $listing->ID );

		}
	}

}

function va_get_listing_available_plans($listing) {
	$plans = new WP_Query( array(
		'post_type' => APPTHEMES_PRICE_PLAN_PTYPE,
		'nopaging' => 1,
		'tax_query' => array(
			array(
				'taxonomy' => VA_LISTING_CATEGORY,
				'field' => 'id',
				'terms' => get_the_listing_category( $listing->ID )->term_id,
				'include_children' => false
			)
		)
	));

	$plans_data = array();
	foreach( $plans->posts as $key => $plan){
		$plans_data[$key] = get_post_custom( $plan->ID );
		$plans_data[$key]['post_data'] = $plan;
	}

	return $plans_data;
}

function _va_show_featured_addon( $addon, $listing_id, $plan_id ){
	global $va_options;

	$addon_title = APP_Item_Registry::get_title( $addon ); 
    $addon_price = APP_Currencies::get_price( APP_Item_Registry::get_meta( $addon , 'price' ) ); 
    $addon_duration = $va_options->addons[$addon]['duration']; 

	// If already on featured option, output disabled checkbox with expiration date
	if( _va_already_featured( $addon, $listing_id ) ){
		_va_show_featured_option( $addon, true, $plan_id );

		$expiration_date = va_get_featured_exipration_date( $addon, $listing_id );
		if('Never' == $expiration_date) {
			printf( __( ' %s for Unlimited days', APP_TD ), $addon_title);
		} else {
			printf( __( ' %s until %s', APP_TD ), $addon_title, $expiration_date );
		}
		return;
	}

	// If the featured listing is disabled, don't bother
	if( _va_addon_disabled( $addon ) ){
		return;
	}

	_va_show_featured_option( $addon, false, $plan_id );
	if( $addon_duration == 0 ){
		$string = __( ' %s for Unlimited days for only %s more.', APP_TD );
		printf( $string, $addon_title, $addon_price );
	}else{
		$string = __( ' %s for %d days for only %s more.', APP_TD );
		printf( $string, $addon_title, $addon_duration, $addon_price );
	}
}

function _va_no_featured_available( $plan ) {
	if( empty($plan[VA_ITEM_FEATURED_HOME][0] ) && empty($plan[VA_ITEM_FEATURED_CAT][0] ) &&  empty($plan['disable_featured'][0]) ) {
		if( _va_addon_disabled( VA_ITEM_FEATURED_HOME ) && _va_addon_disabled( VA_ITEM_FEATURED_CAT )) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function _va_no_featured_purchasable( $plan, $listing ) {
	if( _va_no_featured_available( $plan ) ){
		return true;
	} 
	
	foreach ( array( VA_ITEM_FEATURED_HOME, VA_ITEM_FEATURED_CAT ) as $addon ) {
		if( !_va_already_featured( $addon, $listing->ID ) && !_va_addon_disabled( $addon ) ) {
			return false;
		}		
	}
	return true;
}

function _va_already_featured( $addon, $listing_id ){

	$meta = get_post_meta( $listing_id, $addon, true );
	if( $meta ){
		return true;
	}else{
		return false;
	}

}

function _va_addon_disabled( $addon ){
	global $va_options;
	return empty( $va_options->addons[ $addon ]['enabled'] );
}

function _va_show_featured_option( $addon, $enabled = false, $plan_id){
	echo html( 'input', array(
		'name' => $addon.'_'.$plan_id,
		'type' => 'checkbox',
		'disabled' => $enabled,
		'checked' => $enabled
	) );
}

function va_get_claimed_listing( $listing_id = '' ) {
	$listing_id = !empty( $listing_id ) ? $listing_id : get_queried_object_id();
	$args = array(
		'post_type' => VA_LISTING_PTYPE,
		'post_status' => array( 'publish' ),
		'p' => $listing_id,
	);
	
	$query = new WP_Query( $args );
	return $query;
}