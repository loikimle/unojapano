<?php
/**
 * UserRegistrationMailChimp Admin.
 *
 * @class    URMC_Admin
 * @version  1.0.0
 * @package  UserRegistrationMailChimp/Admin
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URMC_Admin Class
 */
class URMC_Admin {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		urmc_check_plugin_compatibility();
		$message = urmc_is_compatible();
		if ( 'YES' !== $message ) {
			return;
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		add_action( 'admin_init', array( $this, 'handle_backward_compatibility' ) );
		add_filter( 'user_registration_form_field_mailchimp_path', 'urmc_form_field_mailchimp', 10, 1 );

		add_filter(
			'user_registration_mailchimp_admin_template',
			array(
				$this,
				'user_registration_mailchimp_admin_template',
			),
			10,
			1
		);
		add_filter( 'user_registration_registered_form_fields', 'urmc_registered_form_fields', 10, 1 );
		add_filter(
			'user_registration_mailchimp_advance_class',
			array(
				$this,
				'urmc_mailchimp_advance_class',
			),
			10,
			1
		);
		add_action(
			'user_registration_after_admin_save_profile_validation',
			array(
				$this,
				'user_registration_after_admin_save_profile_validation',
			),
			10,
			2
		);

		add_action( 'delete_user', array( $this, 'user_registration_admin_deletes_user_account' ), 10, 3 );
		add_action( 'ur_user_approved', array( $this, 'user_registration_after_admin_approval' ) );

		// add actions and filters.
		add_filter( 'user_registration_get_settings_pages', array( $this, 'add_user_registration_mailchimp_setting' ), 10, 1 );

		// Hook into form settings.
		add_action( 'user_registration_after_form_settings', array( $this, 'render_mailchimp_section' ) );
		add_action( 'user_registration_after_form_settings_save', array( $this, 'save_mailchimp_form_settings' ), 10, 1 );

	}

	/**
	 * Handles Backward Compatibility for exits users who already mapped mailchimp API with Form Fields.
	 *
	 * @since 1.3.0
	 */
	public function handle_backward_compatibility() {

		$is_already_compatible = get_option( 'ur_mailchimp_compatibility', false );

		if ( ! $is_already_compatible ) {
			$connected_accounts = get_option( 'ur_mailchimp_accounts', array() );

			$urmc_mailchimp_settings_option = get_option( 'urmc_mailchimp_settings', array() );
			$api_key                        = isset( $urmc_mailchimp_settings_option['api_key'] ) ? $urmc_mailchimp_settings_option['api_key'] : '';
			$authorized                     = URMC_Ajax::ur_check_mailchimp_api_key( $api_key );

			if ( $authorized ) {
				$id = count( $connected_accounts ) + 1;

				$new_account = array(
					'api_key' => trim( $api_key ),
					'label'   => 'Account ' . $id,
					'date'    => date_i18n( 'Y-m-d H:i:s' ),
				);
				array_push( $connected_accounts, $new_account );
				update_option( 'ur_mailchimp_accounts', $connected_accounts );
				$this->handle_backward_compatibility_for_individual_form( $api_key, $urmc_mailchimp_settings_option );
				update_option( 'ur_mailchimp_compatibility', true );
			}
		}
	}

	/**
	 * Handle backward compatibility for individual form
	 *
	 * @param string $api_key API KEY.
	 * @param array  $urmc_mailchimp_settings_option Previous Mapped Data.
	 */
	public function handle_backward_compatibility_for_individual_form( $api_key, $urmc_mailchimp_settings_option ) {

		$registration = get_posts(
			array(
				'post_type' => 'user_registration',
			)
		);

		foreach ( $registration as $form ) {
			$mailchimp_integration = ur_get_single_post_meta( $form->ID, 'user_registration_mailchimp_integration', array() );
			$post_content_array    = json_decode( $form->post_content );

			if ( ! empty( $post_content_array ) && empty( $mailchimp_integration ) ) {

				foreach ( $post_content_array as $post_content_row ) {

					foreach ( $post_content_row as $post_content_grid ) {

						foreach ( $post_content_grid as $field ) {

							if ( isset( $field->field_key ) && 'mailchimp' === $field->field_key ) {
								$mailchimp_settings = array();
								$mailchimp_list_id  = ( isset( $field->advance_setting->mailchimp_list ) && '' !== $field->advance_setting->mailchimp_list ) ? $field->advance_setting->mailchimp_list : '';
								$form_id            = isset( $urmc_mailchimp_settings_option['data'][ $mailchimp_list_id ]['form_id'] ) ? $urmc_mailchimp_settings_option['data'][ $mailchimp_list_id ]['form_id'] : '0';

								if ( $form_id == $form->ID ) {
									$mailchimp_groups = isset( $urmc_mailchimp_settings_option['data'][ $mailchimp_list_id ]['group'] ) ? $urmc_mailchimp_settings_option['data'][ $mailchimp_list_id ]['group'] : array();
									$mailchimp_fields = isset( $urmc_mailchimp_settings_option['data'][ $mailchimp_list_id ]['fields'] ) ? $urmc_mailchimp_settings_option['data'][ $mailchimp_list_id ]['fields'] : array();
									$connection_name  = __( 'Connection 1', 'user-registration-mailchimp' );
									$mailchimp_lists  = array(
										'connection_id' => 'connection_' . current_datetime()->getTimestamp(),
										'name'          => $connection_name,
										'api_key'       => $api_key,
										'list_id'       => $mailchimp_list_id,
										'list_group'    => json_encode( $mailchimp_groups ),
										'list_fields'   => json_encode( $mailchimp_fields ),
									);
									array_push( $mailchimp_settings, $mailchimp_lists );

									// mailchimp settings save.
									if ( ! empty( $mailchimp_settings ) ) {
										update_post_meta( $form_id, 'user_registration_mailchimp_integration', $mailchimp_settings );
									}
								}
							}
						}
					}
				}
			}
		}
	}

		/**
		 * Adds settings for mailchimp.
		 *
		 * @param array $settings Displays settings for MailChimp.
		 *
		 * @return array $settings
		 */
	public function add_user_registration_mailchimp_setting( $settings ) {
		if ( class_exists( 'UR_Settings_Page' ) ) {
			$settings[] = include_once dirname( __FILE__ ) . '/settings/class-ur-mailchimp-settings.php';
		}

		return $settings;
	}


	/**
	 * Sync to Mailchimp After Admin Approval
	 *
	 * @param int $user_id User ID.
	 */
	public function user_registration_after_admin_approval( $user_id ) {
		$form_id = ur_get_form_id_by_userid( $user_id );

		if ( 'admin_approval' !== ur_get_single_post_meta( $form_id, 'user_registration_form_setting_login_options', get_option( 'user_registration_general_setting_login_options', 'default' ) ) ) {
			return;
		}
		urmc_sync_mailchimp_after_approval( $user_id );
	}

	/**
	 * Unsubscribe user when user deleted.
	 *
	 * @param int      $user_id User ID.
	 * @param int|null $reassign Reassign.
	 * @param WP_User  $user User Data.
	 */
	public function user_registration_admin_deletes_user_account( $user_id, $reassign, $user ) {
		urmc_unsubscibe_user_from_mailchimp_on_user_deletion( $user );
	}

	/**
	 * Sync User with mailchimp update user from admin side.
	 *
	 * @param int   $user_id User ID.
	 * @param array $profile Form Details.
	 */
	public function user_registration_after_admin_save_profile_validation( $user_id, $profile ) {

		if ( isset( $_POST['ur_user_user_status'] ) && 1 != $_POST['ur_user_user_status'] ) {
			return;
		}
		$user_subscribe_list = get_user_meta( $user_id, 'urmc_subscribe_mailchimp_list', true );

		$form_id = get_user_meta( $user_id, 'ur_form_id', true );

		$valid_form_data     = array();
		$mailchimp_field_key = '';
		foreach ( $profile as $field_key => $fields ) {
			if ( 'mailchimp' === $fields['field_key'] ) {
				$mailchimp_field_key = $field_key;
			}
		}

		foreach ( $_POST as $post_key => $post_data ) {

			$pos = strpos( $post_key, 'user_registration_' );

			if ( false !== $pos ) {
				$new_string = substr_replace( $post_key, '', $pos, strlen( 'user_registration_' ) );

				if ( ! empty( $new_string ) ) {
					$valid_form_data[ $new_string ]               = new stdClass();
					$valid_form_data[ $new_string ]->value        = $post_data;
					$valid_form_data[ $new_string ]->field_type   = $profile[ $post_key ]['type'];
					$valid_form_data[ $new_string ]->label        = $profile[ $post_key ]['label'];
					$valid_form_data[ $new_string ]->field_name   = $new_string;
					$valid_form_data[ $new_string ]->extra_params = array(
						'field_key' => $profile[ $post_key ]['field_key'],
						'label'     => $profile[ $post_key ]['label'],
					);
				}
			} else {
				if ( '' !== $mailchimp_field_key && ! isset( $_POST[ $mailchimp_field_key ] ) ) {
					$new_string_key                                   = substr_replace( $mailchimp_field_key, '', $pos, strlen( 'user_registration_' ) );
					$valid_form_data[ $new_string_key ]               = new stdClass();
					$valid_form_data[ $new_string_key ]->value        = '';
					$valid_form_data[ $new_string_key ]->field_type   = $profile[ $mailchimp_field_key ]['type'];
					$valid_form_data[ $new_string_key ]->label        = $profile[ $mailchimp_field_key ]['label'];
					$valid_form_data[ $new_string_key ]->field_name   = $new_string_key;
					$valid_form_data[ $new_string_key ]->extra_params = array(
						'field_key' => $profile[ $mailchimp_field_key ]['field_key'],
						'label'     => $profile[ $mailchimp_field_key ]['label'],
					);

				}

				$key        = 'email' === $post_key ? 'user_email' : $post_key;
				$field_data = 'user_registration_' . $key;
				$data       = isset( $profile[ $field_data ] ) ? $profile[ $field_data ] : array();

				if ( ! empty( $data ) ) {
					$valid_form_data[ $key ]               = new stdClass();
					$valid_form_data[ $key ]->value        = $post_data;
					$valid_form_data[ $key ]->field_type   = $profile[ $field_data ]['type'];
					$valid_form_data[ $key ]->label        = $profile[ $field_data ]['label'];
					$valid_form_data[ $key ]->field_name   = $key;
					$valid_form_data[ $key ]->extra_params = array(
						'field_key' => $profile[ $field_data ]['field_key'],
						'label'     => $profile[ $field_data ]['label'],
					);
				}
			}
		}

		if ( count( $valid_form_data ) < 1 ) {
			return;
		}

		$valid_mailchimp_lists_array   = urmc_get_valid_mailchimp_list( $form_id, $valid_form_data );
		$valid_mailchimp_lists         = $valid_mailchimp_lists_array['selected_list'];
		$sync_mailchimp_on_user_update = $valid_mailchimp_lists_array['sync_mailchimp_on_user_update'];
		$new_subbed_ids                = array();

		foreach ( $valid_mailchimp_lists as $list_key => $lists ) {
			foreach ( $lists as $list ) {

				if ( isset( $valid_form_data[ $list_key ] ) && 'yes' === $sync_mailchimp_on_user_update[ $list_key ] ) {
					$is_mailchimp_subscribed_from_form = '1' == $valid_form_data[ $list_key ]->value ? true : false;

					if ( $is_mailchimp_subscribed_from_form ) {
						$new_subbed_ids[] = $list['list_id'];
						URMC_MailChimp::send_data( $valid_mailchimp_lists, $valid_form_data, $form_id, $user_id );
					}
				}
			}
		}

		foreach ( $user_subscribe_list as $prev_list_id => $pre_api_key ) {
			if ( ! in_array( $prev_list_id, $new_subbed_ids, true ) ) {
				// Unsubscribe Previous list.
				URMC_MailChimp::unsubscribe( $user_id, $prev_list_id, $pre_api_key );
			}
		}
	}

	/**
	 * Mailchimp Advance class.
	 *
	 * @param array $file_data File Data.
	 */
	public function urmc_mailchimp_advance_class( $file_data ) {

		$path = URMC_ABSPATH . 'includes/form/settings/class-ur-setting-mailchimp.php';

		$file_data['file_path'] = $path;

		return $file_data;

	}

	/**
	 * Mailchimp Admin Template.
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	public function user_registration_mailchimp_admin_template( $path ) {

		$path = URMC_ABSPATH . 'includes/form/views/admin/admin-mailchimp.php';

		return $path;

	}

	/**
	 * Handle redirects to setup/welcome page after install and updates.
	 *
	 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
	 */
	public function admin_redirects() {
		// Setup wizard redirect.
		if ( get_transient( '_urmc_activation_redirect' ) ) {
			delete_transient( '_urmc_activation_redirect' );

			if ( ( is_network_admin() || isset( $_GET['activate-multi'] ) ) || ! current_user_can( 'manage_options' ) || apply_filters( 'urmc_prevent_activation_redirect', false ) ) {
				return;
			}

			$message = urmc_is_compatible();
			if ( 'YES' === $message ) {
				// If the user needs to install, send them to the settings page.
				wp_safe_redirect( admin_url( 'admin.php?page=user-registration-settings&tab=user-registration-mailchimp' ) );
				exit;
			}
		}
	}

	/**
	 * Admin Scripts.
	 */
	public function admin_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script(
			'user-registration-mailchimp-admin',
			URMC()->plugin_url() . '/assets/js/admin/user-registration-mailchimp-admin' . $suffix . '.js',
			array(
				'jquery',
			),
			URMC_VERSION
		);
		wp_enqueue_script( 'user-registration-mailchimp-admin' );

		wp_localize_script(
			'user-registration-mailchimp-admin',
			'ur_mailchimp_params',
			array(
				'ajax_url'                          => admin_url( 'admin-ajax.php' ),
				'ur_mailchimp_account_save'         => wp_create_nonce( 'ur_mailchimp_account_save_nonce' ),
				'ur_mailchimp_account_disconnect'   => wp_create_nonce( 'ur_mailchimp_account_disconnect_nonce' ),
				'i18n_cancel'                       => __( 'CANCEL', 'user-registration-mailchimp' ),
				'i18n_ok'                           => __( 'OK', 'user-registration-mailchimp' ),
				'i18n_disconnect'                   => __( 'Disconnect', 'user-registration-mailchimp' ),
				'i18n_confirmation'                 => __( 'Are you sure you want to delete this mailchimp account?', 'user-registration-mailchimp' ),
				'i18n_new_connection_title'         => __( 'Add a new connection ', 'user-registration-mailchimp' ),
				'i18n_new_connection_html'          => __( '<input type="text" id="ur_mailchimp_new_connection_name" class="swal2-input" placeholder="Enter Connection Name">', 'user-registration-mailchimp' ),
				'i18n_new_connection_button_text'   => __( 'Add Connection', 'user-registration-mailchimp' ),
				'i18n_please_enter_connection_name' => __( 'Please Enter Connection Name', 'user-registration-mailchimp' ),
				'ur_mailchimp_account_lists'        => get_option( 'ur_mailchimp_accounts', array() ),
			)
		);
	}

	/**
	 * Admin Styles.
	 */
	public function admin_styles() {

		wp_register_style( 'user-registration-mailchimp-admin-style', URMC()->plugin_url() . '/assets/css/user-registration-mailchimp-admin-style.css', array(), URMC_VERSION );

		wp_enqueue_style( 'user-registration-mailchimp-admin-style' );

	}

	/**
	 * Render mailchimp Section
	 *
	 * @param  int $form_id Form ID.
	 * @return void
	 */
	public function render_mailchimp_section( $form_id = 0 ) {

		$connected_accounts   = get_option( 'ur_mailchimp_accounts', array() );
		$integration_settings = get_post_meta( $form_id, 'user_registration_mailchimp_integration', true );
		$integration_settings = ! empty( $integration_settings ) ? $integration_settings : array();

		include_once URMC_ABSPATH . 'includes/admin/views/html-admin-form-mailchimp-settings.php';
	}

	/**
	 * Integration account lists HTML.
	 *
	 * @param array $connection Connection data object.
	 * @param int   $form_id Form ID.
	 *
	 * @return WP_Error|string
	 */
	public function output_account_lists( $form_id, $connection = array() ) {

		if ( empty( $connection ) ) {
			return '';
		}
		$lists = URMC_Ajax::api_lists( $connection['api_key'] );
		if ( is_wp_error( $lists ) ) {
			return $lists->get_error_message();
		}
		$output = '<div class="">';

		if ( ! empty( $lists ) ) {
			$output .= '<div class="urmc-mailchimp-list-wrap">';
			$output .= sprintf( '<p>%s</p>', esc_html__( 'Select List', 'user-registration-mailchimp' ) );
			$output .= '<div class="list-container">';

			$output .= '<select id="ur_mailchimp_integration_list_id">';

			foreach ( $lists as $list ) {
				$list_id = ! empty( $connection['list_id'] ) ? $connection['list_id'] : $list['list_id'];
				$output .= sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $list['list_id'] ),
					selected( $list_id, $list['list_id'], false ),
					esc_attr( $list['list_title'] )
				);
			}
			$output .= '</select>';
			$output .= '</div>';
			$output .= '</div>';

			foreach ( $lists as $list ) {
				if ( $list_id === $list['list_id'] ) {
					$list_fields = isset( $list['list_fields'] ) ? json_decode( $list['list_fields'] ) : (object) array();
					$group_list  = array();

					if ( isset( $list_fields->interests ) ) {
						$group_list = $list_fields->interests;
						unset( $list_fields->interests );
					}
					$output .= $this->get_mailchimp_group( $connection, $list['list_id'], $group_list );
					$output .= $this->output_account_fields( $connection, $list['list_id'], $form_id, $list_fields );
					$output .= $this->get_mailchimp_options( $connection, $form_id );
				}
			}
		}
		$output .= '</div>';

		return $output;
	}

	/**
	 * Get Mailchimp Group.
	 *
	 * @param array  $connection Mapped Data.
	 * @param string $list_id List Id.
	 * @param array  $interests_list Groups.
	 */
	public function get_mailchimp_group( $connection, $list_id, $interests_list ) {

		if ( empty( $connection['api_key'] ) || empty( $list_id ) || empty( $interests_list ) ) {
			$mailchimp_group = '<div class="urmc-mailchimp-group-wrap"><div class="urmc-mailchimp-group-list"></div></div>';
			return $mailchimp_group;
		}
		$mailchimp_group           = '';
		$mailchimp_group_list_node = '<label class="urmc-mapping-label">' . esc_html__( 'Select Groups', 'user-registration-mailchimp' ) . '</label>';
		$connected_groups          = isset( $connection['list_group'] ) ? (array) json_decode( $connection['list_group'] ) : array();

		foreach ( $interests_list as $key => $interests ) {
			$mailchimp_group_list_node .= '<div class="ur-mailchimp-group-type urmc-mailchimp-' . $interests->type . '" data-id="' . esc_attr( $interests->id ) . '" data-interests_type="' . esc_attr( $interests->type ) . '"><label class="ur-mailchimp-group-title">' . esc_html( $interests->title ) . '</label>';

			if ( 'checkboxes' === $interests->type || 'radio' === $interests->type || 'hidden' === $interests->type ) {

				foreach ( $interests->groups as $group ) {
					$input_type = 'checkboxes' !== $interests->type ? $interests->type : 'checkbox';
					$selected   = '';

					if ( isset( $connected_groups[ $interests->type ] ) && is_array( $connected_groups[ $interests->type ] ) ) {

						if ( in_array( $group->id, $connected_groups[ $interests->type ], true ) ) {
							$selected = 'checked="checked';
						}
					}
					$mailchimp_group_list_node .= '<div class="ur-check"><input type="' . esc_attr( $input_type ) . '" name="urmc_mailchimp_settings_' . $interests->id . '_' . $interests->type . '[]" id="urmc_mailchimp_settings_' . $group->id . '" value="' . esc_attr( $group->id ) . '"  ' . $selected . ' /> <label for="urmc_mailchimp_settings_' . $group->id . '">' . esc_html( $group->name ) . '</label></div>';
				}
			} elseif ( 'dropdown' === $interests->type ) {
				$mailchimp_group_list_node .= '<select name="urmc_mailchimp_settings_' . $interests->id . '_' . $interests->type . '" id="urmc_mailchimp_settings_dropdown">';

				foreach ( $interests->groups as $group ) {
					$selected = '';
					if ( isset( $connected_groups[ $interests->type ] ) && is_array( $connected_groups[ $interests->type ] ) ) {

						if ( in_array( $group->id, $connected_groups[ $interests->type ], true ) ) {
							$selected = 'selected=selected';
						}
					} else {
						$selected = ( isset( $connected_groups[ $interests->type ] ) && $connected_groups[ $interests->type ] == $group->id ) ? 'selected=selected' : '';
					}
						$mailchimp_group_list_node .= sprintf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $group->id ),
							$selected,
							esc_html( $group->name )
						);
				}
				$mailchimp_group_list_node .= '</select>';
			}
			$mailchimp_group_list_node .= '</div>';
		}
		$mailchimp_group .= '<div class="urmc-mailchimp-group-wrap"><div class="urmc-mailchimp-group-list">' . $mailchimp_group_list_node . '</div></div>';
		return $mailchimp_group;
	}

	/**
	 * Renders Mailchimp Options like double optin and conditional logic
	 *
	 * @param array $connection Connection Data.
	 * @param int   $form_id Form ID.
	 */
	public function get_mailchimp_options( $connection, $form_id ) {
		$output  = '<div class="ur_mailchimp_options_container">';
		$output .= '<h4>' . esc_html__( 'Options ', 'user-registration-mailchimp' ) . '</h4>';
		$output .= '<div class="ur_mailchimp_double_optin_container">';
		$output .= '<p class="ur_mailchimp_double_optin ur-check">';
		$checked = '';

		if ( isset( $connection['double_optin'] ) && ur_string_to_bool( $connection['double_optin'] ) ) {
			$checked = 'checked=checked';
		}
		$output .= '<input class="ur-enable-double-optin" type="checkbox" name="ur_mailchimp_double_optin" id="ur_mailchimp_double_optin" ' . $checked . '>';
		$output .= '<label for="ur_mailchimp_double_optin">Use double optin</label>';
		$output .= '</p>';
		$output .= '</div>';
		$output .= user_registration_pro_render_conditional_logic( $connection, 'mailchimp', $form_id );
		$output .= '</div>';
		return $output;
	}
	/**
	 * Renders Mailchimp Mapped List Fields and Groups
	 *
	 * @param array  $connection Connection Data.
	 * @param string $list_id List ID.
	 * @param int    $form_id Form ID.
	 * @param array  $list_fields List Fields.
	 */
	public function output_account_fields( $connection, $list_id, $form_id, $list_fields ) {

		if ( empty( $connection['api_key'] ) || empty( $list_id ) || empty( $form_id ) || empty( $list_fields ) ) {
			return '';
		}
		$form_settings = ur_get_post_content( $form_id );

		if ( ! empty( $form_settings ) ) {
			$form_fields = $this->get_form_field_data( $form_settings );
			$output      = '';
			$output     .= '<div class="ur_mailchimp_fields">';
			$output     .= sprintf( '<p>%s</p>', esc_html__( 'Map Fields', 'user-registration-mailchimp' ) );
			$output     .= '<table class="wp-list-table widefat striped list-fields">';
			$output     .= sprintf( '<thead><tr><th scope="col" class="column-lists">%s</th><th scope="col" class="column-form-fields">%s</th></thead>', esc_html__( 'List Fields', 'user-registration-mailchimp' ), esc_html__( 'Available Form Fields', 'user-registration-mailchimp' ) );
			$output     .= '<tbody id="the-list">';

			foreach ( $list_fields as $account_field ) {
				$output .= '<tr>';
				$output .= '<td class="column-lists">';
				$output .= esc_html( $account_field->name );

				if ( ! empty( $account_field->required ) && '1' === $account_field->required ) {
					$output .= '<span class="required">*</span>';
				}
				$output  .= '</td><td class="column-form-fields">';
				$disabled = '';

				if ( 'email_address' === $account_field->tag ) {
					$disabled = 'disabled=true';
				}

				$output           .= sprintf( '<select id="%s" %s>', esc_attr( $account_field->tag ), esc_attr( $disabled ) );
				$options           = $this->get_form_field_select( $form_fields, $account_field->tag );
				$output           .= '<option value="">' . esc_html__( 'Ignore this field', 'user-registration-mailchimp' ) . '</option>';
				$connection_fields = isset( $connection['list_fields'] ) ? (array) json_decode( $connection['list_fields'] ) : array();
				foreach ( $options as $option ) {
					$value    = sprintf( '%s', $option['field_name'] );
					$selected = ( isset( $connection_fields[ $account_field->tag ] ) && $connection_fields[ $account_field->tag ] == $option['field_name'] ) ? 'selected=selected' : '';

					if ( 'email_address' === $account_field->tag && 'user_email' === $option['field_key'] ) {
						$selected = 'selected=selected';
					}
					$output .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $value ), $selected, esc_html( $option['label'] ) );
				}
				$output .= '</select>';
				$output .= '</td>';
				$output .= '</tr>';
			}
				$output .= '</tbody>';
				$output .= '</table>';
				$output .= '</div>';
			return $output;
		}
	}

	/**
	 * Getting fields ready for select list options.
	 *
	 * @param array  $form_fields     Form's field array.
	 * @param string $form_field_type Field Type for the specfic form.
	 *
	 * @return array
	 */
	public function get_form_field_select( $form_fields = array(), $form_field_type = '' ) {

		if ( empty( $form_fields ) || empty( $form_field_type ) ) {
			return array();
		}
		$formatted = array();

		foreach ( $form_fields as $form_field ) {
			$formatted[] = array(
				'field_key'  => $form_field->field_key,
				'field_name' => $form_field->general_setting->field_name,
				'tag'        => $form_field_type,
				'label'      => $form_field->general_setting->label,
			);
		}
		return $formatted;
	}

	/**
	 * Save Mailerlte Form settings.
	 *
	 * @param array $post Post Data.
	 *
	 * @return void.
	 */
	public function save_mailchimp_form_settings( $post ) {
		$form_id            = absint( $post['form_id'] );
		$mailchimp_settings = isset( $post['ur_mailchimp_integration'] ) ? wp_unslash( $post['ur_mailchimp_integration'] ) : array();
		// mailchimp settings save.
		update_post_meta( $form_id, 'user_registration_mailchimp_integration', $mailchimp_settings );
	}

	/**
	 * Get Form field data.
	 *
	 * @param array $post_content_array Post Content.
	 * @return array
	 */
	public function get_form_field_data( $post_content_array ) {
		$form_field_data_array = array();
		foreach ( $post_content_array as $field_row ) {
			foreach ( $field_row as $field_column ) {
				foreach ( $field_column as $single_item ) {
					if ( isset( $single_item->field_key ) && ! in_array( $single_item->field_key, $this->ur_mailchimp_exclude_fields() ) ) {
						array_push( $form_field_data_array, $single_item );
					}
				}
			}
		}
		return ( $form_field_data_array );
	}

	/**
	 * List of Mailchimp exclude fields.
	 */
	public function ur_mailchimp_exclude_fields() {

		$fields_to_exclude = array(
			'user_pass',
			'user_confirm_password',
			'user_confirm_email',
			'profile_picture',
			'section_title',
			'html',
			'wysiwyg',
			'file',
		);

		return apply_filters(
			'user_registration_mailchimp_exclude_fields',
			$fields_to_exclude
		);
	}

}

return new URMC_Admin();
