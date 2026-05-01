<?php
/**
 * UserRegistrationSocialConnect Frontend.
 *
 * @class    URSC_Network_Linkedin
 * @version  1.0.0
 * @package  UserRegistrationSocialConnect/Networks
 * @category Networks
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URSC_Network_Linkedin Class
 */
class URSC_Network_Linkedin extends URSC_Social_Networks {

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
			_e( 'The linkedin SDK requires PHP version 5.4 or higher. Please notify about this error to site admin.', 'user-registration-social-connect' );
			die();
		}
		$this->api_key = $api_key;

		$this->api_secret = $api_secret;

		$this->encoded_url = isset( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : '';

		if ( isset( $this->encoded_url ) && ! empty( $this->encoded_url ) ) {

			$this->redirect_uri = $this->call_back_url() . 'user_registration_social_login' . '=linkedin&redirect_uri=' . $this->encoded_url;
		} else {
			$this->redirect_uri = $this->call_back_url() . 'user_registration_social_login' . '=linkedin';
		}

		$response = $this->get_social_network_data();

		$response['network'] = 'linkedin';

		$this->set_response( $response );

	}

	/**
	 * @return mixed
	 */
	public function get_social_network_data() {

		$action = isset( $_GET['ursc_action'] ) ? $_GET['ursc_action'] : '';

		$linkedin_access_token = user_registration_social_connect_get_session( 'linkedin_access_token' );

		try {
			if ( empty( $this->api_key ) || empty( $this->api_secret ) ) {

				throw  new Exception( __( 'Empty some credintial of linkedin app.', 'user-registration-social-connect' ) );
			}

			if ( $action == 'login' ) {

				$this->network_login();

			} elseif ( isset( $_GET['code'] ) ) { // Perform HTTP Request to OpenID server to validate key

				$this->set_access_token();

			} elseif ( $linkedin_access_token && ! empty( $linkedin_access_token ) ) {

				$this->set_network_response();

			} else { // User Canceled your Request

				throw  new Exception( __( 'Linkedin connection failed. Please contact website admin.', 'user-registration-social-connect' ) );

			}
		} catch ( Exception $e ) {

			$this->response['status'] = 'ERROR';

			$this->response['message'] = $e->getMessage();
		}

		return $this->response;

	}

	public function network_login() {

		$state = md5( time() );

		user_registration_social_connect_set_session( 'linkedin_state', $state );

		$login_url = "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id={$this->api_key}&redirect_uri={$this->redirect_uri}&state={$state}&scope=r_liteprofile r_emailaddress";

		ursc_custom_redirect( $login_url );

		die();
	}

	/**
	 *
	 */
	public function set_access_token() {

		try {

			$url = 'https://www.linkedin.com/oauth/v2/accessToken';

			$params = array(
				'method'   => 'POST',
				'blocking' => true,
				'body'     => array(
					'grant_type'    => 'authorization_code',
					'code'          => $_GET['code'],
					'redirect_uri'  => $this->redirect_uri,
					'client_id'     => $this->api_key,
					'client_secret' => $this->api_secret,

				),

				'headers'  => array( 'Content-type' => 'application/x-www-form-urlencoded' ),

			);

			$linkedin_response = ( wp_remote_post( $url, $params ) ); // Request for access token

			$access_token = '';

			if ( isset( $linkedin_response['body'] ) ) {

				$linkedin_response_decode = json_decode( $linkedin_response['body'], true );

				if ( isset( $linkedin_response_decode['access_token'] ) ) {

					$access_token = $linkedin_response_decode['access_token'];

				}
			}

			user_registration_social_connect_set_session( 'linkedin_access_token', $access_token );
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

			$linkedin_access_token = user_registration_social_connect_get_session( 'linkedin_access_token' );

			if ( false === $linkedin_access_token || empty( $linkedin_access_token ) ) {

				throw  new Exception( __( 'Token not found.', 'user-registration-social-connect' ) );
			}

			$profile_url = 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))';
			$email_url   = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';

			$params = array(
				'method'   => 'GET',
				'blocking' => true,
				'body'     => array(),

				'headers'  => array(
					'cache-control'             => 'no-cache',
					'Authorization'             => 'Bearer ' . $linkedin_access_token,
					'X-Restli-Protocol-Version' => '2.0.0',
				),

			);

			$linkedin_response       = ( wp_remote_get( $profile_url, $params ) ); // Request for access token
			$linkedin_email_response = ( wp_remote_get( $email_url, $params ) ); // Request for access token

			$user_profile_body = array();
			$user_email_body   = array();

			if ( isset( $linkedin_response['body'] ) && isset( $linkedin_email_response['body'] ) ) {

				$user_profile_body = json_decode( $linkedin_response['body'] );
				$user_email_body   = json_decode( $linkedin_email_response['body'] );

				$user_email_body->elements[0] = (array) $user_email_body->elements[0];

			}

			if ( empty( $user_profile_body ) || empty( $user_email_body ) ) {

				throw  new Exception( __( 'INVALID AUTHORIZATION', 'user-registration-social-connect' ) );
			}

			if ( isset( $user_profile_body->id ) && ! empty( $user_profile_body->id ) ) {

				$this->response['status']  = 'SUCCESS';
				$this->response['message'] = 'Succesfully get data';
				$profile                   = isset( $user_profile_body->publicProfileUrl ) ? $user_profile_body->publicProfileUrl : '';
				$email                     = isset( $user_email_body->elements[0]['handle~']->emailAddress ) ? $user_email_body->elements[0]['handle~']->emailAddress : '';
				$this->response['data']    = array(
					'email'       => $email,
					'username'    => ursc_get_username( explode( '@', $email )[0], $email ),
					'profile'     => $profile,
					'id'          => $user_profile_body->id,
					'profile_pic' => isset( $user_profile_body->profilePicture->{'displayImage~'}->elements[0]->identifiers[0]->identifier ) ? $user_profile_body->profilePicture->{'displayImage~'}->elements[0]->identifiers[0]->identifier : "",
					'first_name'  => isset( $user_profile_body->firstName->localized->en_US ) ? $user_profile_body->firstName->localized->en_US : '',
					'last_name'   => isset( $user_profile_body->lastName->localized->en_US ) ? $user_profile_body->lastName->localized->en_US : '',
				);

			} else {

				$this->response['status']  = 'ERROR';
				$this->response['message'] = __( 'Could not connect to linkedin, please contact site administrator.', 'user-registration-social-connect' );

			}
		} catch ( Exception $e ) {
			$this->response['status']  = 'ERROR';
			$this->response['message'] = $e->getMessage();

		}

	}

}
