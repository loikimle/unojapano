<?php
/**
 * Local Currency
 *
 * Local\ Currency Main Page
 *
 * @class    Local_Currency
 * @package  Local_Currency
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Local_Currency;

use WPEverest\URMembership\Local_Currency\Admin\Ajax;
use WPEverest\URMembership\Local_Currency\Admin\CoreFunctions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local_Currency
 */
class Local_Currency {

	/**
	 * Page.
	 */
	protected $page = '';

	/**
	 * Post type.
	 */
	protected $post_type = '';

	/**
	 * Initialize hooks and assign protected variables;
	 */
	public function __construct() {
		$this->page      = 'user-registration-local-currency';
		$this->post_type = 'local_currency';
		$this->init_hooks();

		if ( is_admin() ) {
			new Ajax();
		}
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 * @since 6.0.0
	 */
	private function init_hooks() {
		add_filter("user_registration_get_settings_payment", array( $this, 'add_local_currency_setting' ) );
		add_action( 'user_registration_settings_save_payment' , array( $this, 'save' ) );
		add_filter( "user_registration_get_sections_payment", array( $this, 'get_settings_callback' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'delete_pricing_zone' ) );
	}

	public function get_settings_callback( $sections ){
		$sections[ 'local-currency' ] = __( 'Local Currency', 'user-registration' );
		return $sections;
	}

	/**
	 * Add local currency settings.
	 *
	 * @since 6.0.0
	 */
	public function add_local_currency_setting( $settings ){
		global $current_section;
		if ( 'local-currency' === $current_section ) {
			add_filter( 'user_registration_settings_hide_save_button', '__return_false' );

			$settings = $this->get_local_currencies_settings();
			// $GLOBALS['hide_save_button'] = true;
		}

		return $settings;
	}

	/**
	 * Get local currency settings.
	 *
	 * @since 6.0.0
	 */
	public function get_local_currencies_settings(){
		$country_lists = ur_get_country_lists();
		$settings = apply_filters(
			'user_registration_get_local_currencies_settings',
			array(
				'title'    => '',
				'sections' => array(
					'local_currency_settings' => array(
						'title'    => esc_html__( 'Local Currency Settings', 'user-registration' ),
						'type'     => 'card',
						'desc'     => '',
						'settings' => array(
							 array(
                                'title'    => __( 'Switch currency option', 'user-registration' ),
                                'desc'     => __( '', 'user-registration' ),
                                'id'       => 'user_registration_switch_local_currency_option',
                                'type'     => 'toggle',
                                'desc_tip' => true,
                                'css'      => '',
                                'default'  => 'no',
                            ),
							 array(
                                'title'    => __( 'Enable Geolocation', 'user-registration' ),
                                'desc'     => __( '', 'user-registration' ),
                                'id'       => 'user_registration_local_currency_by_geolocation',
                                'type'     => 'toggle',
                                'desc_tip' => true,
                                'css'      => '',
                                'default'  => 'no',
                            ),
							 array(
								'title'    => __( 'MaxMind Account ID', 'user-registration' ),
								'desc'     => __( 'MaxMind Account ID.', 'user-registration' ),
								'id'       => 'user_registration_max_mind_account_id',
								'type'     => 'text',
								'css'      => 'min-width: 350px',
								'desc_tip' => true,
								'default'  => '',
							),
							 array(
								'title'    => __( 'MaxMind License Key', 'user-registration' ),
								'desc'     => __( 'MaxMind License Key.', 'user-registration' ),
								'id'       => 'user_registration_max_mind_key',
								'type'     => 'password',
								'css'      => 'min-width: 350px',
								'desc_tip' => true,
								'default'  => '',
							),
							 array(
                                'title'    => __( 'Enable Test Mode', 'user-registration' ),
                                'desc'     => __( '', 'user-registration' ),
                                'id'       => 'user_registration_local_currency_by_geolocation_test_mode',
                                'type'     => 'toggle',
                                'desc_tip' => true,
                                'css'      => '',
                                'default'  => 'no',
                            ),
							array(
								'title'    => __( 'Country', 'user-registration' ),
								'desc'     => __( '', 'user-registration' ),
								'id'       => 'user_registration_local_currency_test_country',
								'default'  => 'US',
								'type'     => 'select',
								'class'    => 'ur-enhanced-select',
								'css'      => '',
								'desc_tip' => true,
								'options'  => $country_lists,
							),
							 array(
								'title'    => __( 'OpenExchange License Key', 'user-registration' ),
								'desc'     => __( 'OpenExchange is for automatic currency conversion.', 'user-registration' ),
								'id'       => 'user_registration_open_exchange_key',
								'type'     => 'password',
								'css'      => 'min-width: 350px',
								'desc_tip' => true,
								'default'  => '',
							),
							array(
								'title'              => __( 'Pricing Zone', 'user-registration' ),
								'desc'               => __( 'Pricing Zone', 'user-registration' ),
								'id'                 => 'user_registration_local_currencies',
								'default'            => '',
								'type'               => 'local_currency',
								'css'                => '',
								'desc_tip'           => true
							)
						),
					),
				),
			)
		);

		/**
		 * Filter to get the settings.
		 *
		 * @param array $settings Frontend Message Setting options to be enlisted.
		 */
		return apply_filters( 'user_registration_get_local_currencies_settings_payment', $settings );
	}

	/**
	 * Save settings
	 *
	 * @since 6.0.0
	 */
	public function save() {
		global $current_section;
		if ( 'local-currency' === $current_section ) {
			$settings = $this->get_local_currencies_settings();
			\UR_Admin_Settings::save_fields( $settings );
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';

		if ( isset( $_GET['section'] ) && 'local-currency' === $_GET['section'] ) {
			wp_register_script(
				'urm_local_currency_admin_script',
				UR()->plugin_url() . '/includes/pro/addons/Local_Currency/assets/user-registration-local-currency' . $suffix . '.js',
				array('jquery', 'sweetalert2', 'ur-enhanced-select'),
				UR_VERSION,
				true
			);

			wp_enqueue_script('urm_local_currency_admin_script');
			wp_localize_script(
				'urm_local_currency_admin_script',
				'urm_local_currency_admin_script_data',
				array(
					'ajax_url'                		 => admin_url( 'admin-ajax.php' ),
					'user_registration_pricing_zone' => wp_create_nonce( 'user_registration_pricing_zone' ),
					'i18n_add_btn_text'				 => __( 'Add', 'user-registration'),
					'i18n_delete_btn_title'			 => __( 'Delete pricing zone?', 'user-registration'),
					'i18n_delete_confirm'			 => __( 'Yes, Delete', 'user-registration'),
					'i18n_update_btn_text'			 => __( 'Update', 'user-registration'),
					'i18n_cancel'					 => __( 'Cancel', 'user-registration' ),
					'i18n_title'					 => __( 'Pricing Zone', 'user-registration' ),
					'i18n_delete_title'				 => __( 'Are you sure want to delete?'),
					'i18n_confirm_btn_text'			 => __( 'Confirm', 'user-registration' ),
					'create_form_template'			 => CoreFunctions::render_local_currencies_create_form(),
					)
				);
			}

			if ( isset( $_GET['page'] ) && 'user-registration-membership' === $_GET['page'] || (  isset( $_GET['section'] ) && 'local-currency' === $_GET['section'] ) ) {
				wp_register_style( 'ur-local-currency', UR()->plugin_url() . '/includes/pro/addons/Local_Currency/assets/css/user-registration-local-currency.css', array(), '1.0.0' );
				wp_enqueue_style( 'ur-local-currency' );
			}
	}

	public function delete_pricing_zone() {
		$section = isset( $_GET['section'] )
			? sanitize_text_field( wp_unslash( $_GET['section'] ) )
			: null;

		if ( 'local-currency' !== $section ) {
			return;
		}

		$action = isset( $_POST['action'] )
			? sanitize_text_field( wp_unslash( $_POST['action'] ) )
			: null;

		if ( 'delete' !== $action ) {
			return;
		}

		if ( empty( $_POST['currency_ids'] ) || ! is_array( $_POST['currency_ids'] ) ) {
			return;
		}

		$post_ids = array_map( 'absint', wp_unslash( $_POST['currency_ids'] ) );

		foreach ( $post_ids as $post_id ) {
			if ( $post_id > 0 ) {
				wp_delete_post( $post_id, true );
			}
		}
	}

}
