<?php
/**
 * URFrontendListing Admin.
 *
 * @class    Admin
 * @version  1.0.0
 * @package  URFrontendListing/Admin
 * @package Admin
 */

namespace WPEverest\URFrontendListing\Admin;

use WPEverest\URFrontendListing\Admin\ListTable;
use WPEverest\URFrontendListing\Admin\Settings\Metabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class
 */
class Admin {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_init', array( $this, 'actions' ) );
		add_filter( 'user_registration_screen_ids', array( $this, 'ur_frontend_listing_add_screen_id' ) );
		add_filter( 'user_row_actions', array( $this, 'create_quick_links' ), 10, 2 );

		// Add admin settings.
		add_filter( 'user_registration_general_settings', array( $this, 'ur_frontend_list_add_general_settings' ) );

		// Integrate member directories.
		add_filter( 'urcr_content_type_options', array( $this, 'add_directory_content_type_option' ) );
		add_filter( 'urcr_type_labels', array( $this, 'add_directory_type_label' ) );
		add_filter( 'urcr_localized_data', array( $this, 'add_directory_localized_data' ) );

		// Fix menu highlighting for frontend listing edit pages.
		add_action( 'admin_head', array( $this, 'fix_menu_highlighting' ) );

		add_action( 'wp_ajax_urfl_create_listing', array( $this, 'ajax_create_listing' ) );
		add_action( 'wp_ajax_urfl_update_title', array( $this, 'ajax_update_title' ) );

		new Metabox();
	}

	/**
	 * Add Directory content type option for content access rules.
	 *
	 * @param array $options Existing content type options.
	 *
	 * @return array
	 */
	public function add_directory_content_type_option( $options ) {

		$existing_values = wp_list_pluck( (array) $options, 'value' );

		if ( in_array( 'directory', $existing_values, true ) ) {
			return $options;
		}

		$options[] = array(
			'value' => 'directory',
			'label' => __( 'Members Directory', 'user-registration-frontend-listing' ),
		);

		return $options;
	}

	/**
	 * Add Directory label for type labels used in various UIs (e.g. membership access tab).
	 *
	 * @param array $labels Existing type labels.
	 *
	 * @return array
	 */
	public function add_directory_type_label( $labels ) {
		$labels['directory'] = __( 'Members Directory', 'user-registration-frontend-listing' );

		return $labels;
	}

	/**
	 * Inject member directory list into urcr_localized_data so React multiselect can show options.
	 *
	 * @param array $data Localized data array.
	 *
	 * @return array
	 */
	public function add_directory_localized_data( $data ) {
		$directories = get_posts(
			array(
				'post_type'      => 'ur_frontend_listings',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'fields'         => 'ids',
			)
		);

		$directory_options = array();

		if ( ! empty( $directories ) ) {
			foreach ( $directories as $directory_id ) {
				$title = get_the_title( $directory_id );
				$directory_options[ $directory_id ] = $title ? $title : sprintf(
					/* translators: %d: Numeric directory post ID when the directory has no title. */
					__( 'Members Directory #%d', 'user-registration-frontend-listing' ),
					$directory_id
				);
			}
		}

		$data['directory'] = $directory_options;

		return $data;
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( ! $screen || 'user-registration-membership_page_user-registration-frontend-list' !== $screen->id ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
		wp_enqueue_style(
			'urfl-modal',
			UR_ASSET_PATH . 'css/user-registration-member-directory-modal.css',
			array(),
			UR_VERSION
		);

		wp_enqueue_script(
			'urfl-modal',
			UR_ASSET_PATH . 'js/pro/admin/ur-member-modal' . $suffix . '.js',
			array( 'jquery' ),
			UR_VERSION,
			true
		);

		wp_localize_script(
			'urfl-modal',
			'urfl_modal',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'urfl_create_listing' ),
				'i18n'     => array(
					'title_required' => __( 'Please enter a listing name', 'user-registration-frontend-listing' ),
					'creating'       => __( 'Creating...', 'user-registration-frontend-listing' ),
					'create_error'   => __( 'Error creating listing. Please try again.', 'user-registration-frontend-listing' ),
				),
			)
		);
	}

	public function enqueue_styles() {
	}

	/**
	 * Add  menu item.
	 */
	public function user_list_menu() {
		$template_page = add_submenu_page(
			'user-registration',
			__( 'User Registration Member Directory', 'user-registration-frontend-listing' ),
			__( 'Members Directory', 'user-registration-frontend-listing' ),
			'manage_user_registration',
			'user-registration-frontend-list',
			array(
				$this,
				'user_registration_frontend_listing_page',
			)
		);

		add_action( 'load-' . $template_page, array( $this, 'user_registration_frontend_listing_page_init' ) );
	}

	/**
	 * Loads Frontend listing table page.
	 */
	public function user_registration_frontend_listing_page_init() {
		global $ur_frontend_listing_table_list;

		$ur_frontend_listing_table_list = new ListTable();
		$ur_frontend_listing_table_list->process_actions();

		// Add screen option.
		add_screen_option(
			'per_page',
			array(
				'default' => 20,
				'option'  => 'user_registration_frontend_listing_per_page',
			)
		);

		do_action( 'user_registration_frontend_listing_page_init' );
	}

	/**
	 *  Init the  Frontend listing table page.
	 */
	public function user_registration_frontend_listing_page() {
		global $ur_frontend_listing_table_list;

		if ( ! $ur_frontend_listing_table_list ) {
			return;
		}
		$ur_frontend_listing_table_list->display_page();
	}

	/**
	 * Fix menu highlighting for frontend listing edit and add new pages.
	 */
	public function fix_menu_highlighting() {
		global $pagenow, $post_type, $submenu_file, $parent_file;
		if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && 'ur_frontend_listings' === $post_type ) {
			$parent_file  = 'user-registration';
			$submenu_file = 'user-registration-frontend-list';
		}
	}

	/**
	 * Frontend Listing admin actions.
	 */
	public function actions() {

		if ( isset( $_GET['page'] ) && 'user-registration-frontend-list' === $_GET['page'] ) {

			// Bulk actions.
			if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['frontend-listing'] ) ) {
				$this->bulk_actions();
			}

			// Empty trash.
			if ( isset( $_GET['empty_trash'] ) ) {
				$this->empty_trash();
			}
		}
	}

	/**
	 * Bulk trash/delete.
	 *
	 * @param array $frontend_lists Frontend List post id.
	 * @param bool  $delete Delete action.
	 */
	private function bulk_trash( $frontend_lists, $delete = false ) {

		foreach ( $frontend_lists as $urfl_id ) {

			if ( $delete ) {
				wp_delete_post( $urfl_id, true );
			} else {
				wp_trash_post( $urfl_id );
			}
		}

		$type   = ! EMPTY_TRASH_DAYS || $delete ? 'deleted' : 'trashed';
		$qty    = count( $frontend_lists );
		$status = isset( $_GET['status'] ) ? '&status=' . sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		// Redirect to Frontend Listings page.
		wp_safe_redirect( esc_url( admin_url( 'admin.php?page=user-registration-frontend-list' . $status . '&' . $type . '=' . $qty ) ) );
		exit();
	}

	/**
	 * Bulk untrash.
	 *
	 * @param array $frontend_lists Frontend List post id.
	 */
	private function bulk_untrash( $frontend_lists ) {
		foreach ( $frontend_lists as $urfl_id ) {
			wp_untrash_post( $urfl_id );
		}

		$qty = count( $frontend_lists );

		// Redirect to Frontend Listings page.
		wp_safe_redirect( esc_url( admin_url( 'admin.php?page=user-registration-frontend-list&status=trashed&untrashed=' . $qty ) ) );
		exit();
	}

	/**
	 * Bulk actions.
	 */
	private function bulk_actions() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permissions to edit user registration frontend lists!', 'user-registration-frontend-listing' ) );
		}

		$frontend_list = array_map( 'absint', ! empty( $_REQUEST['frontend-listing'] ) ? (array) $_REQUEST['frontend-listing'] : array() );
		$action        = isset( $_REQUEST['action'] ) ? wp_unslash( $_REQUEST['action'] ) : array();

		switch ( $action ) {
			case 'trash':
				$this->bulk_trash( $frontend_list );
				break;
			case 'untrash':
				$this->bulk_untrash( $frontend_list );
				break;
			case 'delete':
				$this->bulk_trash( $frontend_list, true );
				break;
			default:
				break;
		}
	}

	/**
	 * Empty Trash.
	 */
	private function empty_trash() {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'empty_trash' ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'user-registration-frontend-listing' ) );
		}

		if ( ! current_user_can( 'delete_posts' ) ) {
			wp_die( esc_html__( 'You do not have permissions to delete user registration frontend lists!', 'user-registration-frontend-listing' ) );
		}

		$frontend_lists = get_posts(
			array(
				'post_type'           => 'ur_frontend_listings',
				'ignore_sticky_posts' => true,
				'nopaging'            => true,
				'post_status'         => 'trash',
				'fields'              => 'ids',
			)
		);

		foreach ( $frontend_lists as $webhook_id ) {
			wp_delete_post( $webhook_id, true );
		}

		$qty = count( $frontend_lists );

		// Redirect to Frontend Listings page.
		wp_safe_redirect( esc_url( admin_url( 'admin.php?page=user-registration-frontend-list&deleted=' . $qty ) ) );
		exit();
	}

	/**
	 * Add Frontend Listing addons screen_ids to the pool of user registration screen ids.
	 *
	 * @param array $screen_ids Screens ids of user registration and addons.
	 * @return array
	 */
	public function ur_frontend_listing_add_screen_id( $screen_ids ) {

		$urfl_screen_ids = array(
			'user-registration-membership_page_user-registration-frontend-list',
			'ur_frontend_listings',
		);

		$screen_ids = array_merge( $screen_ids, $urfl_screen_ids );
		return $screen_ids;
	}

	/**
	 * Add to general settings of User Registration.
	 *
	 * @param array $general_settings General settings array from Core.
	 */
	public function ur_frontend_list_add_general_settings( $general_settings ) {

		// Add new settings to general options.
		$general_options = $general_settings['sections']['general_pages_settings']['settings'];
		$general_options = array_merge(
			$general_options,
			array(
				array(
					'title'    => __( 'Frontend Lists Default Page', 'user-registration-frontend-listing' ),
					'desc'     => __( 'Select the page which contains your frontend list', 'user-registration-frontend-listing' ),
					'id'       => 'user_registration_frontend_listing_default_page',
					'type'     => 'single_select_page',
					'default'  => '',
					'class'    => 'ur-enhanced-select-nostd',
					'css'      => 'min-width:350px;',
					'desc_tip' => true,
				),
			)
		);

		$general_settings['sections']['general_pages_settings']['settings'] = $general_options;

		return $general_settings;
	}

	/**
	 * Create a view user url for each user in the users list.
	 *
	 * @param  array  $actions the approve or pending action.
	 * @param  string $user The id of the user.
	 *
	 * @since 1.0.4
	 *
	 * @return array
	 */
	public function create_quick_links( $actions, $user ) {

		$default_page_id_for_list = get_option( 'user_registration_frontend_listing_default_page', '' );

		if ( '' !== $default_page_id_for_list ) {
			$post                  = get_post( $default_page_id_for_list );
			$default_page_for_list = isset( $post->post_name ) ? $post->post_name : '';
			$post_content          = isset( $post->post_content ) ? $post->post_content : '';

			if ( has_shortcode( $post_content, 'user_registration_frontend_list' ) ) {
				$attributes              = ur_get_shortcode_attr( $post_content );
				$list_id                 = isset( $attributes[0]['id'] ) ? $attributes[0]['id'] : 0;
				$link                    = home_url( '/' . $default_page_for_list ) . '?list_id=' . $list_id . '&user_id=' . $user->ID;
				$actions['view_profile'] = '<a href=' . esc_url( $link ) . '>' . esc_html__( 'View Profile', 'user-registration-frontend-listing' ) . '</a>';
			}
		}
		if ( ! current_user_can( 'administrator' ) ) {
			$privacy_tab_enable     = get_option( 'user_registration_enable_privacy_tab', false );
			$enable_profile_privacy = get_option( 'user_registration_enable_profile_privacy', true );

			if ( ur_string_to_bool( $privacy_tab_enable ) && ur_string_to_bool( $enable_profile_privacy ) ) {
				$show_profile = ur_string_to_bool( get_user_meta( $user->ID, 'ur_show_profile', true ) );
				if ( $show_profile ) {
					unset( $actions['view_profile'] );
				}
			}
		}
		return $actions;
	}

	/**
	 * AJAX handler to create new listing
	 * Add this to your Metabox or Admin class and hook it
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function ajax_create_listing() {
		check_ajax_referer( 'urfl_create_listing', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Permission denied.', 'user-registration-frontend-listing' ),
				)
			);
		}

		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';

		if ( empty( trim( $title ) ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Listing name is required.', 'user-registration-frontend-listing' ),
				)
			);
		}

		$post_id = wp_insert_post(
			array(
				'post_title'  => $title,
				'post_type'   => 'ur_frontend_listings',
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Error creating listing.', 'user-registration-frontend-listing' ),
				)
			);
		}

		wp_send_json_success(
			array(
				'message'  => __( 'Listing created successfully.', 'user-registration-frontend-listing' ),
				'post_id'  => $post_id,
				'redirect' => admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
			)
		);
	}

	/**
	 * AJAX handler to update post title
	 */
	public function ajax_update_title() {
		// Verify nonce for security
		check_ajax_referer( 'urfl_create_listing', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}

		$post_id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$new_title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';

		if ( empty( $new_title ) ) {
			wp_send_json_error( array( 'message' => 'Title cannot be empty.' ) );
		}

		$post = array(
			'ID'         => $post_id,
			'post_title' => $new_title,
		);

		$updated_post_id = wp_update_post( $post, true );

		if ( is_wp_error( $updated_post_id ) ) {
			wp_send_json_error( array( 'message' => 'Error updating the title.' ) );
		}

		wp_send_json_success( array( 'message' => esc_html( $new_title ) ) );
	}
}
