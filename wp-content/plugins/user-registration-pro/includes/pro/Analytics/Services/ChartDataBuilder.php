<?php

namespace WPEverest\URM\Pro\Analytics\Services;

use WPEverest\URM\Analytics\Traits\DateUtils;

defined( 'ABSPATH' ) || exit;

class ChartDataBuilder {

	use DateUtils;

	/**
	 * @var ChartDataFormatter
	 */
	protected $chart_data_formatter;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->chart_data_formatter = new ChartDataFormatter();
	}

	/**
	 * @param int   $start_date
	 * @param int   $end_date
	 * @param string $unit
	 * @param array $date_range_data
	 * @param array $comparison_data
	 * @param array $overview_data
	 * @param array $signup_analytics_data
	 * @param array $top_referrer_data
	 * @param array $registration_source_data
	 * @param array $revenue_data
	 * @param array $subscriptions_data
	 * @param array $memberships_distribution
	 * @return array
	 */
	public function build_charts(
		$start_date,
		$end_date,
		$unit,
		$date_range_data,
		$comparison_data,
		$overview_data,
		$signup_analytics_data = [],
		$top_referrer_data = [],
		$registration_source_data = [],
		$revenue_data = [],
		$subscriptions_data = [],
		$memberships_distribution = []
	) {
		$charts = [];

		$previous_period = $this->chart_data_formatter->get_previous_period( $start_date, $end_date );
		$intervals       = $this->chart_data_formatter->generate_intervals( $start_date, $end_date, $unit );

		$member_charts = [
			[
				'metric'   => __( 'New Members', 'user-registration' ),
				'slug'     => 'new-members',
				'data_key' => 'new_members_in_a_day',
			],
			[
				'metric'   => __( 'Approved Members', 'user-registration' ),
				'slug'     => 'approved-members',
				'data_key' => 'approved_members_in_a_day',
			],
			[
				'metric'   => __( 'Pending Members', 'user-registration' ),
				'slug'     => 'pending-members',
				'data_key' => 'pending_members_in_a_day',
			],
			[
				'metric'   => __( 'Denied Members', 'user-registration' ),
				'slug'     => 'denied-members',
				'data_key' => 'denied_members_in_a_day',
			],
		];

		foreach ( $member_charts as $chart_config ) {
			$charts[] = $this->build_member_chart(
				$chart_config['metric'],
				$chart_config['slug'],
				$chart_config['data_key'],
				$date_range_data,
				$comparison_data,
				$start_date,
				$end_date,
				$unit,
				$previous_period,
				$intervals
			);
		}

		if ( ! empty( $overview_data ) ) {
			$charts = array_merge( $charts, $this->build_members_overview_charts( $start_date, $end_date, $unit, $overview_data, $previous_period, $intervals ) );
		}

		$signup_analytics_charts = $this->build_signup_analytics_charts( $start_date, $end_date, $unit, $signup_analytics_data ?? [], $previous_period, $intervals );
		$charts                  = array_merge( $charts, $signup_analytics_charts );

		$charts[] = $this->build_top_referrer_chart( $start_date, $end_date, $top_referrer_data, $previous_period );

		$registration_source_data_for_chart = $registration_source_data ?? [];
		$charts[]                           = $this->build_registration_source_chart( $start_date, $end_date, $registration_source_data_for_chart, $previous_period );

		$revenue_data_for_charts = $revenue_data ?? [];
		$revenue_charts          = $this->build_revenue_charts( $start_date, $end_date, $unit, $revenue_data_for_charts, $previous_period, $intervals );
		$charts                  = array_merge( $charts, $revenue_charts );

		$subscriptions_data_for_charts = $subscriptions_data ?? [];
		$subscriptions_charts          = $this->build_subscriptions_charts( $start_date, $end_date, $unit, $subscriptions_data_for_charts, $previous_period, $intervals );
		$charts                        = array_merge( $charts, $subscriptions_charts );

		$memberships_distribution_for_chart = $memberships_distribution ?? [];
		$charts[]                           = $this->build_memberships_distribution_chart( $start_date, $end_date, $memberships_distribution_for_chart, $previous_period );

		$data_service = new AnalyticsDataService();
		$mrr_data     = $data_service->get_mrr_date_range_data( $start_date, $end_date, $unit );
		$arr_data     = $data_service->get_arr_date_range_data( $start_date, $end_date, $unit );
		$mrr_charts   = $this->build_recurring_revenue_charts( $start_date, $end_date, $unit, $mrr_data, $arr_data, $previous_period, $intervals );
		$charts       = array_merge( $charts, $mrr_charts );

		return $charts;
	}

	/**
	 * @param array $args
	 * @return array
	 */
	protected function build_chart( $args ) {
		return [
			'metric'                     => $args['metric'],
			'description'                => $args['description'] ?? '',
			'slug'                       => $args['slug'],
			'date_from'                  => $args['date_from'],
			'date_to'                    => $args['date_to'],
			'previous_from'              => $args['previous_from'],
			'previous_to'                => $args['previous_to'],
			'unit'                       => $args['unit'],
			'intervals'                  => $args['intervals'],
			'total'                      => $args['total'],
			'previous_total'             => $args['previous_total'],
			'delta'                      => $args['delta'],
			'avg'                        => $args['avg'],
			'previous_avg'               => $args['previous_avg'],
			'avg_delta'                  => $args['avg_delta'],
			'data'                       => $args['data'],
			'previous_data'              => $args['previous_data'],
			'additional_data'            => $args['additional_data'] ?? [],
			'is_money'                   => $args['is_money'],
			'is_avg'                     => $args['is_avg'],
			'is_cumulative'              => $args['is_cumulative'],
			'is_percentage'              => $args['is_percentage'],
			'currency'                   => $args['currency'],
			'is_loaded'                  => $args['is_loaded'],
			'is_most_recent'             => $args['is_most_recent'],
			'most_recent_total'          => $args['most_recent_total'],
			'previous_most_recent_total' => $args['previous_most_recent_total'],
			'rate'                       => $args['rate'] ?? null,
		];
	}

	/**
	 * @param array  $daily_data
	 * @param string $key
	 * @param string $unit
	 * @return array
	 */
	protected function build_data_map( $daily_data, $key, $unit = 'day' ) {
		$data_map = [];

		if ( ! is_array( $daily_data ) ) {
			return $data_map;
		}

		foreach ( $daily_data as $date_label => $data ) {
			if ( ! isset( $data[ $key ] ) ) {
				continue;
			}

			$is_valid_format = false;
			if ( 'month' === $unit && preg_match( '/^\d{4}-\d{2}$/', $date_label ) ) {
				$date_key        = $date_label;
				$is_valid_format = true;
			} elseif ( 'year' === $unit && preg_match( '/^\d{4}$/', $date_label ) ) {
				$date_key        = $date_label;
				$is_valid_format = true;
			} elseif ( 'hour' === $unit && preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}$/', $date_label ) ) {
				$date_key        = $date_label;
				$is_valid_format = true;
			} elseif ( ( 'day' === $unit || 'week' === $unit ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_label ) ) {
				$date_key        = $date_label;
				$is_valid_format = true;
			}

			if ( ! $is_valid_format ) {
				$timestamp = strtotime( $date_label );
				if ( false === $timestamp ) {
					$timestamp = strtotime( str_replace( ',', '', $date_label ) );
				}
				if ( false === $timestamp ) {
					$hour_timestamp = strtotime( $date_label . ' today' );
					if ( false !== $hour_timestamp ) {
						$timestamp = $hour_timestamp;
					}
				}

				if ( false !== $timestamp ) {
					$date_key = $this->get_date_key_for_timestamp( $timestamp, $unit );
				} else {
					continue;
				}
			}

			if ( isset( $data_map[ $date_key ] ) ) {
				$data_map[ $date_key ] += (float) $data[ $key ];
			} else {
				$data_map[ $date_key ] = (float) $data[ $key ];
			}
		}

		return $data_map;
	}

	/**
	 * @param float $current
	 * @param float $previous
	 * @return float
	 */
	protected function calculate_delta( $current, $previous ) {
		$current  = (float) $current;
		$previous = (float) $previous;

		if ( empty( $previous ) || 0.0 === $previous || abs( $previous ) < 0.0001 ) {
			return $current > 0 ? 100.0 : 0.0;
		}

		return round( ( ( $current - $previous ) / $previous ) * 100, 2 );
	}

	/**
	 * @param array $daily_data
	 * @param string $data_key
	 * @return array
	 */
	protected function extract_data_map( $daily_data, $data_key ) {
		$data_map = [];
		foreach ( $daily_data as $date_key => $data ) {
			$data_map[ $date_key ] = isset( $data[ $data_key ] ) ? (float) $data[ $data_key ] : 0;
		}
		return $data_map;
	}

	/**
	 * @param string $metric
	 * @param string $slug
	 * @param string $data_key
	 * @param array $date_range_data
	 * @param array $comparison_data
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param array $previous_period
	 * @param array $intervals
	 * @return array
	 */
	protected function build_member_chart( $metric, $slug, $data_key, $date_range_data, $comparison_data, $start_date, $end_date, $unit, $previous_period, $intervals ) {
		$data_map             = $this->extract_data_map( $date_range_data['daily_data'] ?? [], $data_key );
		$previous_data_map    = $this->extract_data_map( $comparison_data['daily_data'] ?? [], $data_key );
		$current_data_points  = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, $data_map );
		$previous_data_points = $this->chart_data_formatter->generate_data_points( $previous_period['previous_from'], $previous_period['previous_to'], $unit, $previous_data_map );
		$total                = array_sum( array_column( $current_data_points, 'value' ) );
		$previous_total       = array_sum( array_column( $previous_data_points, 'value' ) );
		$data_count           = count( $current_data_points );
		$avg                  = $data_count > 0 ? $total / $data_count : 0;
		$previous_avg         = count( $previous_data_points ) > 0 ? $previous_total / count( $previous_data_points ) : 0;

		return $this->build_chart(
			[
				'metric'                     => $metric,
				'description'                => '',
				'slug'                       => $slug,
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => $unit,
				'intervals'                  => $intervals,
				'total'                      => $total,
				'previous_total'             => $previous_total,
				'delta'                      => $this->calculate_delta( $total, $previous_total ),
				'avg'                        => $avg,
				'previous_avg'               => $previous_avg,
				'avg_delta'                  => $this->calculate_delta( $avg, $previous_avg ),
				'data'                       => $current_data_points,
				'previous_data'              => $previous_data_points,
				'is_money'                   => false,
				'is_avg'                     => false,
				'is_cumulative'              => false,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);
	}

	/**
	 * Build a revenue chart from daily data
	 *
	 * @param string $metric
	 * @param string $slug
	 * @param string $data_key
	 * @param array $revenue_data
	 * @param array $previous_revenue
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param array $previous_period
	 * @param array $intervals
	 * @param bool $is_avg
	 * @return array
	 */
	protected function build_revenue_chart( $metric, $slug, $data_key, $revenue_data, $previous_revenue, $start_date, $end_date, $unit, $previous_period, $intervals, $is_avg = false ) {
		$revenue_map         = $this->extract_data_map( $revenue_data['daily_data'] ?? [], $data_key );
		$revenue_data_points = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, $revenue_map );

		$previous_revenue_map         = $this->extract_data_map( $previous_revenue['daily_data'] ?? [], $data_key );
		$previous_revenue_data_points = $this->chart_data_formatter->generate_data_points( $previous_period['previous_from'], $previous_period['previous_to'], $unit, $previous_revenue_map );

		$total          = array_sum( array_column( $revenue_data_points, 'value' ) );
		$previous_total = array_sum( array_column( $previous_revenue_data_points, 'value' ) );
		$data_count     = count( $revenue_data_points );
		$avg            = $data_count > 0 ? $total / $data_count : 0;
		$previous_avg   = count( $previous_revenue_data_points ) > 0 ? $previous_total / count( $previous_revenue_data_points ) : 0;

		return $this->build_chart(
			[
				'metric'                     => $metric,
				'description'                => '',
				'slug'                       => $slug,
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => $unit,
				'intervals'                  => $intervals,
				'total'                      => $total,
				'previous_total'             => $previous_total,
				'delta'                      => $this->calculate_delta( $total, $previous_total ),
				'avg'                        => $avg,
				'previous_avg'               => $previous_avg,
				'avg_delta'                  => $this->calculate_delta( $avg, $previous_avg ),
				'data'                       => $revenue_data_points,
				'previous_data'              => $previous_revenue_data_points,
				'is_money'                   => true,
				'is_avg'                     => $is_avg,
				'is_cumulative'              => false,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param mixed $default_value
	 * @return array
	 */
	protected function initialize_empty_data_structure( $start_date, $end_date, $unit, $default_value ) {
		$daily_data  = [];
		$date_format = 'Y-m-d';

		if ( 'hour' === $unit ) {
			$date_format = 'Y-m-d H';
			$incrementor = HOUR_IN_SECONDS;
			for ( $i = $start_date; $i <= $end_date; $i += $incrementor ) {
				$date_key                = wp_date( $date_format, $i );
				$daily_data[ $date_key ] = $default_value;
			}
		} elseif ( 'month' === $unit ) {
			$current_date = new \DateTime();
			$current_date->setTimestamp( $start_date );
			$end_date_obj = new \DateTime();
			$end_date_obj->setTimestamp( $end_date );
			$end_date_obj->setTime( 23, 59, 59 );
			$current_date->modify( 'first day of this month' );
			$current_date->setTime( 0, 0, 0 );

			while ( $current_date <= $end_date_obj ) {
				$date_key                = $current_date->format( 'Y-m' );
				$daily_data[ $date_key ] = $default_value;
				$current_date->modify( '+1 month' );
			}
		} elseif ( 'year' === $unit ) {
			$current_date = new \DateTime();
			$current_date->setTimestamp( $start_date );
			$end_date_obj = new \DateTime();
			$end_date_obj->setTimestamp( $end_date );
			$end_date_obj->setTime( 23, 59, 59 );
			$current_date->modify( 'first day of January this year' );
			$current_date->setTime( 0, 0, 0 );

			while ( $current_date <= $end_date_obj ) {
				$date_key                = $current_date->format( 'Y' );
				$daily_data[ $date_key ] = $default_value;
				$current_date->modify( '+1 year' );
			}
		} elseif ( 'week' === $unit ) {
			$current_date = new \DateTime();
			$current_date->setTimestamp( $start_date );
			$end_date_obj = new \DateTime();
			$end_date_obj->setTimestamp( $end_date );
			$end_date_obj->setTime( 23, 59, 59 );
			$day_of_week    = (int) $current_date->format( 'w' );
			$days_to_monday = ( $day_of_week === 0 ? 6 : $day_of_week - 1 );
			$current_date->modify( "-{$days_to_monday} days" );
			$current_date->setTime( 0, 0, 0 );

			while ( $current_date <= $end_date_obj ) {
				$date_key                = $current_date->format( 'Y-m-d' );
				$daily_data[ $date_key ] = $default_value;
				$current_date->modify( '+1 week' );
			}
		} else {
			$incrementor = DAY_IN_SECONDS;
			for ( $i = $start_date; $i <= $end_date; $i += $incrementor ) {
				$date_key                = wp_date( $date_format, $i );
				$daily_data[ $date_key ] = $default_value;
			}
		}

		return $daily_data;
	}

	/**
	 * @param int   $start_date
	 * @param int   $end_date
	 * @param string $unit
	 * @param array $overview_data
	 * @param array $previous_period
	 * @param array $intervals
	 * @return array
	 */
	protected function build_members_overview_charts( $start_date, $end_date, $unit, $overview_data, $previous_period, $intervals ) {
		$charts = [];

		$previous_overview = $this->get_previous_overview_data( $start_date, $end_date );

		$total_members  = (float) ( $overview_data['total_members'] ?? $overview_data['total_registration'] ?? 0 );
		$previous_total = (float) ( $previous_overview['total_members'] ?? $previous_overview['total_registration'] ?? 0 );

		$total_data_points          = $this->create_static_data_points( $start_date, $end_date, $unit, $total_members );
		$previous_total_data_points = $this->create_static_data_points(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit,
			$previous_total
		);

		$charts[] = $this->build_chart(
			[
				'metric'                     => __( 'Members Overview - Total', 'user-registration' ),
				'description'                => __( 'Total members across all forms and social logins', 'user-registration' ),
				'slug'                       => 'members-overview-total',
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => $unit,
				'intervals'                  => $intervals,
				'total'                      => $total_members,
				'previous_total'             => $previous_total,
				'delta'                      => $this->calculate_delta( $total_members, $previous_total ),
				'avg'                        => 0,
				'previous_avg'               => 0,
				'avg_delta'                  => 0,
				'data'                       => $total_data_points,
				'previous_data'              => $previous_total_data_points,
				'is_money'                   => false,
				'is_avg'                     => false,
				'is_cumulative'              => true,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);

		$form_members  = (float) ( $overview_data['total_form_members'] ?? $overview_data['total_form_registration'] ?? 0 );
		$previous_form = (float) ( $previous_overview['total_form_members'] ?? $previous_overview['total_form_registration'] ?? 0 );

		$form_data_points          = $this->create_static_data_points( $start_date, $end_date, $unit, $form_members );
		$previous_form_data_points = $this->create_static_data_points(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit,
			$previous_form
		);

		$charts[] = $this->build_chart(
			[
				'metric'                     => __( 'Members Overview - Form', 'user-registration' ),
				'description'                => __( 'Total members from forms', 'user-registration' ),
				'slug'                       => 'members-overview-form',
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => $unit,
				'intervals'                  => $intervals,
				'total'                      => $form_members,
				'previous_total'             => $previous_form,
				'delta'                      => $this->calculate_delta( $form_members, $previous_form ),
				'avg'                        => 0,
				'previous_avg'               => 0,
				'avg_delta'                  => 0,
				'data'                       => $form_data_points,
				'previous_data'              => $previous_form_data_points,
				'is_money'                   => false,
				'is_avg'                     => false,
				'is_cumulative'              => true,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);

		$social_members  = (float) ( $overview_data['total_social_members'] ?? $overview_data['total_social_registration'] ?? 0 );
		$previous_social = (float) ( $previous_overview['total_social_members'] ?? $previous_overview['total_social_registration'] ?? 0 );

		$social_data_points          = $this->create_static_data_points( $start_date, $end_date, $unit, $social_members );
		$previous_social_data_points = $this->create_static_data_points(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit,
			$previous_social
		);

		$charts[] = $this->build_chart(
			[
				'metric'                     => __( 'Members Overview - Social', 'user-registration' ),
				'description'                => __( 'Total members from social logins', 'user-registration' ),
				'slug'                       => 'members-overview-social',
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => $unit,
				'intervals'                  => $intervals,
				'total'                      => $social_members,
				'previous_total'             => $previous_social,
				'delta'                      => $this->calculate_delta( $social_members, $previous_social ),
				'avg'                        => 0,
				'previous_avg'               => 0,
				'avg_delta'                  => 0,
				'data'                       => $social_data_points,
				'previous_data'              => $previous_social_data_points,
				'is_money'                   => false,
				'is_avg'                     => false,
				'is_cumulative'              => true,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);

		return $charts;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param array $signup_analytics_data
	 * @param array $previous_period
	 * @param array $intervals
	 * @param array|null $form
	 * @return array
	 */
	protected function build_signup_analytics_charts( $start_date, $end_date, $unit, $signup_analytics_data, $previous_period, $intervals ) {
		$charts = [];

		if ( empty( $signup_analytics_data ) || ! is_array( $signup_analytics_data ) ) {
			$signup_analytics_data = [];
		}
		foreach ( $signup_analytics_data as $form_id => $form_data ) {
			if ( empty( $form_data ) || ! is_array( $form_data ) ) {
				$empty_data = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, [] );

				$charts[] = $this->build_chart(
					[
						'metric'                     => sprintf(
							/* translators: %d: Form id */
							__( 'Form Analytics (#%d) - Impressions', 'user-registration' ),
							(int) $form_id
						),
						'slug'                       => 'signup-analytics-impressions-' . $form_id,
						'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
						'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
						'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
						'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
						'unit'                       => $unit,
						'intervals'                  => $intervals,
						'total'                      => 0,
						'previous_total'             => 0,
						'delta'                      => 0,
						'avg'                        => 0,
						'previous_avg'               => 0,
						'avg_delta'                  => 0,
						'data'                       => $empty_data,
						'previous_data'              => $empty_data,
						'is_money'                   => false,
						'is_avg'                     => false,
						'is_cumulative'              => false,
						'is_percentage'              => false,
						'currency'                   => $this->chart_data_formatter->get_currency(),
						'is_loaded'                  => true,
						'is_most_recent'             => false,
						'most_recent_total'          => 0,
						'previous_most_recent_total' => 0,
					]
				);

				$charts[] = $this->build_chart(
					[
						'metric'                     => sprintf(
							/* translators: %d: Form id */
							__( 'Form Analytics (#%d) - Completed', 'user-registration' ),
							(int) $form_id
						),
						'slug'                       => 'signup-analytics-completed-' . $form_id,
						'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
						'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
						'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
						'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
						'unit'                       => $unit,
						'intervals'                  => $intervals,
						'total'                      => 0,
						'previous_total'             => 0,
						'delta'                      => 0,
						'avg'                        => 0,
						'previous_avg'               => 0,
						'avg_delta'                  => 0,
						'data'                       => $empty_data,
						'previous_data'              => $empty_data,
						'is_money'                   => false,
						'is_avg'                     => false,
						'is_cumulative'              => false,
						'is_percentage'              => false,
						'currency'                   => $this->chart_data_formatter->get_currency(),
						'is_loaded'                  => true,
						'is_most_recent'             => false,
						'most_recent_total'          => 0,
						'previous_most_recent_total' => 0,
					]
				);

				$charts[] = $this->build_chart(
					[
						'metric'                     => sprintf(
							/* translators: %d: Form id */
							__( 'Form Analytics (#%d) - Abandoned', 'user-registration' ),
							(int) $form_id
						),
						'description'                => __( 'Total abandoned forms', 'user-registration' ),
						'slug'                       => 'signup-analytics-abandoned-' . $form_id,
						'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
						'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
						'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
						'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
						'unit'                       => $unit,
						'intervals'                  => $intervals,
						'total'                      => 0,
						'previous_total'             => 0,
						'delta'                      => 0,
						'avg'                        => 0,
						'previous_avg'               => 0,
						'avg_delta'                  => 0,
						'data'                       => $empty_data,
						'previous_data'              => $empty_data,
						'is_money'                   => false,
						'is_avg'                     => false,
						'is_cumulative'              => false,
						'is_percentage'              => false,
						'currency'                   => $this->chart_data_formatter->get_currency(),
						'is_loaded'                  => true,
						'is_most_recent'             => false,
						'most_recent_total'          => 0,
						'previous_most_recent_total' => 0,
					]
				);

				$charts[] = $this->build_chart(
					[
						'metric'                     => sprintf(
							/* translators: %d: Form id */
							__( 'Form Analytics (#%d) - Bounced', 'user-registration' ),
							(int) $form_id
						),
						'slug'                       => 'signup-analytics-bounced-' . $form_id,
						'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
						'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
						'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
						'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
						'unit'                       => $unit,
						'intervals'                  => $intervals,
						'total'                      => 0,
						'previous_total'             => 0,
						'delta'                      => 0,
						'avg'                        => 0,
						'previous_avg'               => 0,
						'avg_delta'                  => 0,
						'data'                       => $empty_data,
						'previous_data'              => $empty_data,
						'is_money'                   => false,
						'is_avg'                     => false,
						'is_cumulative'              => false,
						'is_percentage'              => false,
						'currency'                   => $this->chart_data_formatter->get_currency(),
						'is_loaded'                  => true,
						'is_most_recent'             => false,
						'most_recent_total'          => 0,
						'previous_most_recent_total' => 0,
					]
				);

				continue;
			}

			$impressions_map = [];
			$completed_map   = [];
			$abandoned_map   = [];
			$bounced_map     = [];

			foreach ( $form_data as $row ) {
				$time = is_array( $row ) ? ( $row['time'] ?? '' ) : ( $row->time ?? '' );
				if ( empty( $time ) ) {
					continue;
				}

				$timestamp = strtotime( $time );
				if ( false === $timestamp ) {
					continue;
				}

				$date_key = $this->get_date_key_for_timestamp( $timestamp, $unit );

				$total_count     = is_array( $row ) ? ( $row['total_count'] ?? 0 ) : ( $row->total_count ?? 0 );
				$submitted_count = is_array( $row ) ? ( $row['submitted_count'] ?? 0 ) : ( $row->submitted_count ?? 0 );
				$abandoned_count = is_array( $row ) ? ( $row['abandoned_count'] ?? 0 ) : ( $row->abandoned_count ?? 0 );
				$bounced_count   = is_array( $row ) ? ( $row['bounced_count'] ?? 0 ) : ( $row->bounced_count ?? 0 );

				if ( empty( $bounced_count ) && $total_count > 0 ) {
					$bounced_count = max( 0, $total_count - $submitted_count - $abandoned_count );
				}

				if ( 'month' === $unit && isset( $impressions_map[ $date_key ] ) ) {
					$impressions_map[ $date_key ] += (float) $total_count;
					$completed_map[ $date_key ]   += (float) $submitted_count;
					$abandoned_map[ $date_key ]   += (float) $abandoned_count;
					$bounced_map[ $date_key ]     += (float) $bounced_count;
				} else {
					$impressions_map[ $date_key ] = (float) $total_count;
					$completed_map[ $date_key ]   = (float) $submitted_count;
					$abandoned_map[ $date_key ]   = (float) $abandoned_count;
					$bounced_map[ $date_key ]     = (float) $bounced_count;
				}
			}

			$impressions_data = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, $impressions_map );
			$completed_data   = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, $completed_map );
			$abandoned_data   = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, $abandoned_map );
			$bounced_data     = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, $bounced_map );

			$total_impressions = array_sum( array_column( $impressions_data, 'value' ) );
			$total_completed   = array_sum( array_column( $completed_data, 'value' ) );
			$total_abandoned   = array_sum( array_column( $abandoned_data, 'value' ) );
			$total_bounced     = array_sum( array_column( $bounced_data, 'value' ) );

			$previous_signup_analytics = $this->get_previous_signup_analytics_data( $form_data, $previous_period, $unit );
			$previous_impressions_map  = [];
			$previous_completed_map    = [];
			$previous_abandoned_map    = [];
			$previous_bounced_map      = [];

			$previous_data = isset( $previous_signup_analytics['data'] ) ? $previous_signup_analytics['data'] : $previous_signup_analytics;

			if ( ! empty( $previous_data ) ) {
				foreach ( $previous_data as $row ) {
					$time_or_label = is_array( $row ) ? ( $row['time'] ?? $row['label'] ?? '' ) : ( $row->time ?? $row->label ?? '' );
					if ( empty( $time_or_label ) ) {
						continue;
					}

					$timestamp = strtotime( $time_or_label );
					if ( false === $timestamp ) {
						$timestamp = strtotime( str_replace( ',', '', $time_or_label ) );
					}

					if ( false !== $timestamp ) {
						$date_key = $this->get_date_key_for_timestamp( $timestamp, $unit );

						$total_count     = is_array( $row ) ? ( $row['total_count'] ?? 0 ) : ( $row->total_count ?? 0 );
						$submitted_count = is_array( $row ) ? ( $row['submitted_count'] ?? 0 ) : ( $row->submitted_count ?? 0 );
						$abandoned_count = is_array( $row ) ? ( $row['abandoned_count'] ?? 0 ) : ( $row->abandoned_count ?? 0 );
						$bounced_count   = is_array( $row ) ? ( $row['bounced_count'] ?? 0 ) : ( $row->bounced_count ?? 0 );

						if ( empty( $bounced_count ) && $total_count > 0 ) {
							$bounced_count = max( 0, $total_count - $submitted_count - $abandoned_count );
						}

						if ( 'month' === $unit && isset( $previous_impressions_map[ $date_key ] ) ) {
							$previous_impressions_map[ $date_key ] += (float) $total_count;
							$previous_completed_map[ $date_key ]   += (float) $submitted_count;
							$previous_abandoned_map[ $date_key ]   += (float) $abandoned_count;
							$previous_bounced_map[ $date_key ]     += (float) $bounced_count;
						} else {
							$previous_impressions_map[ $date_key ] = (float) $total_count;
							$previous_completed_map[ $date_key ]   = (float) $submitted_count;
							$previous_abandoned_map[ $date_key ]   = (float) $abandoned_count;
							$previous_bounced_map[ $date_key ]     = (float) $bounced_count;
						}
					}
				}
			}

			$previous_impressions_data = $this->chart_data_formatter->generate_data_points(
				$previous_period['previous_from'],
				$previous_period['previous_to'],
				$unit,
				$previous_impressions_map
			);
			$previous_completed_data   = $this->chart_data_formatter->generate_data_points(
				$previous_period['previous_from'],
				$previous_period['previous_to'],
				$unit,
				$previous_completed_map
			);
			$previous_abandoned_data   = $this->chart_data_formatter->generate_data_points(
				$previous_period['previous_from'],
				$previous_period['previous_to'],
				$unit,
				$previous_abandoned_map
			);
			$previous_bounced_data     = $this->chart_data_formatter->generate_data_points(
				$previous_period['previous_from'],
				$previous_period['previous_to'],
				$unit,
				$previous_bounced_map
			);

			$previous_total_impressions = array_sum( array_column( $previous_impressions_data, 'value' ) );
			$previous_total_completed   = array_sum( array_column( $previous_completed_data, 'value' ) );
			$previous_total_abandoned   = array_sum( array_column( $previous_abandoned_data, 'value' ) );
			$previous_total_bounced     = array_sum( array_column( $previous_bounced_data, 'value' ) );

			$data_count   = count( $impressions_data );
			$avg          = $data_count > 0 ? $total_impressions / $data_count : 0;
			$previous_avg = count( $previous_impressions_data ) > 0 ? $previous_total_impressions / count( $previous_impressions_data ) : 0;

			$charts[] = $this->build_chart(
				[
					'metric'                     => sprintf(
						/* translators: %d: Form id */
						__( 'Signup Analytics (#%d) - Impressions', 'user-registration' ),
						(int) $form_id
					),
					'slug'                       => 'signup-analytics-impressions-' . $form_id,
					'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
					'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
					'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
					'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
					'unit'                       => $unit,
					'intervals'                  => $intervals,
					'total'                      => $total_impressions,
					'previous_total'             => $previous_total_impressions,
					'delta'                      => $this->calculate_delta( $total_impressions, $previous_total_impressions ),
					'avg'                        => $avg,
					'previous_avg'               => $previous_avg,
					'avg_delta'                  => $this->calculate_delta( $avg, $previous_avg ),
					'data'                       => $impressions_data,
					'previous_data'              => $previous_impressions_data,
					'is_money'                   => false,
					'is_avg'                     => false,
					'is_cumulative'              => false,
					'is_percentage'              => false,
					'currency'                   => $this->chart_data_formatter->get_currency(),
					'is_loaded'                  => true,
					'is_most_recent'             => false,
					'most_recent_total'          => 0,
					'previous_most_recent_total' => 0,
					'rate'                       => $total_impressions > 0 ? $total_completed / $total_impressions : 0,
				]
			);

			$data_count   = count( $completed_data );
			$avg          = $data_count > 0 ? $total_completed / $data_count : 0;
			$previous_avg = count( $previous_completed_data ) > 0 ? $previous_total_completed / count( $previous_completed_data ) : 0;

			$charts[] = $this->build_chart(
				[
					'metric'                     => sprintf(
						/* translators: %d: Form id */
						__( 'Signup Analytics (#%d) - completed', 'user-registration' ),
						(int) $form_id
					),
					'slug'                       => 'signup-analytics-completed-' . $form_id,
					'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
					'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
					'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
					'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
					'unit'                       => $unit,
					'intervals'                  => $intervals,
					'total'                      => $total_completed,
					'previous_total'             => $previous_total_completed,
					'delta'                      => $this->calculate_delta( $total_completed, $previous_total_completed ),
					'avg'                        => $avg,
					'previous_avg'               => $previous_avg,
					'avg_delta'                  => $this->calculate_delta( $avg, $previous_avg ),
					'data'                       => $completed_data,
					'previous_data'              => $previous_completed_data,
					'is_money'                   => false,
					'is_avg'                     => false,
					'is_cumulative'              => false,
					'is_percentage'              => false,
					'currency'                   => $this->chart_data_formatter->get_currency(),
					'is_loaded'                  => true,
					'is_most_recent'             => false,
					'most_recent_total'          => 0,
					'previous_most_recent_total' => 0,
					'rate'                       => $total_completed > 0 ? $total_completed / $total_impressions : 0,
				]
			);

			$data_count   = count( $abandoned_data );
			$avg          = $data_count > 0 ? $total_abandoned / $data_count : 0;
			$previous_avg = count( $previous_abandoned_data ) > 0 ? $previous_total_abandoned / count( $previous_abandoned_data ) : 0;

			$charts[] = $this->build_chart(
				[
					'metric'                     => sprintf(
						/* translators: %d: Form id */
						__( 'Signup Analytics (#%d) - Abandoned', 'user-registration' ),
						(int) $form_id
					),
					'slug'                       => 'signup-analytics-abandoned-' . $form_id,
					'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
					'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
					'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
					'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
					'unit'                       => $unit,
					'intervals'                  => $intervals,
					'total'                      => $total_abandoned,
					'previous_total'             => $previous_total_abandoned,
					'delta'                      => $this->calculate_delta( $total_abandoned, $previous_total_abandoned ),
					'avg'                        => $avg,
					'previous_avg'               => $previous_avg,
					'avg_delta'                  => $this->calculate_delta( $avg, $previous_avg ),
					'data'                       => $abandoned_data,
					'previous_data'              => $previous_abandoned_data,
					'is_money'                   => false,
					'is_avg'                     => false,
					'is_cumulative'              => false,
					'is_percentage'              => false,
					'currency'                   => $this->chart_data_formatter->get_currency(),
					'is_loaded'                  => true,
					'is_most_recent'             => false,
					'most_recent_total'          => 0,
					'previous_most_recent_total' => 0,
					'rate'                       => $total_abandoned > 0 ? $total_abandoned / $total_impressions : 0,
				]
			);

			$data_count   = count( $bounced_data );
			$avg          = $data_count > 0 ? $total_bounced / $data_count : 0;
			$previous_avg = count( $previous_bounced_data ) > 0 ? $previous_total_bounced / count( $previous_bounced_data ) : 0;

			$charts[] = $this->build_chart(
				[
					'metric'                     => sprintf(
						/* translators: %d: Form id */
						__( 'Signup Analytics (#%d) - Bounced', 'user-registration' ),
						(int) $form_id
					),
					'slug'                       => 'signup-analytics-bounced-' . $form_id,
					'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
					'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
					'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
					'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
					'unit'                       => $unit,
					'intervals'                  => $intervals,
					'total'                      => $total_bounced,
					'previous_total'             => $previous_total_bounced,
					'delta'                      => $this->calculate_delta( $total_bounced, $previous_total_bounced ),
					'avg'                        => $avg,
					'previous_avg'               => $previous_avg,
					'avg_delta'                  => $this->calculate_delta( $avg, $previous_avg ),
					'data'                       => $bounced_data,
					'previous_data'              => $previous_bounced_data,
					'is_money'                   => false,
					'is_avg'                     => false,
					'is_cumulative'              => false,
					'is_percentage'              => false,
					'currency'                   => $this->chart_data_formatter->get_currency(),
					'is_loaded'                  => true,
					'is_most_recent'             => false,
					'most_recent_total'          => 0,
					'previous_most_recent_total' => 0,
					'rate'                       => $total_bounced > 0 ? $total_bounced / $total_impressions : 0,
				]
			);
		}

		return $charts;
	}

	/**
	 * @param int   $start_date
	 * @param int   $end_date
	 * @param array $top_referrer_data
	 * @param array $previous_period
	 * @return array
	 */
	protected function build_top_referrer_chart( $start_date, $end_date, $top_referrer_data, $previous_period ) {
		$total = array_reduce(
			$top_referrer_data,
			function ( $acc, $curr ) {
				return $acc + absint( $curr['total_visits'] ?? 0 );
			},
			0
		);

		return $this->build_chart(
			[
				'metric'                     => __( 'Top Referrer Pages', 'user-registration' ),
				'description'                => __( 'Top pages that referred users to registration forms', 'user-registration' ),
				'slug'                       => 'top-referrer-pages',
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => 'day',
				'intervals'                  => [],
				'total'                      => $total,
				'previous_total'             => 0,
				'delta'                      => 0,
				'avg'                        => 0,
				'previous_avg'               => 0,
				'avg_delta'                  => 0,
				'data'                       => $top_referrer_data,
				'previous_data'              => [],
				'additional_data'            => array(
					'referrers' => $top_referrer_data,
					'type'      => 'bar',
				),
				'is_money'                   => false,
				'is_avg'                     => false,
				'is_cumulative'              => false,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return array
	 */
	protected function get_previous_overview_data( $start_date, $end_date ) {
		$duration       = $end_date - $start_date;
		$previous_end   = $start_date - DAY_IN_SECONDS;
		$previous_start = $previous_end - $duration;
		$data_service   = new AnalyticsDataService();
		return $data_service->get_members_overview( 'all', $previous_start, $previous_end );
	}

	/**
	 * @param array $current_signup_analytics
	 * @param array $previous_period
	 * @param string $unit
	 * @return array
	 */
	protected function get_previous_signup_analytics_data( $current_signup_analytics, $previous_period, $unit ) {
		if ( ! function_exists( 'urfa_get_isca_data' ) ) {
			return array( 'data' => [] );
		}
		$duration_map  = array(
			'day'   => 'Day',
			'week'  => 'Week',
			'month' => 'Month',
		);
		$duration      = isset( $duration_map[ $unit ] ) ? $duration_map[ $unit ] : 'Week';
		$previous_from = wp_date( 'Y-m-d', $previous_period['previous_from'] );
		$previous_to   = wp_date( 'Y-m-d', $previous_period['previous_to'] );

		return urfa_get_isca_data( 'all', $previous_from, $previous_to, $duration );
	}

	/**
	 * @param int    $start_date
	 * @param int    $end_date
	 * @param string $unit
	 * @param float  $total_value
	 * @return array
	 */
	protected function create_static_data_points( $start_date, $end_date, $unit, $total_value ) {
		$data_points = [];
		$current     = $start_date;

		if ( 'week' === $unit ) {
			$increment   = WEEK_IN_SECONDS;
			$date_format = 'Y-m-d';
		} elseif ( 'month' === $unit ) {
			$current_date = new \DateTime();
			$current_date->setTimestamp( $start_date );
			$end_date_obj = new \DateTime();
			$end_date_obj->setTimestamp( $end_date );

			$current_date->modify( 'first day of this month' );
			$current_date->setTime( 0, 0, 0 );

			while ( $current_date <= $end_date_obj ) {
				$date_key      = $current_date->format( 'Y-m-01' );
				$data_points[] = [
					'time'  => $date_key,
					'value' => $total_value,
				];
				$current_date->modify( '+1 month' );
			}

			return $data_points;
		} else {
			$increment   = DAY_IN_SECONDS;
			$date_format = 'Y-m-d';
		}

		$point_count = 0;
		while ( $current <= $end_date ) {
			$date_key      = wp_date( $date_format, $current );
			$data_points[] = [
				'time'  => $date_key,
				'value' => $total_value,
			];
			$current      += $increment;
			++$point_count;
		}

		return $data_points;
	}

	/**
	 * @param int   $start_date
	 * @param int   $end_date
	 * @param array $registration_source_data
	 * @param array $previous_period
	 * @return array
	 */
	protected function build_registration_source_chart( $start_date, $end_date, $registration_source_data, $previous_period ) {
		$data_points = [];
		$labels      = [];
		$values      = [];

		if ( ! is_array( $registration_source_data ) ) {
			$registration_source_data = [];
		}

		arsort( $registration_source_data );

		foreach ( $registration_source_data as $form_title => $count ) {
			$labels[] = $form_title;
			$values[] = (float) $count;

			$data_points[] = array(
				'label' => $form_title,
				'value' => (float) $count,
			);
		}

		$total                        = array_sum( $values );
		$previous_registration_source = $this->get_previous_registration_source_data( $start_date, $end_date );
		$previous_total               = array_sum( array_values( $previous_registration_source ) );

		return $this->build_chart(
			[
				'metric'                     => __( 'Registration Source', 'user-registration' ),
				'description'                => __( 'Breakdown of registrations by form', 'user-registration' ),
				'slug'                       => 'registration-source',
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => 'day',
				'intervals'                  => $labels,
				'total'                      => $total,
				'previous_total'             => $previous_total,
				'delta'                      => $this->calculate_delta( $total, $previous_total ),
				'avg'                        => 0,
				'previous_avg'               => 0,
				'avg_delta'                  => 0,
				'data'                       => $data_points,
				'previous_data'              => [],
				'additional_data'            => array(
					'sources' => $registration_source_data,
					'type'    => 'doughnut',
				),
				'is_money'                   => false,
				'is_avg'                     => false,
				'is_cumulative'              => false,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return array
	 */
	protected function get_previous_registration_source_data( $start_date, $end_date ) {
		$duration       = $end_date - $start_date;
		$previous_end   = $start_date - DAY_IN_SECONDS;
		$previous_start = $previous_end - $duration;

		$data_service = new AnalyticsDataService();
		return $data_service->get_registration_source_data( 'all', $previous_start, $previous_end );
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param array $revenue_data
	 * @param array $previous_period
	 * @param array $intervals
	 * @return array
	 */
	protected function build_revenue_charts( $start_date, $end_date, $unit, $revenue_data, $previous_period, $intervals ) {
		$charts       = [];
		$data_service = new AnalyticsDataService();

		if ( empty( $revenue_data ) || ! isset( $revenue_data['daily_data'] ) ) {
			$revenue_data = [
				'total_revenue'            => 0,
				'net_revenue'              => 0,
				'refunded_revenue'         => 0,
				'total_orders'             => 0,
				'total_refunds'            => 0,
				'new_payments_revenue'     => 0,
				'new_subscription_revenue' => 0,
				'renewal_revenue'          => 0,
				'average_order_value'      => 0,
				'daily_data'               => $data_service->initialize_daily_data_structure( $start_date, $end_date, $unit ),
			];
		}

		$previous_revenue = $data_service->get_revenue_date_range_data(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit
		);

		$revenue_charts = [
			[
				'metric'   => __( 'Total Revenue', 'user-registration' ),
				'slug'     => 'total-revenue',
				'data_key' => 'total_revenue',
				'is_avg'   => false,
			],
			[
				'metric'   => __( 'Refunded Revenue', 'user-registration' ),
				'slug'     => 'refunded-revenue',
				'data_key' => 'refunded_revenue',
				'is_avg'   => false,
			],
			[
				'metric'   => __( 'New Payments Revenue', 'user-registration' ),
				'slug'     => 'new-payments-revenue',
				'data_key' => 'new_payments_revenue',
				'is_avg'   => false,
			],
			[
				'metric'   => __( 'New Subscription Revenue', 'user-registration' ),
				'slug'     => 'new-subscription-revenue',
				'data_key' => 'new_subscription_revenue',
				'is_avg'   => false,
			],
			[
				'metric'   => __( 'Subscription Renewal Revenue', 'user-registration' ),
				'slug'     => 'subscription-renewal-revenue',
				'data_key' => 'renewal_revenue',
				'is_avg'   => false,
			],
			[
				'metric'   => __( 'Average Order/Payments Value', 'user-registration' ),
				'slug'     => 'average-order-value',
				'data_key' => 'average_order_value',
				'is_avg'   => true,
			],
		];

		foreach ( $revenue_charts as $chart_config ) {
			$charts[] = $this->build_revenue_chart(
				$chart_config['metric'],
				$chart_config['slug'],
				$chart_config['data_key'],
				$revenue_data,
				$previous_revenue,
				$start_date,
				$end_date,
				$unit,
				$previous_period,
				$intervals,
				$chart_config['is_avg']
			);
		}

		$net_revenue_map = [];
		foreach ( $revenue_data['daily_data'] ?? [] as $key => $data ) {
			$net_revenue_map[ $key ] = ( $data['total_revenue'] ?? 0 ) - ( $data['refunded_revenue'] ?? 0 );
		}
		$net_revenue_data         = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, $net_revenue_map );
		$previous_net_revenue_map = [];
		foreach ( $previous_revenue['daily_data'] ?? [] as $key => $data ) {
			$previous_net_revenue_map[ $key ] = ( $data['total_revenue'] ?? 0 ) - ( $data['refunded_revenue'] ?? 0 );
		}
		$previous_net_revenue_data = $this->chart_data_formatter->generate_data_points(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit,
			$previous_net_revenue_map
		);
		$net_revenue               = (float) ( $revenue_data['net_revenue'] ?? 0 );
		$previous_net              = (float) ( $previous_revenue['net_revenue'] ?? 0 );
		$data_count                = count( $net_revenue_data );
		$avg                       = $data_count > 0 ? $net_revenue / $data_count : 0;
		$previous_avg              = count( $previous_net_revenue_data ) > 0 ? $previous_net / count( $previous_net_revenue_data ) : 0;

		$charts[] = $this->build_chart(
			[
				'metric'                     => __( 'Net Revenue', 'user-registration' ),
				'description'                => '',
				'slug'                       => 'net-revenue',
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => $unit,
				'intervals'                  => $intervals,
				'total'                      => $net_revenue,
				'previous_total'             => $previous_net,
				'delta'                      => $this->calculate_delta( $net_revenue, $previous_net ),
				'avg'                        => $avg,
				'previous_avg'               => $previous_avg,
				'avg_delta'                  => $this->calculate_delta( $avg, $previous_avg ),
				'data'                       => $net_revenue_data,
				'previous_data'              => $previous_net_revenue_data,
				'is_money'                   => true,
				'is_avg'                     => false,
				'is_cumulative'              => false,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);

		$charts[] = $this->build_revenue_chart(
			__( 'New Orders', 'user-registration' ),
			'new-orders',
			'completed_orders',
			$revenue_data,
			$previous_revenue,
			$start_date,
			$end_date,
			$unit,
			$previous_period,
			$intervals,
			false
		);

		$last_chart             = array_pop( $charts );
		$last_chart['is_money'] = false;
		$charts[]               = $last_chart;

		$charts[] = $this->build_revenue_chart(
			__( 'Refunds', 'user-registration' ),
			'refunds',
			'refunded_orders',
			$revenue_data,
			$previous_revenue,
			$start_date,
			$end_date,
			$unit,
			$previous_period,
			$intervals,
			false
		);

		$last_chart             = array_pop( $charts );
		$last_chart['is_money'] = false;
		$charts[]               = $last_chart;

		return $charts;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param array $subscriptions_data
	 * @param array $previous_period
	 * @param array $intervals
	 * @return array
	 */
	protected function build_subscriptions_charts( $start_date, $end_date, $unit, $subscriptions_data, $previous_period, $intervals ) {
		$charts       = [];
		$data_service = new AnalyticsDataService();

		if ( empty( $subscriptions_data ) || ! isset( $subscriptions_data['daily_data'] ) || empty( $subscriptions_data['daily_data'] ) ) {
			$default_value = [
				'new_subscriptions'    => 0,
				'active_subscriptions' => 0,
				'trial_subscriptions'  => 0,
			];

			$subscriptions_data = [
				'new_subscriptions' => 0,
				'daily_data'        => $this->initialize_empty_data_structure( $start_date, $end_date, $unit, $default_value ),
			];
		}

		$previous_subscriptions = $data_service->get_subscriptions_date_range_data(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit
		);

		$new_subscriptions_map = [];
		foreach ( $subscriptions_data['daily_data'] ?? [] as $key => $data ) {
			$new_subscriptions_map[ $key ] = $data['new_subscriptions'] ?? 0;
		}
		$new_subscriptions_chart_data   = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, $new_subscriptions_map );
		$previous_new_subscriptions_map = [];
		foreach ( $previous_subscriptions['daily_data'] ?? [] as $key => $data ) {
			$previous_new_subscriptions_map[ $key ] = $data['new_subscriptions'] ?? 0;
		}
		$previous_new_subscriptions_data = $this->chart_data_formatter->generate_data_points(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit,
			$previous_new_subscriptions_map
		);

		$total_new      = (float) ( $subscriptions_data['new_subscriptions'] ?? 0 );
		$previous_total = (float) ( $previous_subscriptions['new_subscriptions'] ?? 0 );
		$data_count     = count( $new_subscriptions_chart_data );
		$avg            = $data_count > 0 ? $total_new / $data_count : 0;
		$previous_avg   = count( $previous_new_subscriptions_data ) > 0 ? $previous_total / count( $previous_new_subscriptions_data ) : 0;

		$charts[] = $this->build_chart(
			[
				'metric'                     => __( 'New Subscriptions', 'user-registration' ),
				'description'                => '',
				'slug'                       => 'new-subscriptions',
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => $unit,
				'intervals'                  => $intervals,
				'total'                      => $total_new,
				'previous_total'             => $previous_total,
				'delta'                      => $this->calculate_delta( $total_new, $previous_total ),
				'avg'                        => $avg,
				'previous_avg'               => $previous_avg,
				'avg_delta'                  => $this->calculate_delta( $avg, $previous_avg ),
				'data'                       => $new_subscriptions_chart_data,
				'previous_data'              => $previous_new_subscriptions_data,
				'is_money'                   => false,
				'is_avg'                     => false,
				'is_cumulative'              => false,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);

		return $charts;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param array $mrr_data
	 * @param array $arr_data
	 * @param array $previous_period
	 * @param array $intervals
	 * @return array
	 */
	protected function build_recurring_revenue_charts( $start_date, $end_date, $unit, $mrr_data, $arr_data, $previous_period, $intervals ) {
		$charts       = [];
		$data_service = new AnalyticsDataService();

		if ( empty( $mrr_data ) || ! isset( $mrr_data['daily_data'] ) ) {
			$mrr_data = [
				'total_mrr'  => 0,
				'daily_data' => $this->initialize_empty_data_structure( $start_date, $end_date, $unit, 0 ),
			];
		}

		if ( empty( $arr_data ) || ! isset( $arr_data['daily_data'] ) ) {
			$arr_data = [
				'total_arr'  => 0,
				'daily_data' => $this->initialize_empty_data_structure( $start_date, $end_date, $unit, 0 ),
			];
		}

		$previous_mrr_data = $data_service->get_mrr_date_range_data(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit
		);
		$previous_arr_data = $data_service->get_arr_date_range_data(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit
		);

		$mrr_map                 = $mrr_data['daily_data'] ?? [];
		$mrr_chart_data          = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, $mrr_map );
		$previous_mrr_map        = $previous_mrr_data['daily_data'] ?? [];
		$previous_mrr_chart_data = $this->chart_data_formatter->generate_data_points(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit,
			$previous_mrr_map
		);

		$total_mrr          = (float) ( $mrr_data['total_mrr'] ?? 0 );
		$previous_total_mrr = (float) ( $previous_mrr_data['total_mrr'] ?? 0 );
		$data_count         = count( $mrr_chart_data );
		$mrr_avg            = $data_count > 0 ? $total_mrr / $data_count : 0;
		$previous_mrr_avg   = count( $previous_mrr_chart_data ) > 0 ? $previous_total_mrr / count( $previous_mrr_chart_data ) : 0;

		$charts[] = $this->build_chart(
			[
				'metric'                     => __( 'Monthly Recurring Revenue (MRR)', 'user-registration' ),
				'description'                => __( 'Total monthly recurring revenue from active subscriptions', 'user-registration' ),
				'slug'                       => 'mrr',
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => $unit,
				'intervals'                  => $intervals,
				'total'                      => $total_mrr,
				'previous_total'             => $previous_total_mrr,
				'delta'                      => $this->calculate_delta( $total_mrr, $previous_total_mrr ),
				'avg'                        => $mrr_avg,
				'previous_avg'               => $previous_mrr_avg,
				'avg_delta'                  => $this->calculate_delta( $mrr_avg, $previous_mrr_avg ),
				'data'                       => $mrr_chart_data,
				'previous_data'              => $previous_mrr_chart_data,
				'is_money'                   => true,
				'is_avg'                     => false,
				'is_cumulative'              => false,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);

		$arr_map                 = $arr_data['daily_data'] ?? [];
		$arr_chart_data          = $this->chart_data_formatter->generate_data_points( $start_date, $end_date, $unit, $arr_map );
		$previous_arr_map        = $previous_arr_data['daily_data'] ?? [];
		$previous_arr_chart_data = $this->chart_data_formatter->generate_data_points(
			$previous_period['previous_from'],
			$previous_period['previous_to'],
			$unit,
			$previous_arr_map
		);

		$total_arr          = (float) ( $arr_data['total_arr'] ?? 0 );
		$previous_total_arr = (float) ( $previous_arr_data['total_arr'] ?? 0 );
		$arr_avg            = $data_count > 0 ? $total_arr / $data_count : 0;
		$previous_arr_avg   = count( $previous_arr_chart_data ) > 0 ? $previous_total_arr / count( $previous_arr_chart_data ) : 0;

		$charts[] = $this->build_chart(
			[
				'metric'                     => __( 'Annual Recurring Revenue (ARR)', 'user-registration' ),
				'description'                => __( 'Total annual recurring revenue from active subscriptions', 'user-registration' ),
				'slug'                       => 'arr',
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => $unit,
				'intervals'                  => $intervals,
				'total'                      => $total_arr,
				'previous_total'             => $previous_total_arr,
				'delta'                      => $this->calculate_delta( $total_arr, $previous_total_arr ),
				'avg'                        => $arr_avg,
				'previous_avg'               => $previous_arr_avg,
				'avg_delta'                  => $this->calculate_delta( $arr_avg, $previous_arr_avg ),
				'data'                       => $arr_chart_data,
				'previous_data'              => $previous_arr_chart_data,
				'is_money'                   => true,
				'is_avg'                     => false,
				'is_cumulative'              => false,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);

		return $charts;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param array $memberships_distribution
	 * @param array $previous_period
	 * @return array
	 */
	protected function build_memberships_distribution_chart( $start_date, $end_date, $memberships_distribution, $previous_period ) {
		$data_service          = new AnalyticsDataService();
		$previous_distribution = $data_service->get_memberships_distribution(
			$previous_period['previous_from'],
			$previous_period['previous_to']
		);

		if ( ! is_array( $memberships_distribution ) ) {
			$memberships_distribution = [];
		}
		if ( ! is_array( $previous_distribution ) ) {
			$previous_distribution = [];
		}

		$data_points = [];
		$labels      = [];
		$values      = [];

		arsort( $memberships_distribution );

		foreach ( $memberships_distribution as $membership_name => $count ) {
			$labels[] = $membership_name;
			$values[] = (float) $count;

			$data_points[] = [
				'label' => $membership_name,
				'value' => (float) $count,
			];
		}

		$total          = array_sum( $values );
		$previous_total = array_sum( array_values( $previous_distribution ) );

		return $this->build_chart(
			[
				'metric'                     => __( 'Memberships Distribution', 'user-registration' ),
				'description'                => __( 'Breakdown of members by membership plan', 'user-registration' ),
				'slug'                       => 'membership-distribution',
				'date_from'                  => $this->chart_data_formatter->format_date_display( $start_date ),
				'date_to'                    => $this->chart_data_formatter->format_date_display( $end_date ),
				'previous_from'              => $this->chart_data_formatter->format_date_display( $previous_period['previous_from'] ),
				'previous_to'                => $this->chart_data_formatter->format_date_display( $previous_period['previous_to'] ),
				'unit'                       => 'day',
				'intervals'                  => $labels,
				'total'                      => $total,
				'previous_total'             => $previous_total,
				'delta'                      => $this->calculate_delta( $total, $previous_total ),
				'avg'                        => 0,
				'previous_avg'               => 0,
				'avg_delta'                  => 0,
				'data'                       => $data_points,
				'previous_data'              => [],
				'additional_data'            => [
					'distribution' => $memberships_distribution,
					'type'         => 'doughnut',
				],
				'is_money'                   => false,
				'is_avg'                     => false,
				'is_cumulative'              => false,
				'is_percentage'              => false,
				'currency'                   => $this->chart_data_formatter->get_currency(),
				'is_loaded'                  => true,
				'is_most_recent'             => false,
				'most_recent_total'          => 0,
				'previous_most_recent_total' => 0,
			]
		);
	}
}
