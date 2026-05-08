<?php

namespace WPEverest\URM\Pro\FileDownloads;

use WPEverest\URM\Pro\FileDownloads\Models\File;
use WPEverest\URM\Pro\FileDownloads\PostTypes\PostType;
use WPEverest\URM\Pro\FileDownloads\Repositories\FileRepository;
use WPEverest\URM\Pro\FileDownloads\Services\ContentRulesIntegrationService;

class MyAccount {

	public function init_hooks() {
		add_action( 'init', [ $this,'add_rewrite_endpoint' ] );
		add_filter( 'user_registration_account_menu_items', [ $this, 'add_account_menu_items' ] );
		add_filter( 'user_registration_get_query_vars', [ $this, 'add_query_vars' ] );
		add_action( 'user_registration_account_file-downloads_endpoint', [ $this, 'render_file_downloads_my_account_content' ] );
		add_filter( 'user_registration_endpoint_file-downloads_title', [ $this, 'update_file_download_endpoint_title' ] );

		add_action( 'save_post_urfd_file', [ $this, 'clear_all_user_file_caches' ] );
		add_action( 'delete_post', [ $this, 'clear_all_user_file_caches' ] );
		add_action( 'urcr_access_rule_saved', [ $this, 'clear_all_user_file_caches' ] );
		add_action( 'ur_membership_status_changed', [ $this, 'clear_user_file_cache' ], 10, 2 );
	}

	public function add_account_menu_items( $items ) {
		$index = array_search( 'user-logout', array_keys( $items ), true );
		if ( false !== $index ) {
			$items = array_merge(
				array_slice( $items, 0, $index ),
				[ 'file-downloads' => __( 'File Downloads', 'user-registration' ) ],
				array_slice( $items, $index )
			);
		}
		return $items;
	}

	public function add_query_vars( $vars ) {
		$vars['file-downloads'] = 'file-downloads';
		$vars['paged']          = 'paged';
		return $vars;
	}

	public function add_rewrite_endpoint() {
		$mask = UR()->query->get_endpoints_mask();
		add_rewrite_endpoint( 'file-downloads', $mask );
		add_rewrite_rule(
			'file-downloads/page/([0-9]+)/?$',
			'index.php?file-downloads=1&paged=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'([^/]+)/file-downloads/page/([0-9]+)/?$',
			'index.php?pagename=$matches[1]&file-downloads=1&paged=$matches[2]',
			'top'
		);
		flush_rewrite_rules();
	}

	public function update_file_download_endpoint_title() {
		return __( 'File Downloads', 'user-registration' );
	}

	public function render_file_downloads_my_account_content() {
		$paged = get_query_var( 'paged', 0 );

		if ( ! $paged ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Pagination parameter, no action performed.
			$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		}

		$paged    = max( 1, absint( $paged ) );
		$per_page = apply_filters( 'urfd_my_account_files_per_page', 10 );

		$result = $this->get_user_accessible_files( $paged, $per_page );

		if ( empty( $result['files'] ) ) {
			echo '<p>' . esc_html__( 'No files available for download.', 'user-registration' ) . '</p>';
			return;
		}

		$this->render_files_list( $result['files'] );
		$this->render_pagination( $paged, $result['max_pages'] );
	}

	/**
	 * @param array<int, File> $files
	 * @return void
	 */
	private function render_files_list( $files ) {
		$ids       = array_map(
			function ( $file ) {
				return $file->get_id();
			},
			$files
		);
		$shortcode = sprintf( '[urm_files ids="%s" show_file_size="true" show_file_type="true"]', implode( ',', $ids ) );
		echo do_shortcode( $shortcode );
	}

	/**
	 * @param int $current_page
	 * @param int $max_pages
	 * @return void
	 */
	private function render_pagination( $current_page, $max_pages ) {
		if ( $max_pages <= 1 ) {
			return;
		}

		$base_url            = ur_get_page_permalink( 'my-account' );
		$permalink_structure = get_option( 'permalink_structure' );

		if ( empty( $permalink_structure ) ) {
			$format = '&paged=%#%';
			$base   = add_query_arg(
				[
					'file-downloads' => '',
					'paged'          => '%#%',
				],
				$base_url
			);
		} else {
			$format = 'page/%#%/';
			$base   = trailingslashit( $base_url . 'file-downloads/' ) . '%_%';
		}

		$pagination_args = [
			'base'      => $base,
			'format'    => $format,
			'current'   => $current_page,
			'total'     => $max_pages,
			'prev_text' => __( '&laquo; Previous', 'user-registration' ),
			'next_text' => __( 'Next &raquo;', 'user-registration' ),
			'type'      => 'list',
		];

		?>
		<nav class="urfd-pagination">
		<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() already escapes output.
			echo paginate_links( $pagination_args );
		?>
		</nav>
		<?php
	}

	/**
	 * @param int $paged
	 * @param int $per_page
	 * @return array{files: array, total: int, max_pages: int}
	 */
	private function get_user_accessible_files( $paged = 1, $per_page = 10 ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return [
				'files'     => [],
				'total'     => 0,
				'max_pages' => 0,
			];
		}

		$paged    = max( 1, (int) $paged );
		$per_page = max( 1, (int) $per_page );

		$cache_version    = get_option( '_urfd_file_cache_version', 0 );
		$cache_key        = 'urfd_accessible_files_' . $user_id . '_v' . $cache_version;
		$accessible_files = wp_cache_get( $cache_key );

		if ( false === $accessible_files ) {
			$accessible_files = $this->query_accessible_files_for_user( $user_id );
			wp_cache_set( $cache_key, $accessible_files, '', HOUR_IN_SECONDS );
		}

		$total_accessible = count( $accessible_files );
		$max_pages        = ceil( $total_accessible / $per_page );
		$offset           = ( $paged - 1 ) * $per_page;
		$paged_files      = array_slice( $accessible_files, $offset, $per_page );

		return [
			'files'     => $paged_files,
			'total'     => $total_accessible,
			'max_pages' => (int) $max_pages,
		];
	}

	/**
	 * @param int $user_id
	 * @return array<File>
	 */
	private function query_accessible_files_for_user( $user_id ) {
		$file_repository       = new FileRepository();
		$content_rules_service = new ContentRulesIntegrationService();

		$accessible_files = [];
		$batch_size       = 50;
		$current_page     = 1;
		$has_more         = true;

		while ( $has_more ) {
			$query_result = $file_repository->query(
				[
					'posts_per_page' => $batch_size,
					'paged'          => $current_page,
					'orderby'        => 'title',
					'order'          => 'DESC',
				]
			);

			if ( empty( $query_result['files'] ) ) {
				$has_more = false;
				break;
			}

			foreach ( $query_result['files'] as $file ) {
				if ( $content_rules_service->user_has_membership_access_to_file( $file, $user_id ) ) {
					$accessible_files[] = $file;
				}
			}

			$has_more = count( $query_result['files'] ) === $batch_size;
			++$current_page;
		}

		return $accessible_files;
	}

	/**
	 * @param int $user_id
	 * @param int $membership_id
	 * @return void
	 */
	public function clear_user_file_cache( $user_id, $membership_id = null ) {
		$cache_version = get_option( '_urfd_file_cache_version', 0 );
		$cache_key     = 'urfd_accessible_files_' . $user_id . '_v' . $cache_version;
		wp_cache_delete( $cache_key );
	}

	/**
	 * @return void
	 */
	public function clear_all_user_file_caches( $post_id ) {
		if ( PostType::FILE !== get_post_type( $post_id ) ) {
			return;
		}
		$version = get_option( '_urfd_file_cache_version', 0 );
		update_option( '_urfd_file_cache_version', $version + 1 );
		wp_cache_flush();
	}
}
