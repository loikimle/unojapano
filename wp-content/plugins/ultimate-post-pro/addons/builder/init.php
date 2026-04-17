<?php
defined( 'ABSPATH' ) || exit;

add_filter('ultp_addons_config', 'ultp_builder_config');
function ultp_builder_config( $config ) {
	$configuration = array(
		'name' => __( 'Builder', 'ultimate-post-pro' ),
		'desc' => __( 'Design template for Archive, Category, Custom Taxonomy, Date, and Search Page.', 'ultimate-post-pro' ),
		'img' => ULTP_PRO_URL.'assets/img/builder-icon.svg',
		'is_pro' => false
	);
	$config['ultp_builder'] = $configuration;
	return $config;
}

add_action('init', 'ultp_builder_init');
function ultp_builder_init(){
	$settings = isset($GLOBALS['ultp_settings']) ? $GLOBALS['ultp_settings'] : [];
	if ( isset($settings['ultp_builder']) ) {
		if ($settings['ultp_builder'] == 'true') {
			require_once ULTP_PRO_PATH.'/addons/builder/Builder.php';
			require_once ULTP_PRO_PATH.'/addons/builder/Condition.php';
			require_once ULTP_PRO_PATH.'/addons/builder/RequestAPI.php';
			new \ULTP_PRO\Builder();
			new \ULTP_PRO\Condition();
			new \ULTP_PRO\RequestAPI();
		}
	}
}