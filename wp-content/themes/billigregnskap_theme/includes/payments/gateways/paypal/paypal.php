<?php

/**
 * Payment Gateway to process PayPal Payments
 */
class APP_PayPal extends APP_Boomerang{

	/**
	 * API URLs to connect to
	 * @var array
	 */
	private $post_urls = array(
		'sandbox' => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
		'live' => 'https://www.paypal.com/cgi-bin/webscr'
	);

	/**
	 * Sets up the gateway
	 */
	public function __construct() {
		parent::__construct( 'paypal', array(
			'dropdown' => __( 'PayPal', APP_TD ),
			'admin' => __( 'PayPal', APP_TD )
		) );
	}

	/**
	 * Processes an Order Payment
	 * See APP_Gateway::process()
	 * @param  APP_Order $order   Order to process
	 * @param  array $options     User inputted options
	 * @return void
	 */
	public function process( $order, $options ) {

		// If available, use PDT as the validator
		APP_Paypal_PDT::init( $options );
		if( APP_Paypal_PDT::is_enabled() ){
			$this->handle_pdt( $order, $options );
			return;
		}

		// Otherwise, validate regularly
		if( $this->is_returning() )
			$this->complete_order( $order );
		else
			$this->create_form( $order, $options );

	}

	/**
	 * Handles processing when PDT is enabled
	 * @param  APP_Order $order   Order to process
	 * @param  array $options     User inputted options
	 * @return void  
	 */
	private function handle_pdt( $order, $options ){

		// Check if it looks like a PDT transaction
		if( APP_Paypal_PDT::can_be_handled() ){

			if( APP_Paypal_PDT::is_valid_transaction( $order, $options ) )
				$this->complete_order( $order );
			else
				$this->fail_order( __( 'PayPal has responded to your transaction as invalid. Please contact site owner.', APP_TD ) );

		}

		// Otherwise send to Paypal
		else
			$this->create_form( $order, $options );

	}

	/**
	 * Displays the form for user redirection
	 * @param  APP_Order $order   Order to process
	 * @param  array $options     User inputted options
	 * @return void  
	 */
	public function create_form( $order, $options ) {
		
		$defaults = array(
			'email_address' => '',
			'currency_code' => 'USD',
			'sandbox_enabled' => false
		);

		$options = wp_parse_args( $options, $defaults );

		$fields = array(

			// No Shipping Required
			'noshipping' => 1,

			// Disable the 'Add Note' Paypal Capability
			'no_note' => 1,

			// Return the buyer to our website via POST, and include variables
			'rm' => 0,

			// Use the 'Buy Now' button as the method of purchase
			'cmd' => '_xclick',

			'charset' => 'utf-8',
		);

		// Item Information
		$fields['item_name'] = $order->get_description();
		$fields['item_number'] = $order->get_id();

		// Seller Options
		$fields['business'] = $options['email_address'];
		$fields['currency_code'] = APP_Gateway_Registry::get_options()->currency_code;

		// Paypal Options
		$fields['cbt'] = sprintf( __( 'Continue to %s', APP_TD ), get_bloginfo( 'name' ) );

		$site = !empty( $options['sandbox_enabled'] ) ? 'sandbox' : 'live';
		$post_url = $this->post_urls[ $site ];

		$fields['amount'] = $order->get_total();
		$fields['return'] = $this->get_return_url( $order );
		$fields['cancel_return'] = $this->get_cancel_url( $order );
		
		$form = array(
			'action' => $post_url,
			'name' => 'paypal_payform',
			'id' => 'create_listing',
		);

		$this->redirect( $form, $fields, __( 'You are now being redirected to PayPal.', APP_TD ) );

	}

	/**
	 * Returns an array for the administrative settings
	 * See APP_Gateway::form()
	 * @return array scbForms style inputs
	 */
	public function form() {

		$general = array(
			'title' => __( 'General Information', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'PayPal Email', APP_TD ),
					'tip' => __( 'Enter your PayPal account email address. This is where your money gets sent.', APP_TD ),
					'type' => 'text',
					'name' => 'email_address',
					'extra' => array( 'size' => 50 )
				),
				array(
					'title' => __( 'Sandbox Mode', APP_TD ),
					'desc' => sprintf( __( "You must have a <a target='_new' href='%s'>PayPal Sandbox</a> account setup before using this feature.", APP_TD ), 'http://developer.paypal.com/' ),
					'tip' => __( 'By default PayPal is set to live mode. If you would like to test and see if payments are being processed correctly, check this box to switch to sandbox mode.', APP_TD ),
					'type' => 'checkbox',
					'name' => 'sandbox_enabled'
				)
			)
		);

		$pdt = array(
				'title' => __( 'Payment Data Transfer (PDT)', APP_TD ),
				'fields' => array(
					array(
						'title' => __( 'Enable PDT', APP_TD ),
						'desc' => sprintf( __( 'See our <a href="%s">tutorial</a> on enabling Payment Data Transfer.', APP_TD ), 'http://docs.appthemes.com/tutorials/enable-paypal-pdt-payment-data-transfer/' ),
						'type' => 'checkbox',
						'name' => 'pdt_enabled'
					),
					array(
						'title' => __( 'Identity Token', APP_TD ),
						'type' => 'text',
						'name' => 'pdt_key'
					),
				)
		);

		return array( $general, $pdt );

	}

	/**
	 * Checks if the current request can be handled
	 * @return bool True if it can be processed, false if not
	 */
	private function can_be_handled(){
		return isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'paypal');
	}

}
appthemes_register_gateway( 'APP_PayPal' );
