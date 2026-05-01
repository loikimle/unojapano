<?php
defined( 'ABSPATH' ) || exit;

get_header();

do_action( 'ultp_before_content' );

$page_id = ultimate_post_pro()->conditions('return');

$width = get_post_meta($page_id, '__container_width', true);
$sidebar = get_post_meta($page_id, '__builder_sidebar', true);
$widget_area = get_post_meta($page_id, '__builder_widget_area', true);
$has_widget = ($sidebar && $widget_area != '') ? true : false;

echo '<div class="ultp-builder-container'.(($has_widget?' ultp-widget-'.$sidebar:'')).'" style="max-width: '.($width??1200).'px;">';
    if ($has_widget && $sidebar == 'left') {
        echo '<div class="ultp-sidebar-left">';
            if (is_active_sidebar($widget_area)) {
                dynamic_sidebar($widget_area);
            }
        echo '</div>';
    }
    if ($page_id) {
        echo '<div class="ultp-builder-wrap">';
            ultimate_post_pro()->content($page_id);
        echo '</div>';
    }
    if ($has_widget && $sidebar == 'right') {
        echo '<div class="ultp-sidebar-right">';
            if (is_active_sidebar($widget_area)) {
                dynamic_sidebar($widget_area);
            }
        echo '</div>';
    }
echo '</div>';

do_action( 'ultp_after_content' );

get_footer();