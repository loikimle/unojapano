<?php

namespace WPEverest\URM\Pro\Analytics\Services;

defined( 'ABSPATH' ) || exit;

use WPEverest\URMembership\TableList;
use WPEverest\URM\Analytics\Services\AnalyticsDataService as BaseAnalyticsDataService;

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

class AnalyticsDataService extends BaseAnalyticsDataService {

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return array
	 */
	public function get_members_overview( $start_date, $end_date ) {
		global $wpdb;

		$total_form_members   = 0;
		$total_social_members = 0;
		$start_date_str       = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str         = wp_date( 'Y-m-d H:i:s', $end_date );

		$form_members = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID
					FROM {$wpdb->users} u
					INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
					WHERE um.meta_key = %s
					AND u.user_registered BETWEEN %s AND %s",
				'ur_form_id',
				$start_date_str,
				$end_date_str
			)
		);

		$total_form_members = count( $form_members );

		$social_members = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT u.ID
				FROM {$wpdb->prefix}users u
				INNER JOIN {$wpdb->prefix}usermeta um ON u.ID = um.user_id
				WHERE um.meta_key LIKE %s
				AND u.user_registered BETWEEN %s AND %s",
				'user_registration_social_connect_%_username',
				$start_date_str,
				$end_date_str
			)
		);

		$total_social_members = count( $social_members );

		foreach ( $social_members as $member ) {
			$member_form = get_user_meta( $member->ID, 'ur_form_id', true );
			if ( ! empty( $member_form ) ) {
				--$total_form_members;
			}
		}

		$total_members = $total_form_members + $total_social_members;

		return [
			'total_members'        => $total_members,
			'total_form_members'   => $total_form_members,
			'total_social_members' => $total_social_members,
		];
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @return array
	 */
	public function get_comparison_data( $start_date, $end_date, $unit = 'day' ) {
		$duration       = $end_date - $start_date;
		$previous_end   = $start_date - DAY_IN_SECONDS;
		$previous_start = $previous_end - $duration;

		return $this->get_date_range_members_data( $previous_start, $previous_end, $unit );
	}

	/**
	 * @param string $date_from
	 * @param string $date_to
	 * @param string $unit
	 * @return array
	 */
	public function get_form_analytics_data( $date_from, $date_to, $unit = 'day' ) {
		$form_count = wp_count_posts( 'user_registration' )->publish;
		if ( ! class_exists( 'UserPostVisitsDB' ) || ! $form_count ) {
			return [];
		}

		global $wpdb;
		$table = ( new \UserPostVisitsDB() )->get_table();

		if ( ! $this->table_exists( $table ) ) {
			return [];
		}

		$unit = in_array( $unit, [ 'hour', 'day', 'week', 'month', 'year' ], true ) ? $unit : 'day';

		$group_by_map = [
			'hour'  => [
				'select' => "DATE_FORMAT(created_at, '%Y-%m-%d %H')",
				'format' => 'Y-m-d H',
			],
			'day'   => [
				'select' => 'DATE(created_at)',
				'format' => 'Y-m-d',
			],
			'week'  => [
				'select' => 'YEARWEEK(created_at, 1)',
				'format' => 'Y-m-d',
			],
			'month' => [
				'select' => "DATE_FORMAT(created_at, '%Y-%m')",
				'format' => 'Y-m',
			],
			'year'  => [
				'select' => "DATE_FORMAT(created_at, '%Y')",
				'format' => 'Y',
			],
		];

		$group_by    = $group_by_map[ $unit ]['select'];
		$time_format = $group_by_map[ $unit ]['format'];

		$where  = [ 'form_id > 0' ];
		$params = [];

		if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
			$where[]  = 'created_at BETWEEN %s AND %s';
			$params[] = $date_from;
			$params[] = $date_to;
		}

		if ( 'week' === $unit ) {
			$sql = "
			SELECT
				form_id,
				{$group_by} AS time_key,
				MIN(DATE(created_at)) AS week_start,
				MAX(DATE(created_at)) AS week_end,
				SUM(form_submitted) AS submitted_count,
				SUM(form_abandoned) AS abandoned_count,
				COUNT(*) AS total_count
			FROM {$table}
			WHERE " . implode( ' AND ', $where ) . '
			GROUP BY form_id, time_key
			ORDER BY form_id, time_key
		';
		} else {
			$sql = "
			SELECT
				form_id,
				{$group_by} AS time_key,
				SUM(form_submitted) AS submitted_count,
				SUM(form_abandoned) AS abandoned_count,
				COUNT(*) AS total_count
			FROM {$table}
			WHERE " . implode( ' AND ', $where ) . '
			GROUP BY form_id, time_key
			ORDER BY form_id, time_key
        ';
		}

		$results = $wpdb->get_results( $wpdb->prepare( $sql, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( empty( $results ) ) {
			$form_ids = array_keys( ur_get_all_user_registration_form() );
			$form_ids = empty( $form_ids ) ? [ 0 ] : $form_ids;
			foreach ( $form_ids as $form_id ) {
				$results[] = (object) [
					'form_id'         => $form_id,
					'time_key'        => wp_date( $time_format ),
					'total_count'     => 0,
					'submitted_count' => 0,
					'abandoned_count' => 0,
				];
			}
		}

		$data = [];
		foreach ( $results as $row ) {
			$form_id   = (int) $row->form_id;
			$total     = (int) $row->total_count;
			$submitted = (int) $row->submitted_count;
			$abandoned = (int) $row->abandoned_count;
			$bounced   = max( 0, $total - $submitted - $abandoned );

			if ( 'week' === $unit ) {
				$time_display = wp_date( $time_format, strtotime( $row->week_start ) );
			} else {
				$time_display = wp_date( $time_format, strtotime( $row->time_key ) );
			}

			if ( ! isset( $data[ $form_id ] ) ) {
				$data[ $form_id ] = [];
			}

			$data[ $form_id ][] = [
				'time'             => $time_display,
				'time_key'         => $row->time_key,
				'value'            => $total,
				'total_count'      => $total,
				'submitted_count'  => $submitted,
				'abandoned_count'  => $abandoned,
				'bounced_count'    => $bounced,
				'abandonment_rate' => $total > 0 ? round( ( $abandoned / $total ) * 100, 2 ) : 0,
				'submission_rate'  => $total > 0 ? round( ( $submitted / $total ) * 100, 2 ) : 0,
			];
		}

		return $data;
	}


	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return array
	 */
	public function get_registration_source_data( $start_date, $end_date ) {
		if ( ! function_exists( 'ur_get_all_user_registration_form' ) ) {
			return [];
		}
		global $wpdb;
		$forms = ur_get_all_user_registration_form();

		if ( count( $forms ) <= 1 ) {
			return [];
		}

		$registration_source_data = [];

		foreach ( $forms as $form_id_key => $form_title ) {
			$users = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT u.ID
					FROM {$wpdb->users} u
					INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
					WHERE um.meta_key = %s AND um.meta_value = %s
					AND u.user_registered BETWEEN %s AND %s",
					'ur_form_id',
					$form_id_key,
					wp_date( 'Y-m-d H:i:s', $start_date ),
					wp_date( 'Y-m-d H:i:s', $end_date )
				)
			);

			$count = count( $users );
			if ( $count > 0 ) {
				$registration_source_data[ $form_title ] = $count;
			}
		}

		return $registration_source_data;
	}

	/**
	 * @param string $date_from
	 * @param string $date_to
	 * @param int $limit
	 * @return array
	 */
	public function get_top_referrer_data( $date_from, $date_to, $limit = 5 ) {
		if ( ! class_exists( 'UserPostVisitsDB' ) ) {
			return [];
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$table = ( new \UserPostVisitsDB() )->get_table();

		if ( ! $this->table_exists( $table ) ) {
			return [];
		}

		$sql = "
			SELECT
				referer_url,
				COUNT(*) AS total_visits,
				COUNT(DISTINCT session_id) AS unique_sessions,
				SUM(form_submitted = 1) AS total_submissions
			FROM {$table}
			WHERE created_at BETWEEN %s AND %s
			AND referer_url IS NOT NULL
			AND referer_url <> ''
		";

		$args = [ $date_from, $date_to ];

		$sql .= '
			GROUP BY referer_url
			ORDER BY total_visits DESC
			LIMIT %d
		';

		$args[] = $limit;

		$query   = $wpdb->prepare( $sql, $args ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $results;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return float
	 */
	public function get_total_revenue( $start_date, $end_date ) {
		global $wpdb;
		$orders_table = TableList::orders_table();

		if ( ! $this->table_exists( $orders_table ) ) {
			return 0.0;
		}

		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(total_amount), 0)
				FROM {$orders_table}
				WHERE status = 'completed'
				AND created_at BETWEEN %s AND %s",
				$start_date_str,
				$end_date_str
			)
		);

		return (float) $result;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return float
	 */
	public function get_net_revenue( $start_date, $end_date ) {
		$total_revenue    = $this->get_total_revenue( $start_date, $end_date );
		$refunded_revenue = $this->get_refunded_revenue( $start_date, $end_date );

		return $total_revenue - $refunded_revenue;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return float
	 */
	public function get_new_payments_revenue( $start_date, $end_date ) {
		global $wpdb;
		$orders_table = TableList::orders_table();

		if ( ! $this->table_exists( $orders_table ) ) {
			return 0.0;
		}

		$start_date_str           = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str             = wp_date( 'Y-m-d H:i:s', $end_date );
		$lifetime_revenue         = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(total_amount), 0)
				FROM {$orders_table}
				WHERE status = 'completed'
				AND order_type = 'paid'
				AND created_at BETWEEN %s AND %s",
				$start_date_str,
				$end_date_str
			)
		);
		$new_subscription_revenue = $this->get_new_subscription_revenue( $start_date, $end_date );
		$renewal_revenue          = $this->get_subscription_renewal_revenue( $start_date, $end_date );

		return (float) $lifetime_revenue + $new_subscription_revenue - $renewal_revenue;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return float
	 */
	public function get_new_subscription_revenue( $start_date, $end_date ) {
		global $wpdb;
		$orders_table = TableList::orders_table();

		if ( ! $this->table_exists( $orders_table ) ) {
			return 0.0;
		}

		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(o.total_amount), 0)
				FROM {$orders_table} o
				WHERE o.status = 'completed'
				AND o.order_type = 'subscription'
				AND o.created_at BETWEEN %s AND %s
				AND NOT EXISTS (
					SELECT 1 FROM {$orders_table} o2
					WHERE o2.user_id = o.user_id
					AND o2.order_type = 'subscription'
					AND o2.id < o.id
				)",
				$start_date_str,
				$end_date_str
			)
		);

		return (float) $result;
	}

	/**

	 * @param int $start_date
	 * @param int $end_date
	 * @return float
	 */
	public function get_subscription_renewal_revenue( $start_date, $end_date ) {
		global $wpdb;
		$orders_table = TableList::orders_table();

		if ( ! $this->table_exists( $orders_table ) ) {
			return 0.0;
		}

		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(o.total_amount), 0)
				FROM {$orders_table} o
				WHERE o.status = 'completed'
				AND o.order_type = 'subscription'
				AND o.created_at BETWEEN %s AND %s
				AND EXISTS (
					SELECT 1 FROM {$orders_table} o2
					WHERE o2.user_id = o.user_id
					AND o2.order_type = 'subscription'
					AND o2.id < o.id
				)",
				$start_date_str,
				$end_date_str
			)
		);

		return (float) $result;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return float
	 */
	public function get_average_order_value( $start_date, $end_date ) {
		global $wpdb;
		$orders_table = TableList::orders_table();

		if ( ! $this->table_exists( $orders_table ) ) {
			return 0.0;
		}

		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(AVG(total_amount), 0)
				FROM {$orders_table}
				WHERE status = 'completed'
				AND created_at BETWEEN %s AND %s",
				$start_date_str,
				$end_date_str
			)
		);

		return (float) $result;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return float
	 */
	public function get_refunded_revenue( $start_date, $end_date ) {
		global $wpdb;
		$orders_table = TableList::orders_table();

		if ( ! $this->table_exists( $orders_table ) ) {
			return 0.0;
		}

		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(total_amount), 0)
				FROM {$orders_table}
				WHERE status = 'refunded'
				AND created_at BETWEEN %s AND %s",
				$start_date_str,
				$end_date_str
			)
		);

		return (float) $result;
	}

	/**
	 * @param int $date
	 * @return float
	 */
	public function get_mrr( $date = null, $membership = null ) {
		if ( null === $date ) {
			$date = current_time( 'timestamp' );
		}

		global $wpdb;
		$subscriptions_table = TableList::subscriptions_table();

		if ( ! $this->table_exists( $subscriptions_table ) ) {
			return 0.0;
		}

		$month_start = wp_date( 'Y-m-01 00:00:00', $date );
		$month_end   = wp_date( 'Y-m-t 23:59:59', $date );

		$membership_filter = '';
		$query_params      = [ $month_end, $month_start ];
		if ( ! empty( $membership ) ) {
			$membership_filter = ' AND item_id = %d';
			$query_params[]    = absint( $membership );
		}

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(
					CASE
						WHEN billing_cycle = 'month' THEN billing_amount
						WHEN billing_cycle = 'year' THEN billing_amount / 12
						WHEN billing_cycle = 'week' THEN billing_amount * 4.33
						WHEN billing_cycle = 'day' THEN billing_amount * 30
						ELSE 0
					END
				), 0)
				FROM {$subscriptions_table}
				WHERE status IN ('active', 'trial')
				AND start_date <= %s
				AND (expiry_date IS NULL OR expiry_date >= %s) {$membership_filter}",
				...$query_params
			)
		);

		return (float) $result;
	}

	/**
	 * @param int $date
	 * @return float
	 */
	public function get_arr( $date = null, $membership = null ) {
		if ( null === $date ) {
			$date = current_time( 'timestamp' );
		}

		global $wpdb;
		$subscriptions_table = TableList::subscriptions_table();

		if ( ! $this->table_exists( $subscriptions_table ) ) {
			return 0.0;
		}

		$year_start = wp_date( 'Y-01-01 00:00:00', $date );
		$year_end   = wp_date( 'Y-12-31 23:59:59', $date );

		$membership_filter = '';
		$query_params      = [ $year_end, $year_start ];
		if ( ! empty( $membership ) ) {
			$membership_filter = ' AND item_id = %d';
			$query_params[]    = absint( $membership );
		}

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(
					CASE
						WHEN billing_cycle = 'year' THEN billing_amount
						WHEN billing_cycle = 'month' THEN billing_amount * 12
						WHEN billing_cycle = 'week' THEN billing_amount * 52
						WHEN billing_cycle = 'day' THEN billing_amount * 365
						ELSE 0
					END
				), 0)
				FROM {$subscriptions_table}
				WHERE status IN ('active', 'trial')
				AND start_date <= %s
				AND (expiry_date IS NULL OR expiry_date >= %s) {$membership_filter}",
				...$query_params
			)
		);

		return (float) $result;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return int
	 */
	public function get_new_orders_count( $start_date, $end_date ) {
		global $wpdb;
		$orders_table = TableList::orders_table();

		if ( ! $this->table_exists( $orders_table ) ) {
			return 0;
		}

		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$orders_table}
				WHERE status = 'completed'
				AND created_at BETWEEN %s AND %s",
				$start_date_str,
				$end_date_str
			)
		);

		return (int) $result;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return int
	 */
	public function get_new_subscriptions_count( $start_date, $end_date ) {
		global $wpdb;
		$subscriptions_table = TableList::subscriptions_table();

		if ( ! $this->table_exists( $subscriptions_table ) ) {
			return 0;
		}

		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$subscriptions_table}
				WHERE created_at BETWEEN %s AND %s",
				$start_date_str,
				$end_date_str
			)
		);

		return (int) $result;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param string $scope 'all'|'membership'|'others'
	 * @param int|null $membership
	 * @return array
	 */
	public function get_revenue_date_range_data( $start_date, $end_date, $unit = 'day', $scope = 'all', $membership = null ) {
		global $wpdb;
		$orders_table = ( new TableList() )->orders_table();
		$daily_data   = $this->initialize_daily_data_structure( $start_date, $end_date, $unit );

		$include_single_items = ( 'others' === $scope || 'all' === $scope );

		$single_item_revenue = [];
		if ( $include_single_items ) {
			$single_item_revenue = $this->get_single_item_revenue_data( $start_date, $end_date, $unit );
		}

		if ( 'others' === $scope ) {
			$total_single_item_revenue = 0;
			foreach ( $single_item_revenue as $time_key => $data ) {
				$single_item_amount = $data['single_item_revenue'] ?? 0;
				if ( isset( $daily_data[ $time_key ] ) ) {
					$daily_data[ $time_key ]['total_revenue']        = $single_item_amount;
					$daily_data[ $time_key ]['new_payments_revenue'] = $single_item_amount;
					$daily_data[ $time_key ]['single_item_revenue']  = $single_item_amount;
					$total_single_item_revenue                      += $single_item_amount;
				}
			}

			return [
				'total_revenue'            => $total_single_item_revenue,
				'net_revenue'              => $total_single_item_revenue,
				'refunded_revenue'         => 0,
				'total_orders'             => 0,
				'total_refunds'            => 0,
				'new_payments_revenue'     => $total_single_item_revenue,
				'new_subscription_revenue' => 0,
				'renewal_revenue'          => 0,
				'single_item_revenue'      => $total_single_item_revenue,
				'average_order_value'      => 0,
				'daily_data'               => $daily_data,
			];
		}

		if ( ! $this->table_exists( $orders_table ) ) {
			if ( $include_single_items ) {
				$total_single_item_revenue = 0;
				foreach ( $single_item_revenue as $time_key => $data ) {
					$single_item_amount = $data['single_item_revenue'] ?? 0;
					if ( isset( $daily_data[ $time_key ] ) ) {
						$daily_data[ $time_key ]['total_revenue']        = $single_item_amount;
						$daily_data[ $time_key ]['new_payments_revenue'] = $single_item_amount;
						$daily_data[ $time_key ]['single_item_revenue']  = $single_item_amount;
						$total_single_item_revenue                      += $single_item_amount;
					}
				}

				return [
					'total_revenue'            => $total_single_item_revenue,
					'net_revenue'              => $total_single_item_revenue,
					'refunded_revenue'         => 0,
					'total_orders'             => 0,
					'total_refunds'            => 0,
					'new_payments_revenue'     => $total_single_item_revenue,
					'new_subscription_revenue' => 0,
					'renewal_revenue'          => 0,
					'single_item_revenue'      => $total_single_item_revenue,
					'average_order_value'      => 0,
					'daily_data'               => $daily_data,
				];
			}

			return [
				'total_revenue'            => 0,
				'net_revenue'              => 0,
				'refunded_revenue'         => 0,
				'total_orders'             => 0,
				'total_refunds'            => 0,
				'new_payments_revenue'     => 0,
				'new_subscription_revenue' => 0,
				'renewal_revenue'          => 0,
				'single_item_revenue'      => 0,
				'average_order_value'      => 0,
				'daily_data'               => $daily_data,
			];
		}

		$membership   = ( 'membership' === $scope && null !== $membership ) ? absint( $membership ) : null;
		$group_by_map = [
			'hour'  => "DATE_FORMAT(created_at, '%Y-%m-%d %H')",
			'day'   => 'DATE(created_at)',
			'week'  => 'DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY))',
			'month' => "DATE_FORMAT(created_at, '%Y-%m')",
			'year'  => "DATE_FORMAT(created_at, '%Y')",
		];

		$group_by       = $group_by_map[ $unit ] ?? $group_by_map['day'];
		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$daily_data = $this->initialize_daily_data_structure( $start_date, $end_date, $unit );

		$membership_filter = '';
		$query_params      = [ $start_date_str, $end_date_str ];
		if ( ! empty( $membership ) ) {
			$membership_filter = ' AND item_id = %d';
			$query_params[]    = absint( $membership );
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					{$group_by} AS time_key,
					COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0) AS total_revenue,
					COALESCE(SUM(CASE WHEN status = 'completed' AND order_type = 'paid' THEN total_amount ELSE 0 END), 0) AS paid_revenue,
					COALESCE(SUM(CASE WHEN status = 'completed' AND order_type = 'subscription' THEN total_amount ELSE 0 END), 0) AS subscription_revenue,
					COALESCE(SUM(CASE WHEN status = 'refunded' THEN total_amount ELSE 0 END), 0) AS refunded_revenue,
					COUNT(CASE WHEN status = 'completed' THEN 1 END) AS completed_orders,
					COUNT(CASE WHEN status = 'refunded' THEN 1 END) AS refunded_orders
				FROM {$orders_table}
				WHERE created_at BETWEEN %s AND %s {$membership_filter}
				GROUP BY time_key
				ORDER BY time_key",
				...$query_params
			)
		);

		$sub_query_params = [ $start_date_str, $end_date_str ];
		if ( ! empty( $membership ) ) {
			$sub_query_params[] = absint( $membership );
		}

		$new_subscription_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					{$group_by} AS time_key,
					COALESCE(SUM(o.total_amount), 0) AS new_subscription_revenue
				FROM {$orders_table} o
				WHERE o.status = 'completed'
				AND o.order_type = 'subscription'
				AND o.created_at BETWEEN %s AND %s {$membership_filter}
				AND NOT EXISTS (
					SELECT 1 FROM {$orders_table} o2
					WHERE o2.user_id = o.user_id
					AND o2.order_type = 'subscription'
					AND o2.id < o.id
				)
				GROUP BY time_key
				ORDER BY time_key",
				...$sub_query_params
			)
		);

		$renewal_results      = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					{$group_by} AS time_key,
					COALESCE(SUM(o.total_amount), 0) AS renewal_revenue
				FROM {$orders_table} o
				WHERE o.status = 'completed'
				AND o.order_type = 'subscription'
				AND o.created_at BETWEEN %s AND %s {$membership_filter}
				AND EXISTS (
					SELECT 1 FROM {$orders_table} o2
					WHERE o2.user_id = o.user_id
					AND o2.order_type = 'subscription'
					AND o2.id < o.id
				)
				GROUP BY time_key
				ORDER BY time_key",
				...$sub_query_params
			)
		);
		$new_subscription_map = [];
		foreach ( $new_subscription_results as $row ) {
			$new_subscription_map[ $row->time_key ] = (float) $row->new_subscription_revenue;
		}

		$renewal_map = [];
		foreach ( $renewal_results as $row ) {
			$renewal_map[ $row->time_key ] = (float) $row->renewal_revenue;
		}

		foreach ( $results as $row ) {
			$time_key             = $row->time_key;
			$paid_revenue         = (float) $row->paid_revenue;
			$new_sub_revenue      = $new_subscription_map[ $time_key ] ?? 0;
			$renewal_revenue      = $renewal_map[ $time_key ] ?? 0;
			$completed_orders     = (int) $row->completed_orders;
			$new_payments_revenue = $paid_revenue + $new_sub_revenue - $renewal_revenue;
			$average_order_value  = $completed_orders > 0 ? ( (float) $row->total_revenue ) / $completed_orders : 0;

			if ( isset( $daily_data[ $time_key ] ) ) {
				$daily_data[ $time_key ] = [
					'total_revenue'            => (float) $row->total_revenue,
					'paid_revenue'             => $paid_revenue,
					'subscription_revenue'     => (float) $row->subscription_revenue,
					'refunded_revenue'         => (float) $row->refunded_revenue,
					'completed_orders'         => $completed_orders,
					'refunded_orders'          => (int) $row->refunded_orders,
					'new_payments_revenue'     => $new_payments_revenue,
					'new_subscription_revenue' => $new_sub_revenue,
					'renewal_revenue'          => $renewal_revenue,
					'average_order_value'      => $average_order_value,
					'single_item_revenue'      => 0,
				];
			}
		}

		if ( $include_single_items ) {
			foreach ( $single_item_revenue as $time_key => $data ) {
				$single_item_amount = $data['single_item_revenue'] ?? 0;

				if ( isset( $daily_data[ $time_key ] ) ) {
					$daily_data[ $time_key ]['single_item_revenue']   = $single_item_amount;
					$daily_data[ $time_key ]['total_revenue']        += $single_item_amount;
					$daily_data[ $time_key ]['new_payments_revenue'] += $single_item_amount;

					$orders = $daily_data[ $time_key ]['completed_orders'];
					if ( $orders > 0 ) {
						$daily_data[ $time_key ]['average_order_value'] = $daily_data[ $time_key ]['total_revenue'] / $orders;
					}
				}
			}
		}

		$total_revenue                  = array_sum( array_column( $daily_data, 'total_revenue' ) );
		$total_refunded                 = array_sum( array_column( $daily_data, 'refunded_revenue' ) );
		$net_revenue                    = $total_revenue - $total_refunded;
		$total_orders                   = array_sum( array_column( $daily_data, 'completed_orders' ) );
		$total_refunds                  = array_sum( array_column( $daily_data, 'refunded_orders' ) );
		$total_new_payments_revenue     = array_sum( array_column( $daily_data, 'new_payments_revenue' ) );
		$total_new_subscription_revenue = array_sum( array_column( $daily_data, 'new_subscription_revenue' ) );
		$total_renewal_revenue          = array_sum( array_column( $daily_data, 'renewal_revenue' ) );
		$total_single_item_revenue      = array_sum( array_column( $daily_data, 'single_item_revenue' ) );
		$total_average_order_value      = $total_orders > 0 ? $total_revenue / $total_orders : 0;

		return [
			'total_revenue'            => $total_revenue,
			'net_revenue'              => $net_revenue,
			'refunded_revenue'         => $total_refunded,
			'total_orders'             => $total_orders,
			'total_refunds'            => $total_refunds,
			'new_payments_revenue'     => $total_new_payments_revenue,
			'new_subscription_revenue' => $total_new_subscription_revenue,
			'renewal_revenue'          => $total_renewal_revenue,
			'single_item_revenue'      => $total_single_item_revenue,
			'average_order_value'      => $total_average_order_value,
			'daily_data'               => $daily_data,
		];
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @return array
	 */
	public function get_mrr_date_range_data( $start_date, $end_date, $unit = 'day', $scope = 'all', $membership = null ) {
		$subscriptions_table = TableList::subscriptions_table();
		$date_keys           = $this->generate_date_keys( $start_date, $end_date, $unit );
		$daily_data          = $this->initialize_data_structure( $date_keys, 0 );

		if ( 'others' === $scope || ! $this->table_exists( $subscriptions_table ) ) {
			return [
				'total_mrr'  => 0,
				'daily_data' => $daily_data,
			];
		}

		$membership_filter = ( 'membership' === $scope && ! empty( $membership ) ) ? absint( $membership ) : null;

		foreach ( $daily_data as $date_key => $value ) {
			$timestamp = strtotime( $date_key );
			if ( false === $timestamp ) {
				if ( preg_match( '/^(\d{4})-(\d{2})$/', $date_key, $matches ) ) {
					$timestamp = strtotime( $matches[1] . '-' . $matches[2] . '-01' );
				} elseif ( preg_match( '/^(\d{4})$/', $date_key, $matches ) ) {
					$timestamp = strtotime( $matches[1] . '-01-01' );
				}
			}

			if ( false !== $timestamp ) {
				$daily_data[ $date_key ] = $this->get_mrr( $timestamp, $membership_filter );
			}
		}

		$total_mrr = array_sum( $daily_data );

		return [
			'total_mrr'  => $total_mrr,
			'daily_data' => $daily_data,
		];
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @return array
	 */
	public function get_arr_date_range_data( $start_date, $end_date, $unit = 'day', $scope = 'all', $membership = null ) {
		$subscriptions_table = TableList::subscriptions_table();
		$date_keys           = $this->generate_date_keys( $start_date, $end_date, $unit );
		$daily_data          = $this->initialize_data_structure( $date_keys, 0 );

		if ( 'others' === $scope || ! $this->table_exists( $subscriptions_table ) ) {
			return [
				'total_arr'  => 0,
				'daily_data' => $daily_data,
			];
		}

		$membership_filter = ( 'membership' === $scope && ! empty( $membership ) ) ? absint( $membership ) : null;

		foreach ( $daily_data as $date_key => $value ) {
			$timestamp = strtotime( $date_key );
			if ( false === $timestamp ) {
				if ( preg_match( '/^(\d{4})-(\d{2})$/', $date_key, $matches ) ) {
					$timestamp = strtotime( $matches[1] . '-' . $matches[2] . '-01' );
				} elseif ( preg_match( '/^(\d{4})$/', $date_key, $matches ) ) {
					$timestamp = strtotime( $matches[1] . '-01-01' );
				}
			}

			if ( false !== $timestamp ) {
				$daily_data[ $date_key ] = $this->get_arr( $timestamp, $membership_filter );
			}
		}

		$total_arr = array_sum( $daily_data );

		return [
			'total_arr'  => $total_arr,
			'daily_data' => $daily_data,
		];
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param string $scope 'all' | 'others' | 'membership'
	 * @return array
	 */
	public function get_subscriptions_date_range_data( $start_date, $end_date, $unit = 'day', $scope = 'all', $membership = null ) {
		global $wpdb;
		$subscriptions_table = TableList::subscriptions_table();

		$date_keys = $this->generate_date_keys( $start_date, $end_date, $unit );

		$default_value = [
			'new_subscriptions'    => 0,
			'active_subscriptions' => 0,
			'trial_subscriptions'  => 0,
		];

		$daily_data = $this->initialize_data_structure( $date_keys, $default_value );

		if ( 'others' === $scope || ! $this->table_exists( $subscriptions_table ) ) {
			return [
				'new_subscriptions' => 0,
				'daily_data'        => $daily_data,
			];
		}

		$group_by_map = [
			'hour'  => "DATE_FORMAT(created_at, '%Y-%m-%d %H')",
			'day'   => 'DATE(created_at)',
			'week'  => 'DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY))',
			'month' => "DATE_FORMAT(created_at, '%Y-%m')",
			'year'  => "DATE_FORMAT(created_at, '%Y')",
		];

		$group_by       = $group_by_map[ $unit ] ?? $group_by_map['day'];
		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$membership_filter = '';
		$query_params      = [ $start_date_str, $end_date_str ];
		if ( 'membership' === $scope && ! empty( $membership ) ) {
			$membership_filter = ' AND item_id = %d';
			$query_params[]    = absint( $membership );
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					{$group_by} AS time_key,
					COUNT(*) AS new_subscriptions,
					COUNT(CASE WHEN status = 'active' THEN 1 END) AS active_subscriptions,
					COUNT(CASE WHEN status = 'trial' THEN 1 END) AS trial_subscriptions
				FROM {$subscriptions_table}
				WHERE created_at BETWEEN %s AND %s {$membership_filter}
				GROUP BY time_key
				ORDER BY time_key",
				...$query_params
			)
		);

		foreach ( $results as $row ) {
			if ( isset( $daily_data[ $row->time_key ] ) ) {
				$daily_data[ $row->time_key ] = [
					'new_subscriptions'    => (int) $row->new_subscriptions,
					'active_subscriptions' => (int) $row->active_subscriptions,
					'trial_subscriptions'  => (int) $row->trial_subscriptions,
				];
			}
		}

		$total_new = array_sum( array_column( $daily_data, 'new_subscriptions' ) );

		return [
			'new_subscriptions' => $total_new,
			'daily_data'        => $daily_data,
		];
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return array
	 */
	public function get_memberships_distribution( $start_date, $end_date ) {
		global $wpdb;
		$subscriptions_table = TableList::subscriptions_table();
		$posts_table         = TableList::posts_table();

		if ( ! $this->table_exists( $subscriptions_table ) || ! $this->table_exists( $posts_table ) ) {
			return [];
		}

		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$unique_plans = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT s.item_id)
				FROM {$subscriptions_table} s
				INNER JOIN {$posts_table} p ON s.item_id = p.ID
				WHERE s.created_at BETWEEN %s AND %s
				AND p.post_type = 'ur_membership'
				AND p.post_status = 'publish'",
				$start_date_str,
				$end_date_str
			)
		);

		if ( (int) $unique_plans <= 1 ) {
			return [];
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.post_title, COUNT(s.ID) as count
				FROM {$subscriptions_table} s
				INNER JOIN {$posts_table} p ON s.item_id = p.ID
				WHERE s.created_at BETWEEN %s AND %s
				AND p.post_type = 'ur_membership'
				AND p.post_status = 'publish'
				GROUP BY s.item_id, p.post_title
				ORDER BY count DESC",
				$start_date_str,
				$end_date_str
			)
		);

		$distribution = [];
		foreach ( $results as $result ) {
			$distribution[ $result->post_title ] = (int) $result->count;
		}

		return $distribution;
	}

	public function get_data_sets() {
		global $wpdb;
		$posts_table      = TableList::posts_table();
		$membership_count = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$posts_table} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'ur_membership'
			AND p.post_status = 'publish'
			AND pm.meta_key = 'ur_membership'"
		);

		$visualization = [
			[
				'slug'    => 'revenue-overview',
				'label'   => __( 'Revenue Overview', 'user-registration' ),
				'metrics' => [
					[
						'slug'  => 'total-revenue',
						'label' => __( 'Total Revenue', 'user-registration' ),
					],
					// [
					//  'slug'  => 'net-revenue',
					//  'label' => __( 'Net Revenue', 'user-registration' ),
					// ],
					[
						'slug'  => 'new-payments-revenue',
						'label' => __( 'New Payments Revenue', 'user-registration' ),
					],
					[
						'slug'  => 'new-subscription-revenue',
						'label' => __( 'New Subscription Revenue', 'user-registration' ),
					],
					[
						'slug'  => 'subscription-renewal-revenue',
						'label' => __( 'Subscription Renewal Revenue', 'user-registration' ),
					],
					[
						'slug'  => 'average-order-value',
						'label' => __( 'Average Order/Payments Value', 'user-registration' ),
					],
					[
						'slug'  => 'refunded-revenue',
						'label' => __( 'Refunded Revenue', 'user-registration' ),
					],
				],
			],
			[
				'slug'    => 'recurring-revenue',
				'label'   => __( 'Recurring Revenue', 'user-registration' ),
				'metrics' => [
					[
						'slug'  => 'mrr',
						'label' => __( 'Monthly Recurring Revenue (MRR)', 'user-registration' ),
					],
					[
						'slug'  => 'arr',
						'label' => __( 'Annual Recurring Revenue (ARR)', 'user-registration' ),
					],
				],
			],
			[
				'slug'    => 'orders-payments',
				'label'   => __( 'Orders & Payments', 'user-registration' ),
				'metrics' => [
					[
						'slug'  => 'new-orders',
						'label' => __( 'New Orders/Payments', 'user-registration' ),
					],
					[
						'slug'  => 'new-subscriptions',
						'label' => __( 'New Subscriptions', 'user-registration' ),
					],
					[
						'slug'  => 'refunds',
						'label' => __( 'Refunds', 'user-registration' ),
					],
				],
			],
			[
				'slug'    => 'members-overview',
				'label'   => __( 'Members Overview', 'user-registration' ),
				'metrics' => [
					[
						'slug'  => 'new-members',
						'label' => __( 'New Members', 'user-registration' ),
					],
					[
						'slug'  => 'approved-members',
						'label' => __( 'Approved Members', 'user-registration' ),
					],
					[
						'slug'  => 'pending-members',
						'label' => __( 'Pending Members', 'user-registration' ),
					],
					[
						'slug'  => 'denied-members',
						'label' => __( 'Denied Members', 'user-registration' ),
					],
				],
			],
		];

		if ( $membership_count > 1 ) {
			$visualization[] = [
				'slug'    => 'membership-distribution',
				'label'   => __( 'Memberships Distribution', 'user-registration' ),
				'type'    => 'pie',
				'metrics' => [
					[
						'slug'  => 'membership-distribution',
						'label' => __( 'All Memberships', 'user-registration' ),
					],
				],
			];
		}

		$forms = ur_get_all_user_registration_form();

		if ( count( $forms ) > 1 ) {
			$visualization[] = [
				'slug'    => 'registration-source',
				'label'   => __( 'Registration Source', 'user-registration' ),
				'type'    => 'pie',
				'metrics' => [
					[
						'slug'  => 'registration-source',
						'label' => __( 'Registration Source', 'user-registration' ),
					],
				],
			];
		}

		$visualization[] = [
			'slug'    => 'top-referrer-pages',
			'label'   => __( 'Top Referrer Pages', 'user-registration' ),
			'type'    => 'list',
			'metrics' => [
				[
					'slug'  => 'top-referrer-pages',
					'label' => __( 'Referrer Pages', 'user-registration' ),
				],
			],
		];

		foreach ( $forms as $form_id => $form_title ) {
				$visualization[] = [
					'slug'    => 'signup-analytics-' . $form_id,
					'label'   => count( $forms ) > 1 ? sprintf(
						/* translators: %s: Form title */
						__( 'Signup Analytics: %s', 'user-registration' ),
						empty( $form_title ) ? "#$form_id" : $form_title
					) : __( 'Signup Analytics', 'user-registration' ),
					'metrics' => [
						[
							'slug'  => 'signup-analytics-impressions-' . $form_id,
							'label' => __( 'Impressions', 'user-registration' ),
						],
						[
							'slug'  => 'signup-analytics-completed-' . $form_id,
							'label' => __( 'Completed', 'user-registration' ),
						],
						[
							'slug'  => 'signup-analytics-abandoned-' . $form_id,
							'label' => __( 'Abandoned', 'user-registration' ),
						],
						[
							'slug'  => 'signup-analytics-bounced-' . $form_id,
							'label' => __( 'Bounced', 'user-registration' ),
						],
					],
					'legacy'  => true,
				];
		}

		return apply_filters(
			'ur_pro_analytics_data_sets',
			[
				'summary'       => [
					[
						'slug'  => 'total-revenue',
						'label' => __( 'Total Revenue', 'user-registration' ),
					],
					// [
					//  'slug'  => 'net-revenue',
					//  'label' => __( 'Net Revenue', 'user-registration' ),
					// ],
					[
						'slug'  => 'new-payments-revenue',
						'label' => __( 'New Payments Revenue', 'user-registration' ),
					],
					[
						'slug'  => 'new-subscription-revenue',
						'label' => __( 'New Subscription Revenue', 'user-registration' ),
					],
					[
						'slug'  => 'subscription-renewal-revenue',
						'label' => __( 'Subscription Renewal Revenue', 'user-registration' ),
					],
					[
						'slug'  => 'average-order-value',
						'label' => __( 'Average Order/Payments Value', 'user-registration' ),
					],
					[
						'slug'  => 'refunded-revenue',
						'label' => __( 'Refunded Revenue', 'user-registration' ),
					],
					[
						'slug'  => 'mrr',
						'label' => __( 'Monthly Recurring Revenue (MRR)', 'user-registration' ),
					],
					[
						'slug'  => 'arr',
						'label' => __( 'Annual Recurring Revenue (ARR)', 'user-registration' ),
					],
					[
						'slug'  => 'new-orders',
						'label' => __( 'New Orders/Payments', 'user-registration' ),
					],
					[
						'slug'  => 'new-subscriptions',
						'label' => __( 'New Subscriptions', 'user-registration' ),
					],
					[
						'slug'  => 'refunds',
						'label' => __( 'Refunds', 'user-registration' ),
					],
					[
						'slug'  => 'new-members',
						'label' => __( 'New Members', 'user-registration' ),
					],
					[
						'slug'  => 'approved-members',
						'label' => __( 'Approved Members', 'user-registration' ),
					],
					[
						'slug'  => 'pending-members',
						'label' => __( 'Pending Members', 'user-registration' ),
					],
					[
						'slug'  => 'denied-members',
						'label' => __( 'Denied Members', 'user-registration' ),
					],
				],
				'visualization' => $visualization,
			]
		);
	}
}
