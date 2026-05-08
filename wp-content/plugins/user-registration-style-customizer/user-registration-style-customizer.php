<?php
/**
 * Plugin Name: User Registration Style Customizer
 * Plugin URI: https://wpuserregistration.com/features/style-customizer
 * Description: Customize your User Registration elements with the WordPress Customizer.
 * Version: 1.0.8
 * Author: WPEverest
 * UR Pro requires at least: 4.0.0
 * UR Pro tested up to: 4.2.0
 * Copyright: © 2017 WPEverest.
 * Author URI: https://wpuserregistration.com
 * Text Domain: user-registration-style-customizer
 * Domain Path: /languages/
 *
 * @package User_Registration_Style_Customizer
 */

defined( 'ABSPATH' ) || exit;

// Define UR_STYLE_CUSTOMIZER_PLUGIN_FILE.
if ( ! defined( 'UR_STYLE_CUSTOMIZER_PLUGIN_FILE' ) ) {
	define( 'UR_STYLE_CUSTOMIZER_PLUGIN_FILE', __FILE__ );
}

// Define UR_STYLE_CUSTOMIZER_VERSION.
if ( ! defined( 'UR_STYLE_CUSTOMIZER_VERSION' ) ) {
	define( 'UR_STYLE_CUSTOMIZER_VERSION', '1.0.8' );
}

// Include the main User_Registration_Two_Factor_Auth_Totp class.
if ( ! class_exists( 'User_Registration_Style_Customizer' ) ) {
	include_once __DIR__ . '/includes/class-user-registration-style-customizer.php';
}
if ( ! defined( 'URSC_DS' ) ) {
	define( 'URSC_DS', DIRECTORY_SEPARATOR );
}

// Initialize the plugin.
add_action( 'plugins_loaded', array( 'User_Registration_Style_Customizer', 'get_instance' ), 5 );
