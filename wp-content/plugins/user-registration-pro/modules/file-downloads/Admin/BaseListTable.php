<?php

namespace WPEverest\URM\Pro\FileDownloads\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}

use WPEverest\URM\Pro\FileDownloads\Models\FileCategory;
use WPEverest\URM\Pro\FileDownloads\Models\File;

/**
 * @template T
 */
abstract class BaseListTable extends \UR_List_Table {

	/**
	 * @var array<int, T>
	 */
	public $items = [];

	/**
	 * @return string
	 */
	abstract protected function get_screen();

	/**
	 * @return string
	 */
	abstract protected function get_page_slug();

	/**
	 * @return string
	 */
	abstract protected function get_per_page_option();

	/**
	 * @return string
	 */
	abstract protected function get_addnew_action();

	/**
	 * @return string
	 */
	abstract protected function get_singular_name();

	/**
	 * @return string
	 */
	abstract protected function get_plural_name();

	/**
	 * Get the page title.
	 *
	 * @return string
	 */
	abstract protected function get_page_title();

	/**
	 * Get the form ID.
	 *
	 * @return string
	 */
	abstract protected function get_form_id();

	/**
	 * @return string
	 */
	abstract protected function get_search_input_id();

	/**
	 * @return string
	 */
	abstract protected function get_checkbox_name();

	/**
	 * @return string
	 */
	abstract protected function get_no_items_message();

	/**
	 * @return string
	 */
	abstract protected function get_default_orderby();

	/**
	 * @return string
	 */
	abstract protected function get_default_order();

	/**
	 * @return string
	 */
	abstract protected function get_items_per_page_key();

	/**
	 * @param mixed $row
	 * @return int
	 */
	abstract protected function get_item_id( $row );

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
		$this->page            = $this->get_page_slug();
		$this->per_page_option = $this->get_per_page_option();
		$this->addnew_action   = $this->get_addnew_action();
		parent::__construct(
			array(
				'singular' => $this->get_singular_name(),
				'plural'   => $this->get_plural_name(),
				'ajax'     => false,
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_edit_links( $row ) {
		$id = $this->get_item_id( $row );
		return add_query_arg(
			[
				'page'   => $this->page,
				'screen' => $this->get_screen(),
				'id'     => $id,
				'action' => 'edit',
			],
			admin_url( 'admin.php' )
		);
	}

	public function get_delete_links( $row ) {
		$id = $this->get_item_id( $row );
		return wp_nonce_url(
			add_query_arg(
				[
					'page'   => $this->page,
					'screen' => $this->get_screen(),
					'action' => 'delete',
					'id'     => $id,
				],
				admin_url( 'admin.php' )
			),
			'delete'
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_duplicate_link( $row ) {
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_row_actions( $row ) {
		return [
			'id'     => sprintf(
				/* translators: %d: Item id */
				__( 'ID: %d', 'user-registration' ),
				$row->get_id()
			),
			'edit'   => sprintf(
				'<a href="%s">%s</a>',
				esc_url( $this->get_edit_links( $row ) ),
				esc_html__( 'Edit', 'user-registration' )
			),
			'delete' => sprintf(
				'<a data-screen="%s" data-id="%s" data-urfd href="%s">%s</a>',
				esc_attr( $this->get_screen() ),
				esc_attr( $this->get_item_id( $row ) ),
				esc_url( $this->get_delete_links( $row ) ),
				esc_html__( 'Delete', 'user-registration' )
			),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function no_items() {
		echo esc_html( $this->get_no_items_message() );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function column_default( $item, $column_name ) {
		return '';
	}

	/**
	 * @param File|FileCategory $model
	 * @return string
	 */
	public function column_name( $model ) {
		return sprintf(
			'<strong><div class="ur-edit-title"><a href="%s" class="row-title">%s</a></div></strong>%s',
			esc_url( $this->get_edit_links( $model ) ),
			esc_html( $model->get_name() ),
			$this->row_actions( $this->get_row_actions( $model ) )
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function column_cb( $item ) {
		$id = $this->get_item_id( $item );
		return sprintf(
			'<input type="checkbox" name="%s[]" value="%s" />',
			esc_attr( $this->get_checkbox_name() ),
			esc_attr( (string) $id )
		);
	}

	/**
	 * @return void
	 */
	public function render() {
		$this->prepare_items();
		?>
		<div id="user-registration-list-table-page">
			<div class="user-registration-list-table-heading">
				<h1>
					<?php echo esc_html( $this->get_page_title() ); ?>
				</h1>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->page . '&action=new' ) ); ?>"
					class="page-title-action">
					<?php esc_html_e( 'Add New', 'user-registration' ); ?>
				</a>
			</div>
			<div id="user-registration-list-filters-row">
				<form method="get" id="user-registration-list-search-form">
					<input type="hidden" name="page" value="user-registration-file-downloads">
					<input type="hidden" name="screen" value="<?php echo esc_attr( $this->get_screen() ); ?>">
					<div>
						<input type="search" id="<?php echo esc_attr( $this->get_search_input_id() ); ?>" name="s" value="<?php echo esc_attr( $_GET['s'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Search', ' user-registration-file-downloads' ); ?> ..." autocomplete="off">
						<button type="submit" id="search-submit">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
								<path fill="#000" fill-rule="evenodd" d="M4 11a7 7 0 1 1 12.042 4.856 1.012 1.012 0 0 0-.186.186A7 7 0 0 1 4 11Zm12.618 7.032a9 9 0 1 1 1.414-1.414l3.675 3.675a1 1 0 0 1-1.414 1.414l-3.675-3.675Z" clip-rule="evenodd"></path>
							</svg>
						</button>
					</div>
				</form>
			</div>
			<hr>
			<form method="post" class="urfd-form" id="<?php echo esc_attr( $this->get_form_id() ); ?>">
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->page ); ?>" />
				<input type="hidden" name="screen" value="<?php echo esc_attr( $this->get_screen() ); ?>" />
				<?php
				if ( $this->screen ) {
					$this->screen->render_screen_reader_content( 'heading_list' );
				}
				$this->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * @param $row
	 *
	 * @return string
	 */
	public function column_action( $row ) {

		$edit_link   = $this->get_edit_links( $row );
		$delete_link = $this->get_delete_links( $row );

		ob_start();
		?>
		<div class="row-actions ur-d-flex ur-align-items-center visible" style="gap: 5px">
			<span class="edit">
				<a href="<?php echo esc_url( $edit_link ); ?>">
					<?php esc_html_e( 'Edit', 'user-registration' ); ?>
				</a>
			</span> &nbsp; | &nbsp;
			<span class="delete">
				<a data-urfd data-id="<?php echo esc_attr( $this->get_item_id( $row ) ); ?>" data-screen="<?php echo esc_attr( $this->get_screen() ); ?>" href="<?php echo esc_url( $delete_link ); ?>">
					<?php esc_html_e( 'Delete', 'user-registration' ); ?>
				</a>
			</span>
		</div>
		<?php
		return ob_get_clean();
	}

	protected function get_bulk_actions() {
		return [
			'delete' => __( 'Delete permanently', 'user-registration' ),
		];
	}
}

