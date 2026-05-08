<?php

namespace WPEverest\URM\Pro\FileDownloads\Admin;

use WPEverest\URM\Pro\FileDownloads\Models\FileCategory;
use WPEverest\URM\Pro\FileDownloads\Models\File;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	/**
	 * @var string
	 */
	private $page_hook;

	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * @return string
	 */
	public function get_page_hook() {
		return $this->page_hook;
	}

	private function init_hooks() {
		add_filter( 'user_registration_notice_excluded_pages', [ $this, 'add_excluded_page' ] );
		add_action( 'admin_menu', [ $this, 'add_menu' ], 25 );
		add_action( 'admin_init', [ $this, 'handle_actions' ] );
		add_filter(
			'submenu_file',
			function ( $submenu_file ) {
				if ( isset( $_GET['page'] ) && 'user-registration-file-downloads' === sanitize_text_field( $_GET['page'] ) ) {
					if ( isset( $_GET['screen'] ) && 'categories' === $_GET['screen'] ) {
						return 'user-registration-file-downloads&screen=categories';
					}
				}
				return $submenu_file;
			}
		);
	}

	public function add_excluded_page( $excluded_pages ) {
		$excluded_pages[] = 'user-registration-file-downloads';
		return $excluded_pages;
	}

	public function add_menu() {
		$this->page_hook = add_submenu_page(
			'user-registration',
			__( 'File Downloads', 'user-registration' ),
			__( 'File Downloads', 'user-registration' ),
			'manage_options',
			'user-registration-file-downloads',
			[ $this, 'render_files_page' ],
		);

		add_action( "load-{$this->page_hook}", [ $this, 'enqueue_scripts_styles' ] );

		if (
			isset( $_GET['page'] ) &&
			'user-registration-file-downloads' === trim( sanitize_text_field( wp_unslash( $_GET['page'] ) ) )
			) {

			add_submenu_page(
				'user-registration',
				__( 'Categories', 'user-registration' ),
				'↳ ' . __( 'Categories', 'user-registration' ),
				'manage_options',
				'user-registration-file-downloads&screen=categories',
				array(
					$this,
					'render_membership_page',
				),
				17
			);
		}
	}

	/**
	 * @return string|null
	 */
	private function get_current_action() {
		return isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * @return void
	 */
	public function handle_actions() {
		// TODO:
	}


	/**
	 * @return void
	 */
	public function render_files_page() {
		$screen = $this->get_current_screen();
		$action = $this->get_current_action();

		if ( isset( $action ) ) {
			$id = $this->get_current_id();
			$this->render_form( $screen, $id );
			return;
		}

		$this->render_list( $screen );
	}

	/**
	 * @return string
	 */
	private function get_current_screen() {
		return isset( $_GET['screen'] ) ? sanitize_text_field( wp_unslash( $_GET['screen'] ) ) : 'files'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * @return int
	 */
	private function get_current_id() {
		return isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * @param string $screen
	 * @return void
	 */
	private function render_list( $screen = 'files' ) {
		$screen = $this->validate_screen( $screen );
		echo do_shortcode( user_registration_plugin_main_header() );

		$table_list = 'categories' === $screen ? new FileCategoriesListTable() : new FilesListTable();
		$table_list->render();
	}

	/**
	 * @param string $screen
	 * @param int    $id
	 * @return void
	 */
	private function render_form( $screen = 'files', $id = 0 ) {
		$screen = $this->validate_screen( $screen );
		$data   = null;
		if ( $id > 0 ) {
			$model_factory = $this->get_model_factory( $screen );
			$model         = call_user_func( $model_factory, $id );
			if ( $model ) {
				$data = $model->to_array();
			}
		}
		printf( '<script>%s</script>', 'window.__UR_FILE_DOWNLOADS_INITIAL_FORM_DATA__=' . wp_json_encode( $data ) );
		echo '<div id="user-registration-file-downloads-form"></div>';
	}

	/**
	 * @param string $screen
	 * @return string
	 */
	private function validate_screen( $screen ) {
		return in_array( $screen, [ 'files', 'categories' ], true ) ? $screen : 'file';
	}

	/**
	 * @param string $screen
	 * @return callable
	 */
	private function get_model_factory( $screen ) {
		return 'categories' === $screen
			? [ FileCategory::class, 'from_term_id' ]
			: [ File::class, 'from_post_id' ];
	}

	/**
	 * @return void
	 */
	public function enqueue_scripts_styles() {
		if ( ! wp_style_is( 'ur-snackbar', 'registered' ) ) {
			wp_register_style(
				'ur-snackbar',
				UR()->plugin_url() . '/assets/css/ur-snackbar/ur-snackbar.css',
				array(),
				'1.0.0'
			);
		}
		wp_enqueue_style( 'ur-snackbar' );

		wp_enqueue_style( 'sweetalert2' );

		if ( ! wp_style_is( 'ur-core-builder-style', 'registered' ) ) {
			wp_register_style(
				'ur-core-builder-style',
				UR()->plugin_url() . '/assets/css/admin.css',
				array(),
				UR_VERSION
			);
		}
		wp_enqueue_style( 'ur-core-builder-style' );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'ur-snackbar', UR()->plugin_url() . '/assets/js/ur-snackbar/ur-snackbar' . $suffix . '.js', array(), '1.0.0', true );
		wp_enqueue_script( 'ur-snackbar' );

		$asset_file = UR()->plugin_path() . '/chunks/file-downloads-form.asset.php';

		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;

			wp_enqueue_script(
				'user-registration-file-downloads-form',
				UR()->plugin_url() . '/chunks/file-downloads-form.js',
				$asset['dependencies'],
				$asset['version'],
				true
			);

			wp_localize_script(
				'user-registration-file-downloads-form',
				'__UR_FILE_DOWNLOADS_FORM__',
				apply_filters(
					'user_registration_file_downloads_form_localized_data',
					[
						'base_admin_url'     => admin_url( 'admin.php?page=user-registration-file-downloads' ),
						'screen'             => $this->get_current_screen(),
						'current_action'     => $this->get_current_action(),
						'allowed_mime_types' => get_allowed_mime_types(),
						'file_size_limit'    => wp_max_upload_size(),
					]
				)
			);

			$css_rtl_suffix = is_rtl() ? '.rtl.css' : '.css';

			wp_enqueue_style( 'wp-components' );
			wp_enqueue_style(
				'user-registration-file-downloads-form',
				UR()->plugin_url() . "/chunks/style-file-downloads-form{$css_rtl_suffix}",
				[],
				$asset['version']
			);
			wp_set_script_translations(
				'user-registration-file-downloads-form',
				'user-registration',
				UR()->plugin_path() . '/language'
			);
		}

		$asset_file = UR()->plugin_path() . '/chunks/file-downloads-list.asset.php';

		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;

			wp_enqueue_script(
				'user-registration-file-downloads-list',
				UR()->plugin_url() . '/chunks/file-downloads-list.js',
				$asset['dependencies'],
				$asset['version'],
				true
			);

			wp_localize_script(
				'user-registration-file-downloads-list',
				'__UR_FILE_DOWNLOADS__',
				apply_filters(
					'user_registration_file_downloads_localized_data',
					[
						'nonce'      => wp_create_nonce( 'wp_rest' ),
						'i18n'       => [
							'delete_prompt_title'       => __( 'Are you sure?', 'user-registration' ),
							'delete_prompt_description' => __( 'Are you sure you want to delete this permanently?', 'user-registration' ),
							'delete_prompt_confirm'     => __( 'Delete', 'user-registration' ),
							'delete_prompt_cancel'      => __( 'Cancel', 'user-registration' ),
							'delete_success'            => __( 'Deleted successfully.', 'user-registration' ),
							'delete_error'              => __( 'An error occurred while deleting. Please try again.', 'user-registration' ),
							'bulk_delete_prompt_title'  => __( 'Are you sure?', 'user-registration' ),
							'bulk_delete_prompt_description' => __( 'Are you sure you want to delete the selected items permanently?', 'user-registration' ),
							'bulk_delete_success'       => __( 'Selected items deleted successfully.', 'user-registration' ),
						],
						'trash_icon' => plugins_url( 'assets/images/users/delete-user-red.svg', UR_PLUGIN_FILE ),
					]
				)
			);
		}
	}
}
