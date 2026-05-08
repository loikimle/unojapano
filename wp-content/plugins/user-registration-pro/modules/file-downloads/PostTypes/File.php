<?php

namespace WPEverest\URM\Pro\FileDownloads\PostTypes;

use WPEverest\URM\Pro\FileDownloads\Controllers\V1\FilesController;
use WPEverest\URM\Pro\FileDownloads\Enums\FileDownload;
use WPEverest\URM\Pro\FileDownloads\Handlers\FilePermalinkHandler;
use WPEverest\URM\Pro\FileDownloads\Meta\MetaFields;
use WPEverest\URM\Pro\FileDownloads\PostTypes\PostType;
use WPEverest\URM\Pro\FileDownloads\Repositories\FileRepository;
use WPEverest\URM\Pro\FileDownloads\Services\AccessControlService;
use WPEverest\URM\Pro\FileDownloads\Services\DownloadService;
use WPEverest\URM\Pro\FileDownloads\Services\FileStorageService;
use WPEverest\URM\Pro\FileDownloads\Taxonomies\Taxonomy;
use WPEverest\URM\Pro\FileDownloads\Models\File as FileModel;
use WPEverest\URM\Pro\FileDownloads\Services\ContentRulesIntegrationService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class File extends Base {

	/**
	 * @var FilePermalinkHandler|null
	 */
	private static $permalink_handler = null;

	public function __construct() {
		add_action( "user_registration_file_downloads_{$this->get_post_type()}_post_type_registered", [ $this, 'on_registration' ] );
		add_filter( 'post_type_link', [ $this, 'filter_post_type_link' ], 10, 2 );
		add_action( 'init', [ $this, 'init_permalink_handler' ], 15 );
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_action( 'parse_request', [ $this, 'handle_file_endpoint' ], 1 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_post_type() {
		return PostType::FILE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_post_type_args() {
		return [
			'labels'                => [
				'name'               => __( 'File', 'user-registration' ),
				'singular_name'      => __( 'File', 'user-registration' ),
				'add_new'            => __( 'Add new file', 'user-registration' ),
				'add_new_item'       => __( 'Add new file', 'user-registration' ),
				'edit_item'          => __( 'Edit file', 'user-registration' ),
				'new_item'           => __( 'New file', 'user-registration' ),
				'view_item'          => __( 'View file', 'user-registration' ),
				'search_items'       => __( 'Search file', 'user-registration' ),
				'not_found'          => __( 'No file found', 'user-registration' ),
				'not_found_in_trash' => __( 'No file found in Trash', 'user-registration' ),
				'parent_item_colon'  => '',
			],
			'public'                => true,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'query_var'             => true,
			'rewrite'               => false,
			'capability_type'       => 'post',
			'has_archive'           => false,
			'hierarchical'          => false,
			'show_in_rest'          => true,
			'supports'              => [ 'title', 'editor', 'custom-fields' ],
			'rest_namespace'        => 'user-registration-pro/v1',
			'rest_base'             => 'files',
			'rest_controller_class' => FilesController::class,
			'taxonomies'            => [ Taxonomy::FILE_CATEGORY ],
		];
	}

	public function on_registration() {
		MetaFields::register();
		add_action( 'before_delete_post', [ $this, 'delete_associated_file' ], 10, 1 );
	}

	/**
	 * @return void
	 */
	public function init_permalink_handler() {
		if ( null === self::$permalink_handler ) {
			$file_repository         = new FileRepository();
			$content_rules_service   = new ContentRulesIntegrationService();
			$access_control          = new AccessControlService( $content_rules_service );
			$file_storage            = new FileStorageService();
			$download_service        = new DownloadService(
				$file_repository,
				$access_control,
				$file_storage
			);
			self::$permalink_handler = new FilePermalinkHandler( $download_service );
			self::$permalink_handler->init();
		}
	}

	public function add_rewrite_rules() {
		$prefix = FileDownload::DOWNLOAD_URL_PREFIX;
		add_rewrite_rule(
			'^' . $prefix . '/([^/]+)/?$',
			'index.php?urfd_plugin=' . FileDownload::PLUGIN_SLUG . '&urfd_action=' . FileDownload::ACTION_DOWNLOAD . '&urfd_file=$matches[1]',
			'top'
		);
	}

	/**
	 * @param \WP $wp
	 * @return void
	 */
	public function handle_file_endpoint( $wp ) {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( empty( $request_uri ) ) {
			return;
		}

		$request_path = strtok( $request_uri, '?' );
		$request_path = trim( $request_path, '/' );

		$home_path = wp_parse_url( home_url(), PHP_URL_PATH );
		$home_path = $home_path ? trim( $home_path, '/' ) : '';

		if ( ! empty( $home_path ) && strpos( $request_path, $home_path ) === 0 ) {
			$request_path = substr( $request_path, strlen( $home_path ) );
			$request_path = ltrim( $request_path, '/' );
		}

		$prefix = FileDownload::DOWNLOAD_URL_PREFIX;
		if ( strpos( $request_path, $prefix . '/' ) !== 0 ) {
			return;
		}

		$post_slug = substr( $request_path, strlen( $prefix ) + 1 );

		if ( empty( $post_slug ) ) {
			return;
		}

		$wp->query_vars['urfd_plugin'] = FileDownload::PLUGIN_SLUG;
		$wp->query_vars['urfd_action'] = FileDownload::ACTION_DOWNLOAD;
		$wp->query_vars['urfd_file']   = $post_slug;
	}

	/**
	 * @param string   $post_link
	 * @param \WP_Post $post
	 * @return string
	 */
	public function filter_post_type_link( $post_link, $post ) {
		if ( PostType::FILE !== $post->post_type ) {
			return $post_link;
		}

		$file = FileModel::from_post_id( $post->ID );
		if ( ! $file ) {
			return $post_link;
		}

		$file_name = $file->get_file_name();
		if ( empty( $file_name ) ) {
			return $post_link;
		}

		$prefix    = FileDownload::DOWNLOAD_URL_PREFIX;
		$post_slug = $post->post_name;

		return home_url( '/' . $prefix . '/' . $post_slug );
	}

	/**
	 * @param int $post_id
	 * @return void
	 */
	public function delete_associated_file( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || PostType::FILE !== $post->post_type ) {
			return;
		}

		$file = FileModel::from_post_id( $post_id );

		if ( ! $file ) {
			return;
		}

		$file_path = $file->get_file_path();

		if ( ! empty( $file_path ) ) {
			$file_storage = new FileStorageService();
			$file_storage->delete_file( $file_path );
		}
	}
}
