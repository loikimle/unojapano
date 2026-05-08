<?php

namespace WPEverest\URM\Pro\FileDownloads\Admin;

use WPEverest\URM\Pro\FileDownloads\Meta\MetaKeys;
use WPEverest\URM\Pro\FileDownloads\Models\File;
use WPEverest\URM\Pro\FileDownloads\PostTypes\PostType;
use WPEverest\URM\Pro\FileDownloads\Repositories\FileRepository;
use WPEverest\URM\Pro\FileDownloads\Services\MembershipIntegrationService;
use WPEverest\URM\Pro\FileDownloads\Taxonomies\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FilesListTable extends BaseListTable {

	/**
	 * @var array<int, File>
	 */
	public $items = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->sort_by = [
			'name'      => [ 'name', false ],
			'downloads' => [ 'downloads', false ],
		];
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_screen() {
		return 'files';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_page_slug(): string {
		return 'user-registration-file-downloads';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_per_page_option(): string {
		return 'user_registration_file_downloads_per_page';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_addnew_action(): string {
		return 'add_new_file';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name(): string {
		return 'file';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name(): string {
		return 'files';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_page_title(): string {
		return __( 'All Files', 'user-registration' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_form_id(): string {
		return 'file-downloads-list';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_search_input_id(): string {
		return 'membership-list-search-input';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_checkbox_name(): string {
		return 'file';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_no_items_message(): string {
		return __( 'No files found.', 'user-registration' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_orderby(): string {
		return 'date';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_order(): string {
		return 'DESC';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_items_per_page_key(): string {
		return 'files_per_page';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_item_id( $row ): int {
		return $row instanceof File ? $row->get_id() : $row->ID;
	}

	/**
	 * Get list columns.
	 *
	 * @return array<string, string>
	 */
	public function get_columns() {
		return [
			'cb'         => '<input type="checkbox" />',
			'name'       => __( 'Name', 'user-registration' ),
			'access'     => __( 'Access Rule', 'user-registration' ),
			'categories' => __( 'Categories', 'user-registration' ),
			'downloads'  => __( 'Downloads', 'user-registration' ),
		];
	}

	/**
	 * Prepare items.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$this->prepare_column_headers();

		$per_page      = $this->get_items_per_page( $this->get_items_per_page_key(), 20 );
		$current_page  = $this->get_pagenum();
		$orderby       = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : $this->get_default_orderby();
		$order         = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : $this->get_default_order();
		$category_name = isset( $_GET['category_name'] ) ? sanitize_text_field( wp_unslash( $_GET['category_name'] ) ) : null;

		$orderby_map = [
			'name'           => 'title',
			'title'          => 'title',
			'date'           => 'date',
			'downloads'      => 'meta_value_num',
			'download_count' => 'meta_value_num',
		];

		$query_orderby = $orderby_map[ $orderby ] ?? $this->get_default_orderby();

		$repository = new FileRepository();

		$args = [
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
			'orderby'        => $query_orderby,
			'order'          => $order,
		];

		if ( 'downloads' === $orderby || 'download_count' === $orderby ) {
			$args['meta_key'] = MetaKeys::DOWNLOAD_COUNT;
		}

		if ( $category_name ) {
			$args['tax_query'] = [
				[
					'taxonomy' => Taxonomy::FILE_CATEGORY,
					'field'    => 'slug',
					'terms'    => [ $category_name ],
				],
			];
		}

		$result = $repository->query( $args );

		$this->items = $result['files'];

		$this->set_pagination_args(
			[
				'total_items' => $result['total'],
				'per_page'    => $per_page,
			]
		);
	}

	/**
	 * @param File $file
	 * @return string
	 */
	public function column_access( $file ) {
		$access_rules = $file->get_access_rules();
		if ( ! empty( $access_rules ) ) {
			$html = '';
			foreach ( $access_rules as $id => $rule ) {
				$access_type   = $rule['access_control'] ?? 'access';
				$conditions    = $rule['logic_map']['conditions'] ?? [];
				$prepared_html = '<a style="display: inline-block; padding: 2px 6px; border-radius: 2px; font-weight: 400; font-size: 11px; margin-right: 4px; margin-top: 4px; %s" class="access access--%s" href="%s">%s</a>';
				if ( 1 === count( $conditions ) && 'membership' === ( $conditions[0]['type'] ?? '' ) ) {
					$membership_id = $conditions[0]['value'][0] ?? 0;
					if ( $membership_id > 0 ) {
						$membership = get_post( $membership_id );
						if ( $membership ) {
							$title = ! empty( $membership->post_title ) ? $membership->post_title : '#' . $membership_id;
							$html .= sprintf(
								$prepared_html,
								'access' === $access_type ? 'background: #e6f4ea; color: #1e7e34;' : 'background: #fcebea;color: #cc1f1a;',
								$access_type,
								admin_url( 'admin.php?page=user-registration-content-restriction&focus=' . $id ),
								__( 'Membership', 'user-registration' ) . ': ' . $title,
							);
						}
					}
				} else {
					$content_rule = get_post( $id );
					$title        = $content_rule && ! empty( $content_rule->post_title ) ? $content_rule->post_title : '#' . $id;
					$html        .= sprintf(
						$prepared_html,
						'access' === $access_type ? 'background: #e6f4ea; color: #1e7e34;' : 'background: #fcebea;color: #cc1f1a;',
						$access_type,
						admin_url( 'admin.php?page=user-registration-content-restriction&focus=' . $id ),
						__( 'Content Rule', 'user-registration' ) . ': ' . $title,
					);
				}
			}
			return $html;
		}
		return '—';
	}

	/**
	 * @param File $file
	 * @return string
	 */
	public function column_categories( $file ) {
		$categories = $file->get_categories();

		if ( empty( $categories ) ) {
			return '—';
		}

		$names = array_map(
			function ( $category ) {
				return sprintf(
					'<a href="%s">%s</a>',
					add_query_arg(
						[
							'page'   => $this->page,
							'screen' => 'categories',
							'id'     => $category->term_id,
							'action' => 'edit',
						],
						admin_url( 'admin.php' )
					),
					$category->name
				);},
			$categories
		);
		return implode( ', ', $names );
	}

	/**
	 * @param File $file
	 * @return string
	 */
	public function column_downloads( $file ) {
		return (string) $file->get_download_count();
	}
}
