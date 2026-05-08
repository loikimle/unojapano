<?php

namespace WPEverest\URTeamMembership\Emails;

use WPEverest\URTeamMembership\Emails\User\UR_Settings_Team_Member_Reset_Password_Email;
use WPEverest\URTeamMembership\Emails\User\UR_Settings_Team_Registered_Email;

/**
 * EmailSettings.php
 *
 * @class    EmailSettings.php
 * @date     4/24/2025 : 2:01 PM
 */
class EmailSettings {
	public function __construct() {
		add_filter( 'user_registration_email_classes', array( $this, 'add_email_settings' ), 10, 1 );
	}

	/**
	 * Add email settings
	 *
	 * @param $emails
	 *
	 * @return array
	 */
	public function add_email_settings( $emails ) {
		$new_emails['UR_Settings_Team_Registered_Email']            = new UR_Settings_Team_Registered_Email();
		$new_emails['UR_Settings_Team_Member_Reset_Password_Email'] = new UR_Settings_Team_Member_Reset_Password_Email();

		return array_merge( $emails, $new_emails );
	}
}
