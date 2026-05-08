<?php

namespace WPEverest\URM\Pro\PDFInvoice;

use UR_Admin_Settings;

defined('ABSPATH') || exit;

/**
 * PDF Invoice.
 */
class PDFInvoice
{
	/**
	 * Page name.
	 *
	 * @var string
	 */
	private $page;


	private $options = [
		'business_logo' => 'urm_business_logo',
		'business_name' => 'urm_business_name',
		'business_address' => 'urm_business_address',
		'business_phone' => 'urm_business_phone',
		'business_email' => 'urm_business_email',
		'invoice_paper_size' => 'urm_invoice_paper_size',
		'invoice_orientation' => 'urm_invoice_orientation',
		'invoice_footer_notes' => 'urm_invoice_footer_notes',
		'invoice_business_address_template' => 'urm_invoice_business_address_template',
		'invoice_starts_from' => 'urm_invoice_starts_from',
		'invoice_current_counter' => 'urm_invoice_current_counter',
	];

	/**
	 * Initialize hooks and assign protected variables;
	 */
	public function __construct()
	{
		$this->page      = 'user-registration-pdf-invoice';
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function init_hooks()
	{
		add_action('user_registration_settings_payment', array($this, 'output'));
		add_filter('user_registration_get_settings_payment', array($this, 'append_business_address_to_payment_settings'));
		add_action('urm_save_invoice-settings_payment_section', array($this, 'save_invoice_details'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_filter('user_registration_admin_field_invoice_business_info', array($this, 'render_merchant_business_address'), 10, 2);
		add_action( 'urm_save_invoice-business-info_payment_section', array( $this, 'save_invoice_business_info' ) );
		add_filter( 'user_registration_get_sections_payment', function( $sections ) {
			$sections[ 'invoice' ] = __( 'Invoices', 'user-registration' );
			return $sections;
		} );
	}
	public function get_field_option( $id ) {
		$id = $this->options[ $id ] ?? $id;
		return get_option( $id, '' );
	}

	/**
	 * Save business information settings.
	 * @param mixed $form_settings
	 * @return void
	 */
	public function save_invoice_business_info( $form_settings ) {
		foreach ( $form_settings as $option_name => $option_value ) {
			update_option( $option_name, $option_value );
		}
	}

	public function render_merchant_business_address($settings, $value){
		$descriptions = UR_Admin_Settings::get_field_description( $value );
		extract( $descriptions );

		$settings .= '<div class="user-registration-global-settings">';
		$settings .= '<label class="ur-label" for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
		$settings .= '<div class="user-registration-global-settings--field">';

		// prepare values for address sub-fields from stored options
		$sub_values = array();
		if ( ! empty( $value['settings'] ) && is_array( $value['settings'] ) ) {
			foreach ( $value['settings'] as $sub ) {
				$sub_id = isset( $sub['id'] ) ? $sub['id'] : '';
				$sub_values[ $sub_id ] = $this->get_field_option( $sub_id, '' );
			}
		}

		// default placeholders derived from sub setting titles
		$placeholders = array();
		if ( ! empty( $value['settings'] ) && is_array( $value['settings'] ) ) {
			foreach ( $value['settings'] as $sub ) {
				$placeholders[ $sub['id'] ] = isset( $sub['title'] ) ? $sub['title'] : '';
			}
		}
		$settings .= '<div class="ur-d-flex" style="flex: 1;flex-direction: column;gap: 4px;">';
		// Address Line 1
		$settings .= '<div class="ur-subfield ur-address-line1" style="min-width:160px;">';
		$settings .= '<input type="text" aria-label="' . esc_attr( $placeholders[ $value['settings'][0]['id'] ] ?? 'Address Line 1' ) . '" id="' . esc_attr( $value['id'] . '_line_1' ) . '" name="' .  esc_attr( $value['id'] . '_line_1' ) . '" class="ur-input-type-address-line1" placeholder="' . esc_attr( $placeholders[ $value['settings'][0]['id'] ] ?? 'Address Line 1' ) . '" value="' . esc_attr( $sub_values[ $value['settings'][0]['id'] ] ?? '' ) . '" />';
		$settings .= '</div>';

		// Address Line 2
		$settings .= '<div class="ur-subfield ur-address-line2" style="min-width:160px;">';
		$settings .= '<input type="text" aria-label="' . esc_attr( $placeholders[ $value['settings'][1]['id'] ] ?? 'Address Line 2' ) . '" id="' . esc_attr( $value['id'] . '_line_2' ) . '" name="' .  esc_attr( $value['id'] . '_line_2' ) . '" class="ur-input-type-address-line2" placeholder="' . esc_attr( $placeholders[ $value['settings'][1]['id'] ] ?? 'Address Line 2' ) . '" value="' . esc_attr( $sub_values[ $value['settings'][1]['id'] ] ?? '' ) . '" />';
		$settings .= '</div>';

		// city / state row (flex row)
		$settings .= '<div class="ur-subfield-group" style="display:flex;gap:8px;flex-wrap:wrap;">';
		// city
		$settings .= '<div class="ur-subfield ur-city" style="flex:1 1 150px;min-width:120px;">';
		$settings .= '<input type="text" aria-label="' . esc_attr( $placeholders[ $value['settings'][2]['id'] ] ?? 'City' ) . '" id="' . esc_attr( $value['id'] . '_city' ) . '" name="' .  esc_attr( $value['id'] . '_city' ) . '" class="ur-input-type-city" placeholder="' . esc_attr( $placeholders[ $value['settings'][2]['id'] ] ?? 'City' ) . '" value="' . esc_attr( $sub_values[ $value['settings'][2]['id'] ] ?? '' ) . '" />';
		$settings .= '</div>';

		// state
		$settings .= '<div class="ur-subfield ur-state" style="flex:1 1 120px;min-width:100px;">';
		$settings .= '<input type="text" aria-label="' . esc_attr( $placeholders[ $value['settings'][3]['id'] ] ?? 'State' ) . '" id="' . esc_attr( $value['id'] . '_state' ) . '" name="' .  esc_attr( $value['id'] . '_state' ) . '" class="ur-input-type-state" placeholder="' . esc_attr( $placeholders[ $value['settings'][3]['id'] ] ?? 'State' ) . '" value="' . esc_attr( $sub_values[ $value['settings'][3]['id'] ] ?? '' ) . '" />';
		$settings .= '</div>';
		$settings .= '</div>';

		// zip / country row
		$settings .= '<div class="ur-subfield-group" style="display:flex;gap:8px;flex-wrap:wrap;">';
		// zip
		$settings .= '<div class="ur-subfield ur-zip-code">';
		$settings .= '<input type="text" aria-label="' . esc_attr( $placeholders[ $value['settings'][4]['id'] ] ?? 'Postal Code' ) . '" id="' . esc_attr( $value['id'] . '_postal' ) . '" name="' . esc_attr( $value['id'] . '_postal' ) . '" class="ur-input-type-zip" placeholder="' . esc_attr( $placeholders[ $value['settings'][4]['id'] ] ?? 'Postal Code' ) . '" value="' . esc_attr( $sub_values[ $value['settings'][4]['id'] ] ?? '' ) . '" />';
		$settings .= '</div>';

		// country (render as disabled text input with value)
		$settings .= '<div class="ur-subfield ur-country" style="flex: 1;">';
		$settings .= '<input type="text" aria-label="' . esc_attr( $placeholders[ $value['settings'][5]['id'] ] ?? 'Country' ) . '" id="' . esc_attr( $value['id'] . '_country' ) . '" name="' . esc_attr( $value['id'] . '_country' ) . '" class="ur-input-type-country" placeholder="' . esc_attr( $placeholders[ $value['settings'][5]['id'] ] ?? 'Country' ) . '" value="' . esc_attr( $sub_values[ $value['settings'][5]['id'] ] ?? '' ) . '" />';
		$settings .= '</div>';
		$settings .= '</div>'; // end zip/country row

		// optional description
		if ( ! empty( $description ) ) {
			$settings .= wp_kses_post( $description );
		}

		$settings .= '</div>'; //flex close.

		$settings .= '</div>';
		$settings .= '</div>';

		return $settings;
	}
	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts()
	{
		$suffix = defined('SCRIPT_DEBUG') ? '' : '.min';

		if (isset($_GET['section']) && 'invoice-settings' === $_GET['section']) {
		}
	}

	/**
	 * Enqueue styles
	 *
	 * @return void
	 */
	public function enqueue_styles()
	{
		if (empty($_GET['page']) || $this->page !== $_GET['page']) {
			return;
		}
	}

	public function output()
	{
		global $current_section;
		if ('invoice' === $current_section) {
			add_filter( 'user_registration_settings_hide_save_button', '__return_true' );
			add_filter( 'user_registration_settings_save_action', '__return_true' );
			$settings = $this->get_invoice_settings();
			\UR_Admin_Settings::output_fields($settings);
		}
	}
	public function get_invoice_settings()
	{
		$invoice_settings = $this->get_raw_settings();

		$settings['sections']['invoice_options'] = $invoice_settings;

		return $settings;
	}

	/**
	 * Returns payment/business address related settings array.
	 * This provides an isolated, re-usable section array containing the
	 * business name, composite address (multiple fields), phone and email.
	 *
	 * The returned value is passed through the
	 * 'user_registration_get_business_address_settings_payment' filter so
	 * other modules can modify or consume it before it gets used in the
	 * payment settings UI.
	 *
	 * @return array Section array for business address settings
	 */
	public function get_business_address_settings()
	{
		$settings = array(
			'id'    => 'invoice-business-info',
			'title' => __('Business Information', 'user-registration'),
			'type'  => 'card',
			'desc'  => '',
			'settings' => array(
				array(
					'title' => __('Business Name', 'user-registration'),
					'desc' => __( 'Name of your business that is used in invoices.' ),
					'desc_tip' => true,
					'id'    => 'urm_business_name',
					'type'  => 'text',
					'default' => $this->get_field_option( 'urm_business_name' ),
				),
				array(
					'title' => __('Business Address', 'user-registration'),
					'desc'  => __('Business address fields used on invoices.', 'user-registration'),
					'id'    => 'urm_business_address',
					'type'  => 'invoice_business_info',
					'desc_tip' => true,
					'settings' => array(
						array(
							'title' => __('Address Line 1', 'user-registration'),
							'id'    => 'urm_business_address_line_1',
							'type'  => 'text',
							'default' => $this->get_field_option( 'urm_business_address_line_1' ),
						),
						array(
							'title' => __('Address Line 2', 'user-registration'),
							'id'    => 'urm_business_address_line_2',
							'type'  => 'text',
							'default' => $this->get_field_option( 'urm_business_address_line_2' ),
						),
						array(
							'title' => __('City', 'user-registration'),
							'id'    => 'urm_business_address_city',
							'type'  => 'text',
							'default' => $this->get_field_option( 'urm_business_address_city' ),
						),
						array(
							'title' => __('State / Province', 'user-registration'),
							'id'    => 'urm_business_address_state',
							'type'  => 'text',
							'default' => $this->get_field_option( 'urm_business_address_state' ),
						),
						array(
							'title' => __('Postal Code', 'user-registration'),
							'id'    => 'urm_business_address_postal',
							'type'  => 'text',
							'default' => $this->get_field_option( 'urm_business_address_postal' ),
						),
						array(
							'title' => __('Country', 'user-registration'),
							'id'    => 'urm_business_address_country',
							'type'  => 'text',
							'default' => $this->get_field_option( 'urm_business_address_country' ),
						),
					),
				),
				array(
					'title' => __('Business Phone', 'user-registration'),
					'desc' => __( 'Business phone number that is used in invoices.', 'user-registration' ),
					'desc_tip' => true,
					'id'    => 'urm_business_phone',
					'type'  => 'text',
					'default' => $this->get_field_option( 'urm_business_phone' ),
				),
				array(
					'title' => __('Business Email', 'user-registration'),
					'desc' => __( 'Business email used in invoices.', 'user-registration' ),
					'desc_tip' => true,
					'id'    => 'urm_business_email',
					'type'  => 'text',
					'default' => $this->get_field_option( 'urm_business_email' ),
				),
				array(
					'title' => __( 'Save', 'user-registration' ),
					'id'    => 'user_registration_business_address_save_settings',
					'type'  => 'button',
					'class' => 'payment-settings-btn'
				),
			),
		);

		/**
		 * Allow other code to modify / consume the business address settings
		 * before they are used in payment settings.
		 */
		return apply_filters('user_registration_get_business_address_settings_payment', $settings);
	}

	/**
	 * Append the business address settings into the provided payment settings
	 * array so the payment settings UI will render these fields.
	 *
	 * @param array $settings Payment settings array to append into.
	 * @return array Modified settings array
	 */
	public function append_business_address_to_payment_settings( $settings )
	{
		global $current_section;

		if ( 'store' !== $current_section ) return $settings;


		// Initialize if necessary
		if ( empty( $settings ) || ! is_array( $settings ) ) {
			$settings = array();
		}

		$business_section = $this->get_business_address_settings();

		$settings[ 'sections' ][ 'invoice_business_info' ] = $business_section;

		return $settings;
	}
	public function get_raw_settings()
	{

		return array(
			'title'    => __( 'Invoices', 'user-registration' ),
			'id'       => 'invoice-settings',
			'type'     => 'card',
			'desc'     => '',
			'settings' => array(
				array(
					'title' => __('Business Logo', 'user-registration'),
					'desc'  => __('Upload or select your business logo. Recommended size: 200x200px.', 'user-registration'),
					'id'    => 'urm_invoice_business_logo',
					'type'  => 'image',
					'css'   => 'min-width: 350px',
					'desc_tip' => true,
					'default'  => '',
				),
				array(
					'title'    => __( 'Business Address', 'user-registration' ),
					'desc'     => __( 'Business details that will appear on invoices.', 'user-registration' ),
					'desc_tip' => true,
					'id'       => 'urm_invoice_business_address',
					'type'     => 'tinymce',
					'show-ur-registration-form-button' => false,
					'show-reset-content-button' => false,
					'autoload' => true,
					'css'      => 'min-width: 350px;',
				),

				array(
					'title'    => __( 'Customer Information', 'user-registration' ),
					'desc'     => __( 'Select which customer fields should appear on invoices.', 'user-registration' ),
					'desc_tip' => true,
					'id'       => 'urm_invoice_customer_info',
					'type'     => 'tinymce',
					'show-ur-registration-form-button' => false,
					'show-reset-content-button' => false,
					'autoload' => true,
					'css'      => 'min-width: 350px;',
				),
				array(
					'title'    => __('Paper Size', 'user-registration'),
					'desc'     => __('Choose the paper size for generated invoices.', 'user-registration'),
					'id'       => 'urm_invoice_paper_size',
					'type'     => 'select',
					'options'  => array(
						'A4'     => 'A4',
						'A5'     => 'A5',
						'LETTER' => 'LETTER',
						'LEGAL' => 'LEGAL',
						'TABLOID' => 'TABLOID',
						'EXECUTIVE' => 'EXECUTIVE',
					),
					'default'  => 'A4',
					'desc_tip' => true,
				),
				array(
					'title'    => __('Orientation', 'user-registration'),
					'desc'     => __('Choose the page orientation for invoices.', 'user-registration'),
					'id'       => 'urm_invoice_orientation',
					'type'     => 'radio',
					'options'  => array(
						'portrait'  => __('Portrait', 'user-registration'),
						'landscape' => __('Landscape', 'user-registration'),
					),
					'desc_tip' => true,
					'default'  => 'portrait',
				),
				array(
					'title'             => __( 'Footer Notes', 'user-registration' ),
					'desc'              => __( 'Customize the footer content that appears at the bottom of all emails. You can use HTML and smart tags like {{blog_info}} and {{home_url}}.', 'user-registration' ),
					'id'                => 'urm_invoice_footer_content',
					'type'              => 'tinymce',
					'default'           => '<p style="margin: 0 0 12px 0; color: #6c757d; font-size: 13px; line-height: 1.5;">Thank you for your purchase!</p>
													<p style="margin: 0; font-size: 14px; line-height: 1.6;"><a href="{{home_url}}" style="color: #4A90E2; text-decoration: none; font-weight: 500;">{{blog_info}} Team</a></p>',
					'css'               => 'min-width: 350px;',
					'show-ur-registration-form-button' => false,
					'show-reset-content-button' => false,
					'autoload'          => true,
					'desc_tip'          => true,
				),
				array(
					'title'    => __('Invoice Format', 'user-registration'),
					'desc'     => __('Format of invoice number. You can use {counter} for the sequence number and {year} for current year (e.g. INV-{year}-{counter}).', 'user-registration'),
					'id'       => 'urm_invoice_format',
					'type'     => 'text',
					'default'  => 'INV-{{year}}-{{counter}}',
					'desc_tip' => true,
				),
				array(
					'title'    => __('Invoice starts from', 'user-registration'),
					'desc'     => __('The initial counter value for invoices. It is used in Invoice format {{ counter }} to seed the initial value.', 'user-registration'),
					'id'       => 'urm_invoice_starts_from',
					'type'     => 'number',
					'default'  => 1,
					'desc_tip' => true,
				),
				array(
					'title' => __('Save', 'user-registration'),
					'id'    => 'user_registration_invoice_save_settings',
					'type'  => 'button',
					'class' => 'payment-settings-btn',
					'desc_tip' => true,
				),
			),
		);
	}
	/**
	 * Save invoice details [Settings > Payments > Invoices ]
	 * 
	 * @param $settings array Settings for invoice details.
	 * 
	 * @return void
	 */
	public function save_invoice_details( $settings )
	{
		foreach ( $settings as $option_name => $option_value ) {
			update_option( $option_name, $option_value );
		}
	}
}
