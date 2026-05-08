<?php

namespace WPEverest\URM\Pro\FileDownloads\Taxonomies;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Taxonomy {

	/**
	 * @var string
	 */
	public const FILE_CATEGORY = 'urfd_file_category';

	private function __construct() {}

	/**
	 * @return array<string>
	 */
	public static function all() {
		return [
			self::FILE_CATEGORY,
		];
	}

	/**
	 * @param string $taxonomy
	 * @return bool
	 */
	public static function is_valid( $taxonomy ) {
		return in_array( $taxonomy, self::all(), true );
	}
}
