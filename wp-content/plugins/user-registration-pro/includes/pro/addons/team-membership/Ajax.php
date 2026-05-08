<?php
/**
 * URTeamMembership AJAX
 *
 * AJAX Event Handler
 *
 * @class    AJAX
 * @version  1.0.0
 * @package  URTeamMembership/Ajax
 * @category Class
 * @author   WPEverest
 */

namespace WPEverest\URTeamMembership;

use UR_Email_Confirmation;
use UR_Emailer;
use UR_Team_Invite_Confirmation;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URTeamMembership\Admin\TeamRepository;
use WPEverest\URTeamMembership\Services\TeamService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Class
 */
class AJAX {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {

		$ajax_events = array(
			'update_team' => false,
			'delete_team' => false,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {

			add_action( 'wp_ajax_user_registration_team_membership_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {

				add_action(
					'wp_ajax_nopriv_user_registration_team_membership_' . $ajax_event,
					array(
						__CLASS__,
						$ajax_event,
					)
				);
			}
		}
	}

		/**
	 * Update membership team from backend
	 *
	 * @return void
	 */
	public static function update_team() {
		if ( empty( $_POST['team_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field team_id is required.', 'user-registration' ),
				)
			);
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, You do not have permission to edit team.', 'user-registration' ),
				)
			);
		}
		ur_membership_verify_nonce( 'ur_team' );

		$team = new TeamService();
		$data = isset( $_POST['team_data'] ) ? (array) json_decode( wp_unslash( $_POST['team_data'] ), true ) : array();

		$data = $team->prepare_team_post_data( $data );

		if ( is_wp_error( $data ) ) {
			wp_send_json_error(
				array(
					'message' => $data->get_error_message(),
				)
			);
			return;
		}

		$data = apply_filters( 'ur_membership_after_update_team_data_prepare', $data );

		$updated_ID = wp_insert_post( $data['team_data'] );

		if ( $updated_ID ) {
			if ( ! empty( $data['team_meta_data'] ) ) {
				//update team post meta
				foreach ( $data['team_meta_data'] as $meta_key => $meta_value ) {
					update_post_meta( $updated_ID, $meta_key, $meta_value );
				}

				//update the current leader id in user_id in subscription table
				$subscription_id         = get_post_meta( $updated_ID, 'urm_subscription_id', true );
				$subscription_repository = new SubscriptionRepository();
				$subscription_repository->update(
					$subscription_id,
					array(
						'user_id' => $data['team_meta_data']['urm_team_leader_id'],
					)
				);
			}

			//send team registration email to the invited members
			$invited_user_ids = array();
			if ( ! empty( $data['invited_emails'] ) ) {
				$team_service = new TeamService();
				foreach ( $data['invited_emails'] as $email ) {
					$user = get_user_by( 'email', $email );
					$team_service->update_team_user_meta_and_notify( $user, $data['team_data']['ID'], $data['team_data']['post_title'], $email );

					if ( $user ) {
						$invited_user_ids[] = $user->ID;
					} else {
						$created_user = get_user_by( 'email', $email );
						if ( $created_user ) {
							$invited_user_ids[] = $created_user->ID;
						}
					}
				}
			}

			if ( ! empty( $invited_user_ids ) ) {
				$existing_member_ids = get_post_meta( $updated_ID, 'urm_member_ids', true );
				if ( ! is_array( $existing_member_ids ) ) {
					$existing_member_ids = array();
				}
				$all_member_ids = array_merge( $existing_member_ids, $invited_user_ids );
				$all_member_ids = array_map( 'absint', $all_member_ids );
				$all_member_ids = array_values( array_unique( $all_member_ids ) );
				update_post_meta( $updated_ID, 'urm_member_ids', $all_member_ids );
			}

			if ( ! empty( $data['removed_emails'] ) ) {
				foreach ( $data['removed_emails'] as $email ) {
					$user = get_user_by( 'email', $email );
					if ( ! $user ) {
						continue;
					}
					$team_service = new TeamService();
					$team_service->update_urm_team_ids( $user->ID, $updated_ID, true );
				}
			}

			$response = array(
				'team_id' => $updated_ID,
				'message' => esc_html__( 'Successfully updated the team data.', 'user-registration' ),
			);

			$response = apply_filters( 'ur_membership_before_create_team_response', $response );
			wp_send_json_success( $response );
		} else {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Sorry! There was an unexpected error while updating the team data . ', 'user-registration' ),
				)
			);
		}
	}

	/**
	 * Delete team
	 *
	 * @return void
	 */
	public static function delete_team() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Permission not allowed.', 'user-registration' ),
				),
				403
			);
		}

		ur_membership_verify_nonce( 'ur_team' );
		if ( empty( $_POST['team_id'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field team_id is required.', 'user-registration' ),
				),
				422
			);
		}
		$team_id = absint( $_POST['team_id'] );

		$team_service = new TeamService();
		$deleted      = $team_service->delete_team( $team_id );
		if ( $deleted['status'] ) {
			wp_send_json_success(
				array(
					'message' => esc_html__( 'Team deleted successfully.', 'user-registration' ),
				)
			);
		}
		wp_send_json_error(
			array(
				'message' => $deleted['message'],
			)
		);
	}
}
