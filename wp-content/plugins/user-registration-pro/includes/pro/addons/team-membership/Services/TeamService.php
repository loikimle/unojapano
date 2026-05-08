<?php

namespace WPEverest\URTeamMembership\Services;

use UR_Team_Invite_Confirmation;
use WP_Error;
use WPEverest\URTeamMembership\Admin\TeamRepository;

class TeamService {
	private $team_repository;

	public function __construct() {
		$this->team_repository = new TeamRepository();
	}

	/**
	 * Retrieves all available users.
	 *
	 * This function fetches all users who are not registered in the same membership from the membership team repository.
	 *
	 * @return array list of available users.
	 */
	public function list_available_users( $team_id ) {
		$users = $this->team_repository->get_users_not_in_same_membership_plan( $team_id );
		return $users;
	}

	/**
	 * Retrieve team Details
	 *
	 * @param int $team_id
	 *
	 * @return array
	 */
	public function get_team_details( $team_id ) {
		$team = $this->team_repository->get_single_team_by_ID( $team_id );
		return $team;
	}

	/**
	 * Get sanitized emails for a list of member IDs.
	 *
	 * @param array $member_ids array of IDs
	 *
	 * @return array array of user emails.
	 */
	public function get_member_emails( $member_ids ) {
		if ( empty( $member_ids ) ) {
			return array();
		}

		$member_ids = array_map( 'absint', $member_ids );

		$emails = array();

		foreach ( $member_ids as $id ) {
			if ( empty( $id ) ) {
				continue;
			}
			$user = get_userdata( $id );
			if ( $user && ! empty( $user->user_email ) ) {
				$sanitized = sanitize_email( $user->user_email );
				if ( is_email( $sanitized ) ) {
					$emails[] = $sanitized;
				}
			}
		}

		return $emails;
	}

	/**
	 * Prepare team post data by validating and sanitizing it.
	 *
	 * This function validates the team data and sanitizes post meta data.
	 *
	 * @param array $data The data required to update a team post.
	 *
	 * @return array The prepared team post data.
	 */
	public function prepare_team_post_data(
		$data
	) {
		$validation = $this->validate_team_data( $data );

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$team_id             = ! empty( $data['id'] ) ? absint( $data['id'] ) : 0;
		$data['name']        = ! empty( $data['name'] ) ? sanitize_text_field( wp_unslash( $data['name'] ) ) : '';
		$data['team_leader'] = ! empty( $data['team_leader'] ) ? sanitize_email( $data['team_leader'] ) : '';
		$members             = ! empty( $data['members'] ) ? (array) $data['members'] : array();
		$data['members']     = array_map( 'sanitize_email', $members );
		$team_leader_id      = get_user_by( 'email', $data['team_leader'] )->ID;
		$new_emails          = array_map( 'sanitize_email', $data['members'] );
		$new_emails          = array_filter( $new_emails ); // remove empty/invalid
		$new_emails          = array_unique( $new_emails );

		$existing_emails = get_post_meta( $team_id, 'urm_member_emails', true );
		if ( ! is_array( $existing_emails ) ) {
			$existing_emails = array();
		}

		$existing_emails = array_map( 'sanitize_email', $existing_emails );
		$existing_emails = array_filter( $existing_emails );

		$invited_emails = array_values(
			array_diff( $new_emails, $existing_emails )
		);

		$removed_emails = array_diff( $existing_emails, $new_emails );

		$team_seats = ! empty( $data['team_seats'] ) ? absint( $data['team_seats'] ) : 0;
		$used_seats = count( $data['members'] );

		if ( $team_seats > 0 && $team_seats < $used_seats ) {
			return new WP_Error(
				'invalid_team_seats',
				sprintf(
					__( 'Team seats cannot be reduced below %d (currently occupied seats).', 'user-registration' ),
					$used_seats
				)
			);
		}

		$member_ids = array();
		if ( ! empty( $data['members_id'] ) && is_array( $data['members_id'] ) ) {
			$member_ids = array_map( 'absint', $data['members_id'] );
			$member_ids = array_filter( $member_ids ); // Remove empty/invalid
			$member_ids = array_values( array_unique( $member_ids ) ); // Remove duplicates and reindex
		} else {
			foreach ( $data['members'] as $email ) {
				$user = get_user_by( 'email', $email );
				if ( $user ) {
					$member_ids[] = $user->ID;
				}
			}
		}

		return array(
			'team_data'      => array(
				'ID'             => $team_id,
				'post_title'     => $data['name'],
				'post_type'      => 'ur_membership_team',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			'team_meta_data' => array(
				'urm_team_leader_id' => $team_leader_id,
				'urm_member_emails'  => $data['members'],
				'urm_member_ids'     => $member_ids,
				'urm_used_seats'     => count( $data['members'] ),
				'urm_team_seats'     => $team_seats > 0 ? $team_seats : ( ! empty( $team_id ) ? absint( get_post_meta( $team_id, 'urm_team_seats', true ) ) : 0 ),
			),
			'invited_emails' => $invited_emails,
			'removed_emails' => $removed_emails,
		);
	}

	private function validate_team_data( $data ) {
		$members        = ! empty( $data['members'] ) ? array_map( 'sanitize_email', (array) $data['members'] ) : array();
		$max_team_seats = ! empty( $data['team_seats'] )
			? absint( $data['team_seats'] )
			: ( ! empty( $data['id'] )
				? absint( get_post_meta( absint( $data['id'] ), 'urm_team_seats', true ) )
				: 0 );

		$team_leader = isset( $data['team_leader'] ) ? sanitize_email( $data['team_leader'] ) : '';

		if ( $max_team_seats > 0 && count( $members ) > $max_team_seats ) {
			return new WP_Error(
				'max_seats_exceeded',
				__( 'Maximum team seats exceeded.', 'user-registration' )
			);
		}

		// Team leader must be in members list
		if ( $team_leader && ! in_array( $team_leader, $members, true ) ) {
			return new WP_Error(
				'leader_not_in_members',
				__( 'Team leader must be part of the team members.', 'user-registration' )
			);
		}

		return true;
	}

	/**
	 * Delete team
	 *
	 * @param int $team_id
	 *
	 * @return array
	 */
	public function delete_team( $team_id ) {
		$response = array(
			'status' => true,
		);

		$this->team_repository->cancel_subscription( $team_id );
		$this->team_repository->delete( $team_id );

		return $response;
	}

	public function update_team_user_meta_and_notify( $user, $team_id, $team_name, $email ) {
		if ( $user ) {
			$this->update_urm_team_ids( $user->ID, $team_id );
			$this->send_team_registered_email( $user, $team_name );
		} else {
			//create user and send the email regarding registration
			$random_password = wp_generate_password();
			$username        = sanitize_user( current( explode( '@', $email ) ) );
			$user_id         = wp_create_user( $username, $random_password, $email );
			if ( $user_id ) {
				$this->update_urm_team_ids( $user_id, $team_id );

				// Get team leader's registration source and form ID
				$leader_id = get_post_meta( $team_id, 'urm_team_leader_id', true );
				if ( ! empty( $leader_id ) ) {
					$leader_registration_source = get_user_meta( $leader_id, 'ur_registration_source', true );
					$leader_form_id             = get_user_meta( $leader_id, 'ur_form_id', true );
					$membership_id              = get_post_meta( $team_id, 'urm_membership_id', true );
					$membership                 = get_post( $membership_id );
					if ( $membership ) {
						$membership_details = get_post_meta( $membership->id, 'ur_membership', true );
						if ( ! empty( $membership_details['role'] ) ) {
							$role    = $membership_details['role'];
							$wp_user = new \WP_User( $user_id );
							// Add role only if the user does not already have it
							if ( ! in_array( $role, (array) $wp_user->roles, true ) ) {
								$wp_user->add_role( $role );
							}
						}
					}
					// Save team leader's registration source and form ID to new user
					if ( ! empty( $leader_registration_source ) ) {
						update_user_meta( $user_id, 'ur_registration_source', $leader_registration_source );
					}
					if ( ! empty( $leader_form_id ) ) {
						update_user_meta( $user_id, 'ur_form_id', $leader_form_id );
					}
				}

				//send email verification email
				// update_user_meta( $user_id, 'ur_login_option', 'email_confirmation' );

				// $confirm_email = new UR_Email_Confirmation();
				// $token         = $confirm_email->get_token( $user_id );
				// update_user_meta( $user_id, 'ur_confirm_email', '0' );
				// update_user_meta( $user_id, 'ur_confirm_email_token', $token );

				// $attachments = apply_filters( 'user_registration_email_attachment_resending_token', array() );
				// $name_value  = ur_get_user_extra_fields( $user_id );
				// // Get selected email template id for specific form.
				// $form_id     = ur_get_form_id_by_userid( $user_id );
				// $template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );
				// UR_Emailer::send_mail_to_user( $email, $username, $user_id, '', $name_value, $attachments, $template_id );

				//send team member invitation email
				$user = get_user_by( 'email', $email );
				$this->send_team_member_reset_password_email( $user, $random_password );
				$this->send_team_registered_email( $user, $team_name );

			}
		}
	}

	public function update_urm_team_ids( $user_id, $team_id, $remove = false ) {
		$team_ids = get_user_meta( $user_id, 'urm_team_ids', true );

		if ( ! is_array( $team_ids ) ) {
			$team_ids = empty( $team_ids ) ? array() : array( $team_ids );
		}

		if ( $remove ) {
			$team_ids = array_values(
				array_diff( $team_ids, [ (int) $team_id ] )
			);

			if ( empty( $team_ids ) ) {
				delete_user_meta( $user_id, 'urm_team_ids' );
			} else {
				update_user_meta( $user_id, 'urm_team_ids', $team_ids );
			}
		} elseif ( ! in_array( $team_id, $team_ids, true ) ) {
				$team_ids[] = $team_id;
				update_user_meta( $user_id, 'urm_team_ids', $team_ids );
		}
	}

	public function send_team_registered_email( $user, $team_name ) {
		$email_service = new EmailService();

		$data = array(
			'member'    => $user->data,
			'member_id' => $user->ID,
			'team_name' => $team_name,
		);
		$email_service->send_email( $data, 'team_registered' );
	}

	public function send_team_member_reset_password_email( $user, $password ) {
		$email_service = new EmailService();
		$key           = get_password_reset_key( $user );

		$data = array(
			'member'    => $user->data,
			'member_id' => $user->ID,
			'key'       => $key,
			'password'  => $password,
		);
		$email_service->send_email( $data, 'team_member_reset_password' );
	}

	public function get_team_tags( $data ) {
		return array(
			'username'  => esc_html( ucwords( isset( $data['member']->display_name ) ? $data['member']->display_name : '' ) ),
			'team_name' => esc_html( ucwords( isset( $data['team_name'] ) ? $data['team_name'] : '' ) ),
			'key'       => ! empty( $data['key'] ) ? $data['key'] : '',
			'password'  => ! empty( $data['password'] ) ? $data['password'] : '',
		);
	}
}
