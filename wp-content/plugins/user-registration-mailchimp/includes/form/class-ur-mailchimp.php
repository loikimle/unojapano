<?php
/**
 * UserRegistrationMailChimp Admin.
 *
 * @class    UR_MailChimp
 * @version  1.0.0
 * @package  UserRegistrationMailChimp/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_MailChimp Class
 */
class UR_MailChimp extends UR_Form_Field {

	private static $_instance;


	/**
	 * Get Instance.
	 *
	 * @return UR_MailChimp
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		$this->id = 'user_registration_mailchimp';

		$this->form_id = 1;

		$this->registered_fields_config = array(

			'label' => __( 'MailChimp', 'user-registration-mailchimp' ),

			'icon'  => 'ur-icon ur-icon-mailchimp',
		);
		$this->field_defaults           = array(

			'default_label'      => __( 'MailChimp', 'user-registration-mailchimp' ),

			'default_field_name' => 'mailchimp_' . ur_get_random_number(),
		);
	}


	/**
	 * Get registered admin fields.
	 *
	 * @return string
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list "

				class="ur-registered-item draggable"

                data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}


	/**
	 * Validate mailchimp Field.
	 *
	 * @param  array  $single_form_field Single Form Field.
	 * @param  array  $form_data Form Data.
	 * @param  string $filter_hook Filter Hook.
	 * @param  int    $form_id Forn ID.
	 */
	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		// TODO: Implement validation() method.
	}
}

return UR_MailChimp::get_instance();
