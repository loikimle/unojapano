<?php
defined( 'ABSPATH' ) || exit;

add_filter('ultp_addons_config', 'ultp_aioseo_config');
function ultp_aioseo_config( $config ) {
	$configuration = array(
		'name' => __( 'All in One SEO Meta', 'ultimate-post-pro' ),
		'desc' => __( 'Show All in One SEO meta description in the excerpt.', 'ultimate-post-pro' ),
		'img' => ULTP_URL.'assets/img/addons/aioseo.svg',
		'is_pro' => false,
		'required' => array(
			'name' => 'All in One SEO',
			'slug' => 'all-in-one-seo-pack/all_in_one_seo_pack.php'
		)
	);
	$config['ultp_aioseo'] = $configuration;
	return $config;
}