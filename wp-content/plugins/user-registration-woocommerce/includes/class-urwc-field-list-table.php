<?php
/**
 * User Registration WooCommerce Field Table List
 *
 * @package UserRegistrationWooCommerce
 * @since   1.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce field list class.
 */
class URWC_Field_Table_List {

	/**
	 * Form id to list fields.
	 *
	 * @var int
	 */
	private $form_id = 0;

	/**
	 * List checkbox option key.
	 *
	 * @var string
	 */
	private $cb_option_key = '';

	/**
	 * Get Form field list table columns.
	 *
	 * @return array Columns.
	 */
	public function get_list_columns() {

		$columns                = array();
		$columns['cb']          = '<input type="checkbox" class="urwc-select-all" />';
		$columns['field_label'] = __( 'Field Label', 'user-registration-woocommerce' );
		$columns['field_name']  = __( 'Field Name', 'user-registration-woocommerce' );
		$columns['plugin']      = __( 'Plugin', 'user-registration-woocommerce' );

		return apply_filters( 'user_registration_woocommerce_field_list_table_columns', $columns );
	}

	/**
	 * Display List header.
	 */
	public function list_header() {
		$columns = $this->get_list_columns();
		$header  = '<tr>';

		foreach ( $columns as $column_key => $column_name ) {
			$header .= sprintf( '<th id="%s"> %s </th>', $column_key, $column_name );
		}

		$header .= '</tr>';

		return $header;
	}

	/**
	 * Display list content.
	 */
	public function list_content() {
		$fields    = urwc_get_form_fields( $this->form_id );
		$cb_fields = get_option( $this->cb_option_key, array() );

		if ( is_wp_error( $fields ) ) {
			return sprintf( '<tr><td colspan="4"><h1 align="center">%s</h1></td></tr>', $fields->get_error_message() );
		}

		$field_elements = '';
		if ( $fields ) {

			foreach ( $fields as $field_name => $field_details ) {
				$checked         = ( in_array( $field_name, $cb_fields ) ) ? ' checked="checked"' : '';
				$field_elements .= '<tr>';
				$field_elements .= sprintf( '<td><input type="checkbox" name="%s[]" value="%s"%s /></td>', $this->cb_option_key, $field_name, $checked );
				$field_elements .= sprintf( '<td>%s</td>', $field_details['label'] );
				$field_elements .= sprintf( '<td>%s</td>', $field_name );
				$field_elements .= sprintf( '<td>%s</td>', 'User Registration' );
				$field_elements .= '</td>';
			}
		}

		return $field_elements;
	}

	/**
	 * Display table list with header, footer and body contents.
	 *
	 * @param int    $form_id Form ID.
	 * @param string $cb_option_key Option key of checkbox.
	 */
	public function display_table_list( $form_id, $cb_option_key, $return = false ) {
		$this->form_id       = $form_id;
		$this->cb_option_key = $cb_option_key;

		ob_start();
		?>
			<table class="wp-list-table widefat fixed">
				<thead>
				<?php echo $this->list_header(); ?>
				</thead>
				<tbody>
				<?php echo $this->list_content(); ?>
				</tbody>
				<tfoot>
				<?php echo $this->list_header(); ?>
				</tfoot>
			</table>
		<?php

		$field_table = ob_get_clean();

		if ( $return ) {
			return $field_table;
		}

		echo $field_table;
	}
}
