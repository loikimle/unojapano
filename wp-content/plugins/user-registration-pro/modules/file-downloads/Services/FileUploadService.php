<?php

namespace WPEverest\URM\Pro\FileDownloads\Services;

use WPEverest\URM\Pro\FileDownloads\Exceptions\FileDownloadException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FileUploadService {

	/**
	 * @var FileStorageService
	 */
	private $file_storage;

	/**
	 * @var array<string>
	 */
	private $allowed_mime_types;

	/**
	 * @var int
	 */
	private $max_file_size;

	/**
	 * @param FileStorageService $file_storage
	 */
	public function __construct( FileStorageService $file_storage ) {
		$this->file_storage       = $file_storage;
		$this->allowed_mime_types = $this->get_default_allowed_mime_types();
		$this->max_file_size      = $this->get_max_upload_size();
	}

	/**
	 * @param array<string, mixed> $file_data
	 * @throws FileDownloadException
	 * @return array<string, mixed>
	 */
	public function upload_file( array $file_data ) {
		$this->validate_upload( $file_data );

		$file_path = $this->file_storage->move_uploaded_file( $file_data['tmp_name'], $file_data['name'] );

		if ( ! $file_path ) {
			throw new FileDownloadException( esc_html__( 'Failed to move uploaded file.', 'user-registration' ) );
		}

		$file_size = $this->file_storage->get_file_size( $file_path );
		$mime_type = $this->file_storage->get_file_mime_type( $file_path );

		return [
			'file_path'      => $file_path,
			'file_size'      => $file_size,
			'file_mime_type' => $mime_type,
			'file_name'      => basename( $file_path ),
		];
	}

	/**
	 * @param array<string, mixed> $file_data
	 * @return void
	 * @throws FileDownloadException
	 */
	private function validate_upload( array $file_data ) {
		if ( ! isset( $file_data['error'] ) || UPLOAD_ERR_OK !== $file_data['error'] ) {
			$error_messages = [
				UPLOAD_ERR_INI_SIZE   => __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'user-registration' ),
				UPLOAD_ERR_FORM_SIZE  => __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'user-registration' ),
				UPLOAD_ERR_PARTIAL    => __( 'The uploaded file was only partially uploaded.', 'user-registration' ),
				UPLOAD_ERR_NO_FILE    => __( 'No file was uploaded.', 'user-registration' ),
				UPLOAD_ERR_NO_TMP_DIR => __( 'Missing a temporary folder.', 'user-registration' ),
				UPLOAD_ERR_CANT_WRITE => __( 'Failed to write file to disk.', 'user-registration' ),
				UPLOAD_ERR_EXTENSION  => __( 'A PHP extension stopped the file upload.', 'user-registration' ),
			];

			$error_code    = $file_data['error'] ?? UPLOAD_ERR_NO_FILE;
			$error_message = $error_messages[ $error_code ] ?? __( 'Unknown upload error.', 'user-registration' );

			throw new FileDownloadException( esc_html( $error_message ) );
		}

		if ( ! isset( $file_data['tmp_name'] ) || ! is_uploaded_file( $file_data['tmp_name'] ) ) {
			throw new FileDownloadException( esc_html__( 'Invalid file upload.', 'user-registration' ) );
		}

		if ( isset( $file_data['size'] ) && $file_data['size'] > $this->max_file_size ) {
			throw new FileDownloadException(
				sprintf(
					/* translators: %s: Maximum file size */
					esc_html__( 'File size exceeds maximum allowed size of %s.', 'user-registration' ),
					esc_html( size_format( $this->max_file_size ) )
				)
			);
		}

		$allowed_mime_types = apply_filters(
			'user_registration_file_downloads_allowed_mime_types',
			$this->allowed_mime_types
		);

		$wp_file_type = wp_check_filetype( $file_data['name'], $allowed_mime_types );
		if ( empty( $wp_file_type['ext'] ) || empty( $wp_file_type['type'] ) ) {
			throw new FileDownloadException( esc_html__( 'Invalid file type.', 'user-registration' ) );
		}
	}

	/**
	 * @return array<string>
	 */
	private function get_default_allowed_mime_types() {
		return get_allowed_mime_types();
	}

	/**
	 * @return int
	 */
	private function get_max_upload_size() {
		$max_size = wp_max_upload_size();
		return (int) apply_filters( 'user_registration_file_downloads_max_upload_size', $max_size );
	}
}
