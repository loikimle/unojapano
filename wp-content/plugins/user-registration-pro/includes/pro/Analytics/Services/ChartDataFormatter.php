<?php

namespace WPEverest\URM\Pro\Analytics\Services;

use WPEverest\URM\Analytics\Traits\DateUtils;

defined( 'ABSPATH' ) || exit;

class ChartDataFormatter {

	use DateUtils;

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @return array
	 */
	public function generate_intervals( $start_date, $end_date, $unit = 'day' ) {
		$intervals = [];

		if ( in_array( $unit, [ 'week', 'month', 'year' ], true ) ) {
			$format_map = [
				'week'  => 'd M',
				'month' => 'M Y',
				'year'  => 'Y',
			];

			$increment_map = [
				'week'  => '+1 week',
				'month' => '+1 month',
				'year'  => '+1 year',
			];

			$current_date = $this->create_aligned_datetime( $start_date, $unit, false );
			$end_date_obj = $this->create_aligned_datetime( $end_date, $unit, true );

			while ( $current_date <= $end_date_obj ) {
				$intervals[] = $current_date->format( $format_map[ $unit ] );
				$current_date->modify( $increment_map[ $unit ] );
			}

			return $intervals;
		}

		$increment_map = [
			'hour' => HOUR_IN_SECONDS,
			'day'  => DAY_IN_SECONDS,
		];

		$format_map = [
			'hour' => 'd M H:i',
			'day'  => 'd M',
		];

		$current   = $start_date;
		$increment = $increment_map[ $unit ] ?? DAY_IN_SECONDS;
		$format    = $format_map[ $unit ] ?? 'd M';

		while ( $current <= $end_date ) {
			$intervals[] = wp_date( $format, $current );
			$current    += $increment;
		}

		return $intervals;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param array  $data_map
	 * @return array
	 */
	public function generate_data_points( $start_date, $end_date, $unit = 'day', $data_map = [] ) {
		$data_points = [];

		if ( in_array( $unit, [ 'week', 'month', 'year' ], true ) ) {
			$format_map = [
				'week'  => 'Y-m-d',
				'month' => 'Y-m',
				'year'  => 'Y',
			];

			$time_format_map = [
				'week'  => 'Y-m-d',
				'month' => 'Y-m-01',
				'year'  => 'Y-01-01',
			];

			$increment_map = [
				'week'  => '+1 week',
				'month' => '+1 month',
				'year'  => '+1 year',
			];

			$current_date = $this->create_aligned_datetime( $start_date, $unit, false );
			$end_date_obj = $this->create_aligned_datetime( $end_date, $unit, true );

			while ( $current_date <= $end_date_obj ) {
				$date_key = $current_date->format( $format_map[ $unit ] );
				$value    = isset( $data_map[ $date_key ] ) ? $data_map[ $date_key ] : 0;

				$data_points[] = [
					'time'  => $current_date->format( $time_format_map[ $unit ] ),
					'value' => (float) $value,
				];

				$current_date->modify( $increment_map[ $unit ] );
			}

			return $data_points;
		}

		$increment_map = [
			'hour' => HOUR_IN_SECONDS,
			'day'  => DAY_IN_SECONDS,
		];

		$format_map = [
			'hour' => 'Y-m-d H',
			'day'  => 'Y-m-d',
		];

		$current     = $start_date;
		$increment   = $increment_map[ $unit ] ?? DAY_IN_SECONDS;
		$date_format = $format_map[ $unit ] ?? 'Y-m-d';

		while ( $current <= $end_date ) {
			$date_key = wp_date( $date_format, $current );
			$value    = isset( $data_map[ $date_key ] ) ? $data_map[ $date_key ] : 0;

			$data_points[] = [
				'time'  => $date_key,
				'value' => (float) $value,
			];

			$current += $increment;
		}

		return $data_points;
	}

	/**
	 * @param int $timestamp
	 * @return string
	 */
	public function format_date_display( $timestamp ) {
		return wp_date( 'd M, Y', $timestamp );
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return array
	 */
	public function get_previous_period( $start_date, $end_date ) {
		$duration = $end_date - $start_date;

		return [
			'previous_from' => $start_date - $duration - DAY_IN_SECONDS,
			'previous_to'   => $start_date - DAY_IN_SECONDS,
		];
	}

	/**
	 * @return string
	 */
	public function get_currency() {
		$currency = get_option( 'user_registration_payment_currency', 'USD' );
		return strtoupper( $currency );
	}
}
