<?php
/**
 * Reports Module
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/classes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace LdGroupRegistration\Modules\Classes;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Ld_Group_Registration_Reports' ) ) {
	/**
	 * Class LD Group Registration Reports
	 */
	class Ld_Group_Registration_Reports {

		/**
		 * Get reports data for a group
		 *
		 * @param int $group_id     ID of the group.
		 */
		public static function get_group_report( $group_id ) {
			// Register and enqueue scripts.
			wp_enqueue_style( 'dashicons' );

			if ( ! wp_style_is( 'wdm_datatable_css', 'enqueued' ) ) {
				wp_enqueue_style(
					'wdm_datatable_css',
					plugins_url(
						'css/datatables.min.css',
						dirname( __FILE__ )
					),
					array(),
					LD_GROUP_REGISTRATION_VERSION
				);
			}

			wp_enqueue_script(
				'wdm-ldgr-group-report-js',
				plugins_url( 'js/wdm-ldgr-group-report.js', dirname( __FILE__ ) ),
				array( 'jquery-ui-core', 'wdm_datatable_js' ),
				LD_GROUP_REGISTRATION_VERSION
			);

			wp_localize_script(
				'wdm-ldgr-group-report-js',
				'ajax_object',
				array(
					'ajax_url'            => admin_url( 'admin-ajax.php' ),
					'group_id'            => $group_id,
					'course_not_selected' => sprintf(
						// translators: Course label.
						__( 'Please select a %s', 'wdm_ld_group' ),
						\LearnDash_Custom_Label::get_label( 'course' )
					),
				)
			);

			wp_enqueue_style(
				'wdm_ldgr_report_css',
				plugins_url(
					'css/wdm_ldgr_report_css.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);

			// include WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-group-report.template.php';

			/**
			 * Replaced `include` with `ldgr_get_template`
			 *
			 * @since 4.1.2
			 */
			ldgr_get_template(
				WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-group-report.template.php',
				array(
					'group_id'	=>	$group_id
				)
			);
		}

		/**
		 * Create Report Table
		 */
		public function create_report_table_callback() {

			if ( is_user_logged_in() ) {
				if ( is_group_leader( get_current_user_id() ) || learndash_is_group_leader_user( get_current_user_id() ) || current_user_can('manage_options')) {
					$admin_group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
					$course_id       = filter_input( INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT );
					$group_id        = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );

					// check if the course has a certificate associated with it.
					$rewards        = false;
					$certificate_id = learndash_get_setting( $course_id, 'certificate' );
					if ( ! empty( $certificate_id ) && 0 !== $certificate_id ) {
						$rewards = true;
					}
					$rewards = apply_filters( 'wdm_ldgr_report_show_rewards_column', $rewards, $course_id, $group_id );
					if ( ! in_array( $group_id, $admin_group_ids ) ) {
						echo json_encode(
							array(
								'group_id' => $group_id,
								'error'    => __(
									'You are not the owner of this group',
									'wdm_ld_group'
								),
							)
						);
						die();
					}

					// ob_start();
					// include WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-group-report-table.template.php';
					// $table = ob_get_contents();
					// ob_end_clean();

					/**
					 * Replaced `include` with `ldgr_get_template`
					 * 
					 * @since 4.1.2
					 */
					$table = ldgr_get_template(
						WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-group-report-table.template.php',
						array(
							'course_id'	=>	$course_id,
							'group_id'	=>	$group_id,
							'rewards'	=>	$rewards
						),
						1
					);

					$table = preg_replace( "/\r|\n/", '', $table );

					/**
					 * Filter group report generated data.
					 * 
					 * @since 4.1.2
					 * 
					 * @var array	$report_data	Group report data.
					 * @var int		$course_id		ID of the Learndash Course.
					 * @var int		$group_id		ID of the Learndash Group.
					 */
					$report_data = apply_filters(
						'ldgr_filter_group_report_data',
						array(
							'table'   => $table,
							'rewards' => $rewards,
						),
						$course_id,
						$group_id
					);

					echo json_encode( $report_data );
					die();

				} else {
					echo json_encode( array( 'error' => __( "You don't have privilege to do this action", 'wdm_ld_group' ) ) );
					die();
				}
			}
		}

		/**
		 * Display group report
		 */
		public function display_ldgr_group_report_callback() {
			// temporary.
			$empty_data_set = array(
				'recordsTotal'    => 0,
				'recordsFiltered' => 0,
				'data'            => [],
			);

			// Is group and course id set?
			if ( ! isset( $_POST['course_id'] ) || ! isset( $_POST['group_id'] ) ) {
				echo json_encode( $empty_data_set );
				wp_die();
			}

			// Check if user logged in.
			if ( ! is_user_logged_in() ) {
				echo json_encode( $empty_data_set );
				wp_die();
			}

			// Check if group leader.
			if ( is_group_leader( get_current_user_id() ) || learndash_is_group_leader_user( get_current_user_id() ) || current_user_can('manage_options')) {
				$admin_group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
				$course_id       = filter_input( INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT );
				$group_id        = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
				$show_rewards    = filter_input( INPUT_POST, 'show_rewards' );
				$show_rewards    = filter_var( $show_rewards, FILTER_VALIDATE_BOOLEAN );

				// Check if user is admin of current group.
				if ( ! in_array( $group_id, $admin_group_ids ) ) {
					echo json_encode( $empty_data_set );
					wp_die();
				}

				// get the limit data parameters.
				$limit  = 10;
				$offset = 0;
				$offset = filter_input( INPUT_POST, 'start', FILTER_SANITIZE_NUMBER_INT );
				$limit  = filter_input( INPUT_POST, 'length', FILTER_SANITIZE_NUMBER_INT );

				// get the users according to limit.
				$group_users_objects = $this->get_next_group_users( $group_id, $limit, $offset );

				if ( empty( $group_users_objects ) ) {
					echo json_encode( $empty_data_set );
					wp_die();
				}

				$data = array();

				foreach ( $group_users_objects['data'] as $key => $group_users_object ) {
					// Full name of the user.
					$user_id              = $group_users_object->ID;
					$userdata             = get_userdata( $user_id );
					$data[ $key ]['name'] = $userdata->first_name . ' ' . $userdata->last_name;
					// $data[$key]['last_name'] = $userdata->last_name;

					// Email.
					$data[ $key ]['email_id'] = $group_users_object->user_email;
					$course_progress_attr     = array(
						'course_id' => $course_id,
						'user_id'   => $user_id,
						'array'     => true,
					);

					// Course Progress.
					$course_progress                 = learndash_course_progress( $course_progress_attr );
					$data[ $key ]['course_progress'] = $course_progress['percentage'] . '%';
					// $data[$key]['course_status'] = learndash_course_status($course_id, $user_id, false);

					// Rewards.
					if ( $show_rewards ) {
						// $data[$key]['action'] = '';
						$certificate_link = learndash_get_course_certificate_link( $course_id, $user_id );
						if ( '' == $certificate_link ) {
							$reward = '-';
						} else {
							$reward = '<a href="' . $certificate_link . '" class="wdm-prnt-cf button" title="' . __( 'certificate', 'wdm_ld_group' ) . '" target="_blank"></a>';
							$reward = apply_filters( 'wdm_ldgr_report_add_rewards', $reward, $user_id, $course_id, $group_id );
							// $reward .= do_action('wdm_ldgr_report_add_rewards', $user_id, $course_id, $group_id);
						}
						$data[ $key ]['reward'] = $reward;
					}

					// Course Report.
					$data[ $key ]['course_report'] = $this->get_detailed_course_report( $course_id, $user_id );
				}

				echo json_encode(
					array(
						'recordsTotal'    => $group_users_objects['recordsTotal'],
						'recordsFiltered' => $group_users_objects['recordsTotal'],
						'data'            => $data,
					)
				);
				wp_die();
			}
		}

		/**
		 * Get next group users based on limit and offset
		 *
		 * @param int $group_id     ID of the group.
		 * @param int $limit        Number of group users to fetch.
		 * @param int $offset       Offset from where to fetch.
		 * @return array
		 */
		public function get_next_group_users( $group_id, $limit, $offset ) {
			$group_users_object = array();

			if ( empty( $group_id ) ) {
				return $group_users_object;
			}

			$user_query_args = array(
				// 'exclude'     =>  $group_leader_user_ids,
				'number'      => intval( $limit ),
				'offset'      => intval( $offset ),
				'orderby'     => 'display_name',
				'order'       => 'ASC',
				'count_total' => true,
				'fields'      => array( 'ID', 'user_email', 'display_name' ),
				'meta_query'  => array(
					array(
						'key'     => 'learndash_group_users_' . intval( $group_id ),
						'compare' => 'EXISTS',
					),
				),
			);

			$user_query = new \WP_User_Query( $user_query_args );

			if ( isset( $user_query->results ) ) {
				$group_users_objects['data']         = $user_query->results;
				$group_users_objects['recordsTotal'] = $user_query->total_users;
				// $group_users_objects['recordsFiltered'] = $user_query->total_users;
			}

			return $group_users_objects;
		}

		/**
		 * Get detailed course report
		 *
		 * @param int $course_id    ID of the course.
		 * @param int $user_id      ID of the user.
		 *
		 * @return string           Detailed course report in HTML.
		 */
		public function get_detailed_course_report( $course_id, $user_id ) {
			$course_report = '';
			if ( empty( $course_id ) || empty( $user_id ) ) {
				return $course_report;
			}

			// Get course details.
			$course      = get_post( $course_id );
			$course_link = get_permalink( $course_id );
			$progress    = learndash_course_progress(
				array(
					'user_id'   => $user_id,
					'course_id' => $course_id,
					'array'     => true,
				)
			);
			$status      = ( 100 == $progress['percentage'] ) ? 'completed' : 'notcompleted';

			// Get quiz details.
			$usermeta           = get_user_meta( $user_id, '_sfwd-quizzes', true );
			$quiz_attempts_meta = empty( $usermeta ) ? false : $usermeta;
			$quiz_attempts      = array();

			if ( ! empty( $quiz_attempts_meta ) ) {
				foreach ( $quiz_attempts_meta as $quiz_attempt ) {
					if ( ! isset( $quiz_attempt['course'] ) ) {
						$quiz_attempt['course'] = learndash_get_course_id( $quiz_attempt['quiz'] );
					}
					$quiz_course_id = intval( $quiz_attempt['course'] );

					if ( intval( $course_id ) !== $quiz_course_id ) {
						continue;
					}

					$c                          = learndash_certificate_details( $quiz_attempt['quiz'], $user_id );
					$quiz_attempt['post']       = get_post( $quiz_attempt['quiz'] );
					$quiz_attempt['percentage'] = ! empty( $quiz_attempt['percentage'] ) ? $quiz_attempt['percentage'] : ( ! empty( $quiz_attempt['count'] ) ? $quiz_attempt['score'] * 100 / $quiz_attempt['count'] : 0 );

					if ( ! empty( $c['certificateLink'] ) && ( ( isset( $quiz_attempt['percentage'] ) && $quiz_attempt['percentage'] >= $c['certificate_threshold'] * 100 ) ) ) {
						$quiz_attempt['certificate'] = $c;
					}

					$quiz_attempts[ $course_id ][] = $quiz_attempt;
				}
			}

			// Generate the course report.
			// ob_start();
			// include WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-detailed-course-report.template.php';
			// $course_report = ob_get_contents();
			// ob_end_clean();

			/**
			 * Replaced `include` with `ldgr_get_template`.
			 * 
			 * @since 4.1.2
			 */
			$course_report = ldgr_get_template(
				WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-detailed-course-report.template.php',
				array(
					'course_id'	=>	$course_id,
					'user_id'	=>	$user_id,
					'quiz_attempts'	=>	$quiz_attempts,
					'progress'	=>	$progress
				),
				1
			);

			return $course_report;
		}
	}
}
