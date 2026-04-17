<?php
/**
 * Handling plugin licenses
 *
 * @link       https://wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/includes
 */

namespace LdGroupRegistration\Includes;

/**
 * LD Group Registration License
 */
class Ld_Group_Registration_License {
	/**
	 * Load license
	 */
	public function load_license() {
		global $wdm_grp_plugin_data;

		if ( empty( $wdm_grp_plugin_data ) ) {
			$wdm_grp_plugin_data = include_once plugin_dir_path( dirname( __FILE__ ) ) . 'license.config.php';
			new \Licensing\WdmLicense( $wdm_grp_plugin_data );
		}
	}

	/**
	 * Check if license available
	 *
	 * @return boolean    True if active, false otherwise.
	 */
	public static function is_available_license() {
		global $wdm_grp_plugin_data;

		if ( empty( $wdm_grp_plugin_data ) ) {
			$wdm_grp_plugin_data = include_once plugin_dir_path( dirname( __FILE__ ) ) . 'license.config.php';
			new \Licensing\WdmLicense( $wdm_grp_plugin_data );
		}

		$get_data_from_db = \Licensing\WdmLicense::checkLicenseAvailiblity( $wdm_grp_plugin_data['pluginSlug'], false );

		if ( 'available' == $get_data_from_db ) {
			return true;
		}

		return false;
	}
}
