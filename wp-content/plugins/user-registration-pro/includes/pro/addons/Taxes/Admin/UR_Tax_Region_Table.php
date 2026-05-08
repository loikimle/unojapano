<?php
namespace WPEverest\URMembership\Taxes\Admin;

defined( 'ABSPATH' ) || exit;

use UR_Base_Layout;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}

if ( ! class_exists( 'UR_Base_Layout' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/admin/class-ur-admin-base-layout.php';
}

class UR_Tax_Region_Table extends \UR_List_Table {

	private $regions = array();
	private $countries = array();
	private $states = array();

	public function __construct() {

		parent::__construct( array(
			'singular' => 'tax_region',
			'plural'   => 'tax_regions',
			'ajax'     => false,
		) );

		$option = get_option( 'user_registration_tax_regions_and_rates', array() );

		$this->regions   = ! empty( $option['regions'] ) ? $option['regions'] : array();
		$this->countries = ur_get_country_lists();
		$this->states    = ur_get_state_lists();
	}

	public function get_columns() {
		return array(
			'cb'        	=> '<input type="checkbox" />',
			'country' => __( 'Country', 'user-registration' ),
			'state'   => __( 'State', 'user-registration' ),
			'rate'    => __( 'Tax Rate', 'user-registration' ),
			// 'actions' => __( 'Actions', 'user-registration' ),
		);
	}

	public function prepare_items() {

		$per_page     = 10;
		$current_page = $this->get_pagenum();
		$data         = array();

		foreach ( $this->regions as $country_code => $region ) {

			$data[] = array(
				'country' => $this->countries[ $country_code ] ?? $country_code,
				'state'   => __( 'Whole country', 'user-registration' ),
				'rate'    => ! empty( $region['rate'] ) ? $region['rate'] . '%' : 0 .'%',
				// 'actions' => $this->get_actions_html( $country_code ),
				'country_code' => $country_code,
			);

			if ( ! empty( $region['states'] ) ) {
				foreach ( $region['states'] as $state_code => $rate ) {
					$data[] = array(
						'country' => $this->countries[ $country_code ] ?? $country_code,
						'state'   => $this->states[ $country_code ][ $state_code ] ?? $state_code,
						'rate'    => $rate . '%',
						// 'actions' => $this->get_actions_html( $country_code, $state_code ),
						'country_code' => $country_code,
						'state_code' => $state_code
					);
				}
			}
		}

		$total_items = count( $data );

		$this->items = array_slice(
			$data,
			( $current_page - 1 ) * $per_page,
			$per_page
		);

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		) );
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		UR_Base_Layout::render_layout(
			$this,
			array(
				'page'           => 'tax-settings',
				'title'          => esc_html__( 'Tax Regions & Rates', 'user-registration' ),
				'add_new_action' => 'manage_tax'
			)
		);
	}

	public function column_country( $item ) {
		$name = esc_html( $item['country'] );
		$country_code = esc_html( $item['country_code'] );
		$state_code   = esc_html( ! empty( $item['state_code'] ) ? $item['state_code'] : '' );

		$actions = array(
			'edit'   => sprintf(
				'<a href="#" class="urm-manage-tax-region-btn" data-action="edit">%s</a>',
				esc_html__( 'Edit', 'user-registration' )
			),
			'delete' => sprintf(
				'<a href="#" class="ur-tax-region-delete" data-country="%s" data-state="%s">%s</a>',
				esc_attr( $country_code ),
				esc_attr( $state_code ),
				esc_html__( 'Delete', 'user-registration' )
			)
		);

		return sprintf(
			'%1$s %2$s',
			$name,
			$this->row_actions( $actions, false )
		);
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

	public function column_default( $item, $column_name ) {
		return $item[ $column_name ] ?? '';
	}

	private function get_actions_html( $country, $state = '' ) {
		return sprintf(
			'<button class="ur-tax-region-delete" data-country="%s" data-state="%s">Delete</button>',
			esc_attr( $country ),
			esc_attr( $state )
		);
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
	 * Checkbox column
	 */
	public function column_cb( $item ) {
		$id = ! empty( $item['state_code'] ) ? $item['state_code'] : $item['country_code'];
		return sprintf(
			'<input type="checkbox" name="currency_ids[]" value="%s" />',
			$id
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		UR_Base_Layout::no_items( 'Tax regions & rates' );
	}

	protected function get_bulk_actions() {
		return [
			'delete' => __( 'Delete', 'user-registration' ),
		];
	}

}
