<?php
/**
 * ProfileCompletionReminderEmail Email Setting.
 *
 * @package  WPEverest\UserRegistration\ProfileCompleteness\Admin\Emails
 * @since  1.0.0
 */

namespace WPEverest\UserRegistration\ProfileCompleteness\Admin\Emails;

/**
 * ProfileCompletionReminderEmail class.
 *
 * @since 1.0.0
 */
class ProfileCompletionReminderEmail {

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
		$this->id          = 'profile_completion_reminder_email';
		$this->title       = __( 'Profile Completion Reminder Email', 'user-registration-profile-completeness' );
		$this->description = __( 'A reminder email sent to users who have incomplete profiles.', 'user-registration-profile-completeness' );
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
			'user_registration_profile_completion_reminder_email',
			array(
				'title'    => __( 'Emails', 'user-registration-profile-completeness' ),
				'sections' => array(
					'completion_email' => array(
						'title'        => __( 'Profile Completion Reminder Email', 'user-registration-profile-completeness' ),
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
								'id'       => 'user_registration_profile_completion_reminder_email_subject',
								'type'     => 'text',
								'default'  => __( 'Reminder to Complete Your Profile - {{blog_info}}', 'user-registration-profile-completeness' ),
								'css'      => 'min-width: 350px;',
								'desc_tip' => true,
							),
							array(
								'title'    => __( 'Email Content', 'user-registration-profile-completeness' ),
								'desc'     => __( 'Customize the content of the reminder email.', 'user-registration-profile-completeness' ),
								'id'       => 'user_registration_profile_completion_reminder_email_content',
								'type'     => 'tinymce',
								'default'  => $this->get_completion_email(),
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
	 * Email message for profile completion status.
	 *
	 * @since 1.0.0
	 */
	public function get_completion_email() {
		$message = apply_filters(
			'user_registration_get_completion_email',
			sprintf(
				__( 'Hello {{username}},<br><br>Your profile completeness status is {{profile_completeness}}.<br><br>Thank you for using our site.<br>Best regards,<br>{{blog_info}}', 'user-registration-profile-completeness' )
			)
		);

		return $message;
	}
}
