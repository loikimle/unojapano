<?php
namespace LDQIE;
/**
 * Plugin Name: LearnDash Quiz Import Export
 * Plugin URI: https://wooninjas.com/wn-products/learndash-quiz-importexport/
 * Description: This plugin will allow you to import and export LearnDash quiz questions from and to XLS file.
 * Version: 3.2
 * Author: Wooninjas
 * Author URI: http://wooninjas.com/
 * Text Domain: ldqie
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( !defined ( 'ABSPATH' ) ) exit;

/**
 * Check if LearnDash is enabled
 */
if( file_exists( ABSPATH . 'wp-admin/includes/plugin.php' ) ) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
function require_dependency() {

    if ( !class_exists( 'SFWD_LMS' ) ) {
        deactivate_plugins ( plugin_basename ( __FILE__ ), true );
        $class = 'notice is-dismissible error';
        $message = __( 'LearnDash Quiz Import/Export requires <a href="https://www.learndash.com">LearnDash</a> plugin to be activated.', 'ldqie' );
        printf ( "<div id='message' class='%s'> <p>%s</p></div>", $class, $message );
    }
}
add_action( "admin_notices", __NAMESPACE__ . "\\require_dependency" );


// Directory
define( 'LDQIE\DIR', plugin_dir_path ( __FILE__ ) );
define( 'LDQIE\DIR_FILE', DIR . basename ( __FILE__ ) );
define( 'LDQIE\INCLUDES_DIR', trailingslashit ( DIR . 'includes' ) );
define( 'LDQIE\BASE_DIR', plugin_basename(__FILE__)); // Plugin Slug

// URLS
define( 'LDQIE\URL', trailingslashit ( plugins_url ( '', __FILE__ ) ) );
define( 'LDQIE\ASSETS_URL', trailingslashit ( URL . 'assets' ) );

if( file_exists( INCLUDES_DIR . 'settings/init.php' ) ) {
    require_once ( INCLUDES_DIR . 'settings/init.php' );
}