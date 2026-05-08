<?php
/**
 * UserRegistration WooCommerce Settings
 *
 * @class    URWC_Settings_Woocommerce
 * @version  1.1.0
 * @package  UserRegistrationWooCommerce/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'URWC_Settings_Woocommerce ' ) ) :

	/**
	 * URWC_Settings_woocommerce Class
	 */
	class URWC_Settings_Woocommerce extends UR_Settings_Page {

		/**
		 * Setting Id.
		 *
		 * @var string
		 */
		public $id = 'woocommerce';

		private $checkout_fields_option_key = 'user_registration_woocommerce_checkout_fields';
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'woocommerce';
			$this->label = __( 'WooCommerce', 'user-registration-woocommerce' );
			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
			add_filter( 'show_user_registration_setting_message', array( $this, 'urwc_setting_message_show' ) );
		}

		/**
		 * Get Global Settings.
		 */
		public function get_settings() {
			return $settings = apply_filters( 'user_registration_woocommerce_settings_' . $this->id, urwc_woocommerce_settings() );
		}

		/**
		 * Show message option.
		 */
		public function urwc_setting_message_show() {
			return true;
		}

		/**
		 * Output the settings
		 */
		public function output() {
			$settings = apply_filters( 'user_registration_woocommerce_settings_' . $this->id, urwc_woocommerce_settings() );
			UR_Admin_Settings::output_fields( $settings );

			echo '<div class="user_registration_woocommerce_form_fields_wrapper">';
			if ( ur_string_to_bool( get_option( 'user_registration_woocommrece_settings_sync_checkout', false ) ) ) {
				$this->display_form_field_lists( get_option( 'user_registration_woocommerce_settings_form' ), $this->checkout_fields_option_key );
			}
			echo '</div>';
		}

		/**
		 * Save settings
		 */
		public function save() {
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'user-registration-settings' ) ) {
				die( __( 'Action failed. Please refresh the page and retry.', 'user-registration-woocommerce' ) );
			}

			$settings = apply_filters( 'user_registration_woocommerce_settings_' . $this->id, urwc_woocommerce_settings() );
			UR_Admin_Settings::save_fields( $settings );
			$sync_field_data = array();
			if ( isset( $_POST[ $this->checkout_fields_option_key ] ) && ! empty( $_POST[ $this->checkout_fields_option_key ] ) ) {
				$form_id                               = absint( $_POST['user_registration_woocommerce_settings_form'] );
				$checkout_fields                       = ur_clean( $_POST[ $this->checkout_fields_option_key ] );
				$saved_sync_field_data                 = get_option( $this->checkout_fields_option_key, array() );
				$sync_field_data[ 'form-' . $form_id ] = $checkout_fields;
				if ( empty( $saved_sync_field_data ) ) {
					update_option( $this->checkout_fields_option_key, $sync_field_data );
				} else {
					$new_sync_data = array_merge( $saved_sync_field_data, $sync_field_data );
					update_option( $this->checkout_fields_option_key, $new_sync_data );
				}
			}
		}

		/**
		 * To display form field list
		 *
		 * @param int   $form_id Form Id.
		 * @param mixed $option_key Option Key.
		 */
		public function display_form_field_lists( $form_id = 0, $option_key = '' ) {

			if ( $form_id && $option_key ) {
				$woocommerce_field_table_list = new URWC_Field_Table_List();
				$woocommerce_field_table_list->display_table_list( $form_id, $option_key );
			}
		}
	}

endif;

return new URWC_Settings_Woocommerce();
