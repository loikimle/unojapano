<?php
/**
 * UserRegistrationWooCommerce Admin.
 *
 * @class    URWC_Admin
 * @version  1.0.0
 * @package  UserRegistrationWooCommerce/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URWC_Admin Class
 */
class URWC_Admin {

	/**
	 * URWC_Admin Constructor
	 */
	public function __construct() {
		$message = urwc_check_plugin_compatibility();
		$message = urwc_is_compatible();

		if ( 'YES' !== $message ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_filter( 'user_registration_query_vars', array( $this, 'user_registration_query_vars' ) );
		add_action( 'user_registration_extra_fields', array( $this, 'user_registration_woocommerce_fields' ) );
		add_filter( 'user_registration_get_settings_pages', array( $this, 'add_woocommerce_setting' ), 10, 1 );
		add_filter( 'user_registration_billing_country_advance_class', array( $this, 'urwc_country_field_advance_settings_class_path' ) );
		add_filter( 'user_registration_shipping_country_advance_class', array( $this, 'urwc_country_field_advance_settings_class_path' ) );

		$woocommerce_fields = ur_get_all_woocommerce_fields();

		foreach ( $woocommerce_fields as $field ) {
			add_filter( 'user_registration_' . $field . '_admin_template', array( $this, 'user_registration_wc_fields_admin_template_includes' ), 10, 1 );
		}
		add_filter( 'user_registration_field_options_general_settings', array( $this, 'field_settings' ), 10, 2 );
		add_filter( 'user_registration_csv_export_columns', array( $this, 'modify_woocommerce_field_label' ), 10, 1 );
		add_filter( 'user_registration_csv_export_exclude_columns', array( $this, 'exclude_fields_to_export' ), 10, 1 );
	}

	/**
	 * Exclude woocommerce some field from exporting users.
	 *
	 * @param array $column $column.
	 */
	public function exclude_fields_to_export( $column ) {
		$exclude_fields = array(
			'billing_address_title',
			'shipping_address_title',
		);
		return array_merge( $column, $exclude_fields );
	}

	/**
	 * Specify field type in woocommerce field label while exporting user.
	 *
	 * @param array $column $column.
	 */
	public function modify_woocommerce_field_label( $column ) {
		foreach ( $column as $field_name => $label ) {
			$billing = strpos( $field_name, 'billing_' );
			$shipping = strpos( $field_name, 'shipping_' );

			if ( false !== $billing ) {
				$column[ $field_name ] = 'Billing ' . $label;
			} elseif ( false !== $shipping ) {
				$column[ $field_name ] = 'Shipping ' . $label;
			}
		}
		return $column;
	}

	/**
	 * Get country field advance settings class path.
	 *
	 * @param array $file_path_array Path Array.
	 */
	public function urwc_country_field_advance_settings_class_path( $file_path_array ) {
		$file_path_array['file_path'] = URWC_FORM_PATH . 'settings' . URWC_DS . 'class-ur-setting-' . $file_path_array['file_name'] . '.php';
		return $file_path_array;
	}

	/**
	 * Html for WooCommerece Address Fields
	 *
	 * @return void
	 */
	public function user_registration_woocommerce_fields() {
		echo '<h2 class="ur-toggle-heading">' . esc_html__( 'WooCommerce Billing Address', 'user-registration-woocommerce' ) . '</h2><hr/>';
		$this->get_woocommerce_billing_fields();
		echo '<h2 class="ur-toggle-heading">' . esc_html__( 'WooCommerce Shipping Address', 'user-registration-woocommerce' ) . '</h2><hr/>';
		$this->get_woocommerce_shipping_fields();
	}

	/**
	 * Billing Address fields
	 *
	 * @return void
	 */
	public function get_woocommerce_billing_fields() {
			$registered_form_fields = function_exists( 'ur_get_woocommerce_billing_fields' ) ? ur_get_woocommerce_billing_fields() : array();
			echo ' <ul id = "ur-draggabled" class="ur-registered-list ur-woocommerce-billing-fields" > ';
		foreach ( $registered_form_fields as $field ) {
			$get_list = new UR_Admin_Menus();
			$get_list->ur_get_list( $field );
		}
			echo ' </ul > ';
	}

	/**
	 * Shipping Address fields
	 *
	 * @return void
	 */
	public function get_woocommerce_shipping_fields() {
		$registered_form_fields = function_exists( 'ur_get_woocommerce_shipping_fields' ) ? ur_get_woocommerce_shipping_fields() : array();
		echo ' <ul id = "ur-draggabled" class="ur-registered-list ur-woocommerce-shipping-fields" > ';
		foreach ( $registered_form_fields as $field ) {
			$get_list = new UR_Admin_Menus();
			$get_list->ur_get_list( $field );
		}
		echo ' </ul > ';
	}

	/**
	 * Modify general settings.
	 *
	 * @param array  $general_settings Setting for field.
	 * @param string $id field.
	 *
	 * @return  array $general_settings
	 */
	public function field_settings( $general_settings, $id ) {

		switch ( $id ) {
			case 'user_registration_billing_address_title':
			case 'user_registration_shipping_address_title':
				$remove_keys = array( 'placeholder', 'required', 'hide_label' );
				foreach ( $remove_keys as $remove_key ) {
					unset( $general_settings[ $remove_key ] );
				}
				break;

			case 'user_registration_billing_country':
			case 'user_registration_separate_shipping':
			case 'user_registration_shipping_country':
				unset( $general_settings['placeholder'] );
				break;
		}

		return $general_settings;
	}

	/**
	 * Include WooCommerce Setting tab.
	 *
	 * @param array $settings Settings.
	 */
	public function add_woocommerce_setting( $settings ) {
		if ( class_exists( 'UR_Settings_Page' ) ) {
			$settings[] = include 'settings/class-urwc-settings-woocommerce.php';
		}
	}

	/**
	 * Get woocommerce_myaccount orders endpoint in query var
	 *
	 * @param array $vars vars.
	 */
	public function user_registration_query_vars( $vars ) {
		$vars['wc-orders'] = get_option( 'woocommerce_myaccount_orders_endpoint', 'wc-orders' );
		return $vars;
	}

	/**
	 * Register Admin Styles
	 */
	public function admin_styles() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		wp_register_style( 'user-registration-woocommerce-admin-style', URWC()->plugin_url() . '/assets/css/user-registration-woocommerce-admin-style.css', array(), URWC_VERSION );
		if ( 'user-registration_page_user-registration-settings' === $screen_id ) {
			wp_enqueue_style( 'user-registration-woocommerce-admin-style' );
		}
	}

	/**
	 * Register Admin Scripts
	 */
	public function admin_scripts() {
		$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		wp_register_script( 'urwc-admin-script', URWC()->plugin_url() . '/assets/js/admin/urwc-admin' . $suffix . '.js', array(), URWC_VERSION );

		if ( 'user-registration_page_user-registration-settings' === $screen_id ) {
			wp_enqueue_script( 'urwc-admin-script' );
			wp_localize_script(
				'urwc-admin-script',
				'user_registration_woocommerce_params',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'user_registration_woocommerce_form_field_listing' => wp_create_nonce( 'user_registration_woocommerce_form_field_listing_nonce' ),
				)
			);
		}
	}

	/**
	 * Include Path wc fields admin template.
	 *
	 * @param string $path Path.
	 *
	 * @return WooCommerce Path
	 */
	public function user_registration_wc_fields_admin_template_includes( $path ) {

		$core_path  = UR_ABSPATH;
		$addon_path = URWC_ABSPATH;
		$path       = str_replace( $core_path, $addon_path, $path );

		return $path;
	}
}

return new URWC_Admin();
