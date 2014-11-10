<?php

// Installation procedures
add_action( 'appthemes_first_run', 'va_init_settings' );
add_action( 'appthemes_first_run', 'va_init_content' );
add_action( 'appthemes_first_run', 'va_setup_postmeta' );
add_action( 'appthemes_first_run', 'va_init_menu' );

add_action( 'load-post-new.php', 'va_disable_admin_listing_creation' );
add_action( 'load-post.php', 'va_disable_admin_listing_editing' );

// Importer
add_action( 'wp_loaded', 'va_csv_importer' );

// Various tweaks
add_action( 'admin_menu', 'va_admin_menu_tweak' );
add_action( 'admin_print_styles', 'va_admin_styles' );

// Admin Scripts
add_action( 'admin_enqueue_scripts', 'va_add_admin_scripts', 10 );

function va_csv_importer() {
	$fields = array(
		'title'       => 'post_title',
		'description' => 'post_content',
		'author'      => 'post_author',
		'date'        => 'post_date',
		'slug'        => 'post_name',
		'status'      => 'post_status'
	);

	$args = array(
		'taxonomies'    => array( VA_LISTING_CATEGORY, VA_LISTING_TAG ),

		'custom_fields' => array(
			'address' => array(),
			'phone' => array(),
			'facebook' => array(),
			'twitter' => array(),
			'website' => array(),
			'listing_duration' => array('internal_key' => 'listing_duration', 'default' => 0),
		),

		'geodata' => true,
		'attachments' => true
	);

	$args = apply_filters( 'va_csv_importer_args', $args );

	$importer = new APP_Importer( VA_LISTING_PTYPE, $fields, $args );
}

function va_init_settings() {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', VA_Listing_Archive::get_id() );
	update_option( 'page_for_posts', VA_Blog_Archive::get_id() );

	if ( !get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}

	flush_rewrite_rules();
}

function va_setup_postmeta() {

	list( $args ) = get_theme_support( 'app-versions' );
	$previous_version = get_option( $args['option_key'] );

	if ( version_compare( $previous_version, '1.1.3', '<' ) ) {

		$listings = get_posts( array (
			'post_type'   	=> VA_LISTING_PTYPE,
			'nopaging' => true,
		) );

		foreach ( $listings  as $listing ) {
			va_set_meta_defaults( $listing );
		}
	}
}

function va_init_content() {
	// Deliberately left untranslated

	$listings = get_posts( array(
		'post_type' => VA_LISTING_PTYPE,
		'posts_per_page' => 1
	) );

	if ( empty( $listings ) ) {

		$cat = appthemes_maybe_insert_term( 'Software', VA_LISTING_CATEGORY );
	
		$listing_id = wp_insert_post( array(
			'post_type' => VA_LISTING_PTYPE,
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'post_title' => 'AppThemes',
			'post_content' => 'AppThemes is a fast growing company that employs talent from all around the world. Our diverse team consists of highly skilled WordPress developers, designers, and enthusiasts who come together to make awesome premium themes available in over two dozen different languages.',
			'tax_input' => array(
				VA_LISTING_CATEGORY => array( $cat['term_id'] ),
				VA_LISTING_TAG => 'wordpress, themes'
			)
		) );
	
		$data = array(
			'phone' => '415-287-3474',
			'address' => '548 Market St, San Francisco, CA 94104, USA',
			'website' => 'appthemes.com',
			'twitter' => 'appthemes',
			'facebook' => 'appthemes',
			'rating_avg' => '5',
		);
	
		foreach ( $data as $key => $value )
			add_post_meta( $listing_id, $key, $value );
	
		appthemes_set_coordinates( $listing_id, '37.7899027', '-122.40078460000001' );
	
		$user_id = username_exists( 'customer' );
		if ( !$user_id ) {
			$user_id = wp_insert_user( array(
				'user_login' => 'customer',
				'display_name' => 'Satisfied Customer',
				'user_pass' => wp_generate_password()
			) );
		}
	
		$review_id = wp_insert_comment( array(
			'comment_type' => VA_REVIEWS_CTYPE,
			'comment_post_ID' => $listing_id,
			'user_id' => $user_id,
			'comment_content' => "Wow! Really powerful stuff from AppThemes. Their themes simply blow away the competition. It seems like everyone is trying to make money online and AppThemes makes it easy to do just that. After downloading and installing their themes, it's just a few button clicks before you have an amazing website - no not a website, a web application. That's what you're getting with AppThemes, really powerful web applications. All you have to take care of is getting traffic to your site. The themes from AppThemes do the rest."
		) );
	
		va_set_rating( $review_id, 5 );
	}
	
	$plans = get_posts( array(
		'post_type' => APPTHEMES_PRICE_PLAN_PTYPE,
		'posts_per_page' => 1
	) );

	if ( empty( $plans ) ) {
		
		$plan_id = wp_insert_post( array(
			'post_type' => APPTHEMES_PRICE_PLAN_PTYPE,
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'post_title' => 'Basic',
			'post_content' => '',
			'tax_input' => array(
				VA_LISTING_CATEGORY => array( $cat['term_id'] ),
			)
		) );
	
		$data = array(
			'title' => 'Basic',
			'description' => 'Get your listing out there with our Basic plan. No frills, no fuss.',
			'duration' => 30,
			'price' => 0,
		);
	
		foreach ( $data as $key => $value )
			add_post_meta( $plan_id, $key, $value );
	
	}
}

function va_init_menu() {
	if ( has_nav_menu( 'header' ) ) {
		return;
	}

	$menu_id = wp_create_nav_menu( __( 'Header', APP_TD ) );
	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	$page_ids = array(
		VA_Listing_Categories::get_id(),
		VA_Listing_Create::get_id(),
		VA_Blog_Archive::get_id(),
	);

	foreach ( $page_ids as $page_id ) {
		$page = get_post( $page_id );

		if ( !$page )
			continue;

		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-type' => 'post_type',
			'menu-item-object' => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-title' => $page->post_title,
			'menu-item-url' => get_permalink( $page ),
			'menu-item-status' => 'publish'
		) );
	}

	$locations = get_theme_mod( 'nav_menu_locations' );
	$locations['header'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

function va_admin_menu_tweak() {
	global $menu;

	// move Posts below listings. 
	$menu[7] = $menu[5];
	
	// move separator down
	$menu[5] = $menu[4];
}

function va_admin_styles() {
	appthemes_menu_sprite_css( array(
		'#toplevel_page_app-dashboard',
		'#adminmenu #menu-posts-listing',
	) );
	?>
	<style>
		.inline-edit-listing .inline-edit-group .alignleft {
			display: none;
		}
		.inline-edit-listing .inline-edit-group .alignleft.inline-edit-status,
		.inline-edit-listing .inline-edit-group .alignleft.inline-edit-claimable{
			display: block;
		}
		
		.wp-list-table th.column-claimable,
		.wp-list-table td.column-claimable {
			display: none;
		}
	</style>
	<?php
}

function va_add_admin_scripts( $hook ) {
	global $post;

	// selective load
	$pages = array ( 'edit.php', 'post.php', 'post-new.php', 'media-upload-popup' );

 	if( ! in_array( $hook, $pages ) )
		return;

	wp_register_script(
		'jquery-validate',
		get_template_directory_uri() . '/scripts/jquery.validate.min.js',
		array( 'jquery' ),
		'1.9.0',
		true
	);

	wp_enqueue_script(
		'va-admin-listing-edit',
		get_template_directory_uri() . '/includes/admin/scripts/listing-edit.js',
		array( 'jquery-validate'),
		VA_VERSION,
		true
	);

	wp_localize_script( 'va-admin-listing-edit', 'VA_admin_l18n', array(
		'user_admin' 		=> current_user_can('manage_options'),
		'listing_type'  	=> VA_LISTING_PTYPE,
		'listing_category'  => VA_LISTING_CATEGORY,
		'post_type'  		=> ( isset( $post->post_type ) ? $post->post_type : '' ),
	) );

}

function va_disable_admin_listing_creation() {
	if( current_user_can( 'edit_others_listings') )
		return;

	if ( VA_LISTING_PTYPE != @$_GET['post_type'] )
		return;

	wp_redirect( va_get_listing_create_url() );
	exit;
}

function va_disable_admin_listing_editing() {

	if( current_user_can( 'edit_others_listings') )
		return;

	if ( 'edit' != @$_GET['action'] )
		return;

	$post_id = (int) @$_GET['post'];

	if ( VA_LISTING_PTYPE != get_post_type( $post_id ) )
		return;

	wp_redirect( va_get_listing_edit_url( $post_id ) );
	exit;
}
