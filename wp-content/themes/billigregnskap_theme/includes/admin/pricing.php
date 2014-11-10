<?php

class VA_Pricing_General_Box extends APP_Meta_Box{

	public function __construct(){
		parent::__construct( 'pricing-details', __( 'Pricing Details', APP_TD ), APPTHEMES_PRICE_PLAN_PTYPE, 'normal', 'high' );
	}
	
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'form-builder-helper', get_template_directory_uri() . '/framework/custom-forms/form-builder-helper.js', array( 'jquery' ), '20110909' );
	}
	
	public function before_form(){
		?><style type="text/css">#notice{ display: none; }</style><?php
	}

	public function form(){
		return array(
			array(
				'title' => __( 'Plan Name', APP_TD ),
				'type' => 'text',
				'name' => 'title',
			),
			array(
				'title' => __( 'Description', APP_TD ),
				'type' => 'textarea',
				'name' => 'description',
				'extra' => array(
					'style' => 'width: 25em;'
				)
			),
			array(
				'title' => __( 'Price', APP_TD ),
				'type' => 'text',
				'name' => 'price',
				'desc' => sprintf( __( 'Example: %s ' , APP_TD ), '1.00' ),
				'extra' => array(
					'style' => 'width: 50px;'
				)
			),
			array(
				'title' => __( 'Listing Duration', APP_TD ),
				'type' => 'text',
				'name' => 'duration',
				'desc' => __( 'days ( 0 = Infinite )', APP_TD),
				'extra' => array(
					'style' => 'width: 50px;'
				)
			),
		);
	}

	public function validate_post_data( $data ){

		$errors = new WP_Error();

		if( empty( $data['title'] ) ){
			$errors->add( 'title', '' );
		}

		if( !is_numeric( $data['price'] ) ){
			$errors->add( 'price', '' );
		}

		if( !is_numeric( $data['duration'] ) ){
			$errors->add( 'duration', '' );
		}

		return $errors;

	}

	public function post_updated_messages( $messages ) {
		$messages[ APPTHEMES_PRICE_PLAN_PTYPE ] = array(
		 	1 => __( 'Plan updated.', APP_TD ),
		 	4 => __( 'Plan updated.', APP_TD ),
		 	6 => __( 'Plan created.', APP_TD ),
		 	7 => __( 'Plan saved.', APP_TD ),
		 	9 => __( 'Plan scheduled.', APP_TD ),
			10 => __( 'Plan draft updated.'),
		);
		return $messages;
	}

}

class VA_Pricing_Addon_Box extends APP_Meta_Box{

	public function __construct(){
		parent::__construct( 'pricing-addons', __( 'Featured Addons', APP_TD ), APPTHEMES_PRICE_PLAN_PTYPE, 'normal', 'high' );
	}

	public function form(){

		$output = array(
		);

		foreach( array( VA_ITEM_FEATURED_CAT, VA_ITEM_FEATURED_HOME ) as $addon ){

			$enabled = array(
				'title' => APP_Item_Registry::get_title( $addon ),
				'type' => 'checkbox',
				'name' => $addon,
				'desc' => __( 'Included', APP_TD ),
			);

			$duration = array(
				'title' => __( 'Duration', APP_TD ),
				'type' => 'text',
				'name' => $addon . '_duration',
				'desc' => __( 'days', APP_TD ),
				'extra' => array(
					'size' => '3'
				),
			);

			$output[] = $enabled;
			$output[] = $duration;

		}

		return $output;
	
	}

	public function before_save( $data, $post_id ){

		foreach( array( VA_ITEM_FEATURED_CAT, VA_ITEM_FEATURED_HOME ) as $addon ){

			if( !empty( $data[ $addon ] ) && empty( $data[ $addon . '_duration' ] ) ){
				$data[ $addon . '_duration' ] = get_post_meta( $post_id, 'duration', true );
			}

		}

		return $data;
	}

	public function validate_post_data( $data, $post_id ){
		$errors = new WP_Error();

		$listing_duration = intval( get_post_meta( $post_id, 'duration', true ) );
		foreach( array( VA_ITEM_FEATURED_CAT, VA_ITEM_FEATURED_HOME ) as $addon ){

			if( !empty( $data[ $addon . '_duration' ] ) ){

				$addon_duration = $data[ $addon . '_duration' ];
				if( !is_numeric( $addon_duration ) )
					$errors->add( $addon . '_duration', '' );

				if( intval( $addon_duration ) > $listing_duration && $listing_duration != 0 )
					$errors->add( $addon . '_duration', '' );

			}

		}

		return $errors;
	}

	public function before_form(){
		echo html( 'p', array(), __( 'You can include featured addons in a plan. These will be immediately added to the listing upon purchase. After they run out, the customer can then purchase regular featured addons.', APP_TD ) );
	}


	public function after_form(){
		echo html( 'p', array('class' => 'howto'), __( 'Durations must be shorter than the listing duration.', APP_TD ) );
	}
	
}

