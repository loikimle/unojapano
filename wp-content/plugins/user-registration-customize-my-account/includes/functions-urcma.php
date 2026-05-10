<?php
/**
 * Plugins Functions and Hooks
 *
 * @package User Registration Customize My Account
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'urcma_build_label' ) ) {
	/**
	 * Build endpoint label by name
	 *
	 * @since 1.0.0
	 * @param string $name Name of the endpoint.
	 * @return string
	 */
	function urcma_build_label( $name ) {
		$label = preg_replace( '/[^a-z]/', ' ', $name );
		$label = trim( $label );
		$label = ucfirst( $label );

		return $label;
	}
}

if ( ! function_exists( 'urcma_get_default_endpoint_options' ) ) {
	/**
	 * Get default options for new endpoints
	 *
	 * @since 1.0.0
	 * @param string $endpoint Name of the endpoint.
	 * @return array
	 */
	function urcma_get_default_endpoint_options( $endpoint_name ) {

		// build endpoint options.
		$options = array(
			'slug'      => urcma_create_field_key( $endpoint_name ),
			'active'    => true,
			'label'     => $endpoint_name,
			'icon'      => '',
			'content'   => '',
			'usr_roles' => '',
		);

		return apply_filters( 'urcma_get_default_endpoint_options', $options );
	}
}
if ( ! function_exists( 'urcma_get_default_link_options' ) ) {
	/**
	 * Get default options for new links
	 *
	 * @since 1.1.2
	 * @param string $link Name of the link.
	 * @return array
	 */
	function urcma_get_default_link_options( $link ) {

		// build endpoint options.
		$options = array(
			'url'          => '#',
			'active'       => true,
			'label'        => $link,
			'icon'         => '',
			'target_blank' => '',
			'usr_roles'    => '',
		);

		return apply_filters( 'urcma_get_default_link_options', $options );
	}
}

if ( ! function_exists( 'urcma_admin_print_endpoint_field' ) ) {
	/**
	 * Print endpoint field options
	 *
	 * @since 1.0.0
	 * @param array $args Template args array.
	 */
	function urcma_admin_print_endpoint_field( $args ) {

		// let third part filter template args.
		$args = apply_filters( 'urcma_admin_print_endpoint_field', $args );
		extract( $args );

		include URCMA_TEMPLATE_PATH . '/admin/endpoint-item.php';
	}
}

if ( ! function_exists( 'urcma_admin_print_link_field' ) ) {
	/**
	 * Print link field options
	 *
	 * @since 1.1.2
	 * @param array $args Template args array.
	 */
	function urcma_admin_print_link_field( $args ) {

		// let third part filter template args.
		$args = apply_filters( 'urcma_admin_print_link_field', $args );
		extract( $args );

		include URCMA_TEMPLATE_PATH . '/admin/link-item.php';
	}
}

if ( ! function_exists( 'urcma_is_default_item' ) ) {
	/**
	 * Check if an item is a default
	 *
	 * @since 1.0.0
	 * @param string $item The endpoint to be checked.
	 * @return boolean
	 */
	function urcma_is_default_item( $item ) {
		$defaults = URCMA()->items->get_default_items();
		return array_key_exists( $item, $defaults );
	}
}


if ( ! function_exists( 'urcma_is_plugin_item' ) ) {
	/**
	 * Check if an item is a plugin item
	 *
	 * @since 1.0.0
	 * @param string $item The endpoint to be checked.
	 * @return boolean
	 */
	function urcma_is_plugin_item( $item ) {
		$plugin_endpoint = URCMA()->items->get_plugin_items();
		return array_key_exists( $item, $plugin_endpoint );
	}
}


if ( ! function_exists( 'urcma_get_editable_roles' ) ) {
	/**
	 * Get editable roles for endpoints
	 *
	 * @since 1.0.0
	 * @return array
	 */
	function urcma_get_editable_roles() {
		// get user role.
		$roles     = get_editable_roles();
		$usr_roles = array();
		foreach ( $roles as $key => $role ) {
			if ( empty( $role['capabilities'] ) ) {
				continue;
			}
			$usr_roles[ $key ] = $role['name'];
		}

		return $usr_roles;
	}
}

if ( ! function_exists( 'urcma_get_icon_list' ) ) {
	/**
	 * Get FontAwesome icon list
	 *
	 * @since 1.0.0
	 * @return array
	 */
	function urcma_get_icon_list() {
		if ( file_exists( URCMA_DIR . 'assets/icons/icon-list.php' ) ) {
			return include URCMA_DIR . 'assets/icons/icon-list.php';
		}

		return array();
	}
}


if ( ! function_exists( 'urcma_item_already_exists' ) ) {
	/**
	 * Check if item already exists
	 *
	 * @since 1.0.0
	 * @param string $endpoint The new endpoint to be tested.
	 * @return boolean
	 */
	function urcma_item_already_exists( $endpoint ) {

		// check first in key.
		$field_key = URCMA()->items->get_items_keys();
		$exists    = in_array( $endpoint, $field_key, true );

		// check also in slug.
		if ( ! $exists ) {
			$endpoint_slug = URCMA()->items->get_items_slug();
			$exists        = in_array( $endpoint, $endpoint_slug );
		}

		return $exists;
	}
}

if ( ! function_exists( 'urcma_create_field_key' ) ) {

	/**
	 * Create field key
	 *
	 * @since 1.0.0
	 * @param string $key Key for the endpoint.
	 * @return string
	 */
	function urcma_create_field_key( $key ) {

		// build endpoint key.
		$field_key = strtolower( $key );
		$field_key = trim( $field_key );
		// clear from space and add -.
		$field_key = sanitize_title( $field_key );

		// Check if the title is url encoded.
		if ( urlencode( urldecode( $field_key ) ) !== $field_key ) {
			$field_key = urldecode( $field_key );
		}

		return $field_key;
	}
}

if ( ! function_exists( 'urcma_save_endpoint_options' ) ) {

	/**
	 * Get and save the endpoint options.
	 *
	 * @since 1.0.0
	 * @param string $endpoint Name of the endpoint.
	 * @param string $option_id Option Id of endpoint.
	 * @param string $remove_endpoint Name of endpoint.
	 * @var array $_POST
	 */
	function urcma_save_endpoint_options( $endpoint, $type, $option_id, $remove_endpoint = '' ) {

		$default_options_function = "urcma_get_default_{$type}_options";
		if ( "urcma_endpoint" === $option_id && ! empty( $remove_endpoint ) ) {
			$options = isset( $_POST[ $option_id . '_' . $remove_endpoint ] ) ? $_POST[ $option_id . '_' . $remove_endpoint ] : $default_options_function( $endpoint );
		} else {
			$options = isset( $_POST[ $option_id . '_' . $endpoint ] ) ? $_POST[ $option_id . '_' . $endpoint ] : $default_options_function( $endpoint );
		}
		$options['label']         = stripslashes( $options['label'] );
		$options['active']        = isset( $options['active'] );

		if ( isset( $options['url'] ) && ! isset( $options['slug'] ) ) {
			$options['url']          = esc_url_raw( trim( $options['url'] ) );
			$options['url']          = esc_url_raw( trim( $options['url'] ) );
			$options['target_blank'] = isset( $options['target_blank'] );
		} else {
			$options['slug']    = ( isset( $options['slug'] ) && ! empty( $options['slug'] ) ) ? urcma_create_field_key( $options['slug'] ) : $endpoint;
			$options['content'] = stripslashes( $options['content'] );

			// synchronize ur options.
			update_option( 'user_registration_myaccount_' . str_replace( '-', '_', $endpoint ) . '_endpoint', $options['slug'] );
			if ( ! empty( $remove_endpoint ) && "urcma_endpoint" === $option_id ) {
				delete_option( 'user_registration_myaccount_' . str_replace( '-', '_', $remove_endpoint ) . '_endpoint' );
				delete_option( $option_id . '_' . $remove_endpoint );
			}
		}

		update_option( $option_id . '_' . $endpoint, $options );
	}
}

if ( ! function_exists( 'urcma_update_fields' ) ) {
	/**
	 * Save the admin field
	 *
	 * @return mixed
	 * @since 1.0.0
	 * @var array $_POST
	 */
	function urcma_update_fields() {

		if ( isset( $_POST['urcma_endpoint'] ) ) {
			$value = $_POST['urcma_endpoint'];

			$decoded_values = json_decode( stripslashes( $value ), true );
			$to_save        = array();
			$default_endpoints = array( 'dashboard', 'edit-password', 'edit-profile', 'user-logout' );

			foreach ( $decoded_values as $decoded_value ) {

				if ( ! isset( $decoded_value['id'] ) ) {
					continue;
				}

				// check for master key.
				$id = urcma_create_field_key( $decoded_value['id'] );
				$slug = isset( $_POST['urcma_endpoint_' . $id]['slug'] ) ? urcma_create_field_key( $_POST['urcma_endpoint_' . $id]['slug'] ) : '';
				$remove_endpoint = '';

				if ( ! empty( $slug ) && ! in_array( $id , $default_endpoints ) ) {
					if ( $id !== $slug ) {
						$remove_endpoint = $id;
						$id = $slug;
					}
				}
				$to_save[ $id ]         = array();
				$to_save[ $id ]['type'] = $decoded_value['type'];

				// save endpoint.
				urcma_save_endpoint_options( $id, $decoded_value['type'], 'urcma_endpoint', $remove_endpoint );
			}

			// handle also removed field.
			urcma_delete_endpoints( 'urcma_endpoint' );

			// reset options for rewrite rules.
			update_option( 'urcma-flush-rewrite-rules', 1 );

			update_option( 'urcma_endpoint', json_encode( $to_save ) );
			return json_encode( $to_save );
		}
	}
}

if ( ! function_exists( 'urcma_delete_endpoints' ) ) {

	/**
	 * Delete removed fields
	 *
	 * @param string $option_id Option Id of endpoint.
	 * @param array  $remove_endpoint endpoint to remove.
	 *
	 * @since 1.0.0
	 */
	function urcma_delete_endpoints( $option_id, $remove_endpoint = array() ) {

		if ( empty( $remove_endpoint ) ) {
			// get fields removed if any.
			$remove_endpoint = isset( $_POST[ $option_id . '_remove_endpoint' ] ) ? $_POST[ $option_id . '_remove_endpoint' ] : '';
			$remove_endpoint = explode( ',', $remove_endpoint );
		}

		if ( ! is_array( $remove_endpoint ) ) {
			return;
		}

		foreach ( $remove_endpoint as $key ) {
			delete_option( $option_id . '_' . $key );
			// delete ur options if any.
			delete_option( 'user_registration_myaccount_' . str_replace( '-', '_', $key ) . '_endpoint' );
		}
	}
}


if ( ! function_exists( 'urcma_get_current_endpoint' ) ) {
	/**
	 * Check if and endpoint is active on frontend. Used for add class 'active' on account menu in frontend
	 *
	 * @since 1.0.0
	 * @return string
	 */
	function urcma_get_current_endpoint() {

		global $wp;

		$current = 'dashboard';
		foreach ( UR()->query->get_query_vars() as $key => $value ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				$current = $key;
			}
		}

		return apply_filters( 'urcma_get_current_endpoint', $current );
	}
}


if ( ! function_exists( 'urcma_get_endpoint_by' ) ) {
	/**
	 * Get endpoint by a specified key
	 *
	 * @since 1.0.0
	 * @param string $value value.
	 * @param string $key Can be key or slug.
	 * @param array  $items Endpoint array.
	 * @return array
	 */
	function urcma_get_endpoint_by( $value, $key = 'key', $items = array() ) {

		$accepted = apply_filters( 'urcma_get_endpoint_by_accepted_key', array( 'key', 'slug' ) );

		if ( ! in_array( $key, $accepted ) ) {
			return array();
		}

		empty( $items ) && $items = URCMA()->items->get_items();
		$find                     = array();

		foreach ( $items as $id => $item ) {
			if ( ( $key == 'key' && $id == $value ) || ( isset( $item[ $key ] ) && $item[ $key ] == $value ) ) {
				$find[ $id ] = $item;
				continue;
			} elseif ( isset( $item['children'] ) ) {
				foreach ( $item['children'] as $child_id => $child ) {
					if ( ( $key == 'key' && $value == $child_id ) || ( isset( $child[ $key ] ) && $child[ $key ] == $value ) ) {
						$find[ $child_id ] = $child;
						continue;
					}
				}
				continue;
			}
		}
		return apply_filters( 'urcma_get_endpoint_by_result', $find );
	}
}

add_action( 'urcma_print_single_endpoint', 'urcma_print_single_endpoint', 10, 2 );

if ( ! function_exists( 'urcma_print_single_endpoint' ) ) {
	/**
	 * Print single endpoint on front menu
	 *
	 * @since 1.0.0
	 * @param string $endpoint Name of endpoint.
	 * @param array  $options Options for endpoint.
	 */
	function urcma_print_single_endpoint( $endpoint, $options ) {

		if ( ! isset( $options['url'] ) ) {
			$url                             = get_permalink( ur_get_page_id( 'myaccount' ) );
			$endpoint  = ur_string_translation(0,'user_registration_' . $endpoint .'_slug', $endpoint);

			$endpoint != 'dashboard' && $url = ur_get_endpoint_url( $endpoint, '', $url );
		} elseif ( isset( $options['link_type'] ) ) {

			if ( 'internal' === $options['link_type'] ) {
				$page = isset( $options['page_link'] ) ? $options['page_link'] : 0;

				if ( $page > 0 && function_exists( 'pll_current_language' ) ) {
					$current_language = pll_current_language();
					if ( ! empty( $current_language ) ) {
						$translations = pll_get_post_translations( $page );
						$page         = isset( $translations[ pll_current_language() ] ) ? $translations[ pll_current_language() ] : $page;
					}
				} elseif ( $page > 0 && has_filter( 'wpml_current_language' ) ) {
					$page = ur_get_wpml_page_language( $page );
				}

				$url = get_permalink( $page );
			} else {
				$url = esc_url( ur_string_translation( 0, 'user_registration_' . $endpoint . '_link', $options['url'] ) );
			}
		} else {
			$url = esc_url( ur_string_translation( 0, 'user_registration_' . $endpoint . '_link', $options['url'] ) );
		}

		if ( 'user-logout' === $endpoint && isset( $options['logout_url'] ) && ur_option_checked( 'user_registration_disable_logout_confirmation', false ) ) {
			$url = $options['logout_url'];
		}

		// check if endpoint is active.
		$current = urcma_get_current_endpoint();
		$classes = array(
			'user-registration-MyAccount-navigation-link',
			'user-registration-MyAccount-navigation-link--' . $endpoint,
		);

		( $endpoint == $current ) && $classes[] = 'is-active';

		if ( empty( $options['active'] ) ) {
			$classes[] = 'hide';
		}

		$classes = apply_filters( 'urcma_endpoint_menu_class', $classes, $endpoint, $options );

		// build args array.
		$args = apply_filters(
			'urcma_print_single_endpoint_args',
			array(
				'url'      => $url,
				'endpoint' => $endpoint,
				'options'  => $options,
				'classes'  => $classes,
			)
		);

		ur_get_template( 'urcma-myaccount-menu-item.php', $args, '', URCMA_DIR . 'templates/' );
	}
}
