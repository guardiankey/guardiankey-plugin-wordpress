<?php
/**
 * @package GuardianKey
 * @version 1.3
 */
/*
Plugin Name: GuardianKey
Plugin URI: http://wordpress.org/plugins/guardiankey/
Description: GuardianKey is a service to protect systems in real-time against authentication attacks. Through an advanced Machine Learning approach, it can detect and block malicious accesses to the system, notify the legitimate user and the system administrator about such access attempts.
Author: GuardianKey
Version: 1.3
Author URI: http://guardiankey.io/
*/


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

register_activation_hook( __FILE__, 'guardiankey_register' );

add_action( 'admin_init', 'register_guardiankey_settings' );
add_action( 'wp_login_failed', 'guardiankey_login_failed', 10, 1 ); 

add_action( 'wp_login', 'guardiankey_checkUser' ,10,2);

add_action('admin_menu', 'guardiankey_options_page');


add_action( 'rest_api_init', 'guardiankey_register_webhook' ); 
add_filter( 'wp_mail_content_type','guardiankey_set_content_type' );
add_action( 'admin_post_guardiankey_test_mail', 'guardiankey_test_mail' );


include( plugin_dir_path( __FILE__ ).'functions.php');



?>
