<?php
/**
 * Handles the frontend functionality for User Registration Profile Completeness.
 *
 * @package WPEverest\UserRegistration\ProfileCompleteness\Frontend
 *
 * @since 1.0.0
 */

namespace WPEverest\UserRegistration\ProfileCompleteness\Frontend;

use WPEverest\UserRegistration\ProfileCompleteness\Admin\Emails\UR_Settings_Profile_Completion_Congrats_Email;
use \UR_Emailer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *  Class Frontend.
 *
 * @since 1.0.0
 */
class Frontend {

	/**
	 * The form ID.
	 *
	 * @var int The form ID.
	 */
	private $form_id;

	/**
	 * The user ID.
	 *
	 * @var int The user ID.
	 */
	private $user_id;

	/**
	 * The percentage required to complete the profile.
	 *
	 * @var float The percentage required to complete the profile.
	 */
	private $percentage_required_to_complete;

	/**
	 * The completed profile percentage.
	 *
	 * @var float The completed profile percentage.
	 */
	private $completed_profile_percentage = 0;

	/**
	 * Indicates if profile completeness feature is enabled.
	 *
	 * @var bool The profile completeness feature enable/disable status.
	 */
	private $is_enabled_proffile_completeness;

	/**
	 * An array of profile fields.
	 *
	 * @var array An array of profile fields.
	 */
	public $profile_fields;

	/**
	 * An array of custom percentage data.
	 *
	 * @var array An array of custom percentage data.
	 */
	public $custom_percentage_data;

	/**
	 * Constructor for the Frontend class.
	 *
	 * Initializes the class properties, and sets up the required action hooks and filters.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->user_id                          = get_current_user_id();
		$this->form_id                          = ur_get_form_id_by_userid( $this->user_id );
		$this->is_enabled_proffile_completeness = ur_string_to_bool( ur_get_single_post_meta( $this->form_id, 'user_registration_enable_profile_completeness', false ) );

		if ( ! $this->is_enabled_proffile_completeness ) {
			return;
		}

		$this->percentage_required_to_complete = floatval( ur_get_single_post_meta( $this->form_id, 'user_registration_profile_completeness_completion_percentage', '100%' ) );
		$this->percentage_required_to_complete = ! $this->percentage_required_to_complete ? (float) 100 : $this->percentage_required_to_complete;
		$this->completed_profile_percentage    = get_user_meta( $this->user_id, 'user_registration_profile_completeness_completed_profile_percentage', true );

		add_action( 'user_registration_my_account_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ), 10, 2 );
		add_action( 'user_registration_before_edit_profile_form_data', array( $this, 'set_profile_fields' ), 10, 2 );

		add_action( 'user_registration_before_edit_profile_form_data', array( $this, 'check_call_for_set_completed_profile_percentage' ), 10, 2 );

		if ( ! $this->completed_profile_percentage ) {
			add_action( 'user_registration_before_edit_profile_form_data', array( $this, 'set_completed_profile_percentage' ), 10, 2 );
		}

		add_action( 'user_registration_before_profile_detail_title', array( $this, 'display_profile_progressbar' ) );
		add_action( 'user_registration_save_profile_details', array( $this, 'set_completed_profile_percentage' ), 9, 2 );
		add_filter( 'user_registration_profile_update_response', array( $this, 'set_profile_progress' ), 9, 1 );
		add_action( 'user_registration_account_content', array( $this, 'display_profile_incomplete_notice' ), 10, 1 );
		add_action( 'user_registration_profile_completeness_after_completed_profile_percentage_set', array( $this, 'add_hidden_input_field_for_profile_completed_value' ), 999, 5 );
		add_action( 'user_registration_profile_completeness_after_completed_profile_percentage_set', array( $this, 'send_profile_completion_congrats_email' ), 10, 5 );
		add_filter( 'user_registration_add_smart_tags', array( $this, 'add_profile_completeness_smart_tags' ), 10, 2 );
	}

	/**
	 * Load script files and localization for js.
	 *
	 * @param array $form_data_array Form Data.
	 * @param int   $form_id Form Id.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_scripts( $form_data_array, $form_id ) {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'user-registration-profile-completeness-frontend-style', plugins_url( '/assets/css/user-registration-profile-completeness.css', UR_PROFILE_COMPLETENESS_PLUGIN_FILE ), array(), UR_PROFILE_COMPLETENESS_VERSION );
		wp_enqueue_script( 'user-registration-profile-completeness-frontend-script', plugins_url( '/assets/js/frontend/user-registration-profile-completeness' . $suffix . '.js', UR_PROFILE_COMPLETENESS_PLUGIN_FILE ), array( 'jquery' ), UR_PROFILE_COMPLETENESS_VERSION, true );
		wp_localize_script(
			'user-registration-profile-completeness-frontend-script',
			'user_registrations_profile_completeness_params',
			array()
		);
	}

	/**
	 * Sets the profile fields by retrieving data from the user registration form.
	 *
	 * @since 1.0.0
	 */
	public function set_profile_fields( $user_id, $form_id ) {
		// Retrieves user registration form data.
		$form_data = user_registration_form_data( $this->user_id, $this->form_id );

		// Extracts profile tab field names from the form data.
		$profile_tab_field_names = array_map(
			function( $value ) {
				return substr( $value, strlen( 'user_registration_' ) );
			},
			array_keys( $form_data )
		);

		// Sets the profile fields and custom percentage data properties.
		$this->profile_fields         = $profile_tab_field_names;
		$this->custom_percentage_data = (array) json_decode( get_post_meta( $this->form_id, 'user_registration_profile_completeness_custom_percentage', true ) );
	}

	/**
	 * Checks if there is any changes in profile tab fields or custom percentage data, and calls the `set_completed_profile_percentage` function if there is.
	 *
	 * @since 1.0.0
	 */
	public function check_call_for_set_completed_profile_percentage( $user_id, $form_id ) {
		if ( ! $this->profile_fields && ! $this->custom_percentage_data ) {
			$this->set_profile_fields( $user_id, $form_id );
		}

		$profile_fields_name    = ur_get_single_post_meta( $this->form_id, 'user_registration_previous_profile_tab_fields_name', array() );
		$custom_percentage_data = ur_get_single_post_meta( $this->form_id, 'user_registration_previous_profile_completeness_custom_percentage_data', array() );

		if ( ! is_array( $profile_fields_name ) && ! is_array( $custom_percentage_data ) ) {
			$this->set_completed_profile_percentage( $this->user_id, $this->form_id );
			return;
		}

		// Call the `set_completed_profile_percentage` function only if there is any changes in profile tab fields or custom percentage data.
		if ( ! empty( array_diff_assoc( $this->profile_fields, $profile_fields_name ) ) || ! empty( array_diff_assoc( $this->custom_percentage_data, $custom_percentage_data ) ) ) {
			$this->set_completed_profile_percentage($this->user_id, $this->form_id );
		}
	}

	/**
	 * Sets the completed profile percentage.
	 *
	 * @since 1.0.0
	 */
	public function set_completed_profile_percentage( $user_id, $form_id ) {

		if ( ! $this->profile_fields && ! $this->custom_percentage_data ) {
			$this->set_profile_fields( $user_id, $form_id );
		}

		$custom_percentage_data = $this->custom_percentage_data;
		$user_data              = get_userdata( $this->user_id );
		$user_extra_data        = ur_get_user_extra_fields( $this->user_id );
		$form_data              = user_registration_form_data( $this->user_id, $this->form_id );

		// Retrieve the current value of urcl_hide_fields from user meta.
		$urcl_hide_fields = get_user_meta( $this->user_id, 'urcl_hide_fields', true );

		// Get the new value from $_POST['urcl_hide_fields'].
		$new_urcl_hide_fields = isset( $_POST['urcl_hide_fields'] ) ? (array) json_decode( stripslashes( $_POST['urcl_hide_fields'] ), true ) : array(); //phpcs:ignore

		// Update user meta only if the new value is different from the current value.
		if ( array_diff( $new_urcl_hide_fields, (array) $urcl_hide_fields ) || array_diff( (array) $urcl_hide_fields, $new_urcl_hide_fields ) ) {
			update_user_meta( $this->user_id, 'urcl_hide_fields', $new_urcl_hide_fields );
		}

		$all_field_names         = array_keys( ur_get_meta_key_label( $this->form_id ) );
		$profile_tab_field_names = array_diff( $this->profile_fields, (array) $urcl_hide_fields );
		$wp_default_fields       = array_diff( $profile_tab_field_names, array_keys( $user_extra_data ) );

		$excluded_fields = array(
			'hidden',
			'section_title',
			'html',
			'captcha',
		);

		foreach ( $profile_tab_field_names as $key => $profile_tab_field_name ) {
			$profile_tab_field_name = 'user_registration_' . $profile_tab_field_name;

			if ( isset( $form_data[ $profile_tab_field_name ] ) && in_array( $form_data[ $profile_tab_field_name ]['field_key'], $excluded_fields, true ) ) {
				unset( $profile_tab_field_names[ $key ] );
			}
		}

		if ( in_array( 'profile_pic_url', $all_field_names, true ) && ! in_array( 'profile_pic_url', $profile_tab_field_names, true ) ) {
			$profile_tab_field_names [] = 'profile_pic_url';
		}

		if ( (bool) ur_get_single_post_meta( $this->form_id, 'user_registration_profile_completeness_enable_custom_percentage', 'no' ) && count( $custom_percentage_data ) ) {
			$selected_field_names      = array_diff( array_keys( $custom_percentage_data ), (array) $urcl_hide_fields );
			$total_selected_percentage = array_sum( array_map( 'floatval', $custom_percentage_data ) );
			$remaining_percentage      = 100 - $total_selected_percentage;

			if ( $remaining_percentage > 0 ) {
				$unselected_field_names = array_diff( $profile_tab_field_names, $selected_field_names );
				$unselected_field_count = count( $unselected_field_names );

				if ( $unselected_field_count ) {
					$percentage_per_unselected_field = round( $remaining_percentage / $unselected_field_count, 2 );
					foreach ( $unselected_field_names as $field_name ) {
						$custom_percentage_data[ $field_name ] = $percentage_per_unselected_field;
					}
				}
			}
		} else {
			$percentage_per_field   = round( 100 / count( $profile_tab_field_names ), 2 );
			$custom_percentage_data = array_fill_keys( $profile_tab_field_names, $percentage_per_field );
		}

		$field_progress_value = array();
		foreach ( $custom_percentage_data as $field_name => $percentage_per_field ) {
			$percentage_per_field = floatval( $percentage_per_field );

			if ( in_array( $field_name, $user_extra_data, true ) ) {
				if ( ( isset( $user_extra_data[ $field_name ][0] ) && ! empty( $user_extra_data[ $field_name ][0] ) ) || ! empty( $user_extra_data[ $field_name ] ) ) {
					$field_progress_value[ $field_name ] = $percentage_per_field;
				} else {
					$field_progress_value[ $field_name ] = 0;
				}
			} elseif ( in_array( $field_name, $wp_default_fields, true ) ) {
				if ( in_array( $field_name, array( 'display_name', 'user_email', 'user_nicename', 'user_login', 'user_url' ), true ) ) {
					if ( 'user_email' === $field_name || 'user_login' === $field_name || ! empty( $user_data->$field_name ) ) {
						$field_progress_value[ $field_name ] = $percentage_per_field;
					} else {
						$field_progress_value[ $field_name ] = 0;
					}
				} else {
					if ( ! empty( get_user_meta( $this->user_id, $field_name, true ) ) || ! empty( get_user_meta( $this->user_id, 'user_registration_' . $field_name, true ) ) ) {
						$field_progress_value[ $field_name ] = $percentage_per_field;
					} else {
						$field_progress_value[ $field_name ] = 0;
					}
				}
			} else {
				if ( ! empty( get_user_meta( $this->user_id, 'user_registration_' . $field_name, true ) ) ) {
					$field_progress_value[ $field_name ] = $percentage_per_field;
				} else {
					$field_progress_value[ $field_name ] = 0;
				}
			}
		}
		$this->completed_profile_percentage = array_sum( $field_progress_value );

		update_user_meta( $this->user_id, 'user_registration_profile_completeness_completed_profile_percentage', $this->completed_profile_percentage );

		$user_data = array_merge( (array) $user_data->data, $user_extra_data );

		// Get form data as per need by the {{all_fields}} smart tag.
		$valid_form_data = array();
		foreach ( $form_data as $key => $value ) {
			$new_key = trim( str_replace( 'user_registration_', '', $key ) );

			if ( isset( $user_data[ $new_key ] ) ) {
				$valid_form_data[ $new_key ] = (object) array(
					'field_type'   => $value['type'],
					'label'        => $value['label'],
					'field_name'   => $value['field_key'],
					'value'        => $user_data[ $new_key ],
					'extra_params' => array(
						'label'     => $value['label'],
						'field_key' => $value['field_key'],
					),
				);
			}
		}

		update_post_meta( $this->form_id, 'user_registration_previous_profile_tab_fields_name', $profile_tab_field_names );
		update_post_meta( $this->form_id, 'user_registration_previous_profile_completeness_custom_percentage_data', $custom_percentage_data );

		do_action( 'user_registration_profile_completeness_after_completed_profile_percentage_set', $this->percentage_required_to_complete, $this->completed_profile_percentage, $this->user_id, $this->form_id, $valid_form_data );
	}

	/**
	 * Displays a notice if the user's profile is incomplete.
	 *
	 * @since 1.0.0
	 */
	public function display_profile_incomplete_notice() {

		$this->check_call_for_set_completed_profile_percentage( $this->user_id, $this->form_id );

		$is_enabled_profile_incomplete_notice = true === (bool) ur_get_single_post_meta( $this->form_id, 'user_registration_profile_completeness_enable_profile_incomplete_notice', 'no' ) ? true : false;

		if ( is_user_logged_in() && $this->is_enabled_proffile_completeness && $is_enabled_profile_incomplete_notice ) {
			if ( ! $this->completed_profile_percentage ) {
				return;
			}
			$profile_incomplete_notice_msg = ur_get_single_post_meta( $this->form_id, 'user_registration_profile_completeness_incompletion_notice_message', __( 'Your profile is currently {{profile_completeness}}. Please complete your profile to get the most out of our website.', 'user-registration-profile-completeness' ) );

			if ( empty( $profile_incomplete_notice_msg ) ) {
				$profile_incomplete_notice_msg = __( 'Your profile is currently {{profile_completeness}}. Please complete your profile to get the most out of our website.', 'user-registration-profile-completeness' );
			}

			if ( strpos( $profile_incomplete_notice_msg, '{{profile_completeness}}' ) !== false ) {
				$profile_incomplete_notice_msg = str_replace( '{{profile_completeness}}', '<span class="ur-profile-completeness">' . round( $this->completed_profile_percentage ) . '%</span>', $profile_incomplete_notice_msg );
			} else {
				$profile_incomplete_notice_msg = $profile_incomplete_notice_msg . '<span class="ur-not-profile-completeness"></span>';
			}

			ur_add_notice( $profile_incomplete_notice_msg, 'notice' );
		}
	}

	/**
	 * Adds a hidden input field to the page if the user is logged in and the profile completeness feature is enabled.
	 *
	 * @since 1.0.0
	 */
	public function add_hidden_input_field_for_profile_completed_value( $percentage_required_to_complete, $completed_profile_percentage, $user_id, $form_id, $valid_form_data ) {
		if ( is_user_logged_in() && $this->is_enabled_proffile_completeness ) {

			if ( isset( $_POST['action'] ) && 'user_registration_update_profile_details' === $_POST['action'] ) {

				$profile_complete_data = array(
					'completed_profile_percentage'  => round( $this->completed_profile_percentage ),
					'is_profile_completed'          => $this->is_profile_completed(),
					'profile_completion_percentage' => $this->percentage_required_to_complete,
				);
				add_filter( 'user_registration_profile_update_response' , function ( $response ) use ( $profile_complete_data ) {
					$response['profile_complete_data'] = $profile_complete_data;
					return $response;
				});
			} else {
				echo wp_kses(
					'<input type="hidden" id="ur-profile-completeness-hidden" data-completed-profile-percentage="' . esc_attr( round( (float) $this->completed_profile_percentage ) ) . '" data-is-profile-completed="' . esc_attr( $this->is_profile_completed() ? true : false ) . '" data-profile-completion-percentage="' . esc_attr( $this->percentage_required_to_complete ) . '">',
					array(
						'input' => array(
							'type'                      => true,
							'data-completed-profile-percentage' => true,
							'data-is-profile-completed' => true,
							'data-profile-completion-percentage' => true,
							'id'                        => true,
						),
					)
				);
			}

		}
	}

	/**
	 * Displays a progress bar input element indicating the percentage completion of the user's profile.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function display_profile_progressbar() {
		if ( is_user_logged_in() && $this->is_enabled_proffile_completeness ) :
			?>
			<div class="ur-profile-completion-detail">
				<div class="ur-profile-completion-detail__circular-bar" role="progressbar" aria-valuemin="<?php echo esc_attr( 0 ); ?>" aria-valuemax="<?php echo esc_attr( $this->percentage_required_to_complete ); ?>" style="--value: <?php echo esc_attr( round( $this->completed_profile_percentage ) ); ?>"></div>
				<h3 class="ur-profile-completion-detail__title">
					<?php esc_html_e( 'Profile Completion', 'user-registration-profile-completeness' ); ?>
				</h3>
			</div>
			<?php
		endif;
	}

	/**
	 * Sets progress data for a user's profile completeness.
	 *
	 * @param array $response The response.
	 *
	 * @return array The updated response with profile completeness data.
	 *
	 * @since 1.0.0
	 */
	public function set_profile_progress( $response ) {
		$response['profile_complete_data'] = array(
			'completed_profile_percentage'  => round( $this->completed_profile_percentage ),
			'is_profile_completed'          => $this->is_profile_completed(),
			'profile_completion_percentage' => $this->percentage_required_to_complete,
		);

		return $response;
	}

	/**
	 * Check if the user's profile is completed.
	 *
	 * @return bool Returns true if the user's profile is completed, false otherwise.
	 */
	private function is_profile_completed() {
		return round( (float) $this->completed_profile_percentage ) < round( (float) $this->percentage_required_to_complete ) ? false : true;
	}

	/**
	 * Sends a congratulatory email to a user for completing their profile.
	 *
	 * @param float $percentage_required_to_complete The percentage required to complete the profile.
	 *
	 * @param float $completed_percentage The percentage of the user's completed profile.
	 *
	 * @param int   $user_id The ID of the user who completed their profile.
	 *
	 * @param int   $form_id The ID of the form used to complete the user's profile.
	 *
	 * @param array $form_data An array of the form's data.
	 *
	 * @return void
	 */
	public function send_profile_completion_congrats_email( $percentage_required_to_complete, $completed_percentage, $user_id, $form_id, $form_data ) {
		$is_enabled_profile_completion_congrats_email = ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_profile_completeness_enable_email_for_completed_profile', false ) );

		if ( $is_enabled_profile_completion_congrats_email && $this->is_profile_completed() && ! get_user_meta( $user_id, 'user_registration_profile_completeness_profile_completion_congrats_email', true ) ) {

			$email_setting = new UR_Settings_Profile_Completion_Congrats_Email();

			$user     = get_user_by( 'ID', $user_id );
			$username = $user->data->user_login;
			$email    = $user->data->user_email;

			list( $name_value, $data_html ) = ur_parse_name_values_for_smart_tags( $user_id, $form_id, $form_data );
			$values                         = array(
				'username'             => $username,
				'email'                => $email,
				'all_fields'           => $data_html,
				'profile_completeness' => $completed_percentage . '%',
			);

			$header  = 'From: ' . UR_Emailer::ur_sender_name() . ' <' . UR_Emailer::ur_sender_email() . ">\r\n";
			$header .= 'Reply-To: ' . UR_Emailer::ur_sender_email() . "\r\n";
			$header .= "Content-Type: text/html\r\n; charset=UTF-8";
			$subject = get_option( 'user_registration_profile_completeness_congrats_email_subject', __( 'Congratulations! You Have Completed Your Profile - {{blog_info}}', 'user-registration-profile-completeness' ) );
			$message = get_option( 'user_registration_profile_completion_congrats_email_content', $email_setting->user_registration_get_profile_completion_congrats_email() );

			$message = UR_Emailer::parse_smart_tags( $message, $values, $name_value );
			$subject = UR_Emailer::parse_smart_tags( $subject, $values, $name_value );

			$template_id = ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' );

			UR_Emailer::user_registration_process_and_send_email( $email, $subject, $message, $header, '', $template_id );

			update_user_meta( $user_id, 'user_registration_profile_completeness_profile_completion_congrats_email', true );
		}
	}

	/**
	 * Adds the smart tag to the default values array.
	 *
	 * @param array  $default_values The array of default values for smart tags.
	 * @param string $email The email associated with the smart tag.
	 * @return array The updated default values.
	 */
	public function add_profile_completeness_smart_tags( $default_values, $email ) {
		$default_values [] = array( 'profile_completeness', '' );
		return $default_values;
	}
}
