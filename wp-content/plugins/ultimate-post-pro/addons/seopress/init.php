<?php
defined( 'ABSPATH' ) || exit;

add_filter('ultp_addons_config', 'ultp_seopress_meta_config');
function ultp_seopress_meta_config( $config ) {
	$configuration = array(
		'name' => __( 'SEOPress Meta', 'ultimate-post-pro' ),
		'desc' => __( 'Show SEOPress meta description in the excerpt.', 'ultimate-post-pro' ),
		'img' => ULTP_URL.'assets/img/addons/seopress.svg',
		'is_pro' => false,
		'required' => array(
			'name' => 'SEOPress',
			'slug' => 'wp-seopress/seopress.php'
		)
	);
	$config['ultp_seopress'] = $configuration;
	return $config;
}