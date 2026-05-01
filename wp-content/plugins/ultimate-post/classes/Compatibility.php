<?php
/**
 * Compatibility Action.
 *
 * @package ULTP\Compatibility
 * @since v.1.1.0
 */

namespace ULTP;

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility class.
 *
 * Handles compatibility with other plugins and themes for Ultimate Post.
 *
 * @package ULTP\Compatibility
 * @since 4.0.0
 */
class Compatibility {

	/**
	 * Setup class.
	 *
	 * @since v.1.1.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handle_front_page_builder' ) );

		// PublishPress Revisions Plugin Compatibility Add
		add_action( 'revisionary_copy_postmeta', array( $this, 'ultp_revisionary_copy_postmeta_callback' ), 10, 3 );
	}

	/**
	 * Compatibility for Front Page Builder
	 *
	 * @since 4.1.12
	 */
	public function handle_front_page_builder() {

		if ( get_option( 'ultp_frontpage_builder_comp' ) != 'yes' ) {
			$builder_condition = get_option( 'ultp_builder_conditions', array() );
			if ( ! empty( $builder_condition ) &&
				! empty( $builder_condition['singular'] )
			) {
				$f_pages = array();
				foreach ( $builder_condition['singular'] as $id => $paths ) {
					if ( in_array( 'include/singular/front_page', $paths ) ) {
						$f_pages[ $id ] = array( 'include/front_page' ); // Add the ID to the list
						update_post_meta( $id, '__ultp_builder_type', 'front_page' );
						unset( $builder_condition['singular'][ $id ] );
					}
				}
				if ( empty( $builder_condition['front_page'] ) ) {
					$builder_condition['front_page'] = $f_pages;
				} else {
					$builder_condition['front_page'] = $builder_condition['front_page'] + $f_pages;
				}
				update_option( 'ultp_frontpage_builder_comp', 'yes' );
				update_option( 'ultp_builder_conditions', $builder_condition );
			}
		}
	}

	/**
	 * Compatibility for PublishPress Revisions Plugin
	 *
	 * @url https://wordpress.org/plugins/revisionary/
	 *
	 * @since v.2.9.8
	 *
	 * @param MIXED $from_post The source post object or ID.
	 * @param INT   $to_post_id The destination post ID.
	 * @param ARRAY $args Additional arguments.
	 */
	public function ultp_revisionary_copy_postmeta_callback( $from_post, $to_post_id, $args ) {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$css_meta           = get_post_meta( $to_post_id, '_ultp_css', true );
		$upload_dir_url     = wp_get_upload_dir();
		$upload_css_dir_url = trailingslashit( $upload_dir_url['basedir'] );
		$css_dir_path       = $upload_css_dir_url . "ultimate-post/ultp-css-{$to_post_id}.css";

		if ( file_exists( $css_dir_path ) ) {
			$css = $wp_filesystem->get_contents( $css_dir_path );
			if ( $css_meta != $css ) {
				$wp_filesystem->put_contents( $css_dir_path, $css_meta );
			}
		}
	}
}
