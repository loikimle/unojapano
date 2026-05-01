<?php
/**
 * Installation related functions and actions.
 *
 * @class    URWC_Install
 * @version  1.0.0
 * @package  UserRegistrationWooCommerce/Classes
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URWC_Install Class.
 */
class URWC_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
	}

	/**
	 * Check UserRegistration version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'user_registration_woocommerce' ) !== URWC()->version ) {
			self::install();
			do_action( 'user_registration_woocommerce_updated' );
		}
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public static function install_actions() {}

	/**
	 * Install URWC.
	 */
	public static function install() {
		global $wpdb;

		if ( ! is_blog_installed() ) {
			return;
		}

		if ( ! defined( 'URWC_INSTALLING' ) ) {
			define( 'URWC_INSTALLING', true );
		}

		self::update_ur_version();
		self::create_options();

		// Trigger action
		do_action( 'user_registration_woocommerce_installed' );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	static function create_options() {
	}

	/**
	 * Update UR version to current.
	 */
	private static function update_ur_version() {
		delete_option( 'user_registration_woocommerce' );
		add_option( 'user_registration_woocommerce', URWC()->version );
	}
}

URWC_Install::init();
