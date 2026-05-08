<?php

/**
 * URMembership MembersSubscriptionEventsRepository
 */

namespace WPEverest\URMembership\Admin\Repositories;

use WPEverest\URMembership\Admin\Interfaces\MembersSubscriptionEventsInterface;
use WPEverest\URMembership\TableList;

class MembersSubscriptionEventsRepository extends BaseRepository implements MembersSubscriptionEventsInterface {

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * Constructor of this class
	 */
	public function __construct() {
		$this->table = TableList::subscription_events_table();
	}

	/**
	 * Get subscription events by subscription ID (paginated)
	 *
	 * @param int $subscription_id
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 */
	public function get_subscription_events_by_subscription_id(
		$subscription_id,
		$limit = 20,
		$offset = 0
	) {

		$limit  = absint( $limit );
		$offset = absint( $offset );

		return $this->wpdb()->get_results(
			$this->wpdb()->prepare(
				"SELECT *
				FROM {$this->table}
				WHERE subscription_id = %d
				ORDER BY created_at DESC
				LIMIT %d OFFSET %d",
				$subscription_id,
				$limit,
				$offset
			),
			ARRAY_A
		);
	}

	/**
	 * Insert a subscription event
	 *
	 * @param array $data
	 *
	 * @return int|false Inserted row ID on success, false on failure
	 */
	public function insert_event( array $data ) {

		$defaults = array(
			'subscription_id' => 0,
			'user_id'         => 0,
			'event_type'      => '',
			'event_status'    => null,
			'title'           => '',
			'message'         => null,
			'reference_id'    => null,
			'meta'            => null,
			'created_at'      => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $data, $defaults );

		if ( empty( $data['subscription_id'] ) || empty( $data['event_type'] ) ) {
			return false;
		}

		$inserted = $this->wpdb()->insert(
			$this->table,
			array(
				'subscription_id' => (int) $data['subscription_id'],
				'user_id'         => (int) $data['user_id'],
				'event_type'      => sanitize_key( $data['event_type'] ),
				'event_status'    => $data['event_status'] ? sanitize_text_field( $data['event_status'] ) : null,
				'title'           => sanitize_text_field( $data['title'] ),
				'message'         => $data['message'],
				'reference_id'    => $data['reference_id'] ? sanitize_text_field( $data['reference_id'] ) : null,
				'meta'            => is_array( $data['meta'] ) || is_object( $data['meta'] )
					? wp_json_encode( $data['meta'] )
					: $data['meta'],
				'created_at'      => $data['created_at'],
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		if ( false === $inserted ) {
			return false;
		}

		return (int) $this->wpdb()->insert_id;
	}

	/**
	 * Get all subscription events by subscription ID
	 *
	 * @param int $subscription_id
	 *
	 * @return array
	 */
	public function get_total_events_by_subscription_id( $subscription_id ) {
		return (int) $this->wpdb()->get_var(
			$this->wpdb()->prepare(
				"SELECT COUNT(*)
			 FROM {$this->table}
			 WHERE subscription_id = %d",
				$subscription_id
			)
		);
	}
}
