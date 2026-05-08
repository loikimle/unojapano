<?php

namespace WPEverest\URM\Pro\FileDownloads\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
// phpcs:disable WordPress.WP.AlternativeFunctions

/**
 * FileStorageService class for managing file uploads and downloads.
 */
class FileStorageService {

	/**
	 * Directory name for storing protected files.
	 *
	 * @var string
	 */
	private const PROTECTED_DIR = 'user-registration'; // phpcs:ignore

	/**
	 * WordPress filesystem API instance.
	 *
	 * @var \WP_Filesystem_Base|null
	 */
	private $filesystem;

	/**
	 * Initializes the WordPress filesystem API.
	 *
	 * @return bool
	 */
	private function init_filesystem() {
		if ( null !== $this->filesystem ) {
			return true;
		}

		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem( false, '', false ) ) {
			if ( ! WP_Filesystem() ) {
				return false;
			}
		}

		$this->filesystem = $wp_filesystem;
		return true;
	}

	/**
	 * Gets the path to the protected directory.
	 *
	 * @return string
	 */
	public function get_protected_dir() {
		$upload_dir    = wp_upload_dir();
		$protected_dir = trailingslashit( $upload_dir['basedir'] ) . self::PROTECTED_DIR;
		if ( ! file_exists( $protected_dir ) ) {
			wp_mkdir_p( $protected_dir );
			$this->protect_directory( $protected_dir );
		}
		return $protected_dir;
	}

	/**
	 * Gets the URL to the protected directory.
	 *
	 * @return string
	 */
	public function get_protected_dir_url() {
		$upload_dir = wp_upload_dir();
		return trailingslashit( $upload_dir['baseurl'] ) . self::PROTECTED_DIR;
	}

	/**
	 * Gets the path to a file within the protected directory.
	 *
	 * @param string $filename File name.
	 * @return string
	 */
	public function get_file_path( $filename ) {
		return trailingslashit( $this->get_protected_dir() ) . $filename;
	}

	/**
	 * Generates a unique filename for a file.
	 *
	 * @param string $original_filename Original file name.
	 * @return string
	 */
	public function generate_unique_filename( $original_filename ) {
		$pathinfo  = pathinfo( $original_filename );
		$extension = isset( $pathinfo['extension'] ) ? '.' . $pathinfo['extension'] : '';
		$basename  = sanitize_file_name( $pathinfo['filename'] );
		$basename  = remove_accents( $basename );
		$basename  = preg_replace( '/[^a-zA-Z0-9\-_]/', '-', $basename );
		$basename  = preg_replace( '/-+/', '-', $basename );
		$basename  = trim( $basename, '-_' );

		if ( empty( $basename ) ) {
			$basename = 'file';
		}

		$protected_dir = $this->get_protected_dir();

		$max_attempts = 10;
		$attempt      = 0;

		do {
			$unique_id = wp_generate_password( 12, false );
			$filename  = $basename . '-' . $unique_id . $extension;
			$file_path = trailingslashit( $protected_dir ) . $filename;
			++$attempt;
		} while ( file_exists( $file_path ) && $attempt < $max_attempts );

		if ( file_exists( $file_path ) ) {
			$timestamp = time();
			$filename  = $basename . '-' . $unique_id . '-' . $timestamp . $extension;
		}

		return $filename;
	}

	/**
	 * Moves an uploaded file to the protected directory.
	 *
	 * @param string $tmp_file Temporary file.
	 * @param string $filename Filename.
	 * @return string|false
	 */
	public function move_uploaded_file( $tmp_file, $filename ) {
		$unique_filename = $this->generate_unique_filename( $filename );
		$target_path     = $this->get_file_path( $unique_filename );

		$tmp_file    = wp_normalize_path( $tmp_file );
		$target_path = wp_normalize_path( $target_path );

		if ( ! file_exists( $tmp_file ) ) {
			return false;
		}

		$this->get_protected_dir();

		if ( $this->init_filesystem() && $this->filesystem ) {
			$moved = $this->filesystem->move( $tmp_file, $target_path, false );
			if ( $moved ) {
				$this->set_file_permissions( $target_path );
				return $target_path;
			}
		}

		$moved = false;

		if ( is_uploaded_file( $tmp_file ) ) {
			$moved = @move_uploaded_file( $tmp_file, $target_path );
		}

		if ( ! $moved && file_exists( $tmp_file ) ) {
			$moved = @rename( $tmp_file, $target_path );
		}

		if ( ! $moved && file_exists( $tmp_file ) ) {
			$moved = @copy( $tmp_file, $target_path );
			if ( $moved ) {
				@unlink( $tmp_file );
			}
		}

		if ( $moved ) {
			$this->set_file_permissions( $target_path );
			return $target_path;
		}

		return false;
	}

	/**
	 * Sets file permissions.
	 *
	 * @param string $path File path.
	 * @param bool   $is_dir Is directory flag.
	 * @return bool
	 */
	private function set_file_permissions( $path, $is_dir = false ) {
		if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
			return true;
		}

		if ( file_exists( $path ) ) {
			$perms = $is_dir ? 0755 : 0644;
			return @chmod( $path, $perms );
		}

		return false;
	}

	/**
	 * Deletes a file from the protected directory.
	 *
	 * @param string $file_path File path.
	 * @return bool
	 */
	public function delete_file( $file_path ) {
		$protected_dir = $this->get_protected_dir();

		if ( strpos( $file_path, $protected_dir ) !== 0 ) {
			return false;
		}

		if ( $this->init_filesystem() && $this->filesystem ) {
			return $this->filesystem->delete( $file_path );
		}

		if ( file_exists( $file_path ) ) {
			return @unlink( $file_path );
		}

		return false;
	}

	/**
	 * Deletes files with a specific prefix from the protected directory.
	 *
	 * @param string $original_filename Original filename.
	 * @return int Number of deleted files.
	 */
	public function delete_files_with_prefix( $original_filename ) {
		$protected_dir = $this->get_protected_dir();
		$pathinfo      = pathinfo( $original_filename );
		$basename      = sanitize_file_name( $pathinfo['filename'] );
		$extension     = isset( $pathinfo['extension'] ) ? '.' . $pathinfo['extension'] : '';

		$pattern      = $basename . '_*' . $extension;
		$pattern_path = trailingslashit( $protected_dir ) . $pattern;

		$deleted_count = 0;

		if ( $this->init_filesystem() && $this->filesystem ) {
			$files = glob( $pattern_path );
			if ( is_array( $files ) ) {
				foreach ( $files as $file ) {
					if ( $this->filesystem->delete( $file ) ) {
						++$deleted_count;
					}
				}
			}
		} else {
			$files = glob( $pattern_path );
			if ( is_array( $files ) ) {
				foreach ( $files as $file ) {
					if ( file_exists( $file ) && @unlink( $file ) ) {
						++$deleted_count;
					}
				}
			}
		}

		return $deleted_count;
	}

	/**
	 * Checks if a file exists in the protected directory.
	 *
	 * @param string $file_path File path.
	 * @return bool
	 */
	public function file_exists( $file_path ) {
		$protected_dir = $this->get_protected_dir();
		$protected_dir = wp_normalize_path( $protected_dir );

		if ( strpos( $file_path, $protected_dir ) !== 0 ) {
			return false;
		}

		return file_exists( $file_path );
	}

	/**
	 * Gets the size of a file in the protected directory.
	 *
	 * @param string $file_path File path.
	 * @return int|false
	 */
	public function get_file_size( $file_path ) {
		if ( ! $this->file_exists( $file_path ) ) {
			return false;
		}
		return filesize( $file_path );
	}

	/**
	 * Gets the MIME type of a file in the protected directory.
	 *
	 * @param string $file_path File path.
	 * @return string|false
	 */
	public function get_file_mime_type( $file_path ) {
		if ( ! $this->file_exists( $file_path ) ) {
			return false;
		}

		$mime_type = wp_check_filetype( $file_path );

		if ( ! empty( $mime_type['type'] ) ) {
			return $mime_type['type'];
		}

		if ( function_exists( 'finfo_file' ) ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			$mime  = finfo_file( $finfo, $file_path );
			finfo_close( $finfo ); // phpcs:ignore
			return $mime ?: 'application/octet-stream'; // phpcs:ignore
		}

		return 'application/octet-stream';
	}

	/**
	 * Protect directory from direct access.
	 *
	 * @param string $dir Directory path.
	 * @return void
	 */
	private function protect_directory( $dir ) {
		$this->init_filesystem();

		$index_file    = trailingslashit( $dir ) . 'index.php';
		$index_content = "<?php\n// Silence is golden.\n";

		if ( $this->filesystem ) {
			if ( ! $this->filesystem->exists( $index_file ) ) {
				$this->filesystem->put_contents( $index_file, $index_content, 0644 );
			}
		} elseif ( ! file_exists( $index_file ) ) {
			file_put_contents( $index_file, $index_content );
		}

		$htaccess_file     = trailingslashit( $dir ) . '.htaccess';
		$htaccess_content  = "# Deny direct access to all files in this directory\n";
		$htaccess_content .= "# Files can only be accessed through the plugin's download service\n";
		$htaccess_content .= "# Works on: Apache, LiteSpeed, OpenLiteSpeed\n";
		$htaccess_content .= "# Note: Nginx requires separate configuration in server block\n";
		$htaccess_content .= "<IfModule mod_authz_core.c>\n";
		$htaccess_content .= "    # Apache 2.4+ / LiteSpeed\n";
		$htaccess_content .= "    Require all denied\n";
		$htaccess_content .= "</IfModule>\n";
		$htaccess_content .= "<IfModule !mod_authz_core.c>\n";
		$htaccess_content .= "    # Apache 2.2\n";
		$htaccess_content .= "    Order Deny,Allow\n";
		$htaccess_content .= "    Deny from all\n";
		$htaccess_content .= "</IfModule>\n";

		if ( $this->filesystem ) {
			if ( ! $this->filesystem->exists( $htaccess_file ) ) {
				$this->filesystem->put_contents( $htaccess_file, $htaccess_content, 0644 );
			}
		} elseif ( ! file_exists( $htaccess_file ) ) {
			file_put_contents( $htaccess_file, $htaccess_content );
		}
	}
}
