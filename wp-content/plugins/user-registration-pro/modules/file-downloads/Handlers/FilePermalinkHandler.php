<?php

namespace WPEverest\URM\Pro\FileDownloads\Handlers;

use WPEverest\URM\Pro\FileDownloads\Enums\FileDownload;
use WPEverest\URM\Pro\FileDownloads\Exceptions\AccessDeniedException;
use WPEverest\URM\Pro\FileDownloads\Exceptions\DownloadLimitExceededException;
use WPEverest\URM\Pro\FileDownloads\Exceptions\FileNotFoundException;
use WPEverest\URM\Pro\FileDownloads\Models\File;
use WPEverest\URM\Pro\FileDownloads\PostTypes\PostType;
use WPEverest\URM\Pro\FileDownloads\Services\DownloadService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FilePermalinkHandler {

	/**
	 * @var DownloadService|null
	 */
	private $download_service = null;

	public function __construct( DownloadService $download_service ) {
		$this->download_service = $download_service;
	}

	/**
	 * @return void
	 */
	public function init() {
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		add_action( 'template_redirect', [ $this, 'handle_file_download' ], 1 );
	}

	/**
	 * @param array<string> $vars
	 * @return array<string>
	 */
	public function add_query_vars( $vars ) {
		$vars = array_merge( $vars, array( 'urfd_plugin', 'urfd_file', 'urfd_action' ) );
		return $vars;
	}

	/**
	 * @return void
	 */
	public function handle_file_download() {
		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'GET' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		$plugin = false;
		$action = false;
		$file   = false;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is a GET request for file download, no action performed yet.
		if ( isset( $_GET[ PostType::FILE ] ) && ! empty( $_GET[ PostType::FILE ] ) ) {
			$plugin = FileDownload::PLUGIN_SLUG;
			$action = FileDownload::ACTION_DOWNLOAD;
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is a GET request for file download, no action performed yet.
			$file = sanitize_text_field( wp_unslash( $_GET[ PostType::FILE ] ) );
		} else {
			$plugin = get_query_var( 'urfd_plugin', false );
			$action = get_query_var( 'urfd_action', false );
			$file   = get_query_var( 'urfd_file', false );
		}

		$permalink_structure = get_option( 'permalink_structure' );
		if ( empty( $permalink_structure ) ) {
			if ( empty( $file ) ) {
				return;
			}
		} elseif (
			$plugin !== FileDownload::PLUGIN_SLUG ||
			$action !== FileDownload::ACTION_DOWNLOAD ||
			empty( $file )
		) {
			return;
		}
		$file_data = explode( '/', $file );
		$post_slug = reset( $file_data );

		$post = get_page_by_path( $post_slug, OBJECT, PostType::FILE );
		if ( ! $post || PostType::FILE !== $post->post_type ) {
			$this->send_error_response( esc_html__( 'File not found.', 'user-registration' ), 404 );
		}

		$file_model = File::from_post_id( $post->ID );
		if ( ! $file_model ) {
			$this->send_error_response( esc_html__( 'File not found.', 'user-registration' ), 404 );
		}

		$actual_filename = $file_model->get_file_name();
		if ( empty( $actual_filename ) ) {
			$this->send_error_response( esc_html__( 'File name not set.', 'user-registration' ), 404 );
		}

		if ( end( $file_data ) !== pathinfo( $actual_filename, PATHINFO_FILENAME ) ) {
			$this->send_error_response( esc_html__( 'File not found.', 'user-registration' ), 404 );
		}

		status_header( 200 );

		while ( ob_get_level() ) {
			ob_end_clean();
		}

		if ( headers_sent() ) {
			$this->send_error_response( esc_html__( 'Headers already sent. Cannot serve file.', 'user-registration' ), 500 );
			exit;
		}

		$this->process_file_download( $file_model );
		exit;
	}

	/**
	 * @param int|File $file_or_id
	 * @return void
	 */
	private function process_file_download( $file_or_id ) {
		try {
			$user_id = get_current_user_id() ?: null;
			$this->download_service->download( $file_or_id, $user_id );
		} catch ( FileNotFoundException $e ) {
			$this->send_error_response( ( $e->getMessage() ), 404 );
		} catch ( AccessDeniedException $e ) {
			$this->send_error_response( ( $e->getMessage() ), 403 );
		} catch ( DownloadLimitExceededException $e ) {
			$this->send_error_response( ( $e->getMessage() ), 403 );
		} catch ( \Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'File download error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
			$this->send_error_response( __( 'An error occurred while processing your request.', 'user-registration' ), 500 );
		}
	}

	/**
	 * @param string $message
	 * @param int    $status_code
	 * @return void
	 */
	private function send_error_response( $message, $status_code = 403 ) {
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		if ( ! headers_sent() ) {
			status_header( $status_code );
			nocache_headers();
		}

		$login_page_id        = get_option( 'user_registration_login_page_id' );
		$registration_page_id = get_option( 'user_registration_member_registration_page_id' );
		$login_url            = $login_page_id ? get_permalink( $login_page_id ) : wp_login_url();
		$signup_url           = $registration_page_id ? get_permalink( $registration_page_id ) : ( $login_page_id ? get_permalink( $login_page_id ) : wp_registration_url() );
		$message              = apply_filters( 'user_registration_process_smart_tags', $message );
		remove_all_filters( 'body_class' );
		show_admin_bar( false );
		header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ), true, $status_code );
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
			<title><?php echo esc_html( wp_get_document_title() ); ?></title>
			<?php wp_head(); ?>
		</head>
		<body <?php body_class(); ?>>
			<?php
			urcr_get_template(
				'base-restriction-template.php',
				array(
					'message'    => $message,
					'login_url'  => $login_url,
					'signup_url' => $signup_url,
				)
			);
			?>
			<?php wp_footer(); ?>
		</body>
		</html>
		<?php
		exit;
	}
}
