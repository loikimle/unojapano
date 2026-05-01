<?php
/**
 * Installation related functions and actions.
 *
 * @class    URCR_Install
 * @version  1.0.0
 * @package  UserRegistrationContentRestriction/Classes
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URCR_Install Class.
 */
class URCR_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_filter( 'plugin_action_links_' . URCR_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );

	}

	/**
	 * Check UserRegistration version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'user_registration_content_restriction' ) !== URCR()->version ) {
			self::install();
			do_action( 'user_registration_content_restriction_updated' );
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

		if ( ! defined( 'URCR_INSTALLING' ) ) {
			define( 'URCR_INSTALLING', true );
		}

		self::update_ur_version();
		self::create_options();


		// Trigger action
		do_action( 'user_registration_content_restriction_installed' );

		set_transient( '_urcr_activation_redirect', 1, 30 );

	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	static function create_options() {
		// Include settings so that we can run through defaults

		$settings_advance = urcr_settings();
		$settings = $settings_advance;

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
		delete_option( 'user_registration_content_restriction' );
		add_option( 'user_registration_content_restriction', URCR()->version );
	}

	/**
	 * Display action links in the Plugins list table.
	 *
	 * @param  array $actions
	 *
	 * @return array
	 */
	public static function plugin_action_links( $actions ) {

		$message = urcr_is_compatible();
		if ( 'YES' !== $message ) {

			return $actions;
		}
		$new_actions = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=content_restriction' ) . '" title="' . esc_attr( __( 'View User Registration Content Restriction Settings', 'user-registration-content-restriction' ) ) . '">' . __( 'Settings', 'user-registration-content-restriction' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}

	public static function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( $plugin_file == URCR_PLUGIN_BASENAME ) {
			$new_plugin_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'user_registration_content_restriction_docs_url', 'https://docs.wpeverest.com/docs/user-registration/user-registration-add-ons/user-registration-content-restriction/' ) ) . '" title="' . esc_attr( __( 'View User Registration Content Restriction Documentation', 'user-registration-content-restriction' ) ) . '">' . __( 'Docs', 'user-registration-content-restriction' ) . '</a>',
				);

			return array_merge( $plugin_meta, $new_plugin_meta );
		}

		return (array) $plugin_meta;
	}


}

URCR_Install::init();
