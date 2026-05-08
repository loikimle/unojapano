<?php
/**
 * User Registration Frontend ListingTable List
 *
 * @version 1.0.0
 * @package  URFrontendListing/ListTable
 */

namespace WPEverest\URFrontendListing\Admin;

use WP_Query;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}

/**
 * Frontend Listing table list class.
 */
class ListTable extends \UR_List_Table {

	/**
	 * Initialize the Frontend Listing table list.
	 */
	public function __construct() {
		$this->post_type       = 'ur_frontend_listings';
		$this->page            = 'user-registration-frontend-list';
		$this->per_page_option = 'user_registration_frontend_listing_per_page';
		$this->sort_by         = array(
			'title'  => array( 'title', false ),
			'author' => array( 'author', false ),
			'date'   => array( 'date', false ),
		);
		$this->bulk_actions    = $this->urfl_bulk_actions();
		parent::__construct(
			array(
				'singular' => 'frontend-listing',
				'plural'   => 'frontend-listings',
				'ajax'     => false,
			)
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		_e( 'No Member Directories found.', 'user-registration' );
	}

	/**
	 * Get list columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Title', 'user-registration' ),
			'shortcode' => __( 'Shortcode', 'user-registration' ),
			'author'    => __( 'Author', 'user-registration' ),
			'date'      => __( 'Date', 'user-registration' ),
		);
	}

	/**
	 * Post Edit Link.
	 *
	 * @param  object $row
	 *
	 * @return string
	 */
	public function get_edit_links( $row ) {
		return admin_url( 'post.php?post=' . $row->ID . '&action=edit' );
	}

	/**
	 * Post Duplicate Link.
	 *
	 * @param  object $row
	 *
	 * @return string
	 */
	public function get_duplicate_link( $row ) {
		return admin_url( 'post.php?post=' . $row->ID . '&action=edit' );
	}



	/**
	 * Return title column.
	 *
	 * @param  object $frontend_listings Frontend List datas.
	 *
	 * @return string
	 */
	public function get_row_actions( $frontend_listings ) {

		$edit_link        = $this->get_edit_links( $frontend_listings );
		$post_status      = $frontend_listings->post_status;
		$post_type_object = get_post_type_object( $frontend_listings->post_type );
		// Get actions.
		$actions = array(
			'id' => sprintf( __( 'ID: %d', 'user-registration' ), $frontend_listings->ID ),
		);

		if ( current_user_can( $post_type_object->cap->edit_post, $frontend_listings->ID ) && 'trash' !== $post_status ) {
			$actions['edit']       = '<a href="' . esc_url( $edit_link ) . '">' . __( 'Edit', 'user-registration' ) . '</a>';
			$actions['edit_title'] = '<a href="#" class="edit-title-link" data-post-id="' . esc_attr( $frontend_listings->ID ) . '">' . __( 'Edit Title', 'user-registration' ) . '</a>';
		}

		if ( current_user_can( $post_type_object->cap->delete_post, $frontend_listings->ID ) ) {
			if ( 'trash' == $post_status ) {
				$actions['untrash'] = '<a aria-label="' . esc_attr__( 'Restore this item from the Trash', 'user-registration' ) . '" href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $frontend_listings->ID ) ), 'untrash-post_' . $frontend_listings->ID ) . '">' . esc_html__( 'Restore', 'user-registration' ) . '</a>';
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = '<a class="submitdelete" aria-label="' . esc_attr__( 'Move this item to the Trash', 'user-registration' ) . '" href="' . get_delete_post_link( $frontend_listings->ID ) . '">' . esc_html__( 'Trash', 'user-registration' ) . '</a>';
			}
			if ( 'trash' == $post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = '<a class="submitdelete" aria-label="' . esc_attr__( 'Delete this item permanently', 'user-registration' ) . '" href="' . get_delete_post_link( $frontend_listings->ID, '', true ) . '">' . esc_html__( 'Delete permanently', 'user-registration' ) . '</a>';
			}
		}
		return $actions;
	}

	/**
	 * Return shortcode column.
	 *
	 * @param  object $frontend_listings Frontend List datas.
	 *
	 * @return string
	 */
	public function column_shortcode( $frontend_listings ) {

		$shortcode = '[user_registration_member_directory id="' . $frontend_listings->ID . '"]';

		echo '<div class="urm-shortcode">';
			printf( '<input type="text" onfocus="this.select();" readonly="readonly" value=\'%s\' class="widefat code"></span>', esc_attr( $shortcode ) );
		?>
			<button id="copy-shortcode" class="button ur-copy-shortcode " href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode ! ', 'user-registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied ! ', 'user-registration' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<path fill="#383838" fill-rule="evenodd" d="M3.116 3.116A1.25 1.25 0 0 1 4 2.75h9A1.25 1.25 0 0 1 14.25 4v1a.75.75 0 0 0 1.5 0V4A2.75 2.75 0 0 0 13 1.25H4A2.75 2.75 0 0 0 1.25 4v9A2.75 2.75 0 0 0 4 15.75h1a.75.75 0 0 0 0-1.5H4A1.25 1.25 0 0 1 2.75 13V4c0-.332.132-.65.366-.884ZM9.75 11c0-.69.56-1.25 1.25-1.25h9c.69 0 1.25.56 1.25 1.25v9c0 .69-.56 1.25-1.25 1.25h-9c-.69 0-1.25-.56-1.25-1.25v-9ZM11 8.25A2.75 2.75 0 0 0 8.25 11v9A2.75 2.75 0 0 0 11 22.75h9A2.75 2.75 0 0 0 22.75 20v-9A2.75 2.75 0 0 0 20 8.25h-9Z" clip-rule="evenodd"></path>
				</svg>
			</button>
		</div>
		<?php
	}

	/**
	 * Return author column.
	 *
	 * @param  object $frontend_listings Frontend List datas.
	 *
	 * @return string
	 */
	public function column_author( $frontend_listings ) {
		$user = get_user_by( 'id', $frontend_listings->post_author );

		if ( ! $user ) {
			return '<span class="na">&ndash;</span>';
		}

		$user_name = ! empty( $user->data->display_name ) ? $user->data->display_name : $user->data->user_login;

		if ( current_user_can( 'edit_user' ) ) {
			return '<a href="' . esc_url(
				add_query_arg(
					array(
						'user_id' => $user->ID,
					),
					admin_url( 'user-edit.php' )
				)
			) . '">' . esc_html( $user_name ) . '</a>';
		}

		return esc_html( $user_name );
	}


	/**
	 * Return created at date column.
	 *
	 * @param  object $frontend_listings Frontend List datas.
	 *
	 * @return string
	 */
	public function column_date( $frontend_listings ) {
		$post = get_post( $frontend_listings->ID );

		if ( ! $post ) {
			return;
		}

		$t_time = mysql2date(
			__( 'Y/m/d g:i:s A', 'user-registration' ),
			$post->post_date,
			true
		);
		$m_time = $post->post_date;
		$time   = mysql2date( 'G', $post->post_date )
				- get_option( 'gmt_offset' ) * 3600;

		$time_diff = time() - $time;

		if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
			$h_time = sprintf(
				__( '%s ago', 'user-registration' ),
				human_time_diff( $time )
			);
		} else {
			$h_time = mysql2date( __( 'Y/m/d', 'user-registration' ), $m_time );
		}

		return '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
	}

	/**
	 * Get bulk actions.
	 *
	 * @return array
	 */
	protected function urfl_bulk_actions() {
		if ( isset( $_GET['status'] ) && 'trash' == $_GET['status'] ) {
			return array(
				'untrash' => __( 'Restore', 'user-registration' ),
				'delete'  => __( 'Delete permanently', 'user-registration' ),
			);
		}

		return array(
			'trash' => __( 'Move to trash', 'user-registration' ),
		);
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		$this->prepare_items();
		if ( ! isset( $_GET['add-new-frontend-listing'] ) ) { // phpcs:ignore Standard.Category.SniffName.ErrorCode: input var okay, CSRF ok.
			?>
				<hr class="wp-header-end">
					<?php $this->render_create_modal(); ?>
				<?php echo user_registration_plugin_main_header(); ?>
				<div class="user-registration-list-table-container">
					<div id="user-registration-list-table-page">
						<div class="user-registration-list-table-header">
							<h2><?php esc_html_e( ' Member Directories', 'user-registration' ); ?></h2>
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ur_frontend_listings' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'user-registration' ); ?></a>
						</div>
						<div class="user-registration-list-table-page__body">
							<form id="frontend-listing-list" method="get" class="user-registration-list-table-action-form" >
								<input type="hidden" name="page" value="user-registration-frontend-list" />
								<?php
								echo "<div id='user-registration-list-filters-row'>";
								$this->views();
								$this->search_box( __( 'Search Listings', 'user-registration' ), 'frontend-listings' );
								echo '</div>';
								$this->display();
								?>
							</form>
						</div>
					</div>
				</div>
				<?php
		}
	}

	/**
	 * Displays the search box.
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = 'user-registration-list-table-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>
		<div id="user-registration-list-search-form">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_html_e( 'Search Listings ...', 'user-registration' ); ?>" />
			<button type="submit" id="search-submit">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<path fill="#000" fill-rule="evenodd" d="M4 11a7 7 0 1 1 12.042 4.856 1.012 1.012 0 0 0-.186.186A7 7 0 0 1 4 11Zm12.618 7.032a9 9 0 1 1 1.414-1.414l3.675 3.675a1 1 0 0 1-1.414 1.414l-3.675-3.675Z" clip-rule="evenodd"/>
				</svg>
			</button>
		</div>
		<?php
	}


	/**
	 * Render the create listing modal
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function render_create_modal() {
		?>
	<div id="urfl-create-modal" class="urfl-modal" style="display: none;">
		<div class="urfl-modal-overlay"></div>
		<div class="urfl-modal-content">
			<div class="urfl-modal-header">
				<h2><?php esc_html_e( 'Add New Member Directory', 'user-registration-frontend-listing' ); ?></h2>
				<button type="button" class="urfl-modal-close">&times;</button>
			</div>
			<div class="urfl-modal-body">
				<div class="urfl-modal-field">
					<label for="urfl-listing-title">
						<?php esc_html_e( 'Directory Name', 'user-registration-frontend-listing' ); ?>
						<span class="required">*</span>
					</label>
					<input
						type="text"
						id="urfl-listing-title"
						class="urfl-modal-input"
						placeholder="<?php esc_attr_e( 'Enter Directory name...', 'user-registration-frontend-listing' ); ?>"
						autocomplete="off"
					/>
					<p class="urfl-modal-error" style="display: none;"></p>
				</div>
			</div>
			<div class="urfl-modal-footer">
				<button type="button" class="button urfl-modal-cancel">
					<?php esc_html_e( 'Cancel', 'user-registration-frontend-listing' ); ?>
				</button>
				<button type="button" class="button button-primary urfl-modal-create">
					<?php esc_html_e( 'Continue', 'user-registration-frontend-listing' ); ?>
				</button>
			</div>
		</div>
	</div>
		<?php
	}
}
