<?php
/**
 * UserRegistrationSocialConnect URSC_AJAX
 *
 * AJAX Event Handler
 *
 * @class    UR_AJAX
 * @version  1.2.1
 * @package  UserRegistrationSocialConnect/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URSC_AJAX Class
 */
class URSC_AJAX {

	/**
	 * URSC_AJAX construct function.
	 */
	public function __construct() {
		$this->add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public function add_ajax_events() {

		$ajax_events = array(

			'dashboard_chart_widget' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_user_registration_social_connect_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_user_registration_social_connect_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}
	}

	/**
	 * Dashboard Chart reporting Widget data.
	 *
	 * @since 1.5.8
	 */
	public function dashboard_chart_widget() {

		check_ajax_referer( 'dashboard-chart-widget', 'security' );

		$total_social_count = 0;

		$available_networks = user_registration_social_networks();
		$available_networks = array_keys( $available_networks );

		$user_report = ursc_get_user_report();

		$others = array(
			'color' => ursc_get_social_chart_color( 'reg_forms' ),
			'count' => count( $user_report['reg_forms'] ),
		);

		foreach ( $available_networks as $network ) {
			$social_user_report[ ucfirst( $network ) ] = array(
				'color' => ursc_get_social_chart_color( $network ),
				'count' => 0,
			);
		}

		foreach ( $user_report['social'] as $user ) {
			$user_network = str_replace( 'user_registration_social_connect_', '', $user[1] );
			$user_network = str_replace( '_username', '', $user_network );

			if ( in_array( $user_network, $available_networks ) ) {
				$social_user_report[ ucfirst( $user_network ) ]['count']++;
				$total_social_count++;
			}
		}

		wp_send_json(
			array(
				'social_user_report' => $social_user_report,
				'others'             => $others,
				'social_total'       => $total_social_count,
			)
		); // WPCS: XSS OK.
	}
}

new URSC_AJAX();
