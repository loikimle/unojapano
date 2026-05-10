<?php
/**
 * URFV_Setting_Custom_Url.
 *
 * @version  1.0.0
 * @package  UserRegistrationFieldVisibility/Fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * URFV_Setting_Custom_Url Class.
 */
class URFV_Setting_Custom_Url extends URFV_Setting_Base {

	/**
	 * URFV_Setting_Custom_Url class constructor.
	 */
	public function __construct() {
		$this->field_id = 'custom_url_advance_setting';
		parent::__construct();
	}
}

return new URFV_Setting_Custom_Url();
