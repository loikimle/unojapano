<?php
/**
 * UserRegistrationContentRestriction Frontend.
 *
 * @class    URCR_Frontend
 * @version  1.2.0
 * @package  UserRegistrationContentRestriction/Admin
 * @category Admin
 * @author   WPEverest
 */

defined( 'ABSPATH' ) || exit;

/**
 * URCR_Frontend Class
 */
class URCR_Frontend {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		if ( 'YES' !== urcr_is_compatible() ) {
			return;
		}

		add_action( 'template_redirect', array( $this, 'run_content_restrictions' ) );
		add_filter( 'template_include', array( $this, 'restrict_blog_page' ), PHP_INT_MAX );
		add_filter( 'template_include', array( $this, 'restrict_whole_site' ), PHP_INT_MAX );
		add_filter( 'template_include', array( $this, 'restrict_wc_shop_page' ), PHP_INT_MAX );
		add_filter( 'template_include', array( $this, 'restrict_wc_product_post' ), PHP_INT_MAX );
	}

	/**
	 * Access Rule for whole site restriction.
	 *
	 * @param mixed $template Template.
	 */
	public function restrict_whole_site( $template ) {
		if ( is_embed() ) {
            return $template;
        }
		$content_restriction_enabled = get_option( 'user_registration_content_restriction_enable', 'yes' );

		if ( 'yes' !== $content_restriction_enabled ) {
			return $template;
		}
		global $wp_query;
		$post                  = $wp_query->posts;
		$access_rule_posts      = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);
		$is_whole_site_restriction = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			// Verify if required params are available.
			if ( ! empty( $access_rule['target_contents'] ) ) {
				$types = wp_list_pluck( $access_rule['target_contents'], 'type' );
				if ( in_array( 'whole_site', $types, true ) ) {
					$is_whole_site_restriction = true;
				}
			}
		}

		if ( $is_whole_site_restriction ) {
			foreach ( $access_rule_posts as $access_rule_post ) {
				$access_rule = json_decode( $access_rule_post->post_content, true );

				// Verify if required params are available.
				if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
					continue;
				}
				// Check if the logic map data is in array format.
				if ( ! is_array( $access_rule['logic_map'] ) ) {
					continue;
				}
				// Validate against empty variables.
				if ( empty( $access_rule['logic_map']['conditions'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
					continue;
				}

				if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {

						$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $post );

						if ( false === $should_allow_access ) {
							do_action( 'urcr_pre_content_restriction_applied', $access_rule, $post );

							$template = urcr_get_template(
									'urcr-whole-site-template.php',
									array(
										'actions'     => $access_rule['actions'],
										'target_post' => $post,
									)
								);

							do_action( 'urcr_post_content_restriction_applied', $access_rule, $post );
							return $template;
						}
				}
			}
		}
		return $template;
	}

	/**
	 * Restrict Woocommerce Product Post_type
	 *
	 * @param mixed $template Template.
	 */
	public function restrict_wc_product_post( $template ) {
		if ( is_embed() ) {
            return $template;
        }
		$content_restriction_enabled = get_option( 'user_registration_content_restriction_enable', 'yes' );

		if ( 'yes' !== $content_restriction_enabled ) {
			return $template;
		}
		if ( function_exists( 'is_singular' ) && is_singular( 'product' ) ) {
			global $wp_query;
			$posts                  = $wp_query->posts;
			$template = $this->advanced_restriction_wc_with_access_rule( $template, $posts );
			return $template;
		}
        return $template;
	}

	/**
	 * Restrict Blog Page.
	 */
	public function restrict_blog_page( $template ) {

		if ( is_embed() ) {
            return $template;
        }
		$content_restriction_enabled = get_option( 'user_registration_content_restriction_enable', 'yes' );

		if ( 'yes' !== $content_restriction_enabled ) {
			return $template;
		}

		$page_for_posts_id = get_option( 'page_for_posts' );
		$blog_page = get_post( $page_for_posts_id );

		if ( empty( $blog_page ) ) {
			return $template;
		}

		$body_classes = get_body_class();
		// Check if "blog" class exists in the array
		if ( in_array( "blog", $body_classes, true ) ) {
			$template = $this->advanced_restriction_wc_with_access_rule( $template, $blog_page );
			return $template;
		}
        return $template;
	}

	/**
	 * Restrict Woocommerce Shop Page.
	 *
	 * @param mixed $template Template.
	 */
	public function restrict_wc_shop_page( $template ) {
		if ( is_embed() ) {
            return $template;
        }
		$content_restriction_enabled = get_option( 'user_registration_content_restriction_enable', 'yes' );

		if ( 'yes' !== $content_restriction_enabled ) {
			return $template;
		}
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return $template;
		}
		$shop_page_id = wc_get_page_id( 'shop' );
		$shop_page = get_post( $shop_page_id );

		if ( empty( $shop_page ) ) {
			return $template;
		}

		if ( ( is_post_type_archive( 'product' ) || is_page( $shop_page_id ) ) ) {
			$template = $this->advanced_restriction_wc_with_access_rule( $template, $shop_page );
			return $template;
		}
        return $template;
	}

	/**
	 * Access Rules for woocommerce content restriction.
	 *
	 * @param mixed $template Template.
	 * @param mixed $post Post Data.
	 */
	public function advanced_restriction_wc_with_access_rule( $template, $post ) {

		$access_rule_posts      = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);
		$is_whole_site_restriction = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			// Verify if required params are available.
			if ( ! empty( $access_rule['target_contents'] ) ) {
				$types = wp_list_pluck( $access_rule['target_contents'], 'type' );
				if ( in_array( 'whole_site', $types, true ) ) {
					$is_whole_site_restriction = true;
				}
			}
		}

		if ( ! $is_whole_site_restriction ) {
			foreach ( $access_rule_posts as $access_rule_post ) {
				$access_rule = json_decode( $access_rule_post->post_content, true );

				// Verify if required params are available.
				if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
					continue;
				}
				// Check if the logic map data is in array format.
				if ( ! is_array( $access_rule['logic_map'] ) ) {
					continue;
				}
				// Validate against empty variables.
				if ( empty( $access_rule['logic_map']['conditions'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
					continue;
				}

				if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {
					$is_target = urcr_is_target_post( $access_rule['target_contents'], $post );

					if ( true === $is_target ) {
						$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $post );

						if ( false === $should_allow_access ) {
							do_action( 'urcr_pre_content_restriction_applied', $access_rule, $post );

							$template = urcr_get_template(
									'urcr-target-access-template.php',
									array(
										'actions'     => $access_rule['actions'],
										'target_post' => $post,
									)
								);

							do_action( 'urcr_post_content_restriction_applied', $access_rule, $post );
							return $template;
						}
					}
				}
			}
		}
		return $template;
	}

	/**
	 * Perform content restriction task.
	 */
	public function run_content_restrictions() {
		$content_restriction_enabled = get_option( 'user_registration_content_restriction_enable', 'yes' );

		if ( 'yes' !== $content_restriction_enabled ) {
			return;
		}

		$restriction_applied = $this->advanced_restriction_with_access_rules();

		if ( false === $restriction_applied ) {
			$this->basic_restrictions();
		}
	}

	/**
	 * Restrict contents with Access Rules.
	 */
	public function advanced_restriction_with_access_rules() {
		global $wp_query;
		$access_rule_posts      = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type'   => 'urcr_access_rule',
			)
		);
		$posts                  = $wp_query->posts;
		$posts_length           = count( $wp_query->posts );
		$is_restriction_applied = false;

		foreach ( $access_rule_posts as $access_rule_post ) {
			$access_rule = json_decode( $access_rule_post->post_content, true );

			// Verify if required params are available.
			if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['target_contents'] ) || empty( $access_rule['actions'] ) ) {
				continue;
			}
			// Check if the logic map data is in array format.
			if ( ! is_array( $access_rule['logic_map'] ) ) {
				continue;
			}
			// Validate against empty variables.
			if ( empty( $access_rule['logic_map']['conditions'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
				continue;
			}

			if ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {
				for ( $i = 0; $i < $posts_length; $i++ ) {
					$post      = $posts[ $i ];
					$is_target = urcr_is_target_post( $access_rule['target_contents'], $post );

					if ( true === $is_target ) {
						$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'], $post );

						if ( false === $should_allow_access ) {
							do_action( 'urcr_pre_content_restriction_applied', $access_rule, $post );

							$is_applied = urcr_apply_content_restriction( $access_rule['actions'], $post );

							// In case there are multiple posts and 'true' occurred at least once, never change it to false.
							$is_restriction_applied = $posts_length > 1 && $is_restriction_applied ? true : $is_applied;

							do_action( 'urcr_post_content_restriction_applied', $access_rule, $post );
						}
					}
				}
			}
		}
		return $is_restriction_applied;
	}

	/**
	 * Perform content restriction task.
	 */
	public function basic_restrictions() {
		global $post;
		$post_id = isset( $post->ID ) ? absint( $post->ID ) : 0;

		// Check shop page and get it's page id.
		if ( function_exists( 'is_shop' ) ) {
			$post_id = is_shop() ? wc_get_page_id( 'shop' ) : $post_id;
		}

		$allowed_roles = get_option( 'user_registration_content_restriction_allow_to_roles', 'administrator' );

		$current_user_role = is_user_logged_in() ? wp_get_current_user()->roles[0] : '';

		$get_meta_data_roles = get_post_meta( $post_id, 'urcr_meta_roles', $single = true );

		$get_meta_data_allow_to = get_post_meta( $post_id, 'urcr_allow_to', $single = true );

		$get_meta_data_checkbox = get_post_meta( $post_id, 'urcr_meta_checkbox', $single = true );

		$override_global_settings = get_post_meta( $post_id, 'urcr_meta_override_global_settings', $single = true );

		if ( $get_meta_data_checkbox == 'on' ) {

			if ( $override_global_settings !== 'on' ) {
				if ( '0' == get_option( 'user_registration_content_restriction_allow_access_to', '0' ) ) {
					if ( ! is_user_logged_in() ) {
						$this->urcr_restrict_contents();
					}
					return $post;
				} elseif ( '1' == get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
					if ( is_array( $allowed_roles ) && in_array( $current_user_role, $allowed_roles ) ) {
						return;
					}
					$this->urcr_restrict_contents();
				} elseif ( '2' === get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
					if ( is_user_logged_in() ) {
						$this->urcr_restrict_contents();
					}
					return $post;
				}
			} else {
				if ( $get_meta_data_allow_to == '0' ) {
					if ( ! is_user_logged_in() ) {
						$this->urcr_restrict_contents();
					}
					return $post;
				} elseif ( $get_meta_data_allow_to == '1' ) {
					if ( isset( $get_meta_data_roles ) && ! empty( $get_meta_data_roles ) ) {
						if ( is_array( $get_meta_data_roles ) && in_array( $current_user_role, $get_meta_data_roles ) ) {
							return;
						}
						$this->urcr_restrict_contents();
					}
				} elseif ( $get_meta_data_allow_to === '2' ) {
					if ( is_user_logged_in() ) {
						$this->urcr_restrict_contents();
					}

					return $post;
				}
			}
		}
	}

	public function urcr_restrict_contents() {

		global $post;

		// Check if this is a product page.
		if ( get_post_type() == 'product' ) {
			$this->restrict_products();
		}

		// Display restriction message instead of post content.
		$post->post_content = $this->message();

		// Add filter for elementor content.
		add_filter( 'elementor/frontend/the_content', array( $this, 'elementor_restrict' ) );

		$get_site_origin_data = get_post_meta( $post->ID, 'panels_data' );

		$get_beaver_data = get_post_meta( $post->ID, '_fl_builder_data' );

		if ( isset( $get_site_origin_data ) && ! empty( $get_site_origin_data ) ) {
			update_post_meta( $post->ID, 'panels_data', '' );
		}

		if ( isset( $get_beaver_data ) && ! empty( $get_beaver_data ) ) {
			remove_filter( 'the_content', 'FLBuilder::render_content' );
		}
	}

	/**
	 * Get content restriction message.
	 *
	 * @return string Content restriction message.
	 */
	public function message() {
		$message = get_option( 'user_registration_content_restriction_message' );

		$message = ( false === $message ) ? esc_html__( 'This content is restricted!', 'user-registration-content-restriction' ) : $message;

		return '<span class="urcr-restrict-msg">' . $message . '</span>';
	}

	/**
	 * Add and remove actions for WooCommerce pages and posts.
	 *
	 * @return void
	 */
	public function restrict_products() {

		// Add restritction notice on products page.
		add_action( 'woocommerce_after_single_product', array( $this, 'products_restriction_message' ), 10, 1 );

		// Remove all actions before shop contents.
		remove_all_actions( 'woocommerce_archive_description' );
		remove_all_actions( 'woocommerce_before_shop_loop' );
		remove_all_actions( 'woocommerce_before_shop_loop_item_title' );
		remove_all_actions( 'woocommerce_before_shop_loop_item' );

		// Add restriction notice on shop page.
		add_action( 'woocommerce_before_shop_loop', array( $this, 'products_restriction_message' ), 10, 1 );

		// Remove all actions after shop contents.
		remove_all_actions( 'woocommerce_shop_loop_item_title' );
		remove_all_actions( 'woocommerce_after_shop_loop_item_title' );
		remove_all_actions( 'woocommerce_after_shop_loop_item' );

		// Remove all
		remove_all_actions( 'woocommerce_before_single_product_summary' );
		remove_all_actions( 'woocommerce_single_product_summary' );
		remove_all_actions( 'woocommerce_after_single_product_summary' );
	}

	public function products_restriction_message() {
		echo $this->message();
	}

	/**
	 * Display restriction message for elementor content.
	 *
	 * @param  $content actual content
	 * @return string restricted content
	 */
	public function elementor_restrict( $content ) {
		return $this->message();
	}
}

return new URCR_Frontend();
