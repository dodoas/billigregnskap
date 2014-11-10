<?php

class VA_Widget_Create_Listing_Button extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'description' => __( 'A button for creating a new listing', APP_TD )
		);

		parent::__construct( 'create_listing_button', __( 'Vantage - Create Listing Button', APP_TD ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$url = va_get_listing_create_url();

		if ( is_tax( VA_LISTING_CATEGORY ) )
			$url = add_query_arg( VA_LISTING_CATEGORY, get_queried_object_id(), $url );

		echo $before_widget;
		echo html_link( $url, __( 'Add a business now', APP_TD ) );
		echo $after_widget;
	}
}


class VA_Widget_Listing_Map extends WP_Widget {

	const AJAX_ACTION = 'vantage_listing_geocode';

	function __construct() {
		$widget_ops = array(
			'description' => __( 'A map containing the location of a listing. Use in Single Listing Sidebar.', APP_TD )
		);

		parent::__construct( 'listing_map', __( 'Vantage - Listing Location', APP_TD ), $widget_ops );

		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'handle_ajax' ) );
		add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( __CLASS__, 'handle_ajax' ) );
	}

	static function handle_ajax() {
		if ( !isset( $_GET['listing_id'] ) )
			return;

		$coord = va_geocode_address( $_GET['listing_id'] );
		if ( !$coord )
			die( "error" );

		die( json_encode( $coord ) );
	}

	function widget( $args, $instance ) {
		if ( !is_singular( VA_LISTING_PTYPE ) )
			return;

		$listing_id = get_queried_object_id();

		$coord = appthemes_get_coordinates( $listing_id, false );

		if ( $coord ) {
			$attr = array(
				'data-lat' => $coord->lat,
				'data-lng' => $coord->lng
			);
		} else {
			$attr = array(
				'data-listing_id' => $listing_id
			);
		}

		$attr['id'] = 'listing-map';

		$title = !empty( $instance['title'] ) ? $instance['title'] : '';

		extract( $args );

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		echo html( 'div', $attr );

		echo $after_widget;

		appthemes_enqueue_geo_scripts( 'vantage_map_view' );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'text' => '' ) );
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
<?php
	}
}


class VA_Widget_Categories extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'description' => __( 'Related Listing Categories', APP_TD )
		);

		parent::__construct( 'listing_categories', __( 'Vantage - Related Categories', APP_TD ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Categories' , APP_TD ) : $instance['title'], $instance, $this->id_base );
		$show_count = $instance['count'] ? '1' : '0';
		$app_pad_counts = $show_count;
		$pad_counts = false;
		$hierarchical = 1;
		$orderby = 'name';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$taxonomy = VA_LISTING_CATEGORY;
		$hide_empty = false;
		$depth = 1;

		$curr_cat = null;

		if ( is_home() ) {
			$child_of = 0;

		} elseif ( is_page() ) {

			$child_of = 0;

		} elseif ( is_tax( VA_LISTING_CATEGORY ) ) {

			$term_slug = get_query_var( VA_LISTING_CATEGORY );

			$term_info = get_term_by( 'slug', $term_slug, VA_LISTING_CATEGORY );
			$child_of = $term_info->term_id;

			$args = array(
				'taxonomy' => VA_LISTING_CATEGORY,
				'child_of' => $child_of,
				'hide_empty'=> 0,
			);

			$term_children = get_categories( $args );
			if ( empty( $term_children ) ) {
				//$cat =
				$category_id = $child_of;
				$category_tax = VA_LISTING_CATEGORY;
				$category_alt = get_term( $category_id, $category_tax );
				$cat_parent = $category_alt->parent;
				$child_of = $cat_parent;
			}


		} elseif ( is_singular( VA_LISTING_PTYPE ) ) {
			$the_terms = get_the_terms( get_the_ID(), VA_LISTING_CATEGORY );

			foreach ( $the_terms as $term ) {
				$first_term = $term;
				break;
			}

			$cat = $first_term->term_id;
			$child_of = $cat;
			$curr_cat = $cat;

			$category_id = $cat;
			$category_tax = VA_LISTING_CATEGORY;
			$category_alt = get_term( $category_id, $category_tax );
			$cat_parent = $category_alt->parent;
			$child_of = $cat_parent;
		} else {
			$child_of = 0;
		}

		$cat_args = compact( 'orderby', 'show_count', 'pad_counts', 'app_pad_counts', 'hierarchical', 'taxonomy', 'child_of', 'hide_empty', 'depth' );
?>
		<ul>
<?php
		$cat_args['title_li'] = '';
		wp_list_categories( apply_filters( 'widget_categories_args', $cat_args ) );
?>
		</ul>
<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['count'] = !empty( $new_instance['count'] ) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = esc_attr( $instance['title'] );
		$count = isset( $instance['count'] ) ? (bool) $instance['count'] :false;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' , APP_TD ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>



		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show post counts' , APP_TD ); ?></label><br />
<?php
	}

}


class VA_Widget_Recent_Reviews extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'description' => __( 'The most recent reviews', APP_TD )
		);
		parent::__construct( 'recent_reviews', __( 'Vantage - Recent Reviews', APP_TD ), $widget_ops );

		$this->alt_option_name = 'va_widget_recent_reviews';

		if ( is_active_widget( false, false, $this->id_base ) )
			add_action( 'wp_head', array( &$this, 'recent_comments_style' ) );

		add_action( 'comment_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'transition_comment_status', array( &$this, 'flush_widget_cache' ) );
	}

	function recent_comments_style() {
		if ( ! current_theme_supports( 'widgets' ) // Temp hack #14876
			|| ! apply_filters( 'show_recent_comments_widget_style', true, $this->id_base ) )
			return;
?>
	<style type="text/css">.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style>
<?php
	}

	function flush_widget_cache() {
		wp_cache_delete( 'va_widget_recent_reviews', 'widget' );
	}

	function widget( $args, $instance ) {

		$cache = wp_cache_get('va_widget_recent_reviews', 'widget');

		if ( ! is_array( $cache ) )
			$cache = array();

		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		extract( $args, EXTR_SKIP );
		$output = '';
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Reviews', APP_TD ) : $instance['title'] );

		if ( ! $number = absint( $instance['number'] ) )
			$number = 5;

		$reviews = va_get_reviews( array(
			'number'  => $number,
			'status'  => 'approve',
			'post_status' => 'publish'
		) );


		$output .= $before_widget;
		if ( $title )
			$output .= $before_title . $title . $after_title;

		$output .= '<ul>';
		if ( $reviews ) {
			foreach ( (array) $reviews as $review ) {
				$user = get_userdata( $review->user_id );

				$output .=  '<li class="recent-review clear">' .
					'<div class="review-author">'.
					html_link( va_get_the_author_reviews_url( $user->ID ) , get_avatar( $user->ID, 45 ) ) .
					'</div>'.
					'<div class="review-content">'.
					'<div class="review-meta">'.
					'<h4 class="listing-title">'.html_link( get_permalink( $review->comment_post_ID ), get_the_title( $review->comment_post_ID ) ).'</h4>'.
					'<div class="stars-cont">'.
					'<div class="stars stars-'.va_get_rating( $review->comment_ID ).'"></div>'.
					'</div>'.
					'<span class="reviewer-date">'.va_get_the_author_reviews_link( $user->ID ).' '.va_string_ago( $review->comment_date ).'</span>'.
					'</div>'.
					'<span>' . va_truncate( $review->comment_content, 120, '', " " ) .' '. html_link( va_get_review_link( $review->comment_ID ) , __(' Read More', APP_TD ) ) . '</span>'.
					'</div>'.
					'</li>';
			}
		}
		$output .= '</ul>';
		$output .= $after_widget;

		echo $output;

		$cache[$args['widget_id']] = $output;
		wp_cache_set('va_widget_recent_reviews', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['va_widget_recent_reviews'] ) )
			delete_option( 'va_widget_recent_reviews' );

		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of reviews to show:' , APP_TD ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}


class VA_Widget_Recent_Listings extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'description' => __( 'The most recent listings', APP_TD )
		);
		parent::__construct( 'recent_listings', __( 'Vantage - Recent Listings', APP_TD ), $widget_ops );

		$this->alt_option_name = 'va_widget_recent_listings';

		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
	}

	function widget( $args, $instance ) {
		$cache = wp_cache_get( 'widget_recent_listings', 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Listings' , APP_TD ) : $instance['title'], $instance, $this->id_base );
		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
			$number = 10;

		$r = new WP_Query( array( 'post_type' => VA_LISTING_PTYPE, 'posts_per_page' => $number, 'no_found_rows' => true, 'post_status' => 'publish', 'ignore_sticky_posts' => true ) );
		if ( $r->have_posts() ) :
		?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php  while ( $r->have_posts() ) : $r->the_post(); ?>
		<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr( get_the_title() ? get_the_title() : get_the_ID() ); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
		<?php
			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();

		endif;

	$cache[$args['widget_id']] = ob_get_flush();
	wp_cache_set( 'va_widget_recent_listings', $cache, 'widget' );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['va_widget_recent_listings'] ) )
			delete_option( 'va_widget_recent_listings' );

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'va_widget_recent_listings', 'widget' );
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' , APP_TD ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of listings to show:' , APP_TD ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}


class VA_Widget_Connect extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'description' => __( 'A set of icons to link to many social networks', APP_TD )
		);

		parent::__construct( 'connect', __( 'Vantage - Connect', APP_TD ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Connect' , APP_TD ) : $instance['title'], $instance, $this->id_base );


		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$img_url = get_bloginfo( 'template_directory' ).'/images/';

		echo '<ul class="connect">';

		if ( $instance['twitter_inc'] && !empty( $instance['twitter'] ) )
			echo '<li><a class="" href="'.$instance['twitter'].'" target="_blank"><img src="'.$img_url.'connect-twitter.png" /></a></li>';
		if ( $instance['facebook_inc'] && !empty( $instance['facebook'] ) )
			echo '<li><a class="" href="'.$instance['facebook'].'" target="_blank"><img src="'.$img_url.'connect-facebook.png" /></a></li>';
		if ( $instance['linkedin_inc'] && !empty( $instance['linkedin'] ) )
			echo '<li><a class="" href="'.$instance['linkedin'].'" target="_blank"><img src="'.$img_url.'connect-linkedin.png" /></a></li>';
		if ( $instance['youtube_inc'] && !empty( $instance['youtube'] ) )
			echo '<li><a class="" href="'.$instance['youtube'].'" target="_blank"><img src="'.$img_url.'connect-youtube.png" /></a></li>';
		if ( $instance['google_inc'] && !empty( $instance['google'] ) )
			echo '<li><a class="" href="'.$instance['google'].'" target="_blank"><img src="'.$img_url.'connect-google.png" /></a></li>';
		if ( $instance['rss_inc'] && !empty( $instance['rss'] ) )
			echo '<li><a class="" href="'.$instance['rss'].'" target="_blank"><img src="'.$img_url.'connect-rss.png" /></a></li>';
		echo '</ul>';

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['twitter'] = strip_tags( $new_instance['twitter'] );
		$instance['twitter_inc'] = !empty( $new_instance['twitter_inc'] ) ? 1 : 0;
		$instance['facebook'] = strip_tags( $new_instance['facebook'] );
		$instance['facebook_inc'] = !empty( $new_instance['facebook_inc'] ) ? 1 : 0;
		$instance['linkedin'] = strip_tags( $new_instance['linkedin'] );
		$instance['linkedin_inc'] = !empty( $new_instance['linkedin_inc'] ) ? 1 : 0;
		$instance['youtube'] = strip_tags( $new_instance['youtube'] );
		$instance['youtube_inc'] = !empty( $new_instance['youtube_inc'] ) ? 1 : 0;
		$instance['google'] = strip_tags( $new_instance['google'] );
		$instance['google_inc'] = !empty( $new_instance['google_inc'] ) ? 1 : 0;
		$instance['rss'] = strip_tags( $new_instance['rss'] );
		$instance['rss_inc'] = !empty( $new_instance['rss_inc'] ) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$twitter = isset( $instance['twitter'] ) ? esc_attr( $instance['twitter'] ) : '';
		$twitter_inc = isset( $instance['twitter_inc'] ) ? (bool) $instance['twitter_inc'] :false;
		$facebook = isset( $instance['facebook'] ) ? esc_attr( $instance['facebook'] ) : '';
		$facebook_inc = isset( $instance['facebook_inc'] ) ? (bool) $instance['facebook_inc'] :false;
		$linkedin = isset( $instance['linkedin'] ) ? esc_attr( $instance['linkedin'] ) : '';
		$linkedin_inc = isset( $instance['linkedin_inc'] ) ? (bool) $instance['linkedin_inc'] :false;
		$youtube = isset( $instance['youtube'] ) ? esc_attr( $instance['youtube'] ) : '';
		$youtube_inc = isset( $instance['youtube_inc'] ) ? (bool) $instance['youtube_inc'] :false;
		$google = isset( $instance['google'] ) ? esc_attr( $instance['google'] ) : '';
		$google_inc = isset( $instance['google_inc'] ) ? (bool) $instance['google_inc'] :false;
		$rss = isset( $instance['rss'] ) ? esc_attr( $instance['rss'] ) : '';
		$rss_inc = isset( $instance['rss_inc'] ) ? (bool) $instance['rss_inc'] :false;
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter' ); ?>"><?php _e( 'Twitter URL:' , APP_TD ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'twitter' ); ?>" name="<?php echo $this->get_field_name( 'twitter' ); ?>" type="text" value="<?php echo $twitter; ?>"  />
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'twitter_inc' ); ?>" name="<?php echo $this->get_field_name( 'twitter_inc' ); ?>"<?php checked( $twitter_inc ); ?> />
			<label for="<?php echo $this->get_field_id( 'twitter_inc' ); ?>"><?php _e( 'Show Twitter Button?' , APP_TD ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'facebook' ); ?>"><?php _e( 'Facebook URL:' , APP_TD ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'facebook' ); ?>" name="<?php echo $this->get_field_name( 'facebook' ); ?>" type="text" value="<?php echo $facebook; ?>"  />
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'facebook_inc' ); ?>" name="<?php echo $this->get_field_name( 'facebook_inc' ); ?>"<?php checked( $facebook_inc ); ?> />
			<label for="<?php echo $this->get_field_id( 'facebook_inc' ); ?>"><?php _e( 'Show Facebook Button?' , APP_TD ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'linkedin' ); ?>"><?php _e( 'LinkedIn URL:' , APP_TD ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'linkedin' ); ?>" name="<?php echo $this->get_field_name( 'linkedin' ); ?>" type="text" value="<?php echo $linkedin; ?>"  />
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'linkedin_inc' ); ?>" name="<?php echo $this->get_field_name( 'linkedin_inc' ); ?>"<?php checked( $linkedin_inc ); ?> />
			<label for="<?php echo $this->get_field_id( 'linkedin_inc' ); ?>"><?php _e( 'Show LinkedIn Button?' , APP_TD ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'youtube' ); ?>"><?php _e( 'YouTube URL:' , APP_TD ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'youtube' ); ?>" name="<?php echo $this->get_field_name( 'youtube' ); ?>" type="text" value="<?php echo $youtube; ?>"  />
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'youtube_inc' ); ?>" name="<?php echo $this->get_field_name( 'youtube_inc' ); ?>"<?php checked( $youtube_inc ); ?> />
			<label for="<?php echo $this->get_field_id( 'youtube_inc' ); ?>"><?php _e( 'Show YouTube Button?' , APP_TD ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'google' ); ?>"><?php _e( 'Google URL:' , APP_TD ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'google' ); ?>" name="<?php echo $this->get_field_name( 'google' ); ?>" type="text" value="<?php echo $google; ?>"  />
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'google_inc' ); ?>" name="<?php echo $this->get_field_name( 'google_inc' ); ?>"<?php checked( $google_inc ); ?> />
			<label for="<?php echo $this->get_field_id( 'google_inc' ); ?>"><?php _e( 'Show Google Button?' , APP_TD ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'rss' ); ?>"><?php _e( 'RSS URL:' , APP_TD ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'rss' ); ?>" name="<?php echo $this->get_field_name( 'rss' ); ?>" type="text" value="<?php echo $rss; ?>"  />
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'rss_inc' ); ?>" name="<?php echo $this->get_field_name( 'rss_inc' ); ?>"<?php checked( $rss_inc ); ?> />
			<label for="<?php echo $this->get_field_id( 'rss_inc' ); ?>"><?php _e( 'Show RSS Button?' , APP_TD ); ?></label>
		</p>


<?php
	}
}


class VA_Widget_Sidebar_Ad extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'description' => __( 'A html/text widget for the sidebar and an image/ad with a width of 260px will fit perfectly. ', APP_TD )
		);

		parent::__construct( 'sidebar_ad', __( 'Vantage - Sidebar Ad', APP_TD ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = !empty( $instance['title'] ) ? $instance['title'] : '';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		echo $instance['text'];
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text'] =  $new_instance['text'];
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'text' => '' ) );
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$text = esc_textarea( $instance['text'] );
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo $text; ?></textarea>
<?php
	}

}

class VA_Widget_Listings_Ad extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'description' => __( 'HTML/Text widget for 468x60 ad banner. Can be used in header and at bottom of listings pages (home page, search results, categories, etc).', APP_TD )
		);

		parent::__construct( 'listings_ad', __( 'Vantage - 468x60 ad banner', APP_TD ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = !empty( $instance['title'] ) ? $instance['title'] : '';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		echo $instance['text'];
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text'] =  $new_instance['text'];
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'text' => '' ) );
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$text = esc_textarea( $instance['text'] );
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo $text; ?></textarea>
<?php
	}

}

class VA_Widget_Popular_Categories extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'description' => __( 'Popular Listing Categories', APP_TD )
		);

		parent::__construct( 'popular_listing_categories', __( 'Vantage - Popular Categories', APP_TD ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Popular Categories' , APP_TD ) : $instance['title'], $instance, $this->id_base );

		$taxonomy = VA_LISTING_CATEGORY;
		if ( ! $a = (int) $instance['amount'] ) {
			$a = 5;
		} elseif ( $a < 1 ) {
			$a = 1;
		}
		$s = 'count';
		$o = 'desc';

		$c = $instance['count'] ? '1' : '0';
		$h = 1;


		$top_cats = get_terms( $taxonomy, array ( 'fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'number' => $a, 'hierarchical' => FALSE ) );
		$included_cats = implode( ",", $top_cats );

		$cat_args = array ( 'taxonomy' => $taxonomy, 'include' => $included_cats, 'orderby' => $s, 'order' => $o, 'show_count' => $c, 'hide_empty' => FALSE, 'hierarchical' => FALSE, 'depth' => - 1, 'title_li' => '', );

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		echo '<ul>';
		wp_list_categories( apply_filters( 'widget_categories_args', $cat_args ) );
		echo '</ul>';
		echo $after_widget;

	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['amount'] = (int) $new_instance['amount'];
		$instance['count'] = !empty( $new_instance['count'] ) ? 1 : 0;
		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'amount' => '' ) );
		$title = esc_attr( $instance['title'] );

		$count = isset( $instance['count'] ) ? (bool) $instance['count'] :false;

		if ( ! $amount = (int) $instance['amount'] ) {
			$amount = 5;
		}

		if ( $amount < 1 ) {
			$amount = 1;
		}

?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' , APP_TD ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'amount' ); ?>"><?php _e( 'How Many Categories to Show?:' , APP_TD ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'amount' ); ?>" name="<?php echo $this->get_field_name( 'amount' ); ?>" type="text" value="<?php echo $amount; ?>" /></p>


		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show post counts' , APP_TD ); ?></label><br />
<?php
	}

}



function va_register_widgets() {
	register_widget( 'VA_Widget_Create_Listing_Button' );
	register_widget( 'VA_Widget_Listing_Map' );
	register_widget( 'VA_Widget_Categories' );
	register_widget( 'VA_Widget_Recent_Reviews' );
	register_widget( 'VA_Widget_Recent_Listings' );
	register_widget( 'VA_Widget_Connect' );
	register_widget( 'VA_Widget_Sidebar_Ad' );
	register_widget( 'VA_Widget_Listings_Ad' );
	register_widget( 'VA_Widget_Popular_Categories' );

	unregister_widget( 'WP_Widget_Search' );
	unregister_widget( 'WP_Widget_Meta' );
}

add_action( 'widgets_init', 'va_register_widgets' );
