<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/includes
 */

namespace LdGroupRegistration\Includes;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
class Ld_Group_Registration_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    4.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wdm_ld_group',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
