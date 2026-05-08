<?php
/**
 * URFrontendListing Frontend.
 *
 * @class    Frontend
 * @version  5.0.0
 * @package  URTeamMembership/Frontend
 * @category Frontend
 * @author   WPEverest
 */

namespace WPEverest\URTeamMembership\Frontend;

use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URTeamMembership\Admin\TeamRepository;
use WPEverest\URTeamMembership\Services\TeamService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle_edit_team_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_loaded', array( $this, 'ur_add_team_tab_endpoint' ) );
		add_filter( 'user_registration_account_menu_items', array( $this, 'urm_team_tab' ), 10, 1 );
		add_action(
			'user_registration_account_urm-team_endpoint',
			array(
				$this,
				'user_registration_urm_team_tab_endpoint_content',
			)
		);
		add_filter(
			'user_registration_membership_add_team_data_if_exists',
			array(
				$this,
				'add_team_data_if_exists',
			),
			10,
			2
		);
		add_action( 'wp_ajax_user_registration_get_user_id_by_email', array( $this, 'get_user_id_by_email' ) );
	}

	public function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
		wp_register_script(
			'ur-team-membership',
			UR()->plugin_url() . '/assets/js/pro/frontend/user-registration-team-membership-frontend' . $suffix . '.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);

		wp_enqueue_script( 'ur-team-membership' );

		wp_localize_script(
			'ur-team-membership',
			'ur_team',
			array(
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'ur_team' ),
				'max_seats_reached' => __( 'Maximum seats reached.', 'user-registration' ),
			)
		);
	}

	/**
	 * Add Team  tab endpoint.
	 */
	public function ur_add_team_tab_endpoint() {
		$mask = Ur()->query->get_endpoints_mask();

		add_rewrite_endpoint( 'urm-team', $mask );
		flush_rewrite_rules();
	}

	/**
	 * Add the item to $items array.
	 *
	 * @param array $items Items.
	 */
	public function urm_team_tab( $items ) {
		$current_user_id = get_current_user_id();
		$user_source     = get_user_meta( $current_user_id, 'ur_registration_source', true );

		if ( 'membership' !== $user_source ) {
			return $items;
		}

		// Check if any membership is a team membership
		$membership_repositories = new MembersRepository();
		$memberships             = $membership_repositories->get_member_membership_by_id( $current_user_id );
		if ( empty( $memberships ) ) {
			return $items;
		}

		$is_team_membership = false;
		foreach ( $memberships as $membership ) {
			$is_team_membership = ur_check_if_member_is_team_leader( $membership['post_id'] );
		}

		if ( ! $is_team_membership ) {
			return $items;
		}

		$new_items             = array();
		$new_items['urm-team'] = __( 'Team', 'user-registration' );
		$items                 = array_merge( $items, $new_items );

		$mask = Ur()->query->get_endpoints_mask();
		add_rewrite_endpoint( 'urm-team', $mask );

		return $this->team_insert_after_helper( $items, $new_items, 'edit-profile' );
	}

	/**
	 * Payment insert after helper.
	 *
	 * @param mixed $items Items.
	 * @param mixed $new_items New items.
	 * @param mixed $after After item.
	 */
	public function team_insert_after_helper( $items, $new_items, $after ) {

		$position = array_search( $after, array_keys( $items ), true ) + 1;

		$return_items  = array_slice( $items, 0, $position, true );
		$return_items += $new_items;
		$return_items += array_slice( $items, $position, count( $items ) - $position, true );

		return $return_items;
	}

	/**
	 * Team tab content.
	 */
	public function user_registration_urm_team_tab_endpoint_content() {
		do_action( 'user_registration_before_team_tab_contents' );

		$layout  = get_option( 'user_registration_my_account_layout', 'vertical' );
		$action  = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$team_id = isset( $_GET['team_id'] ) ? intval( wp_unslash( $_GET['team_id'] ) ) : 0;

		if ( 'vertical' === $layout && isset( ur_get_account_menu_items()['urm-team'] ) ) {
			?>
			<div class="user-registration-MyAccount-content__header">
				<div class="user-registration-MyAccount-content__header-content">
					<?php if ( 'edit' === $action && $team_id > 0 ) { ?>
						<a class="urm-back-button" href="<?php echo esc_url( ur_get_account_endpoint_url( 'urm-team' ) ); ?>">
							<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
						</a>
						<h1><?php echo esc_html__( 'Edit Team', 'user-registration' ); ?></h1>
					<?php } else { ?>
						<h1><?php echo wp_kses_post( ur_get_account_menu_items()['urm-team'] ); ?></h1>
					<?php } ?>
				</div>
			</div>
			<?php
		}

		if ( 'edit' === $action && $team_id > 0 ) {
			$team_repository = new TeamRepository();
			$team            = $team_repository->get_single_team_by_ID( $team_id );
			$current_user_id = get_current_user_id();

			if ( $team && isset( $team['meta']['urm_team_leader_id'] ) && intval( $team['meta']['urm_team_leader_id'] ) === $current_user_id ) {
				ur_get_template( 'myaccount/edit-team.php', array( 'team' => $team ) );
				do_action( 'user_registration_after_team_tab_contents' );
				return;
			}
			esc_html_e( 'You do not have permission to edit this team.', 'user-registration' );
			return;
		} else {

			$current_page = 1;

			if ( isset( $_GET['paged'] ) && intval( $_GET['paged'] ) > 0 ) {
				$current_page = intval( $_GET['paged'] );
			} else {
				$request_path = trim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
				$segments     = explode( '/', $request_path );

				$page_index = array_search( 'page', $segments );
				if ( false !== $page_index && isset( $segments[ $page_index + 1 ] ) ) {
					$current_page = max( 1, intval( $segments[ $page_index + 1 ] ) );
				}
			}

			ur_get_template(
				'myaccount/team.php',
				array(
					'teams'        => $this->get_teams( $current_page, 10 ),
					'current_page' => $current_page,
				)
			);
		}
		do_action( 'user_registration_after_team_tab_contents' );
	}

	/**
	 * @param $args
	 *
	 * @return array
	 */
	private function get_teams( $page = 1, $per_page = 10 ) {
		$user_id     = get_current_user_id();
		$user_source = get_user_meta( $user_id, 'ur_registration_source', true );
		$total_items = array();

		if ( 'membership' === $user_source ) {
			$team_repository = new TeamRepository();
			$total_items     = $team_repository->get_member_all_teams( $user_id );
		}

		$total_count = ! empty( $total_items ) ? count( $total_items ) : 0;
		$page        = max( 1, intval( $page ) );
		$per_page    = max( 1, intval( $per_page ) );
		$offset      = ( $page - 1 ) * $per_page;
		$items       = ! empty( $total_items ) ? array_slice( $total_items, $offset, $per_page ) : array();

		return array(
			'items'       => $items,
			'total_items' => $total_count,
			'page'        => $page,
			'per_page'    => $per_page,
			'total_pages' => ( $per_page > 0 ) ? (int) ceil( $total_count / $per_page ) : 1,
		);
	}

	public function handle_edit_team_form() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( empty( $_POST['ur_edit_team_submit'] ) || empty( $_POST['ur_edit_team_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['ur_edit_team_nonce'], 'ur_edit_team_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'user-registration' ) );
		}

		$team_id         = absint( $_POST['team_id'] ?? 0 );
		$team_name       = sanitize_text_field( $_POST['team_name'] ?? '' );
		$invited_emails  = sanitize_text_field( $_POST['invited_member_emails'] ?? '' );
		$existing_emails = sanitize_text_field( $_POST['existing_member_emails'] ?? '' );
		$members_id      = sanitize_text_field( $_POST['members_id'] ?? '' );
		$max_seats       = absint( $_POST['max_seats'] ?? 0 );

		$team_name = trim( $team_name );
		if ( empty( $team_name ) ) {
			if ( function_exists( 'ur_add_notice' ) ) {
				ur_add_notice( __( 'Team name is required.', 'user-registration' ), 'error' );
			}
			self::redirect_back_to_form();
		}

		$existing_members = get_post_meta( $team_id, 'urm_member_emails', true ) ?? [];
		$existing_arr     = array_filter( array_map( 'trim', explode( ',', $existing_emails ) ) );
		$invited_arr      = array_filter( array_map( 'trim', explode( ',', $invited_emails ) ) );
		$all_mails        = array_merge( $existing_arr, $invited_arr );
		$removed_emails   = array_diff( $existing_members, $all_mails );
		$total            = count( $existing_arr ) + count( $invited_arr );
		if ( $total > $max_seats ) {
			if ( function_exists( 'ur_add_notice' ) ) {
				ur_add_notice( __( 'Maximum seats exceeded.', 'user-registration' ), 'error' );
			}
			self::redirect_back_to_form();
		}

		global $wpdb;

		//      if ( ! empty( $invited_arr ) ) {
		//          $membership_id = get_post_meta( $team_id, 'urm_membership_id', true );
		//
		//          foreach ( $invited_arr as $invited_email ) {
		//              $invited_email = trim( $invited_email );
		//              if ( empty( $invited_email ) ) {
		//                  continue;
		//              }
		//
		//              $user = get_user_by( 'email', $invited_email );
		//              if ( ! $user ) {
		//                  continue;
		//              }
		//
		//              $user_id = $user->ID;
		//
		//              $has_subscription = $wpdb->get_var(
		//                  $wpdb->prepare(
		//                      "SELECT COUNT(*) FROM {$wpdb->prefix}ur_membership_subscriptions
		//                      WHERE user_id = %d AND item_id = %d",
		//                      $user_id,
		//                      $membership_id
		//                  )
		//              );
		//
		//              if ( $has_subscription ) {
		//                  if ( function_exists( 'ur_add_notice' ) ) {
		//                      ur_add_notice( sprintf( __( '%s cannot be added.', 'user-registration' ), $invited_email ), 'error' );
		//                  }
		//                  self::redirect_back_to_form();
		//              }
		//
		//              // Check if user belongs to any team that has this same membership
		//              $user_team_ids           = get_user_meta( $user_id, 'urm_team_ids', true );
		//              $in_team_with_membership = false;
		//
		//              if ( ! empty( $user_team_ids ) && is_array( $user_team_ids ) ) {
		//                  foreach ( $user_team_ids as $user_team_id ) {
		//                      $user_team_id = absint( $user_team_id );
		//                      if ( ! $user_team_id || $team_id === $user_team_id ) {
		//                          continue;
		//                      }
		//
		//                      $team_membership_id = (int) get_post_meta( $user_team_id, 'urm_membership_id', true );
		//
		//                      if ( $team_membership_id === (int) $membership_id ) {
		//                          $in_team_with_membership = true;
		//                          break;
		//                      }
		//                  }
		//              }
		//
		//              if ( $in_team_with_membership ) {
		//                  if ( function_exists( 'ur_add_notice' ) ) {
		//                      ur_add_notice( sprintf( __( '%s cannot be added.', 'user-registration' ), $invited_email ), 'error' );
		//                  }
		//                  self::redirect_back_to_form();
		//              }
		//          }
		//      }

		if ( $team_id > 0 ) {
			$post_data = array(
				'ID'         => $team_id,
				'post_title' => $team_name,
			);
			wp_update_post( $post_data );

			update_post_meta( $team_id, 'urm_member_emails', array_merge( $existing_arr, $invited_arr ) );
			update_post_meta( $team_id, 'urm_used_seats', $total );

			$member_ids = array();
			
			if ( ! empty( $members_id ) ) {
				$member_ids = array_filter( array_map( 'trim', explode( ',', $members_id ) ) );
				$member_ids = array_map( 'absint', $member_ids );
				$member_ids = array_filter( $member_ids ); // Remove empty/invalid
				$member_ids = array_values( array_unique( $member_ids ) ); // Remove duplicates and reindex
			}

			$all_emails = array_merge( $existing_arr, $invited_arr );
			foreach ( $all_emails as $email ) {
				$user = get_user_by( 'email', $email );
				if ( $user && ! in_array( $user->ID, $member_ids, true ) ) {
					$member_ids[] = $user->ID;
				}
			}

			update_post_meta( $team_id, 'urm_member_ids', $member_ids );
		}

		foreach ( $invited_arr as $email ) {
			$user         = get_user_by( 'email', $email );
			$team_service = new TeamService();
			$team_service->update_team_user_meta_and_notify( $user, $team_id, $team_name, $email );
			
			$user_after = get_user_by( 'email', $email );
			if ( $user_after && $team_id > 0 ) {
				$current_member_ids = get_post_meta( $team_id, 'urm_member_ids', true );
				if ( ! is_array( $current_member_ids ) ) {
					$current_member_ids = array();
				}
				if ( ! in_array( $user_after->ID, $current_member_ids, true ) ) {
					$current_member_ids[] = $user_after->ID;
					$current_member_ids = array_map( 'absint', $current_member_ids );
					$current_member_ids = array_values( array_unique( $current_member_ids ) );
					update_post_meta( $team_id, 'urm_member_ids', $current_member_ids );
				}
			}
		}

		if ( ! empty( $removed_emails ) ) {
			foreach ( $removed_emails as $email ) {
				$user = get_user_by( 'email', $email );
				if ( ! $user ) {
					continue;
				}
				$team_service = new TeamService();
				$team_service->update_urm_team_ids( $user->ID, $team_id, true );
			}
		}

		wp_safe_redirect( ur_get_account_endpoint_url( 'urm-team' ) );
		exit;
	}


	/**
	 * Redirect back to team edit form
	 */
	private static function redirect_back_to_form() {
		wp_safe_redirect( $_SERVER['REQUEST_URI'] );
		exit;
	}

	public function add_team_data_if_exists( $data, $last_order ) {
		if ( empty( $last_order['ID'] ) ) {
			return $data;
		}

		$order_id = (int) $last_order['ID'];

		$order_repository = new OrdersRepository();
		$order_meta_data  = $order_repository->get_order_meta_by_order_id_and_meta_key( $order_id, 'urm_team_id' );
		$team_id          = ! empty( $order_meta_data['meta_value'] ) ? $order_meta_data['meta_value'] : '';

		if ( $team_id ) {
			$team_repository = new TeamRepository();
			$data['team']    = $team_repository->get_single_team_by_ID( $team_id );
		}

		return $data;
	}

	/**
	 * AJAX handler to get user ID by email
	 */
	public function get_user_id_by_email() {
		check_ajax_referer( 'resend_team_invite', 'nonce' );

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid email address.', 'user-registration' ) ) );
		}

		$user = get_user_by( 'email', $email );

		if ( $user ) {
			wp_send_json_success( array( 'user_id' => $user->ID ) );
		} else {
			wp_send_json_success( array( 'user_id' => null ) );
		}
	}
}
