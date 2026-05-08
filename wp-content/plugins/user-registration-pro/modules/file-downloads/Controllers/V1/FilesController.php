<?php

namespace WPEverest\URM\Pro\FileDownloads\Controllers\V1;

use WPEverest\URM\Pro\FileDownloads\Models\File;
use WPEverest\URM\Pro\FileDownloads\Meta\MetaKeys;
use WPEverest\URM\Pro\FileDownloads\Services\FileStorageService;
use WPEverest\URM\Pro\FileDownloads\Services\FileUploadService;

class FilesController extends \WP_REST_Posts_Controller {

	/**
	 * @var null|array
	 */
	private $temp_file_data = null;

	/**
	 * {@inheritDoc}
	 */
	public function __construct( $post_type ) {
		parent::__construct( $post_type );
		add_filter( "rest_pre_insert_{$this->post_type}", [ $this, 'on_pre_insert' ], 10, 2 );
		add_filter( "rest_insert_{$this->post_type}", [ $this, 'on_insert' ], 10, 3 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_routes() {
		parent::register_routes();

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/batch-delete',
			[
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'batch_delete_items' ],
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => [
						'ids' => [
							'required' => true,
							'type'     => 'array',
							'items'    => [
								'type' => 'integer',
							],
						],
					],
				],
			]
		);
	}

	/**
	 * @param stdClass $prepared_post
	 * @param \WP_REST_Request $request
	 * @return stdClass|\WP_Error
	 */
	public function on_pre_insert( $prepared_post, $request ) {
		$files     = $request->get_file_params();
		$is_update = isset( $prepared_post->ID ) && $prepared_post->ID > 0;

		if ( ! isset( $files['file'] ) ) {
			if ( ! $is_update ) {
				return new \WP_Error( 'rest_invalid_param', __( 'File is required.', 'user-registration' ), array( 'status' => 400 ) );
			}
			return $prepared_post;
		}

		try {
			$file_storage = new FileStorageService();
			$service      = new FileUploadService( $file_storage );
			if ( $is_update ) {
				$old_file = File::from_post_id( $prepared_post->ID );
				if ( $old_file && ! empty( $old_file->get_file_path() ) ) {
					$old_file_path = $old_file->get_file_path();
					$file_storage->delete_file( $old_file_path );
				}
			}
			$file_data            = $service->upload_file( $files['file'] );
			$this->temp_file_data = $file_data;
			return $prepared_post;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'rest_upload_error', $e->getMessage(), array( 'status' => 400 ) );
		}
	}

	public function on_insert( $post, $request, $create ) {
		if ( $this->temp_file_data ) {
			update_post_meta( $post->ID, MetaKeys::FILE_PATH, $this->temp_file_data['file_path'] );
			update_post_meta( $post->ID, MetaKeys::FILE_SIZE, $this->temp_file_data['file_size'] );
			update_post_meta( $post->ID, MetaKeys::FILE_MIME_TYPE, $this->temp_file_data['file_mime_type'] );
			update_post_meta( $post->ID, MetaKeys::FILE_NAME, $this->temp_file_data['file_name'] );
		}

		if ( isset( $request['meta'] ) && is_array( $request['meta'] ) ) {
			foreach ( $request['meta'] as $key => $value ) {
				update_post_meta( $post->ID, $key, $value );
			}
		}

		unset( $this->temp_file_data );
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function batch_delete_items( $request ) {
		$ids = $request->get_param( 'ids' );

		if ( ! is_array( $ids ) || empty( $ids ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'IDs parameter must be a non-empty array.', 'user-registration' ),
				[ 'status' => 400 ]
			);
		}

		$deleted = [];
		$failed  = [];

		foreach ( $ids as $id ) {
			$id = absint( $id );
			if ( ! $id ) {
				$failed[] = $id;
				continue;
			}
			$prev = wp_delete_post( $id, true );
			if ( ! $prev || is_wp_error( $prev ) ) {
				$failed[] = $id;
				continue;
			}
			$deleted[] = $deleted;
		}

		$response_data = [
			'deleted' => $deleted,
			'failed'  => $failed,
			'count'   => count( $deleted ),
		];

		if ( empty( $deleted ) ) {
			return new \WP_Error(
				'rest_batch_delete_failed',
				__( 'No files were deleted.', 'user-registration' ),
				[
					'status' => 400,
					'data'   => $response_data,
				]
			);
		}

		return new \WP_REST_Response( $response_data, ! empty( $failed ) ? 207 : 200 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare_item_for_response( $post, $request ) {
		$response = parent::prepare_item_for_response( $post, $request );
		$file     = File::from_post_id( $post->ID );

		if ( ! $file ) {
			return new \WP_Error( 'rest_file_not_found', __( 'File not found.', 'user-registration' ), array( 'status' => 404 ) );
		}
		$file_data           = $file->to_array();
		$file_data['_links'] = $response->get_links();

		return rest_ensure_response( $file_data );
	}
}
