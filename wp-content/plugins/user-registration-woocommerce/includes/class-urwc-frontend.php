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
	 * @param $attributes
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

		// Localize the script with new data
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
					return ;
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
			)
		);
	}

	function woocommerce_account_downloads() {
		wc_get_template( 'myaccount/downloads.php' );
	}

	public static function wc_addresses( $load_address = 'billing' ) {
		$current_user = wp_get_current_user();
		$load_address = sanitize_key( $load_address );

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
}

return new URWC_Frontend();
