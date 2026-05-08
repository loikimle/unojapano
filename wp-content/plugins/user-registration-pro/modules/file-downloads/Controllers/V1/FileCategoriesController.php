<?php

namespace WPEverest\URM\Pro\FileDownloads\Controllers\V1;

use WPEverest\URM\Pro\FileDownloads\Models\FileCategory;

class FileCategoriesController extends \WP_REST_Terms_Controller {

	/**
	 * {@inheritDoc}
	 */
	public function __construct( $taxonomy ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		parent::__construct( $taxonomy );
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

		$request->set_param( 'context', 'view' );

		foreach ( $ids as $id ) {
			$id = absint( $id );
			if ( ! $id ) {
				$failed[] = $id;
				continue;
			}

			$term = get_term( (int) $id, $this->taxonomy );
			if ( empty( $term ) || is_wp_error( $term ) || $term->taxonomy !== $this->taxonomy ) {
				$failed[] = $id;
				continue;
			}
			$prev = wp_delete_term( $term->term_id, $term->taxonomy );
			if ( ! $prev || is_wp_error( $prev ) ) {
				$failed[] = $id;
				continue;
			}
			$deleted[] = $id;
		}

		$response_data = [
			'deleted' => $deleted,
			'failed'  => $failed,
			'count'   => count( $deleted ),
		];

		if ( empty( $deleted ) ) {
			return new \WP_Error(
				'rest_batch_delete_failed',
				__( 'No file categories were deleted.', 'user-registration' ),
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
	public function prepare_item_for_response( $term, $request ) {
		$response      = parent::prepare_item_for_response( $term, $request );
		$file_category = FileCategory::from_term_id( $term->term_id );

		if ( ! $file_category ) {
			return new \WP_Error(
				'rest_file_category_not_found',
				__( 'File category not found.', 'user-registration' ),
				[ 'status' => 404 ]
			);
		}
		$file_category_data           = $file_category->to_array();
		$file_category_data['_links'] = $response->get_links();

		return rest_ensure_response( $file_category_data );
	}
}
