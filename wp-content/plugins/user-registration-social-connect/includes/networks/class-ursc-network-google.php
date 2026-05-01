<?php
/**
 * UserRegistrationSocialConnect Frontend.
 *
 * @class    URSC_Network_Google
 * @version  1.0.0
 * @package  UserRegistrationSocialConnect/Networks
 * @category Networks
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URSC_Network_Google Class
 */
class URSC_Network_Google extends URSC_Social_Networks {

	/**
	 * @param $api_key
	 * @param $api_secret
	 *
	 * @return mixed
	 */
	private $redirect_uri;
	private $encoded_url;

	public function request( $api_key, $api_secret ) {

		$this->api_key = $api_key;

		$this->api_secret = $api_secret;

		$this->encoded_url = isset( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : '';

		if ( isset( $this->encoded_url ) && $this->encoded_url != '' ) {
			$this->redirect_uri = $this->call_back_url() . 'user_registration_social_login' . '=google&redirect_to=' . $this->encoded_url;
		} else {
			$this->redirect_uri = $this->call_back_url() . 'user_registration_social_login' . '=google';
		}

		// TODO: Implement init() method.
		if ( ! class_exists( 'Google_Client' ) ) {
			require_once URSC_ABSPATH . 'vendor/autoload.php';
		}

		$response = $this->get_social_network_data();

		$response['network'] = 'google';

		$this->set_response( $response );
	}

	/**
	 * @return mixed
	 */
	public function get_social_network_data() {

		$action = isset( $_GET['ursc_action'] ) ? $_GET['ursc_action'] : '';

		$google_access_token = user_registration_social_connect_get_session( 'google_access_token' );

		try {

			if ( empty( $this->api_key ) || empty( $this->api_secret ) ) {

				throw  new Exception( __( 'Empty some credintial of google app.', 'user-registration-social-connect' ) );
			}
			if ( $action == 'login' ) {

				$this->network_login();

			} elseif ( isset( $_GET['code'] ) ) { // Perform HTTP Request to OpenID server to validate key

				$this->set_access_token();

			} elseif ( $google_access_token && ! empty( $google_access_token ) ) {
				$this->set_network_response();

			} else { // User Canceled your Request

				throw  new Exception( __( 'Google connection failed. Please contact website admin.', 'user-registration-social-connect' ) );

			}
		} catch ( Exception $e ) {

			$this->response['status'] = 'ERROR';

			$this->response['message'] = $e->getMessage();

		}

		return $this->response;

	}

	public function network_login() {

		$network_object = $this->get_network_object();

		user_registration_social_connect_unset_session( 'google_access_token' );

		$auth_url = $network_object->createAuthUrl();

		ursc_custom_redirect( $auth_url );
		die();
	}

	private function get_network_object() {

		$network_object = new Google\Client();
		$network_object->setClientId( $this->api_key );
		$network_object->setClientSecret( $this->api_secret );
		$network_object->setRedirectUri( $this->redirect_uri );
		$network_object->addScope( 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email' );
		if ( isset( $this->encoded_url ) && $this->encoded_url != '' ) {
			$network_object->setState( base64_encode( "redirect_to=$this->encoded_url" ) );
		}

		return $network_object;
	}

	public function set_access_token() {

		$network_object = $this->get_network_object();

		$network_object->fetchAccessTokenWithAuthCode( $_GET['code'] );

		user_registration_social_connect_set_session( 'google_access_token', $network_object->getAccessToken() );

		ursc_custom_redirect( $this->redirect_uri );

		die();

	}

	/**
	 * @return mixed
	 */
	public function set_network_response() {

		try {

			$google_access_token = user_registration_social_connect_get_session( 'google_access_token' );

			if ( false === $google_access_token || empty( $google_access_token ) ) {

				throw  new Exception( __( 'Token not found.', 'user-registration-social-connect' ) );
			}

			$network_object = $this->get_network_object();

			$network_object->setAccessToken( $google_access_token );
			$google_service = new Google_Service_Oauth2( $network_object );

			$user_profile = $google_service->userinfo->get();

			if ( empty( $user_profile ) ) {
				throw  new Exception( __( 'INVALID AUTHORIZATION', 'user-registration-social-connect' ) );
			}

			/* If HTTP response is 200 continue otherwise send to connect page to retry */
			if ( ! empty( $user_profile->email ) ) {

				$this->response['status']  = 'SUCCESS';
				$this->response['message'] = 'Succesfully get data';
				$profile                   = isset( $user_profile->link ) ? $user_profile->link : '';
				$email                     = isset( $user_profile->email ) ? $user_profile->email : '';
				$username                  = ursc_get_username( strtolower( $user_profile->givenName . '_' . $user_profile->familyName ), $email );
				$this->response['data']    = array(
					'email'       => $email,
					'username'    => $username,
					'profile'     => $profile,
					'id'          => isset( $user_profile->id ) ? $user_profile->id : '',
					'profile_pic' => isset( $user_profile->picture ) ? $user_profile->picture : '',
					'first_name'  => isset( $user_profile->givenName ) ? $user_profile->givenName : '',
					'last_name'   => isset( $user_profile->familyName ) ? $user_profile->familyName : '',
				);
			} else {
				$this->response['status']  = 'ERROR';
				$this->response['message'] = __( 'Could not connect to google, please contact site administrator.', 'user-registration-social-connect' );
			}
		} catch ( Exception $e ) {
			$this->response['status']  = 'ERROR';
			$this->response['message'] = $e->getMessage();

		}
	}
}
