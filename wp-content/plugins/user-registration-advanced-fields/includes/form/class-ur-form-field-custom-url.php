<?php
/**
 * UserRegistrationAdvancedFields Admin.
 *
 * @class    UR_Form_Field_Custom_Url
 * @version  1.0.0
 * @package  UserRegistrationAdvancedFields/Form
 * @category Admin
 * @author   WPEverest
 * @since 1.5.6
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Form_Field_Custom_Url Class
 */
class UR_Form_Field_Custom_Url extends UR_Form_Field {

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

		$this->id = 'user_registration_custom_url';

		$this->form_id = 1;

		$this->registered_fields_config = array(

			'label' => __( 'Custom URL', 'user-registration-advanced-fields' ),

			'icon'  => 'ur-icon ur-icon-website',
		);
		$this->field_defaults           = array(

			'default_label'      => __( 'Custom URL', 'user-registration-advanced-fields' ),

			'default_field_name' => 'custom_url_' . ur_get_random_number(),

		);

		add_filter( "{$this->id}_advance_class", array( $this, 'settings_override' ), 10, 1 );
	}

	public function settings_override( $file_path_override ) {
		$file_path_override['file_path'] = URAF_ABSPATH . 'includes' . UR_DS . 'form' . UR_DS . 'settings' . UR_DS . 'class-ur-setting-custom-url.php';
		return $file_path_override;
	}

	/**
	 * Get registered admin fields.
	 */
	public function get_registered_admin_fields() {

		return '<li
				id="' . $this->id . '_list "
				class="ur-registered-item draggable"
                data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' .
				$this->registered_fields_config['label'] .
				'</li>';
	}


	/**
	 * Validate Url field.
	 *
	 * @param mixed $single_form_field Single form field.
	 * @param mixed $form_data Form Data.
	 * @param mixed $filter_hook Filter hook.
	 * @param int   $form_id Form id.
	 */
	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		// Custom field validation here.
	}
}

return UR_Form_Field_Custom_Url::get_instance();
