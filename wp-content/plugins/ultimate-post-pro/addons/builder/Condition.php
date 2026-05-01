<?php
namespace ULTP_PRO;

defined('ABSPATH') || exit;

class Condition {
    public function __construct(){
        add_filter('template_include', array($this, 'include_builder_files'));
        add_action('admin_footer', array($this, 'builder_footer_callback'));
        add_action('admin_enqueue_scripts', array($this, 'load_media'));
    }

    // Load Media
    public function load_media() {
        if (!$this->is_builder()) {
            return;
        }

        wp_enqueue_script('builder-script', ULTP_PRO_URL.'addons/builder/builder.js', array('jquery'), ULTP_VER, true);
        wp_enqueue_style('builder-style', ULTP_PRO_URL.'addons/builder/builder.css', array(), ULTP_VER);

        wp_localize_script('builder-script', 'builder_option', array(
            'security' => wp_create_nonce('ultp-nonce'),
            'ajax' => admin_url('admin-ajax.php')
        ));
    }


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

    public function include_builder_files($template) {
        $includes = ultimate_post_pro()->conditions('includes');
        return $includes ? $includes : $template;
    }

    public function is_builder() {
        global $post;
        return isset($_GET['post_type']) ? ($_GET['post_type'] == 'ultp_builder') : (isset($post->post_type) ? ($post->post_type == 'ultp_builder') : false);
    }


    public function builder_footer_callback() {

        if ($this->is_builder()) { ?>
            <form class="ultp-builder" action="">
                <div class="ultp-builder-modal">
                    <div class="ultp-popup-wrap">
                        <input type="hidden" name="action" value="ultp_new_post">
                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('ultp-nonce'); ?>">
                        <div class="ultp-builder-wrapper">
                            <div class="ultp-builder-left">
                                <div class="ultp-builder-left-content">
                                    <div class="ultp-builder-left-title">
                                        <label><?php _e('Name of Your Template', 'ultimate-post-pro'); ?></label>
                                        <div>
                                            <input type="text" name="post_title" class="ultp-title" placeholder="<?php _e('Template Name', 'ultimate-post-pro'); ?>" />
                                        </div>
                                    </div>
                                    <div class="ultp-builder-left-title">
                                        <label><?php _e('Select Template Type', 'ultimate-post-pro'); ?></label>
                                        <div>
                                            <select name="post_filter">
                                                <option value="archive"><?php _e('Archive', 'ultimate-post-pro'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="ultp-message"></div>
                                    <div class="ultp-builder-button">
                                    <button class="ultp-new-template"><?php echo __('Create Template', 'ultimate-post-pro'); ?></button>
                                    <a class="ultp-edit-template" href="<?php echo get_edit_post_link(get_the_ID()); ?>"><?php echo __('Save & Edit Template', 'ultimate-post-pro'); ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="ultp-builder-right">
                                <div class="ultp-builder-right-title">
                                    <label>
                                        <?php _e('Where You Want to Display Your Template', 'ultimate-post-pro'); ?>
                                    </label>
                                    <span>
                                        <input type="checkbox" id="archive" name="archive" value="archive" class="ultp-single-select"/>
                                        <label for="archive"><?php _e('All Archive Pages', 'ultimate-post-pro'); ?></label>
                                    </span>
                                    <span>
                                        <input type="checkbox" id="author" name="author" value="author" class="ultp-single-select"/>
                                        <label for="author"><?php _e('All Author Pages', 'ultimate-post-pro'); ?></label>
                                    </span>
                                    <span>
                                        <input type="checkbox" id="date" name="date" value="date" class="ultp-single-select"/>
                                        <label for="date"><?php _e('All Date Pages', 'ultimate-post-pro'); ?></label>
                                    </span>
                                    <span>
                                        <input type="checkbox" id="search" name="search" value="search" class="ultp-single-select"/>
                                        <label for="search"><?php _e('Search Result', 'ultimate-post-pro'); ?></label>
                                    </span>
                                    <?php
                                    $taxonomy_list = ultimate_post_pro()->get_taxonomy_list();
                                    foreach ($taxonomy_list as $key => $val) { ?>
                                        <span>
                                            <input type="checkbox" name="<?php echo $val; ?>" id="id-<?php echo $key; ?>" value="<?php echo $val; ?>" class="ultp-single-select"/>
                                            <label for="id-<?php echo $key; ?>"><?php printf( __('All %s Pages', 'ultimate-post-pro'),  $val); ?></label>
                                        </span>
                                    <?php } ?>
                                </div>
                                <div class="ultp-multi-select">
                                    <span class="ultp-multi-select-action"><?php _e('Specific Author', 'ultimate-post-pro'); ?></span>
                                    <select class="multi-select-data select-author ultp-multi-select-hide" name="author_id[]" multiple="multiple" data-type="author"></select>
                                    <div class="ultp-option-multiselect">
                                        <div class="multi-select-action"><ul></ul></div>
                                        <div class="ultp-search-dropdown">
                                            <input type="text" value="" placeholder="Search..." class="ultp-item-search"/>
                                            <div class="ultp-search-results"></div>
                                        </div>
                                    </div>
                                </div>

                                <?php
                                foreach ($taxonomy_list as $val) { ?>
                                <div class="ultp-multi-select">
                                    <span class="ultp-multi-select-action"><?php printf( __('Specific %s', 'ultimate-post-pro'),  $val); ?></span>
                                    <select class="multi-select-data select-<?php echo $val; ?> ultp-multi-select-hide" name="<?php echo $val; ?>_id[]" multiple="multiple" data-type="<?php echo $val; ?>"></select>
                                    <div class="ultp-option-multiselect">
                                        <div class="multi-select-action"><ul></ul></div>
                                        <div class="ultp-search-dropdown">
                                            <input type="text" value="" placeholder="Search..." class="ultp-item-search"/>
                                            <div class="ultp-search-results"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="ultp-builder-close"><span class="dashicons dashicons-no-alt"></span></div>
                    </div>
                </div>
            </form>
        </div>
        <?php    
        }
    }


}
