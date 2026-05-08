<?php

namespace WPEverest\URTeamMembership\Emails\User;

/**
 * Configure Email
 *
 * @package  UR_Settings_Email_Confirmation
 * @extends  UR_Settings_Email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Settings_Team_Member_Reset_Password_Email', false ) ) :

	/**
	 * UR_Settings_Team_Member_Reset_Password_Email Class.
	 */
	class UR_Settings_Team_Member_Reset_Password_Email {
		/**
		 * UR_Settings_Team_Member_Reset_Password_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Team_Member_Reset_Password_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Team_Member_Reset_Password_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * UR_Settings_Team_Member_Reset_Password_Email Receiver.
		 *
		 * @var string
		 */
		public $receiver;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'team_member_reset_password_email';
			$this->title       = __( 'Team Member Reset Password', 'user-registration' );
			$this->description = __( 'Sends a team member login credentials along with the secure password setup link to the member who has been added to the team.', 'user-registration' );
			$this->receiver    = 'User';
		}

			/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			/**
			 * Filter to add the options on settings.
			 *
			 * @param array Options to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_team_member_reset_password_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'team_member_reset_password_email' => array(
							'title'        => __( 'Team Member Reset Password Email', 'user-registration' ),
							'type'         => 'card',
							'desc'         => '',
							'back_link'    => ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email&section=to-user' ) ),
							'preview_link' => ur_email_preview_link(
								__( 'Preview', 'user-registration' ),
								$this->id
							),
							'settings'     => array(
								array(
									'title'    => __( 'Enable this email', 'user-registration' ),
									'desc'     => __( 'Enable this to send an email to the user when they are added in a team.', 'user-registration' ),
									'id'       => 'user_registration_enable_team_member_reset_password_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_team_member_reset_password_email_subject',
									'type'     => 'text',
									'default'  => __( 'Team Member Reset Password', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_team_member_reset_password_email_message',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_team_member_reset_password_email(),
									'css'      => '',
									'desc_tip' => true,
									'show-ur-registration-form-button' => false,
									'show-smart-tags-button' => true,
									'show-reset-content-button' => true,
								),
							),
						),
					),
				)
			);

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Setting options to be enlisted.
			 */
			return apply_filters( 'user_registration_get_settings_' . $this->id, $settings );
		}

		/**
		 * Email Format.
		 *
		 * @return string $message Message content for registered in team email.
		 */
		public function ur_get_team_member_reset_password_email() {
			$body_content = __(
				'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Hi {{username}},
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				Thank you for registering at {{blog_info}}. We are thrilled to have you onboard!
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				Below are your login details to get started:
				</p>
				<ul>
				<li><b>Username:</b> {{username}}</li>
				<li><b>Password:</b> {{password}}</li>
				</ul>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				To enhance your security, we recommend changing your password. You can do so easily by clicking the link below:
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					<a href="{{home_url}}/{{ur_reset_pass_slug}}?action=rp&key={{key}}&login={{username}}" style="color: #4A90E2; text-decoration: none;" rel="noreferrer noopener" target="_blank">Reset Your Password</a>
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					This link is valid for 24 hours.
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				Thanks
				</p>
				',
				'user-registration'
			);
			$body_content = ur_wrap_email_body_content( $body_content );

			if ( UR_PRO_ACTIVE ) {
				$body_content = ur_get_email_template_wrapper( $body_content, false );
			}

			/**
			 * Filter to modify the message content for Team Member Reset Password email.
			 *
			 * @param string $body_content Message content for Team Member Reset Password email to be overridden.
			 */
			$message = apply_filters( 'user_registration_get_team_member_reset_password_email', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Team_Member_Reset_Password_Email();
