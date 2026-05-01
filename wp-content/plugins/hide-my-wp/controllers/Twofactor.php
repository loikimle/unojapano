<?php
/**
 * @package HMWPP/Twofactor
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die( 'Cheating uh?' );

class HMWP_Controllers_Twofactor extends HMWP_Classes_FrontController {

	/** @var WP_user Current logged in user */
	public $user;

	/** @var array Two-Factor Options */
	public $options = array();

	/** @var array Backup Codes */
	public $codes = array();

	public $downloadLinks;

	/**
	 * Constructor. Registers WP hooks for the two-factor authentication flow.
	 *
	 * Hooks into the authentication pipeline to collect credentials, intercept
	 * the login process, and validate the second factor. Skips 2FA hook
	 * registration when the safe URL is detected.
	 *
	 * @throws Exception
	 */
	public function __construct() {
		parent::__construct();

		//save the last login user
		add_filter( 'authenticate', array( $this->model, 'collectAuthLogin' ), PHP_INT_MAX, 1 );

		// If the safe URL was called, don't show the 2FA form
		if ( HMWP_Classes_Tools::calledSafeUrl() ) {
			return;
		}

		//Add login & validation hooks
		add_action( 'wp_login', array( $this, 'hookLogin' ), 10, 2 );
		add_action( 'set_auth_cookie', array( $this->model, 'collectAuthCookieTokens' ) );
		add_action( 'set_logged_in_cookie', array( $this->model, 'collectAuthCookieTokens' ) );
		add_action( 'init', array( $this->model, 'validateTwoFactor' ) );

		// AJAX endpoints for challenge (login) and (optionally) registration
		add_action( 'wp_ajax_hmwp_passkey_begin', array( $this->model, 'validatePasskeyLogin' ) );
		add_action( 'wp_ajax_nopriv_hmwp_passkey_begin', array( $this->model, 'validatePasskeyLogin' ) );

		//user list
		add_filter( 'manage_users_columns', array( $this->model, 'manageUsersColumnHeader' ) );
		add_filter( 'wpmu_users_columns', array( $this->model, 'manageUsersColumnHeader' ) );
		add_filter( 'manage_users_custom_column', array( $this->model, 'manageUsersColumn' ), 10, 3 );
		add_filter( 'users_list_table_query_args', array( $this->model, 'manageUsersColumnQuery' ) );
		add_filter( 'manage_users_sortable_columns', array( $this->model, 'manageUsersColumnSort' ) );

		if ( HMWP_Classes_Tools::isMultisites() ) {
			add_filter( 'manage_users-network_sortable_columns', array( $this->model, 'manageUsersColumnSort' ) );
		}

		//admin dashboard hooks
		add_action( 'admin_notices', array( $this->model, 'adminNotices' ) );

	}

	/**
	 * Load 2FA settings in the user profile
	 *
	 * @param $user
	 *
	 * @return void
	 * @throws Exception
	 */
	public function hookUserSettings( $user ) {

		HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'twofactor' );
		HMWP_Classes_ObjController::getClass( 'HMWP_Classes_DisplayController' )->loadMedia( 'qrcode' );

		//add 2FA with Code Scan in user settings View
		add_action( 'hmwp_two_factor_user_options', function () use ( $user ) {

			if ( ! $this->model->isActiveService( $user, 'hmwp_2fa_totp' )  ) {
				return;
			}

			/** @var HMWP_Models_Twofactor_Tftotp $twoFactorService */
			$twoFactorService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Tftotp' );

			$this->options = $twoFactorService->getTwoFactorOption( $user );

			//Show the two factor block
			$this->show( 'blocks/Totp' );
		} );

		//add 2FA with Email Code in user settings View
		add_action( 'hmwp_two_factor_user_options', function () use ( $user ) {

			if ( ! $this->model->isActiveService( $user, 'hmwp_2fa_email' )  ) {
					return;
			}

			/** @var HMWP_Models_Twofactor_Email $emailService */
			$emailService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Email' );

			$this->options = $emailService->getEmailOption( $user );

			//Show the two factor block
			$this->show( 'blocks/Email' );
		} );

		//add 2FA with Email Code in user settings View
		add_action( 'hmwp_two_factor_user_options', function () use ( $user ) {

			if ( ! $this->model->isActiveService( $user, 'hmwp_2fa_passkey' )  ) {
					return;
			}

			/** @var HMWP_Models_Twofactor_Passkey $passkeyService */
			$passkeyService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Passkey' );

			$this->options = $passkeyService->getPasskeyOption( $user );

			//Show the two factor block
			$this->show( 'blocks/Passkey' );
		} );

		$this->user = $user;

		//Show 2FA in user settings profile
		$this->show( 'TwofactorUser' );

		do_action( 'hmwp_user_security_settings_after', $user );
	}

	/**
	 * Handle the browser-based login.
	 *
	 * @param string $user_login Username.
	 * @param WP_User $user The WP_User instance representing the currently logged-in user.
	 *
	 * @throws Exception
	 */
	public function hookLogin( $user_login, $user ) {

		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) { // phpcs:ignore WordPress.Security, check isset only
			$ip = $_SERVER['REMOTE_ADDR']; // phpcs:ignore WordPress.Security, filtered in isWhitelistedIP function

			if ( HMWP_Classes_ObjController::getClass( 'HMWP_Models_Firewall_Rules' )->isWhitelistedIP( $ip ) ) {
				return;
			}

			if ( $this->model->isRememberDevice( $user->ID ) ) {
				return;
			}

		}

		if ( ! $user ) {
			$user = wp_get_current_user();
		}
		/** @var HMWP_Models_Twofactor_Passkey $passkeyService */
		$passkeyService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Passkey' );

		/** @var HMWP_Models_Twofactor_Tftotp $twoFactorService */
		$twoFactorService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Tftotp' );

		/** @var HMWP_Models_Twofactor_Email $emailService */
		$emailService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Email' );

		// If none of the services are active
		if ( ! $passkeyService->isServiceActive( $user ) && ! $twoFactorService->isServiceActive( $user ) && ! $emailService->isServiceActive( $user ) ) {
			return;
		}

		// Invalidate the current login session to prevent from being re-used.
		$this->model->destroyCurrentSession( $user );

		$this->model->showTwoFactorLogin( $user );
		exit();
	}

	/**
	 * Get all 2FA logs
	 *
	 * @return array The array of 2FA logs
	 * @throws Exception
	 */
	public function getLogs() {

		$logs = array();

		if ( apply_filters( 'hmwp_showlogins', true ) ) {

			/** @var HMWP_Models_Twofactor $twoFactorModel */
			$twoFactorModel = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor' );

			/** @var HMWP_Models_Twofactor_Tftotp $twoFactorService */
			$twoFactorService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Tftotp' );

			/** @var HMWP_Models_Twofactor_Email $emailService */
			$emailService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Email' );

			/** @var HMWP_Models_Twofactor_Passkey $passkeyService */
			$passkeyService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Passkey' );

			/** @var WP_User[] $users */
			$users = get_users();

			foreach ( $users as $user ) {

				if ( $last_totp_login = $twoFactorService->getLastLoginTimestamp( $user->ID ) ) {
					$logs[] = array(
						'user'       => $user,
						'email'      => $user->user_email,
						'last_login' => $twoFactorModel->timeElapsed( $last_totp_login ),
						'success'    => true,
						'mode'       => esc_html__( '2FA Code', 'hide-my-wp' ),
					);
				}


				if ( $last_totp_login = $emailService->getLastLoginTimestamp( $user->ID ) ) {
					$logs[] = array(
						'user'       => $user,
						'email'      => $user->user_email,
						'last_login' => $twoFactorModel->timeElapsed( $last_totp_login ),
						'success'    => true,
						'mode'       => esc_html__( "Email Code", 'hide-my-wp' ),
					);
				}

				if ( $last_failed = $twoFactorModel->getLastUserLoginFail( $user ) ) {
					$logs[] = array(
						'user'       => $user,
						'email'      => $user->user_email,
						'last_login' => wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_failed ),
						'success'    => false,
					);
				}

				if ( $last_passkey_login = $passkeyService->getLastLoginTimestamp( $user->ID ) ) {
					$logs[] = array(
						'user'       => $user,
						'email'      => $user->user_email,
						'last_login' => $twoFactorModel->timeElapsed( $last_passkey_login ),
						'success'    => true,
						'mode'       => esc_html__( 'Passkey', 'hide-my-wp' ),
					);
				}

			}

			return $logs;

		}

		return $logs;

	}

	/**
	 * Login form validation.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function action() {
		parent::action();

		//if the current user can't manage the personal profile
		if ( ! HMWP_Classes_Tools::userCan( 'read' ) ) {
			return;
		}

		switch ( HMWP_Classes_Tools::getValue( 'action' ) ) {
			case 'hmwp_2fasettings':

				// Check if the current user has the 'hmwp_manage_settings' capability
				if ( ! HMWP_Classes_Tools::userCan( HMWP_CAPABILITY ) ) {
					return;
				}

				if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) ) { // phpcs:ignore WordPress.Security
					$this->saveValues( $_POST ); // phpcs:ignore WordPress.Security, nonce is checked in function
				}

				// Save the text every time to prevent from removing the white space from the text
				HMWP_Classes_Tools::saveOptions( 'hmwp_2falogin_message', HMWP_Classes_Tools::getValue( 'hmwp_2falogin_message' ) );
				HMWP_Classes_Tools::saveOptions( 'hmwp_2falogin_fail_message', HMWP_Classes_Tools::getValue( 'hmwp_2falogin_fail_message' ) );

				// Add action for later use
				do_action( 'hmwp_2fasettings_saved' );

				HMWP_Classes_Error::setNotification( esc_html__( 'Saved', 'hide-my-wp' ), 'success' );

				break;
			case 'hmwp_2fa_method':

				$user_id = HMWP_Classes_Tools::getValue( 'user_id' );
				$method     = HMWP_Classes_Tools::getValue( 'method' );

				HMWP_Classes_Tools::saveUserMeta('_hmwp_2fa_method', $method, $user_id);

				wp_send_json_success( esc_html__( 'Saved', 'hide-my-wp' ) );

				break;
			case 'hmwp_totp_submit':
				$user_id = HMWP_Classes_Tools::getValue( 'user_id' );
				$key     = HMWP_Classes_Tools::getValue( 'key' );
				$code    = HMWP_Classes_Tools::getValue( 'authcode' );

				/** @var HMWP_Models_Twofactor_Tftotp $twoFactorService */
				$twoFactorService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Tftotp' );
				$response         = $twoFactorService->setupTotp( $user_id, $key, $code );

				if ( ! is_wp_error( $response ) ) {
					$user = get_user_by( 'ID', $user_id );

					$this->options = $twoFactorService->getTwoFactorOption( $user );

					//Show the two factor block
					wp_send_json_success( $this->getView( 'blocks/Totp' ) );
				} else {
					/** @var WP_Error $response */
					wp_send_json_error( $response->get_error_message() );
				}
				break;
			case 'hmwp_totp_reset':
				$user_id = HMWP_Classes_Tools::getValue( 'user_id' );


				/** @var HMWP_Models_Twofactor_Tftotp $twoFactorService */
				$twoFactorService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Tftotp' );

				if ( $twoFactorService->deleteUserTotpKey( $user_id ) ) {

					// Remove the remember devices
					$this->model->deleteRememberDevices( $user_id );

					$user = get_user_by( 'ID', $user_id );

					$this->options = $twoFactorService->getTwoFactorOption( $user );

					//Show the two-factor block
					wp_send_json_success( $this->getView( 'blocks/Totp' ) );
				} else {
					wp_send_json_error( 'Error' );
				}
				break;
			case 'hmwp_codes_generate':
				$user_id = HMWP_Classes_Tools::getValue( 'user_id' );

				/** @var HMWP_Models_Twofactor_Codes $codesService */
				$codesService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Codes' );

				if ( $user = get_user_by( 'ID', (int) $user_id ) ) {
					if ( $this->codes = $codesService->generateCodes( $user ) ) {
						$this->downloadLinks = $codesService->getDownloadLink( $this->codes );

						//Show the two factor block
						wp_send_json_success( $this->getView( 'blocks/Codes' ) );
					} else {
						wp_send_json_error( 'Error' );
					}
				}

				break;

			case 'hmwp_email_submit':
				$user_id = HMWP_Classes_Tools::getValue( 'user_id' );
				$email   = HMWP_Classes_Tools::getValue( 'email' );

				/** @var HMWP_Models_Twofactor_Email $emailService */
				$emailService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Email' );

				if ( $user_id ) {
					if ( $emailService->setUserEmail( $user_id, $email ) ) {
						$user = get_user_by( 'ID', $user_id );

						$this->options = $emailService->getEmailOption( $user );

						wp_send_json_success( $this->getView( 'blocks/Email' ) );
					} else {
						wp_send_json_error( 'Error' );
					}
				}

				break;

			case 'hmwp_email_reset':
				$user_id = HMWP_Classes_Tools::getValue( 'user_id' );

				/** @var HMWP_Models_Twofactor_Email $emailService */
				$emailService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Email' );

				if ( $user_id ) {
					if ( $emailService->deleteUserEmail( $user_id ) ) {
						$user = get_user_by( 'ID', $user_id );

						$this->options = $emailService->getEmailOption( $user );

						wp_send_json_success( $this->getView( 'blocks/Email' ) );
					} else {
						wp_send_json_error( 'Error' );
					}
				}

				break;

			case 'hmwp_passkey_submit':
				$user_id = HMWP_Classes_Tools::getValue( 'user_id' );

				if ( ! $user_id ) {
					wp_send_json_error( esc_html__( 'Not authenticated.', 'hide-my-wp' ) );
				}

				/** @var HMWP_Models_Twofactor_Passkey $passkeyService */
				$passkeyService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Passkey' );

				if ( $user_id ) {
					if ($passkey = $passkeyService->passkeyRegister( $user_id )){
						wp_send_json_success( $passkey );
					}
				}

				wp_send_json_error( esc_html__( 'Passkey registered failed.', 'hide-my-wp' ) );
				break;

			case 'hmwp_passkey_register':

				$user_id = HMWP_Classes_Tools::getValue( 'user_id' );

				if ( ! $user_id || ! isset( $_POST['credential'] ) ) { //phpcs:ignore
					wp_send_json_error( esc_html__( 'Not authenticated.', 'hide-my-wp' ) );
				}

				$credential = json_decode( sanitize_text_field( wp_unslash( $_POST['credential'] ) ), true ); //phpcs:ignore

				/** @var HMWP_Models_Twofactor_Passkey $passkeyService */
				$passkeyService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Passkey' );

				if ( $user_id && $passkeyService->registerCredential( $user_id, $credential )) {
					wp_send_json_success( esc_html__( 'Passkey registered successfully.', 'hide-my-wp' ) );
				}

				wp_send_json_error( esc_html__( 'Passkey registered failed.', 'hide-my-wp' ) );
				break;

			case 'hmwp_passkey_remove':

				$user_id = HMWP_Classes_Tools::getValue( 'user_id' );
				$id = HMWP_Classes_Tools::getValue( 'id' );

				if ( ! $user_id ) {
					wp_send_json_error( esc_html__( 'Not authenticated.', 'hide-my-wp' ) );
				}

				/** @var HMWP_Models_Twofactor_Passkey $passkeyService */
				$passkeyService = HMWP_Classes_ObjController::getClass( 'HMWP_Models_Twofactor_Passkey' );

				if ( $user_id && $passkeyService->passkeyDelete( $user_id, $id )) {
					wp_send_json_success( esc_html__( 'Passkey deleted successfully.', 'hide-my-wp' ) );
				}

				wp_send_json_error( esc_html__( 'Passkey deleted failed.', 'hide-my-wp' ) );
				break;


		}
	}

	/**
	 * Persist a set of plugin option values.
	 *
	 * Iterates over the supplied key/value pairs, sanitizes each value that
	 * corresponds to a known plugin option, and saves them all in one call.
	 *
	 * @param array $params Associative array of option keys and their new values.
	 *
	 * @return void
	 */
	public function saveValues( $params ) {

		//Save the option values
		foreach ( $params as $key => $value ) {
			if ( in_array( $key, array_keys( HMWP_Classes_Tools::$options ) ) ) {

				//Sanitize each value from subarray
				HMWP_Classes_Tools::$options[ $key ] = HMWP_Classes_Tools::getValue( $key );
			}
		}

		//sanitize the value and save it
		HMWP_Classes_Tools::saveOptions();
	}

}
