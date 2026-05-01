<?php
defined( 'ABSPATH' ) || exit;

add_filter('ultp_addons_config', 'ultp_rankmath_config');
function ultp_rankmath_config( $config ) {
	$configuration = array(
		'name' => __( 'RankMath Meta', 'ultimate-post-pro' ),
		'desc' => __( 'Show RankMath meta description in the excerpt.', 'ultimate-post-pro' ),
		'img' => ULTP_URL.'assets/img/addons/rankmath.svg',
		'is_pro' => false,
		'required' => array(
			'name' => 'RankMath',
			'slug' => 'seo-by-rank-math/rank-math.php'
		)
	);
	$config['ultp_rankmath'] = $configuration;
	return $config;
}