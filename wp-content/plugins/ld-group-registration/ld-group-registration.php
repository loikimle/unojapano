<?php
/**
 * Plugin Name:       LearnDash Group Registration
 * Plugin URI:        https://wisdmlabs.com/group-registration-for-learndash/
 * Description:       Allows Group leaders to purchase a course (or courses) on behalf of students, and then enroll members to the course.
 * Version:           4.1.5
 * Author:            WisdmLabs
 * Author URI:        https://wisdmlabs.com
 * Text Domain:       wdm_ld_group
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
update_option('wdm_ld_group_license_key_sites','hotrowordpressdotcom_wdm_ld_group');
update_option('wdm_ld_group_license_max_site',99);
update_option('edd_wdm_ld_group_license_key', 'hotrowordpressdotcom_wdm_ld_group');
update_option('edd_wdm_ld_group_license_status', 'valid');

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Set Plugin Version
 */
define( 'LD_GROUP_REGISTRATION_VERSION', '4.1.5' );

/**
 * Set Default Plugin File Path Constant
 */
if ( ! defined( 'WDM_LDGR_PLUGIN_FILE' ) ) {
	define( 'WDM_LDGR_PLUGIN_FILE', __FILE__ );
}

/**
 * Set the plugin slug as default text domain.
 */
if ( ! defined( 'WDM_LDGR_TXT_DOMAIN' ) ) {
	define( 'WDM_LDGR_TXT_DOMAIN', 'wdm_ld_group' );
}

/**
 * Set Default Plugin Directory Path Constant
 */
if ( ! defined( 'WDM_LDGR_PLUGIN_DIR' ) ) {
	define( 'WDM_LDGR_PLUGIN_DIR', __DIR__ );
}

require plugin_dir_path( __FILE__ ) . 'includes/class-ld-group-registration.php';

/**
 * Begins execution of the plugin.
 */
function run_ld_group_registration() {

	$plugin = new \LdGroupRegistration\Includes\Ld_Group_Registration();
	$plugin->run();

}
run_ld_group_registration();

