<?php
/**
 * Compatibility Action.
 * 
 * @package ULTP\Notice
 * @since v.1.1.0
 */

namespace ULTP;

defined('ABSPATH') || exit;

/**
 * Compatibility class.
 */
class Compatibility{

    /**
	 * Setup class.
	 *
	 * @since v.1.1.0
	 */
    public function __construct() {
        add_action( 'upgrader_process_complete', array($this, 'plugin_upgrade_completed'), 10, 2 );
    }


    /**
	 * Compatibility Class Run after Plugin Upgrade
	 *
	 * @since v.1.1.0
	 */
    public function plugin_upgrade_completed( $upgrader_object, $options ) {
        if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] )) {
            foreach( $options['plugins'] as $plugin ) {
                if ($plugin == ULTP_BASE ) {
                    $set_settings = array(
                        'disable_view_cookies' => '',
                        'ultp_category' => 'false',
                        'ultp_templates' => 'true',
                        'ultp_elementor' => 'true',
                        'ultp_table_of_content' => 'true',
                        'ultp_builder' => 'true',
                        'post_grid_1' => 'yes',
                        'post_grid_2' => 'yes',
                        'post_grid_3' => 'yes',
                        'post_grid_4' => 'yes',
                        'post_grid_5' => 'yes',
                        'post_grid_6' => 'yes',
                        'post_grid_7' => 'yes',
                        'post_list_1' => 'yes',
                        'post_list_2' => 'yes',
                        'post_list_3' => 'yes',
                        'post_list_4' => 'yes',
                        'post_module_1' => 'yes',
                        'post_module_2' => 'yes',
                        'post_slider_1' => 'yes',
                        'post_slider_2' => 'yes',
                        'heading' => 'yes',
                        'image' => 'yes',
                        'taxonomy' => 'yes',
                        'wrapper' => 'yes',
                        'news_ticker' => 'yes',
                        'builder_advance_post_meta' => 'yes',
                        'builder_archive_title'     => 'yes',
                        'builder_author_box'        => 'yes',
                        'builder_post_next_previous'=> 'yes',
                        'builder_post_author_meta'  => 'yes',
                        'builder_post_breadcrumb'   => 'yes',
                        'builder_post_category'     => 'yes',
                        'builder_post_comment_count'=> 'yes',
                        'builder_post_comments'     => 'yes',
                        'builder_post_content'      => 'yes',
                        'builder_post_date_meta'    => 'yes',
                        'builder_post_excerpt'      => 'yes',
                        'builder_post_featured_image'=> 'yes',
                        'builder_post_reading_time' => 'yes',
                        'builder_post_social_share' => 'yes',
                        'builder_post_tag'          => 'yes',
                        'builder_post_title'        => 'yes',
                        'builder_post_view_count'   => 'yes',
                        'save_version' => rand(1, 1000)
                    );
                    $addon_data = ultimate_post()->get_setting();
                    foreach ($set_settings as $key => $value) {
                        if (!isset($addon_data[$key])) {
                            ultimate_post()->set_setting($key, $value);
                        }
                    }
                    
            
                    // License Check And Active
                    if (defined('ULTP_PRO_VER')) {
                        $license = get_option( 'edd_ultp_license_key' );
                        $response = wp_remote_post( 
                            'https://www.wpxpo.com',
                            array(
                                'timeout' => 15,
                                'sslverify' => false,
                                'body' => array(
                                    'edd_action' => 'activate_license',
                                    'license'    => $license,
                                    'item_id'    => 181,
                                    'url'        => home_url()
                                )
                            )
                        );
                        if (!is_wp_error( $response ) && 200 == wp_remote_retrieve_response_code( $response ) ) {
                            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                            update_option( 'edd_ultp_license_status', $license_data->license );    
                        }
                    }
                }
            }
        }
    }
}