<?php
/**
 * Group Code Registration Module
 *
 * @since    4.1.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/classes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
namespace LdGroupRegistration\Modules\Classes;

use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Groups as Ld_Group_Registration_Groups;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Ld_Group_Registration_Group_Code_Registration' ) ) {
	/**
	 * Class LD Group Registration Group Code Registration
	 */
	class Ld_Group_Registration_Group_Code_Registration {
		/**
		 * Add group code registration shortcodes
		 *
		 * @since 4.1.0
		 */
		public function add_group_code_registration_shortcodes() {
			add_shortcode(
				'ldgr-group-code-registration-form',
				array( $this, 'handle_group_code_registration_shortcode_display' )
			);
		}
		/**
		 * Display the contents of the group code registration form shortcode - [ldgr-group-code-registration-form]
		 *
		 * @param array $atts   Shortcode attributes
		 * @since 4.1.0
		 */
		public function handle_group_code_registration_shortcode_display( $atts ) {
			$atts = shortcode_atts(
				array(
					'register' => true,
				),
				$atts,
				'ldgr-group-code-registration-form'
			);

			/**
			 * Allow 3rd party addons to filter attributes.
			 *
			 * @since 4.1.0
			 */
			$atts = apply_filters( 'ldgr_filter_group_code_registration_shortcode_attributes', $atts );

			// Check if user logged in.
			if ( is_user_logged_in() || 'false' === $atts['register'] ) {
				return $this->show_group_code_enrollment_form();
			} else {
				return $this->show_group_code_registration_form();
			}
		}
		/**
		 * Display the group code registration form for logged out users
		 *
		 * @since 4.1.0
		 */
		public function show_group_code_registration_form() {
			$enable_recaptcha           = get_option( 'ldgr_group_code_enable_recaptcha' );
			$ldgr_recaptcha_site_key    = get_option( 'ldgr_recaptcha_site_key' );
			$ldgr_enable_gdpr           = get_option( 'ldgr_enable_gdpr' );
			$ldgr_gdpr_checkbox_message = get_option( 'ldgr_gdpr_checkbox_message' );

			// Default gdpr checkbox message.
			if ( empty( $ldgr_gdpr_checkbox_message ) ) {
				/**
				 * Filter the default GDPR checkbox message.
				 *
				 * @since 4.1.3
				 *
				 * @param string $default_gdpr_message  The default GDPR checkbox message displayed if not updated.
				 */
				$default_gdpr_message = apply_filters(
					'ldgr_filter_default_gdpr_checkbox_message',
					'By using this form you agree with the storage and handling of your data by this website in accordance with our <a target="blank" href="{privacy_policy}">Privacy Policy</a>'
				);

				$ldgr_gdpr_checkbox_message = $default_gdpr_message;
			}

			// Perform privacy policy page link replaces.
			if ( false !== strpos( $ldgr_gdpr_checkbox_message, '{privacy_policy}' ) ) {
				$ldgr_gdpr_checkbox_message = str_replace( '{privacy_policy}', get_privacy_policy_url(), $ldgr_gdpr_checkbox_message );
			}

			return ldgr_get_template(
				WDM_LDGR_PLUGIN_DIR . '/modules/templates/group-code-screens/ldgr-group-code-registration-form.template.php',
				array(
					'enable_recaptcha'           => $enable_recaptcha,
					'ldgr_recaptcha_site_key'    => $ldgr_recaptcha_site_key,
					'ldgr_enable_gdpr'           => $ldgr_enable_gdpr,
					'ldgr_gdpr_checkbox_message' => $ldgr_gdpr_checkbox_message,
				),
				1
			);
		}
		/**
		 * Display the group code enrollment form for logged in users
		 *
		 * @since 4.1.0
		 */
		public function show_group_code_enrollment_form() {
			$enable_recaptcha           = get_option( 'ldgr_group_code_enable_recaptcha' );
			$ldgr_recaptcha_site_key    = get_option( 'ldgr_recaptcha_site_key' );
			$ldgr_enable_gdpr           = get_option( 'ldgr_enable_gdpr' );
			$ldgr_gdpr_checkbox_message = get_option( 'ldgr_gdpr_checkbox_message' );

			// Default gdpr checkbox message.
			if ( empty( $ldgr_gdpr_checkbox_message ) ) {
				/**
				 * Filter the default GDPR checkbox message.
				 *
				 * @since 4.1.3
				 *
				 * @param string $default_gdpr_message  The default GDPR checkbox message displayed if not updated.
				 */
				$default_gdpr_message = apply_filters(
					'ldgr_filter_default_gdpr_checkbox_message',
					'By using this form you agree with the storage and handling of your data by this website in accordance with our <a target="blank" href="{privacy_policy}">Privacy Policy</a>'
				);

				$ldgr_gdpr_checkbox_message = $default_gdpr_message;
			}

			// Perform privacy policy page link replaces.
			if ( false !== strpos( $ldgr_gdpr_checkbox_message, '{privacy_policy}' ) ) {
				$ldgr_gdpr_checkbox_message = str_replace( '{privacy_policy}', get_privacy_policy_url(), $ldgr_gdpr_checkbox_message );
			}

			return ldgr_get_template(
				WDM_LDGR_PLUGIN_DIR . '/modules/templates/group-code-screens/ldgr-group-code-enrollment-form.template.php',
				array(
					'enable_recaptcha'           => $enable_recaptcha,
					'ldgr_recaptcha_site_key'    => $ldgr_recaptcha_site_key,
					'ldgr_enable_gdpr'           => $ldgr_enable_gdpr,
					'ldgr_gdpr_checkbox_message' => $ldgr_gdpr_checkbox_message,
				),
				1
			);
		}
		/**
		 * Enqueue group code registration styles and scripts
		 *
		 * @since 4.1.0
		 */
		public function enqueue_group_code_registration_scripts() {
			global $post;

			if ( empty( $post ) || ! has_shortcode( $post->post_content, 'ldgr-group-code-registration-form' ) ) {
				return;
			}

			wp_enqueue_style(
				'ldgr-group-code-registration-styles',
				plugins_url(
					'css/ldgr-group-code-registration-styles.css',
					dirname( __FILE__ )
				),
				array( 'dashicons' ),
				LD_GROUP_REGISTRATION_VERSION
			);

			wp_enqueue_script(
				'ldgr-group-code-registration-script',
				plugins_url(
					'js/ldgr-group-code-registration-script.js',
					dirname( __FILE__ )
				),
				array( 'jquery' ),
				LD_GROUP_REGISTRATION_VERSION
			);

			wp_localize_script(
				'ldgr-group-code-registration-script',
				'group_code_reg_loc',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
		}
		/**
		 * Handle the submission of the group code registration form
		 *
		 * @since 4.1.0
		 */
		public function ajax_submit_group_code_reg_form() {
			if ( ! empty( $_POST ) && array_key_exists( 'action', $_POST ) && 'ldgr-submit-group-code-reg-form' == $_POST['action'] ) {
				// Authenticate valid request.
				check_ajax_referer( 'ldgr-group-code-registration-form', 'nonce' );

				$form_data = array();
				parse_str( $_POST['form'], $form_data );

				// Check if user registration enabled.
				$users_can_register = get_option( 'users_can_register' );
				if ( ! $users_can_register ) {
					echo wp_json_encode(
						array(
							'type' => 'error',
							'msg'  => __( 'User registrations are currently disabled on this website. Please contact admin for more details', WDM_LDGR_TXT_DOMAIN ),
						)
					);
					wp_die();
				}

				// Check for recaptcha.
				$enable_recaptcha = get_option( 'ldgr_group_code_enable_recaptcha' );
				if ( 'on' == $enable_recaptcha ) {
					if ( $invalid = $this->is_invalid_recaptcha_response( $form_data ) ) {
						echo wp_json_encode(
							array(
								'type' => 'error',
								'msg'  => $invalid,
							)
						);
						wp_die();
					}
				}

				// Validate form data.
				if ( $invalid = $this->is_invalid_group_reg_form_input( $form_data ) ) {
					echo wp_json_encode(
						array(
							'type' => 'error',
							'msg'  => $invalid,
						)
					);
					wp_die();
				}

				// Verify group code.
				if ( $invalid = $this->is_invalid_group_code( $form_data ) ) {
					echo wp_json_encode(
						array(
							'type' => 'error',
							'msg'  => $invalid,
						)
					);
					wp_die();
				}

				// Enroll user.
				if ( ! $this->create_and_enroll_user( $form_data ) ) {
					echo wp_json_encode(
						array(
							'type' => 'error',
							'msg'  => __(
								'Some error occurred while creating user and enrolling user in group. Please contact admin',
								WDM_LDGR_TXT_DOMAIN
							),
						)
					);
					wp_die();
				}

				// Create success data.
				$success_data = $this->get_success_response_data( $form_data );

				echo wp_json_encode( $success_data );
			}

			wp_die();
		}
		/**
		 * Validate the group code registration form
		 *
		 * @param array $form_data  Registration form data.
		 * @return mixed            Error message if form data invalid, false otherwise.
		 *
		 * @since 4.1.0
		 */
		public function is_invalid_group_reg_form_input( $form_data ) {
			// Check if empty
			if ( empty( $form_data ) ) {
				return __( 'Empty Form Details', WDM_LDGR_TXT_DOMAIN );
			}

			// Get form data
			$first_name       = trim( $form_data['ldgr-user-first-name'] );
			$last_name        = trim( $form_data['ldgr-user-last-name'] );
			$user_name        = trim( $form_data['ldgr-user-username'] );
			$user_email       = trim( $form_data['ldgr-user-email'] );
			$user_password    = trim( $form_data['ldgr-user-password'] );
			$confirm_password = trim( $form_data['ldgr-user-confirm-password'] );
			$code_string      = trim( $form_data['ldgr-user-group-code'] );

			// Name validation
			if ( empty( $first_name ) || empty( $last_name ) ) {
				return __( 'Empty First Name and/or Last Name', WDM_LDGR_TXT_DOMAIN );
			}

			// Username validation
			if ( empty( $user_name ) || username_exists( $user_name ) ) {
				return __( 'Empty Username or entered username already exists, please enter another', WDM_LDGR_TXT_DOMAIN );
			}

			// Email validation
			if ( empty( $user_email ) || ! is_email( $user_email ) ) {
				return __( 'Please enter a valid email address.', WDM_LDGR_TXT_DOMAIN );
			}

			// Check if email exists
			if ( email_exists( $user_email ) ) {
				return __( 'Email address already registered, please login to continue.', WDM_LDGR_TXT_DOMAIN );
			}

			// Check password same
			if ( empty( $user_password ) || 0 !== strcmp( $user_password, $confirm_password ) ) {
				return __( 'Password must not be empty and both passwords must be exactly the same', WDM_LDGR_TXT_DOMAIN );
			}

			// Check code string
			if ( empty( $code_string ) ) {
				return __( 'Please enter valid group code', WDM_LDGR_TXT_DOMAIN );
			}

			return false;
		}
		/**
		 * Validate and verify if proper group code
		 *
		 * @param array $form_data
		 * @return bool
		 * @since 4.1.0
		 */
		public function is_invalid_group_code( $form_data ) {
			$code_string = trim( $form_data['ldgr-user-group-code'] );
			$group_code  = get_page_by_title( $code_string, OBJECT, 'ldgr_group_code' );

			// If no group code found
			if ( empty( $group_code ) ) {
				return __( 'Please enter valid Group Code', WDM_LDGR_TXT_DOMAIN );
			}

			// Check if active
			if ( 'publish' !== $group_code->post_status ) {
				return __( 'Group Code not activated, please try again later', WDM_LDGR_TXT_DOMAIN );
			}

			// Check if enrollments allowed
			$allowed_enrollments = get_post_meta( $group_code->ID, 'group_code_enrollment_count', 1 );
			if ( empty( $allowed_enrollments ) ) {
				return __( 'No more enrollments allowed for this Group Code', WDM_LDGR_TXT_DOMAIN );
			}

			// Get related groups
			$related_groups = get_post_meta( $group_code->ID, 'group_code_related_groups', 1 );
			if ( empty( $related_groups ) ) {
				return __( 'No groups found related to this group code, please contact group leader or administrator', WDM_LDGR_TXT_DOMAIN );
			}

			// Get group seat limit
			$group_seats_remaining = get_post_meta( $related_groups, 'wdm_group_users_limit_' . $related_groups, 1 );
			$is_unlimited          = get_post_meta( $related_groups, 'ldgr_unlimited_seats', 1 );
			if ( empty( $group_seats_remaining ) && ! $is_unlimited ) {
				return __( 'No available seats found in the group, please contact group leader or administrator', WDM_LDGR_TXT_DOMAIN );
			}

			// Check enrollment date range.

			// Check from date.
			$code_valid_from = get_post_meta( $group_code->ID, 'group_code_from', 1 );

			if ( time() < $code_valid_from ) {
				return __( 'This Group Code is scheduled to be activated at a later time, please try again later', WDM_LDGR_TXT_DOMAIN );
			}

			// Check to date.
			$code_valid_to = get_post_meta( $group_code->ID, 'group_code_to', 1 );
			if ( time() > $code_valid_to ) {
				return __( 'Sorry but this Group Code has expired', WDM_LDGR_TXT_DOMAIN );
			}

			// Check for validation rules
			$code_validation_check = get_post_meta( $group_code->ID, 'group_code_validation_check', 1 );
			if ( 'on' == $code_validation_check ) {
				// Validate IP address
				$validation_ip = get_post_meta( $group_code->ID, 'group_code_ip_validation', 1 );
				$client_ip     = $this->get_client_ip_address();
				if ( ! empty( $validation_ip ) && ! empty( $client_ip ) && ( 0 != strcmp( $client_ip, $validation_ip ) ) ) {
					return __( 'IP Address validation failed', WDM_LDGR_TXT_DOMAIN );
				}

				// Validate email domain name
				$validation_domain = get_post_meta( $group_code->ID, 'group_code_domain_validation', 1 );
				$user_email        = trim( $form_data['ldgr-user-email'] );

				// Check if logged in user
				if ( empty( $user_email ) && is_user_logged_in() ) {
					$current_user = wp_get_current_user();
					$user_email   = $current_user->user_email;
				}
				$user_email_domain = $this->extract_email_domain( $user_email );
				if ( ! empty( $validation_domain ) && ! empty( $user_email_domain ) && ( 0 != strcmp( $validation_domain, $user_email_domain ) ) ) {
					return __( 'Domain name validation failed', WDM_LDGR_TXT_DOMAIN );
				}
			}

			/**
			 * Filter whether group code form data is valid.
			 *
			 * @param mixed $is_invalid     True if form data invalid else error message string.
			 * @param array $form_data      List of form data.
			 *
			 * @since 4.1.5
			 */
			return apply_filters( 'ldgr_filter_is_invalid_group_code', false, $form_data );
		}
		/**
		 * Create new WP User and enroll in group
		 *
		 * @param array $form_data
		 * @return int
		 * @since 4.1.0
		 */
		public function create_and_enroll_user( $form_data ) {
			if ( empty( $form_data ) ) {
				return false;
			}

			// User Details.
			$userdata = array(
				'user_login' => trim( $form_data['ldgr-user-username'] ),
				'user_email' => trim( $form_data['ldgr-user-email'] ),
				'first_name' => trim( $form_data['ldgr-user-first-name'] ),
				'last_name'  => trim( $form_data['ldgr-user-last-name'] ),
				'user_pass'  => trim( $form_data['ldgr-user-password'] ),
			);

			// Group Code.
			$code_string = trim( $form_data['ldgr-user-group-code'] );

			// Fetch Group Code Details.
			$group_code = get_page_by_title( $code_string, OBJECT, 'ldgr_group_code' );

			/**
			 * Allow 3rd party plugins to alter userdata before the new user is created
			 *
			 * @since 4.1.0
			 */
			$userdata = apply_filters( 'ldgr_filter_new_user_details', $userdata, $form_data );

			// Create the new user.
			$member_user_id   = wp_insert_user( $userdata );
			$related_group_id = get_post_meta( $group_code->ID, 'group_code_related_groups', 1 );

			/**
			 * Perform actions after a new user is created and enrolled using group code
			 *
			 * @since 4.1.0
			 *
			 * @param int       $member_user_id     ID of the new user created.
			 * @param object    $group_code         Group Code object used for registration.
			 * @param array     $form_data          Data of the form submitted during registration.
			 */
			do_action( 'ldgr_action_group_code_user_created', $member_user_id, $group_code, $form_data );

			// Send user enrollment emails.
			$emails_list                    = array();
			$emails_list[ $member_user_id ] = array(
				'email'     => $userdata['user_email'],
				'new'       => true,
				'group_id'  => $related_group_id,
				'user_data' => $userdata,
				'courses'   => learndash_group_enrolled_courses( $related_group_id ),
				'lead_user' => get_userdata( $group_code->post_author ),
			);

			// Get groups class instance.
			$instance = Ld_Group_Registration_Groups::get_instance();
			$instance->send_bulk_upload_emails( $emails_list, $related_group_id, $form_data );

			// Update Group Limit.
			$group_limit = get_post_meta( $related_group_id, 'wdm_group_users_limit_' . $related_group_id, 1 );

			// If unlimited seats then no need to update.
			$is_unlimited = get_post_meta( $related_group_id, 'ldgr_unlimited_seats', 1 );
			if ( ! $is_unlimited ) {
				update_post_meta( $related_group_id, 'wdm_group_users_limit_' . $related_group_id, intval( $group_limit - 1 ) );
			}

			// Update group code enrollment limit.
			$allowed_enrollments = get_post_meta( $group_code->ID, 'group_code_enrollment_count', 1 );
			update_post_meta( $group_code->ID, 'group_code_enrollment_count', intval( $allowed_enrollments - 1 ) );

			if ( $group_limit <= 0 && ! $is_unlimited ) {
				do_action( 'wdm_group_limit_is_zero', $related_group_id );
			}

			$group_leader_ids = learndash_get_groups_administrator_ids( $related_group_id );

			do_action(
				'ld_group_postdata_updated',
				$related_group_id,
				$group_leader_ids,
				array( $member_user_id ),
				learndash_group_enrolled_courses( $related_group_id )
			);

			return $member_user_id;
		}
		/**
		 * Enroll the user into the group
		 *
		 * @param int    $user_id
		 * @param array  $form_data
		 * @param object $group_code
		 * @param int    $group_id
		 *
		 * @since 4.1.0
		 */
		public function enroll_user_to_group( $user_id, $form_data, $group_code, $group_id ) {
			// Group Code
			$code_string = trim( $form_data['ldgr-user-group-code'] );
			$instance    = Ld_Group_Registration_Groups::get_instance();

			if ( empty( $code_string ) ) {
				return false;
			}

			$member_user = new \WP_User( $user_id );
			$lead_user   = get_userdata( $group_code->post_author );
			$courses     = learndash_group_enrolled_courses( $group_id );
			$group_limit = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );

			// Retrieves the all group leader ids.
			$group_leader_ids = learndash_get_groups_administrator_ids( $group_id );

			// adds user if user is not group member or leader.
			if ( apply_filters( 'is_ldgr_default_user_add_action', true ) ) {
				ld_update_group_access( $user_id, $group_id );
				delete_user_meta( $member_user->ID, '_total_groups_an_user_removed_from' );
			}

			do_action( 'ldgr_action_existing_user_enroll', $user_id, $group_id, $form_data );
			$enrolled_course = array();
			if ( ! empty( $courses ) ) {
				foreach ( $courses as $key => $value ) {
					$enrolled_course[] = get_the_title( $value );
					$url               = get_permalink( $value );
					unset( $key );
				}
			}
			$tsub = get_option( 'wdm-u-add-gr-sub' );
			if ( empty( $tsub ) ) {
				$tsub = WDM_U_ADD_GR_SUB;
			}
			$subject = stripslashes( $tsub );
			$subject = str_replace( '{group_title}', get_the_title( $group_id ), $subject );
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
			$body = str_replace( '{course_list}', $instance->get_course_list_html( $enrolled_course, $group_id, $member_user->ID ), $body );
			$body = str_replace( '{group_leader_name}', ucfirst( strtolower( $lead_user->first_name ) ) . ' ' . ucfirst( strtolower( $lead_user->last_name ) ), $body );
			$body = str_replace( '{user_first_name}', ucfirst( strtolower( $member_user->first_name ) ), $body );
			$body = str_replace( '{user_last_name}', ucfirst( strtolower( $member_user->last_name ) ), $body );
			$body = str_replace( '{login_url}', wp_login_url( $url ), $body );

			// Fetch enable/disable email setting
			$wdm_u_add_gr_enable = get_option( 'wdm_u_add_gr_enable' );

			if ( apply_filters( 'wdm_group_enrollment_email_status', true, $group_id ) && 'off' != $wdm_u_add_gr_enable ) {
				// Send user enrollment emails
				$emails_list             = array();
				$emails_list[ $user_id ] = array(
					'email'   => trim( $member_user->user_email ),
					'subject' => $subject,
					'body'    => $body,
					'new'     => false,
				);

				// Get groups class instance
				$instance->send_bulk_upload_emails( $emails_list, $group_id, $form_data );
			}

			// Update if not unlimited
			$is_unlimited = get_post_meta( $group_id, 'ldgr_unlimited_seats', 1 );
			if ( ! $is_unlimited ) {
				update_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, intval( $group_limit - 1 ) );
			}

			// Update group code enrollment limit
			$allowed_enrollments = get_post_meta( $group_code->ID, 'group_code_enrollment_count', 1 );
			update_post_meta( $group_code->ID, 'group_code_enrollment_count', intval( $allowed_enrollments - 1 ) );

			if ( $group_limit <= 0 && ! $is_unlimited ) {
				do_action( 'wdm_group_limit_is_zero', $group_id );
			}

			// Add meta that user enrolled via group code
			update_user_meta( $user_id, 'ldgr_code_enrolled_' . $group_code->ID, $group_id );

			// Update group code meta for enrolled users
			$this->update_group_code_enrolled_meta( $group_code->ID, $user_id );

			/**
			 * Allow 3rd party plugins to perform actions on enrollment via group codes.
			 *
			 * @since 4.1.0
			 *
			 * @param int       $user_id        ID of the enrolled user.
			 * @param object    $group_code     Group code object used for enrollment.
			 * @param array     $form_data      Data of the form submitted during enrollment.
			 */
			do_action( 'ldgr_action_group_code_user_enrolled', $user_id, $group_code, $form_data );
			do_action(
				'ld_group_postdata_updated',
				$group_id,
				$group_leader_ids,
				array( $user_id ),
				learndash_group_enrolled_courses( $group_id )
			);

			return true;
		}
		/**
		 * Get the IP address of the client
		 *
		 * @return string
		 * @since 4.1.0
		 */
		function get_client_ip_address() {
			$ip_address = '';

			// Check whether ip is from the share internet
			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ip_address = $_SERVER['HTTP_CLIENT_IP'];
			}
			// Check whether ip is from the proxy
			elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			// Check whether ip is from the remote address
			elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
				$ip_address = $_SERVER['REMOTE_ADDR'];
			}

			return $ip_address;
		}
		/**
		 * Extract the email domain name from the email address
		 *
		 * @param string $email_address
		 * @return string
		 * @since 4.1.0
		 */
		function extract_email_domain( $email_address ) {
			if ( empty( $email_address ) ) {
				return false;
			}
			$domain = substr( strstr( $email_address, '@' ), 1 );

			return $domain;
		}
		/**
		 * Handle the submission of the group code enrollment form
		 *
		 * @since 4.1.0
		 */
		public function ajax_submit_group_code_enroll_form() {
			if ( ! empty( $_POST ) && array_key_exists( 'action', $_POST ) && 'ldgr-submit-group-code-enroll-form' == $_POST['action'] ) {
				// Authenticate valid request.
				check_ajax_referer( 'ldgr-group-code-enrollment-form', 'nonce' );

				$form_data = array();
				parse_str( $_POST['form'], $form_data );

				// Check if user registration enabled.
				$users_can_register = get_option( 'users_can_register' );
				if ( ! $users_can_register ) {
					echo wp_json_encode(
						array(
							'type' => 'error',
							'msg'  => __( 'User registrations are currently disabled on this website. Please contact admin for more details', WDM_LDGR_TXT_DOMAIN ),
						)
					);
					wp_die();
				}

				// Check for recaptcha.
				$enable_recaptcha = get_option( 'ldgr_group_code_enable_recaptcha' );
				if ( 'on' == $enable_recaptcha ) {
					if ( $invalid = $this->is_invalid_recaptcha_response( $form_data ) ) {
						echo json_encode(
							array(
								'type' => 'error',
								'msg'  => $invalid,
							)
						);
						wp_die();
					}
				}

				if ( ! is_user_logged_in() ) {
					echo wp_json_encode(
						array(
							'type' => 'error',
							'msg'  => __( 'Please login first to use the group code', WDM_LDGR_TXT_DOMAIN ),
						)
					);
					wp_die();
				}
				// Get user id.
				$user_id = get_current_user_id();

				// Admin do not need to enroll.
				if ( current_user_can( 'manage_options' ) ) {
					echo wp_json_encode(
						array(
							'type' => 'success',
							'msg'  => __( 'Administrators already have access to all groups', WDM_LDGR_TXT_DOMAIN ),
						)
					);
					wp_die();
				}

				// Check code string.
				$code_string = trim( $form_data['ldgr-user-group-code'] );

				if ( empty( $code_string ) ) {
					echo wp_json_encode(
						array(
							'type' => 'error',
							'msg'  => __( 'Please enter valid group code', WDM_LDGR_TXT_DOMAIN ),
						)
					);
					wp_die();
				}

				// Verify group code.
				if ( $invalid = $this->is_invalid_group_code( $form_data ) ) {
					echo wp_json_encode(
						array(
							'type' => 'error',
							'msg'  => $invalid,
						)
					);
					wp_die();
				}

				// Fetch Group details from Group Code.
				$group_code = get_page_by_title( $code_string, OBJECT, 'ldgr_group_code' );
				$group_id   = get_post_meta( $group_code->ID, 'group_code_related_groups', 1 );

				// Check if user already in group.
				if ( learndash_is_user_in_group( $user_id, $group_id ) ) {
					echo wp_json_encode(
						array(
							'type' => 'success',
							'msg'  => __( 'You are already enrolled in the group', WDM_LDGR_TXT_DOMAIN ),
						)
					);
					wp_die();
				}

				// Enroll user.
				if ( ! $this->enroll_user_to_group( $user_id, $form_data, $group_code, $group_id ) ) {
					echo wp_json_encode(
						array(
							'type' => 'error',
							'msg'  => __(
								'Some error occurred while creating user and enrolling user in group. Please contact admin',
								WDM_LDGR_TXT_DOMAIN
							),
						)
					);
					wp_die();
				}

				// Create success data.
				$success_data = $this->get_success_response_data( $form_data );

				echo wp_json_encode( $success_data );
			}

			wp_die();
		}
		/**
		 * Verify if the reCAPTCHA response is valid
		 *
		 * @param array $form_data
		 * @return bool
		 * @since 4.1.0
		 */
		public function is_invalid_recaptcha_response( $form_data ) {
			// Check data
			if ( empty( $form_data ) || ! array_key_exists( 'g-recaptcha-response', $form_data ) || empty( $form_data['g-recaptcha-response'] ) ) {
				return __( 'Invalid Captcha, please check the reCAPTCHA checkbox', WDM_LDGR_TXT_DOMAIN );
			}

			$recaptcha_response = trim( $form_data['g-recaptcha-response'] );
			$secret_key         = get_option( 'ldgr_recaptcha_secret_key' );

			// Check configuration
			if ( empty( $secret_key ) ) {
				return __( 'Incomplete captcha configuration, please contact admin', WDM_LDGR_TXT_DOMAIN );
			}

			// Prepare the request
			$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';

			$recaptcha_params = array(
				'secret'   => $secret_key,
				'response' => $recaptcha_response,
			);

			// Get recaptcha response
			$response = wp_remote_post(
				add_query_arg(
					$recaptcha_params,
					$recaptcha_url
				)
			);

			// Check for errors
			if ( is_wp_error( $response ) ) {
				return __( 'The reCAPTCHA validation with some error, please try again', WDM_LDGR_TXT_DOMAIN );
			}

			// Get captcha response data
			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			// If invalid captcha then return error
			if ( ! $response_data->success ) {
				return __( 'The reCAPTCHA validation failed, please try again', WDM_LDGR_TXT_DOMAIN );
			}

			return false;
		}

		/**
		 * Update group code meta for enrolled users
		 *
		 * @param int $group_code_id
		 * @param int $user_id
		 *
		 * @since 4.1.0
		 */
		public function update_group_code_enrolled_meta( $group_code_id, $user_id ) {
			if ( empty( $group_code_id ) || empty( $user_id ) ) {
				return;
			}

			$code_enrolled_users = maybe_unserialize( get_post_meta( $group_code_id, 'ldgr_code_enrolled_users', 1 ) );
			$code_enrolled_users = empty( $code_enrolled_users ) ? array() : $code_enrolled_users;

			// Check if user already used this code.
			if ( ! in_array( $user_id, $code_enrolled_users ) ) {
				$code_enrolled_users[] = $user_id;
			}

			update_post_meta( $group_code_id, 'ldgr_code_enrolled_users', $code_enrolled_users );
		}

		/**
		 * Get the group code enrollment message
		 *
		 * @param array $form_data
		 * @return string
		 *
		 * @since 4.1.0
		 */
		public function get_group_code_enrollment_message( $form_data ) {
			$ldgr_group_code_enrollment_message = get_option( 'ldgr_group_code_enrollment_message' );
			if ( empty( $ldgr_group_code_enrollment_message ) ) {
				return false;
			}

			// Check code string
			$code_string = trim( $form_data['ldgr-user-group-code'] );
			// Fetch Group Code Details
			$group_code = get_page_by_title( $code_string, OBJECT, 'ldgr_group_code' );
			// Fetch Group ID
			$group_id = get_post_meta( $group_code->ID, 'group_code_related_groups', 1 );

			if ( is_user_logged_in() ) {
				$user_data = wp_get_current_user();
			} else {
				$user_email = trim( $form_data['ldgr-user-email'] );
				$user_data  = get_user_by( 'email', $user_email );
			}

			// Message placeholders
			$ldgr_group_code_placeholders = array(
				'{group_title}',
				'{user_first_name}',
				'{user_last_name}',
				'{login_url}',
			);

			$ldgr_group_code_placeholders = apply_filters( 'ldgr_filter_group_code_enrollment_placeholders', $ldgr_group_code_placeholders );

			foreach ( $ldgr_group_code_placeholders as $placeholder ) {
				switch ( $placeholder ) {
					case '{group_title}':
						$ldgr_group_code_enrollment_message = str_replace(
							'{group_title}',
							get_the_title( $group_id ),
							$ldgr_group_code_enrollment_message
						);
						break;

					case '{user_first_name}':
						$ldgr_group_code_enrollment_message = str_replace(
							'{user_first_name}',
							ucfirst( strtolower( $user_data->first_name ) ),
							$ldgr_group_code_enrollment_message
						);
						break;

					case '{user_last_name}':
						$ldgr_group_code_enrollment_message = str_replace(
							'{user_last_name}',
							ucfirst( strtolower( $user_data->last_name ) ),
							$ldgr_group_code_enrollment_message
						);
						break;

					case '{login_url}':
						$login_url                          = apply_filters( 'ldgr_filter_group_code_enrollment_login_url', wp_login_url() );
						$ldgr_group_code_enrollment_message = str_replace(
							'{login_url}',
							$login_url,
							$ldgr_group_code_enrollment_message
						);
						break;

					default:
						$ldgr_group_code_enrollment_message = apply_filters( 'ldgr_filter_group_code_enrollment_placeholder_process', $ldgr_group_code_enrollment_message, $placeholder, $form_data );
						break;
				}
			}

			$ldgr_group_code_enrollment_message = wpautop( $ldgr_group_code_enrollment_message );
			return $ldgr_group_code_enrollment_message;
		}

		/**
		 * Get response data on successful group code form submission.
		 *
		 * @param array $form_data  Details of the group code registration/enrollment form
		 * @return array            Success data to be sent as a response after submisison.
		 *
		 * @since 4.1.1
		 */
		public function get_success_response_data( $form_data ) {
			$success_data = array(
				'type' => 'success',
			);

			// Check whether to display message or redirect to page.
			$ldgr_group_code_redirect = get_option( 'ldgr_group_code_redirect' );

			if ( 'on' == $ldgr_group_code_redirect ) {
				$ldgr_group_code_redirect_page = get_permalink( get_option( 'ldgr_group_code_redirect_page' ) );

				// If page not set, redirect to home page.
				if ( empty( $ldgr_group_code_redirect_page ) ) {
					$ldgr_group_code_redirect_page = home_url();
				}

				$success_data = array(
					'redirect'     => true,
					'redirect_url' => $ldgr_group_code_redirect_page,
				);
			} else {
				// Success message.
				$enrollment_message = $this->get_group_code_enrollment_message( $form_data );

				// If not set display default one.
				if ( empty( $enrollment_message ) ) {
					$enrollment_message = __( 'Congratulations, you have successfully been enrolled in the respective group', WDM_LDGR_TXT_DOMAIN );
				}
				$success_data = array(
					'redirect' => false,
					'msg'      => $enrollment_message,
				);
			}

			/**
			 * Filter group code enrollment/registration form success data
			 *
			 * @since 4.1.1
			 *
			 * @param array $success_data       Response data returned on successfull form submission.
			 * @param array $form_data          Details of the group code enrollment/registration form.
			 */
			return apply_filters( 'ldgr_filter_group_code_success_data', $success_data, $form_data );
		}
	}
}
