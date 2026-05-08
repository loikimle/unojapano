<?php

namespace WPEverest\URM\Pro\FileDownloads\Enums;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FileDownload {

	public const DOWNLOAD_URL_PREFIX = 'ur-files';
	public const PLUGIN_SLUG         = 'user-registration';
	public const ACTION_DOWNLOAD     = 'download';

	private function __construct() {}
}
