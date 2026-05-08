<?php
/**
 * Taxes
 *
 * Taxes Main Page
 *
 * @class    Taxes
 * @package  Taxes
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Taxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taxes
 */
class Taxes {
	/**
	 * Page name.
	 *
	 * @var string
	 */
	private $page;

	/**
	 * Post type.
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * Initialize hooks and assign protected variables;
	 */
	public function __construct() {
		$this->page      = 'user-registration-taxes';
		$this->post_type = 'ur_taxes';
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_filter( 'user_registration_get_settings_payment' , array( $this, 'add_tax_settings' ) );
		add_action( 'user_registration_settings_save_payment' , array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( "user_registration_get_sections_payment", array( $this, 'get_settings_callback' ) );
		add_action( 'admin_init', array( $this, 'delete_tax_regions_and_rates' ) );
	}

	/**
	 * Include Used Classes
	 *
	 * @return void
	 */
	public function includes() {
		new Ajax();
	}

	/**
	 * Append sections.
	 */
	public function get_settings_callback( $sections ){
		$sections[ 'tax-settings' ] = __( 'Tax & VAT', 'user-registration' );

		return $sections;
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';

		if ( isset( $_GET['section'] ) && 'tax-settings' === $_GET['section'] ) {
			wp_register_script(
				'urm_tax_admin_script',
				UR()->plugin_url() . '/includes/pro/addons/Taxes/assets/ur-tax-settings' . $suffix . '.js',
				array('jquery', 'sweetalert2'),
				UR_VERSION,
				true
			);

			$tax_regions = get_option( 'user_registration_tax_regions_and_rates', array() );

			wp_enqueue_script('urm_tax_admin_script');
			wp_localize_script(
				'urm_tax_admin_script',
				'urm_tax_admin_script_data',
				array(
					'ajax_url'                		=> admin_url( 'admin-ajax.php' ),
					'user_registration_tax_regions' => wp_create_nonce( 'user_registration_tax_regions' ),
					'add_tax_regions_template' 		=> user_registration_pro_tax_regions_template(),
					'i18n_add_btn_text'				=> __( 'Add', 'user-registration'),
					'i18n_cancel'					=> __( 'Cancel', 'user-registration' ),
					'i18n_title'					=> __( 'Tax Regions', 'user-registration' ),
					'i18n_delete_title'				=> __( 'Are you sure want to delete?'),
					'i18n_confirm_btn_text'			=> __( 'Confirm', 'user-registration' ),
					'tax_regions_list'				=> isset( $tax_regions[ 'regions' ] ) ? $tax_regions[ 'regions' ] : array(),
					)
				);
			}
	}

	/**
	 * Enqueue styles
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		if ( empty( $_GET['page'] ) || 'user-registration-taxes' !== $_GET['page'] ) {
			return;
		}
		if ( ! wp_style_is( 'ur-snackbar', 'reqistered' ) ) {
			wp_register_style( 'ur-snackbar', UR()->plugin_url() . '/assets/css/ur-snackbar/ur-snackbar.css', array(), '1.0.0' );
			wp_enqueue_style( 'ur-snackbar' );
		}
		wp_register_style( 'ur-core-builder-style', UR()->plugin_url() . '/assets/css/admin.css', array(), UR_VERSION );
		wp_register_style( 'ur-coupon-css', UR()->plugin_url() . '/assets/css/ur-taxes.css', array(), UR_VERSION );
		wp_enqueue_style( 'ur-coupon-css' );
		wp_enqueue_style( 'sweetalert2' );
		wp_enqueue_style( 'ur-core-builder-style' );
		wp_enqueue_style( 'ur-membership-admin-style' );
	}

	/**
	 * Add tax settings.
	 *
	 * @since 6.0.0
	 */
	public function add_tax_settings( $settings ){
		global $current_section;
		if ( 'tax-settings' === $current_section ) {
			add_filter( 'user_registration_settings_hide_save_button', '__return_false' );
			$settings = $this->get_tax_settings();
		}
		return $settings;
	}

	/**
	 * Get tax settings.
	 *
	 * @since 6.0.0
	 */
	public function get_tax_settings(){
		$regions = get_option( 'user_registration_tax_regions_and_rates', array() );
		$tax_calculation_method = get_option( 'user_registration_tax_calculation_during_checkout', 'no' );

		/**
		 * Filter to add the tax options settings.
		 *
		 * @param array Options to be enlisted.
		 */
		$settings = apply_filters(
			'user_registration_get_tax_settings',
			array(
				'title'    => '',
				'sections' => array(
					'tax_settings' => array(
						'title'    => esc_html__( 'GLOBAL TAX SETTINGS', 'user-registration' ),
						'type'     => 'card',
						'desc'     => '<strong>' . __( 'Notice: ', 'user-registration' ) . '</strong>' . __( 'Tax calculation requires a Country field. Please add it to the membership form.', 'user-registration' ),
						'settings' => array(
							array(
                                'title'    => __( 'Calculate tax at checkout', 'user-registration' ),
                                'desc'     => __( 'Enable this to calculate tax during checkout.', 'user-registration' ),
                                'id'       => 'user_registration_tax_calculation_during_checkout',
                                'type'     => 'toggle',
                                'desc_tip' => true,
                                'css'      => '',
                                'default'  => 'no',
                            ),
							array(
								'title'              => __( 'TAX REGIONS & RATES', 'user-registration' ),
								'desc'               => __( 'TAX REGIONS & RATES', 'user-registration' ),
								'id'                 => 'user_registration_tax_regions_and_rates',
								'default'            => '',
								'type'               => 'tax_table',
								'css'                => '',
								'desc_tip'           => true
							)
						),
					)
				),
			)
		);

		/**
		 * Filter to get the settings.
		 *
		 * @param array $settings Frontend Message Setting options to be enlisted.
		 */
		return apply_filters( 'user_registration_get_tax_settings_payment', $settings );
	}

	/**
	 * Save settings
	 *
	 * @since 6.0.0
	 */
	public function save() {
		global $current_section;
		if ( 'tax-settings' === $current_section ) {
			$settings = $this->get_tax_settings();
			\UR_Admin_Settings::save_fields( $settings );
		}
	}

	public function delete_tax_regions_and_rates() {

		$section = isset( $_GET['section'] )
			? sanitize_text_field( wp_unslash( $_GET['section'] ) )
			: null;

		if ( 'tax-settings' !== $section ) {
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

		$data    = get_option( 'user_registration_tax_regions_and_rates', array() );
		$regions = isset( $data['regions'] ) ? $data['regions'] : array();

		$selected_ids = array_map( 'sanitize_text_field', wp_unslash( $_POST['currency_ids'] ) );

		foreach ( $selected_ids as $id ) {
			foreach ( $regions as $country_code => $country_data ) {
				if (
					! empty( $country_data['states'] ) &&
					isset( $country_data['states'][ $id ] )
				) {
					unset( $regions[ $country_code ]['states'][ $id ] );
				}
			}
		}

		foreach ( $selected_ids as $id ) {

			if ( ! isset( $regions[ $id ] ) ) {
				continue;
			}

			$has_states = ! empty( $regions[ $id ]['states'] );

			if ( $has_states ) {
				$regions[ $id ]['rate'] = '';
			} else {
				unset( $regions[ $id ] );
			}
		}

		update_option(
			'user_registration_tax_regions_and_rates',
			array( 'regions' => $regions )
		);
	}
}
