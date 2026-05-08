<?php
/**
 *
 */

namespace WPEverest\URMembership\Coupons;

use UR_Base_Layout;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}

if ( ! class_exists( 'UR_Base_Layout' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/admin/class-ur-admin-base-layout.php';
}

/**
 * Orders table list class.
 */
class CouponsListTable extends \UR_List_Table {

	/**
	 * Initialize the Orders table list.
	 */
	public function __construct() {
		$this->post_type       = 'ur_coupons';
		$this->page            = 'user-registration-coupons';
		$this->per_page_option = 'user_registration_coupons_per_page';
		$this->addnew_action   = 'add_new_coupon';
		$this->sort_by         = array(
			'title' => array( 'title', false ),
		);
		parent::__construct(
			array(
				'singular' => 'coupon',
				'plural'   => 'coupons',
				'ajax'     => true,
			)
		);
	}


	public function column_default( $item, $column_name ) {
		$meta_data = json_decode( get_post_meta( $item->ID, 'ur_coupon_meta', true ), true );

		switch ( $column_name ) {
			case 'code':
				return esc_html( $meta_data['coupon_code'] );
			case 'amount':
				return $this->show_column_amount( $meta_data );
			case 'status':
				return $this->show_column_status( $meta_data );
			// case 'action':
			//  return $this->column_action( $item );
			case 'expires':
				if ( ! empty( $meta_data['coupon_end_date'] ) ) {
					echo date_i18n( get_option( 'date_format' ), strtotime( $meta_data['coupon_end_date'] ) );
				} else {
					echo __( 'Never Expires', 'user-registration-membership' );
				}
				break;
			case 'type':
				echo ucfirst( $meta_data['coupon_discount_type'] );
				break;
			default:
				return print_r( $item, true );
		}
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		UR_Base_Layout::no_items( 'Coupons' );
	}

	/**
	 * Returns the formatted amount for a coupon based on the discount type.
	 *
	 * @param array $meta_data An array of meta data containing the coupon discount type and amount.
	 *
	 * @return string The formatted coupon amount with the appropriate symbol.
	 */
	public function show_column_amount( $meta_data ) {
		$symbol = '';
		if ( ur_check_module_activation( 'payments' ) || is_plugin_active( 'user-registration-stripe/user-registration-stripe.php' ) || is_plugin_active( 'user-registration-authorize-net/user-registration-authorize-net.php' ) ) {
			$currency   = get_option( 'user_registration_payment_currency', 'USD' );
			$currencies = ur_payment_integration_get_currencies();
			$symbol     = $currencies[ $currency ]['symbol'];
		}

		return ( isset( $meta_data['coupon_discount_type'] ) && 'fixed' === $meta_data['coupon_discount_type'] ) ? $symbol . esc_html( $meta_data['coupon_discount'] ) : esc_html( $meta_data['coupon_discount'] ) . '%';
	}

	/**
	 * Prepare the items for the table to process.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$this->prepare_column_headers();
		$per_page     = $this->get_items_per_page( $this->per_page_option );
		$current_page = $this->get_pagenum();

		// Query args.
		$args = array(
			'post_type'           => $this->post_type,
			'posts_per_page'      => $per_page,
			'ignore_sticky_posts' => true,
			'paged'               => $current_page,
		);

		// Handle the status query.
		if ( ! empty( $_REQUEST['status'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$args['post_status'] = sanitize_text_field( wp_unslash( $_REQUEST['status'] ) );

		}

		// Handle the search query.
		if ( ! empty( $_REQUEST['s'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$args['s']              = trim( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) );
			$new_args['meta_query'] = array(
				array(
					'key'     => 'ur_coupon_meta',
					'value'   => $_REQUEST['s'],
					'compare' => 'LIKE',
				),
			);
		}

		$args['orderby'] = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'date_created'; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$args['order']   = isset( $_REQUEST['order'] ) && 'ASC' === strtoupper( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ? 'ASC' : 'DESC'; //phpcs:ignore WordPress.Security.NonceVerification.Missing

		$first_items = get_posts( $args );

		$second_items = get_posts(
			array(
				'post_type'  => 'ur_coupons',
				'meta_query' => array(
					array(
						'key'     => 'ur_coupon_meta',
						'value'   => $args['s'] ?? '',
						'compare' => 'LIKE',
					),
				),
			)
		);

		$this->items = array_unique( array_merge( $first_items, $second_items ), SORT_REGULAR );

		// Set the pagination.
		$this->set_pagination_args(
			array(
				'total_items' => count( $this->items ),
				'per_page'    => $per_page,
				'total_pages' => ceil( count( $this->items ) / $per_page, ),
			)
		);
	}


	/**
	 * Get list columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'title'   => __( 'Coupon Name', 'user-registration-membership' ),
			'code'    => __( 'Coupon Code', 'user-registration-membership' ),
			'type'    => __( 'Coupon Type', 'user-registration-membership' ),
			'amount'  => __( 'Discount', 'user-registration-membership' ),
			'expires' => __( 'Expires', 'user-registration-membership' ),
			'status'  => __( 'Status', 'user-registration-membership' ),
			// 'action'  => __( 'Action', 'user-registration-membership' ),
		);
	}


	/**
	 * Post Edit Link.
	 *
	 * @param object $row
	 *
	 * @return string
	 */
	public function get_edit_links(
		$row
	) {
		return admin_url( 'admin.php?post_id=' . $row->ID . '&action=' . $this->addnew_action . '&page=' . $this->page );
	}

	/**
	 * Post Duplicate Link.
	 *
	 * @param object $row
	 *
	 * @return string
	 */
	public function get_duplicate_link(
		$row
	) {
		return admin_url( 'post.php?post=' . $row->ID . '&action=edit' );
	}

	/**
	 * @param $coupon
	 *
	 * @return array
	 */
	public function get_row_actions(
		$coupon
	) {

		return array();
	}

	/**
	 * @param $coupon
	 *
	 * @return string
	 */
	public function show_column_status(
		$meta
	) {

		if ( isset( $meta['coupon_status'] ) && ! empty( $meta['coupon_status'] ) ) {
			if ( ! empty( $meta['coupon_end_date'] ) ) {
				$status = $meta['coupon_end_date'] >= date( 'Y-m-d\TH:i' ) ? 'active' : 'expired';
			} else {
				$status = 'never';
			}
		} else {
			$status = 'inactive';
		}
		$checked = 'active' === $status || 'never' === $status ? '1' : '';
		$actions = '';

		$actions .= '<div class="ur-toggle-section">';
		$actions .= '<span class="user-registration-toggle-form">';
		$actions .= '<input
						class="ur-coupon-change-status user-registration-switch__control hide-show-check enabled"
						type="checkbox"
						value="1"
						' . esc_attr( checked( true, ur_string_to_bool( $checked ), false ) ) . '
						data-coupon-code="' . esc_attr( $meta['coupon_code'] ) . '"
						>';
		$actions .= '<span class="slider round"></span>';
		$actions .= '</span>';
		$actions .= '</div>';
		return $actions;
	}

	/**
	 * Get the sortable columns for the table.
	 *
	 * @return array The list of columns that are sortable.
	 */
	public function get_sortable_columns() {
		return array(
			'amount' => array( 'amount' ),
			'status' => array( 'status' ),
		);
	}


	/**
	 * @param $coupon
	 *
	 * @return string
	 */
	public function column_action(
		$coupon
	) {
		return '
				<div class="row-actions ur-d-flex ur-align-items-center visible" style="gap: 5px">
					<span class="view">
						<a class="show-coupon-detail" value = ' . esc_attr( $coupon->ID ) . ' href="' . $this->get_edit_links( $coupon ) . '">' . __( 'View', 'user-registration-membership' ) . '</a>
					</span>
					&nbsp | &nbsp
					<span id="delete-coupon" class="trash">
						<a class="submitdelete" aria-label="' . esc_attr__( 'Move this item to the Trash', 'user-registration-membership' ) . '" href="' . get_delete_post_link( $coupon->ID ) . '">' . esc_html__( 'Delete', 'user-registration-membership' ) . '</a>
					</span>
					</div>
					';
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		UR_Base_Layout::render_layout(
			$this,
			array(
				'page'           => $this->page,
				'title'          => esc_html__( 'Coupons', 'user-registration' ),
				'add_new_action' => 'add_new_coupon',
				'search_id'      => 'user-registration-payment-history-search',
				'form_id'        => 'ur-coupon-list-form',
				'skip_query_key' => 'add-new-membership',
			)
		);
	}

	/**
	 * Display the table in the admin area.
	 *
	 * This function renders the table in the admin area. It displays the table headers, rows, and table nav.
	 *
	 * @return void
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
	 * Displays the search box.
	 *
	 * @since 4.1
	 */
	public function display_search_box( $search_id ) {
		?>
		<p class="search-box">
			</p>
			<div id="user-registration-list-search-form">
				<?php
				$placeholder = __( 'Search Coupon...', 'user-registration' );
				UR_Base_Layout::display_search_field( $search_id, $placeholder );
				?>
				</div>
			<p></p>
		<?php
	}

	/**
	 * @return array
	 */
	public function get_all_memberships() {
		$posts        = get_posts(
			array(
				'post_type'   => 'ur_membership',
				'numberposts' => - 1,
			)
		);
		$active_posts = array_filter(
			json_decode( json_encode( $posts ), true ),
			function ( $item ) {
				$content = json_decode( wp_unslash( $item['post_content'] ), true );

				return $content['status'];
			}
		);

		return wp_list_pluck( $active_posts, 'post_title', 'ID' );
	}

	/**
	 * @return array
	 */
	public function get_all_forms() {
		$posts = get_posts(
			array(
				'post_type'   => 'user_registration',
				'numberposts' => - 1,
				'post_status' => 'publish',

			)
		);

		return wp_list_pluck( $posts, 'post_title', 'ID' );
	}

	/**
	 * Show column title.
	 */
	public function column_title( $item ) {
		$meta_data = json_decode( get_post_meta( $item->ID, 'ur_coupon_meta', true ), true );

		$title = ! empty( $item->post_title )
			? $item->post_title
			: ( ! empty( $meta_data['coupon_code'] )
				? $meta_data['coupon_code'] . '_title'
				: __( 'Untitled Coupon', 'user-registration-membership' )
			);

		$title_link = sprintf(
			'<a href="%s" class="row-title">%s</a>',
			esc_url( $this->get_edit_links( $item ) ),
			esc_html( $title )
		);

		$actions = array(
			'edit'   => sprintf(
				'<a href="%s">%s</a>',
				esc_url( $this->get_edit_links( $item ) ),
				__( 'Edit', 'user-registration-membership' )
			),
			'delete' => sprintf(
				'<a href="%s" class="submitdelete">%s</a>',
				esc_url( get_delete_post_link( $item->ID ) ),
				__( 'Delete', 'user-registration-membership' )
			),
		);

		$id_label = sprintf(
			'<span class="ur-row-id">ID: %d</span> ',
			absint( $item->ID )
		);

		return $title_link . $this->row_actions(
			array(
				'id' => $id_label,
			) + $actions
		);
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
}
