<?php
/**
 * UserRegistrationAdvancedFields Validation
 *
 * @class    URAF_Validation
 * @version  1.0.0
 * @package  UserRegistrationAdvancedFields
 * @category Validation
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URWC_Frontend Class
 */
class URAF_Validation {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_filter( 'user_registration_form_key_list', array( $this, 'remove_validation_on_update' ) );
		add_filter( 'user_registration_field_validations', array( $this, 'add_field_validations' ) );
	}


	/**
	 * Bypass validation for certain fields on update profile.
	 *
	 * @param [array] $form_field_keys Form Field Keys.
	 * @return array
	 */
	public function remove_validation_on_update( $form_field_keys ) {
		$bypass_fields = array(
			'profile_pic_url',
		);

		foreach ( $bypass_fields as $field_key ) {
			$index = array_search( $field_key, $form_field_keys, true );
			if ( $index ) {
				unset( $form_field_keys[ $index ] );
			}
		}

		return $form_field_keys;
	}


	/**
	 * Add default validations to specific advanced fields.
	 *
	 * @param [array] $validations Field Validation array.
	 * @return array
	 */
	public function add_field_validations( $validations ) {

		$validations = array_merge(
			$validations,
			array(
				'custom_url' => array( 'is_url' ),
			)
		);

		return $validations;
	}
}

new URAF_Validation();
