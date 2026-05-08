<?php
/**
 * UserRegistrationWooCommerce Sync.
 *
 * @class    URWC_Sync
 * @version  1.1.0
 * @package  UserRegistrationWooCommerce/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URWC_Sync Class
 */
class URWC_Sync {

	/**
	 * Hook registration.
	 */
	public function __construct() {

		// Edit Account Task.
		add_action( 'woocommerce_edit_account_form', array( $this, 'edit_account_fields' ) );
		add_action( 'woocommerce_save_account_details', array( $this, 'edit_account_process_form' ) );
		add_action( 'woocommerce_save_account_details_errors', array( $this, 'validate_edit_account_fields' ), 10, 2 );

		// Checkout Task.
		add_action( 'woocommerce_after_checkout_registration_form', array( $this, 'checkout_fields' ) );
		// add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout_fields' ), 10, 2 );
		add_filter( 'woocommerce_form_field_args', array( $this, 'checkout_fields_args' ), 10, 3 );

		// Add to cart Validation
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_product_page_fields' ), 10, 5 );

		/**
		 * Below filter is no longer needed because required validation we are checking from our field class itself,
		 * Using this filter throws required error message twice.
		 * Below code is commented for future need only.
		 */

		// add_filter( 'woocommerce_checkout_fields', array( $this, 'validate_required_checkout_fields' ) );

		// Registration and Login Page.
		add_filter( 'wc_get_template', array( $this, 'replace_myaccount_login_register_edit_account_templates' ), 10, 5 );
	}

	/**
	 * WooCommerce Edit Account modification.
	 */
	public function edit_account_fields() {

		$user_id                 = get_current_user_id();
		$user_registered_form_id = ur_get_form_id_by_userid( $user_id );

		$edit_account_form_id = get_option( 'user_registration_woocommerce_settings_form', 0 );

		// allows multiple forms to be added or changed  on the same edit account page of woocommerce.
		$edit_account_forms_change = apply_filters( 'user_registration_edit_account_form_change', false );
		// Check if current user is registerd through the UR form that is synced to woocommerce edit profile and checkout page.
		if ( ( 0 !== $user_registered_form_id && $user_registered_form_id === $edit_account_form_id && 0 < $edit_account_form_id ) || true === $edit_account_forms_change ) {

			// Enqueue Faltpickr.
			$has_date = ur_has_flatpickr_field( $edit_account_form_id );
			wp_enqueue_script( 'user-registration' );
			$form_data_array = ( $edit_account_form_id ) ? UR()->form->get_form( $edit_account_form_id, array( 'content_only' => true ) ) : array();

			do_action( 'user_registration_my_account_enqueue_scripts', $form_data_array, $edit_account_form_id );

			if ( true === $has_date ) {
				wp_enqueue_style( 'flatpickr' );
				wp_enqueue_script( 'flatpickr' );
			}

			echo '<div class="user-registration" data-form-id="' . esc_attr( $edit_account_form_id ) . '"><h1>' . esc_html__( 'Extra Information', 'user-registration-woocommerce' ) . '</h1>';

			$profile   = user_registration_form_data( $user_id, $edit_account_form_id );
			$user_data = get_userdata( $user_id );
			$user_data = $user_data->data;

			// Prepare values.
			foreach ( $profile as $key => $field ) {
				$value                    = get_user_meta( get_current_user_id(), $key, true );
				$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $value, $key );
				$new_key                  = str_replace( 'user_registration_', '', $key );

				if ( in_array( $new_key, ur_get_registered_user_meta_fields(), true ) ) {
					$value                    = get_user_meta( get_current_user_id(), ( str_replace( 'user_', '', $new_key ) ), true );
					$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $value, $key );
				} elseif ( isset( $user_data->$new_key ) && in_array( $new_key, ur_get_user_table_fields(), true ) ) {
					$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $user_data->$new_key, $key );

				} elseif ( isset( $user_data->display_name ) && 'user_registration_display_name' === $key ) {
					$profile[ $key ]['value'] = apply_filters( 'user_registration_my_account_edit_profile_field_value', $user_data->display_name, $key );
				}
			}

			foreach ( $profile as $profile_key => $profile_field ) {
				$key = str_replace( 'user_registration_', '', $profile_key );

				$checkout_all_form_fields = urwc_get_form_fields( $edit_account_form_id );
				$checkout_form_fields     = array_keys( $checkout_all_form_fields );
				if ( is_array( $checkout_form_fields ) && ! empty( $checkout_form_fields ) ) {

					if ( in_array( $profile_key, $checkout_form_fields, true ) ) {
						$profile_field['input_class'] = array( 'urwc-field-input' );
						$cl_html                      = '';

						if ( isset( $profile_field['enable_conditional_logic'] ) && true === $profile_field['enable_conditional_logic'] ) {
							$cl_map  = isset( $profile_field['cl_map'] ) ? $profile_field['cl_map'] : '';
							$cl_html = sprintf( 'data-conditional-logic-enabled="yes" data-conditional-logic-map="%s"', esc_attr( $cl_map ) );
						}
						echo '<div data-field-id="' . $profile_key . '" class="ur-field-item field-' . $profile_field['field_key'] . ' "' . $cl_html . '>';
						echo '<div data-field-id="' . $key . '" class="ur-field-item field-' . $profile_field['field_key'] . ' "' . $cl_html . '>';

						user_registration_form_field( $profile_key, $profile_field, ! empty( $_POST[ $profile_key ] ) ? ur_clean( $_POST[ $profile_key ] ) : $profile_field['value'] );
						echo '</div>';
						echo '</div>';
					}
				}
			}
			echo '<input type="hidden" id="urcl_hide_fields" name="urcl_hide_fields" value="[]">';
			echo '</div>';
		}
	}

	/**
	 * Process Form submission in WooCommerce Edit Account page.
	 *
	 * @param int $user_id User ID.
	 */
	public function edit_account_process_form( $user_id ) {

		$form_id                 = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$user_registered_form_id = ur_get_form_id_by_userid( $user_id );

		// Check if current user is registerd through the UR form that is synced to woocommerce edit profile and checkout page.
		if ( 0 < $form_id && 0 !== $user_registered_form_id && $user_registered_form_id === $form_id ) {
			$profile = user_registration_form_data( $user_id, $form_id );

			foreach ( $profile as $key => $field ) {
				if ( ! isset( $field['type'] ) ) {
					$field['type'] = 'text';
				}
				// Get Value.
				switch ( $field['type'] ) {
					case 'checkbox':
						if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
							$_POST[ $key ] = $_POST[ $key ];
						} else {
							$_POST[ $key ] = (int) isset( $_POST[ $key ] );
						}
						break;
					default:
						$_POST[ $key ] = isset( $_POST[ $key ] ) ? ur_clean( $_POST[ $key ] ) : '';
						break;
				}

				if ( isset( $_POST['account_first_name'] ) ) {
					$_POST['user_registration_first_name'] = $_POST['account_first_name'];
				}
				if ( isset( $_POST['account_last_name'] ) ) {
					$_POST['user_registration_last_name'] = $_POST['account_last_name'];
				}
				if ( isset( $_POST['account_email'] ) ) {
					$_POST['user_registration_user_email'] = $_POST['account_email'];
				}

				// Hook to allow modification of value.
				$_POST[ $key ] = apply_filters( 'user_registration_process_myaccount_field_' . $key, $_POST[ $key ] );

			}

			do_action( 'user_registration_after_save_profile_validation', $user_id, $profile );

			if ( 0 === ur_notice_count( 'error' ) ) {
				$user_data = array();

				foreach ( $profile as $key => $field ) {
					$new_key = str_replace( 'user_registration_', '', $key );

					if ( ! in_array( $new_key, urwc_get_excluded_fields(), true ) ) {
						if ( in_array( $new_key, ur_get_user_table_fields(), true ) ) {
							$user_data[ $new_key ] = $_POST[ $key ];
						} else {
							$update_key = $key;

							if ( in_array( $new_key, ur_get_registered_user_meta_fields(), true ) ) {
								$update_key = str_replace( 'user_', '', $new_key );
							}

							update_user_meta( $user_id, $update_key, $_POST[ $key ] );
						}
					}
				}

				if ( count( $user_data ) > 0 ) {
					$user_data['ID'] = $user_id;
					wp_update_user( $user_data );
				}

				do_action( 'user_registration_save_profile_details', $user_id, $form_id );
			}
		}
	}

	/**
	 * Validate fields in edit account page.
	 *
	 * @param WP_Error $errors WP Error Class.
	 * @param object   $user User Object.
	 */
	public function validate_edit_account_fields( $errors, $user ) {

		$edit_account_form_id = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$checkout_form_fields = get_option( 'user_registration_woocommerce_checkout_fields', array() );
		$urcl_hide_fields     = isset( $_POST['urcl_hide_fields'] ) ? (array) json_decode( stripslashes( $_POST['urcl_hide_fields'] ), true ) : array();

		if ( 0 < $edit_account_form_id ) {
			$profile = user_registration_form_data( $user->ID, $edit_account_form_id );

			if ( ! is_wp_error( $profile ) ) {

				foreach ( $profile as $profile_key => $profile_field ) {
					$new_key = str_replace( 'user_registration_', '', $profile_key );

					if ( ! in_array( $new_key, $urcl_hide_fields, true ) ) {
						if ( in_array( $profile_key, $checkout_form_fields, true ) ) {
							$field_key   = $profile_field['field_key'];
							$class_name  = ur_load_form_field_class( $field_key );
							$filter_hook = "user_registration_validate_{$field_key}_message";
							$class       = $class_name::get_instance();

							$field_data                                   = new StdClass();
							$field_data->label                            = '<strong>' . $profile_field['label'] . '</strong>';
							$field_data->value                            = isset( $_POST[ $profile_key ] ) ? $_POST[ $profile_key ] : '';
							$single_form_field                            = new StdClass();
							$single_form_field->general_setting           = new StdClass();
							$single_form_field->general_setting->required = $profile_field['required'];

							$class->validation( $single_form_field, $field_data, $filter_hook, $edit_account_form_id );
							$response = apply_filters( $filter_hook, '' );

							if ( ! empty( $response ) ) {
								$errors->add( 'error', $response );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Display Fields in WooCommerce Checkout page.
	 *
	 * @param object $checkout WooCommerce Checkout object.
	 */
	public function checkout_fields( $checkout ) {

		$checkout_form_id     = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$checkout_sync        = ur_string_to_bool( get_option( 'user_registration_woocommrece_settings_sync_checkout', false ) );
		$checkout_form_fields = get_option( 'user_registration_woocommerce_checkout_fields', array() );

		if ( 0 < $checkout_form_id && $checkout_sync && 0 < count( $checkout_form_fields ) ) {
			$mapped_checkout_form_fields = map_checkout_form_fields( $checkout_form_fields, $checkout_form_id );
			// Enqueue Faltpickr.
			$has_date = ur_has_flatpickr_field( $checkout_form_id );
			wp_enqueue_script( 'user-registration' );
			$form_data_array = ( $checkout_form_id ) ? UR()->form->get_form( $checkout_form_id, array( 'content_only' => true ) ) : array();

			do_action( 'user_registration_enqueue_scripts', $form_data_array, $checkout_form_id );

			if ( true === $has_date ) {
				wp_enqueue_style( 'flatpickr' );
				wp_enqueue_script( 'flatpickr' );
			}

			$profile = urwc_get_form_fields( $checkout_form_id );

			echo '<div class="user-registration urwc-form" data-form-id="' . esc_attr( $checkout_form_id ) . '">';
			foreach ( $profile as $profile_key => $profile_field ) {
				$key = str_replace( 'user_registration_', '', $profile_key );

				if ( in_array( $profile_key, $mapped_checkout_form_fields, true ) ) {
					$profile_field['input_class'] = array( 'urwc-field-input' );
					$cl_html                      = '';

					if ( isset( $profile_field['enable_conditional_logic'] ) && true === $profile_field['enable_conditional_logic'] ) {
						$cl_map  = isset( $profile_field['cl_map'] ) ? $profile_field['cl_map'] : '';
						$cl_html = sprintf( 'data-conditional-logic-enabled="yes" data-conditional-logic-map="%s"', esc_attr( $cl_map ) );
					}

					echo '<div data-field-id="' . $key . '" class="ur-field-item field-' . $profile_field['field_key'] . ' "' . $cl_html . '>';
					user_registration_form_field( $profile_key, $profile_field, ! empty( $_POST[ $profile_key ] ) ? ur_clean( $_POST[ $profile_key ] ) : '' );
					echo '</div>';
				}
			}
			echo '<input type="hidden" id="urcl_hide_fields" name="urcl_hide_fields" value="[]">';
			echo '</div>';
		}
	}

	/**
	 * Get fields to validate required fields.
	 *
	 * @param array $fields WooCommerce Field array.
	 */
	public function validate_required_checkout_fields( $fields ) {
		$checkout = WC()->checkout();
		if ( ! $checkout->is_registration_required() && empty( $_POST['createaccount'] ) ) {
			return $fields;
		}

		$checkout_form_id     = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$checkout_sync        = ur_string_to_bool( get_option( 'user_registration_woocommrece_settings_sync_checkout', false ) );
		$checkout_form_fields = get_option( 'user_registration_woocommerce_checkout_fields', array() );

		if ( 0 < $checkout_form_id && $checkout_sync && ! is_user_logged_in() ) {
			$profile                     = urwc_get_form_fields( $checkout_form_id );
			$mapped_checkout_form_fields = map_checkout_form_fields( $checkout_form_fields, $checkout_form_id );

			if ( ! is_wp_error( $profile ) ) {
				$fields['user-registration'] = array();

				foreach ( $profile as $profile_key => $profile_field ) {
					if ( in_array( $profile_key, $mapped_checkout_form_fields, true ) ) {
						$fields['user-registration'][ $profile_key ] = array(
							'label'    => $profile_field['label'],
							'required' => $profile_field['required'],
						);
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Validate Checkout field data.
	 *
	 * @param array  $data Form Data.
	 * @param object $error WooCommerce error object.
	 */
	public function validate_checkout_fields( $data, $error ) {

		$checkout = WC()->checkout();
		if ( ! $checkout->is_registration_required() && empty( $_POST['createaccount'] ) ) {
			return;
		}

		$checkout_form_id     = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$checkout_sync        = ur_string_to_bool( get_option( 'user_registration_woocommrece_settings_sync_checkout', false ) );
		$checkout_form_fields = get_option( 'user_registration_woocommerce_checkout_fields', array() );
		$urcl_hide_fields     = isset( $_POST['urcl_hide_fields'] ) ? (array) json_decode( stripslashes( $_POST['urcl_hide_fields'] ), true ) : array();

		if ( 0 < $checkout_form_id && $checkout_sync && ! is_user_logged_in() ) {
			$profile                     = urwc_get_form_fields( $checkout_form_id );
			$mapped_checkout_form_fields = map_checkout_form_fields( $checkout_form_fields, $checkout_form_id );

			if ( ! is_wp_error( $profile ) ) {

				foreach ( $profile as $profile_key => $profile_field ) {
					$field_name = str_replace( 'user_registration_', '', $profile_key );

					if ( ! in_array( $field_name, $urcl_hide_fields, true ) ) {
						if ( in_array( $profile_key, $mapped_checkout_form_fields, true ) ) {
							$passed = $this->validate_single_fields( $profile_field, $field_name, $profile_key, $checkout_form_id );
						}
					}
				}
			}
		}
	}

	public function validate_product_page_fields( $passed, $product_id, $quantity, $variation_id = '', $variations = '' ) {

		$form_id                 = get_post_meta( $product_id, 'user_registration_woocommerce_product_page_settings_form_' . $product_id, true );
		$product_all_form_fields = get_option( 'user_registration_woocommerce_product_page_fields_' . $product_id, array() );

		if ( array_key_exists( 'form-' . $form_id, $product_all_form_fields ) ) {
			$product_form_fields = $product_all_form_fields[ 'form-' . $form_id ];
		}

		$urcl_hide_fields = isset( $_POST['urcl_hide_fields'] ) ? (array) json_decode( stripslashes( $_POST['urcl_hide_fields'] ), true ) : array();

		$profile = urwc_get_form_fields( $form_id );

		if ( ! is_wp_error( $profile ) ) {

			foreach ( $profile as $profile_key => $profile_field ) {
				$field_name = str_replace( 'user_registration_', '', $profile_key );

				if ( ! in_array( $field_name, $urcl_hide_fields, true ) ) {
					if ( in_array( $profile_key, $product_form_fields, true ) && ( isset( $_POST[ $profile_key ] ) || 'checkbox' === $profile_field['type'] ) ) {
						$passed = $this->validate_single_fields( $profile_field, $field_name, $profile_key, $form_id, $passed );
					}
				}
			}
		}

		return $passed;
	}

	public function validate_single_fields( $profile_field, $field_name, $profile_key, $form_id, $passed = true ) {

		$field_key   = $profile_field['field_key'];
		$class_name  = ur_load_form_field_class( $field_key );
		$filter_hook = "user_registration_validate_{$field_key}_message";
		$class       = $class_name::get_instance();

		$single_form_field                  = new StdClass();
		$single_form_field->field_key       = $profile_field['field_key'];
		$single_form_field->field_name      = $field_name;
		$single_form_field->general_setting = $profile_field['general_setting'];
		$single_form_field->advance_setting = $profile_field['advance_setting'];

		$single_form_field->general_setting->required = '1' == $profile_field['required'] ? 'yes' : 'no';

		$form_data = (object) array(
			'value'        => isset( $_POST[ $profile_key ] ) ? $_POST[ $profile_key ] : '',
			'field_type'   => $profile_field['type'],
			'field_name'   => $field_name,
			'label'        => $profile_field['label'],
			'extra_params' => array(
				'field_key' => $profile_field['field_key'],
				'label'     => $profile_field['label'],
			),
		);

		if ( class_exists( 'UR_Form_Validation' ) ) {
			$validation_class  = new UR_Form_Validation();
			$field_validations = $validation_class->get_field_validations( $single_form_field->field_key );

			if ( $validation_class->is_field_required( $single_form_field, $form_data ) ) {
				array_unshift( $field_validations, 'required' );
			}

			if ( ! empty( $field_validations ) ) {
				if ( in_array( 'required', $field_validations, true ) || ! empty( $form_data->value ) ) {
					foreach ( $field_validations as $validation ) {
						$result = $validation_class::$validation( $form_data->value );
						if ( is_wp_error( $result ) ) {
								$error_code = $result->get_error_code();
								$message    = $validation_class->get_error_message( $error_code, $single_form_field->general_setting->label );

							if ( ! empty( $message ) ) {
								wc_add_notice( $message, 'error' );
								$passed = false;
							}

								break;
						}
					}
				}
			}
		}

		$class->validation( $single_form_field, $form_data, $filter_hook, $form_id );
		$response = apply_filters( $filter_hook, '' );
		if ( ! empty( $response ) ) {
			wc_add_notice( $response, 'error' );
			$passed = false;
		}

		return $passed;
	}

	/**
	 * Display first_name and last_name at checkout page.
	 *
	 * @param string $args Form attributes.
	 * @param string $key Form key.
	 * @param null   $value Form value.
	 *
	 * @since 1.2.5
	 * @return mixed
	 */
	public function checkout_fields_args( $args, $key, $value ) {

		if ( is_user_logged_in() ) {
			$user       = wp_get_current_user();
			$first_name = $user ? $user->user_firstname : '';
			$last_name  = $user ? $user->user_lastname : '';

			if ( 'billing_first_name' === $key ) {
				$args['default'] = empty( $value ) ? $first_name : $value;

			}
			if ( 'billing_last_name' === $key ) {
				$args['default'] = empty( $value ) ? $last_name : $value;

			}
		}
		return $args;
	}



	/**
	 * Replace WooCommerce login and registration page template.
	 *
	 * @param string $located Located.
	 * @param string $template_name Template Name.
	 * @param string $args Arguments.
	 * @param string $template_path Template Path.
	 * @param string $default_path Default Path.
	 * @return string
	 */
	public function replace_myaccount_login_register_edit_account_templates( $located, $template_name, $args = '', $template_path = '', $default_path = '' ) {
		$form_id           = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$sync_registration = ur_string_to_bool( get_option( 'user_registration_woocommrece_settings_replace_login_registration', false ) );
		$checkout_login    = ur_string_to_bool( get_option( 'user_registration_woocommrece_settings_replace_checkout_login', false ) );
		if ( ! is_user_logged_in() ) {
			if ( $template_name == 'myaccount/form-login.php' ) {
				if ( 0 < $form_id && $sync_registration ) {
					ob_start();
					ur_get_template( 'woocommerce-login-register.php', array(), 'user-registration-woocommerce', URWC_ABSPATH . '/templates/' );
					$located = ob_get_clean();
				}
			} elseif ( $template_name == 'checkout/form-login.php' ) {
				if ( ur_string_to_bool( get_option( 'woocommerce_enable_checkout_login_reminder' ) ) && $checkout_login ) {
					ob_start();
					ur_get_template( 'woocommerce-checkout-login', array(), 'user-registration-woocommerce', URWC_ABSPATH . '/templates/' );
					$located = ob_get_clean();
				}
			}
		}
		return $located;
	}
}

new URWC_Sync();
