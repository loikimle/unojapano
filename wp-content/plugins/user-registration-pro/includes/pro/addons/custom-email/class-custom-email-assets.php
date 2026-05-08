<?php
/**
 * Custom Email Assets Handler
 *
 * @class    Custom_Email_Assets
 * @version
 * @package  UserRegistration/Modules/CustomEmail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Custom_Email_Assets' ) ) :

	class Custom_Email_Assets {

		/**
		 * Enqueue scripts and styles for custom email functionality.
		 */
		public function enqueue_scripts() {
			if ( ! isset( $_GET['page'] ) || 'user-registration-settings' !== $_GET['page'] ) {
				return;
			}

			if ( ! isset( $_GET['tab'] ) || 'email' !== $_GET['tab'] ) {
				return;
			}

			$current_section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
			if ( empty( $current_section ) ) {
				global $current_section;
				$current_section = isset( $current_section ) ? $current_section : '';
			}

			$is_custom_email_page = ( 'custom-email' === $current_section || strpos( $current_section, 'ur_settings_custom_email_' ) === 0 );

			if ( ! $is_custom_email_page ) {
				return;
			}

			$plugin_path = plugin_dir_path( __FILE__ );
			$plugin_url  = plugin_dir_url( __FILE__ );

			$css_file = $plugin_path . 'assets/css/custom-email.css';
			if ( file_exists( $css_file ) ) {
				$css_version = filemtime( $css_file );
				wp_enqueue_style(
					'ur-custom-email',
					$plugin_url . 'assets/css/custom-email.css',
					array(),
					$css_version
				);
			}

			$js_file = $plugin_path . 'assets/js/custom-email.js';
			if ( file_exists( $js_file ) ) {
				$js_version = filemtime( $js_file );
				wp_enqueue_script(
					'ur-custom-email',
					$plugin_url . 'assets/js/custom-email.js',
					array( 'jquery' ),
					$js_version,
					true
				);
			} else {
				return;
			}

			wp_localize_script(
				'ur-custom-email',
				'urCustomEmail',
				array(
					'nonce' => wp_create_nonce( 'user-registration-settings' ),
					'i18n'  => array(
						'emailIdNotFound'       => __( 'Email ID not found.', 'user-registration' ),
						'confirmDelete'         => __( 'Are you sure you want to delete this email? This action cannot be undone.', 'user-registration' ),
						'deleting'              => __( 'Deleting...', 'user-registration' ),
						'errorDeleting'         => __( 'Error deleting email.', 'user-registration' ),
						'errorDeletingRetry'    => __( 'Error deleting email. Please try again.', 'user-registration' ),
						'addNewEmail'           => __( 'Add New Email', 'user-registration' ),
						'loading'               => __( 'Loading...', 'user-registration' ),
						'errorLoadingForm'      => __( 'Error loading email form.', 'user-registration' ),
						'errorLoadingFormRetry' => __( 'Error loading email form. Please try again.', 'user-registration' ),
						'sendBefore'            => __( 'Send before', 'user-registration' ),
						'sendAfter'             => __( 'Send after', 'user-registration' ),
					),
				)
			);
		}
	}

endif;
