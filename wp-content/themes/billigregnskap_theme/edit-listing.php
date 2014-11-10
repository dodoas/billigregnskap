<?php
$listing = va_get_existing_listing_to_edit();

appthemes_load_template( 'form-listing.php', array(
	'title' => sprintf( __( 'Edit %s', APP_TD ), html_link( get_permalink( $listing ), get_the_title( $listing->ID ) ) ),
	'listing' => $listing,
	'action' => __( 'Save Changes', APP_TD ),
	'form_action' => va_get_listing_edit_url( $listing->ID ),
) );

