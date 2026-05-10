<?php
/**
 * Handles the admin settings and functionality for the Profile Completeness add-on.
 *
 * @package WPEverest\UserRegistration\ProfileCompleteness\Admin
 *
 * @since 1.0.0
 */

namespace WPEverest\UserRegistration\ProfileCompleteness\Admin;

// use WPEverest\UserRegistration\ProfileCompleteness\Admin\Emails\ProfileCompletionReminderEmail;

/**
 * Class Admin
 *
 * @since 1.0.0
 */
class Admin {

	/**
	 * Admin constructor.
	 * Initializes hooks for admin functionality.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function init_hooks() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'user_registration_after_form_settings', array( $this, 'render_profile_completeness_settings_section' ) );
		add_filter( 'user_registration_form_settings_save', array( $this, 'save_profile_completeness_settings' ), 10, 2 );
		add_action( 'user_registration_after_form_settings_save', array( $this, 'save_profile_completeness_custom_percentage' ), 10, 1 );
		add_action( 'user_registration_content_restriction_add_user_based_logic_field', array( $this, 'add_content_restriction_logic_option' ) );
	}


	/**
	 * Enqueues the admin style.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( 'user-registration-profile-completeness-style', plugins_url( '/assets/css/admin.css', UR_PROFILE_COMPLETENESS_PLUGIN_FILE ), array(), UR_PROFILE_COMPLETENESS_VERSION );
	}

	/**
	 * Enqueues the admin scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {
		// Enqueue admin scripts here.
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'ur-profile-completeness-admin-script', UR_PROFILE_COMPLETENESS_ASSETS_URL . '/js/admin/admin' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
		wp_localize_script(
			'ur-profile-completeness-admin-script',
			'ur_profile_completeness_params',
			array()
		);

		wp_enqueue_script( 'ur-profile-completeness-admin-script' );

	}

	/**
	 * Render Profile Completeness Settings Section.
	 *
	 * @param  int $form_id The Form ID.
	 * @return void
	 */
	public function render_profile_completeness_settings_section( $form_id = 0 ) {

		echo '<div id="profile-completeness-settings" ><h3>' . esc_html__( 'Profile Completeness', 'user-registration-profile-completeness' ) . '</h3>';

		$arguments              = $this->get_profile_completeness_settings( $form_id );
		$fields                 = user_registration_form_data( get_current_user_id(), $form_id );
		$custom_percentage_data = json_decode( get_post_meta( $form_id, 'user_registration_profile_completeness_custom_percentage', true ) );
		$custom_percentage_data = $custom_percentage_data ? $custom_percentage_data : array( '' => '' );

		// Add profile picture fields to the fields if profile picture field is present.
		$form_fields = ur_pro_get_form_fields( $form_id );

		if ( ! is_wp_error( $form_fields ) && isset( $form_fields['user_registration_profile_pic_url'] ) ) {
			$fields ['user_registration_profile_pic_url'] = $form_fields['user_registration_profile_pic_url'];
		}

		foreach ( $arguments as $args ) {
			user_registration_form_field( $args['id'], $args, get_post_meta( $form_id, $args['id'], true ) );

			if ( 'user_registration_profile_completeness_enable_custom_percentage' === $args['id'] ) {
				?>
				<div class="form-row" id="user_registration_profile_completeness_custom_percentage_field" data-priority="">
					<div>
						<?php esc_html_e( 'Remaining Percentage', 'user-registration-profile-completeness' ); ?>
						<span class="user-registration-help-tip tooltipstered" data-tip="<?php esc_html_e( 'The remaining percentage value will be equally divided to all other fields.', 'user-registration-profile-completeness' ); ?>"></span>
						&nbsp;:&nbsp;<strong><span class="ur-profile-completeness-remaining-percentage">100</span>%</strong>
					</div>
					<ul class="ur-options-list user-registration-profile-completeness-custom-percentage">
						<?php
						foreach ( $custom_percentage_data as $field_name => $percentage ) {
							?>
						<li>
							<div class="">
								<select class="user-registration-profile-completeness-custom-percentage-field" name="user_registration_profile_completeness_custom_percentage_field[]" data-field="options">
									<option value=""><?php esc_html_e( '-- Select Field --', 'user-registration-profile-completeness' ); ?></option>
									<?php
									if ( ! empty( $fields ) ) {
										foreach ( $fields as $index => $field ) {
											$index = str_replace( 'user_registration_', '', $index );
											if ( isset( $field['field_key'] ) && 'captcha' === $field['field_key'] ) {
												continue;
											}
											?>
												<option value="<?php echo esc_attr( $index ); ?>" data-type="<?php echo isset( $field['type'] ) ? esc_attr( $field['type'] ) : '' ; ?>" <?php selected( $field_name, $index ); ?>> <?php /* translators: %s - field label. */  printf( esc_html__( '%s', 'user-registration-profile-completeness' ), isset( $field['label'] ) ? $field['label'] : '' ); // phpcs:ignore ?> </option>
											<?php
										}
									}
									?>
								</select>
							</div>
							<div class="user-registration-profile-completeness-custom-percentage-value-wrapper">
								<input name="user_registration_profile_completeness_custom_percentage_value[]" value="<?php echo esc_attr( $percentage ); ?>" class="user-registration-profile-completeness-custom-percentage-value" type="text" data-field="options">
							</div>
							<span class="add"><i class="dashicons dashicons-plus"></i></span>
							<span class="remove"><i class="dashicons dashicons-minus"></i></span>
						</li>
							<?php
						}
						?>
					</ul>
				</div>
				<?php
			}
		}
		echo '</div>';
	}

	/**
	 * Returns the settings for Profile Completeness.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id The form ID.
	 *
	 * @return array $settings The settings array.
	 */
	public function get_profile_completeness_settings( $form_id ) {
		$arguments = array(
			'form_id'      => $form_id,

			'setting_data' => array(
				array(
					'type'              => 'toggle',
					'label'             => __( 'Enable Profile Completeness', 'user-registration-profile-completeness' ),
					'description'       => '',
					'required'          => false,
					'id'                => 'user_registration_enable_profile_completeness',
					'class'             => array(),
					'custom_attributes' => array(),
					'default'           => ur_get_single_post_meta( $form_id, 'user_registration_enable_profile_completeness', 'false' ),
					'tip'               => __( 'Check this option to enable the profile completeness feature, which allows you to track how complete a user\'s profile is.', 'user-registration-profile-completeness' ),
				),
				array(
					'type'              => 'text',
					'label'             => __( 'Completion Percentage (%)', 'user-registration-profile-completeness' ),
					'description'       => '',
					'required'          => false,
					'id'                => 'user_registration_profile_completeness_completion_percentage',
					'class'             => array(),
					'custom_attributes' => array(),
					'default'           => ur_get_single_post_meta( $form_id, 'user_registration_profile_completeness_completion_percentage', '100%' ),
					'tip'               => __( 'This option allows you to set the percentage of profile completion required for a user to be considered as having completed their profile.', 'user-registration-profile-completeness' ),
				),
				array(
					'type'              => 'toggle',
					'label'             => __( 'Enable Custom Percentage for Each Field', 'user-registration-profile-completeness' ),
					'description'       => '',
					'required'          => false,
					'id'                => 'user_registration_profile_completeness_enable_custom_percentage',
					'class'             => array(),
					'custom_attributes' => array(),
					'default'           => ur_get_single_post_meta( $form_id, 'user_registration_profile_completeness_enable_custom_percentage', 'false' ),
					'tip'               => __( 'Check this option to enable custom percentage completion for each field. This allows to set a different percentage value for each field.', 'user-registration-profile-completeness' ),
				),
				array(
					'type'              => 'toggle',
					'label'             => __( 'Enable Profile Incomplete Notice', 'user-registration-profile-completeness' ),
					'description'       => '',
					'required'          => false,
					'id'                => 'user_registration_profile_completeness_enable_profile_incomplete_notice',
					'class'             => array(),
					'custom_attributes' => array(),
					'default'           => ur_get_single_post_meta( $form_id, 'user_registration_profile_completeness_enable_profile_incomplete_notice', 'false' ),
					'tip'               => __( 'Check this option to display a notice to users if their profile is incomplete.', 'user-registration-profile-completeness' ),
				),
				array(
					'type'              => 'textarea',
					'label'             => __( 'Profile Incomplete Notice Message', 'user-registration-profile-completeness' ),
					'description'       => '',
					'required'          => false,
					'id'                => 'user_registration_profile_completeness_incompletion_notice_message',
					'class'             => array(),
					'custom_attributes' => array(),
					'default'           => ur_get_single_post_meta( $form_id, 'user_registration_profile_completeness_incompletion_notice_message', __( 'Your profile is currently {{profile_completeness}}. Please complete your profile to get the most out of our website.', 'user-registration-profile-completeness' ) ),
					'tip'               => __( 'Enter the message to be displayed to users if their profile is incomplete. Use the smart tag {{profile_completeness}} to display the percentage of completed fields in the user\'s profile.', 'user-registration-profile-completeness' ),
				),
				array(
					'type'              => 'toggle',
					'label'             => __( 'Enable Profile Completion Congrats Email', 'user-registration-profile-completeness' ),
					'description'       => '',
					'required'          => false,
					'id'                => 'user_registration_profile_completeness_enable_email_for_completed_profile',
					'class'             => array(),
					'custom_attributes' => array(),
					'default'           => ur_get_single_post_meta( $form_id, 'user_registration_profile_completeness_enable_email_for_completed_profile', 'false' ),
					'tip'               => __( 'Check this option to send an email notification to users when their profile is completed.', 'user-registration-profile-completeness' ),
				),
			),
		);

		$arguments = apply_filters( 'user_registration_get_profile_completeness_settings', $arguments );

		return $arguments['setting_data'];
	}

	/**
	 * Saves the profile completeness settings for the given form ID.
	 *
	 * @param array $settings The settings array to be updated.
	 *
	 * @param int   $form_id The ID of the form.
	 *
	 * @return array The updated settings array.
	 */
	public function save_profile_completeness_settings( $settings, $form_id = 0 ) {

		$profile_completeness_setting = $this->get_profile_completeness_settings( $form_id );
		$settings                     = array_merge( $settings, $profile_completeness_setting );

		return $settings;
	}

	/**
	 * Saves custom profile completeness percentage data.
	 *
	 * @param array $post An array of POST data.
	 *
	 * @return void
	 */
	public function save_profile_completeness_custom_percentage( $post ) {
		$form_id                          = absint( $post['form_id'] );
		$is_enabled_proffile_completeness = ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_enable_profile_completeness', false ) );
		$is_enabled_custom_percentage     = ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_profile_completeness_enable_custom_percentage', false ) );

		if ( ! $is_enabled_proffile_completeness && ! $is_enabled_custom_percentage ) {
			return;
		}

		$data                   = isset( $post['profile_completeness__custom_percentage'] ) ? wp_unslash( $post['profile_completeness__custom_percentage'] ) : array();
		$custom_percentage_data = array();

		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $item ) {
				if ( 'user_registration_profile_completeness_custom_percentage_field[]' === $item['name'] ) {
					$custom_percentage_data[ sanitize_text_field( $item['value'] ) ] = isset( $data[ $key + 1 ] ) ? sanitize_text_field( $data[ $key + 1 ]['value'] ) : '';
				}
			}
		}

		update_post_meta( $form_id, 'user_registration_profile_completeness_custom_percentage', wp_json_encode( $custom_percentage_data, JSON_UNESCAPED_UNICODE ) );
	}

	/**
	 * Add Profile Completeness logic option in Content Restriction logic options list.
	 *
	 * @return void
	 */
	public function add_content_restriction_logic_option() {
		echo '<option value="profile_completeness">' . esc_html__( 'Profile Completeness', 'user-registration-profile-completeness' ) . '</option>';
	}

}
