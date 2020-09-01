<?php
/**
 * @package GuardianKey
 * @version 3.1
 */
/*
Plugin Name: GuardianKey
Plugin URI: http://wordpress.org/plugins/guardiankey/
Description: GuardianKey is a service to protect systems in real-time against authentication attacks. Through an advanced Machine Learning approach, it can detect and block malicious accesses to the system, notify the legitimate user and the system administrator about such access attempts. This is a implementation of GuardianKey Lite version, that is free until 100 users.
Author: GuardianKey
Version: 3.1
Author URI: http://guardiankey.io/
*/


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

register_activation_hook( __FILE__, 'guardiankey_install' );

add_action( 'admin_init', 'register_guardiankey_settings' );
add_action( 'wp_login_failed', 'guardiankey_login_failed', 10, 1 ); 

add_filter ('wp_authenticate_user', 'guardiankey_checkUser', 10,2);
add_action('admin_menu', 'guardiankey_options_page');
add_action('gk_unlock', 'gk_unlock_ips');
add_action( 'upgrader_process_complete', 'guardiankey_upgrade',10, 2);


add_filter( 'wp_mail_content_type','guardiankey_set_content_type' );
add_action( 'admin_post_guardiankey_test_mail', 'guardiankey_test_mail' );
add_action( 'admin_post_sendqrcode', 'sendqrcode' );


include( plugin_dir_path( __FILE__ ).'functions.php');



?>
