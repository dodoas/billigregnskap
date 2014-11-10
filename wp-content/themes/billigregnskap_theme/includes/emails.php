<?php

add_action( 'appthemes_transaction_completed', 'va_send_receipt' );
add_action( 'transition_post_status', 'va_new_listing_notification', 10, 3 );


function va_new_listing_notification( $new_status, $old_status, $post ) {
	if ( VA_LISTING_PTYPE != $post->post_type )
		return;

	if ( 'pending' == $new_status && 'publish' != $old_status )
		va_send_pending_listing_notification( $post );

	elseif ( 'publish' == $new_status && 'pending' == $old_status )
		va_send_approved_notification( $post );

	elseif ( 'expired' == $new_status && 'publish' == $old_status )
		va_send_expired_notification( $post );
	
	elseif ( 'publish' == $new_status && 'pending-claimed' == $old_status ) {
		$claimable = get_post_meta( $post->ID, 'listing_claimable', true );
		if ( !empty( $claimable ) ) {
			va_send_rejected_claim_notification( $post );
		} else {
			va_send_approved_claim_notification( $post );
		}
	} elseif ( 'pending-claimed' == $new_status ) {
		va_send_pending_claimed_listing_notification( $post );
	}
	
}

function va_send_receipt( $order ) {
	global $va_options;

	$recipient = get_user_by( 'id', $order->get_author() );

	$item = '';
	foreach ( $order->get_items() as $item ) {
		$item = html( 'p', html_link( get_permalink( $item['post']->ID ), $item['post']->post_title ) );
		break;
	}
	
	$table = new APP_Order_Summary_Table( $order );
	ob_start();
	$table->show();
	$table_output = ob_get_clean();
		
	$content = '';
	$content .= html( 'p', sprintf( __( 'Hello %s,', APP_TD ), $recipient->display_name ) );
	$content .= html( 'p', __( 'This email confirms that you have purchased the following listing:', APP_TD ) );
	$content .= $item;
	$content .= html( 'p', __( 'Order Summary:', APP_TD ) );
	$content .= $table_output;

	$subject = sprintf( __( '[%s] Receipt for your order', APP_TD ), get_bloginfo( 'name' ) );

	va_send_email( $recipient->user_email, $subject, $content );
}

function va_send_pending_listing_notification( $post ) {
	$content = '';

	$content .= html( 'p', sprintf(
		__( 'A new listing is awaiting moderation: %s', APP_TD ),
		html_link( get_permalink( $post ), $post->post_title ) ) );

	$content .= html( 'p', html_link(
		admin_url( 'edit.php?post_status=pending&post_type=listing' ),
		__( 'Review pending listings', APP_TD ) ) );

	$subject = sprintf( __( '[%s] Pending Listing: "%s"', APP_TD ), get_bloginfo( 'name' ), $post->post_title );

	va_send_email( get_option( 'admin_email' ), $subject, $content );
}

function va_send_pending_claimed_listing_notification( $post ) {
	$content = '';

	$content .= html( 'p', sprintf(
		__( 'A new listing claim is awaiting moderation: %s', APP_TD ),
		html_link( get_permalink( $post ), $post->post_title ) ) );

	$content .= html( 'p', html_link(
		admin_url( 'edit.php?post_status=pending-claimed&post_type=listing' ),
		__( 'Review pending claimed listings', APP_TD ) ) );

	$subject = sprintf( __( '[%s] Pending Claimed Listing: "%s"', APP_TD ), get_bloginfo( 'name' ), $post->post_title );

	va_send_email( get_option( 'admin_email' ), $subject, $content );
}


function va_send_approved_notification( $post ) {
	$recipient = get_user_by( 'id', $post->post_author );

	$content = '';

	$content .= html( 'p', sprintf( __( 'Hello %s,', APP_TD ), $recipient->display_name ) );

	$content .= html( 'p', sprintf(
		__( 'Your "%s" listing has been approved.', APP_TD ),
		html_link( get_permalink( $post ), $post->post_title )
	) );

	$subject = sprintf( __( '[%s] Listing Approved: "%s"', APP_TD ), get_bloginfo( 'name' ), $post->post_title );

	va_send_email( $recipient->user_email, $subject, $content );
}


function va_send_approved_claim_notification( $post ) {
	$recipient_id = get_post_meta( $post->ID, 'claimee', true );
	$recipient = get_user_by( 'id', $recipient_id );

	$content = '';

	$content .= html( 'p', sprintf( __( 'Hello %s,', APP_TD ), $recipient->display_name ) );

	$content .= html( 'p', sprintf(
		__( 'Your "%s" listing claim has been approved.', APP_TD ),
		html_link( get_permalink( $post ), $post->post_title )
	) );

	$subject = sprintf( __( '[%s] Claimed Listing Approved: "%s"', APP_TD ), get_bloginfo( 'name' ), $post->post_title );

	va_send_email( $recipient->user_email, $subject, $content );
}

function va_send_rejected_claim_notification( $post ) {
	$recipient_id = get_post_meta( $post->ID, 'rejected_claimee', true );
	$recipient = get_user_by( 'id', $recipient_id );

	$content = '';

	$content .= html( 'p', sprintf( __( 'Hello %s,', APP_TD ), $recipient->display_name ) );

	$content .= html( 'p', sprintf(
		__( 'Your "%s" listing claim has been denied.', APP_TD ),
		html_link( get_permalink( $post ), $post->post_title )
	) );

	$subject = sprintf( __( '[%s] Listing Claim Denied: "%s"', APP_TD ), get_bloginfo( 'name' ), $post->post_title );

	va_send_email( $recipient->user_email, $subject, $content );
}

function va_send_expired_notification( $post ) {
	$recipient = get_user_by( 'id', $post->post_author );

	$content = '';

	$content .= html( 'p', sprintf( __( 'Hello %s,', APP_TD ), $recipient->display_name) );

	$content .= html( 'p', sprintf(
		__( 'Your "%s" listing has expired (it is not visible to the public anymore).', APP_TD ),
		html_link( get_permalink( $post ), $post->post_title )
	) );

	$content .= html( 'p', html_link( va_get_listing_edit_url( $post->ID ), __( 'Renew listing!', APP_TD ) ) );

	$subject = sprintf( __( '[%s] Listing Expired: "%s"', APP_TD ), get_bloginfo( 'name' ), $post->post_title );

	va_send_email( $recipient->user_email, $subject, $content );
}


function va_send_email( $address, $subject, $content ) {
	$domain = preg_replace( '#^www\.#', '', strtolower( $_SERVER['SERVER_NAME'] ) );

	$headers['from']     = sprintf( 'From: %1$s <%2$s>', get_bloginfo('name'), "wordpress@$domain" );
	$headers['mime']     = 'MIME-Version: 1.0';
	$headers['type']     = 'Content-Type: text/html; charset="' . get_bloginfo( 'charset' ) . '"';
	$headers['reply_to'] = "Reply-To: noreply@$domain";

	ob_start();
	require dirname( __FILE__ ) . '/email-template.php';
	$body = ob_get_clean();

	wp_mail( $address, $subject, $body, implode( "\n", $headers ) );
}

