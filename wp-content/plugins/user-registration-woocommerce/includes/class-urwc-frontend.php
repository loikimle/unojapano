<?php
/**
 * UserRegistrationWooCommerce Frontend.
 *
 * @class    URWC_Frontend
 * @version  1.0.0
 * @package  UserRegistrationWooCommerce/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URWC_Frontend Class
 */
class URWC_Frontend {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		global $post;

		add_filter( 'user_registration_registered_form_fields', 'ur_get_woocommerce_all_fields_in_frontend', 10, 1 );

		$woocommerce_fields = function_exists( 'ur_get_all_woocommerce_fields' ) ? ur_get_all_woocommerce_fields() : array();

		foreach ( $woocommerce_fields as $woo_field ) {
			add_filter( 'user_registration_form_field_' . $woo_field . '_path', 'urwc_form_field_includes', 10, 1 );
		}

		add_filter(
			'user_registration_billing_country_frontend_form_data',
			array(
				$this,
				'user_registration_billing_country_frontend_form_data',
			),
			10,
			1
		);

		add_filter(
			'user_registration_shipping_country_frontend_form_data',
			array(
				$this,
				'user_registration_shipping_country_frontend_form_data',
			),
			10,
			1
		);

		add_filter(
			'user_registration_separate_shipping_frontend_form_data',
			array(
				$this,
				'user_registration_separate_shipping_frontend_form_data',
			),
			10,
			1
		);

		add_action( 'user_registration_enqueue_scripts', array( $this, 'load_scripts' ), 10, 2 );
		add_action( 'user_registration_my_account_enqueue_scripts', array( $this, 'load_scripts' ), 10, 2 );

		add_filter( 'user_registration_account_menu_items', array( $this, 'user_registration_account_menu_items' ) );
		add_filter(
			'user_registration_account_menu_item_classes',
			array(
				$this,
				'user_registration_account_menu_item_classes',
			),
			10,
			2
		);
		add_action( 'user_registration_account_orders_endpoint', array( $this, 'wc_account_orders' ) );
		add_action( 'user_registration_account_downloads_endpoint', array( $this, 'woocommerce_account_downloads' ) );

		add_action( 'user_registration_account_edit-address_endpoint', array( $this, 'wc_addresses' ) );
		add_action( 'user_registration_account_view-order_endpoint', array( $this, 'view_order' ) );

		add_action( 'template_redirect', array( __CLASS__, 'save_address' ) );
		add_filter( 'woocommerce_get_view_order_url', array( $this, 'woocommerce_get_view_order_url' ), 10, 2 );
		add_action(
			'before-user-registration-my-account-shortcode',
			array(
				$this,
				'before_user_registration_my_account_shortcode',
			)
		);

		add_filter( 'send_auth_cookies', array( $this, 'prevent_auto_login_on_woocommerce_checkout' ) );

		/** WooCommerce product actions/filters
		 *
		 *@version  1.5.0
		 */
		add_action( 'woocommerce_checkout_update_user_meta', array( $this, 'urwc_checkout_process' ), 10, 2 );
		// Change add to cart text.
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'urwc_add_to_cart_text' ), 100 );
		add_filter( 'woocommerce_add_to_cart_url', array( $this, 'urwc_add_to_cart_url' ), 10, 2 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'urwc_add_to_cart_url' ), 10, 2 );
		add_filter( 'woocommerce_product_supports', array( $this, 'urwc_product_supports' ), 10, 3 );

		// Use to add ur fields.
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'urwc_before_add_to_cart_button' ) );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'urwc_add_cart_item_data' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'urwc_get_item_data' ), 10, 2 );
		// WooCommerce checkout.
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'urwc_checkout_create_order_line_item' ), 10, 4 );
		add_filter( 'user_registration_parse_values_for_smart_tags', array( $this, 'urwc_parse_billing_shipping_country_code' ) );
		//Register customer with customer role after Registration.
		add_action( 'user_registration_after_register_user_action', array( $this, 'urwc_register_customer' ), 10, 3 );
	}

	/**
	 * Register customer with customer after Registration
	 *
	 * @param array $valid_form_data Form Data.
	 * @param int $form_id FormId.
	 * @param int $user_id UserId.
	 */
	public function urwc_register_customer( $valid_form_data, $form_id, $user_id ) {
		if ( ! $user_id ) {
			return;
		}
		$userdata = get_user_by( 'ID', $user_id );
    	$userrole = $userdata->roles[0];
		if ( 'customer' === $userrole && ! is_checkout() ) {
			$order = wc_create_order( array( 'customer_id' => $user_id ) );
			wp_delete_post($order->get_id(),true);
		}
	}

	/**
	 * Parse billing and shipping country name instead country code.
	 *
	 * @since 1.5.1
	 *
	 * @param object $form_data Form Data.
	 */
	public function urwc_parse_billing_shipping_country_code( $form_data ){
		if ( isset( $form_data->extra_params['field_key'] ) && 'billing_country' === $form_data->extra_params['field_key'] && '' !== $form_data->value ) {
			$countries =  WC()->countries->get_countries();
			$form_data->value = isset( $countries[ $form_data->value ] ) ? $countries[ $form_data->value ] : $form_data->value;
		}
		if ( isset( $form_data->extra_params['field_key'] ) && 'shipping_country' === $form_data->extra_params['field_key'] && '' !== $form_data->value ) {
			$countries =  WC()->countries->get_countries();
			$form_data->value = isset( $countries[ $form_data->value ] ) ? $countries[ $form_data->value ] : $form_data->value;
		}
		return $form_data;
	}

	/**
	 * Get all county list on frontend
	 *
	 * @param  array $filter_data data to filter
	 * @return array $filter_data filtered data
	 */
	public function user_registration_billing_country_frontend_form_data( $filter_data ) {
		return $filter_data;
	}

	/**
	 * Get all county list on frontend
	 *
	 * @param  array $filter_data data to filter
	 * @return array $filter_data filtered data
	 */
	public function user_registration_shipping_country_frontend_form_data( $filter_data ) {
		return $filter_data;
	}

	/**
	 * Check separate shipping by default
	 *
	 * @param $filter_data
	 * @return array
	 */
	public function user_registration_separate_shipping_frontend_form_data( $filter_data ) {
		$filter_data['form_data']['default'] = 0;
		return $filter_data;
	}

	/**
	 * @param $notices
	 *
	 * @return mixed
	 */
	public function before_user_registration_my_account_shortcode() {
		if ( ! function_exists( 'wc_get_notices' ) ) {
			return;
		}

		if ( ! isset( WC()->session ) ) {
			return;
		}

		$wc_notices = wc_get_notices();
		wc_clear_notices();
		$ur_notices = ur_get_notices();
		$wc_error   = isset( $wc_notices['error'] ) ? array_values( $wc_notices['error'] ) : array();
		$wc_success = isset( $wc_notices['success'] ) ? array_values( $wc_notices['success'] ) : array();
		$ur_error   = isset( $ur_notices['error'] ) ? $ur_notices['error'] : array();
		$ur_success = isset( $ur_notices['success'] ) ? $ur_notices['success'] : array();
		foreach ( $wc_success as $success_notice ) {
			if ( ! in_array( $success_notice, $ur_success ) ) {
				ur_add_notice( $success_notice['notice'], 'success' );
			}
		}
		foreach ( $wc_error as $error_notice ) {
			if ( ! in_array( $error_notice, $ur_error ) ) {
				ur_add_notice( $error_notice['notice'], 'error' );
			}
		}

	}

	/**
	 * @param $classes
	 * @param $endpoint
	 */
	public function user_registration_account_menu_item_classes( $classes, $endpoint ) {
		$order_id = ( absint( get_query_var( 'view-order' ) ) );
		if ( $order_id > 0 && $endpoint === get_option( 'woocommerce_myaccount_orders_endpoint', 'orders' ) ) {

			$classes[] = ' is-active ';
		}

		return $classes;

	}

	public function view_order( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order || ! current_user_can( 'view_order', $order_id ) ) {
			echo '<div class="user-registration-error">' . esc_html__( 'Invalid order.', 'user-registration-woocommerce' ) . ' <a href="' . esc_url( ur_get_page_permalink( 'myaccount' ) ) . '">' . esc_html__( 'My Account', 'user-registration-woocommerce' ) . '</a>' . '</div>';
			return;
		}

		// Backwards compatibility.
		$status       = new stdClass();
		$status->name = wc_get_order_status_name( $order->get_status() );

		wc_get_template(
			'myaccount/view-order.php',
			array(
				'status'   => $status,
				'order'    => $order,
				'order_id' => $order->get_id(),
			)
		);
	}

	public function woocommerce_get_view_order_url( $url, $order ) {
		$value = $order->get_id();
		return ur_wc_get_endpoint_url( 'view-order', $value, $url );
	}

	/**
	 * User registration my account shortcode.
	 *
	 * @param array $attributes
	 */
	public function user_registration_my_account_shortcode( $attributes ) {

		if ( is_user_logged_in() ) {
			$template            = get_option( 'user_registration_woocommerce_settings_template', 'horizontal' );
			$attributes['class'] = $attributes['class'] . ' ' . $template;
		}
		return $attributes;

	}

	public function load_scripts( $form_data_array, $form_id ) {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'user-registration-woocommerce-frontend-script', URWC()->plugin_url() . '/assets/js/frontend/urwc-frontend' . $suffix . '.js', array( 'jquery' ), URWC_VERSION );

		if ( class_exists( 'WC_Countries' ) ) {
			$WC_Countries_Obj = new WC_Countries();
			$locale           = $WC_Countries_Obj->get_country_locale();
		} else {
			$locale = array();
		}
		// User registration form validator.
		wp_enqueue_script( 'ur-form-validator' );

		// Localize the script with new data.
		$translation_array = array(
			'countries'              => json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
			'i18n_select_state_text' => esc_attr__( 'Select an option&hellip;', 'user-registration-woocommerce' ),
			'locale'                 => json_encode( $locale ),
		);

		wp_localize_script( 'user-registration-woocommerce-frontend-script', 'wc_country_select_params', $translation_array );
		wp_enqueue_script( 'user-registration-woocommerce-frontend-script' );

		wp_register_style( 'user-registration-woocommerce-frontend-style', URWC()->plugin_url() . '/assets/css/user-registration-woocommerce-frontend-style.css', array(), URWC_VERSION );

		$condition = true;

		if ( $condition ) {
			wp_enqueue_script( 'user-registration-woocommerce-frontend-script' );
			wp_enqueue_style( 'user-registration-woocommerce-frontend-style' );
		}


	}

	/*
	 Save and and update a billing or shipping address if the
	 * form was submitted through the user account page.
	 */
	public static function save_address() {
		global $wp;

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'edit_address' !== $_POST['action'] || empty( $_POST['woocommerce-edit-address-nonce'] ) || ! wp_verify_nonce( $_POST['woocommerce-edit-address-nonce'], 'woocommerce-edit_address' ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		$customer = new WC_Customer( $user_id );

		if ( ! $customer ) {
			return;
		}

		$load_address = isset( $wp->query_vars['edit-address'] ) ? wc_edit_address_i18n( sanitize_title( $wp->query_vars['edit-address'] ), true ) : 'billing';

		$address = WC()->countries->get_address_fields( esc_attr( $_POST[ $load_address . '_country' ] ), $load_address . '_' );

		foreach ( $address as $key => $field ) {

			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}

			// Get Value.
			switch ( $field['type'] ) {
				case 'checkbox':
					$_POST[ $key ] = (int) isset( $_POST[ $key ] );
					break;
				default:
					$_POST[ $key ] = isset( $_POST[ $key ] ) ? wc_clean( $_POST[ $key ] ) : '';
					break;
			}

			// Hook to allow modification of value.
			$_POST[ $key ] = apply_filters( 'woocommerce_process_myaccount_field_' . $key, $_POST[ $key ] );

			// Validation: Required fields.
			if ( ! empty( $field['required'] ) && empty( $_POST[ $key ] ) ) {
				if ( class_exists( 'WC_Form_Handler' ) ) {
					return;
				} else {
					wc_add_notice( sprintf( __( '%s is a required field.', 'user-registration-woocommerce' ), $field['label'] ), 'error' );
				}
			}

			if ( ! empty( $_POST[ $key ] ) ) {

				// Validation rules.
				if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
					foreach ( $field['validate'] as $rule ) {
						switch ( $rule ) {
							case 'postcode':
								$_POST[ $key ] = strtoupper( str_replace( ' ', '', $_POST[ $key ] ) );

								if ( ! WC_Validation::is_postcode( $_POST[ $key ], $_POST[ $load_address . '_country' ] ) ) {
									wc_add_notice( __( 'Please enter a valid postcode / ZIP.', 'user-registration-woocommerce' ), 'error' );
								} else {
									$_POST[ $key ] = wc_format_postcode( $_POST[ $key ], $_POST[ $load_address . '_country' ] );
								}
								break;
							case 'phone':
								$_POST[ $key ] = wc_format_phone_number( $_POST[ $key ] );

								if ( ! WC_Validation::is_phone( $_POST[ $key ] ) ) {
									wc_add_notice( sprintf( __( '%s is not a valid phone number.', 'user-registration-woocommerce' ), '<strong>' . $field['label'] . '</strong>' ), 'error' );
								}
								break;
							case 'email':
								$_POST[ $key ] = strtolower( $_POST[ $key ] );

								if ( ! is_email( $_POST[ $key ] ) ) {
									wc_add_notice( sprintf( __( '%s is not a valid email address.', 'user-registration-woocommerce' ), '<strong>' . $field['label'] . '</strong>' ), 'error' );
								}
								break;
						}
					}
				}
			}

			try {
				// Set prop in customer object.
				if ( is_callable( array( $customer, "set_$key" ) ) ) {
					$customer->{"set_$key"}( $_POST[ $key ] );
				} else {
					$customer->update_meta_data( $key, $_POST[ $key ] );
				}
			} catch ( WC_Data_Exception $e ) {
				// Set notices. Ignore invalid billing email, since is already validated.
				if ( 'customer_invalid_billing_email' !== $e->getErrorCode() ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		do_action( 'woocommerce_after_save_address_validation', $user_id, $load_address, $address, $customer );

		if ( 0 === wc_notice_count( 'error' ) ) {

			foreach ( $address as $key => $field ) {
				update_user_meta( $user_id, $key, $_POST[ $key ] );
			}

			wc_add_notice( __( 'Address changed successfully.', 'user-registration-woocommerce' ), 'success' );

			do_action( 'woocommerce_customer_save_address', $user_id, $load_address );

			wp_safe_redirect( wc_get_endpoint_url( 'edit-address', '', wc_get_page_permalink( 'myaccount' ) ) );

			exit;
		}
	}

	/**
	 * @param $items
	 *
	 * @return array
	 */
	function user_registration_account_menu_items( $items ) {
		$key    = 'user-logout';
		$offset = array_search( $key, array_keys( $items ) );
		$result = array_merge(
			array_slice( $items, 0, $offset ),
			array(
				get_option( 'woocommerce_myaccount_orders_endpoint', 'orders' )             => __( 'Orders', 'user-registration-woocommerce' ),
				get_option( 'woocommerce_myaccount_downloads_endpoint', 'downloads' )       => __( 'Downloads', 'user-registration-woocommerce' ),
				get_option( 'woocommerce_myaccount_edit_address_endpoint', 'edit-address' ) => __( 'Addresses', 'user-registration-woocommerce' ),
			),
			array_slice( $items, $offset, null )
		);

		foreach ( $result as $key => $item ) {
			if ( empty( $key ) ) {
				unset( $result[ $key ] );
			}
		}

		return $result;
	}

	/**
	 * @param $current_page
	 */
	function wc_account_orders( $current_page ) {
		$current_page    = empty( $current_page ) ? 1 : absint( $current_page );
		$customer_orders = wc_get_orders(
			apply_filters(
				'woocommerce_my_account_my_orders_query',
				array(
					'customer' => get_current_user_id(),
					'page'     => $current_page,
					'paginate' => true,
				)
			)
		);

		wc_get_template(
			'myaccount/orders.php',
			array(
				'current_page'    => absint( $current_page ),
				'customer_orders' => $customer_orders,
				'has_orders'      => 0 < $customer_orders->total,
				'wp_button_class' => wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
			)
		);
	}

	function woocommerce_account_downloads() {
		wc_get_template( 'myaccount/downloads.php' );
	}

	public static function wc_addresses( $load_address = 'billing' ) {
		global $wp;

		$current_user = wp_get_current_user();

		if ( isset( $wp->query_vars['edit-address'] ) && empty( $load_address ) ) {
			$load_address = wc_edit_address_i18n( sanitize_title( $wp->query_vars['edit-address'] ), true );
		};

		$load_address = isset( $wp->query_vars['edit-address'] ) ? wc_edit_address_i18n( sanitize_title( $load_address ), true ) : 'billing';

		$address = WC()->countries->get_address_fields( get_user_meta( get_current_user_id(), $load_address . '_country', true ), $load_address . '_' );

		// Enqueue scripts
		wp_enqueue_script( 'wc-country-select' );
		wp_enqueue_script( 'wc-address-i18n' );

		// Prepare values
		foreach ( $address as $key => $field ) {

			$value = get_user_meta( get_current_user_id(), $key, true );

			if ( ! $value ) {
				switch ( $key ) {
					case 'billing_email':
					case 'shipping_email':
						$value = $current_user->user_email;
						break;
					case 'billing_country':
					case 'shipping_country':
						$value = WC()->countries->get_base_country();
						break;
					case 'billing_state':
					case 'shipping_state':
						$value = WC()->countries->get_base_state();
						break;
				}
			}

			$address[ $key ]['value'] = apply_filters( 'woocommerce_my_account_edit_address_field_value', $value, $key, $load_address );
		}

		wc_get_template(
			'myaccount/form-edit-address.php',
			array(
				'load_address' => $load_address,
				'address'      => apply_filters( 'woocommerce_address_to_edit', $address, $load_address ),
			)
		);
		echo '<div style="clear:both"></div>';
	}

	function wc_addresses1() {
		wc_get_template( 'myaccount/form-edit-address.php' );
		echo '<div style="clear:both"></div>';
	}

	/**
	 * Prevent new registered user from being auto logged.
	 *
	 * @return bool
	 */
	public function prevent_auto_login_on_woocommerce_checkout() {

		// Check if user is registered from woocommerce checkout page.
		if ( isset( $_POST['woocommerce-process-checkout-nonce'] ) ) {
			if ( isset( $_POST['createaccount'] ) && ( '1' === $_POST['createaccount'] ) ) {
				$login_option_enabled = ur_string_to_bool( get_option( 'user_registration_woocommrece_settings_login_option', false ) );

				if ( $login_option_enabled ) {
					$form_id = get_option( 'user_registration_woocommerce_settings_form', 0 );

					$login_option = ur_get_single_post_meta(
						$form_id,
						'user_registration_form_setting_login_options',
						get_option( 'user_registration_general_setting_login_options', 'default' )
					);

					if ( 'auto_login' === $login_option ) {
						return true;
					}
				}
				return false;
			}
		}
		return true;
	}

	/**
	 * Add login options to users registered from woocommerce.
	 *
	 * @param int   $customer_id User ID.
	 * @param array $data Form Data.
	 */
	public function urwc_checkout_process( $customer_id, $data ) {
		$checkout = WC()->checkout();
		if ( ! $checkout->is_registration_required() && empty( $_POST['createaccount'] ) ) {
			return;
		}

		$form_id              = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$login_option_enabled = ur_string_to_bool( get_option( 'user_registration_woocommrece_settings_login_option', false ) );

		$login_option = ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) );

		$default_option_selected = 'default' === $login_option || 'auto_approval' === $login_option;

		if ( 0 < $form_id && $login_option_enabled ) {

			$profile         = user_registration_form_data( $customer_id, $form_id );
			$valid_form_data = array();
			$hide_field_list = isset($_POST['urcl_hide_fields']) ? json_decode( stripslashes( $_POST['urcl_hide_fields'] ) ) : array(); // phpcs:ignore

			foreach ( $_POST as $post_key => $post_data ) {

				// Fetch Email and First/Last name from and replace with ur default field keys.
				if ( 'billing_email' === $post_key ) {
					$post_key = 'user_registration_user_email';
				} elseif ( 'billing_first_name' === $post_key || 'billing_last_name' === $post_key ) {
					$post_key = 'billing_first_name' === $post_key ? 'user_registration_first_name' : 'user_registration_last_name';
				}

				$pos = strpos( $post_key, 'user_registration_' );

				if ( false !== $pos && isset( $profile[ $post_key ]['field_key'] ) ) {
					$new_string = substr_replace( $post_key, '', $pos, strlen( 'user_registration_' ) );

					if ( ! empty( $new_string ) ) {
						if ( in_array( $new_string, $hide_field_list, true ) ) {
							continue;
						}
						$tmp_array       = ur_get_valid_form_data_format( $new_string, $post_key, $profile, $post_data );
						$valid_form_data = array_merge( $valid_form_data, $tmp_array );
					}
				}
			}

			$post_content = ur_get_post_content( $form_id );
			$files        = ur_get_form_data_by_key( $post_content, 'file' );

			foreach ( $files as $field_name => $value ) {
				if ( in_array( $field_name, $hide_field_list, true ) ) {
					continue;
				}
				$file_upload  = isset( $_POST[ 'urfu_uploaded_file_' . $field_name ] ) && ! empty( $_POST[ 'urfu_uploaded_file_' . $field_name ] ) && is_array( $_POST[ 'urfu_uploaded_file_' . $field_name ] ) ? implode( ',', $_POST[ 'urfu_uploaded_file_' . $field_name ] ) : '';

				$valid_form_data[ $field_name ]        = new stdClass();
				$valid_form_data[ $field_name ]->value = $file_upload;
				$valid_form_data[ $field_name ]->field_name = $field_name;
			}
			if ( count( $valid_form_data ) > 1 ) {
				UR_Frontend_Form_Handler::ur_update_user_meta( $customer_id, $valid_form_data, $form_id );
				do_action( 'user_registration_after_user_meta_update', $valid_form_data, $form_id, $customer_id );

				do_action( 'user_registration_after_register_user_action', $valid_form_data, $form_id, $customer_id );
			}

			return;
		}
	}
	/**
	 * Render the selected field on product page.
	 */
	public function urwc_before_add_to_cart_button() {
		global $post;

		if ( false === $post->ID ) {
			return;
		}

		$form_id = get_post_meta( $post->ID, 'user_registration_woocommerce_product_page_settings_form_' . $post->ID, true );
		if ( empty( $form_id ) ) {
			return;
		}

		$product_form_fields = get_option( 'user_registration_woocommerce_product_page_fields_' . $post->ID, array() );
		if ( 0 < $form_id && 0 < count( $product_form_fields ) ) {
			$mapped_checkout_form_fields = map_checkout_form_fields( $product_form_fields, $form_id );
			// Enqueue Faltpickr.
			$has_date = ur_has_flatpickr_field( $form_id );
			wp_enqueue_script( 'user-registration' );
			$form_data_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

			do_action( 'user_registration_enqueue_scripts', $form_data_array, $form_id );

			if ( true === $has_date ) {
				wp_enqueue_style( 'flatpickr' );
				wp_enqueue_script( 'flatpickr' );
			}

			$profile = urwc_get_form_fields( $form_id );

			echo '<div class="user-registration urwc-form" data-form-id="' . esc_attr( $form_id ) . '">';
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
					user_registration_form_field( $profile_key, $profile_field, ! empty( $_POST[ $profile_key ] ) ? ur_clean( $_POST[ $profile_key ] ) : '' ); // phpcs:ignore
					echo '</div>';
				}
			}
			echo '<input type="hidden" id="urcl_hide_fields" name="urcl_hide_fields" value="[]">';
			echo '</div>';
		}
	}

	/**
	 * Add cart item data for WooCommerce products.
	 *
	 * @param array $cart_item_data Array of other cart item data.
	 * @param int   $product_id    ID of the product added to the cart.
	 * @param int   $variation_id  Variation ID of the product added to the cart.
	 *
	 * @return array Modified $cart_item_data with additional product form data.
	 */
	public function urwc_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		if ( isset( $cart_item_data['urwc_product_form_data'] ) ) {
			unset( $cart_item_data['urwc_product_form_data'] );
		}

		$form_id = get_post_meta( $product_id, 'user_registration_woocommerce_product_page_settings_form_' . $product_id, true );
		if ( empty( $form_id ) ) {
			return $cart_item_data;
		}

		$form_fields             = urwc_get_form_fields( $form_id );
		$product_all_form_fields = get_option( 'user_registration_woocommerce_product_page_fields_' . $product_id, array() );

		if ( empty( $product_all_form_fields ) ) {
			return $cart_item_data;
		}

		if ( array_key_exists( 'form-' . $form_id, $product_all_form_fields ) ) {
			$product_form_fields = $product_all_form_fields[ 'form-' . $form_id ];
		} else {
			return $cart_item_data;
		}

		$submited_product_form_data = array();
		$hide_field_list = isset($_POST['urcl_hide_fields']) ? json_decode( stripslashes( $_POST['urcl_hide_fields'] ) ) : array(); // phpcs:ignore
		$hide_field_list            = array_map(
			function( $value ) {
				return 'user_registration_' . $value;
			},
			$hide_field_list
		);
		foreach ( $product_form_fields as $field ) {
			if ( in_array( $field, $hide_field_list, true ) ) {
				continue;
			}
			if ( array_key_exists( $field, $form_fields ) ) {
				$field_label = $form_fields[ $field ]['label'];
				$field_type  = $form_fields[ $field ]['type'];
				$field_key   = $form_fields[ $field ]['field_key'];
			}

			if ( 'file' === $field_key ) {
				$field = str_replace( 'user_registration_', 'urfu_uploaded_file_', $field ) ;
			}
			$field_value = isset( $_POST[ $field ] ) ? $_POST[ $field ] : ''; // phpcs:ignore

			$parse_field_value = urwc_format_field_values( $field_key, $field_value );

			$submited_product_form_data[ $field ] = array(
				'label' => $field_label,
				'type'  => $field_type,
				'value' => $parse_field_value,
				'key'   => $field_label,
			);
		}
		$urwc_product_form_data = array(
			'form-id'   => $form_id,
			'form-data' => $submited_product_form_data,
		);

		$cart_item_data['urwc_product_form_data'] = $urwc_product_form_data;
		return $cart_item_data;
	}

	/**
	 * Retrieve additional item data for a WooCommerce cart item.
	 *
	 * This function is used to retrieve and merge additional item data, specifically product form data,
	 * to be displayed for a cart item in the WooCommerce cart.
	 *
	 * @param array $item_data  Existing item data for the cart item.
	 * @param array $cart_item The cart item being processed.
	 *
	 * @return array Modified item data including product form data if available.
	 */
	public function urwc_get_item_data( $item_data, $cart_item ) {

		if ( isset( $cart_item['urwc_product_form_data'] ) ) {

			$urwc_form_data = isset( $cart_item['urwc_product_form_data']['form-data'] ) ? $cart_item['urwc_product_form_data']['form-data'] : array();
			$item_data      = array_merge( $item_data, $urwc_form_data );
		}

		return $item_data;
	}

	/**
	 * Custom function to add data to WooCommerce order line items during checkout.
	 *
	 * This function is hooked into the 'woocommerce_checkout_create_order_line_item' action hook.
	 *
	 * @param WC_Order_Item_Product $item          The order line item being created.
	 * @param string                $cart_item_key  The key representing the cart item.
	 * @param array                 $values         Information about the cart item.
	 * @param WC_Order              $order          The order object.
	 */
	public function urwc_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
		// Add field meta data.
		$urwc_form_item_data = isset( $values['urwc_product_form_data'] ) ? $values['urwc_product_form_data'] : array();
		if ( empty( $urwc_form_item_data ) ) {
			return $item;
		}
		foreach ( $urwc_form_item_data['form-data'] as $item_data ) {

			$item->add_meta_data( $item_data['key'], $item_data['value'] );
		}

		// Add submit to order item.
		$item->add_meta_data( 'urwc_product_form_data', $urwc_form_item_data );
	}

	/**
	 * Customize the "Add to Cart" text for specific products based on product and meta data.
	 *
	 * @param string $text The original "Add to Cart" text.
	 * @return string The customized "Add to Cart" text.
	 */
	public function urwc_add_to_cart_text( $text ) {

		global $product;

		// Check product.
		if ( ! is_object( $product ) ) {
			return $text;
		}

		// Get product ID.
		$product_id = $product->get_id();

		// Get product.
		$product = wc_get_product( $product_id );
		if ( empty( $product ) ) {
			return $text;
		}

		// Get product meta data.
		$form_meta_data = get_option( 'user_registration_woocommerce_product_page_fields_' . $product_id );
		$form_id        = get_post_meta( $product_id, 'user_registration_woocommerce_product_page_settings_form_' . $product_id, true );

		if ( empty( $form_meta_data ) || empty( $single_form_meta_data ) || empty( $form_id ) ) {
			return $text;
		}

		$single_form_meta_data = array_key_exists( 'form-' . $form_id, $form_meta_data ) ? $form_meta_data[ 'form-' . $form_id ] : array();
		$text                  = esc_html__( 'Select Options', 'user-registration-woocommerce' );

		return $text;
	}

	/**
	 * Modify the Add to Cart URL for a product.
	 *
	 * This function is responsible for generating and customizing the URL used when adding
	 * a product to the cart. It checks for conditions such as whether the product is empty,
	 * whether the request is from the WC_Quick_View API, and retrieves the product and form IDs.
	 * If all conditions are met, it filters the product's permalink through the 'addons_add_to_cart_url'
	 * filter before returning it.
	 *
	 * @param string $url     The original Add to Cart URL.
	 * @param object $product The WooCommerce product object.
	 *
	 * @return string The modified Add to Cart URL.
	 */
	public function urwc_add_to_cart_url( $url, $product ) {
		// Check product.
		if ( empty( $product ) ) {
			return $url;
		}

		// Check for quick view.
		if ( isset( $_GET['wc-api'] ) && ( 'WC_Quick_View' === $_GET['wc-api'] ) ) {	// phpcs:ignore
			return $url;
		}

		// Get product ID.
		$product_id = $product->get_id();

		// Check product ID.
		if ( empty( $product_id ) ) {
			return $url;
		}

		// Get form ID on product.
		$form_id = get_post_meta( $product_id, 'user_registration_woocommerce_product_page_settings_form_' . $product_id, true );

		// Check form ID.
		if ( empty( $form_id ) ) {
			return $url;
		}

		// Return filtered permalink.
		return apply_filters( 'urwc_addons_add_to_cart_url', get_permalink( $product_id ) );
	}

	/**
	 * Disable AJAX Add to Cart support for a specific product feature.
	 *
	 * This function is used to customize the support for specific product features.
	 * In this case, it checks if the feature being examined is 'ajax_add_to_cart'.
	 * If it is, it returns false to disable AJAX Add to Cart support for the product.
	 * Otherwise, it returns the original value of the $supports variable.
	 *
	 * @param array  $supports An array of supported features for the product.
	 * @param string $feature  The feature being examined.
	 * @param object $product  The WooCommerce product object.
	 *
	 * @return array  The modified array of supported features.
	 */
	public function urwc_product_supports( $supports, $feature, $product ) {
		// Get product ID.
		$product_id = $product->get_id();
		// Get form ID on product.
		$form_id = get_post_meta( $product_id, 'user_registration_woocommerce_product_page_settings_form_' . $product_id, true );

		// Check form ID.
		if ( empty( $form_id ) ) {
			return $supports;
		}
		// Ensure feature is not ajax add to cart.
		if ( 'ajax_add_to_cart' !== $feature ) {
			return $supports;
		}

		return false;
	}
}

return new URWC_Frontend();
