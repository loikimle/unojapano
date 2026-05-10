<?php
/**
 * Main plugin class for User Registration Profile Completeness.
 *
 * @package WPEverest\UserRegistration\ProfileCompleteness
 * @since   1.0.0
 */

namespace WPEverest\UserRegistration\ProfileCompleteness;

use WPEverest\UserRegistration\ProfileCompleteness\Admin\Admin;
use WPEverest\UserRegistration\ProfileCompleteness\Frontend\Frontend;
use WPEverest\UserRegistration\ProfileCompleteness\Admin\Emails\UR_Settings_Profile_Completion_Congrats_Email;

/**
 * Main plugin class for User Registration Profile Completeness.
 *
 * @since 1.0.0
 */
class ProfileCompleteness {

	/**
	 * The single instance of the class.
	 *
	 * @var object
	 *
	 * @since 1.0.0
	 */
	protected static $instance;

	/**
	 * Admin class instance.
	 *
	 * @var Admin
	 * @since 1.0.0
	 */
	public $admin;

	/**
	 * Frontend class instance.
	 *
	 * @var Frontend
	 * @since 1.0.0
	 */
	public $frontend;

	/**
	 * The Ajax object.
	 *
	 * @var Ajax
	 * @since 1.0.0
	 */
	public $ajax;

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'user-registration-profile-completeness' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'user-registration-profile-completeness' ), '1.0.0' );
	}

	/**
	 * Main plugin class instance.
	 *
	 * Ensures only one instance of the plugin is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @return ProfileCompleteness Main instance of the class.
	 */
	final public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Plugin Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->define_constants();

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		$ur_pro_plugins_path = WP_PLUGIN_DIR . URPCOMPLETENESS_DS . 'user-registration-pro' . URPCOMPLETENESS_DS . 'user-registration.php';

		if ( file_exists( $ur_pro_plugins_path ) ) {

			$ur_pro_plugin_file_path = 'user-registration-pro/user-registration.php';
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( is_plugin_active( $ur_pro_plugin_file_path ) ) {

				if ( defined( 'UR_VERSION' ) && version_compare( UR_VERSION, '4.0.0', '>=' ) ) {
					$this->includes();
				} else {
					add_action( 'admin_notices', array( $this, 'user_registration_missing_notice' ) );
				}
			} else {
				add_action( 'admin_notices', array( $this, 'user_registration_missing_notice' ) );
			}
		} else {
			add_action( 'admin_notices', array( $this, 'user_registration_missing_notice' ) );

		}
	}


	/**
	 * Define Constants.
	 *
	 * @since    1.0.0
	 */
	private function define_constants() {
		$this->define( 'UR_PROFILE_COMPLETENESS_DS', DIRECTORY_SEPARATOR );
		$this->define( 'UR_PROFILE_COMPLETENESS_ABSPATH', dirname( UR_PROFILE_COMPLETENESS_PLUGIN_FILE ) . UR_PROFILE_COMPLETENESS_DS );
		$this->define( 'UR_PROFILE_COMPLETENESS_PLUGIN_BASENAME', plugin_basename( UR_PROFILE_COMPLETENESS_PLUGIN_FILE ) );
		$this->define( 'UR_PROFILE_COMPLETENESS_URL', plugin_dir_url( UR_PROFILE_COMPLETENESS_PLUGIN_FILE ) );
		$this->define( 'UR_PROFILE_COMPLETENESS_ASSETS_URL', UR_PROFILE_COMPLETENESS_URL . 'assets' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name Constant name.
	 * @param string|bool $value Constant value.
	 *
	 * @since    1.0.0
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type admin, ajax, cron, or frontend.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}


	/**
	 * Initializes the plugin by instantiating the required classes for different requests.
	 * If the request is for admin, an instance of the Admin class is created.
	 * If the request is for frontend, an instance of the Frontend class is created.
	 * It also checks if a rewrite rule flush is needed, and flushes the rules if necessary.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function includes() {
		// $this->ajax = new Ajax();

		if ( $this->is_request( 'admin' ) ) {
			$this->admin = new Admin();
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend = new Frontend();
		}
		add_filter( 'user_registration_email_classes', array( $this, 'add_email_settings' ), 10, 1 );

		$do_flush = get_option( 'urpn-flush-rewrite-rules', 1 );

		if ( $do_flush ) {
			// change option.
			update_option( 'urpn-flush-rewrite-rules', 0 );
			// the flush rewrite rules.
			flush_rewrite_rules();
		}
	}



	/**
	 * Check if the current request is an admin request.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Returns true if the current request is an admin request, false otherwise.
	 */
	public function is_admin() {
		$check_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX;
		$check_context = isset( $_REQUEST['context'] ) && 'frontend' === $_REQUEST['context'];

		return is_admin() && ! ( $check_ajax && $check_context );
	}


	/**
	 * Load the plugin text domain for localization.
	 *
	 * This function loads the translations for the plugin. It first checks if a translation file for the
	 * user-registration-profile-completeness plugin exists in the language directory of WordPress,
	 * and loads that file if it does. If no file is found, it will try to load the translation file from
	 *  the languages directory of the plugin itself.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'user-registration-profile-completeness' );

		// Load the translation file from the WordPress language directory if available.
		load_textdomain( 'user-registration-profile-completeness', WP_LANG_DIR . '/user-registration-profile-completeness/user-registration-profile-completeness-' . $locale . '.mo' );
		// If no translation file was found in the WordPress language directory, try to load the one from the plugin's languages directory.
		load_plugin_textdomain( 'user-registration-profile-completeness', false, plugin_basename( dirname( UR_PROFILE_COMPLETENESS_PLUGIN_FILE ) ) . '/languages' );
	}
			/**
			 * User Registration fallback notice.
			 */
	public function user_registration_missing_notice() {
		/* translators: %s: user-registration-pro version */
		echo '<div class="error notice is-dismissible"><p>' . sprintf( esc_html__( 'User Registration Profile Completeness requires %s version 4.0.0 or later to work', 'user-registration-profile-completeness' ), '<a href="https://wpuserregistration.com/" target="_blank">' . esc_html__( 'User Registration Pro', 'user-registration-profile-completeness' ) . '</a>' ) . '</p></div>';
	}


	/**
	 * Adds a new email setting to the provided email settings array.
	 *
	 * @param array $emails An array of email settings.
	 * @return array An updated array of email settings.
	 */
	public function add_email_settings( $emails ) {
		// $emails['user_registration_profile_completeness_reminder_email'] = new ProfileCompletionReminderEmail();
		$emails['UR_Settings_Profile_Completion_Congrats_Email'] = new UR_Settings_Profile_Completion_Congrats_Email();
		return $emails;
	}
}
