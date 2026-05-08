<?php

namespace WPEverest\URM\Pro\FileDownloads\Services;

use WPEverest\URM\Pro\FileDownloads\Exceptions\AccessDeniedException;
use WPEverest\URM\Pro\FileDownloads\Models\File;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AccessControlService {

	/**
	 * @var ContentRulesIntegrationService
	 */
	private $content_rules_service;

	/**
	 * Constructor.
	 *
	 * @param ContentRulesIntegrationService $content_rules_service
	 */
	public function __construct( ContentRulesIntegrationService $content_rules_service ) {
		$this->content_rules_service = $content_rules_service;
	}

	/**
	 * Check if user can access file.
	 *
	 * @param File   $file File model.
	 * @return bool
	 * @throws AccessDeniedException If access is denied.
	 */
	public function can_access( File $file ) {
		$content_rules_access = $this->content_rules_service->check_file_access( $file );
		if ( ! $content_rules_access['access'] ) {
			$message = empty( $content_rules_access['message'] ) ? get_option( 'user_registration_content_restriction_message', '' ) : $content_rules_access['message'];
			throw new AccessDeniedException( $message );
		}
		return true;
	}
}
