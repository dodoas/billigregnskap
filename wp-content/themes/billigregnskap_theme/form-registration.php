<?php
// Template Name: Register

// set a redirect for after logging in
if ( isset( $_REQUEST['redirect_to'] ) ) {
	$redirect = $_REQUEST['redirect_to'];
}
if (!isset($redirect)) $redirect = home_url();
?>

<div id="main" class="list">
	<div class="section-head">
		<h1><?php _e( 'Register', APP_TD ); ?></h1>
	</div>

	<?php do_action( 'appthemes_notices' ); ?>
	
	
	<?php if ( get_option('users_can_register') ) : ?>
		<form action="<?php echo appthemes_get_registration_url(); ?>" method="post" class="login-form register-form" name="registerform" id="login-form">
			
			<p><?php _e('Complete the fields below to register.', APP_TD ) ?></p>
			
			<fieldset>
				<div class="form-field">
					<label>
						<?php _e('Username:', APP_TD ) ?>
						<input tabindex="1" type="text" class="text required" name="user_login" id="user_login" value="<?php if (isset($_POST['user_login'])) echo esc_attr(stripslashes($_POST['user_login'])); ?>" />
					</label>
				</div>
	
				<div class="form-field">
					<label>
						<?php _e('Email:', APP_TD ) ?>
						<input tabindex="2" type="text" class="text required email" name="user_email" id="user_email" value="<?php if (isset($_POST['user_email'])) echo esc_attr(stripslashes($_POST['user_email'])); ?>" />
					</label>
				</div>
	
				<div class="form-field">
					<label>
						<?php _e('Password:', APP_TD ) ?>
						<input tabindex="3" type="password" class="text required" name="pass1" id="pass1" value="" autocomplete="off" />
					</label>
				</div>
	
				<div class="form-field">
					<label>
						<?php _e('Password Again:', APP_TD ) ?>
						<input tabindex="4" type="password" class="text required" name="pass2" id="pass2" value="" autocomplete="off" />
					</label>
				</div>
				
				<div class="form-field">
					<div id="pass-strength-result" class="hide-if-no-js"><?php _e('Strength indicator'); ?></div>
					<p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).', APP_TD ); ?></p>
				</div>	
				
				<?php 
				// include the spam checker if enabled
				//appthemes_recaptcha();
	
				do_action('register_form');
				?>
	
				<div class="form-field">
					<input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect); ?>" />				
					<input tabindex="30" type="submit" class="btn reg" id="register" name="register" value="<?php _e('Register', APP_TD ); ?>" />
				</div>
	
			</fieldset>
	
			<!-- autofocus the field -->
			<script type="text/javascript">try{document.getElementById('user_login').focus();}catch(e){}</script>		
		</form>

	<?php else: ?>
	
			<h3><?php _e('User registration has been disabled.', APP_TD); ?></h3>
		
	<?php endif; ?>	
</div>

<div id="sidebar">
	<?php get_sidebar( app_template_base() ); ?>
</div>
