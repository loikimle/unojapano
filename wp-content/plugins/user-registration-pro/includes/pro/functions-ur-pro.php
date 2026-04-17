<?php
/**
 * PRO Functions and Hooks
 *
 * @package User Registration Pro
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'user_registration_validate_email_whitelist', 'user_registration_pro_validate_email', 10, 2 );
add_action( 'user_registration_after_register_user_action',  'ur_send_form_data_to_custom_url' , 10, 3 );
add_action( 'init',  'user_registration_force_logout' );

if ( 'yes' === get_option( 'user_registration_pro_general_setting_prevent_active_login','no' ) && !is_admin(  ) ) {
	//User can be authenticated with the provided password
	add_filter( 'wp_authenticate_user', 'ur_prevent_concurrent_logins',10,2 );
}

if ( ! function_exists( 'user_registration_pro_validate_email' ) ) {

	/**
	 * Validate user entered email against whitelisted email domain
	 *
	 * @since 1.0.0
	 * @param email $user_email email entered by user.
	 * @param $filter_hook Filter for validation error message.
	 */
	function user_registration_pro_validate_email( $user_email, $filter_hook ) {

		$option = get_option( 'user_registration_pro_domain_restriction_settings', '' );

		if ( ! empty( $option ) ) {
			$whitelist = array_map( 'trim', explode( PHP_EOL, $option ) );
			$email     = explode( '@', $user_email );

			if ( ! in_array( $email[1], $whitelist ) ) {

				$blacklisted_email = $email[1];
				$message           = sprintf( __( 'The email domain %s is restricted. Please try another email address.', 'user-registration' ), $blacklisted_email );
				if ( '' !== $filter_hook ) {
					add_filter(
						$filter_hook,
						function ( $msg ) use ( $message ) {
							return $message;
						}
					);
				} else {
					// Check if ajax fom submission on edit profile is on.
					if ( 'yes' === get_option( 'user_registration_ajax_form_submission_on_edit_profile', 'no' ) ) {
						wp_send_json_error(
							array(
								'message' => $message,
							)
						);
					} else {
						ur_add_notice( $message, 'error' );
					}
				}
			}
		}
	}

	/**
	 * Handles all settings action.
	 *
	 * @return void.
	 */
	function user_registration_pro_popup_settings_handler() {

		if ( ! empty( $_POST ) ) {

			// Nonce Check.
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'user-registration-settings' ) ) {
				die( __( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
			}

			// Update the popups for add new functionality
			if ( ( isset( $_POST['user_registration_pro_popup_title'] ) && ! empty( $_POST['user_registration_pro_popup_title'] ) ) || ( isset( $_REQUEST['edit-popup'] ) && ! empty( $_REQUEST['edit-popup'] ) ) ) {
				$active       = isset( $_POST['user_registration_pro_enable_popup'] ) ? $_POST['user_registration_pro_enable_popup'] : '';
				$popup_type   = isset( $_POST['user_registration_pro_popup_type'] ) ? $_POST['user_registration_pro_popup_type'] : '';
				$popup_title  = isset( $_POST['user_registration_pro_popup_title'] ) ? $_POST['user_registration_pro_popup_title'] : '';
				$popup_header = isset( $_POST['user_registration_pro_popup_header_content'] ) ? $_POST['user_registration_pro_popup_header_content'] : '';
				$form         = isset( $_POST['user_registration_pro_popup_registration_form'] ) ? $_POST['user_registration_pro_popup_registration_form'] : '';
				$popup_footer = isset( $_POST['user_registration_pro_popup_footer_content'] ) ? $_POST['user_registration_pro_popup_footer_content'] : '';
				$popup_size   = isset( $_POST['user_registration_pro_popup_size'] ) ? $_POST['user_registration_pro_popup_size'] : 'default';

				$post_data = array(
					'popup_type'   => $popup_type,
					'popup_title'  => $popup_title,
					'popup_status' => $active,
					'popup_header' => $popup_header,
					'popup_footer' => $popup_footer,
					'popup_size'   => $popup_size,
				);

				if ( 'registration' === $popup_type ) {
					$post_data['form'] = $form;
				}

				$post_data = array(
					'post_type'      => 'ur_pro_popup',
					'post_title'     => ur_clean( $popup_title ),
					'post_content'   => wp_json_encode( $post_data, JSON_UNESCAPED_UNICODE ),
					'post_status'    => 'publish',
					'comment_status' => 'closed',   // if you prefer
					'ping_status'    => 'closed',      // if you prefer
				);

				if ( isset( $_REQUEST['edit-popup'] ) ) {
					$post_data['ID'] = $_REQUEST['edit-popup'];
					$post_id         = wp_update_post( wp_slash( $post_data ), true );
				} else {
					$post_id = wp_insert_post( wp_slash( $post_data ), true );
				}
				return true;
			}
		}

	}
}

add_filter( 'user_registration_add_form_field_data', 'user_registration_form_honeypot_field_filter', 10, 2 );

if ( ! function_exists( 'user_registration_form_honeypot_field_filter' ) ) {
	/**
	 * Add honeypot field data to form data.
	 *
	 * @since 1.0.0
	 * @param array $form_data_array Form data parsed form form's post content.
	 * @param int   $form_id ID of the form.
	 */

	function user_registration_form_honeypot_field_filter( $form_data_array, $form_id ) {

		$enable_spam_protection   = ur_get_single_post_meta( $form_id, 'user_registration_pro_spam_protection_by_honeypot_enable' );

		if( 'yes' === $enable_spam_protection || '1'===$enable_spam_protection ) {
			$honeypot = (object) array(
				'field_key'       => 'honeypot',
				'general_setting' => (object) array(
					'label'       => 'Honeypot',
					'description' => '',
					'field_name'  => 'honeypot',
					'placeholder' => '',
					'required'    => 'no',
					'hide_label'  => 'no',
				),
			);
			array_push( $form_data_array, $honeypot );
		}
		return $form_data_array;
	}
}

add_action( 'user_registration_validate_honeypot_container', 'user_registration_validate_honeypot_container', 10, 4 );

if ( ! function_exists( 'user_registration_validate_honeypot_container' ) ) {

	/**
	 * Validate user honeypot to check if the field is filled with spams.
	 *
	 * @since 1.0.0
	 * @param object $data Data entered by the user.
	 * @param array  $filter_hook Filter for validation error message.
	 * @param int    $form_id ID of the form.
	 * @param array  $form_data_array All fields form data entered by user.
	 */
	function user_registration_validate_honeypot_container( $data, $filter_hook, $form_id, $form_data_array ) {
		$value = isset( $data->value ) ? $data->value : '';

		if ( '' !== $value ) {

			$form_data = array();

			foreach ( $form_data_array as $single_field_data ) {
					$form_data[ $single_field_data->field_name ] = $single_field_data->value;
			}

			// Log the spam entry.
			$logger = ur_get_logger();
			$logger->notice( sprintf( 'Spam entry for Form ID %d Response: %s', absint( $form_id ), print_r( $form_data, true ) ), array( 'source' => 'honeypot' ) );

			add_filter(
				$filter_hook,
				function ( $msg ) {
					return esc_html__( 'Registration Error. Your Registration has been blocked by Spam Protection.', 'user-registration' );
				}
			);
		}
	}
}

if ( ! function_exists( 'user_registration_pro_dasboard_card' ) ) {

	/**
	 * User Registration dashboard card.
	 *
	 * @since 1.0.0
	 */
	function user_registration_pro_dasboard_card( $title, $body_class, $body ) {

		$card  = '';
		$card .= '<div class="user-registration-card ur-mb-6">';

		if ( '' !== $title ) {
			$card .= '<div class="user-registration-card__header">';
			$card .= '<h3 class="user-registration-card__title">' . esc_html( $title ) . '</h3>';
			$card .= '</div>';
		}

		$card .= '<div class="user-registration-card__body ' . esc_attr( $body_class ) . '">' . $body . '</div>';
		$card .= '</div>';

		return $card;
	}
}

if ( ! function_exists( 'user_registration_pro_approval_status_registration_overview_report' ) ) {

	/**
	 * Builds User Status card template based on form selected.
	 *
	 * @param int    $form_id ID of selected form.
	 * @param array  $overview Array of user datas at different settings.
	 * @param string $label Label for status card.
	 * @param string $approval_status Specific approval status for specific status cards .
	 * @param string $link_text View lists of specific approval status link text.
	 */
	function user_registration_pro_approval_status_registration_overview_report( $form_id, $overview, $label, $approval_status, $link_text ) {
		$ur_specific_form_user = '&ur_user_approval_status=' . $approval_status;

		if ( 'all' !== $form_id ) {
			$ur_specific_form_user .= '&ur_specific_form_user=' . $form_id;
		}

		$admin_url                          = admin_url( '', 'admin' ) . 'users.php?s&action=-1&new_role' . $ur_specific_form_user . '&ur_user_filter_action=Filter&paged=1&action2=-1&new_role2&ur_user_approval_status2&ur_specific_form_user2';
		$status_registration_overview_card  = '';
		$status_registration_overview_card .= '<div class="ur-col-lg-3 ur-col-md-6">';

		$body  = '';
		$body .= '<div class="ur-row ur-align-items-center">';
		$body .= '<div class="ur-col">';

		$body .= '<h4 class="ur-text-muted ur-mt-0">' . esc_html__( $label, 'user-registration' ) . '</h4>';
		$body .= '<span class="ur-h2 ur-mr-1">' . esc_html( $overview ) . '</span>';
		$body .= '</div>';
		$body .= '<div class="ur-col-auto">';
		$body .= '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="23" y1="11" x2="17" y2="11"></line></svg>';
		$body .= '</div>';
		$body .= '<div class="ur-col-12">';
		$body .= '<a class="ur-d-flex ur-mb-0 ur-mt-2" href="' . esc_url( $admin_url ) . ' ">' . esc_html( $link_text ) . '</a>';
		$body .= '</div>';
		$body .= '</div>';

		$status_registration_overview_card .= user_registration_pro_dasboard_card( '', '', $body );

		$status_registration_overview_card .= '</div>';

		return $status_registration_overview_card;

	}
}

if ( ! function_exists( 'ur_exclude_fields_in_post_submssion' ) ) {

	/**
	 * Get the user registration form fields to exclude in post submission.
	 *
	 * @return array
	 */
	function ur_exclude_fields_in_post_submssion() {
		$fields_to_exclude = array(
			'user_pass',
			'user_confirm_password',
			'password',
		);
		 $fields_to_exclude = apply_filters( 'ur_exclude_fields_in_post_submssion', $fields_to_exclude );
		return $fields_to_exclude;
	}
}

if ( ! function_exists( 'ur_send_form_data_to_custom_url' ) ) {
/**
	 * Send form data to custom url after registration hook.
	 *
	 * @param  array $valid_form_data Form filled data.
	 * @param  int   $form_id         Form ID.
	 * @param  int   $user_id         User ID.
	 * @return void
	 */
	function ur_send_form_data_to_custom_url( $valid_form_data, $form_id, $user_id){

		$valid_form_data = isset( $valid_form_data ) ? $valid_form_data : array();
		$fields_to_exclude = ur_exclude_fields_in_post_submssion();

		foreach ( $fields_to_exclude as $key => $value ) {

			if ( isset( $valid_form_data[$value] ) ){
				unset( $valid_form_data[$value] );
			}
		}

		if (	null !==  get_option( "user_registration_pro_general_post_submission_settings" )){
			$url = get_option( "user_registration_pro_general_post_submission_settings" );
			$single_field = array();
			foreach ( $valid_form_data as $data ) {
				$single_field[ $data->field_name ] = isset( $data->value ) ? $data->value : '';
			}

			if ( "post_json" === get_option("user_registration_pro_general_setting_post_submission",array()) ) {
				$headers = array('Content-Type' => 'application/json; charset=utf-8');
				wp_remote_post($url, array('body'=>json_encode($single_field), 'headers'=>$headers));

			} elseif ( "get" === get_option("user_registration_pro_general_setting_post_submission",array()) ) {
				$url = $url.'?'.http_build_query($single_field);
				wp_remote_get( $url);
			} else {
				wp_remote_post( $url, array('body'=>$single_field));
			}
  		}

	}
}

if ( ! function_exists( 'ur_prevent_concurrent_logins' ) ) {

	/**
	 * Validate if the maximum active logins limit reached.
	 *
	 * @param object $user User Object/WPError.
	 *
	 * @since  3.0.0
	 *
	 * @return object User object or error object.
	 */
	function ur_prevent_concurrent_logins( $user ) {

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$pass  = ! empty( $_POST['password'] ) ? $_POST['password'] : '';
		$ur_max_active_login =  intval( get_option( 'user_registration_pro_general_setting_limited_login' ) );
		$user_id =  $user->ID;

		//Get current user's session
		$sessions = WP_Session_Tokens::get_instance( $user_id );

		//Get all his active wordpress sessions
		$all_sessions = $sessions->get_all();
		$count = count( $all_sessions );

		if ( $count >= $ur_max_active_login &&  wp_check_password( $pass, $user->user_pass,$user->ID ) )  {
			$user_id = $user->ID;
			$user_email = $user->user_email;

			// Error message
			$error_message = sprintf( '<strong>' . __( 'ERROR:', 'user-registration' ) . '</strong>' . __( 'Maximum no. of active logins found for this account. Please logout from another device to continue. %1s', 'user-registration' ), "<a href='javascript:void(0)' class='user-registartion-force-logout' data-user-id='".$user_id."' data-email='".$user_email."'>" . __( 'Force Logout?', 'user-registration' ) . '</a>' );

			return new WP_Error( 'user_registration_error_message', $error_message );
		}

		return $user;
	}

}


if ( ! function_exists( 'user_registration_force_logout' ) ) {
	/**
	 * Destroy the session of user.
	 *
	 * @since  3.0.0
	 */
	function user_registration_force_logout(){

		if ( ! empty( $_GET['force-logout'] ) ) {
			$user_id = intval($_GET['force-logout']);
			$sessions = WP_Session_Tokens::get_instance( $user_id );
			$sessions->destroy_all();
			wp_redirect(ur_get_page_permalink( 'myaccount' ) );
			exit;
		}
	}
}


if ( ! function_exists( 'ur_get_license_plan' ) ) {

	/**
	 * Get a PRO license plan.
	 *
	 * @since  3.0.1
	 * @return bool|string Plan on success, false on failure.
	 */
	function ur_get_license_plan() {
		$license_key = get_option( 'user-registration_license_key' );

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( $license_key && is_plugin_active( 'user-registration-pro/user-registration.php' ) ) {
			delete_transient( 'ur_pro_license_plan' );
			$license_data = get_transient( 'ur_pro_license_plan' );

			if ( false === $license_data ) {
				$license_data = json_decode(
					UR_Updater_Key_API::check(
						array(
							'license' => $license_key,
						)
					)
				);

				if ( ! empty( $license_data->item_name ) ) {
					$license_data->item_plan  = strtolower( str_replace( 'LifeTime', '', str_replace( 'User Registration', '', $license_data->item_name ) ) );
					set_transient( 'ur_pro_license_plan', $license_data, WEEK_IN_SECONDS );
				}
			}

			return isset( $license_data->item_plan ) ? $license_data->item_plan : false;
		}

		return false;
	}
}
