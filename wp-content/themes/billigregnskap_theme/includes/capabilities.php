<?php

add_action( 'appthemes_first_run', 'va_add_caps' );


function va_add_caps() {
	va_manage_caps( 'add_cap' );
}

function va_remove_caps() {
	va_manage_caps( 'remove_cap' );
}

function va_manage_caps( $operation ) {
	global $wp_roles;

	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	foreach ( $wp_roles->roles as $role => $details ) {
		foreach ( va_get_custom_caps( $role ) as $cap ) {
			$wp_roles->$operation( $role, $cap );
		}
	}
}

function va_get_custom_caps( $role ) {
	$caps = array(
		'edit_listings',
		'edit_published_listings',
		'delete_listings',
	);

	if ( in_array( $role, array( 'editor', 'administrator' ) ) ) {
		$caps = array_merge( $caps, array(
			'edit_others_listings',
			'publish_listings',
			'delete_published_listings',
			'delete_others_listings'
		) );
	}

	return $caps;
}

