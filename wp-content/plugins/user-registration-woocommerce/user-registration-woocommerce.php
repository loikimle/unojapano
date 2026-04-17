<?php
/**
 * Plugin Name: User Registration WooCommerce
 * Plugin URI: https://wpeverest.com/wordpress-plugins/user-registration/woocommerce
 * Description: WooCommerce synchronization for user registration plugin.
 * Version: 1.2.7
 * Author: WPEverest
 * Author URI: https://wpeverest.com
 * Text Domain: user-registration-woocommerce
 * Domain Path: /languages/
 * UR requires at least: 1.4.1
 * UR tested up to: 2.1.0
 * WC requires at least 2.5.0
 * WC tested up to: 5.9.0
 * Copyright: © 2017 WPEverest.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UserRegistrationWooCommerce' ) ) :

	/**
	 * Main UserRegistrationWooCommerce Class.
	 *
	 * @class   UserRegistrationWooCommerce
	 * @version 1.0.0
	 */
	final class UserRegistrationWooCommerce {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		public $version = '1.2.7';

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
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'user-registration-woocommerce' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'user-registration-woocommerce' ), '1.0' );
		}

		/**
		 * FlashToolkit Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'user_registration_woocommerce_loaded' );
		}

		/**
		 * Hook into actions and filters.
		 */
		private function init_hooks() {
			register_activation_hook( __FILE__, array( 'URWC_Install', 'install' ) );
			add_action( 'user_registration_loaded', array( $this, 'plugin_updater' ) );
			add_action( 'init', array( $this, 'init' ), 0 );
			add_action( 'plugins_loaded', array( $this, 'third_party_support' ), 11 );
		}

		/**
		 * Plugin Updater.
		 */
		public function plugin_updater() {
			if ( function_exists( 'ur_addon_updater' ) ) {
				ur_addon_updater( __FILE__, 348, $this->version );
			}
		}

		/**
		 * Define URWC Constants.
		 */
		private function define_constants() {
			$this->define( 'URWC_DS', DIRECTORY_SEPARATOR );
			$this->define( 'URWC_PLUGIN_FILE', __FILE__ );
			$this->define( 'URWC_ABSPATH', dirname( __FILE__ ) . URWC_DS );
			$this->define( 'URWC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'URWC_VERSION', $this->version );
			$this->define( 'URWC_TEMPLATE_DEBUG_MODE', false );
			$this->define( 'URWC_FORM_PATH', URWC_ABSPATH . 'includes' . URWC_DS . 'form' . URWC_DS );
			$this->define( 'URWC_NETWORK_PATH', URWC_ABSPATH . 'includes' . URWC_DS . 'networks' . URWC_DS );

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

			include_once URWC_ABSPATH . 'includes/class-urwc-autoloader.php';
			include_once URWC_ABSPATH . 'includes/functions-urwc-core.php';
			include_once URWC_ABSPATH . 'includes/class-urwc-ajax.php';

			$message = urwc_is_compatible();

			if ( $this->is_request( 'admin' ) ) {
				include_once URWC_ABSPATH . 'includes/class-urwc-field-list-table.php';
				include_once URWC_ABSPATH . 'includes/admin/class-urwc-admin.php';
			}

			if ( $this->is_request( 'frontend' ) && 'YES' == $message ) {
				include_once URWC_ABSPATH . 'includes/class-urwc-frontend.php'; // Frontend Scripts.
				include_once URWC_ABSPATH . 'includes/class-urwc-sync.php'; // WooCommerce Sync.
			}
		}

		/**
		 * Init UserRegistrationWooCommerce when WordPress Initialises.
		 */
		public function init() {
			// Before init action.
			do_action( 'before_user_registration_woocommerce_init' );

			// Set up localisation.
			$this->load_plugin_textdomain();

			// Init action.
			do_action( 'user_registration_woocommerce_init' );
		}

		/**
		 * Adds support for third party plugin
		 *
		 * @since 1.0.4
		 */
		public function third_party_support() {
			if ( class_exists( 'WC_Subscriptions' ) ) {
				include_once URWC_ABSPATH . 'includes/3rd-party/class-urwc-wc-subscriptions.php';
			}
			if ( class_exists( 'WC_Memberships' ) ) {
				include_once URWC_ABSPATH . 'includes/3rd-party/class-urwc-wc-memberships.php';
			}
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/user-registration-woocommerce/user-registration-woocommerce-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/user-registration-woocommerce-LOCALE.mo
		 */
		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'user-registration-woocommerce' );

			unload_textdomain( 'user-registration-woocommerce' );
			load_textdomain( 'user-registration-woocommerce', WP_LANG_DIR . '/user-registration-woocommerce/user-registration-woocommerce-' . $locale . '.mo' );
			load_plugin_textdomain( 'user-registration-woocommerce', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
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
			return apply_filters( 'user_registration_woocommerce_template_path', 'user-registration-woocommerce/' );
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
 * Main instance of UserRegistrationWooCommerce.
 *
 * Returns the main instance of FT to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return UserRegistrationWooCommerce
 */

function URWC() {
	return UserRegistrationWooCommerce::instance();
}

// Global for backwards compatibility.
$GLOBALS['user-registration-woocommerce'] = URWC();
