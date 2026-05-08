<?php

namespace WPEverest\URM\Pro\FileDownloads\Meta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MetaKeys {

	public const FILE_PATH               = 'urfd_file_path';
	public const FILE_SIZE               = 'urfd_file_size';
	public const FILE_NAME               = 'urfd_file_name';
	public const FILE_MIME_TYPE          = 'urfd_file_mime_type';
	public const DOWNLOAD_LIMIT          = 'urfd_download_limit';
	public const DOWNLOAD_COUNT          = 'urfd_download_count';
	public const DOWNLOAD_LIMIT_TOTAL    = 'urfd_download_limit_total';
	public const DOWNLOAD_LIMIT_PER_USER = 'urfd_download_limit_per_user';

	private function __construct() {}

	/**
	 * @return array<string>
	 */
	public static function all() {
		return [
			self::FILE_PATH,
			self::FILE_SIZE,
			self::FILE_NAME,
			self::FILE_MIME_TYPE,
			self::DOWNLOAD_COUNT,
			self::DOWNLOAD_LIMIT,
			self::DOWNLOAD_LIMIT_TOTAL,
			self::DOWNLOAD_LIMIT_PER_USER,
		];
	}

	/**
	 * @param string $base_name
	 * @return string
	 */
	public static function get_key( $base_name ) {
		$key_map = [
			'file_path'               => self::FILE_PATH,
			'file_name'               => self::FILE_NAME,
			'file_size'               => self::FILE_SIZE,
			'file_mime_type'          => self::FILE_MIME_TYPE,
			'download_count'          => self::DOWNLOAD_COUNT,
			'download_limit'          => self::DOWNLOAD_LIMIT,
			'download_limit_total'    => self::DOWNLOAD_LIMIT_TOTAL,
			'download_limit_per_user' => self::DOWNLOAD_LIMIT_PER_USER,
		];

		return $key_map[ $base_name ] ?? "urfd_$base_name";
	}
}
