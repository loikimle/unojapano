<?php
/**
 * UserRegistrationSocialConnect Frontend.
 *
 * @class    URSC_Frontend
 * @version  1.0.0
 * @package  UserRegistrationSocialConnect/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URSC_Social_Data Class
 */
class URSC_Social_Data {


	/**
	 * @param $email
	 */
	public static function email_exists( $email ) {

		$exists = email_exists( $email );

		if ( $exists ) {

			return true;
		}

		return false;
	}

	/**
	 * @param $email
	 * @param $password
	 */
	public static function check_password( $email, $password ) {

		$check = wp_authenticate_email_password( null, $email, $password );

		if ( null === $check ) {

			return __( 'Could not found any user.', 'user-registration-social-connect' );
		}
		if ( is_wp_error( $check ) ) {

			return WP_Error::get_error_message( $check );
		}

		return 'success';
	}

	/**
	 * @param $network
	 * @param $user_id
	 */
	public static function is_already_connected_network( $username, $network_name, $email = '' ) {

		$meta_prefix = 'user_registration_social_connect_';
		$user_data   =
			get_users(
				array(
					'meta_key'    => $meta_prefix . $network_name . '_username',
					'meta_value'  => $username,
					'number'      => 1,
					'count_total' => false,
				)
			);

		if ( ! empty( $email ) ) {

			if ( isset( $user_data[0] ) ) {
				return $user_data[0]->data->user_email === $email ? true : false;
			}

			return false;
		}

		return count( $user_data ) === 0 ? false : true;
	}

	/**
	 * @param $username
	 * @param $email
	 */
	public static function is_already_connected( $username, $email = '' ) {

		$user_data = get_user_by( 'login', $username );

		if ( ! empty( $email ) ) {

			if ( $user_data ) {
				return $user_data->data->user_email === $email ? true : false;
			}

			return false;
		}

		return $user_data ? true : false;
	}

	/**
	 * @param $user_id
	 */
	public static function update_network_connection( $user_id, $network_data = array() ) {

		$network_name = $network_data['network'];
		$meta_prefix  = 'user_registration_social_connect_';
		update_user_meta( $user_id, $meta_prefix . $network_name . '_username', $network_data['username'] );
		update_user_meta( $user_id, $meta_prefix . $network_name . '_profile', $network_data['profile'] );

		/**
		 * Pull and update profile_pic, first_name and last_name through social connect.
		 *
		 * @since 1.3.4
		 */
		if ( '' !== $network_data['profile_pic'] ) {
			update_user_meta( $user_id, $meta_prefix . $network_name . '_profile_pic', $network_data['profile_pic'] );
			update_user_meta( $user_id, 'user_registration_profile_pic_url', $network_data['profile_pic'] );
		}

		update_user_meta( $user_id, 'first_name', $network_data['first_name'] );
		update_user_meta( $user_id, 'last_name', $network_data['last_name'] );

		update_user_meta( $user_id, $meta_prefix . 'bypass_current_password', true );
	}

	/**
	 * @param $network_data
	 */
	public static function check_user_and_login( $network_data ) {

		$meta_prefix = 'user_registration_social_connect_';
		$user_data   =
			get_users(
				array(
					'meta_key'    => $meta_prefix . $network_data['network'] . '_username',
					'meta_value'  => $network_data['username'],
					'number'      => 1,
					'count_total' => false,
				)
			);

		$user = $user_data[0];

		$status = self::login_user( $user->ID );

		if ( is_wp_error( $status ) ) {
			return $status;
		}

		if ( wp_safe_redirect( ursc_social_login_redirect() ) ) {
			exit;
		}
	}

	/**
	 * @param $user_id
	 */
	public static function login_user( $user_id ) {

		$login_option = get_option( 'user_registration_general_setting_login_options', '' );

		if ( 'admin_approval' === $login_option ) {

			$user_status = get_user_meta( $user_id, 'ur_user_status', true );

			if ( $user_status == 0 || $user_status == - 1 ) {

				$message = $user_status == 0 ? __( 'Your account is still pending approval.', 'user-registration-social-connect' ) : __( 'Your account has been denied.', 'user-registration-social-connect' );

				return new WP_Error( 'user_registration_admin_approval_prevent', $message );
			}
		}

		ursc_flush_all();
		wp_clear_auth_cookie();
		wp_set_auth_cookie( $user_id );
	}

	/**
	 * @param $userdata
	 *
	 * @return int|WP_Error
	 */
	public static function register_user( $userdata ) {

		$userdata = array(
			'user_login' => $userdata['user_login'],
			'user_pass'  => $userdata['user_pass'],
			'user_email' => $userdata['user_email'],
			'role'       => $userdata['role'],
		);

		$user_id = wp_insert_user( $userdata );

		return $user_id;
	}

	/**
	 * @param $network_data
	 * @param $password
	 */
	public static function ursc_register_user( $network_data, $password ) {
		$valid_form_data = array();
		$user_data       = array(
			'user_email' => $network_data['email'],
			'user_pass'  => $password,
			'user_login' => $network_data['username'],
			'role'       => get_option( 'user_registration_social_setting_default_user_role', 'subscriber' ),
		);

		if ( 'no' === get_option( 'user_registration_social_setting_enable_social_registration', 'no' ) ) {

			return __( 'Could not register user, please contact with site administrator', 'user-registration-social-connect' );
		}

		$user_id = self::register_user( $user_data );

		$form_data = array(
			'user_login' => array(
				'value'      => $network_data['username'],
				'field_name' => 'user_login',
			),
			'user_email' => array(
				'value'      => $network_data['email'],
				'field_name' => 'user_email',
			),
		);

		include_once UR_ABSPATH . 'includes' . UR_DS . 'frontend' . UR_DS . 'class-ur-frontend-form-handler.php';

		$form_id            = get_option( 'user_registration_social_setting_form_integration' );
		$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();
		$form_field_data    = UR_Frontend_Form_Handler::get_form_field_data( $post_content_array );
		$valid_form_data    = self::get_users_form_data( $form_field_data, $form_data );

		if ( 0 !== absint( $form_id ) ) {
			UR_Frontend_Form_Handler::ur_update_user_meta( $user_id, $valid_form_data, $form_id );
		}

		if ( is_wp_error( $user_id ) ) {
			return $user_id->get_error_message();
		}

		self::update_network_connection(
			$user_id,
			$network_data
		);
		do_action( 'user_registration_after_register_user_action', $valid_form_data, $form_id, $user_id );

		return $user_id;
	}

	/**
	 * Get Users Data On Specific format to send emails after social connect.
	 */
	private static function get_users_form_data( $form_field_data = array(), $form_data = array() ) {
		$valid_form_data = array();

		$form_data_field = wp_list_pluck( $form_data, 'field_name' );
		$form_key_list   = wp_list_pluck( wp_list_pluck( $form_field_data, 'general_setting' ), 'field_name' );

		foreach ( $form_data as $datas ) {
			$data = (object) $datas;
			if ( in_array( $data->field_name, $form_key_list ) ) {
				$form_data_index                      = array_search( $data->field_name, $form_key_list );
				$single_form_field                    = $form_field_data[ $form_data_index ];
				$general_setting                      = isset( $single_form_field->general_setting ) ? $single_form_field->general_setting : new stdClass();
				$single_field_key                     = $single_form_field->field_key;
				$single_field_label                   = isset( $general_setting->label ) ? $general_setting->label : '';
				$data->extra_params                   = array(
					'field_key' => $single_field_key,
					'label'     => $single_field_label,
				);
				$valid_form_data[ $data->field_name ] = UR_Frontend_Form_Handler::get_sanitize_value( $data );
			}
		}
		return $valid_form_data;
	}
}

return new URSC_Social_Data();
