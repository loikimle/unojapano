<?php
/**
 * URMC_Ajax
 *
 * AJAX Event Handler
 *
 * @class    URMC_Ajax
 * @version  1.0.0
 * @package  UserRegistrationFileUpload/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URMC_Ajax Class
 */
class URMC_Ajax {

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
			'mailchimp_account_action'            => false,
			'mailchimp_account_disconnect_action' => false,
			'mailchimp_lists_by_api_key_action'   => false,
			'mailchimp_groups_by_list_id_action'  => false,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {

			add_action( 'wp_ajax_user_registration_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {

				add_action(
					'wp_ajax_nopriv_user_registration_' . $ajax_event,
					array(
						__CLASS__,
						$ajax_event,
					)
				);
			}
		}
	}

	/**
	 * Connect to mailchimp Account by API Key.
	 */
	public static function mailchimp_lists_by_api_key_action() {
		try {
			$lists = self::api_lists( $_POST['ur_mailchimp_api_key'] );
			if ( is_wp_error( $lists ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'API list error: No lists found', 'user-registration-mailchimp' ),
					)
				);
			} else {
				wp_send_json_success(
					array(
						'mailchimp_list' => $lists,
					)
				);
			}
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'API list error: No lists found', 'user-registration-mailchimp' ),
				)
			);
		}

	}

	/**
	 * Get Mailchimp Group by List ID.
	 */
	public static function mailchimp_groups_by_list_id_action() {
		try {
			$lists = self::api_lists( $_POST['ur_mailchimp_api_key'] );
			if ( is_wp_error( $lists ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'API list error: No lists found', 'user-registration-mailchimp' ),
					)
				);
			} else {
				wp_send_json_success(
					array(
						'mailchimp_list' => $lists,
					)
				);
			}
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'API list error: No lists found', 'user-registration-mailchimp' ),
				)
			);
		}

	}

	/**
	 * Get Integration account lists.
	 *
	 * @param string $api_key    Account ID for fetching the account lists.
	 */
	public static function api_lists( $api_key ) {
		try {

			if ( ! class_exists( '\DrewM\MailChimp\MailChimp' ) ) {
				require_once URMC_ABSPATH . 'mailchimp-api/src/Batch.php';
				require_once URMC_ABSPATH . 'mailchimp-api/src/Webhook.php';
				require_once URMC_ABSPATH . 'mailchimp-api/src/MailChimp.php';
			}
			$mailchimp             = new \DrewM\MailChimp\MailChimp( $api_key );
			$mailchimp->verify_ssl = apply_filters( 'user_registration_mailchimp_verify_ssl', true );
			$result_array          = $mailchimp->get( 'lists' );
			$parsed_lists          = array();
			$lists                 = isset( $result_array['lists'] ) ? $result_array['lists'] : array();

			foreach ( $lists as $key => $list ) {

				$parsed_array                  = array();
				$list_id                       = isset( $list['id'] ) ? $list['id'] : '';
				$total_member                  = isset( $list['stats']['member_count'] ) ? $list['stats']['member_count'] : 0;
				$parsed_array['list_id']       = $list_id;
				$parsed_array['web_id']        = $list['web_id'];
				$parsed_array['list_title']    = $list['name'];
				$parsed_array['updated_date']  = gmdate( 'Y-m-d H:i:s' );
				$parsed_array['created_date']  = gmdate( 'Y-m-d H:i:s' );
				$parsed_array['total_members'] = $total_member;

				$merge_fields_response = $mailchimp->get(
					'lists/' . $list_id . '/merge-fields',
					array(
						'count' => 500,
					)
				);
				$merge_fields          = isset( $merge_fields_response['merge_fields'] ) ? $merge_fields_response['merge_fields'] : array();
				$parsed_merge_fields   = array();
				$has_email             = false;
				$parsed_merge_fields[] = array(
					'merge_id' => 0,
					'tag'      => 'email_address',
					'name'     => 'Email Address',
					'type'     => 'email',
					'required' => true,
				);

				foreach ( $merge_fields as $field_key => $field_value ) {
					$single_merge_field             = array();
					$single_merge_field['merge_id'] = $field_value['merge_id'];
					$single_merge_field['tag']      = $field_value['tag'];
					$single_merge_field['name']     = $field_value['name'];
					$single_merge_field['type']     = $field_value['type'];
					$single_merge_field['required'] = $field_value['required'];

					if ( false === $has_email && 'EMAIL' === $field_value['tag'] ) {
						$has_email = true;
					}
					array_push( $parsed_merge_fields, $single_merge_field );
				}

				if ( $has_email ) {
					unset( $parsed_merge_fields[0] );
				}
				$parsed_array['field_count'] = count( $parsed_merge_fields );
				$interest_list               = $mailchimp->get(
					'lists/' . $list_id . '/interest-categories',
					array(
						'count'  => 500,
						'fields' => 'categories.id,categories.title,categories.type',
					)
				);

				if ( isset( $interest_list['categories'] ) && ! empty( $interest_list['categories'] ) ) {
					$group_details = array();

					foreach ( $interest_list['categories'] as $key => $value ) {
						$groups          = $mailchimp->get(
							'lists/' . $list_id . '/interest-categories/' . $value['id'] . '/interests',
							array(
								'count'  => 500,
								'fields' => 'interests.id,interests.name',
							)
						);
						$value['groups'] = $groups['interests'];
						array_push( $group_details, $value );
					}
					$parsed_merge_fields['interests'] = $group_details;
				}
				$parsed_array['list_fields'] = wp_json_encode( $parsed_merge_fields );
				array_push( $parsed_lists, $parsed_array );
			}

			if ( ! empty( $parsed_lists ) ) {
				return $parsed_lists;
			} else {
				throw  new Exception( 'API list error: No lists found' );
			}
		} catch ( Exception $e ) {
			$logger = ur_get_logger();
			$logger->error( $e->getMessage(), array( 'source' => 'ur-mailchimp' ) );
			$error_msg = __( 'API list error: No lists found', 'user-registration-mailchimp' );
			return new \WP_Error( 'user-registration-mailchimp-error', $error_msg );
		}
	}

	/**
	 * New API Key for mailchimp
	 *
	 * @throws Exception Post data set.
	 */
	public static function mailchimp_account_action() {
		try {
			check_ajax_referer( 'ur_mailchimp_account_save_nonce', 'security' );
			if ( ! isset( $_POST['ur_mailchimp_api_key'] ) || empty( $_POST['ur_mailchimp_api_key'] ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Please enter mailchimp API Key.', 'user-registration-mailchimp' ),
					)
				);
			}
			$authorized = self::ur_check_mailchimp_api_key( $_POST['ur_mailchimp_api_key'] );
			if ( $authorized ) {
				$connected_accounts = get_option( 'ur_mailchimp_accounts', array() );
				if ( ! in_array( $_POST['ur_mailchimp_api_key'], array_column( $connected_accounts, 'api_key' ), true ) ) {
					$id           = count( $connected_accounts ) + 1;
					$new_accounts = array(
						'api_key' => trim( $_POST['ur_mailchimp_api_key'] ),
						'label'   => ! empty( $_POST['ur_mailchimp_account_name'] ) ? sanitize_text_field( $_POST['ur_mailchimp_account_name'] ) : 'Account ' . $id,
						'date'    => date_i18n( 'Y-m-d H:i:s' ),
					);
					array_push( $connected_accounts, $new_accounts );
					update_option( 'ur_mailchimp_accounts', $connected_accounts );
					wp_send_json_success(
						array(
							'new_account' => $new_accounts,
							'message'     => __( 'Connected', 'user-registration-mailchimp' ),
						)
					);
				} else {
					wp_send_json_error(
						array(
							'message' => __( 'API Already Exits', 'user-registration-mailchimp' ),
						)
					);
				}
			}
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Validate API Key from Mailer Library.
	 *
	 * @param string $api_key API Key.
	 */
	public static function ur_check_mailchimp_api_key( $api_key ) {
		if ( empty( $api_key ) ) {
			return false;
		}
		if ( ! class_exists( '\DrewM\MailChimp\MailChimp' ) ) {
			require_once URMC_ABSPATH . 'mailchimp-api/src/Batch.php';
			require_once URMC_ABSPATH . 'mailchimp-api/src/Webhook.php';
			require_once URMC_ABSPATH . 'mailchimp-api/src/MailChimp.php';
		}

		$mailchimp             = new \DrewM\MailChimp\MailChimp( $api_key );
		$mailchimp->verify_ssl = apply_filters( 'user_registration_mailchimp_verify_ssl', true );

		$response = $mailchimp->get( '/' );

		$account_id = isset( $response['account_id'] ) ? $response['account_id'] : '';

		if ( empty( $account_id ) ) {

			wp_send_json_error(
				array(
					'message' => esc_html__( 'May be api key invalid or could not connect to mailchimp.', 'user-registration-mailchimp' ),
				)
			);
		}

		return true;
	}


	/**
	 * Delete API Key for mailchimp
	 *
	 * @throws Exception Post data set.
	 */
	public static function mailchimp_account_disconnect_action() {
		try {
			check_ajax_referer( 'ur_mailchimp_account_disconnect_nonce', 'security' );

			if ( isset( $_POST['api_key'] ) ) {
				$connected_accounts = get_option( 'ur_mailchimp_accounts', array() );
				$account_exits = array_filter(
					$connected_accounts,
					function( $accounts ) {
						if ( $accounts['api_key'] !== $_POST['api_key'] ) {
							return $accounts;
						}
					}
				);

				if ( count( $account_exits ) >= 0 ) {

					update_option( 'ur_mailchimp_accounts', $account_exits );
					wp_send_json_success(
						array(
							'message' => __( 'Disconnected Successfully', 'user-registration-mailchimp' ),
						)
					);
				}
			}
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Nonce Error.', 'user-registration-mailchimp' ),
				)
			);
		}
	}

}

URMC_Ajax::init();
