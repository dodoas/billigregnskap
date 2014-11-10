<?php global $va_options; ?>
<div id="main">

<?php do_action( 'appthemes_notices' ); ?>

<div class="section-head">
	<h1><?php echo $title; ?></h1>
</div>

<form id="create-listing" enctype="multipart/form-data" method="post" action="<?php echo $form_action; ?>">
	<?php wp_nonce_field( 'va_create_listing' ); ?>
	<input type="hidden" name="action" value="<?php echo ( get_query_var('listing_edit') ? 'edit-listing' : 'new-listing' ); ?>" />
	<input type="hidden" name="ID" value="<?php echo esc_attr( $listing->ID ); ?>" />

<fieldset id="essential-fields">
	<div class="featured-head"><h3><?php _e( 'Essential info', APP_TD ); ?></h3></div>

	<div class="form-field"><label>
		<?php _e( 'Title', APP_TD ); ?>
		<input name="post_title" type="text" value="<?php echo esc_attr( $listing->post_title ); ?>" class="required" />
	</label></div>

	<div class="form-field">
		<?php $coord = appthemes_get_coordinates( $listing->ID ); ?>
		<input name="lat" type="hidden" value="<?php echo esc_attr( $coord->lat ); ?>" />
		<input name="lng" type="hidden" value="<?php echo esc_attr( $coord->lng ); ?>" />

		<label>
			<?php _e( 'Address (street nr., street, city, state, country)', APP_TD ); ?>
			<input id="listing-address" name="address" type="text" value="<?php echo esc_attr( $listing->address ); ?>" class="required" />
		</label>
		<input id="listing-find-on-map" type="button" value="<?php esc_attr_e( 'Find on map', APP_TD ); ?>">

		<div id="listing-map"></div>
	</div>
</fieldset>

<?php
	// if categories are locked display only the current listing category
	if ( va_categories_locked() )
		$listing_cat = $listing->category;
	else
		$listing_cat = array();
?>

<fieldset id="category-fields">
	<div class="featured-head"><h3><?php _e( 'Listing type', APP_TD ); ?></h3></div>

	<div class="form-field"><label>
		<?php _e( 'Category', APP_TD ); ?>
		<?php wp_dropdown_categories( array(
			'taxonomy' => VA_LISTING_CATEGORY,
			'hide_empty' => false,
			'hierarchical' => true,
			'name' => '_'.VA_LISTING_CATEGORY,
			'selected' => $listing->category,
			'show_option_none' => __( 'Select Category', APP_TD ),
			'class' => 'required',
			'orderby' => 'name',
			'include' => $listing_cat
		) ); ?>
	</label></div>


<div id="custom-fields">
<?php
	if ( $listing->category ) {
		the_listing_files_editor( $listing->ID );

		va_render_form( (int) $listing->category, $listing->ID );
	}
?>
</div>

</fieldset>

<fieldset id="contact-fields">
	<div class="featured-head"><h3><?php _e( 'Contact info', APP_TD ); ?></h3></div>

	<div class="form-field phone"><label>
		<?php _e( 'Phone Number', APP_TD ); ?>
		<input name="phone" type="text" value="<?php echo esc_attr( $listing->phone ); ?>" />
	</label></div>

	<div class="form-field listing-urls web">
		<label>
			<?php _e( 'Website', APP_TD ); ?><br />
			<span>http://</span><input name="website" type="text" value="<?php echo esc_attr( $listing->website ); ?>" />
		</label>
    </div>

    <div class="form-field listing-urls twitter">
		<label>
			<?php _e( 'Twitter', APP_TD ); ?>
			<span>@</span><input name="twitter" type="text" value="<?php echo esc_attr( $listing->twitter ); ?>" />
		</label>
    </div>

    <div class="form-field listing-urls facebook">
		<label>
			<?php _e( 'Facebook', APP_TD ); ?>
			<span>facebook.com/</span><input name="facebook" type="text" value="<?php echo esc_attr( $listing->facebook ); ?>" />
		</label>
	</div>
</fieldset>

<fieldset id="misc-fields">
	<div class="featured-head"><h3><?php _e( 'Additional info', APP_TD ); ?></h3></div>

	<div class="form-field images">
		<?php _e( 'Listing Images', APP_TD ); ?>
		<?php the_listing_image_editor( $listing->ID ); ?>
	</div>

	<div class="form-field"><label>
		<?php _e( 'Business Description', APP_TD ); ?>
		<textarea name="post_content"><?php echo esc_textarea( $listing->post_content ); ?></textarea>
	</label></div>

	<div class="form-field"><label>
		<?php _e( 'Tags', APP_TD ); ?>
		<input name="tax_input[<?php echo VA_LISTING_TAG; ?>]" type="text" value="<?php the_listing_tags_to_edit( $listing->ID ); ?>" />
	</label></div>
</fieldset>

<?php do_action( 'va_after_create_listing_form' ); ?>

<fieldset>
	<div class="form-field"><input type="submit" value="<?php echo esc_attr( $action ); ?>" /></div>
</fieldset>

</form>

</div><!-- #content -->
