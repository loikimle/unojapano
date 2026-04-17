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
 * URSC_Frontend Class
 */
class URSC_Frontend {

	/**
	 * Hook in tabs.
	 */
	private $wp_error;

	public function __construct() {

		global $ursc_response_global;
		add_action( 'init', array( $this, 'social_login_check' ) ); // check for the social logins
		add_action( 'login_form', array( $this, 'add_ur_social_login' ) );

		$social_position_option = get_option( 'user_registration_social_login_position', 'bottom' );
		if ( 'top' === $social_position_option ) {
			add_action( 'user_registration_login_form_start', array( $this, 'add_ur_social_login' ) ); // check for the social logins
		} else {
			add_action( 'user_registration_login_form_end', array( $this, 'add_ur_social_login' ) ); // check for the social logins
		}
		add_action( 'user_registration_login_form_end', array( $this, 'user_registration_social_scripts' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'user_registration_social_scripts' ) );
		add_filter( 'login_message', array( $this, 'login_message' ) );
		add_filter( 'user_registration_login_form_before_notice', array( $this, 'ur_login_message' ) );
		add_action( 'user_registration_save_account_details', array( $this, 'update_bypass_current_password_meta' ), 10, 1 );

		if ( 'yes' === get_option( 'user_registration_social_setting_display_social_buttons_in_registration', 'no' ) ) {
			add_action( 'user_registration_form_registration_end', array( $this, 'add_ur_social_login' ) ); // check for the social logins
			add_action( 'user_registration_form_registration_end', array( $this, 'user_registration_social_scripts' ) );
		}
	}

	/**
	 * @param $message
	 */
	public function login_message() {
		if ( is_wp_error( $this->wp_error ) ) {
			echo '<div id="login_error">' . $this->wp_error->get_error_message() . '</div>';
		}
	}

	public function ur_login_message() {
		if ( is_wp_error( $this->wp_error ) ) {
			ur_print_notice( $this->wp_error->get_error_message(), 'error' );
		}
	}

	public function user_registration_social_scripts() {
		wp_register_style( 'user-registration-social-connect-style', URSC()->plugin_url() . '/assets/css/user-registration-social-connect-style.css', array(), URSC_VERSION );
		wp_enqueue_style( 'user-registration-social-connect-style' );
	}

	public function add_ur_social_login($form_id) {
		include 'views/social-login-template.php';
	}

	public function social_login_check() {
		ob_start();
		if ( isset( $_GET['user_registration_social_login'] ) ) {
			if ( isset( $_REQUEST['state'] ) ) {
				parse_str( base64_decode( $_REQUEST['state'] ), $state_vars );

				if ( isset( $state_vars['redirect_to'] ) ) {
					$_GET['redirect_to'] = $_REQUEST['redirect_to'] = $state_vars['redirect_to'];
				}
			}

			$social_network      = $_GET['user_registration_social_login'];
			$all_social_networks = user_registration_social_networks();

			if ( isset( $all_social_networks[ $social_network ] ) ) {
				$api_key    = get_option( $all_social_networks[ $social_network ]['key_id'] );
				$api_secret = get_option( $all_social_networks[ $social_network ]['secret_id'] );
				switch ( $social_network ) {
					case 'twitter':
						$twitter_network = new URSC_Network_Twitter();
						$twitter_network->request( $api_key, $api_secret );
						break;
					case 'google':
						$google_network = new URSC_Network_Google();
						$google_network->request( $api_key, $api_secret );
						break;
					case 'facebook':
						$facebook_network = new URSC_Network_Facebook();
						$facebook_network->request( $api_key, $api_secret );
						break;
					case 'linkedin':
						$linkedin_network = new URSC_Network_Linkedin();
						$linkedin_network->request( $api_key, $api_secret );
						break;

				}
			}
		}

		if ( ! is_user_logged_in() ) {
			$this->check_if_already_connected();
		} else {
			$user_id = get_current_user_id();
			if ( $user_id ) {
				$bypass = get_user_meta( $user_id, 'user_registration_social_connect_bypass_current_password', true );
				if ( $bypass ) {
					add_filter( 'user_registration_save_account_bypass_current_password', array( $this, 'bypass_current_password' ) );
					add_filter( 'user_registration_change_password_current_password_display', array( $this, 'bypass_current_password_display' ) );
				}
			}
		}
		ob_end_flush();
	}

	/**
	 * Checks if the email address is already connected.
	 *
	 * @return void
	 */
	public function check_if_already_connected() {

		global $ursc_response_global;

		if ( isset( $ursc_response_global['status'] ) && 'SUCCESS' === $ursc_response_global['status'] ) {

			if ( isset( $ursc_response_global['data'] ) && isset( $ursc_response_global['data']['email'] ) ) {

				$global_data = isset( $ursc_response_global['data'] ) ? $ursc_response_global['data'] : array();

				$network_data         = array(
					'email'       => isset( $global_data['email'] ) ? $global_data['email'] : '',
					'username'    => isset( $global_data['username'] ) ? $global_data['username'] : '',
					'profile'     => isset( $global_data['profile'] ) ? $global_data['profile'] : '',
					'profile_pic' => isset( $global_data['profile_pic'] ) ? $global_data['profile_pic'] : '',
					'first_name'  => isset( $global_data['first_name'] ) ? $global_data['first_name'] : '',
					'last_name'   => isset( $global_data['last_name'] ) ? $global_data['last_name'] : '',
					'network'     => isset( $ursc_response_global['network'] ) ? $ursc_response_global['network'] : '',
					'has_email'   => ! empty( $global_data['email'] ) ? true : false,
				);
				$is_already_connected = URSC_Social_Data::is_already_connected_network( $network_data['username'], $network_data['network'], $network_data['email'] );

				if ( $is_already_connected ) {
					$status = URSC_Social_Data::check_user_and_login( $network_data );
					if ( is_wp_error( $status ) ) {
						$this->wp_error = $status;
					}
				} elseif ( URSC_Social_Data::is_already_connected( $network_data['username'], $network_data['email'] ) ) {
					$message        = __( 'User already registered through other medium.', 'user-registration-social-connect' );
					$this->wp_error = new WP_Error( 'user_registration_user_already_created', $message );
				} else {
					try {
						if ( ( ( $network_data['network'] !== 'google' && $network_data['network'] !== 'linkedin' ) && empty( $network_data['profile'] ) ) || empty( $network_data['username'] ) ) {
							throw  new Exception( __( 'Network user profile not found for this email address.', 'user-registration-social-connect' ) );
						}
						$password = wp_generate_password( 15, true, true );
						$user_id  = URSC_Social_Data::ursc_register_user( $network_data, $password );

						if ( ! is_numeric( $user_id ) ) {
							ursc_flush_all();
							throw  new Exception( $user_id );
						}
						$status = URSC_Social_Data::login_user( $user_id );

						if ( is_wp_error( $status ) ) {
							throw  new Exception( $status->get_error_message() );
						}

						if ( wp_safe_redirect( ursc_social_login_redirect() ) ) {
							exit;
						}
					} catch ( Exception $e ) {
						ursc_flush_all();
						$this->wp_error = new WP_Error( 'user_registration_social_connect_registration_error', $e->getMessage() );
					}
				}
			}
		}
	}

	/**
	 * Update Bypass current password user meta.
	 *
	 * @param int $user_id
	 */
	public function update_bypass_current_password_meta( $user_id ) {
		if ( get_user_meta( $user_id, 'user_registration_social_connect_bypass_current_password', true ) ) {
			update_user_meta( $user_id, 'user_registration_social_connect_bypass_current_password', false );
		}
	}

	/**
	 * Whether to display current password or not.
	 *
	 * @param bool $bypass
	 */
	public function bypass_current_password_display() {
		return false;
	}

	/**
	 * Bypass current password validation.
	 *
	 * @param bool $bypass
	 */
	public function bypass_current_password() {
		return true;
	}
}

return new URSC_Frontend();
