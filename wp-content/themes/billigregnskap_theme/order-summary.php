<div id="main">

<?php do_action( 'appthemes_notices' ); ?>

<div class="section-head">
	<h1><?php _e( 'Order Summary', APP_TD ); ?></h1>
</div>

<div class="order-summary">
	<?php the_order_summary(); ?>

	<p><?php _e( 'Your order has been completed.', APP_TD ); ?></p>
	<input type="submit" value="<?php esc_attr_e( 'Continue to Listing', APP_TD ); ?>" onClick="location.href='<?php echo get_permalink( $items[0]['post']->ID ); ?>';return false;">
</div>

</div>