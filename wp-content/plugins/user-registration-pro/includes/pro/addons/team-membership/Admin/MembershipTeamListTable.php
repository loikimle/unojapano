<?php
/**
 * User Registration Team Membership Members Table List
 *
 * @version
 * @package  URTeamMembership/MembershipTeamListTable
 */

namespace WPEverest\URTeamMembership\Admin;

use UR_Base_Layout;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}

if ( ! class_exists( 'UR_Base_Layout' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/admin/class-ur-admin-base-layout.php';
}

class MembershipTeamListTable extends \UR_List_Table {

	private $team_id;

	private $team_leader_id;

	public function __construct( $team_id = 0 ) {
		$this->team_id         = absint( $team_id );
		$this->team_leader_id  = absint( get_post_meta( $this->team_id, 'urm_team_leader_id', true ) );
		$this->page            = 'user-registration-team-members';
		$this->post_type       = 'ur_membership_team';
		$this->per_page_option = 'user_registration_membership_members_per_page';

		parent::__construct(
			array(
				'singular' => 'member',
				'plural'   => 'members',
				'ajax'     => false,
			)
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		$image_url = esc_url( plugin_dir_url( UR_PLUGIN_FILE ) . 'assets/images/empty-table.png' );
		?>
		<div class="empty-list-table-container">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="">
			<h3><?php esc_html_e( 'No team members found.', 'user-registration' ); ?></h3>
		</div>
		<?php
	}

	/**
	 * Get list columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'user_id' => __( 'User ID', 'user-registration' ),
			'email'   => __( 'Email', 'user-registration' ),
			'name'    => __( 'Name', 'user-registration' ),
			'role'    => __( 'Role', 'user-registration' ),
			'status'  => __( 'Status', 'user-registration' ),
			'action'  => __( 'Action', 'user-registration' ),
		);
	}

	/**
	 * Prepare items for display.
	 */
	public function prepare_items() {
		$this->prepare_column_headers();
		$per_page     = $this->get_items_per_page( $this->per_page_option, 10 );
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$members_emails = get_post_meta( $this->team_id, 'urm_member_emails', true );
		if ( ! is_array( $members_emails ) ) {
			$members_emails = array();
		}

		$members_data     = array();
		$team_leader_data = null;
		$other_members    = array();

		foreach ( $members_emails as $email ) {
			$user = get_user_by( 'email', $email );
			if ( $user ) {
				$member_data = array(
					'ID'    => $user->ID,
					'email' => $user->user_email,
					'name'  => $user->display_name,
				);

				if ( $user->ID === $this->team_leader_id ) {
					$team_leader_data = $member_data;
				} else {
					$other_members[] = $member_data;
				}
			}
		}

		$members_data = array();
		if ( $team_leader_data ) {
			$members_data[] = $team_leader_data;
		}
		$other_members = array_reverse( $other_members );
		$members_data  = array_merge( $members_data, $other_members );

		$total_items = count( $members_data );
		$items       = array_slice( $members_data, $offset, $per_page );

		$this->items = $items;
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Column user ID.
	 *
	 * @param array $item Member data.
	 * @return string
	 */
	public function column_user_id( $item ) {
		return esc_html( $item['ID'] );
	}

	/**
	 * Column email.
	 *
	 * @param array $item Member data.
	 * @return string
	 */
	public function column_email( $item ) {
		return esc_html( $item['email'] );
	}

	/**
	 * Column name.
	 *
	 * @param array $item Member data.
	 * @return string
	 */
	public function column_name( $item ) {
		return esc_html( $item['name'] );
	}

	/**
	 * Column status.
	 *
	 * @param array $item Member data.
	 * @return string
	 */
	public function column_status( $item ) {
		$user = get_userdata( $item['ID'] );
		if ( ! $user ) {
			return '—';
		}

		$email_confirmed = get_user_meta( $item['ID'], 'ur_confirm_email', true );

		if ( '0' === $email_confirmed ) {
			$status = __( 'Email Not Confirmed', 'user-registration' );
			$class  = 'ur-status-pending';
			$color  = '#ee9936';
		} else {
			$status = __( 'Active', 'user-registration' );
			$class  = 'ur-status-active';
			$color  = '#4cc741';
		}

		return '<span class="' . esc_attr( $class ) . '" style="color: ' . esc_attr( $color ) . '; font-weight: 500;">' . esc_html( $status ) . '</span>';
	}

	/**
	 * Column role.
	 *
	 * @param array $item Member data.
	 * @return string
	 */
	public function column_role( $item ) {
		$user = get_userdata( $item['ID'] );
		if ( ! $user ) {
			return '—';
		}

		$roles = $user->roles;
		if ( empty( $roles ) ) {
			return '—';
		}

		if ( $item['ID'] === $this->team_leader_id ) {
			return '<span class="ur-team-leader-badge" style="font-weight: 500;">' . esc_html__( 'Team Leader', 'user-registration' ) . '</span>';
		}

		$wp_roles  = wp_roles();
		$role_name = isset( $wp_roles->roles[ $roles[0] ] ) ? $wp_roles->roles[ $roles[0] ]['name'] : $roles[0];

		return esc_html( $role_name );
	}

	public function column_action( $item ) {
		$url     = add_query_arg(
			array(
				'page'      => 'user-registration-users',
				'view_user' => '',
				'action'    => 'view',
				'user_id'   => $item['ID'],
				'_wpnonce'  => wp_create_nonce( 'bulk-users' ),
			),
			admin_url( 'admin.php' )
		);
		$actions = sprintf( '<a href="%s" style="color:#475bb2;">%s</a>', esc_url( $url ), esc_html__( 'View', 'user-registration' ) );
		return $actions;
	}


	/**
	 * Get edit links (not applicable for member display table).
	 *
	 * @param array $row Member data.
	 * @return string
	 */
	public function get_edit_links( $row ) {
		return '';
	}

	/**
	 * Get duplicate link (not applicable for member display table).
	 *
	 * @param array $row Member data.
	 * @return array
	 */
	public function get_duplicate_link( $row ) {
		return array();
	}

	/**
	 * Get row actions (not applicable for member display table).
	 *
	 * @param array $row Member data.
	 * @return array
	 */
	public function get_row_actions( $row ) {
		return array();
	}

	/**
	 * Displays the table.
	 *
	 * @since 3.1.0
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
				<?php $this->print_table_description(); ?>
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tbody id="the-list"
				<?php
				if ( $singular ) {
					echo " data-wp-lists='list:$singular'";
				}
				?>
				>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Generates the table navigation above or below the table
	 *
	 * @since 4.1
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( $this->has_items() && 'top' === $which ) : ?>
				<div>
					<div class="alignleft actions bulkactions">
						<?php $this->bulk_actions( $which ); ?>
					</div>
					<?php $this->extra_tablenav( $which ); ?>
				</div>
				<?php
			endif;
			if ( 'bottom' === $which ) :
				?>
				<div class="alignleft">
					<?php $this->footer_text(); ?>
				</div>
				<?php
				$this->pagination( $which );
			endif;
			?>
		</div>
		<?php
	}

	/**
	 * Displays the pagination.
	 *
	 * @since 3.1.0
	 *
	 * @param string $which The location of the pagination: Either 'top' or 'bottom'.
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args['total_items'] ) ) {
			return;
		}

		$total_items     = $this->_pagination_args['total_items'];
		$total_pages     = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}

		$current              = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = false;
		$disable_last  = false;
		$disable_prev  = false;
		$disable_next  = false;

		if ( 1 === $current ) {
			$disable_first = true;
			$disable_prev  = true;
		}
		if ( $total_pages === $current ) {
			$disable_last = true;
			$disable_next = true;
		}

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='first-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				/* translators: Hidden accessibility text. */
				__( 'First page' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='prev-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
				/* translators: Hidden accessibility text. */
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = sprintf(
				'<span class="screen-reader-text">%s</span>' .
				'<span id="table-paging" class="paging-input">' .
				'<span class="tablenav-paging-text">',
				/* translators: Hidden accessibility text. */
				__( 'Current Page' )
			);
		} else {
			$html_current_page = sprintf(
				'<label for="current-page-selector" class="screen-reader-text">%s</label>' .
				"<input class='current-page' id='current-page-selector' type='text'
					name='paged' value='%s' size='%d' aria-describedby='table-paging' />" .
				"<span class='tablenav-paging-text'>",
				/* translators: Hidden accessibility text. */
				__( 'Current Page' ),
				$current,
				strlen( $total_pages )
			);
		}

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );

		$page_links[] = $total_pages_before . sprintf(
			/* translators: 1: Current page, 2: Total pages. */
			_x( '%1$s of %2$s', 'paging' ),
			$html_current_page,
			$html_total_pages
		) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='next-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
				/* translators: Hidden accessibility text. */
				__( 'Next page' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='last-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				/* translators: Hidden accessibility text. */
				__( 'Last page' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class .= ' hide-if-js';
		}
		$output = "\n<span class='$pagination_links_class'>" . implode( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}

	protected function get_bulk_actions() {
		return array();
	}

	/**
	 * Get default primary column.
	 *
	 * @return string
	 */
	protected function get_default_primary_column_name() {
		return 'user_id';
	}
}
