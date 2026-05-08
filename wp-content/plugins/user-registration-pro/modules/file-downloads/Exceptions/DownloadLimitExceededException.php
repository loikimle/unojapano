<?php

namespace WPEverest\URM\Pro\FileDownloads\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DownloadLimitExceededException extends FileDownloadException {}
