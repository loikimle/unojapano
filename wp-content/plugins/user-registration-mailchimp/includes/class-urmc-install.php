<?php
/**
 * Installation related functions and actions.
 *
 * @class    URMC_Install
 * @version  1.0.0
 * @package  UserRegistrationMailChimp/Classes
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URMC_Install Class.
 */
class URMC_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_filter( 'plugin_action_links_' . URMC_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) );

	}

	/**
	 * Check UserRegistration version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'user_registration_mailchimp' ) !== URMC()->version ) {
			self::install();
			do_action( 'user_registration_mailchimp_updated' );
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
	 * Install URMC.
	 */
	public static function install() {
		global $wpdb;

		if ( ! is_blog_installed() ) {
			return;
		}

		if ( ! defined( 'URMC_INSTALLING' ) ) {
			define( 'URMC_INSTALLING', true );
		}

		self::update_ur_version();
		self::create_tables();

		// Trigger action.
		do_action( 'user_registration_mailchimp_installed' );

		set_transient( '_urmc_activation_redirect', 1, 30 );

	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *        user_registration_sessions - Table for storing sessions data.
	 */
	private static function create_tables() {

		global $wpdb;

		$wpdb->hide_errors();

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "
CREATE TABLE IF NOT EXISTS {$wpdb->prefix}urmc_lists (
  ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  list_fields longtext,
  list_id varchar(100),
  list_title varchar(255),
  total_members int(9) NOT NULL DEFAULT '0',
  field_count int(9) NOT NULL DEFAULT '0',
  web_id varchar(100),
  updated_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  created_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (ID)
) $collate;
";

		dbDelta( $sql );
	}

	/**
	 * Update UR version to current.
	 */
	private static function update_ur_version() {
		delete_option( 'user_registration_mailchimp' );
		add_option( 'user_registration_mailchimp', URMC()->version );
	}

	/**
	 * Display action links in the Plugins list table.
	 *
	 * @param  array $actions Actions.
	 *
	 * @return array
	 */
	public static function plugin_action_links( $actions ) {

		$message = urmc_is_compatible();

		if ( 'YES' !== $message ) {

			return $actions;
		}
		$new_actions = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=user-registration-mailchimp' ) . '" title="' . esc_attr( __( 'View User Registration Settings', 'user-registration-mailchimp' ) ) . '">' . __( 'Settings', 'user-registration-mailchimp' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}


}

URMC_Install::init();
