<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract UR Field Setting Class
 *
 * @version  1.0.0
 * @package  UserRegistrationSocialConnect/Abstracts
 * @category Abstract Class
 * @author   WPEverest
 */
abstract class URSC_Social_Networks {

	protected $response;

	protected $api_key;

	protected $api_secret;

	/**
	 * @param $email
	 *
	 * @return array
	 */
	public function get_user_by_email( $email ) {

		global $wpdb;

		$user_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_email=%s", $email ) );

		if ( isset( $user_data[0] ) ) {
			return ( (array) $user_data[0] );
		}

		return array();
	}


	/**
	 * @return string
	 */
	function call_back_url() {

		$url = ( ! empty( $_SERVER['HTTPS'] ) ) ? 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$formatted_url = substr( $url, 0, strpos( $url, '?' ) );
		$url           = esc_url_raw( $formatted_url );

		if ( strpos( $url, '?' ) === false ) {
			$url .= '?';
		} else {
			$url .= '&';
		}

		return $url;
	}

	/**
	 * @param $api_key
	 * @param $api_secret
	 *
	 * @return mixed
	 */
	abstract function request( $api_key, $api_secret );


	/**
	 * @return mixed
	 */
	abstract function get_social_network_data();

	/**
	 * @return mixed
	 */
	abstract function set_access_token();

	/**
	 * @return mixed
	 */
	abstract function set_network_response();


	/**
	 * @param $response
	 */
	protected function set_response( $response ) {

		global $ursc_response_global;

		$ursc_response_global = $response;

		$response_json = json_encode( $response );

		if ( 'SUCCESS' === $response['status'] ) {

			user_registration_social_connect_set_session( 'user_registration_social_connect_network_response', $response );
		}
	}
}
