<?php
/**
 * URFV_Setting_Range.
 *
 * @version  1.0.0
 * @package  UserRegistrationFieldVisibility/Fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * URFV_Setting_Range Class.
 */
class URFV_Setting_Range extends URFV_Setting_Base {

	/**
	 * URFV_Setting_Range class constructor.
	 */
	public function __construct() {
		$this->field_id = 'range_advance_setting';
		parent::__construct();
	}
}

return new URFV_Setting_Range();
