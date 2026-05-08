<?php
/**
 * URFrontendListing CoreFunctions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPEverest
 * @category Core
 * @package  URFrontendListing/Handler
 * @version  1.0.0
 */

if ( ! function_exists( 'ur_frontend_listing_is_compatible' ) ) {

	/**
	 * Check if Frontend Listing addon is compatible.
	 *
	 * @return string
	 */
	function ur_frontend_listing_is_compatible() {

		$ur_plugins_path     = WP_PLUGIN_DIR . UR_FRONTEND_LISTING_DS . 'user-registration' . UR_FRONTEND_LISTING_DS . 'user-registration.php';
		$ur_pro_plugins_path = WP_PLUGIN_DIR . UR_FRONTEND_LISTING_DS . 'user-registration-pro' . UR_FRONTEND_LISTING_DS . 'user-registration.php';

		if ( ! file_exists( $ur_plugins_path ) && ! file_exists( $ur_pro_plugins_path ) ) {
			return __( 'Please install <code>user-registration-pro' . '</code> plugin to use <code>user-registration-frontend-listing</code> addon.', 'user-registration-frontend-listing' );
		}

		$ur_plugin_file_path     = 'user-registration/user-registration.php';
		$ur_pro_plugin_file_path = 'user-registration-pro/user-registration.php';

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! is_plugin_active( $ur_plugin_file_path ) && ! is_plugin_active( $ur_pro_plugin_file_path ) ) {
			return __( 'Please activate <code>user-registration-pro</code> plugin to use <code>user-registration-frontend-listing</code> addon.', 'user-registration-frontend-listing' );
		}

		if ( function_exists( 'UR' ) ) {
			$user_registration_version = UR()->version;
		} else {
			$user_registration_version = get_option( 'user_registration_version' );
		}

		if ( ! is_plugin_active( $ur_pro_plugin_file_path ) ) {

			if ( version_compare( $user_registration_version, '1.9.5', '<' ) ) {
				return __( 'Please update your <code>user-registration-pro</code> plugin(to at least 1.9.5 version) to use <code>user-registration-frontend-listing</code> addon.', 'user-registration-frontend-listing' );
			}
		} elseif ( version_compare( $user_registration_version, '3.0.0', '<' ) ) {

				return __( 'Please update your <code>user registration-pro</code> plugin to at least 3.0.0 version to use <code>user-registration-frontend-listing</code> addon.', 'user-registration-frontend-listing' );
		}

		return 'YES';
	}
}

if ( ! function_exists( 'ur_frontend_listing_check_plugin_compatibility' ) ) {

	/**
	 * Check Plugin Compatibility.
	 */
	function ur_frontend_listing_check_plugin_compatibility() {

		add_action( 'admin_notices', 'ur_frontend_listing_admin_notice', 10 );
	}
}

if ( ! function_exists( 'ur_frontend_listing_admin_notice' ) ) {

	/**
	 * Print Admin Notice.
	 */
	function ur_frontend_listing_admin_notice() {

		$class = 'notice notice-error';

		$message = ur_frontend_listing_is_compatible();

		if ( 'YES' !== $message && '' !== $message ) {

			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
		}
	}
}

if ( ! function_exists( 'ur_frontend_listing_user_search_fields' ) ) {

	/**
	 * Fields to search when searching is enabled.
	 *
	 * @return string
	 */
	function ur_frontend_listing_user_search_fields() {

		$fields =
			array(
				'user_login'   => __( 'Username', 'user-registration-frontend-listing' ),
				'user_email'   => __( 'Email', 'user-registration-frontend-listing' ),
				'user_url'     => __( 'User URL', 'user-registration-frontend-listing' ),
				'display_name' => __( 'Display Name', 'user-registration-frontend-listing' ),
				'first_name'   => __( 'First Name', 'user-registration-frontend-listing' ),
				'last_name'    => __( 'Last Name', 'user-registration-frontend-listing' ),
				'nickname'     => __( 'Nickname', 'user-registration-frontend-listing' ),
			);

		$fields = apply_filters( 'user_registration_frontend_listing_user_search_fields', $fields );

		return $fields;
	}
}

if ( ! function_exists( 'ur_frontend_listing_exclude_fields_in_view_profile' ) ) {

	/**
	 * Get the user registration form fields to exclude in view profile.
	 *
	 * @return array
	 */
	function ur_frontend_listing_exclude_fields_in_view_profile() {
		$fields_to_exclude = array(
			'user_pass',
			'user_confirm_password',
			'user_confirm_email',
			'password',
			'user_registered',
			'profile_picture',
			'privacy_policy',
			'section_title',
			'html',
			'stripe_gateway',
		);

		$fields_to_exclude = apply_filters( 'ur_frontend_listing_exclude_fields_in_view_profile', $fields_to_exclude );
		return $fields_to_exclude;
	}
}

if ( ! function_exists( 'ur_frontend_listing_include_fields_in_view_profile' ) ) {

	/**
	 * Get the user registration form fields to include in view profile.
	 *
	 * @return array
	 */
	function ur_frontend_listing_include_fields_in_view_profile() {
		$default_fields            = array_merge( ur_get_user_table_fields(), ur_get_registered_user_meta_fields() );
		$fields_to_exclude         = array_merge( $default_fields, ur_frontend_listing_exclude_fields_in_view_profile() );
		$default_fields_with_label = array(
			'form_label' => 'Basic Details',
			'field_list' => array(),
		);

		// Default field Exclude.
		foreach ( $default_fields as $field_key => $field_value ) {
			$default_fields_with_label['field_list'][ $field_value ] = ucwords( implode( ' ', explode( '_', $field_value ) ) );
			if ( 'user_pass' === $field_value ) {
				unset( $default_fields_with_label['field_list'][ $field_value ] );
			}
		}

		if ( function_exists( 'ur_get_all_form_fields' ) ) {
			$form_fields = ur_get_all_form_fields( $fields_to_exclude );
		} else {
			$form_fields = array();
		}

		array_unshift( $form_fields, $default_fields_with_label );
		return $form_fields;
	}
}

if ( ! function_exists( 'urfl_is_directory_restricted' ) ) {

	function urfl_is_directory_restricted( $directory_id ) {
		$directory_id = absint( $directory_id );

		if ( ! $directory_id ) {
			return false;
		}

		if ( function_exists( 'is_super_admin' ) && is_super_admin() ) {
			return false;
		}

		if ( ! function_exists( 'urcr_is_access_rule_enabled' ) ) {
			return false;
		}

		$access_rule_posts = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);

		if ( empty( $access_rule_posts ) ) {
			return false;
		}

		$restricted_message = '';
		$has_restricted     = false;
		$has_allowed        = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
				continue;
			}

			if ( ! is_array( $access_rule['logic_map'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
				continue;
			}

			$directory_targets = array_values(
				array_filter(
					(array) $access_rule['target_contents'],
					static function ( $target ) {
						return isset( $target['type'] ) && 'directory' === $target['type'];
					}
				)
			);

			if ( empty( $directory_targets ) ) {
				continue;
			}

			$is_target = false;

			foreach ( $directory_targets as $target ) {
				$target_values = isset( $target['value'] ) ? (array) $target['value'] : array();
				$target_values = array_map( 'strval', $target_values );

				if ( in_array( (string) $directory_id, $target_values, true ) ) {
					$is_target = true;
					break;
				}
			}

			if ( ! $is_target ) {
				continue;
			}

			if ( ! urcr_is_access_rule_enabled( $access_rule ) || ! urcr_is_action_specified( $access_rule ) ) {
				continue;
			}

			$has_conditions = ! empty( $access_rule['logic_map']['conditions'] );

			if ( $has_conditions ) {
				$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], null );
			} else {
				$should_allow_access = is_user_logged_in();
			}

			$rule_type     = get_post_meta( $access_rule_post->ID, 'urcr_rule_type', true );
			$membership_id = get_post_meta( $access_rule_post->ID, 'urcr_membership_id', true );

			$actions             = (array) $access_rule['actions'];
			$first_action        = isset( $actions[0] ) && is_array( $actions[0] ) ? $actions[0] : array();

			$access_control = isset( $first_action['access_control'] ) && ! empty( $first_action['access_control'] ) ? $first_action['access_control'] : 'access';

			$is_restricted = ( true === $should_allow_access && 'restrict' === $access_control )
				|| ( false === $should_allow_access && 'access' === $access_control );

			if ( ! $is_restricted ) {
				$has_allowed = true;
				continue;
			}

			$message = '';

			if ( isset( $first_action['type'] ) && 'message' === $first_action['type'] && ! empty( $first_action['message'] ) ) {
				$message = urldecode( $first_action['message'] );
				$message = apply_filters( 'user_registration_process_smart_tags', $message );
			}

			if ( '' === $message ) {
				if ( class_exists( 'URCR_Admin_Assets' ) && method_exists( 'URCR_Admin_Assets', 'get_default_message' ) ) {
					$message = URCR_Admin_Assets::get_default_message();
				} else {
					$global_message = get_option( 'user_registration_content_restriction_message' );
					if ( false === $global_message || '' === $global_message ) {
						$global_message = esc_html__( 'This content is restricted!', 'user-registration' );
					}
					$message = apply_filters( 'user_registration_process_smart_tags', $global_message );
				}
			}

			$has_restricted     = true;
			$restricted_message = $message;
		}

		if ( $has_allowed ) {
			return false;
		}

		if ( $has_restricted ) {
			return array(
				'restricted' => true,
				'message'    => $restricted_message,
			);
		}

		return false;
	}
}

/**
 * Card fields options helper functions
 *
 * @package URFrontendListing
 */


if ( ! function_exists( 'urfl_card_fields_grouped_options_by_selection' ) ) {
	function urfl_card_fields_grouped_options_by_selection( $post_id, $mode_override = null, $forms_override = null, $memberships_override = null ) {
		$mode = $mode_override !== null
			? (string) $mode_override
			: (string) get_post_meta( $post_id, 'user_registration_frontend_listings_ur_only', true );

		$default_fields    = array_merge( ur_get_user_table_fields(), ur_get_registered_user_meta_fields() );
		$fields_to_exclude = array_merge( $default_fields, ur_frontend_listing_exclude_fields_in_view_profile() );

		$all_forms = ur_get_all_user_registration_form();

		$groups = array();


			$basic = array();
			foreach ( $default_fields as $field_value ) {
				if ( 'user_pass' === $field_value ) {
					continue;
				}
				$basic[ $field_value ] = ucwords( implode( ' ', explode( '_', $field_value ) ) );
			}

			$groups[] = array(
				'label'   => __( 'Basic Details', 'user-registration-frontend-listing' ),
				'options' => $basic,
			);


		if ( '2' === $mode ) {
			foreach ( $all_forms as $form_id => $form_label ) {
				$form_fields = urfl_extract_form_fields_map( (int) $form_id, $fields_to_exclude );
				if ( empty( $form_fields ) ) {
					continue;
				}
				$groups[] = array(
					'label'   => (string) $form_label,
					'options' => $form_fields,
				);
			}

			return $groups;
		}

		if ( '1' === $mode ) {
			$selected_forms = $forms_override !== null
				? (array) $forms_override
				: (array) get_post_meta( $post_id, 'user_registration_frontend_listings_ur_forms', true );
			$selected_forms = array_values( array_filter( array_map( 'intval', $selected_forms ) ) );

			if ( empty( $selected_forms ) ) {
				$selected_forms = array_map( 'intval', array_keys( $all_forms ) );
			}

			foreach ( $selected_forms as $form_id ) {
				if ( empty( $all_forms[ $form_id ] ) ) {
					continue;
				}
				$form_fields = urfl_extract_form_fields_map( (int) $form_id, $fields_to_exclude );
				if ( empty( $form_fields ) ) {
					continue;
				}
				$groups[] = array(
					'label'   => (string) $all_forms[ $form_id ],
					'options' => $form_fields,
				);
			}

			return $groups;
		}

		$selected_memberships = $memberships_override !== null
			? (array) $memberships_override
			: (array) get_post_meta( $post_id, 'user_registration_member_directory_ur_membership_type', true );
		$selected_memberships = array_values( array_filter( array_map( 'strval', $selected_memberships ) ) );

		foreach ( $all_forms as $form_id => $form_label ) {
			$match = urfl_form_has_matching_membership( (int) $form_id, $selected_memberships );
			if ( ! $match ) {
				continue;
			}
			$form_fields = urfl_extract_form_fields_map( (int) $form_id, $fields_to_exclude );
			if ( empty( $form_fields ) ) {
				continue;
			}
			$groups[] = array(
				'label'   => (string) $form_label,
				'options' => $form_fields,
			);
		}

		return $groups;
	}
}


if ( ! function_exists( 'urfl_flatten_grouped_options' ) ) {
	function urfl_flatten_grouped_options( $groups ) {
		$out = array();

		foreach ( (array) $groups as $group ) {
			if ( empty( $group['options'] ) || ! is_array( $group['options'] ) ) {
				continue;
			}
			foreach ( $group['options'] as $k => $v ) {
				$out[ (string) $k ] = (string) $v;
			}
		}

		return $out;
	}
}

if ( ! function_exists( 'urfl_card_fields_options_by_selection' ) ) {
	function urfl_card_fields_options_by_selection( $post_id, $mode_override = null, $forms_override = null, $memberships_override = null ) {
		$groups = urfl_card_fields_grouped_options_by_selection( $post_id, $mode_override, $forms_override, $memberships_override );
		return urfl_flatten_grouped_options( $groups );
	}
}


if ( ! function_exists( 'urfl_extract_form_fields_map' ) ) {
	/**
	 * Extract form fields as key-label map.
	 *
	 * @since x.x.x
	 * @param int   $form_id      The form ID.
	 * @param array $strip_fields Fields to exclude.
	 * @return array
	 */
	function urfl_extract_form_fields_map( $form_id, $strip_fields ) {
		$post         = get_post( $form_id );
		$post_content = isset( $post->post_content ) ? $post->post_content : '';
		$rows         = ! empty( $post_content ) ? json_decode( $post_content ) : array();

		$out = array();

		if ( ! is_array( $rows ) && ! is_object( $rows ) ) {
			return $out;
		}

		foreach ( $rows as $row ) {
			foreach ( $row as $grid ) {
				foreach ( $grid as $field ) {
					if ( empty( $field->field_key ) || empty( $field->general_setting ) || empty( $field->general_setting->field_name ) ) {
						continue;
					}

					if ( in_array( (string) $field->field_key, $strip_fields, true ) ) {
						continue;
					}

					$name  = (string) $field->general_setting->field_name;
					$label = isset( $field->general_setting->label ) ? (string) $field->general_setting->label : $name;

					if ( 'user_pass' === $name ) {
						continue;
					}

					$out[ $name ] = $label;
				}
			}
		}

		return $out;
	}
}


if ( ! function_exists( 'urfl_form_has_matching_membership' ) ) {
	/**
	 * Check if form has matching membership.
	 *
	 * @since x.x.x
	 * @param int   $form_id              The form ID.
	 * @param array $selected_memberships Selected membership IDs.
	 * @return bool
	 */
	function urfl_form_has_matching_membership( $form_id, $selected_memberships ) {
		$post         = get_post( $form_id );
		$post_content = isset( $post->post_content ) ? $post->post_content : '';
		$rows         = ! empty( $post_content ) ? json_decode( $post_content ) : array();

		if ( ! is_array( $rows ) && ! is_object( $rows ) ) {
			return false;
		}

		foreach ( $rows as $row ) {
			foreach ( $row as $grid ) {
				foreach ( $grid as $field ) {
					if ( empty( $field->field_key ) || 'membership' !== (string) $field->field_key ) {
						continue;
					}

					$opt = '';
					if ( isset( $field->general_setting ) && isset( $field->general_setting->membership_listing_option ) ) {
						$opt = $field->general_setting->membership_listing_option;
					}

					if ( 'all' === (string) $opt ) {
						return true;
					}

					$allowed = array();
					if ( is_array( $opt ) || is_object( $opt ) ) {
						foreach ( $opt as $v ) {
							$allowed[] = (string) $v;
						}
					} elseif ( is_string( $opt ) && '' !== $opt ) {
						$allowed = array_map( 'trim', explode( ',', $opt ) );
						$allowed = array_values( array_filter( array_map( 'strval', $allowed ) ) );
					}

					if ( empty( $allowed ) ) {
						return true;
					}

					foreach ( (array) $selected_memberships as $mid ) {
						if ( in_array( (string) $mid, $allowed, true ) ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}
}

if ( ! function_exists( 'urfl_get_card_fields_order' ) ) {
	/**
	 * Get the ordered card fields for frontend display.
	 *
	 * @since x.x.x
	 * @param int $post_id The listing post ID.
	 * @return array Array of field keys in saved order.
	 */
	function urfl_get_card_fields_order( $post_id ) {
		$order_raw = get_post_meta( $post_id, 'user_registration_frontend_listings_lists_fields_order', true );

		if ( empty( $order_raw ) ) {
			return array();
		}

		$order = json_decode( $order_raw, true );

		if ( ! is_array( $order ) ) {
			return array();
		}

		return array_values( array_filter( $order ) );
	}
}

if ( ! function_exists( 'urfl_get_card_fields_for_user' ) ) {
	/**
	 * Get card field data for frontend rendering.
	 *
	 * Returns an array of field data in the saved order, ready for display.
	 *
	 * @since x.x.x
	 * @param int $post_id The listing post ID.
	 * @param int $user_id The user ID to get field values for.
	 * @return array Array of arrays with 'key', 'label', and 'value'.
	 */
	function urfl_get_card_fields_for_user( $post_id, $user_id ) {
		$order   = urfl_get_card_fields_order( $post_id );
		$options = urfl_card_fields_options_by_selection( $post_id );
		$user    = get_userdata( $user_id );

		if ( empty( $order ) || ! $user ) {
			return array();
		}

		$fields = array();

		foreach ( $order as $field_key ) {
			if ( ! isset( $options[ $field_key ] ) ) {
				continue;
			}

			$value = urfl_get_user_field_value( $user_id, $field_key, $user );

			$fields[] = array(
				'key'   => $field_key,
				'label' => $options[ $field_key ],
				'value' => $value,
			);
		}

		return $fields;
	}
}

if ( ! function_exists( 'urfl_get_user_field_value' ) ) {
	/**
	 * Get user field value by key.
	 *
	 * Handles standard WP fields, standard user meta, and User Registration
	 * custom fields with dynamic numeric suffixes.
	 *
	 * @since x.x.x
	 * @param int     $user_id   User ID.
	 * @param string  $field_key Field key.
	 * @param WP_User $user      Optional. User object for performance.
	 * @return mixed Field value or empty string.
	 */
	function urfl_get_user_field_value( $user_id, $field_key, $user = null ) {
		if ( ! $user ) {
			$user = get_userdata( $user_id );
		}

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

		if ( 'membership' === $field_key ) {
			return get_user_meta( $user_id, 'ur_membership_id', true );
		}

		$value = get_user_meta( $user_id, $field_key, true );
		if ( '' !== $value && ! is_null( $value ) ) {
			return is_array( $value ) ? implode( ', ', $value ) : $value;
		}

		if ( strpos( $field_key, 'user_registration_' ) !== 0 ) {
			$prefixed_key = 'user_registration_' . $field_key;
			$value        = get_user_meta( $user_id, $prefixed_key, true );

			if ( '' !== $value && ! is_null( $value ) ) {
				return is_array( $value ) ? implode( ', ', $value ) : $value;
			}
		}

		$value = urfl_get_user_meta_by_pattern( $user_id, $field_key );

		if ( '' !== $value && ! is_null( $value ) ) {
			return is_array( $value ) ? implode( ', ', $value ) : $value;
		}

		return '';
	}
}

if ( ! function_exists( 'urfl_get_user_meta_by_pattern' ) ) {
	/**
	 * Get user meta value by matching pattern.
	 *
	 * Handles dynamic field keys like user_registration_textarea_1767956495
	 * where the field key stored might be just 'textarea' or 'user_registration_textarea'.
	 *
	 * @since x.x.x
	 * @param int    $user_id   User ID.
	 * @param string $field_key Field key pattern.
	 * @return mixed Meta value or empty string.
	 */
	function urfl_get_user_meta_by_pattern( $user_id, $field_key ) {
		global $wpdb;

		$base_key = str_replace( 'user_registration_', '', $field_key );

		if ( preg_match( '/_\d+$/', $field_key ) ) {

			$full_key = strpos( $field_key, 'user_registration_' ) === 0
				? $field_key
				: 'user_registration_' . $field_key;

			$value = get_user_meta( $user_id, $full_key, true );
			if ( '' !== $value && ! is_null( $value ) ) {
				return $value;
			}
		}

		$pattern = 'user_registration_' . $base_key . '_%';

		$meta_key = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_key FROM {$wpdb->usermeta}
				 WHERE user_id = %d
				 AND meta_key LIKE %s
				 LIMIT 1",
				$user_id,
				$pattern
			)
		);

		if ( $meta_key ) {
			return get_user_meta( $user_id, $meta_key, true );
		}

		$exact_pattern = 'user_registration_' . $base_key;
		$value         = get_user_meta( $user_id, $exact_pattern, true );

		if ( '' !== $value && ! is_null( $value ) ) {
			return $value;
		}

		return '';
	}
}


if ( ! function_exists( 'ur_frontend_listings_metabox_field_ids' ) ) {

	/**
	 * Get the metabox fields ids of the frontend_listings post..
	 *
	 * @return array
	 */
	function ur_frontend_listings_metabox_field_ids() {
		$metabox_field_ids = array(
			'user_registration_frontend_listings_layout',
			'user_registration_frontend_listings_allow_guest',
			'user_registration_frontend_listings_display_profile_picture',
			'user_registration_frontend_listings_view_profile',
			'user_registration_frontend_listings_card_fields',
			'user_registration_frontend_listings_lists_fields',
			'user_registration_frontend_listings_search_form',
			'user_registration_frontend_listings_search_fields',
			'user_registration_frontend_listings_sort_by',
			'user_registration_frontend_listings_default_sorter',
			'user_registration_frontend_listings_role_restriction',
			'user_registration_frontend_listings_ur_only',
			'user_registration_frontend_listings_ur_forms',
			'user_registration_frontend_listings_advanced_filter_fields',
			'user_registration_frontend_listings_amount_filter',
			'user_registration_frontend_listings_default_page_filter',
			'user_registration_frontend_listings_filtered_user_message',
			'user_registration_frontend_listings_no_users_found_text',
			'user_registration_frontend_listings_access_denied_text',
			'user_registration_frontend_listing_search_placeholder_text',
			'user_registration_frontend_listings_search_button_text',
			'user_registration_frontend_listings_view_profile_button_text',
			'user_registration_frontend_listings_advanced_filter',
			'user_registration_frontend_listings_filter_by_user_status',
			'user_registration_frontend_listings_lists_fields_order',
			'user_registration_member_directory_ur_membership_type',
			'user_registration_frontend_listings_card_fields_order',
			'user_registration_frontend_listings_view_profile_display_profile_picture',
			'user_registration_frontend_listings_view_profile_fields_order',

		);

		return apply_filters( 'ur_frontend_listings_metabox_ids', $metabox_field_ids );
	}

}


if ( ! function_exists( 'ur_frontend_listing_amount_filter' ) ) {

	/**
	 * Get the amount filters for users per page.
	 *
	 * @return array
	 */
	function ur_frontend_listing_amount_filter() {

		$amount_filter = array(
			'10'  => 10,
			'20'  => 20,
			'50'  => 50,
			'100' => 100,
		);

		$amount_filter = apply_filters( 'ur_frontend_listing_amount_filter', $amount_filter );
		return $amount_filter;
	}
}

if ( ! function_exists( 'ur_frontend_listing_sort_filter' ) ) {

	/**
	 * Get the sort filters for sorting users.
	 *
	 * @return array
	 */
	function ur_frontend_listing_sort_filter() {

		$sort_filter = array(
			'user_registered' => __( 'Latest Users', 'user-registration-frontend-listing' ),
			'first_name'      => __( 'First Name', 'user-registration-frontend-listing' ),
			'last_name'       => __( 'Last Name', 'user-registration-frontend-listing' ),
			'display_name'    => __( 'Display Name', 'user-registration-frontend-listing' ),
		);

		$sort_filter = apply_filters( 'ur_frontend_listing_sort_filter', $sort_filter );
		return $sort_filter;
	}
}

if ( ! function_exists( 'ur_frontend_listing_advanced_filter' ) ) {
	/**
	 * Get the advanced filters for narrowing down user search.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	function ur_frontend_listing_advanced_filter() {

		$default_fields  = ur_frontend_listing_default_advanced_filter();
		$default_labels  = ur_get_registered_form_fields_with_default_labels();
		$advanced_filter = array();

		foreach ( $default_fields as $field_key ) {
			if ( 'user_pass' === $field_key ) {
				continue;
			}

			if ( isset( $default_labels[ $field_key ] ) ) {
				$advanced_filter[ $field_key ] = $default_labels[ $field_key ];
			}
		}

		$advanced_filter['other'] = esc_html__( 'Other ( Custom field )', 'user-registration-frontend-listing' );

		$advanced_filter = apply_filters( 'ur_frontend_listing_advanced_filter', $advanced_filter );
		return $advanced_filter;
	}
}


if ( ! function_exists( 'ur_frontend_listing_default_advanced_filter' ) ) {
	/**
	 * Get the default advanced filters for narrowing down user search.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	function ur_frontend_listing_default_advanced_filter() {

		$table_field            = ur_get_user_table_fields();
		$registered_meta_fields = ur_get_registered_user_meta_fields();

		$default_fields = array_merge( $table_field, $registered_meta_fields );
		return $default_fields;
	}
}

if ( ! function_exists( 'ur_frontend_list_advanced_filter_wrapper' ) ) {

	/**
	 * Generates the advanced filter wrapper to show in frontend list page.
	 *
	 * @param array $advanced_filter_fields Advanced Filter fields wrapper.
	 * @since 1.1.0
	 */
	function ur_frontend_list_advanced_filter_wrapper( $advanced_filter_fields ) {
		$filter_wrapper = '<div class="ur-advance-setting-container ur-frontend-listing-advance-filter-wrapper" style="display:none;">';

		$filter_wrapper .= '<div class="ur-advance-setting-lists">';

		foreach ( $advanced_filter_fields as $filter_key => $filter_label ) {
			$filter_wrapper .= '<span class="ur-advance-settings-list">';
			$filter_wrapper .= '<label for="">' . esc_attr( $filter_label ) . '</label>';
			$filter_wrapper .= '<input type="text" name="ur_frontend_listing_' . esc_attr( $filter_key ) . '" />';
			$filter_wrapper .= '</span>';
		}
		$filter_wrapper .= '</div>';

		$filter_wrapper .= '<div class="ur-advance-setting-button">';
		$filter_wrapper .= '<div class="ur-advance-setting-reset" data-id="all-filter">';
		$filter_wrapper .= '<span>' . esc_html__( 'Start Over? ', 'user-registration-frontend-listing' ) . '</span><a>' . esc_html__( 'Reset Filter', 'user-registration-frontend-listing' ) . '</a>';
		$filter_wrapper .= '</div>';

		$filter_wrapper .= ' <div class="ur-advance-setting-button-group">';
		$filter_wrapper .= '<input type="submit" value="' . esc_html__( 'Apply Filter', 'user-registration-frontend-listing' ) . '" class="ur-btn btn-primary ur-frontend-listing-advance-filter-apply">';
		$filter_wrapper .= '</div>';
		$filter_wrapper .= '</div>';
		$filter_wrapper .= '</div>';

		echo $filter_wrapper; // phpcs:ignore
	}
}

if ( ! function_exists( 'ur_get_all_user_status' ) ) {

	/**
	 * ur_get_all_user_status
	 *
	 * @return mixed|null
	 */
	function ur_get_all_user_status() {

		$statuses =
			array(
				'approved' => __( 'Approved', 'user-registration-frontend-listing' ),
				'pending'  => __( 'Pending', 'user-registration-frontend-listing' ),
				'denied'   => __( 'Denied', 'user-registration-frontend-listing' ),
				'awaiting' => __( 'Awaiting Email Confirmation', 'user-registration-frontend-listing' ),
			);

		$statuses = apply_filters( 'user_registration_frontend_listing_all_user_status', $statuses );
		return $statuses;
	}
}

if ( ! function_exists( 'urfl_get_user_ids_by_memberships' ) ) {
	function urfl_get_user_ids_by_memberships( $membership_ids, $status = 'active' ) {
		global $wpdb;

		$membership_ids = array_values( array_filter( array_map( 'absint', (array) $membership_ids ) ) );

		if ( empty( $membership_ids ) ) {
			return array();
		}

		$table        = $wpdb->prefix . 'ur_membership_subscriptions';
		$placeholders = implode( ',', array_fill( 0, count( $membership_ids ), '%d' ) );

		$sql    = "SELECT DISTINCT user_id FROM {$table} WHERE item_id IN ($placeholders) AND status = %s";
		$params = array_merge( $membership_ids, array( $status ) );

		$user_ids = $wpdb->get_col( $wpdb->prepare( $sql, $params ) );

		return array_values( array_filter( array_map( 'absint', (array) $user_ids ) ) );
	}
}
