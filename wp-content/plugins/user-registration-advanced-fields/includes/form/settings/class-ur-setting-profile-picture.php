<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * URAF_Setting_Profile_Picture Class
 *
 * @package  UserRegistrationAdvancedFields/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Profile_Picture extends UR_Field_Settings {


	public function __construct() {
		$this->field_id = 'profile_pic_advance_setting';
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
				'placeholder' => esc_html__( 'Custom Class', 'user-registration' ),
				'tip'         => __( 'Custom css class to embed in this field.', 'user-registration-advanced-fields' ),
			),
			'valid_file_type' => array(
				'type'     => 'select',
				'label'    => __( 'Valid File Types', 'user-registration-advanced-fields' ),
				'multiple' => true,
				'data-id'  => $this->field_id . '_valid_file_type',
				'name'     => $this->field_id . '[valid_file_type]',
				'class'    => $this->default_class,
				'required' => true,
				'default'  => array(),
				'options'  => uraf_get_valid_file_type(),
				'tip'      => __( 'Choose valid file types allowed for uploads', 'user-registration-advanced-fields' ),
			),
			'max_upload_size' => array(
				'type'        => 'text',
				'label'       => __( 'Max File Size Allowed', 'user-registration-advanced-fields' ),
				'data-id'     => $this->field_id . '_max_upload_size',
				'name'        => $this->field_id . '[max_upload_size]',
				'class'       => $this->default_class,
				'required'    => true,
				'placeholder' => '1024',
				'default'     => '',
				'tip'         => sprintf( esc_html__( 'Enter the max file size, in Kb, to allow. If left blank, the value defaults to the maximum size the server allows which is %s.', 'user-registration-advanced-fields' ), ( wp_max_upload_size() / 1024 ) . ' Kb' ),
			),
		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_Profile_Picture();
