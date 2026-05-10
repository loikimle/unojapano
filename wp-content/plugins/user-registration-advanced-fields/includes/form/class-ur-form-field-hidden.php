<?php
/**
 * UR_Form_Field_Hidden.
 *
 * @package  UserRegistrationAdvancedFields/Form
 * @category Admin
 * @author   WPEverest
 *
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Hidden Class
 */
class UR_Form_Field_hidden extends UR_Form_Field {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * Get Instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id                       = 'user_registration_hidden';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Hidden Field', 'user-registration-advanced-fields' ),
			'icon'  => 'ur-icon ur-icon-hidden-field',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'Hidden Field', 'user-registration-advanced-fields' ),
			'default_field_name' => 'hidden_' . ur_get_random_number(),
		);

	}

	/**
	 * Get registered admin fields.
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list " class="ur-registered-item draggable" data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}

	/**
	 * Validate Hidden field.
	 *
	 * @param mixed $single_form_field Single form field.
	 * @param mixed $form_data Form Data.
	 * @param mixed $filter_hook Filter hook.
	 * @param int   $form_id Form id.
	 */
	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		// empty code body.
	}
}

return UR_Form_Field_Hidden::get_instance();
