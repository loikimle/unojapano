<?php
defined( 'ABSPATH' ) || exit;

add_filter('ultp_addons_config', 'ultp_squirrly_meta_config');
function ultp_squirrly_meta_config( $config ) {
	$configuration = array(
		'name' => __( 'Squirrly Meta', 'ultimate-post-pro' ),
		'desc' => __( 'Show Squirrly meta description in the excerpt.', 'ultimate-post-pro' ),
		'img' => ULTP_URL.'assets/img/addons/squirrly.svg',
		'is_pro' => false,
		'required' => array(
			'name' => 'Squirrly',
			'slug' => 'squirrly-seo/squirrly.php'
		),
	);
	$config['ultp_squirrly'] = $configuration;
	return $config;
}