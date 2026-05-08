<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Shipping_country Class
 *
 * @package  UserRegistration/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Shipping_country extends UR_Field_Settings {


	public function __construct() {
		$this->field_id = 'shipping_country_advance_setting';
	}

	public function output( $field_data = array() ) {

		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;

		return $field_html;
	}

	public function register_fields() {

		$fields = array(
			'selected_countries' => array(
				'label'    => __( 'Selected Countries', 'user-registration-woocommerce' ),
				'data-id'  => $this->field_id . '_selected_countries',
				'name'     => $this->field_id . '[selected_countries][]',
				'class'    => $this->default_class . ' ur-settings-selected-countries',
				'type'     => 'select',
				'default'  => array_keys( UR_Form_Field_Shipping_Country::get_instance()->get_country() ),
				'multiple' => true,
				'required' => true,
				'options'  => UR_Form_Field_Shipping_Country::get_instance()->get_country(),
				'tip'      => __( 'Select countries to give user as options.', 'user-registration-woocommerce' ),
			),
			'default_value'      => array(
				'label'    => __( 'Default Value', 'user-registration-woocommerce' ),
				'data-id'  => $this->field_id . '_default_value',
				'name'     => $this->field_id . '[default_value]',
				'class'    => $this->default_class . ' ur-settings-default-value',
				'type'     => 'select',
				'required' => false,
				'default'  => 'AF',
				'options'  => $this->get_default_value_options(),
				'tip'      => __( 'Select a default country.', 'user-registration-woocommerce' ),
			),
			'custom_class'       => array(
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

	/**
	 * Get country options for the default value field option.
	 */
	public function get_default_value_options() {

		$selected_countries = isset( $this->field_data->advance_setting->selected_countries ) ? $this->field_data->advance_setting->selected_countries : null;
		$value              = UR_Form_Field_Billing_Country::get_instance()->get_country();

		// Get only the selected countries
		if ( is_array( $selected_countries ) ) {
			$value = array_intersect_key(
				UR_Form_Field_Shipping_Country::get_instance()->get_country(),
				array_flip( $selected_countries )
			);
		}

		return $value;
	}
}

return new UR_Setting_Shipping_country();
