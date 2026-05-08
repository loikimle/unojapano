<?php
/**
 * Configure Email
 *
 * @category Class
 * @author   WPEverest
 * @since   1.0.0
 * @package UserRegistrationPayments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Payment_Pending_Email', false ) ) :

	/**
	 * UR_Settings_Payment_Pending_Email Class.
	 */
	class UR_Settings_Payment_Pending_Email {
		/**
		 * UR_Settings_Payment_Pending_Email Id.
		 *
		 * @var string
		 */
		public $id;

		/**
		 * UR_Settings_Payment_Pending_Email Title.
		 *
		 * @var string
		 */
		public $title;

		/**
		 * UR_Settings_Payment_Pending_Email Description.
		 *
		 * @var string
		 */
		public $description;

		/**
		 * UR_Settings_Payment_Pending_Email Receiver.
		 *
		 * @var string
		 */
		public $receiver;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'payment_pending_email';
			$this->title       = esc_html__( 'Payment Pending', 'user-registration' );
			$this->description = esc_html__( 'Notifies the user that their payment is pending approval or confirmation.', 'user-registration' );
			$this->receiver    = 'User';
		}

		/**
		 * Get settings
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'user_registration_payment_pending_email',
				array(
					'title'    => __( 'Emails', 'user-registration' ),
					'sections' => array(
						'payment_pending_email' => array(
							'title'        => esc_html__( 'Payment Pending Email', 'user-registration' ),
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
									'desc'     => __( 'Enable this email sent to the user after resgistration.', 'user-registration' ),
									'id'       => 'user_registration_enable_payment_pending_email',
									'default'  => 'yes',
									'type'     => 'toggle',
									'autoload' => false,
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_payment_pending_email_subject',
									'type'     => 'text',
									'default'  => __( 'Complete Your Payment for {{blog_info}}', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'user_registration_payment_pending_email',
									'type'     => 'tinymce',
									'default'  => $this->ur_get_payment_pending_email(),
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
		 * Get payment pending email.
		 */
		public static function ur_get_payment_pending_email() {

			$body_content = __(
				'<p style="margin:0 0 20px 0; color:#000000; font-size:16px; line-height:1.6;">
       			 Hi {{username}},
		    </p>
		    <p style="margin:0 0 20px 0; color:#000000; font-size:16px; line-height:1.6;">
		     You have successfully registered at <a href="{{home_url}}" style="color:#4A90E2; text-decoration:none;">{{blog_info}}</a>, but your payment is still pending.
		    </p>
		    <p style="margin:0 0 20px 0; color:#000000; font-size:16px; line-height:1.6;">
		    To activate your {{membership_plan_name}} membership, please complete your payment.
		    </p>
		    <p style="margin: 0 0 16px 0; color: #000000; font-size: 16px; line-height: 1.6;">
					Thanks
				</p>
		    ',
				'user-registration'
			);

			$body_content = ur_wrap_email_body_content( $body_content );

			$message = apply_filters( 'user_registration_payment_email_message', $body_content );

			return $message;
		}
	}
endif;

return new UR_Settings_Payment_Pending_Email();
