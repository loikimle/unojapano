<?php
/**
 * Local Currency Ajax
 *
 *
 * @class    Local_Currency
 * @package  Local_Currency
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Local_Currency\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local_Currency
 */
class Ajax {


	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {

		$ajax_events = array(
			'create_pricing_zone' => false,
			'edit_pricing_zone'	  => false,
			'delete_pricing_zone' => false,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {

			add_action( 'wp_ajax_user_registration_local_currency_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {

				add_action(
					'wp_ajax_nopriv_user_registration_local_currency_' . $ajax_event,
					array(
						__CLASS__,
						$ajax_event,
					)
				);
			}
		}
	}

	/**
	 * Add pricing zone.
	 *
	 * @since 6.0.0
	 */
	public static function create_pricing_zone() {
		if ( ! check_ajax_referer( 'user_registration_pricing_zone', 'security', false ) ) {
			wp_send_json_error( array(
				'message' => __( 'Nonce error please reload.', 'user-registration' ),
			) );
		}

		$action  = isset( $_POST['pricing_action'] ) ? sanitize_text_field( $_POST['pricing_action'] ) : 'add';
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		$zone_name = ! empty( $_POST['ur_local_currencies_zone_name'] )
			? sanitize_text_field( $_POST['ur_local_currencies_zone_name'] )
			: '';

		if ( empty( $zone_name ) ) {
			wp_send_json_error( array(
				'message' => __( 'Zone name is required.', 'user-registration' ),
			) );
		}

		$pricing_zone_details = array(
			'ur_local_currencies_zone_name'        => $zone_name,
			'ur_local_currencies_zone_description' => ! empty( $_POST['ur_local_currencies_zone_description'] )
				? sanitize_text_field( $_POST['ur_local_currencies_zone_description'] )
				: '',
			'ur_local_currencies_exchange_rate'    => ! empty( $_POST['ur_local_currencies_exchange_rate'] )
				? floatval( $_POST['ur_local_currencies_exchange_rate'] )
				: '',
			'ur_local_currencies_countries'         => ! empty( $_POST['ur_local_currencies_countries'] )
				? array_map( 'sanitize_text_field', (array) $_POST['ur_local_currencies_countries'] )
				: array(),
			'ur_local_currency'                    => ! empty( $_POST['ur_local_currency'] )
				? array_map( 'sanitize_text_field', (array) $_POST['ur_local_currency'] )
				: array(),
			'ur_local_currencies_conversion_type'  => ! empty( $_POST['ur_local_currencies_conversion_type'] )
				? sanitize_text_field( $_POST['ur_local_currencies_conversion_type'] )
				: 'manual',
		);

		if ( 'add' === $action ) {

			$post_id = wp_insert_post( array(
				'post_title'  => $zone_name,
				'post_name'   => sanitize_title( $zone_name ),
				'post_status' => 'publish',
				'post_type'   => 'urm_price_zone',
			) );

			if ( is_wp_error( $post_id ) ) {
				wp_send_json_error( array( 'message' => 'Failed to create pricing zone.' ) );
			}
		}

		if ( 'edit' === $action && $post_id ) {

			wp_update_post( array(
				'ID'         => $post_id,
				'post_title'=> $zone_name,
				'post_name' => sanitize_title( $zone_name ),
			) );
		}

		foreach ( $pricing_zone_details as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value );
		}

		wp_send_json_success( array(
			'message' => __( 'Pricing zone saved successfully.', 'user-registration' ),
		) );
	}


	/**
	 * Edit pricing zone.
	 *
	 * @since 6.0.0
	 */
	public static function edit_pricing_zone(){

		$post_id = absint( $_POST['post_id'] );

		$template = CoreFunctions::render_local_currencies_create_form( $post_id );

		wp_send_json_success(
			array(
				'template' => $template
			)
		);
	}

	/**
	 * Delete pricing zone.
	 *
	 * @since 6.0.0
	 */
	public static function delete_pricing_zone() {
		check_ajax_referer(
			'user_registration_pricing_zone',
			'security'
		);

		$post_id = isset( $_POST['post_id'] )
			? absint( $_POST['post_id'] )
			: 0;

		if ( ! $post_id ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid pricing zone.', 'user-registration' ) )
			);
		}

		if ( get_post_type( $post_id ) !== 'urm_price_zone' ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid post type.', 'user-registration' ) )
			);
		}

		wp_delete_post( $post_id, true );

		wp_send_json_success(
			array( 'message' => __( 'Pricing zone deleted.', 'user-registration' ) )
		);
	}
}
