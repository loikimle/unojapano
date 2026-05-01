<?php

/**
 * Initialization Action.
 *
 * @package ULTP\ULTP_Initialization
 * @since v.1.1.0
 */

namespace ULTP;

defined( 'ABSPATH' ) || exit;

use ULTP\Includes\Durbin\Xpo;
use ULTP\Includes\Notice\Notice;

/**
 * Initialization class.
 *
 * Handles plugin initialization for Ultimate Post.
 *
 * @package ULTP\Initialization
 * @since 4.0.0
 */
class ULTP_Initialization {

	/**
	 * Setup class.
	 *
	 * @since 4.0.0
	 * @return void No return value.
	 */
	public function __construct() {

		$this->compatibility_check();
		$this->requires();
		$this->include_addons();

		add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ) );  // add body class in editor.
		add_filter( 'body_class', array( $this, 'add_body_class' ) );  // add body class in front end.

		add_action( 'wp', array( $this, 'popular_posts_tracker_callback' ) );
		add_action( 'after_setup_theme', array( $this, 'add_image_size' ) );
		add_filter( 'block_categories_all', array( $this, 'register_category_callback' ), 999999999, 2 );  // Block Category Register.

		add_filter( 'safe_style_css', array( $this, 'ultp_handle_safe_style_css' ) );  // support for css used in svg icon.
		add_filter( 'wp_kses_allowed_html', array( $this, 'ultp_handle_allowed_html' ), 99, 2 );   // support for svg icon used in list, row, icon block.

		add_action( 'enqueue_block_editor_assets', array( $this, 'register_block_scripts_editor_area' ) );    // Only editor.
		add_action( 'admin_enqueue_scripts', array( $this, 'register_option_panel_scripts_callback' ) );    // Option Panel.
		register_activation_hook( ULTP_PATH . 'ultimate-post.php', array( $this, 'plugin_activation_hook' ) );
		add_action( 'activated_plugin', array( $this, 'ultp_plugin_activation' ) ); // Plugin Activation Call.
		// new Notice();
	}

	/**
	 * Check plugin compatibility.
	 *
	 * @since v.1.0.0
	 * @return void
	 */
	public function compatibility_check() {
		require_once ULTP_PATH . 'classes/Compatibility.php';
		new \ULTP\Compatibility();
	}

	/**
	 * Load all required classes.
	 *
	 * @since v.1.0.0
	 * @return void No return value.
	 */
	public function requires() {
		require_once ULTP_PATH . 'classes/Styles.php';
		require_once ULTP_PATH . 'classes/Options.php';
		require_once ULTP_PATH . 'classes/REST_API.php';
		require_once ULTP_PATH . 'classes/Caches.php';
		require_once ULTP_PATH . 'classes/Importer.php';
		require_once ULTP_PATH . 'classes/Dashboard.php';
		require_once ULTP_PATH . 'classes/Blocks.php';
		new \ULTP\REST_API();
		new \ULTP\Options();
		new \ULTP\Caches();
		new \ULTP\Styles();
		new \ULTP\Importer();
		new \ULTP\Dashboard();
		new \ULTP\Blocks();

		require_once ULTP_PATH . 'includes/durbin/class-durbin-client.php';
		require_once ULTP_PATH . 'includes/durbin/class-xpo.php';
		require_once ULTP_PATH . 'includes/deactive/class-deactive.php';
		require_once ULTP_PATH . 'includes/notice/class-notice.php';
		require_once ULTP_PATH . 'includes/durbin/class-our-plugins.php';
		require_once ULTP_PATH . 'includes/notice/class-wow-shipping-promotion.php';
		new \ULTP\Includes\Deactive\Deactive();
		new \ULTP\Includes\notice\Notice();
		new \ULTP\Includes\notice\WowShippingPromotion();
		new \ULTP\Includes\Durbin\OurPlugins();
	}

	/**
	 * Include all addons from the addons directory.
	 *
	 * @since v.1.0.0
	 * @return void
	 */
	public function include_addons() {
		$addons_dir = array_filter( glob( ULTP_PATH . 'addons/*' ), 'is_dir' );
		if ( count( $addons_dir ) > 0 ) {
			foreach ( $addons_dir as $key => $value ) {
				$addon_dir_name = str_replace( dirname( $value ) . '/', '', $value );
				$file_name      = ULTP_PATH . 'addons/' . $addon_dir_name . '/init.php';
				if ( file_exists( $file_name ) ) {
					include_once $file_name;
				}
			}
		}
	}


	/**
	 * Add admin body class in editor.
	 *
	 * @since v.3.1.6
	 * @param STRING $classes The existing admin body classes.
	 * @return STRING Modified admin body classes.
	 */
	public function add_admin_body_class( $classes ) {
		$classes .= ' postx-admin-page ';
		return $classes;
	}

	/**
	 * Add body class in front end.
	 *
	 * @since v.3.1.6
	 * @param ARRAY $classes The existing body classes.
	 * @return ARRAY Modified body classes.
	 */
	public function add_body_class( $classes ) {
		$classes[] = 'postx-page';
		return $classes;
	}

	/**
	 * Post view counter for every post.
	 *
	 * @since v.1.0.0
	 * @param INT $post_id The post ID.
	 * @return NULL No return value.
	 */
	public function popular_posts_tracker_callback( $post_id ) {
		if ( ! is_single() ) {
			return;
		}
		global $post;
		$post_id  = isset( $post->ID ) ? $post->ID : '';
		$isEnable = apply_filters( 'ultp_view_cookies', true );
		// add_filter( 'ultp_view_cookies', '__return_false' );
		$cookies_disable = ultimate_post()->get_setting( 'disable_view_cookies' );
		if ( $post_id && $isEnable && $cookies_disable != 'yes' ) {
			$has_cookie = isset( $_COOKIE[ 'ultp_view_' . $post_id ] ) ? sanitize_text_field( $_COOKIE[ 'ultp_view_' . $post_id ] ) : false;
			if ( ! $has_cookie ) {
				$count = (int) get_post_meta( $post_id, '__post_views_count', true );
				update_post_meta( $post_id, '__post_views_count', $count ? (int) $count + 1 : 1 );
				setcookie( 'ultp_view_' . $post_id, 1, time() + 86400, COOKIEPATH ); // 1 days cookies
			}
		}
	}

	/**
	 * Set image sizes for the plugin.
	 *
	 * @since v.1.0.0
	 * @return void No return value.
	 */
	public function add_image_size() {
		$size_disable = ultimate_post()->get_setting( 'disable_image_size' );
		if ( $size_disable != 'yes' ) {
			add_image_size( 'ultp_layout_landscape_large', 1200, 800, true );
			add_image_size( 'ultp_layout_landscape', 870, 570, true );
			add_image_size( 'ultp_layout_portrait', 600, 900, true );
			add_image_size( 'ultp_layout_square', 600, 600, true );
		}
	}

	/**
	 * Block categories initialization.
	 *
	 * @since v.1.0.0
	 * @param ARRAY $categories The block categories.
	 * @param ARRAY $post The post object.
	 * @return ARRAY Modified block categories.
	 */
	public function register_category_callback( $categories, $post ) {
		$attr = array(
			array(
				'slug'  => 'ultimate-post',
				'title' => __( 'PostX - Gutenberg Post Blocks', 'ultimate-post' ),
			),
			array(
				'slug'  => 'postx-site-builder',
				'title' => __( 'PostX Site Builder', 'ultimate-post' ),
			),
		);
		return array_merge( $attr, $categories );
	}

	/**
	 * Add support for CSS to use SVG.
	 *
	 * @since 4.0.0
	 * @param ARRAY $styles The allowed CSS styles.
	 * @return ARRAY Modified allowed CSS styles.
	 */
	public function ultp_handle_safe_style_css( $styles ) {
		if ( ! is_multisite() && ! current_user_can( 'edit_posts' ) ) {
			return $styles;
		}
		return array_merge(
			$styles,
			array(
				'opacity',
				// for SVG gradients.
				// 'stop-opacity',
				// 'stop-color',
			)
		);
	}

	/**
	 * Add support for HTML tags to use SVG.
	 *
	 * @since 4.0.0
	 * @param ARRAY  $tags The allowed HTML tags.
	 * @param STRING $context The context for allowed HTML.
	 * @return ARRAY Modified allowed HTML tags.
	 */
	public function ultp_handle_allowed_html( $tags, $context ) {
		if ( 'post' !== $context && ! is_multisite() && ! current_user_can( 'edit_posts' ) ) {
			return $tags;
		}
		if ( ! isset( $tags['svg'] ) ) {
			$tags['svg'] = array_merge(
				array(
					'xmlns'               => true,
					// 'xmlns:xlink'   => true,
					// 'xlink:href'     => true,
					// 'xml:id'     => true,
					// 'xlink:title'    => true,
					// 'xml:space'  => true,
					'viewbox'             => true,
					'enable-background'   => true,
					'version'             => true,
					'preserveaspectratio' => true,
					'fill'                => true,
				)
			);
		}
		if ( ! isset( $tags['path'] ) || ! is_array( $tags['path'] ) ) {
			$tags['path'] = array();
		}

		$tags['path'] = array_merge(
			$tags['path'],
			array(
				'd'                 => true,
				'stroke'            => true,
				'stroke-miterlimit' => true,
				'data-original'     => true,
				'class'             => true,
				'transform'         => true,
				'style'             => true,
				'opacity'           => true,
				'fill'              => true,
				'stroke-width'      => true,
				'stroke-linecap'    => true,
				'stroke-linejoin'   => true,
			)
		);

		if ( ! isset( $tags['g'] ) ) {
			$tags['g'] = array(
				'transform' => true,
				'clip-path' => true,
			);
		}
		if ( ! isset( $tags['clippath'] ) ) {
			$tags['clippath'] = array();
		}
		if ( ! isset( $tags['defs'] ) ) {
			$tags['defs'] = array();
		}
		if ( ! isset( $tags['rect'] ) ) {
			$tags['rect'] = array(
				'rx'        => true,
				'height'    => true,
				'width'     => true,
				'transform' => true,
				'x'         => true,
				'fill'      => true,
			);
		}
		if ( ! isset( $tags['circle'] ) ) {
			$tags['circle'] = array(
				'cx'        => true,
				'cy'        => true,
				'transform' => true,
				'r'         => true,
			);
		}
		if ( ! isset( $tags['polygon'] ) ) {
			$tags['polygon'] = array(
				'points' => true,
			);
		}
		if ( ! isset( $tags['lineargradient'] ) ) {
			$tags['lineargradient'] = array(
				'gradienttransform' => true,
				'id'                => true,
			);
		}
		if ( ! isset( $tags['stop'] ) ) {
			$tags['stop'] = array(
				'offset'       => true,
				'stop-color'   => true,
				'style'        => true,
				'stop-opacity' => true,
			);
		}
		return $tags;
	}

	/**
	 * Plugin activation callback.
	 *
	 * @since v.1.1.0
	 * @param STRING $plugin The plugin file path.
	 * @return NULL No return value.
	 */
	public function ultp_plugin_activation( $plugin ) {
		if ( wp_doing_ajax() ) {
			return;
		}
		if ( $plugin == 'ultimate-post/ultimate-post.php' ) {
			if ( wp_doing_ajax() || is_network_admin() || isset( $_GET['activate-multi'] ) || isset( $_POST['action'] ) && 'activate-selected' == $_POST['action'] ) {
				return;
			}
			if ( ultimate_post()->get_setting( 'init_setup' ) != 'yes' ) {
				ultimate_post()->set_setting( 'init_setup', 'yes' );
				exit(wp_safe_redirect(admin_url('admin.php?page=ultp-setup-wizard'))); //phpcs:ignore
			} else {
				exit(wp_safe_redirect(admin_url('admin.php?page=ultp-settings#home'))); //phpcs:ignore
			}
		}
	}

	/**
	 * Enqueue option panel CSS and JS scripts.
	 *
	 * @since v.1.0.0
	 * @return void No return value.
	 */
	public function register_option_panel_scripts_callback() {
		$is_active   = ultimate_post()->is_lc_active();
		$license_key = Xpo::get_lc_key();
		$_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';    // @codingStandardsIgnoreLine
		$post_type   = get_post_type();

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_script( 'ultp-option-script', ULTP_URL . 'assets/js/ultp-option.js', array( 'jquery' ), ULTP_VER, true );
		wp_enqueue_style( 'ultp-option-style', ULTP_URL . 'assets/css/ultp-option.css', array(), ULTP_VER );
		wp_localize_script(
			'ultp-option-script',
			'ultp_option_panel',
			array(
				'url'                => ULTP_URL,
				'version'            => ULTP_VER,
				'active'             => $is_active,
				'security'           => wp_create_nonce( 'ultp-nonce' ),
				'ajax'               => admin_url( 'admin-ajax.php' ),
				'settings'           => ultimate_post()->get_setting(),
				'post_type'          => $post_type,
				'saved_template_url' => admin_url( 'admin.php?page=ultp-settings#saved-templates' ),
			)
		);

		if ( $post_type == 'ultp_custom_font' ) {
			$font_settings = ultimate_post()->get_setting( 'ultp_custom_font' );
			if ( $font_settings == 'true' ) {
				wp_enqueue_media();
			}
		}

		/* === Installation Wizard === */
		if ( $_page == 'ultp-setup-wizard' ) {
			wp_enqueue_script( 'ultp-initial-setup-script', ULTP_URL . 'assets/js/ultp_initial_setup_min.js', array( 'wp-i18n', 'wp-api-fetch', 'wp-api-request' ), ULTP_VER, true );
			wp_set_script_translations( 'ultp-initial-setup-script', 'ultimate-post', ULTP_PATH . 'languages/' );
		}

		/* === Builder And Setting Pannel === */
		if ( get_post_type( get_the_ID() ) == 'ultp_builder' ) {
			wp_enqueue_script( 'ultp-conditions-script', ULTP_URL . 'addons/builder/assets/js/conditions.js', array( 'wp-i18n', 'wp-api-fetch', 'wp-components', 'wp-i18n', 'wp-blocks' ), ULTP_VER, true );
			wp_localize_script(
				'ultp-conditions-script',
				'ultp_condition',
				array(
					'url'         => ULTP_URL,
					'active'      => $is_active,
					'builder_url' => admin_url( 'admin.php?page=ultp-settings#builder' ),
				)
			);
			wp_set_script_translations( 'ultp-conditions-script', 'ultimate-post', ULTP_PATH . 'languages/' );
		}

		/* === Dashboard === */
		if ( $_page == 'ultp-settings' ) {
			wp_enqueue_script( 'ultp-dashboard-script', ULTP_URL . 'assets/js/ultp_dashboard_min.js', array( 'wp-i18n', 'wp-api-fetch', 'wp-api-request', 'wp-components', 'wp-blocks' ), ULTP_VER, true );
			$user_info = get_userdata( get_current_user_id() );
			wp_localize_script(
				'ultp-dashboard-script',
				'ultp_dashboard_pannel',
				array_merge(
					array(
						'ajax'              => admin_url( 'admin-ajax.php' ),
						'security'          => wp_create_nonce( 'ultp-nonce' ),
						'url'               => ULTP_URL,
						'active'            => $is_active,
						'license'           => $license_key,
						'settings'          => ultimate_post()->get_setting(),
						'addons_settings'   => apply_filters( 'ultp_settings', array() ),
						'builder_url'       => admin_url( 'admin.php?page=ultp-settings#builder' ),
						'version'           => ULTP_VER,
						'setup_wizard_link' => admin_url( 'admin.php?page=ultp-setup-wizard' ),
						'is_free'           => ! $is_active,
						'user_email'        => wp_get_current_user()->user_email,
						'home_url'          => home_url(),
						'generalDiscount'   => get_transient( 'ultp_generalDiscount' ),
						'helloBar'          => Notice::get_hellobar_config(),
						'userInfo'          => array(
							'name'  => $user_info->first_name ? $user_info->first_name . ( $user_info->last_name ? ' ' . $user_info->last_name : '' ) : $user_info->user_login,
							'email' => $user_info->user_email,
						),
					),
					Xpo::get_wow_products_details()
				)
			);
			wp_set_script_translations( 'ultp-dashboard-script', 'ultimate-post', ULTP_PATH . 'languages/' );
		}
	}

	/**
	 * Enqueue backend CSS and JS scripts for the block editor.
	 *
	 * @since v.1.0.0
	 * @return void No return value.
	 */
	public function register_block_scripts_editor_area() {
		ultimate_post()->register_scripts_common();
		global $pagenow;
		$depends = 'wp-editor';
		if ( $pagenow === 'widgets.php' ) {
			$depends = 'wp-edit-widgets';
		}
		wp_enqueue_script( 'ultp-blocks-editor-script', ULTP_URL . 'assets/js/editor.blocks.js', array( 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', $depends ), ULTP_VER, true );
		wp_enqueue_style( 'ultp-blocks-editor-css', ULTP_URL . 'assets/css/blocks.editor.css', array(), ULTP_VER );
		if ( is_rtl() ) {
			wp_enqueue_style( 'ultp-blocks-editor-rtl-css', ULTP_URL . 'assets/css/rtl.css', array(), ULTP_VER );
		}
		$is_active = ultimate_post()->is_lc_active();
		$post_type = get_post_type();

		// Custom Font Support Added
		$font_settings = ultimate_post()->get_setting( 'ultp_custom_font' );
		$custom_fonts  = array();
		if ( $font_settings == 'true' ) {
			$args  = array(
				'post_type'   => 'ultp_custom_font',
				'post_status' => 'publish',
				'numberposts' => -1,
				'order'       => 'ASC',
			);
			$posts = get_posts( $args );
			if ( $posts ) {
				foreach ( $posts as $post ) {
					setup_postdata( $post );
					$font = get_post_meta( $post->ID, '__font_settings', true );

					if ( $font ) {
						array_push(
							$custom_fonts,
							array(
								'title' => $post->post_title,
								'font'  => $font,
							)
						);
					}
				}
				wp_reset_postdata();
			}
		}

		wp_localize_script(
			'ultp-blocks-editor-script',
			'ultp_data',
			array(
				'url'                => ULTP_URL,
				'ajax'               => admin_url( 'admin-ajax.php' ),
				'security'           => wp_create_nonce( 'ultp-nonce' ),
				'hide_import_btn'    => ultimate_post()->get_setting( 'hide_import_btn' ),
				'premium_link'       => Xpo::generate_utm_link(),
				'license'            => $is_active ? Xpo::get_lc_key() : '',
				'active'             => $is_active,
				'archive'            => ultimate_post()->is_archive_builder(),
				'settings'           => ultimate_post()->get_setting(),
				'post_type'          => $post_type == 'premade' ? 'ultp_builder' : $post_type, // premade used for ultp.wpxpo.com
				'date_format'        => get_option( 'date_format' ),
				'time_format'        => get_option( 'time_format' ),
				'blog'               => get_current_blog_id(),
				'affiliate_id'       => apply_filters( 'ultp_affiliate_id', false ),
				'category_url'       => admin_url( 'edit-tags.php?taxonomy=category' ),
				'disable_image_size' => ultimate_post()->get_setting( 'disable_image_size' ),
				'dark_logo'          => get_option( 'ultp_site_dark_logo' ) ? get_option( 'ultp_site_dark_logo' ) : false,
				'builder_url'        => admin_url( 'admin.php?page=ultp-settings#builder' ),
				'custom_fonts'       => $custom_fonts,
			)
		);
		wp_set_script_translations( 'ultp-blocks-editor-script', 'ultimate-post', ULTP_PATH . 'languages/' );
	}


	/**
	 * Fire when plugin is first installed.
	 *
	 * @since v.1.0.0
	 * @return void No return value.
	 */
	public function plugin_activation_hook() {
		$data        = get_option( 'ultp_options', array() );
		$currentDate = new \DateTime();
		$currentDate->setTime( 0, 0, 0, 0 );
		$init_data = array(
			'preloader_style'             => 'style1',
			'preloader_color'             => '#037fff',
			'container_width'             => '1140',
			'hide_import_btn'             => '',
			'disable_image_size'          => '',
			'disable_view_cookies'        => '',
			'disable_google_font'         => '',
			'ultp_templates'              => 'true',
			'ultp_elementor'              => 'true',
			'ultp_table_of_content'       => 'true',
			'ultp_builder'                => 'true',
			'ultp_dynamic_content'        => 'true',
			'ultp_custom_font'            => 'true',
			'ultp_chatgpt'                => 'true',
			'post_grid_1'                 => 'yes',
			'post_grid_2'                 => 'yes',
			'post_grid_3'                 => 'yes',
			'post_grid_4'                 => 'yes',
			'post_grid_5'                 => 'yes',
			'post_grid_6'                 => 'yes',
			'post_grid_7'                 => 'yes',
			'post_list_1'                 => 'yes',
			'post_list_2'                 => 'yes',
			'post_list_3'                 => 'yes',
			'post_list_4'                 => 'yes',
			'post_module_1'               => 'yes',
			'post_module_2'               => 'yes',
			'post_slider_1'               => 'yes',
			'post_slider_2'               => 'yes',
			'heading'                     => 'yes',
			'image'                       => 'yes',
			'taxonomy'                    => 'yes',
			'wrapper'                     => 'yes',
			'news_ticker'                 => 'yes',
			'accordion'                   => 'yes',
			'star_rating'                 => 'yes',
			'youtube_gallery'             => 'yes',
			'builder_advance_post_meta'   => 'yes',
			'builder_archive_title'       => 'yes',
			'builder_author_box'          => 'yes',
			'builder_post_next_previous'  => 'yes',
			'builder_post_author_meta'    => 'yes',
			'builder_post_breadcrumb'     => 'yes',
			'builder_post_category'       => 'yes',
			'builder_post_comment_count'  => 'yes',
			'builder_post_comments'       => 'yes',
			'builder_post_content'        => 'yes',
			'builder_post_date_meta'      => 'yes',
			'builder_post_excerpt'        => 'yes',
			'builder_post_featured_image' => 'yes',
			'builder_post_reading_time'   => 'yes',
			'builder_post_social_share'   => 'yes',
			'builder_post_tag'            => 'yes',
			'builder_post_title'          => 'yes',
			'builder_post_view_count'     => 'yes',
			'save_version'                => wp_rand( 1, 1000 ),
			'activated_date'              => $currentDate->getTimestamp(),
		);
		if ( empty( $data ) ) {
			update_option( 'ultp_options', $init_data );
			$GLOBALS['ultp_settings'] = $init_data;
		} else {
			foreach ( $init_data as $key => $single ) {
				if ( ! isset( $data[ $key ] ) ) {
					$data[ $key ] = $single;
				}
			}
			update_option( 'ultp_options', $data );
			$GLOBALS['ultp_settings'] = $data;
		}
		if ( ! get_transient( 'wpxpo_installation_date' ) ) {
			set_transient( 'wpxpo_installation_date', 'yes', 5 * DAY_IN_SECONDS ); // 5 Days Notice
		}
	}
}
