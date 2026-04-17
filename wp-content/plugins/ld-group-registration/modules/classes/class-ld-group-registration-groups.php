<?php
/**
 * Groups Module
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/classes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace LdGroupRegistration\Modules\Classes;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Ld_Group_Registration_Groups' ) ) {
	/**
	 * Class LD Group Registration Groups
	 */
	class Ld_Group_Registration_Groups {
		protected static $instance = null;

		/**
		 * Get a singleton instance of this class
		 *
		 * @return object
		 * @since	4.1.0
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Enqueue Data tables scripts and styles
		 */
		public static function enqueue_data_table() {
			wp_register_script(
				'wdm_datatable_js',
				plugins_url( 'js/datatable.js', dirname( __FILE__ ) ),
				array( 'jquery' ),
				LD_GROUP_REGISTRATION_VERSION
			);

			$dtdata = array(
				'previous'                             => __( 'Previous', 'wdm_ld_group' ),
				'first'                                => __( 'First', 'wdm_ld_group' ),
				'last'                                 => __( 'Last', 'wdm_ld_group' ),
				'next'                                 => __( 'Next', 'wdm_ld_group' ),
				'no_data_available_in_table'           => __( 'No data available in table', 'wdm_ld_group' ),
				'no_matching_records_found'            => __( 'No matching records found', 'wdm_ld_group' ),
				'search_colon'                         => __( 'Search:', 'wdm_ld_group' ),
				'processing_dot_dot_dot'               => __( 'Processing...', 'wdm_ld_group' ),
				'loading_dot_dot_dot'                  => __( 'Loading...', 'wdm_ld_group' ),
				'show__menu__entries'                  => sprintf(
					// translators: For Showing entries in menu.
					__( 'Show %s entries', 'wdm_ld_group' ),
					'_MENU_'
				),
				'showing_zero_to_zero_of_zero_entries' => __( 'Showing 0 to 0 of 0 entries', 'wdm_ld_group' ),
				'filtered_from__max__tot_entries'      => sprintf(
					// translators: For Showing maximum number of entries.
					__( '(filtered from %s total entries)', 'wdm_ld_group' ),
					'_MAX_'
				),
				'showing__start__to__end__of__total__entries' => sprintf(
					// translators: For Showing from - to number of entries in pagination.
					__( 'Showing %1$s to %2$s of %3$s entries', 'wdm_ld_group' ),
					'_START_',
					' _END_',
					'_TOTAL_'
				),
				's_sort_descending'                    => __( ': activate to sort column descending', 'wdm_ld_group' ),
				's_sort_ascending'                     => __( ': activate to sort column ascending', 'wdm_ld_group' ),
			);
			wp_localize_script( 'wdm_datatable_js', 'wdm_datatable', $dtdata );

			wp_enqueue_script( 'wdm_datatable_js' );
		}

		/**
		 * Group removal request reject ajax
		 */
		public function handle_reject_request() {
			$user_id  = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
			$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );

			echo json_encode( $this->ldgr_group_request_process( 'reject', $user_id, $group_id ) );
			die();
		}

		/**
		 * Group removal request processing
		 *
		 * @param int $user_id   ID of the user.
		 * @param int $group_id  ID of the group.
		 */
		public function ldgr_group_request_process_check( $user_id, $group_id ) {
			if ( ! is_user_logged_in() ) {
				echo json_encode( array( 'error' => __( 'Please login to perform action', 'wdm_ld_group' ) ) );
				die();
			}
			if ( ! is_super_admin() ) {
				echo json_encode( array( 'error' => __( 'You are not the authorised user to perform this action', 'wdm_ld_group' ) ) );
				die();
			}
			if ( '' == $user_id || '' == $group_id ) {
				echo json_encode( array( 'error' => __( 'Oops, something went wrong', 'wdm_ld_group' ) ) );
				die();
			}
		}

		/**
		 * Process Group Request
		 *
		 * @param string $action       Action to perform on the request.
		 * @param int    $user_id      ID of the user.
		 * @param int    $group_id     ID of the group.
		 *
		 * @return array            Request response.
		 */
		public function ldgr_group_request_process( $action, $user_id, $group_id ) {
			$this->ldgr_group_request_process_check( $user_id, $group_id );

			$removal_request = maybe_unserialize( get_post_meta( $group_id, 'removal_request', true ) );
			if ( empty( $removal_request ) ) {
				return array( 'error' => __( 'No request found', 'wdm_ld_group' ) );
			}
			if ( ( $key = array_search( $user_id, $removal_request ) ) !== false ) {
				unset( $removal_request[ $key ] );
			}
			if ( empty( $removal_request ) ) {
				delete_post_meta( $group_id, 'removal_request', null );
			} else {
				update_post_meta( $group_id, 'removal_request', $removal_request );
			}
			if ( 'accept' == $action ) {
				$remove_user = $this->ldgr_remove_user_from_group( $user_id, $group_id );
				$group_limit = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );

				if ( $remove_user ) {
					return array(
						'success'     => __( 'Request accepted successfully', 'wdm_ld_group' ),
						'group_limit' => $group_limit,
					);
				}
			} elseif ( 'reject' == $action ) {
				$admin_group_ids = learndash_get_groups_administrator_ids( $group_id );
				$wdm_gr_gl_acpt_enable = get_option( 'wdm_gr_gl_acpt_enable' );
				if ( ! empty( $admin_group_ids ) && 'off' != $wdm_gr_gl_acpt_enable ) {
					$user_data = get_user_by( 'id', $user_id );
					foreach ( $admin_group_ids as $key => $value ) {
						if ( apply_filters( 'wdm_removal_request_reject_email_status', true, $group_id ) ) {
							$leader_data = get_user_by( 'id', $value );
							$subject     = __( 'User Removal request from group', 'wdm_ld_group' );

							$subject = get_option( 'wdm-gr-gl-acpt-sub' );
							if ( empty( $subject ) ) {
								$subject = WDM_GR_GL_ACPT_SUB;
							}
							$subject = stripslashes( $subject );
							$subject = str_replace( '{group_title}', get_the_title( $group_id ), $subject );
							$subject = str_replace( '{user_email}', $user_data->user_email, $subject );
							$subject = str_replace( '{group_leader_name}', ucfirst( strtolower( $leader_data->first_name ) ) . ' ' . ucfirst( strtolower( $leader_data->last_name ) ), $subject );
							$subject = apply_filters( 'wdm_removal_request_reject_subject', $subject, $group_id, $user_id, $value );

							$body = get_option( 'wdm-gr-gl-acpt-body' );
							if ( empty( $body ) ) {
								$body = WDM_GR_GL_ACPT_BODY;
							}
							$body = stripslashes( $body );

							$body = str_replace( '{group_title}', get_the_title( $group_id ), $body );
							$body = str_replace( '{user_email}', $user_data->user_email, $body );
							$body = str_replace( '{group_leader_name}', ucfirst( strtolower( $leader_data->first_name ) ) . ' ' . ucfirst( strtolower( $leader_data->last_name ) ), $body );
							$body = apply_filters( 'wdm_removal_request_reject_body', $body, $group_id, $user_id, $value );

							ldgr_send_group_mails(
								$leader_data->user_email,
								$subject,
								$body,
								array(),
								array(),
								array(
									'email_type' => 'WDM_GR_GL_ACPT_BODY',
									'group_id'   => $group_id,
								)
							);
						}
					}
				}
				return array( 'success' => __( 'Request rejected successfully', 'wdm_ld_group' ) );
			}
		}

		/**
		 * Group removal request accept ajax
		 */
		public function handle_accept_request() {
			$user_id  = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
			$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );
			echo json_encode( $this->ldgr_group_request_process( 'accept', $user_id, $group_id ) );
			die();
		}

		/**
		 * Bulk group removal request accept ajax
		 */
		public function handle_bulk_accept_request() {
			$user_ids = filter_input( INPUT_POST, 'user_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );

			$response = array();

			foreach ( $user_ids as $key => $user_id ) {
				$response[ $user_id ] = $this->ldgr_group_request_process( 'accept', $user_id, $group_id );
			}

			echo json_encode( $response );
			die();
		}

		/**
		 * Bulk group removal request reject ajax
		 */
		public function handle_bulk_reject_request() {
			$user_ids = filter_input( INPUT_POST, 'user_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );

			foreach ( $user_ids as $key => $user_id ) {
				$response[ $user_id ] = $this->ldgr_group_request_process( 'reject', $user_id, $group_id );
			}

			echo json_encode( $response );
			die();
		}

		/**
		 * Save post type group
		 * - Updating registration left count for each leader
		 *
		 * @param int $post_id  ID of the post.
		 */
		public function handle_registrations_left_save( $post_id ) {
			if ( ! isset( $_POST['wdm_ld_group'] ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $_POST['wdm_ld_group'], 'wdm_ld_group_value' ) ) {
				return;
			}
			$admin_group_ids = learndash_get_groups_administrator_ids( $post_id );

			if ( $_POST['wdm_ld_group_registration_left'] != '' ) {
				$left = $_POST['wdm_ld_group_registration_left'];
			} else {
				$left = 0;
			}

			// Check for unlimited seats group
			$is_unlimited = get_post_meta( $post_id, 'ldgr_unlimited_seats', 1 );

			if ( ! $is_unlimited ) {
				update_post_meta( $post_id, 'wdm_group_users_limit_' . $post_id, $left );
			}
		}

		/**
		 * Adding group registration left count meta box
		 */
		public function add_groups_metaboxes() {
			$screens = array( 'groups' );

			foreach ( $screens as $screen ) {
				add_meta_box(
					'wdm_ld_group',
					__( 'Group Registrations left', 'wdm_ld_group' ),
					array( $this, 'ldgr_registrations_left_callback' ),
					$screen
				);
			}
		}

		/**
		 * Display group registrations left metabox
		 *
		 * @param obj $post  Object of type Post.
		 */
		public function ldgr_registrations_left_callback( $post ) {
			wp_nonce_field( 'wdm_ld_group_value', 'wdm_ld_group' );
			$group_id     = $post->ID;
			$group_limit  = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
			$is_unlimited = get_post_meta( $group_id, 'ldgr_unlimited_seats', 1 );

			self::enqueue_data_table();

			wp_enqueue_script(
				'wdm_admin_js',
				plugins_url(
					'js/wdm_admin.js',
					dirname( __FILE__ )
				),
				array( 'jquery' ),
				LD_GROUP_REGISTRATION_VERSION
			);
			wp_enqueue_script(
				'ldgr_snackbar',
				plugins_url(
					'js/snackbar.js',
					dirname( __FILE__ )
				),
				array( 'jquery' ),
				LD_GROUP_REGISTRATION_VERSION
			);

			$data = array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'ajax_loader'      => plugins_url( 'media/ajax-loader.gif', dirname( __FILE__ ) ),
				'no_user_selected' => __( 'No user selected', 'wdm_ld_group' ),
			);

			wp_localize_script( 'wdm_admin_js', 'wdm_ajax', $data );

			wp_enqueue_style(
				'wdm_datatable_css',
				plugins_url(
					'css/datatables.min.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);
			wp_enqueue_style(
				'wdm_style_css',
				plugins_url(
					'css/style.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);
			wp_enqueue_style(
				'wdm_snackbar_css',
				plugins_url(
					'css/wdm-snackbar.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);

			$removal_request = maybe_unserialize( get_post_meta( $group_id, 'removal_request', true ) );

			include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/ldgr-group-registrations-left-metabox.template.php';
		}

		/**
		 * Ajax for handling bulk removal request of a group leader
		 */
		public function handle_bulk_remove_group_users() {
			$return    = array();
			$user_ids  = filter_input( INPUT_POST, 'user_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$group_ids = filter_input( INPUT_POST, 'group_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

			if ( ! is_array( $user_ids ) || empty( $user_ids ) ) {
				echo json_encode( array( 'error' => __( 'Oops Something went wrong', 'wdm_ld_group' ) ) );
				die();
			}

			foreach ( $user_ids as $key => $user_id ) {
				$return[ $user_id ] = $this->remove_group_user( $user_id, $group_ids[ $key ] );
			}

			echo json_encode( $return );

			die();
		}

		/**
		 * Remove user from group
		 *
		 * @param int $user_id      ID of the user.
		 * @param int $group_id     ID of the group.
		 *
		 * @return array            Success or error message in key => value format.
		 */
		public function remove_group_user( $user_id, $group_id ) {
			if ( is_user_logged_in() ) {
				if ( is_group_leader( get_current_user_id() ) || learndash_is_group_leader_user( get_current_user_id() ) || current_user_can( 'manage_options' ) ) {
					$admin_group_ids = learndash_get_administrators_group_ids( get_current_user_id() );

					if ( ! in_array( $group_id, $admin_group_ids ) ) {
						return array( 'error' => __( 'You are not the owner of this group', 'wdm_ld_group' ) );
					}

					if ( '' != $user_id && '' != $group_id ) {
						$ldgr_admin_approval = get_option( 'ldgr_admin_approval' );

						if ( 'on' == $ldgr_admin_approval ) {
							$response = $this->ldgr_remove_user_from_group( $user_id, $group_id );
							if ( $response ) {
								return array( 'success' => __( 'User removed from the Group Successfully', 'wdm_ld_group' ) );
							} else {
								return array( 'error' => __( 'Oops Something went wrong', 'wdm_ld_group' ) );
							}
							// die();.
						} else {
							// When Admin needs to approv the remove request.
							$removal_request = maybe_unserialize( get_post_meta( $group_id, 'removal_request', true ) );
							if ( empty( $removal_request ) ) {
								$removal_request = array();
							}

							$removal_request[]  = $user_id;
							$removal_req_unique = array_unique( $removal_request );
							update_post_meta( $group_id, 'removal_request', $removal_req_unique );

							// Fetch email enable/disable setting
							$wdm_a_rq_rmvl_enable = get_option( 'wdm_a_rq_rmvl_enable' );

							if ( apply_filters( 'wdm_removal_request_admin_email_status', true, $group_id ) && 'off' != $wdm_a_rq_rmvl_enable ) {
								$user_data   = get_user_by( 'id', $user_id );
								$group_title = get_the_title( $group_id );
								$subject     = __( 'User Removal request from group', 'wdm_ld_group' );
								$leader_data = get_user_by( 'id', get_current_user_id() );

								$subject = get_option( 'wdm-a-rq-rmvl-sub' );
								if ( empty( $subject ) ) {
									$subject = WDM_A_RQ_RMVL_SUB;
								}
								$subject = stripslashes( $subject );
								$subject = str_replace( '{group_title}', $group_title, $subject );
								$subject = str_replace( '{user_email}', $user_data->user_email, $subject );
								$subject = str_replace( '{group_edit_link}', admin_url( 'post.php?post=' . $group_id . '&action=edit' ), $subject );
								$subject = str_replace( '{group_leader_name}', ucfirst( strtolower( $leader_data->first_name ) ) . ' ' . ucfirst( strtolower( $leader_data->last_name ) ), $subject );
								$subject = apply_filters( 'wdm_removal_subject', $subject, $group_id, get_current_user_id(), $user_id );

								$tbody = get_option( 'wdm-a-rq-rmvl-body' );
								if ( empty( $tbody ) ) {
									$tbody = WDM_A_RQ_RMVL_BODY;
								}
								$body = stripslashes( $tbody );

								$body = str_replace( '{group_title}', $group_title, $body );
								$body = str_replace( '{group_leader_name}', ucfirst( strtolower( $leader_data->first_name ) ) . ' ' . ucfirst( strtolower( $leader_data->last_name ) ), $body );
								$body = str_replace( '{user_email}', $user_data->user_email, $body );
								$body = str_replace( '{group_edit_link}', admin_url( 'post.php?post=' . $group_id . '&action=edit' ), $body );
								$body = apply_filters( 'wdm_removal_request_body', $body, $group_id, get_current_user_id(), $user_id );

								// Admin emails
								$admin_email = ! empty( get_option( 'wdm-gr-admin-email' ) ) ? get_option( 'wdm-gr-admin-email' ) : get_option( 'admin_email' );

								ldgr_send_group_mails(
									apply_filters( 'wdm_removal_request_email_to', $admin_email ),
									$subject,
									$body,
									array(),
									array(),
									array(
										'email_type' => 'WDM_A_RQ_RMVL_BODY',
										'group_id'   => $group_id,
									)
								);
							}

							return array( 'success' => __( 'Removal request sent Successfully', 'wdm_ld_group' ) );
						}
					} else {
						return array( 'error' => __( 'Oops Something went wrong', 'wdm_ld_group' ) );
						// die();
					}
				} else {
					return array( 'error' => __( "You don't have privilege to do this action", 'wdm_ld_group' ) );
				}
			} else {
				return array( 'error' => __( "You don't have privilege to do this action", 'wdm_ld_group' ) );
			}
			return array();
		}

		/**
		 *  Ajax for handling removal request from group leader
		 */
		public function handle_group_unenrollment() {
			 $user_id = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
			$group_id = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );

			echo json_encode( $this->remove_group_user( $user_id, $group_id ) );

			die();
		}

		/**
		 * After trashing group, deleting associated user details
		 *
		 * @param int $postid  ID of the post
		 */
		public function handle_group_deletion( $postid ) {
			// echo $postid;exit;
			// We check if the global post type isn't ours and just return
			global $post_type;
			if ( $post_type != 'groups' ) {
				return;
			}
			$group_leaders = learndash_get_groups_administrator_ids( $postid );
			if ( ! empty( $group_leaders ) ) {
				foreach ( $group_leaders as $k => $v ) {
					delete_user_meta( $v, 'wdm_group_users_limit_' . $postid, null );
					delete_user_meta( $v, 'wdm_group_product_' . $postid, null );
					delete_user_meta( $v, 'ldgr_unlimited_seats', null );
					unset( $k );
				}
			}
		}

		/**
		 * Uploading the csv file
		 *
		 * @param int   $step
		 * @param float $percentage
		 * @return array
		 * @since
		 */
		public function ldgr_upload_csv( &$step, &$percentage ) {
			if ( isset( $_POST['wdm_upload_check'] ) &&
			( isset( $_POST['wdm_ldgr_csv_upload_enroll_field'] ) && wp_verify_nonce( $_POST['wdm_ldgr_csv_upload_enroll_field'], 'wdm_ldgr_csv_upload_enroll' ) )
			) {
				$csv_invalid = $this->check_if_valid_csv_file( $_FILES );

				if ( ! empty( $csv_invalid ) ) {
					define( 'WDM_ERROR_MESSAGE', __( 'ERROR', 'wdm_ld_group' ) . ': ' . $csv_invalid );
					return;
				}

				$group_id = filter_input( INPUT_POST, 'wdm_group_id', FILTER_SANITIZE_NUMBER_INT );

				if ( $step ) {
					$batch_length = apply_filters( 'ldgr_filter_csv_upload_batch_length', 10 );
				}

				$csv_data_list = $this->get_csv_data_list( $_FILES, $group_id, $step, $batch_length );

				if ( ! empty( $csv_data_list['error'] ) ) {
					define( 'WDM_ERROR_MESSAGE', __( 'ERROR', 'wdm_ld_group' ) . ': ' . $csv_data_list['error'] );
					return;
				}

				// Batch Process data.
				if ( $step ) {
					$csv_length = count( $csv_data_list['emails'] );
					if ( count( $csv_length > $batch_length ) ) {
						$start                        = ( $step - 1 ) * $batch_length;
						$csv_data_list['emails']      = array_slice( $csv_data_list['emails'], $start, $batch_length );
						$csv_data_list['first_names'] = array_slice( $csv_data_list['first_names'], $start, $batch_length );
						$csv_data_list['last_names']  = array_slice( $csv_data_list['last_names'], $start, $batch_length );

						$step++;
						$processed_count = intval( $start + $batch_length );

						if ( $csv_length <= $processed_count ) {
							$step = 'done';
						}
					}
				}

				$percentage = intval( ( $processed_count / $csv_length ) * 100 );

				return $this->ldgr_enroll_users( $csv_data_list, $group_id );
			}
		}

		/**
		 * Enroll users from CSV
		 *
		 * @param array $csv_data_list  CSV data details array.
		 * @param int   $group_id       ID of the group.
		 *
		 * @return array                List of newly added users.
		 */
		public function ldgr_enroll_users( $csv_data_list, $group_id ) {
			global $error_data;
			global $success_data;

			$final_csv_data = array(
				'fname'    => $csv_data_list['first_names'],
				'lname'    => $csv_data_list['last_names'],
				'email'    => $csv_data_list['emails'],
				'group_id' => $group_id,
			);

			$final_csv_data = apply_filters( 'wdm_ld_gr_alter_upload_data', $final_csv_data, $group_id, $csv_data_list );

			$fname    = $final_csv_data['fname'];
			$lname    = $final_csv_data['lname'];
			$email    = $final_csv_data['email'];
			$group_id = $final_csv_data['group_id'];

			$newly_added_user = array();
			$lead_user        = new \WP_User( get_current_user_id() );
			$courses          = learndash_group_enrolled_courses( $group_id );
			$group_limit      = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
			$is_unlimited     = get_post_meta( $group_id, 'ldgr_unlimited_seats', 1 );
			$group_leader_ids = learndash_get_groups_administrator_ids( $group_id );
			$all_emails_list  = array();

			foreach ( $email as $k => $val ) {
				if ( $group_limit > 0 || $is_unlimited ) {
					$user_exits = email_exists( $val );
					if ( '' != $user_exits ) {
						$member_user = new \WP_User( $user_exits );

						$already_enroll = apply_filters(
							'ldgr_filter_enroll_user_in_group',
							learndash_is_user_in_group(
								$user_exits,
								$group_id
							),
							$user_exits,
							$group_id
						);

						// Retrieves the all group leader ids.
						$group_leader_ids = learndash_get_groups_administrator_ids( $group_id );

						// adds user if user is not group member or leader.
						// if (!$already_enroll && !in_array($member_user->ID, $group_leader_ids)) { .
						if ( ! $already_enroll ) {

							if ( apply_filters( 'is_ldgr_default_user_add_action', true ) ) {
								ld_update_group_access( $user_exits, $group_id );
								delete_user_meta( $member_user->ID, '_total_groups_an_user_removed_from' );
							}

							do_action( 'ldgr_action_existing_user_enroll', $user_exits, $group_id, $final_csv_data );

							$tsub = get_option( 'wdm-u-add-gr-sub' );
							if ( empty( $tsub ) ) {
								$tsub = WDM_U_ADD_GR_SUB;
							}
							$subject = stripslashes( $tsub );
							$enrolled_course = array();
							if ( ! empty( $courses ) ) {
								foreach ( $courses as $key => $value ) {
									$enrolled_course[] = get_the_title( $value );
									$url               = get_permalink( $value );
									unset( $key );
								}
							}
							$subject = str_replace( '{group_title}', get_the_title( $group_id ), $subject );
							// $subject = str_replace("{course_list}", '' , $subject);
							$subject = str_replace( '{group_leader_name}', ucfirst( strtolower( $lead_user->first_name ) ) . ' ' . ucfirst( strtolower( $lead_user->last_name ) ), $subject );
							$subject = str_replace( '{user_first_name}', ucfirst( strtolower( $member_user->first_name ) ), $subject );
							$subject = str_replace( '{user_last_name}', ucfirst( strtolower( $member_user->last_name ) ), $subject );
							$subject = str_replace( '{login_url}', wp_login_url( $url ), $subject );

							$tbody = get_option( 'wdm-u-add-gr-body' );
							if ( empty( $tbody ) ) {
								$tbody = WDM_U_ADD_GR_BODY;
							}
							$body = stripslashes( $tbody );

							$body = str_replace( '{group_title}', get_the_title( $group_id ), $body );
							$body = str_replace( '{course_list}', $this->get_course_list_html( $enrolled_course, $group_id, $member_user->ID ), $body );
							$body = str_replace( '{group_leader_name}', ucfirst( strtolower( $lead_user->first_name ) ) . ' ' . ucfirst( strtolower( $lead_user->last_name ) ), $body );
							$body = str_replace( '{user_first_name}', ucfirst( strtolower( $member_user->first_name ) ), $body );
							$body = str_replace( '{user_last_name}', ucfirst( strtolower( $member_user->last_name ) ), $body );
							$body = str_replace( '{login_url}', wp_login_url( $url ), $body );

							// Fetch enable/disable email setting
							$wdm_u_add_gr_enable = get_option( 'wdm_u_add_gr_enable' );

							if ( apply_filters( 'wdm_group_enrollment_email_status', true, $group_id ) && 'off' != $wdm_u_add_gr_enable ) {
								$all_emails_list[ $member_user->ID ] = array(
									'email'   => $val,
									'subject' => $subject,
									'body'    => $body,
									'new'     => false,
								);
							}
							$success_data .= apply_filters( 'wdm_group_enrollment_success_message', sprintf( __( '%s has been enrolled', 'wdm_ld_group' ), $val ) . '<br />', $group_id, $val );
							--$group_limit;
							$newly_added_user[] = $member_user->ID;
						} else {
							$error_data .= apply_filters( 'wdm_group_enrollment_error_message', sprintf( __( '%s is already enrolled to group', 'wdm_ld_group' ), $member_user->user_email ) . '<br />', $group_id, $val );
						}
					} else {
						// If E-mail is invalid then show error for that user only.
						if ( ! filter_var( $val, FILTER_VALIDATE_EMAIL ) ) {
							$error_data .= apply_filters( 'wdm_group_leader_enrollment_error_message', sprintf( __( 'Invalid E-mail address : %s ', 'wdm_ld_group' ), $val ) . '<br />', $group_id, $val );
						} else {
							$password = wp_generate_password( 8 );
							$userdata = array(
								'user_login' => $val,
								'user_email' => $val,
								'first_name' => $fname[ $k ],
								'last_name'  => $lname[ $k ],
								'user_pass'  => $password, // When creating a user, `user_pass` is expected.
							);
							$userdata = apply_filters( 'ldgr_filter_new_user_details', $userdata, $final_csv_data );

							$member_user_id = wp_insert_user( $userdata );
							$f_name         = $fname[ $k ];
							$l_name         = $lname[ $k ];

							$all_emails_list[ $member_user_id ] = array(
								'email'     => $val,
								'new'       => true,
								'group_id'  => $group_id,
								'user_data' => $userdata,
								'courses'   => $courses,
								'lead_user' => $lead_user,
							);

							// On success.
							--$group_limit;
							$newly_added_user[] = $member_user_id;
						}
					}
				}
			}

			$this->send_bulk_upload_emails( $all_emails_list, $group_id, $final_csv_data );

			$error_data = str_replace( '<br>', '<br>ERROR: ', $error_data );
			$error_data = ldgr_str_lreplace( 'ERROR: ', '', $error_data );
			if ( '' != $error_data ) {
				define( 'WDM_ERROR_MESSAGE', __( 'ERROR', 'wdm_ld_group' ) . ': ' . $error_data );
			}
			$success_data = str_replace( '<br>', '<br>SUCCESS: ', $success_data );
			$success_data = ldgr_str_lreplace( 'SUCCESS: ', '', $success_data );
			if ( '' != $success_data ) {
				define( 'WDM_SUCCESS_MESSAGE', __( 'SUCCESS', 'wdm_ld_group' ) . ': ' . $success_data );
			}

			// Update Group User Limit.
			update_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, $group_limit );

			if ( $group_limit <= 0 && ! $is_unlimited ) {
				do_action( 'wdm_group_limit_is_zero', $group_id );
			}

			if ( ! empty( $newly_added_user ) ) {
				do_action( 'ld_group_postdata_updated', $group_id, $group_leader_ids, $newly_added_user, $courses );
			}

			return $newly_added_user;
		}

		/**
		 * Get course list HTML
		 *
		 * @param array $course_list    List of courses to display.
		 *
		 * @return string               HTML list of courses.
		 */
		public function get_course_list_html( $course_list, $group_id = 0, $user_id = 0 ) {
			$return = '';
			if ( ! empty( $course_list ) ) {
				$return = '<ul>';
				foreach ( $course_list as $course ) {
					$return .= '<li>' . $course . '</li>';
				}
				$return .= '</ul>';
			}
			return apply_filters( 'ldgr_course_list_html', $return, $course_list, $group_id, $user_id );
		}

		/**
		 * Creating user, associating user with group
		 */
		public function handle_group_enrollment_form() {
			if ( isset( $_POST['wdm_add_user_check'] ) ) {
				$group_id = filter_input( INPUT_POST, 'wdm_group_id', FILTER_SANITIZE_NUMBER_INT );
				$email    = filter_input( INPUT_POST, 'wdm_members_email', FILTER_SANITIZE_EMAIL, FILTER_REQUIRE_ARRAY );
				$fname    = filter_input( INPUT_POST, 'wdm_members_fname', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
				$lname    = filter_input( INPUT_POST, 'wdm_members_lname', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );

				if ( is_group_leader_restricted_to_perform_actions( get_current_user_id(), $group_id ) ) {
					$error_data = __( "You don't have permission to perform this action.", 'wdm_ld_group' );
					define( 'WDM_ERROR_MESSAGE', __( 'ERROR', 'wdm_ld_group' ) . ': ' . $error_data );
				} else {
					$this->ldgr_enroll_users(
						array(
							'emails'      => $email,
							'first_names' => $fname,
							'last_names'  => $lname,
						),
						$group_id
					);
				}
			}
		}

		/**
		 * Register new user and enroll in group
		 *
		 * @param int    $member_user_id   ID of the user to register and enroll.
		 * @param string $f_name           First name of the user.
		 * @param string $l_name           Last name of the user.
		 * @param string $val              Email of the user.
		 * @param string $password         Password of the new user.
		 * @param array  $courses          List of courses to enroll in.
		 * @param obj    $lead_user        Group leader.
		 * @param int    $group_id         ID of the group.
		 *
		 * @return string                  Status of the newly enrolled user.
		 */
		public function new_user_registration( $member_user_id, $f_name, $l_name, $val, $password, $courses, $lead_user, $group_id ) {
			global $success_data;

			if ( ! is_wp_error( $member_user_id ) ) {
				$user_data           = get_user_by( 'id', $member_user_id );
				$key                 = get_password_reset_key( $user_data );
				$reset_arg           = array(
					'action' => 'rp',
					'key'    => $key,
					'login'  => rawurlencode( $user_data->user_login ),
				);
				$reset_password_link = add_query_arg( $reset_arg, network_site_url( 'wp-login.php', 'login' ) );

				$subject = get_option( 'wdm-u-ac-crt-sub' );
				if ( empty( $subject ) ) {
					$subject = WDM_U_AC_CRT_SUB;
				}
				$enrolled_course = array();
				foreach ( $courses as $key => $value ) {
					$enrolled_course[] = get_the_title( $value );
					$url               = get_permalink( $value );
					unset( $key );
				}
				$subject = stripslashes( $subject );
				$subject = str_replace( '{group_title}', get_the_title( $group_id ), $subject );
				$subject = str_replace( '{site_name}', get_bloginfo(), $subject );
				$subject = str_replace( '{user_first_name}', ucfirst( $f_name ), $subject );
				$subject = str_replace( '{user_last_name}', ucfirst( $l_name ), $subject );
				$subject = str_replace( '{user_email}', $val, $subject );
				$subject = str_replace( '{user_password}', $password, $subject );
				$subject = str_replace( '{course_list}', $this->get_course_list_html( $enrolled_course, $group_id, $member_user_id ), $subject );
				$subject = str_replace( '{group_leader_name}', ucfirst( strtolower( $lead_user->first_name ) ) . ' ' . ucfirst( strtolower( $lead_user->last_name ) ), $subject );
				$subject = str_replace( '{login_url}', wp_login_url(), $subject );
				$subject = str_replace( '{reset_password}', $reset_password_link, $subject );

				$tbody = get_option( 'wdm-u-ac-crt-body' );
				if ( empty( $tbody ) ) {
					$tbody = WDM_U_AC_CRT_BODY;
				}
				$body = stripslashes( $tbody );

				$body = str_replace( '{group_title}', get_the_title( $group_id ), $body );
				$body = str_replace( '{site_name}', get_bloginfo(), $body );
				$body = str_replace( '{user_first_name}', ucfirst( $f_name ), $body );
				$body = str_replace( '{user_last_name}', ucfirst( $l_name ), $body );
				$body = str_replace( '{user_email}', $val, $body );
				$body = str_replace( '{user_password}', $password, $body );
				$body = str_replace( '{course_list}', $this->get_course_list_html( $enrolled_course, $group_id, $member_user_id ), $body );
				$body = str_replace( '{group_leader_name}', ucfirst( strtolower( $lead_user->first_name ) ) . ' ' . ucfirst( strtolower( $lead_user->last_name ) ), $body );
				$body = str_replace( '{login_url}', wp_login_url(), $body );
				$body = str_replace( '{reset_password}', $reset_password_link, $body );

				// Fetch enable/disable email setting
				$wdm_u_ac_crt_enable = get_option( 'wdm_u_ac_crt_enable' );
				if ( apply_filters( 'wdm_group_enrollment_email_status', true, $group_id ) && 'off' != $wdm_u_ac_crt_enable ) {
					ldgr_send_group_mails(
						$val,
						apply_filters( 'wdm_group_email_subject', $subject, $group_id, $member_user_id ),
						apply_filters( 'wdm_group_email_body', $body, $group_id, $member_user_id ),
						array(),
						array(),
						array(
							'email_type' => 'WDM_U_AC_CRT_BODY',
							'group_id'   => $group_id,
						)
					);
				}
				$success_data .= apply_filters( 'wdm_group_enrollment_success_message', sprintf( __( '%s has been enrolled', 'wdm_ld_group' ), $val ) . '<br />' );
				ld_update_group_access( $member_user_id, $group_id );
				$member_user_data = new \WP_User( $member_user_id );

				$blogname          = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
				$member_login_data = stripslashes( $member_user_data->user_login );
				$member_email_data = stripslashes( $member_user_data->user_email );

				$tbody = get_option( 'wdm-a-u-ac-crt-body' );
				if ( empty( $tbody ) ) {
					$tbody = WDM_A_U_AC_CRT_BODY;
				}
				$message = stripslashes( $tbody );

				$message = str_replace( '{group_title}', get_the_title( $group_id ), $message );
				$message = str_replace( '{site_name}', $blogname, $message );
				$message = str_replace( '{user_email}', $member_email_data, $message );
				$message = str_replace( '{user_login}', $member_login_data, $message );

				$title = get_option( 'wdm-a-u-ac-crt-sub' );
				if ( empty( $title ) ) {
					$title = WDM_A_U_AC_CRT_SUB;
				}

				$title = str_replace( '{group_title}', get_the_title( $group_id ), $title );
				$title = str_replace( '{site_name}', $blogname, $title );
				$title = str_replace( '{user_email}', $member_email_data, $title );
				$title = str_replace( '{user_login}', $member_login_data, $title );

				// admin emails
				$admin_email = ! empty( get_option( 'wdm-gr-admin-email' ) ) ? get_option( 'wdm-gr-admin-email' ) : get_option( 'admin_email' );
				$send_to     = apply_filters( 'new_user_admin_notification_mail_to', $admin_email );

				// Fetch enable/disable email setting
				$wdm_a_u_ac_crt_enable = get_option( 'wdm_a_u_ac_crt_enable' );

				if ( apply_filters( 'wdm_new_user_creation_email_status', true, $group_id ) && 'off' != $wdm_a_u_ac_crt_enable ) {
					$title   = apply_filters( 'wdm_new_user_admin_notification_subject', $title, $group_id );
					$message = apply_filters( 'wdm_new_user_admin_notification_body', $message, $group_id, $member_user_data );
					ldgr_send_group_mails(
						$send_to,
						$title,
						$message,
						array(),
						array(),
						array(
							'email_type' => 'WDM_A_U_AC_CRT_BODY',
							'group_id'   => $group_id,
						)
					);
				}

				return $success_data;
			}
		}

		/**
		 * Get limit of a group
		 *
		 * @param int $group_limit  Limit of the group.
		 * @return int              Group limit if valid, else 0.
		 */
		public function get_group_limit_number( $group_limit ) {
			$grp_limit_count = '';
			if ( $group_limit == '' ) {
				$grp_limit_count = 0;
			} else {
				$grp_limit_count = $group_limit;
			}
			return $grp_limit_count;
		}

		/**
		 * Get selected group value.
		 */
		public function get_selected_group_value( $group_id, $val ) {
			if ( $group_id == '' ) {
				$group_id = $val;
			}
			return $group_id;
		}

		/**
		 * Add groups shortcodes
		 */
		public function add_groups_shortcodes() {
			add_shortcode(
				'wdm_group_users',
				array( $this, 'handle_group_registration_shortcode_display' )
			);
		}

		/**
		 * Display group registration shortcode page - [wdm_group_users]
		 */
		public function handle_group_registration_shortcode_display() {
			ob_start();

			if ( ! is_user_logged_in() ) {
				echo '<h2>' . __( 'Please Login to view this page', 'wdm_ld_group' ) . '</h2>';
				do_action( 'ldgr_action_after_login_restriction' );
				return ob_get_clean();
			}

			$user_id   = get_current_user_id();
			$group_ids = ldgr_get_leader_group_ids( $user_id );

			if ( empty( $group_ids ) ) {
				esc_html_e( 'You are not the leader of any group', 'wdm_ld_group' );
				do_action( 'ldgr_action_no_groups' );
				return ob_get_clean();
			}

			$group_id = filter_input( INPUT_POST, 'wdm_group_id', FILTER_SANITIZE_NUMBER_INT );

			if ( empty( $group_id ) ) {
				$group_id = $group_ids[0];
			}

			$user_data = get_user_by( 'id', $user_id );
			if ( ! $this->check_if_group_leader( $user_id ) ) {
				echo '<h2>' . __( 'You do not have privilege to view this page.', 'wdm_ld_group' ) . '</h2>';
				do_action( 'ldgr_action_no_group_privileges' );
				return ob_get_clean();
			}
			$this->enqueue_group_users_display_shortcode_scripts( $group_id );

			$group_limit     = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
			$grp_limit_count = $this->get_group_limit_number( $group_limit );

			$need_to_restrict   = false;
			$subscription_id    = get_post_meta( $group_id, 'wdm_group_subscription_' . $group_id, true );
			$sub_current_status = '';
			$user_sub_det       = $this->get_subscription_status( $user_id, $subscription_id );
			$need_to_restrict   = $user_sub_det['need_to_restrict'];
			$sub_current_status = $user_sub_det['sub_current_status'];

			// Due to transient is set it doesn't refreshes the users so set it to zero.
			update_option( '_transient_timeout_learndash_group_users_' . $group_id, 0 );

			include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/ldgr-group-users/ldgr-group-users.template.php';

			return ob_get_clean();
		}

		/**
		 * Get subscription status
		 *
		 * @param int $user_id          ID of the user.
		 * @param int $subscription_id  ID of the subscription.
		 *
		 * @return array $details       Details about the subscription.
		 */
		public function get_subscription_status( $user_id, $subscription_id ) {
			$details = array(
				'sub_current_status' => '',
				'need_to_restrict'   => false,
			);
			if ( ! empty( $subscription_id ) ) {
				$not_active_sub = get_user_meta( $user_id, '_wdm_total_hold_subscriptions', true );
				if ( ! empty( $not_active_sub ) && ( in_array( $subscription_id, $not_active_sub ) ) ) {
					$details['need_to_restrict'] = true;
					$wdm_subscription            = \wcs_get_subscription( $subscription_id );
					// $sub_current_status = '';
					if ( $wdm_subscription instanceof \WC_Subscription ) {
						$details['sub_current_status'] = $wdm_subscription->get_status();
					}
				}
			}
			return $details;
		}

		/**
		 * Display subscription errors
		 *
		 * @param bool  $need_to_restrict    Whether to restrict the content or not.
		 * @param int   $subscription_id     ID of the subscription.
		 * @param array $sub_current_status Details about the subscription status.
		 */
		public function show_subscription_errors( $need_to_restrict, $subscription_id, $sub_current_status ) {
			$wdm_link = '';
			if ( $need_to_restrict ) {
				$wdm_link .= '<a href="' . site_url() . '/my-account/subscriptions/' . '">#' . $subscription_id . '</a>';
				if ( 'on-hold' == $sub_current_status ) {
					echo sprintf( __( '<p>Your %s subscription put on the hold. Please contact admin.</p>', 'wdm_ld_group' ), $wdm_link );
				} elseif ( 'cancelled' == $sub_current_status ) {
					echo sprintf( __( '<p>Your %s subscription has been cancelled. Please contact admin.</p>', 'wdm_ld_group' ), $wdm_link );
				} elseif ( 'switched' == $sub_current_status || 'expired' == $sub_current_status ) {
					echo sprintf( __( '<p>Your %s subscription has been expired. Please contact admin.</p>', 'wdm_ld_group' ), $wdm_link );
				} elseif ( 'pending' == $sub_current_status ) {
					echo sprintf( __( '<p> Your %s subscription status is pending. Please contact admin.</p>', 'wdm_ld_group' ), $wdm_link );
				} else {
					echo sprintf( __( '<p>Your %s subscription put on the hold. Please contact admin.</p>', 'wdm_ld_group' ), $wdm_link );
				}
			}
		}

		/**
		 * Add product link to add new users to the group.
		 *
		 * @param int $group_id     ID of the group
		 *
		 * @return string           Link to the product associated with the group.
		 */
		public function add_new_users_link( $group_id ) {
			$is_unlimited = get_post_meta( $group_id, 'ldgr_unlimited_seats', 1 );
			if ( get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true ) == 0 && ! $is_unlimited ) {
				?>
				<a
					class="ldgr-add-new-users"
					href="<?php echo get_permalink( get_user_meta( get_current_user_id(), 'wdm_group_product_' . $group_id, true ) ); ?>"
				>
					<?php echo apply_filters( 'wdm_add_new_users_label', __( 'Add New Users', 'wdm_ld_group' ) ); ?>
				</a>
				<?php
			}
		}

		/**
		 * Adding group leader column in groups post type listing
		 *
		 * @param array $array containing column names.
		 */
		public function add_column_heading( $array ) {
			$res = array_slice( $array, 0, 2, true ) + array( 'group_leader' => __( 'Group Leader', 'wdm_ld_group' ) ) + array_slice( $array, 2, count( $array ) - 1, true );
			return $res;
		}

		/**
		 * Fetching group leader associated with each group.
		 *
		 * @param string $column_key    Key of the column.
		 * @param int    $group_id      ID of the group.
		 */
		public function add_column_data( $column_key, $group_id ) {
			// exit early if this is not the column we want.
			if ( 'group_leader' != $column_key ) {
				return;
			}
			$group_leader = learndash_get_groups_administrator_ids( $group_id );
			if ( ! empty( $group_leader ) ) {
				$group_temp = array();
				foreach ( $group_leader as $k => $v ) {
					$group_user   = get_user_by( 'id', $v );
					$group_temp[] = $group_user->user_email;
					unset( $k );
				}
				echo esc_html( implode( ', ', $group_temp ) );
			}
		}

		/**
		 * Check if group leader
		 *
		 * @param int $user_id  ID of the user.
		 * @return bool         True if user is group leader, false otherwise.
		 */
		public function check_if_group_leader( $user_id ) {
			if ( current_user_can( 'manage_options' ) ) {
				return true;
			}
			if ( function_exists( 'learndash_is_group_leader_user' ) ) {
				if ( learndash_is_group_leader_user( $user_id ) ) {
					return true;
				}
				return false;
			} else {
				if ( is_group_leader( $user_id ) ) {
					return true;
				}
				return false;
			}
		}

		public function handle_group_limit_empty( $group_id ) {
			if ( \metadata_exists( 'post', $group_id, 'wdm_group_users_limit_' . $group_id ) ) {
				$not_updated_user = array();
				$product_id       = '';
				$user_id          = '';
				$admin_ids        = \learndash_get_groups_administrator_ids( $group_id );
				if ( ! empty( $admin_ids ) ) {
					foreach ( $admin_ids as $id ) {
						if ( \metadata_exists( 'user', $id, 'wdm_group_product_' . $group_id ) ) {
							$product_id = get_user_meta( $id, 'wdm_group_product_' . $group_id, true );
							$user_id    = $id;
							break;
						}
					}
				}
				if ( ! empty( $product_id ) ) {
					foreach ( $admin_ids as $id ) {
						if ( $id != $user_id ) {
							update_user_meta( $id, 'wdm_group_product_' . $group_id, $product_id );
						}
					}
				}
			}
		}

		public function ldgr_remove_user_from_group( $user_id, $group_id ) {
			$group_limit = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
			if ( $group_limit == '' ) {
				$group_limit = 0;
			}

			// If the group limit setting is not enable then increase group limit on user removal
			$ldgr_group_limit = get_option( 'ldgr_group_limit' );
			if ( $ldgr_group_limit != 'on' ) {
				$group_limit = $group_limit + 1;
				update_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, $group_limit );
			}

			$ldgr_admin_approval   = get_option( 'ldgr_admin_approval' );
			$wdm_gr_gl_rmvl_enable = get_option( 'wdm_gr_gl_rmvl_enable' );

			if ( $ldgr_admin_approval != 'on' && 'off' != $wdm_gr_gl_rmvl_enable ) {
				$admin_group_ids = learndash_get_groups_administrator_ids( $group_id );
				if ( ! empty( $admin_group_ids ) ) {
					$user_data = get_user_by( 'id', $user_id );
					foreach ( $admin_group_ids as $key => $value ) {
						// update_user_meta($value, 'wdm_group_users_limit_'.$group_id, $group_limit);
						if ( apply_filters( 'wdm_removal_request_accept_email_status', true, $group_id ) ) {
							$leader_data = get_user_by( 'id', $value );

							$gl_rmvl_sub = get_option( 'wdm-gr-gl-rmvl-sub' );
							if ( empty( $gl_rmvl_sub ) ) {
								$gl_rmvl_sub = WDM_GR_GL_RMVL_SUB;
							}
							$gl_rmvl_sub = str_replace( '{group_title}', get_the_title( $group_id ), $gl_rmvl_sub );
							$gl_rmvl_sub = str_replace( '{user_email}', $user_data->user_email, $gl_rmvl_sub );
							$gl_rmvl_sub = str_replace( '{group_leader_name}', ucfirst( strtolower( $leader_data->first_name ) ) . ' ' . ucfirst( strtolower( $leader_data->last_name ) ), $gl_rmvl_sub );
							$subject     = apply_filters( 'wdm_removal_request_accept_subject', $gl_rmvl_sub, $group_id, $user_id, $value );

							$gl_rmvl_body = get_option( 'wdm-gr-gl-rmvl-body' );
							if ( empty( $gl_rmvl_body ) ) {
								$gl_rmvl_body = WDM_GR_GL_RMVL_BODY;
							}
							$gl_rmvl_body = str_replace( '{group_title}', get_the_title( $group_id ), $gl_rmvl_body );
							$gl_rmvl_body = str_replace( '{user_email}', $user_data->user_email, $gl_rmvl_body );
							$gl_rmvl_body = str_replace( '{group_leader_name}', ucfirst( strtolower( $leader_data->first_name ) ) . ' ' . ucfirst( strtolower( $leader_data->last_name ) ), $gl_rmvl_body );
							$body         = stripslashes( $gl_rmvl_body );
							$body         = apply_filters( 'wdm_removal_request_accept_body', $body, $group_id, $user_id, $value );

							ldgr_send_group_mails(
								$leader_data->user_email,
								$subject,
								$body,
								array(),
								array(),
								array(
									'email_type' => 'WDM_GR_GL_RMVL_BODY',
									'group_id'   => $group_id,
								)
							);
						}
					}
				}
			}

			ld_update_group_access( $user_id, $group_id, true );
			do_action( 'wdm_removal_request_accepted_successfully', $group_id, $user_id );

			return true;
		}

		public function send_reinvite_mail_callback() {

			if ( is_user_logged_in() ) {
				if ( is_group_leader( get_current_user_id() ) || learndash_is_group_leader_user( get_current_user_id() ) || current_user_can( 'manage_options' ) ) {
					$admin_group_ids = learndash_get_administrators_group_ids( get_current_user_id() );
					$user_id         = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
					$group_id        = filter_input( INPUT_POST, 'group_id', FILTER_SANITIZE_NUMBER_INT );

					if ( ! in_array( $group_id, $admin_group_ids ) ) {
						echo json_encode( array( 'error' => __( 'You are not the owner of this group', 'wdm_ld_group' ) ) );
						die();
					}
					if ( '' != $user_id && '' != $group_id ) {
						// Fetch enable/disable email setting
						$wdm_gr_reinvite_enable = get_option( 'wdm_gr_reinvite_enable' );
						if ( apply_filters( 'wdm_send_reinvite_email_status', true, $group_id ) && 'off' != $wdm_gr_reinvite_enable ) {
							$user_data   = get_user_by( 'id', $user_id );
							$group_title = get_the_title( $group_id );
							$leader_data = get_user_by( 'id', get_current_user_id() );

							$user_login = $user_data->user_login;

							// Calculation for Reset Password link.
							global $wpdb;
							$key       = get_password_reset_key( $user_data );
							$reset_arg = array(
								'action' => 'rp',
								'key'    => $key,
								'login'  => rawurlencode( $user_login ),
							);

							$reset_password_link = add_query_arg( $reset_arg, network_site_url( 'wp-login.php', 'login' ) );

							// fetch enrolled courses.
							$courses         = learndash_group_enrolled_courses( $group_id, true );
							$enrolled_course = array();
							foreach ( $courses as $key => $value ) {
								$enrolled_course[] = get_the_title( $value );
								$url               = get_permalink( $value );
								unset( $key );
							}

							$tsub = get_option( 'wdm-reinvite-sub' );
							if ( empty( $tsub ) ) {
								$tsub = WDM_REINVITE_SUB;
							}
							$subject = stripslashes( $tsub );
							$subject = str_replace( '{group_title}', get_the_title( $group_id ), $subject );
							$subject = str_replace( '{site_name}', get_bloginfo(), $subject );
							$subject = str_replace( '{user_first_name}', ucfirst( $user_data->first_name ), $subject );
							$subject = str_replace( '{user_last_name}', ucfirst( $user_data->last_name ), $subject );
							$subject = str_replace( '{user_email}', $user_data->user_email, $subject );
							$subject = str_replace( '{reset_password}', $reset_password_link, $subject );
							$subject = str_replace( '{course_list}', $this->get_course_list_html( $enrolled_course, $group_id, $user_id ), $subject );
							$subject = str_replace( '{group_leader_name}', ucfirst( strtolower( $leader_data->first_name ) ) . ' ' . ucfirst( strtolower( $leader_data->last_name ) ), $subject );
							$subject = str_replace( '{login_url}', wp_login_url(), $subject );
							$subject = apply_filters( 'wdm_reinvite_email_subject', $subject, $group_id, get_current_user_id(), $user_id );

							$tbody = get_option( 'wdm-reinvite-body' );
							if ( empty( $tbody ) ) {
								$tbody = WDM_REINVITE_BODY;
							}

							$body = stripslashes( $tbody );
							// $body = $reset_password_link;
							$body = str_replace( '{group_title}', get_the_title( $group_id ), $body );
							$body = str_replace( '{site_name}', get_bloginfo(), $body );
							$body = str_replace( '{user_first_name}', ucfirst( $user_data->first_name ), $body );
							$body = str_replace( '{user_last_name}', ucfirst( $user_data->last_name ), $body );
							$body = str_replace( '{user_email}', $user_data->user_email, $body );
							$body = str_replace( '{reset_password}', $reset_password_link, $body );
							$body = str_replace( '{course_list}', $this->get_course_list_html( $enrolled_course, $group_id, $user_id ), $body );
							$body = str_replace( '{group_leader_name}', ucfirst( strtolower( $leader_data->first_name ) ) . ' ' . ucfirst( strtolower( $leader_data->last_name ) ), $body );
							$body = str_replace( '{login_url}', wp_login_url(), $body );

							$body = apply_filters( 'wdm_reinvite_email_body', $body, $group_id, get_current_user_id(), $user_id );

							ldgr_send_group_mails(
								$user_data->user_email,
								$subject,
								$body,
								array(),
								array(),
								array(
									'email_type' => 'WDM_REINVITE_BODY',
									'group_id'   => $group_id,
								)
							);

							echo json_encode(
								array(
									'success' => __( 'Re Invitation mail has been sent successfully.', 'wdm_ld_group' ),
								)
							);
						}
						die();
					} else {
						echo json_encode( array( 'error' => __( 'Oops Something went wrong', 'wdm_ld_group' ) ) );
						die();
					}
				} else {
					echo json_encode( array( 'error' => __( "You don't have privilege to do this action", 'wdm_ld_group' ) ) );
				}
			} else {
				echo json_encode( array( 'error' => __( "You don't have privilege to do this action", 'wdm_ld_group' ) ) );
			}
			die();
		}

		/**
		 * Upload users from CSV via ajax
		 */
		public function ajax_upload_users_from_csv() {

			if ( ! is_user_logged_in() ) {
				echo json_encode(
					array(
						'error' => __( "You don't have privilege to do this action", 'wdm_ld_group' ),
					)
				);
				die();
			}

			$user_id = get_current_user_id();
			if ( ! $this->check_if_group_leader( $user_id ) ) {
				echo json_encode(
					array(
						'error' => __( "You don't have privilege to do this action", 'wdm_ld_group' ),
					)
				);
				die();
			}

			$step       = filter_input( INPUT_POST, 'step', FILTER_SANITIZE_NUMBER_INT );
			$percentage = 0;

			$enrolled_users = array();
			$enrolled_users = $this->ldgr_upload_csv( $step, $percentage );
			$results        = array();

			if ( ! empty( $enrolled_users ) ) {
				$group_id              = filter_input( INPUT_POST, 'wdm_group_id', FILTER_SANITIZE_NUMBER_INT );
				$enrolled_users_list   = $this->get_enrolled_users_list( $enrolled_users, $group_id );
				$results['users']      = $enrolled_users_list;
				$results['step']       = $step;
				$results['percentage'] = $percentage;
			}
			if ( defined( 'WDM_ERROR_MESSAGE' ) ) {
				$results['error'] = WDM_ERROR_MESSAGE;
			}

			if ( defined( 'WDM_SUCCESS_MESSAGE' ) ) {
				$results['update'] = WDM_SUCCESS_MESSAGE;
			}

			echo json_encode( $results );
			die();
		}

		/**
		 * Get List of Enrolled users
		 *
		 * @param array $enrolled_users         List of users to be enrolled.
		 * @param int   $group_id               ID of the group to enroll the users in.
		 *
		 * @return array    $enrolled_users_list    List of enrolled users
		 */
		public function get_enrolled_users_list( $enrolled_users, $group_id ) {
			$enrolled_users_list = array();
			if ( empty( $enrolled_users ) || empty( $group_id ) ) {
				return $enrolled_users_list;
			}

			$default                            = array( 'removal_request' => array() );
			$removal_request['removal_request'] = maybe_unserialize( get_post_meta( $group_id, 'removal_request', true ) );
			$removal_request                    = array_filter( $removal_request );

			$removal_request = wp_parse_args( $default, $removal_request );
			$removal_request = $removal_request['removal_request'];

			$ldgr_reinvite_user  = get_option( 'ldgr_reinvite_user' );
			$reinvite_class_data = 'wdm-reinvite';
			$reinvite_text_data  = apply_filters( 'wdm_change_reinvite_label', __( 'Re-Invite', 'wdm_ld_group' ) );

			foreach ( $enrolled_users as $user_id ) {
				$user_data = get_user_by( 'id', $user_id );

				$user_name  = get_user_meta( $user_id, 'first_name', true ) . ' ' . get_user_meta( $user_id, 'last_name', true );
				$user_email = $user_data->user_email;

				if ( ! in_array( $user_id, $removal_request ) ) {
					$class_data = 'wdm_remove';
					$text_data  = __( 'Remove', 'wdm_ld_group' );
				} else {
					$class_data = 'request_sent';
					$text_data  = __( 'Request sent', 'wdm_ld_group' );
				}

				$action = '';
				if ( $ldgr_reinvite_user == 'on' ) {
					$action = "<a 
					href='#'
					data-user_id ='$user_id'
					data-group_id='$group_id'
					class='$reinvite_class_data button'>$reinvite_text_data</a>&nbsp;";
				}

				if ( apply_filters( 'wdm_ldgr_remove_user_button', true, $user_id, $group_id ) ) {
					$action .= "<a 
					href='#'
					data-user_id ='$user_id'
					data-group_id='$group_id'
					class='$class_data button'>$text_data</a>";
				}

				$checkbox = "<input type='checkbox' name='bulk_select' data-user_id ='$user_id' data-group_id='$group_id'>";

				$enrolled_users_list[] = apply_filters(
					'ldgr_ajax_upload_user_each',
					array(
						$checkbox,
						$user_name,
						$user_email,
						$action,
					),
					$user_id,
					$group_id
				);

				$user_name = $user_email = $action = '';
			}

			return apply_filters( 'ldgr_ajax_upload_user_list', $enrolled_users_list, $group_id );
		}

		/**
		 * Enqueue scripts for the group registration page shortcode
		 *
		 * @param int $group_id  ID of the group.
		 */
		public function enqueue_group_users_display_shortcode_scripts( $group_id ) {
			self::enqueue_data_table();

			wp_enqueue_style(
				'wdm_datatable_css',
				plugins_url(
					'css/datatables.min.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);
			wp_enqueue_style(
				'wdm_style_css',
				plugins_url(
					'css/style.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);
			wp_enqueue_style(
				'wdm_snackbar_css',
				plugins_url(
					'css/wdm-snackbar.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);

			wp_register_script(
				'wdm_remove_js',
				plugins_url( 'js/wdm_remove.js', dirname( __FILE__ ) ),
				array( 'jquery' ),
				LD_GROUP_REGISTRATION_VERSION
			);

			wp_register_script(
				'snackbar_js',
				plugins_url( 'js/snackbar.js', dirname( __FILE__ ) ),
				array( 'jquery' ),
				LD_GROUP_REGISTRATION_VERSION
			);

			$data = array(
				'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
				// 'group_id'                  => get_user_meta(get_current_user_id(), 'wdm_group_users_limit_'.$group_id, true),
				'group_limit'              => get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true ),
				'is_unlimited'             => get_post_meta( $group_id, 'ldgr_unlimited_seats', 1 ),
				'admin_approve'            => get_option( 'ldgr_admin_approval' ),
				'ajax_loader'              => plugins_url( 'media/ajax-loader.gif', dirname( __FILE__ ) ),
				'request_sent'             => __( 'Request sent', 'wdm_ld_group' ),
				'remove_html'              => '<a href="#" class="wdm_remove_add_user" title=' . __( 'Delete', 'wdm_ld_group' ) . '><span class="dashicons dashicons-no"></span></a>',
				'user_limit'               => __( 'User limit exceeded', 'wdm_ld_group' ),
				'student_singular'         => __( 'the student', 'wdm_ld_group' ),
				'student_plural'           => __( 'the students', 'wdm_ld_group' ),
				'no_user_selected'         => __( 'No user selected', 'wdm_ld_group' ),
				'are_you_sure'             => __( "Are you sure you want to remove the following user from the group? \n\n\t {user}", 'wdm_ld_group' ),
				'are_you_sure_plural'      => __( 'Are you sure you want to remove the selected users from the group?', 'wdm_ld_group' ),
				'only_csv_file_allowed'    => __( 'Only CSV file allowed!', 'wdm_ld_group' ),
				'no_matching_record_found' => __( 'No matching records found', 'wdm_ld_group' ),
				'search'                   => __( 'Search', 'wdm_ld_group' ),
				'processing'               => __( 'Processing...', 'wdm_ld_group' ),
				'loading'                  => __( 'Loading...', 'wdm_ld_group' ),
				'no_user_is_enrolled'      => __( 'No user is enrolled', 'wdm_ld_group' ),
				// translators: For menu.
				'length_menu'              => sprintf( __( 'Show %s Users', 'wdm_ld_group' ), '_MENU_' ),
				'of'                       => __( 'of', 'wdm_ld_group' ),
				// translators: For max total entries.
				'info_filtered'            => sprintf( __( '(filtered from %s total entries)', 'wdm_ld_group' ), '_MAX_' ),
			);
			wp_localize_script( 'wdm_remove_js', 'wdm_data', $data );

			wp_enqueue_script( 'wdm_remove_js' );
			wp_enqueue_script( 'snackbar_js' );

			// Enqueue Re-Invite javascript.
			wp_enqueue_script(
				'wdm_reinvite_js',
				plugins_url(
					'js/reinvite.js',
					dirname( __FILE__ )
				),
				array( 'jquery', 'wdm_remove_js' ),
				LD_GROUP_REGISTRATION_VERSION
			);
			wp_enqueue_script(
				'ldgr_group_settings',
				plugins_url(
					'js/ldgr-group-settings.js',
					dirname( __FILE__ )
				),
				array( 'jquery' ),
				LD_GROUP_REGISTRATION_VERSION
			);
			wp_localize_script(
				'ldgr_group_settings',
				'ldgr_loc',
				array(
					'ajax_url'           => admin_url( 'admin-ajax.php' ),
					'invalid_group_name' => __( 'Please enter a valid group name', 'wdm_ld_group' ),
					'invalid_group_id'   => __( 'Some error occurred, group id not found. Please refresh the page and try again', 'wdm_ld_group' ),
					'common_error'       => __( 'Some error occurred', 'wdm_ld_group' ),
				)
			);
		}

		/**
		 * Update group details via ajax
		 *
		 * @since 3.2.0
		 */
		public function ajax_update_group_details() {
			if ( array_key_exists( 'action', $_POST ) && 'ldgr_update_group_details' == $_POST['action'] ) {
				$group_id   = intval( $_POST['group_id'] );
				$group_name = $_POST['group_name'];
				if ( empty( $group_id ) || empty( $group_name ) ) {
					echo json_encode(
						array(
							'status'  => 'error',
							'message' => __( 'Group name or Group ID not found', 'wdm_ld_group' ),
						)
					);
					die();
				}

				$update = wp_update_post(
					array(
						'ID'         => $group_id,
						'post_title' => $group_name,
					)
				);

				if ( empty( $update ) ) {
					echo json_encode(
						array(
							'status'  => 'error',
							'message' => __( 'Group name could not be updated. Please try again later or contact admin', 'wdm_ld_group' ),
						)
					);
					die();
				}

				echo json_encode(
					array(
						'status'  => 'success',
						'message' => __( 'Group name updated successfully!!', 'wdm_ld_group' ),
					)
				);
			}
			die();
		}

		/**
		 * Display notification messages.
		 */
		public function show_notification_messages() {
			if ( defined( 'WDM_SUCCESS_MESSAGE' ) ) {
				?>
				<div class = 'wdm-update-message'>
					<?php echo WDM_SUCCESS_MESSAGE; ?>
				</div>
				<?php
			}

			if ( defined( 'WDM_ERROR_MESSAGE' ) ) {
				?>
				<div class = 'wdm-error-message'>
					<?php echo WDM_ERROR_MESSAGE; ?>
				</div>
				<?php
			}
		}

		/**
		 * Show group registration page select wrapper
		 *
		 * @param int    $group_id                 ID of the group
		 * @param int    $subscription_id          ID of the subscription, if any
		 * @param bool   $need_to_restrict        Need to restrict the content.
		 * @param string $sub_current_status    Subscription current status, if any.
		 */
		public function show_group_select_wrapper( $group_id, $subscription_id, $need_to_restrict, $sub_current_status ) {
			$user_id            = get_current_user_id();
			$group_ids          = ldgr_get_leader_group_ids( $user_id );
			$user_data          = get_user_by( 'id', $user_id );
			$ldgr_group_courses = get_option( 'ldgr_group_courses' );
			$group_courses      = array();

			if ( 'on' == $ldgr_group_courses ) {
				$group_courses = learndash_group_enrolled_courses( $group_id );
			}

			/**
			 * Filter the list of courses in the group on groups dashboard.
			 *
			 * @param array $group_courses  List of courses in the group.
			 * @param int $group_id         ID of the group.
			 *
			 * @since 4.1.5
			 */
			$group_courses = apply_filters( 'ldgr_filter_group_course_list', $group_courses, $group_id );

			$group_limit     = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
			$grp_limit_count = $this->get_group_limit_number( $group_limit );
			$is_unlimited    = get_post_meta( $group_id, 'ldgr_unlimited_seats', 1 );


			$args = array(
				'group_id'                     => $group_id,
				'subscription_id'              => $subscription_id,
				'need_to_restrict'             => $$need_to_restrict,
				'sub_current_status'           => $sub_current_status,
				'user_id'                      => $user_id,
				'group_ids'                    => $group_ids,
				'user_data'                    => $user_data,
				'ldgr_group_courses'           => $ldgr_group_courses,
				'group_courses'                => $group_courses,
				'group_limit'                  => $group_limit,
				'grp_limit_count'              => $grp_limit_count,
				'is_unlimited'                 => $is_unlimited,
				'Ld_Group_Registration_Groups' => $this,

			);
			ldgr_get_template(
				plugin_dir_path( dirname( __FILE__ ) ) . 'templates/ldgr-group-users/ldgr-group-users-select-wrapper.template.php',
				$args,
				false
			);
		}

		/**
		 * Display group select box
		 *
		 * @param int   $group_id     ID of the group.
		 * @param array $group_ids  List of all groups.
		 * @param obj   $user_data    User data object of group leader.
		 */
		public function display_group_select_list_html( $group_id, $group_ids, $user_data ) {
			foreach ( $group_ids as $value ) {
				$demo_title  = get_post( $value );
				$group_title = $demo_title->post_title;
				$username    = $user_data->user_login;
				$title       = str_replace( $username . ' - ', '', $group_title );
				$group_id    = $this->get_selected_group_value( $group_id, $value );
				$title       = apply_filters( 'wdm_modify_ldgr_group_title', $title, $value );
				?>
					<option value="<?php echo esc_html( $value ); ?>" <?php selected( $value, $group_id ); ?>>
						<?php echo esc_html( $title ); ?>
					</option>
				<?php
			}
		}

		/**
		 * Show group registration tabs.
		 *
		 * @param int  $group_id             ID of the group.
		 * @param bool $need_to_restrict    Whether there is a need to restrict any content.
		 */
		public function show_group_registrations_tabs( $group_id, $need_to_restrict ) {
			$group_limit  = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
			$is_unlimited = get_post_meta( $group_id, 'ldgr_unlimited_seats', 1 );

			// If unlimited seats but empty group limit then set group limit for pre-requisites.
			if ( $is_unlimited && $group_limit <= 0 ) {
				$group_limit = 1;
			}

			/**
			 * Filter the list of users to be displayed on any of the groups dashboard tabs.
			 *
			 * @since 4.1.4
			 *
			 * @param array $users      List of group users to be displayed for the group.
			 * @param int   $group_id   ID of the current LD group.
			 */
			$users = apply_filters( 'ldgr_filter_tab_user_list', learndash_get_groups_user_ids( $group_id ), $group_id );

			$tab_headers = array(
				array(
					'title' => __( 'Enrolled Users', 'wdm_ld_group' ),
					'slug'  => 'wdm_enrolled_users_label',
					'icon'  => plugin_dir_url( dirname( __FILE__ ) ) . 'media/enrolled-users.png',
					'id'    => 1,
				),
				array(
					'title'   => __( 'Enroll New User', 'wdm_ld_group' ),
					'slug'    => 'wdm_add_users_label',
					'icon'    => plugin_dir_url( dirname( __FILE__ ) ) . 'media/add-user.png',
					'pre_req' => array(
						'key'   => $group_limit,
						'value' => 0,
						'check' => 'greater',    // greater, lesser and equall
					),
					'id'      => 2,
				),
				array(
					'title' => __( 'Report', 'wdm_ld_group' ),
					'slug'  => 'wdm_ldgr_view_report_label',
					'icon'  => plugin_dir_url( dirname( __FILE__ ) ) . 'media/report.svg',
					'id'    => 3,
				),
			);

			$tab_headers = apply_filters( 'ldgr_filter_group_registration_tab_headers', $tab_headers, $group_id );

			$tab_contents = array(
				array(
					'id'       => 1,
					'active'   => true,
					'template' => plugin_dir_path(
						dirname( __FILE__ )
					) . 'templates/ldgr-group-users/tabs/enrolled-users-tab.template.php',
				),
				array(
					'id'       => 2,
					'active'   => false,
					'template' => plugin_dir_path(
						dirname( __FILE__ )
					) . 'templates/ldgr-group-users/tabs/new-users-tab.template.php',
				),
				array(
					'id'       => 3,
					'active'   => false,
					'template' => plugin_dir_path(
						dirname( __FILE__ )
					) . 'templates/ldgr-group-users/tabs/reports-tab.template.php',
				),
			);

			$tab_contents = apply_filters( 'ldgr_filter_group_registration_tab_contents', $tab_contents, $group_id );

			$clear_icon = plugin_dir_url( dirname( __FILE__ ) ) . 'media/clear.png';

			include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/ldgr-group-users/ldgr-group-users-tabs.template.php';
		}

		/**
		 * Check if a tab has any pre-requisites or not on group registration page.
		 *
		 * @param array $tab_header     Details about the tab to check.
		 *
		 * @return boolean              False if not pre-req or tab required, true otherwise.
		 */
		public function not_required_tab( $tab_header ) {
			if ( ! array_key_exists( 'pre_req', $tab_header ) ) {
				return false;
			}

			$pre_req = true;

			$condition = $tab_header['pre_req'];

			switch ( $condition['check'] ) {
				case 'greater':
					if ( $condition['key'] > $condition['value'] ) {
						$pre_req = false;
					}
					break;

				case 'lesser':
					if ( $condition['key'] < $condition['value'] ) {
						$pre_req = false;
					}
					break;

				case 'equall':
					if ( $condition['key'] == $condition['value'] ) {
						$pre_req = false;
					}
					break;
			}

			return apply_filters( 'ldgr_filter_pre_requisite_tab_check', $pre_req, $tab_header );
		}

		/**
		 * Get selected group name
		 *
		 * @param int $group_id     ID of the group.
		 * @param obj $user_data    User details.
		 *
		 * @return string           Selected group name title.
		 */
		public function get_selected_group_name( $group_id, $user_data ) {
			if ( empty( $group_id ) ) {
				return '';
			}

			$group_title = filter_var( get_the_title( $group_id ), FILTER_SANITIZE_SPECIAL_CHARS );

			if ( empty( $user_data ) ) {
				return $group_title;
			}

			$group_title = str_replace( array( '&#38;#8211;', $user_data->user_login ), '', $group_title );

			return trim( $group_title );
		}

		/**
		 * Validate CSV file
		 *
		 * @param array $csv_file  CSV file details.
		 *
		 * @return boolean         True if valid CSV file, else false.
		 */
		public function check_if_valid_csv_file( $csv_file ) {
			$msg = '';

			$file_name = $csv_file['uploadcsv']['tmp_name'];

			$ext = pathinfo( $csv_file['uploadcsv']['name'], PATHINFO_EXTENSION );

			if ( '' == $file_name || null == $file_name ) {
				$msg = __( 'No files chosen to upload!', 'wdm_ld_group' );
			}
			if ( 'csv' != $ext ) {
				$msg = __( 'Only CSV file is allowed!', 'wdm_ld_group' );
			}

			return apply_filters( 'ldgr_filter_csv_file_validation', $msg, $csv_file );
		}

		/**
		 * Get CSV data
		 *
		 * @param array $csv_file       CSV file all details.
		 * @param int   $group_id       ID of the group.
		 * @param int   $step           Current batch processing step (only used in patch processing)
		 * @param int   $batch_length   Length of batch in batch processing (only used in patch processing)
		 *
		 * @return array             Extracted CSV file details.
		 */
		public function get_csv_data_list( $csv_file, $group_id, $step = 1, $batch_length = 0 ) {

			$file_name = $csv_file['uploadcsv']['tmp_name'];

			$csv_data_list = array(
				'emails'      => array(),
				'first_names' => array(),
				'last_names'  => array(),
			);

			$group_limit  = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
			$is_unlimited = get_post_meta( $group_id, 'ldgr_unlimited_seats', 1 );

			ini_set( 'auto_detect_line_endings', true );

			$allowed_columns = apply_filters( 'ldgr_filter_allowed_csv_columns', 3, $file_name, $group_id );
			$file            = fopen( $file_name, 'r' );
			$count           = 0;
			while ( ( $data = fgetcsv( $file, 1000, ',' ) ) !== false ) {
				if ( 0 == $count ) {
					$count++;
				} else {
					// @todo: Check if we can secure incoming CSV in any way here.
					// $data = array_map( 'utf8_encode', $data );
					$count++;
					if ( count( $data ) != $allowed_columns ) {
						$msg = __( 'Value is not in a proper format, check sample file for format!', 'wdm_ld_group' );
						return array( 'error' => $msg );
					}

					if ( ! $this->valid_csv_data( $data, $allowed_columns ) ) {
						$msg = __( 'One of the value is missing!', 'wdm_ld_group' );
						return array( 'error' => $msg );
					}

					$csv_data_list['first_names'][] = trim( $data[0] );
					$csv_data_list['last_names'][]  = trim( $data[1] );
					$csv_data_list['emails'][]      = trim( $data[2] );

					$csv_data_list = apply_filters( 'ldgr_filter_csv_data_list', $csv_data_list, $data, $group_id );
				}
			}
			fclose( $file );
			ini_set( 'auto_detect_line_endings', false );

			$csv_length = count( $csv_data_list['emails'] );

			if ( $step > 1 ) {
				$enrolled_users_count = ( $step - 1 ) * $batch_length;
				$csv_length           = intval( $csv_length - $enrolled_users_count );
			}

			if ( $csv_length > $group_limit && ! $is_unlimited ) {
				$msg = __( 'Your group Limit exceeded!!', 'wdm_ld_group' );
				return array( 'error' => $msg );
			}

			return $csv_data_list;
		}

		/**
		 * Validate CSV file data
		 *
		 * @param array $csv_data       CSV data to be validated.
		 * @param int   $allowed_columns  Allowed columns in the CSV to be read.
		 *
		 * @return boolean              True if CSV data valid, false otherwise.
		 */
		public function valid_csv_data( $csv_data, $allowed_columns ) {
			$column   = 0;
			$is_valid = true;

			while ( $column < $allowed_columns ) {
				if ( ! isset( $csv_data[ $column ] ) || empty( $csv_data[ $column ] ) ) {
					$is_valid = false;
					break;
				}
				$column++;
			}

			return apply_filters( $is_valid, 'ldgr_filter_valid_csv_data', $csv_data );
		}

		/**
		 * Send bulk upload emails
		 *
		 * @param array $all_emails_list     List of all emails to send emails to.
		 * @param int   $group_id            ID of the group.
		 */
		public function send_bulk_upload_emails( $all_emails_list, $group_id, $final_csv_data ) {
			global $success_data;

			if ( empty( $all_emails_list ) ) {
				return;
			}

			$all_emails_list = apply_filters( 'ldgr_filter_enroll_user_emails', $all_emails_list, $group_id, $final_csv_data );

			foreach ( $all_emails_list as $user_id => $details ) {
				if ( $details['new'] ) {
					if ( apply_filters( 'is_ldgr_default_user_add_action', true ) ) {
						$success_data = $this->new_user_registration(
							$user_id,
							$details['user_data']['first_name'],
							$details['user_data']['last_name'],
							$details['user_data']['user_email'],
							$details['user_data']['user_pass'],
							$details['courses'],
							$details['lead_user'],
							$details['group_id']
						);
					}
				} else {
					ldgr_send_group_mails(
						$details['email'],
						$details['subject'],
						$details['body'],
						array(),
						array(),
						array(
							'email_type' => 'WDM_U_ADD_GR_BODY',
							'group_id'   => $group_id,
						)
					);
				}
				do_action( 'ldgr_action_new_user_enroll', $user_id, $details );
			}
		}

		/**
		 * Display logo on group registration page for group leaders
		 *
		 * @since 4.1.0
		 */
		public function display_logo() {
			// Check whether to display logo or not
			$ldgr_logo_enabled = get_option( 'ldgr_logo_enabled', 1 );

			if ( 'on' != $ldgr_logo_enabled ) {
				return;
			}

			// Fetch logo url
			$ldgr_logo_url = get_option( 'ldgr_logo_url', 1 );

			if ( empty( $ldgr_logo_url ) ) {
				return;
			}

			ldgr_get_template(
				WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-group-leader-logo.template.php',
				array(
					'src' => $ldgr_logo_url,
				)
			);
		}
	}
}
