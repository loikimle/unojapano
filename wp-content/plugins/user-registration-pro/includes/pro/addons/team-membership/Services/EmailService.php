<?php

/**
 * EmailService.php
 *
 * EmailService.php
 *
 * @class    EmailService.php
 * @package  Coupons
 * @author   WPEverest
 */

namespace WPEverest\URTeamMembership\Services;

use WPEverest\URTeamMembership\Emails\User\UR_Settings_Team_Member_Reset_Password_Email;
use WPEverest\URTeamMembership\Emails\User\UR_Settings_Team_Registered_Email;

class EmailService {

	protected $email_type, $logger;

	public function __construct() {
		$this->logger = ur_get_logger();
	}

	/**
	 * Send email
	 *
	 * @param $data
	 * @param $type
	 *
	 * @return bool|mixed|void
	 */
	public function send_email( $data, $type ) {
		if ( ! isset( $data['member_id'] ) ) {
			$this->logger->notice( 'Send Email:Registration: Member Id not Present.', array( 'source' => 'ur-membership-email-logs' ) );
			return false;
		}

		switch ( $type ) {
			case 'team_registered': // team registered
				return self::send_team_registration_email( $data );
			case 'team_member_reset_password': // team reset mail
				return self::send_team_member_reset_password_email( $data );
			default:
				break;
		}
	}


	public function send_team_registration_email( $data ) {
		$subject = get_option( 'user_registration_team_registered_email_subject', esc_html__( 'Team Registration Success', 'user-registration' ) );

		$form_id      = ur_get_form_id_by_userid( $data['member_id'] );
		$settings     = new UR_Settings_Team_Registered_Email();
		$team_service = new TeamService();
		$tags         = $team_service->get_team_tags( $data );
		$message      = apply_filters( 'user_registration_process_smart_tags', get_option( 'user_registration_team_registered_email_message', $settings->ur_get_team_registered_email() ), $tags, $form_id );
		$message      = apply_filters( 'ur_membership_team_registered_email_custom_template', $message, $subject );
		$template_id  = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );
		$headers      = \UR_Emailer::ur_get_header();
		if ( ur_string_to_bool( get_option( 'user_registration_enable_team_registered_email', true ) ) ) {
			return \UR_Emailer::user_registration_process_and_send_email( $data['member']->user_email, $subject, $message, $headers, array(), $template_id );
		}
	}

	public function send_team_member_reset_password_email( $data ) {
		$subject = get_option( 'user_registration_team_member_reset_password_email_subject', esc_html__( 'Team Member Reset Password', 'user-registration' ) );

		$form_id      = ur_get_form_id_by_userid( $data['member_id'] );
		$settings     = new UR_Settings_Team_Member_Reset_Password_Email();
		$team_service = new TeamService();
		$tags         = $team_service->get_team_tags( $data );
		$message      = apply_filters( 'user_registration_process_smart_tags', get_option( 'user_registration_team_member_reset_password_email_message', $settings->ur_get_team_member_reset_password_email() ), $tags, $form_id );
		$message      = apply_filters( 'ur_membership_team_member_reset_password_email_custom_template', $message, $subject );
		$template_id  = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );
		$headers      = \UR_Emailer::ur_get_header();
		if ( ur_string_to_bool( get_option( 'user_registration_enable_team_member_reset_password_email', true ) ) ) {
			return \UR_Emailer::user_registration_process_and_send_email( $data['member']->user_email, $subject, $message, $headers, array(), $template_id );
		}
	}
}
