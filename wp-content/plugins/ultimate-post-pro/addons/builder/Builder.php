<?php
namespace ULTP_PRO;

defined('ABSPATH') || exit;

class Builder {
    public function __construct(){
        $this->builder_post_type_callback();
        add_filter('manage_ultp_builder_posts_columns', array($this, 'builder_table_head'));
        add_action('manage_ultp_builder_posts_custom_column', array($this, 'builder_table_content'), 10, 2);
        add_action('restrict_manage_posts', array($this, 'filter_restrict_builder_callback'));
        add_filter('parse_query', array($this, 'builder_filter_callback'));
        add_filter('post_row_actions', array($this, 'edit_condition_link_callback'), 10, 2);
        // add_filter('views_edit-ultp_builder', array($this, 'builder_tabs_callback'));

        add_action('add_meta_boxes', array($this, 'init_metabox_callback'));
        add_action('save_post', array($this, 'metabox_save_data'));

        add_action('add_meta_boxes', array($this, 'init_video_metabox_callback'));
        add_action('save_post', array($this, 'metabox_save_video_data'));
    }

    function init_video_metabox_callback() {
        add_meta_box('ultp-feature-video', __('Feature Video', 'ultimate-post-pro'), array($this, 'video_source_callback'), 'post', 'side');
    }
    
    function video_source_callback($post) {
        wp_nonce_field('video_meta_box', 'video_meta_box_nonce');
        $video = get_post_meta($post->ID, '__builder_feature_video', true); ?>
        <p>
            <label><?php _e('PostX Feature Video', 'ultimate-post-pro'); ?></label>
            <input type="text" name="feature-video" value="<?php echo $video; ?>"/>
            <button class="ultp-add-media"><?php _e('+ Media', 'ultimate-post-pro'); ?></button>
        </p>
    <?php }
    
    function metabox_save_video_data($post_id) {
        if (!isset($_POST['video_meta_box_nonce'])) { return; }
        if (!wp_verify_nonce(wp_unslash($_POST['video_meta_box_nonce']), 'video_meta_box')) { return; }
        if (!isset( $_POST['feature-video'])) { return; }
    
        update_post_meta($post_id, '__builder_feature_video', sanitize_text_field($_POST['feature-video']));
    }


    function init_metabox_callback() {
        add_meta_box('postx-builder-id', __('PostX Builder Settings', 'ultimate-post-pro'), array($this, 'container_width_callback'), 'ultp_builder', 'side');
    }
    
    function container_width_callback($post) {
        wp_nonce_field('container_meta_box', 'container_meta_box_nonce');
        $width = get_post_meta($post->ID, '__container_width', true);
        $sidebar = get_post_meta($post->ID, '__builder_sidebar', true);
        $widget = get_post_meta($post->ID, '__builder_widget_area', true);

        $widget_area = wp_get_sidebars_widgets();
        if (isset($widget_area['wp_inactive_widgets'])) { unset($widget_area['wp_inactive_widgets']); }
        if (isset($widget_area['array_version'])) { unset($widget_area['array_version']); }
        ?>
        <p>
            <label><?php _e('Container Width', 'ultimate-post-pro'); ?></label>
            <input type="number" style="width:100%" name="container-width" value="<?php echo ($width ? $width : 1140); ?>"/>
        </p>
        <p class="postx-meta-sidebar-position">
            <label><?php _e('Sidebar', 'ultimate-post-pro'); ?></label>
            <select name="builder-sidebar" style="width:88%">
                <option <?php selected( $sidebar, '' ); ?> value=""><?php _e('- None -', 'ultimate-post-pro'); ?></option>
                <option <?php selected( $sidebar, 'left' ); ?> value="left"><?php _e('Left Sidebar', 'ultimate-post-pro'); ?></option>
                <option <?php selected( $sidebar, 'right' ); ?> value="right"><?php _e('Right Sidebar', 'ultimate-post-pro'); ?></option>
            </select>
        </p>
        <p class="postx-meta-sidebar-widget">
            <label><?php _e('Select Sidebar(Widget Area)', 'ultimate-post-pro'); ?></label>
            <select name="builder-widget-area" style="width:88%">
                <option <?php selected( $sidebar, '' ); ?> value=""><?php _e('- None -', 'ultimate-post-pro'); ?></option>
                <?php foreach ($widget_area as $key => $val) { ?>
                    <option <?php selected( $widget, $key ); ?> value="<?php echo $key; ?>"><?php echo ucwords(str_replace('-', ' ', $key)); ?></option>
                <?php } ?>
            </select>
        </p>
    <?php }
    
    function metabox_save_data($post_id) {
        if (!isset($_POST['container_meta_box_nonce'])){ return; }
        if (!wp_verify_nonce( wp_unslash($_POST['container_meta_box_nonce']), 'container_meta_box')) { return; }

        if (isset($_POST['container-width'])) {
            update_post_meta($post_id, '__container_width', sanitize_text_field($_POST['container-width']));
        }

        if (isset($_POST['builder-sidebar'])) {
            update_post_meta($post_id, '__builder_sidebar', sanitize_text_field($_POST['builder-sidebar']));
        }

        if (isset($_POST['builder-widget-area'])) {
            update_post_meta($post_id, '__builder_widget_area', sanitize_text_field($_POST['builder-widget-area']));
        }
    }

    function builder_tabs_callback() {
        $menu_lists = array(
            'all' => __('All Template', 'ultimate-post-pro'),
            'archive' => __('Archive', 'ultimate-post-pro')
        );

        $current_tab = (isset($_GET['tab']) ? $_GET['tab'] : 'all');

        $arg = array( 'post_type' => 'ultp_builder' );

        echo '<div id="ultp-tabs-wrapper" class="nav-tab-wrapper">';
            foreach ($menu_lists as $key => $val) {
                $arg['tab'] = $key;
                $url = add_query_arg( $arg, admin_url( 'edit.php' ) );
                echo '<a class="nav-tab'.($current_tab == $key ? ' nav-tab-active' : '').'" href="'.$url.'">'.$val.'</a>';
            }
        echo '</div>';
    }

    function edit_condition_link_callback($actions, $post) {
        if ($post->post_type == 'ultp_builder') {
            if (isset($actions['edit'])) {
                $actions['edit'] = str_replace( strip_tags($actions['edit']), __('Edit with Gutenberg', 'ultimate-post-pro'), $actions['edit'] );
            }
            $new_element = array( 'condition' => '<a href="'.get_edit_post_link($post->ID).'" class="ultp-builder-conditions">'.__('Edit Condition' , 'ultimate-post-pro').'</a>' );
            array_splice($actions, 1, 0,$new_element);
        }
        return $actions;
    }

    // Restrict Content Callback
    public function filter_restrict_builder_callback() {
        $type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
        if ('ultp_builder' == $type){
            $values = array(
                'All' => 'all', 
                'Archive' => 'archive',
            );
            ?>
            <select name="builder_type">
            <?php
                $current_v = isset($_GET['builder_type'])? $_GET['builder_type'] : 'all';
                foreach ($values as $label => $value) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        $value,
                        $value == $current_v? ' selected="selected"':'',
                        $label
                    );
                }
            ?>
            </select>
            <?php
        }
    }
    

    // Builder Filter Add
    public function builder_filter_callback( $query ) {
        global $pagenow;
        $type = 'post';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }
        if ( 'ultp_builder' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['builder_type']) && $_GET['builder_type'] != '' && $_GET['builder_type'] != 'all') {
            $query->query_vars['meta_key'] = '_ultp_builder_type';
            $query->query_vars['meta_value'] = $_GET['builder_type'];
        }
    }

    
    // Builder Heading Add
    public function builder_table_head( $defaults ) {
        $type_array = array('type' => __('Builder Type', 'ultimate-post-pro'));
        array_splice( $defaults, 2, 0, $type_array ); 
        return $defaults;
    }


    // Column Content
    public function builder_table_content( $column_name, $post_id ) {
        echo ucfirst(get_post_meta( $post_id, '_ultp_builder_type', true ));
    }

    // Builder Post Type Register
    public function builder_post_type_callback() {
        $labels = array(
            'name'                => _x( 'Builder', 'Builder', 'ultimate-post-pro' ),
            'singular_name'       => _x( 'Builder', 'Builder', 'ultimate-post-pro' ),
            'menu_name'           => __( 'Builder', 'ultimate-post-pro' ),
            'parent_item_colon'   => __( 'Parent Builder', 'ultimate-post-pro' ),
            'all_items'           => __( 'Builder', 'ultimate-post-pro' ),
            'view_item'           => __( 'View Builder', 'ultimate-post-pro' ),
            'add_new_item'        => __( 'Add New', 'ultimate-post-pro' ),
            'add_new'             => __( 'Add New', 'ultimate-post-pro' ),
            'edit_item'           => __( 'Edit Builder', 'ultimate-post-pro' ),
            'update_item'         => __( 'Update Builder', 'ultimate-post-pro' ),
            'search_items'        => __( 'Search Builder', 'ultimate-post-pro' ),
            'not_found'           => __( 'No Builder Found', 'ultimate-post-pro' ),
            'not_found_in_trash'  => __( 'Not Builder found in Trash', 'ultimate-post-pro' ),
        );
        $args = array(
            'labels'              => $labels,
            'show_in_rest'        => true,
            'supports'            => array( 'title', 'editor' ),
            'hierarchical'        => false,
            'public'              => false,
            'rewrite'             => false,
            'show_ui'             => true,
            'show_in_menu'        => 'ultp-settings',
            'show_in_nav_menus'   => false,
            'exclude_from_search' => true,
            'capability_type'     => 'page',
        );
       register_post_type( 'ultp_builder', $args );
    }
}