<?php global $va_options; ?>

<?php 
	echo html( 'a', array(
		'href' => get_permalink( get_the_ID() ),
		'title' => esc_attr( get_the_title() ),
		'rel' => 'bookmark',
	), get_the_listing_thumbnail());
?>

<div class="review-meta">
	<?php the_listing_star_rating(); ?>

	<p class="reviews"><?php
		if ( va_user_can_add_reviews() ) {
			echo html_link(
				get_permalink( get_the_ID() ) . '#add-review',
				__( 'Add your review', APP_TD )
			);
			echo ', ';
		} else if ( !is_user_logged_in() ) {
			echo html_link(
				get_permalink( get_the_ID() ) . '#add-review',
				__( 'Add your review', APP_TD )
			);
			echo ', ';
		}
		
		the_review_count();
	?></p>
</div>

<?php appthemes_before_post_title( VA_LISTING_PTYPE ); ?>
<h2><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
<?php appthemes_after_post_title( VA_LISTING_PTYPE ); ?>

<p class="listing-cat"><?php the_listing_category(); ?></p>
<?php if ( function_exists('sharethis_button') && $va_options->listing_sharethis ): ?>
	<div class="listing-sharethis"><?php sharethis_button(); ?></div>
	<div class="clear"></div>
<?php endif; ?>
<div class="content-listing listing-faves">
	<?php the_listing_faves_link(); ?>
</div>
<p class="listing-phone"><?php echo esc_html( get_post_meta( get_the_ID(), 'phone', true ) ); ?></p>
<p class="listing-address"><?php the_listing_address(); ?></p>
<p class="listing-description"><strong><?php _e( 'Description:', APP_TD ); ?></strong> <?php the_excerpt(); ?> <?php echo html_link( get_permalink(), __( 'Read more...', APP_TD ) ); ?></p>
<p class="listing-description"><?php echo do_shortcode('[types field="pris" format="FIELD_NAME: FIELD_VALUE"][/types]'); ?></p>
