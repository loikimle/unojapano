<?php
/**
 * UserRegistrationMailChimp Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistrationMailChimp/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Checks Mailchimp Compatible.
 *
 * @return string
 */
function urmc_is_compatible() {

	$ur_plugins_path     = WP_PLUGIN_DIR . URMC_DS . 'user-registration' . URMC_DS . 'user-registration.php';
	$ur_pro_plugins_path = WP_PLUGIN_DIR . URMC_DS . 'user-registration-pro' . URMC_DS . 'user-registration.php';

	if ( ! file_exists( $ur_plugins_path ) && ! file_exists( $ur_pro_plugins_path ) ) {
		return __( 'Please install <code>user-registration-pro</code> plugin to use <code>user-registration-mailchimp</code> addon.', 'user-registration-mailchimp' );
	}

	$ur_plugin_file_path     = 'user-registration/user-registration.php';
	$ur_pro_plugin_file_path = 'user-registration-pro/user-registration.php';

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( $ur_plugin_file_path ) && ! is_plugin_active( $ur_pro_plugin_file_path ) ) {
		return __( 'Please activate <code>user-registration-pro</code> plugin to use <code>user-registration-mailchimp</code> addon.', 'user-registration-mailchimp' );
	}

	if ( function_exists( 'UR' ) ) {
		$user_registration_version = UR()->version;
	} else {
		$user_registration_version = get_option( 'user_registration_version' );
	}

	if ( ! is_plugin_active( $ur_pro_plugin_file_path ) ) {

		if ( version_compare( $user_registration_version, '1.1.0', '<' ) ) {
			return __( 'Please update your <code>user registration</code> plugin(to at least 1.1.0 version) to use <code>user-registration-mailchimp</code> addon.', 'user-registration-mailchimp' );
		}
	} else {

		if ( version_compare( $user_registration_version, '3.0.0', '<' ) ) {
			return __( 'Please update your <code>user-registration-pro</code> plugin(to at least 3.0.0 version) to use <code>user-registration-mailchimp</code> addon.', 'user-registration-mailchimp' );
		}
	}

	return 'YES';

}

/**
 * Checks Plugin compatibility
 */
function urmc_check_plugin_compatibility() {

	add_action( 'admin_notices', 'user_registration_mailchimp_admin_notice', 10 );

}

/**
 * Admin Notices.
 */
function user_registration_mailchimp_admin_notice() {

	$class = 'notice notice-error';

	$message = urmc_is_compatible();

	if ( 'YES' !== $message ) {

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), ( $message ) );
	}
}

/**
 * Deprecate plugin missing notice.
 *
 * @deprecated 1.2.1
 *
 * @return void
 */
function urmc_admin_notices() {
	ur_deprecated_function( 'urmc_admin_notices', '1.2.1', 'user_registration_mailchimp_admin_notice' );
}

/**
 * Form field mailchimp.
 *
 * @param string $path Path.
 *
 * @return string
 */
function urmc_form_field_mailchimp( $path ) {

	return URMC_ABSPATH . 'includes/form/class-ur-mailchimp.php';
}

/**
 * Get registered form fields.
 *
 * @param array $fields Fields.
 *
 * @return array
 */
function urmc_registered_form_fields( $fields ) {

	$field = 'mailchimp';

	if ( ! isset( $fields[ $field ] ) ) {

		array_push( $fields, $field );
	}

	return $fields;
}

/**
 * Get valid mailchimp list.
 *
 * @param int   $form_id Form Id.
 * @param array $valid_form_data Form Data.
 *
 * @return array
 */
function urmc_get_valid_mailchimp_list( $form_id, $valid_form_data ) {
	$mailchimp_integration = ur_get_single_post_meta( $form_id, 'user_registration_mailchimp_integration', array() );

	$post_content       = ur_get_post_content( $form_id );
	$post_content_array = ur_get_form_data_by_key( $post_content, 'mailchimp' );

	$valid_mailchimp_lists = array(
		'selected_list'                 => array(),
		'sync_mailchimp_on_user_update' => array(),
		'unsubscribe_on_user_deletion'  => array(),
	);
	if ( count( $mailchimp_integration ) > 0 ) {

		foreach ( $post_content_array as $mailchimp_key => $mailchimp_data ) {

			if ( isset( $valid_form_data[ $mailchimp_key ] ) ) {
				$mailchimp_list = array();

				foreach ( $mailchimp_integration as $list_id => $lists ) {
					if ( isset( $lists['enable_conditional_logic'] ) && ur_string_to_bool( $lists['enable_conditional_logic'] ) ) {
						switch ( $lists['conditional_logic_data']['conditional_operator'] ) {
							case 'is':
								if ( $valid_form_data[ $lists['conditional_logic_data']['conditional_field'] ]->value === $lists['conditional_logic_data']['conditional_value'] ) {
									array_push( $mailchimp_list, $lists );
								}
								break;
							case 'is_not':
								if ( $valid_form_data[ $lists['conditional_logic_data']['conditional_field'] ]->value !== $lists['conditional_logic_data']['conditional_value'] ) {
									array_push( $mailchimp_list, $lists );
								}
								break;
							default:
								break;
						}
					} else {
						array_push( $mailchimp_list, $lists );
					}
				}
				$sync_mailchimp_on_user_update = isset( $mailchimp_data->advance_setting->sync_mailchimp_on_user_update ) ? $mailchimp_data->advance_setting->sync_mailchimp_on_user_update : '';
				$unsubscribe_on_user_deletion  = isset( $mailchimp_data->advance_setting->unsubscribe_on_user_deletion ) ? $mailchimp_data->advance_setting->unsubscribe_on_user_deletion : '';
				$is_mailchimp_checked          = isset( $valid_form_data[ $mailchimp_key ]->value ) ? $valid_form_data[ $mailchimp_key ]->value : '0';

				if ( ! empty( $mailchimp_list ) && $is_mailchimp_checked ) {
					$valid_mailchimp_lists['selected_list'][ $mailchimp_key ] = $mailchimp_list;
				}
				$valid_mailchimp_lists['sync_mailchimp_on_user_update'][ $mailchimp_key ] = $sync_mailchimp_on_user_update;
				$valid_mailchimp_lists['unsubscribe_on_user_deletion'][ $mailchimp_key ]  = $unsubscribe_on_user_deletion;
			}
		}
	}
	return $valid_mailchimp_lists;
}

/**
 * Send user data to mailchimp
 *
 * @param array $valid_form_data User Data.
 * @param int   $form_id Form Id.
 * @param int   $user_id User Id.
 */
function urmc_send_data_to_mailchimp( $valid_form_data, $form_id, $user_id ) {
	$valid_mailchimp_lists_array = urmc_get_valid_mailchimp_list( $form_id, $valid_form_data );
	$valid_mailchimp_lists       = $valid_mailchimp_lists_array['selected_list'];

	if ( count( $valid_mailchimp_lists ) > 0 ) {
		URMC_MailChimp::send_data( $valid_mailchimp_lists, $valid_form_data, $form_id, $user_id );
	} else {
		$logger = ur_get_logger();
		$logger->error( 'Could not found any valid mailchimp list.', array( 'source' => 'ur-mailchimp' ) );
	}
}

/**
 * Sync Mailchimp after email-confirmation and admin approval.
 *
 * @param int $user_id User Id.
 */
function urmc_sync_mailchimp_after_approval( $user_id ) {
	$form_id   = ur_get_form_id_by_userid( $user_id );
	$user_data = get_user_meta( $user_id );
	$profile   = user_registration_form_data( $user_id, $form_id );
	$user      = get_user_by( 'id', $user_id );

	$user_data['user_registration_first_name'] = isset( $user_data['first_name'] ) ? $user_data['first_name'] : array();
	$user_data['user_registration_last_name']  = isset( $user_data['last_name'] ) ? $user_data['last_name'] : array();
	$user_data['user_registration_user_login'] = isset( $user->user_login ) ? array( $user->user_login ) : array();
	$user_data['user_registration_user_email'] = isset( $user->user_email ) ? array( $user->user_email ) : array();

	$valid_form_data = array();

	foreach ( $user_data as $post_key => $post_data ) {
		$pos_billing = strpos( $post_key, 'billing_' );
		if ( false !== $pos_billing ) {
			$post_key = 'user_registration_' . $post_key;
		}
		$pos_shipping = strpos( $post_key, 'shipping_' );
		if ( false !== $pos_shipping ) {
			$post_key = 'user_registration_' . $post_key;
		}
		$pos = strpos( $post_key, 'user_registration_' );

		if ( false !== $pos ) {
			$new_string = substr_replace( $post_key, '', $pos, strlen( 'user_registration_' ) );

			if ( ! empty( $new_string ) && 'geolocation' !== $new_string ) {
				$tmp_array       = ur_get_valid_form_data_format( $new_string, $post_key, $profile, $post_data[0] );
				$valid_form_data = array_merge( $valid_form_data, $tmp_array );
			}
		}
	}
	urmc_send_data_to_mailchimp( $valid_form_data, $form_id, $user_id );
}

/**
 * Unsubscibe User from mailchimp on user deletion.
 *
 * @param object $user Users Data.
 */
function urmc_unsubscibe_user_from_mailchimp_on_user_deletion( $user ) {
	$form_id               = ur_get_form_id_by_userid( $user->ID );
	$user_data             = get_user_meta( $user->ID );
	$profile               = user_registration_form_data( $user->ID, $form_id );
	$valid_form_data       = array();
	$check_mailchimp_field = false;
	$mailchimp_field_name  = '';

	foreach ( $user_data as $post_key => $post_data ) {

		$pos = strpos( $post_key, 'user_registration_' );

		if ( false !== $pos ) {
			$new_string = substr_replace( $post_key, '', $pos, strlen( 'user_registration_' ) );
			if ( ! empty( $new_string ) && 'geolocation' !== $new_string ) {
				if ( 'mailchimp' === $profile[ $post_key ]['field_key'] ) {
					$check_mailchimp_field = true;
					$mailchimp_field_name  = $new_string;
				}
				$tmp_array       = ur_get_valid_form_data_format( $new_string, $post_key, $profile, $post_data[0] );
				$valid_form_data = array_merge( $valid_form_data, $tmp_array );
			}
		}
	}

	if ( ! $check_mailchimp_field || count( $valid_form_data ) < 1 ) {
		return;
	}

	$user_subscribe_list          = get_user_meta( $user->ID, 'urmc_subscribe_mailchimp_list', true );
	$valid_mailchimp_lists_array  = urmc_get_valid_mailchimp_list( $form_id, $valid_form_data );
	$unsubscribe_on_user_deletion = $valid_mailchimp_lists_array['unsubscribe_on_user_deletion'];

	if ( is_array( $user_subscribe_list ) ) {
		// Unsubscribe from all mailchimp list users has been subscribed with.
		foreach ( $user_subscribe_list as $prev_list_id => $pre_api_key ) {
			if ( isset( $unsubscribe_on_user_deletion[ $mailchimp_field_name ] ) && 'yes' === $unsubscribe_on_user_deletion[ $mailchimp_field_name ] ) {
				// Unsubscribe Previous list.
				URMC_MailChimp::unsubscribe( $user->ID, $prev_list_id, $pre_api_key );
			}
		}
	}
}
