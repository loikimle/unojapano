<?php
namespace ULTP_PRO;

defined('ABSPATH') || exit;

class ULTP_PRO_Initialization{

    public function __construct(){
        $this->requires();
    }

    // Require File
    public function requires() {
        require_once ULTP_PRO_PATH.'classes/Notice.php';
        new \ULTP_PRO\Notice();
        if ( ultimate_post_pro()->is_ultp_free_ready() ) {
            $this->include_addons();
        }

        // Pro Plugin Updater Class
        require_once ULTP_PRO_PATH.'classes/updater/License.php';
        new \ULTP_PRO\License();

        add_action( 'activated_plugin', array($this, 'activation_redirect'));
    }

    

    public function activation_redirect($plugin) {
        // Set Init Data
        ultimate_post_pro()->set_addons_data();

        // Redirect To License Page
        if( $plugin == 'ultimate-post-pro/ultimate-post-pro.php' ) {
            exit(wp_redirect(admin_url('admin.php?page=ultp-license')));
        }
    }

    // Include Addons directory
	public function include_addons() {
		$addons_dir = array_filter(glob(ULTP_PRO_PATH.'addons/*'), 'is_dir');
		if (count($addons_dir) > 0) {
			foreach( $addons_dir as $key => $value ) {
				$addon_dir_name = str_replace(dirname($value).'/', '', $value);
				$file_name = ULTP_PRO_PATH . 'addons/'.$addon_dir_name.'/init.php';
				if ( file_exists($file_name) ) {
					include_once $file_name;
				}
			}
		}
    }
    
    
}