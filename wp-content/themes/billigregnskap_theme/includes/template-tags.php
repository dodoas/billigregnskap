<?php

function the_review_count( $listing_id = '' ) {
	$review_count = va_get_reviews_count( $listing_id );

	echo sprintf( _n( '1 erfaring', '%s erfaringer', $review_count, APP_TD ), number_format_i18n( $review_count ) );
}

function the_listing_address( $listing_id = '' ) {
	$parts = array();

	$listing_id = !empty( $listing_id ) ? $listing_id : get_the_ID();

	echo esc_html( get_post_meta( $listing_id , 'address', true ) );
}

function the_listing_tags( $before = null, $sep = ', ', $after = '' ) {
	if ( null === $before )
		$before = __( 'Tags: ', APP_TD );
	echo get_the_term_list( 0, VA_LISTING_TAG, $before, $sep, $after );
}

function the_listing_category( $listing_id = 0 ) {
	$cat = get_the_listing_category( $listing_id );
	if ( !$cat )
		return;

	printf( __( 'Listed in %s', APP_TD ), html_link( get_term_link( $cat ), $cat->name ) );
}

function the_listing_fields( $listing_id = 0 ) {
	$listing_id = $listing_id ? $listing_id : get_the_ID();

	$cat = get_the_listing_category( $listing_id );
	if ( !$cat )
		return;

	foreach ( va_get_fields_for_cat( (int) $cat->term_id ) as $field ) {
		if ( 'checkbox' == $field['type'] ) {
			$value = implode( ', ', get_post_meta( $listing_id, $field['name'] ) );
		} else {
			$value = get_post_meta( $listing_id, $field['name'], true );
		}

		if ( !$value )
			continue;

		$field['id_tag'] = va_make_custom_field_id_tag( $field['desc'] );

		echo html( 'p', array('class' => 'listing-custom-field', 'id' => $field['id_tag']),
			html('span', array('class' => 'custom-field-label'), $field['desc'] ). html('span', array('class' => 'custom-field-sep'), ': ' ) . html('span', array('class' => 'custom-field-value'), $value ) );
	}
}

function va_make_custom_field_id_tag( $desc, $prefix='listing-custom-field-' ) {
	$id_tag = $desc;
	$id_tag = strtolower( $id_tag );
	$id_tag = str_ireplace( ' ', '-', $id_tag );
	$id_tag = $prefix.$id_tag;
	return $id_tag;
}

function va_the_post_byline() {
	// Can't use the_date() because it only shows up once per date
	printf( __( '%1$s | %2$s %3$s', APP_TD ),
		get_the_time( get_option( 'date_format' ) ),
		va_get_author_posts_link(),
		get_the_category_list()
	);
}

function get_the_listing_category( $listing_id ) {
	$terms = get_the_terms( $listing_id, VA_LISTING_CATEGORY );
	if ( !$terms )
		return;

	return reset( $terms );
}

function the_listing_edit_link( $listing_id = 0, $text = '' ) {
	$listing_id = $listing_id ? $listing_id : get_the_ID();

	if ( !current_user_can( 'edit_post', $listing_id ) )
		return;

	if( empty( $text ) )
		$text = __( 'Edit Listing', APP_TD );

	echo html( 'a', array(
		'class' => 'listing-edit-link',
		'href' => va_get_listing_edit_url( $listing_id ),
	), $text );
}

function the_listing_claimable_link( $listing_id = '', $text = '' ) {
	$listing_id = !empty( $listing_id ) ? $listing_id : get_the_ID();
	if( !_va_is_claimable( $listing_id ) ) return;

	if( empty( $text ) )
		$text = __( 'Claim Listing', APP_TD );

	echo html( 'a', array(
		'class' => 'listing-claim-link',
		'href' => va_get_listing_claim_url( $listing_id ),
	), $text );
}


function va_get_listing_edit_url( $listing_id ) {
	global $wp_rewrite, $va_options;

	if ( $wp_rewrite->using_permalinks() ) {
		$listing_permalink = $va_options->listing_permalink;
		$permalink = $va_options->edit_listing_permalink;
		return home_url( user_trailingslashit( "$listing_permalink/$permalink/$listing_id" ) );
	}

	return home_url( "?listing_edit=$listing_id" );
}

function the_listing_purchase_link( $listing_id = 0, $text = '' ) {
	global $va_options;

	if( ! $va_options->listing_charge )
		return;

	if( !va_any_featured_addon_enabled() )
		return;

	$listing_id = $listing_id ? $listing_id : get_the_ID();

	if ( !current_user_can( 'edit_post', $listing_id ) )
		return;

	if( empty( $text ) )
		$text = __( 'Upgrade Listing', APP_TD );

	echo html( 'a', array(
		'class' => 'listing-edit-link',
		'href' => va_get_listing_purchase_url( $listing_id ),
	), $text );
}

function va_get_listing_purchase_url( $listing_id ) {
	global $wp_rewrite, $va_options;

	if ( $wp_rewrite->using_permalinks() ) {
		$listing_permalink = $va_options->listing_permalink;
		$permalink = $va_options->purchase_listing_permalink;
		return home_url( user_trailingslashit( "$listing_permalink/$permalink/$listing_id" ) );
	}

	return home_url( "?listing_purchase=$listing_id" );
}


function va_get_listing_claim_url( $listing_id ) {
	global $wp_rewrite, $va_options;

	if ( $wp_rewrite->using_permalinks() ) {
		$listing_permalink = $va_options->listing_permalink;
		$permalink = $va_options->claim_listing_permalink;
		return home_url( user_trailingslashit( "$listing_permalink/$permalink/$listing_id" ) );
	}

	return home_url( "?listing_claim=$listing_id" );
}

function the_listing_faves_link( $listing_id = 0 ) {
	$listing_id = $listing_id ? $listing_id : get_the_ID();
	va_display_fave_button( $listing_id );
}

function va_get_listing_create_url() {
	return get_permalink( VA_Listing_Create::get_id() );
}

function the_listing_star_rating( $post_id = '' ) {
	$rating = str_replace( '.' , '_' , va_get_rating_average( $post_id ) );

	if ( '' == $rating )
		$rating = '0';

?>
		<div class="stars-cont">
			<div class="stars stars-<?php echo $rating;  ?>"></div>
		</div>
<?php
}

function the_refine_order_ui() {
	$options = array();

	$options['rating'] = __( 'Highest Rating', APP_TD );

	if ( get_query_var( 'app_geo_query' ) )
		$options['distance'] = __( 'Closest', APP_TD );

	$options['title'] = __( 'Alphabetical', APP_TD );

	$data = array( 'orderby' => get_query_var( 'orderby' ) );
	$data['orderby'] = $data['orderby'] == 'meta_value' ? 'rating' : $data['orderby'];

	echo scbForms::input( array(
		'type' => 'radio',
		'name' => 'orderby',
		'values' => $options
	), $data );
}

function the_refine_distance_ui() {
	global $va_options, $wp_query;

	$current_radius = (int) get_query_var( 'radius' );

	$geo_query = $wp_query->get( 'app_geo_query' );

	$current_radius = $geo_query['rad'];
	
	extract(va_calc_radius_slider_controls($current_radius));

?>
<label>
	<input name="radius" value="<?php echo esc_attr( $current_radius ); ?>" type="range" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" />
	<div class="radius-info-box"><span id="radius-info"><?php echo $current_radius; ?></span> <?php 'km' == $va_options->geo_unit ? _e( 'km', APP_TD ) : _e( 'miles', APP_TD ); ?></div>
</label>
<?php
}

function the_refine_category_ui() {
	require_once ABSPATH . '/wp-admin/includes/template.php';

	ob_start();
	wp_terms_checklist( 0, array(
		'taxonomy' => VA_LISTING_CATEGORY,
		'selected_cats' => isset( $_GET['listing_cat'] ) ? $_GET['listing_cat'] : array(),
		'checked_ontop' => false
	) );
	$output = ob_get_clean();

	$output = str_replace( 'tax_input[' . VA_LISTING_CATEGORY . ']', 'listing_cat', $output );
	$output = str_replace( 'disabled=\'disabled\'', '', $output );

	echo html( 'ul', $output );
}

function the_search_refinements() {
	appthemes_pass_request_var( 'orderby' );
	appthemes_pass_request_var( 'radius' );
	appthemes_pass_request_var( 'listing_cat' );
}

function va_display_logo(){
	$header_image = '' != get_header_image() ? get_header_image() : get_template_directory_uri().'/images/vantage-logo.png';
?>
	<h1 id="site-title">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="custom-header-image" style="height:<?php echo get_custom_header()->height; ?>px;width:<?php echo get_custom_header()->width; ?>px;background: transparent url('<?php echo $header_image; ?>') no-repeat 0 0;"><?php bloginfo( 'title' ); ?></a>
	</h1>
	<?php if( display_header_text() ) { ?>
	<h2 id="site-description" style="color:#<?php header_textcolor(); ?>;"><?php bloginfo( 'description' ); ?></h2>
	<?php } ?>
<?php
}

function va_display_navigation_menu() {

	wp_nav_menu( array(
		'menu_id'         => 'navigation',
		'theme_location' => 'header',
		'container_class' => 'menu rounded',
		'items_wrap' => '<ul id="%1$s">%3$s</ul>',
		'fallback_cb' => false
	) );
?>
	<script type="text/javascript">
		jQuery('#navigation').tinyNav({
			active: 'current-menu-item',
			header: '<?php _e( 'Navigation' , APP_TD ); ?>',
			header_href: '<?php echo esc_js( home_url( '/' ) ); ?>',
			indent: '-',
			excluded: ['#adv_categories']
		});
	</script>
<?php
}

/**
* Taken from http://codex.wordpress.org/Template_Tags/the_author_posts_link.
* Modified to return the link instead of display it
*/
function va_get_author_posts_link() {

        global $authordata;
        if ( !is_object( $authordata ) )
                return false;
        $link = sprintf(
                '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
                get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
                esc_attr( sprintf( __( 'Posts by %s', APP_TD ), get_the_author() ) ),
                get_the_author()
        );
        return apply_filters( 'the_author_posts_link', $link );
}

function va_js_redirect( $url ) {
	echo html( 'a', array( 'href' => $url ), __( 'Continue', APP_TD ) );
	echo html( 'script', 'location.href="' . $url . '"' );
}

function va_js_redirect_to_listing( $listing_id, $query_args = array() ) {
	if ( !is_admin() ) {
		$url = add_query_arg( $query_args, get_permalink( $listing_id ) );
		va_js_redirect( $url );
	}
}

function va_js_redirect_to_claimed_listing( $listing_id ) {
	if ( !is_admin() ) {
		$url = va_get_claimed_listings_url() . '#post-'. $listing_id;
		va_js_redirect( $url );
	}
}
