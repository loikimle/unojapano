<?php
/**
 * Plugin Name: User Registration Social Connect
 * Plugin URI: https://wpeverest.com/wordpress-plugins/user-registration/social-connect
 * Description: Social connect addon for user registration plugin.
 * Version: 1.4.0
 * Author: WPEverest
 * Author URI: https://wpeverest.com
 * Text Domain: user-registration-social-connect
 * Domain Path: /languages/
 * UR requires at least: 1.1.0
 * UR PRO tested up to: 3.0.2.1
 *
 * Copyright: © 2017 WPEverest.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UserRegistrationSocialConnect' ) ) :

	/**
	 * Main UserRegistrationSocialConnect Class.
	 *
	 * @class   UserRegistrationSocialConnect
	 * @version 1.0.0
	 */
	final class UserRegistrationSocialConnect {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		public $version = '1.4.0';


		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $_instance = null;

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function instance() {
			// If the single instance hasn't been set, set it now.
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'user-registration-social-connect' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'user-registration-social-connect' ), '1.0' );
		}

		/**
		 * FlashToolkit Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'user_registration_social_connect_loaded' );
		}

		/**
		 * Hook into actions and filters.
		 */
		private function init_hooks() {
			register_activation_hook( __FILE__, array( 'URSC_Install', 'install' ) );
			add_action( 'user_registration_loaded', array( $this, 'plugin_updater' ) );
			add_action( 'init', array( $this, 'init' ), 0 );
		}

		/**
		 * Plugin Updater.
		 */
		public function plugin_updater() {
			if ( function_exists( 'ur_addon_updater' ) ) {
				ur_addon_updater( __FILE__, 346, $this->version );
			}
		}
		/**
		 * Define FT Constants.
		 */
		private function define_constants() {
			$this->define( 'URSC_DS', DIRECTORY_SEPARATOR );
			$this->define( 'URSC_PLUGIN_FILE', __FILE__ );
			$this->define( 'URSC_ABSPATH', dirname( __FILE__ ) . URSC_DS );
			$this->define( 'URSC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'URSC_VERSION', $this->version );
			$this->define( 'URSC_TEMPLATE_DEBUG_MODE', false );
			$this->define( 'URSC_NETWORK_PATH', URSC_ABSPATH . 'includes' . URSC_DS . 'networks' . URSC_DS );

		}

		/**
		 * Define constant if not already set.
		 *
		 * @param string      $name
		 * @param string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
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
		 * Includes.
		 */
		private function includes() {

			/**
			 * Core classes.
			 */
			include_once URSC_ABSPATH . 'includes/class-ursc-autoloader.php';
			include_once URSC_ABSPATH . 'includes/class-ursc-ajax.php';

			/**
			 * Abstract classes
			 */
			include_once URSC_ABSPATH . 'includes/abstracts/abstract-ursc-social-networks.php';

			include_once URSC_ABSPATH . 'includes/functions-ursc-core.php';

			include_once URSC_ABSPATH . 'includes/class-ursc-install.php';

			$message = ursc_is_compatible();

			if ( $this->is_request( 'admin' ) ) {
				include_once URSC_ABSPATH . 'includes/admin/class-ursc-admin.php';
			}

			if ( $this->is_request( 'frontend' ) && 'YES' == $message ) {

				include_once URSC_ABSPATH . 'includes/class-ursc-frontend.php';               // Frontend Scripts
			}

		}

		/**
		 * Init UserRegistrationSocialConnect when WordPress Initialises.
		 */
		public function init() {
			// Before init action.
			do_action( 'before_user_registration_social_connect_init' );

			// Set up localisation.
			$this->load_plugin_textdomain();

			// Init action.
			do_action( 'user_registration_social_connect_init' );
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/user-registration-social-connect/user-registration-social-connect-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/user-registration-social-connect-LOCALE.mo
		 */
		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'user-registration-social-connect' );

			unload_textdomain( 'user-registration-social-connect' );
			load_textdomain( 'user-registration-social-connect', WP_LANG_DIR . '/user-registration-social-connect/user-registration-social-connect-' . $locale . '.mo' );
			load_plugin_textdomain( 'user-registration-social-connect', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'user_registration_social_connect_template_path', 'user-registration-social-connect/' );
		}

		/**
		 * Get Ajax URL.
		 *
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}
	}

endif;

/**
 * Main instance of UserRegistrationSocialConnect.
 *
 * Returns the main instance of FT to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return UserRegistrationSocialConnect
 */

function URSC() {
	return UserRegistrationSocialConnect::instance();
}

// Global for backwards compatibility.
$GLOBALS['user-registration-social-connect'] = URSC();
