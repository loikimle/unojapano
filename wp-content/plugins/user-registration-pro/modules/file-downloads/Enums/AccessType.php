<?php

namespace WPEverest\URM\Pro\FileDownloads\Enums;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AccessType {

	public const ALL        = 'all';
	public const MEMBERSHIP = 'membership';
	public const LOGGED_IN  = 'logged_in';

	private function __construct() {}

	/**
	 * @return array<string>
	 */
	public static function all() {
		return [
			self::ALL,
			self::MEMBERSHIP,
			self::LOGGED_IN,
		];
	}

	/**
	 * @param string $access_type
	 * @return bool
	 */
	public static function is_valid( $access_type ) {
		return in_array( $access_type, self::all(), true );
	}
}
