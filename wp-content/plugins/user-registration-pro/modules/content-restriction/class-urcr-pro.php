<?php

defined( 'ABSPATH' ) || exit;

class URCR_Pro {
	private $restriction_messages = [];

	public function __construct() {
		$this->init_hooks();
	}

	private function init_hooks() {
		add_filter( 'urcr_content_type_options', [ $this, 'add_content_type_options' ] );
		add_filter( 'urcr_localized_data', [ $this, 'update_localized_data' ] );
		add_filter( 'urcr_match_target_type', [ $this, 'match_menu_items' ], 10, 3 );
		add_filter( 'urcr_match_target_type', [ $this, 'match_custom_uri' ], 10, 3 );
		add_filter( 'wp_nav_menu_objects', array( $this, 'restrict_menu_items' ), 10, 2 );
		add_filter( 'render_block_core/navigation-link', array( $this, 'restrict_navigation_link_block' ), 10, 2 );
		add_filter( 'render_block_core/navigation-submenu', array( $this, 'restrict_navigation_submenu_block' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'handle_restricted_menu_url' ) );
		add_action( 'template_redirect', array( $this, 'handle_restricted_custom_uri' ) );
		add_action( 'wp_footer', array( $this, 'add_content_restriction_dialog_html' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_filter( 'walker_nav_menu_start_el', array( $this, 'add_menu_item_data_attributes' ), 10, 4 );
		add_filter( 'urcr_type_labels', [ $this, 'add_type_labels' ] );
		add_filter( 'urcr_default_content_target_html', [ $this, 'custom_uri_target_html' ], 10, 4 );
	}

	public function custom_uri_target_html( $html, $target_id, $type, $value ) {
		if ( 'custom_uri' !== $type ) {
			return $html;
		}
		$value = is_array( $value ) ? $value : [ '', false ];
		return sprintf(
			'<div data-content-type="%s" data-target-id="%s" style="display:flex;align-items:center;gap:4px;flex:1"><input value="%s" type="text" style="flex:1;" class="components-text-control__input urcr-condition-value-input urcr-condition-value-text urcr-form-field-value-input" /></div>',
			esc_attr( $type ),
			esc_attr( $target_id ),
			esc_attr( $value[0] ?? '' ),
			checked( true, $value[1] ?? false, false ),
		);
	}

	public function add_type_labels( $labels ) {
		$labels['menu_items'] = __( 'Menu Items', 'user-registration' );
		$labels['custom_uri'] = __( 'Custom URI', 'user-registration' );
		return $labels;
	}

	public function match_menu_items( $result, $target, $target_post ) {
		if ( 'menu_items' !== $target['type'] || is_super_admin() ) {
			return $result;
		}
		if ( ! empty( $target['value'] ) ) {
			$target_menu_item_ids = (array) $target['value'];

			if ( is_object( $target_post ) && isset( $target_post->ID ) ) {
				$menu_item_id = strval( $target_post->ID );
				if ( in_array( $menu_item_id, $target_menu_item_ids, true ) ) {
					return true;
				}
			}
			if ( is_scalar( $target_post ) ) {
				if ( in_array( $target_post, $target_menu_item_ids, true ) ) {
					return true;
				}
			}
		}

		return $result;
	}

	/**
	 * @param bool $result
	 * @param array $target
	 * @param mixed $target_post
	 * @return bool
	 */
	public function match_custom_uri( $result, $target, $target_post ) {
		if ( 'custom_uri' !== $target['type'] || is_super_admin() ) {
			return $result;
		}

		if ( empty( $target['value'] ) || ! is_array( $target['value'] ) ) {
			return $result;
		}

		$uri_pattern = $target['value'][0] ?? '';
		$is_regex    = $target['value'][1] ?? false;

		if ( empty( $uri_pattern ) ) {
			return $result;
		}

		$current_uri = $this->get_current_uri();

		if ( empty( $current_uri ) ) {
			return $result;
		}

		return $this->match_pattern( $uri_pattern, $current_uri );
	}

	/**
	 * @return string
	 */
	private function get_current_uri() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return '';
		}

		$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] );
		$url         = trim( home_url( $request_uri ) );

		if ( strpos( $url, '://' ) !== false ) {
			$path = wp_parse_url( $url, PHP_URL_PATH );
			$url  = null !== $path ? $path : '/';
		}

		$query_pos = strpos( $url, '?' );
		if ( false !== $query_pos ) {
			$url = substr( $url, 0, $query_pos );
		}

		$fragment_pos = strpos( $url, '#' );
		if ( false !== $fragment_pos ) {
			$url = substr( $url, 0, $fragment_pos );
		}

		$url = rtrim( $url, '/' );

		return '' === $url ? '/' : $url;
	}

	/**
	 * Supported patterns:
	 * - /path           : Exact match
	 * - /path/*         : Direct children only
	 * - /path/**        : All descendants
	 * - /path | /other  : Multiple patterns (OR)
	 * - !/path/**       : Exclude pattern
	 *
	 * @param string $uri_pattern
	 * @param string $current_uri
	 * @return bool
	 */
	private function match_pattern( $uri_pattern, $current_uri ) {
		$uri_pattern = trim( $uri_pattern );

		if ( empty( $uri_pattern ) ) {
			return false;
		}

		$parts = preg_split( '/\s*\|\s*/', $uri_pattern, -1, PREG_SPLIT_NO_EMPTY );

		if ( empty( $parts ) ) {
			return false;
		}

		$positive_patterns = array();
		$negative_patterns = array();

		foreach ( $parts as $pattern_part ) {
			$pattern_part = trim( $pattern_part );

			if ( empty( $pattern_part ) ) {
				continue;
			}

			$is_negated = false;

			if ( 0 === strpos( $pattern_part, '!' ) ) {
				$is_negated   = true;
				$pattern_part = trim( substr( $pattern_part, 1 ) );

				if ( empty( $pattern_part ) ) {
					continue;
				}
			}

			$regex_pattern = $this->convert_uri_pattern_to_regex( $pattern_part );

			if ( $is_negated ) {
				$negative_patterns[] = $regex_pattern;
			} else {
				$positive_patterns[] = $regex_pattern;
			}
		}

		if ( ! empty( $negative_patterns ) ) {
			$negative_pattern = '#^(' . implode( '|', $negative_patterns ) . ')$#';
			if ( preg_match( $negative_pattern, $current_uri ) ) {
				return false;
			}
		}

		if ( empty( $positive_patterns ) ) {
			return true;
		}

		$positive_pattern = '#^(' . implode( '|', $positive_patterns ) . ')$#';
		return (bool) preg_match( $positive_pattern, $current_uri );
	}

	private function convert_uri_pattern_to_regex( $pattern ) {
		$parsed_path = wp_parse_url( $pattern, PHP_URL_PATH );
		$pattern     = $parsed_path ?? $pattern;

		$pattern = str_replace( '/**', '<<<SLASH_DOUBLE_WILDCARD>>>', $pattern );
		$pattern = str_replace( '/*', '<<<SLASH_SINGLE_WILDCARD>>>', $pattern );
		$pattern = str_replace( '**', '<<<DOUBLE_WILDCARD>>>', $pattern );
		$pattern = str_replace( '*', '<<<SINGLE_WILDCARD>>>', $pattern );

		$pattern = preg_quote( $pattern, '#' );

		$pattern = str_replace( '\<\<\<SLASH_DOUBLE_WILDCARD\>\>\>', '(?:/.*)?', $pattern );
		$pattern = str_replace( '\<\<\<SLASH_SINGLE_WILDCARD\>\>\>', '/[^/]+', $pattern );
		$pattern = str_replace( '\<\<\<DOUBLE_WILDCARD\>\>\>', '.*', $pattern );
		$pattern = str_replace( '\<\<\<SINGLE_WILDCARD\>\>\>', '[^/]*', $pattern );

		if ( '\\/' !== $pattern ) {
			$pattern = rtrim( $pattern, '\\/' ) . '\/?';
		}

		return $pattern;
	}

	public function add_content_type_options( $options ) {
		$index     = array_search( 'post_types', array_column( $options, 'value' ), true );
		$index     = $index !== false ? $index : count( $options );
		$options   = array_merge(
			array_slice( $options, 0, $index + 1 ),
			[
				[
					'value' => 'menu_items',
					'label' => __( 'Menu Items', 'user-registration-pro' ),
				],
			],
			array_slice( $options, $index + 1 )
		);
		$options[] = [
			'value' => 'custom_uri',
			'label' => __( 'Custom URI', 'user-registration-pro' ),
		];
		return $options;
	}

	public function update_localized_data( $data ) {
		$data['menu_items'] = $this->get_menu_items();
		return $data;
	}

	public function get_menu_items() {

		$menu_items = [];

		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			$wp_navigation_posts = get_posts(
				[
					'post_type'      => 'wp_navigation',
					'post_status'    => [ 'publish', 'draft' ],
					'posts_per_page' => -1,
				]
			);
			if ( ! empty( $wp_navigation_posts ) ) {
				foreach ( $wp_navigation_posts as $wp_navigation_post ) {
					$parsed_content   = parse_blocks( $wp_navigation_post->post_content );
					$navigation_items = $this->extract_navigation_items( $parsed_content, 0, $wp_navigation_post->ID );

					if ( ! empty( $navigation_items ) ) {
						$menu_items[] = [
							'group'   => $wp_navigation_post->post_title,
							'options' => $navigation_items,
						];
					}
				}
			}
		} else {
			foreach ( wp_get_nav_menus() as $menu ) {
				$items = wp_get_nav_menu_items( $menu->term_id );
				if ( empty( $items ) ) {
					continue;
				}
				$menu_items[] = [
					'group'   => $menu->name,
					'options' => array_map(
						function ( $item ) {
							return [
								'value' => $item->ID,
								'label' => $item->menu_item_parent ? ' — ' . $item->title : $item->title,
							];
						},
						$items
					),
				];
			}
		}

		return $menu_items;
	}

	/**
	 * @param array  $blocks
	 * @param int $depth
	 * @return array
	 */
	private function extract_navigation_items( $blocks, $depth = 0 ) {
		$items = [];

		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && in_array( $block['blockName'], [ 'core/navigation-link', 'core/navigation-submenu' ], true ) ) {
				$attrs = isset( $block['attrs'] ) ? $block['attrs'] : [];

				$label = isset( $attrs['label'] ) ? $attrs['label'] : '';

				if ( empty( $label ) && isset( $block['innerHTML'] ) ) {
					$label = wp_strip_all_tags( html_entity_decode( $block['innerHTML'], ENT_QUOTES, 'UTF-8' ) );
				}

				$url  = isset( $attrs['url'] ) ? $attrs['url'] : '';
				$id   = isset( $attrs['id'] ) ? $attrs['id'] : '';
				$kind = isset( $attrs['kind'] ) ? $attrs['kind'] : '';

				if ( ! empty( $id ) && ! empty( $kind ) ) {
					$value = base64_encode( $kind . ':' . $id ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				} elseif ( ! empty( $url ) ) {
					$value = base64_encode( 'url:' . $url ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				} elseif ( ! empty( $label ) ) {
					$value = base64_encode( 'label:' . $label ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				} else {
					continue;
				}

				$display_label = $depth > 0 ? str_repeat( ' — ', $depth ) . $label : $label;

				if ( ! empty( $label ) ) {
					$items[] = [
						'value' => $value,
						'label' => $display_label,
					];
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$nested_items = self::extract_navigation_items( $block['innerBlocks'], $depth + 1 );
				$items        = array_merge( $items, $nested_items );
			}
		}

		return $items;
	}

	/**
	 * @param array $items
	 * @param stdClass $args
	 * @return array
	 */
	public function restrict_menu_items( $items, $args ) {
		if ( empty( $items ) || ! is_array( $items ) || is_super_admin() ) {
			return $items;
		}

		foreach ( $items as $item ) {
			if ( $this->is_menu_item_restricted( $item ) ) {
				$item_url = isset( $item->url ) ? $item->url : '';
				if ( ! empty( $item_url ) ) {
					$item->attr_title = isset( $item->attr_title ) ? $item->attr_title : '';

					$message_html = $this->get_menu_item_restriction_message_template( $item );
					$identifier   = is_object( $item ) && isset( $item->ID ) ? strval( $item->ID ) : '';
					$safe_id      = ! empty( $identifier ) ? sanitize_html_class( 'menu-' . $identifier ) : '';

					if ( ! empty( $safe_id ) ) {
						$this->restriction_messages[ $safe_id ] = $message_html;
					}

					if ( ! isset( $item->urcr_data ) ) {
						$item->urcr_data = array();
					}
					$item->urcr_data['restricted']   = true;
					$item->urcr_data['identifier']   = $safe_id;
					$item->urcr_data['message_html'] = $message_html;

					if ( ! $this->is_wordpress_url( $item_url ) ) {
						$item->url = $this->get_restricted_menu_url( $item );
					}
				}
			}
		}

		return $items;
	}

	/**
	 * @param int|string|object $menu_item
	 * @return bool
	 */
	public function is_menu_item_restricted( $menu_item ) {
		$access_rule_posts = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);

		$restricted = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );
			if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
				continue;
			}
			if ( ! is_array( $access_rule['logic_map'] ) ) {
				continue;
			}
			if ( empty( $access_rule['logic_map']['conditions'] ) ) {
				continue;
			}

			if ( ! $this->rule_targets_menu_items( $access_rule ) ) {
				continue;
			}

			$menu_item_targets = $this->get_menu_item_targets_only( $access_rule );
			if ( empty( $menu_item_targets ) ) {
				continue;
			}

			if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {
				$is_target = urcr_is_target_post( $menu_item_targets, $menu_item );

				if ( $is_target ) {
					$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $menu_item );
					$access_control      = isset( $access_rule['actions'][0]['access_control'] ) && ! empty( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : 'access';
					$restricted          = ( $should_allow_access && 'restrict' === $access_control ) || ( ! $should_allow_access && 'access' === $access_control );
				}
			}
		}

		return $restricted;
	}

	/**
	 * @param array $access_rule
	 * @return bool
	 */
	private function rule_targets_menu_items( $access_rule ) {
		if ( empty( $access_rule['target_contents'] ) || ! is_array( $access_rule['target_contents'] ) ) {
			return false;
		}
		$types = wp_list_pluck( $access_rule['target_contents'], 'type' );
		return in_array( 'menu_items', $types, true );
	}

	/**
	 * Get only the menu_items targets from a rule so menu restriction is evaluated only against selected menu items.
	 *
	 * @param array $access_rule Decoded access rule (post_content).
	 * @return array
	 */
	private function get_menu_item_targets_only( $access_rule ) {
		if ( empty( $access_rule['target_contents'] ) || ! is_array( $access_rule['target_contents'] ) ) {
			return array();
		}
		return array_values( array_filter( $access_rule['target_contents'], function ( $target ) {
			return isset( $target['type'] ) && 'menu_items' === $target['type'];
		} ) );
	}

	/**
	 * @param int|string|object $menu_item
	 * @return string
	 */
	private function get_menu_item_restriction_message_template( $menu_item ) {
		$message  = $this->get_menu_item_restriction_message( $menu_item );
		$template = apply_filters(
			'urcr_menu_restriction_message_template',
			'<div class="urcr-restriction-message">%s</div>',
			$menu_item,
			$message
		);

		return sprintf( $template, wp_kses_post( $message ) );
	}

	/**
	 * @param int|string|object $menu_item
	 * @return string
	 */
	private function get_menu_item_restriction_message( $menu_item ) {
		$access_rule_posts = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
				continue;
			}

			if ( ! is_array( $access_rule['logic_map'] ) ) {
				continue;
			}

			if ( empty( $access_rule['logic_map']['conditions'] ) ) {
				continue;
			}

			if ( ! $this->rule_targets_menu_items( $access_rule ) ) {
				continue;
			}

			$menu_item_targets = $this->get_menu_item_targets_only( $access_rule );
			if ( empty( $menu_item_targets ) ) {
				continue;
			}

			if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {
				$is_target = urcr_is_target_post( $menu_item_targets, $menu_item );
				if ( true === $is_target ) {
					$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $menu_item );
					$access_control      = isset( $access_rule['actions'][0]['access_control'] ) && ! empty( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : 'access';
					if ( ( true === $should_allow_access && 'restrict' === $access_control ) || ( false === $should_allow_access && 'access' === $access_control ) ) {
						$action = $access_rule['actions'][0];
						if ( isset( $action['type'] ) && 'message' === $action['type'] && ! empty( $action['message'] ) ) {
							$message = urldecode( $action['message'] );
							$message = apply_filters( 'user_registration_process_smart_tags', $message );
							return $message;
						}
					}
				}
			}
		}

		return $this->message();
	}

	public function message() {
		$message = get_option( 'user_registration_content_restriction_message' );
		$message = ( false === $message ) ? esc_html__( 'This content is restricted!', 'user-registration' ) : $message;
		$message = apply_filters( 'user_registration_process_smart_tags', $message );
		return '<span class="urcr-restrict-msg">' . $message . '</span>';
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	private function is_wordpress_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}
		$home_url  = home_url();
		$site_url  = site_url();
		$url_host  = wp_parse_url( $url, PHP_URL_HOST );
		$home_host = wp_parse_url( $home_url, PHP_URL_HOST );
		$site_host = wp_parse_url( $site_url, PHP_URL_HOST );

		if ( $url_host === $home_host || $url_host === $site_host ) {
			return true;
		}
		if ( strpos( $url, $home_url ) === 0 || strpos( $url, $site_url ) === 0 ) {
			return true;
		}
		return false;
	}

	/**
	 * @param int|string|object $menu_item
	 * @return string
	 */
	private function get_restricted_menu_url( $menu_item ) {
		$identifier = '';
		if ( is_object( $menu_item ) && isset( $menu_item->ID ) ) {
			$identifier = $menu_item->ID;
		} elseif ( is_numeric( $menu_item ) ) {
			$identifier = $menu_item;
		} elseif ( is_string( $menu_item ) ) {
			$identifier = $menu_item;
		}
		if ( empty( $identifier ) ) {
			return '';
		}
		$restricted_url = add_query_arg(
			array(
				'urcr_restricted_menu' => urlencode( $identifier ),
			),
			home_url( '/' )
		);

		return $restricted_url;
	}

	/**
	 * @param string $block_content
	 * @param array $block
	 * @return string
	 */
	public function restrict_navigation_submenu_block( $block_content, $block ) {
		return $this->restrict_navigation_link_block( $block_content, $block );
	}

	/**
	 * @param string $block_content
	 * @param array $block
	 * @return string
	 */
	public function restrict_navigation_link_block( $block_content, $block ) {
		if ( empty( $block['attrs'] ) ) {
			return $block_content;
		}

		$attrs = $block['attrs'];

		$url   = isset( $attrs['url'] ) ? $attrs['url'] : '';
		$id    = isset( $attrs['id'] ) ? $attrs['id'] : '';
		$kind  = isset( $attrs['kind'] ) ? $attrs['kind'] : '';
		$label = isset( $attrs['label'] ) ? $attrs['label'] : '';

		$base_identifier = '';
		if ( ! empty( $id ) && ! empty( $kind ) ) {
			$base_identifier = base64_encode( $kind . ':' . $id ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		} elseif ( ! empty( $url ) ) {
			$base_identifier = base64_encode( 'url:' . $url ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		} elseif ( ! empty( $label ) ) {
			$base_identifier = base64_encode( 'label:' . $label ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		}

		if ( empty( $base_identifier ) ) {
			return $block_content;
		}

		if ( $this->is_menu_item_restricted( $base_identifier ) ) {
			$message_html = $this->get_menu_item_restriction_message_template( $base_identifier );

			$safe_identifier                                = sanitize_html_class( 'block-' . $base_identifier );
			$this->restriction_messages[ $safe_identifier ] = $message_html;

			$data_attrs = ' data-restricted="' . esc_attr( $safe_identifier ) . '"';

			if ( ! empty( $url ) && $this->is_wordpress_url( $url ) ) {
				$block_content = preg_replace( '/(<a[^>]*class=["\'])([^"\']*)(["\'])/i', '$1$2 urcr-restricted-menu-item$3', $block_content );
				if ( strpos( $block_content, 'data-restricted' ) === false ) {
					$block_content = preg_replace( '/(<a[^>]*)(>)/i', '$1' . $data_attrs . '$2', $block_content );
				}
				return $block_content;
			} else {
				$restricted_url = $this->get_restricted_menu_url( $base_identifier );
				if ( ! empty( $restricted_url ) ) {
					$block_content = preg_replace( '/(<a[^>]*\s+href=["\'])([^"\']*)(["\'][^>]*>)/i', '$1' . esc_url( $restricted_url ) . '$3' . $data_attrs, $block_content );
				}
			}
		}

		return $block_content;
	}

	/**
	 * @param WP $wp
	 */
	public function handle_restricted_menu_url( $wp ) {
		if ( isset( $_GET['urcr_restricted_menu'] ) ) {
			$menu_identifier = sanitize_text_field( $_GET['urcr_restricted_menu'] );
			$message         = $this->get_menu_item_restriction_message( $menu_identifier );
			wp_die( wp_kses_post( $message ), '', [] );
		}

		$requested_url = '';
		global $wp;
		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$requested_url = home_url( $_SERVER['REQUEST_URI'] );
		} elseif ( ! empty( $wp->request ) ) {
			$requested_url = home_url( '/' . $wp->request );
		}

		if ( ! empty( $requested_url ) && $this->is_wordpress_url( $requested_url ) ) {
			$menu_item = $this->get_menu_item_by_url( $requested_url );
			if ( $menu_item && $this->is_menu_item_restricted( $menu_item ) ) {
				$message = $this->get_menu_item_restriction_message( $menu_item );
				$this->render_restriction_html( $message );
				exit;
			}
		}
	}

	/**
	 * @param string $url
	 * @return object|null
	 */
	private function get_menu_item_by_url( $url ) {
		$url_normalized = trailingslashit( $url );
		$url_no_slash   = untrailingslashit( $url );

		$menus = wp_get_nav_menus();
		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			if ( ! empty( $items ) ) {
				foreach ( $items as $item ) {
					if ( isset( $item->url ) ) {
						$item_url_normalized = trailingslashit( $item->url );
						$item_url_no_slash   = untrailingslashit( $item->url );
						if ( $item->url === $url || $item_url_normalized === $url_normalized || $item_url_no_slash === $url_no_slash ) {
							return $item;
						}
					}
				}
			}
		}

		$post_id = url_to_postid( $url );
		if ( $post_id ) {
			$post = get_post( $post_id );
			if ( $post ) {
				$menu_items_linking_to_post = $this->get_menu_items_linking_to_post( $post );
				if ( ! empty( $menu_items_linking_to_post ) ) {
					foreach ( $menu_items_linking_to_post as $menu_item_identifier ) {
						if ( $this->is_menu_item_restricted( $menu_item_identifier ) ) {
							$menu_item            = new stdClass();
							$menu_item->ID        = $menu_item_identifier;
							$menu_item->url       = $url;
							$menu_item->object_id = $post_id;
							return $menu_item;
						}
					}
				}
			}
		}

		return null;
	}

	/**
	 * @param int|object $post
	 * @return array
	 */
	public function get_menu_items_linking_to_post( $post ) {
		$post_id    = is_object( $post ) ? $post->ID : $post;
		$menu_items = array();

		if ( empty( $post_id ) ) {
			return $menu_items;
		}

		$menus = wp_get_nav_menus();
		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			if ( ! empty( $items ) ) {
				foreach ( $items as $item ) {
					if ( isset( $item->object_id ) && (int) $item->object_id === (int) $post_id ) {
						$menu_items[] = $item->ID;
					} elseif ( isset( $item->url ) ) {
						$post_url = get_permalink( $post_id );
						if ( $item->url === $post_url ) {
							$menu_items[] = $item->ID;
						}
					}
				}
			}
		}

		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			$wp_navigation_posts = get_posts(
				array(
					'post_type'      => 'wp_navigation',
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => -1,
				)
			);

			foreach ( $wp_navigation_posts as $nav_post ) {
				$parsed_content = parse_blocks( $nav_post->post_content );
				$post_url       = get_permalink( $post_id );
				$post_type      = get_post_type( $post_id );
				$this->extract_navigation_links_for_post( $parsed_content, $post_id, $post_url, $post_type, $nav_post->ID, $menu_items );
			}
		}

		return $menu_items;
	}

	/**
	 * @param array $blocks
	 * @param int $post_id
	 * @param string $post_url
	 * @param string $post_type
	 * @param int $nav_post_id
	 * @param array &$menu_items
	 */
	private function extract_navigation_links_for_post( $blocks, $post_id, $post_url, $post_type, $nav_post_id, &$menu_items ) {
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && in_array( $block['blockName'], array( 'core/navigation-link', 'core/navigation-submenu' ), true ) ) {
				$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();

				$url  = isset( $attrs['url'] ) ? $attrs['url'] : '';
				$id   = isset( $attrs['id'] ) ? $attrs['id'] : '';
				$kind = isset( $attrs['kind'] ) ? $attrs['kind'] : '';

				$matches = false;
				if ( ! empty( $id ) && ! empty( $kind ) ) {
					if ( ( 'post-type' === $kind || 'postType' === $kind ) && (int) $id === (int) $post_id ) {
						$matches = true;
					}
				} elseif ( ! empty( $url ) && $url === $post_url ) {
					$matches = true;
				}

				if ( $matches ) {
					$base_identifier = '';
					if ( ! empty( $id ) && ! empty( $kind ) ) {
						$base_identifier = base64_encode( $kind . ':' . $id ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					} elseif ( ! empty( $url ) ) {
						$base_identifier = base64_encode( 'url:' . $url ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					}

					if ( ! empty( $base_identifier ) ) {
						$menu_items[] = 'nav_' . $nav_post_id . '_' . $base_identifier;
					}
				}
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				$this->extract_navigation_links_for_post( $block['innerBlocks'], $post_id, $post_url, $post_type, $nav_post_id, $menu_items );
			}
		}
	}

	private function render_restriction_html( $message ) {
		$login_page_id        = get_option( 'user_registration_login_page_id' );
		$registration_page_id = get_option( 'user_registration_member_registration_page_id' );
		$login_url            = $login_page_id ? get_permalink( $login_page_id ) : wp_login_url();
		$signup_url           = $registration_page_id ? get_permalink( $registration_page_id ) : ( $login_page_id ? get_permalink( $login_page_id ) : wp_registration_url() );

		remove_all_filters( 'body_class' );
		header( 'HTTP/1.1 401 Unauthorized' );
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta http-equiv="Content-Type"
		content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
	<title><?php echo esc_html( wp_get_document_title() ); ?></title>
		<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
		<?php
				urcr_get_template(
					'base-restriction-template.php',
					array(
						'message'    => $message,
						'login_url'  => $login_url,
						'signup_url' => $signup_url,
					)
				);
		?>
		<?php wp_footer(); ?>
</body>

</html>
		<?php
	}

	public function handle_restricted_custom_uri() {
		if (
			is_super_admin() ||
			wp_doing_ajax() ||
			( defined( 'REST_REQUEST' ) && REST_REQUEST )
		) {
			return;
		}
		$current_uri = $this->get_current_uri();

		if ( empty( $current_uri ) ) {
			return;
		}

		$access_rule_posts = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
				continue;
			}

			if ( ! is_array( $access_rule['logic_map'] ) ) {
				continue;
			}

			if ( empty( $access_rule['logic_map']['conditions'] ) ) {
				continue;
			}

			if ( ! urcr_is_access_rule_enabled( $access_rule ) || ! urcr_is_action_specified( $access_rule ) ) {
				continue;
			}

			$has_custom_uri = false;
			foreach ( $access_rule['target_contents'] as $target ) {
				if ( isset( $target['type'] ) && 'custom_uri' === $target['type'] ) {
					$has_custom_uri = true;
					break;
				}
			}

			if ( ! $has_custom_uri ) {
				continue;
			}

			$is_target = urcr_is_target_post( $access_rule['target_contents'], $current_uri );

			if ( true === $is_target ) {
				$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $current_uri );
				$access_control      = isset( $access_rule['actions'][0]['access_control'] ) && ! empty( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : 'access';
				$is_restricted       = ( true === $should_allow_access && 'restrict' === $access_control ) || ( false === $should_allow_access && 'access' === $access_control );

				if ( $is_restricted ) {
					$action  = $access_rule['actions'][0];
					$message = '';

					if ( isset( $action['type'] ) && 'message' === $action['type'] && ! empty( $action['message'] ) ) {
						$message = urldecode( $action['message'] );
						$message = apply_filters( 'user_registration_process_smart_tags', $message );
					}

					if ( empty( $message ) ) {
						$message = $this->message();
					}
					$this->render_restriction_html( $message );
					exit;
				}
			}
		}
	}

	/**
	 * @param string $item_output
	 * @param object $item
	 * @param int $depth
	 * @param stdClass $args
	 * @return string
	 */
	public function add_menu_item_data_attributes( $item_output, $item, $depth, $args ) {
		if ( isset( $item->urcr_data ) && ! empty( $item->urcr_data['restricted'] ) ) {
			$identifier = isset( $item->urcr_data['identifier'] ) ? $item->urcr_data['identifier'] : '';
			if ( ! empty( $identifier ) ) {
				$data_attrs = ' data-restricted="' . esc_attr( $identifier ) . '"';
				if ( strpos( $item_output, 'data-restricted' ) === false ) {
					$item_output = preg_replace( '/(<a[^>]*)(>)/i', '$1' . $data_attrs . '$2', $item_output );
				}
			}
		}
		return $item_output;
	}

	public function enqueue_frontend_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'urcr-pro-frontend',
			UR()->plugin_url() . '/assets/css/modules/content-restriction/urcr-pro-frontend.css',
			array(),
			constant( 'UR_VERSION' )
		);

		wp_enqueue_script(
			'urcr-pro-frontend',
			UR()->plugin_url() . '/assets/js/modules/content-restriction/frontend/urcr-pro-frontend' . $suffix . '.js',
			array( 'jquery' ),
			constant( 'UR_VERSION' ),
			true
		);

		wp_localize_script(
			'urcr-menu-restriction',
			'urcrMenuRestriction',
			array(
				'i18n' => array(
					'restrictedContent' => __( 'Restricted Content', 'user-registration' ),
					'close'             => __( 'Close', 'user-registration' ),
				),
			)
		);
	}

	public function add_content_restriction_dialog_html() {
		$content_restriction_enabled = ur_string_to_bool( get_option( 'user_registration_content_restriction_enable', true ) );
		if ( ! $content_restriction_enabled ) {
			return;
		}
		?>
<dialog id="URCR-Restriction-Modal" class="urcr-restriction-modal" aria-labelledby="URCR-Modal-Title"
	aria-describedby="URCR-Modal-Description">
	<div class="urcr-restriction-modal__wrapper" role="document">
		<div class="urcr-restriction-modal__body">
			<div id="URCR-Modal-Description" class="urcr-restriction-modal__description"></div>
		</div>
		<button type="button" class="urcr-restriction-modal__close"
			aria-label="<?php echo esc_attr__( 'Close', 'user-registration' ); ?>">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
</dialog>
<div id="URCR-Restriction-Messages" style="display: none;">
		<?php
		if ( ! empty( $this->restriction_messages ) ) {
			foreach ( $this->restriction_messages as $identifier => $message ) {
				$message_id = 'urcr-msg-' . esc_attr( $identifier );
				?>
	<div id="<?php echo esc_attr( $message_id ); ?>" class="urcr-restriction-message-content">
				<?php
						$login_page_id        = get_option( 'user_registration_login_page_id' );
						$registration_page_id = get_option( 'user_registration_member_registration_page_id' );
						$login_url            = $login_page_id ? get_permalink( $login_page_id ) : wp_login_url();
						$signup_url           = $registration_page_id ? get_permalink( $registration_page_id ) : ( $login_page_id ? get_permalink( $login_page_id ) : wp_registration_url() );

						urcr_get_template(
							'base-restriction-template.php',
							array(
								'message'    => $message,
								'login_url'  => $login_url,
								'signup_url' => $signup_url,
							)
						);
				?>
	</div>
				<?php
			}
		}
		?>
</div>
		<?php
	}
}

new URCR_Pro();
