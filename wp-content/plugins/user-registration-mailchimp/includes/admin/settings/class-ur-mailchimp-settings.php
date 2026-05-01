<?php
/**
 * UserRegistration Mailchimp Settings class.
 *
 * @package  UserRegistration/Admin
 * @author   WPEverest
 * @since  v1.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'User_Registration_Mailchimp_Settings' ) ) :

	/**
	 * User_Registration_Mailchimp_Settings Setting
	 */
	class User_Registration_Mailchimp_Settings extends UR_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'user-registration-mailchimp';
			$this->label = esc_html__( 'Mailchimp', 'user-registration-mailchimp' );

			$this->init_hooks();

		}

		/**
		 * Initialize hooks.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		private function init_hooks() {
			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
		}

		/**
		 * Add this page to settings.
		 *
		 * @param  array $pages Pages.
		 * @return mixed
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		/**
		 * Outputs Page
		 *
		 * @return void
		 */
		public function output() {
			global $current_section, $hide_save_button;

			switch ( $current_section ) {
				case '':
					$hide_save_button = true;
					include_once URMC_ABSPATH . 'includes/admin/views/html-admin-page-mailchimp.php';
					break;
			}
		}
	}
endif;
return new User_Registration_Mailchimp_Settings();
