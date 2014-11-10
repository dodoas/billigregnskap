<?php

add_action( 'init', 'va_schedule_featured_prune' );
add_action( 'va_prune_expired_featured', 'va_prune_expired_featured' );
add_filter( 'posts_clauses', 'va_expired_featured_sql', 10, 2 );
add_filter( 'va_featured_listings_args', 'va_sort_featured_listings' );


function va_is_listing_featured( $post_id, $addon = '' ) {

	if(empty($addon)) {
		foreach( array( VA_ITEM_FEATURED_HOME, VA_ITEM_FEATURED_CAT ) as $addon ){
			$featured = get_post_meta( $post_id, $addon, true );
			if(!empty($featured)) return true;
		}
	} else {
		$featured = get_post_meta( $post_id, $addon, true );
		if(!empty($featured)) return true;
	}
	
	return false;
}

function va_add_featured( $post_id, $addon, $duration ){

	update_post_meta( $post_id, $addon , true);
	update_post_meta( $post_id, $addon .'_start_date', current_time( 'mysql' ));
	update_post_meta( $post_id, $addon .'_duration', $duration);

}

function va_remove_featured( $post_id, $addon ){

	delete_post_meta( $post_id, $addon );
	delete_post_meta( $post_id, $addon .'_start_date' );
	delete_post_meta( $post_id, $addon .'_duration' );

}

function va_schedule_featured_prune() {
	if ( !wp_next_scheduled( 'va_prune_expired_featured' ) )
		wp_schedule_event( time(), 'hourly', 'va_prune_expired_featured' );
}

function va_prune_expired_featured() {

	foreach( array( VA_ITEM_FEATURED_HOME, VA_ITEM_FEATURED_CAT ) as $addon ){

		$expired_posts = new WP_Query( array(
			'post_type' => VA_LISTING_PTYPE,
			'expired' => $addon,
			'nopaging' => true,
		) );
	
		foreach ( $expired_posts->posts as $post )
			va_remove_featured( $post->ID, $addon );

	}
	
}

function va_expired_featured_sql( $clauses, $wp_query ) {
	global $wpdb;

	switch( $wp_query->get( 'expired' ) ){

		case VA_ITEM_FEATURED_HOME:
			$clauses['join'] .= _va_get_expired_sql_join( VA_ITEM_FEATURED_HOME );
			$clauses['where'] = _va_get_expired_sql_where( VA_ITEM_FEATURED_HOME );
			break;

		case VA_ITEM_FEATURED_CAT:
			$clauses['join'] .= _va_get_expired_sql_join( VA_ITEM_FEATURED_CAT );
			$clauses['where'] = _va_get_expired_sql_where( VA_ITEM_FEATURED_CAT );
			break;

	}

	return $clauses;
}

function _va_get_expired_sql_join( $addon ){
	global $wpdb;

	$output = '';
	$output .= " INNER JOIN " . $wpdb->postmeta ." AS duration ON (" . $wpdb->posts .".ID = duration.post_id)";
	$output .= " INNER JOIN " . $wpdb->postmeta ." AS start ON (" . $wpdb->posts .".ID = start.post_id)";

	return $output;

}

function _va_get_expired_sql_where( $addon ){

	$where = 'AND (';
		$where .= 'duration.meta_key = \'' . $addon . '_duration\' AND ';
		$where .= 'start.meta_key = \'' . $addon . '_start_date\'';
		$where .= ' AND ';
		$where .= ' DATE_ADD( start.meta_value, INTERVAL duration.meta_value DAY ) < \'' . current_time( 'mysql' ) . '\'';
		$where .= ' AND duration.meta_value > 0 ';
	$where .= ") ";

	return $where;
	
}

function va_sort_featured_listings( $args ){

	global $va_options;
	
	switch( $va_options->featured_sort ){

		case 'oldest':
			$args['orderby'] = 'date';
			$args['order'] = 'ASC';
			break;
		case 'random':
			$args['orderby'] = 'rand';
			break;

	}

	return $args;

}

/**
 * @return WP_Query instance or null
 */
function va_get_featured_listings() {
	global $va_options;

	$args = array(
		'post_type' => VA_LISTING_PTYPE,
		'posts_per_page' => $va_options->featured_per_page,
		'paged' => get_query_var( 'page' ),
	);

	$args = apply_filters( 'va_featured_listings_args', $args );

	if ( is_tax( VA_LISTING_CATEGORY ) ) {
		$where = 'cat';
		$args['tax_query'] = array(
			array(
				'taxonomy' => VA_LISTING_CATEGORY,
				'terms' => array( get_queried_object_id() )
			)
		);
	} elseif ( is_front_page() ) {
		$where = 'home';
	} 
	else {
		return;
	}

	$args['meta_key'] = 'featured-' . $where;
	$args['meta_value'] = 1;

	$query = new WP_Query( $args );

	if ( !$query->have_posts() )
		return;

	return $query;
}

function va_any_featured_addon_enabled(){

	global $va_options;

	$addons = array( VA_ITEM_FEATURED_HOME, VA_ITEM_FEATURED_CAT );
	foreach( $addons as $addon ){
		if( $va_options->addons[$addon]['enabled'] == 'yes' )
			return true;
	}

	return false;
}

