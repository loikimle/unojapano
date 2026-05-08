<?php

/**
 * PRO Functions and Hooks
 *
 * @package User Registration Pro
 * @version 1.0.0
 */

use WPEverest\URMembership\Taxes\Admin\UR_Tax_Region_Table;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

add_action( 'user_registration_validate_email_whitelist', 'user_registration_pro_validate_email', 10, 4 );
add_action( 'user_registration_validate_slot_booking', 'user_registration_pro_validate_slot_booking', 10, 4 );
add_action( 'user_registration_after_register_user_action', 'ur_send_form_data_to_custom_url', 10, 3 );
// Register Signature Field
add_filter( 'user_registration_registered_form_fields', 'ur_register_signature_field' );
add_filter( 'user_registration_form_field_signature_path', 'ur_add_signature_field' );
add_filter( 'user_registration_signature_admin_template', 'ur_add_signature_template' );
add_filter( 'user_registration_field_keys', 'ur_get_signature_field_type', 10, 2 );
add_filter( 'user_registration_sanitize_field', 'ur_sanitize_signature_field', 10, 2 );
// Register Captcha field.
add_filter( 'user_registration_registered_form_fields', 'ur_register_captcha_field' );
add_filter( 'user_registration_form_field_captcha_path', 'ur_add_captcha_field' );
add_filter( 'user_registration_captcha_admin_template', 'ur_add_captcha_template' );
add_filter( 'user_registration_field_keys', 'ur_get_captcha_field_type', 10, 2 );
add_filter( 'user_registration_sanitize_field', 'ur_sanitize_captcha_field', 10, 2 );
add_action( 'init', 'user_registration_force_logout' );

add_action( 'save_post', 'user_registration_pro_handle_get_current_screen_error', 8, 3 );

if ( ! function_exists( 'user_registration_pro_handle_get_current_screen_error' ) ) {
	/**
	 * Check if get_current_screen function exists if woocommerce addon activated.
	 *
	 * @param int    $post_id Post ID.
	 * @param object $post Post.
	 * @param   bool   $update Update.
	 */
	function user_registration_pro_handle_get_current_screen_error( $post_id, $post, $update ) {
		if ( is_plugin_active( 'user-registration-woocommerce/user-registration-woocommerce.php' ) ) {
			if ( ! function_exists( 'get_current_screen' ) ) {
				require_once ABSPATH . '/wp-admin/includes/screen.php';
			}
		}
	}
}
/**
 * Register signature field.
 *
 * @param array $fields Fields.
 * @return array  All registered fields.
 */
function ur_register_signature_field( $fields ) {
	$fields[] = 'signature';
	return $fields;
}
/**
 * Add signature field path
 */
function ur_add_signature_field() {
	include_once __DIR__ . '/form/class-ur-form-field-signature.php';
}

/**
 * Signature field template
 *
 * @return  string
 */
function ur_add_signature_template() {

	$path = __DIR__ . '/form/views/admin/admin-signature.php';

	return $path;
}

/**
 * Assign field type to Signature
 *
 * @param  string $field_type Field Type.
 * @param  string $field_key Field Key.
 * @return string
 */
function ur_get_signature_field_type( $field_type, $field_key ) {

	if ( 'signature' === $field_key ) {
		$field_type = 'signature';
	}

	return $field_type;
}

/**
 * Sanitize captcha fields on frontend submit
 *
 * @param  array  $form_data    Form Data.
 * @param  string $field_key    Field Key.
 * @return array  Form Data.
 */
function ur_sanitize_signature_field( $form_data, $field_key ) {

	switch ( $field_key ) {
		case 'signature':
			if ( ! empty( $form_data->value ) ) {
				$form_data->value = sanitize_text_field( $form_data->value );
			}

			break;
	}

	return $form_data;
}

/**
 * Register captcha field.
 *
 * @param array $fields Fields.
 * @return array  All registered fields.
 */
function ur_register_captcha_field( $fields ) {
	$fields[] = 'captcha';
	return $fields;
}

/**
 * Add captcha field path
 */
function ur_add_captcha_field() {
	include_once __DIR__ . '/form/class-ur-form-field-captcha.php';
}

/**
 * Captcha field template
 *
 * @return  string
 */
function ur_add_captcha_template() {

	$path = __DIR__ . '/form/views/admin/admin-captcha.php';

	return $path;
}

/**
 * Assign field type to Captcha
 *
 * @param  string $field_type Field Type.
 * @param  string $field_key Field Key.
 * @return string
 */
function ur_get_captcha_field_type( $field_type, $field_key ) {

	if ( 'captcha' === $field_key ) {
		$field_type = 'captcha';
	}

	return $field_type;
}

/**
 * Sanitize captcha fields on frontend submit
 *
 * @param  array  $form_data    Form Data.
 * @param  string $field_key    Field Key.
 * @return array  Form Data.
 */
function ur_sanitize_captcha_field( $form_data, $field_key ) {

	switch ( $field_key ) {
		case 'captcha':
			if ( ! empty( $form_data->value ) ) {
				$form_data->value = sanitize_text_field( $form_data->value );
			}

			break;
	}

	return $form_data;
}

/**
 * Function to pick random captcha question from and array.
 *
 * @param array $options options.
 * @return string $index index.
 */
if ( ! function_exists( 'ur_captcha_random_question' ) ) {
	function ur_captcha_random_question( $options ) {

		if ( empty( $options ) ) {
			return false;
		}

		foreach ( $options as $key => $question ) {
			if ( empty( $question['question'] ) || empty( $question['answer'] ) ) {
				unset( $form_fields['questions'][ $key ] );
			}
		}
		$index = array_rand( $options );
		if ( ! isset( $options[ $index ]['question'] ) || ! isset( $options[ $index ]['answer'] ) ) {
			$index = ur_captcha_random_question( $options );
		}
		return $index;
	}
}
/**
 * Function to pick random image captcha group from an array.
 *
 * @param array $image_captchas image captcha group.
 * @return string $index index.
 */
if ( ! function_exists( 'ur_captcha_random_image_group' ) ) {
	function ur_captcha_random_image_group( $image_captchas ) {

		if ( empty( $image_captchas ) ) {
			return false;
		}

		foreach ( $image_captchas as $key => $group ) {
			if ( ! is_array( $group ) ) {
				$group = (array) $group;
			}
			$image_captchas[ $key ] = $group;
			if ( ( empty( $group['icon_tag'] ) || empty( $group['icon-1'] ) || empty( $group['icon-2'] ) || empty( $group['icon-3'] ) ) || empty( $group['correct_icon'] ) ) {
				unset( $image_captchas[ $key ] );
			}
		}

		if ( empty( $image_captchas ) ) {
			return 'false';
		}

		$index = array_rand( $image_captchas );

		if ( ! isset( $image_captchas[ $index ]['icon-1'] ) || ! isset( $image_captchas[ $index ]['icon-2'] ) || ! isset( $image_captchas[ $index ]['icon-3'] ) || ! isset( $image_captchas[ $index ]['correct_icon'] ) || ! isset( $image_captchas[ $index ]['icon_tag'] ) ) {
			$index = ur_captcha_random_image_group( $image_captchas );
		}
		return $index;
	}
}

if ( ur_string_to_bool( get_option( 'user_registration_pro_general_setting_prevent_active_login', false ) ) ) {
	// User can be authenticated with the provided password.
	add_filter( 'wp_authenticate_user', 'ur_prevent_concurrent_logins', 10, 2 );
}

if ( ur_string_to_bool( get_option( 'user_registration_pro_general_post_submission_profile_update', false ) ) ) {
	// Allow Sharing of data with custom URL
	add_action( 'user_registration_save_profile_details', 'ur_send_form_data_on_profile_update', 10, 2 );

	if ( isset( $_GET['confirm_email'] ) && isset( $_GET['confirm_key'] ) ) {
		add_action( 'user_registration_email_change_success', 'ur_send_form_data_on_email_confirmation_success', 10, 1 );
	}
}

if ( ! function_exists( ' user_registration_pro_sync_external_field' ) ) {
	/**
	 * While registration save external field meta with mapped user registration field value.
	 *
	 * @param array $valid_form_data Form Data.
	 * @param int   $form_id Form ID.
	 * @param int   $user_id User ID.
	 */
	function user_registration_pro_sync_external_field( $valid_form_data, $form_id, $user_id ) {

		global $wpdb;

		$field_mapping_settings = maybe_unserialize( get_post_meta( $form_id, 'user_registration_pro_external_fields_mapping', true ) );

		if ( ! empty( $field_mapping_settings ) ) {

			$usermeta_table                 = $wpdb->prefix . 'usermeta';
			$selected_db_table              = isset( $field_mapping_settings[0]['db_table'] ) ? $field_mapping_settings[0]['db_table'] : $usermeta_table;
			$selected_user_id_db_column     = isset( $field_mapping_settings[0]['user_id_db_column'] ) ? $field_mapping_settings[0]['user_id_db_column'] : '';
			$selected_field_key_db_column   = isset( $field_mapping_settings[0]['field_key_db_column'] ) ? $field_mapping_settings[0]['field_key_db_column'] : '';
			$selected_field_value_db_column = isset( $field_mapping_settings[0]['field_value_db_column'] ) ? $field_mapping_settings[0]['field_value_db_column'] : '';

			$is_valid_db_tables_and_columns = false;

			if ( $usermeta_table !== $selected_db_table ) {

				if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $selected_db_table ) ) ) === $selected_db_table ) {
					$is_valid_db_tables_and_columns = true;
				}
			}

			if ( isset( $field_mapping_settings[0]['mapped_fields'] ) ) {

				foreach ( $field_mapping_settings[0]['mapped_fields'] as $fields_row ) {

					foreach ( $fields_row as $key => $mapping_row ) {
						if ( isset( $valid_form_data[ $mapping_row['ur_field'] ] ) ) {
							if ( $usermeta_table === $selected_db_table ) {
								update_user_meta( $user_id, $mapping_row['external_field'], $valid_form_data[ $mapping_row['ur_field'] ]->value );
							} elseif ( $is_valid_db_tables_and_columns && ! empty( $selected_user_id_db_column ) && ! empty( $selected_field_key_db_column ) && ! empty( $selected_field_value_db_column ) ) {

								$element_prepared = $wpdb->prepare(
									"SELECT $selected_field_key_db_column FROM $selected_db_table WHERE $selected_user_id_db_column=%d AND $selected_field_key_db_column=%s",
									array( $user_id, $mapping_row['external_field'] )
								);
								$field_key_db     = $wpdb->get_var( $element_prepared );

								$filtered_value = apply_filters( 'user_registration_sync_external_' . $valid_form_data[ $mapping_row['ur_field'] ]->extra_params['field_key'] . '_field', $valid_form_data[ $mapping_row['ur_field'] ]->value, $form_id, $mapping_row['ur_field'] );

								$value = is_array( $filtered_value ) ? maybe_serialize( $filtered_value ) : $filtered_value;

								if ( $field_key_db === $mapping_row['external_field'] ) {

									$result = $wpdb->update(
										$selected_db_table,
										array(
											$selected_field_value_db_column => $value,
										),
										array(
											$selected_user_id_db_column     => $user_id,
											$selected_field_key_db_column   => $mapping_row['external_field'],
										)
									);
								} else {
									$result = $wpdb->insert(
										$selected_db_table,
										array(
											$selected_user_id_db_column     => $user_id,
											$selected_field_key_db_column   => $mapping_row['external_field'],
											$selected_field_value_db_column => $value,
										)
									);
								}

								if ( is_wp_error( $result ) ) {
									$error_string = $wpdb->last_error;
									ur_get_logger()->debug( print_r( $error_string, true ) );
								}
							}
						}
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'user_registration_pro_validate_email' ) ) {

	/**
	 * Validate user entered email against whitelisted email domain
	 *
	 * @since 1.0.0
	 * @param email  $user_email email entered by user.
	 * @param string $filter_hook Filter for validation error message.
	 */
	function user_registration_pro_validate_email( $user_email, $filter_hook, $field, $form_id ) {
		$enable_domain_settings   = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_enable_whitelist_domain', false );
		$domain_settings          = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_whitelist_domain', 'allowed' );
		$whitelist_domain_entries = strtolower( ur_get_single_post_meta( $form_id, 'user_registration_form_setting_domain_restriction_settings', '' ) );
		$field_label              = $field->general_setting->field_name;

		if ( ur_string_to_bool( $enable_domain_settings ) ) {
			if ( ! empty( $whitelist_domain_entries ) ) {
				$whitelist         = array_map( 'trim', explode( ',', $whitelist_domain_entries ) );
				$email             = explode( '@', $user_email );
				$blacklisted_email = '';
				$domain            = strtolower( $email[1] );
				if ( 'allowed' === $domain_settings ) {
					if ( ! in_array( $domain, $whitelist ) ) {
						$blacklisted_email = $domain;
					}
				} elseif ( in_array( $domain, $whitelist ) ) {
					$blacklisted_email = $domain;
				}

				if ( ! empty( $blacklisted_email ) ) {
					$message = sprintf(
						/* translators: %s - Restricted domain. */
						__( 'The email domain %s is restricted. Please try another email address.', 'user-registration' ),
						$blacklisted_email
					);

					if ( '' !== $filter_hook ) {
						$message = array(
							/* translators: %s - validation message */
							$field_label => sprintf( __( '%s.', 'user-registration' ), $message ),
							'individual' => true,
						);
						add_filter(
							$filter_hook,
							function ( $msg ) use ( $field_label, $message ) {
								if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || ! ur_option_checked( 'user_registration_ajax_form_submission_on_edit_profile', false ) ) {
									return sprintf( $message[ $field_label ] );
								} else {
									wp_send_json_error(
										array(
											'message' => $message,
										)
									);
								}
							}
						);
					} else {
						// Check if ajax fom submission on edit profile is on.
						if ( ur_option_checked( 'user_registration_ajax_form_submission_on_edit_profile', false ) ) {
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
		 * Email Blacklist validation.
		 *
		 * @since 4.0.4.2
		 */
		$enable_email_blocking_setting = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_email_blocking', false );
		if ( ur_string_to_bool( $enable_email_blocking_setting ) ) {
			$email_blacklist = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_email_black_list', '' );
			if ( ! empty( $email_blacklist ) ) {
				$email_blacklist_arr = array_map( 'trim', explode( ',', $email_blacklist ) );
				$email_blacklist_arr = array_map( 'strtolower', $email_blacklist_arr );
				if ( in_array( strtolower( $user_email ), $email_blacklist_arr, true ) ) {
					$message = sprintf(
						/* translators: %s - Restricted email. */
						__( 'The email %s is restricted. Please try another email address.', 'user-registration' ),
						$user_email
					);

					$message = apply_filters( 'user_registration_email_blacklist_error_message', $message, $email_blacklist_arr, $user_email );

					if ( '' !== $filter_hook ) {
						$message = array(
							/* translators: %s - validation message */
							$field_label => sprintf( __( '%s.', 'user-registration' ), $message ),
							'individual' => true,
						);
						add_filter(
							$filter_hook,
							function ( $msg ) use ( $field_label, $message ) {
								if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || ! ur_option_checked( 'user_registration_ajax_form_submission_on_edit_profile', false ) ) {
									return sprintf( $message[ $field_label ] );
								} else {
									wp_send_json_error(
										array(
											'message' => $message,
										)
									);
								}
							}
						);
					} else {
						// Check if ajax fom submission on edit profile is on.
						if ( ur_option_checked( 'user_registration_ajax_form_submission_on_edit_profile', false ) ) {
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
	}
}

/**
 * Handles all settings action.
 *
 * @return bool.
 */
function user_registration_pro_popup_settings_handler() {

	if ( ! empty( $_POST ) ) {

		// Nonce Check.
		if (empty($_REQUEST['_wpnonce']) || ! wp_verify_nonce($_REQUEST['_wpnonce'], 'user-registration-settings')) { // phpcs:ignore
			die( esc_html__( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
		}

		$popup_data = wp_unslash( $_POST );

		// Update the popups for add new functionality.
		if ( ( isset( $popup_data['user_registration_pro_popup_title'] ) && ! empty( $popup_data['user_registration_pro_popup_title'] ) ) || ( isset( $_REQUEST['edit-popup'] ) && ! empty( $_REQUEST['edit-popup'] ) ) ) {
			$active       = isset( $popup_data['user_registration_pro_enable_popup'] ) ? $popup_data['user_registration_pro_enable_popup'] : '';
			$popup_type   = isset( $popup_data['user_registration_pro_popup_type'] ) ? $popup_data['user_registration_pro_popup_type'] : '';
			$popup_title  = isset( $popup_data['user_registration_pro_popup_title'] ) ? $popup_data['user_registration_pro_popup_title'] : '';
			$popup_header = isset( $popup_data['user_registration_pro_popup_header_content'] ) ? $popup_data['user_registration_pro_popup_header_content'] : '';
			$form         = isset( $popup_data['user_registration_pro_popup_registration_form'] ) ? $popup_data['user_registration_pro_popup_registration_form'] : '';
			$popup_footer = isset( $popup_data['user_registration_pro_popup_footer_content'] ) ? $popup_data['user_registration_pro_popup_footer_content'] : '';
			$popup_size   = isset( $popup_data['user_registration_pro_popup_size'] ) ? $popup_data['user_registration_pro_popup_size'] : 'default';

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
				'comment_status' => 'closed',   // if you prefer.
				'ping_status'    => 'closed',      // if you prefer.
			);

			if ( isset( $_REQUEST['edit-popup'] ) ) {
				$post_data['ID'] = wp_unslash( intval( $_REQUEST['edit-popup'] ) );
				$post_id         = wp_update_post( wp_slash( $post_data ), true );
				update_option( 'ur-popup-edited', true );
			} else {
				$post_id = wp_insert_post( wp_slash( $post_data ), true );
				update_option( 'ur-popup-created', true );
			}
			return true;
		}
	}
}


if ( ! function_exists( 'user_registration_pro_validate_slot_booking' ) ) {
	/**
	 * Validate the slot booking while new registration as well as while update the profile.
	 *
	 * @since 4.1.0
	 * @param [array]  $form_data Form data.
	 * @param [string] $filter_hook Filter hook.
	 * @param [array]  $field    Single field data.
	 * @param [int]    $form_id Form ID.
	 */
	function user_registration_pro_validate_slot_booking( $form_data, $filter_hook, $field, $form_id ) {

		$slot_booking_fields_settings = ur_pro_get_slot_booking_fields_settings( $form_id );
		$all_parse_arr                = array();
		$date_value                   = '';
		$time_value                   = '';
		$time_interval                = '';
		$mode                         = '';
		$mode_type                    = '';
		$valid_form_data              = array();
		foreach ( $form_data as $field ) {
			if ( isset( $field->field_name ) && isset( $field->value ) ) {
				$valid_form_data[ $field->field_name ] = array( 'value' => $field->value );
			}
		}
		foreach ( $slot_booking_fields_settings as $field_name => $field_setting ) {
			if ( $field_setting['field_key'] === 'date' ) {
				if ( ur_string_to_bool( $field_setting['enable_date_slot_booking'] ) ) {
					$mode      = 'date';
					$mode_type = isset( $field_setting['enable_date_range'] ) ? $field_setting['enable_date_range'] : '';
					if ( ur_string_to_bool( $mode_type ) ) {
						$mode_type = 'range';
					}
					$date_value = $valid_form_data[ $field_name ]['value'];

					$parse_arr                    = ur_pro_parse_date_time( $date_value, $time_value, $time_interval, $mode, '', $mode_type, '' );
					$all_parse_arr[ $field_name ] = $parse_arr;
				}
			} elseif ( $field_setting['field_key'] === 'timepicker' ) {
				if ( ur_string_to_bool( $field_setting['enable_time_slot_booking'] ) ) {
					$mode          = 'time';
					$time_value    = $valid_form_data[ $field_name ]['value'];
					$time_interval = isset( $field_setting['time_interval'] ) ? $field_setting['time_interval'] : '';

					if ( '' !== $field_setting['target_date_field'] ) {
						if ( array_key_exists( $field_setting['target_date_field'], $slot_booking_fields_settings ) ) {
							$target_field_name = $field_setting['target_date_field'];

							$mode       = 'date-time';
							$date_value = $valid_form_data[ $field_setting['target_date_field'] ]['value'];
							$mode_type  = isset( $slot_booking_fields_settings[ $target_field_name ]['enable_date_range'] ) ? $slot_booking_fields_settings[ $target_field_name ]['enable_date_range'] : '';
							if ( ur_string_to_bool( $mode_type ) ) {
								$mode_type = 'range';
							}
						}
					}

					$parse_arr                    = ur_pro_parse_date_time( $date_value, $time_value, $time_interval, $mode, '', $mode_type, '' );
					$all_parse_arr[ $field_name ] = $parse_arr;
				}
			}
		}
		$users_slot_booked_meta_data = get_users_slot_booked_meta_data( $form_id );

		$is_booked = false;
		foreach ( $all_parse_arr as $field_key => $date_time_arr ) {

			foreach ( $date_time_arr as $arr ) {
				foreach ( $users_slot_booked_meta_data as $user_id => $booked_slot ) {
					if ( empty( $booked_slot ) ) {
						continue;
					}
					if ( array_key_exists( $field_key, $booked_slot ) ) {

						if ( is_user_logged_in() ) {
							if ( $user_id == get_current_user_id() ) {
								continue;
							}
						}

						$slot_arrs = $booked_slot[ $field_key ];

						if ( empty( $slot_arrs ) ) {
							continue;
						}
						$slot = $slot_arrs[0];
						if ( $arr[0] >= $slot[0] && $arr[1] <= $slot[1] ) {
							$is_booked = true;
							break;
						} elseif ( $arr[0] >= $slot[0] && $arr[0] < $slot[1] && $arr[1] >= $slot[1] ) {
							$is_booked = true;
							break;
						}
					}
				}
				if ( $is_booked ) {
					break;
				}
			}
		}

		if ( $is_booked ) {
			$message = sprintf(
				/* translators: %s - Restricted email. */
				esc_html__( 'This slot is already booked. Please choose other slot', 'user-registration' ),
				$is_booked
			);

			$message = apply_filters( 'ur_pro_slot_booking_message', $message );

			if ( '' !== $filter_hook ) {
				add_filter(
					$filter_hook,
					function ( $msg ) use ( $message ) {
						return $message;
					}
				);
			} else {
				// Check if ajax fom submission on edit profile is on.
				if ( ur_option_checked( 'user_registration_ajax_form_submission_on_edit_profile', false ) ) {
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

		$enable_spam_protection = ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_pro_spam_protection_by_honeypot_enable' ) );

		if ( $enable_spam_protection ) {
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
	 * @param string $title Dashboard card title.
	 * @param string $body_class Dashboard card body class.
	 * @param html   $body Dashboard card body.
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

		$body .= '<h4 class="ur-text-muted ur-mt-0">' . esc_html( $label ) . '</h4>';
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
	function ur_send_form_data_to_custom_url( $valid_form_data, $form_id, $user_id ) {

		$valid_form_data   = isset( $valid_form_data ) ? $valid_form_data : array();
		$fields_to_exclude = ur_exclude_fields_in_post_submssion();

		foreach ( $fields_to_exclude as $key => $value ) {

			if ( isset( $valid_form_data[ $value ] ) ) {
				unset( $valid_form_data[ $value ] );
			}
		}

		if ( null !== get_option( 'user_registration_pro_general_post_submission_settings' ) ) {
			$url          = get_option( 'user_registration_pro_general_post_submission_settings' );
			$single_field = array();
			foreach ( $valid_form_data as $data ) {
				if ( isset( $data->field_type ) && 'password' === $data->field_type ) {
					continue;
				} elseif ( isset( $data->extra_params['field_key'] ) && 'privacy_policy' === $data->extra_params['field_key'] ) {
					$single_field[ $data->field_name ] = ( isset( $data->value ) && $data->value ) ? 'Accepted' : 'Not Accepted';
				} else {
					$single_field[ $data->field_name ] = isset( $data->value ) ? $data->value : '';
				}
			}

			if ( 'post_json' === get_option( 'user_registration_pro_general_setting_post_submission', array() ) ) {
				$headers = array( 'Content-Type' => 'application/json; charset=utf-8' );
				wp_remote_post(
					$url,
					array(
						'body'    => json_encode( $single_field ),
						'headers' => $headers,
					)
				);
			} elseif ( 'get' === get_option( 'user_registration_pro_general_setting_post_submission', array() ) ) {
				$url = $url . '?' . http_build_query( $single_field );
				wp_remote_get( $url );
			} else {
				wp_remote_post( $url, array( 'body' => $single_field ) );
			}
		}
	}
}

if ( ! function_exists( 'ur_send_form_data_on_profile_update' ) ) {
	/**
	 * Send form data to custom url during save profile details hook.
	 *
	 * @param  int $user_id         User ID.
	 * @param  int $form_id         Form ID.
	 * @return void
	 */
	function ur_send_form_data_on_profile_update( $user_id, $form_id ) {

		$user_extra_fields        = ur_get_user_extra_fields( $user_id );
		$user_data                = (array) get_userdata( $user_id )->data;
		$user_data['first_name']  = get_user_meta( $user_id, 'first_name', true );
		$user_data['last_name']   = get_user_meta( $user_id, 'last_name', true );
		$user_data['description'] = get_user_meta( $user_id, 'description', true );
		$user_data['nickname']    = get_user_meta( $user_id, 'nickname', true );
		$user_data                = array_merge( $user_data, $user_extra_fields );

		$form_field_data_array = user_registration_pro_profile_details_form_fields( $form_id );
		$user_data_to_show     = user_registration_pro_profile_details_form_field_datas( $form_id, $user_data, $form_field_data_array );

		if ( null !== get_option( 'user_registration_pro_general_post_submission_settings' ) ) {
			$url = get_option( 'user_registration_pro_general_post_submission_settings' );

			$single_field = array();
			foreach ( $user_data_to_show as $key => $data ) {
				if ( 'password' === $data['field_key'] ) {
					continue;
				} elseif ( 'privacy_policy' === $data['field_key'] ) {
					$single_field[ $key ] = ( isset( $data['value'] ) && $data['value'] ) ? 'Accepted' : 'Not Accepted';
				} else {
					$single_field[ $key ] = isset( $data['value'] ) ? $data['value'] : '';
				}
			}

			if ( 'post_json' === get_option( 'user_registration_pro_general_setting_post_submission', array() ) ) {
				$headers = array( 'Content-Type' => 'application/json; charset=utf-8' );
				wp_remote_post(
					$url,
					array(
						'body'    => json_encode( $single_field ),
						'headers' => $headers,
					)
				);
			} elseif ( 'get' === get_option( 'user_registration_pro_general_setting_post_submission', array() ) ) {
				$url = $url . '?' . http_build_query( $single_field );
				wp_remote_get( $url );
			} else {
				wp_remote_post( $url, array( 'body' => $single_field ) );
			}
		}
	}
}

if ( ! function_exists( 'ur_send_form_data_on_email_confirmation_success' ) ) {

	/**
	 * Send form data to custom url after email confirmation hook.
	 *
	 * @param  int $user_id         User ID.
	 * @return void
	 */
	function ur_send_form_data_on_email_confirmation_success( $user_id ) {

		$form_id = ur_get_form_id_by_userid( $user_id );

		ur_send_form_data_on_profile_update( $user_id, $form_id );
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

		$roles = array( 'administrator' );
		if ( array_intersect( $roles, $user->roles ) ) {
			return $user;
		}

		// Check prevent active login.
		$user = user_registration_prevent_active_login( $user );

		return $user;
	}
}

if ( ! function_exists( 'user_registration_prevent_active_login' ) ) {
	/**
	 * Validate if the maximum active logins limit reached.
	 *
	 * @param object $user User Object/WPError.
	 *
	 * @since  3.0.0
	 *
	 * @return object User object or error object.
	 */
	function user_registration_prevent_active_login( $user ) {

		$pass                = isset( $_POST['password'] ) ? $_POST['password'] : '';
		$ur_max_active_login = intval( get_option( 'user_registration_pro_general_setting_limited_login' ) );
		$user_id             = $user->ID;

		// Get current user's session.
		$sessions = WP_Session_Tokens::get_instance( $user_id );

		// Get all his active WordPress sessions.
		$all_sessions = $sessions->get_all();
		$count        = count( $all_sessions );

		if ( ! empty( $pass ) ) {
			if ( $count >= $ur_max_active_login && wp_check_password( $pass, $user->user_pass, $user->ID ) ) {
				$user_id    = $user->ID;
				$user_email = $user->user_email;

				// Error message.
				$error_message = sprintf(
					'<strong>' .
						/* translators: %s Logout link */
						__( 'ERROR:', 'user-registration' ) . '</strong>' . __( 'Maximum no. of active logins found for this account. Please logout from another device to continue. %s', 'user-registration' ),
					"<a href='javascript:void(0)' class='user-registartion-force-logout' data-user-id='" . $user_id . "' data-email='" . $user_email . "'>" . __( 'Force Logout?', 'user-registration' ) . '</a>'
				);

				return new WP_Error( 'user_registration_error_message', $error_message );
			}
			return $user;
		} elseif ( $count >= $ur_max_active_login ) {
			$user_id    = $user->ID;
			$user_email = $user->user_email;

			// Error message.
			$error_message = sprintf(
				'<strong>' .
					/* translators: %s Logout link */
					__( 'ERROR:', 'user-registration' ) . '</strong>' . __( 'Maximum no. of active logins found for this account. Please logout from another device to continue. %s', 'user-registration' ),
				"<a href='javascript:void(0)' class='user-registartion-force-logout' data-user-id='" . $user_id . "' data-email='" . $user_email . "'>" . __( 'Force Logout?', 'user-registration' ) . '</a>'
			);
			throw new Exception( $error_message );
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
	function user_registration_force_logout() {

		if ( ! empty( $_GET['force-logout'] ) ) {
			$user_id  = intval( $_GET['force-logout'] );
			$sessions = WP_Session_Tokens::get_instance( $user_id );
			$sessions->destroy_all();
			wp_redirect( ur_get_page_permalink( 'myaccount' ) );
			exit;
		}
	}
}

if ( ! function_exists( 'user_registration_get_all_db_tables' ) ) {
	/**
	 * Get All Database Table List.
	 */
	function user_registration_get_all_db_tables() {
		global $wpdb;
		$results    = $wpdb->get_results( 'SHOW TABLES;', ARRAY_N );
		$tables     = array();
		$tables[''] = __( '-- Select Table Name --', 'user-registration' );
		foreach ( $results as $result ) {
			$tables[ $result[0] ] = $result[0];
		}
		return $tables;
	}
}

if ( ! function_exists( 'user_registration_get_columns_by_table' ) ) {

	/**
	 * Get list of Columns for specific table.
	 *
	 * @param string $table Table Name.
	 */
	function user_registration_get_columns_by_table( $table ) {
		global $wpdb;
		$column_list = array();
		if ( ! empty( $table ) && '0' != $table ) {
			$columns = $wpdb->get_results('SHOW COLUMNS FROM ' . $table); //phpcs:ignore

			foreach ( $columns as $key => $column ) {
				$column_list[$column->Field] = $column->Field; //phpcs:ignore;
			}
		}
		return $column_list;
	}
}


if ( ! function_exists( 'user_registration_pro_auto_populate_supported_fields' ) ) {
	/**
	 * Get fields for which auto populate is supported
	 *
	 * @return array
	 */
	function user_registration_pro_auto_populate_supported_fields() {

		$fields = array(
			'display_name',
			'checkbox',
			'country',
			'date',
			'description',
			'email',
			'first_name',
			'last_name',
			'nickname',
			'number',
			'password',
			'radio',
			'select',
			'text',
			'textarea',
			'user_email',
			'user_login',
			'user_url',
			'invite_code',
		);

		return apply_filters(
			'user_registration_auto_populate_fields',
			$fields
		);
	}
}

if ( ! function_exists( 'user_registration_pro_pattern_validation_fields' ) ) {
	/**
	 * Get fields for which pattern validation is supported
	 *
	 * @return array
	 */
	function user_registration_pro_pattern_validation_fields() {

		$fields = array(
			'phone',
			'display_name',
			'date',
			'email',
			'first_name',
			'last_name',
			'nickname',
			'number',
			'password',
			'text',
			'user_login',
			'user_pass',
			'user_url',
			'custom_url',
		);

		return apply_filters(
			'user_registration_pattern_validation_fields',
			$fields
		);
	}
}

if ( ! function_exists( 'user_registration_pro_blacklist_words_fields' ) ) {
	/**
	 * Get fields for which blacklist words is supported
	 *
	 * @return array
	 */
	function user_registration_pro_blacklist_words_fields() {

		$fields = array(
			'text',
			'textarea',
			'description',
			'nickname',
			'user_login',
			'last_name',
			'first_name',
		);

		return apply_filters(
			'user_registration_blacklist_words_fields',
			$fields
		);
	}
}

if ( ! function_exists( 'user_registration_pro_profile_details_form_fields' ) ) {

	/**
	 * Get the user registration form fields to include in view profile.
	 *
	 * @param int   $form_id Id of the form through which user was registered.
	 * @param array $fields_to_include Fields to include.
	 * @return array
	 */
	function user_registration_pro_profile_details_form_fields( $form_id, $fields_to_include = array() ) {

		$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

		$form_field_data_array = array();
		foreach ( $post_content_array as $row_index => $row ) {
			foreach ( $row as $grid_index => $grid ) {
				foreach ( $grid as $field_index => $field ) {
					if ( isset( $field->general_setting->field_name ) ) {
						$field->field_key = isset( $field->field_key ) ? $field->field_key : '';
						$form_field_data_array[ $field->general_setting->field_name ] = array(
							'field_key' => $field->field_key,
							'label'     => $field->general_setting->label,
						);
						if ( in_array( $field->field_key, $fields_to_include ) ) {
							$form_field_data_array[ $field->general_setting->field_name ] = array(
								'field_key' => $field->field_key,
								'label'     => $field->general_setting->label,
							);
						}
					}
				}
			}
		}

		return $form_field_data_array;
	}
}

if ( ! function_exists( 'user_registration_pro_profile_details_form_field_datas' ) ) {

	/**
	 * Get the user registration form fields data for fields included in view profile.
	 *
	 * @param int   $form_id Id of the form through which user was registered.
	 * @param array $user_data All the datas of the user.
	 * @param array $form_field_data_array All the fields to be included in profile details page.
	 * @param array $field_to_include Field to include.
	 * @return array
	 */
	function user_registration_pro_profile_details_form_field_datas( $form_id, $user_data, $form_field_data_array, $field_to_include = array() ) {

		$user_data_to_show = array();
		foreach ( $user_data as $key => $value ) {

			if ( ! empty( $field_to_include ) && ! in_array( $key, $field_to_include ) ) {
				continue;
			}

			if ( isset( $form_field_data_array[ $key ] ) && '' !== $value ) {

				$user_data_to_show[ $key ] = array(
					'field_key' => $form_field_data_array[ $key ]['field_key'],
					'label'     => $form_field_data_array[ $key ]['label'],
					'value'     => $value,
				);
			}

			$fields_to_exclude = array_merge( ur_exclude_profile_details_fields(), apply_filters( 'user_registration_pro_excluded_fields_in_view_details_page', array( 'profile_picture', 'privacy_policy', 'password' ) ) );

			if ( isset( $user_data_to_show[ $key ]['field_key'] ) ) {
				if ( 'file' === $user_data_to_show[ $key ]['field_key'] && '' !== $user_data_to_show[ $key ]['value'] ) {
					$upload_data = array();
					$file_data   = is_string( $value ) ? explode( ',', $value ) : $value;

					foreach ( $file_data as $attachment_key => $attachment_id ) {
						$file      = isset( $attachment_id ) ? wp_get_attachment_url( $attachment_id ) : '';
						$file_link = '<a href="' . esc_url( $file ) . '" rel="noreferrer noopener" target="_blank" >' . esc_html( basename( get_attached_file( $attachment_id ) ) ) . '</a>';
						$file_link = apply_filters( 'user_registration_membership_frontend_listing_file_link', $file_link, $attachment_id );
						array_push( $upload_data, $file_link );
					}
					// Check if value contains array.
					if ( is_array( $upload_data ) ) {
						$value = implode( ',', $upload_data );
					}

					$user_data_to_show[ $key ]['value'] = $value;
				}

				// For Country Field.
				if ( 'country' === $user_data_to_show[ $key ]['field_key'] && '' !== $user_data_to_show[ $key ]['value'] ) {
					$country_class                      = ur_load_form_field_class( $user_data_to_show[ $key ]['field_key'] );
					$countries                          = $country_class::get_instance()->get_country();
					$user_data_to_show[ $key ]['value'] = isset( $countries[ $value ] ) ? $countries[ $value ] : $value;
				}

				// For checkbox and multiselect field.
				if ( ( 'checkbox' === $user_data_to_show[ $key ]['field_key'] || 'multi_select2' === $user_data_to_show[ $key ]['field_key'] ) && '' !== $user_data_to_show[ $key ]['value'] ) {
					$user_data_to_show[ $key ]['value'] = is_array( $user_data_to_show[ $key ]['value'] ) ? implode( ',', $user_data_to_show[ $key ]['value'] ) : $user_data_to_show[ $key ]['value'];
				}

				if ( in_array( $key, $fields_to_exclude ) ) {
					unset( $user_data_to_show[ $key ] );
				}
			}
		}

		return $user_data_to_show;
	}
}

if ( ! function_exists( 'user_registration_pro_profile_details_form_keys_to_include' ) ) {

	/**
	 * Get the user registration form fields keys of fields to include in view profile.
	 *
	 * @param array $fields_to_include Field to include.
	 * @param array $form_field_data_array All the fields to be included in profile details page.
	 * @return array
	 */
	function user_registration_pro_profile_details_form_keys_to_include( $fields_to_include, $form_field_data_array ) {
		$fields_keys_to_include = array();

		foreach ( $form_field_data_array as $field_id => $field_data ) {
			if ( in_array( $field_data['field_key'], $fields_to_include ) || in_array( $field_id, $fields_to_include ) ) {
				array_push( $fields_keys_to_include, $field_id );
			}
		}
		return $fields_keys_to_include;
	}
}

/**
 * UR Validate Unique Field.
 */
if ( ! function_exists( 'ur_validate_unique_field' ) ) {
	/**
	 *  Validate unique field value.
	 *
	 * @param array $args search args.
	 * @return array $ids ids.
	 */
	function ur_validate_unique_field( $args ) {
		global $wpdb;
		$args    = wp_parse_args(
			$args,
			array(
				'limit'      => 10,
				'ur_form_id' => 0,
				'offset'     => 0,
				'order'      => 'DESC',
				'orderby'    => 'ID',
				'meta_query' => array(
					'key'   => 'ur_form_id',
					'value' => $args['ur_form_id'],
				),
			)
		);
		$query   = array();
		$query[] = "SELECT DISTINCT {$wpdb->prefix}usermeta.user_id FROM {$wpdb->prefix}usermeta INNER JOIN {$wpdb->prefix}users WHERE {$wpdb->prefix}usermeta.user_id = {$wpdb->prefix}users.ID";

		if ( ! empty( $args['search'] ) ) {
			if ( 'user_url' === $args['field_name'] ) {
				$query[] = $wpdb->prepare( 'AND user_url = %s', $args['search'] );
			} elseif ( 'display_name' === $args['field_name'] ) {
				$query[] = $wpdb->prepare( 'AND display_name = %s', $args['search'] );
			} else {
				$query[] = $wpdb->prepare( 'AND meta_key = %s AND meta_value = %s', $args['field_name'], $args['search'] );
			}
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( implode( ' ', $query ), ARRAY_A );
		$ids     = wp_list_pluck( $results, 'user_id' );
		return $ids;
	}
}

if ( ! function_exists( 'ur_pro_add_bulk_options' ) ) {
	/**
	 * Bulk Add Options.
	 *
	 * @param array  $general_setting General Setting.
	 * @param string $id ID.
	 * @return array
	 */
	function ur_pro_add_bulk_options( $general_setting, $id ) {
		$fields = array( 'user_registration_checkbox', 'user_registration_select', 'user_registration_radio' );

		if ( in_array( $id, $fields, true ) ) {
			$general_setting['options'] = array_merge( $general_setting['options'], array( 'add_bulk_options' => sprintf( '<a href="#" class="ur-toggle-bulk-options after-label-description" data-bulk-options-label="%s" data-bulk-options-tip="%s" data-bulk-options-button="%s">%s</a>', esc_attr__( 'Add Bulk Options', 'user-registration' ), esc_attr__( 'To add multiple options at once, press enter key after each option.', 'user-registration' ), esc_attr__( 'Add New Options', 'user-registration' ), esc_html__( 'Bulk Add', 'user-registration' ) ) ) );
		}

		return $general_setting;
	}
}

add_filter( 'user_registration_field_options_general_settings', 'ur_pro_add_bulk_options', 10, 2 );

if ( ! function_exists( 'ur_get_all_form_fields' ) ) {

	/**
	 * Get all the form Fields.
	 *
	 * @param array $strip_fields Stripe Fields.
	 * @return array $form_field_lists Form Field List .
	 */
	function ur_get_all_form_fields( $strip_fields ) {
		$all_forms        = ur_get_all_user_registration_form();
		$form_field_lists = array();

		foreach ( $all_forms as $form_id => $form_label ) {
			$post                                   = get_post( $form_id );
			$post_content                           = isset( $post->post_content ) ? $post->post_content : '';
			$post_content_array                     = isset( $post_content ) ? json_decode( $post_content ) : array();
			$specific_form_field_list               = array();
			$specific_form_field_list['form_label'] = $form_label;
			if ( is_array( $post_content_array ) || is_object( $post_content_array ) ) {
				foreach ( $post_content_array as $post_content_row ) {
					foreach ( $post_content_row as $post_content_grid ) {
						foreach ( $post_content_grid as $field ) {
							if ( isset( $field->field_key ) && isset( $field->general_setting->field_name ) ) {
								if ( in_array( $field->field_key, $strip_fields, true ) ) {
									continue;
								}
								$specific_form_field_list['field_list'][ $field->general_setting->field_name ] = $field->general_setting->label;
								$specific_form_field_list['field_key'][ $field->general_setting->field_name ]  = $field->field_key;
							}
						}
					}
				}
			}
			array_push( $form_field_lists, $specific_form_field_list );
		}

		return $form_field_lists;
	}
}

add_action( 'user_registration_after_account_privacy', 'user_registration_after_account_privacy', 10, 2 );

if ( ! function_exists( 'user_registration_after_account_privacy' ) ) {
	/**
	 * Download and erase personal data in privacy tab.
	 *
	 * @param string $enable_download_personal_data download personal data.
	 * @param string $enable_erase_personal_data erase personal data.
	 */
	function user_registration_after_account_privacy( $enable_download_personal_data, $enable_erase_personal_data ) {
		global $wpdb;
		$user_id = get_current_user_id();
		if ( ur_string_to_bool( $enable_download_personal_data ) ) :
			?>

			<div class="user-registration-form-row user-registration-form-row--wide form-row form-row-wide ur-about-your-data">
				<div class="ur-privacy-field-label">
					<label>
						<?php esc_html_e( 'About Your Data', 'user-registration' ); ?>
						<span class='ur-portal-tooltip tooltipstered' data-tip="
				<?php
				esc_html_e(
					'Download or erase all of your personal data from the site by requesting to the site\'s admin.',
					'user-registration'
				)
				?>
				"></span>
					</label>
				</div>
				<div class="ur-about-your-data-input">
					<div class="ur-field ur-field-export_data">
						<?php
						$hide_download_input = '';
						$completed           = $wpdb->get_row(
							$wpdb->prepare(
								"SELECT ID
				FROM $wpdb->posts
				WHERE post_author = %d AND
					post_type = 'user_request' AND
					post_name = 'export_personal_data' AND
					post_status = 'request-completed'
				ORDER BY ID DESC
				LIMIT 1",
								$user_id
							),
							ARRAY_A
						);

						$pending = $wpdb->get_row(
							$wpdb->prepare(
								"SELECT ID, post_status
				FROM $wpdb->posts
				WHERE post_author = %d AND
					post_type = 'user_request' AND
					post_name = 'export_personal_data' AND
					post_status != 'request-completed'
				ORDER BY ID DESC
				LIMIT 1",
								$user_id
							),
							ARRAY_A
						);

						if ( ! empty( $completed ) && empty( $pending ) ) {
							$hide_download_input = 'none';
							$exports_url         = wp_privacy_exports_url();
							echo "<div class='ur-download-personal-data'>";
							echo '<h3>' . esc_html__( 'Download Your Data', 'user-registration' ) . '</h3>';
							echo '<p>' . esc_html__( 'You could download your previous data as your download request is approved', 'user-registration' ) . '</p>';
							echo '<div class="ur-privacy-action-btn">';
							echo '<a class="ur-button" href="' . esc_attr( $exports_url . get_post_meta( $completed['ID'], '_export_file_name', true ) ) . '">' . esc_html__( 'Download Personal Data', 'user-registraton' ) . '</a>';
							echo '<a  id ="ur-new-download-request" javascript:void(0) href="#">' . esc_html__( 'New Download Request', 'user-registration' ) . '</a>';
							echo '</div>';
							echo '</div>';
						}

						if ( ! empty( $pending ) && 'request-confirmed' === $pending['post_status'] ) {
							echo '<div class="ur-download-personal-data-request-confirmed">';
							echo '<h3>' . esc_html__( 'Download Your Data', 'user-registration' ) . '</h3>';
							echo '<p>' . esc_html__( 'The administrator has not yet approved downloading the data. Please wait for approval.', 'user-registration' ) . '</p>';
							echo '</div>';
						} else {
							?>
							<div id="ur-download-personal-data-request-input" class="ur-download-personal-data-request-input"
								style="display:<?php echo esc_attr( $hide_download_input ); ?>">
								<label name="ur-export-data">
									<?php esc_html_e( 'Enter your current password to download your data.', 'user-registration' ); ?>
								</label>
								<div class="ur-field-area">
									<input id="ur-export-data" type="password"
										placeholder="<?php esc_attr_e( 'Password', 'user-registration' ); ?>">
									<div class="ur-field-error ur-export-data" style="display:none">
										<span class="ur-field-arrow"><i
												class="ur-faicon-caret-up"></i></span><?php esc_html_e( 'You must enter a password', 'user-registration' ); ?>
									</div>
									<div class="ur-field-area-response ur-export-data"></div>
								</div>

								<button type="button" class="ur-request-button ur-export-data-button" data-action="ur-export-data">
									<?php esc_html_e( 'Send Download Request', 'user-registration' ); ?>
								</button>
							</div>
						<?php } ?>

					</div>
				<?php
			endif;
		if ( ur_string_to_bool( $enable_erase_personal_data ) ) :
			?>
					<div class="ur-field ur-field-export_data">
					<?php
					$hide_erase_input = '';
					$completed        = $wpdb->get_row(
						$wpdb->prepare(
							"SELECT ID
					FROM $wpdb->posts
					WHERE post_author = %d AND
						post_type = 'user_request' AND
						post_name = 'remove_personal_data' AND
						post_status = 'request-completed'
					ORDER BY ID DESC
					LIMIT 1",
							$user_id
						),
						ARRAY_A
					);

					$pending = $wpdb->get_row(
						$wpdb->prepare(
							"SELECT ID, post_status
				FROM $wpdb->posts
				WHERE post_author = %d AND
					post_type = 'user_request' AND
					post_name = 'remove_personal_data' AND
					post_status != 'request-completed'
				ORDER BY ID DESC
				LIMIT 1",
							$user_id
						),
						ARRAY_A
					);
					if ( ! empty( $completed ) && empty( $pending ) ) {
						$hide_erase_input = 'none';
						echo "<div class='ur-erase-personal-data'>";
						echo '<h3>' . esc_html__( 'Erase Of Your Data', 'user-registration' ) . '</h3>';
						echo '<p>' . esc_html__( 'Your personal data has been deleted as per your request.', 'user-registration' ) . '</p>';
						echo '<div class="ur-privacy-action-btn">';
						echo '<a  id ="ur-new-erase-request" javascript:void(0) href="#">' . esc_html__( 'New Erase Request', 'user-registration' ) . '</a>';
						echo '</div>';
						echo '</div>';
					}

					if ( ! empty( $pending ) && 'request-confirmed' === $pending['post_status'] ) {
						echo '<div class="ur-erase-personal-data-request-confirmed">';
						echo '<h3>' . esc_html__( 'Erase Of Your Data', 'user-registration' ) . '</h3>';
						echo '<p>' . esc_html__( 'The administrator has not yet approved deleting your data. Please wait for approval.', 'user-registration' ) . '</p>';
						echo '</div>';
					} else {
						?>
							<div id="ur-erase-personal-data-request-input" class="ur-download-personal-data-request-input"
								style="display:<?php echo esc_attr( $hide_erase_input ); ?>">
								<label name="ur-erase-data">
								<?php esc_html_e( 'Enter your current password to erasure your personal data.', 'user-registration' ); ?>
								</label>

								<div class="ur-field-area">
									<input id="ur-erase-data" type="password"
										placeholder="<?php esc_attr_e( 'Password', 'user-registration' ); ?>">
									<div class="ur-field-error ur-erase-data" style="display:none">
										<span class="ur-field-arrow"><i
												class="ur-faicon-caret-up"></i></span><?php esc_html_e( 'You must enter a password', 'user-registrationon' ); ?>
									</div>
									<div class="ur-field-area-response ur-erase-data"></div>
								</div>

								<button class="ur-request-button ur-erase-data-button" data-action="ur-erase-data">
								<?php esc_html_e( 'Send Erase Request', 'user-registration' ); ?>
								</button>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php
			endif;
	}
}

	add_action( 'wp_head', 'ur_profile_dynamic_meta_desc', 20 );

if ( ! function_exists( 'ur_profile_dynamic_meta_desc' ) ) {
	/**
	 * Adding non indexing tag in user page.
	 */
	function ur_profile_dynamic_meta_desc() {
		$privacy_tab_enable      = get_option( 'user_registration_enable_privacy_tab', false );
		$enable_profile_indexing = get_option( 'user_registration_enable_profile_indexing', true );

		if ( ur_string_to_bool( $privacy_tab_enable ) && ur_string_to_bool( $enable_profile_indexing ) ) {

			if ( isset( $_GET['user_id'] ) && isset( $_GET['list_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
				$user_id  = sanitize_key( wp_unslash( $_GET['user_id'] ) ); //phpcs:ignore WordPress.Security.NonceVerification
				$nonindex = ur_string_to_bool( get_user_meta( $user_id, 'ur_profile_noindex', true ) );

				if ( $nonindex ) {
					echo '<meta name="robots" content="noindex, nofollow" />';
				}
			}
		}
	}
}

if ( ! function_exists( 'user_registration_pro_passwordless_login_process' ) ) {
	/**
	 * Passwordless login process.
	 *
	 * @param array        $post_data Login data.
	 * @param string|email $username Username or Email.
	 * @param string       $nonce Nonce value.
	 * @param array        $error_messages Custom error messages.
	 *
	 * @throws Exception Login errors.
	 */
	function user_registration_pro_passwordless_login_process( $post_data, $username, $nonce, $error_messages ) {
		// Check if passwordless login is enabled and conditions are met
		if (
			ur_is_passwordless_login_enabled() &&
			( ( isset( $_GET['pl'] ) && 'true' === $_GET['pl'] && ! isset( $post_data['password'] ) ) ||
				ur_is_user_registration_pro_passwordless_login_default_login_area_enabled() )
		) {

			// Validate user login
			$valid_email = user_registration_pro_validate_user_login( $username, $error_messages );

			if ( is_wp_error( $valid_email ) ) {
				throw new Exception( $valid_email->get_error_message() );
			}

			// Get user by email
			$user         = get_user_by( 'email', $valid_email );
			$redirect_url = get_home_url();

			// Determine redirect URL based on user role and post data
			if ( in_array( 'administrator', $user->roles, true ) && 'yes' === get_option( 'user_registration_login_options_prevent_core_login', 'no' ) ) {
				$redirect_url = admin_url();
			} elseif ( ! empty( $post_data['redirect'] ) ) {
				$redirect_url = esc_url_raw( wp_unslash( $post_data['redirect'] ) );
			} elseif ( wp_get_raw_referer() ) {
				$redirect_url = wp_get_raw_referer();
			}

			// Send magic login link email
			$status = user_registration_pro_send_magic_login_link_email( $valid_email, $nonce, $redirect_url );
			if ( $status ) {
				unset( $_POST['username'] ); // phpcs:ignore WordPress.Security.NonceVerification
				$success_message = apply_filters( 'user_registration_passwordless_login_success', __( 'A secure login link has been sent to your email address, it will expire in 1 hour.', 'user-registration' ) );

				throw new Exception( $success_message, 200 );
			}

			// Handle email sending failure
			$error_message = apply_filters( 'user_registration_passwordless_login_failed', __( 'There was a problem sending your email. Please try again or contact an administrator.', 'user-registration' ) );

			throw new Exception( $error_message );
		}
	}
}

	add_action( 'user_registration_login_process_before_username_validation', 'user_registration_pro_passwordless_login_process', 10, 4 );

if ( ! function_exists( 'user_registration_pro_validate_user_login' ) ) {
	/**
	 * Checks whether the username or email is valid or not.
	 *
	 * @param email|string $user_login Username or Email.
	 * @param array        $error_messages Custom error messages.
	 * @return email|WP_Error
	 */
	function user_registration_pro_validate_user_login( $user_login, $error_messages ) {

		if ( empty( $user_login ) ) {
			return new WP_Error( 'empty_username', ! empty( $error_messages['empty_username'] ) ? $error_messages['empty_username'] : __( 'The username or email field is empty.', 'user-registration' ) );
		}

		// Check if the entered value is a valid email address.
		$user = null;
		if ( is_email( $user_login ) ) {
			$user = get_user_by( 'email', $user_login );
		} else {
			$user = get_user_by( 'login', $user_login );
		}

		// Check the prevent active login.
		if ( ur_string_to_bool( get_option( 'user_registration_pro_general_setting_prevent_active_login', false ) ) ) {
			user_registration_prevent_active_login( $user );
		}

		if ( ! $user ) {
			return new WP_Error( 'unknown_email', ! empty( $error_messages['unknown_email'] ) ? $error_messages['unknown_email'] : __( 'The username or email you provided do not exist.', 'user-registration' ) );
		}

		if ( class_exists( 'UR_User_Approval' ) ) {
			$user_approval = new UR_User_Approval();

			$user = $user_approval->check_status_on_login( $user, '' );

			if ( is_wp_error( $user ) ) {
				$error_messages = $user->get_error_messages();
				if ( isset( $error_messages[0] ) ) {
					return new WP_Error( 'pending_approval', $error_messages[0] );
				}
			}

			// when user status is approved.
			if ( is_email( $user_login ) && email_exists( $user_login ) ) {
				return $user_login;
			}

			if ( ! is_email( $user_login ) && username_exists( $user_login ) ) {
				$user = get_user_by( 'login', $user_login );
				if ( $user ) {
					return $user->get( 'user_email' );
				}
			}
		}

		return new WP_Error( 'unknown_email', ! empty( $error_messages['unknown_email'] ) ? $error_messages['unknown_email'] : __( 'The username or email you provided do not exist.', 'user-registration' ) );
	}
}

if ( ! function_exists( 'user_registration_pro_generate_magic_login_link' ) ) {
	/**
	 * Generates a one-time use magic link for passwordless login and returns the link URL.
	 *
	 * @param string $email The email address of the user.
	 *
	 * @param string $nonce The nonce for the link.
	 * @param string $redirect_url The redirect URL.
	 *
	 * @return string The URL for the one-time use magic link.
	 */
	function user_registration_pro_generate_magic_login_link( $email, $nonce, $redirect_url ) {
		$user  = get_user_by( 'email', $email );
		$token = ur_generate_onetime_token( $user->ID, 'ur_passwordless_login', 32, 60 );

		update_user_meta( $user->ID, 'ur_passwordless_login_redirect_url' . $user->ID, $redirect_url );

		$arr_params = array( 'action', 'uid', 'token', 'nonce' );
		$url        = remove_query_arg( $arr_params, ur_get_my_account_url() );

		$url_params = array(
			'uid'   => $user->ID,
			'token' => $token,
			'nonce' => $nonce,
		);

		$url = add_query_arg( $url_params, $url );

		return $url;
	}
}

if ( ! function_exists( 'user_registration_pro_send_magic_login_link_email' ) ) {
	/**
	 * Sends a magic login link email to the user.
	 * Generates a magic link URL and sends an email to the user with the link to log in without a password.
	 *
	 * @param string $email The email address of the user.
	 * @param string $nonce The nonce string to verify the request.
	 * @param string $redirect_url The redirect URL.
	 * @return bool True if the email was sent successfully, false otherwise.
	 */
	function user_registration_pro_send_magic_login_link_email( $email, $nonce, $redirect_url ) {
		include __DIR__ . '/admin/settings/emails/class-ur-settings-passwordless-login-email.php';
		$settings = new UR_Settings_Passwordless_Login_Email();

		$blog_name   = esc_attr( get_bloginfo( 'name' ) );
		$user        = get_user_by( 'email', $email );
		$form_id     = ur_get_form_id_by_userid( $user->ID );
		$template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );

		$passwordless_login_link = user_registration_pro_generate_magic_login_link( $email, $nonce, $redirect_url );

		$values = array(
			'username'                => $user->data->user_nicename,
			'email'                   => $email,
			'passwordless_login_link' => $passwordless_login_link,
			'form_id'                 => $form_id,
		);

		$subject = sprintf( __( 'Login at %s', 'user-registration' ), $blog_name );
		$subject = get_option( 'user_registration_passwordless_login_email_subject', $subject );
		$subject = apply_filters( 'ur_password_less_login_email_subject', $subject, $blog_name );

		$message = $settings->ur_get_passwordless_login_email();
		$message = get_option( 'user_registration_passwordless_login_email_content', $message );
		$message = apply_filters( 'ur_magic_login_link_email_message', $message, $email, $passwordless_login_link );

		$headers = apply_filters( 'ur_password_less_login_email_headers', UR_Emailer::ur_get_header(), $passwordless_login_link, $email );

		if ( ur_option_checked( 'uret_override_passwordless_login_email', true ) ) {
			[$message, $subject] = user_registration_email_content_overrider( $form_id, $settings, $message, $subject );
		}

		$message = UR_Emailer::parse_smart_tags( $message, $values, array() );
		$subject = UR_Emailer::parse_smart_tags( $subject, $values, array() );

		$message = user_registration_process_email_content( $message, $template_id );

		if ( ur_is_passwordless_login_enabled() ) {
			$mail = UR_Emailer::user_registration_process_and_send_email( $email, $subject, $message, $headers, '', $template_id );
		}

		return $mail;
	}
}

if ( ! function_exists( 'user_registration_pro_login_via_magic_link_url' ) ) {
	/**
	 * Handles the login process via a magic link.
	 *
	 * @return void
	 */
	function user_registration_pro_login_via_magic_link_url() {

		if ( ! isset( $_GET['token'] ) || ! isset( $_GET['uid'] ) || ! isset( $_GET['nonce'] ) ) {
			return;
		}

		$uid              = isset( $_GET['uid'] ) ? absint( $_GET['uid'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$confirm_token    = isset( $_GET['token'] ) ? sanitize_key( $_GET['token'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$nonce            = isset( $_GET['nonce'] ) ? sanitize_key( $_GET['nonce'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$arr_params       = array( 'uid', 'token', 'nonce' );
		$current_page_url = remove_query_arg( $arr_params, ur_get_current_page_url() );

		if ( ! $uid || ! $confirm_token || ! $nonce ) {
			wp_safe_redirect( $current_page_url );
			exit;
		}

		$stored_key   = get_user_meta( $uid, 'ur_passwordless_login_token' . $uid, true );
		$expiration   = get_user_meta( $uid, 'ur_passwordless_login_token_expiration' . $uid, true );
		$redirect_url = get_user_meta( $uid, 'ur_passwordless_login_redirect_url' . $uid, true );

		if ( ! $stored_key || ! $expiration ) {
			wp_safe_redirect( $current_page_url );
			exit;
		}

		if ( time() > $expiration || $confirm_token !== $stored_key || ! ( wp_verify_nonce( $nonce, 'ur_login_form_save_nonce' ) || wp_verify_nonce( $nonce, 'user-registration-login' ) ) ) {
			wp_safe_redirect( $current_page_url );
			exit;
		}

		wp_set_auth_cookie( $uid );

		delete_user_meta( $uid, 'ur_passwordless_login_token' . $uid );
		delete_user_meta( $uid, 'ur_passwordless_login_token_expiration' . $uid );
		delete_user_meta( $uid, 'ur_passwordless_login_redirect_url' . $uid );

		do_action( 'user_registration_passwordless_login_success', $uid );

		wp_redirect( apply_filters( 'user_registration_after_passwordless_login_redirect', $redirect_url ) );
		exit;
	}
}
	add_action( 'template_redirect', 'user_registration_pro_login_via_magic_link_url' );

if ( ! function_exists( 'ur_integration_settings_template' ) ) {
	/**
	 * Return Template for Email Marketing Integration.
	 *
	 * @param object $integration Integration.
	 */
	function ur_integration_settings_template( $integration ) {

		$settings  = '<div class="ur-export-users-page">';
		$settings .= '<div class="nav-tab-content">';
		$settings .= '<div class="nav-tab-inside">';
		$settings .= '<div class="' . $integration->id . '-wrapper">';
		$settings .= '<div id="' . $integration->id . '_div" class="postbox">';
		$settings .= '<h3 class="hndle"> ' . esc_html__( 'Accounts Settings', 'user-registration' ) . '</h3>';
		$settings .= '<div class="inside">';
		$settings .= '<div class="ur-form-row">';

		if ( 'activecampaign' === $integration->id ) {
			$settings .= '<div class="ur-form-group">';
			$settings .= '<label class="ur-label">' . esc_html__( 'ActiveCampaign URL', 'user-registration' ) . '</label>';
			$settings .= '<input type="text" name="ur_activecampaign_url" id="ur_activecampaign_url" placeholder="' . esc_attr__( 'Enter the ActiveCampaign URL', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings .= '</div>';
		}

		if ( 'sms_integration' === $integration->id ) {
			$settings          .= '<div class="ur-form-group">';
			$settings          .= '<label class="ur-label">' . esc_html__( 'Number From', 'user-registration' ) . '</label>';
			$settings          .= '<input type="text" name="ur_twilio_client_number" id="ur_twilio_client_number" placeholder="' . esc_attr__( 'Enter Twilio Number.', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings          .= '</div>';
			$settings          .= '<div class="ur-form-group">';
			$settings          .= '<label class="ur-label">' . esc_html__( 'Account SID', 'user-registration' ) . '</label>';
			$settings          .= '<input type="text" name="ur_twilio_client_id" id="ur_twilio_client_id" placeholder="' . esc_attr__( 'Enter Twilio Account SID.', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings          .= '</div>';
			$settings          .= '<div class="ur-form-group">';
			$settings          .= '<label class="ur-label">' . esc_html__( 'Auth Token', 'user-registration' ) . '</label>';
			$settings          .= '<input type="text" name="ur_twilio_client_auth" id="ur_twilio_client_auth" placeholder="' . esc_attr__( 'Enter Twilio API Auth Code.', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings          .= '</div>';
			$settings          .= '<div class="publishing-action">';
			$settings          .= '<button type="button" class="button button-primary ur_' . $integration->id . '_account_action_button" name="user_registration_' . $integration->id . '_account"> ' . esc_attr__( 'Connect', 'user-registration' ) . '</button>';
			$settings          .= '</div>';
			$connected_accounts = get_option( 'ur_' . $integration->id . '_accounts', array() );
		}

		if ( 'google_sheets' === $integration->id ) {
			$is_disabled   = '';
			$is_hidden     = '';
			$verify_button = 'none';

			$connected_accounts_gs = get_option( 'ur_' . $integration->id . '_accounts', array() );
			if ( ! empty( $connected_accounts_gs ) ) {
				foreach ( $connected_accounts_gs as $account ) {
					if ( ! empty( $account['refresh_token'] ) || ! empty( $account['access_token'] ) ) {
						$is_hidden = 'none';
						break;
					}
				}
			}

			if ( isset( $_GET['code'] ) && isset( $_GET['scope'] ) && ! empty( $_GET['code'] ) && ! empty( $_GET['scope'] ) ) {

				$scopes = explode( ' ', $_GET['scope'] );

				if (
					in_array( 'https://www.googleapis.com/auth/spreadsheets', $scopes ) &&
					in_array( 'https://www.googleapis.com/auth/userinfo.email', $scopes )
				) {
					$is_disabled   = 'disabled';
					$is_hidden     = 'none';
					$verify_button = '';
					$settings     .= '<div class="ur-form-group" ';
					$settings     .= '<label class="ur-label">' . esc_html__( 'Google Access Code', 'user-registration' ) . '</label>';
					$settings     .= '<input type="text" disabled value="' . esc_html( $_GET['code'] ) . '" name=name="ur_' . $integration->id . '_access_code" id="ur_' . $integration->id . '_access_code" placeholder="' . esc_attr__( 'Enter Google Access Code', 'user-registration' ) . '" class="ur-input forms-list"/>';
					$settings     .= '</div>';
				}
			}
			$settings .= '<div class="ur-form-group" style="display:' . $is_hidden . '">';
			$settings .= '<label class="ur-label"> ' . esc_html__( 'Google Client ID', 'user-registration' ) . '</label>';
			$settings .= '<input type="text" name="ur_' . $integration->id . '_client_id" id="ur_' . $integration->id . '_client_id" placeholder=" ' . esc_attr__( 'Enter Google Client ID', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings .= '</div>';

			$settings .= '<div class="ur-form-group" style="display:' . $is_hidden . '">';
			$settings .= '<label class="ur-label">' . esc_html__( 'Google Client Secret', 'user-registration' ) . '</label>';
			$settings .= '<input type="text" name="ur_' . $integration->id . '_client_secret" id="ur_' . $integration->id . '_client_secret" placeholder=" ' . esc_attr__( 'Enter Google Client Secret', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings .= '</div>';

			$settings .= '<button style="display:' . $verify_button . '" type="button" class="button button-primary ur_' . $integration->id . '_account_verify_button" name="user_registration_' . $integration->id . '_account"> ' . esc_attr__( 'Verify Access Code', 'user-registration' ) . '</button>';
			$settings .= '</div>';

			$settings          .= '<div class="publishing-action" style="display:' . $is_hidden . '">';
			$connected_accounts = get_option( 'ur_' . $integration->id . '_accounts', array() );
			foreach ( $connected_accounts as $account ) {
				if ( ! isset( $account['access_token'] ) ) {
					$connected_accounts = array();
				}
			}
			if ( empty( $connected_accounts ) ) {
				$settings .= '<button type="button" class="button button-primary ur_' . $integration->id . '_account_action_button" name="user_registration_' . $integration->id . '_account"> ' . esc_attr__( 'Connect', 'user-registration' ) . '</button>';
			}
		}

		if ( 'salesforce' === $integration->id ) {
			$settings .= '<div class="ur-form-group">';
			$settings .= '<label class="ur-label">' . esc_html__( 'Consumer Key', 'user-registration' ) . '</label>';
			$settings .= '<input type="text" name="ur_' . $integration->id . '_client_id" id="ur_' . $integration->id . '_client_id" placeholder=" ' . esc_attr__( 'Enter the Consumer Key', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings .= '</div>';
			$settings .= '<div class="ur-form-group">';
			$settings .= '<label class="ur-label">' . esc_html__( 'Consumer Secret', 'user-registration' ) . '</label>';
			$settings .= '<input type="text" name="ur_' . $integration->id . '_client_secret" id="ur_' . $integration->id . '_client_secret" placeholder=" ' . esc_attr__( 'Enter the Consumer Secret', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings .= '</div>';
			$settings .= '<div class="ur-form-group">';
			$settings .= '<label class="ur-label">' . esc_html__( 'Callback URL', 'user-registration' ) . '</label>';
			$settings .= '<input type="text" name="ur_' . $integration->id . '_callback_url" id="ur_' . $integration->id . '_callback_url" value="' . esc_url_raw( rtrim( site_url(), '/' ) . '?page=ur-integration=salesforce' ) . '" class="ur-input forms-list"/>';
			$settings .= '</div>';
			$settings .= '<div class="ur-form-group">';
			$settings .= '<label class="ur-label">' . esc_html__( 'Account Name', 'user-registration' ) . '</label>';
			$settings .= '<input type="text" name="ur_' . $integration->id . '_account_name" id="ur_' . $integration->id . '_account_name" placeholder=" ' . esc_attr__( 'Enter a Account Name', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings .= '</div>';
			$settings .= '</div>';
			$settings .= '<div class="ur-form-group" style="display:none">';
			$settings .= '<label class="ur-label">' . esc_html__( 'Access Code', 'user-registration' ) . '</label>';
			$settings .= '<input type="text" name="ur_' . $integration->id . '_access_code" id="ur_' . $integration->id . '_access_code" placeholder="' . esc_attr__( 'Enter the Access Code', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings .= '</div>';

			$settings          .= '<div class="publishing-action">';
			$settings          .= '<button type="button" class="button button-primary ur_' . $integration->id . '_account_action_button" name="user_registration_' . $integration->id . '_account"> ' . esc_attr__( 'Authenticate with Salesforce', 'user-registration' ) . '</button>';
			$connected_accounts = get_option( 'ur_' . $integration->id . '_accounts', array() );
		}

		if ( 'google_sheets' !== $integration->id && 'sms_integration' !== $integration->id && 'salesforce' !== $integration->id ) {

			$settings .= '<div class="ur-form-group">';
			$settings .= '<label class="ur-label"> ' . esc_html__( 'API Key', 'user-registration' ) . '</label>';
			$settings .= '<input type="text" name="ur_' . $integration->id . '_api_key" id="ur_' . $integration->id . '_api_key" placeholder=" ' . esc_attr__( 'Enter the API Key', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings .= '</div>';

			if ( 'convertkit' === $integration->id ) {
				$settings .= '<div class="ur-form-group">';
				$settings .= '<label class="ur-label"> ' . esc_html__( 'API Secret', 'user-registration' ) . '</label>';
				$settings .= '<input type="text" name="ur_' . $integration->id . '_api_secret" id="ur_' . $integration->id . '_api_secret" placeholder=" ' . esc_attr__( 'Enter the API Secret', 'user-registration' ) . '" class="ur-input forms-list"/>';
				$settings .= '</div>';
			}

			$settings .= '<div class="ur-form-group">';
			$settings .= '<label class="ur-label">' . esc_html__( 'Account Name', 'user-registration' ) . '</label>';
			$settings .= '<input type="text" name="ur_' . $integration->id . '_account_name" id="ur_' . $integration->id . '_account_name" placeholder=" ' . esc_attr__( 'Enter a Account Name', 'user-registration' ) . '" class="ur-input forms-list"/>';
			$settings .= '</div>';
			$settings .= '</div>';

			$settings          .= '<div class="publishing-action">';
			$settings          .= '<button type="button" class="button button-primary ur_' . $integration->id . '_account_action_button" name="user_registration_' . $integration->id . '_account"> ' . esc_attr__( 'Connect', 'user-registration' ) . '</button>';
			$connected_accounts = get_option( 'ur_' . $integration->id . '_accounts', array() );
		}

		$settings .= '</div>';
		$settings .= '</div>';
		$settings .= '</div>';

		if ( ! empty( $connected_accounts ) ) {
			$settings .= '<div id="' . $integration->id . '_accounts" class="postbox">';
			$settings .= '<ul class="ur-integration-connected-accounts">';

			foreach ( $connected_accounts as $key => $list ) {
				$settings .= '<li>';
				$settings .= '<div class="ur-integration-connected-accounts--label"><strong> ' . sanitize_text_field( $list['label'] ) . '</strong></div>';
				$settings .= '<div class="ur-integration-connected-accounts--date">Connected on ' . $list['date'] . '</div>';
				$settings .= '<div class="ur-integration-connected-accounts--disconnect">';

				if ( 'google_sheets' === $integration->id ) {
					$settings .= "<a href='#' class='disconnect ur-" . $integration->id . "-disconnect-account' data-key='" . $list['refresh_token'] . "' > " . esc_html__( 'Disconnect', 'user-registration' ) . '</a>';
				}
				if ( 'sms_integration' === $integration->id ) {
					$settings .= "<a href='#' class='disconnect ur-" . $integration->id . "-disconnect-account' data-key='" . $list['client_number'] . "' > " . esc_html__( 'Disconnect', 'user-registration' ) . '</a>';
				}
				if ( 'salesforce' === $integration->id ) {
					$settings .= "<a href='#' class='disconnect ur-" . $integration->id . "-disconnect-account' data-key='" . $list['consumer_key'] . "' > " . esc_html__( 'Disconnect', 'user-registration' ) . '</a>';
				}
				if ( 'google_sheets' !== $integration->id && 'sms_integration' !== $integration->id && 'salesforce' !== $integration->id ) {
					$settings .= "<a href='#' class='disconnect ur-" . $integration->id . "-disconnect-account' data-key='" . $list['api_key'] . "' > " . esc_html__( 'Disconnect', 'user-registration' ) . '</a>';
				}
				$settings .= '</div>';
				$settings .= '</li>';
			}

			$settings .= '</ul>';
			$settings .= '</div>';
		}

		$settings .= '</div>';
		$settings .= '</div>';
		$settings .= '</div>';
		$settings .= '</div>';

		return $settings;
	}
}

	add_filter( 'hidden_columns', 'ur_users_table_hidden_columns', 10, 3 );

if ( ! function_exists( 'ur_users_table_hidden_columns' ) ) {

	/**
	 * Adds field specific fields to hide and returns the hidden columns array.
	 *
	 * @param [array]  $hidden Hidden columns.
	 * @param [object] $screen Screen Object.
	 * @param [bool]   $use_defaults Whether to use defaults.
	 * @return array
	 */
	function ur_users_table_hidden_columns( $hidden, $screen, $use_defaults ) {

		if ( ! $screen->id === 'user-registration-membership_page_user-registration-users' || ! isset( $_GET['form_filter'] ) ) {
			return $hidden;
		}

		$form_id = (int) sanitize_text_field( $_GET['form_filter'] );

		if ( $form_id ) {
			$form_data_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

			$user_id = get_current_user_id();
			if ( ! empty( get_user_meta( $user_id, "ur_users_hidden_columns_{$form_id}", true ) ) ) {
				return $hidden;
			}

			foreach ( $form_data_array as $data ) {
				foreach ( $data as $grid_key => $grid_data ) {
					foreach ( $grid_data as $grid_data_key => $single_item ) {

						$field_name = $single_item->general_setting->field_name;

						$skip_fields = array(
							'user_login',
							'user_email',
							'user_confirm_email',
							'user_pass',
							'user_confirm_password',
						);

						if ( in_array( $field_name, $skip_fields ) ) {
							continue;
						}

						if ( ! empty( $field_name ) ) {
							$hidden[] = $field_name;
						}
					}
				}
			}
		}

		return $hidden;
	}
}

if ( ! function_exists( 'ur_pro_parse_date_time' ) ) {
	/**
	 * Parse and format a date and time based on mode.
	 *
	 * This function takes a date value, a time value, a time interval, a format,
	 * and a mode as input, and returns the parsed and formatted date and time string.
	 *
	 * @param string $date_value      The date value.
	 * @param string $time_value      The time value.
	 * @param string $time_interval   The time interval.
	 * @param string $mode            The date and time mode.
	 * @param string $format          The date and time format.
	 * @param string $mode_type       The date and time mode type.
	 * @param string $date_local      The date localization.
	 *
	 * @return array The formatted date and time string according to the provided format.
	 */
	function ur_pro_parse_date_time( $date_value, $time_value, $time_interval, $mode, $format = '', $mode_type = '', $date_locale = '' ) {
		$datetime_arr = array();

		switch ( $mode ) {
			case 'time':
				$current_date   = gmdate( 'Y-m-d' );
				$selected_times = explode( ' to ', $time_value );
				if ( count( $selected_times ) < 2 ) {
					return;
				}
				$time_start_value = gmdate( 'H:i', strtotime( $selected_times[0] ) );
				$time_end_value   = gmdate( 'H:i', strtotime( $selected_times[1] ) );

				$datetime_start = "$current_date $time_start_value";
				$datetime_end   = "$current_date $time_end_value";

				$datetime_arr[] = array( $datetime_start, $datetime_end );
				break;
			case 'date':
				if ( 'range' === $mode_type ) {
					$selected_dates = explode( ' to ', $date_value );
					if ( count( $selected_dates ) >= 2 ) {
						$datetime_start = "$selected_dates[0] 00:00";
						$datetime_start = gmdate( 'Y-m-d H:i', strtotime( $datetime_start ) );
						$date_time      = new DateTime( $selected_dates[1] );
						$date_time->modify( '+23 hour' );
						$datetime_end   = $date_time->format( 'Y-m-d H:i' );
						$datetime_arr[] = array( $datetime_start, $datetime_end );
					}
				} else {
					$selected_dates = explode( ', ', $date_value );

					foreach ( $selected_dates as $selected_date ) {
						$datetime_start = "$selected_date 00:00";
						$datetime_start = gmdate( 'Y-m-d H:i', strtotime( $datetime_start ) );
						$date_time      = new DateTime( $datetime_start );
						$date_time->modify( '+23 hour' );

						$datetime_end   = $date_time->format( 'Y-m-d H:i' );
						$datetime_arr[] = array( $datetime_start, $datetime_end );
					}
				}
				break;
			case 'date-time':
				if ( 'range' === $mode_type ) {
					$selected_dates = explode( ' to ', $date_value );
					$selected_times = explode( ' to ', $time_value );
					if ( count( $selected_dates ) < 2 || count( $selected_times ) < 2 ) {
						return;
					}
					$start_date = $selected_dates[0];
					$end_date   = $selected_dates[1];
					$start_time = $selected_times[0];
					$end_time   = $selected_times[1];

					$selected_datetimes = array();

					$selected_datetimes[0] = "$start_date $start_time";
					$selected_datetimes[1] = "$end_date $end_time";

					if ( count( $selected_datetimes ) >= 2 ) {
						$start_datetime = gmdate( 'Y-m-d H:i', strtotime( $selected_datetimes[0] ) );
						$end_datetime   = gmdate( 'Y-m-d H:i', strtotime( $selected_datetimes[1] ) );
						$datetime_arr[] = array( $start_datetime, $end_datetime );
					}
				} else {
					$selected_times = explode( ' to ', $time_value );

					if ( count( $selected_times ) < 2 ) {
						return;
					}
					$start_time = $selected_times[0];
					$end_time   = $selected_times[1];

					$selected_datetimes = array();

					$selected_datetimes[0] = "$date_value $start_time";
					$selected_datetimes[1] = "$date_value $end_time";

					if ( count( $selected_datetimes ) >= 2 ) {
						$start_datetime = gmdate( 'Y-m-d H:i', strtotime( $selected_datetimes[0] ) );
						$end_datetime   = gmdate( 'Y-m-d H:i', strtotime( $selected_datetimes[1] ) );
						$datetime_arr[] = array( $start_datetime, $end_datetime );
					}
				}
				break;
		}

		return $datetime_arr;
	}
}
if ( ! function_exists( 'get_users_slot_booked_meta_data' ) ) {
	/**
	 * Retrieve user meta data with the specified meta_key 'user_slot_booking' as an associative array.
	 *
	 * This function queries the WordPress database to retrieve user meta data
	 * that has the specified meta_key 'user_slot_booking' and returns it as an
	 * associative array with user IDs as keys and meta values as values.
	 *
	 * @return array An associative array containing user meta data with user IDs as keys.
	 */
	function get_users_slot_booked_meta_data( $form_id ) {
		global $wpdb;
		$meta_key = 'user_booked_slot';
		$results  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->usermeta} WHERE meta_key = %s",
				$meta_key
			)
		);

		$meta_data = array();
		foreach ( $results as $result ) {
			$meta_data[ $result->user_id ] = ur_maybe_unserialize( $result->meta_value );
		}

		return $meta_data;
	}
}
if ( ! function_exists( 'ur_pro_get_slot_booking_fields_settings' ) ) {
	/**
	 * Retrieves and processes slot booking field settings for a given form.
	 *
	 * It extracts and organizes settings for
	 * date and time picker fields, including their dependencies and options,
	 * to be used in slot booking functionality.
	 *
	 * @param int $form_id The ID of the form for which to retrieve slot booking settings.
	 *
	 * @return array An array containing the organized slot booking settings for the form's fields.
	 */
	function ur_pro_get_slot_booking_fields_settings( $form_id ) {
		$slot_booking_settings = array();
		$form_post_content     = UR()->form->get_form( $form_id )->post_content;
		$form_post_content_arr = json_decode( $form_post_content, true );

		foreach ( $form_post_content_arr as $section ) {
			foreach ( $section as $row ) {
				foreach ( $row as $field ) {

					if ( ! isset( $field['field_key'] ) ) {
						continue;
					}

					switch ( $field['field_key'] ) {
						case 'date':
							$general_settings = isset( $field['general_setting'] ) ? $field['general_setting'] : array();
							$advance_settings = isset( $field['advance_setting'] ) ? $field['advance_setting'] : array();

							if ( isset( $advance_settings['enable_date_slot_booking'] ) ) {
								$slot_booking_settings[ $general_settings['field_name'] ] = array(
									'field_key'         => 'date',
									'enable_date_slot_booking' => $advance_settings['enable_date_slot_booking'],
									'enable_date_range' => isset( $advance_settings['enable_date_range'] ) ? $advance_settings['enable_date_range'] : '',
								);
							}
							break;
						case 'timepicker':
							$general_settings = isset( $field['general_setting'] ) ? $field['general_setting'] : array();
							$advance_settings = isset( $field['advance_setting'] ) ? $field['advance_setting'] : array();
							if ( isset( $advance_settings['enable_time_slot_booking'] ) ) {
								$slot_booking_settings[ $general_settings['field_name'] ] = array(
									'field_key'         => 'timepicker',
									'enable_time_slot_booking' => $advance_settings['enable_time_slot_booking'],
									'target_date_field' => isset( $advance_settings['target_date_field'] ) ? $advance_settings['target_date_field'] : '',
									'time_interval'     => isset( $advance_settings['time_interval'] ) ? $advance_settings['time_interval'] : '',
								);
							}

							break;
					}
				}
			}
		}
		return $slot_booking_settings;
	}
}

// -------------------  MIGRATION SCRIPTS  ------------------- //


	/**
	 * Migration for Role Based Redirection Settings.
	 */
function ur_pro_update_40_option_migrate() {

	$selected_roles_pages = get_option( 'ur_pro_settings_redirection_after_registration', array() );

	// Get all posts with user_registration post type.
	$posts = get_posts( 'post_type=user_registration' );

	foreach ( $posts as $post ) {
		if ( ! empty( $selected_roles_pages ) ) {
			update_post_meta( $post->ID, 'user_registration_form_setting_redirect_after_registration', 'role-based-redirection' );
		}
	}
}


if ( ! function_exists( 'ur_pro_module_addons_migrate' ) ) {
	/**
	 * Migration to enable module if specific addons activated.
	 *
	 * @since 4.2.0
	 */
	function ur_pro_module_addons_migrate() {
		$merge_addons = array( 'payments', 'content-restriction', 'frontend-listing' );

		foreach ( $merge_addons as $slug ) {
			$enabled_features = get_option( 'user_registration_enabled_features', array() );

			if ( ! in_array( 'user-registration-' . $slug, $enabled_features, true ) && is_plugin_active( 'user-registration-' . $slug . '/user-registration-' . $slug . '.php' ) ) {
				array_push( $enabled_features, 'user-registration-' . $slug );
				update_option( 'user_registration_enabled_features', $enabled_features );
				deactivate_plugins( 'user-registration-' . $slug . '/user-registration-' . $slug . '.php' );
			}
		}
	}
}

if ( ! function_exists( 'ur_add_pdf_fonts' ) ) {

	function ur_add_pdf_fonts() {
		$font = get_option( 'user_registration_pdf_font', 'dejavusans' );

		// Load appropriate font files.
		if ( file_exists( UR_ABSPATH . 'vendor/tecnickcom/tcpdf/fonts/' . $font . '.php' ) ) {
			$font_file = UR_ABSPATH . 'vendor/tecnickcom/tcpdf/fonts/' . $font . '.php';
		} else {
			$font = 'dejavusans';
			if ( file_exists( UR_ABSPATH . 'vendor/tecnickcom/tcpdf/fonts/' . $font . '.php' ) ) {
				$font_file = UR_ABSPATH . 'vendor/tecnickcom/tcpdf/fonts/' . $font . '.php';
			}
		}

		$fontname = TCPDF_FONTS::addTTFfont( $font_file, 'TrueTypeUnicode', '', 96 );
		return $fontname;
	}
}


	// -------------------  END MIGRATION SCRIPTS  ------------------- //

	add_action( 'admin_init', 'ur_handle_force_update' );

if ( ! function_exists( 'ur_handle_force_update' ) ) {

	/**
	 * Delete our plugins addon updater transient during force update.
	 */
	function ur_handle_force_update() {
		global $pagenow;

		if ( 'update-core.php' === $pagenow && ( isset( $_GET['force-check'] ) ) && ( '1' === $_GET['force-check'] ) ) {
			delete_transient( 'user_registration_addon_updater' );
		}
	}
}

	// Code to trigger addons update. Should be removed on later version of pro.
	add_action( 'admin_init', 'ur_check_addons_update' );

if ( ! function_exists( 'ur_check_addons_update' ) ) {

	/**
	 * Manually check for addons update.
	 */
	function ur_check_addons_update() {
		if ( get_transient( 'user_registration_addon_updater' ) ) {
			return;
		}

		set_transient( 'user_registration_addon_updater', true, DAY_IN_SECONDS );
		delete_site_transient( 'update_plugins' );

		if ( function_exists( 'ur_addon_updater' ) ) {

			$raw_sections = wp_safe_remote_get( 'https://assets.wpeverest.com/wpuserregistration/addons/addons-update-details.json' );
			$sections     = array();

			if ( ! is_wp_error( $raw_sections ) ) {
				$sections = json_decode( wp_remote_retrieve_body( $raw_sections ) );

				if ( isset( $sections->addons ) ) {
					$addons = $sections->addons;

					if ( ! empty( $addons ) ) {
						foreach ( $addons as $addon ) {

							$addon_file = $addon->slug . '/' . $addon->slug . '.php';

							if ( is_plugin_active( $addon_file ) && defined( $addon->plugin_file ) && defined( $addon->version ) ) {
								ur_addon_updater( constant( $addon->plugin_file ), $addon->id, constant( $addon->version ) );
							}
						}

						/**
						 * Some addons do nat have compatibility for above code. So below code is written which can be removed after few releases of pro.
						 */
						$plugins_to_check = array();

						if ( class_exists( 'User_Registration_Field_Visibility' ) && is_plugin_active( 'user-registration-field-visibility/user-registration-field-visibility.php' ) && defined( 'UR_FIELD_VISIBILITY_PLUGIN_FILE' ) ) {
							$plugins_to_check['User_Registration_Field_Visibility'] = array(
								'plugin'  => 'user-registration-field-visibility/user-registration-field-visibility.php',
								'file'    => UR_FIELD_VISIBILITY_PLUGIN_FILE,
								'id'      => 18271,
								'version' => User_Registration_Field_Visibility::VERSION,
							);
						}

						if ( class_exists( 'User_Registration_Import_Users' ) && is_plugin_active( 'user-registration-import-users/user-registration-import-users.php' ) && defined( 'UR_IMPORT_USERS_PLUGIN_FILE' ) ) {
							$plugins_to_check['User_Registration_Import_Users'] = array(
								'plugin'  => 'user-registration-import-users/user-registration-import-users.php',
								'file'    => UR_IMPORT_USERS_PLUGIN_FILE,
								'id'      => 72487,
								'version' => User_Registration_Import_Users::VERSION,
							);
						}

						if ( class_exists( 'User_Registration_Invite_Codes' ) && is_plugin_active( 'user-registration-invite-codes/user-registration-invite-codes.php' ) && defined( 'UR_IMPORT_USERS_PLUGIN_FILE' ) ) {
							$plugins_to_check['User_Registration_Invite_Codes'] = array(
								'plugin'  => 'user-registration-invite-codes/user-registration-invite-codes.php',
								'file'    => UR_INVITE_CODES_PLUGIN_FILE,
								'id'      => 9157,
								'version' => User_Registration_Invite_Codes::VERSION,
							);
						}

						if ( class_exists( 'UR_PDF_Form_Submission' ) && is_plugin_active( 'user-registration-pdf-form-submission/user-registration-pdf-form-submission.php' ) && defined( 'URPDF_DIR' ) ) {
							$pdf_instance                               = new UR_PDF_Form_Submission();
							$plugins_to_check['UR_PDF_Form_Submission'] = array(
								'plugin'  => 'user-registration-pdf-form-submission/user-registration-pdf-form-submission.php',
								'file'    => URPDF_DIR . 'user-registration-pdf-form-submission.php',
								'id'      => 3021,
								'version' => $pdf_instance->version,
							);
						}

						if ( class_exists( 'User_Registration_Profile_Connect' ) && is_plugin_active( 'user-registration-profile-connect/user-registration-profile-connect.php' ) && defined( 'UR_IMPORT_USERS_PLUGIN_FILE' ) ) {
							$plugins_to_check['User_Registration_Profile_Connect'] = array(
								'plugin'  => 'user-registration-profile-connect/user-registration-profile-connect.php',
								'file'    => UR_PROFILE_CONNECT_PLUGIN_FILE,
								'id'      => 2826,
								'version' => User_Registration_Profile_Connect::VERSION,
							);
						}

						if ( class_exists( 'User_Registration_Style_Customizer' ) && is_plugin_active( 'user-registration-style-customizer/user-registration-style-customizer.php' ) && defined( 'UR_STYLE_CUSTOMIZER_PLUGIN_FILE' ) ) {
							$plugins_to_check['User_Registration_Style_Customizer'] = array(
								'plugin'  => 'user-registration-style-customizer/user-registration-style-customizer.php',
								'file'    => UR_STYLE_CUSTOMIZER_PLUGIN_FILE,
								'id'      => 26043,
								'version' => User_Registration_Style_Customizer::VERSION,
							);
						}

						foreach ( $plugins_to_check as $class_name => $plugin_data ) {
							ur_addon_updater( $plugin_data['file'], $plugin_data['id'], $plugin_data['version'] );
						}
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'user_registration_payments_activated' ) ) {
	/**
	 * Deactivate payments and show notice when trying to activate payments when pro is still active
	 *
	 * @since 3.0.0
	 */
	function user_registration_payments_activated() {
		set_transient( 'user_registration_pro_need_to_deactivate_payments', true );
	}
}
	add_action( 'activate_user-registration-payments/user-registration-payments.php', 'user_registration_payments_activated' );

if ( ! function_exists( 'user_registration_pro_need_to_deactivate_payments' ) ) {
	/**
	 * Deactivate payments and show notice when trying to activate payments when pro is still active
	 *
	 * @since 3.0.0
	 */
	function user_registration_pro_need_to_deactivate_payments() {
		echo '<div class="notice-warning notice is-dismissible"><p>' . wp_kses_post( 'As <strong>User Registration Pro</strong> is active, <strong>User Registration Payments</strong> is now not needed.', 'user-registration' ) . '</p></div>';
		deactivate_plugins( 'user-registration-payments/user-registration-payments.php' );

		delete_transient( 'user_registration_pro_need_to_deactivate_payments' );
	}
}

if ( get_transient( 'user_registration_pro_need_to_deactivate_payments' ) ) {
	add_action( 'admin_notices', 'user_registration_pro_need_to_deactivate_payments' );
}

if ( ! function_exists( 'user_registration_content_restriction_activated' ) ) {
	/**
	 * Deactivate content_restriction and show notice when trying to activate content_restriction when pro is still active
	 *
	 * @since 3.0.0
	 */
	function user_registration_content_restriction_activated() {
		set_transient( 'user_registration_pro_need_to_deactivate_content_restriction', true );
	}
}
	add_action( 'activate_user-registration-content-restriction/user-registration-content-restriction.php', 'user_registration_content_restriction_activated' );

if ( ! function_exists( 'user_registration_pro_need_to_deactivate_content_restriction' ) ) {
	/**
	 * Deactivate content_restriction and show notice when trying to activate content_restriction when pro is still active
	 *
	 * @since 3.0.0
	 */
	function user_registration_pro_need_to_deactivate_content_restriction() {
		echo '<div class="notice-warning notice is-dismissible"><p>' . wp_kses_post( 'As <strong>User Registration Pro</strong> is active, <strong>User Registration Content Restriction</strong> is now not needed.', 'user-registration' ) . '</p></div>';
		deactivate_plugins( 'user-registration-content-restriction/user-registration-content-restriction.php' );

		delete_transient( 'user_registration_pro_need_to_deactivate_content_restriction' );
	}
}

if ( get_transient( 'user_registration_pro_need_to_deactivate_content_restriction' ) ) {
	add_action( 'admin_notices', 'user_registration_pro_need_to_deactivate_content_restriction' );
}

if ( ! function_exists( 'user_registration_frontend_listing_activated' ) ) {
	/**
	 * Deactivate frontend_listing and show notice when trying to activate frontend_listing when pro is still active
	 *
	 * @since 3.0.0
	 */
	function user_registration_frontend_listing_activated() {
		set_transient( 'user_registration_pro_need_to_deactivate_frontend_listing', true );
	}
}
	add_action( 'activate_user-registration-frontend-listing/user-registration-frontend-listing.php', 'user_registration_frontend_listing_activated' );

if ( ! function_exists( 'user_registration_pro_need_to_deactivate_frontend_listing' ) ) {
	/**
	 * Deactivate frontend_listing and show notice when trying to activate frontend_listing when pro is still active
	 *
	 * @since 3.0.0
	 */
	function user_registration_pro_need_to_deactivate_frontend_listing() {
		echo '<div class="notice-warning notice is-dismissible"><p>' . wp_kses_post( 'As <strong>User Registration Pro</strong> is active, <strong>User Registration Frontend Listing</strong> is now not needed.', 'user-registration' ) . '</p></div>';
		deactivate_plugins( 'user-registration-frontend-listing/user-registration-frontend-listing.php' );

		delete_transient( 'user_registration_pro_need_to_deactivate_frontend_listing' );
	}
}

if ( get_transient( 'user_registration_pro_need_to_deactivate_frontend_listing' ) ) {
	add_action( 'admin_notices', 'user_registration_pro_need_to_deactivate_frontend_listing' );
}



	add_filter( 'all_plugins', 'ur_hide_module_enabled_addons' );

if ( ! function_exists( 'ur_hide_module_enabled_addons' ) ) {

	/**
	 * Hide module enabled addons from all plugins page so that user cannot activate them.
	 *
	 * @param array $plugins List of all plugins.
	 */
	function ur_hide_module_enabled_addons( $plugins ) {
		unset( $plugins['user-registration-content-restriction/user-registration-content-restriction.php'] );
		unset( $plugins['user-registration-frontend-listing/user-registration-frontend-listing.php'] );
		unset( $plugins['user-registration-payments/user-registration-payments.php'] );
		return $plugins;
	}
}

	add_action( 'user_registration_form_grid_options', 'output_row_settings_selector' );

if ( ! function_exists( 'output_row_settings_selector' ) ) {
	/**
	 * Add Row settigns selector tab.
	 */
	function output_row_settings_selector() {

		ob_start();

		if ( is_plugin_active( 'user-registration-conditional-logic/user-registration-conditional-logic.php' ) || is_plugin_active( 'user-registration-repeater-fields/user-registration-repeater-fields.php' ) ) {
			?>
			<button type="button" class="dashicons dashicons-admin-settings ur-row-settings" title="<?php esc_html_e( 'Row Settings', 'user-registration' ); ?>" style="display:none;"></button>
			<?php
		}

		$row_settings_selector = ob_get_clean();
		echo $row_settings_selector;
	}
}

	add_action( 'user_registration_form_bulder_tabs', 'output_row_options_tab' );

if ( ! function_exists( 'output_row_options_tab' ) ) {
	/**
	 * Add Row options tab.
	 */
	function output_row_options_tab() {
		ob_start();

		if ( is_plugin_active( 'user-registration-conditional-logic/user-registration-conditional-logic.php' ) || is_plugin_active( 'user-registration-repeater-fields/user-registration-repeater-fields.php' ) ) {
			?>
			<li style="display:none;">
				<a href="#ur-row-settings" class="nav-tab" id="ur-row-options" class="row-options"><?php esc_html_e( 'Row Options', 'user-registration' ); ?></a>
			</li>
			<?php
		}

		$row_settings = ob_get_clean();
		echo $row_settings;
	}
}

	add_action( 'user_registration_form_bulder_content', 'output_row_options' );

if ( ! function_exists( 'output_row_options' ) ) {
	/**
	 * Add Rows fields options.
	 */
	function output_row_options( $form_id ) {
		$form_data    = json_decode( get_post_field( 'post_content', $form_id ) );
		$form_row_ids = json_decode( get_post_meta( $form_id, 'user_registration_form_row_ids', true ) );

		if ( ! isset( $_GET['edit-registration'] ) && empty( $form_row_ids ) ) {
			return;
		}

		$row_count = is_array( $form_data ) ? count( $form_data ) : 0;

		?>
		<div id="ur-row-section-settings" class="ur-tab-content ur-row-content">
			<form method="post" id="ur-row-settings" onsubmit="return false;">
			<?php
			for ( $index = 0; $index < $row_count; $index++ ) {
				?>
					<div class='ur-form-row ur-individual-row-settings' data-row-id="<?php echo isset( $form_row_ids[ $index ] ) ? esc_attr( $form_row_ids[ $index ] ) : esc_attr( $index ); ?>">
					<?php
					do_action( 'user_registration_get_row_settings', $form_id, isset( $form_row_ids[ $index ] ) ? $form_row_ids[ $index ] : $index );
					?>
					</div>
				<?php
			}
			?>
			</form>
		</div>
		<?php
	}
}

	/**
	 * Get all user registration popups title with respective id.
	 *
	 * @param int $post_count Post Count.
	 * @return array
	 */
function ur_get_all_user_registration_pop( $post_count = -1 ) {
	$args = array(
		'post_type'   => 'ur_pro_popup',
		'status'      => 'publish',
		'numberposts' => $post_count,
		'order'       => 'ASC',

	);
	$query = new WP_Query( $args );
	$posts = array();

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$posts[ $post->ID ] = $post->post_title;
		}
		wp_reset_postdata();
	}
	return $posts;
}

	add_action( 'user_registration_add_wpbakery_widget', 'user_registration_pro_add_wpbakery_widget' );

if ( ! function_exists( 'user_registration_pro_add_wpbakery_widget' ) ) {

	/**
	 * Add User Registration Pro widgets like view profile, popup, content restriction, frontend-listing
	 */
	function user_registration_pro_add_wpbakery_widget() {
		/**
		 * View Profile Details Widget.
		 */
		vc_map(
			array(
				'name'        => esc_html__( 'View Profile Details', 'user-registration' ),
				'base'        => 'user_registration_view_profile_details',
				'icon'        => 'icon-wpb-vc_user_registration',
				'category'    => esc_html__( 'User Registration', 'user-registration' ),
				'description' => esc_html__( 'View Profile Details widget for WPBakery.', 'user-registration' ),
			),
		);

		/**
		 * Popup Widget.
		 */
		vc_map(
			array(
				'name'        => esc_html__( 'Popup', 'user-registration' ),
				'base'        => 'user_registration_popup',
				'icon'        => 'icon-wpb-vc_user_registration',
				'category'    => esc_html__( 'User Registration', 'user-registration' ),
				'description' => esc_html__( 'Popup widget for WPBakery.', 'user-registration' ),
				'params'      => array(
					array(
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Popup List', 'user-registration' ),
						'param_name'  => 'id',
						'value'       => ur_get_popup_list_for_wpbakery(),
						'description' => esc_html__( 'Select Popup.', 'user-registration' ),
					),
					array(
						'type'        => 'checkbox',
						'param_name'  => 'type',
						'heading'     => esc_html__( 'Use as button', 'user-registration' ),
						'description' => esc_html__( 'If enabled it show button to display popup else directly display popup.', 'user-registration' ),
						'group'       => esc_html__( 'Display Type', 'user-registration' ),
						'value'       => array( esc_html__( 'Yes', 'user-registration' ) => 'button' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Button Text', 'user-registration' ),
						'param_name'  => 'button_text',
						'value'       => '',
						'description' => esc_html__( 'Enter Button Text.', 'user-registration' ),
						'group'       => esc_html__( 'Display Type', 'user-registration' ),
						'dependency'  => array(
							'element' => 'type',
							'value'   => 'button',
						),
					),
				),
			),
		);

		/**
		 * Frontend Listing Widget.
		 */
		if ( ( ur_check_module_activation( 'frontend-listing' ) ) ) {
			vc_map(
				array(
					'name'        => esc_html__( 'Frontend Listing', 'user-registration' ),
					'base'        => 'user_registration_frontend_listing',
					'icon'        => 'icon-wpb-vc_user_registration',
					'category'    => esc_html__( 'User Registration', 'user-registration' ),
					'description' => esc_html__( 'Frontend Listing widget for WPBakery.', 'user-registration' ),
					'params'      => array(
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Frontend List', 'user-registration' ),
							'param_name'  => 'id',
							'value'       => ur_get_frontend_listing_list_for_wpbakery(),
							'description' => esc_html__( 'Select a list.', 'user-registration' ),
						),
					),
				),
			);
		}

		/**
		 * PDF Form Submission Widget.
		 */
		if ( is_plugin_active( 'user-registration-pdf-form-submission/user-registration-pdf-form-submission.php' ) ) {
			vc_map(
				array(
					'name'        => esc_html__( 'Download PDF Link', 'user-registration' ),
					'base'        => 'user_registration_download_pdf_button',
					'icon'        => 'icon-wpb-vc_user_registration',
					'category'    => esc_html__( 'User Registration', 'user-registration' ),
					'description' => esc_html__( 'Download PDF Link widget for WPBakery.', 'user-registration' ),
				),
			);
		}
	}
}

if ( ! function_exists( 'ur_get_frontend_listing_list_for_wpbakery' ) ) {

	/**
	 * Get Frontend-Listing list for WP Bakery Widgets
	 */
	function ur_get_frontend_listing_list_for_wpbakery() {
		$args           = array(
			'post_type'   => 'ur_frontend_listings',
			'post_status' => 'public',
		);
		$frontend_lists = get_posts( $args );
		$frontend_list  = array();
		foreach ( $frontend_lists as $frontend ) {
			$frontend_list[ $frontend->post_title ] = $frontend->ID;
		}
		return $frontend_list;
	}
}

if ( ! function_exists( 'ur_get_popup_list_for_wpbakery' ) ) {

	/**
	 * Get Popup list for WP Bakery Widgets
	 */
	function ur_get_popup_list_for_wpbakery() {
		$args       = array(
			'post_type'   => 'ur_pro_popup',
			'post_status' => 'public',
		);
		$popups     = new WP_Query( $args );
		$popup_list = array();
		foreach ( $popups->posts as $popup ) {
			$popup_list[ $popup->post_title ] = $popup->ID;
		}
		return $popup_list;
	}
}

if ( ! function_exists( 'ur_get_input_fields_for_blacklisting' ) ) {
	/**
	 * Get supported fields for blacklisting words.
	 *
	 * @return array
	 * @since 4.3.3
	 */
	function ur_get_input_fields_for_blacklisting( $form_id ) {

		$all_fields = array();

		$all_field_keys = user_registration_pro_blacklist_words_fields();
		$get_all_fields = user_registration_pro_get_conditional_fields_by_form_id( $form_id, '' );

		foreach ( $get_all_fields as $key => $field ) {
			if ( in_array( $field['field_key'], $all_field_keys ) ) {
				$all_fields[ $key ] = $field['label'];
			}
		}

		return $all_fields;
	}
}

	/**
	 * Output passwordless login link.
	 *
	 * @return void
	 */
function user_registration_pro_passwordless_login_link() {
	if ( ur_is_passwordless_login_enabled() && ! ur_is_user_registration_pro_passwordless_login_default_login_area_enabled() && ( ! isset( $_GET['pl'] ) ) || isset( $_GET['page'] ) && 'user-registration-login-forms' === $_GET['page'] ) {
		echo '<p class="user-registration-passwordless-login">';
		echo '<a href="' . esc_url_raw( add_query_arg( array( 'pl' => 'true' ), ur_get_current_page_url() ) ) . '">';

		/**
		 * Filter to modify passwordless login link text.
		 *
		 * @since 5.2.4
		 *
		 * @retun string Modified passwordless login link text.
		 */
		echo esc_html( apply_filters( 'user_registration_passwordless_login_link_text', __( 'Passwordless Login', 'user-registration' ) ) );
		echo '</a>';
		echo '</p>';
	}
}
	add_action(
		'user_registration_login_form_before_submit_button',
		'user_registration_pro_passwordless_login_link',
		20
	);

	if ( ! function_exists( 'ur_custom_password_reset_message' ) ) {
		/**
		 * Customize the password reset email message.
		 *
		 * @param string $message   Default email message.
		 * @param string $key       The activation key.
		 * @param string $user_login The username for the user.
		 * @param object $user_data WP_User object.
		 *
		 * @return string
		 */
		function ur_custom_password_reset_message( $message, $key, $user_login, $user_data ) {
			// Set email content type to HTML.
			add_filter(
				'wp_mail_content_type',
				function ( $content_type ) {
					return 'text/html';
				}
			);
			$site_name = get_bloginfo( 'name' );
			$locale    = determine_locale();
			// Generate the reset link.
			$reset_link = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . '&wp_lang=' . $locale;
			// Build the plain text message.
			$message = __( 'Someone has requested a password reset for the following account:' ) . '<br>';
			/* translators: %s: Site name. */
			$message .= sprintf( __( 'Site Name: %s' ), $site_name ) . '<br>';
			/* translators: %s: User login. */
			$message .= sprintf( __( 'Username: %s' ), $user_login ) . '<br>';
			$message .= __( 'If this was a mistake, ignore this email and nothing will happen.' ) . '<br>';
			$message .= __( 'To reset your password, visit the following address:' ) . '<br>';
			$message .= "<a href='" . esc_url( $reset_link ) . "' style='color: #1a73e8; text-decoration: none;'>Reset Your Password</a><br>";
			// Format the message as HTML.
			$formatted_message = "
    	    <html>
    	    <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
    	        <p>" . $message . '</p>
    	    </body>
    	    </html>
    	';
			return $formatted_message;
		}
	}
	add_filter( 'retrieve_password_message', 'ur_custom_password_reset_message', 10, 4 );

	if ( ! function_exists( 'user_registration_pro_analytics_body' ) ) {

		/**
		 * Generate analytics body.
		 */
		function user_registration_pro_analytics_body() {

			ob_start()
			?>
		<div class="ur-row user-registration-registration-count-report">
			<div class="ur-col-lg-3 ur-col-md-6 user-registration-registration-total-count">
				<div class="user-registration-card ur-mb-6">
					<div class="user-registration-card__body">
						<h4 class="ur-text-muted ur-mt-0"><?php esc_html_e( 'Total Registration', 'user-registration' ); ?></h4>
						<div class="ur-registration--loading">
							<div class="loading-content">
								<div class="loading-text-container">
									<div class="ur--loading--animate main-text"></div>
									<div class="ur--loading--animate sub-text"></div>
								</div>
								<div class="ur--loading--animate loading-btn"></div>
							</div>
						</div>
						<!-- <span class="ur-spinner"></span> -->
					</div>
				</div>
			</div>
			<div class="ur-col-lg-3 ur-col-md-6 user-registration-registration-approved-count">
				<div class="user-registration-card ur-mb-6">
					<div class="user-registration-card__body">
						<h4 class="ur-text-muted ur-mt-0"><?php esc_html_e( 'Approved Users', 'user-registration' ); ?></h4>
						<div class="ur-registration--loading">
							<div class="loading-content">
								<div class="loading-text-container">
									<div class="ur--loading--animate main-text"></div>
									<div class="ur--loading--animate sub-text"></div>
								</div>
								<div class="ur--loading--animate loading-btn"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ur-col-lg-3 ur-col-md-6 user-registration-registration-pending-count">
				<div class="user-registration-card ur-mb-6">
					<div class="user-registration-card__body">
						<h4 class="ur-text-muted ur-mt-0"><?php esc_html_e( 'Pending Users', 'user-registration' ); ?></h4>
						<div class="ur-registration--loading">
							<div class="loading-content">
								<div class="loading-text-container">
									<div class="ur--loading--animate main-text"></div>
									<div class="ur--loading--animate sub-text"></div>
								</div>
								<div class="ur--loading--animate loading-btn"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ur-col-lg-3 ur-col-md-6 user-registration-registration-denied-count">
				<div class="user-registration-card ur-mb-6">
					<div class="user-registration-card__body">
						<h4 class="ur-text-muted ur-mt-0"><?php esc_html_e( 'Denied Users', 'user-registration' ); ?></h4>
						<div class="ur-registration--loading">
							<div class="loading-content">
								<div class="loading-text-container">
									<div class="ur--loading--animate main-text"></div>
									<div class="ur--loading--animate sub-text"></div>
								</div>
								<div class="ur--loading--animate loading-btn"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="ur-row user-registration-registration-chart-report">
			<div class="ur-col-lg-3 ur-col-md-4 user-registration-registration-source-report">
				<div class="user-registration-card ur-mb-6">
					<div class="user-registration-card__header">
						<h3 class="user-registration-card__title"><?php esc_html_e( 'Registration Source', 'user-registration' ); ?></h3>
					</div>
					<div class="user-registration-card__body user-registration-specific-registration-chart">
						<div class="ur-registration--loading">
							<div class="loading-content">
								<div class="ur--loading--animate--long circular"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ur-col-lg-9 ur-col-md-8 user-registration-registration-overview-report">
				<div class="user-registration-card ur-mb-6">
					<div class="user-registration-card__header">
						<h3 class="user-registration-card__title"><?php esc_html_e( 'Registration Overview', 'user-registration' ); ?></h3>
					</div>
					<div class="user-registration-card__body user-registration-total-registration-chart">
						<div class="ur-registration--loading">
							<div class="loading-content">
								<div class="ur--loading--animate--long rectangular"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="ur-row user-registration-form-analytics-report">
			<div class="ur-col-lg-8 ur-col-md-8 user-registration-form-analytics-overview-report">
				<div class="user-registration-card ur-mb-6">
					<div class="user-registration-card__header">
						<h3 class="user-registration-card__title"><?php esc_html_e( 'Form Analytics', 'user-registration' ); ?></h3>
					</div>
					<div class="user-registration-card__body user-registration-form-analytics-chart">
						<div class="ur-registration--loading">
							<div class="loading-content">
								<div class="ur--loading--animate--long rectangular"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ur-col-lg-4 ur-col-md-4 user-registration-form-analytics-referer-report">
				<div class="user-registration-card ur-mb-6">
					<div class="user-registration-card__header">
						<h3 class="user-registration-card__title"><?php esc_html_e( 'Top Referer Pages', 'user-registration' ); ?></h3>
					</div>
					<div class="user-registration-card__body user-registration-form-analytics-top-referer-pages">
						<div class="ur-registration--loading">
							<div class="loading-content">
								<div class="ur--loading--animate--long liner"></div>
								<div class="ur--loading--animate--long liner"></div>
								<div class="ur--loading--animate--long liner"></div>
								<div class="ur--loading--animate--long liner short"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="ur-row">
			<div class="ur-col-lg-12 ur-col-md-8 user-registration-form-analytics-summary-report">
				<div class="user-registration-card ur-mb-6">
					<div class="user-registration-card__header">
						<h3 class="user-registration-card__title"><?php esc_html_e( 'Form Summary', 'user-registration' ); ?></h3>
					</div>
					<div class="user-registration-card__body user-registration-form-summary">
						<div class="ur-registration--loading">
							<div class="loading-content table--view header">
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
							</div>
							<div class="loading-content table--view">
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
							</div>
							<div class="loading-content table--view">
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
							</div>
							<div class="loading-content table--view">
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
								<div class="ur--loading--animate liner"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
			<?php

			$body = ob_get_clean();
			return $body;
		}
	}

	if ( ! function_exists( 'ur_get_user_meta_query_by_user_status' ) ) {

		function ur_get_user_meta_query_by_user_status( $user_status ) {
			switch ( $user_status ) {
				case 'pending':
					return array(
						'relation' => 'AND',
						array(
							'key'     => 'ur_user_status',
							'value'   => '0',
							'compare' => '=',
						),
						array(
							'relation' => 'AND',
							array(
								'relation' => 'OR',
								array(
									'key'     => 'ur_confirm_email',
									'value'   => '0',
									'compare' => '!=',
								),
								array(
									'key'     => 'ur_confirm_email',
									'compare' => 'NOT EXISTS',
								),
							),
							array(
								'relation' => 'OR',
								array(
									'key'     => 'ur_admin_approval_after_email_confirmation',
									'value'   => 'false',
									'compare' => '=',
								),
								array(
									'key'     => 'ur_admin_approval_after_email_confirmation',
									'compare' => 'NOT EXISTS',
								),
							),
						),
					);
				case 'denied':
					return array(
						'relation' => 'OR',
						array(
							'key'     => 'ur_user_status',
							'value'   => '-1',
							'compare' => '=',
						),
						array(
							'key'     => 'ur_confirm_email',
							'value'   => '-1',
							'compare' => '=',
						),
					);
				case 'pending_email':
					return array(
						'relation' => 'AND',
						array(
							'relation' => 'OR',
							array(
								'key'     => 'ur_user_status',
								'compare' => 'NOT EXISTS',
							),
							array(
								'key'     => 'ur_user_status',
								'value'   => '0',
								'compare' => '=',
							),
						),
						array(
							'key'     => 'ur_confirm_email',
							'value'   => '0',
							'compare' => '=',
						),
					);
				default:
					return array(); // Default to an empty array or handle as needed.
			}
		}
	}

	add_action( 'ur_membership_subscription_event_triggered', 'urm_register_triggered_subscription_event' );

	if ( ! function_exists( 'urm_register_triggered_subscription_event' ) ) {

		/**
		 * Register triggered subscription event.
		 *
		 * @param array $data Event data.
		 */
		function urm_register_triggered_subscription_event( $data ) {

			$subscription_events_service = new WPEverest\URMembership\Admin\Services\SubscriptionEventsService();
			$defaults                    = array(
				'subscription_id' => 0,
				'member_id'       => 0,
				'event_type'      => '',
				'meta'            => array(),
			);

			$data = wp_parse_args( $data, $defaults );

			$subscription_id = absint( $data['subscription_id'] );
			$member_id       = absint( $data['member_id'] );
			$event_type      = sanitize_key( $data['event_type'] );
			$meta            = is_array( $data['meta'] ) ? $data['meta'] : array();

			if ( empty( $subscription_id ) || empty( $member_id ) || empty( $event_type ) ) {
				return;
			}

			switch ( $event_type ) {

				case 'created':
					$subscription_events_service->subscription_created( $subscription_id, $member_id, $meta );
					break;

				case 'trial_started':
					$subscription_events_service->trial_started( $subscription_id, $member_id, $meta );
					break;

				case 'trial_ended':
					$subscription_events_service->trial_ended( $subscription_id, $member_id, $meta );
					break;

				case 'renewed':
					$subscription_events_service->subscription_renewed( $subscription_id, $member_id, $meta );
					break;

				case 'canceled':
				case 'cancelled':
					$mode = ! empty( $meta['mode'] ) ? sanitize_key( $meta['mode'] ) : 'expiry';
					$subscription_events_service->subscription_canceled( $subscription_id, $member_id, $mode, $meta );
					break;

				case 'expired':
					$subscription_events_service->subscription_expired( $subscription_id, $member_id, $meta );
					break;

				case 'reactivated':
					$subscription_events_service->subscription_reactivated( $subscription_id, $member_id, $meta );
					break;

				case 'upgraded':
					$subscription_events_service->subscription_upgraded( $subscription_id, $member_id, $meta );
					break;

				default:
					/**
					 * Allow 3rd parties to handle custom event types fired through
					 * `do_action( 'ur_membership_subscription_event_triggered', $payload )`.
					 */
					do_action(
						'ur_membership_subscription_event_triggered_unhandled',
						array(
							'subscription_id' => $subscription_id,
							'member_id'       => $member_id,
							'event_type'      => $event_type,
							'meta'            => $meta,
						)
					);
					break;
			}
		}
	}

	if ( ! function_exists( 'ur_get_membership_ids_link_with_coupons' ) ) {
		/**
		 * Get membership id which is linked with coupons.
		 *
		 * @since 6.0
		 */
		function ur_get_membership_ids_link_with_coupons() {
			$coupon_ids = get_posts(
				array(
					'post_type'      => 'ur_coupons',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);

			$coupon_membership = array();

			foreach ( $coupon_ids as $id ) {
				$meta = json_decode( get_post_meta( $id, 'ur_coupon_meta', true ) );

				if ( ! empty( $meta->coupon_for ) && 'membership' === $meta->coupon_for && ! empty( $meta->coupon_membership ) ) {
					$members = json_decode( $meta->coupon_membership, true );
					if ( is_array( $members ) ) {
						$coupon_membership = array_merge( $coupon_membership, $members );
					}
				}
			}

			$coupon_membership = array_unique( $coupon_membership );

			$coupon_membership = array_combine( $coupon_membership, $coupon_membership );

			return $coupon_membership;
		}
	}

	if ( ! function_exists( 'ur_pro_get_email_template_wrapper' ) ) {
		/**
		 * Wraps email body content with styled header and footer (Pro version).
		 *
		 * @param string $body_content Email body content to wrap.
		 * @param bool   $wrap Whether to wrap the content with header/footer. Default true.
		 * @param array  $values Values for smart tag processing.
		 * @return string Wrapped email content with header and footer, or just body content if $wrap is false.
		 */
		function ur_pro_get_email_template_wrapper( $body_content, $wrap = true, $values = array() ) {
			if ( ! $wrap ) {
				return $body_content;
			}

			$current_year = date( 'Y' );

			$pro_template_path = '';
			if ( function_exists( 'UR' ) && is_callable( array( UR(), 'plugin_path' ) ) ) {
				$pro_template_path = UR()->plugin_path() . '/templates/pro/emails/email-wrapper.php';
			} else {
				$pro_plugin_path   = dirname( __DIR__ );
				$pro_template_path = $pro_plugin_path . '/templates/pro/emails/email-wrapper.php';
			}

			if ( file_exists( $pro_template_path ) ) {
				$template_path = $pro_template_path;
			} else {
				$template_path = ur_locate_template( 'emails/email-wrapper.php' );
			}

			if ( $template_path && file_exists( $template_path ) ) {
				ob_start();
				$template_values = $values;
				include $template_path;
				return ob_get_clean();
			}

			$header_enable     = ur_string_to_bool( get_option( 'user_registration_email_template_header_enable', 'no' ) );
			$header_logo       = get_option( 'user_registration_email_template_header_logo', '' );
			$header_text       = get_option( 'user_registration_email_template_header_text', '' );
			$footer_enable     = ur_string_to_bool( get_option( 'user_registration_email_template_footer_enable', 'no' ) );
			$footer_content    = get_option( 'user_registration_email_template_footer_content', '' );
			$header_text_color = '#000000';
			$header_logo_align = 'left';
			$header_bg_color   = '#FFFFFF';

			// Check if this is a preview and set width to 600px.
			$is_preview  = isset( $_GET['ur_email_preview'] ) && 'email_template_option' === sanitize_text_field( wp_unslash( $_GET['ur_email_preview'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$email_width = $is_preview ? '600px' : '50%';

			// Default footer content if not set.
			if ( empty( $footer_content ) ) {
				$footer_content = '
			<p style="margin: 0 0 12px 0; color: #6c757d; font-size: 13px; line-height: 1.5;">© ' . date( 'Y' ) . ' {{blog_info}}. All rights reserved.</p>
			<p style="margin: 0; font-size: 14px; line-height: 1.6;"><a href="{{home_url}}" style="color: #4A90E2; text-decoration: none; font-weight: 500;">{{blog_info}} Team</a></p>';
			}

			// Build logo HTML.
			$logo_html = '';
			if ( ! empty( $header_logo ) ) {
				$logo_html = '<div style="position: relative; z-index: 1; display: inline-block;">
				<img src="' . esc_url( $header_logo ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" style="max-width: 200px; max-height: 60px; height: auto; display: block;" />
			</div>';
			}

			// Build header text HTML.
			$header_text_html = '';
			if ( ! empty( $header_text ) ) {
				$header_text_html = '<div style="position: relative; z-index: 1; margin-top: 6px; color: ' . esc_attr( $header_text_color ) . '; font-size: 18px; line-height: 1.5;">
				' . wp_kses_post( $header_text ) . '
			</div>';
			}

			// Build header HTML if enabled.
			if ( $header_enable ) {
				$header = apply_filters(
					'user_registration_email_template_header',
					'<div style="font-family: Arial, sans-serif; border-top: 1px solid #e0e0e0; padding: 100px 0; background-color: #ffffff; ">
				<div style="width: ' . esc_attr( $email_width ) . '; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
				<!-- Header -->
				<div style="background-color: ' . esc_attr( $header_bg_color ) . '; padding: 30px; position: relative; overflow: hidden; border-radius: 12px 12px 0 0;">
					<!-- Logo -->
					<div style="text-align: ' . esc_attr( $header_logo_align ) . ';">
						' . $logo_html . '
						' . $header_text_html . '
					</div>
				</div>

				<!-- Body Content -->
				<div style="padding: 40px 30px; background-color: #ffffff;">'
				);
			} else {
				// Minimal wrapper without header.
				$header = '<div style="font-family: Arial, sans-serif; padding: 100px 0; background-color: #ffffff;">
			<div style="width: ' . esc_attr( $email_width ) . '; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
			<!-- Body Content -->
			<div style="padding: 40px 30px; background-color: #ffffff;">';
			}

			// Build footer HTML if enabled.
			if ( $footer_enable && ! empty( $footer_content ) ) {
				$footer = apply_filters(
					'user_registration_email_template_footer',
					'</div>

				<!-- Footer -->
				<div style="padding: 30px; background-color: #ffffff; border-top: 1px solid #e0e0e0; text-align: center;">
					' . $footer_content . '
				</div>
			</div>
			</div>'
				);
			} else {
				// Close wrapper divs without footer.
				$footer = '</div></div></div>';
			}

			return $header . $body_content . $footer;
		}
	}

	if ( ! function_exists( 'ur_pro_process_email_content_wrapper' ) ) {
		/**
		 * Process email content with pro wrapper and smart tags.
		 * This function handles the email wrapper logic for pro version.
		 *
		 * @param string $email_content Email content to process.
		 * @return string Processed email content with wrapper and smart tags.
		 */
		function ur_pro_process_email_content_wrapper( $email_content ) {
			// Use pro wrapper function to wrap email content with header and footer.
			$email_content = ur_pro_get_email_template_wrapper( $email_content, true );

			// Process smart tags in the wrapped content (including footer).
			// This ensures footer smart tags like {{blog_info}} and {{home_url}} are processed.
			$email_content = apply_filters( 'user_registration_process_smart_tags', $email_content, array(), array() );

			return $email_content;
		}
	}

	if ( ! function_exists( 'ur_pro_user_registration_process_email_content' ) ) {
		/**
		 * Returns email content wrapped in email template.
		 * This is the Pro version that handles email template wrapping.
		 *
		 * @param string $email_content Email Content.
		 * @param string $template Email Template id.
		 * @return string Processed email content.
		 */
		function ur_pro_user_registration_process_email_content( $email_content, $template = '' ) {
			$template = is_null( $template ) ? '' : (string) $template;
			$template = trim( $template );

			if ( '' !== $template && 'none' !== strtolower( $template ) ) {
				/**
				 * Filters the email template message.
				 *
				 * The 'user_registration_email_template_message' filter allows developers to modify
				 * the content of the email template used during the user registration process. It provides
				 * an opportunity to customize the email content based on the original content and the template.
				 *
				 * @param string $email_content The original content of the email template.
				 * @param string $template      The template being used for the email.
				 */
				$email_content = apply_filters( 'user_registration_email_template_message', $email_content, $template );
			} else {
				$email_content = ur_pro_process_email_content_wrapper( $email_content );
			}

			return $email_content;
		}
	}

	if ( ! function_exists( 'user_registration_process_email_content' ) ) {
		/**
		 * Returns email content wrapped in email template.
		 * Pro version - directly calls ur_pro_user_registration_process_email_content.
		 * No function_exists check needed since we're in the Pro plugin.
		 *
		 * @param string $email_content Email Content.
		 * @param string $template Email Template id.
		 * @return string Processed email content.
		 */
		function user_registration_process_email_content( $email_content, $template = '' ) {
			return ur_pro_user_registration_process_email_content( $email_content, $template );
		}
	}

	if ( ! function_exists( 'ur_get_email_template_wrapper' ) ) {
		/**
		 * Wraps email body content with styled header and footer.
		 * This is the Pro version that provides full email wrapper functionality.
		 *
		 * @param string $body_content Email body content to wrap.
		 * @param bool   $wrap Whether to wrap the content with header/footer. Default true.
		 * @param array  $values Values for smart tag processing.
		 * @return string Wrapped email content with header and footer, or just body content if $wrap is false.
		 */
		function ur_get_email_template_wrapper( $body_content, $wrap = true, $values = array() ) {
			return ur_pro_get_email_template_wrapper( $body_content, $wrap, $values );
		}
	}


	if ( ! function_exists( 'ur_render_email_marketing_sync_settings' ) ) {

		/**
		 * Render email marketing sync settings on Membership.
		 *
		 * @since 6.0
		 */
		function ur_render_email_marketing_sync_settings( $membership_details ) {
			$page_id = absint( get_option( 'user_registration_member_registration_page_id' ) );
			if ( ! $page_id ) {
				return;
			}

			$post_by_id = get_post( $page_id );
			if ( ! $post_by_id instanceof WP_Post ) {
				return;
			}
			$matches = array();

			preg_match_all( '/\[user_registration_form\s+id="(\d+)"\]/', $post_by_id->post_content, $matches );
			$form_id = 0;

			if ( ! empty( $matches[1][0] ) ) {
				$form_id = absint( $matches[1][0] );
			}

			if ( ! $form_id ) {
				return;
			}

			$email_marketing_sync_details = isset( $membership_details['email_marketing_sync'] ) ? $membership_details['email_marketing_sync'] : array();
			$is_email_marketing_sync      = ur_string_to_bool( isset( $email_marketing_sync_details['is_enable'] ) ? $email_marketing_sync_details['is_enable'] : '0' );
			$addons_sync_details          = isset( $email_marketing_sync_details['addons_sync_details'] ) ? $email_marketing_sync_details['addons_sync_details'] : array();

			$marketing_addons_list = array(
				'activecampaign' => 'ur_activecampaign_render_list',
				'brevo'          => 'ur_brevo_render_list',
				'convertkit'     => 'ur_convertkit_render_list',
				'klaviyo'        => 'ur_klaviyo_render_list',
				'mailchimp'      => 'ur_mailchimp_render_list',
				'mailerlite'     => 'ur_mailerlite_render_list',
				'mailpoet'       => 'ur_mailpoet_render_list',
				'salesforce'     => '',
				'zapier'         => 'ur_zapier_render_list',
			);

			$marketing_addons_list = apply_filters( 'user_registration_marketing_addons_list', $marketing_addons_list );
			$integration_data      = array();
			$connected_accounts    = array();
			$render_lists          = array();

			foreach ( $marketing_addons_list as $addon_key => $function_name ) {
				$integration_data_key = 'zapier' != $addon_key ? 'user_registration_' . $addon_key . '_integration' : 'user_registration_zapier_webhook_url';
				if (
					ur_is_marketing_addon_active( $addon_key ) &&
					! empty(
						ur_get_single_post_meta(
							$form_id,
							$integration_data_key,
							array()
						)
					)
				) {
					$integration_data[ $addon_key ] = ur_get_single_post_meta( $form_id, $integration_data_key, array() );
				}
				if ( ! empty( $integration_data[ $addon_key ] ) ) {
					$connected_accounts[ $addon_key ] = get_option( 'ur_' . $addon_key . '_accounts', array() );
					$render_lists[ $addon_key ]       = $function_name;
				}
			}
			?>
		<div id="ur-sync-to-email-marketing-container"
			class="ur-sync-to-email-marketing-container-wrapper">
			<div class="user-registration-card">
				<div class="user-registration-card__body ur-d-flex ur-flex-column"
					style="gap: 20px">

					<?php
					if ( empty( $integration_data ) ) :
						echo esc_html__( 'No Email Marketing Integration Found in the Membership Registration form.', 'user-registration' );
					else :
						foreach ( $integration_data as $key => $data ) :
							if ( empty( $data ) ) {
								continue;
							}
							?>
							<div class="ur-sync-to-email-marketing-addon-title-container">
								<div class="ur-sync-to-email-marketing-addon-title-container-inner">
									<div class="ur-sync-to-email-marketing-addon-logo">
										<img src="
									<?php
									echo esc_url(
										UR()->plugin_url() . '/assets/images/settings-icons/' . sanitize_key( $key ) . '.png'
									);
									?>
										"
											alt="<?php echo esc_attr( ucfirst( $key ) ); ?>">
									</div>
									<div class="ur-sync-to-email-marketing-addon-title">
										<?php echo ucfirst( esc_html( $key ) ); ?>
									</div>
								</div>
							</div>
							<div class="ur-sync-to-email-marketing-addon-sync-container">
								<div class="ur-sync-to-email-marketing-addon-sync-container-inner ur-d-flex">
									<?php
									if ( ( 'mailpoet' != $key && 'zapier' != $key ) && empty( $connected_accounts[ $key ] ) ) :
										echo "<p class='user-registration-notice user-registration-notice-info'>";
										echo wp_kses_post(
											sprintf(
												/* translators: %s: payment settings URL */
												__( 'Please add a Connection from <a href="%s">settings panel</a>.', 'user-registration-brevo' ),
												esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=integration' ) )
											)
										);
										echo '</p>';

									else :
										?>
										<div class="ur-sync-to-email-marketing-addon-sync-toggle-container">
											<?php
											$is_checked = ! empty( $addons_sync_details[ $key ]['email_marketing_sync'] ) ? 'checked' : '';
											?>
											<input type="checkbox" name="sync_membership_plan_with_<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $is_checked ); ?>>
											<span><?php echo 'Sync to ' . esc_html( $key ); ?></span>
										</div>
										<div class="ur-sync-to-email-marketing-addon-sync-toggle-label-container">
											<?php
											$addon_sync_detail = ! empty( $addons_sync_details[ $key ] ) ? $addons_sync_details[ $key ] : array();
											if ( 'mailpoet' != $key && 'zapier' != $key ) :
												?>
												<div class="form-row ur-enhanced-select"><label class=""><?php echo esc_html__( 'Select Account', 'user-registration' ); ?> </label>
													<select id="ur_sync_email_marketing_<?php echo esc_attr( $key ); ?>_account" class="ur_sync_email_marketing_addon_account" data-addon_name="<?php echo esc_attr( $key ); ?>">
														<?php
														$api_key            = '';
														$default_connection = array();
														foreach ( $connected_accounts[ $key ] as $account ) {
															$find_key = 'salesforce' == $key ? 'consumer_key' : 'api_key';
															if ( empty( $api_key ) ) {
																$api_key            = ! empty( $addon_sync_detail['email_marketing_sync'] ) && ur_string_to_bool( $addon_sync_detail['email_marketing_sync'] ) ? $addon_sync_detail['email_marketing_account'] : $account[ $find_key ];
																$default_connection = $account;
															}
															$selected = $api_key === $account[ $find_key ] ? 'selected=selected' : '';
															?>
															<option value="<?php echo esc_attr( $account[ $find_key ] ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $account['label'] ); ?></option>
															<?php
														}
														?>
													</select>
												</div>
												<?php
											endif;
											if ( ! empty( $render_lists[ $key ] ) ) :
												?>
												<div class="urmc-sync-email-marketing-<?php echo esc_attr( $key ); ?>-list-wrap form-row ur-enhanced-select">
													<?php if ( 'zapier' != $key ) : ?>
														<label><?php echo __( 'Select List', 'user-registration' ); ?></label>
														<?php
													endif;
													if ( function_exists( $render_lists[ $key ] ) ) {
														$list_id = isset( $addon_sync_detail['integration_list_id'] ) ? $addon_sync_detail['integration_list_id'] : '';
														echo $render_lists[ $key ]( $api_key, $list_id, $default_connection );
													}
													?>
												</div>
												<?php
											endif;
											if ( 'mailchimp' === $key ) :
												?>
												<div class="urmc-sync-email-marketing-<?php echo esc_attr( $key ); ?>-list-tag-wrap form-row ur-enhanced-select">
													<label><?php echo __( 'Select Tags', 'user-registration' ); ?></label>
													<?php
													if ( function_exists( 'urmc_render_list_tags' ) ) {
														$list_id = isset( $addon_sync_detail['integration_list_id'] ) ? $addon_sync_detail['integration_list_id'] : '';
														echo urmc_render_list_tags( $api_key, $list_id, $addon_sync_detail );
													}
													?>
												</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
								<div class="ur-sync-to-email-marketing-addon-account-list-container">

								</div>
							</div>
							<?php
						endforeach;
					endif;
					?>

				</div>
			</div>
		</div>
			<?php
		}
	}

	if ( ! function_exists( 'ur_is_marketing_addon_active' ) ) {

		/**
		 * Check marketing addon is active or not.
		 *
		 * @since 6.0
		 *
		 * @param string $addon_key Addon Key.
		 * @return bool
		 */
		function ur_is_marketing_addon_active( $addon_key ) {

			if ( 'mailpoet' === $addon_key ) {
				return is_plugin_active( 'mailpoet/mailpoet.php' );
			}

			$plugin_slug = 'user-registration-' . $addon_key;
			$plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';

			return is_plugin_active( $plugin_file );
		}
	}

	if ( ! function_exists( 'user_registration_pro_tax_regions_template' ) ) {

		function user_registration_pro_tax_regions_template( $tax_regions = '' ) {
			ob_start();
			if ( empty( $tax_regions['regions'] ) ) {
				$tax_regions = get_option( 'user_registration_tax_regions_and_rates', array() );
			}
			$countries = ur_get_country_lists();
			$states    = ur_get_state_lists();
			?>
		<div class="ur-tax-regions-popup-outer-wrapper" >
			<div>

				<!-- Search Box -->
				<div class="ur-tax-regions-search-outer-container">
					<span>🔍</span>

					<input type="text" placeholder="Search countries..."
						class="ur-tax-regions-search">
				</div>

				<!-- Scrollable List -->
				<div class="ur-tax-entire-country-and-state-wrapper">

					<!-- Country Row -->
					<?php	foreach ( $countries as $country_name_key => $country_name ) : ?>
						<div class="ur-tax-country-and-state-outer-wrapper"  data-country="<?php echo esc_attr( $country_name_key ); ?>">
							<div class="ur-tax-regions-popup-country-outer-wrapper">
								<input class="ur-tax-country-checkbox"
									type="checkbox"
									style="margin-right:10px;"
									<?php echo isset( $tax_regions['regions'][ $country_name_key ] ) ? 'checked' : ''; ?> >

								<img class="ur-tax-regions-flag" src="https://flagcdn.com/w40/<?php echo strtolower( $country_name_key ); ?>.png" style="width:24px; margin-right:10px;">
								<span style="font-size:14px;"><?php echo esc_html( $country_name ); ?></span>
							</div>
						<div class="ur-tax-regions-popup-state-outer-wrapper"
							<?php echo isset( $tax_regions['regions'][ $country_name_key ] ) ? '' : 'style="display:none;"'; ?> >

								<div class="ur-tax-regions-popup-state-entire-country-wrapper">
									<div class="ur-tax-regions-entire-country-checkbox-wrapper">
										<?php if ( ! empty( $states[ $country_name_key ] ) ) : ?>

										<input class="ur-tax-entire-country-checkbox" type="checkbox" style="margin-right:10px; margin-top: 0"
											<?php
											echo esc_attr( ! empty( $tax_regions['regions'][ $country_name_key ]['rate'] ) ? '' : 'checked' );
											?>
											>

										<p><?php echo __( 'Apply single tax for entire country', 'user-registration' ); ?></p>
										<?php else : ?>
											<p><?php echo __( 'This tax will be applied to entire country', 'user-registration' ); ?></p>
										<?php endif; ?>
									</div>
									<input class="ur-tax-regions-rate-input <?php echo esc_attr( ! empty( $tax_regions['regions'][ $country_name_key ]['states'] ) && ! empty( $tax_regions['regions'][ $country_name_key ]['rate'] ) ? 'urm-hide-wrapper' : '' ); ?>"
									type="number" min="0" max="100"
										name="regions[<?php echo $country_name_key; ?>][rate]"
										value="<?php echo esc_attr( ! empty( $tax_regions['regions'][ $country_name_key ]['rate'] ) ? $tax_regions['regions'][ $country_name_key ]['rate'] : '' ); ?>"
										data-has_state="<?php echo ( ! empty( $states[ $country_name_key ] ) ? 'yes' : 'no' ); ?>"
										>
								</div>

								<?php if ( ! empty( $states[ $country_name_key ] ) ) : ?>
								<div class="ur-tax-state-outer-wrapper"
									<?php echo esc_attr( empty( $tax_regions['regions'][ $country_name_key ]['rate'] ) && empty( $tax_regions['regions'][ $country_name_key ]['states'] ) ? 'style="display:none"' : '' ); ?>
								>
									<div class="ur-tax-default-tax-outer-wrapper">
										<div class="ur-tax-default-tax-text-container">
											<p>
												<?php
													echo __( 'Default rate for non selected states', 'user-registration' );
												?>
											</p>
											<span>
												<?php
													echo __( 'This rate will apply to states not individually configured below', 'user-registration' );
												?>
											</span>

										</div>
										<input class="ur-tax-regions-default-rate" type="number" min="0" max="100"
											name="regions[<?php echo $country_name_key; ?>][rate]"
											value="<?php echo esc_attr( ! empty( $tax_regions['regions'][ $country_name_key ]['rate'] ) ? $tax_regions['regions'][ $country_name_key ]['rate'] : '' ); ?>"
											>
									</div>
									<div class="ur-tax-state-lists-container" >
										<?php
										foreach ( $states[ $country_name_key ] as $state_key => $state ) :
											?>
										<div class="ur-tax-state-list" data-state="<?php echo $state_key; ?>">
											<div class="ur-tax-state-list-inner">
											<input type="checkbox"
											<?php
												echo esc_attr( ! empty( $tax_regions['regions'][ $country_name_key ]['states'][ $state_key ] ) ? 'checked' : '' );
											?>
											>

											<p><?php echo $state; ?></p>
											</div>
											<input
												class="ur-tax-state-rate-input"
											type="number" min="0" max="100"
												name="regions[<?php echo $country_name_key; ?>][states][<?php echo $state_key; ?>]"
												value="<?php echo esc_attr( ! empty( $tax_regions['regions'][ $country_name_key ]['states'][ $state_key ] ) ? $tax_regions['regions'][ $country_name_key ]['states'][ $state_key ] : '' ); ?>"
												>
										</div>
										<?php endforeach; ?>
									</div>
								</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
			<?php
			$body = ob_get_clean();

			return $body;
		}
	}

	if ( ! function_exists( 'ur_render_tax_table' ) ) {

		function ur_render_tax_table() {

			ob_start();

			$table = new UR_Tax_Region_Table();
			$table->prepare_items();
			?>
		<hr style="margin-top:40px;margin-bottom:40px">
			<div id="user-registration-tax-regions-table-page">
			<form id="membership-team-list" method="get" class="user-registration-base-list-table-form">
				<?php $table->display_page(); ?>
			</form>
			</div>
			<?php

			$settings = ob_get_clean();

			return $settings;
		}
	}
