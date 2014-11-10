<?php

add_action('admin_init', 'va_featured_setup_tab_init');

function va_featured_setup_tab_init() {
	global $admin_page_hooks;

	add_action( 'tabs_'.$admin_page_hooks['app-payments'].'_page_app-payments-settings', array( 'VA_Featured_Settings_Tab', 'init' ) );
}

class VA_Featured_Settings_Tab {

	private static $page;

	static function init( $page ) {
		self::$page = $page;

		$fields = array();

		foreach ( array( VA_ITEM_FEATURED_HOME, VA_ITEM_FEATURED_CAT ) as $addon ) {
			$fields = array_merge( $fields, self::generate_fields( $addon ) );
		}

		$page->tab_sections['general']['featured'] = array(
			'title' => __( 'Listing Add-ons', APP_TD ),
			'renderer' => array( __CLASS__, 'render' ),
			'fields' => $fields
		);
	}

	static function render( $section ) {
		$columns = array(
			'type' => __( 'Type', APP_TD ),
			'enabled' => __( 'Enabled', APP_TD ),
			'price' => __( 'Price', APP_TD ),
			'duration' => __( 'Duration', APP_TD ),
		);

		$header = '';
		foreach ( $columns as $key => $label )
			$header .= html( 'th', $label );

		$rows = '';
		foreach ( array( VA_ITEM_FEATURED_HOME, VA_ITEM_FEATURED_CAT ) as $addon ) {
			$row = html( 'td', APP_Item_Registry::get_title( $addon ) );

			foreach ( self::generate_fields( $addon ) as $field )
				$row .= html( 'td', self::$page->input( $field ) );

			$rows .= html( 'tr', $row );
		}

		echo html( 'table id="featured-pricing" class="widefat"', html( 'tr', $header ), html( 'tbody', $rows ) );
	}

	private static function generate_fields( $addon ) {
		return array(
			array(
				'type' => 'checkbox',
				'name' => array( 'addons', $addon, 'enabled' ),
				'desc' => __( 'Yes', APP_TD ),
			),
			array(
				'type' => 'text',
				'name' => array( 'addons', $addon, 'price' ),
				'sanitize' => 'absint',
				'extra' => array( 'size' => 3 ),
			),
			array(
				'type' => 'text',
				'name' => array( 'addons', $addon, 'duration' ),
				'sanitize' => 'absint',
				'extra' => array( 'size' => 3 ),
				'desc' => __( 'days', APP_TD )
			),
		);
	}
}

