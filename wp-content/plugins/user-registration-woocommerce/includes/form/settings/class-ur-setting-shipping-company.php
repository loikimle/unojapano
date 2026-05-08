<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Shipping_company Class
 *
 * @package  UserRegistrationWooCommerce/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Shipping_company extends UR_Field_Settings {


	public function __construct() {
		$this->field_id = 'shipping_company_advance_setting';
	}

	public function output( $field_data = array() ) {

		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

	public function register_fields() {
		$fields = array(

			'custom_class' => array(
				'label'       => esc_html__( 'Custom Class', 'user-registration-woocommerce' ),
				'data-id'     => $this->field_id . '_custom_class',
				'name'        => $this->field_id . '[custom_class]',
				'class'       => $this->default_class . ' ur-settings-custom-class',
				'type'        => 'text',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( 'Custom Class', 'user-registration-woocommerce' ),
				'tip'         => __( 'Custom css class to embed in this field.', 'user-registration-woocommerce' ),
			),
		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_Billing_company();
