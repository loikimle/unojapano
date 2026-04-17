<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Form_Field_Shipping_Country
 * @version  1.0.0
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Form_Field_Shipping_Country Class
 */
class UR_Form_Field_Shipping_Country extends UR_Form_Field {

	private static $_instance;


	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}


	public function get_country() {

		return array(
			'AF' => __( 'Afghanistan', 'user-registration-woocommerce' ),
			'AX' => __( '&#197;land Islands', 'user-registration-woocommerce' ),
			'AL' => __( 'Albania', 'user-registration-woocommerce' ),
			'DZ' => __( 'Algeria', 'user-registration-woocommerce' ),
			'AS' => __( 'American Samoa', 'user-registration-woocommerce' ),
			'AD' => __( 'Andorra', 'user-registration-woocommerce' ),
			'AO' => __( 'Angola', 'user-registration-woocommerce' ),
			'AI' => __( 'Anguilla', 'user-registration-woocommerce' ),
			'AQ' => __( 'Antarctica', 'user-registration-woocommerce' ),
			'AG' => __( 'Antigua and Barbuda', 'user-registration-woocommerce' ),
			'AR' => __( 'Argentina', 'user-registration-woocommerce' ),
			'AM' => __( 'Armenia', 'user-registration-woocommerce' ),
			'AW' => __( 'Aruba', 'user-registration-woocommerce' ),
			'AU' => __( 'Australia', 'user-registration-woocommerce' ),
			'AT' => __( 'Austria', 'user-registration-woocommerce' ),
			'AZ' => __( 'Azerbaijan', 'user-registration-woocommerce' ),
			'BS' => __( 'Bahamas', 'user-registration-woocommerce' ),
			'BH' => __( 'Bahrain', 'user-registration-woocommerce' ),
			'BD' => __( 'Bangladesh', 'user-registration-woocommerce' ),
			'BB' => __( 'Barbados', 'user-registration-woocommerce' ),
			'BY' => __( 'Belarus', 'user-registration-woocommerce' ),
			'BE' => __( 'Belgium', 'user-registration-woocommerce' ),
			'PW' => __( 'Belau', 'user-registration-woocommerce' ),
			'BZ' => __( 'Belize', 'user-registration-woocommerce' ),
			'BJ' => __( 'Benin', 'user-registration-woocommerce' ),
			'BM' => __( 'Bermuda', 'user-registration-woocommerce' ),
			'BT' => __( 'Bhutan', 'user-registration-woocommerce' ),
			'BO' => __( 'Bolivia', 'user-registration-woocommerce' ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'user-registration-woocommerce' ),
			'BA' => __( 'Bosnia and Herzegovina', 'user-registration-woocommerce' ),
			'BW' => __( 'Botswana', 'user-registration-woocommerce' ),
			'BV' => __( 'Bouvet Island', 'user-registration-woocommerce' ),
			'BR' => __( 'Brazil', 'user-registration-woocommerce' ),
			'IO' => __( 'British Indian Ocean Territory', 'user-registration-woocommerce' ),
			'VG' => __( 'British Virgin Islands', 'user-registration-woocommerce' ),
			'BN' => __( 'Brunei', 'user-registration-woocommerce' ),
			'BG' => __( 'Bulgaria', 'user-registration-woocommerce' ),
			'BF' => __( 'Burkina Faso', 'user-registration-woocommerce' ),
			'BI' => __( 'Burundi', 'user-registration-woocommerce' ),
			'KH' => __( 'Cambodia', 'user-registration-woocommerce' ),
			'CM' => __( 'Cameroon', 'user-registration-woocommerce' ),
			'CA' => __( 'Canada', 'user-registration-woocommerce' ),
			'CV' => __( 'Cape Verde', 'user-registration-woocommerce' ),
			'KY' => __( 'Cayman Islands', 'user-registration-woocommerce' ),
			'CF' => __( 'Central African Republic', 'user-registration-woocommerce' ),
			'TD' => __( 'Chad', 'user-registration-woocommerce' ),
			'CL' => __( 'Chile', 'user-registration-woocommerce' ),
			'CN' => __( 'China', 'user-registration-woocommerce' ),
			'CX' => __( 'Christmas Island', 'user-registration-woocommerce' ),
			'CC' => __( 'Cocos (Keeling) Islands', 'user-registration-woocommerce' ),
			'CO' => __( 'Colombia', 'user-registration-woocommerce' ),
			'KM' => __( 'Comoros', 'user-registration-woocommerce' ),
			'CG' => __( 'Congo (Brazzaville)', 'user-registration-woocommerce' ),
			'CD' => __( 'Congo (Kinshasa)', 'user-registration-woocommerce' ),
			'CK' => __( 'Cook Islands', 'user-registration-woocommerce' ),
			'CR' => __( 'Costa Rica', 'user-registration-woocommerce' ),
			'HR' => __( 'Croatia', 'user-registration-woocommerce' ),
			'CU' => __( 'Cuba', 'user-registration-woocommerce' ),
			'CW' => __( 'Cura&ccedil;ao', 'user-registration-woocommerce' ),
			'CY' => __( 'Cyprus', 'user-registration-woocommerce' ),
			'CZ' => __( 'Czech Republic', 'user-registration-woocommerce' ),
			'DK' => __( 'Denmark', 'user-registration-woocommerce' ),
			'DJ' => __( 'Djibouti', 'user-registration-woocommerce' ),
			'DM' => __( 'Dominica', 'user-registration-woocommerce' ),
			'DO' => __( 'Dominican Republic', 'user-registration-woocommerce' ),
			'EC' => __( 'Ecuador', 'user-registration-woocommerce' ),
			'EG' => __( 'Egypt', 'user-registration-woocommerce' ),
			'SV' => __( 'El Salvador', 'user-registration-woocommerce' ),
			'GQ' => __( 'Equatorial Guinea', 'user-registration-woocommerce' ),
			'ER' => __( 'Eritrea', 'user-registration-woocommerce' ),
			'EE' => __( 'Estonia', 'user-registration-woocommerce' ),
			'ET' => __( 'Ethiopia', 'user-registration-woocommerce' ),
			'FK' => __( 'Falkland Islands', 'user-registration-woocommerce' ),
			'FO' => __( 'Faroe Islands', 'user-registration-woocommerce' ),
			'FJ' => __( 'Fiji', 'user-registration-woocommerce' ),
			'FI' => __( 'Finland', 'user-registration-woocommerce' ),
			'FR' => __( 'France', 'user-registration-woocommerce' ),
			'GF' => __( 'French Guiana', 'user-registration-woocommerce' ),
			'PF' => __( 'French Polynesia', 'user-registration-woocommerce' ),
			'TF' => __( 'French Southern Territories', 'user-registration-woocommerce' ),
			'GA' => __( 'Gabon', 'user-registration-woocommerce' ),
			'GM' => __( 'Gambia', 'user-registration-woocommerce' ),
			'GE' => __( 'Georgia', 'user-registration-woocommerce' ),
			'DE' => __( 'Germany', 'user-registration-woocommerce' ),
			'GH' => __( 'Ghana', 'user-registration-woocommerce' ),
			'GI' => __( 'Gibraltar', 'user-registration-woocommerce' ),
			'GR' => __( 'Greece', 'user-registration-woocommerce' ),
			'GL' => __( 'Greenland', 'user-registration-woocommerce' ),
			'GD' => __( 'Grenada', 'user-registration-woocommerce' ),
			'GP' => __( 'Guadeloupe', 'user-registration-woocommerce' ),
			'GU' => __( 'Guam', 'user-registration-woocommerce' ),
			'GT' => __( 'Guatemala', 'user-registration-woocommerce' ),
			'GG' => __( 'Guernsey', 'user-registration-woocommerce' ),
			'GN' => __( 'Guinea', 'user-registration-woocommerce' ),
			'GW' => __( 'Guinea-Bissau', 'user-registration-woocommerce' ),
			'GY' => __( 'Guyana', 'user-registration-woocommerce' ),
			'HT' => __( 'Haiti', 'user-registration-woocommerce' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'user-registration-woocommerce' ),
			'HN' => __( 'Honduras', 'user-registration-woocommerce' ),
			'HK' => __( 'Hong Kong', 'user-registration-woocommerce' ),
			'HU' => __( 'Hungary', 'user-registration-woocommerce' ),
			'IS' => __( 'Iceland', 'user-registration-woocommerce' ),
			'IN' => __( 'India', 'user-registration-woocommerce' ),
			'ID' => __( 'Indonesia', 'user-registration-woocommerce' ),
			'IR' => __( 'Iran', 'user-registration-woocommerce' ),
			'IQ' => __( 'Iraq', 'user-registration-woocommerce' ),
			'IE' => __( 'Ireland', 'user-registration-woocommerce' ),
			'IM' => __( 'Isle of Man', 'user-registration-woocommerce' ),
			'IL' => __( 'Israel', 'user-registration-woocommerce' ),
			'IT' => __( 'Italy', 'user-registration-woocommerce' ),
			'CI' => __( 'Ivory Coast', 'user-registration-woocommerce' ),
			'JM' => __( 'Jamaica', 'user-registration-woocommerce' ),
			'JP' => __( 'Japan', 'user-registration-woocommerce' ),
			'JE' => __( 'Jersey', 'user-registration-woocommerce' ),
			'JO' => __( 'Jordan', 'user-registration-woocommerce' ),
			'KZ' => __( 'Kazakhstan', 'user-registration-woocommerce' ),
			'KE' => __( 'Kenya', 'user-registration-woocommerce' ),
			'KI' => __( 'Kiribati', 'user-registration-woocommerce' ),
			'KW' => __( 'Kuwait', 'user-registration-woocommerce' ),
			'KG' => __( 'Kyrgyzstan', 'user-registration-woocommerce' ),
			'LA' => __( 'Laos', 'user-registration-woocommerce' ),
			'LV' => __( 'Latvia', 'user-registration-woocommerce' ),
			'LB' => __( 'Lebanon', 'user-registration-woocommerce' ),
			'LS' => __( 'Lesotho', 'user-registration-woocommerce' ),
			'LR' => __( 'Liberia', 'user-registration-woocommerce' ),
			'LY' => __( 'Libya', 'user-registration-woocommerce' ),
			'LI' => __( 'Liechtenstein', 'user-registration-woocommerce' ),
			'LT' => __( 'Lithuania', 'user-registration-woocommerce' ),
			'LU' => __( 'Luxembourg', 'user-registration-woocommerce' ),
			'MO' => __( 'Macao S.A.R., China', 'user-registration-woocommerce' ),
			'MK' => __( 'Macedonia', 'user-registration-woocommerce' ),
			'MG' => __( 'Madagascar', 'user-registration-woocommerce' ),
			'MW' => __( 'Malawi', 'user-registration-woocommerce' ),
			'MY' => __( 'Malaysia', 'user-registration-woocommerce' ),
			'MV' => __( 'Maldives', 'user-registration-woocommerce' ),
			'ML' => __( 'Mali', 'user-registration-woocommerce' ),
			'MT' => __( 'Malta', 'user-registration-woocommerce' ),
			'MH' => __( 'Marshall Islands', 'user-registration-woocommerce' ),
			'MQ' => __( 'Martinique', 'user-registration-woocommerce' ),
			'MR' => __( 'Mauritania', 'user-registration-woocommerce' ),
			'MU' => __( 'Mauritius', 'user-registration-woocommerce' ),
			'YT' => __( 'Mayotte', 'user-registration-woocommerce' ),
			'MX' => __( 'Mexico', 'user-registration-woocommerce' ),
			'FM' => __( 'Micronesia', 'user-registration-woocommerce' ),
			'MD' => __( 'Moldova', 'user-registration-woocommerce' ),
			'MC' => __( 'Monaco', 'user-registration-woocommerce' ),
			'MN' => __( 'Mongolia', 'user-registration-woocommerce' ),
			'ME' => __( 'Montenegro', 'user-registration-woocommerce' ),
			'MS' => __( 'Montserrat', 'user-registration-woocommerce' ),
			'MA' => __( 'Morocco', 'user-registration-woocommerce' ),
			'MZ' => __( 'Mozambique', 'user-registration-woocommerce' ),
			'MM' => __( 'Myanmar', 'user-registration-woocommerce' ),
			'NA' => __( 'Namibia', 'user-registration-woocommerce' ),
			'NR' => __( 'Nauru', 'user-registration-woocommerce' ),
			'NP' => __( 'Nepal', 'user-registration-woocommerce' ),
			'NL' => __( 'Netherlands', 'user-registration-woocommerce' ),
			'NC' => __( 'New Caledonia', 'user-registration-woocommerce' ),
			'NZ' => __( 'New Zealand', 'user-registration-woocommerce' ),
			'NI' => __( 'Nicaragua', 'user-registration-woocommerce' ),
			'NE' => __( 'Niger', 'user-registration-woocommerce' ),
			'NG' => __( 'Nigeria', 'user-registration-woocommerce' ),
			'NU' => __( 'Niue', 'user-registration-woocommerce' ),
			'NF' => __( 'Norfolk Island', 'user-registration-woocommerce' ),
			'MP' => __( 'Northern Mariana Islands', 'user-registration-woocommerce' ),
			'KP' => __( 'North Korea', 'user-registration-woocommerce' ),
			'NO' => __( 'Norway', 'user-registration-woocommerce' ),
			'OM' => __( 'Oman', 'user-registration-woocommerce' ),
			'PK' => __( 'Pakistan', 'user-registration-woocommerce' ),
			'PS' => __( 'Palestinian Territory', 'user-registration-woocommerce' ),
			'PA' => __( 'Panama', 'user-registration-woocommerce' ),
			'PG' => __( 'Papua New Guinea', 'user-registration-woocommerce' ),
			'PY' => __( 'Paraguay', 'user-registration-woocommerce' ),
			'PE' => __( 'Peru', 'user-registration-woocommerce' ),
			'PH' => __( 'Philippines', 'user-registration-woocommerce' ),
			'PN' => __( 'Pitcairn', 'user-registration-woocommerce' ),
			'PL' => __( 'Poland', 'user-registration-woocommerce' ),
			'PT' => __( 'Portugal', 'user-registration-woocommerce' ),
			'PR' => __( 'Puerto Rico', 'user-registration-woocommerce' ),
			'QA' => __( 'Qatar', 'user-registration-woocommerce' ),
			'RE' => __( 'Reunion', 'user-registration-woocommerce' ),
			'RO' => __( 'Romania', 'user-registration-woocommerce' ),
			'RU' => __( 'Russia', 'user-registration-woocommerce' ),
			'RW' => __( 'Rwanda', 'user-registration-woocommerce' ),
			'BL' => __( 'Saint Barth&eacute;lemy', 'user-registration-woocommerce' ),
			'SH' => __( 'Saint Helena', 'user-registration-woocommerce' ),
			'KN' => __( 'Saint Kitts and Nevis', 'user-registration-woocommerce' ),
			'LC' => __( 'Saint Lucia', 'user-registration-woocommerce' ),
			'MF' => __( 'Saint Martin (French part)', 'user-registration-woocommerce' ),
			'SX' => __( 'Saint Martin (Dutch part)', 'user-registration-woocommerce' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'user-registration-woocommerce' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'user-registration-woocommerce' ),
			'SM' => __( 'San Marino', 'user-registration-woocommerce' ),
			'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'user-registration-woocommerce' ),
			'SA' => __( 'Saudi Arabia', 'user-registration-woocommerce' ),
			'SN' => __( 'Senegal', 'user-registration-woocommerce' ),
			'RS' => __( 'Serbia', 'user-registration-woocommerce' ),
			'SC' => __( 'Seychelles', 'user-registration-woocommerce' ),
			'SL' => __( 'Sierra Leone', 'user-registration-woocommerce' ),
			'SG' => __( 'Singapore', 'user-registration-woocommerce' ),
			'SK' => __( 'Slovakia', 'user-registration-woocommerce' ),
			'SI' => __( 'Slovenia', 'user-registration-woocommerce' ),
			'SB' => __( 'Solomon Islands', 'user-registration-woocommerce' ),
			'SO' => __( 'Somalia', 'user-registration-woocommerce' ),
			'ZA' => __( 'South Africa', 'user-registration-woocommerce' ),
			'GS' => __( 'South Georgia/Sandwich Islands', 'user-registration-woocommerce' ),
			'KR' => __( 'South Korea', 'user-registration-woocommerce' ),
			'SS' => __( 'South Sudan', 'user-registration-woocommerce' ),
			'ES' => __( 'Spain', 'user-registration-woocommerce' ),
			'LK' => __( 'Sri Lanka', 'user-registration-woocommerce' ),
			'SD' => __( 'Sudan', 'user-registration-woocommerce' ),
			'SR' => __( 'Suriname', 'user-registration-woocommerce' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'user-registration-woocommerce' ),
			'SZ' => __( 'Swaziland', 'user-registration-woocommerce' ),
			'SE' => __( 'Sweden', 'user-registration-woocommerce' ),
			'CH' => __( 'Switzerland', 'user-registration-woocommerce' ),
			'SY' => __( 'Syria', 'user-registration-woocommerce' ),
			'TW' => __( 'Taiwan', 'user-registration-woocommerce' ),
			'TJ' => __( 'Tajikistan', 'user-registration-woocommerce' ),
			'TZ' => __( 'Tanzania', 'user-registration-woocommerce' ),
			'TH' => __( 'Thailand', 'user-registration-woocommerce' ),
			'TL' => __( 'Timor-Leste', 'user-registration-woocommerce' ),
			'TG' => __( 'Togo', 'user-registration-woocommerce' ),
			'TK' => __( 'Tokelau', 'user-registration-woocommerce' ),
			'TO' => __( 'Tonga', 'user-registration-woocommerce' ),
			'TT' => __( 'Trinidad and Tobago', 'user-registration-woocommerce' ),
			'TN' => __( 'Tunisia', 'user-registration-woocommerce' ),
			'TR' => __( 'Turkey', 'user-registration-woocommerce' ),
			'TM' => __( 'Turkmenistan', 'user-registration-woocommerce' ),
			'TC' => __( 'Turks and Caicos Islands', 'user-registration-woocommerce' ),
			'TV' => __( 'Tuvalu', 'user-registration-woocommerce' ),
			'UG' => __( 'Uganda', 'user-registration-woocommerce' ),
			'UA' => __( 'Ukraine', 'user-registration-woocommerce' ),
			'AE' => __( 'United Arab Emirates', 'user-registration-woocommerce' ),
			'GB' => __( 'United Kingdom (UK)', 'user-registration-woocommerce' ),
			'US' => __( 'United States (US)', 'user-registration-woocommerce' ),
			'UM' => __( 'United States (US) Minor Outlying Islands', 'user-registration-woocommerce' ),
			'VI' => __( 'United States (US) Virgin Islands', 'user-registration-woocommerce' ),
			'UY' => __( 'Uruguay', 'user-registration-woocommerce' ),
			'UZ' => __( 'Uzbekistan', 'user-registration-woocommerce' ),
			'VU' => __( 'Vanuatu', 'user-registration-woocommerce' ),
			'VA' => __( 'Vatican', 'user-registration-woocommerce' ),
			'VE' => __( 'Venezuela', 'user-registration-woocommerce' ),
			'VN' => __( 'Vietnam', 'user-registration-woocommerce' ),
			'WF' => __( 'Wallis and Futuna', 'user-registration-woocommerce' ),
			'EH' => __( 'Western Sahara', 'user-registration-woocommerce' ),
			'WS' => __( 'Samoa', 'user-registration-woocommerce' ),
			'YE' => __( 'Yemen', 'user-registration-woocommerce' ),
			'ZM' => __( 'Zambia', 'user-registration-woocommerce' ),
			'ZW' => __( 'Zimbabwe', 'user-registration-woocommerce' ),
		);

	}

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		$this->id = 'user_registration_shipping_country';

		$this->form_id = 1;

		$this->registered_fields_config = array(

			'label' => __( 'Country', 'user-registration-woocommerce' ),

			'icon'  => 'ur-icon ur-icon-flag',
		);

		$this->field_defaults = array(

			'default_label'      => __( 'Country', 'user-registration-woocommerce' ),

			'default_field_name' => 'shipping_country',
		);
	}


	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list "

				class="ur-registered-item draggable"

                data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}


	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		// TODO: Implement validation() method.
	}
}

return UR_Form_Field_Shipping_Country::get_instance();
