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
add_action( 'user_registration_init', 'urwc_sync_checkout_registration_migration' );
add_filter( 'user_registration_is_field_required', 'urwc_is_field_required', 10, 3 );

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

	$ur_pro_plugins_path = WP_PLUGIN_DIR . URWC_DS . 'user-registration-pro' . URWC_DS . 'user-registration.php';

	if ( ! file_exists( $ur_pro_plugins_path ) ) {
		return;
	}

	$ur_pro_plugin_file_path = 'user-registration-pro/user-registration.php';

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( $ur_pro_plugin_file_path ) ) {
		return;
	}

	$wc_plugins_path = WP_PLUGIN_DIR . URWC_DS . 'woocommerce' . URWC_DS . 'woocommerce.php';

	if ( ! file_exists( $wc_plugins_path ) ) {
		return ;
	}

	$wc_plugins_path = 'woocommerce/woocommerce.php';

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( $wc_plugins_path ) ) {
		return;
	}

	if ( function_exists( 'UR' ) ) {
		$user_registration_version = UR()->version;
	} else {
		$user_registration_version = get_option( 'user_registration_version' );
	}

	if ( version_compare( $user_registration_version, '4.0.0', '<' ) ) {
		return;
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
		case 'billing_phone':
			if ( is_plugin_active( 'user-registration-advanced-fields/user-registration-advanced-fields.php' ) ) {
				$field_type = 'phone';
			}
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
	$class = 'error notice is-dismissible';
	$message = urwc_is_compatible();
	if ( ! is_plugin_active('user-registration-pro/user-registration.php') && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		printf( '<div class="' . esc_attr( $class ) . '"><p>' . sprintf( esc_html__( 'User Registration WooCommerce  requires %1s version 4.0.0 or greater and %2s to work', 'user-registration-woocommerce' ), '<a href="https://wpuserregistration.com/" target="_blank">' . esc_html__( 'User Registration Pro', 'user-registration-woocommerce' ) . '</a>','<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">' . esc_html__( 'WooCommerce', 'user-registration-woocommerce' ) . '</a>' ) . '</p></div>' );
	}elseif ( ! is_plugin_active('user-registration-pro/user-registration.php') && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		printf( '<div class="' . esc_attr( $class ) . '"><p>' . sprintf( esc_html__( 'User Registration WooCommerce  requires %1s version 4.0.0 or greater to work', 'user-registration-woocommerce' ), '<a href="https://wpuserregistration.com/" target="_blank">' . esc_html__( 'User Registration Pro', 'user-registration-woocommerce' ) . '</a>' ) . '</p></div>' );
	}elseif( is_plugin_active('user-registration-pro/user-registration.php') && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		printf( '<div class="' . esc_attr( $class ) . '"><p>' . sprintf( esc_html__( 'User Registration WooCommerce  requires %1s to work', 'user-registration-woocommerce' ), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">' . esc_html__( 'WooCommerce', 'user-registration-woocommerce' ) . '</a>' ) . '</p></div>' );
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
			'title'    => __( 'WooCommerce', 'user-registration-woocommerce' ),
			'sections' => array(
				'user_registration_woocommerce_settings' => array(
					'title'    => esc_html__( 'WooCommerce Sync', 'user-registration-woocommerce' ),
					'type'     => 'card',
					'desc'     => '',
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
							'title'     => __( 'Enable Login Options', 'user-registration-woocommerce' ),
							'desc_tip'  => __( 'Apply user login option on checkout.', 'user-registration-woocommerce' ),
							'desc'      => __( 'Check this option to apply default user login option during checkout.', 'user-registration-woocommerce' ),
							'id'        => 'user_registration_woocommrece_settings_login_option',
							'type'      => 'toggle',
							'css'       => 'min-width: 350px;',
							'row_class' => '',
							'default'   => 'false',
						),
						array(
							'title'     => __( 'Replace registration page', 'user-registration-woocommerce' ),
							'desc_tip'  => __( 'Replace default WooCommerce login and registration form with User Registration form and login', 'user-registration-woocommerce' ),
							'desc'      => __( 'Check this option to replace default WooCommerce\'s login and registration page', 'user-registration-woocommerce' ),
							'id'        => 'user_registration_woocommrece_settings_replace_login_registration',
							'type'      => 'toggle',
							'css'       => 'min-width: 350px;',
							'row_class' => ur_string_to_bool( get_option( 'user_registration_woocommerce_settings_form', false ) ) ? 'ur-setting-hidden' : '',
							'default'   => 'false',
						),
						array(
							'title'     => __( 'Replace checkout login', 'user-registration-woocommerce' ),
							'desc_tip'  => __( 'Replace default WooCommerce login in checkout page with User Registration login', 'user-registration-woocommerce' ),
							'desc'      => __( 'Check this option to replace default WooCommerce\'s login in checkout page', 'user-registration-woocommerce' ),
							'id'        => 'user_registration_woocommrece_settings_replace_checkout_login',
							'type'      => 'toggle',
							'css'       => 'min-width: 350px;',
							'row_class' => ( get_option( 'user_registration_woocommerce_settings_form', '0' ) === '0' ) ? 'ur-setting-hidden' : '',
							'default'   => 'false',
						),
						array(
							'title'             => __( 'Sync checkout registration', 'user-registration-woocommerce' ),
							'desc_tip'          => __( 'This option lets you select registration form fields to be synced with WooCommerce Checkout page\'s registration form.', 'user-registration-woocommerce' ),
							'desc'              => __( 'Check this option to sync user registration form with Woocommerce checkout registration', 'user-registration-woocommerce' ),
							'id'                => 'user_registration_woocommrece_settings_sync_checkout',
							'type'              => 'toggle',
							'css'               => 'min-width: 350px;',
							'row_class'         => ur_string_to_bool( get_option( 'user_registration_woocommerce_settings_form', false ) ) ? 'ur-setting-hidden' : '',
							'default'           => 'false',
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
							$required          = ur_string_to_bool( $required );
							$enable_cl         = isset( $field->advance_setting->enable_conditional_logic ) && ur_string_to_bool( $field->advance_setting->enable_conditional_logic );
							$cl_map            = isset( $field->advance_setting->cl_map ) ? $field->advance_setting->cl_map : '';
							$custom_attributes = isset( $field->general_setting->custom_attributes ) ? $field->general_setting->custom_attributes : array();
							$default           = '';

							if ( isset( $field->general_setting->default_value ) ) {
								$default = $field->general_setting->default_value;
							} elseif ( isset( $field->advance_setting->default_value ) ) {
								$default = $field->advance_setting->default_value;
							}

							if ( empty( $field_label ) ) {
								$field_label_array = explode( '_', $field_name );
								$field_label       = join( ' ', array_map( 'ucwords', $field_label_array ) );
							}

							if ( ! empty( $field_name ) ) {
								$extra_params = array();

								switch ( $field_key ) {

									case 'text':
									case 'textarea':
									case 'password':
										if ( isset( $field->advance_setting->limit_length ) && $field->advance_setting->limit_length ) {
											if ( isset( $field->advance_setting->limit_length_limit_count ) && isset( $field->advance_setting->limit_length_limit_mode ) ) {
												if ( 'characters' === $field->advance_setting->limit_length_limit_mode ) {
													$custom_attributes['maxlength'] = $field->advance_setting->limit_length_limit_count;
												} elseif ( 'words' === $field->advance_setting->limit_length_limit_mode ) {
													$custom_attributes['max-words'] = $field->advance_setting->limit_length_limit_count;
												}
											}
										}

										if ( isset( $field->advance_setting->minimum_length ) && $field->advance_setting->minimum_length ) {
											if ( isset( $field->advance_setting->minimum_length_limit_count ) && isset( $field->advance_setting->minimum_length_limit_mode ) ) {
												if ( 'characters' === $field->advance_setting->minimum_length_limit_mode ) {
													$custom_attributes['minlength'] = $field->advance_setting->minimum_length_limit_count;
												} elseif ( 'words' === $field->advance_setting->minimum_length_limit_mode ) {
													$custom_attributes['data-min-words'] = $field->advance_setting->minimum_length_limit_count;
												}
											}
										}

										if ( isset( $field->advance_setting->size ) ) {
											$custom_attributes['maxlength'] = $field->advance_setting->size;
										}

										break;

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

										if ( isset( $field->advance_setting->enable_min_max ) && ur_string_to_bool( $field->advance_setting->enable_min_max ) ) {
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
										$extra_params['valid_file_type'] = isset( $field->advance_setting->valid_file_type ) ? $field->advance_setting->valid_file_type : '';
										break;

									case 'phone':
										$extra_params['phone_format'] = isset( $field->general_setting->phone_format ) ? $field->general_setting->phone_format : '';
										break;
									case 'learndash_course':
										$extra_params['learndash_field_type'] = isset( $field->general_setting->learndash_field_type ) ? $field->general_setting->learndash_field_type : '';
										if ( isset( $field->advance_setting->enroll_type ) ) {
											if ( 'courses' === $field->advance_setting->enroll_type ) {
												$extra_params['options'] = function_exists( 'get_courses_list' ) ? get_courses_list() : array();
											} else {
												$extra_params['options'] = function_exists( 'get_groups_list' ) ? get_groups_list() : array();
											}
										}
										break;
									case 'number':
										$custom_attributes['min'] = isset( $field->advance_setting->min ) ? $field->advance_setting->min : '';
										$custom_attributes['max'] = isset( $field->advance_setting->max ) ? $field->advance_setting->max : '';
										$custom_attributes['step'] = isset( $field->advance_setting->step ) ? $field->advance_setting->step : '';
										break;

									case 'range':
										$extra_params['range_min'] = isset( $field->advance_setting->range_min ) ? $field->advance_setting->range_min : '';
										$extra_params['range_min'] = ! empty( $extra_params['range_min'] ) ? $extra_params['range_min'] : '0';
										$extra_params['range_max'] = isset( $field->advance_setting->range_max ) ? $field->advance_setting->range_max : '';
										$extra_params['range_max'] = ! empty( $extra_params['range_max'] ) ? $extra_params['range_max'] : '10';
										$extra_params['range_step'] = isset( $field->advance_setting->range_step ) ? $field->advance_setting->range_step : '';
										$extra_params['range_step'] = ! empty( $extra_params['range_step'] ) ? $extra_params['range_step'] : '1';
										break;

									default:
										break;
								}

								$extra_params['default'] = $default;

								$fields[ 'user_registration_' . $field_name ] = array(
									'label'       => ur_string_translation( $form_id, 'user_registration_' . $field_name . '_label', $field_label ),
									'description' => ur_string_translation( $form_id, 'user_registration_' . $field_name . '_description', $field_description ),
									'type'        => $field_type,
									'placeholder' => ur_string_translation( $form_id, 'user_registration_' . $field_name . '_placeholder', $placeholder ),
									'field_key'   => $field_key,
									'field_name'   => $field_name,
									'required'    => $required,
									'general_setting'    => $field->general_setting,
									'advance_setting'    => $field->advance_setting,
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

/**
 * URWC sync checkout registraton migration
 */

if ( ! function_exists( 'urwc_sync_checkout_registration_migration' ) ) {
	/**
	 *  Update option user_registration_woocommerce_checkout_fields value to key-value pair.
	 */
	function urwc_sync_checkout_registration_migration() {
		$form_id             = get_option( 'user_registration_woocommerce_settings_form', 0 );
		$fields              = urwc_get_form_fields( $form_id );
		$saved_form_fields   = get_option( 'user_registration_woocommerce_checkout_fields', array() );
		$migrate_form_fields = array();

		if ( ! get_option( 'user_registration_woocommerce_checkout_fields_migrated', false ) ) {
			foreach ( $fields as $field_name => $field_details ) {
				if ( in_array( $field_name, $saved_form_fields ) ) {
					$migrate_form_fields[ 'form-' . $form_id ][] = $field_name;
					update_option( 'user_registration_woocommerce_checkout_fields', $migrate_form_fields );
				}
			}

			update_option( 'user_registration_woocommerce_checkout_fields_migrated', true );
		}
	}
}

/**
 * Map checkout form fields.
 *
 * @param array $checkout_form_fields Form Fields.
 * @param int $form_id  Form ID.
 * @return array $mapped_checkout_form_fields.
 */
if ( ! function_exists( 'map_checkout_form_fields' ) ) {

	function map_checkout_form_fields( $checkout_form_fields, $form_id ) {
		$saved_checkout_form_id      = 'form-' . $form_id;
		$mapped_checkout_form_fields = array();
		foreach ( $checkout_form_fields as $mapped_key => $mapped_field_value ) {
			if ( $mapped_key == $saved_checkout_form_id ) {
				$mapped_checkout_form_fields = $mapped_field_value;
			}
		}
		return $mapped_checkout_form_fields;
	}
}


/**
 * Make shipping fields non required when separate shipping is disabled.
 *
 * @param [bool]   $is_required Is field required.
 * @param [object] $field field object.
 * @param [array]  $form_data Form Data.
 *
 * @since 1.3.2
 *
 * @return bool
 */
function urwc_is_field_required( $is_required, $field, $form_data ) {
	$field_key = $field->field_key;

	if ( 0 === strpos( $field_key, 'shipping_' ) ) {

		foreach ( $form_data as $data ) {
			if ( 'separate_shipping' === $data->field_name ) {
				$value = ur_string_to_bool( $data->value );
				if ( ! $value ) {
					$is_required = false;
				}

				break;
			}
		}
	}

	return $is_required;
}

if ( ! function_exists( 'urwc_format_field_values' ) ) {

	/**
	 * Get field type by meta key
	 *
	 * @param string $field_key Field key.
	 * @param string $field_value Field's value .
	 */
	function urwc_format_field_values( $field_key, $field_value ) {
		switch ( $field_key ) {
			case 'checkbox':
			case 'multi_select2':
				$field_value = ( is_array( $field_value ) && ! empty( $field_value ) ) ? implode( ', ', $field_value ) : $field_value;

				break;
			case 'country':
				$countries = UR_Form_Field_Country::get_instance()->get_country();
				if ( ! isset( $countries[ $field_value ] ) ) {
					$key = array_search( $field_value, $countries, true );
					if ( $key ) {
						$field_value = $key;
					}
				}
				$field_value = isset( $countries[ $field_value ] ) ? $countries[ $field_value ] : '';
				break;
			case 'file':
				$attachment_ids = is_array( $field_value ) ? $field_value : explode( ',', $field_value );
				$links          = array();

				$upload_path = apply_filters( 'user_registration_woocommerce_file_upload_url', UR_UPLOAD_PATH . 'file-uploads/' ); /*Get path of upload dir of WordPress*/

				// Checks if the upload directory exists and create one if not.
				if ( ! file_exists( $upload_path ) ) {
					wp_mkdir_p( $upload_path );
				}

				foreach ( $attachment_ids as $attachment_id ) {

					if ( ! is_numeric( $attachment_id ) ) {
						$upload = maybe_unserialize( crypt_the_string( $attachment_id, 'd' ) );

						if ( isset( $upload['file_name'] ) && isset( $upload['file_path'] ) && isset( $upload['file_extension'] ) ) {
							$file_name   = wp_unique_filename( $upload_path, $upload['file_name'] );
							$file_path   = $upload_path . sanitize_file_name( $file_name );
							// Check the type of file. We'll use this as the 'post_mime_type'.
							$filetype = wp_check_filetype( basename( $file_name ), null );
							$moved    = rename( $upload['file_path'], $file_path );

							if ( $moved ) {
								$file_url = get_home_url() . '/wp-content/uploads/user_registration_uploads/file-uploads/'. $file_name ;
							}

							$file_href = '<a href="'. $file_url .'" target="_blank">'. $file_name .'</a>';

							array_push( $links, $file_href );

						}
					} else {
						$links[] = $attachment_id;
					}
				}

				$field_value = implode( ', ', $links );

				break;
			case 'privacy_policy':
				if ( ur_string_to_bool( $field_value ) ) {
					$field_value = 'Checked';
				} else {
					$field_value = 'Not Checked';
				}
				break;
			case 'wysiwyg':
				$field_value = html_entity_decode( $field_value );
				break;
			case 'profile_picture':
				$field_value = '<img class="profile-preview" alt="Profile Picture" width="50px" height="50px" src="' . ( is_numeric( $field_value ) ? esc_url( wp_get_attachment_url( $field_value ) ) : esc_url( $field_value ) ) . '" />';
				$field_value = wp_kses_post( $field_value );
				break;
			default:
				$field_value = $field_value;
				break;
		}

		return $field_value;
	}
}
