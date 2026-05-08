<?php
/**
 * Configure Email
 *
 * @package  UR_Settings_Email_Verified_Admin_Email
 * @extends  UR_Settings_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Email_Verified_Admin_Email', false ) ) :

	/**
	 * UR_Settings_Email_Verified_Admin_Email Class.
	 */
	class UR_Settings_Email_Verified_Admin_Email {
		/**
		 * UR_Settings_Email_Verified_Admin_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Email_Verified_Admin_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Email_Verified_Admin_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * UR_Settings_Email_Verified_Admin_Email Receiver.
		 *
		 * @var string
		 */
		public $receiver;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'email_verified_admin_email';
			$this->title       = __( 'Email Verified - Awaiting Admin Approval Email', 'user-registration' );
			$this->description = __( 'Email sent to the admin when a user confirmed his/her email', 'user-registration' );
			$this->receiver    = 'Admin';
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_pro_email_verified_admin_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'admin_email' => array(
							'title'        => __( 'Email Verified - Awaiting Admin Approval Email', 'user-registration' ),
							'type'         => 'card',
							'desc'         => '',
							'back_link'    => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ),
							'preview_link' => ur_email_preview_link(
								__( 'Preview', 'user-registration' ),
								$this->id
							),
							'settings'     => array(
								array(
									'title'    => __( 'Enable this email', 'user-registration' ),
									'desc'     => __( 'Enable this email to send to admin requesting admin approval after user has successfully confirmed email.', 'user-registration' ),
									'id'       => 'user_registration_enable_email_verified_admin_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Receipents', 'user-registration' ),
									'desc'     => __( 'Use comma to send emails to multiple receipents.', 'user-registration' ),
									'id'       => 'user_registration_pro_email_verified_admin_email_receipents',
									'default'  => get_option( 'admin_email' ),
									'type'     => 'text',
									'css'      => 'min-width: 350px;',
									'autoload' => false,
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_pro_email_verified_admin_email_subject',
									'type'     => 'text',
									'default'  => __( 'User email confirmed awaiting admin approval', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_pro_email_verified_admin_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_email_verified_admin_email(),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
							),
						),
					),
				)
			);

			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Email format.
		 */
		public function ur_get_email_verified_admin_email() {

			$body_content = __(
				'<p style="margin:0 0 20px 0; color:#000000; font-size:16px; line-height:1.6;">
				 Hi Admin,
			    </p>
			    <p style="margin:0 0 20px 0; color:#000000; font-size:16px; line-height:1.6;">
			        The user {{username}} has successfully verified the email address - {{email}}.
			    </p>
			    <p style="margin:0 0 20px 0; color:#000000; font-size:16px; line-height:1.6;">
			        Please review the user role and details from the \'<b>Users</b>\' menu in your WP dashboard and approve accordingly.
			    </p>
			    <p style="margin:0 0 20px 0;">
			        Click on this link to approve this user directly:
			        <a href="{{approval_link}}" rel="noreferrer noopener" target="_blank" style="color:#4A90E2; text-decoration:none;">Approve</a><br />
			        Click on this link to deny this user directly:
			        <a href="{{denial_link}}" rel="noreferrer noopener" target="_blank" style="color:#4A90E2; text-decoration:none;">Deny</a>
			    </p>
			    <p style="margin:0 0 20px 0; color:#000000; font-size:16px; line-height:1.6;">
			        Thank You!
			    </p>',
				'user-registration'
			);

			$body_content = ur_wrap_email_body_content( $body_content );

			/**
			 * Filter to modify the verified admin email message content.
			 *
			 * @param string $body_content Message to be overridden for admin email.
			 */
			$message = apply_filters( 'user_registration_pro_email_verified_admin_email_message', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Email_Verified_Admin_Email();
