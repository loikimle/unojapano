<?php

namespace WPEverest\URM\Pro\Analytics\Controllers\V1;

defined( 'ABSPATH' ) || exit;

use WPEverest\URM\Pro\Analytics\Services\ChartDataFormatter;
use WPEverest\URM\Pro\Analytics\Services\ChartDataBuilder;
use WPEverest\URM\Pro\Analytics\Services\AnalyticsDataService;
use WPEverest\URM\Analytics\Controllers\V1\AnalyticsController as BaseAnalyticsController;
use WPEverest\URM\Analytics\Services\MembershipService;

class AnalyticsController extends BaseAnalyticsController {

	/**
	 * @var AnalyticsDataService
	 */
	protected $data_service;

	/**
	 * @var ChartDataFormatter
	 */
	protected $date_formatter;

	/**
	 * @var ChartDataBuilder
	 */
	protected $chart_builder;

	/**
	 * @var MembershipService
	 */
	protected $membership_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->data_service       = new AnalyticsDataService();
		$this->date_formatter     = new ChartDataFormatter();
		$this->chart_builder      = new ChartDataBuilder();
		$this->membership_service = new MembershipService();
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_overview' ),
					'permission_callback' => array( $this, 'check_permissions' ),
					'args'                => $this->get_analytics_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/preferences',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_preferences' ),
					'permission_callback' => array( $this, 'check_permissions' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_preferences' ),
					'permission_callback' => array( $this, 'check_permissions' ),
				),
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return WP_Error|\WP_REST_Response
	 */
	public function get_overview( $request ) {
		$date_from  = $request->get_param( 'date_from' );
		$date_to    = $request->get_param( 'date_to' );
		$unit       = $request->get_param( 'unit' ) ?? 'day';
		$scope      = $request->get_param( 'scope' ) ?? 'all';
		$membership = $request->get_param( 'membership' ) ?? null;

		if ( empty( $date_from ) || empty( $date_to ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'date_from and date_to are required parameters.', 'user-registration' ),
				[ 'status' => 400 ]
			);
		}

		$start_date = strtotime( $date_from . ' 00:00:00' );
		$end_date   = strtotime( $date_to . ' 23:59:59' );

		if ( false === $start_date || false === $end_date ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'Invalid date format. Use YYYY-MM-DD format.', 'user-registration' ),
				[ 'status' => 400 ]
			);
		}

		if ( ! in_array( $scope, [ 'membership', 'others', 'all' ], true ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'Invalid scope parameter. Allowed values are all, others, membership.', 'user-registration' ),
				[ 'status' => 400 ]
			);
		}

		if ( 'membership' === $scope && ( null === $membership || ! is_numeric( $membership ) ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'Valid membership ID is required when scope is membership.', 'user-registration' ),
				[ 'status' => 400 ]
			);
		}

		$form_analytics_helper = dirname( dirname( dirname( __DIR__ ) ) ) . '/form-analytics/class-ur-pro-form-analytics-helpers.php';
		if ( file_exists( $form_analytics_helper ) ) {
			require_once $form_analytics_helper;
		}

		$overview_data            = $this->data_service->get_members_overview( $start_date, $end_date );
		$date_range_data          = $this->data_service->get_date_range_members_data( $start_date, $end_date, $unit );
		$comparison_data          = $this->data_service->get_comparison_data( $start_date, $end_date, $unit );
		$top_referrer_data        = $this->data_service->get_top_referrer_data( $date_from, $date_to );
		$registration_source_data = $this->data_service->get_registration_source_data( $start_date, $end_date );
		$revenue_data             = $this->data_service->get_revenue_date_range_data( $start_date, $end_date, $unit, $scope, $membership );
		$subscriptions_data       = $this->data_service->get_subscriptions_date_range_data( $start_date, $end_date, $unit, $scope, $membership );
		$memberships_distribution = $this->data_service->get_memberships_distribution( $start_date, $end_date );
		$form_analytics_data      = $this->data_service->get_form_analytics_data( $date_from, $date_to, $unit );
		$charts                   = $this->chart_builder->build_charts(
			$start_date,
			$end_date,
			$unit,
			$date_range_data,
			$comparison_data,
			$overview_data,
			$form_analytics_data,
			$top_referrer_data,
			$registration_source_data,
			$revenue_data,
			$subscriptions_data,
			$memberships_distribution
		);

		$total_members          = $overview_data['total_members'];
		$new_members            = $date_range_data['new_members'];
		$new_members_percentage = 0;
		if ( $total_members > 0 ) {
			$new_members_percentage = round( ( $new_members / $total_members ) * 100, 2 );
		}
		$comparison_percentage = $this->data_service->calculate_percentage_change(
			$new_members,
			$comparison_data['new_members']
		);
		$response              = array(
			'props'   => array(
				'overview' => array(
					'dateFrom'                  => $date_from,
					'dateTo'                    => $date_to,
					'unit'                      => $unit,
					'validUnits'                => array( 'hour', 'day', 'week', 'month', 'year' ),
					'charts'                    => $charts,
					'totalMembers'              => $total_members,
					'totalFormMembers'          => $overview_data['total_form_members'],
					'totalSocialMembers'        => $overview_data['total_social_members'],
					'newMembers'                => $new_members,
					'newMembersPercentage'      => $new_members_percentage,
					'comparisonPercentage'      => $comparison_percentage,
					'approvedMembers'           => $date_range_data['approved_members'] ?? 0,
					'approvedMembersPercentage' => $date_range_data['approved_members_percentage'] ?? 0,
					'pendingMembers'            => $date_range_data['pending_members'] ?? 0,
					'pendingMembersPercentage'  => $date_range_data['pending_members_percentage'] ?? 0,
					'deniedMembers'             => $date_range_data['denied_members'] ?? 0,
					'deniedMembersPercentage'   => $date_range_data['denied_members_percentage'] ?? 0,
					'dateDifference'            => $date_range_data['date_difference'] ?? '',
					'memberships'               => $this->membership_service->get_memberships_list(),
				),
			),
			'url'     => $this->build_url( $date_from, $date_to, $unit ),
			'version' => $this->get_version(),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * @return array
	 */
	protected function get_analytics_args() {
		return array(
			'date_from'  => [
				'description' => __( 'Start date in YYYY-MM-DD format.', 'user-registration' ),
				'type'        => 'string',
				'required'    => false,
				'default'     => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
			],
			'date_to'    => [
				'description' => __( 'End date in YYYY-MM-DD format.', 'user-registration' ),
				'type'        => 'string',
				'required'    => false,
				'default'     => gmdate( 'Y-m-d' ),
			],
			'unit'       => [
				'description' => __( 'Time unit for data aggregation (hour, day, week, month, year).', 'user-registration' ),
				'type'        => 'string',
				'required'    => false,
				'default'     => 'day',
				'enum'        => [ 'hour', 'day', 'week', 'month', 'year' ],
			],
			'scope'      => [
				'description' => __( 'Scope of the analytics data (all, others, membership).', 'user-registration' ),
				'type'        => 'string',
				'required'    => false,
				'default'     => 'all',
				'enum'        => [ 'all', 'others', 'membership' ],
			],
			'membership' => [
				'description' => __( 'Membership ID for filtering data when scope is membership.', 'user-registration' ),
				'type'        => 'string',
				'required'    => false,
				'default'     => null,
			],
		);
	}

	/**
	 * @param WP_REST_Request
	 * @return bool|\WP_Error
	 */
	public function check_permissions( $request ) {
		if ( ! current_user_can( 'manage_user_registration' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			return new \WP_Error(
				'rest_forbidden',
				\__( 'Sorry, you are not allowed to access analytics data.', 'user-registration' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * @param string $date_from
	 * @param string $date_to
	 * @param string $unit
	 * @param string $scope
	 * @param null|int $membership
	 * @return string
	 */
	protected function build_url( $date_from, $date_to, $unit, $scope = 'all', $membership = null ) {
		$params = array(
			'date_from'  => $date_from,
			'date_to'    => $date_to,
			'unit'       => $unit,
			'scope'      => $scope,
			'membership' => $membership,
		);

		return '/analytics?' . http_build_query( $params );
	}

	/**
	 * @return string
	 */
	protected function get_version() {
		return md5( UR_VERSION . time() );
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_REST_Response
	 */
	public function get_preferences( $request ) {
		$preferences = get_option( '_ur_pro_analytics_preferences' );

		if ( empty( $preferences ) || ! is_array( $preferences ) ) {
			$preferences = $this->get_default_preferences();
		}
		if ( empty( $preferences['updated_at'] ) ) {
			$preferences['updated_at'] = current_time( 'mysql' );
			update_option( '_ur_pro_analytics_preferences', $preferences );
		}
		return rest_ensure_response( $preferences );
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|\WP_REST_Response
	 */
	public function save_preferences( $request ) {

		$summary       = $request->get_param( 'summary' );
		$visualization = $request->get_param( 'visualization' );

		$preferences = get_option( '_ur_pro_analytics_preferences' );
		if ( empty( $preferences ) || ! is_array( $preferences ) ) {
			$preferences = $this->get_default_preferences();
		}

		if ( ! empty( $summary ) ) {
			$preferences['summary'] = $summary;
		}

		if ( ! empty( $visualization ) ) {
			$preferences['visualization'] = $visualization;
		}

		$preferences['updated_at'] = current_time( 'mysql' );

		update_option( '_ur_pro_analytics_preferences', $preferences );
		return rest_ensure_response( $preferences );
	}

	/**
	 * @return array
	 */
	protected function get_default_preferences() {
		$forms         = ur_get_all_user_registration_form();
		$visualization = [
			'revenue-overview',
			'orders-payments',
			'members-overview',
			'registration-source',
		];
		if ( ! empty( $forms ) ) {
			$visualization[3] = 'signup-analytics-' . array_key_first( $forms );
		} else {
			$visualization[3] = 'recurring-revenue';
		}
		return [
			'summary'       => [
				'new-members',
				'approved-members',
				'pending-members',
				'denied-members',
			],
			'visualization' => $visualization,
		];
	}
}
