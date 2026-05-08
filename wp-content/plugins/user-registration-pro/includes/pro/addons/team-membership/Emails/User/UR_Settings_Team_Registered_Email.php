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

if ( ! class_exists( 'UR_Settings_Team_Registered_Email', false ) ) :

	/**
	 * UR_Settings_Team_Registered_Email Class.
	 */
	class UR_Settings_Team_Registered_Email {
		/**
		 * UR_Settings_Team_Registered_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Team_Registered_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Team_Registered_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * UR_Settings_Team_Registered_Email Receiver.
		 *
		 * @var string
		 */
		public $receiver;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'team_registered_email';
			$this->title       = __( 'Team Registration Success', 'user-registration' );
			$this->description = __( 'Confirms a successful team registration to the members.', 'user-registration' );
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
				'user_registration_team_registered_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'team_registered_email' => array(
							'title'        => __( 'Team Registration Email', 'user-registration' ),
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
									'desc'     => __( 'Enable this to send an email to the user when they are registered in a team.', 'user-registration' ),
									'id'       => 'user_registration_enable_team_registered_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_team_registered_email_subject',
									'type'     => 'text',
									'default'  => __( 'Team Registration', 'user-registration' ),
									'css'      => '',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_team_registered_email_message',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_team_registered_email(),
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
		public function ur_get_team_registered_email() {
			$body_content = __(
				'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Hi {{username}},
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
				You’ve successfully joined the "{{team_name}}" team ! We’re thrilled to have you with us.
				</p>
				<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					To get started, you can access your account using the following link: {{my_account_link}}
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
			 * Filter to modify the message content for successfully registered email.
			 *
			 * @param string $body_content Message content for successfully registered email to be overridden.
			 */
			$message = apply_filters( 'user_registration_get_successfully_team_registered_email', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Team_Registered_Email();
