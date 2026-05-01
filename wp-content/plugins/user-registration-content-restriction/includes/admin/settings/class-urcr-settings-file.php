<?php
/**
 * UserRegistrationContentRestriction Settings
 *
 * @class    URCR_Settings_File
 * @version  1.0.0
 * @package  UserRegistrationContentRestriction/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'URCR_Settings_File ' ) ) :

	/**
	 * URCR_Settings_File Class
	 */
	class URCR_Settings_File extends UR_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'content_restriction';
			$this->label = __( 'Content Restriction', 'user-registration-content-restriction' );
			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
			add_filter( 'show_user_registration_setting_message', array( $this, 'urcr_setting_message_show' ) );
			add_action( ' admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		}

		public function register_scripts() {
			wp_register_script( 'custom-js', URCR()->plugin_url() . '/assets/custom' . $suffix . '.js', array( 'jquery' ), '3.5.4' );
		}

		public function urcr_setting_message_show() {
			return true;
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */

		public function get_settings( $current_section = '' ) {
			$settings = urcr_settings();
			return apply_filters( 'user_registration_content_restriction_settings' . $this->id, $settings );
		}


		public function output() {

			wp_enqueue_script( 'custom-js' );

			global $current_section;

			$settings = $this->get_settings( $current_section );

			UR_Admin_Settings::output_fields( $settings );

		}

		/**
		 * Save settings
		 */

		public function save() {

			global $current_section;

			$settings = $this->get_settings( $current_section );

			UR_Admin_Settings::save_fields( $settings );

		}
	}

endif;

return new URCR_Settings_File();
