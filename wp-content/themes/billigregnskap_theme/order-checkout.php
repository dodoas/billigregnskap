<div id="main">

	<div class="section-head">
		<h1><?php _e( 'Order Summary', APP_TD ); ?></h1>
	</div>

	<div class="order-summary">
		<?php the_order_summary(); ?>

		<form action="<?php the_order_return_url(); ?>" method="POST">
			<p><?php _e( 'Please select a method for processing your payment:', APP_TD ); ?></p>
			<?php appthemes_list_gateway_dropdown(); ?>
			<input type="submit">
		</form>

	</div>

</div>