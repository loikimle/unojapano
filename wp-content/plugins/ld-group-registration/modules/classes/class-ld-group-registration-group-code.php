<?php
/**
 * Group Code Module
 *
 * @since      4.1.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/classes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace LdGroupRegistration\Modules\Classes;

defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'Ld_Group_Registration_Group_Code' ) ) {
	/**
	 * Class LD Group Registration Group Code
	 */
	class Ld_Group_Registration_Group_Code {
		/**
		 * Tab ID
		 *
		 * @var int
		 * @since   4.1.0
		 */
		protected $tab_id;

		public function __construct() {
			// Set the tab id group registration page
			$this->tab_id = 4;
		}

		/**
		 * Create the group code post type
		 *
		 * @since   4.1.0
		 */
		public function create_group_code_post_type() {
			$labels = array(
				'name'                  => _x( 'Group Codes', 'Post type general name', WDM_LDGR_TXT_DOMAIN ),
				'singular_name'         => _x( 'Group Code', 'Post type singular name', WDM_LDGR_TXT_DOMAIN ),
				'menu_name'             => _x( 'Group Codes', 'Admin Menu text', WDM_LDGR_TXT_DOMAIN ),
				'name_admin_bar'        => _x( 'Group Code', 'Add New on Toolbar', WDM_LDGR_TXT_DOMAIN ),
				'add_new'               => __( 'Add New', WDM_LDGR_TXT_DOMAIN ),
				'add_new_item'          => __( 'Add New Group Code', WDM_LDGR_TXT_DOMAIN ),
				'new_item'              => __( 'New Group Code', WDM_LDGR_TXT_DOMAIN ),
				'edit_item'             => __( 'Edit Group Code', WDM_LDGR_TXT_DOMAIN ),
				'view_item'             => __( 'View Group Code', WDM_LDGR_TXT_DOMAIN ),
				'all_items'             => __( 'All Group Codes', WDM_LDGR_TXT_DOMAIN ),
				'search_items'          => __( 'Search Group Codes', WDM_LDGR_TXT_DOMAIN ),
				'parent_item_colon'     => __( 'Parent Group Codes:', WDM_LDGR_TXT_DOMAIN ),
				'not_found'             => __( 'No group codes found.', WDM_LDGR_TXT_DOMAIN ),
				'not_found_in_trash'    => __( 'No group codes found in Trash.', WDM_LDGR_TXT_DOMAIN ),
				'featured_image'        => _x( 'Group Code Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', WDM_LDGR_TXT_DOMAIN ),
				'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', WDM_LDGR_TXT_DOMAIN ),
				'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', WDM_LDGR_TXT_DOMAIN ),
				'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', WDM_LDGR_TXT_DOMAIN ),
				'archives'              => _x( 'Group Code archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', WDM_LDGR_TXT_DOMAIN ),
				'insert_into_item'      => _x( 'Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', WDM_LDGR_TXT_DOMAIN ),
				'uploaded_to_this_item' => _x( 'Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', WDM_LDGR_TXT_DOMAIN ),
				'filter_items_list'     => _x( 'Filter group codes list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', WDM_LDGR_TXT_DOMAIN ),
				'items_list_navigation' => _x( 'Group Codes list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', WDM_LDGR_TXT_DOMAIN ),
				'items_list'            => _x( 'Group Codes list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', WDM_LDGR_TXT_DOMAIN ),
			);

			$arguments = array(
				'labels'               => $labels,
				'description'          => __( 'Group Codes for enrolling users in LearnDash Groups.', WDM_LDGR_TXT_DOMAIN ),
				'public'               => true,
				'exclude_from_search'  => true,
				'publicly_queryable'   => true,
				'show_ui'              => true,
				'show_in_menu'         => false,
				'query_var'            => true,
				'rewrite'              => array( 'slug' => 'ldgr_group_code' ),
				'capability_type'      => 'post',
				'has_archive'          => true,
				'hierarchical'         => false,
				'menu_position'        => null,
				'supports'             => array( 'title', 'author' ),
				'register_meta_box_cb' => array( $this, 'ldgr_group_code_metaboxes' ),
			);

			register_post_type(
				'ldgr_group_code',
				$arguments
			);
		}

		/**
		 * Save group code settings
		 *
		 * @since 4.1.0
		 */
		public function save_group_code_settings() {
			// Save if data set
			if ( array_key_exists( 'ldgr_nonce', $_POST ) && wp_verify_nonce( $_POST['ldgr_nonce'], 'ldgr_save_group_code_settings' ) ) {
				$ldgr_group_code_enable_recaptcha = array_key_exists( 'ldgr_group_code_enable_recaptcha', $_POST ) ? trim( $_POST['ldgr_group_code_enable_recaptcha'] ) : '';
				update_option( 'ldgr_group_code_enable_recaptcha', $ldgr_group_code_enable_recaptcha );

				$ldgr_recaptcha_site_key = array_key_exists( 'ldgr_recaptcha_site_key', $_POST ) ? trim( $_POST['ldgr_recaptcha_site_key'] ) : '';
				update_option( 'ldgr_recaptcha_site_key', $ldgr_recaptcha_site_key );

				$ldgr_recaptcha_secret_key = array_key_exists( 'ldgr_recaptcha_secret_key', $_POST ) ? trim( $_POST['ldgr_recaptcha_secret_key'] ) : '';
				update_option( 'ldgr_recaptcha_secret_key', $ldgr_recaptcha_secret_key );

				$ldgr_enable_group_code = array_key_exists( 'ldgr_enable_group_code', $_POST ) ? trim( $_POST['ldgr_enable_group_code'] ) : '';
				update_option( 'ldgr_enable_group_code', $ldgr_enable_group_code );

				$ldgr_group_code_enrollment_message = array_key_exists( 'ldgr_group_code_enrollment_message', $_POST ) ? trim( $_POST['ldgr_group_code_enrollment_message'] ) : '';
				update_option( 'ldgr_group_code_enrollment_message', $ldgr_group_code_enrollment_message );

				$ldgr_group_code_enrollment_page = array_key_exists( 'ldgr_group_code_enrollment_page', $_POST ) ? trim( $_POST['ldgr_group_code_enrollment_page'] ) : '';
				update_option( 'ldgr_group_code_enrollment_page', $ldgr_group_code_enrollment_page );

				$this->add_shortcode_to_page( $ldgr_group_code_enrollment_page );

				$ldgr_group_code_redirect = array_key_exists( 'ldgr_group_code_redirect', $_POST ) ? trim( $_POST['ldgr_group_code_redirect'] ) : '';
				update_option( 'ldgr_group_code_redirect', $ldgr_group_code_redirect );

				$ldgr_group_code_redirect_page = array_key_exists( 'ldgr_group_code_redirect_page', $_POST ) ? trim( $_POST['ldgr_group_code_redirect_page'] ) : '';
				update_option( 'ldgr_group_code_redirect_page', $ldgr_group_code_redirect_page );

				$ldgr_enable_gdpr = array_key_exists( 'ldgr_enable_gdpr', $_POST ) ? trim( $_POST['ldgr_enable_gdpr'] ) : '';
				update_option( 'ldgr_enable_gdpr', $ldgr_enable_gdpr );

				$ldgr_gdpr_checkbox_message = array_key_exists( 'ldgr_gdpr_checkbox_message', $_POST ) ? trim( $_POST['ldgr_gdpr_checkbox_message'] ) : '';
				update_option( 'ldgr_gdpr_checkbox_message', $ldgr_gdpr_checkbox_message );
			}
		}

		/**
		 * Add group code submenu page
		 *
		 * @since 4.1.0
		 */
		public function add_group_code_submenu() {
			// Check if settings enabled
			$ldgr_enable_group_code = get_option( 'ldgr_enable_group_code' );

			if ( 'on' == $ldgr_enable_group_code ) {
				add_submenu_page(
					'learndash-lms',
					__( 'Group Code', WDM_LDGR_TXT_DOMAIN ),
					__( 'Group Code', WDM_LDGR_TXT_DOMAIN ),
					'manage_options',
					'edit.php?post_type=ldgr_group_code',
					null
				);
			} else {
				remove_submenu_page( 'learndash-lms', 'edit.php?post_type=ldgr_group_code' );
			}
		}

		/**
		 * Register group code metaboxes
		 *
		 * @since   4.1.0
		 */
		public function ldgr_group_code_metaboxes() {
			add_meta_box(
				'ldgr-group-code-details',
				__( 'Group Code Details', WDM_LDGR_TXT_DOMAIN ),
				array( $this, 'add_group_code_details_metabox' ),
				'ldgr_group_code',
				'normal',
				'high'
			);
		}

		/**
		 * Add group code details metaboxes
		 *
		 * @param object $post  WP Post object of group code id.
		 *
		 * @since 4.1.0
		 */
		public function add_group_code_details_metabox( $post ) {
			$post_id = $post->ID;
			$user_id = get_current_user_id();

			$group_code_from              = get_post_meta( $post_id, 'group_code_from', 1 );
			$group_code_to                = get_post_meta( $post_id, 'group_code_to', 1 );
			$group_code_enrollment_count  = get_post_meta( $post_id, 'group_code_enrollment_count', 1 );
			$group_code_related_groups    = get_post_meta( $post_id, 'group_code_related_groups', 1 );
			$group_code_validation_check  = get_post_meta( $post_id, 'group_code_validation_check', 1 );
			$group_code_ip_validation     = get_post_meta( $post_id, 'group_code_ip_validation', 1 );
			$group_code_domain_validation = get_post_meta( $post_id, 'group_code_domain_validation', 1 );
			$group_list                   = learndash_get_administrators_group_ids( $user_id );
			$code_enrolled_users          = maybe_unserialize( get_post_meta( $post_id, 'ldgr_code_enrolled_users', 1 ) );
			$code_enrolled_users          = empty( $code_enrolled_users ) ? array() : $code_enrolled_users;

			include WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-group-code-details-metabox.php';
		}

		/**
		 * Add group code tab header on group registration page
		 *
		 * @param array $tab_headers    Array of group registration tab header details.
		 * @param int   $group_id       ID of the group.
		 *
		 * @return array                Updated array of group registration tab header details.
		 * @since   4.1.0
		 */
		public function add_group_code_tab_header( $tab_headers, $group_id ) {
			// Check if array
			if ( ! is_array( $tab_headers ) ) {
				return $tab_headers;
			}

			// Check if not already added.
			if ( false !== array_search( 'ldgr_group_code_label', array_column( $tab_headers, 'slug' ) ) ) {
				return $tab_headers;
			}

			$ldgr_enable_group_code = get_option( 'ldgr_enable_group_code' );

			// Check if settings enabled
			if ( 'on' != $ldgr_enable_group_code ) {
				return $tab_headers;
			}

			// Add group code tab header
			array_push(
				$tab_headers,
				array(
					'title' => __( 'Group Code', WDM_LDGR_TXT_DOMAIN ),
					'slug'  => 'ldgr_group_code_label',
					'icon'  => plugin_dir_url( dirname( __FILE__ ) ) . 'media/group-code.svg',
					'id'    => $this->tab_id,
				)
			);

			return $tab_headers;
		}

		/**
		 * Add group code tab contents on group registration page
		 *
		 * @param array $tab_contents   Array of group registration tab header details.
		 * @param int   $group_id       ID of the group.
		 *
		 * @return array                Updated array of group registration tab content details.
		 * @since   4.1.0
		 */
		public function add_group_code_tab_contents( $tab_contents, $group_id ) {
			// Check if array
			if ( ! is_array( $tab_contents ) ) {
				return $tab_contents;
			}

			// Check if not already added.
			if ( false !== array_search( $this->tab_id, array_column( $tab_contents, 'id' ) ) ) {
				return $tab_contents;
			}

			$ldgr_enable_group_code = get_option( 'ldgr_enable_group_code' );

			// Check if settings enabled
			if ( 'on' != $ldgr_enable_group_code ) {
				return $tab_contents;
			}

			// Check the group code tab contents to be displayed
			$group_code_tab_contents = $this->fetch_group_code_tab_contents( $group_id );

			// Add group code tab contents
			array_push(
				$tab_contents,
				$group_code_tab_contents
			);

			return $tab_contents;
		}

		/**
		 * Fetch list of group code table details to be displayed for the current user .
		 *
		 * @param int $group_id ID of the group.
		 * @param int $user_id  ID of the user, if empty then current user id considered
		 *
		 * @return array
		 * @since   4.1.0
		 */
		public function fetch_group_code_table_data( $group_id, $user_id = 0 ) {
			$group_code_details = array();

			$group_codes = $this->get_group_codes_list( $group_id, $user_id );

			foreach ( $group_codes as $group_code_id ) {
				array_push(
					$group_code_details,
					array(
						'id'         => $group_code_id,
						'title'      => get_the_title( $group_code_id ),
						'status'     => get_post_status( $group_code_id ),
						'schedule'   => $this->get_group_code_schedule( $group_code_id ),
						'user_count' => count( $this->get_group_code_enrolled_users( $group_code_id ) ),
					)
				);
			}

			return $group_code_details;
		}

		/**
		 * Fetch list of group codes for the user.
		 *
		 * @param int $group_id     ID of the group.
		 * @param int $user_id      ID of the user, if not passed then current user ID is considered.
		 *
		 * @return array
		 * @since   4.1.0
		 */
		public function get_group_codes_list( $group_id, $user_id ) {
			// Check user ID
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			$group_codes = array();

			// Check if group id not empty,
			if ( empty( $group_id ) ) {
				return $group_codes;
			}

			// Fetch group codes for the given group id.
			$group_codes = get_posts(
				array(
					'post_type'   => 'ldgr_group_code',
					'meta_query'  => array(
						array(
							'key'   => 'group_code_related_groups',
							'value' => $group_id,
						),
					),
					'fields'      => 'ids',
					'post_status' => array( 'publish', 'draft' ),
					'author'      => $user_id,
				)
			);

			return $group_codes;
		}

		/**
		 * Fetch the contents for the group code tab
		 *
		 * @param int $group_id
		 * @return array
		 * @since   4.1.0
		 */
		public function fetch_group_code_tab_contents( $group_id ) {
			$template_path                   = WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-group-users/tabs/group-codes-tab.template.php';
			$is_unlimited                    = get_post_meta( $group_id, 'ldgr_unlimited_seats', 1 );
			$ldgr_group_code_enrollment_page = get_option( 'ldgr_group_code_enrollment_page' );
			$data                            = array(
				// 'groups_list'	=>	ldgr_get_leader_group_ids(),
				'group_codes_data'   => $this->fetch_group_code_table_data( $group_id ),
				'group_id'           => $group_id,
				'is_unlimited'       => $is_unlimited,
				'enrollment_page_id' => $ldgr_group_code_enrollment_page,
			);

			// Add group code tab contents
			$group_code_tab_contents = array(
				'id'       => $this->tab_id,
				'active'   => false,
				'template' => $template_path,
				'data'     => $data,
			);

			return $group_code_tab_contents;
		}

		/**
		 * Enqueue group code scripts and styles
		 *
		 * @since   4.1.0
		 */
		public function enqueue_group_code_scripts() {
			global $post;

			if ( empty( $post ) || ! has_shortcode( $post->post_content, 'wdm_group_users' ) ) {
				return;
			}

			wp_enqueue_style(
				'ldgr-datepicker-base-styles',
				'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.min.css'
			);

			wp_enqueue_style(
				'ldgr-group-code-styles',
				plugins_url(
					'css/ldgr-group-code-styles.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);

			wp_enqueue_script(
				'ldgr-group-code-script',
				plugins_url(
					'js/ldgr-group-code.js',
					dirname( __FILE__ )
				),
				array( 'jquery', 'jquery-ui-datepicker' ),
				LD_GROUP_REGISTRATION_VERSION
			);

			wp_localize_script(
				'ldgr-group-code-script',
				'group_code_loc',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'lang'     => array(
						'delete_msg'     => __( 'Are you sure you want to delete this group code ?', WDM_LDGR_TXT_DOMAIN ),
						'clipboard_copy' => __( 'Copied "__ldgr_code__" to clipboard', WDM_LDGR_TXT_DOMAIN ),
					),
				)
			);
		}

		/**
		 * Create new group code via ajax
		 *
		 * @since   4.1.0
		 */
		public function ajax_create_group_code() {
			if ( ! empty( $_POST ) && array_key_exists( 'action', $_POST ) && 'ldgr-create-group-code' == $_POST['action'] ) {
				// Get current user ID.
				$user_id = get_current_user_id();

				// Authenticate valid request.
				check_ajax_referer( 'ldgr-create-group-code-' . $user_id, 'nonce' );

				$form_data = array();
				parse_str( $_POST['form'], $form_data );

				// Validate form data.
				if ( $invalid = $this->invalid_group_code_form( $form_data ) ) {
					echo json_encode(
						array(
							'type' => 'error',
							'msg'  => $invalid,
						)
					);
					wp_die();
				}

				// Create new group code.
				$group_code_id = wp_insert_post(
					array(
						'post_title'  => $form_data['ldgr-code-string'],
						'post_type'   => 'ldgr_group_code',
						'post_status' => ( 'on' == $form_data['ldgr-code-status'] ) ? 'publish' : 'draft',
						'meta_input'  => array(
							'group_code_from'             => ldgr_get_date_time_of_day( trim( $form_data['ldgr-code-date-range-from'] ), 'BOD' ),
							'group_code_to'               => ldgr_get_date_time_of_day( trim( $form_data['ldgr-code-date-range-to'] ), 'EOD' ),
							'group_code_enrollment_count' => intval( trim( $form_data['ldgr-code-limit'] ) ),
							'group_code_related_groups'   => intval( trim( $form_data['ldgr-code-groups'] ) ),
						),
					)
				);

				if ( empty( $group_code_id ) || is_wp_error( $group_code_id ) ) {
					echo json_encode(
						array(
							'type' => 'error',
							'msg'  => __(
								'Some error occurred',
								WDM_LDGR_TXT_DOMAIN
							),
						)
					);
					wp_die();
				}

				// Check if validation rules set.
				if ( 'on' == trim( $form_data['ldgr-code-validation-check'] ) ) {
					update_post_meta( $group_code_id, 'group_code_validation_check', 'on' );
					// Save validation IP and domain details
					$ip_address = trim( $form_data['ldgr-code-ip-validation'] );
					if ( ! empty( $ip_address ) ) {
						update_post_meta( $group_code_id, 'group_code_ip_validation', $ip_address );
					}
					$domain_name = trim( $form_data['ldgr-code-domain-validation'] );
					if ( ! empty( $domain_name ) ) {
						update_post_meta( $group_code_id, 'group_code_domain_validation', $domain_name );
					}
				}

				// Successfully created new group code.
				$row_html = ldgr_get_template(
					WDM_LDGR_PLUGIN_DIR . '/modules/templates/group-code-screens/ldgr-view-group-code-single.template.php',
					array(
						'group_code' => array(
							'id'         => $group_code_id,
							'title'      => get_the_title( $group_code_id ),
							'status'     => get_post_status( $group_code_id ),
							'schedule'   => $this->get_group_code_schedule( $group_code_id ),
							'user_count' => count( $this->get_group_code_enrolled_users( $group_code_id ) ),
						),
					),
					1
				);

				echo json_encode(
					array(
						'type'     => 'success',
						'msg'      => __( 'New Group Code successfully created', WDM_LDGR_TXT_DOMAIN ),
						'row_html' => $row_html,
					)
				);
			}

			wp_die();
		}

		/**
		 * Validate the group code form details
		 *
		 * @param array $form_data
		 * @param bool  $update
		 * @return bool
		 * @since   4.1.0
		 */
		public function invalid_group_code_form( $form_data, $update = false ) {
			// Check if empty.
			if ( empty( $form_data ) ) {
				return __( 'Empty Form Details', WDM_LDGR_TXT_DOMAIN );
			}

			$code_string = trim( $form_data['ldgr-code-string'] );

			if ( ! $this->valid_code_string( $code_string, $update ) ) {
				return __( 'Invalid or Duplicate Group Code', WDM_LDGR_TXT_DOMAIN );
			}

			$date_from = trim( $form_data['ldgr-code-date-range-from'] );
			$date_to   = trim( $form_data['ldgr-code-date-range-to'] );

			if ( ! $this->valid_date_range( $date_from, $date_to ) ) {
				return __( 'Invalid From or To Date', WDM_LDGR_TXT_DOMAIN );
			}

			$group = trim( $form_data['ldgr-code-groups'] );
			if ( ! $this->valid_code_groups( $group ) ) {
				return __( 'Invalid Group', WDM_LDGR_TXT_DOMAIN );
			}

			$group_code_limit = trim( $form_data['ldgr-code-limit'] );
			if ( ! $this->valid_code_limit( $group_code_limit ) ) {
				return __( 'Not more seats or max limit for seats reached.', WDM_LDGR_TXT_DOMAIN );
			}

			$validation_check = trim( $form_data['ldgr-code-validation-check'] );
			if ( 'on' == $validation_check ) {
				$ip_address = trim( $form_data['ldgr-code-ip-validation'] );
				if ( ! empty( $ip_address ) && ! filter_var( $ip_address, FILTER_VALIDATE_IP ) ) {
					return __( 'Please enter a valid IP Address for validation', WDM_LDGR_TXT_DOMAIN );
				}

				$domain_name = trim( $form_data['ldgr-code-domain-validation'] );
				if ( ! empty( $domain_name ) && ! filter_var( $domain_name, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
					return __( 'Please enter a valid Domain name for validation', WDM_LDGR_TXT_DOMAIN );
				}
			}

			return false;
		}

		/**
		 * Check if group code valid
		 *
		 * @param string $code_string
		 * @param bool   $update
		 * @return bool
		 * @since   4.1.0
		 */
		public function valid_code_string( $code_string, $update = false ) {
			// Check if empty
			if ( empty( $code_string ) ) {
				return false;
			}

			// Check for duplicates.
			global $wpdb;
			$table     = $wpdb->prefix . 'posts';
			$post_type = 'ldgr_group_code';
			$sql       = $wpdb->prepare( "SELECT ID FROM $table WHERE post_title = %s AND post_type=%s", $code_string, $post_type );

			if ( ! empty( $wpdb->get_col( $sql ) ) && ! $update ) {
				return false;
			}

			// Valid
			return true;
		}

		/**
		 * Check if from and to dates are valid
		 *
		 * @param string $date_from
		 * @param string $date_to
		 *
		 * @return bool
		 * @since   4.1.0
		 */
		public function valid_date_range( $date_from, $date_to ) {
			// Check if empty.
			if ( empty( $date_from ) || empty( $date_to ) ) {
				return false;
			}

			$date_from = ldgr_get_date_time_of_day( $date_from, 'BOD' ); //strtotime( $date_from ); 
			$date_to   = ldgr_get_date_time_of_day( $date_to, 'EOD' ); //strtotime( $date_to );

			// If from date greater than to date then return.
			if ( $date_from > $date_to ) {
				return false;
			}

			return true;
		}

		/**
		 * Check if group is valid
		 *
		 * @param int $group_id
		 * @return bool
		 * @since   4.1.0
		 */
		public function valid_code_groups( $group_id ) {
			if ( empty( $group_id ) ) {
				return false;
			}

			// Check if valid group
			$group_details = get_post( $group_id );

			if ( empty( $group_details ) || 'publish' != $group_details->post_status || 'groups' != $group_details->post_type ) {
				return false;
			}

			$current_user_id = get_current_user_id();

			// Check if admin or group leader
			if ( ! current_user_can( 'manage_options' ) && ! learndash_is_group_leader_user( $current_user_id ) ) {
				return false;
			}

			$user_groups = learndash_get_administrators_group_ids( $current_user_id );

			// Check if user not group leader
			if ( ! in_array( $group_id, $user_groups ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Check if valid group code limit
		 *
		 * @param int $group_code_limit
		 * @return bool
		 * @since   4.1.0
		 */
		public function valid_code_limit( $group_code_limit ) {
			if ( empty( $group_code_limit ) ) {
				return false;
			}

			/**
			 * Filter to enable max group code seat limit
			 *
			 * @since 4.1.1
			 *
			 * @param int $max_group_limit  Zero by default, if set, will be validated against
			 *                              creating new group codes.
			 */
			$max_group_limit = apply_filters( 'ldgr_filter_group_code_seat_limit', 0 );

			if ( ! empty( $max_group_limit ) ) {
				if ( $group_code_limit > $max_group_limit ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Enqueue admin scripts for group code module
		 *
		 * @since 4.1.0
		 */
		public function admin_enqueue_group_code_scripts() {
			global $current_screen;
			// Check if group code screen.
			if ( 'ldgr_group_code' !== $current_screen->id ) {
				return;
			}

			wp_enqueue_script(
				'ldgr-group-code-script',
				plugins_url(
					'js/ldgr-group-code.js',
					dirname( __FILE__ )
				),
				array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ),
				LD_GROUP_REGISTRATION_VERSION
			);

			wp_enqueue_style(
				'ldgr-datepicker-base-styles',
				'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.min.css'
			);

			wp_enqueue_style(
				'ldgr-group-code-styles',
				plugins_url(
					'css/ldgr-admin-group-code.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);
		}

		/**
		 * Ajax generate unique group code
		 *
		 * @since   4.1.0
		 */
		public function ajax_generate_group_code() {
			if ( array_key_exists( 'action', $_POST ) && 'ldgr-generate-group-code' == $_POST['action'] ) {
				$unique_id = $this->get_unique_group_code();
				echo json_encode(
					array(
						'type'      => 'success',
						'unique_id' => $unique_id,
					)
				);
			}
			wp_die();
		}

		/**
		 * Generate a random unique group code
		 *
		 * @return void
		 * @since
		 */
		public function get_unique_group_code() {
			$length    = apply_filters( 'ldgr_filter_random_group_code_length', 10 );
			$unique_id = $this->generate_random_string( $length );

			// Ensure if group code already exists with this ID.
			if ( ! $this->valid_code_string( $unique_id ) ) {
				return $this->get_unique_group_code();
			}

			// Return group code.
			return $unique_id;
		}

		/**
		 * Generate a random string
		 *
		 * @param int $length
		 * @return string
		 *
		 * @since   4.1.0
		 */
		public function generate_random_string( $length ) {
			$characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randomString = '';

			for ( $loop = 0; $loop < $length; $loop++ ) {
				$index         = rand( 0, strlen( $characters ) - 1 );
				$randomString .= $characters[ $index ];
			}

			return $randomString;
		}

		/**
		 * Ajax toggling group code status
		 *
		 * @since   4.1.0
		 */
		public function ajax_group_code_status_toggle() {
			if ( array_key_exists( 'action', $_POST ) && 'ldgr-group-code-status-toggle' == $_POST['action'] ) {
				// Get group code and current user id
				$group_code_id   = $_POST['group_code'];
				$current_user_id = get_current_user_id();

				// Authenticate valid request.
				check_ajax_referer( 'ldgr-group-code-' . $group_code_id . '-' . $current_user_id, 'nonce' );

				// Get updated group code status check
				$checked = $_POST['checked'];

				// Get group code details
				$group_code = get_post( $group_code_id );

				// Check if group code post type
				if ( ( 'ldgr_group_code' != $group_code->post_type ) || ( ! current_user_can( 'manage_options' ) && $current_user_id != $group_code->post_author ) ) {
					echo json_encode(
						array(
							'type' => 'error',
							'msg'  => __( 'Some error occurred, group code not updated', WDM_LDGR_TXT_DOMAIN ),
						)
					);
					wp_die();
				}

				// Update group code code accordingly
				wp_update_post(
					array(
						'ID'          => $group_code_id,
						'post_status' => ( 'true' == $checked ) ? 'publish' : 'draft',
					)
				);

				// Return success
				echo json_encode(
					array(
						'type' => 'success',
					)
				);
			}
			wp_die();
		}

		/**
		 * Fetch group code details for edit screen
		 *
		 * @since   4.1.0
		 */
		public function ajax_fetch_group_code_details() {
			if ( array_key_exists( 'action', $_POST ) && 'ldgr-fetch-group-code-details' == $_POST['action'] ) {
				// Get group code and current user id
				$group_code_id   = $_POST['group_code'];
				$current_user_id = get_current_user_id();

				// Authenticate valid request.
				check_ajax_referer( 'ldgr-group-code-edit-' . $group_code_id . '-' . $current_user_id, 'nonce' );

				// Get group code details
				$group_code = get_post( $group_code_id );

				// Check if group code post type and access to the group code
				if ( ( 'ldgr_group_code' != $group_code->post_type ) || ( ! current_user_can( 'manage_options' ) && $current_user_id != $group_code->post_author ) ) {
					echo json_encode(
						array(
							'type' => 'error',
							'msg'  => __( 'Some error occurred, group code not found', WDM_LDGR_TXT_DOMAIN ),
						)
					);
					wp_die();
				}

				// Fetch enrolled users count.
				$code_enrolled_users = maybe_unserialize( get_post_meta( $group_code_id, 'ldgr_code_enrolled_users', 1 ) );
				$code_enrolled_users = empty( $code_enrolled_users ) ? array() : $code_enrolled_users;

				// Fetch details.
				$data = array(
					'ID'                   => $group_code->ID,
					'title'                => $group_code->post_title,
					'date_from'            => esc_html( ldgr_date_in_site_timezone( get_post_meta( $group_code_id, 'group_code_from', 1 ) ) ),
					'date_to'              => esc_html( ldgr_date_in_site_timezone( get_post_meta( $group_code_id, 'group_code_to', 1 ) ) ),
					'enrollment_count'     => get_post_meta( $group_code_id, 'group_code_enrollment_count', 1 ),
					'related_group'        => get_post_meta( $group_code_id, 'group_code_related_groups', 1 ),
					'status'               => ( 'publish' == $group_code->post_status ) ? true : false,
					'enrolled_users_count' => count( $code_enrolled_users ),
				);

				// Check if validation set
				$validation_check = get_post_meta( $group_code_id, 'group_code_validation_check', 1 );

				$data['validation_check'] = $validation_check;

				if ( $validation_check ) {
					// Fetch validation IP and domain details
					$data['validation_ip']     = get_post_meta( $group_code_id, 'group_code_ip_validation', 1 );
					$data['validation_domain'] = get_post_meta( $group_code_id, 'group_code_domain_validation', 1 );
				}

				// Return group code details
				echo json_encode(
					array(
						'type' => 'success',
						'data' => $data,
					)
				);
			}
			wp_die();
		}

		/**
		 * Update group code via ajax
		 *
		 * @since   4.1.0
		 */
		public function ajax_update_group_code() {
			if ( ! empty( $_POST ) && array_key_exists( 'action', $_POST ) && 'ldgr-update-group-code' == $_POST['action'] ) {
				// Get current user ID.
				$user_id = get_current_user_id();

				// Authenticate valid request.
				check_ajax_referer( 'ldgr-update-group-code-' . $user_id, 'nonce' );

				$form_data = array();
				parse_str( $_POST['form'], $form_data );

				// Validate form data.
				if ( $invalid = $this->invalid_group_code_form( $form_data, 1 ) ) {
					echo json_encode(
						array(
							'type' => 'error',
							'msg'  => $invalid,
						)
					);
					wp_die();
				}

				// Fetch group code ID.
				$group_code_id = intval( $form_data['ldgr-edit-group-code-id'] );

				if ( empty( $group_code_id ) ) {
					echo json_encode(
						array(
							'type' => 'error',
							'msg'  => __(
								'Some error occurred, Group Code ID not set.',
								WDM_LDGR_TXT_DOMAIN
							),
						)
					);
					wp_die();
				}

				// Update group code.
				$update = wp_insert_post(
					array(
						'ID'          => $group_code_id,
						'post_title'  => $form_data['ldgr-code-string'],
						'post_type'   => 'ldgr_group_code',
						'post_status' => ( 'on' == $form_data['ldgr-code-status'] ) ? 'publish' : 'draft',
						'meta_input'  => array(
							'group_code_from'             => ldgr_get_date_time_of_day( trim( $form_data['ldgr-code-date-range-from'] ), 'BOD' ),
							'group_code_to'               => ldgr_get_date_time_of_day( trim( $form_data['ldgr-code-date-range-to'] ), 'EOD' ),
							'group_code_enrollment_count' => intval( trim( $form_data['ldgr-code-limit'] ) ),
							'group_code_related_groups'   => intval( trim( $form_data['ldgr-code-groups'] ) ),
						),
					)
				);

				if ( empty( $update ) || is_wp_error( $update ) ) {
					echo json_encode(
						array(
							'type' => 'error',
							'msg'  => __(
								'Some error occurred, group code not updated',
								WDM_LDGR_TXT_DOMAIN
							),
						)
					);
					wp_die();
				}

				// Check if validation rules set.
				if ( 'on' == trim( $form_data['ldgr-code-validation-check'] ) ) {
					update_post_meta( $group_code_id, 'group_code_validation_check', 'on' );
					// Save validation IP and domain details.
					$ip_address = trim( $form_data['ldgr-code-ip-validation'] );
					update_post_meta( $group_code_id, 'group_code_ip_validation', $ip_address );

					$domain_name = trim( $form_data['ldgr-code-domain-validation'] );
					update_post_meta( $group_code_id, 'group_code_domain_validation', $domain_name );
				} else {
					update_post_meta( $group_code_id, 'group_code_validation_check', 'off' );
				}

				// Successfully updated existing group code.
				$row_html = ldgr_get_template(
					WDM_LDGR_PLUGIN_DIR . '/modules/templates/group-code-screens/ldgr-view-group-code-single.template.php',
					array(
						'group_code' => array(
							'id'         => $group_code_id,
							'title'      => get_the_title( $group_code_id ),
							'status'     => get_post_status( $group_code_id ),
							'schedule'   => $this->get_group_code_schedule( $group_code_id ),
							'user_count' => count( $this->get_group_code_enrolled_users( $group_code_id ) ),
						),
					),
					1
				);

				echo json_encode(
					array(
						'type'     => 'success',
						'msg'      => __( 'Group Code updated successfully!!', WDM_LDGR_TXT_DOMAIN ),
						'title'    => get_the_title( $group_code_id ),
						'status'   => ( 'publish' == get_post_status( $group_code_id ) ) ? true : false,
						'row_html' => $row_html,
						'row_id'   => 'ldgr-group-code-row-' . $group_code_id,
					)
				);
			}

			wp_die();
		}

		/**
		 * Delete group code via ajax
		 *
		 * @return void
		 * @since
		 */
		public function ajax_delete_group_code() {
			if ( ! empty( $_POST ) && array_key_exists( 'action', $_POST ) && 'ldgr-delete-group-code' == $_POST['action'] ) {
				// Get group code and current user id
				$group_code_id   = $_POST['group_code'];
				$current_user_id = get_current_user_id();

				// Authenticate valid request.
				check_ajax_referer( 'ldgr-group-code-delete-' . $group_code_id . '-' . $current_user_id, 'nonce' );

				// Get group code details
				$group_code = get_post( $group_code_id );

				// Check if group code post type
				if ( ( 'ldgr_group_code' != $group_code->post_type ) || ( ! current_user_can( 'manage_options' ) && $current_user_id != $group_code->post_author ) ) {
					echo json_encode(
						array(
							'type' => 'error',
							'msg'  => __( 'Some error occurred, group code not deleted', WDM_LDGR_TXT_DOMAIN ),
						)
					);
					wp_die();
				}

				// Delete the group code
				$status = wp_delete_post( $group_code->ID );

				// Error trashing the group code
				if ( false == $status || null == $status ) {
					echo json_encode(
						array(
							'type' => 'error',
							'msg'  => __( 'Some error occurred, group code not deleted', WDM_LDGR_TXT_DOMAIN ),
						)
					);
					wp_die();
				}

				// Successfully trashed
				echo json_encode(
					array(
						'type'   => 'success',
						'msg'    => __( 'Group code successfully deleted !!', WDM_LDGR_TXT_DOMAIN ),
						'row_id' => '#ldgr-group-code-row-' . $group_code_id,
					)
				);
			}

			wp_die();
		}

		/**
		 * Save the group code details on admin update.
		 *
		 * @param int    $post_id
		 * @param object $post
		 * @param bool   $update
		 *
		 * @since 4.1.0
		 */
		public function admin_save_group_code( $post_id, $post, $update ) {
			// Return if auto-draft.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			// Verify Nonce.
			$user_id = get_current_user_id();
			if ( ! isset( $_POST['ldgr_nonce'] ) || ! wp_verify_nonce( $_POST['ldgr_nonce'], 'ldgr-create-group-code-' . $user_id ) ) {
				return;
			}

			// Return if no previlege.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			// Save from date.
			if ( isset( $_POST['ldgr-code-date-range-from'] ) ) {
				$code_date_range_from = $_POST['ldgr-code-date-range-from'];
				update_post_meta( $post_id, 'group_code_from', ldgr_get_date_time_of_day( $code_date_range_from, 'BOD' ) );
			}

			// Save to date.
			if ( isset( $_POST['ldgr-code-date-range-to'] ) ) {
				$code_date_range_to = $_POST['ldgr-code-date-range-to'];
				update_post_meta( $post_id, 'group_code_to', ldgr_get_date_time_of_day( $code_date_range_to, 'EOD' ) );
			}

			// Save related group.
			$code_groups = $_POST['ldgr-code-groups'];
			update_post_meta( $post_id, 'group_code_related_groups', $code_groups );

			// Save enrollment limit.
			$code_limit = $_POST['ldgr-code-limit'];
			update_post_meta( $post_id, 'group_code_enrollment_count', $code_limit );

			// Save validation check.
			$code_validation_check = $_POST['ldgr-code-validation-check'];
			update_post_meta( $post_id, 'group_code_validation_check', $code_validation_check );

			// Save IP validation details.
			$code_ip_validation = $_POST['ldgr-code-ip-validation'];
			update_post_meta( $post_id, 'group_code_ip_validation', $code_ip_validation );

			// Save domain validation details.
			$code_domain_validation = $_POST['ldgr-code-domain-validation'];
			update_post_meta( $post_id, 'group_code_domain_validation', $code_domain_validation );
		}

		/**
		 * Add Group Code Setting Tab
		 *
		 * @param array $setting_tabs
		 *
		 * @return array
		 * @since 4.1.0
		 */
		public function add_group_code_setting_tab_header( $setting_tabs ) {
			if ( ! array_key_exists( 'group-code', $setting_tabs ) ) {
				$setting_tabs['group-code'] = __( 'Group Code', WDM_LDGR_TXT_DOMAIN );
			}

			return $setting_tabs;
		}

		/**
		 * Display tab contents for group code settings.
		 *
		 * @param string $current_tab
		 * @since 4.1.0
		 */
		public function add_group_code_setting_tab_contents( $current_tab ) {
			// Check if group code tab
			if ( 'group-code' != $current_tab ) {
				return;
			}

			// Enqueue styles
			wp_enqueue_style(
				'wdm-admin_css',
				plugins_url(
					'css/wdm-admin.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);

			// Fetch data.
			$ldgr_group_code_enable_recaptcha   = get_option( 'ldgr_group_code_enable_recaptcha' );
			$ldgr_recaptcha_site_key            = get_option( 'ldgr_recaptcha_site_key' );
			$ldgr_recaptcha_secret_key          = get_option( 'ldgr_recaptcha_secret_key' );
			$ldgr_enable_group_code             = get_option( 'ldgr_enable_group_code' );
			$ldgr_group_code_enrollment_message = get_option( 'ldgr_group_code_enrollment_message' );
			$ldgr_group_code_enrollment_page    = get_option( 'ldgr_group_code_enrollment_page' );
			$users_can_register                 = get_option( 'users_can_register' );
			$ldgr_group_code_redirect           = get_option( 'ldgr_group_code_redirect' );
			$ldgr_group_code_redirect_page      = get_option( 'ldgr_group_code_redirect_page' );
			$ldgr_enable_gdpr                   = get_option( 'ldgr_enable_gdpr' );
			$ldgr_gdpr_checkbox_message         = get_option( 'ldgr_gdpr_checkbox_message' );

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
					"By using this form you agree with the storage and handling of your data by this website in accordance with our <a target='blank' href='{privacy_policy}'>Privacy Policy</a>"
				);

				$ldgr_gdpr_checkbox_message = $default_gdpr_message;
			}

			// Message placeholders.
			$ldgr_group_code_placeholders = array(
				'{group_title}',
				'{user_first_name}',
				'{user_last_name}',
				'{login_url}',
			);

			$pages = get_posts(
				array(
					'post_type'   => 'page',
					'numberposts' => -1,
					'post_status' => 'publish',
					'fields'      => 'ids',
				)
			);

			$ldgr_group_code_placeholders = apply_filters( 'ldgr_filter_group_code_enrollment_placeholders', $ldgr_group_code_placeholders );

			return ldgr_get_template(
				WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-group-code-settings.template.php',
				array(
					'ldgr_group_code_enable_recaptcha'   => $ldgr_group_code_enable_recaptcha,
					'ldgr_recaptcha_site_key'            => $ldgr_recaptcha_site_key,
					'ldgr_recaptcha_secret_key'          => $ldgr_recaptcha_secret_key,
					'ldgr_enable_group_code'             => $ldgr_enable_group_code,
					'ldgr_group_code_enrollment_message' => $ldgr_group_code_enrollment_message,
					'ldgr_group_code_placeholders'       => $ldgr_group_code_placeholders,
					'ldgr_group_code_enrollment_page'    => $ldgr_group_code_enrollment_page,
					'pages'                              => $pages,
					'users_can_register'                 => $users_can_register,
					'ldgr_group_code_redirect'           => $ldgr_group_code_redirect,
					'ldgr_group_code_redirect_page'      => $ldgr_group_code_redirect_page,
					'ldgr_enable_gdpr'                   => $ldgr_enable_gdpr,
					'ldgr_gdpr_checkbox_message'         => $ldgr_gdpr_checkbox_message,
				)
			);
		}

		/**
		 * Check whether a code is live, expired or how long to expire
		 *
		 * @param int $group_code_id
		 * @return string
		 *
		 * @since 4.1.0
		 */
		public function get_group_code_schedule( $group_code_id ) {
			if ( empty( $group_code_id ) ) {
				return '-';
			}

			// Get current time.
			$now = time();

			// Get from date for group code.
			$group_code_from = get_post_meta( $group_code_id, 'group_code_from', 1 );

			// Check if yet to go live.
			if ( $now < $group_code_from ) {
				$remaining_time = $group_code_from - $now;
				$remaining      = round( $remaining_time / ( 60 * 60 * 24 ) );
				$time           = __( 'Days', WDM_LDGR_TXT_DOMAIN );

				if ( empty( $remaining ) ) {
					$remaining = round( $remaining_time / ( 60 * 60 ) );
					$time      = __( 'Hours', WDM_LDGR_TXT_DOMAIN );
				}

				if ( empty( $remaining ) ) {
					$remaining = round( $remaining_time / 60 );
					$time      = __( 'Minutes', WDM_LDGR_TXT_DOMAIN );
				}

				return sprintf( __( 'Live in %1$s %2$s', WDM_LDGR_TXT_DOMAIN ), $remaining, $time );
			}

			// Get to date for group code.
			$group_code_to = get_post_meta( $group_code_id, 'group_code_to', 1 );
			if ( $now < $group_code_to ) {
				$expiry_time = $group_code_to - $now;
				$expiry      = round( $expiry_time / ( 60 * 60 * 24 ) );
				$time        = __( 'Days', WDM_LDGR_TXT_DOMAIN );

				if ( empty( $expiry ) ) {
					$expiry = round( $expiry_time / ( 60 * 60 ) );
					$time   = __( 'Hours', WDM_LDGR_TXT_DOMAIN );
				}

				if ( empty( $expiry ) ) {
					$expiry = round( $expiry_time / 60 );
					$time   = __( 'Minutes', WDM_LDGR_TXT_DOMAIN );
				}

				// Translators: Expiry date and time.
				return sprintf( __( 'Expires in %1$s %2$s', WDM_LDGR_TXT_DOMAIN ), $expiry, $time );
			}

			return __( 'Expired', WDM_LDGR_TXT_DOMAIN );
		}

		/**
		 * Return list of users enrolled via group code
		 *
		 * @param int $group_code_id
		 * @return array
		 *
		 * @since 4.1.0
		 */
		public function get_group_code_enrolled_users( $group_code_id ) {
			$enrolled_users = array();
			if ( empty( $group_code_id ) ) {
				return $enrolled_users;
			}

			$enrolled_users = maybe_unserialize( get_post_meta( $group_code_id, 'ldgr_code_enrolled_users', 1 ) );
			$enrolled_users = empty( $enrolled_users ) ? array() : $enrolled_users;

			return $enrolled_users;
		}

		/**
		 * Append shortcode to page
		 *
		 * @param int $page_id
		 * @since 4.1.0
		 */
		public function add_shortcode_to_page( $page_id ) {
			if ( empty( $page_id ) ) {
				return;
			}

			// Get Page
			$page = get_post( $page_id );
			if ( empty( $page ) || 'page' != $page->post_type ) {
				return;
			}

			// Check if shortcode already added
			if ( has_shortcode( $page->post_content, 'ldgr-group-code-registration-form' ) ) {
				return;
			}

			// Append shortcode to page content.
			$page_content = $page->post_content . '[ldgr-group-code-registration-form]';

			// Save changes
			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_content' => $page_content,
				)
			);
		}
	}
}
