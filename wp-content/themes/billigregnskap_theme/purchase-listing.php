<?php

	global $va_options;

	$listing = get_queried_object();

	if ( _va_needs_publish( $listing ) || _va_is_claimable( $listing->ID ) || !$plan = _va_get_last_plan_info( $listing->ID ) ) {

		$plans_data = va_get_listing_available_plans($listing);

		appthemes_load_template( 'purchase-listing-new.php', array(
			'listing' => $listing,
			'plans' => $plans_data
		));

	} else {

		appthemes_load_template( 'purchase-listing-existing.php', array(
			'listing' => $listing,
			'plan' => $plan
		));

	}

?>