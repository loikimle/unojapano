<?php
/**
 * Abstract UR Setting MailChimp Class
 *
 * @version  1.0.0
 * @package  UserRegistrationMailChimp/Form/Settings
 * @category Abstract Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Setting_MailChimp class.
 */
class UR_Setting_MailChimp extends UR_Field_Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->field_id = 'mailchimp_advance_setting';

	}

	/**
	 * Output.
	 *
	 * @param array $field_data Field Data.
	 */
	public function output( $field_data = array() ) {

		// TODO: Implement output() method.
		$this->field_data = $field_data;

		$this->register_fields();

		$field_html = $this->fields_html;

		return $field_html;
	}

	/**
	 * Register Fields.
	 */
	public function register_fields() {

		// TODO: Implement register_fields() method.
		$fields = array(

			'auto_check_list'               => array(

				'label'       => __( 'Auto check list', 'user-registration-mailchimp' ),

				'data-id'     => $this->field_id . '_auto_check_list',

				'name'        => $this->field_id . '[auto_check_list]',

				'class'       => $this->default_class . ' ur-settings-auto-check-list',

				'type'        => 'select',

				'required'    => true,

				'default'     => 'yes',

				'options'     => array(

					'yes' => 'Yes',
					'no'  => 'No',
				),
				'placeholder' => '',

				'tip'         => __( 'Enabling this option will check the MailChimp field by default in the frontend form.', 'user-registration-mailchimp' ),
			),
			'sync_mailchimp_on_user_update' => array(

				'label'       => __( 'Auto sync on user update', 'user-registration-mailchimp' ),

				'data-id'     => $this->field_id . '_sync_mailchimp_on_user_update',

				'name'        => $this->field_id . '[sync_mailchimp_on_user_update]',

				'class'       => $this->default_class . ' ur-settings-sync-mailchimp-on-user-update',

				'type'        => 'select',

				'required'    => true,

				'default'     => 'yes',

				'options'     => array(

					'yes' => 'Yes',
					'no'  => 'No',
				),
				'placeholder' => '',

				'tip'         => __( 'Whether to automatically synchronize the user profile details updated in the Mailchimp account', 'user-registration-mailchimp' ),
			),
			'unsubscribe_on_user_deletion'  => array(

				'label'       => __( 'Unsubscribe on user deletion', 'user-registration-mailchimp' ),

				'data-id'     => $this->field_id . '_unsubscribe_on_user_deletion',

				'name'        => $this->field_id . '[unsubscribe_on_user_deletion]',

				'class'       => $this->default_class . ' ur-settings-unsubscribe-on-user-deletion',

				'type'        => 'select',

				'required'    => true,

				'default'     => 'yes',

				'options'     => array(

					'yes' => 'Yes',
					'no'  => 'No',
				),
				'placeholder' => '',

				'tip'         => __( 'Whether to automatically unsubscribe user from mailchimp when user deleted from the site.', 'user-registration-mailchimp' ),
			),
		);

		$this->render_html( $fields );
	}
}

return new UR_Setting_MailChimp();
