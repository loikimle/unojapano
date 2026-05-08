<?php
/**
 * UserRegistrationWooCommerce Admin.
 *
 * @class    UR_Form_Field_Billing_First_Name
 * @version  1.0.0
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Form_Field_Billing_First_Name Class
 */
class UR_Form_Field_Billing_First_Name extends UR_Form_Field {

	private static $_instance;


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

		$this->id = 'user_registration_billing_first_name';

		$this->form_id = 1;

		$this->registered_fields_config = array(

			'label' => __( 'First Name ', 'user-registration-woocommerce' ),

			'icon'  => 'ur-icon ur-icon-input-first-name',
		);
		$this->field_defaults           = array(

			'default_label'      => __( 'First Name', 'user-registration-woocommerce' ),

			'default_field_name' => 'billing_first_name',
		);

		add_filter( "{$this->id}_advance_class", array( $this, 'settings_override' ), 10, 1 );
	}

	public function settings_override( $file_path_override ) {
		$file_path_override['file_path'] = URWC_ABSPATH . 'includes' . UR_DS . 'form' . UR_DS . 'settings' . UR_DS . 'class-ur-setting-billing-first-name.php';
		return $file_path_override;
	}

	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list "

				class="ur-registered-item draggable"

                data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}


	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		// TODO: Implement validation() method.
	}
}

return UR_Form_Field_Billing_First_Name::get_instance();
