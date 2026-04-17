<?php
/**
 * UserRegistrationSocialConnect Admin.
 *
 * @class    URSC_Admin
 * @version  1.0.0
 * @package  UserRegistrationSocialConnect/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URSC_Admin Class
 */
class URSC_Admin {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		ursc_check_plugin_compatibility();
		$message = ursc_is_compatible();
		if ( $message !== 'YES' ) {
			return;
		}

		add_filter( 'user_registration_get_settings_pages', array( $this, 'add_social_connect' ), 10, 1 );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_reporting_widget' ) );
		add_filter( 'user_registration_get_form_settings', array( $this, 'add_social_connect_in_form_setting' ) );
	}

	/**
	 * Handle redirects to setup/welcome page after install and updates.
	 *
	 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
	 */
	public function admin_redirects() {
		// Setup wizard redirect
		if ( get_transient( '_ursc_activation_redirect' ) ) {
			delete_transient( '_ursc_activation_redirect' );

			if ( ( is_network_admin() || isset( $_GET['activate-multi'] ) ) || ! current_user_can( 'manage_options' ) || apply_filters( 'ursc_prevent_activation_redirect', false ) ) {
				return;
			}

			$message = ursc_is_compatible();
			if ( 'YES' == $message ) {
				// If the user needs to install, send them to the settings page.
				wp_safe_redirect( admin_url( 'admin.php?page=user-registration-settings&tab=social_connect' ) );
				exit;
			}
		}
	}

	/**
	 * @param $settings
	 *
	 * @return array
	 */
	public function add_social_connect( $settings ) {

		if ( class_exists( 'UR_Settings_Page' ) ) {
			$settings[] = include 'settings/class-ursc-settings-social.php';
		}

		return $settings;
	}


	public function add_dashboard_reporting_widget() {

		if ( ! current_user_can( 'manage_user_registration' ) ) {
			return;
		}

		wp_add_dashboard_widget( 'user_registration_social_connect_dashboard_reporting', __( 'User Registration Social Signup', 'user-registration-social-connect' ), array( $this, 'show_chart_reporting' ) );
	}

	public function show_chart_reporting() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'user-registration-social-connect-dashboard-widget-js', plugins_url( 'assets/js/dashboard-chart-report-widget' . $suffix . '.js', URSC_PLUGIN_FILE ), array( 'jquery', 'chartjs' ), URSC_VERSION );
		wp_enqueue_style( 'user-registration-social-connect-dashboard-widget-css', plugins_url( 'assets/css/dashboard-chart-report-widget' . $suffix . '.css', URSC_PLUGIN_FILE ), array(), URSC_VERSION );
		wp_localize_script(
			'user-registration-dashboard-widget-js',
			'ursc_widget_params',
			array(
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
				'widget_nonce' => wp_create_nonce( 'dashboard-chart-widget' ),
			)
		);
		include plugin_dir_path( URSC_PLUGIN_FILE ) . 'includes/views/dashboard-chart-widget.php';
	}

	/**
	 * Add social connect option in form user registration form settings
	 */

	 public function add_social_connect_in_form_setting($arguments) {

		$social_networks = user_registration_social_networks();
		$options = array();
		foreach ( $social_networks as $network_key => $network_data ) {
			if ( 'yes' === get_option( $network_data['enable_id'] ) ) {
				$options[ ucfirst( $network_key ) ] = __( ucfirst($network_key), 'user-registration-social-connect' );
			}
		}

		 $social_btn = array(
			'type'              => 'multiselect',
			'label'             => __( 'Social Connect Option', 'user-registration-social-connect' ),
			'description'       => '',
			'required'          => false,
			'id'                => 'user_registration_social_connect_btn',
			'class'             => array( 'ur-enhanced-select' ),
			'input_class'       => array(),
			'options'           => $options,
			'custom_attributes' => array(),
			'default'           => ur_get_single_post_meta( $arguments['form_id'], 'user_registration_social_connect_btn',$options ),
			'tip'               => __( 'Choose social connect to use.', 'user-registration-social-connect' ),
		);

		array_push( $arguments['setting_data'],$social_btn );
		return $arguments;

	 }
}

return new URSC_Admin();
