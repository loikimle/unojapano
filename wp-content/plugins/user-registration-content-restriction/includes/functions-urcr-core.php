<?php
/**
 * UserRegistrationContentRestriction Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistrationContentRestriction/Functions
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param $path
 *
 * @return string
 */


function urcr_is_compatible() {

	$ur_plugins_path = WP_PLUGIN_DIR . URCR_DS . 'user-registration' . URCR_DS . 'user-registration.php';
	$ur_pro_plugins_path = WP_PLUGIN_DIR . URCR_DS . 'user-registration-pro' . URCR_DS . 'user-registration.php';

	if ( ! file_exists( $ur_plugins_path ) && ! file_exists( $ur_pro_plugins_path ) ) {
		return __( 'Please install <code>user-registration-pro</code> plugin to use <code>user-registration-content-restriction</code> addon.', 'user-registration-content-restriction' );
	}

	$ur_plugin_file_path = 'user-registration/user-registration.php';
	$ur_pro_plugin_file_path = 'user-registration-pro/user-registration.php';

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( $ur_plugin_file_path ) && ! is_plugin_active( $ur_pro_plugin_file_path ) ) {
		return __( 'Please activate <code>user-registration-pro</code> plugin to use <code>user-registration-content-restriction</code> addon.', 'user-registration-content-restriction' );
	}

	if ( function_exists( 'UR' ) ) {
		$user_registration_version = UR()->version;
	} else {
		$user_registration_version = get_option( 'user_registration_version' );
	}

	if ( ! is_plugin_active( $ur_pro_plugin_file_path ) ) {

		if ( version_compare( $user_registration_version, '1.1.0', '<' ) ) {
			return __( 'Please update your <code>user-registration-pro</code> plugin(to at least 1.1.0 version) to use <code>user-registration-content-restriction</code> addon.', 'user-registration-content-restriction' );
		}
	} else {

		if ( version_compare( $user_registration_version, '3.0.0', '<' ) ) {
			return __( 'Please update your <code>user registration-pro</code> plugin to at least 3.0.0 version to use <code>user-registration-content-restriction</code> addon.', 'user-registration-content-restriction' );
		}
	}

	return 'YES';

}

function urcr_check_plugin_compatibility() {

	add_action( 'admin_notices', 'user_registration_content_restriction_admin_notice', 10 );

}

function user_registration_content_restriction_admin_notice() {

	$class = 'notice notice-error';

	$message = urcr_is_compatible();

	if ( 'YES' !== $message ) {

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), ( $message ) );
	}
}

/**
 * Deprecate plugin missing notice.
 *
 * @deprecated 1.1.1
 *
 * @return void
 */
function urcr_admin_notices() {
	ur_deprecated_function( 'urcr_admin_notices', '1.1.1', 'user_registration_content_restriction_admin_notice' );
}

function urcr_settings() {
	$access_rules_list_link = admin_url( 'admin.php?page=user-registration-content-restriction' );
	$link_html              = sprintf( '<a href="%s">%s</a>', $access_rules_list_link, __( 'Go to Content Access Rules page for advanced restrictions', 'user-registration-content-restriction' ) );

	return apply_filters(
		'user_registration_content_restriction_settings',
		array(
			'title' => __( 'Content Restriction Settings', 'user-registration-content-restriction' ),
			'sections' => array(
				'user_registration_content_restriction_settings' => array(
					'title' => __( 'General', 'user-registration-content-restriction' ),
					'type'  => 'card',
					'desc'  => $link_html,
					'settings' => array(
						array(
							'row_class' => 'urcr_enable_disable urcr_content_restriction_enable',
							'title'     => __( 'Enable Content Restriction ?', 'user-registration-content-restriction' ),
							'desc'      => __( 'Check To Enable Content Restriction', 'user-registration-content-restriction' ),
							'id'        => 'user_registration_content_restriction_enable',
							'default'   => 'yes',
							'type'      => 'checkbox',
							'autoload'  => false,
						),

						array(
							'row_class' => 'urcr_content_restriction_allow_access_to',
							'title'     => __( 'Allow Access To', 'user-registration-content-restriction' ),
							'desc'      => __( 'Select Option To Allow Access To', 'user-registration-content-restriction' ),
							'id'        => 'user_registration_content_restriction_allow_access_to',
							'type'      => 'select',
							'class'     => 'ur-enhanced-select',
							'css'       => 'min-width: 350px;',
							'desc_tip'  => true,
							'options'   => array( 'All Logged In Users', 'Choose Specific Roles', 'Guest Users' ),
						),

						array(
							'row_class' => 'urcr_content_restriction_allow_access_to_roles',
							'title'     => __( 'Select Roles', 'user-registration-content-restriction' ),
							'desc'      => __( 'The roles selected here will have access to restricted content.', 'user-registration-content-restriction' ),
							'id'        => 'user_registration_content_restriction_allow_to_roles',
							'default'   => array( 'administrator' ),
							'type'      => 'multiselect',
							'class'     => 'ur-enhanced-select',
							'css'       => 'min-width: 350px;',
							'desc_tip'  => true,
							'options'   => urcr_get_all_roles(),
						),

						array(
							'title'    => __( 'Restricted Content Message', 'user-registration-content-restriction' ),
							'desc'     => __( 'The message you would like to display in restricted content.', 'user-registration-content-restriction' ),
							'id'       => 'user_registration_content_restriction_message',
							'type'     => 'tinymce',
							'default'  => 'This content is restricted!',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
						),
					),
				),
			),
		)
	);
}

function urcr_get_allow_options() {

	return apply_filters( 'user_registration_content_restriction_to_roles', $all_roles );
}


function urcr_get_all_roles() {
	global $wp_roles;

	if ( ! class_exists( 'WP_Roles' ) ) {
		return;
	}

	$roles = array();
	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}
	$roles = $wp_roles->roles;

	$all_roles = array();

	foreach ( $roles as $role_key => $role ) {

		$all_roles[ $role_key ] = $role['name'];
	}

	return apply_filters( 'user_registration_content_restriction_to_roles', $all_roles );
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param  string|array $var Data to sanitize.
 * @return string|array
 */
function urcr_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'urcr_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Get a list containing all available capabilities.
 *
 * @since 2.0.0
 *
 * @return array List of capabilities.
 */
function urcr_get_all_capabilities() {
	global $wp_roles;

	$wp_capabilities = array();

	foreach ( $wp_roles->roles as $role ) {
		$role_cap_slugs    = array_keys( $role['capabilities'] );
		$role_capabilities = array_combine( $role_cap_slugs, $role_cap_slugs );
		$wp_capabilities   = array_merge( $wp_capabilities, $role_capabilities );
	}

	return apply_filters( 'urcr_capabilities_list', $wp_capabilities );
}

/**
 * See if the given access rule is enabled.
 *
 * @since 2.0.0
 *
 * @param array $access_rule Acess Rule.
 *
 * @return bool
 */
function urcr_is_access_rule_enabled( $access_rule = array() ) {
	$access_rule = (array) $access_rule;

	if ( isset( $access_rule['enabled'] ) && true === $access_rule['enabled'] ) {
		return true;
	}
	return false;
}

/**
 * See if any action has been specified for a content acess rule.
 *
 * @since 2.0.0
 *
 * @param array $access_rule Access Rule.
 *
 * @return bool
 */
function urcr_is_action_specified( $access_rule = array() ) {
	$access_rule = (array) $access_rule;

	if ( ! empty( $access_rule['actions'] ) ) {
		$actions = (array) $access_rule['actions'];

		foreach ( $actions as $action ) {
			if ( ! empty( $action['type'] ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * See if the post is in the provided targets list.
 *
 * @since 2.0.0
 *
 * @param array       $targets Targets list.
 * @param object|null $target_post Post to check against.
 *
 * @return bool
 */
function urcr_is_target_post( $targets = array(), $target_post = null ) {

	if ( is_array( $targets ) ) {

		foreach ( $targets as $target ) {
			if ( isset( $target['type'] ) && ! empty( $target['value'] ) ) {
				switch ( $target['type'] ) {
					case 'wp_posts':
						$post_id         = ( 'object' === gettype( $target_post ) && $target_post->ID ) ? strval( $target_post->ID ) : '0';
						$target_post_ids = (array) $target['value'];

						if ( in_array( $post_id, $target_post_ids, true ) ) {
							return true;
						}
						break;

					case 'wp_pages':
						$page_id         = ( 'object' === gettype( $target_post ) && $target_post->ID ) ? strval( $target_post->ID ) : '0';
						$target_page_ids = (array) $target['value'];

						if ( in_array( $page_id, $target_page_ids, true ) ) {
							return true;
						}
						break;

					case 'post_types':
						$post_type         = ( 'object' === gettype( $target_post ) && $target_post->post_type ) ? strval( $target_post->post_type ) : '';
						$post_type         = ( is_singular( 'product' ) && is_array( $target_post ) ) ? $target_post[0]->post_type : $post_type;
						$target_post_types = (array) $target['value'];

						if ( in_array( $post_type, $target_post_types, true ) ) {
							return true;
						}
						break;

					case 'taxonomy':
						if ( ! empty( $target['taxonomy'] ) && ! empty( $target['value'] ) ) {
							if ( has_term( (array) $target['value'], $target['taxonomy'] ) ) {
								return true;
							}
						}
						break;
					case 'whole_site':
						return true;
						break;

					default:
						break;
				}
			}
		}
	}
	return false;
}

/**
 * See if the required conditions are met by the given logic map to be resolved as true.
 *
 * @deprecated 1.1.2
 *
 * @param array $targets Targets list.
 *
 * @return bool
 */
function urcr_is_current_target( $targets = array() ) {
	if ( function_exists( 'ur_deprecated_function' ) ) {
		ur_deprecated_function( 'urcr_is_current_target', '1.1.2', 'urcr_is_target_post' );
	}
	return urcr_is_target_post( $targets, null );
}

/**
 * See if the required conditions are met by the given logic map to be resolved as true.
 *
 * @since 2.0.0
 *
 * @param array       $logic_map Logic Map.
 * @param object|null $target_post Post to check against.
 *
 * @return bool
 */
function urcr_is_allow_access( $logic_map = array(), $target_post = null ) {
	global $post;

	if ( ! is_object( $target_post ) ) {
		$target_post = $post;
	}

	$logic_map = (array) $logic_map;

	if ( ! empty( $logic_map ) ) {
		$type = $logic_map['type'];

		// Process Logic Map.
		if ( 'group' === $type ) {
			$gate = ! empty( $logic_map['logic_gate'] ) ? $logic_map['logic_gate'] : 'OR';

			foreach ( $logic_map['conditions'] as $sub_logic_map ) {
				$is_allow_access = urcr_is_allow_access( $sub_logic_map, $target_post );

				if ( 'AND' === $gate && false === $is_allow_access ) {
					return false;
				} elseif ( 'NOT' === $gate && true === $is_allow_access ) {
					return false;
				} elseif ( 'OR' === $gate && true === $is_allow_access ) {
					return true;
				}
			}
			if ( 'AND' === $gate || 'NOT' === $gate ) {
				return true;
			}
			if ( 'OR' === $gate ) {
				return false;
			}
		} else {
			$user = wp_get_current_user();

			switch ( $type ) {
				case 'roles':
					if ( $user->ID && count( array_intersect( (array) $user->roles, $logic_map['value'] ) ) ) {
						return true;
					}
					break;

				case 'capabilities':
					if ( $user->ID ) {
						$allowed_caps = isset( $logic_map['value'] ) ? (array) $logic_map['value'] : array();

						foreach ( $allowed_caps as $cap ) {
							if ( current_user_can( $cap, $target_post->ID ) ) {
								return true;
							}
						}
					}
					break;

				case 'user_registered_date':
					if ( $user->ID ) {
						$registered_date = ! empty( $user->data->user_registered ) ? $user->data->user_registered : '';
						$date_range      = ! empty( $logic_map['value'] ) ? explode( 'to', (string) $logic_map['value'] ) : array();
						$start_date      = ! empty( $date_range[0] ) ? trim( $date_range[0] ) : '';
						$end_date        = ! empty( $date_range[1] ) ? trim( $date_range[1] ) : '';

						if ( ! empty( $start_date ) && ! empty( $end_date ) && ur_falls_in_date_range( $registered_date, $start_date, $end_date ) ) {
							return true;
						}
					}
					break;

				case 'user_state':
					$should_be_logged_in = ( isset( $logic_map['value'] ) && 'logged-in' === $logic_map['value'] ) ? true : false;

					if ( $should_be_logged_in ) {
						return is_user_logged_in();
					} else {
						return ! is_user_logged_in();
					}
					break;

				case 'post_count':
					if ( $user->ID ) {
						$public_posts_by_user_count   = (int) count_user_posts( $user->ID, 'post', true );
						$minimum_required_posts_count = ! empty( $logic_map['value'] ) ? (int) $logic_map['value'] : 0;

						if ( $public_posts_by_user_count >= $minimum_required_posts_count ) {
							return true;
						}
					}
					break;

				case 'email_domain':
					if ( $user->ID ) {
						$domains           = ! empty( $logic_map['value'] ) ? explode( ',', (string) $logic_map['value'] ) : array();
						$domains           = array_map( 'trim', $domains );
						$user_email        = explode( '@', $user->data->user_email );
						$user_email_domain = isset( $user_email[1] ) ? trim( $user_email[1] ) : '';

						if ( in_array( $user_email_domain, $domains, true ) ) {
							return true;
						}
					}
					break;

				case 'registration_source':
					if ( $user->ID ) {
						$registered_source = ur_get_registration_source_id( $user->ID );
						$sources           = ! empty( $logic_map['value'] ) ? $logic_map['value'] : array();

						if ( in_array( $registered_source, $sources, true ) ) {
							return true;
						}
					}
					break;
			}
			return false;
		}
	}
	return true;
}

/**
 * See if the required conditions are met by the given logic map to be resolved as true.
 *
 * @deprecated 1.1.2
 *
 * @param array $logic_map Logic Map.
 *
 * @return bool
 */
function urcr_resolve_logic_map( $logic_map = array() ) {
	if ( function_exists( 'ur_deprecated_function' ) ) {
		ur_deprecated_function( 'urcr_resolve_logic_map', '1.1.2', 'urcr_is_allow_access' );
	}
	return urcr_is_allow_access( $logic_map, null );
}

/**
 * See if a elementor content have been restricted and shown a message.
 *
 * @since 1.1.3
 *
 * @return boolean
 */
function urcr_is_elementor_content_restricted() {
	return isset( $GLOBALS['urcr_ecr_flag'] ) && $GLOBALS['urcr_ecr_flag'] === true;
}

/**
 * Set a flag to indicate that a elementor content have been restricted and shown a message.
 *
 * @since 1.1.3
 */
function urcr_set_elementor_content_restricted() {
	$GLOBALS['urcr_ecr_flag'] = true;
}

/**
 * Apply content restriction to the current content.
 *
 * @since 2.0.0
 *
 * @param array       $actions Sequence of actions to run.
 * @param object|null $target_post Post to check against.
 *
 * @return bool
 */
function urcr_apply_content_restriction( $actions, &$target_post = null ) {
	global $post;

	if ( ! is_object( $target_post ) ) {
		$target_post = $post;
	}

	$actions = (array) $actions;
	$action  = $actions[0];

	if ( $target_post->ID && ! empty( $action['type'] ) ) {
		if ( 'message' === $action['type'] ) {
			$message = ! empty( $action['message'] ) ? urldecode( $action['message'] ) : '';

			$target_post->post_content = $message;

			// Add filter for elementor content.
			add_filter(
				'elementor/frontend/the_content',
				function () use ( $message ) {
					if ( ! urcr_is_elementor_content_restricted() ) {
						urcr_set_elementor_content_restricted();

						return $message;
					}
					return '';
				}
			);

			return true;
		} elseif ( 'redirect' === $action['type'] ) {
			$redirect_url = trim( ! empty( $action['redirect_url'] ) ? $action['redirect_url'] : admin_url() );
			$redirect_url = urldecode( $redirect_url );

			if ( strpos( $redirect_url, 'http' ) !== 0 ) {
				$redirect_url = 'http://' . $redirect_url;
			}
			wp_redirect( $redirect_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		} elseif ( 'redirect_to_local_page' === $action['type'] ) {
			$page_id = ! empty( $action['local_page'] ) ? $action['local_page'] : null;

			if ( $target_post->ID && strval( $page_id ) === strval( $target_post->ID ) ) {
				wp_die( esc_html__( 'URCR: Cannot redirect to same page. The target page was selected as redirection target for content restriction.', 'user-registration-content-restriction' ) );
			}
			if ( $page_id ) {
				$page_url = get_page_link( $page_id );
				wp_redirect( $page_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				exit;
			}
		} elseif ( 'ur-form' === $action['type'] ) {
			$ur_form_id                = ! empty( $action['ur_form'] ) ? $action['ur_form'] : '';
			$shortcode                 = sprintf( '[user_registration_form id="%s"]', $ur_form_id );
			$target_post->post_content = $shortcode;

			// Add filter for elementor content.
			add_filter(
				'elementor/frontend/the_content',
				function () use ( $shortcode ) {
					if ( ! urcr_is_elementor_content_restricted() ) {
						urcr_set_elementor_content_restricted();

						return $shortcode;
					}
					return '';
				}
			);

			return true;
		} elseif ( 'shortcode' === $action['type'] && ! empty( $action['shortcode'] ) ) {
			$shortcode_tag             = ! empty( $action['shortcode']['tag'] ) ? $action['shortcode']['tag'] : '';
			$shortcode_args            = ! empty( $action['shortcode']['args'] ) ? urldecode( $action['shortcode']['args'] ) : '';
			$shortcode                 = sprintf( '[%s %s]', $shortcode_tag, $shortcode_args );
			$target_post->post_content = $shortcode;

			// Add filter for elementor content.
			add_filter(
				'elementor/frontend/the_content',
				function () use ( $shortcode ) {
					if ( ! urcr_is_elementor_content_restricted() ) {
						urcr_set_elementor_content_restricted();

						return $shortcode;
					}
					return '';
				}
			);

			return true;
		}
	}
	return false;
}

/**
 * Get other templates (e.g. my account) passing attributes and including the file.
 *
 * @param string $template_name Template Name.
 * @param array  $args Extra arguments(default: array()).
 * @param string $template_path Path of template provided (default: '').
 * @param string $default_path  Default path of template provided(default: '').
 */
function urcr_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args ); // phpcs:ignore
	}

	$located = urcr_locate_template( $template_name, $template_path, $default_path );

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'urcr_get_template', $located, $template_name, $args, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $located ) ), '1.0' );

		return;
	}

	do_action( 'user_registration_content_restriction_before_template_part', $template_name, $template_path, $located, $args );

	include $located;

	do_action( 'user_registration_content_restriction_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *        yourtheme        /    $template_path    /    $template_name
 *        yourtheme        /    $template_name
 *        $default_path    /    $template_name
 *
 * @param string $template_name Template Name.
 * @param string $template_path Path of template provided (default: '').
 * @param string $default_path  Default path of template provided(default: '').
 *
 * @return string
 */
function urcr_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = URCR()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = URCR()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template.
	if ( ! $template || UR_TEMPLATE_DEBUG_MODE ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'user_registration_content_restriction_locate_template', $template, $template_name, $template_path );
}

/**
 * Common function for template access actions
 *
 * @param mixed $target_post Post.
 * @param mixed $action Action.
 */
function urcr_advanced_access_actions( $target_post, $action ) {
	if ( $target_post->ID && ! empty( $action['type'] ) ) {
		if ( 'message' === $action['type'] ) {
			$message = ! empty( $action['message'] ) ? urldecode( $action['message'] ) : '';
			echo wp_kses_post( $message );
		} elseif ( 'redirect' === $action['type'] ) {
			$redirect_url = trim( ! empty( $action['redirect_url'] ) ? $action['redirect_url'] : admin_url() );
			$redirect_url = urldecode( $redirect_url );

			if ( strpos( $redirect_url, 'http' ) !== 0 ) {
				$redirect_url = 'http://' . $redirect_url;
			}
			echo "<script>window.location.href = '" . esc_url( $redirect_url ) . "';</script>";

			wp_redirect( $redirect_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		} elseif ( 'redirect_to_local_page' === $action['type'] ) {
			$page_id = ! empty( $action['local_page'] ) ? $action['local_page'] : null;

			if ( $target_post->ID && strval( $page_id ) === strval( $target_post->ID ) ) {
				wp_die( esc_html__( 'URCR: Cannot redirect to same page. The target page was selected as redirection target for content restriction.', 'user-registration-content-restriction' ) );
			}
			if ( $page_id ) {
				$page_url = get_page_link( $page_id );
				echo "<script>window.location.href = '" . esc_url( $page_url ) . "';</script>";
				wp_redirect( $page_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				exit;
			}
		} elseif ( 'ur-form' === $action['type'] ) {
			$ur_form_id                = ! empty( $action['ur_form'] ) ? $action['ur_form'] : '';
			$shortcode                 = sprintf( '[user_registration_form id="%s"]', $ur_form_id );
			if ( function_exists( 'apply_shortcodes' ) ) {
				echo apply_shortcodes( $shortcode );
			} else {
				echo do_shortcode( $shortcode );
			}
		} elseif ( 'shortcode' === $action['type'] && ! empty( $action['shortcode'] ) ) {
			$shortcode_tag             = ! empty( $action['shortcode']['tag'] ) ? $action['shortcode']['tag'] : '';
			$shortcode_args            = ! empty( $action['shortcode']['args'] ) ? urldecode( $action['shortcode']['args'] ) : '';
			$shortcode                 = sprintf( '[%s %s]', $shortcode_tag, $shortcode_args );
			if ( function_exists( 'apply_shortcodes' ) ) {
				echo apply_shortcodes( $shortcode );
			} else {
				echo do_shortcode( $shortcode );
			}
		}
	}
}
