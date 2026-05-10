<?php
/**
 * User_Registration_Profile_Connect setup
 *
 * @package User_Registration_Profile_Connect
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main User_Registration_Profile_Connect Class.
 *
 * @class User_Registration_Profile_Connect
 */
final class User_Registration_Profile_Connect {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.2';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks if user registration is installed
		$ur_pro_plugins_path = WP_PLUGIN_DIR . URPCONNECT_DS . 'user-registration-pro' . URPCONNECT_DS . 'user-registration.php';

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
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/user-registration-profile-connect/user-registration-profile-connect-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/user-registration-profile-connect-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'user-registration-profile-connect' );

		load_textdomain( 'user-registration-profile-connect', WP_LANG_DIR . '/user-registration-profile-connect/user-registration-profile-connect-' . $locale . '.mo' );
		load_plugin_textdomain( 'user-registration-profile-connect', false, plugin_basename( dirname( UR_PROFILE_CONNECT_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		if ( is_admin() && current_user_can( 'promote_users' ) ) {
			include_once __DIR__ . '/class-user-registration-profile-connect-process.php';
		}
	}

	/**
	 * User Registration fallback notice.
	 */
	public function user_registration_missing_notice() {
		/* translators: %s: user-registration plugin link */
		echo '<div class="error notice is-dismissible"><p>' . sprintf( esc_html__( 'User Registration Profile Connect requires %s  version 4.0.0 or above to work', 'user-registration-profile-connect' ), '<a href="https://wpuserregistration.com/" target="_blank">' . esc_html__( 'User Registration Pro', 'user-registration-profile-connect' ) . '</a>' ) . '</p></div>';
	}
}
