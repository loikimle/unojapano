<?php
/**
 * UserRegistrationWooCommerce Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistrationWooCommerce/Functions
 * @version  1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'user_registration_field_keys', 'ur_get_woocommerce_field_keys', 10, 2 );
add_filter( 'user_registration_one_time_draggable_form_fields', 'ur_get_woocommerce_all_fields_in_frontend', 10, 1 );
add_filter( 'user_registration_fields_without_prefix', 'ur_get_woocommerce_all_fields_in_frontend', 10, 1 );
add_filter( 'user_registration_registered_user_meta_fields', 'ur_get_woocommerce_all_fields_in_frontend', 10, 1 );
add_filter( 'user_registration_user_profile_field_only', 'ur_exclude_wc_fields_in_profile', 10, 1 );
add_filter( 'user_registration_after_register_user_action', 'urwc_copy_billing_address', 10, 3 );
add_filter( 'user_registration_sanitize_field', 'urwc_sanitize_fields', 10, 2 );
add_filter( 'user_registration_form_field_address_title', 'user_registration_woocommerce_title_fields_render', 10, 4 );

/**
 * Sanitize wooCommerce fields on frontend submit
 *
 * @param  array  $form_data
 * @param  string $field_key
 * @return array
 */
function urwc_sanitize_fields( $form_data, $field_key ) {

	if ( in_array( $field_key, ur_get_all_woocommerce_fields() ) ) {
		$form_data->value = sanitize_text_field( $form_data->value );
	}
	return $form_data;
}

/*
 Copy billing address to save to shipping address.
*/
function urwc_copy_billing_address( $form_data, $form_id, $user_id ) {

	$billing_fields = ur_get_woocommerce_billing_fields();
	$remove_keys    = array( 'billing_address_title', 'separate_shipping' );
	$billing_fields = array_diff( $billing_fields, $remove_keys );

	foreach ( $billing_fields as $field ) {
		$billing_field_value = get_user_meta( $user_id, $field, true );
		$field_name          = str_replace( 'billing_', '', $field );
		$exclude             = array( 'email', 'phone' );   // Shipping doesnot contain email and phone.

		if ( ! in_array( $field_name, $exclude ) ) {
			$user_meta = get_user_meta( $user_id, 'shipping_' . $field_name, true );

			if ( metadata_exists( 'user', $user_id, 'separate_shipping' ) && '1' != get_user_meta( $user_id, 'separate_shipping', true ) ) {
				update_user_meta( $user_id, 'shipping_' . $field_name, $billing_field_value );
			} elseif ( ! metadata_exists( 'user', $user_id, 'separate_shipping' ) && empty( $user_meta ) ) {
				update_user_meta( $user_id, 'shipping_' . $field_name, $billing_field_value );
			}
		}
	}
}

/**
 * Compatibility check
 *
 * @return string
 */
function urwc_is_compatible() {

	$ur_plugins_path = WP_PLUGIN_DIR . URWC_DS . 'user-registration' . URWC_DS . 'user-registration.php';
	$ur_pro_plugins_path = WP_PLUGIN_DIR . URWC_DS . 'user-registration-pro' . URWC_DS . 'user-registration.php';

	if ( ! file_exists( $ur_plugins_path ) && ! file_exists( $ur_pro_plugins_path ) ) {
			return __( 'Please install <code>user-registration-pro</code> plugin to use <code>user-registration-woocommerce</code> addon.', 'user-registration-woocommerce' );
	}

	$ur_plugin_file_path = 'user-registration/user-registration.php';
	$ur_pro_plugin_file_path = 'user-registration-pro/user-registration.php';

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( $ur_plugin_file_path ) && ! is_plugin_active( $ur_pro_plugin_file_path ) ) {
		return __( 'Please activate <code>user-registration-pro</code> plugin to use <code>user-registration-woocommerce</code> addon.', 'user-registration-woocommerce' );
	}

	$wc_plugins_path = WP_PLUGIN_DIR . URWC_DS . 'woocommerce' . URWC_DS . 'woocommerce.php';

	if ( ! file_exists( $wc_plugins_path ) ) {
		return __( 'Please install <code>woocommerce</code> plugin to use <code>user-registration-woocommerce</code> addon.', 'user-registration-woocommerce' );
	}

	$wc_plugins_path = 'woocommerce/woocommerce.php';

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( $wc_plugins_path ) ) {
		return __( 'Please activate <code>woocommerce</code> plugin to use <code>user-registration-woocommerce</code> addon.', 'user-registration-woocommerce' );
	}

	if ( function_exists( 'UR' ) ) {
		$user_registration_version = UR()->version;
	} else {
		$user_registration_version = get_option( 'user_registration_version' );
	}

	if ( ! is_plugin_active( $ur_pro_plugin_file_path ) ) {

		if ( version_compare( $user_registration_version, '1.4.1', '<' ) ) {
			return __( 'Please update your <code>user registration</code> plugin to at least 1.4.1 version to use <code>user-registration-woocommerce</code> addon.', 'user-registration-woocommerce' );
		}
	} else {

		if ( version_compare( $user_registration_version, '3.0.0', '<' ) ) {
			return __( 'Please update your <code>user-registration-pro</code> plugin(to at least 3.0.0 version) to use <code>user-registration-woocommerce</code> addon.', 'user-registration-woocommerce' );
		}
	}

	return 'YES';
}

/**
 * Admin notice for plugin compatibility
 */
function urwc_check_plugin_compatibility() {
	add_action( 'admin_notices', 'user_registration_woocommerce_admin_notice', 10 );
}

/**
 * Exclude WooCommerce fields to display on admin profile
 *
 * @param  array $fields fields to display on admin profile
 * @return array $fields
 */
function ur_exclude_wc_fields_in_profile( $fields ) {
	$woo_commerce_fields = ur_get_all_woocommerce_fields();
	$fields              = array_diff( $fields, $woo_commerce_fields );
	return $fields;
}

/**
 * Assign field types to WooCommerce field keys
 *
 * @param  string    $field_type
 * @param  $field_key
 * @return $field_type
 */
function ur_get_woocommerce_field_keys( $field_type, $field_key ) {
	switch ( $field_key ) {
		case 'separate_shipping':
			$field_type = 'checkbox';
			break;
		case 'billing_email':
			$field_type = 'email';
			break;
		case 'billing_country':
		case 'shipping_country':
			$field_type = 'select';
			break;
		case 'billing_address_title':
		case 'shipping_address_title':
			$field_type = 'address_title';
			break;
	}

	return $field_type;
}

/**
 * Render frontend html for WooCoommerce billing and shipping addresses title
 *
 * @param  $field
 * @param  $key
 * @param  $args
 * @param  $value
 * @return void
 */
function user_registration_woocommerce_title_fields_render( $field, $key, $args, $value ) {
	if ( $args['label'] ) {
		$field_content  = '<h3 id="' . esc_attr( $args['id'] ) . '">' . esc_html( $args['label'] ) . '</h3>';
		$field_content .= '<span class="description">' . isset( $args['description'] ) ? $args['description'] : '' . '</span>';
		echo $field_content;
	}
}

/**
 * Admin Notices
 *
 * @return void
 */
function user_registration_woocommerce_admin_notice() {
	$class   = 'notice notice-error';
	$message = urwc_is_compatible();

	if ( 'YES' !== $message ) {
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), ( $message ) );
	}
}

/**
 * Deprecate plugin missing notice.
 *
 * @deprecated 1.2.3
 *
 * @return void
 */
function urwc_admin_notices() {
	ur_deprecated_function( 'urwc_admin_notices', '1.2.3', 'user_registration_woocommerce_admin_notice' );
}

/**
 * User Registration WooCoomerce Account Details Settings
 *
 * @return array
 */
function urwc_woocommerce_settings() {

	$forms    = ur_get_all_user_registration_form();
	$forms[0] = __( 'None', 'user-registration-social-connect' );
	ksort( $forms );

	return apply_filters(
		'user_registration_woocommerce_settings',
		array(
			'title' =>  __( 'WooCommerce', 'user-registration-woocommerce' ),
				'sections' => array (
					'user_registration_woocommerce_settings' => array(
						'title' => esc_html__( 'WooCommerce Sync', 'user-registration-woocommerce' ),
						'type'  => 'card',
						'desc'  => '',
						'settings' => array(
						array(
							'title'    => __( 'Select Registration Form', 'user-registration-woocommerce' ),
							'desc'     => __( 'Choose registration form to sync with WooCommerce.', 'user-registration-woocommerce' ),
							'id'       => 'user_registration_woocommerce_settings_form',
							'default'  => 'None',
							'type'     => 'select',
							'class'    => 'ur-enhanced-select',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
							'options'  => $forms,
						),
						array(
							'title'     => __( 'Replace registration page', 'user-registration' ),
							'desc_tip'  => __( 'Replace default WooCommerce login and registration form with User Registration form and login', 'user-registration' ),
							'desc'      => __( 'Check this option to replace default WooCommerce\'s login and registration page', 'user-registration' ),
							'id'        => 'user_registration_woocommrece_settings_replace_login_registration',
							'type'      => 'checkbox',
							'css'       => 'min-width: 350px;',
							'row_class' => ( get_option( 'user_registration_woocommerce_settings_form', '0' ) === '0' ) ? 'ur-setting-hidden' : '',
							'default'   => 'no',
						),
						array(
							'title'     => __( 'Replace checkout login', 'user-registration' ),
							'desc_tip'  => __( 'Replace default WooCommerce login in checkout page with User Registration login', 'user-registration' ),
							'desc'      => __( 'Check this option to replace default WooCommerce\'s login in checkout page', 'user-registration' ),
							'id'        => 'user_registration_woocommrece_settings_replace_checkout_login',
							'type'      => 'checkbox',
							'css'       => 'min-width: 350px;',
							'row_class' => ( get_option( 'user_registration_woocommerce_settings_form', '0' ) === '0' ) ? 'ur-setting-hidden' : '',
							'default'   => 'no',
						),
						array(
							'title'             => __( 'Sync checkout registration', 'user-registration' ),
							'desc_tip'          => __( 'This option lets you select registration form fields to be synced with WooCommerce Checkout page\'s registration form.', 'user-registration' ),
							'desc'              => __( 'Check this option to sync user registration form with Woocommerce checkout registration', 'user-registration' ),
							'id'                => 'user_registration_woocommrece_settings_sync_checkout',
							'type'              => 'checkbox',
							'css'               => 'min-width: 350px;',
							'row_class'         => ( get_option( 'user_registration_woocommerce_settings_form', '0' ) === '0' ) ? 'ur-setting-hidden' : '',
							'default'           => 'no',
							'custom_attributes' => array(
								'data-field_option_key' => 'user_registration_woocommerce_checkout_fields',
							),
						),
					),
				),
			),
		)
	);
}

/**
 * @param $path
 *
 * @return string
 */
function urwc_form_field_includes( $path ) {
	$core_path  = UR_ABSPATH;
	$addon_path = URWC_ABSPATH;
	$path       = str_replace( $core_path, $addon_path, $path );
	return $path;
}

/**
 * All WooCommerce fields
 *
 * @return array
 */
function ur_get_all_woocommerce_fields() {
	return array(
		'billing_address_title',
		'shipping_address_title',
		'billing_country',
		'billing_first_name',
		'billing_last_name',
		'billing_company',
		'billing_address_1',
		'billing_address_2',
		'billing_city',
		'billing_state',
		'billing_postcode',
		'billing_email',
		'billing_phone',
		'separate_shipping',
		'shipping_country',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_company',
		'shipping_address_1',
		'shipping_address_2',
		'shipping_city',
		'shipping_state',
		'shipping_postcode',
	);
}

/**
 * Merge WooCommerce fields with all other fields
 */
function ur_get_woocommerce_all_fields_in_frontend( $fields ) {
	$woocommerce_fields = ur_get_all_woocommerce_fields();

	foreach ( $woocommerce_fields as $woo_fields ) {
		array_push( $fields, $woo_fields );
	}

	return $fields;
}

/**
 * All WooCommerce billing fields
 *
 * @return array
 */
function ur_get_woocommerce_billing_fields() {
	return apply_filters(
		'user_registration_woocommerce_billing_fields',
		array(
			'billing_address_title',
			'billing_country',
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_email',
			'billing_phone',
			'separate_shipping',
		)
	);
}

/**
 * All WooCommerce Shipping fields
 *
 * @return array
 */
function ur_get_woocommerce_shipping_fields() {
	return apply_filters(
		'user_registration_woocommerce_shipping_fields',
		array(
			'shipping_address_title',
			'shipping_country',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode',
		)
	);
}

/**
 * Template for myaccount page
 *
 * @return array
 */
function ur_woocommerce_template() {
	return apply_filters(
		'user_registration_woocommercetemplate',
		array(
			'vertical'   => __( 'Vertical', 'user-registration-woocommerce' ),
			'horizontal' => __( 'Horizontal', 'user-registration-woocommerce' ),
		)
	);
}

/**
 * get endpoint url
 *
 * @since 1.0.4
 *
 * @param  string $endpoint
 * @param  string $value
 * @param  string $url
 * @return string
 */
function ur_wc_get_endpoint_url( $endpoint, $value, $url ) {

	$myaccount_page = get_post( get_option( 'woocommerce_myaccount_page_id' ) );
	$matched        = 0;

	if ( ! empty( $myaccount_page ) ) {
		$matched = preg_match( '/\[user_registration_my_account(\s\S+){0,3}\]|\[user_registration_login(\s\S+){0,3}\]/', $myaccount_page->post_content );
	}

	if ( 1 <= absint( $matched ) ) {

		$permalink = ur_get_page_permalink( 'myaccount' );

		// Map endpoint to options
		$query_vars = WC()->query->get_query_vars();
		$endpoint   = ! empty( $query_vars[ $endpoint ] ) ? $query_vars[ $endpoint ] : $endpoint;

		if ( get_option( 'permalink_structure' ) ) {
			if ( strstr( $permalink, '?' ) ) {
				$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
				$permalink    = current( explode( '?', $permalink ) );
			} else {
				$query_string = '';
			}
			$url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
		} else {
			$url = add_query_arg( $endpoint, $value, $permalink );
		}
	}

	return $url;
}

/**
 * Get form fields.
 *
 * @param int $form_id Registration Form ID.
 * @return array|WP_Error
 */
function urwc_get_form_fields( $form_id ) {
	$form   = get_post( $form_id );
	$fields = array();

	if ( $form && 'user_registration' === $form->post_type ) {
		$form_field_array = json_decode( $form->post_content );

		if ( $form_field_array ) {

			foreach ( $form_field_array as $post_content_row ) {
				foreach ( $post_content_row as $post_content_grid ) {
					foreach ( $post_content_grid as $field ) {
						if ( isset( $field->field_key ) && ! in_array( $field->field_key, urwc_get_excluded_fields() ) ) {
							$field_name        = isset( $field->general_setting->field_name ) ? $field->general_setting->field_name : '';
							$field_label       = isset( $field->general_setting->label ) ? $field->general_setting->label : '';
							$field_description = isset( $field->general_setting->description ) ? $field->general_setting->description : '';
							$placeholder       = isset( $field->general_setting->placeholder ) ? $field->general_setting->placeholder : '';
							$options           = isset( $field->general_setting->options ) ? $field->general_setting->options : array();
							$field_key         = isset( $field->field_key ) ? ( $field->field_key ) : '';
							$field_type        = isset( $field->field_key ) ? ur_get_field_type( $field_key ) : '';
							$required          = isset( $field->general_setting->required ) ? $field->general_setting->required : '';
							$required          = 'yes' == $required ? true : false;
							$enable_cl         = isset( $field->advance_setting->enable_conditional_logic ) && ( '1' === $field->advance_setting->enable_conditional_logic || 'on' === $field->advance_setting->enable_conditional_logic ) ? true : false;
							$cl_map            = isset( $field->advance_setting->cl_map ) ? $field->advance_setting->cl_map : '';
							$custom_attributes = isset( $field->general_setting->custom_attributes ) ? $field->general_setting->custom_attributes : array();

							if ( empty( $field_label ) ) {
								$field_label_array = explode( '_', $field_name );
								$field_label       = join( ' ', array_map( 'ucwords', $field_label_array ) );
							}

							if ( ! empty( $field_name ) ) {
								$extra_params = array();

								switch ( $field_key ) {

									case 'radio':
									case 'select':
										$advanced_options        = isset( $field->advance_setting->options ) ? $field->advance_setting->options : '';
										$advanced_options        = explode( ',', $advanced_options );
										$extra_params['options'] = ! empty( $options ) ? $options : $advanced_options;
										$extra_params['options'] = array_map( 'trim', $extra_params['options'] );

										$extra_params['options'] = array_combine( $extra_params['options'], $extra_params['options'] );

										break;

									case 'checkbox':
										$advanced_options        = isset( $field->advance_setting->choices ) ? $field->advance_setting->choices : '';
										$advanced_options        = explode( ',', $advanced_options );
										$extra_params['options'] = ! empty( $options ) ? $options : $advanced_options;
										$extra_params['options'] = array_map( 'trim', $extra_params['options'] );

										$extra_params['options'] = array_combine( $extra_params['options'], $extra_params['options'] );

										break;

									case 'date':
										$date_format       = isset( $field->advance_setting->date_format ) ? $field->advance_setting->date_format : '';
										$min_date          = isset( $field->advance_setting->min_date ) ? str_replace( '/', '-', $field->advance_setting->min_date ) : '';
										$max_date          = isset( $field->advance_setting->max_date ) ? str_replace( '/', '-', $field->advance_setting->max_date ) : '';
										$set_current_date  = isset( $field->advance_setting->set_current_date ) ? $field->advance_setting->set_current_date : '';
										$enable_date_range = isset( $field->advance_setting->enable_date_range ) ? $field->advance_setting->enable_date_range : '';
										$extra_params['custom_attributes']['data-date-format'] = $date_format;

										if ( isset( $field->advance_setting->enable_min_max ) && 'true' === $field->advance_setting->enable_min_max ) {
											$extra_params['custom_attributes']['data-min-date'] = '' !== $min_date ? date_i18n( $date_format, strtotime( $min_date ) ) : '';
											$extra_params['custom_attributes']['data-max-date'] = '' !== $max_date ? date_i18n( $date_format, strtotime( $max_date ) ) : '';
										}
										$extra_params['custom_attributes']['data-default-date'] = $set_current_date;
										$extra_params['custom_attributes']['data-mode']         = $enable_date_range;
										break;

									case 'country':
										$class_name              = ur_load_form_field_class( $field_key );
										$extra_params['options'] = $class_name::get_instance()->get_selected_countries( $form_id, $field_name );
										break;

									case 'file':
										$extra_params['max_files'] = isset( $field->general_setting->max_files ) ? $field->general_setting->max_files : '';
										break;

									case 'phone':
										$extra_params['phone_format'] = isset( $field->general_setting->phone_format ) ? $field->general_setting->phone_format : '';
										break;

									default:
										break;
								}

								$extra_params['default'] = isset( $all_meta_value[ 'user_registration_' . $field_name ][0] ) ? $all_meta_value[ 'user_registration_' . $field_name ][0] : '';

								$fields[ 'user_registration_' . $field_name ] = array(
									'label'       => ur_string_translation( $form_id, 'user_registration_' . $field_name . '_label', $field_label ),
									'description' => ur_string_translation( $form_id, 'user_registration_' . $field_name . '_description', $field_description ),
									'type'        => $field_type,
									'placeholder' => ur_string_translation( $form_id, 'user_registration_' . $field_name . '_placeholder', $placeholder ),
									'field_key'   => $field_key,
									'required'    => $required,
								);

								if ( true === $enable_cl ) {
									$fields[ 'user_registration_' . $field_name ]['enable_conditional_logic'] = $enable_cl;
									$fields[ 'user_registration_' . $field_name ]['cl_map']                   = $cl_map;
								}

								if ( count( $custom_attributes ) > 0 ) {
									$extra_params['custom_attributes'] = $custom_attributes;
								}

								if ( isset( $fields[ 'user_registration_' . $field_name ] ) && count( $extra_params ) > 0 ) {
									$fields[ 'user_registration_' . $field_name ] = array_merge( $fields[ 'user_registration_' . $field_name ], $extra_params );
								}
								$filter_data = array(
									'fields'     => $fields,
									'field'      => $field,
									'field_name' => $field_name,
								);

								$filtered_data_array = apply_filters( 'user_registration_profile_account_filter_' . $field_key, $filter_data, $form_id );
								if ( isset( $filtered_data_array['fields'] ) ) {
									$fields = $filtered_data_array['fields'];
								}
							}// End if().
						}
					}// End foreach().
				}// End foreach().
			}// End foreach().
		}
	} else {
		return new WP_Error( 'form-not-found', __( 'Form not found!', 'user-registration-woocommerce' ) );
	}

	return apply_filters( 'user_registration_woocommerce_field_list', $fields );
}

/**
 * Get fields to exclude from field listing in settings.
 *
 * @return array
 */
function urwc_get_excluded_fields() {
	$excluded_fields = array(
		'display_name',
		'first_name',
		'last_name',
		'user_login',
		'user_pass',
		'user_confirm_password',
		'user_email',
		'user_confirm_email',
	);

	$excluded_fields = array_merge( $excluded_fields, ur_get_woocommerce_billing_fields() );
	$excluded_fields = array_merge( $excluded_fields, ur_get_woocommerce_shipping_fields() );

	return apply_filters( 'user_registration_woocommerce_excluded_fields', $excluded_fields );
}
