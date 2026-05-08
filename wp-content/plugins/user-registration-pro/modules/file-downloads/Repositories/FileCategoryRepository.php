<?php

namespace WPEverest\URM\Pro\FileDownloads\Repositories;

use WPEverest\URM\Pro\FileDownloads\Models\FileCategory;
use WPEverest\URM\Pro\FileDownloads\Taxonomies\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FileCategoryRepository {

	/**
	 * @param int $category_id
	 * @return FileCategory|null
	 */
	public function find( $category_id ) {
		return FileCategory::from_term_id( $category_id );
	}

	/**
	 * @param array<string, mixed> $args
	 * @return array{categories: array<Category>, total: int}
	 */
	public function query( $args = [] ) {
		$defaults = [
			'taxonomy'   => Taxonomy::FILE_CATEGORY,
			'hide_empty' => false,
		];

		$query_args = array_merge( $defaults, $args );

		$per_page     = $query_args['number'] ?? 20;
		$current_page = $query_args['paged'] ?? 1;
		$offset       = ( $current_page - 1 ) * $per_page;

		$query_args['number'] = $per_page;
		$query_args['offset'] = $offset;

		$count_args           = $query_args;
		$count_args['number'] = 0;
		$count_args['offset'] = 0;
		$count_args['fields'] = 'ids';
		$total_terms          = get_terms( $count_args );
		$total                = is_wp_error( $total_terms ) ? 0 : count( $total_terms );
		$terms                = get_terms( $query_args );

		if ( is_wp_error( $terms ) ) {
			return [
				'categories' => [],
				'total'      => 0,
			];
		}

		$categories = $this->convert_terms_to_categories( $terms );

		return [
			'categories' => $categories,
			'total'      => $total,
		];
	}

	/**
	 * @param array<string, mixed> $args
	 * @return array<Category>
	 */
	public function all( $args = [] ) {
		$args['number'] = 0;
		$result         = $this->query( $args );
		return $result['categories'];
	}

	/**
	 * @param array<\WP_Term> $terms
	 * @return array<Category>
	 */
	private function convert_terms_to_categories( $terms ) {
		$categories = [];

		foreach ( $terms as $term ) {
			$category = FileCategory::from_term( $term );
			if ( $category ) {
				$categories[] = $category;
			}
		}

		return $categories;
	}

	/**
	 * @param int $category_id
	 * @return bool
	 */
	public function exists( $category_id ) {
		return null !== $this->find( $category_id );
	}
}
