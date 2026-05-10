<?php
/**
 * UserRegistrationAdvancedFields Admin.
 *
 * @class    UR_Form_Field_Timepicker
 * @version  1.0.0
 * @package  UserRegistrationAdvancedFields/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Form_Field_Timepicker Class
 */
class UR_Form_Field_Timepicker extends UR_Form_Field {

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
	 * Hook in tabs.
	 */
	public function __construct() {

		$this->id = 'user_registration_timepicker';

		$this->form_id = 1;

		$this->registered_fields_config = array(

			'label' => __( 'Time Picker', 'user-registration-advanced-fields' ),

			'icon'  => 'ur-icon ur-icon-time-picker',
		);
		$this->field_defaults           = array(

			'default_label'      => __( 'Time Picker', 'user-registration-advanced-fields' ),

			'default_field_name' => 'timepicker_' . ur_get_random_number(),

		);

		add_filter( "{$this->id}_advance_class", array( $this, 'settings_override' ), 10, 1 );
	}

	public function settings_override( $file_path_override ) {
		$file_path_override['file_path'] = URAF_ABSPATH . 'includes' . UR_DS . 'form' . UR_DS . 'settings' . UR_DS . 'class-ur-setting-timepicker.php';
		return $file_path_override;
	}

	/**
	 * Get registered admin fields.
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list "

				class="ur-registered-item draggable"

                data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}


	/**
	 * Validate Timepicker field.
	 *
	 * @param mixed $single_form_field Single form field.
	 * @param mixed $form_data Form Data.
	 * @param mixed $filter_hook Filter hook.
	 * @param int   $form_id Form id.
	 */
	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {

		$field_label = $single_form_field->general_setting->label;
		$value       = strtotime( $form_data->value );

		if ( ! empty( $form_data->value ) && empty( $value ) ) {
			add_filter(
				$filter_hook,
				function ( $msg ) use ( $field_label ) {
					/* translators: %1$s - Field Label */
					return sprintf( __( 'Please choose a valid time for %1$s field.', 'user-registration' ), "<strong>$field_label</strong>" );
				}
			);
			return;
		}

		if ( ! empty( $single_form_field->advance_setting->time_min ) ) {
			$min           = $single_form_field->advance_setting->time_min;
			$min_timestamp = strtotime( $min );

			if ( $value < $min_timestamp ) {
				add_filter(
					$filter_hook,
					function ( $msg ) use ( $min, $field_label ) {
						return sprintf(
							/* translators: %1$s - Field Label */
							__( 'Please choose a time later than %1$s for %2$s.', 'user-registration' ),
							$min,
							"<strong>$field_label</strong>"
						);
					}
				);
				return;
			}
		}

		if ( ! empty( $single_form_field->advance_setting->time_max ) ) {
			$max           = $single_form_field->advance_setting->time_max;
			$max_timestamp = strtotime( $max );

			if ( $value > $max_timestamp ) {
				add_filter(
					$filter_hook,
					function ( $msg ) use ( $max, $field_label ) {
						return sprintf(
							/* translators: %1$s - Field Label */
							__( 'Please choose a time before %1$s for %2$s.', 'user-registration' ),
							$max,
							"<strong>$field_label</strong>"
						);
					}
				);
				return;
			}
		}
	}
}

return UR_Form_Field_Timepicker::get_instance();
