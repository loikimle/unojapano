<?php
/**
 * Plugin Name: User Registration Field Visibility
 * Plugin URI: https://wpuserregistration.com/features/field-visibility
 * Description: Allows you to add field visibility option for specific roles and in specific location.
 * Version: 1.1.7
 * Author: WPEverest
 * UR Pro requires at least: 4.0.0
 * UR Pro tested up to: 4.2.0
 * Copyright: © 2017 WPEverest.
 * Author URI: https://wpuserregistration.com
 * Text Domain: user-registration-field-visibility
 * Domain Path: /languages/
 *
 * @package User_Registration_Field_Visibility
 */

defined( 'ABSPATH' ) || exit;

// Define UR_FIELD_VISIBILITY_PLUGIN_FILE.
if ( ! defined( 'UR_FIELD_VISIBILITY_PLUGIN_FILE' ) ) {
	define( 'UR_FIELD_VISIBILITY_PLUGIN_FILE', __FILE__ );
}

// Define URFV_VERSION.
if ( ! defined( 'URFV_VERSION' ) ) {
	define( 'URFV_VERSION', '1.1.7' );
}

// Include the main User_Registration_Field_Visibility class.
if ( ! class_exists( 'User_Registration_Field_Visibility' ) ) {
	include_once __DIR__ . '/includes/class-user-registration-field-visibility.php';
}
// Define UR_FIELD_VISIBILITY_PLUGIN_FILE_DIRECTORY.
if ( ! defined( 'URFV_DS' ) ) {
	define( 'URFV_DS', DIRECTORY_SEPARATOR );
}

// Initialize the plugin.
add_action( 'plugins_loaded', array( 'User_Registration_Field_Visibility', 'get_instance' ) );
