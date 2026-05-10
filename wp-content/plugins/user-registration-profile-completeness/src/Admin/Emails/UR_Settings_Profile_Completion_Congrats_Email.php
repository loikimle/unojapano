<?php
/**
 * UR_Settings_Profile_Completion_Congrats_Email Email Setting.
 *
 * @package  WPEverest\UserRegistration\ProfileCompleteness\Admin\Emails
 * @since  1.0.0
 */

namespace WPEverest\UserRegistration\ProfileCompleteness\Admin\Emails;

/**
 * UR_Settings_Profile_Completion_Congrats_Email class.
 *
 * @since 1.0.0
 */
class UR_Settings_Profile_Completion_Congrats_Email {

	/**
	 * Unique Email Id.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Email Title.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Email description.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->id          = 'profile_completion_congrats_email';
		$this->title       = __( 'Profile Completion Congrats Email', 'user-registration-profile-completeness' );
		$this->description = __( 'A congratulatory email sent to users who have completed their profiles.', 'user-registration-profile-completeness' );
	}

	/**
	 * Get settings
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = apply_filters(
			'user_registration_profile_completeness_congrats_email',
			array(
				'title'    => __( 'Emails', 'user-registration-profile-completeness' ),
				'sections' => array(
					'completion_email' => array(
						'title'        => __( 'Profile Completion Congrats Email', 'user-registration-profile-completeness' ),
						'type'         => 'card',
						'desc'         => '',
						'back_link'    => ur_back_link( __( 'Return to emails', 'user-registration-profile-completeness' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ),
						'preview_link' => ur_email_preview_link(
							__( 'Preview', 'user-registration-profile-completeness' ),
							$this->id
						),
						'settings'     => array(
							array(
								'title'    => __( 'Email Subject', 'user-registration-profile-completeness' ),
								'desc'     => __( 'Customize the email subject.', 'user-registration-profile-completeness' ),
								'id'       => 'user_registration_profile_completeness_congrats_email_subject',
								'type'     => 'text',
								'default'  => apply_filters( 'user_registration_profile_completeness_congrats_email_subject', __( 'Congratulations! You Have Completed Your Profile - {{blog_info}}', 'user-registration-profile-completeness' ) ),
								'css'      => 'min-width: 350px;',
								'desc_tip' => true,
							),
							array(
								'title'    => __( 'Email Content', 'user-registration-profile-completeness' ),
								'desc'     => __( 'Customize the content of the congratulatory email.', 'user-registration-profile-completeness' ),
								'id'       => 'user_registration_profile_completeness_congrats_email_message',
								'type'     => 'tinymce',
								'default'  => $this->user_registration_get_profile_completion_congrats_email(),
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
	 * Congratulations email sent to users on completing their profile.
	 *
	 * @since 1.0.0
	 */
	public function user_registration_get_profile_completion_congrats_email() {
		$message = apply_filters(
			'user_registration_profile_completeness_congrats_email_message',
			sprintf(
				__( 'Hello {{username}},<br><br>Congratulations on completing your profile! You can now fully access all the features of our site.<br><br>Thank you for using our site.<br>Best regards,<br>{{blog_info}}', 'user-registration-profile-completeness' )
			)
		);

		return $message;
	}
}
