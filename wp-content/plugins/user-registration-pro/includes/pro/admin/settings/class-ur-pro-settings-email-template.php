<?php
/**
 * UserRegistration Pro Email Template Settings
 *
 * @class    UR_Pro_Settings_Email_Template
 * @version  1.0.0
 * @package  UserRegistration/Pro/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Pro_Settings_Email_Template', false ) ) :

	/**
	 * UR_Pro_Settings_Email_Template Class.
	 */
	class UR_Pro_Settings_Email_Template {

		/**
		 * Setting Id.
		 *
		 * @var string
		 */
			public $id = 'email_template_option';

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_filter( 'user_registration_email_settings', array( $this, 'add_email_template_settings' ), 20 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_preview_scripts' ) );
		}

		/**
		 * Get email template preview link.
		 *
		 * @return string Preview link HTML.
		 */
		private function get_email_template_preview_link() {
			$url = add_query_arg(
				array(
					'ur_email_preview' => 'email_template_option',
					'TB_iframe'        => 'true',
					'width'            => '1200',
					'height'           => '800',
				),
				home_url()
			);

			return '<a href="' . esc_url( $url ) . '" class="button ur-email-template-preview thickbox" style="min-width:70px;" aria-label="' . esc_attr__( 'Preview Email Template', 'user-registration' ) . '">
				' . esc_html__( 'Preview', 'user-registration' ) . '
			</a>';
		}

		/**
		 * Enqueue scripts and styles for email template preview popover.
		 *
		 * @param string $hook Current admin page hook.
		 */
		public function enqueue_preview_scripts( $hook ) {
			// Only load on settings page.
			if ( 'user-registration_page_user-registration-settings' !== $hook ) {
				return;
			}

			// Check if we're on the email tab.
			if ( isset( $_GET['tab'] ) && 'email' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// Enqueue Thickbox (WordPress built-in).
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );

				// Enqueue custom script for preview handling.
				wp_enqueue_script(
					'ur-pro-email-template-preview',
					plugins_url( '/assets/js/pro/admin/email-template-preview.js', UR_PLUGIN_FILE ),
					array( 'jquery', 'thickbox' ),
					UR_VERSION,
					true
				);

				// Add inline CSS to style Thickbox title and iframe.
				wp_add_inline_style(
					'thickbox',
					'
					.ur-email-template-preview-modal #TB_title {
						background-color: #ebebeb !important;
					}
					.ur-email-template-preview-modal #TB_iframeContent {
						overflow: hidden !important;
					}
					.ur-email-template-preview-modal #TB_iframeContent iframe {
						display: block !important;
					}
					'
				);
			}
		}


		/**
		 * Add email template settings section.
		 *
		 * @param array $settings Existing settings.
		 * @return array Modified settings.
		 */
		public function add_email_template_settings( $settings ) {
			// Add new email template section.
			if ( isset( $settings['sections'] ) ) {
				$settings['sections']['email_template_options'] = array(
					'title'    => __( 'Email Template', 'user-registration' ),
					'type'     => 'card',
					'desc'     => '',
					'settings' => array(
						array(
							'title'    => __( 'Email', 'user-registration' ),
							'desc_tip' => __( 'Click to preview how your email template will look.', 'user-registration' ),
							'id'       => 'user_registration_email_template_preview_link',
							'type'     => 'link',
							'buttons'  => array(
								array(
									'title' => __( 'Preview ', 'user-registration' ),
									'class' => 'ur-email-template-preview thickbox button button-primary',
									'href'  => add_query_arg(
										array(
											'ur_email_preview' => 'email_template_option',
											'TB_iframe' => 'true',
											'width'     => '1200',
											'height'    => '800',
										),
										home_url()
									),
								),
							),
						),
						array(
							'title'    => __( 'Email Header', 'user-registration' ),
							'desc'     => __( 'Enable the email header with logo and background.', 'user-registration' ),
							'id'       => 'user_registration_email_template_header_enable',
							'type'     => 'toggle',
							'default'  => 'no',
							'autoload' => false,
							'desc_tip' => true,
						),
						array(
							'title'             => __( 'Header Logo', 'user-registration' ),
							'desc'              => __( 'Upload a logo image to display in the email header. Recommended size: 200x50px.', 'user-registration' ),
							'id'                => 'user_registration_email_template_header_logo',
							'type'              => 'image',
							'default'           => '',
							'autoload'          => false,
							'desc_tip'          => true,
							'display_condition' => array(
								'field'    => 'user_registration_email_template_header_enable',
								'operator' => 'equals',
								'value'    => 'yes',
								'case'     => 'insensitive',
							),
						),
						array(
							'title'    => __( 'Email Footer', 'user-registration' ),
							'desc'     => __( 'Enable the email footer with custom content.', 'user-registration' ),
							'id'       => 'user_registration_email_template_footer_enable',
							'type'     => 'toggle',
							'default'  => 'no',
							'autoload' => false,
							'desc_tip' => true,
						),
						array(
							'title'             => __( 'Footer Content', 'user-registration' ),
							'desc'              => __( 'Customize the footer content that appears at the bottom of all emails. You can use HTML and smart tags like {{blog_info}} and {{home_url}}.', 'user-registration' ),
							'id'                => 'user_registration_email_template_footer_content',
							'type'              => 'tinymce',
							'default'           => '<p style="margin: 0 0 12px 0; color: #6c757d; font-size: 13px; line-height: 1.5;">© ' . date( 'Y' ) . ' {{blog_info}}. All rights reserved.</p>
													<p style="margin: 0; font-size: 14px; line-height: 1.6;"><a href="{{home_url}}" style="color: #4A90E2; text-decoration: none; font-weight: 500;">{{blog_info}} Team</a></p>',
							'css'               => 'min-width: 350px;',
							'autoload'          => false,
							'desc_tip'          => true,
							'display_condition' => array(
								'field'    => 'user_registration_email_template_footer_enable',
								'operator' => 'equals',
								'value'    => 'yes',
								'case'     => 'insensitive',
							),
						),
					),
				);
			}

			return $settings;
		}
	}

endif;

return new UR_Pro_Settings_Email_Template();
