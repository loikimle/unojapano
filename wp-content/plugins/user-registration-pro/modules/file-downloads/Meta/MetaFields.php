<?php

namespace WPEverest\URM\Pro\FileDownloads\Meta;

use WPEverest\URM\Pro\FileDownloads\Enums\AccessType;
use WPEverest\URM\Pro\FileDownloads\PostTypes\PostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MetaFields {

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_definitions() {
		return [
			'file_name'               => [
				'single'       => true,
				'show_in_rest' => true,
				'type'         => 'string',
				'default'      => '',
			],
			'file_path'               => [
				'single'       => true,
				'show_in_rest' => true,
				'type'         => 'string',
				'default'      => '',
			],
			'file_size'               => [
				'single'       => true,
				'show_in_rest' => true,
				'type'         => 'string',
				'default'      => '',
			],
			'file_mime_type'          => [
				'single'       => true,
				'show_in_rest' => true,
				'type'         => 'string',
				'default'      => '',
			],
			'download_count'          => [
				'single'       => true,
				'show_in_rest' => true,
				'type'         => 'integer',
				'default'      => 0,
			],
			'download_limit'          => [
				'single'       => true,
				'show_in_rest' => true,
				'type'         => 'boolean',
				'default'      => false,
			],
			'download_limit_total'    => [
				'single'       => true,
				'show_in_rest' => true,
				'type'         => 'integer',
				'default'      => 0,
			],
			'download_limit_per_user' => [
				'single'       => true,
				'show_in_rest' => true,
				'type'         => 'integer',
				'default'      => 0,
			],
		];
	}

	/**
	 * @return void
	 */
	public static function register() {
		foreach ( self::get_definitions() as $key => $args ) {
			register_meta(
				'post',
				MetaKeys::get_key( $key ),
				array_merge(
					$args,
					[
						'object_subtype' => PostType::FILE,
					]
				)
			);
		}
	}
}
