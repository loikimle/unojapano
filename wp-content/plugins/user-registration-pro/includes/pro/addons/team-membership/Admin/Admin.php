<?php
/**
 * URFrontendListing Admin.
 *
 * @class    Admin
 * @version  1.0.0
 * @package  URTeamMembership/Admin
 * @category Admin
 * @author   WPEverest
 */

namespace WPEverest\URTeamMembership\Admin;

use WPEverest\URTeamMembership\Services\TeamService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {
	/**
	 * Current page.
	 *
	 * @var string
	 */
	protected $page = null;

	/**
	 * Constructor for the class.
	 *
	 * Sets the page property and registers various hooks.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->page = 'user-registration-team';
		add_action( 'in_admin_header', array( __CLASS__, 'hide_unrelated_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'user_registration_single_user_details_content', array( $this, 'render_team_details' ), 11, 2 );
	}

	/**
	 * Remove Notices.
	 */
	public static function hide_unrelated_notices() {
		global $wp_filter;

		// Return on other than access rule creator page.
		if ( empty( $_REQUEST['page'] ) || 'user-registration-team' !== $_REQUEST['page'] ) {
			return;
		}

		foreach ( array( 'user_admin_notices', 'admin_notices', 'all_admin_notices' ) as $wp_notice ) {
			if ( ! empty( $wp_filter[ $wp_notice ]->callbacks ) && is_array( $wp_filter[ $wp_notice ]->callbacks ) ) {
				foreach ( $wp_filter[ $wp_notice ]->callbacks as $priority => $hooks ) {
					foreach ( $hooks as $name => $arr ) {
						// Remove all notices except user registration plugins notices.
						if ( ! strstr( $name, 'user_registration_' ) ) {
							unset( $wp_filter[ $wp_notice ]->callbacks[ $priority ][ $name ] );
						}
					}
				}
			}
		}
	}

	public function add_teams_menu() {
		add_submenu_page(
			'user-registration',
			__( 'Teams', 'user-registration' ),
			__( 'Teams', 'user-registration' ),
			'manage_options',
			'user-registration-team',
			[ $this, 'render_team_page' ]
		);
	}

	/**
	 * Enqueue styles
	 *
	 */
	public function enqueue_styles() {
		if ( empty( $_GET['page'] ) || 'user-registration-team' !== $_GET['page'] ) {
			return;
		}
		wp_register_style( 'ur-snackbar', UR()->plugin_url() . '/assets/css/ur-snackbar/ur-snackbar.css', array(), '1.0.0' );
		wp_register_style( 'ur-core-builder-style', UR()->plugin_url() . '/assets/css/admin.css', array(), UR_VERSION );
		wp_register_style( 'ur-membership-admin-style', UR_MEMBERSHIP_CSS_ASSETS_URL . '/user-registration-membership-admin.css', array(), UR_VERSION );
		wp_enqueue_style( 'ur-membership-admin-style' );
		wp_enqueue_style( 'user-registration-pro-admin-style' );
		wp_enqueue_style( 'ur-core-builder-style' );
		wp_enqueue_style( 'sweetalert2' );
		wp_enqueue_style( 'ur-snackbar' );
		wp_enqueue_style( 'select2', UR()->plugin_url() . '/assets/css/select2/select2.css', array(), '4.0.6' );
	}

	/**
	 * Enqueue scripts
	 *
	 */
	public function enqueue_scripts() {
		if ( empty( $_GET['page'] ) || 'user-registration-team' !== $_GET['page'] ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
		wp_register_script( 'ur-snackbar', UR()->plugin_url() . '/assets/js/ur-snackbar/ur-snackbar' . $suffix . '.js', array(), '1.0.0', true );
		wp_enqueue_script( 'ur-snackbar' );
		wp_register_script( 'user-registration-team-membership', UR()->plugin_url() . '/assets/js/pro/admin/user-registration-team-membership' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'user-registration-team-membership' );
		wp_register_script( 'selectWoo', UR()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '5.0.0', false );
		wp_enqueue_script( 'selectWoo' );
		wp_enqueue_script( 'sweetalert2' );
		$this->localize_scripts();
	}

	/**
	 * Localizes the scripts for the user registration membership plugin.
	 *
	 * Localizes scripts for user registration membership plugin.
	 *
	 * @return void
	 */
	public function localize_scripts() {
		$team_id = ! empty( $_GET['post_id'] ) ? $_GET['post_id'] : null;

		wp_localize_script(
			'user-registration-team-membership',
			'ur_team_localized_data',
			array(
				'_nonce'      => wp_create_nonce( 'ur_team' ),
				'team_id'     => $team_id,
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'labels'      => $this->get_i18_labels(),
				'team_url'    => admin_url( 'admin.php?page=user-registration-team' ),
				'delete_icon' => plugins_url( 'assets/images/users/delete-user-red.svg', UR_PLUGIN_FILE ),
			)
		);
	}

	/**
	 * Get i18 labels.
	 *
	 * @return array
	 */
	public function get_i18_labels() {
		return array(
			'network_error'                => esc_html__( 'Network error', 'user-registration' ),
			'i18n_error'                   => __( 'Error', 'user-registration' ),
			'i18n_field_is_required'       => _x( 'field is required.', 'user-registration' ),
			'i18n_max_seats_exceeded'      => __( 'Maximum team members reached.', 'user-registration' ),
			'i18n_group_leader_removal'    => __( 'Group leader cannot be removed from members.', 'user-registration' ),
			'i18n_prompt_title'            => __( 'Delete Team', 'user-registration' ),
			'i18n_prompt_bulk_subtitle'    => __( 'Are you sure you want to delete these teams permanently?', 'user-registration' ),
			'i18n_prompt_single_subtitle'  => __( 'Are you sure you want to delete this team permanently?', 'user-registration' ),
			'i18n_prompt_delete'           => __( 'Delete', 'user-registration' ),
			'i18n_prompt_cancel'           => __( 'Cancel', 'user-registration' ),
			'i18n_prompt_no_team_selected' => __( 'Please select at least one team.', 'user-registration' ),
		);
	}

	/**
	 * Render Membership Team Page or Edit Page
	 *
	 * @return void
	 */
	public function render_team_page() {
		$action_page = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		$post_id     = isset( $_GET['post_id'] ) ? sanitize_text_field( $_GET['post_id'] ) : '';
		if ( empty( $action_page ) ) {
			$membership_table_list = new ListTable();
			require __DIR__ . '/../Views/header.php';
			$membership_table_list->display_page();
			return;
		} else {
			$this->render_team_edit_page( $post_id );
		}
	}

	/**
	 * Render Membership Team Edit Page
	 *
	 * @param $team_id
	 *
	 * @return void
	 */
	public function render_team_edit_page( $team_id ) {
		$team_service = new TeamService();
		$users        = get_users();
		$team         = $team_service->get_team_details( $team_id );
		include __DIR__ . '/../Views/membership-team-edit.php';
	}

	public function render_team_details( $user_id, $form_id ) {
		if ( ur_check_module_activation( 'team' ) === false ) {
			return;
		}

		$teams = get_user_meta( $user_id, 'urm_team_ids', true );

		if ( empty( $teams ) ) {
			return;
		}

		ob_start();
		?>
		<div class="urm-admin-user-content-container">
			<div id="urm-admin-user-content-header" >
				<h3>
					<?php
					if ( count( $teams ) > 1 ) {
						esc_html_e( 'Team Details', 'user-registration' );
					} else {
						esc_html_e( 'Team Detail', 'user-registration' );
					}
					?>
				</h3>
			</div>
			<div class="user-registration-user-form-details">
				<table class="wp-list-table widefat fixed striped users">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Team Name', 'user-registration' ); ?></th>
							<th><?php esc_html_e( 'Membership', 'user-registration' ); ?></th>
							<th><?php esc_html_e( 'Team Leader', 'user-registration' ); ?></th>
							<th><?php esc_html_e( 'Action', 'user-registration' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $teams as $team_id ) {
							$team_repository = new TeamRepository();
							$team            = $team_repository->get_single_team_by_ID( $team_id );
							if ( ! $team ) {
								break;
							}
							?>
							<tr>
								<td><?php echo esc_html( $team['team_name'] ); ?></td>
								<td><?php echo esc_html( $team['membership'] ? $team['membership']['post_title'] : '-' ); ?></td>
								<td><?php echo esc_html( $team['team_leader'] ? $team['team_leader']['display_name'] : '-' ); ?></td>
								<td>
									<a href="<?php echo esc_url( admin_url( "admin.php?post_id={$team_id}&action=edit&page=user-registration-team" ) ); ?>">
										<?php esc_html_e( 'View', 'user-registration' ); ?>
									</a>
								</td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<?php

		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
