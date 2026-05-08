<?php
/**
 * User Registration Frontend Listing Shortcodes.
 *
 * @class    Shortcodes
 * @version  1.0.0
 * @package  URFrontendListing/Classes
 * @category Class
 * @author   WPEverest
 */

namespace WPEverest\URFrontendListing\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcodes Class
 */
class Shortcodes {

	public static $parts = false;

	/**
	 * Init Shortcodes.
	 */
	public function __construct() {
		$shortcodes = array(
			'user_registration_frontend_list'    => __CLASS__ . '::frontend_list',
			'user_registration_member_directory' => __CLASS__ . '::frontend_list',
			'user_registration_membership_frontend_listing_user_profile' => __CLASS__ . '::urm_frontend_listing_user_profile',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param string[] $function
	 * @param array    $atts (default: array())
	 * @param array    $wrapper
	 *
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'user-registration-frontend-list',
			'before' => null,
			'after'  => null,
		)
	) {
		ob_start();

		echo empty( $wrapper['before'] ) ? '<div id="user-registration-frontend_list" class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		return ob_get_clean();
	}

	/**
	 * User Registration Frontend List shortcode.
	 *
	 * @param mixed $atts
	 */
	public static function frontend_list( $atts ) {

		if ( empty( $atts ) || ! isset( $atts['id'] ) ) {
			return '';
		}

		ob_start();
		self::render_frontend_list( $atts['id'] );
		return ob_get_clean();
	}

	/**
	 * Decode a stored meta value into a clean string array.
	 *
	 * Supports:
	 * - array
	 * - serialized array
	 * - JSON array string (with or without slashes)
	 *
	 * @since x.x.x
	 *
	 * @param mixed $raw Meta value.
	 * @return array
	 */
	private static function urfl_decode_meta_list( $raw ) {
		if ( is_string( $raw ) ) {
			$raw = wp_unslash( $raw );
			$raw = maybe_unserialize( $raw );
		}

		if ( is_array( $raw ) ) {
			$raw = array_values( $raw );
			$raw = array_map( 'strval', $raw );
			$raw = array_filter( $raw, 'strlen' );
			return array_values( $raw );
		}

		if ( is_string( $raw ) && '' !== $raw ) {
			$tmp = json_decode( $raw, true );
			if ( is_array( $tmp ) ) {
				$tmp = array_values( $tmp );
				$tmp = array_map( 'strval', $tmp );
				$tmp = array_filter( $tmp, 'strlen' );
				return array_values( $tmp );
			}
		}

		return array();
	}

	/**
	 * Build ordered field keys from selected + order meta.
	 *
	 * @since x.x.x
	 *
	 * @param array $selected Selected field keys.
	 * @param mixed $order_raw Order meta raw value.
	 * @return array
	 */
	private static function urfl_build_ordered_fields_to_include( $selected, $order_raw ) {
		$selected = is_array( $selected ) ? $selected : self::urfl_decode_meta_list( $selected );
		$selected = array_values( array_filter( array_map( 'strval', $selected ), 'strlen' ) );

		$order_decoded = self::urfl_decode_meta_list( $order_raw );

		if ( empty( $order_decoded ) ) {
			return $selected;
		}

		$fields_to_include = array_values(
			array_filter(
				$order_decoded,
				function ( $k ) use ( $selected ) {
					return in_array( (string) $k, $selected, true );
				}
			)
		);

		foreach ( $selected as $k ) {
			if ( ! in_array( (string) $k, $fields_to_include, true ) ) {
				$fields_to_include[] = (string) $k;
			}
		}

		return $fields_to_include;
	}

	/**
	 * Output for frontend list.
	 */
	public static function render_frontend_list( $list_id ) {

		$post                = get_post( $list_id );
		$post_id             = (int) $list_id;
		$display_to_guest    = get_post_meta( $post_id, 'user_registration_frontend_listings_allow_guest', true );
		$is_new_installation = ur_string_to_bool( get_option( 'urm_is_new_installation', '' ) );
		$access_denied       = get_post_meta( $post_id, 'user_registration_frontend_listings_access_denied_text', true );

		if ( $is_new_installation ) {
			$display_to_guest = true;
		} else {
			$display_to_guest = ur_string_to_bool( $display_to_guest );
		}

		$script_data = array(
			'ajax_url'                                  => admin_url( 'admin-ajax.php' ),
			'ur_frontend_listing_user_data_security'    => wp_create_nonce( 'ur_frontend_listing_user_data_nonce' ),
			'ur_frontend_listing_filtered_user_message' => get_post_meta( $post_id, 'user_registration_frontend_listings_filtered_user_message', true ),
		);

		if ( function_exists( 'urfl_is_directory_restricted' ) ) {
			$restriction = urfl_is_directory_restricted( $post_id );

			if ( is_array( $restriction ) && ! empty( $restriction['restricted'] ) ) {
				$message = isset( $restriction['message'] ) && '' !== $restriction['message']
					? $restriction['message']
					: $access_denied;

				if ( function_exists( 'urcr_get_template' ) ) {
					$login_page_id        = get_option( 'user_registration_login_page_id' );
					$registration_page_id = get_option( 'user_registration_member_registration_page_id' );
					$login_url            = $login_page_id ? get_permalink( $login_page_id ) : wp_login_url();
					$signup_url           = $registration_page_id ? get_permalink( $registration_page_id ) : ( $login_page_id ? get_permalink( $login_page_id ) : wp_registration_url() );

					echo '<div class="user-registration-frontend-listing-error-wrapper">';
					urcr_get_template(
						'base-restriction-template.php',
						array(
							'message'    => $message,
							'login_url'  => $login_url,
							'signup_url' => $signup_url,
						)
					);
					echo '</div>';
				} else {
					echo '<div class="user-registration-error user-registration-frontend-listing-error">' . wp_kses_post( $message ) . '</div>';
				}

				return;
			}
		}

		wp_enqueue_style( 'user-registration-frontend-listing-frontend-style' );

		if ( ! $display_to_guest && ! is_user_logged_in() ) {
			echo '<div class="user-registration-error user-registration-frontend-listing-error">' . esc_html( $access_denied ) . '</div>';
			return;
		}

		if ( isset( $_GET['user_id'] ) && intval( $_GET['user_id'] ) ) {

			$view_id = isset( $_GET['list_id'] ) ? (int) wp_unslash( $_GET['list_id'] ) : 0;
			$user_id = (int) wp_unslash( $_GET['user_id'] );

			if ( $view_id !== $post_id ) {
				return;
			}

			if ( ! $user_id || ! get_userdata( $user_id ) ) {
				echo '<p>' . esc_html__( 'Invalid user ID.', 'user-registration-frontend-listing' ) . '</p>';
				return;
			}

			wp_enqueue_style( 'user-registration-pro-frontend-style' );

			$new_key = 'user_registration_frontend_listings_view_profile_display_profile_picture';
			$old_key = 'user_registration_frontend_listings_display_profile_picture';

			$new_exists = metadata_exists( 'post', $post_id, $new_key );
			$raw_val    = $new_exists ? get_post_meta( $post_id, $new_key, true ) : get_post_meta( $post_id, $old_key, true );

			$show_profile_picture = function_exists( 'ur_string_to_bool' ) ? ur_string_to_bool( $raw_val ) : (bool) $raw_val;

			$order_raw = get_post_meta( $post_id, 'user_registration_frontend_listings_card_fields_order', true );
			$selected  = get_post_meta( $post_id, 'user_registration_frontend_listings_card_fields', true );

			$fields_to_include = self::urfl_build_ordered_fields_to_include( $selected, $order_raw );
			if ( empty( $fields_to_include ) && function_exists( 'ur_frontend_listing_include_fields_in_view_profile' ) ) {
				$fields_to_include = array_keys( ur_frontend_listing_include_fields_in_view_profile() );
			}

			$user_data_to_show = self::build_user_data_to_show_compat( $post_id, $user_id, $fields_to_include );

			ur_get_template(
				'pro/user-registration-pro-view-user.php',
				array(
					'user_data_to_show'    => $user_data_to_show,
					'show_profile_picture' => $show_profile_picture,
					'user_id'              => $user_id,
				),
				'user-registration-pro',
				UR_TEMPLATE_PATH
			);

			return;
		}

		wp_enqueue_script( 'user-registration-frontend-listing-frontend-script' );

		echo '<script id="user-registration-frontend-listing-frontend-script">';
		echo 'const user_registration_frontend_listings_frontend_script_data = ' . wp_json_encode( $script_data ) . ';';
		echo '</script>';

		if ( $post ) {
			ur_get_template(
				'pro/frontend-listing/user-registration-frontend-listing-layout.php',
				array( 'post_id' => $post_id ),
				'user-registration-pro',
				UR_TEMPLATE_PATH
			);
		} else {
			echo '<p>' . esc_html__( 'Frontend List not found', 'user-registration-frontend-listing' ) . '</p>';
		}
	}

	/**
	 * Build user data for View Profile with backward compatibility.
	 *
	 * - If legacy UR Pro helpers exist, use legacy pipeline (old installs).
	 * - Otherwise fallback to new pattern-based builder (new installs).
	 *
	 * @param int   $post_id
	 * @param int   $user_id
	 * @param array $fields_to_include
	 * @return array
	 */
	private static function build_user_data_to_show_compat( $post_id, $user_id, $fields_to_include ) {

		$can_use_legacy =
			function_exists( 'ur_get_user_extra_fields' ) &&
			function_exists( 'ur_get_form_id_by_userid' ) &&
			function_exists( 'user_registration_pro_profile_details_form_fields' ) &&
			function_exists( 'user_registration_pro_profile_details_form_keys_to_include' ) &&
			function_exists( 'user_registration_pro_profile_details_form_field_datas' ) &&
			function_exists( 'ur_frontend_listing_include_fields_in_view_profile' );

		if ( $can_use_legacy ) {

			$user_extra_fields = ur_get_user_extra_fields( $user_id );
			$user_obj          = get_userdata( $user_id );

			if ( ! $user_obj ) {
				return array();
			}

			$user_data                = (array) $user_obj->data;
			$user_data['first_name']  = get_user_meta( $user_id, 'first_name', true );
			$user_data['last_name']   = get_user_meta( $user_id, 'last_name', true );
			$user_data['description'] = get_user_meta( $user_id, 'description', true );
			$user_data['nickname']    = get_user_meta( $user_id, 'nickname', true );
			$user_data                = array_merge( $user_data, (array) $user_extra_fields );

			$form_id = ur_get_form_id_by_userid( $user_id );

			if ( empty( $form_id ) ) {
				return self::build_user_data_to_show_new( $user_id, $fields_to_include );
			}

			$form_field_data_array = user_registration_pro_profile_details_form_fields( $form_id, $fields_to_include );
			$field_keys_to_include = user_registration_pro_profile_details_form_keys_to_include( $fields_to_include, $form_field_data_array );

			foreach ( (array) $field_keys_to_include as $meta_key ) {
				if ( preg_match( '/^(billing_|shipping_).+|.+_shipping$/', (string) $meta_key ) ) {
					$user_data[ $meta_key ] = get_user_meta( $user_id, (string) $meta_key, true );
				}
			}

			$legacy = user_registration_pro_profile_details_form_field_datas(
				$form_id,
				$user_data,
				$form_field_data_array,
				$field_keys_to_include
			);

			$legacy = self::normalize_legacy_output( $legacy );
			$legacy = self::urfl_ensure_membership_row_legacy( $legacy, $user_id, $fields_to_include );
			$legacy = self::reorder_rows_by_fields_to_include( $legacy, $fields_to_include );

			return $legacy;

		}

		return self::build_user_data_to_show_new( $user_id, $fields_to_include );
	}

	/**
	 * Normalize legacy output so no value is an array/object.
	 *
	 * @param array $legacy
	 * @return array
	 */
	private static function normalize_legacy_output( $legacy ) {

		if ( ! is_array( $legacy ) ) {
			return array();
		}

		foreach ( $legacy as $i => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			if ( isset( $row['value'] ) ) {
				$legacy[ $i ]['value'] = self::normalize_value( $row['value'] );
			}
		}

		return $legacy;
	}




	/**
	 * New pipeline: builds ordered rows: field_key, label, value (always string value).
	 *
	 * @param int   $user_id
	 * @param array $fields_to_include
	 * @return array
	 */
	private static function build_user_data_to_show_new( $user_id, $fields_to_include ) {

		if ( ! function_exists( 'ur_frontend_listing_include_fields_in_view_profile' ) ) {
			return array();
		}

		$labels_map = ur_frontend_listing_include_fields_in_view_profile();
		$ordered    = array();

		foreach ( (array) $fields_to_include as $key ) {
			$key = (string) $key;

			$value = self::get_view_profile_field_value( $user_id, $key );
			$value = self::normalize_value( $value );

			if ( '' === $value ) {
				continue;
			}

			$label = isset( $labels_map[ $key ] ) ? $labels_map[ $key ] : self::format_field_label( $key );

			$ordered[] = array(
				'field_key' => $key,
				'label'     => $label,
				'value'     => $value,
			);
		}

		return $ordered;
	}

	/**
	 * Get field value for View Profile page.
	 *
	 * Handles standard WP fields, standard user meta, and UR custom fields
	 * with dynamic numeric suffixes.
	 *
	 * @param int    $user_id   User ID.
	 * @param string $field_key Field key.
	 * @return mixed
	 */
	private static function get_view_profile_field_value( $user_id, $field_key ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return '';
		}

		$table_fields = array(
			'user_login',
			'user_email',
			'user_url',
			'display_name',
			'user_registered',
			'user_nicename',
		);

		if ( in_array( $field_key, $table_fields, true ) ) {
			return isset( $user->$field_key ) ? $user->$field_key : '';
		}

		$standard_meta = array( 'first_name', 'last_name', 'nickname', 'description' );
		if ( in_array( $field_key, $standard_meta, true ) ) {
			return get_user_meta( $user_id, $field_key, true );
		}

		if ( 'membership' === $field_key || preg_match( '/^membership(_field)?_\d+$/', (string) $field_key ) ) {
			$membership_id = get_user_meta( $user_id, 'ur_membership_id', true );

			return $membership_id ? get_the_title( (int) $membership_id ) : '';
		}

		$value = get_user_meta( $user_id, $field_key, true );
		if ( '' !== $value && ! is_null( $value ) ) {
			return $value;
		}

		if ( strpos( $field_key, 'user_registration_' ) !== 0 ) {
			$prefixed_key = 'user_registration_' . $field_key;
			$value        = get_user_meta( $user_id, $prefixed_key, true );
			if ( '' !== $value && ! is_null( $value ) ) {
				return $value;
			}
		}

		$value = self::get_user_meta_by_pattern( $user_id, $field_key );
		if ( '' !== $value && ! is_null( $value ) ) {
			return $value;
		}

		if ( preg_match( '/^(billing_|shipping_).+|.+_shipping$/', $field_key ) ) {
			$woo_value = get_user_meta( $user_id, $field_key, true );
			if ( '' !== $woo_value && ! is_null( $woo_value ) ) {
				return $woo_value;
			}
		}

		return '';
	}

	/**
	 * Get user meta value by matching pattern.
	 *
	 * Handles dynamic field keys like user_registration_textarea_1767956495
	 *
	 * @param int    $user_id
	 * @param string $field_key
	 * @return mixed
	 */
	private static function get_user_meta_by_pattern( $user_id, $field_key ) {
		global $wpdb;

		$base_key = str_replace( 'user_registration_', '', (string) $field_key );

		if ( preg_match( '/_\d+$/', (string) $field_key ) ) {
			$full_key = ( strpos( $field_key, 'user_registration_' ) === 0 )
				? (string) $field_key
				: 'user_registration_' . (string) $field_key;

			$value = get_user_meta( $user_id, $full_key, true );
			if ( '' !== $value && ! is_null( $value ) ) {
				return $value;
			}
		}

		$like     = $wpdb->esc_like( 'user_registration_' . $base_key . '_' ) . '%';
		$meta_key = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_key FROM {$wpdb->usermeta}
				 WHERE user_id = %d
				   AND meta_key LIKE %s
				 LIMIT 1",
				$user_id,
				$like
			)
		);

		if ( $meta_key ) {
			return get_user_meta( $user_id, $meta_key, true );
		}

		$exact_key = 'user_registration_' . $base_key;
		$value     = get_user_meta( $user_id, $exact_key, true );
		if ( '' !== $value && ! is_null( $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Format field key into readable label.
	 *
	 * @param string $key Field key.
	 * @return string
	 */
	private static function format_field_label( $key ) {

		$label = str_replace( array( 'user_registration_', 'ur_' ), '', (string) $key );
		$label = preg_replace( '/_\d+$/', '', $label );

		$label = str_replace( '_', ' ', $label );
		$label = ucwords( $label );

		return $label;
	}

	/**
	 * Normalize any value into safe display string.
	 *
	 * Prevents "Array to string conversion" everywhere.
	 *
	 * @param mixed $value
	 * @return string
	 */
	private static function normalize_value( $value ) {

		if ( is_null( $value ) ) {
			return '';
		}

		if ( is_array( $value ) ) {
			$flat = array();

			array_walk_recursive(
				$value,
				function ( $v ) use ( &$flat ) {
					if ( is_scalar( $v ) ) {
						$flat[] = (string) $v;
					}
				}
			);

			$flat = array_filter( $flat, 'strlen' );
			return implode( ', ', $flat );
		}

		if ( is_object( $value ) ) {
			if ( method_exists( $value, '__toString' ) ) {
				return (string) $value;
			}
			return '';
		}

		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * Shortcode to display user profile in frontend listing.
	 *
	 * @param  array $atts
	 */
	public static function urm_frontend_listing_user_profile( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id'              => 0,
				'show_profile_picture' => true,
			),
			$atts,
			'user_registration_membership_frontend_listing_user_profile'
		);

		$user_id              = intval( $atts['user_id'] );
		$show_profile_picture = filter_var( $atts['show_profile_picture'], FILTER_VALIDATE_BOOLEAN );

		if ( ! $user_id || ! get_userdata( $user_id ) ) {
			return '<p>' . esc_html__( 'Invalid user ID.', 'user-registration' ) . '</p>';
		}

		wp_enqueue_style( 'user-registration-pro-frontend-style' );

		if ( ! function_exists( 'ur_frontend_listing_include_fields_in_view_profile' ) ) {
			return '';
		}

		$labels_map        = ur_frontend_listing_include_fields_in_view_profile();
		$fields_to_include = array_keys( $labels_map );

		$ordered = array();

		foreach ( $fields_to_include as $key ) {
			$key   = (string) $key;
			$value = self::get_view_profile_field_value( $user_id, $key );
			$value = self::normalize_value( $value );

			if ( '' === $value ) {
				continue;
			}

			$label = isset( $labels_map[ $key ] ) ? $labels_map[ $key ] : self::format_field_label( $key );

			$ordered[] = array(
				'field_key' => $key,
				'label'     => $label,
				'value'     => $value,
			);
		}

		$ordered = apply_filters( 'urm_single_user_profile_details_field_keys', $ordered );

		ob_start();
		ur_get_template(
			'pro/user-registration-pro-view-user.php',
			array(
				'user_data_to_show'    => $ordered,
				'show_profile_picture' => $show_profile_picture,
				'user_id'              => $user_id,
			),
			'user-registration-pro',
			UR_TEMPLATE_PATH
		);

		return ob_get_clean();
	}

	private static function urfl_get_membership_title_for_user( $user_id, $selected_key = '' ) {

		$membership_id = get_user_meta( $user_id, 'ur_membership_id', true );
		if ( $membership_id ) {
			$t = get_the_title( (int) $membership_id );
			if ( $t ) {
				return (string) $t;
			}
		}

		if ( $selected_key ) {
			$keys_to_try = array( (string) $selected_key );

			if ( strpos( $selected_key, 'user_registration_' ) === 0 ) {
				$keys_to_try[] = substr( $selected_key, strlen( 'user_registration_' ) );
			} else {
				$keys_to_try[] = 'user_registration_' . $selected_key;
			}

			$raw = '';
			foreach ( $keys_to_try as $k ) {
				$v = get_user_meta( $user_id, $k, true );
				if ( '' !== $v && null !== $v ) {
					$raw = $v;
					break;
				}
			}

			if ( is_numeric( $raw ) ) {
				$t = get_the_title( (int) $raw );
				if ( $t ) {
					return (string) $t;
				}
			}

			if ( is_scalar( $raw ) && '' !== (string) $raw ) {
				return (string) $raw;
			}
		}

		return '';
	}

	private static function urfl_normalize_field_key( $key ) {
		$key = (string) $key;

		if ( strpos( $key, 'user_registration_' ) === 0 ) {
			$key = substr( $key, strlen( 'user_registration_' ) );
		}

		if ( 'membership' === $key || preg_match( '/^membership(_field)?_\d+$/', $key ) ) {
			return 'membership';
		}

		return $key;
	}


	/**
	 * Reorder rows returned by legacy pipeline to match $fields_to_include order.
	 *
	 * @param array $rows
	 * @param array $fields_to_include
	 * @return array
	 */
	private static function reorder_rows_by_fields_to_include( $rows, $fields_to_include ) {
		if ( ! is_array( $rows ) || ! is_array( $fields_to_include ) || empty( $fields_to_include ) ) {
			return is_array( $rows ) ? $rows : array();
		}

		$pos = array();
		foreach ( $fields_to_include as $i => $k ) {
			$pos[ self::urfl_normalize_field_key( $k ) ] = (int) $i;
		}

		$get_key = static function ( $row ) {
			if ( ! is_array( $row ) ) {
				return '';
			}
			if ( isset( $row['field_key'] ) ) {
				return (string) $row['field_key'];
			}
			if ( isset( $row['meta_key'] ) ) {
				return (string) $row['meta_key'];
			}
			if ( isset( $row['key'] ) ) {
				return (string) $row['key'];
			}
			if ( isset( $row['name'] ) ) {
				return (string) $row['name'];
			}
			return '';
		};

		$decorated = array();
		foreach ( $rows as $idx => $row ) {
			$key      = $get_key( $row );
			$key_norm = self::urfl_normalize_field_key( $key );

			$decorated[] = array(
				'row' => $row,
				'i'   => isset( $pos[ $key_norm ] ) ? $pos[ $key_norm ] : 999999,
				'idx' => $idx,
			);
		}

		usort(
			$decorated,
			static function ( $a, $b ) {
				if ( $a['i'] === $b['i'] ) {
					return $a['idx'] <=> $b['idx'];
				}
				return $a['i'] <=> $b['i'];
			}
		);

		$out = array();
		foreach ( $decorated as $d ) {
			$out[] = $d['row'];
		}

		return $out;
	}


	private static function urfl_ensure_membership_row_legacy( $rows, $user_id, $fields_to_include ) {

		$needs_membership        = false;
		$selected_membership_key = '';

		foreach ( (array) $fields_to_include as $k ) {
			$k = (string) $k;
			if ( 'membership' === self::urfl_normalize_field_key( $k ) ) {
				$needs_membership        = true;
				$selected_membership_key = $k;
				break;
			}
		}

		if ( ! $needs_membership ) {
			return $rows;
		}

		foreach ( (array) $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$key = '';
			if ( isset( $row['field_key'] ) ) {
				$key = (string) $row['field_key'];
			} elseif ( isset( $row['meta_key'] ) ) {
				$key = (string) $row['meta_key'];
			} elseif ( isset( $row['key'] ) ) {
				$key = (string) $row['key'];
			} elseif ( isset( $row['name'] ) ) {
				$key = (string) $row['name'];
			}

			if ( 'membership' === self::urfl_normalize_field_key( $key ) ) {
				return $rows;
			}
		}

		$title = self::urfl_get_membership_title_for_user( $user_id, $selected_membership_key );

		if ( '' === $title ) {
			return $rows;
		}

		$rows[] = array(
			'field_key' => $selected_membership_key,
			'meta_key'  => $selected_membership_key,
			'key'       => $selected_membership_key,
			'name'      => $selected_membership_key,
			'label'     => 'Membership',
			'value'     => $title,
		);

		return $rows;
	}
}
