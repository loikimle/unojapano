<?php

namespace WPEverest\URM\Pro\FileDownloads\Models;

use WPEverest\URM\Pro\FileDownloads\Taxonomies\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FileCategory {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var int
	 */
	private $parent_id;

	/**
	 * @var int
	 */
	private $count;

	/**
	 * @param int $term_id
	 * @return self|null
	 */
	public static function from_term_id( $term_id ) {
		$term = get_term( $term_id, Taxonomy::FILE_CATEGORY );

		if ( is_wp_error( $term ) || ! $term ) {
			return null;
		}

		$category              = new self();
		$category->id          = $term->term_id;
		$category->name        = $term->name;
		$category->slug        = $term->slug;
		$category->description = $term->description;
		$category->parent_id   = $term->parent;
		$category->count       = $term->count;

		return $category;
	}

	/**
	 * @param \WP_Term $term
	 * @return self|null
	 */
	public static function from_term( $term ) {
		if ( ! $term instanceof \WP_Term || Taxonomy::FILE_CATEGORY !== $term->taxonomy ) {
			return null;
		}

		$category              = new self();
		$category->id          = $term->term_id;
		$category->name        = $term->name;
		$category->slug        = $term->slug;
		$category->description = $term->description;
		$category->parent_id   = $term->parent;
		$category->count       = $term->count;

		return $category;
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * @return int
	 */
	public function get_parent_id() {
		return $this->parent_id;
	}

	/**
	 * @return int
	 */
	public function get_count() {
		return $this->count;
	}

	/**
	 * @return self|null
	 */
	public function get_parent() {
		if ( ! $this->parent_id ) {
			return null;
		}

		return self::from_term_id( $this->parent_id );
	}

	/**
	 * @return array<File>
	 */
	public function get_files() {
		$posts = get_posts(
			[
				'post_type'      => \WPEverest\URM\Pro\FileDownloads\PostTypes\PostType::FILE,
				'posts_per_page' => -1,
				'tax_query'      => [
					[
						'taxonomy' => Taxonomy::FILE_CATEGORY,
						'field'    => 'term_id',
						'terms'    => $this->id,
					],
				],
			]
		);

		$files = [];
		foreach ( $posts as $post ) {
			$file = File::from_post_id( $post->ID );
			if ( $file ) {
				$files[] = $file;
			}
		}

		return $files;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function to_array() {
		return [
			'id'          => $this->id,
			'name'        => $this->name,
			'slug'        => $this->slug,
			'description' => $this->description,
			'parent_id'   => $this->parent_id,
			'count'       => $this->count,
		];
	}
}
