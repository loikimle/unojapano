<?php
namespace LDQIE;
/**
 * Mapping Function For Import
 *
 * @class     Mapping_Import
 * @version   1.0.0
 * @package   LDQIE/Classes/Import/Mapping
 * @category  Class
 * @author    WooNinjas
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Mapping_Import Class.
 */
class Mapping_Import {

    /**
     * Class Constructor
     */
    public function __construct () {
        add_action ( "admin_notices", array( __CLASS__, "admin_notifications" ) );
        add_filter ( "leasndash_quiz_default_viewPofileStatistics", array( __CLASS__, "view_statistics" ) );
    }

    /**
     * Notifications
     */
    public static function admin_notifications () {
        $current_page_id = get_current_screen()->id;
        if ( "admin_page_ldAdvQuiz" == $current_page_id ) {
            if ( is_null( $_GET["module"] ) ) { ?>
                <div class="notice notice-warning">
                    <p><?php _e( "<strong>Warning: </strong>This area is for developers.", "ldqie" ); ?></p>
                    <p><?php _e( "<strong>Course administrators</strong> should not use this area for importing and exporting", "ldqie" ); ?></p>
                </div> <?php
            }
        }
    }

    public function view_statistics() {
        $values = get_option("quiz_default");
        if( isset( $values['viewProfileStatistics'] ) && $values['viewProfileStatistics'] == 'on'  ) {
            return true;
        }
    }
}