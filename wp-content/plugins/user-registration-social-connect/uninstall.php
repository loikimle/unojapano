<?php
/**
 * UserRegistrationSocialConnect Uninstall
 *
 * Uninstalls the plugin and associated data.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistrationSocialConnect/Uninstaller
 * @version  1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Only remove ALL product and page data if URSC_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if ( defined( 'URSC_REMOVE_ALL_DATA' ) && true === URSC_REMOVE_ALL_DATA ) {

	wp_cache_flush();
}
