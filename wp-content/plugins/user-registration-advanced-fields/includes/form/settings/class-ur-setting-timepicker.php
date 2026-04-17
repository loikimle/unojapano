<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * URAF_Setting_Timepicker Class
 *
 * @package  UserRegistrationAdvancedFields/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */
class UR_Setting_Timepicker extends UR_Field_Settings {


	public function __construct() {
		$this->field_id = 'timepicker_advance_setting';
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

			'time_min'                  => array(
				'label'       => __( 'Minimum Time', 'user-registration-advanced-fields' ),
				'data-id'     => $this->field_id . '_time_min',
				'name'        => $this->field_id . '[time_min]',
				'class'       => $this->default_class . ' ur-settings-time-min',
				'type'        => 'text',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( '11:30am', 'user-registration-advanced-fields' ),
				'tip'         => __( 'Minimum allowed time.', 'user-registration-advanced-fields' ),
			),

			'time_max'                  => array(
				'label'       => __( 'Maximum Value', 'user-registration-advanced-fields' ),
				'data-id'     => $this->field_id . '_time_max',
				'name'        => $this->field_id . '[time_max]',
				'class'       => $this->default_class . ' ur-settings-time-max',
				'type'        => 'text',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( '2:30pm', 'user-registration-advanced-fields' ),
				'tip'         => __( 'Maximum allowed time.', 'user-registration-advanced-fields' ),
			),

			'current_time' => array(
				'type'     => 'select',
				'data-id'  => $this->field_id . 'current_time',
				'label'    => __( 'Set Current Time', 'user-registration-advanced-fields' ),
				'name'     => $this->field_id . '[current_time]',
				'class'    => $this->default_class . ' ur-settings-current_time',
				'default'  => 'false',
				'required' => false,
				'options'  => array(
					'no'  => __( 'No', 'user-registration-advanced-fields' ),
					'yes' => __( 'Yes', 'user-registration-advanced-fields' ),
				),
				'tip'      => __( 'Enable this if you want set the current time .', 'user-registration-advanced-fields' ),
			),

			'time_interval'   => array(
				'label'       => __( 'Time Interval', 'user-registration-advanced-fields' ),
				'data-id'     => $this->field_id . '_time_interval',
				'name'        => $this->field_id . '[time_interval]',
				'class'       => $this->default_class . ' ur-settings-time_interval',
				'type'        => 'number',
				'required'    => false,
				'default'     => 30,
				'placeholder' => __( 'Time Interval', 'user-registration-advanced-fields' ),
				'tip'         => __( 'Allows users to enter specific time intervals.', 'user-registration-advanced-fields' ),
			),
		);

		$fields = apply_filters( 'user_registration_timepicker_field_advance_settings', $fields, $this->field_id, $this->default_class );
		$this->render_html( $fields );
	}
}

return new UR_Setting_Timepicker();
