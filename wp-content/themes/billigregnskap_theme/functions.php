<?php
/**
 * Theme functions file
 *
 * DO NOT MODIFY THIS FILE. Make a child theme instead: http://codex.wordpress.org/Child_Themes
 *
 * @package Vantage
 * @author AppThemes
 */

// Constants
define( 'VA_VERSION', '1.1.4-beta' );

define( 'VA_LISTING_PTYPE', 'listing' );
define( 'VA_LISTING_CATEGORY', 'listing_category' );
define( 'VA_LISTING_TAG', 'listing_tag' );
define( 'VA_LISTING_FAVORITES', 'va_favorites' );

define( 'VA_REVIEWS_CTYPE', 'review' );
define( 'VA_REVIEWS_RATINGS', 'rating' );
define( 'VA_REVIEWS_PER_PAGE', 10 );

define( 'VA_ITEM_REGULAR', 'regular' );
define( 'VA_ITEM_FEATURED_HOME', 'featured-home' );
define( 'VA_ITEM_FEATURED_CAT', 'featured-cat' );

define( 'VA_MAX_FEATURED', 5 );
define( 'VA_MAX_IMAGES', 5 );

define('VA_ATTACHMENT_FILE', 'file' );
define('VA_ATTACHMENT_GALLERY', 'gallery' );

// Framework
require dirname(__FILE__) . '/framework/load.php';

// Payments
require dirname( __FILE__ ) . '/includes/payments/load.php';

// Theme-specific files
require dirname( __FILE__ ) . '/includes/utils.php';
require dirname( __FILE__ ) . '/includes/options.php';
require dirname( __FILE__ ) . '/includes/core.php';
require dirname( __FILE__ ) . '/includes/capabilities.php';
require dirname( __FILE__ ) . '/includes/views.php';
require dirname( __FILE__ ) . '/includes/listing-form.php';
require dirname( __FILE__ ) . '/includes/listing-status.php';
require dirname( __FILE__ ) . '/includes/listing-purchase.php';
require dirname( __FILE__ ) . '/includes/listing-activate.php';
require dirname( __FILE__ ) . '/includes/reviews.php';
require dirname( __FILE__ ) . '/includes/favorites.php';
require dirname( __FILE__ ) . '/includes/images.php';
require dirname( __FILE__ ) . '/includes/files.php';
require dirname( __FILE__ ) . '/includes/categories.php';
require dirname( __FILE__ ) . '/includes/template-tags.php';
require dirname( __FILE__ ) . '/includes/widgets.php';
require dirname( __FILE__ ) . '/includes/emails.php';
require dirname( __FILE__ ) . '/includes/custom-forms.php';
require dirname( __FILE__ ) . '/includes/featured.php';
require dirname( __FILE__ ) . '/includes/dashboard.php';
require dirname( __FILE__ ) . '/includes/admin-bar.php';

require dirname( __FILE__ ) . '/includes/payments.php';

require dirname( __FILE__ ) . '/includes/customizer.php';

if ( is_admin() ) {
	require dirname( __FILE__ ) . '/framework/admin/importer.php';
	require dirname( __FILE__ ) . '/framework/admin/class-meta-box.php';

	require dirname( __FILE__ ) . '/includes/admin/dashboard.php';
	require dirname( __FILE__ ) . '/includes/admin/settings.php';
	require dirname( __FILE__ ) . '/includes/admin/admin.php';
	require dirname( __FILE__ ) . '/includes/admin/pricing.php';
	require dirname( __FILE__ ) . '/includes/admin/listing-single.php';
	require dirname( __FILE__ ) . '/includes/admin/listing-list.php';
	require dirname( __FILE__ ) . '/includes/admin/featured.php';

	new VA_Pricing_General_Box();
	new VA_Pricing_Addon_Box();

	add_filter( 'manage_' . VA_LISTING_PTYPE . '_posts_columns', 'va_listing_manage_columns' );

	new VA_Listing_Location_Meta;
	new VA_Listing_Contact_Meta;
	new VA_Listing_Pricing_Meta;
	new VA_Listing_Publish_Moderation;
	new VA_Listing_Claim_Moderation;
	new VA_Listing_Claimable_Meta;
	new VA_Listing_Gallery_Meta;
	new VA_Listing_Reviews_Status_Meta;
	new VA_Listing_Author_Meta;

	$va_settings_admin = new VA_Settings_Admin( $va_options );
	add_action( 'admin_init', array( $va_settings_admin, 'init_integrated_options' ), 10 );
}

add_theme_support( 'app-versions', array(
	'update_page' => 'admin.php?page=app-settings&firstrun=1',
	'current_version' => VA_VERSION,
	'option_key' => 'vantage_version',
) );

add_theme_support( 'app-wrapping' );

add_theme_support( 'app-geo', array(
	'unit' => $va_options->geo_unit,
	'region' => $va_options->geo_region,
	'language' => $va_options->geo_language,
	'default_radius' => $va_options->default_radius
) );

add_theme_support( 'app-login', array(
	'login' => 'form-login.php',
	'register' => 'form-registration.php',
	'recover' => 'form-password-recovery.php',
	'reset' => 'form-password-reset.php',
) );

add_theme_support( 'app-form-builder', array(
	'show_in_menu' => 'edit.php?post_type=' . VA_LISTING_PTYPE
) );

add_theme_support( 'app-payments', array(
	'items' => array(
		array(
			'type' => VA_ITEM_REGULAR,
			'title' => __( 'Regular Listing', APP_TD ),
			'meta' => array(
				'price' => $va_options->listing_price
			)
		),
		array(
			'type' => VA_ITEM_FEATURED_HOME,
			'title' => __( 'Feature on Homepage', APP_TD ),
			'meta' => array(
				'price' => $va_options->addons[ VA_ITEM_FEATURED_HOME ]['price']
			)
		),
		array(
			'type' => VA_ITEM_FEATURED_CAT,
			'title' => __( 'Feature on Category', APP_TD ),
			'meta' => array(
				'price' => $va_options->addons[ VA_ITEM_FEATURED_CAT ]['price']
			)
		)
	),
	'items_post_types' => array( VA_LISTING_PTYPE ),
	'options' => $va_options,
) );

add_theme_support( 'app-term-counts', array(
	'post_type' => array( VA_LISTING_PTYPE ),
	'post_status' => array( 'publish' ),
) );

new APP_User_Profile;

new VA_Blog_Archive;
new VA_Listing_Archive;
new VA_Listing_Categories;
new VA_Listing_Taxonomy;
new VA_Listing_Search;
new VA_Listing_Create;
new VA_Listing_Purchase;
new VA_Listing_Claim;
new VA_Listing_Edit;
new VA_Listing_Single;
new VA_Listing_Author;
new VA_Listing_Dashboard;

// Taxonomies need to be registered before the post type, in order for the rewrite rules to work
add_action( 'init', 'va_register_taxonomies', 8 );
add_action( 'init', 'va_register_post_types', 9 );

// Flush rewrite rules if the related transient is set
add_action('init','va_check_rewrite_rules_transient', 10);

add_action( 'user_contactmethods', 'va_user_contact_methods' );
if ( !is_admin() ) {
	add_action( 'user_profile_update_errors', 'va_user_update_profile', 10, 3 );
}

add_action( 'template_redirect', 'va_add_style' );
add_action( 'template_redirect', 'va_add_scripts' );

add_action( 'after_setup_theme', 'va_setup_theme' );

add_filter( 'wp_nav_menu_objects', 'va_disable_hierarchy_in_footer', 9, 2 );

add_filter( 'body_class', 'va_body_class' );

add_filter( 'excerpt_more', 'va_excerpt_more' );
add_filter( 'excerpt_length', 'va_excerpt_length' );
add_filter( 'the_excerpt', 'strip_tags' );

add_action( 'wp_login', 'va_redirect_to_front_page' );
add_action( 'app_login', 'va_redirect_to_front_page' );
add_action( 'login_enqueue_scripts', 'va_login_styling' );
add_filter( 'login_headerurl', 'va_login_logo_url' );
add_filter( 'login_headertitle', 'va_login_logo_url_title' );

// Add a very low priority action to make sure any extra settings have been added to the permalinks global
add_action( 'admin_init', 'va_enable_permalink_settings', 999999 );

// ShareThis plugin compatibility
remove_filter( 'the_content', 'st_add_widget' );

// Social Connect plugin compatibility
add_action( 'app_login_pre_redirect', 'social_connect_grab_login_redirect' );

appthemes_init();

