<?php
/**
 * URFV_Setting_User_Email.
 *
 * @version  1.0.0
 * @package  UserRegistrationFieldVisibility/Fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * URFV_Setting_User_Email Class.
 */
class URFV_Setting_User_Email extends URFV_Setting_Base {

	/**
	 * URFV_Setting_User_Email class constructor.
	 */
	public function __construct() {
		$this->field_id = 'user_email_advance_setting';
		parent::__construct();

		unset( $this->fields['read_only']['options']['reg_form'] );
		unset( $this->fields['read_only']['options']['both'] );
	}
}

return new URFV_Setting_User_Email();
