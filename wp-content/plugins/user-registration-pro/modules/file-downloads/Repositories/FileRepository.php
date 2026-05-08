<?php

namespace WPEverest\URM\Pro\FileDownloads\Repositories;

use WPEverest\URM\Pro\FileDownloads\Models\File;
use WPEverest\URM\Pro\FileDownloads\PostTypes\PostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FileRepository {

	/**
	 * @param int $file_id
	 * @return File|null
	 */
	public function find( $file_id ) {
		return File::from_post_id( $file_id );
	}

	/**
	 * @param array<string, mixed>
	 * @return array{files: array<File>, total: int}
	 */
	public function query( $args = [] ) {
		$defaults = [
			'post_type'   => PostType::FILE,
			'post_status' => 'publish',
		];

		$query = new \WP_Query( array_merge( $defaults, $args ) );
		$files = $this->convert_posts_to_files( $query->posts );

		return [
			'files' => $files,
			'total' => $query->found_posts,
		];
	}

	/**
	 * @param array<string, mixed> $args
	 * @return array<File>
	 */
	public function all( $args = [] ) {
		$args['posts_per_page'] = -1;
		$result                 = $this->query( $args );
		return $result['files'];
	}

	/**
	 * @param array<\WP_Post> $posts
	 * @return array<File>
	 */
	private function convert_posts_to_files( $posts ) {
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
	 * @param int $file_id
	 * @return bool
	 */
	public function exists( $file_id ) {
		return null !== $this->find( $file_id );
	}
}
