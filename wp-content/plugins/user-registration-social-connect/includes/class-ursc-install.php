<?php
/**
 * Installation related functions and actions.
 *
 * @class    URSC_Install
 * @version  1.0.0
 * @package  UserRegistrationScialConnect/Classes
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URSC_Install Class.
 */
class URSC_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_filter( 'plugin_action_links_' . URSC_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) );

	}

	/**
	 * Check UserRegistration version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'user_registration_social_connect' ) !== URSC()->version ) {
			self::install();
			do_action( 'user_registration_social_connect_updated' );
		}
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public static function install_actions() {

	}

	/**
	 * Install UR.
	 */
	public static function install() {
		global $wpdb;

		if ( ! is_blog_installed() ) {
			return;
		}

		if ( ! defined( 'URSC_INSTALLING' ) ) {
			define( 'URSC_INSTALLING', true );
		}

		self::update_ur_version();
		self::create_options();


		// Trigger action
		do_action( 'user_registration_social_connect_installed' );

		set_transient( '_ursc_activation_redirect', 1, 30 );

	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	static function create_options() {
		// Include settings so that we can run through defaults

		$message          = ursc_is_compatible();
		$settings_advance = array();
		if ( 'YES' === $message ) {
			$settings_advance = ursc_social_advance_settings();

		}
		$settings_api = ursc_social_api_settings();

		$settings = array_merge( $settings_advance, $settings_api );

		foreach ( $settings as $setting ) {

			if ( isset( $setting['default'] ) && isset( $setting['id'] ) ) {
				$autoload = isset( $setting['autoload'] ) ? (bool) $setting['autoload'] : true;
				add_option( $setting['id'], $setting['default'], '', ( $autoload ? 'yes' : 'no' ) );
			}

		}

	}

	/**
	 * Update UR version to current.
	 */
	private static function update_ur_version() {
		delete_option( 'user_registration_social_connect' );
		add_option( 'user_registration_social_connect', URSC()->version );
	}

	/**
	 * Display action links in the Plugins list table.
	 *
	 * @param  array $actions
	 *
	 * @return array
	 */
	public static function plugin_action_links( $actions ) {

		$message = ursc_is_compatible();
		if ( 'YES' !== $message ) {

			return $actions;
		}
		$new_actions = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=social_connect' ) . '" title="' . esc_attr( __( 'View User Registration Social Connect Settings', 'user-registration-social-connect' ) ) . '">' . __( 'Settings', 'user-registration-social-connect' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}


}

URSC_Install::init();
