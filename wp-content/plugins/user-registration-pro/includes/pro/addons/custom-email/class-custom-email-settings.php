<?php
/**
 * Custom Email Settings Handler
 *
 * @class    Custom_Email_Settings
 * @version
 * @package  UserRegistration/Modules/CustomEmail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Custom_Email_Settings' ) ) :

	class Custom_Email_Settings {

		/**
		 * Data handler instance.
		 *
		 * @var Custom_Email_Data
		 */
		private $data_handler;

		/**
		 * Constructor.
		 *
		 * @param Custom_Email_Data $data_handler Data handler instance.
		 */
		public function __construct( $data_handler ) {
			$this->data_handler = $data_handler;
		}

		/**
		 * Get custom email settings when custom-email section is active.
		 *
		 * @param array $settings Existing settings.
		 * @return array Custom email section settings.
		 */
		public function get_custom_email_email_settings( $settings ) {
			global $current_section;

			if ( empty( $current_section ) && isset( $_GET['section'] ) ) {
				$current_section = sanitize_text_field( wp_unslash( $_GET['section'] ) );
			}

			if ( ! empty( $current_section ) && strpos( $current_section, 'ur_settings_custom_email_' ) === 0 ) {
				$email_id = str_replace( 'ur_settings_custom_email_', '', $current_section );
				$emails   = $this->data_handler->get_custom_emails();

				$email = isset( $emails[ $email_id ] ) ? $emails[ $email_id ] : array(
					'title'       => '',
					'description' => '',
					'enabled'     => false,
				);

				$option_key = 'ur_custom_email_enabled_' . $email_id;
				$enabled_value = isset( $email['enabled'] ) ? $email['enabled'] : false;
				if ( function_exists( 'ur_string_to_bool' ) ) {
					$enabled_value = ur_string_to_bool( $enabled_value );
				} elseif ( is_string( $enabled_value ) ) {
					$enabled_value = ( 'yes' === $enabled_value || '1' === $enabled_value || 'true' === $enabled_value || true === $enabled_value );
				}
				$option_value = $enabled_value ? 'yes' : 'no';
				update_option( $option_key, $option_value );

				return $this->get_custom_email_configuration_settings( $email_id, $email );
			}

			if ( 'custom-email' === $current_section ) {
				return $this->get_custom_email_email_list_section();
			}

			return $settings;
		}

		/**
		 * Get custom email email list section settings.
		 *
		 * @return array Custom email section settings.
		 */
		public function get_custom_email_email_list_section() {
			/**
			 * Filter to add the options on settings.
			 *
			 * @param array Options to be enlisted.
			 */
			$settings = apply_filters(
				'user_registration_custom_email_email_list_section_settings',
				array(
					'title'    => '',
					'sections' => array(
						'email_notification_settings' => array(
							'title'    => __( 'Custom Email', 'user-registration' ),
							'type'     => 'card',
							'button'   => array(
								'button_link'  => '#',
								'button_text'  => __( 'Add New Email', 'user-registration' ),
								'button_type'  => 'ur-add-new-custom-email',
								'button_class' => 'page-title-action ur-add-new-custom-email',
							),
							'settings' => array(
								array(
									'type' => 'custom_email_notification',
									'id'   => 'user_registration_custom_email_notification_settings',
								),
							),
						),
					),
				)
			);

			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			/**
			 * Filter to get the settings.
			 *
			 * @param array $settings Email Setting options to be enlisted.
			 */
			$filtered_settings = apply_filters( 'user_registration_get_custom_email_email_list_section_settings_email', $settings );

			return is_array( $filtered_settings ) ? $filtered_settings : $settings;
		}

		/**
		 * Get custom email configuration settings.
		 *
		 * @param string $email_id Email ID.
		 * @param array  $email    Email data.
		 * @return array Settings array.
		 */
		public function get_custom_email_configuration_settings( $email_id, $email ) {

			if ( empty( $email_id ) ) {
				return array();
			}

			if ( ! is_array( $email ) ) {
				$email = array();
			}

			$settings = apply_filters(
				'user_registration_custom_email_configuration_settings',
				array(
					'title'    => '',
					'sections' => array(
						'custom_email_config' => array(
							'title'        => __( 'Custom Email', 'user-registration' ),
							'type'         => 'card',
							'back_link'    => '<a href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=email&section=custom-email' ) ) . '" class="ur-back-link"><span class="dashicons dashicons-arrow-left-alt2"></span></a>',
							'preview_link' => '<a href="' . esc_url(
								add_query_arg(
									array(
										'ur_custom_email_preview' => $email_id,
									),
									home_url()
								)
							) . '" aria-label="' . esc_attr__( 'Preview Email', 'user-registration' ) . '" class="button user-registration-email-preview" style="min-width:70px;" target="_blank" rel="noreferrer noopener">' . esc_html__( 'Preview', 'user-registration' ) . '</a>',
							'settings'     => array(
								array(
									'title'    => __( 'Enable', 'user-registration' ),
									'desc'     => __( 'Enable or disable this Custom Email.', 'user-registration' ),
									'id'       => 'ur_custom_email_enabled_' . $email_id,
									'type'     => 'toggle',
									'default'  => isset( $email['enabled'] ) && $email['enabled'] ? 'yes' : 'no',
									'autoload' => false,
									'desc_tip' => true,
								),
								array(
									'title'       => __( 'Email Name', 'user-registration' ),
									'desc'        => __( 'Enter a name for this Custom Email.', 'user-registration' ),
									'id'          => 'ur_custom_email_name_' . $email_id,
									'type'        => 'text',
									'css'         => 'min-width:300px;',
									'default'     => isset( $email['name'] ) ? $email['name'] : '',
									'autoload'    => false,
									'desc_tip'    => true,
									'placeholder' => __( 'Enter email name', 'user-registration' ),
									'required'    => true,
								),
								array(
									'id'       => 'ur_custom_email_trigger_event_' . $email_id,
									'desc'     => __( 'Choose the trigger event for when to send this email.', 'user-registration' ),
									'title'    => __( 'Trigger Event', 'user-registration' ),
									'type'     => 'select',
									'options'  => array(
										'member_signs_up' => esc_html__( 'Member Sign Up', 'user-registration' ),
										'membership_expired' => esc_html__( 'Membership Expiry', 'user-registration' ),
										'membership_cancellation' => esc_html__( 'Membership Cancellation', 'user-registration' ),
										'membership_renewal_success' => esc_html__( 'Membership Renewal Success', 'user-registration' ),
										'membership_renewal_failed' => esc_html__( 'Membership Renewal Failed', 'user-registration' ),
										'membership_upgrade' => esc_html__( 'Membership Upgrade', 'user-registration' ),
							//                                      'membership_downgrade' => esc_html__( 'Membership Downgrade', 'user-registration' ),
									),
									'default'  => isset( $email['trigger_event'] ) ? $email['trigger_event'] : 'member_signs_up',
									'css'      => 'min-width:300px;',
									'desc_tip' => true,
									'required' => true,
								),
								array(
									'id'                  => 'ur_custom_email_delivery_timing_' . $email_id,
									'desc'                => __( 'Choose when to send this email.', 'user-registration' ),
									'title'               => __( 'Delivery Timing', 'user-registration' ),
									'type'                => 'radio',
									'class'               => 'ur-modern-radio-group',
									'options'             => array(
										'instant'   => esc_html__( 'Instantly', 'user-registration' ),
										'scheduled' => esc_html__( 'Scheduled', 'user-registration' ),
									),
									'option_descriptions' => array(
										'instant'   => esc_html__( 'Send immediately when the trigger occurs', 'user-registration' ),
										'scheduled' => esc_html__( 'Send at a specific time relative to the trigger', 'user-registration' ),
									),
									'default'             => isset( $email['delivery_timing'] ) ? $email['delivery_timing'] : 'instant',
									'desc_tip'            => true,
									'required'            => true,
								),
								array(
									'id'                   => 'ur_custom_email_duration_' . $email_id,
									'type'                 => 'duration_input',
									'unit_id'              => 'ur_custom_email_duration_unit_' . $email_id,
									'value_id'             => 'ur_custom_email_duration_value_' . $email_id,
									'unit_options'         => array(
										// 'second' => esc_html__( 'Second(s)', 'user-registration' ),
										'minutes' => esc_html__( 'Minute(s)', 'user-registration' ),
										'days'   => esc_html__( 'Day(s)', 'user-registration' ),
										'weeks'  => esc_html__( 'Week(s)', 'user-registration' ),
										'months' => esc_html__( 'Month(s)', 'user-registration' ),
									),
									'before_after_id'      => 'ur_custom_email_duration_before_after_' . $email_id,
									'before_after_options' => array(
										'after'  => esc_html__( 'After', 'user-registration' ),
										'before' => esc_html__( 'Before', 'user-registration' ),
									),
									'default_unit'         => isset( $email['duration_unit'] ) ? $email['duration_unit'] : 'days',
									'default_value'        => isset( $email['duration_value'] ) ? $email['duration_value'] : 1,
									'default_before_after' => isset( $email['before_after'] ) ? $email['before_after'] : 'after',
									'css'                  => 'min-width:300px;',
									'class'                => 'ur-duration-field-wrapper',
									'desc_tip'             => true,
									'required'             => false,
								),
								array(
									'id'       => 'ur_custom_email_send_to_' . $email_id,
									'desc'     => __( 'Choose who should receive this email.', 'user-registration' ),
									'title'    => __( 'Send To', 'user-registration' ),
									'type'     => 'select',
									'options'  => array(
										'all_members' => esc_html__( 'All Members', 'user-registration' ),
										'specific_memberships' => esc_html__( 'Specific Memberships', 'user-registration' ),
										'admin'       => esc_html__( 'Admin', 'user-registration' ),
									),
									'default'  => isset( $email['send_to'] ) ? $email['send_to'] : 'all_members',
									'css'      => 'min-width:300px;',
									'desc_tip' => true,
									'required' => true,
								),
								array(
									'id'       => 'ur_custom_email_membership_' . $email_id,
									'desc'     => __( 'Choose membership plans for this email. Select "All subscriptions" to send to all members.', 'user-registration' ),
									'title'    => __( 'Membership', 'user-registration' ),
									'type'     => 'multiselect-v2',
									'options'  => $this->get_membership_plans_options(),
									'default'  => isset( $email['membership'] ) ? $email['membership'] : array( 'all' ),
									'desc_tip' => true,
									'required' => true,
								),
								array(
									'title'     => __( 'Override default email for this trigger to selected member', 'user-registration' ),
									'desc'      => __( 'Override the default email for this trigger and send to the selected member instead.', 'user-registration' ),
									'id'        => 'ur_custom_email_override_default_' . $email_id,
									'type'      => 'toggle',
									'default'   => isset( $email['override_default'] ) && $email['override_default'] ? true : false,
									'autoload'  => false,
									'desc_tip'  => true,
									'class'     => 'ur-override-default-enable',
									'row_class' => 'ur-override-default-wrapper',
								),
								array(
									'title'    => __( 'Email Subject', 'user-registration' ),
									'desc'     => __( 'The email subject you want to customize.', 'user-registration' ),
									'id'       => 'ur_custom_email_subject_' . $email_id,
									'type'     => 'text',
									'default'  => isset( $email['email_subject'] ) ? $email['email_subject'] : __( 'Custom Email', 'user-registration' ),
									'css'      => 'min-width:300px;',
									'autoload' => false,
									'desc_tip' => true,
									'required' => true,
								),
								array(
									'title'    => __( 'Email Content', 'user-registration' ),
									'desc'     => __( 'The email content you want to customize.', 'user-registration' ),
									'id'       => 'ur_custom_email_content_' . $email_id,
									'type'     => 'tinymce',
									'default'  => isset( $email['email_content'] ) ? $email['email_content'] : $this->get_default_email_content(),
									'css'      => '',
									'autoload' => false,
									'desc_tip' => true,
									'show-ur-registration-form-button' => false,
									'show-smart-tags-button' => true,
									'show-reset-content-button' => true,
									'required' => true,
								),
							),
						),
					),
				),
				$email_id,
				$email
			);

			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			return $settings;
		}

		/**
		 * Get default email content for custom email.
		 *
		 * @return string Default email content.
		 */
		public function get_default_email_content() {
			$body_content = __(
				'',
				'user-registration'
			);

			/**
			 * Filter to modify the default email content for custom email.
			 *
			 * @param string $message Default email content.
			 */
			return apply_filters( 'user_registration_custom_email_default_email_content', $body_content );
		}

		/**
		 * Get membership plans options for membership field.
		 *
		 * @return array Membership plans as options array.
		 */
		private function get_membership_plans_options() {
			$options = array(
				'all' => esc_html__( 'All Memberships', 'user-registration' ),
			);

			if ( ! function_exists( 'ur_check_module_activation' ) || ! ur_check_module_activation( 'membership' ) ) {
				return $options;
			}

			try {
				$membership_plans = get_posts(
					array(
						'post_type'      => 'ur_membership',
						'posts_per_page' => -1,
						'post_status'    => 'publish',
						'orderby'        => 'title',
						'order'          => 'ASC',
					)
				);

				if ( ! empty( $membership_plans ) && is_array( $membership_plans ) ) {
					foreach ( $membership_plans as $plan ) {
						if ( isset( $plan->ID ) && isset( $plan->post_title ) ) {
							$options[ $plan->ID ] = esc_html( $plan->post_title );
						}
					}
				}
			} catch ( Exception $e ) {
				return $options;
			}

			/**
			 * Filter to modify membership plans options.
			 *
			 * @param array $options Membership plans options.
			 */
			$filtered_options = apply_filters( 'user_registration_custom_email_membership_plans_options', $options );

			return is_array( $filtered_options ) ? $filtered_options : $options;
		}
	}

endif;
