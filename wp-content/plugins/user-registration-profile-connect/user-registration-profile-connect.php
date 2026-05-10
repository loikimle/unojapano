<?php
/**
 * Plugin Name: User Registration Profile Connect
 * Plugin URI: https://wpuserregistration.com/features/profile-connect/
 * Description: Connect users registered with other means to forms created with user registration
 * Version: 1.0.2
 * Author: WPEverest
 * UR Pro requires at least: 4.0.0
 * UR Pro tested up to: 4.2.0
 * Copyright: © 2017 WPEverest.
 * Author URI: https://wpuserregistration.com
 * Text Domain: user-registration-profile-connect
 * Domain Path: /languages/
 *
 * @package User_Registration_Profile_Connect
 */

defined( 'ABSPATH' ) || exit;

// Define UR_Profile_Connect_PLUGIN_FILE.
if ( ! defined( 'UR_PROFILE_CONNECT_PLUGIN_FILE' ) ) {
	define( 'UR_PROFILE_CONNECT_PLUGIN_FILE', __FILE__ );
}
// Define UR_PROFILE_CONNECT_VERSION.
if ( ! defined( 'UR_PROFILE_CONNECT_VERSION' ) ) {
	define( 'UR_PROFILE_CONNECT_VERSION', '1.0.2' );
}

// Include the main User_Registration_Profile_Connect class.
if ( ! class_exists( 'User_Registration_Profile_Connect' ) ) {
	include_once __DIR__ . '/includes/class-user-registration-profile-connect.php';
}

if ( ! defined( 'URPCONNECT_DS' ) ) {
	define( 'URPCONNECT_DS', DIRECTORY_SEPARATOR );
}

// Initialize the plugin.
add_action( 'plugins_loaded', array( 'User_Registration_Profile_Connect', 'get_instance' ) );
