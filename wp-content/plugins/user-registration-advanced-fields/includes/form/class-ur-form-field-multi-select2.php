<?php
/**
 * UR_Form_Field_Multi_Select2.
 *
 * @package  UserRegistrationAdvancedFields/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Multi_Select2 Class
 */
class UR_Form_Field_Multi_Select2 extends UR_Form_Field {

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

		$this->id                       = 'user_registration_multi_select2';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Multi Select2', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-multi-select',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'Multi Select2', 'user-registration' ),
			'default_field_name' => 'multi_select2_' . ur_get_random_number(),
			'default_options'    => array(
				__( 'First Choice', 'user-registration' ),
				__( 'Second Choice', 'user-registration' ),
				__( 'Third Choice', 'user-registration' ),
			),
		);

		add_filter( "{$this->id}_advance_class", array( $this, 'settings_override' ), 10, 1 );
	}

	public function settings_override( $file_path_override ) {
		$file_path_override['file_path'] = URAF_ABSPATH . 'includes' . UR_DS . 'form' . UR_DS . 'settings' . UR_DS . 'class-ur-setting-multi-select2.php';
		return $file_path_override;
	}

	/**
	 * Get registered admin fields
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list " class="ur-registered-item draggable" data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}

	/**
	 * Validate Multi-select2 field.
	 *
	 * @param mixed $single_form_field Single form field.
	 * @param mixed $form_data Form Data.
	 * @param mixed $filter_hook Filter hook.
	 * @param int   $form_id Form id.
	 */
	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {

		$field_label  = $single_form_field->general_setting->label;
		$value        = isset( $form_data->value ) ? $form_data->value : array();
		$choice_limit = $single_form_field->advance_setting->choice_limit;

		if ( ! empty( $choice_limit ) && 0 < intval( $choice_limit ) ) {
			$options_count = is_array( $form_data->value ) ? count( $form_data->value ) : 0;

			if ( $options_count > $choice_limit ) {
				add_filter(
					$filter_hook,
					function ( $msg ) use ( $choice_limit, $field_label ) {
						return sprintf(
							__( 'Please select no more than %d items for %s.', 'user-registration' ), // phpcs:ignore
							$choice_limit,
							"<strong>$field_label</strong>"
						);
					}
				);
			}
		}

		$valid_options = $single_form_field->general_setting->options;

		foreach ( $value as $option ) {
			if ( ! in_array( $option, $valid_options, true ) ) {
				add_filter(
					$filter_hook,
					function ( $msg ) use ( $field_label ) {
						/* translators: %1$s - Field Label */
						return sprintf( __( 'Please choose a valid option for %1$s field.', 'user-registration' ), "<strong>$field_label</strong>" );
					}
				);
				return;
			}
		}

	}
}

return UR_Form_Field_Multi_Select2::get_instance();
