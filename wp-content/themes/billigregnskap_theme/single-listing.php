<div id="main">

<?php the_post(); ?>

<?php do_action( 'appthemes_notices' ); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php the_listing_image_gallery(); ?>

	<?php appthemes_before_post_title( VA_LISTING_PTYPE ); ?>
	<h1><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
	<p class="author"><?php printf( __( 'Added by %s', APP_TD ), va_get_the_author_listings_link() ); ?> </p>
	<?php the_listing_star_rating(); ?>
	<p class="reviews"><?php
		the_review_count();

		if ( va_user_can_add_reviews() ) {
			echo ', ' . html_link( '#add-review', __( 'Add your review', APP_TD ) );
		}
	?></p>

	<?php appthemes_after_post_title( VA_LISTING_PTYPE ); ?>

	<?php $website = get_post_meta( get_the_ID(), 'website', true ); ?>
	<?php $facebook = get_post_meta( get_the_ID(), 'facebook', true ); ?>
	<?php $twitter = get_post_meta( get_the_ID(), 'twitter', true ); ?>

	<ul>
		<li class="address"><?php the_listing_address(); ?></li>
		<li class="phone"><strong><?php echo esc_html( get_post_meta( get_the_ID(), 'phone', true ) ); ?></strong></li>
	<?php if ( $website ) : ?>
		<li id="listing-website"><a href="<?php echo esc_url( 'http://' . $website ); ?>" title="<?php _e( 'Website', APP_TD ); ?>" target="_blank"><?php echo esc_html( $website ); ?></a></li>
	<?php endif; ?>
	</ul>

	<?php if ( $facebook or $twitter ) : ?>
		<div id="listing-follow">
			<p><?php _e( 'Follow:', APP_TD ); ?></p>
			<?php if ( $facebook ) : ?>
			<a href="<?php echo esc_url( 'http://facebook.com/' . $facebook ); ?>" title="<?php _e( 'Facebook', APP_TD ); ?>" target="_blank"><div class="facebook-icon">Facebook</div></a>
			<?php endif; ?>
			<?php if ( $twitter ) : ?>
			<a href="<?php echo esc_url( 'http://twitter.com/' . $twitter ); ?>" title="<?php _e( 'Twitter', APP_TD ); ?>" target="_blank"><div class="twitter-icon">Twitter -</div> <span class="twitter-handle">@<?php echo esc_html( $twitter ); ?></span></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="listing-fields">
		<?php the_listing_fields(); ?>
	</div>

	<div class="single-listing listing-faves">
		<?php the_listing_faves_link(); ?>
	</div>

	<div class="listing-actions">
		<?php the_listing_edit_link(); ?>
		<?php the_listing_claimable_link(); ?>
		<?php the_listing_purchase_link(); ?>
	</div>

	<div class="listing-share">
		<?php if ( function_exists( 'sharethis_button' ) ) sharethis_button(); ?>
	</div>

	<hr />
	<div class="tags"><?php the_listing_tags( '<span>' . __( 'Tags:', APP_TD ) . '</span> ' ); ?></div>

	<?php the_listing_files(); ?>

	<div id="listing-tabs">
		<div class="tabs">
			<a id="overview-tab" class="active-tab rounded-t first" href="#overview"><?php _e( 'Overview', APP_TD ); ?></a>
			<a id="reviews-tab" class="rounded-t" href="#reviews"><?php _e( 'Reviews', APP_TD ); ?></a>

			<br class="clear" />
		</div>

		<section id="overview">
			<?php appthemes_before_post_content( VA_LISTING_PTYPE ); ?>
			<?php the_content(); ?>
			<?php appthemes_after_post_content( VA_LISTING_PTYPE ); ?>
		</section>

		<section id="reviews">
			<?php get_template_part( 'reviews', 'listing' ); ?>
		</section>
	</div>

	<div class="section-head">
		<a id="add-review" name="add-review"></a>
		<h2 id="left-hanger-add-review"><?php _e( 'Add Your Review', APP_TD ); ?></h2>
	</div>

	<?php if ( $review_id = va_get_user_review_id( get_current_user_id(), get_the_ID() ) ) : ?>
		<p>
			<?php _e( 'You have already reviewed this listing.', APP_TD ); ?>
		</p>
	<?php elseif ( va_user_can_add_reviews() ) : ?>
		<?php appthemes_load_template( 'form-review.php' ); ?>
	<?php elseif ( get_current_user_id() == get_the_author_meta('ID') ) : ?>
		<p>
			<?php _e( "You can't review your own listing.", APP_TD ); ?>
		</p>
	<?php elseif ( !is_user_logged_in() ) : ?>
		<p>
			<?php printf( __( "Please %s to add your review.", APP_TD ), html_link( wp_login_url(), __( 'login', APP_TD ) ) ); ?>
		</p>
	<?php endif; ?>

</article>

</div><!-- /#main -->

<div id="sidebar">
<?php get_sidebar( 'single-listing' ); ?>
</div>
