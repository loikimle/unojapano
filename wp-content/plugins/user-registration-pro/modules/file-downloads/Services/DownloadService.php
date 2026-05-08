<?php

namespace WPEverest\URM\Pro\FileDownloads\Services;

use WPEverest\URM\Pro\FileDownloads\Exceptions\DownloadLimitExceededException;
use WPEverest\URM\Pro\FileDownloads\Exceptions\FileNotFoundException;
use WPEverest\URM\Pro\FileDownloads\Exceptions\AccessDeniedException;
use WPEverest\URM\Pro\FileDownloads\Models\File;
use WPEverest\URM\Pro\FileDownloads\Repositories\FileRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DownloadService {

	/**
	 * @var FileRepository
	 */
	private $file_repository;

	/**
	 * @var AccessControlService
	 */
	private $access_control;

	/**
	 * @var FileStorageService
	 */
	private $file_storage;

	/**
	 * Constructor.
	 *
	 * @param FileRepository       $file_repository
	 * @param AccessControlService $access_control
	 * @param FileStorageService   $file_storage
	 */
	public function __construct(
		FileRepository $file_repository,
		AccessControlService $access_control,
		FileStorageService $file_storage
	) {
		$this->file_repository = $file_repository;
		$this->access_control  = $access_control;
		$this->file_storage    = $file_storage;
	}

	/**
	 * @param int|File $file_or_id
	 * @param int|null $user_id
	 * @return void
	 * @throws FileNotFoundException
	 * @throws AccessDeniedException
	 * @throws DownloadLimitExceededException
	 */
	public function download( $file_or_id, $user_id = null ) {
		$file = $file_or_id instanceof File ? $file_or_id : $this->file_repository->find( (int) $file_or_id );

		if ( ! $file ) {
			throw new FileNotFoundException( esc_html__( 'File not found.', 'user-registration' ) );
		}

		if ( empty( $file->get_access_rules() ) ) {
			throw new AccessDeniedException( esc_html__( 'File access rules not set.', 'user-registration' ) );
		}

		$this->access_control->can_access( $file );

		$this->check_download_limits( $file, $user_id );

		$file_path = $file->get_file_path();

		if ( empty( $file_path ) || ! $this->file_storage->file_exists( $file_path ) ) {
			throw new FileNotFoundException( esc_html__( 'File not found on server.', 'user-registration' ) );
		}

		$file->increment_download_count();
		if ( $user_id ) {
			$file->increment_user_download_count( $user_id );
		}

		$this->serve_file( $file_path, $file->get_file_mime_type() );
	}

	/**
	 * @param File   $file
	 * @param int|null $user_id
	 * @return void
	 * @throws DownloadLimitExceededException
	 */
	private function check_download_limits( $file, $user_id = null ) {
		$total_limit = $file->get_download_limit_total();
		if ( $total_limit > 0 && $file->get_download_count() >= $total_limit ) {
			throw new DownloadLimitExceededException( esc_html__( 'Download limit for this file has been reached.', 'user-registration' ) );
		}

		if ( $user_id && $user_id > 0 ) {
			$per_user_limit = $file->get_download_limit_per_user();
			if ( $per_user_limit > 0 ) {
				$user_download_count = $file->get_user_download_count( $user_id );
				if ( $user_download_count >= $per_user_limit ) {
					throw new DownloadLimitExceededException( esc_html__( 'Your download limit for this file has been reached.', 'user-registration' ) );
				}
			}
		}
	}

	/**
	 * @param string $file_path
	 * @param string $mime_type
	 */
	private function serve_file( $file_path, $mime_type ) {
		$file_path = realpath( $file_path );

		if ( ! $file_path || ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			throw new FileNotFoundException( esc_html__( 'File not found on server.', 'user-registration' ) );
		}

		$file_name        = basename( $file_path );
		$file_name        = sanitize_file_name( $file_name );
		$file_size        = filesize( $file_path );
		$mime_type        = ! empty( $mime_type ) ? sanitize_mime_type( $mime_type ) : 'application/octet-stream';
		$encoded_filename = rawurlencode( $file_name );
		$utf8_filename    = "filename*=UTF-8''" . $encoded_filename;

		nocache_headers();
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: ' . $mime_type );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"; ' . $utf8_filename );
		header( 'Content-Length: ' . $file_size );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: bytes' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		if ( isset( $_SERVER['HTTP_RANGE'] ) ) {
			$this->serve_range_request( $file_path, $file_size, $mime_type );
		} else {
			readfile( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		}
		exit;
	}

	/**
	 * @param string $file_path
	 * @param int $file_size
	 * @return bool
	 */
	private function serve_range_request( $file_path, $file_size, $mime_type = 'application/octet-stream' ) {
		if ( empty( $_SERVER['HTTP_RANGE'] ) ) {
			return false;
		}

		$range = sanitize_text_field( wp_unslash( $_SERVER['HTTP_RANGE'] ) );

		if ( ! preg_match( '/^bytes=(\d*)-(\d*)$/', $range, $matches ) ) {
			header( 'HTTP/1.1 416 Range Not Satisfiable' );
			header( 'Content-Range: bytes */' . $file_size );
			exit;
		}

		$start = ! empty( $matches[1] ) ? intval( $matches[1] ) : 0;
		$end   = ! empty( $matches[2] ) ? intval( $matches[2] ) : $file_size - 1;

		if ( $start < 0 || $start > $end || $start >= $file_size || $end >= $file_size ) {
			header( 'HTTP/1.1 416 Range Not Satisfiable' );
			header( 'Content-Range: bytes */' . $file_size );
			exit;
		}

		$length = $end - $start + 1;

		header( 'HTTP/1.1 206 Partial Content' );
		header( 'Content-Type: ' . $mime_type );
		header( 'Content-Range: bytes ' . $start . '-' . $end . '/' . $file_size );
		header( 'Content-Length: ' . $length );
		header( 'Accept-Ranges: bytes' );
		header( 'X-Content-Type-Options: nosniff' );
		$fp = fopen( $file_path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		if ( false === $fp ) {
			throw new \Exception( esc_html__( 'Error reading file.', 'user-registration' ) );
		}

		if ( fseek( $fp, $start ) === -1 ) {
			fclose( $fp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			throw new \Exception( esc_html__( 'Error seeking file position.', 'user-registration' ) );
		}

		$buffer_size = 8192;
		$bytes_sent  = 0;

		while ( ! feof( $fp ) && $bytes_sent < $length && ! connection_aborted() ) {
			$chunk_size = min( $buffer_size, $length - $bytes_sent );
			$data       = fread( $fp, $chunk_size ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread

			if ( false === $data ) {
				break;
			}
			echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( ob_get_level() ) {
				ob_flush();
			}
			flush();
			$bytes_sent += strlen( $data );
		}
		fclose( $fp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		return true;
	}
}
