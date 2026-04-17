<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization and
 * all module hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace LdGroupRegistration\Includes;

use \LdGroupRegistration\Includes\Ld_Group_Registration_Loader as Ld_Group_Registration_Loader;
use \LdGroupRegistration\Includes\Ld_Group_Registration_I18n as Ld_Group_Registration_I18n;
use \LdGroupRegistration\Includes\Ld_Group_Registration_Activator as Ld_Group_Registration_Activator;
use \LdGroupRegistration\Includes\Ld_Group_Registration_License as Ld_Group_Registration_License;

use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Groups as Ld_Group_Registration_Groups;
use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Settings as Ld_Group_Registration_Settings;
use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Subscriptions as Ld_Group_Registration_Subscriptions;
use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Users as Ld_Group_Registration_Users;
use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Edd as Ld_Group_Registration_Edd;
use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Woocommerce as Ld_Group_Registration_Woocommerce;
use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Reports as Ld_Group_Registration_Reports;
use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Group_Code as Ld_Group_Registration_Group_Code;
use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Group_Code_Registration as Ld_Group_Registration_Group_Code_Registration;
use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Unlimited_Members as Ld_Group_Registration_Unlimited_Members;

/**
 * LD Group Registration class
 */
class Ld_Group_Registration {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    4.0
	 * @access   protected
	 * @var      Ld_Group_Registration_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    4.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    4.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    4.0
	 */
	public function __construct() {
		if ( defined( 'LD_GROUP_REGISTRATION_VERSION' ) ) {
			$this->version = LD_GROUP_REGISTRATION_VERSION;
		} else {
			$this->version = '4.1.0';
		}
		$this->plugin_name = 'ld-group-registration';

		$this->load_dependencies();
		$this->define_licenses();
		$this->handle_activation();
		$this->set_locale();

		/*
		// Licensing activation check - To be used for restricting features wrt license status.
		if ( Ld_Group_Registration_License::is_available_license() ) {
			// Put code that requires the license to be active here.
		}
		*/
		$this->define_module_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ld_Group_Registration_Loader. Orchestrates the hooks of the plugin.
	 * - Ld_Group_Registration_I18n. Defines internationalization functionality.
	 * - Ld_Group_Registration_Admin. Defines all hooks for the admin area.
	 * - Ld_Group_Registration_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    4.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for handling licensing functionalities of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'licensing/class-wdm-license.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-ld-group-registration-license.php';

		/**
		 * The class responsible for handling activation functionalities of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-ld-group-registration-activator.php';

		/**
		 * The class responsible for handling deactivation functionalities of the
		 * plugin.
		 */
		// require_once plugin_dir_path( __DIR__ ) . 'includes/class-ld-group-registration-deactivator.php';.

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ld-group-registration-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ld-group-registration-i18n.php';

		/**
		 * The file responsible for defining common functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/ld-group-registration-functions.php';

		/**
		 * The file responsible for defining common static variables
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/ld-group-registration-constants.php';

		/**
		 * The file responsible for handling deprecated functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/ld-group-registration-deprecated.php';

		// Load Modules.

		/**
		 * The class responsible for defining all actions to control group related functionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/classes/class-ld-group-registration-groups.php';

		/**
		 * The class responsible for defining all actions to control settings related functionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/classes/class-ld-group-registration-settings.php';

		/**
		 * The class responsible for defining all actions to control user related functionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/classes/class-ld-group-registration-users.php';

		/**
		 * The class responsible for defining all actions to control easy digital downloads related functionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/classes/class-ld-group-registration-edd.php';

		/**
		 * The class responsible for defining all actions to control woocommerce related functionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/classes/class-ld-group-registration-woocommerce.php';

		/**
		 * The class responsible for defining all actions to control woocommerce subscriptions related functionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/classes/class-ld-group-registration-subscriptions.php';

		/**
		 * The class responsible for defining all actions to control reports related functionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/classes/class-ld-group-registration-reports.php';

		/**
		 * The class responsible for defining all actions to control group code related functionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/classes/class-ld-group-registration-group-code.php';

		/**
		 * The class responsible for defining all actions to control group code registration related functionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/classes/class-ld-group-registration-group-code-registration.php';

		/**
		 * The class responsible for defining all actions to control group code registration related functionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/classes/class-ld-group-registration-unlimited-members.php';

		$this->loader = new Ld_Group_Registration_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ld_Group_Registration_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    4.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ld_Group_Registration_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Define the licensing for this plugin.
	 */
	private function define_licenses() {
		$plugin_license = new Ld_Group_Registration_License();

		$this->loader->add_action( 'plugins_loaded', $plugin_license, 'load_license' );
	}

	/**
	 * Handle plugin activation
	 */
	private function handle_activation() {
		$plugin_activator = new Ld_Group_Registration_Activator();

		$this->loader->add_action( 'init', $plugin_activator, 'activate' );
		$this->loader->add_action( 'admin_init', $plugin_activator, 'admin_activate' );
		$this->loader->add_action( 'in_plugin_update_message-woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', $plugin_activator, 'handle_update_notices', 10, 2 );
	}

	/**
	 * Register all of the module hooks
	 *
	 * @since    4.0
	 * @access   private
	 */
	private function define_module_hooks() {

		$modules = array(
			'groups',
			'settings',
			'subscriptions',
			'users',
			'edd',
			'woocommerce',
			'reports',
			'group_code',
			'group_code_registration',
			'unlimited_members'
		);

		foreach ( $modules as $module ) {
			call_user_func( array( $this, 'define_' . $module . '_module_hooks' ) );
		}
	}

	/**
	 * Register all of the hooks related to the groups module functionality
	 * of the plugin.
	 *
	 * @since    4.0
	 * @access   private
	 */
	private function define_groups_module_hooks() {
		$plugin_groups = new Ld_Group_Registration_Groups();

		$this->loader->add_filter( 'manage_edit-groups_columns', $plugin_groups, 'add_column_heading', 20, 1 );
		$this->loader->add_action( 'manage_posts_custom_column', $plugin_groups, 'add_column_data', 20, 2 );

		$this->loader->add_action( 'wp', $plugin_groups, 'handle_group_enrollment_form' );
		$this->loader->add_action( 'before_delete_post', $plugin_groups, 'handle_group_deletion' );
		$this->loader->add_action( 'wp_ajax_wdm_group_unenrollment', $plugin_groups, 'handle_group_unenrollment' );
		$this->loader->add_action( 'wp_ajax_bulk_unenrollment', $plugin_groups, 'handle_bulk_remove_group_users' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_groups, 'add_groups_metaboxes' );
		$this->loader->add_action( 'save_post_groups', $plugin_groups, 'handle_registrations_left_save', 100 );
		$this->loader->add_action( 'wp_ajax_wdm_ld_group_request_accept', $plugin_groups, 'handle_accept_request', 100 );
		$this->loader->add_action( 'wp_ajax_wdm_ld_group_request_reject', $plugin_groups, 'handle_reject_request', 100 );
		$this->loader->add_action( 'wp_ajax_bulk_group_request_accept', $plugin_groups, 'handle_bulk_accept_request', 100 );
		$this->loader->add_action( 'wp_ajax_bulk_group_request_reject', $plugin_groups, 'handle_bulk_reject_request', 100 );
		$this->loader->add_action( 'wdm_group_limit_is_zero', $plugin_groups, 'handle_group_limit_empty' );

		// Reinvite Ajax call.
		$this->loader->add_action( 'wp_ajax_wdm_send_reinvite_mail', $plugin_groups, 'send_reinvite_mail_callback' );

		// Upload Users CSV Ajax Call.
		$this->loader->add_action( 'wp_ajax_wdm_upload_users_csv', $plugin_groups, 'ajax_upload_users_from_csv' );

		// Edit Group Name.
		$this->loader->add_action( 'wp_ajax_ldgr_update_group_details', $plugin_groups, 'ajax_update_group_details' );

		// Shortcode for group users.
		$this->loader->add_action( 'init', $plugin_groups, 'add_groups_shortcodes' );
	}

	/**
	 * Register all of the hooks related to the settings module functionality
	 * of the plugin.
	 *
	 * @since    4.0
	 * @access   private
	 */
	private function define_settings_module_hooks() {
		$plugin_settings = new Ld_Group_Registration_Settings();

		$this->loader->add_action( 'admin_menu', $plugin_settings, 'add_settings_menu', 100 );
		$this->loader->add_filter( 'plugin_action_links_' . plugin_basename( WDM_LDGR_PLUGIN_FILE ), $plugin_settings, 'add_settings_page_link' );
		$this->loader->add_filter( 'login_redirect', $plugin_settings, 'handle_wp_login_redirect', 10, 3 );
		$this->loader->add_filter( 'woocommerce_login_redirect', $plugin_settings, 'handle_woo_login_redirect', 10, 2 );
		$this->loader->add_filter( 'ldgr_setting_tab_headers', $plugin_settings, 'add_feedback_and_other_setting_tab_header', 100, 1 );
		$this->loader->add_action( 'ldgr_settings_tab_content_end', $plugin_settings, 'display_feedback_tab_contents', 100, 1 );
	}

	/**
	 * Register all of the hooks related to the subscriptions module functionality
	 * of the plugin.
	 *
	 * @since    4.0
	 * @access   private
	 */
	private function define_subscriptions_module_hooks() {
		$plugin_subscriptions = new Ld_Group_Registration_Subscriptions();

		$this->loader->add_action( 'woocommerce_product_after_variable_attributes', $plugin_subscriptions, 'handle_variation_settings_fields', 10, 3 );
		$this->loader->add_action( 'woocommerce_save_product_variation', $plugin_subscriptions, 'save_variation_settings_fields', 10, 2 );
	}

	/**
	 * Register all of the hooks related to the users module functionality
	 * of the plugin.
	 *
	 * @since    4.0
	 * @access   private
	 */
	private function define_users_module_hooks() {
		$plugin_users = new Ld_Group_Registration_Users();

		$this->loader->add_action( 'woocommerce_subscription_status_on-hold', $plugin_users, 'restrict_users_after_sub_put_on_hold' );
		$this->loader->add_action( 'woocommerce_subscription_status_cancelled', $plugin_users, 'restrict_users_after_sub_put_on_hold' );
		$this->loader->add_action( 'woocommerce_subscription_status_expired', $plugin_users, 'restrict_users_after_sub_put_on_hold' );
		$this->loader->add_action( 'woocommerce_subscription_status_active', $plugin_users, 'give_access_to_users_after_sub_active' );
		$this->loader->add_action( 'wdm_created_new_group_using_ldgr', $plugin_users, 'save_additional_data', 10, 3 );
		/**
		 * Commented below filter hook callback, since it adds the complete subscription title before the group name
		 * and the reason to have done this is not known
		 *
		 * @since 3.8.2
		 */
		// $this->loader->add_filter('wdm_modify_ldgr_group_title', array($plugin_users, 'modify_product_title_on_grp_reg_page'), 10, 2);
		$this->loader->add_filter( 'ld_woocommerce_add_subscription_course_access', $plugin_users, 'handle_group_leader_paid_course_access', 10, 3 );
	}

	/**
	 * Register all of the hooks related to the edd module functionality
	 * of the plugin.
	 *
	 * @since    4.0
	 * @access   private
	 */
	private function define_edd_module_hooks() {
		$plugin_edd = new Ld_Group_Registration_Edd();

		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_edd, 'enqueue_styles' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_edd, 'add_product_meta_box' );
		$this->loader->add_action( 'edd_save_download', $plugin_edd, 'save_metabox_settings', 100 );
		$this->loader->add_action( 'edd_purchase_link_top', $plugin_edd, 'display_group_purchase_options', 100, 2 );
		$this->loader->add_filter( 'edd_add_to_cart_item', $plugin_edd, 'save_edd_cart_item_data', 100, 1 );
		$this->loader->add_filter( 'edd_get_cart_item_name', $plugin_edd, 'add_edd_cart_item_name', 100, 3 );
		$this->loader->add_action( 'edd_complete_purchase', $plugin_edd, 'create_group_on_course_payment_complete', 1000, 1 );
		// Add additional group fields on download single page.
		$this->loader->add_filter( 'edd_purchase_form_quantity_input', $plugin_edd, 'add_additional_group_details', 10, 3 );
		// Update Group Title if customer set a different name for it.
		$this->loader->add_filter( 'wdm_group_name', $plugin_edd, 'update_group_title', 10, 5 );
	}

	/**
	 * Register all of the hooks related to the woocommerce module functionality
	 * of the plugin.
	 *
	 * @since    4.0
	 * @access   private
	 */
	private function define_woocommerce_module_hooks() {
		$plugin_woocommerce = new Ld_Group_Registration_Woocommerce();

		$this->loader->add_action( 'woocommerce_order_status_completed', $plugin_woocommerce, 'handle_group_creation_on_order_completion', 100, 1 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_woocommerce, 'add_group_purchase_metabox' );
		$this->loader->add_action( 'save_post_product', $plugin_woocommerce, 'save_group_purchase_options', 100 );
		$this->loader->add_action( 'woocommerce_before_add_to_cart_button', $plugin_woocommerce, 'display_woo_group_registration_options', 100 );
		// Store the custom fields.
		$this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_woocommerce, 'save_cart_item_data', 10, 2 );
		$this->loader->add_filter( 'woocommerce_get_cart_item_from_session', $plugin_woocommerce, 'check_group_registration_status_for_product', 1, 3 );
		$this->loader->add_action( 'woocommerce_add_order_item_meta', $plugin_woocommerce, 'update_woo_order_item_meta', 1, 2 );
		$this->loader->add_filter( 'woocommerce_cart_item_name', $plugin_woocommerce, 'woo_update_cart_item_name', 10, 3 );
		$this->loader->add_filter( 'woocommerce_cart_item_quantity', $plugin_woocommerce, 'woo_update_cart_item_quantity', 10, 3 );
		$this->loader->add_filter( 'woocommerce_add_to_cart_validation', $plugin_woocommerce, 'handle_woo_add_to_cart_validation', 10, 3 );
		$this->loader->add_action( 'wp_ajax_wdm_show_enroll_option', $plugin_woocommerce, 'ajax_show_enroll_option_callback' );
		$this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_woocommerce, 'woo_add_group_details', 10 );

		// Update Group Title if customer set a different name for it.
		$this->loader->add_filter( 'wdm_group_name', $plugin_woocommerce, 'woo_update_group_title', 10, 5 );

		// Hide group registration order meta on cart, checkout and order pages.
		$this->loader->add_filter( 'woocommerce_hidden_order_itemmeta', $plugin_woocommerce, 'hide_admin_group_reg_order_meta' );
	}

	/**
	 * Register all of the hooks related to the reports module functionality
	 * of the plugin.
	 *
	 * @since    4.0
	 * @access   private
	 */
	private function define_reports_module_hooks() {
		$plugin_reports = new Ld_Group_Registration_Reports();

		// Ajax call for the table.
		$this->loader->add_action( 'wp_ajax_wdm_lgdr_create_report_table', $plugin_reports, 'create_report_table_callback' );
		// Ajax call for report.
		$this->loader->add_action( 'wp_ajax_wdm_display_ldgr_group_report', $plugin_reports, 'display_ldgr_group_report_callback' );
	}

	/**
	 * Register all of the hooks related to the group code module functionality
	 * of the plugin.
	 *
	 * @since    4.1.0
	 * @access   private
	 */
	private function define_group_code_module_hooks() {
		$plugin_group_code = new Ld_Group_Registration_Group_Code();

		// Settings
		$this->loader->add_filter( 'ldgr_setting_tab_headers', $plugin_group_code, 'add_group_code_setting_tab_header', 10, 1 );
		$this->loader->add_action( 'ldgr_settings_tab_content_end', $plugin_group_code, 'add_group_code_setting_tab_contents', 10, 1 );
		$this->loader->add_action( 'admin_menu', $plugin_group_code, 'save_group_code_settings', 100 );
		$this->loader->add_action( 'admin_menu', $plugin_group_code, 'add_group_code_submenu', 100 );
		
		// Check if settings enabled
		$ldgr_enable_group_code = get_option( 'ldgr_enable_group_code' );
		
		if ( 'on' != $ldgr_enable_group_code ) {
			return;
		}
		
		// Create the group code post type
		$this->loader->add_action( 'init', $plugin_group_code, 'create_group_code_post_type' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_group_code, 'enqueue_group_code_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_group_code, 'admin_enqueue_group_code_scripts' );
		$this->loader->add_action( 'save_post_ldgr_group_code', $plugin_group_code, 'admin_save_group_code', 10, 3 );
		$this->loader->add_filter( 'ldgr_filter_group_registration_tab_headers', $plugin_group_code, 'add_group_code_tab_header', 10, 2 );
		$this->loader->add_filter( 'ldgr_filter_group_registration_tab_contents', $plugin_group_code, 'add_group_code_tab_contents', 10, 2 );

		// Various group code ajax methods
		$this->loader->add_action( 'wp_ajax_ldgr-create-group-code', $plugin_group_code, 'ajax_create_group_code' );
		$this->loader->add_action( 'wp_ajax_ldgr-update-group-code', $plugin_group_code, 'ajax_update_group_code' );
		$this->loader->add_action( 'wp_ajax_ldgr-generate-group-code', $plugin_group_code, 'ajax_generate_group_code' );
		$this->loader->add_action( 'wp_ajax_ldgr-delete-group-code', $plugin_group_code, 'ajax_delete_group_code' );
		$this->loader->add_action( 'wp_ajax_ldgr-group-code-status-toggle', $plugin_group_code, 'ajax_group_code_status_toggle' );
		$this->loader->add_action( 'wp_ajax_ldgr-fetch-group-code-details', $plugin_group_code, 'ajax_fetch_group_code_details' );

	}

	/**
	 * Register all of the hooks related to the group code registration module functionality
	 * of the plugin.
	 *
	 * @since    4.1.0
	 * @access   private
	 */
	private function define_group_code_registration_module_hooks() {
		$plugin_group_code_registration = new Ld_Group_Registration_Group_Code_Registration();

		// Check if group code settings enabled
		$ldgr_enable_group_code = get_option( 'ldgr_enable_group_code' );
		
		if ( 'on' != $ldgr_enable_group_code ) {
			return;
		}

		// Shortcode for group code registration.
		$this->loader->add_action( 'init', $plugin_group_code_registration, 'add_group_code_registration_shortcodes' );

		// Enqueue scripts and styles
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_group_code_registration, 'enqueue_group_code_registration_scripts' );

		// Ajax handler for group code reg form submission
		$this->loader->add_action( 'wp_ajax_nopriv_ldgr-submit-group-code-reg-form', $plugin_group_code_registration, 'ajax_submit_group_code_reg_form' );
		$this->loader->add_action( 'wp_ajax_nopriv_ldgr-submit-group-code-enroll-form', $plugin_group_code_registration, 'ajax_submit_group_code_enroll_form' );
		$this->loader->add_action( 'wp_ajax_ldgr-submit-group-code-enroll-form', $plugin_group_code_registration, 'ajax_submit_group_code_enroll_form' );
	}

	/**
	 * Register all of the hooks related to the unlimited members module functionality
	 * of the plugin.
	 *
	 * @since    4.1.0
	 * @access   private
	 */
	private function define_unlimited_members_module_hooks() {
		$plugin_unlimited_members = new Ld_Group_Registration_Unlimited_Members();

		// * Add and Save Metaboxes on product create/edit page for unlimited groups
		$this->loader->add_action('save_post_product', $plugin_unlimited_members, 'save_unlimited_member_settings', 100);

		// * Handle Product single page
		$this->loader->add_action('woocommerce_before_add_to_cart_button', $plugin_unlimited_members, 'display_unlimited_members_product_options', 100);

		// * Handle Cart Page
		// * Save cart and order meta
		$this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_unlimited_members, 'save_unlimited_members_product_options', 99, 2 );
		$this->loader->add_filter( 'woocommerce_get_item_data', $plugin_unlimited_members, 'render_details_on_cart_and_checkout', 99, 2 );
		$this->loader->add_action( 'woocommerce_add_order_item_meta', $plugin_unlimited_members, 'update_order_meta_details', 99, 3 );
		$this->loader->add_filter( 'woocommerce_hidden_order_itemmeta', $plugin_unlimited_members, 'hide_unlimited_seats_order_meta', 10, 1 );

		// * Dyanamically update price
		$this->loader->add_action( 'woocommerce_before_calculate_totals', $plugin_unlimited_members, 'calculate_unlimited_members_product_price', 99 );

		// Disable quantity for unlimited members products
		$this->loader->add_filter('woocommerce_cart_item_quantity', $plugin_unlimited_members, 'remove_quantity_for_unlimited_member_products', 10, 3);
		// * Handle meta for unlimited groups after group creation
		$this->loader->add_filter('wdm_change_group_quantity', $plugin_unlimited_members, 'update_group_quantity_to_unlimited', 10, 4);

		$this->loader->add_action( 'ldgr_action_after_update_group', $plugin_unlimited_members, 'update_group_meta_for_unlimited_seats', 10, 5 );
		$this->loader->add_action( 'ldgr_action_after_create_group', $plugin_unlimited_members, 'update_group_meta_for_unlimited_seats', 10, 5 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    4.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ld_Group_Registration_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
