<?php
/**
 * UserRegistration Pro Email Template Preview Handler
 *
 * @class    UR_Pro_Email_Template_Preview
 * @version  1.0.0
 * @package  UserRegistration/Pro/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Pro_Email_Template_Preview', false ) ) :

	/**
	 * UR_Pro_Email_Template_Preview Class.
	 */
	class UR_Pro_Email_Template_Preview {

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init' ), 5 );
		}

		/**
		 * Initialize preview handler.
		 */
		public function init() {
			if ( isset( $_GET['ur_email_preview'] ) && 'email_template_option' === sanitize_text_field( wp_unslash( $_GET['ur_email_preview'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				add_filter( 'template_include', array( $this, 'handle_email_template_preview' ), PHP_INT_MAX );
			}
		}

		/**
		 * Handle email template option preview.
		 *
		 * @param string $template Template path.
		 * @return string Template path.
		 */
		public function handle_email_template_preview( $template ) {
			if ( ! is_user_logged_in() ) {
				return $template;
			}

			$option_name = isset( $_GET['ur_email_preview'] ) ? sanitize_text_field( wp_unslash( $_GET['ur_email_preview'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 'email_template_option' !== $option_name ) {
				return $template;
			}

			$sample_content = '<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">Hi {{username}},</p>
<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">This is just for preview; it will be replaced by email content.</p>';

			$email_content = apply_filters( 'user_registration_process_smart_tags', $sample_content );

			$pro_template_path = '';
			if ( function_exists( 'UR' ) && is_callable( array( UR(), 'plugin_path' ) ) ) {
				$pro_template_path = UR()->plugin_path() . '/templates/';
			} elseif ( defined( 'UR_PLUGIN_FILE' ) ) {
				$pro_template_path = dirname( UR_PLUGIN_FILE ) . '/templates/';
			} else {
				$pro_plugin_path   = dirname( dirname( dirname( dirname( __DIR__ ) ) ) );
				$pro_template_path = $pro_plugin_path . '/templates/';
			}

			ur_get_template(
				'email-preview.php',
				array(
					'email_content'  => $email_content,
					'email_template' => '',
				),
				'',
				$pro_template_path
			);
			exit;
		}
	}

endif;

return new UR_Pro_Email_Template_Preview();
