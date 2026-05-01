<?php
defined( 'ABSPATH' ) || exit;

add_filter('ultp_addons_config', 'ultp_progressbar_config');

/**
 * ultp porgressbar config render
 *
 * @param $config
 *
 * @return mixed
 */
function ultp_progressbar_config( $config ) {
    $configuration = array(
        'name'   => __( 'Progress Bar', 'ultimate-post-pro' ),
        'desc'   => __( 'Let the users see a graphical indicator to know the reading progress of a blog post.', 'ultimate-post-pro' ),
        'img'    => ULTP_URL.'assets/img/addons/progressbar.svg',
        'is_pro' => true
    );
    $config['ultp_progressbar'] = $configuration;
    return $config;
}

add_action('init', 'ultp_progressbar_init');
function ultp_progressbar_init(){
    $settings = isset($GLOBALS['ultp_settings']) ? $GLOBALS['ultp_settings'] : [];
    if ( isset($settings['ultp_progressbar']) ) {
        if ($settings['ultp_progressbar'] == 'true') {
            require_once ULTP_PRO_PATH.'/addons/progressbar/ProgressBar.php';
            new \ULTP_PRO\ProgressBar();
        }
    }
}


