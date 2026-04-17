<?php
defined( 'ABSPATH' ) || exit;

add_filter('ultp_addons_config', 'ultp_yoast_meta_config');
function ultp_yoast_meta_config( $config ) {
	$configuration = array(
		'name' => __( 'Yoast Meta', 'ultimate-post-pro' ),
		'desc' => __( 'Show Yoast meta description in the excerpt.', 'ultimate-post-pro' ),
		'img' => ULTP_URL.'assets/img/addons/yoast.svg',
		'is_pro' => false,
		'required' => array(
			'name' => 'Yoast',
			'slug' => 'wordpress-seo/wp-seo.php'
		)
	);
	$config['ultp_yoast'] = $configuration;
	return $config;
}