<?php
function va_get_dashboard_permalink_setting( $permalink ) {
	global $va_options;

	$permalinks = array(
		'dashboard' => $va_options->dashboard_permalink,
		'listings' => $va_options->dashboard_listings_permalink,
		'reviews' => $va_options->dashboard_reviews_permalink,
		'favorites' => $va_options->dashboard_faves_permalink,
		'claimed-listings' => $va_options->dashboard_claimed_permalink,
	);

	return $permalinks[$permalink];
}

function va_get_dashboard_name() {
	global $wp_query;
	$dashboard_type = $wp_query->get( 'dashboard' );

	if ( $dashboard_type == va_get_dashboard_permalink_setting('reviews') ) {
		return __( 'Reviews', APP_TD );
	} else if ( $dashboard_type == va_get_dashboard_permalink_setting('claimed-listings') ) {
		return __( 'Claimed Listings', APP_TD );
	} else {
		return __( 'Listings', APP_TD );
	}
}

function va_get_dashboard_type() {
	global $wp_query;
	$dashboard_type = $wp_query->get( 'dashboard' );

	if ( $dashboard_type == va_get_dashboard_permalink_setting('reviews') ) {
		return 'reviews';
	} else if ( $dashboard_type == va_get_dashboard_permalink_setting('claimed-listings') ) {
		return 'claimed-listings';
	} elseif ( $dashboard_type == va_get_dashboard_permalink_setting('favorites') ) {
		return 'favorites';
	} else {
		return 'listings';
	}
}

function va_is_own_dashboard() {
	global $wp_query;
	$dashboard_author = $wp_query->get( 'dashboard_author' );

	if ( $dashboard_author == 'self' ) {
		return true;
	} else {
		return false;
	}
}

function va_get_dashboard_author() {
	global $wp_query;
	$dashboard_author = $wp_query->get( 'dashboard_author' );

	if ( $dashboard_author == 'self' ) {
		$user = wp_get_current_user();
	} else {
		$user = get_user_by( 'slug', $dashboard_author );
	}

	$user->email_public = get_user_meta( $user->ID, 'email_public', true );
	$user->twitter = get_user_meta( $user->ID, 'twitter', true );
	$user->facebook = get_user_meta( $user->ID, 'facebook', true );
	$user->has_claimed = (bool) get_user_meta( $user->ID, 'claimee', true );

	return $user;
}

function va_get_dashboard_verbiage( $key ) {

	$dashboard_verbiage = array(
		'pending' => __( 'Pending', APP_TD ),
		'pending-claimed' => __( 'Pending Claimed', APP_TD ),		
		'publish' => __( 'Active', APP_TD ),
		'expired' => __( 'Expired', APP_TD ),
	);

	return $dashboard_verbiage[$key];
}

function va_dashboard_get_user_stats( $user ) {
	global $wpdb;

	$stats = array();

	$stats['reviews_live'] = va_get_user_reviews_count( $user->ID, 'approve' );
	$stats['reviews_pending'] = va_get_user_reviews_count( $user->ID, 'hold' );
	$stats['reviews_total'] = $stats['reviews_live'] + $stats['reviews_pending'];

	$stats['listings_live'] = va_count_user_posts( $user->ID, 'publish' );
	$stats['listings_pending'] = va_count_user_posts( $user->ID, 'pending' );
	$stats['listings_expired'] = va_count_user_posts( $user->ID, 'expired' );

	$stats['listings_total'] = $stats['listings_live'] + $stats['listings_pending'] + $stats['listings_expired'];

	return $stats;
}

function va_count_user_posts( $user_id , $status ) {
	global $wpdb;

	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = %d AND post_type = %s AND post_status = %s", $user_id, VA_LISTING_PTYPE, $status ) );
}


function va_dashboard_show_account_info( $user, $self = false ) {

	if ( !is_user_logged_in() ) return false;

	if ( $self ) return true;


	$twitter = ( !empty( $user->twitter ) ? true : false );

	if ( $twitter ) return true;

	$facebook = ( !empty( $user->facebook ) ? true : false );

	if ( $facebook ) return true;

	$email = ( !empty( $user->email_public ) ? true : false );

	if ( $email ) return true;

	$url = ( !empty( $user->user_url ) ? true : false );

	if ( $url ) return true;

	return false;

}

function va_get_dashboard_listings( $user_id, $self = false ) {
	global $va_options;
	
	$args = array(
		'post_type' => VA_LISTING_PTYPE,
		'author' => $user_id,
		'paged' => get_query_var( 'paged' ),
		'posts_per_page' => $va_options->listings_per_page
	);

	if ( $self ) {
		$args['post_status'] = array( 'publish', 'pending', 'expired' );
	} else {
		$args['post_status'] = array( 'publish' );
	}

	$query = new WP_Query( $args );
	return $query;
}

function va_get_dashboard_favorites( $user_id, $self = false ) {

	$favorites = new WP_Query( array(
	  'connected_type'  => 'va_favorites',
	  'connected_items' => $user_id,
	  'nopaging' 	    => true,
	) );

	return $favorites;

}

function va_get_dashboard_reviews( $user_id, $self = false ) {

	$limit = VA_REVIEWS_PER_PAGE;

	$page = max( 1, get_query_var( 'paged' ) );

	$offset = $limit * ( $page - 1 );

	$reviews = va_get_reviews( array(
			'user_id' => $user_id,
			'status' => ( $self === true ? '' : 'approve' ),
			'number' => $limit,
			'offset' => $offset,
		) );

	return $reviews;
}

function va_get_dashboard_claimed_listings( $user_id, $self = false ) {
	global $va_options;
	
	$args = array(
		'post_type' => VA_LISTING_PTYPE,
		'meta_key' => 'claimee',
		'meta_value' => $user_id,
		'paged' => get_query_var( 'paged' ),
		'posts_per_page' => $va_options->listings_per_page
		
	);

	if ( $self ) {
		$args['post_status'] = array( 'publish', 'pending', 'pending-claimed', 'expired' );
	} else {
		return array();
	}

	$query = new WP_Query( $args );
	return $query;
}

function va_the_listing_expiration_notice( $listing_id = '' ) {
	echo va_get_listing_expiration_notice( $listing_id );
}

function va_get_listing_expiration_notice( $listing_id = '' ) {
	
	$expiration_date = va_get_listing_exipration_date( $listing_id );
	if( !$expiration_date )
		return;

	$is_expired = strtotime($expiration_date) < time();
	if( $is_expired ){
		$notice = sprintf( __('Listing Expired on: %s', APP_TD ), mysql2date( get_option('date_format'), $expiration_date ) );
	}else{
		$notice = sprintf( __('Listing Expires on: %s', APP_TD ), mysql2date( get_option('date_format'), $expiration_date ) );
	}
	
	$output = '<p class="dashboard-expiration-meta">';
	$output .= $notice;
	$output .='</p>';
	return $output;
}

function va_get_listing_exipration_date( $listing_id = '' ) {
	global $post;
	
	$listing_id = !empty( $listing_id ) ? $listing_id : get_the_ID();
	
	$duration = get_post_meta( $listing_id, 'listing_duration', true );

	if( empty( $duration ) ){
		return 0;
	}
	
	return va_get_expiration_date( $post->post_date, $duration );
}

function va_get_featured_exipration_date( $addon, $listing_id = '' ) {
	
	$listing_id = !empty( $listing_id ) ? $listing_id : get_the_ID();

	$start_date = get_post_meta( $listing_id, $addon . '_start_date', true);
	
	$duration = get_post_meta( $listing_id, $addon.'_duration', true );

	if( !$start_date || !$duration ){
		return __( 'Never', APP_TD );
	}
	
	return va_get_expiration_date( $start_date, $duration );
}

function va_get_expiration_date( $start_date, $duration ){

	$expiration_date = date('m/d/Y', strtotime( $start_date .' + ' . $duration . 'days' ) );
	
	return $expiration_date;

}

function va_get_dashboard_reviews_count( $user_id, $self = false ) {
	return max( 1, ceil( va_get_user_reviews_count( $user_id, ( $self === true ? '' : 'approve' ) )  / VA_REVIEWS_PER_PAGE ) );
}

function va_the_author_listings_link( $user_id = '' ) {

	echo va_get_the_author_listings_link( $user_id );
}

function va_get_the_author_listings_link( $user_id = '' ) {
	$nicename = get_the_author_meta( 'user_nicename', $user_id );
	$display_name = get_the_author_meta( 'display_name', $user_id );

	$url = get_bloginfo( 'wpurl' );
	$permalink = '/' . va_get_dashboard_permalink_setting('dashboard') . '/' . va_get_dashboard_permalink_setting('listings');

	if ( get_option('permalink_structure') != '' ) { 
		$url .=  $permalink . '/' . $nicename . '/';
	} else {
		$url .= '?dashboard='.va_get_dashboard_permalink_setting('listings').'&dashboard_author=' . $nicename . '';
	}
	
	return html_link( $url, $display_name );
}

function va_get_the_author_listings_url( $user_id = '' , $self = false ) {
	$nicename = get_the_author_meta( 'user_nicename', $user_id );

	$url = get_bloginfo( 'wpurl' );
	$permalink = '/' . va_get_dashboard_permalink_setting('dashboard') . '/' . va_get_dashboard_permalink_setting('listings');

	if ( $self ) {
		if ( get_option('permalink_structure') != '' ) { 
			$url .=  $permalink . '/';
		} else {
			$url .= '?dashboard='.va_get_dashboard_permalink_setting('listings').'&dashboard_author=self';
		}
	} else {
		if ( get_option('permalink_structure') != '' ) { 
			$url .=  $permalink . '/' . $nicename . '/';
		} else {
			$url .= '?dashboard='.va_get_dashboard_permalink_setting('listings').'&dashboard_author='.$nicename;
		}
	}

	return $url;
}

function va_get_claimed_listings_url() {
	$url = get_bloginfo( 'wpurl' );
	$permalink = '/' . va_get_dashboard_permalink_setting('dashboard') . '/' . va_get_dashboard_permalink_setting('claimed-listings') . '/';
	
	if ( get_option('permalink_structure') != '' ) { 
		$url .=  $permalink;
	} else {
		$url .= '?dashboard='.va_get_dashboard_permalink_setting('claimed-listings').'&dashboard_author=self';
	}
	
	return $url;
}

function va_the_author_reviews_link( $user_id = '' ) {
	echo va_get_the_author_reviews_link( $user_id );
}

function va_get_the_author_reviews_link( $user_id = '' ) {
	$nicename = get_the_author_meta( 'user_nicename', $user_id );
	$display_name = get_the_author_meta( 'display_name', $user_id );

	$url = get_bloginfo( 'wpurl' );
	$permalink = '/' . va_get_dashboard_permalink_setting('dashboard') . '/' . va_get_dashboard_permalink_setting('reviews');

	if ( get_option('permalink_structure') != '' ) { 
		$url .=  $permalink . '/' . $nicename . '/';
	} else {
		$url .= '?dashboard='.va_get_dashboard_permalink_setting('reviews').'&dashboard_author='.$nicename;
	}

	return html_link( $url, $display_name );
}

function va_get_edit_review_url( $review_id ) {
	$url = get_bloginfo( 'wpurl' );
	$permalink = '/' . va_get_dashboard_permalink_setting('dashboard') . '/' . va_get_dashboard_permalink_setting('reviews');

	if ( get_option('permalink_structure') != '' ) { 
		$url .=  $permalink . '/#review-' . $review_id;
	} else {
		$url .= '?dashboard='.va_get_dashboard_permalink_setting('reviews').'&dashboard_author=self#review-' . $review_id;
	}	

	return $url;
}

function va_get_the_author_reviews_url( $user_id = '' , $self = false ) {
	$nicename = get_the_author_meta( 'user_nicename', $user_id );
	
	$url = get_bloginfo( 'wpurl' );
	$permalink = '/' . va_get_dashboard_permalink_setting('dashboard') . '/' . va_get_dashboard_permalink_setting('reviews');

	if ( $self ) {
		if ( get_option('permalink_structure') != '' ) { 
			$url .=  $permalink . '/';
		} else {
			$url .= '?dashboard='.va_get_dashboard_permalink_setting('reviews').'&dashboard_author=self';
		}
	} else {
		if ( get_option('permalink_structure') != '' ) { 
			$url .=  $permalink . '/' . $nicename . '/';
		} else {
			$url .= '?dashboard='.va_get_dashboard_permalink_setting('reviews').'&dashboard_author='.$nicename;
		}
	}

	return $url;
}

function va_get_the_author_faves_url( $user_id = '' , $self = false ) {
	$nicename = get_the_author_meta( 'user_nicename', $user_id );

	$url = get_bloginfo( 'wpurl' );
	$permalink = '/' . va_get_dashboard_permalink_setting('dashboard') . '/' . va_get_dashboard_permalink_setting('favorites');

	if ( $self ) {
		if ( get_option('permalink_structure') != '' ) {
			$url .=  $permalink . '/';
		} else {
			$url .= '?dashboard='.va_get_dashboard_permalink_setting('favorites').'&dashboard_author=self';
		}
	} else {
		if ( get_option('permalink_structure') != '' ) {
			$url .=  $permalink . '/' . $nicename . '/';
		} else {
			$url .= '?dashboard='.va_get_dashboard_permalink_setting('favorites').'&dashboard_author='.$nicename;
		}
	}

	return $url;
}
