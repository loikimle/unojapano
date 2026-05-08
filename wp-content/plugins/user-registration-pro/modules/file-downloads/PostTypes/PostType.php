<?php

namespace WPEverest\URM\Pro\FileDownloads\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PostType {

	/**
	 * @var string
	 */
	public const FILE = 'urfd_file';

	private function __construct() {}

	/**
	 * @return array<string>
	 */
	public static function all() {
		return [
			self::FILE,
		];
	}

	/**
	 * @param string $post_type
	 * @return bool
	 */
	public static function is_valid( $post_type ) {
		return in_array( $post_type, self::all(), true );
	}
}
