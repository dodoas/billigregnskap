<div id="sidebar">
	<aside id="dashboard-side-nav">

		<div class="avatar">
			<?php if ( $is_own_dashboard ) {
				echo html_link( va_get_the_author_listings_url( $dashboard_user->ID, true ), get_avatar( $dashboard_user->ID, 45 ) );
			 } else {
				 echo html_link( va_get_the_author_listings_url( $dashboard_user->ID ), get_avatar( $dashboard_user->ID, 45 ) );
			 }
			?>
		</div>
		<div class="user_meta">
			<p><?php $is_own_dashboard ? _e( 'Welcome, ' , APP_TD ) : ''; ?><b><?php echo $dashboard_user->display_name; ?></b></p>
			<p class="smaller"><?php _e( 'Member Since: ', APP_TD ); ?><?php echo mysql2date( get_option('date_format'), $dashboard_user->user_registered ); ?></p>
		</div>
		<ul class="links">
			<li class="faves"><?php
			if ( $is_own_dashboard ) {
			echo html_link( va_get_the_author_faves_url( $dashboard_user->ID, true ), __( 'View Favorites', APP_TD ) );
			} else {
			echo html_link( va_get_the_author_faves_url( $dashboard_user->ID ), __( 'View Favorites', APP_TD ) );
			}
			?></li>
			<li class="reviews"><?php
			if ( $is_own_dashboard ) {
			echo html_link( va_get_the_author_reviews_url( $dashboard_user->ID, true ), __( 'View Reviews', APP_TD ) );
			} else {
			echo html_link( va_get_the_author_reviews_url( $dashboard_user->ID ), __( 'View Reviews', APP_TD ) );
			}
			?></li>
			<li class="view-listings"><?php
			if ( $is_own_dashboard ) {
			echo html_link( va_get_the_author_listings_url( $dashboard_user->ID, true ), __( 'View Listings', APP_TD ) );
			} else {
			echo html_link( va_get_the_author_listings_url( $dashboard_user->ID ), __( 'View Listings', APP_TD ) );
			}
			?></li>
			<?php if ( $is_own_dashboard ) { ?>
			<li class="edit-profile"><?php echo html_link( appthemes_get_edit_profile_url(), __( 'Edit Profile', APP_TD ) ); ?></li>
			<li class="add-listings"><?php echo html_link( va_get_listing_create_url(), __( 'New Listing', APP_TD ) ); ?></li>
			<?php if ( $dashboard_user->has_claimed ) { ?>
				<li class="claimed-listings"><?php echo html_link( va_get_claimed_listings_url(), __( 'Claimed Listings', APP_TD ) ); ?></li>
			<?php } ?>
			<?php } ?>
		</ul>

	</aside>

	<?php if ( va_dashboard_show_account_info( $dashboard_user, $is_own_dashboard ) ) { ?>

	<aside id="dashboard-acct-info">
		<div class="section-head">
			<h3><?php _e( 'Account Information', APP_TD ); ?></h3>
		</div>

		<ul class="links">
			<?php if ( $is_own_dashboard || !empty( $dashboard_user->email_public ) ) { ?>
			<li class="email"><a href="mailto:<?php echo $dashboard_user->user_email; ?>"><?php echo $dashboard_user->user_email; ?></a></li>
			<?php } ?>
			<?php if ( !empty( $dashboard_user->twitter ) && $is_own_dashboard || !empty( $dashboard_user->twitter ) ) { ?>
			<li class="twitter"><a href="<?php echo $dashboard_user->twitter; ?>" target="_blank"><?php echo $dashboard_user->twitter; ?></a></li>
			<?php } ?>
			<?php if ( !empty( $dashboard_user->facebook ) && $is_own_dashboard || !empty( $dashboard_user->facebook ) ) { ?>
			<li class="facebook"><a href="<?php echo $dashboard_user->facebook; ?>" target="_blank"><?php echo $dashboard_user->facebook; ?></a></li>
			<?php } ?>
			<?php if ( !empty( $dashboard_user->user_url ) && $is_own_dashboard || !empty( $dashboard_user->user_url ) ) { ?>
			<li class="website"><a href="<?php echo $dashboard_user->user_url; ?>" target="_blank"><?php echo $dashboard_user->user_url; ?></a></li>
			<?php } ?>
		</ul>

	</aside>

	<?php } ?>
	<aside id="dashboard-acct-stats">
		<div class="section-head">
			<h3><?php _e( 'Account Statistics', APP_TD ); ?></h3>
		</div>
		<?php
		$stats = va_dashboard_get_user_stats($dashboard_user);
		?>
		<ul class="stats">
			<li><?php printf( __('Live Listings: %d', APP_TD ), $stats['listings_live'] ); ?></li>
			<li><?php printf( __('Pending Listings: %d', APP_TD ), $stats['listings_pending'] ); ?></li>
			<li><?php printf( __('Expired Listings: %d', APP_TD ), $stats['listings_expired'] ); ?></li>
			<li><?php printf( __('Total Listings: %d', APP_TD ), $stats['listings_total'] ); ?></li>
		</ul>
		<ul class="stats">
			<li><?php printf( __('Live Reviews: %d', APP_TD ), $stats['reviews_live'] ); ?></li>
			<li><?php printf( __('Pending Reviews: %d', APP_TD ), $stats['reviews_pending'] ); ?></li>
			<li><?php printf( __('Total Reviews: %d', APP_TD ), $stats['reviews_total'] ); ?></li>
		</ul>

	</aside>
</div>