<?php
/**
 * Template Name: Create Listing
 */

the_post();

appthemes_load_template( 'form-listing.php', array(
	'title' => get_the_title(),
	'listing' => va_get_default_listing_to_edit(),
	'action' => __( 'Next Step', APP_TD ),
	'form_action' => va_get_listing_create_url(),
	
) );

