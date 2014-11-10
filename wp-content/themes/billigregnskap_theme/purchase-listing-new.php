<div id="main">
	<?php do_action( 'appthemes_notices' ); ?>
	<div class="section-head">
	      <h1><?php _e( 'Select a Plan', APP_TD ); ?></h1>
	</div>
	<form id="create-listing" method="POST" action="<?php echo va_get_listing_purchase_url( $listing->ID ); ?>">
		<fieldset>
		    <div class="pricing-options">
		    	<?php if( !empty( $plans ) ) { ?>
			    	<?php foreach( $plans as $key => $plan ){ ?>
			    		<div class="plan">
			    			<div class="content">
			    				<div class="title">
			    					<?php echo $plan['title'][0]; ?>
			    				</div>
			    				<div class="description">
		    	    				<?php echo $plan['description'][0]; ?>
		    	    			</div>
		    	    			<div class="featured-options">
		    	    			<?php if( _va_no_featured_available( $plan, $listing ) ) { ?>	    			
		    	    				<div class="option-header">
		    	    					<?php _e( 'Featured Listings are not available for this price plan.', APP_TD ); ?>
		    	    				</div>		    	    				
		    	    			<?php } else { ?>
		    	    			
		    	    				<div class="option-header">
		    	    					<?php _e( 'Please choose additional featured options:', APP_TD ); ?>
		    	    				</div>
		    	    				<?php foreach ( array( VA_ITEM_FEATURED_HOME, VA_ITEM_FEATURED_CAT ) as $addon ) : ?>
										<div class="featured-option"><label>
											<?php if( ! empty( $plan[ $addon ][0] ) ){ ?>
												<?php _va_show_featured_option( $addon, true, $plan['post_data']->ID ); ?>
												<?php if ( $plan[$addon.'_duration'][0] != 0 ) { ?>
												<?php printf( _n( '%s is included in this plan for %d day.', '%s is included in this plan for %d days.', $plan[$addon.'_duration'][0], APP_TD ), APP_Item_Registry::get_title( $addon ), $plan[$addon.'_duration'][0] ); ?>
												<?php } else { ?>
												<?php printf( __( '%s is included in this plan for Unlimited days.'), APP_Item_Registry::get_title( $addon ) ); ?>												
												<?php } ?>
											<?php }else{ ?>
												<?php _va_show_featured_addon( $addon, $listing->ID, $plan['post_data']->ID ); ?>
											<?php } ?>
										</label></div>
									<?php endforeach; ?>
		    	    			
		    	    			<?php } ?>
		    	    			</div>
		    	    		</div>
		    	    		<div class="price-box">
		    	    			<div class="price">
		    	    				<?php echo APP_Currencies::get_price($plan['price'][0]); ?>
		    	    			</div>
		    	    			<div class="duration">
		    	    				<?php if( $plan['duration'][0] != 0 ){ ?>
		    	    					<?php printf( _n( 'for <br /> %s day', 'for <br /> %s days', $plan['duration'][0], APP_TD ), $plan['duration'][0] ); ?>
		    	    				<?php }else{ ?>
		    	    					<?php _e( 'Unlimited</br> days', APP_TD ); ?>
		    	    				<?php } ?>
		    	    			</div>
			    				<div class="radio-button">
			    					<label>
			    						<input name="plan" type="radio" <?php echo ($key == 0) ? 'checked="checked"' : ''; ?> value="<?php echo $plan['post_data']->ID; ?>" />
			    						<?php _e( 'Choose this option', APP_TD ); ?>
			    					</label>
			    				</div>
			    			</div>
			    		</div>
			    	<?php } ?>
			    <?php } else { ?>
			    	<em><?php _e( 'No Plans are currently available for this category. Please come back later.', APP_TD ); ?></em>
			    <?php } ?>
		    </div>
		</fieldset>
		<fieldset>
			<?php do_action( 'va_after_purchase_listing_new_form', $listing ); ?>	 
			<?php do_action( 'app_purchase_fields' ); ?>	 
			<input type="hidden" name="action" value="purchase-listing">
			<input type="hidden" name="ID" value="<?php echo $listing->ID; ?>">
			<div classess="form-field">
				<?php if( !empty( $plans ) ){ ?>
					<input type="submit" value="<?php _e( 'Continue', APP_TD ) ?>" />
				<?php } ?>
			</div>
		</fieldset>
	</form>
</div>