<?php
/**
 * UserRegistrationSocialConnect Social Settings
 *
 * @class    URSC_Settings_Social
 * @version  1.0.0
 * @package  UserRegistrationSocialConnect/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'URSC_Settings_Social ' ) ) :

	/**
	 * URSC_Settings_Social Class
	 */
	class URSC_Settings_Social extends UR_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'social_connect';
			$this->label = __( 'Social Connect', 'user-registration-social-connect' );
			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				''                 => __( 'API Settings', 'user-registration-social-connect' ),
				'advance_settings' => __( 'Advance Settings', 'user-registration-social-connect' ),
			);

			return apply_filters( 'user_registration_get_sections_' . $this->id, $sections );
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {

			if ( 'advance_settings' == $current_section ) {


				$settings = ursc_social_advance_settings();

			} else {
				$settings = ursc_social_api_settings();
			}


			return apply_filters( 'user_registration_social_connect_settings' . $this->id, $settings );
		}


		public function output() {

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

return new URSC_Settings_Social ();
