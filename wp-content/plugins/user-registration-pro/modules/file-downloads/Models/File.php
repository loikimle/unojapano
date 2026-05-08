<?php

namespace WPEverest\URM\Pro\FileDownloads\Models;

use WPEverest\URM\Pro\FileDownloads\Enums\FileDownload;
use WPEverest\URM\Pro\FileDownloads\Meta\MetaKeys;
use WPEverest\URM\Pro\FileDownloads\PostTypes\PostType;
use WPEverest\URM\Pro\FileDownloads\Services\ContentRulesIntegrationService;
use WPEverest\URM\Pro\FileDownloads\Taxonomies\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class File {

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
	private $description;

	/**
	 * @var string
	 */
	private $file_name;

	/**
	 * @var string
	 */
	private $file_path;

	/**
	 * @var int
	 */
	private $file_size;

	/**
	 * @var string
	 */
	private $file_mime_type;

	/**
	 * @var int
	 */
	private $download_count;

	/**
	 * @var int
	 */
	private $download_limit_total;

	/**
	 * @var bool
	 */
	private $download_limit;

	/**
	 * @var int
	 */
	private $download_limit_per_user;

	/**
	 * @var array<int, mixed>
	 */
	private $access_rules;

	/**
	 * Create File model from post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return self|null
	 */
	public static function from_post_id( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || PostType::FILE !== $post->post_type ) {
			return null;
		}

		$file                          = new self();
		$file->id                      = $post->ID;
		$file->name                    = $post->post_title;
		$file->description             = $post->post_content;
		$file->file_name               = get_post_meta( $post->ID, MetaKeys::FILE_NAME, true ) ?: '';
		$file->file_path               = get_post_meta( $post->ID, MetaKeys::FILE_PATH, true ) ?: '';
		$file->file_size               = (int) get_post_meta( $post->ID, MetaKeys::FILE_SIZE, true ) ?: '';
		$file->file_mime_type          = get_post_meta( $post->ID, MetaKeys::FILE_MIME_TYPE, true ) ?: '';
		$file->download_count          = (int) get_post_meta( $post->ID, MetaKeys::DOWNLOAD_COUNT, true );
		$file->download_limit          = (bool) get_post_meta( $post->ID, MetaKeys::DOWNLOAD_LIMIT, true );
		$file->download_limit_total    = (int) get_post_meta( $post->ID, MetaKeys::DOWNLOAD_LIMIT_TOTAL, true );
		$file->download_limit_per_user = (int) get_post_meta( $post->ID, MetaKeys::DOWNLOAD_LIMIT_PER_USER, true );
		$file->access_rules            = ( new ContentRulesIntegrationService() )->find_access_rules_by_file_ids( $post->ID );

		return $file;
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
	public function get_description() {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function get_file_name() {
		return $this->file_name;
	}

	/**
	 * Get file path.
	 *
	 * @return string
	 */
	public function get_file_path() {
		return $this->file_path;
	}

	/**
	 * @return int
	 */
	public function get_file_size() {
		return $this->file_size;
	}

	/**
	 * @return string
	 */
	public function get_file_mime_type() {
		return $this->file_mime_type;
	}

	/**
	 * @return int
	 */
	public function get_download_count() {
		return $this->download_count;
	}

	/**
	 * @return bool
	 */
	public function get_download_limit() {
		return $this->download_limit_total;
	}

	/**
	 * @return int
	 */
	public function get_download_limit_total() {
		return $this->download_limit_total;
	}

	/**
	 * @return int
	 */
	public function get_download_limit_per_user() {
		return $this->download_limit_per_user;
	}

	/**
	 * Get access control rules applied to this file.
	 *
	 * @return array<int, mixed>
	 */
	public function get_access_rules() {
		return $this->access_rules;
	}

	/**
	 * Check if file has any access control rules.
	 *
	 * @return bool
	 */
	public function has_access_rules() {
		return ! empty( $this->access_rules );
	}

	/**
	 * @return string
	 */
	public function get_download_url() {
		$post = get_post( $this->id );
		if ( ! $post ) {
			return '';
		}
		$file_name = $this->get_file_name();
		if ( empty( $file_name ) ) {
			return '';
		}
		$post_slug = $post->post_name;
		if ( empty( $post_slug ) ) {
			return '';
		}

		$permalink_structure = get_option( 'permalink_structure' );
		$prefix              = FileDownload::DOWNLOAD_URL_PREFIX;
		$file_name           = pathinfo( $file_name, PATHINFO_FILENAME );

		if ( empty( $permalink_structure ) ) {
			return add_query_arg(
				array(
					PostType::FILE => $post_slug . '/' . $file_name,
				),
				home_url( '/' )
			);
		}

		return home_url( '/' . $prefix . '/' . $post_slug . '/' . $file_name );
	}

	/**
	 * @return void
	 */
	public function increment_download_count() {
		++$this->download_count;
		update_post_meta( $this->id, MetaKeys::DOWNLOAD_COUNT, $this->download_count );
	}

	/**
	 * @param int $user_id
	 * @return int
	 */
	public function get_user_download_count( $user_id ) {
		if ( ! $user_id || $user_id <= 0 ) {
			return 0;
		}

		$meta_key = $this->get_user_download_count_meta_key();
		$count    = get_user_meta( $user_id, $meta_key, true );

		return $count ? (int) $count : 0;
	}

	/**
	 * @param int $user_id
	 * @return void
	 */
	public function increment_user_download_count( $user_id ) {
		if ( ! $user_id || $user_id <= 0 ) {
			return;
		}

		$meta_key = $this->get_user_download_count_meta_key();
		$count    = $this->get_user_download_count( $user_id );
		++$count;
		update_user_meta( $user_id, $meta_key, $count );
	}

	/**
	 * @return string
	 */
	private function get_user_download_count_meta_key() {
		return 'urfd_file_' . $this->id . '_download_count';
	}

	/**
	 * @return \WP_Term[]
	 */
	public function get_categories() {
		$categories = get_the_terms( $this->id, Taxonomy::FILE_CATEGORY );
		if ( is_wp_error( $categories ) || ! $categories ) {
			return [];
		}
		return $categories;
	}

	/**
	 * @param bool $include_categories
	 * @return array<string, mixed>
	 */
	public function to_array( $include_categories = true ) {
		$data = [
			'id'                      => $this->id,
			'name'                    => $this->name,
			'description'             => $this->description,
			'file_name'               => $this->file_name,
			'file_path'               => $this->file_path,
			'file_size'               => $this->file_size,
			'file_mime_type'          => $this->file_mime_type,
			'download_count'          => $this->download_count,
			'download_limit_total'    => $this->download_limit_total,
			'download_limit'          => $this->download_limit,
			'download_limit_per_user' => $this->download_limit_per_user,
			'download_url'            => $this->get_download_url(),
			'access_rules'            => $this->access_rules,
		];

		if ( $include_categories ) {
			$categories         = $this->get_categories();
			$data['categories'] = array_map(
				function ( $term ) {
					return [
						'id'   => $term->term_id,
						'name' => $term->name,
						'slug' => $term->slug,
					];
				},
				$categories
			);
		}

		return $data;
	}
}