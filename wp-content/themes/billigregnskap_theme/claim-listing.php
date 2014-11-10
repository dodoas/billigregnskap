<div id="main">
	<div class="section-head">
		<h1><?php printf( __( 'Claim "%s"', APP_TD ), get_the_title( get_queried_object_id() ) ); ?></h1>
	</div>
	
	<?php do_action( 'appthemes_notices' ); ?>
	
	<?php $queried_listing = va_get_claimed_listing(); ?>
	<?php while ( $queried_listing->have_posts() ) : $queried_listing->the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php get_template_part( 'content-claimed-listing' ); ?>
		</article>
	<?php endwhile; ?>
	
	<div class="claim-listing">
        <p><?php printf( __('So, you\'d like to claim "%s"?', APP_TD), get_the_title( get_queried_object_id() ) );?></p>

        <?php if ( !is_user_logged_in() ) { ?>
        <p><?php _e( 'If you are the owner to this business, you may proceed with claiming this listing by ', APP_TD ); ?><?php echo html_link( APP_Login::get_url( '', va_get_listing_purchase_url( get_queried_object_id() ) ), __( 'Logging in', APP_TD ) );
        	if ( get_option( 'users_can_register' ) ) {
				_e( ' or ', APP_TD );
				echo html_link( add_query_arg( 'redirect_to', urlencode( va_get_listing_purchase_url( get_queried_object_id() ) ), appthemes_get_registration_url() ), __( 'Registering for an account', APP_TD ) );
			}
        ?><?php _e( ', to begin the claiming process.', APP_TD ); ?></p>
		<?php } else { ?>
        <p><?php _e( 'If you are the owner to this business, you may proceed with claiming this listing by clicking the continue button below to begin the claiming process.', APP_TD ); ?></p>
        <form id="claim-listing" method="POST" action="<?php echo va_get_listing_purchase_url( get_queried_object_id() ); ?>">
			<fieldset>
				<?php wp_nonce_field( 'va_claim_listing' ); ?>
				<input type="hidden" name="action" value="claim-listing">
				<input type="hidden" name="ID" value="<?php echo get_queried_object_id(); ?>">
				<div classess="form-field"><input type="submit" value="<?php _e( 'Continue', APP_TD ) ?>" /></div>
			</fieldset>
		</form>
		<?php } ?>		
	</div>
</div>