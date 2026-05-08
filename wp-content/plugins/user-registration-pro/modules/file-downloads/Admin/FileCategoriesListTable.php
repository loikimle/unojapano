<?php

namespace WPEverest\URM\Pro\FileDownloads\Admin;

use WPEverest\URM\Pro\FileDownloads\Models\FileCategory;
use WPEverest\URM\Pro\FileDownloads\Repositories\FileCategoryRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FileCategoriesListTable extends BaseListTable {

	/**
	 * @var array<int, FileCategory>
	 */
	public $items = [];

	public function __construct() {
		$this->sort_by = [
			'name'  => [ 'name', false ],
			'count' => [ 'count', false ],
		];
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_screen() {
		return 'categories';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_page_slug() {
		return 'user-registration-file-downloads&screen=categories';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_per_page_option() {
		return 'user_registration_file_downloads_categories_per_page';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_addnew_action() {
		return 'add_new_category';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name() {
		return 'category';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name() {
		return 'categories';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_page_title() {
		return __( 'All Categories', 'user-registration' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_form_id() {
		return 'category-downloads-list';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_search_input_id() {
		return 'category-list-search-input';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_checkbox_name() {
		return 'category';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_no_items_message() {
		return __( 'No categories found.', 'user-registration' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_orderby() {
		return 'name';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_order() {
		return 'ASC';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_items_per_page_key() {
		return 'categories_per_page';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_item_id( $row ) {
		return $row instanceof FileCategory ? $row->get_id() : $row->term_id;
	}

	/**
	 * @return array<string, string>
	 */
	public function get_columns() {
		return [
			'cb'     => '<input type="checkbox" />',
			'name'   => __( 'Name', 'user-registration' ),
			'parent' => __( 'Parent', 'user-registration' ),
			'count'  => __( 'Count', 'user-registration' ),
		];
	}

	/**
	 * @return void
	 */
	public function prepare_items() {
		$this->prepare_column_headers();

		$per_page     = $this->get_items_per_page( $this->get_items_per_page_key(), 20 );
		$current_page = $this->get_pagenum();
		$orderby      = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : $this->get_default_orderby();
		$order        = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : $this->get_default_order();

		$orderby_map = [
			'name'  => 'name',
			'slug'  => 'slug',
			'count' => 'count',
		];

		$query_orderby = $orderby_map[ $orderby ] ?? $this->get_default_orderby();

		$repository = new FileCategoryRepository();

		$args = [
			'number'  => $per_page,
			'paged'   => $current_page,
			'orderby' => $query_orderby,
			'order'   => $order,
		];

		$result = $repository->query( $args );

		$this->items = $result['categories'];

		$this->set_pagination_args(
			[
				'total_items' => $result['total'],
				'per_page'    => $per_page,
			]
		);
	}

	/**
	 * @param FileCategory $category
	 * @return string
	 */
	public function column_description( $category ) {
		return esc_html( $category->get_description() );
	}

	/**
	 * @param FileCategory $category
	 * @return string
	 */
	public function column_slug( $category ) {
		return esc_html( $category->get_slug() );
	}

	/**
	 * @param FileCategory $category
	 * @return string
	 */
	public function column_parent( $category ) {
		$parent = $category->get_parent();
		if ( $parent ) {
			return esc_html( $parent->get_name() );
		}
		return '—';
	}

	/**
	 * @param FileCategory $category
	 * @return string
	 */
	public function column_count( $category ) {
		return (string) $category->get_count();
	}
}
