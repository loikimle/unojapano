<?php
defined( 'ABSPATH' ) || exit;

add_filter('ultp_addons_config', 'ultp_category_config');
function ultp_category_config( $config ) {
	$configuration = array(
		'name' => __( 'Category', 'ultimate-post-pro' ),
		'desc' => __( 'Choose your desired color and Image for categories or any taxonomy.', 'ultimate-post-pro' ),
		'is_pro' => true,
		'img' => ULTP_PRO_URL.'assets/img/category-style.svg',
	);
	$config['ultp_category'] = $configuration;
	return $config;
}

add_action('init', 'ultp_category_init');
function ultp_category_init(){
	$settings = isset($GLOBALS['ultp_settings']) ? $GLOBALS['ultp_settings'] : [];
	if ( isset($settings['ultp_category']) ) {
		if ($settings['ultp_category'] == 'true') {
			require_once ULTP_PRO_PATH.'/addons/category/Category.php';
        	new \ULTP_PRO\Category();
		}
	}
}