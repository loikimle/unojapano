<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * URAF_Setting_Multi_Select2 Class
 *
 * @package  UserRegistrationAdvancedFields/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Multi_Select2 extends UR_Field_Settings {


	public function __construct() {
		$this->field_id = 'multi_select2_advance_setting';
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
				'label'       => esc_html__( 'Custom Class', 'user-registration-advanced-fields' ),
				'data-id'     => $this->field_id . '_custom_class',
				'name'        => $this->field_id . '[custom_class]',
				'class'       => $this->default_class . ' ur-settings-custom-class',
				'type'        => 'text',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( 'Custom Class', 'user-registration-advanced-fields' ),
				'tip'         => __( 'Custom css class to embed in this field.', 'user-registration-advanced-fields' ),
			),
			'choice_limit'          => array(
				'label'       => __( 'Choice Limit', 'user-registration-advanced-fields' ),
				'data-id'     => $this->field_id . '_choice_limit',
				'name'        => $this->field_id . '[choice_limit]',
				'class'       => $this->default_class . ' ur-settings-min',
				'type'        => 'number',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Choice Limit', 'user-registration-advanced-fields' ),
				'tip'         => __( 'Enter minimum number choices that can be selected.', 'user-registration-advanced-fields' ),
			),
			'select_all'          => array(
				'label'       => __( 'Select All ', 'user-registration-advanced-fields' ),
				'data-id'     => $this->field_id . '_select_all',
				'name'        => $this->field_id . '[select_all]',
				'class'       => $this->default_class . ' ur-settings-select',
				'type'        => 'select',
				'required'    => false,
				'options'     => array(
				'no'  => __( 'No', 'user-registration-advanced-fields' ),
				'yes' => __( 'Yes', 'user-registration-advanced-fields' ),
			),
				'default'     => 'no',
				'placeholder' =>'',
				'tip'         => __( 'Enable this option to select all the options', 'user-registration-advanced-fields' ),
			)
		);

		$fields = apply_filters( 'user_registration_multi_select2_field_advance_settings', $fields, $this->field_id, $this->default_class );
		$this->render_html( $fields );
	}
}

return new UR_Setting_Multi_Select2();
