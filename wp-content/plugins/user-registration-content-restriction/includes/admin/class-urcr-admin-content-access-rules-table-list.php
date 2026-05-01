<?php
/**
 * User Registration Content Restriction - Content Access Rules Table List
 *
 * @version 1.0.0
 *
 * @package UserRegistrationContentRestriction\Admin
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}
/**
 * Content access rules table list class.
 *
 * @since 2.0.0
 */
class URCR_Admin_Content_Access_Rules_Table_List extends UR_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->post_type       = 'urcr_access_rule';
		$this->page            = 'user-registration-content-restriction';
		$this->per_page_option = 'urcr_access_rules_per_page';
		$this->addnew_action   = 'add_new_urcr_content_access_rule';
		parent::__construct(
			array(
				'singular' => 'content-access-rule',
				'plural'   => 'content-access-rules',
				'ajax'     => false,
			)
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		esc_html_e( 'No content access rule found.', 'user-registration-content-restriction' );
	}

	/**
	 * Define columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'     => '<input type="checkbox" />',
			'title'  => esc_html__( 'Title', 'user-registration-content-restriction' ),
			'status' => esc_html__( 'Status', 'user-registration-content-restriction' ),
			'action' => esc_html__( 'Action', 'user-registration-content-restriction' ),
			'author' => esc_html__( 'Author', 'user-registration-content-restriction' ),
			'date'   => esc_html__( 'Last Update', 'user-registration-content-restriction' ),
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
		return admin_url( 'admin.php?page=' . $this->page . '&action=' . $this->addnew_action . '&post-id=' . $row->ID );
	}

	/**
	 * Post Duplicate Link.
	 *
	 * @param  object $row
	 *
	 * @return string
	 */
	public function get_duplicate_link( $row ) {
		return admin_url( 'admin.php?page=' . $this->page . '&action=' . $this->addnew_action . '&post-id=' . $row->ID );
	}


	/**
	 * Column: Actions.
	 *
	 * @param  object $row
	 *
	 * @return array
	 */
	public function get_row_actions( $row ) {

		$edit_link            = $this->get_edit_links( $row );
		$post_status          = $row->post_status;
		$post_type_object     = get_post_type_object( $row->post_type );
		$current_status_trash = ( 'trash' === $post_status );

		//
		// Prepare column actions.
		//

		// Column ID.
		$actions = array(
			'id' => sprintf( '%s: %d', esc_html__( 'ID', 'user-registration-content-restriction' ), $row->ID ),
		);

		// Edit Action.
		if ( current_user_can( 'edit_post', $row->ID ) && 'trash' !== $post_status ) {
			$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html__( 'Edit', 'user-registration-content-restriction' ) );
		}

		// Trash / Untrash / Delete Actions.
		if ( current_user_can( 'delete_post', $row->ID ) ) {

			if ( $current_status_trash ) {
				$untrash_link       = wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $row->ID ) ), 'untrash-post_' . $row->ID );
				$actions['untrash'] = sprintf(
					'<a aria-label="%s" href="%s">%s</a>',
					esc_attr__( 'Restore this item from the Trash', 'user-registration-content-restriction' ),
					$untrash_link,
					esc_html__( 'Restore', 'user-registration-content-restriction' )
				);
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = sprintf(
					'<a class="submitdelete" aria-label="%s" href="%s">%s</a>',
					esc_attr__( 'Move this item to the Trash', 'user-registration-content-restriction' ),
					get_delete_post_link( $row->ID ),
					esc_html__( 'Trash', 'user-registration-content-restriction' )
				);
			}

			if ( $current_status_trash || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = sprintf(
					'<a class="submitdelete" aria-label="%s" href="%s">%s</a>',
					esc_attr__( 'Delete this item permanently', 'user-registration-content-restriction' ),
					get_delete_post_link( $row->ID, '', true ),
					esc_html__( 'Delete permanently', 'user-registration-content-restriction' )
				);
			}
		}

		// Duplicate Post Action.
		if ( current_user_can( 'edit_post', $row->ID ) ) {
			$_nonce         = wp_create_nonce( 'ur_duplicate_post_' . $row->ID );
			$duplicate_link = admin_url( 'admin.php?page=user-registration-content-restriction&action=duplicate&nonce=' . $_nonce . '&post-id=' . $row->ID );

			if ( 'publish' === $post_status ) {
				$actions['duplicate'] = sprintf( '<a href="%s">%s</a>', esc_url( $duplicate_link ), esc_html__( 'Duplicate', 'user-registration-content-restriction' ) );
			}
		}

		return $actions;
	}

	/**
	 * Column: Status.
	 *
	 * @param  object $access_rule_post
	 *
	 * @return string
	 */
	public function column_status( $access_rule_post ) {
		$access_rule  = json_decode( $access_rule_post->post_content, true );
		$enabled      = urcr_is_access_rule_enabled( $access_rule );
		$status_class = $enabled ? 'user-registration-badge user-registration-badge--success-subtle' : 'user-registration-badge user-registration-badge--secondary-subtle';
		$status_label = $enabled ? esc_html__( 'Active', 'user-registration-content-restriction' ) : esc_html__( 'Inactive', 'user-registration-content-restriction' );
		$output       = sprintf( '<span class="%s">%s</span>', $status_class, $status_label );
		return $output;
	}

	/**
	 * Column: Action.
	 *
	 * @param  object $access_rule_post
	 *
	 * @return string
	 */
	public function column_action( $access_rule_post ) {
		$access_rule = json_decode( $access_rule_post->post_content, true );
		$actions     = array();

		foreach ( $access_rule['actions'] as $action ) {
			$actions[] = ! empty( $action['label'] ) ? $action['label'] : strtoupper( isset( $action['type'] ) ? $action['type'] : '' );
		}
		$actions = implode( ' | ', $actions );
		$actions = ! empty( $actions ) ? $actions : '—';

		return '<span>' . $actions . '</span>';
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		$this->prepare_items();
		?>

		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Content Access Rules', 'user-registration-content-restriction' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-content-restriction&action=add_new_urcr_content_access_rule' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'user-registration-content-restriction' ); ?></a>
			<hr class="wp-header-end">
			<form id="urcr-content-access-rules-list" method="get">
				<input type="hidden" name="page" value="user-registration-content-restriction" />
				<?php
					$this->views();
					$this->search_box( esc_html__( 'Search Rule', 'user-registration-content-restriction' ), 'content-access-rule' );
					$this->display();
				?>
			</form>
		</div>

		<?php
	}
}
