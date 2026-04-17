<?php
/**
 * URWC_Ajax
 *
 * AJAX Event Handler
 *
 * @class    URWC_Ajax
 * @version  1.0.0
 * @package  UserRegistrationFileUpload/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URWC_Ajax Class
 */
class URWC_Ajax {

	/**
	 * Hooks in ajax handlers
	 */

	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {

		$ajax_events = array(
			'setting_form_field_listing' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_user_registration_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_user_registration_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Get form field table in Setting page.
	 */
	public static function setting_form_field_listing() {

		if ( ! check_ajax_referer( 'user_registration_woocommerce_form_field_listing_nonce', 'security', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Nonce error, please reload.', 'user-registration' ),
				)
			);
		}

		$form_id    = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : '';
		$option_key = isset( $_POST['option_key'] ) ? ur_clean( $_POST['option_key'] ) : '';

		$data = array();

		if ( $form_id && $option_key ) {
			$woocommerce_field_table_list = new URWC_Field_Table_List();
			$data['table']                = $woocommerce_field_table_list->display_table_list( $form_id, $option_key, true );
		} else {
			wp_send_json_success( array() );
		}

		wp_send_json_success( $data );
	}
}

URWC_Ajax::init();
