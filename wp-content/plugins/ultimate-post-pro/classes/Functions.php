<?php
namespace ULTP_PRO;

defined('ABSPATH') || exit;

class Functions{

    public function get_yoast_meta($post_id = 0) {
        return get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
    }

    public function get_rankmath_meta($post_id = 0) {
        return get_post_meta($post_id, 'rank_math_description', true);
    }
    
    public function get_aioseo_meta($post_id = 0) {
        return get_post_meta($post_id, '_aioseo_description', true);
    }

    public function get_seopress_meta($post_id = 0) {
        return get_post_meta($post_id, '_seopress_titles_desc', true);
    }
    
    public function get_squirrly_meta($post_id = 0) {
        if ($post_id) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "qss` WHERE URL = %s OR URL = %s", urldecode_deep(get_permalink($post_id)), get_permalink($post_id)), OBJECT );
            if (isset($row->seo)) {
                $data = maybe_unserialize($row->seo);
                if (isset($data['description'])) {
                    return $data['description'];
                }
            }
        }
        return '';
    }

    public function set_addons_data() {
        $is_installed = get_option('ultp_pro_install_before', false);
        if ($is_installed) {
            $addon_data = get_option('ultp_options', array());
            if (!isset($addon_data['ultp_category'])) {
                $addon_data['ultp_category'] = 'true';
            }
            if (!isset($addon_data['ultp_builder'])) {
                $addon_data['ultp_builder'] = 'true';   
            }
            if (!isset($addon_data['ultp_progressbar'])) {
                $addon_data['ultp_progressbar'] = 'false';   
            }
            update_option('ultp_options', $addon_data);
            $GLOBALS['ultp_settings'] = $addon_data;
        } else {
            update_option('ultp_pro_install_before', '1');
        }
    }


    public function get_taxonomy_list($default = false) {
        $default_remove = $default ? array('post_tag', 'category', 'nav_menu', 'link_category', 'post_format') : array('nav_menu', 'link_category', 'post_format');
        $taxonomy = get_taxonomies();
        foreach ($taxonomy as $key => $val) {
            if( in_array($key, $default_remove) ){
                unset( $taxonomy[$key] );
            }
        }
        return array_keys($taxonomy);
    }
    

    // Image Placeholder
    public function img_placeholder($type = 'small') {
        switch ($type) {
            case 'small':
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAG4AAABLAQMAAACr9CA9AAAAA1BMVEX///+nxBvIAAAAAXRSTlMAQObYZgAAABZJREFUOI1jYMADmEe5o9xR7iiXQi4A4BsA388WUyMAAAAASUVORK5CYII=';
                break;

            case 'wide':
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAKCAYAAADVTVykAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAB9JREFUeNpi/P//P8NAAiaGAQajDhh1wKgDRh0AEGAAQTcDEcKDrpMAAAAASUVORK5CYII=';
                break;

            case 'square':
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEX///+nxBvIAAAAAXRSTlMAQObYZgAAAApJREFUCJljYAAAAAIAAfRxZKYAAAAASUVORK5CYII=';
                break;

            case 'slider':
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKYAAABkCAMAAAA7drv6AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAAZQTFRF////AAAAVcLTfgAAAAF0Uk5TAEDm2GYAAAAqSURBVHja7MEBDQAAAMKg909tDjegAAAAAAAAAAAAAAAAAAAAAH5NgAEAQTwAAWZtItYAAAAASUVORK5CYII=';
                break;
            
            default:
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAYYAAADcAQMAAABOLJSDAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAACJJREFUaIHtwTEBAAAAwqD1T20ND6AAAAAAAAAAAAAA4N8AKvgAAUFIrrEAAAAASUVORK5CYII=';
                break;
        }
    }

    // Get Size of the Image
    public function get_size($name = '') {
        global $_wp_additional_image_sizes;
        $image_size = $name ? ( isset($_wp_additional_image_sizes[$name]) ? $_wp_additional_image_sizes[$name] : array_values($_wp_additional_image_sizes)[0] ) : array_values($_wp_additional_image_sizes)[0];
        
        return ' width="'.$image_size['width'].'" height="'.$image_size['height'].'" ';
    }

    // Template Conditions
    public function conditions( $type = 'return' ) {
        $page_id = '';
        $conditions = get_option('ultp_builder_conditions', array());
        if (isset($conditions['archive'])) {
            if (!empty($conditions['archive'])) {
                foreach ($conditions['archive'] as $key => $val) {
                    if (is_archive()) {
                        if (in_array('include/archive', $val)) {
                            $page_id = $key;
                        }
                        if (is_category()) {
                            if (in_array('include/archive/category', $val)) {
                                $page_id = $key;
                            }
                            $taxonomy = get_queried_object();
                            if ($this->in_string_part('include/archive/category/'.$taxonomy->term_id, $val)) {
                                $page_id = $key;
                            }
                        } else if (is_tag()) {
                            if (in_array('include/archive/post_tag', $val)) {
                                $page_id = $key;
                            }
                            $taxonomy = get_queried_object();
                            if ($this->in_string_part('include/archive/post_tag/'.$taxonomy->term_id, $val)) {
                                $page_id = $key;
                            }
                        } else if (is_date()) {
                            if (in_array('include/archive/date', $val)) {
                                $page_id = $key;
                            }
                        } else if (is_author()) {
                            if (in_array('include/archive/author', $val)) {
                                $page_id = $key;
                            }
                            $author_id = get_the_author_meta('ID');
                            if ($this->in_string_part('include/archive/author/'.$author_id, $val)) {
                                $page_id = $key;
                            }
                        } else {
                            $taxonomy_list = $this->get_taxonomy_list(true);
                            foreach ($taxonomy_list as $value) {
                                if (in_array('include/archive/'.$value, $val)) {
                                    $page_id = $key;
                                }
                                $taxonomy = get_queried_object();
                                if (isset($taxonomy->term_id)) {
                                    if ($this->in_string_part('include/archive/'.$value.'/'.$taxonomy->term_id, $val)) {
                                        $page_id = $key;
                                    }
                                }
                            }
                        }
                    } else if (is_search()) {
                        if (in_array('include/archive/search', $val)) {
                            $page_id = $key;
                        }
                    } else if (is_404()) {
                        if (in_array('include/archive/error', $val)) {
                            $page_id = $key;
                        }
                    }
                }
            }  
        }


        if ($type == 'return') {
            return $page_id;
        }
        if ($type == 'includes') {
            return $page_id ? ULTP_PRO_PATH.'addons/builder/Archive_Template.php' : '';
        }
    }

    // Content Print
    public function content($post_id) {
        $content_post = get_post($post_id);
        $content = $content_post->post_content;
        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);
        echo $content;
    }

    // is Free Plugin Ready
    public function is_ultp_free_ready(){
        $active_plugins = get_option( 'active_plugins', array() );
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, array_keys(get_site_option( 'active_sitewide_plugins', array() )));
        }
        if (file_exists(WP_PLUGIN_DIR.'/ultimate-post/ultimate-post.php') && in_array('ultimate-post/ultimate-post.php', $active_plugins)) {
            return true;
        } else {
            return false;
        }
    }

    // String part Exits
    public function in_string_part($part, $data) {
        $return = false;
        foreach ($data as $val) {
            if (strpos($val, $part) !== false) {
                $return = true;
                break;
            }
        }
        return $return;
    }

}
