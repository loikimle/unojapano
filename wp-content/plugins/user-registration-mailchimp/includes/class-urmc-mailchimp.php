<?php
/**
 * UserRegistrationMailChimp MailChimpClass.
 *
 * @class    URMC_Frontend
 * @version  1.0.0
 * @package  UserRegistrationMailChimp
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class URMC_MailChimp
 */
class URMC_MailChimp {

	/**
	 * Holds valid mailchimp Lists.
	 *
	 * @var array
	 */
	private static $valid_mailchimp_lists = array();

	/**
	 * Initialization of Mailchimp.
	 */
	private static function init() {
		if ( ! class_exists( '\DrewM\MailChimp\MailChimp' ) ) {
			require_once URMC_ABSPATH . 'mailchimp-api/src/Batch.php';
			require_once URMC_ABSPATH . 'mailchimp-api/src/Webhook.php';
			require_once URMC_ABSPATH . 'mailchimp-api/src/MailChimp.php';
		}
	}

	/**
	 * Mailchimp parse with mapping data.
	 *
	 * @param array $valid_form_data Form Data.
	 * @param array $fields Field.
	 * @param int   $enable_double_optin Enable Double Option.
	 * @param int   $user_id UserId.
	 */
	private static function parse_with_mapping_data( $valid_form_data, $user_id, $fields = array(), $enable_double_optin = 0 ) {

		$email_obj  = isset( $valid_form_data['user_email'] ) ? $valid_form_data['user_email'] : new stdClass();
		$user_email = isset( $email_obj->value ) ? $email_obj->value : '';

		if ( empty( $user_email ) ) {
			$logger = ur_get_logger();
			$logger->notice( 'Email not found on parse_with_mapping_data ', array( 'source' => 'ur-mailchimp' ) );
			return;
		}
		$status      = ur_string_to_bool( $enable_double_optin ) ? 'pending' : 'subscribed';
		$parsed_data = array(
			'email_address' => $user_email,
			'status'        => $status,
			'merge_fields'  => array(),
		);

		if ( ! empty( $fields ) ) {

			foreach ( $fields as $mailchimp_key => $form_field ) {

				if ( 'email_address' !== $mailchimp_key ) {
					$value_object = isset( $valid_form_data[ $form_field ] ) ? $valid_form_data[ $form_field ] : new stdClass();
					$value        = isset( $value_object->value ) ? $value_object->value : '';

					if ( ! empty( $value ) && '-1' !== $value ) {
						$field_object = (object) $value_object->extra_params;

						if ( 'date' === $field_object->field_key && '' !== $value ) {
							$date_format = get_option( 'user_registration_' . $form_field . '_date_format' );
							$date        = '' !== $date_format ? DateTime::createFromFormat( $date_format, $value ) : DateTime::createFromFormat( 'Y-m-d', $value );
							$value       = $date->format( 'm/d' );
						}

						if ( ( 'country' === $field_object->field_key || 'billing_country' === $field_object->field_key || 'shipping_country' === $field_object->field_key ) && '' !== $value ) {
							$country_class = ur_load_form_field_class( $field_object->field_key );
							$countries     = $country_class::get_instance()->get_country();
							$value         = isset( $countries[ $value ] ) ? $countries[ $value ] : $value;
						}
						$parsed_data['merge_fields'][ $mailchimp_key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
					}
				}
			}
		} else {

			$first_name = get_user_meta( $user_id, 'first_name', true );

			$last_name = get_user_meta( $user_id, 'last_name', true );

			if ( ! empty( $first_name ) ) {
				$parsed_data['merge_fields']['FNAME'] = $first_name;
			}
			if ( ! empty( $last_name ) ) {
				$parsed_data['merge_fields']['LNAME'] = $last_name;
			}
		}
		return $parsed_data;

	}

	/**
	 * Send Data to Mailchimp.
	 *
	 * @param array $valid_mailchimp_lists_array Mailchimp List Array.
	 * @param array $valid_form_data Form Data.
	 * @param int   $form_id Form Id.
	 * @param int   $user_id User Id.
	 *
	 * @throws Exception Throws exception according to conditions.
	 */
	public static function send_data( $valid_mailchimp_lists_array, $valid_form_data, $form_id, $user_id ) {
		$subscribed_lists = array();
		foreach ( $valid_mailchimp_lists_array as $key => $lists ) {
			foreach ( $lists as $list ) {

				try {
					$fields              = isset( $list['list_fields'] ) ? json_decode( $list['list_fields'] ) : array();
					$enable_double_optin = isset( $list['double_optin'] ) ? $list['double_optin'] : 0;
					$valid_data          = self::parse_with_mapping_data( $valid_form_data, $user_id, $fields, $enable_double_optin );

					if ( ! isset( $valid_data['email_address'] ) ) {
						throw  new Exception( __( 'Email address not found.', 'user-registration-mailchimp' ) );
					}

					if ( empty( $valid_data['email_address'] ) ) {
						throw  new Exception( __( 'Empty email address found.', 'user-registration-mailchimp' ) );
					}
					$member_id = md5( strtolower( $valid_data['email_address'] ) );
					self::init();

					if ( count( $valid_data['merge_fields'] ) < 1 ) {
						unset( $valid_data['merge_fields'] );
					}
					$list_groups = json_decode( $list['list_group'] );

					if ( ! empty( $list_groups ) ) {
						$groups = array();

						foreach ( $list_groups as $id => $segments ) {
							if ( is_array( $segments ) ) {
								foreach ( $segments as $id => $group ) {
									$groups[ $group ] = true;
								}
							} else {
								$groups[ $segments ] = true;
							}
						}

						if ( ! empty( $groups ) ) {
							$valid_data['interests'] = $groups;
						}
					}
					$mailchimp             = new \DrewM\MailChimp\MailChimp( $list['api_key'] );
					$mailchimp->verify_ssl = apply_filters( 'user_registration_mailchimp_verify_ssl', true );
					$result_array          = $mailchimp->put( 'lists/' . $list['list_id'] . '/members/' . $member_id, $valid_data );
					$result                = (object) $result_array;
					$id                    = isset( $result->id ) ? $result->id : '';

					if ( empty( $id ) ) {
						self::mailchimp_error_handler( $result, $list );
					} else {
						$list_data        = array(
							$list['list_id'] => $list['api_key'],
						);
						$subscribed_lists = array_merge( $subscribed_lists, $list_data );
					}
				} catch ( Exception $e ) {

					$logger = ur_get_logger();

					$logger->error( $e->getMessage(), array( 'source' => 'ur-mailchimp' ) );

					return false;

				}
			}
		}

		if ( count( $subscribed_lists ) > 0 ) {
			update_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', $subscribed_lists );
		}
	}

	/**
	 * Mailchimp Error Handler.
	 *
	 * @param mixed  $result Result.
	 * @param string $list List.
	 *
	 * @throws Exception Throws exception according to conditions.
	 */
	private static function mailchimp_error_handler( $result, $list ) {

		$error_status = isset( $result->status ) ? $result->status : '';

		$detail = isset( $result->detail ) ? $result->detail : '';

		$errors = isset( $result->errors ) ? $result->errors : array();

		if ( empty( $error_status ) && empty( $detail ) ) {

			throw  new Exception( 'Something wrong while connecting mailchimp list(' . $list . ').' );

		}

		if ( count( $errors ) < 1 || ! is_array( $errors ) ) {

			throw  new Exception( $detail . ' - list(' . $list . ').' );

		}
		$is_unknown_error = true;

		$logger = ur_get_logger();

		foreach ( $errors as $error ) {

			$field = isset( $error['field'] ) ? $error['field'] : '';

			$message = isset( $error['message'] ) ? $error['message'] : '';

			if ( ! empty( $field ) || ! empty( $message ) ) {

				$is_unknown_error = false;

				$logger->error( $field . ' ' . $message . ' - list(' . $list . ').', array( 'source' => 'ur-mailchimp' ) );
			}
		}
		if ( $is_unknown_error ) {

			throw  new Exception( __( 'Unknown error on mailchimp - list(' . $list . ').', 'user-registration-mailchimp' ) );

		}
	}

	/**
	 * Unsubcribe the user from Mailchimp.
	 *
	 * @param int    $user_id UserId.
	 * @param string $list_key List Key.
	 * @param string $api_key API Key.
	 */
	public static function unsubscribe( $user_id, $list_key, $api_key ) {
		try {

			$user      = get_userdata( $user_id );
			$user_data = isset( $user->data ) ? $user->data : new stdClass();

			self::init();

			$member_id = md5( strtolower( $user_data->user_email ) );

			$mailchimp             = new \DrewM\MailChimp\MailChimp( $api_key );
			$mailchimp->verify_ssl = apply_filters( 'user_registration_mailchimp_verify_ssl', true );
			$result_array          = $mailchimp->delete( 'lists/' . $list_key . '/members/' . $member_id );

			$result = (object) $result_array;

			$error_status = isset( $result->status ) ? $result->status : '';

			$user_subscriber_lists = get_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', true );

			if ( ! empty( $error_status ) ) {
				self::mailchimp_error_handler( $result, $list_key );
			} else {
				unset( $user_subscriber_lists[ $list_key ] );
				update_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', $user_subscriber_lists );
			}
		} catch ( Exception $exception ) {

			$logger = ur_get_logger();

			$logger->error( $exception->getMessage(), array( 'source' => 'ur-mailchimp' ) );

			return false;

		}
	}


}
