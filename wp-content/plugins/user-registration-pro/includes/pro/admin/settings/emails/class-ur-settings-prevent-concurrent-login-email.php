<?php
/**
 * Configure Email
 *
 * @class   User_Registration_Settings_Prevent_Concurrent_Email
 * @extends  User_Registration_Settings_Email
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Prevent_Concurrent_Login_Email', false ) ) :

	/**
	 * User_Registration_Settings_Prevent_Concurrent_Email Class.
	 */
	class UR_Settings_Prevent_Concurrent_Login_Email {
		/**
		 * UR_Settings_Prevent_Concurrent_Login_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Prevent_Concurrent_Login_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Prevent_Concurrent_Login_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * UR_Settings_Prevent_Concurrent_Login_Email Receiver.
		 *
		 * @var string
		 */
		public $receiver;

		/**
		 * constructor
		 */
		public function __construct() {
			$this->id          = 'prevent_concurrent_login_email';
			$this->title       = esc_html__( 'Prevent Concurrent Login ', 'user-registration' );
			$this->description = esc_html__( 'Informs the user that simultaneous logins limits have reached or an unauthorized login attempt was blocked.', 'user-registration' );
			$this->receiver    = 'User';
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_prevent_concurrent_login_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'prevent_concurrent_email' => array(
							'title'        => __( 'Prevent Concurrent Login Email', 'user-registration' ),
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
									'desc'     => __( 'Enable this email sent to the user after succesfully email sent', 'user-registration' ),
									'id'       => 'user_registration_enable_prevent_concurrent_login_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_prevent_concurrent_login_email_subject',
									'type'     => 'text',
									'default'  => __( 'Account Security Alert', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_prevent_concurrent_login_email_content',
									'type'     => 'tinymce',
									'default'  => $this->user_registration_get_prevent_concurrent_login_email(),
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
		 * Email Format.
		 */
		public static function user_registration_get_prevent_concurrent_login_email() {

			$body_content = __(
				'<p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
       		 Hi {{username}},
		    </p>
		    <p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
		     A request has been made to log out all active sessions for your account at {{site_name}}.
		    </p>
		    <p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
		         If you did not make this request, you can safely ignore this email. <br> To proceed with logging out all sessions, click the link below: <br/>
		        <a href="{{home_url}}/{{ur_login}}?action=force-logout&amp;login={{user_id}}" rel="noreferrer noopener" target="_blank" style="color: #4A90E2; text-decoration: none;">Click Here</a>
		    </p>
		    <p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
		        If you need assistance, we\'re here to help.
		    </p>
		    <p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Thanks
				</p>
		    ',
				'user-registration'
			);

			$body_content = ur_wrap_email_body_content( $body_content );

			/**
			 * Filter to modify the reset password / force-logout email message content.
			 *
			 * @param string $body_content Message to be overridden for reset password email.
			 */
			$message = apply_filters( 'user_registration_reset_password_email_message', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Prevent_Concurrent_Login_Email();
