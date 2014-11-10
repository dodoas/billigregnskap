<?php

add_action( 'init', 'va_forms_register_post_type', 11 );
add_action( 'wp_ajax_app-render-form', 'va_forms_ajax_render_form' );
add_action( 'edited_term_taxonomy', 'va_exclude_forms_from_counter', 10, 2 );


function va_forms_register_post_type() {
	register_taxonomy_for_object_type( VA_LISTING_CATEGORY, APP_FORMS_PTYPE );
}

function va_forms_ajax_render_form() {
	if ( ! $cat = $_POST['listing_category'] )
		die;

	va_render_form( $cat );
	die;
}

function va_render_form( $cat, $listing_id = 0 ) {
	foreach ( va_get_fields_for_cat( $cat ) as $field ) {
		$html = html( 'div class="form-field"', scbForms::input_from_meta( $field, $listing_id ) );
		echo apply_filters( 'va_render_form_field', $html, $field, $listing_id, $cat );
	}
}

function va_get_fields_for_cat( $cat ) {
	$form = get_posts(
		array(
			'fields' => 'ids',
			'post_type' => APP_FORMS_PTYPE,
			'tax_query' => array(
				array(
					'taxonomy' => VA_LISTING_CATEGORY,
					'terms' => $cat,
					'field' => 'term_id',
					'include_children' => false
				)
			),
			'post_status' => 'publish',
			'numberposts' => 1
		)
	);

	if ( empty( $form ) )
		return array();

	return APP_Form_Builder::get_fields( $form[0] );
}

function va_exclude_forms_from_counter( $term, $taxonomy ) {
	global $wpdb;
	if ( is_object( $taxonomy ) && $taxonomy->name == VA_LISTING_CATEGORY ) {
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_status = 'publish' AND post_type = %s AND term_taxonomy_id = %d", VA_LISTING_PTYPE, $term ) );
		$wpdb->update( $wpdb->term_taxonomy, array( 'count' => $count ), array( 'term_taxonomy_id' => $term ) );
	}
}

