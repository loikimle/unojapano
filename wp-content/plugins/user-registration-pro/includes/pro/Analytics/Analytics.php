<?php

namespace WPEverest\URM\Pro\Analytics;

use WPEverest\URM\Pro\Analytics\Controllers\V1\AnalyticsController;
use WPEverest\URM\Pro\Analytics\Services\AnalyticsDataService;

class Analytics {

	/**
	 * @var Analytics $instance
	 */
	private static $instance;

	/**
	 * @return Analytics
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
		$this->init_hooks();
	}

	protected function __clone() {}

	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize a singleton.' );
	}

	private function init_hooks() {
		add_filter( 'user_registration_analytics_localized_data', [ $this, 'add_analytics_localized_data' ] );
		add_filter( 'user_registration_analytics_controllers', [ $this, 'modify_analytics_controllers' ] );
	}

	public function add_analytics_localized_data( $data ) {
		$data['data_sets'] = ( new AnalyticsDataService() )->get_data_sets();
		return $data;
	}

	public function modify_analytics_controllers( $controllers ) {
		$controllers   = array_filter(
			$controllers,
			function ( $controller ) {
				return $controller !== 'WPEverest\URM\Analytics\Controllers\V1\AnalyticsController';
			}
		);
		$controllers[] = AnalyticsController::class;
		return $controllers;
	}
}
