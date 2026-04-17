<?php
/**
 * UserRegistrationSocialConnect Frontend.
 *
 * @class    URSC_Network_Facebook
 * @version  1.0.0
 * @package  UserRegistrationSocialConnect/Networks
 * @category Networks
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URSC_Network_Facebook Class
 */
class URSC_Network_Facebook extends URSC_Social_Networks {

	/**
	 * @param $api_key
	 * @param $api_secret
	 *
	 * @return mixed
	 */
	private $redirect_uri;
	private $encoded_url;

	/**
	 * @param $api_key
	 * @param $api_secret
	 */
	public function request( $api_key, $api_secret ) {

		if ( version_compare( PHP_VERSION, '5.4.0', '<' ) ) {
			_e( 'The Facebook SDK requires PHP version 5.4 or higher. Please notify about this error to site admin.', 'user-registration-social-connect' );
			die();
		}
		$this->api_key = $api_key;

		$this->api_secret = $api_secret;

		$this->encoded_url = isset( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : '';

		if ( isset( $this->encoded_url ) && ! empty( $this->encoded_url ) ) {

			$this->redirect_uri = $this->call_back_url() . 'user_registration_social_login' . '=facebook&redirect_to=' . $this->encoded_url;
		} else {
			$this->redirect_uri = $this->call_back_url() . 'user_registration_social_login' . '=facebook';
		}

		user_registration_session_start();

		if ( ! class_exists( 'Facebook\\Facebook' ) ) {

			include URSC_NETWORK_PATH . 'facebook/Facebook/autoload.php';

		}

		$response = $this->get_social_network_data();

		$response['network'] = 'facebook';

		$this->set_response( $response );

	}

	/**
	 * @return mixed
	 */
	public function get_social_network_data() {

		$action = isset( $_GET['ursc_action'] ) ? $_GET['ursc_action'] : '';

		$facebook_access_token = user_registration_social_connect_get_session( 'facebook_access_token' );

		try {

			if ( empty( $this->api_key ) || empty( $this->api_secret ) ) {

				throw  new Exception( __( 'Empty some credintial of facebook app.', 'user-registration-social-connect' ) );
			}

			if ( $action == 'login' ) {

				$this->network_login();

			} elseif ( isset( $_GET['code'] ) ) { // Perform HTTP Request to OpenID server to validate key

				$this->set_access_token();

			} elseif ( $facebook_access_token && ! empty( $facebook_access_token ) ) {

				$this->set_network_response();

			} else { // User Canceled your Request

				throw  new Exception( __( 'Facebook connection failed. Please contact website admin.', 'user-registration-social-connect' ) );
			}
		} catch ( Exception $e ) {

			$this->response['status'] = 'ERROR';

			$this->response['message'] = $e->getMessage();

		}

		return $this->response;

	}

	public function network_login() {

		$fb = $this->get_network_object();

		$helper = $fb->getRedirectLoginHelper();

		$permissions = array( 'email', 'public_profile' ); // optional

		$login_url = $helper->getLoginUrl( $this->redirect_uri, $permissions );

		ursc_custom_redirect( $login_url );

		die();
	}

	/**
	 * @return \Facebook\Facebook
	 */
	private function get_network_object() {

		$config = array(
			'app_id'                  => $this->api_key,
			'app_secret'              => $this->api_secret,
			'default_graph_version'   => 'v2.10',
			'persistent_data_handler' => 'session',
		);

		$network_object = new Facebook\Facebook( $config );

		return $network_object;
	}

	/**
	 * Set access token in session.
	 */
	public function set_access_token() {

		try {
			$fb = $this->get_network_object();

			$helper = $fb->getRedirectLoginHelper();

			if ( isset( $_GET['state'] ) ) {

				$helper->getPersistentDataHandler()->set( 'state', $_GET['state'] );
			}

			user_registration_social_connect_set_session( 'facebook_state', $_GET['state'] );

			$accessToken = $helper->getAccessToken();

			user_registration_social_connect_set_session( 'facebook_access_token', $accessToken->getValue() );
		} catch ( Exception $e ) {

		}

		ursc_custom_redirect( $this->redirect_uri );

		die();
	}

	/**
	 * @return mixed
	 */
	public function set_network_response() {

		try {

			$facebook_access_token = user_registration_social_connect_get_session( 'facebook_access_token' );

			if ( false === $facebook_access_token || empty( $facebook_access_token ) ) {

				throw  new Exception( __( 'Token not found.', 'user-registration-social-connect' ) );
			}

			$fb = $this->get_network_object();

			$user_profile = $fb->get( '/me?fields=email,name, first_name, last_name, picture.type(large), gender, link, about, birthday, education, hometown, languages, location, website', $facebook_access_token );

			$user_profile_body = (object) $user_profile->getDecodedBody();

			if ( empty( $user_profile_body ) ) {

				throw  new Exception( __( 'INVALID AUTHORIZATION', 'user-registration-social-connect' ) );
			}

			/* If HTTP response is 200 continue otherwise send to connect page to retry */
			if ( ! empty( $user_profile_body->id ) ) {

				$this->response['status']  = 'SUCCESS';
				$this->response['message'] = 'Succesfully get data';
				$profile                   = 'https://facebook.com/' . $user_profile_body->id;
				$email                     = isset( $user_profile_body->email ) ? $user_profile_body->email : '';

				$this->response['data'] = array(
					'email'       => $email,
					'username'    => ursc_get_username( strtolower( trim( $user_profile_body->first_name ) . trim( $user_profile_body->last_name ) ), $email ),
					'profile'     => $profile,
					'id'          => $user_profile_body->id,
					'profile_pic' => $user_profile_body->picture['data']['url'],
					'first_name'  => $user_profile_body->first_name,
					'last_name'   => $user_profile_body->last_name,
				);

			} else {

				$this->response['status']  = 'ERROR';
				$this->response['message'] = __( 'Could not connect to facebook, please contact site administrator.', 'user-registration-social-connect' );

			}
		} catch ( Exception $e ) {
			$this->response['status']  = 'ERROR';
			$this->response['message'] = $e->getMessage();

		}
	}
}
