<?php

$GLOBALS['va_options'] = new scbOptions( 'va_options', false, array(
	'geo_region' => 'US',
	'geo_language' => 'en',
	'geo_unit' => 'mi',
	'currency_code' => 'USD',

	'color' => 'blue',

	// Listings
	'listing_price' => 0,
	'listing_charge' => 'no',
	'listing_duration' => 30,
	'moderate_listings' => 'no',
	'moderate_claimed_listings' => 'yes',

	'listings_per_page' => 10,
	'featured_per_page' => 5,
	'featured_sort' => 'newest',


	// Search
	'default_geo_search_sort' => 'distance',
	'default_search_sort' => 'rating',
	'default_radius' => '',
	
	// Featured Listings
	'addons' => array(
		VA_ITEM_FEATURED_HOME => array(
			'enabled' => 'yes',
			'price' => 0,
			'duration' => 30,
		),

		VA_ITEM_FEATURED_CAT => array(
			'enabled' => 'yes',
			'price' => 0,
			'duration' => 30,
		),
	),

	// Category Options
	'categories_menu' => array(
		'count' => 0,
		'depth' => 3,
		'sub_num' => 3,
		'hide_empty' => false,
	),
	'categories_dir' => array(
		'count' => 0,
		'depth' => 3,
		'sub_num' => 3,
		'hide_empty' => false,
	),

	// Permalinks
	'listing_permalink' 		 	=> 'listings',
	'edit_listing_permalink'  	 	=> 'edit',
	'claim_listing_permalink' 	 	=> 'claim',
	'purchase_listing_permalink' 	=> 'purchase',
	'listing_cat_permalink' 	 	=> 'category',
	'listing_tag_permalink' 	 	=> 'tag',
	'dashboard_permalink' 	 	 	=> 'dashboard',
	'dashboard_listings_permalink'	=> 'listings',
	'dashboard_claimed_permalink'	=> 'claimed-listings',
	'dashboard_reviews_permalink'	=> 'reviews',
	'dashboard_faves_permalink'  	=> 'favorites',
	
	// Gateways
	'gateways' => array(
		'enabled' => array()
	),

	// Integration
	'listing_sharethis' => 0,
	'blog_post_sharethis' => 0,
) );

