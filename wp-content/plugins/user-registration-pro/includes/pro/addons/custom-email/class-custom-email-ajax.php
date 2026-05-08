<?php
/**
 * Custom Email AJAX Handler
 *
 * @class    Custom_Email_Ajax
 * @version
 * @package  UserRegistration/Modules/CustomEmail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Custom_Email_Ajax' ) ) :

	class Custom_Email_Ajax {

		/**
		 * Data handler instance.
		 *
		 * @var Custom_Email_Data
		 */
		private $data_handler;

		/**
		 * Constructor.
		 *
		 * @param Custom_Email_Data $data_handler Data handler instance.
		 */
		public function __construct( $data_handler ) {
			$this->data_handler = $data_handler;
		}

		/**
		 * AJAX handler to add new custom email.
		 */
		public function ajax_add_custom_email() {
			check_ajax_referer( 'user-registration-settings', 'security' );

			if ( ! current_user_can( 'manage_user_registration' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'user-registration' ) ) );
			}

			$email_id = 'custom_email_' . time() . '_' . wp_rand( 1000, 9999 );

			wp_send_json_success(
				array(
					'email_id'     => $email_id,
					'redirect_url' => admin_url( 'admin.php?page=user-registration-settings&tab=email&from=custom-email&section=ur_settings_custom_email_' . $email_id ),
				)
			);
		}

		/**
		 * AJAX handler to save custom email.
		 */
		public function ajax_save_custom_email() {
			check_ajax_referer( 'user-registration-settings', 'security' );

			if ( ! current_user_can( 'manage_user_registration' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'user-registration' ) ) );
			}

			$email_id          = isset( $_POST['email_id'] ) ? sanitize_text_field( wp_unslash( $_POST['email_id'] ) ) : '';
			$email_name        = isset( $_POST['email_name'] ) ? sanitize_text_field( wp_unslash( $_POST['email_name'] ) ) : '';
			$email_description = isset( $_POST['email_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['email_description'] ) ) : '';

			if ( empty( $email_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Email ID is required.', 'user-registration' ) ) );
			}

			if ( empty( $email_name ) ) {
				wp_send_json_error( array( 'message' => __( 'Email name is required.', 'user-registration' ) ) );
			}

			$emails = $this->data_handler->get_custom_emails();
			if ( ! isset( $emails[ $email_id ] ) ) {
				wp_send_json_error( array( 'message' => __( 'Email not found.', 'user-registration' ) ) );
			}

			$email_data                = $emails[ $email_id ];
			$email_data['title']       = $email_name;
			$email_data['description'] = $email_description;

			if ( isset( $_POST['enabled'] ) ) {
				$email_data['enabled'] = ur_string_to_bool( sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) );
			}

			$created_at = isset( $email_data['created_at'] ) && ! empty( $email_data['created_at'] ) ? $email_data['created_at'] : current_time( 'mysql' );

			unset( $email_data['created_at'] );
			unset( $email_data['updated_at'] );

			$email_data['created_at'] = $created_at;
			$email_data['updated_at'] = current_time( 'mysql' );

			$this->data_handler->save_custom_email( $email_id, $email_data );

			wp_send_json_success(
				array(
					'message' => __( 'Custom email saved successfully.', 'user-registration' ),
				)
			);
		}

		/**
		 * AJAX handler to delete custom email.
		 */
		public function ajax_delete_custom_email() {
			check_ajax_referer( 'user-registration-settings', 'security' );

			if ( ! current_user_can( 'manage_user_registration' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'user-registration' ) ) );
			}

			$email_id = isset( $_POST['email_id'] ) ? sanitize_text_field( wp_unslash( $_POST['email_id'] ) ) : '';

			if ( empty( $email_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Email ID is required.', 'user-registration' ) ) );
			}

			$emails = $this->data_handler->get_custom_emails();
			if ( ! isset( $emails[ $email_id ] ) ) {
				wp_send_json_error( array( 'message' => __( 'Email not found.', 'user-registration' ) ) );
			}

			unset( $emails[ $email_id ] );
			update_option( 'user_registration_custom_emails', $emails );

			wp_send_json_success(
				array(
					'message'      => __( 'Custom email deleted successfully.', 'user-registration' ),
					'redirect_url' => admin_url( 'admin.php?page=user-registration-settings&tab=email&section=custom-email' ),
				)
			);
		}
	}

endif;
