<?php

add_action( 'pending_to_publish', 'va_update_listing_start_date' );
add_action( 'draft_to_publish', 'va_update_listing_start_date' );
add_action( 'pending-claimed_to_publish', 'va_update_listing_start_date' );

add_action( 'init', 'va_schedule_listing_prune' );
add_action( 'va_prune_expired_listings', 'va_prune_expired_listings' );

add_filter( 'posts_clauses', 'va_expired_listing_sql', 10, 2 );

function va_maybe_publish_listing( $listing_id ) {
	global $va_options;

	if ( $va_options->moderate_listings ) {
		va_update_post_status( $listing_id, 'pending' );
	}
	else {
		va_update_post_status( $listing_id, 'publish' );
	}

}

function va_update_listing_start_date( $post ) {
	global $va_options;
	if ( $post->post_type == VA_LISTING_PTYPE ) {
		wp_update_post( array(
			"ID" => $post->ID,
			"post_date" => current_time( 'mysql' )
		) );

	}
}

function va_schedule_listing_prune() {
	if ( !wp_next_scheduled( 'va_prune_expired_listings' ) )
		wp_schedule_event( time(), 'hourly', 'va_prune_expired_listings' );
}

function va_prune_expired_listings() {

	$expired_posts = new WP_Query( array(
		'post_type' => VA_LISTING_PTYPE,
		'expired_listings' => true,
		'nopaging' => true,
	) );

	foreach ( $expired_posts->posts as $post ) {
		va_update_post_status( $post->ID, 'expired' );
	}
}

function va_expired_listing_sql( $clauses, $wp_query ) {
	global $wpdb;

	if ( $wp_query->get( 'expired_listings' ) ) {
		$clauses['join'] .= " INNER JOIN " . $wpdb->postmeta ." AS exp1 ON (" . $wpdb->posts .".ID = exp1.post_id)";

		$clauses['where'] .= " AND ( exp1.meta_key = 'listing_duration' AND DATE_ADD(post_date, INTERVAL exp1.meta_value DAY) < '" . current_time( 'mysql' ) . "' AND exp1.meta_value > 0 )";
	}

	return $clauses;
}