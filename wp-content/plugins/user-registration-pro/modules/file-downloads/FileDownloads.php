<?php

namespace WPEverest\URM\Pro\FileDownloads;

use WPEverest\URM\Pro\FileDownloads\Admin\AdminService;
use WPEverest\URM\Pro\FileDownloads\PostTypes\File;
use WPEverest\URM\Pro\FileDownloads\Services\ContentRulesIntegrationService;
use WPEverest\URM\Pro\FileDownloads\Services\FileStorageService;
use WPEverest\URM\Pro\FileDownloads\Shortcodes\Shortcodes;
use WPEverest\URM\Pro\FileDownloads\Taxonomies\FileCategory;

final class FileDownloads {

	/**
	 * @var FileDownloads
	 */
	private static $instance = null;

	/**
	 * @return FileDownloads
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		if ( ! ur_check_module_activation( 'file-downloads' ) ) {
			return;
		}
		AdminService::init();
		add_action( 'init', [ $this, 'on_init' ], 0 );
		add_filter( 'block_editor_rest_api_preload_paths', [ $this, 'add_preload_paths' ] );
		$this->initialize_protected_directory();

		( new ContentRulesIntegrationService() )->init_hooks();
		( new MyAccount() )->init_hooks();
	}

	/**
	 * @return void
	 */
	private function initialize_protected_directory() {
		$file_storage = new FileStorageService();
		$file_storage->get_protected_dir();
	}

	public function on_init() {
		$this->register_post_types();
		$this->register_taxonomies();
		$this->register_shortcodes();
		$this->register_blocks();
	}

	/**
	 * @return void
	 */
	private function register_post_types() {
		$post_types = [
			File::class,
		];

		foreach ( $post_types as $post_type ) {
			( new $post_type() )->register();
		}
	}

	/**
	 * @return void
	 */
	private function register_taxonomies() {
		$taxonomies = [
			FileCategory::class,
		];

		foreach ( $taxonomies as $taxonomy ) {
			( new $taxonomy() )->register();
		}
	}

	/**
	 * @return void
	 */
	private function register_shortcodes() {
		$shortcodes = new Shortcodes();
		$shortcodes->register();
	}

	/**
	 * @return void
	 */
	private function register_blocks() {
		$asset_file = UR()->plugin_path() . '/chunks/file-downloads-blocks.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset = require $asset_file;
		wp_register_script(
			'user-registration-file-downloads-blocks',
			UR()->plugin_url() . '/chunks/file-downloads-blocks.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
		wp_register_style(
			'user-registration-file-downloads-blocks',
			UR()->plugin_url() . '/chunks/file-downloads-blocks.css',
			[],
			$asset['version']
		);
		wp_register_style(
			'user-registration-file-downloads-blocks-style',
			UR()->plugin_url() . '/chunks/style-file-downloads-blocks.css',
			[],
			$asset['version']
		);
		// static function ( $attributes, $content, $block ) use ( $template_path ) {
		//      ob_start();
		//      require $template_path;
		//      return ob_get_clean();
		//  };
		register_block_type_from_metadata(
			UR()->plugin_path() . '/chunks/file-downloads/block.json',
			[
				'render_callback' => static function ( $attributes, $content, $block ) {
					ob_start();
					include_once __DIR__ . '/render.php';
					return ob_get_clean();
				},
			]
		);
	}

	public function add_preload_paths( $preload_paths ) {
		$preload_paths[] = '/user-registration-pro/v1/files';
		$preload_paths[] = '/user-registration-pro/v1/file-categories';
		return $preload_paths;
	}
}


FileDownloads::get_instance();
