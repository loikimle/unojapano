<?php

/**
 * UserRegistrationPayments Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @package UserRegistrationPayments/Functions
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

add_filter('user_registration_field_keys', 'ur_get_payment_field_type', 10, 2);
add_filter('user_registration_single_item_admin_template', 'ur_add_single_item_template');
add_filter('user_registration_total_field_admin_template', 'ur_add_total_field_template');
add_filter('user_registration_multiple_choice_admin_template', 'ur_add_multiple_choice_payment_template');
add_filter('user_registration_subscription_plan_admin_template', 'ur_add_subscription_plan_payment_template');
add_filter('user_registration_quantity_field_admin_template', 'ur_add_quantity_field_template');
add_filter('user_registration_sanitize_field', 'ur_sanitize_payment_fields', 10, 2);
add_filter('user_registration_payments_currencies', 'ur_support_extra_currencies');

add_filter('user_registration_form_field_single_item_path', 'ur_add_single_item_field');
add_filter('user_registration_form_field_total_field_path', 'ur_add_total_field');
add_filter('user_registration_form_field_multiple_choice_path', 'ur_add_multiple_choice_payment_field');
add_filter('user_registration_form_field_subscription_plan_path', 'ur_add_subscription_plan_payment_field');
add_filter('user_registration_form_field_quantity_field_path', 'ur_add_quantity_field');

// Register coupon field
// add_filter( 'user_registration_form_field_coupon', 'ur_register_coupon_field' );
if (ur_check_module_activation('coupon')) {
	add_filter('user_registration_form_field_coupon_path', 'ur_add_coupon_field_path');
	add_filter('user_registration_coupon_admin_template', 'ur_add_coupon_template');
	add_filter('user_registration_coupon_field_advance_class', 'coupon_field_advance_settings');
}


/**
 * Add coupon field path
 */
function ur_add_coupon_field_path() {

	include_once __DIR__ . '/form/class-ur-form-field-coupon.php';

}

/**
 * Captcha field template
 *
 * @return  string
 */
function ur_add_coupon_template()
{

	$path = __DIR__ . '/form/views/admin/admin-coupon-field.php';

	return $path;
}

/**
 * Sanitize payment fields on frontend submit
 *
 * @param mixed  $form_data Form Data.
 * @param string $field_key Field Key.
 *
 * @return array
 */
function ur_sanitize_payment_fields($form_data, $field_key)
{
	switch ($field_key) {
		case 'single_item':
			$form_data->value = user_registration_sanitize_amount($form_data->value, 'USD');
			break;
	}

	return $form_data;
}

/**
 * Add single item field
 */
function ur_add_single_item_field()
{
	include_once __DIR__ . '/form/class-ur-form-field-single-item.php';
}

/**
 * Add total field
 */
function ur_add_total_field()
{
	include_once __DIR__ . '/form/class-ur-form-field-total.php';
}

/*
 * Add Multiple Choice Payment field
 */
function ur_add_multiple_choice_payment_field()
{
	include_once __DIR__ . '/form/class-ur-form-field-multiple-choice.php';
}

/*
 * Add Subscription Plan field
 */
function ur_add_subscription_plan_payment_field()
{
	include_once __DIR__ . '/form/class-ur-form-field-subscription-plan.php';
}

/**
 * Add quantity field
 */
function ur_add_quantity_field()
{
	include_once __DIR__ . '/form/class-ur-form-field-quantity.php';
}

/**
 * Single item field template
 *
 * @return  string
 */
function ur_add_single_item_template()
{
	$path = __DIR__ . '/form/views/admin/admin-single-item.php';

	return $path;
}

/**
 * Total field template
 *
 * @return  string
 */
function ur_add_total_field_template()
{
	$path = __DIR__ . '/form/views/admin/admin-total-field.php';

	return $path;
}

/*
 * Multiple Choice field template
 *
 * @return  string
 */
function ur_add_multiple_choice_payment_template()
{
	$path = __DIR__ . '/form/views/admin/admin-multiple-choice.php';

	return $path;
}

/*
 * Subscription Plan field template
 *
 * @return  string
 */
function ur_add_subscription_plan_payment_template()
{
	$path = __DIR__ . '/form/views/admin/admin-subscription-plan.php';

	return $path;
}

/*
 * Quantity field template
 *
 * @return  string
 */
function ur_add_quantity_field_template()
{
	$path = __DIR__ . '/form/views/admin/admin-quantity-field.php';

	return $path;
}

/**
 * Assign field type to single item
 *
 * @param string $field_type Field Type.
 * @param string $field_key Field Key.
 *
 * @return string
 */
function ur_get_payment_field_type($field_type, $field_key)
{

	if ('single_item' === $field_key) {
		$field_type = 'single_item';
	}
	if ('total_field' === $field_key) {
		$field_type = 'total_field';
	}
	if ('multiple_choice' === $field_key) {
		$field_type = 'multiple_choice';
	}
	if ('subscription_plan' === $field_key) {
		$field_type = 'subscription_plan';
	}
	if ('quantity_field' === $field_key) {
		$field_type = 'quantity_field';
	}

	return $field_type;
}

/**
 * All payment fields
 *
 * @return  array
 */
function user_registration_payment_fields()
{
	return apply_filters(
		'user_registration_payment_fields',
		array(
			'single_item',
			'total_field',
			'multiple_choice',
			'subscription_plan',
			'quantity_field',
		)
	);
}



/**
 * Check if range is payment slider
 *
 * @param string $field_name Field Name.
 * @param int    $form_id Form ID.
 *
 * @return  boolean $payment_slider
 * @since 1.1.4
 */
function check_is_range_payment_slider($field_name, $form_id)
{
	$post_content_array = ($form_id) ? UR()->form->get_form($form_id, array('content_only' => true)) : array();
	$payment_slider     = false;

	if (! is_null($post_content_array)) {
		foreach ($post_content_array as $post_content_row) {
			foreach ($post_content_row as $post_content_grid) {
				foreach ($post_content_grid as $fields) {
					if (isset($fields->general_setting->field_name) && $field_name === $fields->general_setting->field_name && 'range' === $fields->field_key && (isset($fields->advance_setting->enable_payment_slider) && ur_string_to_bool($fields->advance_setting->enable_payment_slider))) {
						$payment_slider = true;
					}
				}
			}
		}
	}

	return $payment_slider;
}

/**
 * Support Extra currencies
 *
 * @param array $currencies currency.
 *
 * @return array $currencies.
 * @since 1.4.3
 */
function ur_support_extra_currencies($currencies)
{
	$extra_currencies = array(
		'CNY' => array(
			'name'                => esc_html__('Chinese Renmenbi ', 'user-registration'),
			'symbol'              => '&yen;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'RON' => array(
			'name'                => esc_html__('Romanian Leu', 'user-registration'),
			'symbol'              => 'lei',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'HRK' => array(
			'name'                => esc_html__('Croatian kuna', 'user-registration'),
			'symbol'              => 'kn',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'INR' => array(
			'name'                => esc_html__('Indian rupee', 'user-registration'),
			'symbol'              => '&#8377;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'TRY' => array(
			'name'                => esc_html__('Turkish lira', 'user-registration'),
			'symbol'              => '&#8378;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'NGN' => array(
			'name'                => esc_html__('Nigerian naira', 'user-registration'),
			'symbol'              => '&#8358;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'ZMW' => array(
			'name'                => esc_html__('Zambian Kwacha', 'user-registration'),
			'symbol'              => 'ZK',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
		'GHS' => array(
			'name'                => esc_html__('Ghanaian cedi', 'user-registration'),
			'symbol'              => 'GH&#8373;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		),
	);

	$currencies = array_merge($currencies, $extra_currencies);

	return $currencies;
}

if (! function_exists('ur_add_enable_selling_price_options')) {
	/**
	 * Enable Discount price options.
	 */
	function ur_add_enable_selling_price_options($general_setting, $id)
	{

		if ('user_registration_multiple_choice' === $id) {
			$setting_array   = array(
				'setting_id'  => 'selling-price',
				'type'        => 'toggle',
				'label'       => __('Enable Selling Price', 'user-registration'),
				'name'        => 'ur_general_setting[selling_price]',
				'placeholder' => '',
				'required'    => true,
				'default'     => 'false',
				'tip'         => __('Check this option to enable selling price of this field.', 'user-registration'),
			);
			$index           = array_search('description', array_keys($general_setting));
			$general_setting = array_slice($general_setting, 0, $index + 1, true) + array('selling_price' => $setting_array) + array_slice($general_setting, $index + 1, null, true);
		}
		if ('user_registration_subscription_plan' === $id) {
			$setting_array   = array(
				'setting_id'  => 'selling-price',
				'type'        => 'toggle',
				'label'       => __('Enable Selling Price', 'user-registration'),
				'name'        => 'ur_general_setting[selling_price]',
				'placeholder' => '',
				'required'    => true,
				'default'     => 'false',
				'tip'         => __('Check this option to enable selling price of this field.', 'user-registration'),
			);
			$index           = array_search('description', array_keys($general_setting));
			$general_setting = array_slice($general_setting, 0, $index + 1, true) + array('selling_price' => $setting_array) + array_slice($general_setting, $index + 1, null, true);
		}

		return $general_setting;
	}
}
add_filter('user_registration_field_options_general_settings', 'ur_add_enable_selling_price_options', 10, 2);

if (!function_exists('ur_pro_generate_pdf_file')) {

	/**
	 * Initialize PDF document with basic settings.
	 *
	 * @param array $config Configuration array.
	 * @return TCPDF PDF object.
	 */
	function ur_pro_init_pdf($config)
	{
		extract($config);

		$pdf = new TCPDF($orientation, PDF_UNIT, $paper_size, true, 'UTF-8', false);
		$pdf->SetCreator('Invoice Generator');
		$pdf->SetAuthor($company_name);
		$pdf->SetTitle('Invoice');
		$pdf->SetSubject('Invoice PDF');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins($margin_left, $margin_top, $margin_right);
		$pdf->SetAutoPageBreak(TRUE, $margin_bottom);
		$pdf->AddPage();

		return $pdf;
	}

	/**
	 * Draw company header information.
	 *
	 * @param TCPDF $pdf PDF object.
	 * @param array $config Configuration array.
	 */
	function ur_pro_draw_company_header($pdf, $config)
	{
		extract($config);

		if( ! empty( $company_logo ) ) {
			$imagePath = urlToPath( $company_logo );
			if( $imagePath && file_exists( $imagePath ) ) {
				$pdf->Image( $imagePath, $margin_left, 10, 20 );
			}
		}

		$pdf->SetFont('helvetica', 'B', 20);
		$pdf->SetTextColor($color_primary_dark[0], $color_primary_dark[1], $color_primary_dark[2]);
		$pdf->Cell(0, 0, $company_name, 0, 1, 'L');

		$pdf->SetFont('helvetica', '', 9);
		$pdf->SetTextColor($color_text_muted[0], $color_text_muted[1], $color_text_muted[2]);
		$pdf->Ln(4);

		if( ! empty( $company_detail ) ) {
			//Override business info when business editor field is filled.
			$pdf->writeHTML($company_detail, true, false, true, false, 'L');
		} else {
			if (!empty($company_address)) {
				$pdf->Cell(0, 5, $company_address, 0, 1, 'L');
			}
			if (!empty($company_city)) {
				$pdf->Cell(0, 5, $company_city, 0, 1, 'L');
			}
			if (!empty($company_email)) {
				$pdf->Cell(0, 5, $company_email, 0, 1, 'L');
			}
			if (!empty($company_phone)) {
				$pdf->Cell(0, 5, $company_phone, 0, 1, 'L');
			}
		}
	}

	/**
	 * Draw invoice information section (right side).
	 *
	 * @param TCPDF $pdf PDF object.
	 * @param array $config Configuration array.
	 */
	function ur_pro_draw_invoice_info($pdf, $config)
	{
		extract($config);

		$pdf->SetY($start_y);
		$pdf->SetFont('helvetica', 'B', 24);
		$pdf->SetTextColor($color_primary_light[0], $color_primary_light[1], $color_primary_light[2]);
		$pdf->SetX(150);
		$pdf->Cell(40, 0, 'INVOICE', 0, 1, 'R');

		$pdf->SetFont('helvetica', '', 9);
		$pdf->SetTextColor($color_text_muted[0], $color_text_muted[1], $color_text_muted[2]);
		$pdf->SetX(150);
		$pdf->Cell(40, 5, 'Invoice #: ' . $invoice_number, 0, 1, 'R');
		$pdf->SetX(150);
		$pdf->Cell(40, 5, 'Date: ' . $invoice_date, 0, 1, 'R');
		if( ! empty( $invoice_due_date) ) {
			$pdf->SetX(150);
			$pdf->Cell(40, 5, 'Due Date: ' . $invoice_due_date, 0, 1, 'R');
		}

		// // Badge with small top margin
		// $pdf->Ln(3); // Add small margin top
		// $pdf->SetFillColor($color_badge_bg[0], $color_badge_bg[1], $color_badge_bg[2]);
		// $pdf->SetTextColor($color_badge_text[0], $color_badge_text[1], $color_badge_text[2]);
		// $pdf->SetFont('helvetica', 'B', 8);
		// $pdf->SetX(190 - $badge_width);
		// $pdf->Cell($badge_width, $badge_height, $invoice_status, 0, 0, 'C', true);
		// $pdf->Ln(2);

		// Badge spacing
		$pdf->Ln(3);

		// Colors
		$pdf->SetFillColor($color_badge_bg[0], $color_badge_bg[1], $color_badge_bg[2]);
		$pdf->SetTextColor($color_badge_text[0], $color_badge_text[1], $color_badge_text[2]);

		$pdf->SetFont('helvetica', 'B', 8);

		// Position
		$x = 190 - $badge_width;
		$y = $pdf->GetY();

		// Padding
		$padding_x = 3;
		$padding_y = 1.5;

		// Draw rounded rectangle
		$radius = 2; // corner radius
		$pdf->RoundedRect(
			$x,
			$y,
			$badge_width,
			$badge_height,
			$radius,
			'1111',
			'F' // Fill only
		);

		// Text inside with padding
		$pdf->SetXY($x + $padding_x, $y + $padding_y);
		$pdf->Cell(
			$badge_width - ($padding_x * 2),
			$badge_height - ($padding_y * 2),
			$invoice_status,
			0,
			0,
			'C'
		);

		$pdf->Ln($badge_height + 2);
	}

	/**
	 * Draw separator line.
	 *
	 * @param TCPDF $pdf PDF object.
	 * @param array $config Configuration array.
	 */
	function ur_pro_draw_separator($pdf, $config)
	{
		extract($config);

		$maxY = max($pdf->GetY(), 60);
		$pdf->SetY($maxY);
		$pdf->Ln(5);
		$pdf->SetDrawColor($color_border[0], $color_border[1], $color_border[2]);
		$pdf->SetLineWidth(0.5);
		$pdf->Line($margin_left, $pdf->GetY(), 190, $pdf->GetY());
		$pdf->Ln(10);
	}

	/**
	 * Draw bill to section.
	 *
	 * @param TCPDF $pdf PDF object.
	 * @param array $config Configuration array.
	 */
	function ur_pro_draw_bill_to($pdf, $config)
	{
		extract($config);

		$pdf->SetFont('helvetica', 'B', 8);
		$pdf->SetTextColor($color_text_label[0], $color_text_label[1], $color_text_label[2]);
		$pdf->Cell(0, 0, 'BILL TO', 0, 1, 'L');
		$pdf->Ln(4);

		if( ! empty( $customer_detail ) ) {
			$pdf->SetFont('helvetica', 'B', 10);
			$pdf->SetTextColor($color_primary_light[0], $color_primary_light[1], $color_primary_light[2]);
			$pdf->writeHTML($customer_detail, true, false, true, false, 'L');
		} else {
			$pdf->SetFont('helvetica', 'B', 10);
			$pdf->SetTextColor($color_primary_dark[0], $color_primary_dark[1], $color_primary_dark[2]);
			$pdf->Cell(0, 0, $customer_name, 0, 1, 'L');
			$pdf->Ln(3);

			$pdf->SetFont('helvetica', '', 9);
			$pdf->SetTextColor($color_primary_light[0], $color_primary_light[1], $color_primary_light[2]);
			if (!empty($customer_address)) {
				$pdf->Cell(0, 5, $customer_address, 0, 1, 'L');
			}
			if (!empty($customer_city)) {
				$pdf->Cell(0, 5, $customer_city, 0, 1, 'L');
			}
			if (!empty($customer_email)) {
				$pdf->Cell(0, 5, $customer_email, 0, 1, 'L');
			}
		}
		$pdf->Ln(12);
	}

	/**
	 * Draw items table with proper text wrapping and height calculation.
	 *
	 * @param TCPDF $pdf PDF object.
	 * @param array $config Configuration array.
	 */
	function ur_pro_draw_items_table($pdf, $config)
	{
		extract($config);

		// Table header (no borders) - align with BILL TO section
		$header_start_y = $pdf->GetY();
		// Start table at margin_left to align with BILL TO section
		$pdf->SetX($margin_left);
		$header_start_x = $margin_left;

		$pdf->SetFillColor($color_header_bg[0], $color_header_bg[1], $color_header_bg[2]);
		$pdf->SetTextColor($color_text_muted[0], $color_text_muted[1], $color_text_muted[2]);
		$pdf->SetFont('helvetica', 'B', 8);

		// Header cells aligned with BILL TO section (no padding)
		$pdf->SetX($header_start_x);
		$pdf->Cell($col1_width, 8, 'DESCRIPTION', 0, 0, 'L', true);
		$pdf->SetX($header_start_x + $col1_width);
		$pdf->Cell($col2_width, 8, 'PERIOD', 0, 0, 'L', true);
		$pdf->SetX($header_start_x + $col1_width + $col2_width);
		$pdf->Cell($col3_width, 8, 'AMOUNT', 0, 1, 'R', true);

		// Add very subtle bottom border on header row
		$header_end_y = $pdf->GetY();
		$pdf->SetDrawColor($color_border[0], $color_border[1], $color_border[2]);
		$pdf->SetLineWidth(0.15);
		$pdf->SetX($margin_left);
		$pdf->Line($margin_left, $header_end_y, 190, $header_end_y);

		// Add spacing between header and items
		$pdf->Ln(2);

		// Table row - align with BILL TO section (no padding)
		$currentX = $margin_left;
		$currentY = $pdf->GetY();
		$text_start_y = $currentY + 3;

		// Draw description text using MultiCell for wrapping - aligned with BILL TO
		// Constrain width to prevent overflow into PERIOD column
		$pdf->SetFillColor(255, 255, 255);
		$pdf->SetXY($currentX, $text_start_y);
		$pdf->SetFont('helvetica', 'B', 10);
		$pdf->SetTextColor($color_primary_light[0], $color_primary_light[1], $color_primary_light[2]);
		$description_start_y = $pdf->GetY();
		$description_x = $pdf->GetX();

		// Calculate exact maximum width - ensure text never exceeds DESCRIPTION column boundary
		// The PERIOD column starts at $currentX + $col1_width, so we must stay within $col1_width
		$max_description_x = $currentX + $col1_width;
		$description_width = $max_description_x - $description_x - 2; // 2 unit safety margin

		// Ensure we don't exceed the column width
		if ($description_width > $col1_width) {
			$description_width = $col1_width - 2;
		}

		$pdf->SetX($description_x);
		$pdf->MultiCell($description_width, 5, $item_description, 0, 'L', false);
		$description_end_y = $pdf->GetY();
		$description_height = $description_end_y - $description_start_y;

		// Draw detail text using MultiCell for wrapping
		$detail_end_y = $description_end_y;
		if ( ! empty( $item_detail_text ) ) {
			// Add more spacing between description and detail text
			$pdf->SetXY($description_x, $description_end_y + 3);
			$pdf->SetFont('helvetica', '', 8);
			$pdf->SetTextColor($color_text_muted[0], $color_text_muted[1], $color_text_muted[2]);
			$detail_start_y = $pdf->GetY();
			// Use same constrained width to ensure text stays within DESCRIPTION column
			$pdf->SetX($description_x);
			$pdf->MultiCell($description_width, 4, $item_detail_text, 0, 'L', false);
			$detail_end_y = $pdf->GetY();
		}

		// Calculate actual row height needed (including both description and detail text)
		$content_height = $detail_end_y - $text_start_y;
		$actual_row_height = max($row_height, $content_height + 6);

		// No borders - removed Rect() calls for cleaner look

		// Position period and amount cells vertically centered - aligned with header
		$period_y = $currentY + ($actual_row_height / 2) - 2.5;
		$pdf->SetXY($currentX + $col1_width, $period_y);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->SetTextColor($color_primary_light[0], $color_primary_light[1], $color_primary_light[2]);
		$pdf->Cell($col2_width, 5, $item_period, 0, 0, 'L', false);

		$pdf->SetXY($currentX + $col1_width + $col2_width, $period_y);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->SetTextColor($color_primary_light[0], $color_primary_light[1], $color_primary_light[2]);
		$pdf->Cell($col3_width, 5, $item_amount, 0, 0, 'R', false);

		$pdf->SetY($currentY + $actual_row_height);

		// Add very subtle bottom border on table row
		$pdf->SetDrawColor($color_border[0], $color_border[1], $color_border[2]);
		$pdf->SetLineWidth(0.15);
		$pdf->SetX($margin_left);
		// $pdf->Line($margin_left, $pdf->GetY(), 190, $pdf->GetY());

		// Add more spacing between items and totals section
		// $pdf->Ln(20);

		// Subtle separator line (lighter and thinner)
		// $pdf->SetDrawColor($color_border[0], $color_border[1], $color_border[2]);
		// $pdf->SetLineWidth(0.2);
		// $pdf->SetX($margin_left);
		// $pdf->Line($margin_left, $pdf->GetY(), 190, $pdf->GetY());
		$pdf->Ln(16);
	}

	/**
	 * Draw totals section.
	 *
	 * @param TCPDF $pdf PDF object.
	 * @param array $config Configuration array.
	 */
	function ur_pro_draw_totals($pdf, $config)
	{
		extract($config);

		$pdf->SetX($totals_x);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->SetTextColor($color_text_muted[0], $color_text_muted[1], $color_text_muted[2]);

		$pdf->SetX($totals_x);
		$pdf->Cell(30, 8, 'Subtotal', 0, 0, 'L');
		$pdf->Cell(30, 8, $subtotal, 0, 1, 'R');

		if ($is_tax_enabled) {
			$pdf->SetX($totals_x);
			$pdf->Cell(30, 8, $tax_label, 0, 0, 'L');
			$pdf->Cell(30, 8, $tax_amount, 0, 1, 'R');
		}

		// Add spacing before Total row
		$pdf->Ln(4);

		$pdf->SetX($totals_x);
		$pdf->SetFillColor($color_header_bg[0], $color_header_bg[1], $color_header_bg[2]);
		$pdf->SetFont('helvetica', 'B', 12);
		$pdf->SetTextColor($color_primary_dark[0], $color_primary_dark[1], $color_primary_dark[2]);
		// No borders on Total row, just background fill
		$pdf->Cell(30, 8, 'Total', 0, 0, 'L', true);
		$pdf->Cell(30, 8, $total_amount, 0, 1, 'R', true);
	}


	function urlToPath( $url ) {
		$parsed = parse_url( $url );

		if ( ! isset($parsed[ 'path' ] ) ) {
			return false;
		}

		return rtrim( $_SERVER[ 'DOCUMENT_ROOT' ], '/' ) . $parsed[ 'path' ] ;
	}

	/**
	 * Draw footer section.
	 *
	 * @param TCPDF $pdf PDF object.
	 * @param array $config Configuration array.
	 */
	function ur_pro_draw_footer($pdf, $config)
	{
		extract($config);

		$pdf->SetY($footer_y);
		$pdf->SetDrawColor($color_border[0], $color_border[1], $color_border[2]);
		$pdf->SetLineWidth(0.3);
		$pdf->Line($margin_left, $pdf->GetY(), 190, $pdf->GetY());
		$pdf->Ln(6);

		$pdf->SetFont('helvetica', '', 9);
		$pdf->SetTextColor($color_text_label[0], $color_text_label[1], $color_text_label[2]);
		$pdf->writeHTML($footer_notes, true, false, true, false, 'C');
	}


	/**
	 * Generate pdf file for user.
	 *
	 * @param array $args Configuration arguments.
	 * @return TCPDF PDF object.
	 */
	function ur_pro_generate_pdf_file($args)
	{
		// Accept either a string (HTML) or an array of options
		$args = (array)$args;
		// Default configuration
		$defaults = array(
			'html' => '',
			'paper_size' => 'A4',
			'orientation' => defined('PDF_PAGE_ORIENTATION') ? PDF_PAGE_ORIENTATION : 'P',
			'fontname' => ur_add_pdf_fonts(),
			'font_size' => 12,
			'rtl' => false,

			// Company
			'company_name' => get_option('urm_business_name', get_bloginfo('name')),
			'company_address' => get_option('urm_business_address_line_1', ''),
			'company_city' => get_option('urm_business_address_city', ''),
			'company_state' => get_option('urm_business_address_state', ''),
			'company_zip_code' => get_option('urm_business_address_postal'),
			'company_email' => get_option('urm_business_email', get_option('admin_email')),
			'company_phone' => get_option('urm_business_phone', ''),
			'company_logo_html' => '<img src="' . get_option( 'urm_invoice_business_logo' ) . '">',
			'company_logo' => get_option( 'urm_invoice_business_logo' ),

			// Invoice
			'invoice_number' => '',
			'invoice_date' => '',
			'invoice_due_date' => '',
			'invoice_status' => '',

			// Customer
			'customer_name' => '',
			'customer_address' => '',
			'customer_city' => '',
			'customer_email' => '',
			'customer_detail' => '',

			// Item
			'item_description' => '',
			'item_detail_text' => '',
			'item_period' => '',
			'item_amount' => '',

			// Totals
			'subtotal' => '',
			'tax_label' => '',
			'tax_amount' => '',
			'total_amount' => '',

			// Footer
			'footer_notes' => '',

			// Colors
			'color_primary_dark' => array(31, 41, 55),
			'color_primary_light' => array(55, 65, 81),
			'color_text_muted' => array(107, 114, 128),
			'color_text_label' => array(156, 163, 175),
			'color_header_bg' => array(249, 250, 251),
			'color_badge_bg' => array(209, 250, 229),
			'color_badge_text' => array(6, 95, 70),
			'color_border' => array(229, 231, 235),

			// Layout
			'margin_left' => 20,
			'margin_right' => 20,
			'margin_top' => 20,
			'margin_bottom' => 20,
			'badge_width' => 20,
			'badge_height' => 7,
			'col1_width' => 80,
			'col2_width' => 60,
			'col3_width' => 30,
			'row_height' => 16,
			'totals_x' => 130,
			'start_y' => 20,
			'footer_y' => -45,
		);

		$config = wp_parse_args($args, $defaults);

		// Strip HTML tags and decode HTML entities from text fields
		// First decode entities, then strip tags, then decode again to handle any remaining entities
		$config['item_description'] = html_entity_decode(strip_tags(html_entity_decode($config['item_description'], ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$config['item_detail_text'] = html_entity_decode(strip_tags(html_entity_decode($config['item_detail_text'], ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES | ENT_HTML5, 'UTF-8');

		// Remove any remaining HTML tags that might have been missed (more aggressive cleaning)
		$config['item_description'] = preg_replace('/<[^>]*>/', '', $config['item_description']);
		$config['item_detail_text'] = preg_replace('/<[^>]*>/', '', $config['item_detail_text']);

		// Clean up any extra whitespace
		$config['item_description'] = trim($config['item_description']);
		$config['item_detail_text'] = trim($config['item_detail_text']);

		// Initialize PDF
		$pdf = ur_pro_init_pdf($config);

		// If raw HTML was provided, write it (appended)
		//Backward compatibility.
		if ( ! empty( $html ) ) {
			$pdf->SetFont( 'dejavusans', '', 10);
			$pdf->writeHTML( $html, true, false, true, false, '' );
		} else {
			//New invoices use non-HTML version.
			// Draw sections
			ur_pro_draw_company_header($pdf, $config);
			ur_pro_draw_invoice_info($pdf, $config);
			ur_pro_draw_separator($pdf, $config);
			ur_pro_draw_bill_to($pdf, $config);
			ur_pro_draw_items_table($pdf, $config);
			ur_pro_draw_totals($pdf, $config);
			ur_pro_draw_footer($pdf, $config);
		}

		return $pdf;
	}
}
