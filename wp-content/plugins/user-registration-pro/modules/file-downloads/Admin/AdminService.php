<?php

namespace WPEverest\URM\Pro\FileDownloads\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AdminService {

	/**
	 * @var Admin|null
	 */
	private static $admin_instance = null;

	public static function init() {
		if ( ! is_admin() ) {
			return;
		}
		if ( null === self::$admin_instance ) {
			self::$admin_instance = new Admin();
		}
	}

	/**
	 * @return Admin|null
	 */
	public static function get_instance() {
		return self::$admin_instance;
	}
}
