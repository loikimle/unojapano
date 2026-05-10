<?php
/**
 * Plugin Name: User Registration Profile completeness
 * Plugin URI: https://wpuserregistration.com/features/profile-completeness
 * Description: Profile completeness addon for user registration plugin.
 * Version: 1.0.3
 * Author: WPEverest
 * Author URI: https://wpuserregistration.com
 * Text Domain: user-registration-profile-completeness
 * Domain Path: /languages/
 * UR Pro requires at least: 4.0
 * UR Pro tested up to: 4.2.0
 *
 * Copyright: © 2020 WPEverest.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package UserRegistration\ProfileCompleteness
 */

defined( 'ABSPATH' ) || exit;

// Define plugin version.
if ( ! defined( 'UR_PROFILE_COMPLETENESS_VERSION' ) ) {
	define( 'UR_PROFILE_COMPLETENESS_VERSION', '1.0.3' );
}

// Define plugin root file.
if ( ! defined( 'UR_PROFILE_COMPLETENESS_PLUGIN_FILE' ) ) {
	define( 'UR_PROFILE_COMPLETENESS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'URPCOMPLETENESS_DS' ) ) {
	define( 'URPCOMPLETENESS_DS', DIRECTORY_SEPARATOR );
}
/**
 * Autoload packages.
 *
 * We want to fail gracefully if `composer install` has not been executed yet, so we are checking for the autoloader.
 * If the autoloader is not present, let's log the failure and display a nice admin notice.
 */
$autoloader = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $autoloader ) ) {
	require $autoloader;
} else {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			sprintf(
				/* translators: 1: composer command. 2: plugin directory */
				esc_html__( 'Your installation of the User Registration Profile Completeness plugin is incomplete. Please run %1$s within the %2$s directory.', 'everest-forms-constant-contact' ),
				'`composer install`',
				'`' . esc_html( str_replace( ABSPATH, '', __DIR__ ) ) . '`'
			)
		);
	}

	/**
	 * Outputs an admin notice if composer install has not been ran.
	 */
	add_action(
		'admin_notices',
		function() {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					printf(
						/* translators: 1: composer command. 2: plugin directory */
						esc_html__( 'Your installation of the User Registration Profile Completeness plugin is incomplete. Please run %1$s within the %2$s directory.', 'everest-forms-constant-contact' ),
						'<code>composer install</code>',
						'<code>' . esc_html( str_replace( ABSPATH, '', __DIR__ ) ) . '</code>'
					);
					?>
				</p>
			</div>
			<?php
		}
	);
	return;
}

// Initialize the plugin.
add_action( 'plugins_loaded', array( 'WPEverest\\UserRegistration\\ProfileCompleteness\\ProfileCompleteness', 'instance' ) );
