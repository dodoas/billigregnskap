<?php

class APP_Login extends APP_View_Page {

	private $error;

	function __construct( $template ) {
		parent::__construct( $template, __('Login', APP_TD) );
		add_filter( 'login_url', array( $this, '_change_login_url' ), 10, 2 );
		add_filter( 'register', array( $this, 'register_link') );
	}

	function _change_login_url( $url, $redirect_to ) {
		return APP_Login::get_url( 'redirect', $redirect_to );
	}

	function init() {
		global $pagenow;

		if($pagenow == 'wp-login.php' && ( empty($_GET['action']) || !in_array($_GET['action'], array('logout', 'lostpassword', 'retrievepassword', 'rp', 'resetpass', 'register') ) ) && self::get_id() ) {

			$continue = true;
			$continue = apply_filters('app_login_pre_redirect', $continue);
			if( false !== $continue ) {
				wp_redirect(APP_Login::get_url('redirect'));
				exit();
			}
		}
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}

	function register_link($link) {
		$new_url = appthemes_get_registration_url();
		
		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$new_url = add_query_arg('redirect_to', urlencode( $_REQUEST['redirect_to'] ), $new_url );
		}
		
		$link = str_ireplace( site_url('wp-login.php?action=register'), $new_url, $link );
		
		return $link;
	}

	static function redirect_field() {
		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$redirect = $_REQUEST['redirect_to'];
		} else {
			$redirect = home_url();
		}

		return html( 'input', array(
			'type' => 'hidden',
			'name' => 'redirect_to',
			'value' => $redirect
		) );
	}

	static function get_url( $context = 'display', $redirect_to = '' ) {
		$args = wp_array_slice_assoc( $_GET, array( 'checkemail', 'registration', 'loggedout' ) );

		if ( !empty( $redirect_to ) )
			$args['redirect_to'] = urlencode( $redirect_to );

		if ( $page_id = self::get_id() ) {
			$permalink = get_permalink( $page_id );
		} else {
			$permalink = site_url( 'wp-login.php' );
		}

		return esc_url( add_query_arg( $args, $permalink ), null, $context );
	}

	function template_redirect() {
		$this->process_form();
	}

	function process_form() {

		if ( is_user_logged_in() ) {
			do_action('app_login');
		}

		if ( empty( $_POST['login'] ) ) return;

		if ( isset( $_REQUEST['redirect_to'] ) )
			$redirect_to = $_REQUEST['redirect_to'];
		else
			$redirect_to = admin_url('index.php');

		if ( is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
			$secure_cookie = false;
		else
			$secure_cookie = '';

		$user = wp_signon('', $secure_cookie);

		$redirect_to = apply_filters('login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

		if ( !is_wp_error($user) ) {
			wp_safe_redirect($redirect_to);
			exit;
		}

		$this->error = $user;
	}

	function notices() {
		$message = '';

		if ( !isset( $this->error ) || !empty($_GET['loggedout']) )
			$this->error = new WP_Error;

		if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) ) {
			$this->error->add('test_cookie', __('Cookies are blocked or not supported by your browser. You must enable cookies to continue.', APP_TD));
		}

		if ( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] ) {
			$message = __('You are now logged out.', APP_TD);

		} elseif ( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )	{
			$this->error->add('registerdisabled', __('User registration is currently not allowed.', APP_TD));

		} elseif ( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] ) {
			$message = __('Check your email for the confirmation link.', APP_TD);

		} elseif ( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] ) {
			$message = __('Check your email for your new password.', APP_TD);

		} elseif ( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] ) {
			$message = __('Registration complete. Please check your e-mail.', APP_TD);
		} elseif ( isset($_GET['action']) && 'lostpassword' == $_GET['action'] && !empty($_GET['success'])) {
			$message = __('Your password has been reset. Please login.', APP_TD);
		}

		if ( $transient_notice = get_transient( 'login_notice' ) ) {
			appthemes_display_notice( $transient_notice[0], $transient_notice[1] );
			delete_transient( 'login_notice' );
		}

		if ( $this->error->get_error_code() ) {
			$error_html = '';
			foreach ( $this->error->errors as $error ) {
				$error_html .= html( 'li', $error[0] );
			}
			appthemes_display_notice( 'error', html( 'ul class="errors"', $error_html ) );
		} elseif ( !empty( $message ) ) {
			appthemes_display_notice( 'success', $message );
		}
	}
}


class APP_Password_Recovery extends APP_View_Page {

	private $error;

	function __construct( $template ) {
		parent::__construct( $template, __('Password Recovery', APP_TD) );
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}

	function init() {
		global $pagenow;

		if($pagenow == 'wp-login.php' && !empty($_GET['action']) && in_array( $_GET['action'], array('lostpassword', 'retrievepassword') ) && self::get_id() ) {
			wp_redirect(appthemes_get_password_recovery_url('redirect'));
			exit();
		}

	}

	function template_redirect() {
		$this->process_form();
	}

	function process_form() {
		$errors = new WP_Error();

		if ( !empty($_POST['user_login']) ) {
			$errors = $this->retrieve_password();

			if ( !is_wp_error($errors) ) {
				$url = APP_Login::get_url('redirect');
				$pre = get_option('permalink_structure') == '' ? '&' : '?';
				$url = $url . $pre . 'checkemail=confirm';
				wp_redirect( $url );
				exit();
			}

			$this->error = $errors;
		}

		do_action('lost_password');

	}

	function notices() {

		if (isset($_GET['invalidkeyerror']) && '1' == $_GET['invalidkeyerror'] ) {
			appthemes_display_notice( 'error', __('Sorry, that key does not appear to be valid. Please try again.', APP_TD) );
		}

		if (isset($this->error) && sizeof($this->error)>0 && $this->error->get_error_code()) {
			$error_html ='<ul class="errors">';
			foreach ($this->error->errors as $error) {
				$error_html .='<li>'.$error[0].'</li>';
			}
			$error_html .='</ul>';
			appthemes_display_notice( 'error', $error_html );
		}
	}

	function retrieve_password() {
		global $wpdb, $current_site;

		$errors = new WP_Error();

		if ( empty( $_POST['user_login'] ) ) {
			$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or e-mail address.', APP_TD));
		} else if ( strpos( $_POST['user_login'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
			if ( empty( $user_data ) )
				$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.', APP_TD));
		} else {
			$login = trim($_POST['user_login']);
			$user_data = get_user_by('login', $login);
		}

		do_action('lostpassword_post');

		if ( $errors->get_error_code() )
			return $errors;

		if ( !$user_data ) {
			$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.', APP_TD));
			return $errors;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action('retreive_password', $user_login);  // Misspelled and deprecated
		do_action('retrieve_password', $user_login);

		$allow = apply_filters('allow_password_reset', true, $user_data->ID);

		if ( ! $allow )
			return new WP_Error('no_password_reset', __('Password reset is not allowed for this user', APP_TD));
		else if ( is_wp_error($allow) )
			return $allow;

		$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
		if ( empty($key) ) {
			// Generate something random for a key...
			$key = wp_generate_password(20, false);
			do_action('retrieve_password_key', $user_login, $key);
			// Now insert the new md5 key into the db
			$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
		}
		$message = __('Someone requested that the password be reset for the following account:', APP_TD) . "\r\n\r\n";
		$message .= site_url() . "\r\n\r\n";
		$message .= sprintf(__('Username: %s', APP_TD), $user_login) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.', APP_TD) . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:', APP_TD) . "\r\n\r\n";
		$url = appthemes_get_password_reset_url();
		$pre = get_option('permalink_structure') == '' ? '&' : '?';
		$url = $url . $pre . "action=rp&key=".$key."&login=" . rawurlencode($user_login);
		$message .= '<' . $url . ">\r\n";

		if ( is_multisite() )
			$blogname = $GLOBALS['current_site']->site_name;
		else
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$title = sprintf( __('[%s] Password Reset', APP_TD), $blogname );

		$title = apply_filters('retrieve_password_title', $title);
		$message = apply_filters('retrieve_password_message', $message, $key);

		if ( $message && !wp_mail($user_email, $title, $message) )
			wp_die( __('The e-mail could not be sent.', APP_TD) . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...', APP_TD) );

		return true;
	}
}

class APP_Password_Reset extends APP_View_Page {

	private $error;

	function __construct( $template ) {
		parent::__construct( $template, __('Password Reset', APP_TD) );
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}

	function init() {

		global $pagenow;

		if($pagenow == 'wp-login.php' && !empty( $_GET['action'] ) && 'rp' == $_GET['action'] && !empty( $_GET['key'] ) && !empty( $_GET['login'] ) && self::get_id() ) {
			wp_redirect(appthemes_get_password_reset_url('redirect'));
			exit();
		}
	}

	function template_redirect() {

		if ( !$this->condition() ) return;

		if(!empty( $_GET['action'] ) && 'rp' == $_GET['action'] && !empty( $_GET['key'] ) && !empty( $_GET['login'] )) {
			$this->process_form();
		}

		wp_enqueue_script('utils');
		wp_enqueue_script('user-profile');
	}

	function process_form() {

		$user = $this->check_password_reset_key($_GET['key'], $_GET['login']);

		if ( is_wp_error($user) ) {
			$url = appthemes_get_password_recovery_url('redirect');
			$pre = get_option('permalink_structure') == '' ? '&' : '?';
			$url = $url . $pre . 'action=lostpassword&invalidkeyerror=1';
			wp_redirect( $url );

			exit;
		} else {
			$this->error = $user;
		}

		if ( isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2'] ) {
			$this->error = new WP_Error( 'password_reset_mismatch', __('The passwords do not match.', APP_TD) );
		} elseif ( isset($_POST['pass1']) && !empty($_POST['pass1']) ) {
			$this->reset_password($user, $_POST['pass1']);
			$url = APP_Login::get_url('redirect');
			$pre = get_option('permalink_structure') == '' ? '&' : '?';
			$url = $url . $pre . 'action=lostpassword&success=1';
			wp_redirect( $url );
			exit;
		}
	}

	function check_password_reset_key($key, $login) {
		global $wpdb;

		$key = preg_replace('/[^a-z0-9]/i', '', $key);

		if ( empty( $key ) || !is_string( $key ) )
			return new WP_Error('invalid_key', __('Invalid key', APP_TD));

		if ( empty($login) || !is_string($login) )
			return new WP_Error('invalid_key', __('Invalid key', APP_TD));

		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));

		if ( empty( $user ) )
			return new WP_Error('invalid_key', __('Invalid key', APP_TD));

		return $user;
	}

	function reset_password($user, $new_pass) {
		do_action('password_reset', $user, $new_pass);

		wp_set_password($new_pass, $user->ID);

		wp_password_change_notification($user);
	}

	function notices() {

		if (isset($this->error->errors) && sizeof($this->error->errors)>0 && $this->error->get_error_code()) {
			$error_html ='<ul class="errors">';
			foreach ($this->error->errors as $error) {
				$error_html .='<li>'.$error[0].'</li>';
			}
			$error_html .='</ul>';
			appthemes_display_notice( 'error', $error_html );
		}
	}
}

function appthemes_get_password_reset_url($context = 'display') {
	$url = '';

	$pre = get_option('permalink_structure') == '' ? '&' : '?';

	if(!empty($_GET['action']) && 'rp' == $_GET['action'] && !empty($_GET['key']) && !empty($_GET['login'])) {
		$url  .= 'action='.$_GET['action'].'&key='.$_GET['key'].'&login='.$_GET['login'];
	}

	if ( $page_id = APP_Password_Reset::get_id() ) {
		$permalink = get_permalink( $page_id );
		if ( !empty( $url ) ) {
			$pre = get_option('permalink_structure') == '' ? '&' : '?';
			$permalink = $permalink . $pre . $url;
		}
		return esc_url( $permalink, null, $context );
	}

	return esc_url( site_url( 'wp-login.php' ) . '?' . $url, null, $context );
}


class APP_Registration extends APP_View_Page {

	private $error;

	function __construct( $template ) {
		parent::__construct( $template, __('Register', APP_TD) );

		add_action ( 'appthemes_after_registration', 'wp_new_user_notification', 10, 2 );
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}

	function init() {

		global $pagenow;

		if(isset($_GET['action'])) $theaction = $_GET['action']; else $theaction ='';

		if($pagenow == 'wp-login.php' && $theaction == 'register' && !isset($_GET['key']) && self::get_id() ) {
			wp_redirect(appthemes_get_registration_url('redirect'));
			exit();
		}
	}

	function template_redirect() {
		if ( !empty( $_POST['register'] ) && !empty( $_POST['user_login'] )  && !empty( $_POST['user_email'] )) {
			$this->process_form();
		}

		wp_enqueue_script('utils');
		wp_enqueue_script('user-profile');
	}

	function process_form() {
		$errors = $this->register_new_user();
		if ( !is_wp_error($errors) ) {
			$url = APP_Login::get_url('redirect');
			$pre = get_option('permalink_structure') == '' ? '&' : '?';
			$url = $url . $pre . 'checkemail=registered';
			$redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : $url;
			wp_safe_redirect( $redirect_to );
			exit();
		}
	}

	function register_new_user() {

		$posted = array();
		$errors = new WP_Error();
		$user_pass = wp_generate_password();
		$show_password_fields = apply_filters('show_password_fields', true);
		// Get (and clean) data

		$fields = array(
			'user_login',
			'user_email',
			'pass1',
			'pass2'
		);

		foreach ($fields as $field) {
			if ( isset($_POST[$field]) )
				$posted[$field] = stripslashes(trim($_POST[$field]));
		}

		$sanitized_user_login = sanitize_user( $posted['user_login'] );
		$user_email = apply_filters( 'user_registration_email', $posted['user_email'] );

		// Check the username
		if ( $sanitized_user_login == '' ) {
			$errors->add( 'empty_username', __('<strong>ERROR</strong>: Please enter a username.', APP_TD) );
		} elseif ( ! validate_username( $posted['user_login'] ) ) {
			$errors->add( 'invalid_username', __('<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.', APP_TD) );
			$sanitized_user_login = '';
		} elseif ( username_exists( $sanitized_user_login ) ) {
			$errors->add( 'username_exists', __('<strong>ERROR</strong>: This username is already registered, please choose another one.', APP_TD) );
		}

		// Check the e-mail address
		if ( $user_email == '' ) {
			$errors->add( 'empty_email', __('<strong>ERROR</strong>: Please type your e-mail address.', APP_TD) );
		} elseif ( ! is_email( $user_email ) ) {
			$errors->add( 'invalid_email', __('<strong>ERROR</strong>: The email address isn&#8217;t correct.', APP_TD) );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.', APP_TD) );
		}

		do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

		if ( $errors->get_error_code() ) {
			$this->error = $errors;
			return $this->error;
		}

		if ( $show_password_fields ) {
			if ( empty($posted['pass1']) )	{
				$errors->add( 'empty_password', __('<strong>ERROR</strong>: Please enter a password.', APP_TD) );
			} elseif ( empty($posted['pass2']) ) {
				$errors->add( 'empty_password', __('<strong>ERROR</strong>: Please enter the password twice.', APP_TD) );
			} elseif ( !empty($posted['pass1']) && $posted['pass1'] != $posted['pass2'] ) {
				$errors->add( 'password_mismatch', __('<strong>ERROR</strong>: The passwords do not match.', APP_TD) );
			}
		}

		if ( current_theme_supports( 'app-recaptcha' ) ) {
			list( $options ) = get_theme_support( 'app-recaptcha' );

			require_once ( $options['file'] );

			// check and make sure the reCaptcha values match
			$resp = recaptcha_check_answer( $options['private_key'], $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );

			if ( !$resp->is_valid )
				$errors->add( 'invalid_recaptcha', __('<strong>ERROR</strong>: The reCaptcha anti-spam response was incorrect.', APP_TD) );
		}

		if ( $errors->get_error_code() ) {
			$this->error = $errors;
			return $this->error;
		}

		if ( isset($posted['pass1']) )
			$user_pass = $posted['pass1'];

		// create the account and pass back the new user id
		$user_id = wp_create_user( $posted['user_login'], $user_pass, $posted['user_email'] );

		// something went wrong captain
		if ( !$user_id ) {
			$errors->add('registerfail', sprintf(__('<strong>ERROR</strong>: Couldn&#39;t register you... please contact the <a href="mailto:%s">webmaster</a> !', APP_TD), get_option('admin_email')));

			if ( $errors->get_error_code() ) {
				$this->error = $errors;
				return $this->error;
			}
		}

		do_action( 'appthemes_after_registration', $user_id, $user_pass );

		if ( $show_password_fields ) {
			// set the WP login cookie (log the user in)
			$secure_cookie = is_ssl() ? true : false;
			wp_set_auth_cookie($user_id, true, $secure_cookie);

			if ( isset( $_REQUEST['redirect_to'] ) )
				$success_redirect = $_REQUEST['redirect_to'];
			else
				$success_redirect = get_option('siteurl');
		} else {
			// WP created password for user, so show a message that it's been emailed to him
			$success_redirect = add_query_arg( 'checkemail', 'newpass', APP_Login::get_url('redirect') );
		}

		// redirect
		wp_redirect($success_redirect);
		exit;
	}

	function notices() {
		if (isset($this->error->errors) && sizeof($this->error->errors)>0 && $this->error->get_error_code()) {
			$error_html ='<ul class="errors">';
			foreach ($this->error->errors as $error) {
				$error_html .='<li>'.$error[0].'</li>';
			}
			$error_html .='</ul>';
			appthemes_display_notice( 'error', $error_html );
		}
	}
}
