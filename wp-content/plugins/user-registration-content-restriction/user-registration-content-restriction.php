<?php
/**
 * Plugin Name: User Registration Content Restriction
 * Plugin URI: https://wpeverest.com/wordpress-plugins/user-registration/content-restriction
 * Description: Content Restriction addon for user registration plugin.
 * Version: 1.2.1
 * Author: WPEverest
 * Author URI: https://wpeverest.com
 * Text Domain: user-registration-content-restriction
 * Domain Path: /languages/
 * UR requires at least: 1.1.0
 * UR tested up to: 2.1.7
 * Copyright: © 2017 WPEverest.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'UserRegistrationContentRestriction' ) ) :

	/**
	 * Main UserRegistrationContentRestriction Class.
	 *
	 * @class   UserRegistrationContentRestriction
	 * @version 1.0.0
	 */
	final class UserRegistrationContentRestriction {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		public $version = '1.2.1';


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
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'user-registration-content-restriction' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'user-registration-content-restriction' ), '1.0' );
		}

		/**
		 * FlashToolkit Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'user_registration_content_restriction_loaded' );
		}

		/**
		 * Hook into actions and filters.
		 */
		private function init_hooks() {
			register_activation_hook( __FILE__, array( 'URCR_Install', 'install' ) );
			add_action( 'user_registration_loaded', array( $this, 'plugin_updater' ) );
			add_action( 'init', array( $this, 'init' ), 0 );
		}

		/**
		 * Plugin Updater.
		 */
		public function plugin_updater() {
			if ( function_exists( 'ur_addon_updater' ) ) {
				ur_addon_updater( __FILE__, 864, $this->version );
			}
		}

		/**
		 * Define FT Constants.
		 */
		private function define_constants() {
			$this->define( 'URCR_DS', DIRECTORY_SEPARATOR );
			$this->define( 'URCR_PLUGIN_FILE', __FILE__ );
			$this->define( 'URCR_TEMPLATES_DIR', __DIR__ . '/templates' );
			$this->define( 'URCR_ABSPATH', dirname( __FILE__ ) . URCR_DS );
			$this->define( 'URCR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'URCR_VERSION', $this->version );
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
			include_once URCR_ABSPATH . 'includes/class-urcr-autoloader.php';

			/**
			 * Abstract classes
			 */

			include_once URCR_ABSPATH . 'includes/functions-urcr-core.php';

			/*
			 * Core classes
			 */
			include_once URCR_ABSPATH . 'includes/class-urcr-install.php';
			include_once URCR_ABSPATH . 'includes/class-urcr-ajax.php';
			include_once URCR_ABSPATH . 'includes/class-urcr-post-types.php';
			include_once URCR_ABSPATH . 'includes/class-urcr-shortcodes.php';

			if ( $this->is_request( 'admin' ) ) {
				include_once URCR_ABSPATH . 'includes/admin/class-urcr-admin-assets.php';
				include_once URCR_ABSPATH . 'includes/admin/class-urcr-admin.php';
			}

			if ( $this->is_request( 'frontend' ) ) {

				include_once URCR_ABSPATH . 'includes/class-urcr-frontend.php';               // Frontend Scripts
			}

		}

		/**
		 * Init UserRegistrationContentRestriction when WordPress Initialises.
		 */
		public function init() {
			// Before init action.
			do_action( 'before_user_registration_content_restriction_init' );

			// Set up localisation.
			$this->load_plugin_textdomain();

			if ( $this->is_request( 'admin' ) ) {
				include_once URCR_ABSPATH . 'includes/admin/class-urcr-admin-meta-boxes.php';
			}
			// Init action.
			do_action( 'user_registration_content_restriction_init' );
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/user-registration-content-restriction/user-registration-content-restriction-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/user-registration-content-restriction-LOCALE.mo
		 */

		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'user-registration-content-restriction' );

			unload_textdomain( 'user-registration-content-restriction' );
			load_textdomain( 'user-registration-content-restriction', WP_LANG_DIR . '/user-registration-content-restriction/user-registration-content-restriction' . $locale . '.mo' );
			load_plugin_textdomain( 'user-registration-content-restriction', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
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
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'user_registration_content_restriction_template_path', 'user-registration-content-restriction/' );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
	}

endif;

/**
 * Main instance of UserRegistrationContentRestriction.
 *
 * Returns the main instance of FT to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return UserRegistrationContentRestriction
 */
function URCR() {
	return UserRegistrationContentRestriction::instance();
}

// Global for backwards compatibility.
$GLOBALS['user-registration-content-restriction'] = URCR();
