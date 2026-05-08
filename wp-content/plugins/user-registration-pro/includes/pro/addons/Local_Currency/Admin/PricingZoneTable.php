<?php
/**
 * User Registration Table List
 *
 * @version 1.2.0
 * @package UserRegistration\Admin\Registration
 */

namespace WPEverest\URMembership\Local_Currency\Admin;

defined( 'ABSPATH' ) || exit;

use UR_Base_Layout;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}

if ( ! class_exists( 'UR_Base_Layout' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/admin/class-ur-admin-base-layout.php';
}

/**
 * Registrations table list class.
 */
class PricingZoneTable extends \UR_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'currency',
			'plural'   => 'currencies',
			'ajax'     => false,
		) );
	}

	/**
	 * Table columns
	 */
	public function get_columns() {
		return array(
			'cb'        	=> '<input type="checkbox" />',
			'name'			=> __( 'Name', 'user-registration' ),
			'currency'  	=> __( 'Currency', 'user-registration' ),
			'exchange_rate' => __( 'Exchange Rate', 'user-registration' ),
			'country'   	=> __( 'Countries', 'user-registration' ),
			'date'			=> __( 'Date', 'user-registration' ),
			// 'status'    	=> __( 'Status', 'user-registration' ),
		);
	}

	/**
	 * Checkbox column
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="currency_ids[]" value="%s" />',
			$item['id']
		);
	}

	protected function get_bulk_actions() {
		return [
			'delete' => __( 'Delete', 'user-registration' ),
		];
	}

	public function display() {
		$this->display_tablenav( 'top' );
		?>
		<table class="wp-list-table <?php echo implode( ' ', array_map( 'esc_attr', $this->get_table_classes() ) ); ?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>
			<tbody id="the-list">
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Default column output
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ] ? $item[ $column_name ] : '—';
	}

	/**
	 * Prepare table data
	 */
	public function prepare_items() {

		$per_page     = $this->get_items_per_page( 'ur_currencies_per_page', 10 );
		$current_page = $this->get_pagenum();

		$args = array(
			'post_type'      => 'urm_price_zone',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		$posts = get_posts( $args );

		$data = array();

		foreach ( $posts as $post ) {
			$currency_key = get_post_meta( $post->ID, 'ur_local_currency', true );
			$data[] = array(
				'id'       		=> $post->ID,
				'name'     		=> $post->post_title,
				'country'  		=> $this->render_country( get_post_meta( $post->ID, 'ur_local_currencies_countries', true ) ),
				'currency' 		=> ur_get_currency_by_key( $currency_key[0] ),
				'exchange_rate' => $this->ur_get_exchange_rate( get_post_meta( $post->ID, 'ur_local_currencies_exchange_rate', true ),  get_post_meta( $post->ID, 'ur_local_currency', true ) ),
				'date'			=> $post->post_date,
				// 'status'   		=> $post->post_status === 'publish' ? 'Active' : 'Inactive',
			);
		}

		$total_items = count( $data );

		$data = array_slice(
			$data,
			( $current_page - 1 ) * $per_page,
			$per_page
		);

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			array(),
		);

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}


	public function column_date( $item ) {

		if ( ! is_array( $item ) || empty( $item['date'] ) ) {
			return '—';
		}

		$timestamp = strtotime( $item['date'] );

		return esc_html(
			date_i18n( 'Y-m-d, h:i A', $timestamp )
		);
	}

	public function render_country( $countries ) {
		$country_list = ur_get_country_lists();

		if ( empty( $countries ) ) {
			return '';
		}

		if ( count( $countries ) === 1 ) {
			return esc_html( ( ! empty( $country_list[ $countries[0] ] ) ? $country_list[ $countries[0] ] : '' ) );
		}

		ob_start();

		$first_key   = array_key_first( $countries );
		$first_code = $countries[ $first_key ];
		$remaining  = $countries;
		unset( $remaining[ $first_key ] );
		?>

		<div class="ur-country-dropdown">
			<button type="button" class="ur-country-btn">
				<span class="ur-country-label">
					<?php echo esc_html( $country_list[ $first_code ] ); ?>

					<span class="ur-country-count">
						+<?php echo count( $remaining ); ?>
					</span>
				</span>
			</button>

			<div class="ur-country-menu">
				<?php foreach ( $remaining as $code ) : ?>
					<span type="button" class="ur-country-item">
						<?php echo esc_html( $country_list[ $code ] ?? '' ); ?>
					</span>
				<?php endforeach; ?>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

	public function column_name( $item ) {
		$name = esc_html( $item['name'] );

		$actions = array(
			'edit'   => sprintf(
				'<a data-id="' . esc_attr( $item[ 'id'] ) . '" href="#" class="ur-local-currency-add-pricing-zone" data-action="edit">%s</a>',
				esc_html__( 'Edit', 'user-registration' )
			),
			'delete' => sprintf(
				'<a data-id="' . esc_attr( $item[ 'id'] ) . '" href="#" class="ur-local-currency-delete-pricing-zone">%s</a>',
				esc_html__( 'Delete', 'user-registration' )
			),
		);

		return sprintf(
			'%1$s %2$s',
			$name,
			$this->row_actions( $actions, false )
		);
	}

	public function ur_get_exchange_rate( $exchange_rate, $local_currency ) {

		if ( empty( $exchange_rate ) || empty( $local_currency[0] ) ) {
			return '—';
		}

		$base_currency  = get_option( 'user_registration_payment_currency', 'USD' );
		$local_currency = esc_html( $local_currency[0] );
		$exchange_rate  = floatval( $exchange_rate );

		ob_start();
		?>
		<div class="ur-exchange-rate">
			<span class="ur-exchange-base">
				<?php echo esc_html( '1 ' . $base_currency ); ?>
			</span>

			<span class="ur-exchange-separator">=</span>

			<span class="ur-exchange-local">
				<?php echo esc_html( $exchange_rate . ' ' . $local_currency ); ?>
			</span>
		</div>
		<?php
		return ob_get_clean();
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
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		UR_Base_Layout::render_layout(
			$this,
			array(
				'page'           => 'local-currency-settings',
				'title'          => esc_html__( 'Pricing Zone', 'user-registration' ),
				'add_new_action' => 'manage_pricing_zone'
			)
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		UR_Base_Layout::no_items( 'Pricing zone' );
	}
}
