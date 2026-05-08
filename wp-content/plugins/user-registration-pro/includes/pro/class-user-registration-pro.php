<?php
/**
 * User_Registration_Pro setup
 *
 * @package User_Registration_Pro
 * @since  1.0.0
 */

use WPEverest\URM\Pro\Analytics\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'User_Registration_Pro' ) ) :

	/**
	 * Main User_Registration_Pro Class
	 *
	 * @class User_Registration_Pro
	 */
	final class User_Registration_Pro {


		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $_instance = null;

		/**
		 * Admin class instance
		 *
		 * @var \User_Registration_Pro_Admin
		 * @since 1.0.0
		 */
		public $admin = null;

		/**
		 * Frontend class instance
		 *
		 * @var \User_Registration_Pro_Frontend
		 * @since 1.0.0
		 */
		public $frontend = null;

		/**
		 * Delete user background.
		 *
		 * @var \UR_Background_Delete_User
		 * @since xx.xx.xx
		 */
		private static $background_delete_user = null;


		/**
		 * Return an instance of this class
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		private function __construct() {

			add_action( 'init', array( $this, 'create_post_type' ), 0 );

			$this->includes();
			add_action( 'init', array( 'User_Registration_Pro_Shortcodes', 'init' ) );
			add_action( 'user_registration_init', array( $this, 'plugin_updater' ) );

			// add actions and filters.
			add_filter(
				'user_registration_get_settings_pages',
				array(
					$this,
					'add_user_registration_pro_setting',
				),
				10,
				1
			);

			add_action( 'init', array( $this, 'ur_deactivate_addons' ) );
			add_action( 'plugins_loaded', array( $this, 'include_payments_files' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'include_frontend_listing_files' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'include_sms_integration_files' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'include_custom_email_files' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'include_coupons_files' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'include_local_currency_files' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'include_taxes_files' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'include_pdf_invoices_files' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'include_custom_email_files' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'include_teams_files' ), 1 );

				/**
				 * Action to delete the user based on the schedular.
				 *
				 * @since xx.xx.xx
				 */
			add_action( 'init', array( $this, 'ur_delete_user_schedular' ) );
			if ( class_exists( 'WPEverest\URM\Analytics\Analytics' ) ) {
				Analytics::get_instance();
			}
		}

		/**
		 * Includes SMS Integration addons files
		 *
		 * @since 4.3.0
		 */
		public function include_custom_email_files() {

			if ( ur_check_module_activation( 'custom-email' ) ) {
				include_once __DIR__ . '/addons/custom-email/CustomEmail.php';
			}
		}

		/**
		 * Plugin Updater.
		 */
		public function plugin_updater() {
			if ( get_transient( 'user_registration_addon_updater' ) ) {
				return;
			}

			set_transient( 'user_registration_addon_updater', true, DAY_IN_SECONDS );
			delete_site_transient( 'update_plugins' );

			if ( function_exists( 'ur_addon_updater' ) ) {
				ur_addon_updater( UR_PLUGIN_FILE, 167196, UR()->version );
			}
		}

		/**
		 * Includes.
		 */
		private function includes() {
			require_once 'functions-ur-pro.php';
			include_once 'class-ur-pro-shortcodes.php';
			include_once 'class-ur-pro-ajax.php';
			require_once 'stats/class-ur-pro-admin-stats.php';
			require_once 'form-analytics/class-ur-pro-form-analytics.php';
			require_once dirname( __DIR__, 2 ) . '/modules/file-downloads/FileDownloads.php';
			require_once dirname( __DIR__, 2 ) . '/modules/content-restriction/class-urcr-pro.php';
			require_once __DIR__ . '/Analytics/Analytics.php';

			// Class admin.
			if ( $this->is_admin() ) {
				// require file.
				require_once 'class-ur-pro-admin.php';
				include_once __DIR__ . '/class-ur-pro-popup-table-list.php';
				include_once __DIR__ . '/class-ur-pro-dashboard-analytics.php';
				// Include email template settings.
				include_once __DIR__ . '/admin/settings/class-ur-pro-settings-email-template.php';
				// Include email template preview handler.
				include_once __DIR__ . '/admin/class-ur-pro-email-template-preview.php';

				$this->admin = new User_Registration_Pro_Admin();
			} else {
				// Include email template preview handler for frontend preview.
				include_once __DIR__ . '/admin/class-ur-pro-email-template-preview.php';
			}

			if ( $this->is_request( 'frontend' ) ) {
				require_once 'class-ur-pro-frontend.php';
				$this->frontend = new User_Registration_Pro_Frontend();
			}

			add_filter( 'user_registration_email_classes', array( $this, 'get_emails' ), 10, 1 );
			add_filter( 'user_registration_login_redirect_url', array( $this, 'role_based_redirection_after_login' ), 50, 3 );
			add_filter( 'user_registration_logout_redirect_url', array( $this, 'role_based_redirection_after_logout' ), 50, 2 );
		}

		/**
		 * Deactivate Addons when Features enabled.
		 *
		 * @since 4.2.0
		 */
		public function ur_deactivate_addons() {
			if ( ur_check_module_activation( 'payments' ) ) {
				deactivate_plugins( 'user-registration-payments/user-registration-payments.php' );
			}
			if ( ur_check_module_activation( 'frontend-listing' ) ) {
				deactivate_plugins( 'user-registration-frontend-listing/user-registration-frontend-listing.php' );
			}
			if ( ur_check_module_activation( 'content-restriction' ) ) {
				deactivate_plugins( 'user-registration-content-restriction/user-registration-content-restriction.php' );
			}
			if ( ur_check_module_activation( 'membership' ) ) {
				deactivate_plugins( 'user-registration-membership/user-registration-membership.php' );
			}
		}

		/**
		 * Includes Payments addons files
		 *
		 * @since 4.2.0
		 */
		public function include_payments_files() {

			// if ( ( ( ur_check_module_activation( 'payments' ) || is_plugin_active( 'user-registration-stripe/user-registration-stripe.php' ) || is_plugin_active( 'user-registration-authorize-net/user-registration-authorize-net.php' ) ) && ! is_plugin_active( 'user-registration-payments/user-registration-payments.php' ) ) ) {
			if ( $this->is_admin() ) {
				include_once __DIR__ . '/class-ur-pro-payments-admin.php';
			}
			if ( $this->is_request( 'frontend' ) ) {
				require_once 'class-ur-pro-payments-frontend.php';
			}
			include_once __DIR__ . '/functions-payments.php';

			if ( ur_check_module_activation( 'payments' ) ) {
				include_once __DIR__ . '/addons/paypal/class-ur-pro-paypal-standard.php';
			}
			// }
		}

		/**
		 * Includes SMS Integration addons files
		 *
		 * @since 4.3.0
		 */
		public function include_sms_integration_files() {

			if ( ur_check_module_activation( 'sms-integration' ) ) {
				include_once __DIR__ . '/addons/sms-integration/SMSIntegration.php';
			}
		}

		/**
		 * Includes frontend listing addons files
		 *
		 * @since 4.2.0
		 */
		public function include_frontend_listing_files() {

			if ( ( ur_check_module_activation( 'frontend-listing' ) && ! is_plugin_active( 'user-registration-frontend-listing/user-registration-frontend-listing.php' ) ) ) {
				include_once __DIR__ . '/addons/frontend-listing/FrontendListing.php';
			}
		}

		/**
		 * Includes Coupon addons files
		 *
		 * @since 4.2.0
		 */
		public function include_coupons_files() {

			if ( ur_check_module_activation( 'coupon' ) ) {
				new \WPEverest\URMembership\Coupons\Coupons();
			}
		}
		public function include_pdf_invoices_files() {
			if ( ur_check_module_activation( 'pdf-invoice' ) ) {
				new \WPEverest\URM\Pro\PDFInvoice\PDFInvoice();
			}
		}

		/**
		 * Include tax files.
		 *
		 * @since 6.1.0
		 */
		public function include_local_currency_files() {
			if ( ur_check_module_activation( 'local-currency' ) ) {
				new \WPEverest\URMembership\Local_Currency\Local_Currency();
			}
		}

		/**
		 * Include tax files.
		 *
		 * @since 6.1.0
		 */
		public function include_taxes_files() {
			if ( ur_check_module_activation( 'taxes' ) ) {
				new \WPEverest\URMembership\Taxes\Taxes();
			}
		}

		/**
		 * Includes Payments addons files
		 *
		 * @since 4.2.0
		 */
		public function include_teams_files() {

			if ( ur_check_module_activation( 'team' ) ) {
				include_once __DIR__ . '/addons/team-membership/TeamMembership.php';
			}
		}

		/**
		 * Get all emails triggered.
		 *
		 * @return array $emails List of all emails.
		 */
		public function get_emails( $emails ) {
			$emails['UR_Settings_Auto_Generated_Password_Email'] = include __DIR__ . '/admin/settings/emails/class-ur-settings-generated-password-email.php';

			if ( 'disable' !== get_option( 'user_registration_pro_general_setting_delete_account', true ) ) {
				$emails['UR_Settings_Delete_Account_Email']       = include __DIR__ . '/admin/settings/emails/class-ur-settings-delete-account-email.php';
				$emails['UR_Settings_Delete_Account_Admin_Email'] = include __DIR__ . '/admin/settings/emails/class-ur-settings-delete-account-admin-email.php';
			}

			$emails['UR_Settings_Prevent_Concurrent_Login_Email'] = include __DIR__ . '/admin/settings/emails/class-ur-settings-prevent-concurrent-login-email.php';

			if ( ur_is_passwordless_login_enabled() ) {
				$emails['UR_Settings_Passwordless_Login_Email'] = include __DIR__ . '/admin/settings/emails/class-ur-settings-passwordless-login-email.php';
			}
			if ( ur_check_module_activation( 'membership' ) || ur_check_module_activation( 'payments' ) || is_plugin_active( 'user-registration-stripe/user-registration-stripe.php' ) || is_plugin_active( 'user-registration-authorize-net/user-registration-authorize-net.php' ) ) {
				$emails['UR_Settings_Payment_Pending_Email'] = include_once __DIR__ . '/admin/settings/emails/class-ur-settings-payment-pending-email.php';
			}

			// Payment Retry Emails.
			$emails['UR_Settings_Payment_Retry_Failed_Email'] = include_once __DIR__ . '/admin/settings/emails/class-ur-settings-payment-retry-failed-email.php';
			$emails['UR_Settings_Payment_Retry_Cancel_Email'] = include_once __DIR__ . '/admin/settings/emails/class-ur-settings-payment-retry-cancel-email.php';

			return $emails;
		}

		/**
		 * Check if is admin or not and load the correct class
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function is_admin() {
			$check_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX;
			$check_context = isset( $_REQUEST['context'] ) && $_REQUEST['context'] == 'frontend';

			return is_admin() && ! ( $check_ajax && $check_context );
		}

		/**
		 * What type of request is this?
		 *
		 * @param string $type admin, ajax, cron or frontend.
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
		 * Adds settings for extra features.
		 *
		 * @param array $settings Displays settings for extra features.
		 *
		 * @return array $settings
		 */
		public function add_user_registration_pro_setting( $settings ) {
			if ( class_exists( 'UR_Settings_Page' ) ) {
				$settings[] = include_once __DIR__ . '/admin/settings/class-ur-pro-settings.php';
			}

			return $settings;
		}

		// Register Custom Post Type
		function create_post_type() {

			register_post_type(
				'ur_pro_popup',
				apply_filters(
					'user_registration_pro_popup_post_type',
					array(
						'labels'              => array(
							'name'               => __( 'Popups', 'user-registration' ),
							'singular_name'      => __( 'Popup', 'user-registration' ),
							'menu_name'          => _x( 'Popups', 'Admin Popup name', 'user-registration' ),
							'add_new'            => __( 'Add popups', 'user-registration' ),
							'add_new_item'       => __( 'Add popups', 'user-registration' ),
							'edit'               => __( 'Edit', 'user-registration' ),
							'edit_item'          => __( 'Edit popup', 'user-registration' ),
							'new_item'           => __( 'New popup', 'user-registration' ),
							'view'               => __( 'View popups', 'user-registration' ),
							'view_item'          => __( 'View popup', 'user-registration' ),
							'search_items'       => __( 'Search popups', 'user-registration' ),
							'not_found'          => __( 'No popups found', 'user-registration' ),
							'not_found_in_trash' => __( 'No popups found in trash', 'user-registration' ),
							'parent'             => __( 'Parent popup', 'user-registration' ),
						),
						'public'              => false,
						'show_ui'             => true,
						'capability_type'     => 'post',
						'map_meta_cap'        => true,
						'publicly_queryable'  => false,
						'exclude_from_search' => true,
						'show_in_menu'        => false,
						'hierarchical'        => false,
						'rewrite'             => false,
						'query_var'           => false,
						'supports'            => false,
						'show_in_nav_menus'   => false,
						'show_in_admin_bar'   => false,
					)
				)
			);
		}
		/**
		 * Delete user schedular.
		 *
		 * @since xx.xx.xx
		 * @return void
		 */
		public function ur_delete_user_schedular() {

			$enable_delete_usr_schlr = get_option( 'user_registration_enable_delete_user_schedular', false );

			if ( ! $enable_delete_usr_schlr ) {
				return;
			}

			$next_date = get_option( 'user_registration_delete_user_schedular_next_date', '' );

			if ( '' === $next_date ) {
				return;
			}

			if ( ! $next_date ) {
				return;
			}
			$today = strtotime( 'today' );

			if ( $today < $next_date ) {
				return;
			}

			include_once __DIR__ . '/class-ur-background-delete-user.php';

			if ( ! self::$background_delete_user ) {
				self::$background_delete_user = new UR_Background_Delete_User();
			}

			self::$background_delete_user->push_to_queue(
				array()
			);

			ur_get_logger()->debug( print_r( 'Background delete user started....', true ) );

			self::$background_delete_user->save()->dispatch();
			// Update the schedular duration.
			$duration  = get_option( 'user_registration_delete_user_schedular_duration' );
			$next_date = strtotime( $duration );
			update_option( 'user_registration_delete_user_schedular_next_date', $next_date );

			ur_get_logger()->debug( print_r( 'Delete user schedular next date updated....', true ) );
		}

		/**
		 * Modify redirect url for role based redirection after login.
		 *
		 * @param string $redirect The redirect URL.
		 * @param object $user The user object.
		 * @param string $redirect_option The redirect option.
		 * @return string Modified redirect URL.
		 */
		public function role_based_redirection_after_login( $redirect, $user, $redirect_option ) {
			if ( 'role-based-redirection' !== $redirect_option ) {
				return $redirect;
			}

			if ( ! $user instanceof \WP_User || ! isset( $user->roles ) || empty( $user->roles ) ) {
				return $redirect;
			}

			$role_based_redirection = get_option( 'user_registration_login_options_after_login_role_based_redirection', array() );

			if ( empty( $role_based_redirection ) || ! is_array( $role_based_redirection ) ) {
				return $redirect;
			}

			foreach ( $user->roles as $role ) {
				$redirection_key = "user_registration_after_login_role_based_redirection-{$role}";

				foreach ( $role_based_redirection as $redirection_item ) {
					if ( ! is_array( $redirection_item ) || ! isset( $redirection_item['name'], $redirection_item['value'] ) ) {
						continue;
					}

					if ( $redirection_item['name'] === $redirection_key ) {
						$page_id = absint( $redirection_item['value'] );

						if ( $page_id > 0 && 'publish' === get_post_status( $page_id ) ) {
							$new_redirect = get_page_link( $page_id );

							if ( ! empty( $new_redirect ) && filter_var( $new_redirect, FILTER_VALIDATE_URL ) ) {
								$redirect = $new_redirect;
							}
						}
						break 2;
					}
				}
			}

			return $redirect;
		}

		/**
		 * Modify redirect url for role based redirection after logout.
		 *
		 * @param string $redirect The redirect URL.
		 * @param string $redirect_option The redirect option.
		 * @return string Modified redirect URL.
		 */
		public function role_based_redirection_after_logout( $redirect, $redirect_option ) {
			if ( 'role-based-redirection' !== $redirect_option ) {
				return $redirect;
			}

			$role_based_redirection = get_option( 'user_registration_login_options_after_logout_role_based_redirection', array() );

			if ( empty( $role_based_redirection ) || ! is_array( $role_based_redirection ) ) {
				return $redirect;
			}

			if ( ! is_user_logged_in() ) {
				return $redirect;
			}

			$user = wp_get_current_user();

			if ( ! isset( $user->roles ) || empty( $user->roles ) ) {
				return $redirect;
			}

			foreach ( $user->roles as $role ) {
				$redirection_key = "user_registration_after_logout_role_based_redirection-{$role}";

				foreach ( $role_based_redirection as $redirection_item ) {
					if ( ! is_array( $redirection_item ) || ! isset( $redirection_item['name'], $redirection_item['value'] ) ) {
						continue;
					}

					if ( $redirection_item['name'] === $redirection_key ) {
						$page_id = absint( $redirection_item['value'] );

						if ( $page_id > 0 && 'publish' === get_post_status( $page_id ) ) {
							$new_redirect = get_page_link( $page_id );

							if ( ! empty( $new_redirect ) && filter_var( $new_redirect, FILTER_VALIDATE_URL ) ) {
								$redirect = $new_redirect;
							}
						}
						break 2;
					}
				}
			}

			return $redirect;
		}
	}
endif;

User_Registration_Pro::get_instance();
