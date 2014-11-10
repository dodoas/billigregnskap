<?php

// Replace any children the "Categories" menu item might have with the category dropdown
add_filter( 'wp_nav_menu_objects', 'va_disable_cat_children_menu', 10, 2 );
add_filter( 'walker_nav_menu_start_el', 'va_insert_cat_dropdown_menu', 10, 4 );


function va_disable_cat_children_menu( $items, $args ) {
	foreach ( $items as $key => $item ) {
		if ( $item->object_id == VA_Listing_Categories::get_id() ) {
			$item->current_item_ancestor = false;
			$item->current_item_parent = false;
			$menu_id = $item->ID;
		}
	}

	if ( isset( $menu_id ) ) {
		foreach ( $items as $key => $item )
			if ( $item->menu_item_parent == $menu_id )
				unset( $items[$key] );
	}

	return $items;
}

function va_insert_cat_dropdown_menu( $item_output, $item, $depth, $args ) {
	if ( $item->object_id == VA_Listing_Categories::get_id() ) {
		$item_output .= '<div class="adv_categories" id="adv_categories">' . va_cat_menu_drop_down( 'menu' ) . '</div>';
	}
	return $item_output;
}

function va_cat_menu_drop_down( $location = 'menu' ) {
	global $va_options;

	$key = 'categories_' . $location;
	$options = $va_options->$key;

	$args['menu_cols'] = ( $location == 'menu' ? 3 : 2 );
	$args['menu_depth'] = $options['depth'];
	$args['menu_sub_num'] = $options['sub_num'];
	$args['cat_parent_count'] = $options['count'];
	$args['cat_child_count'] = $options['count'];
	$args['cat_hide_empty'] = $options['hide_empty'];
	$args['cat_nocatstext'] = true;
	$args['cat_order'] = 'ASC';
	$args['taxonomy'] = VA_LISTING_CATEGORY;

	$terms_args['pad_counts'] = false;
	$terms_args['app_pad_counts'] = true;

	return appthemes_categories_list($args, $terms_args);
}

/*
 * Returns TRUE if categories can be modified on existing posts, FALSE otherwise
 */
function va_categories_locked() {
	$disabled = ! current_user_can('administrator');
	return (bool) apply_filters( 'va_categories_locked' , $disabled );
}

