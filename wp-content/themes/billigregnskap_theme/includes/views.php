<?php

class VA_Blog_Archive extends APP_View_Page {

	function __construct() {
		parent::__construct( 'index.php', __( 'Blog', APP_TD ) );

		add_action('appthemes_before_blog_post_content', array($this, 'blog_featured_image'));
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}

	public function blog_featured_image() {
		if ( has_post_thumbnail() ) {
			echo html('a', array(
				'href' => get_permalink(),
				'title' => the_title_attribute(array('echo'=>0)),
				), get_the_post_thumbnail( get_the_ID(), array( 420, 150 ), array( 'class' => 'alignleft' ) ) );
		}
	}
}


class VA_Listing_Archive extends APP_View_Page {

	function __construct() {
		parent::__construct( 'archive-listing.php', __( 'Listings', APP_TD ) );
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}


}


class VA_Listing_Categories extends APP_View_Page {

	function __construct() {
		parent::__construct( 'categories-list.php', __( 'Categories', APP_TD ) );
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}
}


class VA_Listing_Taxonomy extends APP_View {

	function condition() {
		return is_tax( VA_LISTING_CATEGORY ) || is_tax( VA_LISTING_TAG );
	}

	function template_include( $template ) {
		if ( 'index.php' == basename( $template ) )
			return locate_template( 'archive-listing.php' );

		return $template;
	}
}


class VA_Listing_Search extends APP_View {

	function init() {
		global $wp;

		$wp->add_query_var( 'ls' );
	}

	function condition() {
		return isset( $_GET['ls'] ) || get_query_var( 'location' );
	}

	function parse_query( $wp_query ) {
		global $va_options;

		$wp_query->set( 'ls', trim( get_query_var( 'ls' ) ) );
		$wp_query->set( 's', get_query_var( 'ls' ) );
		$wp_query->set( 'post_type', VA_LISTING_PTYPE );
		$wp_query->set( 'posts_per_page', $va_options->listings_per_page );

		if ( '' == $wp_query->get( 'order' ) )
			$wp_query->set( 'order', 'asc' );

		$orderby = $wp_query->get( 'orderby' );

		if ( empty( $orderby ) ) {
			$location = trim( $wp_query->get( 'location' ) );

			if ( !empty( $location ) ) {
				$orderby = $va_options->default_geo_search_sort;
			} else {
				$orderby = $va_options->default_search_sort;
			}

			$wp_query->set( 'orderby', $orderby );
		}

		switch ( $orderby ) {
		case 'rating':
			$wp_query->set( 'meta_key', 'rating_avg' );
			$wp_query->set( 'orderby', 'meta_value' );
			$wp_query->set( 'order', 'desc' );
			break;
		case 'distance':
		case 'title':
		default:
			break;
		}

		if ( isset( $_GET['listing_cat'] ) ) {
			$wp_query->set( 'tax_query', array(
				array(
					'taxonomy' => VA_LISTING_CATEGORY,
					'terms' => $_GET['listing_cat']
				)
			) );
		}

		$wp_query->is_home = false;
		$wp_query->is_archive = true;
		$wp_query->is_search = true;
	}

	function posts_search( $sql, $wp_query ) {
		global $wpdb;

		$q = $wp_query->query_vars;
		$search = '';

		if ( empty( $q['search_terms'] ) ) return $sql;

		// BEGIN COPY FROM WP_Query
		$n = !empty($q['exact']) ? '' : '%';
		$searchand = '';
		foreach( (array) $q['search_terms'] as $term ) {
			$term = esc_sql( like_escape( $term ) );

			// ADDED tter.name
			$search .= "{$searchand}(
				($wpdb->posts.post_title LIKE '{$n}{$term}{$n}') OR
				($wpdb->posts.post_content LIKE '{$n}{$term}{$n}') OR
				(tter.name LIKE '{$n}{$term}{$n}')
			)";

			$searchand = ' AND ';
		}

		if ( !empty($search) ) {
			$search = " AND ({$search}) ";
			if ( !is_user_logged_in() )
				$search .= " AND ($wpdb->posts.post_password = '') ";
		}
		// END COPY

		return $search;
	}

	function posts_clauses( $clauses ) {
		global $wpdb;

		$taxonomies = scbUtil::array_to_sql( array( VA_LISTING_CATEGORY, VA_LISTING_TAG ) );

		$clauses['join'] .= "
			INNER JOIN $wpdb->term_relationships AS trel
			ON ($wpdb->posts.ID = trel.object_id)
			INNER JOIN $wpdb->term_taxonomy AS ttax
			ON (ttax.taxonomy IN ($taxonomies) AND trel.term_taxonomy_id = ttax.term_taxonomy_id)
			INNER JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id)
			";

		$clauses['distinct'] = "DISTINCT";

		return $clauses;
	}

	function template_redirect() {

		wp_enqueue_script(
			'jquery-range',
			get_template_directory_uri() . '/scripts/jquery.range.js',
			array( 'jquery' ),
			'1.0',
			true
		);
	}
}


class VA_Listing_Dashboard extends APP_View {

	private $error;

	function init() {
		$this->handle_form();
		$this->add_rewrite_rules();
	}

	private function handle_form() {
		if ( !isset( $_POST['action'] ) || 'dashboard-reviews' != $_POST['action'] )
			return;

		if ( empty($_POST) || !wp_verify_nonce($_POST['_wpnonce'],'va-dashboard-reviews') ) {
			//nonce did not verify
			$this->error = __("There was an error. Please try again.", APP_TD );
		} else {
			// process form data
			// nonce did verify
			$review = get_comment($_POST['review_id']);
			$user_id = get_current_user_id();
			if ($user_id == $review->user_id ) {
				va_delete_review($_POST['review_id']);
				wp_redirect( './?deleted=true' );
				exit();
			} else {
				$this->error = __("Cannot delete review, it belongs to another user.", APP_TD );
			}
		}
	}

	private function add_rewrite_rules() {
		global $wp, $va_options;

		// User dashboard
		$wp->add_query_var( 'dashboard' );
		$wp->add_query_var( 'dashboard_author' );

		$dashboard_permalink = $va_options->dashboard_permalink;
		$dashboard_listings_permalink = $va_options->dashboard_listings_permalink;
		$dashboard_reviews_permalink = $va_options->dashboard_reviews_permalink;
		$dashboard_faves_permalink = $va_options->dashboard_faves_permalink;
		$dashboard_claimed_permalink = $va_options->dashboard_claimed_permalink;

		$dashboard_all_permalinks =
				   $dashboard_listings_permalink .
			'?|' . $dashboard_reviews_permalink .
			'?|' . $dashboard_faves_permalink .
			'?|' . $dashboard_claimed_permalink;

		// dashboard permalinks

		appthemes_add_rewrite_rule( $dashboard_permalink . '/?$', array(
			'dashboard' => $dashboard_listings_permalink,
			'dashboard_author' => 'self'
		) );

		appthemes_add_rewrite_rule( $dashboard_permalink . '/page/([0-9]+)/?$', array(
			'dashboard' => $dashboard_listings_permalink,
			'dashboard_author' => 'self',
			'paged' => '$matches[1]',
		) );

		// dashboard author (self) permalinks

		appthemes_add_rewrite_rule( $dashboard_permalink . '/(' . $dashboard_all_permalinks . '?)/?$', array(
			'dashboard' => '$matches[1]',
			'dashboard_author' => 'self'
		) );
		appthemes_add_rewrite_rule( $dashboard_permalink . '/(' . $dashboard_all_permalinks . '?)/?page/([0-9]+)/?$', array(
			'dashboard' => '$matches[1]',
			'dashboard_author' => 'self',
			'paged' => '$matches[2]',
		) );

		// dashboard author permalinks

		appthemes_add_rewrite_rule( $dashboard_permalink . '/(' . $dashboard_all_permalinks . '?)/(.*?)/page/([0-9]+)/?$', array(
			'dashboard' => '$matches[1]',
			'dashboard_author' => '$matches[2]',
			'paged' => '$matches[3]',
		) );
		appthemes_add_rewrite_rule( $dashboard_permalink . '/(' . $dashboard_all_permalinks . '?)/(.*?)/?$', array(
			'dashboard' => '$matches[1]',
			'dashboard_author' => '$matches[2]'
		) );

	}

	function condition() {
		return (bool) get_query_var( 'dashboard' );
	}

	function template_redirect() {
		global $wp_query;

		$wp_query->is_home = false;
		$wp_query->is_archive = true;
		$wp_query->is_404 = false;

		if ( get_query_var( 'dashboard_author' ) == 'self' ) {
			appthemes_auth_redirect_login();
		}
		
		add_filter( 'body_class', array($this, 'body_class' ), 0 );
		add_filter( 'wp_title', array( $this, 'title' ), 0 );
	}

	function template_include( $path ) {
		return locate_template( 'dashboard-setup.php' );
	}

	function body_class($classes) {
		$classes[] = 'va-dashboard';
		$classes[] = 'va-dashboard-'.va_get_dashboard_type();
		if(va_is_own_dashboard()) {
			$classes[] = 'va-dashboard-self';
		}
		
		return $classes;
	}
	
	function title() {
		return __( 'Dashboard', APP_TD );
	}

	function breadcrumbs( $trail ) {
		$trail['trail_end'] = $this->title();

		return $trail;
	}

	function notices() {
		if ( !empty( $this->error ) ) {
			appthemes_display_notice( 'success-pending', $this->error );
		} elseif ( isset( $_GET['deleted'] ) ) {
			appthemes_display_notice( 'success', __( 'Review deleted.', APP_TD ) );
		}
	}
}


class VA_Listing_Author extends APP_View {

	function condition() {
		return is_author();
	}

	function parse_query( $wp_query ) {
		global $va_options;

		$wp_query->set( 'post_type', VA_LISTING_PTYPE );

		$current_user = wp_get_current_user();

		if ( $wp_query->get( 'author_name' ) == $current_user->display_name )
		{
			$wp_query->query_vars = array_merge( $wp_query->query_vars, array(
				'post_type' => VA_LISTING_PTYPE,
				'post_status' => array( 'publish', 'pending' ),
			) );
		}

		$wp_query->set( 'posts_per_page', $va_options->listings_per_page );
	}
}


class VA_Listing_Create extends APP_View_Page {

	private $errors;
	
	function __construct() {
		parent::__construct( 'create-listing.php', __( 'Create Listing', APP_TD ) );
		add_action( 'wp_ajax_vantage_create_listing_geocode', array( __CLASS__, 'handle_ajax' ) );
		add_action( 'wp_ajax_nopriv_vantage_create_listing_geocode', array( __CLASS__, 'handle_ajax' ) );
	}

	public function handle_ajax() {
		if ( !isset( $_GET['address'] ) && (!isset( $_GET['lat'] ) && !isset( $_GET['lng'] )) )
			return;

		if( isset( $_GET['address'] ) ) {
			$api_response = va_geocode_address_api( $_GET['address'] );
		} else if( isset( $_GET['lat'] ) ) {
			$api_response = va_geocode_lat_lng_api( $_GET['lat'], $_GET['lng'] );
		}

		if ( !$api_response )
			die( "error" );

		die( json_encode( $api_response ) );

	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}

	function template_include( $path ) {

		if ( !current_user_can( 'edit_listings' ) ) {
			if ( get_option( 'users_can_register' ) ) {
				$message = sprintf( __( 'You must first login or <a href="%s">register</a> to Create a Listing.' , APP_TD ), add_query_arg( array( 'redirect_to' => urlencode(va_get_listing_create_url()) ), appthemes_get_registration_url() ) );
			} else {
				$message = __( 'You must first login to Create a Listing.' , APP_TD );
			}
			set_transient( 'login_notice', array( 'error', $message ), 300);
			wp_redirect( add_query_arg( array( 'redirect_to' => urlencode(va_get_listing_create_url()) ), APP_Login::get_url('redirect') ) );
			exit();
		}

		return $path;
	}

	function template_redirect() {
		$this->check_failed_upload();
		
		wp_register_script(
			'jquery-validate',
			get_template_directory_uri() . '/scripts/jquery.validate.min.js',
			array( 'jquery' ),
			'1.9.0',
			true
		);

		wp_enqueue_script(
			'va-listing-edit',
			get_template_directory_uri() . '/scripts/listing-edit.js',
			array( 'jquery-validate', 'jquery-ui-sortable' ),
			VA_VERSION,
			true
		);

		wp_localize_script(
			'va-listing-edit',
			'VA_i18n',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'clear'	  => __( 'Clear', APP_TD )
			)
		);

		appthemes_enqueue_geo_scripts( 'vantage_map_edit' );
		
		add_filter( 'body_class', array( $this, 'body_class' ), 99 );
	}
	
	function body_class($classes) {
		$classes[] = 'va_listing_create';
		return $classes;	
	}
	
	function check_failed_upload() {
		if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) return;
		
		$max_size = $this->convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
		$max_size_string = $this->convert_bytes_to_hr( $max_size );
		
		if ( !empty( $_SERVER['CONTENT_LENGTH'] ) && $_SERVER['CONTENT_LENGTH'] > $max_size ) {
			$errors = va_get_listing_error_obj();
			$errors->add( 'file-too-large', sprintf( __('Uploaded file was too large, maximum file size is %s', APP_TD ), $max_size_string ) );
		}
	}
	
	function convert_hr_to_bytes( $size ) {
		$size = strtolower($size);
		$bytes = (int) $size;
		if ( strpos($size, 'k') !== false )
			$bytes = intval($size) * 1024;
		elseif ( strpos($size, 'm') !== false )
			$bytes = intval($size) * 1024 * 1024;
		elseif ( strpos($size, 'g') !== false )
			$bytes = intval($size) * 1024 * 1024 * 1024;
		return $bytes;
	
	}
	
	function convert_bytes_to_hr( $bytes ) {
		$units = array( 0 => 'B', 1 => 'kB', 2 => 'MB', 3 => 'GB' );
		$log = log( $bytes, 1024 );
		$power = (int) $log;
		$size = pow(1024, $log - $power);
		return $size . $units[$power];
	}
	
}

class VA_Listing_Edit extends VA_Listing_Create {

	function init() {
		global $wp, $va_options;

		$wp->add_query_var( 'listing_edit' );

		$listing_permalink = $va_options->listing_permalink;
		$permalink = $va_options->edit_listing_permalink;

		appthemes_add_rewrite_rule( $listing_permalink. '/' . $permalink . '/(\d+)/?$', array(
			'listing_edit' => '$matches[1]'
		) );
	}

	function condition() {
		return (bool) get_query_var( 'listing_edit' );
	}

	function parse_query( $wp_query ) {
		$listing_id = $wp_query->get( 'listing_edit' );

		if ( !current_user_can( 'edit_post', $listing_id ) ) {
			wp_die( __( 'You do not have permission to edit that listing.', APP_TD ) );
		}

		$wp_query->is_home = false;

		$wp_query->query_vars = array_merge( $wp_query->query_vars, array(
			'post_type' => VA_LISTING_PTYPE,
			'post_status' => 'any',
			'post__in' => array( $listing_id )
		) );
	}

	function the_posts( $posts, $wp_query ) {
		if ( !empty( $posts ) ) {
			$wp_query->queried_object = reset( $posts );
			$wp_query->queried_object_id = $wp_query->queried_object->ID;
		}

		return $posts;
	}

	function template_include( $path ) {
		return locate_template( 'edit-listing.php' );
	}

	function title_parts( $parts ) {
		return array( sprintf( __( 'Edit "%s"', APP_TD ), get_the_title( get_queried_object_id() ) ) );
	}
	
	function body_class($classes) {
		$classes[] = 'va_listing_edit';
		return $classes;	
	}	
}


class VA_Listing_Purchase extends APP_View {

	function init() {
		global $wp, $va_options;

		$wp->add_query_var( 'listing_purchase' );
		
		$listing_permalink = $va_options->listing_permalink;
		$permalink = $va_options->purchase_listing_permalink;

		appthemes_add_rewrite_rule( $listing_permalink . '/' . $permalink . '/(\d+)/?$', array(
			'listing_purchase' => '$matches[1]'
		) );
	}

	function condition() {
		return (bool) get_query_var( 'listing_purchase' );
	}

	function parse_query( $wp_query ) {
		$listing_id = $wp_query->get( 'listing_purchase' );

		if ( 1 == get_post_meta( $listing_id, 'listing_claimable', true ) ) {
			// This is claimable, they may proceed with purchasing.
		} else if ( !current_user_can( 'edit_post', $listing_id ) ) {
			wp_die( __( 'You do not have permission to purchase that listing.', APP_TD ) );
		}

		$wp_query->is_home = false;
		$wp_query->query_vars = array_merge( $wp_query->query_vars, array(
			'post_type' => VA_LISTING_PTYPE,
			'post_status' => 'any',
			'post__in' => array( $listing_id )
		) );

	}

	function the_posts( $posts, $wp_query ) {
		if ( !empty( $posts ) ) {
			$wp_query->queried_object = reset( $posts );
			$wp_query->queried_object_id = $wp_query->queried_object->ID;
		}

		return $posts;
	}

	function template_include( $path ) {
		return locate_template( 'purchase-listing.php' );
	}

	function title_parts( $parts ) {
		return array( sprintf( __( 'Purchase "%s"', APP_TD ), get_the_title( get_queried_object_id() ) ) );
	}

}

class VA_Listing_Claim extends APP_View {

	function init() {
		global $wp, $va_options;

		$wp->add_query_var( 'listing_claim' );

		$listing_permalink = $va_options->listing_permalink;
		$permalink = $va_options->claim_listing_permalink;

		appthemes_add_rewrite_rule( $listing_permalink . '/' . $permalink . '/(\d+)/?$', array(
			'listing_claim' => '$matches[1]'
		) );
		
		add_filter( 'va_listing_validate_purchase_fields', array( $this, 'validate_purchase_claimable' ) );
		add_action( 'va_after_purchase_listing_new_form', array( $this, 'add_claiming_field' ) );
		add_action( 'va_create_listing_order', array( $this, 'claim_listing_order'), 10, 2 );
		add_action( 'appthemes_transaction_completed', array( $this, 'handle_claim_transaction_completed' ) );
		add_action( 'pending-claimed_to_publish', array( $this, 'update_listing_author' ) );
		add_action( 'va_purchase_activated', array( $this, 'update_order_listing_author' ) );
		add_action( 'appthemes_transaction_activated', array( $this, 'update_order_listing_author' ) );
		add_action( 'appthemes_after_import_upload_form', array( $this, 'import_form_option' ) );
		add_action( 'app_importer_import_row_post_meta', array( $this, 'import_form_action' ) );
		
		if ( isset($_GET['rejected']) )
			add_action( 'admin_notices', array( $this, 'rejected_claim_success_notice' ) );
	}

	function condition() {
		return (bool) get_query_var( 'listing_claim' );
	}

	function parse_query( $wp_query ) {
		$listing_id = $wp_query->get( 'listing_claim' );

		$claimable = get_post_meta( $listing_id, 'listing_claimable', true );
		if ( empty($claimable) ) {
			wp_die( __( 'This listing is not claimable.', APP_TD ) );
		}

		$wp_query->is_home = false;
		$wp_query->query_vars = array_merge( $wp_query->query_vars, array(
			'post_type' => VA_LISTING_PTYPE,
			'post_status' => 'any',
			'post__in' => array( $listing_id )
		) );

	}

	function the_posts( $posts, $wp_query ) {
		if ( !empty( $posts ) ) {
			$wp_query->queried_object = reset( $posts );
			$wp_query->queried_object_id = $wp_query->queried_object->ID;
		}

		return $posts;
	}

	function template_include( $path ) {
		add_filter( 'body_class', array($this, 'body_class' ), 0 );
		return locate_template( 'claim-listing.php' );
	}
	
	function body_class($classes) {
		$classes[] = 'va-claim-listing';
		
		return $classes;
	}
	
	function validate_purchase_claimable( $errors ){
		if ( !empty( $_POST['claiming'] ) ) {
			$claimable = get_post_meta( $_POST['ID'], 'listing_claimable', true );
			
			if ( empty( $claimable ) )
				$errors->add( 'not-claimable', __( 'This listing is not claimable.', APP_TD ) );

			if ( get_current_user_id() == get_post( $_POST['ID'] )->post_author )
				$errors->add( 'own-not-claimable', __( 'This listing already belongs to you.', APP_TD ) );

		}

		return $errors;	
	}
	
	function add_claiming_field( $listing ) {
		if ( get_current_user_id() == get_post( $listing->ID )->post_author )
			return false;

		$claiming = get_post_meta( $listing->ID, 'listing_claimable', true );
		
		if ( empty( $claiming ) ) return;
		?>
		<input type="hidden" name="claiming" value="1">
		<?php
	}

	function claim_listing_order( $order, $listing ) {
		if ( empty( $_POST['claiming'] ) ) return;
		
		add_post_meta( $order->get_id(), 'claimee', get_current_user_id(), true );
	}
	
	function handle_no_charge_claim_listing( $listing_id ) {
		global $va_options;

		$claimee = get_current_user_id();

		add_post_meta( $listing_id, 'claimee', $claimee, true );
		delete_post_meta( $listing_id, 'listing_claimable' );
		add_user_meta( $claimee, 'claimee', 1 );

		if ( $va_options->moderate_claimed_listings ) {
			va_update_post_status( $listing_id, 'pending-claimed' );
			$url = va_get_claimed_listings_url() . '#post-'. $listing_id;
		} else {
			self::update_listing_author( get_post( $listing_id ) );
			$url = get_permalink( $listing_id );
		}
		wp_redirect($url);
		exit;
	}
	
	function handle_claim_transaction_completed( $order ) {
		
		$claimee = get_post_meta( $order->get_id(), 'claimee', true );
		if ( empty( $claimee ) ) return;
		
		$order_info = _va_get_order_listing_info( $order );
				
		$listing_id = $order_info['listing']->ID;
		
		add_post_meta( $listing_id, 'claimee', $claimee, true );
		delete_post_meta( $listing_id, 'listing_claimable' );
		add_user_meta( $claimee, 'claimee', 1 );
	}
	
	function update_listing_author( $post ) {
		if ( isset( $_GET['reject'] ) ) return;
		
		$old_author = $post->post_author;
		$new_author = get_post_meta( $post->ID, 'claimee', true );
		
		if ( $old_author == $new_author ) return;
		
		wp_update_post( array(
			'ID' => $post->ID,
			'post_author' => $new_author
		) );
	}
	
	function update_order_listing_author( $order ) {
		$claimee = get_post_meta( $order->get_id(), 'claimee', true );
		if ( empty( $claimee ) ) return;
		
		$order_info = _va_get_order_listing_info( $order );

		$this->update_listing_author( $order_info['listing'] );
	}
	
	function reject_claim() {
		global $pagenow;
		
		if ( 'post.php' != $pagenow ) return;
		
		$listing_id = intval( $_GET['post'] );
		
		$rejected_claimee = get_post_meta( $listing_id, 'claimee', true );
		
		if ( empty( $rejected_claimee ) ) return;
		
		delete_post_meta( $listing_id, 'claimee' );
		add_post_meta( $listing_id, 'listing_claimable', '1', true );
		add_post_meta( $listing_id, 'rejected_claimee', $rejected_claimee ); //for future use to A. Just in case we would need to undo this rejection, and B. To check for and deny future attempts to claim this listing by this user
		
		va_update_post_status( $listing_id, 'publish' );
		
		do_action( 'va_rejected_listing_claim', $listing_id, $rejected_claimee );
		
		return true;
	}
	
	function rejected_claim_success_notice() {
		echo scb_admin_notice( __( 'You have rejected the claim, and now this listing has been reset to <a href="#listing-claimable">claimable</a>.', APP_TD ) );
	}	
		
	function import_form_option() {
		?>
		<p><label><?php _e('Mark All as Claimable?:', APP_TD) ?> <input type="checkbox" name="listing_claimable" value="1" /></label></p>
		<?php
	}
	
	function import_form_action( $post_meta ) {
		if ( !empty( $_POST['listing_claimable'] ) )
			$post_meta['listing_claimable'] = 1;
		
		return $post_meta;
	}
}

class VA_Listing_Single extends APP_View {

	function condition() {
		return is_singular( VA_LISTING_PTYPE );
	}

	function template_redirect() {
		wp_enqueue_style(
			'colorbox',
			get_template_directory_uri() . '/styles/colorbox/colorbox.css',
			array(),
			'1.3.19'
		);
		wp_enqueue_script(
			'colorbox',
			get_template_directory_uri() . '/scripts/jquery.colorbox-min.js',
			array( 'jquery' ),
			'1.3.19'
		);

		wp_enqueue_script(
			'jquery-raty',
			get_template_directory_uri() . '/scripts/jquery.raty.min.js',
			array( 'jquery' ),
			'2.1.0',
			true
		);

		wp_enqueue_script(
			'jquery-validate',
			get_template_directory_uri() . '/scripts/jquery.validate.min.js',
			array( 'jquery' ),
			'1.9.0',
			true
		);

		add_action( 'wp_footer', array( $this, 'script_init' ), 99 );
	}

	function script_init() {
		$hint_list = array(
			__( 'bad', APP_TD ),
			__( 'poor', APP_TD ),
			__( 'regular', APP_TD ),
			__( 'good', APP_TD ),
			__( 'excellent', APP_TD )
		);

?>
<script type="text/javascript">
jQuery(function($){
	$('#review-rating').raty({
		hintList: <?php echo json_encode( $hint_list ); ?>,
		path: '<?php echo get_template_directory_uri() . '/images/'; ?>',
		scoreName: 'review_rating',
		click: function(score, evt) {
			jQuery('#add-review-form').find('.rating-error').remove();
		}
	});
});
</script>

<?php
	}

	// Show parent categories instead of listing archive
	function breadcrumbs( $trail ) {
		$cat = get_the_listing_category( get_queried_object_id() );

		if ( !$cat )
			return $trail;

		$cat = (int) $cat->term_id;
		$chain = array_reverse( get_ancestors( $cat, VA_LISTING_CATEGORY ) );
		$chain[] = $cat;

		$new_trail = array( $trail[0] );

		foreach ( $chain as $cat ) {
			$cat_obj = get_term( $cat, VA_LISTING_CATEGORY );
			$new_trail[] = html_link( get_term_link( $cat_obj ), $cat_obj->name );
		}

		$new_trail['trail_end'] = $trail['trail_end'];

		return $new_trail;
	}

	function notices() {
		$status = get_post_status( get_queried_object() );

		if ( isset( $_GET['completed'] ) ) {
			if ( $status == 'pending' ) {
				appthemes_display_notice( 'success-pending', __( 'Your order has been successfully processed. It is currently pending and must be approved by an administrator.', APP_TD ) );
			} else {
				appthemes_display_notice( 'success', __( 'Your order has been successfully completed.', APP_TD ) );
			}
		}
		elseif ( isset( $_GET['updated'] ) ) {
			appthemes_display_notice( 'success', __( 'The listing has been successfully updated.', APP_TD ) );
		}
		elseif ( $status == 'pending' ) {
			appthemes_display_notice( 'success-pending', __( 'This listing is currently pending and must be approved by an administrator.', APP_TD ) );
		}
	}
}

