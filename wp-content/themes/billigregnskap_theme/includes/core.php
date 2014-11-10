<?php

function va_register_post_types() {
	global $va_options;

	$labels = array(
		'name' => __( 'Listings', APP_TD ),
		'singular_name' => __( 'Listing', APP_TD ),
		'add_new' => __( 'Add New', APP_TD ),
		'add_new_item' => __( 'Add New Listing', APP_TD ),
		'edit_item' => __( 'Edit Listing', APP_TD ),
		'new_item' => __( 'New Listing', APP_TD ),
		'view_item' => __( 'View Listing', APP_TD ),
		'search_items' => __( 'Search Listings', APP_TD ),
		'not_found' => __( 'No listings found', APP_TD ),
		'not_found_in_trash' => __( 'No listings found in Trash', APP_TD ),
		'parent_item_colon' => __( 'Parent Listing:', APP_TD ),
		'menu_name' => __( 'Listings', APP_TD ),
	);

	$args = array(
		'labels' => $labels,
		'hierarchical' => false,

		'supports' => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions' ),

		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 6,

		'show_in_nav_menus' => false,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'has_archive' => true,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => array( 'slug' => $va_options->listing_permalink, 'with_front' => false ),
		'capability_type' => 'listing',
		'map_meta_cap' => true
	);

	if ( current_user_can( 'manage_options' ) )
		$args['supports'][] = 'custom-fields';

	register_post_type( VA_LISTING_PTYPE, $args );

	$status_args = array(
		'exclude_from_search' => false,
		'show_in_admin_all_list' => true,
		'show_in_admin_status_list' => true,
		'label_count' => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', APP_TD ),
	);
	register_post_status( 'expired' , $status_args);

	$status_args = array(
		'exclude_from_search' => false,
		'show_in_admin_all_list' => true,
		'show_in_admin_status_list' => true,
		'label_count' => _n_noop( 'Pending Claimed <span class="count">(%s)</span>', 'Pending Claimed <span class="count">(%s)</span>', APP_TD ),
	);
	register_post_status( 'pending-claimed' , $status_args);

	va_reorder_post_statuses();
}

function va_reorder_post_statuses() {
	global $wp_post_statuses;

	$new_statuses = array();
	$pending_claimed = $wp_post_statuses['pending-claimed'];
	unset($wp_post_statuses['pending-claimed']);
	foreach($wp_post_statuses as $wp_post_status_name=>$wp_post_status) {
		$new_statuses[$wp_post_status_name] = $wp_post_status;

		if( 'pending' == $wp_post_status_name )
			$new_statuses['pending-claimed'] = $pending_claimed;
	}

	$wp_post_statuses = $new_statuses;
}

function va_register_taxonomies() {
	global $va_options;

	$labels = array(
		'name' => __( 'Listing Categories', APP_TD ),
		'singular_name' => __( 'Listing Category', APP_TD ),
		'search_items' => __( 'Search Listing Categories', APP_TD ),
		'all_items' => __( 'All Categories', APP_TD ),
		'parent_item' => __( 'Parent Listing Category', APP_TD ),
		'parent_item_colon' => __( 'Parent Listing Category:', APP_TD ),
		'edit_item' => __( 'Edit Listing Category', APP_TD ),
		'update_item' => __( 'Update Listing Category', APP_TD ),
		'add_new_item' => __( 'Add New Listing Category', APP_TD ),
		'new_item_name' => __( 'New Listing Category Name', APP_TD ),
		'add_or_remove_items' => __( 'Add or remove listing categories', APP_TD ),
		'menu_name' => __( 'Categories', APP_TD ),
	);

	$args = array(
		'labels' => $labels,
		'public' => true,

		'show_ui' => true,
		'show_admin_column' => true,

		'show_in_nav_menus' => true,
		'show_tagcloud' => false,
		'hierarchical' => true,

		'query_var' => true,
		'rewrite' => array( 'slug' => $va_options->listing_permalink . '/' . $va_options->listing_cat_permalink, 'with_front' => false ),
	);

	register_taxonomy( VA_LISTING_CATEGORY, VA_LISTING_PTYPE, $args );

	$labels = array(
		'name' => __( 'Listing Tags', APP_TD ),
		'singular_name' => __( 'Listing Tag', APP_TD ),
		'search_items' => __( 'Search Listing Tags', APP_TD ),
		'popular_items' => __( 'Popular Listing Tags', APP_TD ),
		'all_items' => __( 'All Listing Tags', APP_TD ),
		'parent_item' => __( 'Parent Listing Tag', APP_TD ),
		'parent_item_colon' => __( 'Parent Listing Tag:', APP_TD ),
		'edit_item' => __( 'Edit Listing Tag', APP_TD ),
		'update_item' => __( 'Update Listing Tag', APP_TD ),
		'add_new_item' => __( 'Add New Listing Tag', APP_TD ),
		'new_item_name' => __( 'New Listing Tag Name', APP_TD ),
		'separate_items_with_commas' => __( 'Separate listing tags with commas', APP_TD ),
		'add_or_remove_items' => __( 'Add or remove listing tags', APP_TD ),
		'choose_from_most_used' => __( 'Choose from the most used listing tags', APP_TD ),
		'menu_name' => __( 'Tags', APP_TD ),
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_ui' => true,
		'show_tagcloud' => true,
		'hierarchical' => false,

		'query_var' => true,
		'rewrite' => array( 'slug' => $va_options->listing_permalink . '/' . $va_options->listing_tag_permalink, 'with_front' => false ),
	);

	register_taxonomy( VA_LISTING_TAG, VA_LISTING_PTYPE, $args );
}


function va_add_style() {
	global $va_options;

	if ( is_child_theme() )
		return;

	wp_enqueue_style(
		'at-color',
		get_template_directory_uri() . "/styles/$va_options->color.css",
		array(),
		VA_VERSION
	);
}

// Scripts loaded on all pages
function va_add_scripts() {
	wp_enqueue_script(
		'html5-shiv',
		get_template_directory_uri() . '/scripts/html5.js',
		array(),
		3
	);

	wp_enqueue_script(
		'va-scripts',
		get_template_directory_uri() . '/scripts/scripts.js',
		array( 'jquery' ),
		VA_VERSION,
		true
	);

	wp_enqueue_script(
		'va-selectnav',
		get_template_directory_uri() . '/scripts/jquery.tinynav.js',
		array( 'jquery' ),
		1.1
	);	

	wp_localize_script( 'va-scripts', 'Vantage', array(
		'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
		'current_url'		=> scbUtil::get_current_url()
	) );
}

function va_setup_theme() {
	// Menus
	register_nav_menu( 'header', __( 'Header Menu', APP_TD ) );
	register_nav_menu( 'footer', __( 'Footer Menu', APP_TD ) );

	// Sidebars
	va_register_sidebar( 'main', __( 'Main Sidebar', APP_TD ), __( 'The sidebar appearing on all pages except search, pages, and the single listing page', APP_TD ) );
	va_register_sidebar( 'search-listing', __( 'Listing Search Sidebar', APP_TD ), __( 'The sidebar for the Listing search page', APP_TD ) );
	va_register_sidebar( 'single-listing', __( 'Single Listing Sidebar', APP_TD ), __( 'The sidebar for single Listing page', APP_TD ) );
	va_register_sidebar( 'page', __( 'Page Sidebar', APP_TD ), __( 'The sidebar for pages', APP_TD ) );

	va_register_sidebar( 'va-header', __( 'Header', APP_TD ), __( 'An optional widget area for your site header', APP_TD ) );
	va_register_sidebar( 'va-footer', __( 'Footer', APP_TD ), __( 'An optional widget area for your site footer', APP_TD ) );

	va_register_sidebar( 'va-listings-ad', __( 'Listings Pages Ad', APP_TD ), __( 'An optional widget area for your ads on listings pages', APP_TD ) );

	// Misc
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	
	$defaults = array(
		'wp-head-callback'       => 'va_custom_background_cb',
	);
	add_theme_support( 'custom-background', $defaults );
	
	// Our theme handles the customer header in va_display_logo()
	$defaults = array(
		'default-image'          => '%s/images/vantage-logo.png',
		'width'                  => 400,
		'height'                 => 70,
		'flex-height'            => true,
		'flex-width'             => true,
		'default-text-color'     => '444444',
		'header-text'            => true,
		'uploads'                => true,
		'wp-head-callback'       => '',
		'admin-head-callback'    => '',
		'admin-preview-callback' => '',
	);
	add_theme_support( 'custom-header', $defaults );	
}


function va_disable_hierarchy_in_footer( $items, $args ) {
	if ( 'footer' != $args->theme_location )
		return $items;

	foreach ( $items as &$item ) {
		if ( $item->menu_item_parent > 0 )
			$item = false;
	}

	return array_filter( $items );
}


function va_user_contact_methods( $methods ) {
	return array(
		'twitter' => __( 'Twitter', APP_TD ),
		'facebook' => __( 'Facebook', APP_TD ),
	);
}

function va_user_update_profile( $errors, $update, $user ) {
	if ( !$update )
		return;

	if ( isset( $_POST['email_public'] ) )
		add_user_meta( $user->ID, 'email_public', true, true );
	else
		delete_user_meta( $user->ID, 'email_public' );
}


function va_redirect_to_front_page() {
	if (!isset($_REQUEST['redirect_to'])) {
		wp_redirect( home_url() );
		exit();
	}
}

// Social Connect plugin compatibility
function social_connect_grab_login_redirect() {
	if( !empty($_POST['action']) && 'social_connect' == $_POST['action'] ) {
		return false;
	}
}

function va_login_logo_url() {
    return get_bloginfo( 'url' );
}

function va_login_logo_url_title() {
    return get_bloginfo( 'description' );
}


function va_login_styling() {
	$header_image = get_header_image();

	if ( ! empty( $header_image ) ) {
?>
	<style>
	body.login div#login h1 a {
	    background-image: url('<?php header_image(); ?>');
	    width: <?php echo HEADER_IMAGE_WIDTH; ?>;
	    height: <?php echo HEADER_IMAGE_HEIGHT; ?>;
	}
	</style>
<?php
	}
	wp_enqueue_style(
		'va-login',
		get_template_directory_uri() . '/styles/login.css',
		false,
		VA_VERSION
	);
}

function va_body_class( $classes ) {
	if ( !is_user_logged_in() )
		$classes[] = 'not-logged-in';

	return $classes;
}

function va_custom_background_cb() {
        $background = get_background_image();
        $color = get_background_color();
        if ( ! $background && ! $color )
                return;
        $style = $color ? "background-color: #$color;" : '';
        if ( $background ) {
                $image = " background-image: url('$background');";
                $repeat = get_theme_mod( 'background_repeat', 'repeat' );
                if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) )
                        $repeat = 'repeat';
                $repeat = " background-repeat: $repeat;";
                $position = get_theme_mod( 'background_position_x', 'left' );
                if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) )
                        $position = 'left';
                $position = " background-position: top $position;";
                $attachment = get_theme_mod( 'background_attachment', 'scroll' );
                if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) )
                        $attachment = 'scroll';
                $attachment = " background-attachment: $attachment;";
                $style .= $image . $repeat . $position . $attachment;
        } else if(!$background && $color){
        	$style .= " background-image: none; ";
        }
?>
<style type="text/css">
body.custom-background { <?php echo trim( $style ); ?> }
</style>
<?php
}

function _va_get_color_choices(){
	return array(
		'blue' => __( 'Blue', 'appthemes' ),
		'red' => __( 'Red', 'appthemes' ),
		'orange' => __( 'Orange', 'appthemes' ),
		'green' => __( 'Green', 'appthemes' ),
		'navy' => __( 'Navy Blue', 'appthemes' ),
		'purple' => __( 'Purple', 'appthemes' ),
		'pink' => __( 'Pink', 'appthemes' ),
		'gray' => __( 'Dark Gray', 'appthemes' ),
	);
}

function va_after_admin_bar_login_form() {
	ob_start();

	do_action('va_after_admin_bar_login_form');

	return ob_get_clean();
}

// Hook in social connect to admin bar login form
if ( function_exists('sc_render_login_form_social_connect') ) {
	add_action( 'va_after_admin_bar_login_form', 'sc_render_login_form_social_connect' );
}

add_action( 'appthemes_after_import_upload_form', 'va_disable_emails_on_import_option' );
function va_disable_emails_on_import_option() {
	?>
	<p><label><?php _e('Disable sending "Pending Listing.." notification emails for this import?:', APP_TD) ?> <input type="checkbox" name="disable_import_emails" value="1" /></label></p>
	<?php
}

add_action( 'appthemes_after_import_upload_form', 'va_geocode_listings_on_import_option' );
function va_geocode_listings_on_import_option() {
	?>
	<p><label><?php _e( 'Geocode imported listings?:' , APP_TD ); ?> <input type="checkbox" name="geocode_imported" value="1" /></label>
	<br />
	<span class="description"><?php _e( '(Note: Maximum of 2500 geocode requests per day are allowed)' , APP_TD ); ?></span></p>
	
	<?php
}

add_filter( 'app_importer_import_row_after' , 'va_set_import_meta_defaults' );
function va_set_import_meta_defaults( $listing ) {
	return va_set_meta_defaults( $listing );
}

add_action( 'app_importer_import_row_after', 'va_geocode_listings_on_import', 10, 2 );
function va_geocode_listings_on_import( $listing_id, $row ) {
	if ( empty( $_POST['geocode_imported'] ) ) return;
	if ( !empty( $row['lat'] ) && !empty( $row['lng'] ) ) return;
	va_geocode_address($listing_id);
}

add_action( 'wp_mail', 'va_disable_emails_on_import', 1 );
function va_disable_emails_on_import($args) {
	
	if ( !empty( $_POST['disable_import_emails'] ) ) {
    	$args['to'] = 'example@example.com';
	}
    	
	return $args;
}

function va_calc_radius_slider_controls($current_radius) {
	$major_steps = array( 250 => 25, 100 => 10, 50 => 5, 25 => 5, 15 => 1, 10 => 1, 3 => .5, 1 => .1, .05 => .05);
	$major_steps = apply_filters('va_calc_radius_slider_controls_steps', $major_steps);
	
	krsort($major_steps);
			
	$last_major_step = '';
	foreach( $major_steps as $major_step => $step ) {
		if($current_radius >= $major_step) {
			$current_radius = va_round_to_nearest($current_radius, $step );
			$min = $step;
			$max = !empty($last_major_step) ? $last_major_step * 1.5 : ( $current_radius * 1.5 );
			$max = va_round_to_nearest($max, $step );
			break;
		}
		$last_major_step = $major_step;
	}
	return compact('current_radius', 'min', 'max', 'step');
}

add_filter('appthemes_geo_query', 'va_geo_query');
function va_geo_query($geo_query) {
	$radius_calc = va_calc_radius_slider_controls($geo_query['rad']);
	$geo_query['rad'] = $radius_calc['current_radius'];
	return $geo_query;
}
