<?php

	/*
	* Plugin Name: WordPress Stripe
	* Plugin URI: https://package7.com
	* Description: WordPress Stripe implements the Stripe PHP API.
	* Author: George Dobre <george@package7.com>
	* Author URI: https://package7.com
	* Version: 1.0.0
	* License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	*/
	
	require_once(__DIR__ . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'stripe-php-6.4.1' . DIRECTORY_SEPARATOR . 'init.php');
	require_once(__DIR__ . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'stripe-wrapper.php');
	
	$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	function listCustomers($limit = 10) {
		$sw = new StripeWrapper();
		if($sw->getCustomers()) {
			$customers = $sw->result;
		echo '<table class="widefat fixed" cellspacing="0">
		<thead><th>E-mail address</th><th>Description</th></thead><tbody>';
			foreach($customers as $customer) {
				echo '<tr><td class="column-columnname">' . $customer['email'] . '</td><td>' . $customer['description'] . '</td></tr>';
			}
			echo '</tbody></table>';
		} else {
			echo 'eroare';
		}
	}
	
	function wordpress_stripe_activate() {
		$stripe_keys = array('stripe_pk_test', 'stripe_sk_test', 'stripe_pk_live', 'stripe_sk_live');
		
		foreach($stripe_keys as $key) {
			add_option($key, '', '', 'yes');
		}
		
		$errors = 0;
		
		foreach($stripe_keys as $key) {
			if(get_option($key) == '') {
				$errors++;
			}
		}
		
		add_option('stripe_api_mode', 'test', '', 'yes');
		
		if($errors !== 0) {
			add_action('admin_notices', 'stripe_keys_notice');
		}
		
		function Zumper_widget_enqueue_script() {   
			wp_enqueue_script( 'my_custom_script', plugin_dir_url( __FILE__ ) . 'js/jquery.repeatable.js' );
		}
		
		add_action('wp_enqueue_scripts', 'Zumper_widget_enqueue_script');
	}
	
	
	function stripe_keys_notice() {
		//echo '<div class="notice notice-error"><p><strong>ActiveCollab</strong> Please don\'t forget to configure the plugin</p><p><a href="' . menu_page_url('activecollab_settings', false) . '" class="button button-primary">Go to Settings</a></p></div>';
		echo '<div class="notice notice-error"><p><strong>WordPress Stripe</strong> Please update your Stripe keys</p></div>';
	}
	
	add_action('admin_menu', 'wordpress_stripe_admin_page');
	
	function wordpress_stripe_admin_page() {
		
		add_options_page('Stripe', 'Stripe', 'manage_options', 'wordpress_stripe', 'wordpress_stripe_admin_page_content', '', 6);
	}
	
	function wordpress_stripe_admin_page_content() {
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'stripe_general';
        if(isset($_GET['tab'])) $active_tab = $_GET['tab'];
		
		$tabs = array(
			'stripe_general' => 'General',
			'stripe_customers'	=>	'Customers'
		);
			
		echo '<div class="wrap">';
		echo '<h1>Stripe <span class="notice notice-warning" style="vertical-align: middle;"><small>TEST MODE</small></span></h1>';
		echo ' <h2 class="nav-tab-wrapper">';
		
		foreach($tabs as $tab => $name) {
			
			echo '<a href="?page=wordpress_stripe&tab=' . $tab . '" class="nav-tab';
			
			if($tab == $_GET['tab']) {
				echo ' nav-tab-active';
			}
			
			echo '">' . $name . '</a>';
		}
		
		echo '</h2><br/>';
			
		switch($_GET['tab']) {
			case 'stripe_general':
				if(isset($_POST['submitted'])) {
					$errors = 0;
					$changes = 0;
					
					if(get_option('stripe_pk_live') != $_POST['stripe_pk_live']) {
						$changes++;
						update_option('stripe_pk_live', $_POST['stripe_pk_live']);
					}
					
					if(get_option('stripe_sk_live') != $_POST['stripe_pk_live']) {
						$changes++;
						update_option('stripe_sk_live', $_POST['stripe_sk_live']);
					}
					
					if(get_option('stripe_pk_test') != $_POST['stripe_pk_test']) {
						$changes++;
						update_option('stripe_pk_test', $_POST['stripe_pk_test']);
					}
					
					if(get_option('stripe_sk_test') != $_POST['stripe_sk_test']) {
						$changes++;
						update_option('stripe_sk_test', $_POST['stripe_sk_test']);
					}
				}
				
				echo '
				<form action="' . $actual_link . '" method="post">
					<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="my-text-field">Public Key</label></th>
							<td><input type="text" value="' . get_option('stripe_pk_live') . '" id="stripe_pk_live" name="stripe_pk_live"></td>
						</tr>
						<tr>
							<th scope="row"><label for="my-text-field">Secret Key</label></th>
							<td><input type="text" value="' . get_option('stripe_sk_live') . '" id="stripe_sk_live" name="stripe_sk_live"></td>
						</tr>
						<tr>
							<th scope="row"><label for="my-text-field">Test Public Key</label></th>
							<td><input type="text" value="' . get_option('stripe_pk_test') . '" id="stripe_pk_test" name="stripe_pk_test"></td>
						</tr>
						<tr>
							<th scope="row"><label for="my-text-field">Test Secret Key</label></th>
							<td><input type="text" value="' . get_option('stripe_sk_test') . '" id="stripe_sk_test" name="stripe_sk_test"></td>
						</tr>
<tr>
    <th scope="row">API Mode</th>
    <td>
        <fieldset>
            <legend class="screen-reader-text"><span>Mode</span></legend>
            ';

			$stripe_modes = array('live', 'test');
			
			foreach($stripe_modes as $mode) {
				echo '<label><input type="radio" checked="checked" value="G" name="avatar_rating"> ' . $mode . '</label> ';
			}
				if(get_option('stripe_mode')) {
					
				}
			
echo '
        </fieldset>
    </td>
</tr>
    </tbody>
</table>
    <p class="submit"><input type="submit" value="Save Changes" name="submitted" class="button-primary" name="Submit"></p>
</form>';
			break;
			case 'stripe_customers':
				listCustomers();
			break;
		}
		
		echo '</div>';
		$html = '<div class="wrap">';
		$html .= '<h1>Stripe</h1>';
		$html .= '';
		$html .= '<div><strong>PK test key</strong><input type="text" value="' . get_option('stripe_pk_test') . '"/></div>';
		$html .= '<div><strong>SK test key</strong><input type="text" value="' . get_option('stripe_sk_test') . '"/></div>';
		$html .= '<div><strong>PK live key</strong><input type="text" value="' . get_option('stripe_pk_live') . '"/></div>';
		$html .= '<div><strong>SK live key</strong><input type="text" value="' . get_option('stripe_sk_live') . '"/></div>';
		//echo $html;
	}
	
	function if_exists($post) {
		if(isset($_POST[$post])) {
			return $_POST[$post];
		}
	}
	
	function register_form() {
		if(isset($_POST['submitted'])) {
			$errors = 0;
			$issues = array();
			
			if(empty($_POST['first_name'])) {
				$errors++;
			}
			
			if(empty($_POST['last_name'])) {
				$errors++;
			}
			
			if(empty($_POST['email_address'])) {
				$errors++;
			}
			
			if(empty($_POST['password'])) {
				$errors++;
			}
			
			if($errors==0) {
				$register = wp_create_user($_POST['username'], $_POST['password'], $_POST['email_address']);
				
				if(is_numeric($register)) {
					$sw = new StripeWrapper;
					update_user_meta($register, 'first_name', $_POST['first_name']);
					update_user_meta($register, 'last_name', $_POST['last_name']);
					if($sw->create_user($_POST['username'], $_POST['email_address'])) {
						add_user_meta($register, 'stripe_customer_id', $sw->result);
					}
					
					wp_set_current_user($register);
					wp_set_auth_cookie($register);
					wp_redirect('https://package7.com/finished');
				} else {
					if(isset($register->errors)) {
						foreach($register->errors as $key=>$value) {
							echo '<div>' . $value[0] . '</div>';
						}
					}
				}
			} else {
				echo '<div style="border: 1px solid #ff0000; color: #ff0000; margin-bottom: 25px; padding: 5px;">Please complete all fields</div>';
			}
		}
		
		echo '
		<form action="' . get_permalink() . '" id="register_form" method="post">
			<div>First name</div>
			<div><input type="text" name="first_name" value="' . if_exists('first_name') . '"/></div>
			<div>Last name</div>
			<div><input type="text" name="last_name" value="' . if_exists('last_name') . '"/></div>
			<div>Username</div>
			<div><input type="text" name="username" value="' . if_exists('username') . '"/></div>
			<div>E-mail</div>
			<div><input type="text" name="email_address" value="' . if_exists('email_address') . '"/></div>
			<div>Password</div>
			<div><input type="password" name="password" value=""/></div>
			<div>&nbsp;</div>
			<div><input type="submit" name="submitted" value="Register"/></div>
		</form>';
	}
	
	add_shortcode('register_form', 'register_form');
	
	register_activation_hook(__FILE__, 'wordpress_stripe_activate');