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
		add_action( 'woocommerce_checkout_update_user_meta', array( $this, 'checkout_process_form' ), 10, 2 );
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout_fields' ), 10, 2 );
		add_filter('woocommerce_form_field_args', array( $this, 'checkout_fields_args' ), 10, 3);
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

		// Check if current user is registerd through the UR form that is synced to woocommerce edit profile and checkout page.
		if ( 0 !== $user_registered_form_id && $user_registered_form_id === $edit_account_form_id && 0 < $edit_account_form_id ) {

			// Enqueue Faltpickr.
			$has_date = ur_has_date_field( $edit_account_form_id );
			wp_enqueue_script( 'user-registration' );
			$form_data_array = ( $edit_account_form_id ) ? UR()->form->get_form( $edit_account_form_id, array( 'content_only' => true ) ) : array();

			do_action( 'user_registration_my_account_enqueue_scripts', $form_data_array, $edit_account_form_id );

			if ( true === $has_date ) {
				wp_enqueue_style( 'flatpickr' );
				wp_enqueue_script( 'flatpickr' );
			}

			echo '<div class="user-registration"><h1>Extra Information</h1>';

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

				if ( ! in_array( $key, urwc_get_excluded_fields(), true ) ) {
					echo '<div data-field-id="' . $key . '" class="ur-field-item field-' . $profile_field['field_key'] . ' ">';
					user_registration_form_field( $profile_key, $profile_field, ! empty( $_POST[ $profile_key ] ) ? ur_clean( $_POST[ $profile_key ] ) : $profile_field['value'] );
					echo '</div>';
				}
			}
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

		if ( 0 < $edit_account_form_id ) {
			$profile = user_registration_form_data( $user->ID, $edit_account_form_id );

			if ( ! is_wp_error( $profile ) ) {

				foreach ( $profile as $profile_key => $profile_field ) {
					if ( ! in_array( $profile_field['field_key'], urwc_get_excluded_fields(), true ) ) {
						$field_key   = $profile_field['field_key'];
						$class_name  = ur_load_form_field_class( $field_key );
						$filter_hook = "user_registration_validate_{$field_key}_message";
						$class       = $class_name::get_instance();

						$field_data        = new StdClass();
						$field_data->label = '<strong>' . $profile_field['label'] . '</strong>';
						$field_data->value = isset( $_POST[ $profile_key ] ) ? $_POST[$profile_key] : '';
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

	/**
	 * Display Fields in WooCommerce Checkout page.
	 *
	 * @param object $checkout WooCommerce Checkout object.
	 */
	public function checkout_fields( $checkout ) {

		$checkout_form_id     = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$checkout_sync        = get_option( 'user_registration_woocommrece_settings_sync_checkout', 'no' );
		$checkout_form_fields = get_option( 'user_registration_woocommerce_checkout_fields', array() );

		if ( 0 < $checkout_form_id && 'yes' === $checkout_sync && 0 < count( $checkout_form_fields ) ) {

			// Enqueue Faltpickr.
			$has_date = ur_has_date_field( $checkout_form_id );
			wp_enqueue_script( 'user-registration' );
			$form_data_array = ( $checkout_form_id ) ? UR()->form->get_form( $checkout_form_id, array( 'content_only' => true ) ) : array();

			do_action( 'user_registration_enqueue_scripts', $form_data_array, $checkout_form_id );

			if ( true === $has_date ) {
				wp_enqueue_style( 'flatpickr' );
				wp_enqueue_script( 'flatpickr' );
			}

			$profile = urwc_get_form_fields( $checkout_form_id );

			echo '<div class="user-registration">';
			foreach ( $profile as $profile_key => $profile_field ) {
				$key = str_replace( 'user_registration_', '', $profile_key );

				if ( in_array( $profile_key, $checkout_form_fields, true ) ) {
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
	 * Process Form submission in WooCommerce Checkout page.
	 *
	 * @param int   $customer_id User ID.
	 * @param array $data Form Data.
	 */
	public function checkout_process_form( $customer_id, $data ) {
		$checkout = WC()->checkout();
		if ( ! $checkout->is_registration_required() && empty( $_POST['createaccount'] ) ) {
			return;
		}

		$form_id       = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$checkout_sync = get_option( 'user_registration_woocommrece_settings_sync_checkout', 'no' );
		$form_fields   = get_option( 'user_registration_woocommerce_checkout_fields', array() );
		$data          = $_POST;

		if ( 0 < $form_id && 'yes' === $checkout_sync ) {
			$profile = urwc_get_form_fields( $form_id );

			foreach ( $profile as $key => $field ) {
				$default_value = isset( $field['default'] ) ? ur_clean( $field['default'] ) : '';

				// Check if data is sent from our fields from checkout page.
				if ( isset( $data[ $key ] ) ) {
					// If data is set then send data value else send default value or null.
					$data[ $key ] = '' !== $data[ $key ] ? $data[ $key ] : $default_value;
				} else {
					/** In case data is not sent from checkout field,
					 * check if that specific data key has value previously set when the user was registered.
					*/
					$user_datas   = get_user_meta( $customer_id, $key, true );
					$data[ $key ] = '' !== $user_datas ? $user_datas : $default_value;
				}
			}

			$user_data = array();

			foreach ( $profile as $key => $field ) {
				$new_key = str_replace( 'user_registration_', '', $key );

				if ( in_array( $key, $form_fields, true ) ) {
					if ( in_array( $key, ur_get_user_table_fields(), true ) ) {
						$user_data[ $new_key ] = $data[ $key ];
					} else {
						$update_key = $key;

						if ( in_array( $new_key, ur_get_registered_user_meta_fields(), true ) ) {
							$update_key = str_replace( 'user_', '', $new_key );
						}

						update_user_meta( $customer_id, $update_key, $data[ $key ] );
					}
				}
			}

			if ( count( $user_data ) > 0 ) {
				$user_data['ID'] = $customer_id;
				wp_update_user( $user_data );
			}

			update_user_meta( $customer_id, 'ur_form_id', $form_id );
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
		$checkout_sync        = get_option( 'user_registration_woocommrece_settings_sync_checkout', 'no' );
		$checkout_form_fields = get_option( 'user_registration_woocommerce_checkout_fields', array() );

		if ( 0 < $checkout_form_id && 'yes' === $checkout_sync && ! is_user_logged_in() ) {
			$profile = urwc_get_form_fields( $checkout_form_id );

			if ( ! is_wp_error( $profile ) ) {
				$fields['user-registration'] = array();

				foreach ( $profile as $profile_key => $profile_field ) {
					if ( in_array( $profile_key, $checkout_form_fields, true ) ) {
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
		$checkout_sync        = get_option( 'user_registration_woocommrece_settings_sync_checkout', 'no' );
		$checkout_form_fields = get_option( 'user_registration_woocommerce_checkout_fields', array() );
		$urcl_hide_fields = isset( $_POST['urcl_hide_fields'] ) ? (array) json_decode( stripslashes( $_POST['urcl_hide_fields'] ), true ) : array();

		if ( 0 < $checkout_form_id && 'yes' === $checkout_sync && ! is_user_logged_in() ) {
			$profile = urwc_get_form_fields( $checkout_form_id );

			if ( ! is_wp_error( $profile ) ) {

				foreach ( $profile as $profile_key => $profile_field ) {

					if ( !in_array( $profile_field['field_key'], $urcl_hide_fields, true ) ) {
						if ( in_array( $profile_key, $checkout_form_fields, true ) ) {

							$field_key   = $profile_field['field_key'];
							$class_name  = ur_load_form_field_class( $field_key );
							$filter_hook = "user_registration_validate_{$field_key}_message";
							$class       = $class_name::get_instance();

							$field_data                  = new StdClass();
							$field_data->label           = '<strong>' . $profile_field['label'] . '</strong>';
							$field_data->value           = isset( $_POST[ $profile_key ] ) ? $_POST[ $profile_key ] : '';
							$required                    =
							array(
								'required' => '1' == $profile_field['required'] ? 'yes' : 'no',
							);
							$field_data->extra_params    = array( 'field_key' => $field_key );
							$field_data->general_setting = (object) $required;
							$class->validation( $field_data, $field_data, $filter_hook, $checkout_form_id );
							$response = apply_filters( $filter_hook, '' );
							if ( ! empty( $response ) ) {
								wc_add_notice( $response, 'error' );
							}
						}
					}
				}
			}
		}
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

		if(	is_user_logged_in() ) {
			$user = wp_get_current_user();
			$first_name = $user ? $user->user_firstname : '';
			$last_name = $user ? $user->user_lastname : '';

			if( 'billing_first_name' === $key ) {
				$args['default'] = empty($value) ? $first_name : $value ;

			}
			if( 'billing_last_name' === $key ) {
				$args['default'] = empty($value) ? $last_name : $value ;

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
		$sync_registration = get_option( 'user_registration_woocommrece_settings_replace_login_registration', 'no' );
		$checkout_login    = get_option( 'user_registration_woocommrece_settings_replace_checkout_login', 'no' );
		if ( ! is_user_logged_in() ) {
			if ( $template_name == 'myaccount/form-login.php' ) {
				if ( 0 < $form_id && 'yes' === $sync_registration ) {
					$located = URWC_ABSPATH . '/templates/woocommerce-login-register.php';
				}
			} elseif ( $template_name == 'checkout/form-login.php' ) {
				if ( 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) && 'yes' === $checkout_login ) {
					$located = URWC_ABSPATH . '/templates/woocommerce-checkout-login.php';
				}
			}
		}
		return $located;
	}
}

new URWC_Sync();
