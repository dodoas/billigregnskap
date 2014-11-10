<?php

/**
 * Defines the Payments Settings Administration Panel
 */
class APP_Payments_Settings_Admin extends APP_Tabs_Page {

	/**
	 * Sets up the page
	 * @return void
	 */
	function setup() {
		$this->textdomain = APP_TD;

		$this->args = array(
			'page_title' => __( 'Payments Settings', APP_TD ),
			'menu_title' => __( 'Settings', APP_TD ),
			'page_slug' => 'app-payments-settings',
			'parent' => 'app-payments',
			'screen_icon' => 'options-general',
			'admin_action_priority' => 11,
		);

	}

	/**
	 * Creates the tabs for the page
	 * @return void
	 */
	protected function init_tabs() {
		$this->tabs->add( 'general', __( 'General', APP_TD ) );

		$this->tab_sections['general']['currency'] = array(
			'title' => __( 'Currencies', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Currency Selection', APP_TD ),
					'type' => 'select',
					'name' => 'currency_code',
					'values' => APP_Currencies::get_currency_string_array(),
				),
			)
		);

		$this->tab_sections['general']['gateways'] = array(
			'title' => __( 'Installed Gateways', APP_TD ),
			'fields' => array(),
		);

		$gateways = APP_Gateway_Registry::get_gateways();
		foreach ( $gateways as $gateway ) {
			$this->tab_sections['general']['gateways']['fields'][] = $this->load_gateway_tabs( $gateway );
		}

		add_action( 'admin_notices', array( $this, 'disabled_gateway_warning' ) );
	}

	/**
	 * Displays notices if a gateway is disabled
	 * @return void
	 */
	function disabled_gateway_warning() {
		if ( isset( $_GET['tab'] ) ) {
			$gateway_id = $_GET['tab'];

			if ( APP_Gateway_Registry::is_gateway_registered( $gateway_id ) && !APP_Gateway_Registry::is_gateway_enabled( $gateway_id ) ) {
				$this->admin_msg( __( 'This gateway is currently <strong>disabled</strong>. Users cannot use it as a purchasing option. Go to the <a href="?page=app-payments-settings">General</a> tab to enable it.', APP_TD ) );
			}
		}
	}

	/**
	 * Loads the gateway form fields into tabs
	 * @param  string $gateway Gateway identifier
	 * @return array           Array for the checkbox to enable the gateway
	 */
	function load_gateway_tabs( $gateway ){

		$form_values = $gateway->form();
		$nicename = $gateway->identifier();

		if( array_key_exists( 'fields', $form_values ) ){

			// Wrap values
			foreach ( $form_values['fields'] as $key => $block ) {

				$value = $block['name'];
				$form_values['fields'][$key]['name'] = array( 'gateways', $nicename, $value );

			}

			$this->tab_sections[ $nicename ][ 'general_settings' ] = $form_values;
		}else{

			// Wrap values
			foreach ( $form_values as $s_key => $section ){
				foreach ( $section['fields'] as $key => $block ) {

					$value = $block['name'];
					$form_values[$s_key]['fields'][$key]['name'] = array( 'gateways', $nicename, $value );

				}
			}

			$this->tab_sections[ $nicename ] = $form_values;
		}

		// Only add a tab for gateways with a form
		$title = $gateway->display_name( 'admin' );
		if( $form_values ){
			$this->tabs->add( $nicename, $title );
			$title = html_link( add_query_arg( array(
				'page' => $this->args['page_slug'],
				'tab' => $nicename
			), 'admin.php' ), $title );
		}

		return array(
			'title' => $title,
			'type' => 'checkbox',
			'desc' => __( 'Enable', APP_TD ),
			'name' => array( 'gateways', 'enabled', $nicename ),
		);

	}
}

