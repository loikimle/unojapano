<?php
/**
 * UserRegistrationSocialConnect Frontend.
 *
 * @class    URSC_Twitter_Login_Checker
 * @version  1.0.0
 * @package  UserRegistrationSocialConnect/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URSC_Twitter_Login_Checker Class
 */
class URSC_Network_Twitter extends URSC_Social_Networks {

	/**
	 * @param $api_key
	 * @param $api_secret
	 *
	 * @return mixed
	 */
	public function request( $api_key, $api_secret ) {

		$this->api_key = $api_key;

		$this->api_secret = $api_secret;

		// TODO: Implement init() method.
		if ( ! class_exists( 'TwitterOAuth' ) ) {

			include URSC_NETWORK_PATH . 'twitter/OAuth.php';

			include URSC_NETWORK_PATH . 'twitter/twitteroauth.php';

		}

		$response = $this->get_social_network_data();

		$response['network'] = 'twitter';

		$this->set_response( $response );
	}

	/**
	 * @return mixed
	 */
	public function get_social_network_data() {

		$request = $_REQUEST;

		$action = isset( $_GET['ursc_action'] ) ? $_GET['ursc_action'] : '';

		try {
			if ( empty( $this->api_key ) || empty( $this->api_secret ) ) {

				throw  new Exception( __( 'Empty some credintial of twitter app.', 'user-registration-social-connect' ) );
			}

			if ( $action == 'login' ) {

				$this->set_access_token();

			} elseif ( isset( $request['oauth_token'] ) && isset( $request['oauth_verifier'] ) ) {

				$this->set_network_response();

			} else { // User Canceled your Request

				throw  new Exception( __( 'Twitter connection failed. Please contact website admin.', 'user-registration-social-connect' ) );

			}
		} catch ( Exception $e ) {

			$this->response['status'] = 'ERROR';

			$this->response['message'] = $e->getMessage();
		}

		return $this->response;
	}

	/**
	 *
	 */
	public function set_access_token() {

		$network_object = new URSC_TwitterOAuth( $this->api_key, $this->api_secret );
		$encoded_url    = isset( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : '';

		if ( isset( $encoded_url ) && $encoded_url != '' ) {
			$callback = $this->call_back_url() . 'user_registration_social_login' . '=twitter&redirect_to=' . $encoded_url;
		} else {
			$callback = $this->call_back_url() . 'user_registration_social_login' . '=twitter';
		}

		$request_token = $network_object->getRequestToken( $callback );

        /* Save temporary credentials to session. */
		$token              = isset( $request_token['oauth_token'] ) ? $request_token['oauth_token'] : '';
		$oauth_token_secret = isset( $request_token['oauth_token_secret'] ) ? $request_token['oauth_token_secret'] : '';

		user_registration_social_connect_set_session( 'oauth_twitter_token', $token );
		user_registration_social_connect_set_session( 'oauth_twitter_token_secret', $oauth_token_secret );
		/* If last connection failed don't display authorization link. */

		switch ( $network_object->http_code ) {
			case 200:
				try {
					$url = $network_object->getAuthorizeUrl( $token );
					ursc_custom_redirect( $url );
				} catch ( Exception $e ) {
					$this->response['status']  = 'ERROR';
					$this->response['message'] = __( 'Could not get AuthorizeUrl', 'user-registration-social-connect' );
				}
				break;
			default:
				$this->response['status']  = 'ERROR';
				$this->response['message'] = __( 'Could not connect to Twitter. Refresh the page or try again later.', 'user-registration-social-connect' );
				break;
		}

	}

	/**
	 * @return mixed
	 */
	public function set_network_response() {

		$request = $_REQUEST;

		$oauth_twitter_token = user_registration_social_connect_get_session( 'oauth_twitter_token' );

		$oauth_twitter_token_secret = user_registration_social_connect_get_session( 'oauth_twitter_token_secret' );

		/* Remove no longer needed request tokens */

		user_registration_social_connect_unset_session( 'oauth_twitter_token' );
		user_registration_social_connect_unset_session( 'oauth_twitter_token_secret' );

		try {

			if ( false === $oauth_twitter_token || false === $oauth_twitter_token_secret ) {

				throw  new Exception( __( 'Token not found.', 'user-registration-social-connect' ) );
			}
			$network_object = new URSC_TwitterOAuth( $this->api_key, $this->api_secret, $oauth_twitter_token, $oauth_twitter_token_secret );
			$access_token   = $network_object->getAccessToken( $request['oauth_verifier'] );
			/* If HTTP response is 200 continue otherwise send to connect page to retry */
			if ( 200 == $network_object->http_code ) {
				$user_profile = $network_object->get(
					'account/verify_credentials',
					array(
						'screen_name'      => $access_token['screen_name'],
						'skip_status'      => 'true',
						'include_entities' => 'true',
						'include_email'    => 'true',
					)
				);

				$this->response['status']     = 'SUCCESS';
				$this->response['message']    = 'Succesfully get at';
				$username                     = isset( $user_profile->screen_name ) ? $user_profile->screen_name : '';
				$email                        = isset( $user_profile->email ) ? $user_profile->email : '';
				$profile_pic                  = isset( $user_profile->profile_image_url_https ) ? $user_profile->profile_image_url_https . "/" : '';
				$name                         = isset( $user_profile->name ) ? $user_profile->name : '';
				list( $firstname, $lastname ) = explode( " ", $name );

				$this->response['data'] = array(
					'email'       => $email,
					'username'    => ursc_get_username( $username, $email ),
					'profile'     => home_url() . $username,
					'id'          => isset( $user_profile->id ) ? $user_profile->id : '',
					'profile_pic' => $profile_pic,
					'first_name'  => $firstname,
					'last_name'   => $lastname,
				);

			} else {

				$this->response['status']  = 'ERROR';
				$this->response['message'] = __( 'Could not connect to twitter, please contact site administrator.', 'user-registration-social-connect' );

			}
		} catch ( Exception $e ) {
			$this->response['status']  = 'ERROR';
			$this->response['message'] = $e->getMessage();

		}
	}

}
