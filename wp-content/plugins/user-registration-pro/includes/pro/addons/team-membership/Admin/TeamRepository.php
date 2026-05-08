<?php

namespace WPEverest\URTeamMembership\Admin;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URMembership\TableList;

class TeamRepository {
	protected $table;
	protected $posts_meta_table;
	protected $users;

	public function __construct() {
		$this->table            = TableList::posts_table();
		$this->posts_meta_table = TableList::posts_meta_table();
		$this->users            = TableList::users_table();
	}

	/**
	 * Return global wpdb.
	 *
	 * @return \wpdb
	 */
	public function wpdb() {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * @return array
	 */
	public function get_all_membership_team( $per_page, $current_page ) {
		$args = array(
			'post_type'      => 'ur_membership_team',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
			'orderby'        => 'ID',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => 'urm_team_leader_id',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'urm_used_seats',
					'compare' => 'EXISTS',
				),
			),
		);
		// $sql  = "
		//  SELECT wpp.ID,
		//          wpp.post_title AS team_name,
		//          wu.display_name AS team_leader,
		//          wpm_seats.meta_value AS members,
		//          wpp.post_date AS created_date
		//  FROM $this->table wpp
		//  JOIN $this->posts_meta_table wpm_leader
		//      ON wpm_leader.post_id = wpp.ID
		//      AND wpm_leader.meta_key = 'urm_team_leader_id'
		//  JOIN $this->users wu
		//      ON wu.ID = wpm_leader.meta_value
		//  JOIN $this->posts_meta_table wpm_seats
		//      ON wpm_seats.post_id = wpp.ID
		//      AND wpm_seats.meta_key = 'urm_used_seats'
		//  WHERE wpp.post_type = 'ur_membership_team'
		//  AND wpp.post_status = 'publish'
		//  ORDER BY 1 DESC
		// ";

		// $membership_team = $this->wpdb()->get_results(
		//  $sql,
		//  ARRAY_A
		// );

		$query = new \WP_Query( $args );
		$team  = array();

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {

				$leader_id = get_post_meta( $post->ID, 'urm_team_leader_id', true );
				$leader    = $leader_id ? get_userdata( $leader_id ) : null;

				$team[] = array(
					'ID'           => $post->ID,
					'team_name'    => $post->post_title,
					'team_leader'  => $leader ? $leader->display_name : '',
					'members'      => (int) get_post_meta( $post->ID, 'urm_used_seats', true ),
					'created_date' => $post->post_date,
				);
			}
		}

		wp_reset_postdata();

		return array(
			'items'       => $team,
			'total_items' => (int) $query->found_posts,
		);
	}

	/**
	 * Get users not in same membership plan
	 *
	 * @param $team_id
	 *
	 * @return array List of available users.
	 */

	public function get_users_not_in_same_membership_plan( $team_id ) {
		global $wpdb;

		$member_emails = get_post_meta( $team_id, 'urm_member_emails', true );
		$member_emails = is_array( $member_emails ) ? $member_emails : array_map( 'trim', explode( ',', $member_emails ) );

		$team_users = [];
		if ( ! empty( $member_emails ) ) {
			foreach ( $member_emails as $email ) {
				if ( ! empty( $email ) ) {
					$user = get_user_by( 'email', $email );
					if ( $user ) {
						$team_users[ $user->ID ] = $user;
					}
				}
			}
		}

		$membership_id   = (int) get_post_meta( $team_id, 'urm_membership_id', true );
		$all_users       = get_users();
		$available_users = [];

		foreach ( $all_users as $user ) {
			$user_id = $user->ID;

			if ( isset( $team_users[ $user_id ] ) ) {
				$available_users[ $user_id ] = $user;
				continue;
			}

			// Check if user has a subscription for this same membership
			$has_subscription = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ur_membership_subscriptions
					WHERE user_id = %d AND item_id = %d",
					$user_id,
					$membership_id
				)
			);

			if ( $has_subscription ) {
				continue;
			}

			// Check if user belongs to any team that has this same membership
			$user_team_ids = get_user_meta( $user_id, 'urm_team_ids', true );

			if ( ! empty( $user_team_ids ) && is_array( $user_team_ids ) ) {
				$should_include = false;
				foreach ( $user_team_ids as $other_team_id ) {
					$other_team_id = absint( $other_team_id );
					if ( ! $other_team_id ) {
						continue;
					}

					$team_membership_id = (int) get_post_meta( $other_team_id, 'urm_membership_id', true );

					if ( $team_membership_id === $membership_id ) {
						$should_include = false;
						break;
					} else {
						$should_include = true;
					}
				}
				if ( $should_include ) {
					$available_users[ $user_id ] = $user;
				}
				continue;
			}

			$available_users[ $user_id ] = $user;
		}

		return array_values( $available_users );
	}

	/**
	 * get_single_team_by_ID
	 *
	 * @param $team_id
	 *
	 * @return array|object|\stdClass|void|null
	 */
	public function get_single_team_by_ID( $team_id ) {
		$team_post = get_post( $team_id );

		if ( ! $team_post || 'ur_membership_team' !== $team_post->post_type ) {
			return null;
		}

		$all_meta = get_post_meta( $team_post->ID );

		$meta = array();
		foreach ( $all_meta as $key => $value ) {
			$meta[ $key ] = maybe_unserialize( count( $value ) === 1 ? $value[0] : $value );
		}

		$leader_id = $meta['urm_team_leader_id'] ?? '';
		$leader    = $leader_id ? get_userdata( $leader_id ) : null;

		$membership_id      = $meta['urm_membership_id'] ?? '';
		$membership_details = '';
		if ( $membership_id ) {
			$membership_repository = new MembershipRepository();
			$membership_details    = $membership_repository->get_single_membership_by_ID( $membership_id );
		}

		return array(
			'ID'           => $team_post->ID,
			'team_name'    => $team_post->post_title,
			'post_status'  => $team_post->post_status,
			'post_type'    => $team_post->post_type,
			'created_date' => $team_post->post_date,
			'author_id'    => $team_post->post_author,
			'team_leader'  => $leader ? array(
				'ID'           => $leader->ID,
				'display_name' => $leader->display_name,
				'email'        => $leader->user_email,
			) : null,
			'membership'   => $membership_details ? $membership_details : null,
			'meta'         => $meta,
		);
	}

	public function cancel_subscription( $team_id ) {
		$team = get_post( $team_id );

		if ( ! $team || 'ur_membership_team' !== $team->post_type ) {
			return false;
		}

		$subscription_id = get_post_meta( $team_id, 'urm_subscription_id', true );

		if ( ! $subscription_id ) {
			return false;
		}

		$subscription_repository = new SubscriptionRepository();
		$subscription_repository->cancel_subscription_by_id( $subscription_id );

		return true;
	}

	/**
	 * get_member_all_teams
	 *
	 * @param $member_id
	 *
	 * @return array|false|object|\stdClass|void
	 */
	public function get_member_all_teams( $member_id ) {
		$result = $this->wpdb()->get_results(
			$this->wpdb()->prepare(
				"SELECT wpp.* FROM $this->table wpp
		         INNER JOIN $this->posts_meta_table  wpm ON wpp.ID = wpm.post_id
		         WHERE wpm.meta_key = %s AND wpm.meta_value = %d  ORDER BY wpp.post_date DESC",
				'urm_team_leader_id',
				$member_id
			),
			ARRAY_A
		);

		foreach ( $result as &$team_post ) {

			$all_meta = get_post_meta( $team_post['ID'] );

			$meta = array();
			foreach ( $all_meta as $key => $value ) {
				$meta[ $key ] = maybe_unserialize( count( $value ) === 1 ? $value[0] : $value );
			}
			$team_post['meta'] = $meta;
		}

		return ! $result ? false : $result;
	}

	/**
	 * Delete single record by ID
	 *
	 * @param $id
	 *
	 * @return bool|int|\mysqli_result|null
	 */
	public function delete( $id ) {
		return $this->wpdb()->delete( $this->table, array( 'ID' => $id ) );
	}
}
