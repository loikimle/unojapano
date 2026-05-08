<?php
/**
 * UserRegistration Custom Email Settings
 *
 * @class    Custom_Email
 * @version
 * @package  UserRegistration/Modules/CustomEmail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-custom-email-save.php';
require_once __DIR__ . '/class-custom-email-data.php';
require_once __DIR__ . '/class-custom-email-settings.php';
require_once __DIR__ . '/class-custom-email-display.php';
require_once __DIR__ . '/class-custom-email-ajax.php';
require_once __DIR__ . '/class-custom-email-assets.php';
require_once __DIR__ . '/class-custom-email-sender.php';

if ( ! class_exists( 'Custom_Email' ) ) :

	class Custom_Email {

		/**
		 * Data handler instance.
		 *
		 * @var Custom_Email_Data
		 */
		private $data_handler;

		/**
		 * Settings handler instance.
		 *
		 * @var Custom_Email_Settings
		 */
		private $settings_handler;

		/**
		 * Display handler instance.
		 *
		 * @var Custom_Email_Display
		 */
		private $display_handler;

		/**
		 * AJAX handler instance.
		 *
		 * @var Custom_Email_Ajax
		 */
		private $ajax_handler;

		/**
		 * Assets handler instance.
		 *
		 * @var Custom_Email_Assets
		 */
		private $assets_handler;

		/**
		 * Sender handler instance.
		 *
		 * @var Custom_Email_Sender
		 */
		private $sender_handler;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->data_handler     = new Custom_Email_Data();
			$this->settings_handler = new Custom_Email_Settings( $this->data_handler );
			$this->display_handler  = new Custom_Email_Display( $this->data_handler );
			$this->ajax_handler     = new Custom_Email_Ajax( $this->data_handler );
			$this->assets_handler   = new Custom_Email_Assets();
			$this->sender_handler   = new Custom_Email_Sender( $this->data_handler );

			add_filter( 'user_registration_get_email_settings_email', array( $this->settings_handler, 'get_custom_email_email_settings' ), 10, 1 );
			add_filter( 'user_registration_admin_field_custom_email_notification', array( $this->display_handler, 'custom_email_notification_setting' ), 10, 2 );
			add_action( 'wp_ajax_ur_add_custom_email', array( $this->ajax_handler, 'ajax_add_custom_email' ) );
			add_action( 'wp_ajax_ur_save_custom_email', array( $this->ajax_handler, 'ajax_save_custom_email' ) );
			add_action( 'wp_ajax_ur_delete_custom_email', array( $this->ajax_handler, 'ajax_delete_custom_email' ) );
			add_action( 'user_registration_settings_save_email', array( $this->data_handler, 'save_custom_email_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this->assets_handler, 'enqueue_scripts' ) );
			add_action( 'template_redirect', array( $this, 'handle_custom_email_preview' ) );
		}

		/**
		 * Handle custom email preview.
		 */
		public function handle_custom_email_preview() {
			if ( ! isset( $_GET['ur_custom_email_preview'] ) ) {
				return;
			}

			if ( ! is_user_logged_in() || ! current_user_can( 'manage_user_registration' ) ) {
				wp_die( esc_html__( 'You do not have permission to view this preview.', 'user-registration' ) );
			}

			$email_id = sanitize_text_field( wp_unslash( $_GET['ur_custom_email_preview'] ) );
			$emails   = $this->data_handler->get_custom_emails();

			if ( ! isset( $emails[ $email_id ] ) ) {
				wp_die( esc_html__( 'Email not found.', 'user-registration' ) );
			}

			$email = $emails[ $email_id ];

			$email_subject = isset( $email['email_subject'] ) ? $email['email_subject'] : __( 'Custom Email', 'user-registration' );
			$email_content = isset( $email['email_content'] ) ? $email['email_content'] : $this->settings_handler->get_default_email_content();

			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;

			$values = array(
				'user_id'     => $user_id,
				'email'       => $current_user->user_email,
				'username'    => $current_user->user_login,
				'first_name'  => get_user_meta( $user_id, 'first_name', true ) ? get_user_meta( $user_id, 'first_name', true ) : __( 'John', 'user-registration' ),
				'last_name'   => get_user_meta( $user_id, 'last_name', true ) ? get_user_meta( $user_id, 'last_name', true ) : __( 'Doe', 'user-registration' ),
				'blogname'    => get_option( 'blogname' ),
				'blog_info'   => get_bloginfo( 'name' ),
				'home_url'    => get_home_url(),
				'site_url'    => get_site_url(),
				'admin_email' => get_option( 'admin_email' ),
			);

			$values = apply_filters( 'user_registration_smart_tag_values', $values );

			$form_id    = function_exists( 'ur_get_form_id_by_userid' ) ? ur_get_form_id_by_userid( $user_id ) : '';
			$name_value = array();

			if ( $form_id && function_exists( 'user_registration_form_data' ) ) {
				$profile = user_registration_form_data( $user_id, $form_id );
				foreach ( (array) $profile as $key => $field ) {
					$field_name  = isset( $field->field_name ) ? $field->field_name : '';
					$field_value = isset( $field->value ) ? $field->value : '';
					if ( ! empty( $field_name ) ) {
						if ( function_exists( 'ur_format_field_values' ) ) {
							$name_value[ $field_name ] = ur_format_field_values( $field_name, $field_value );
						} else {
							$name_value[ $field_name ] = $field_value;
						}
					}
				}
				$name_value = apply_filters( 'user_registration_process_smart_tag', $name_value, array(), $form_id, $user_id );
			}

			if ( class_exists( 'UR_Emailer' ) && method_exists( 'UR_Emailer', 'parse_smart_tags' ) ) {
				$email_subject = UR_Emailer::parse_smart_tags( $email_subject, $values, $name_value );
				$email_content = UR_Emailer::parse_smart_tags( $email_content, $values, $name_value );
			} else {
				$search        = array( '{{user_id}}', '{{email}}', '{{username}}', '{{first_name}}', '{{last_name}}', '{{blogname}}', '{{blog_info}}', '{{home_url}}', '{{site_url}}', '{{admin_email}}' );
				$replace       = array(
					$values['user_id'],
					$values['email'],
					$values['username'],
					$values['first_name'],
					$values['last_name'],
					$values['blogname'],
					$values['blog_info'],
					$values['home_url'],
					$values['site_url'],
					$values['admin_email'],
				);
				$email_subject = str_replace( $search, $replace, $email_subject );
				$email_content = str_replace( $search, $replace, $email_content );
			}

			if ( function_exists( 'ur_wrap_email_body_content' ) ) {
				$email_content = ur_wrap_email_body_content( $email_content );
			}

			$email_template = '';
			if ( isset( $_GET['ur_email_template'] ) ) {
				$email_template = sanitize_text_field( wp_unslash( $_GET['ur_email_template'] ) );
			} elseif ( $form_id && function_exists( 'ur_get_single_post_meta' ) ) {
				$email_template = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );
			}

			$email_content = user_registration_process_email_content( $email_content, $email_template );

			ur_get_template(
				'email-preview.php',
				array(
					'email_content'  => $email_content,
					'email_subject'  => $email_subject,
					'email_template' => $email_template,
				)
			);

			exit;
		}
	}

endif;

return new Custom_Email();
