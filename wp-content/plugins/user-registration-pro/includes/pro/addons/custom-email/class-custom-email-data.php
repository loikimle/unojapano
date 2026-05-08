<?php
/**
 * Custom Email Data Handler
 *
 * @class    Custom_Email_Data
 * @version
 * @package  UserRegistration/Modules/CustomEmail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Custom_Email_Data' ) ) :

	class Custom_Email_Data {

		/**
		 * Save handler instance.
		 *
		 * @var Custom_Email_Save
		 */
		private $save_handler;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->save_handler = new Custom_Email_Save();
		}

		/**
		 * Get custom emails from options.
		 *
		 * @return array Custom emails.
		 */
		public function get_custom_emails() {
			return get_option( 'user_registration_custom_emails', array() );
		}

		/**
		 * Save custom email.
		 *
		 * @param string $email_id Email ID.
		 * @param array  $data        Email data.
		 * @return bool Success status.
		 */
		public function save_custom_email( $email_id, $data ) {
			$emails              = $this->get_custom_emails();
			$emails[ $email_id ] = $data;
			return update_option( 'user_registration_custom_emails', $emails );
		}

		/**
		 * Save custom email settings.
		 */
		public function save_custom_email_settings() {
			global $current_section;

			$section = $current_section;
			if ( empty( $section ) && isset( $_REQUEST['section'] ) ) {
				$section = sanitize_text_field( wp_unslash( $_REQUEST['section'] ) );
			}

			if ( 'custom-email' === $section ) {
				$this->save_email_statuses();
			}

			if ( ! empty( $section ) && strpos( $section, 'ur_settings_custom_email_' ) === 0 ) {
				$email_id = str_replace( 'ur_settings_custom_email_', '', $section );
				$emails   = $this->get_custom_emails();

				$old_email = isset( $emails[ $email_id ] ) ? $emails[ $email_id ] : array();
				$email     = $this->save_handler->process_email_data( $email_id, $emails );

				if ( false === $email ) {
					return;
				}

				$old_enabled = ! empty( $old_email ) && isset( $old_email['enabled'] ) ? $old_email['enabled'] : false;
				if ( function_exists( 'ur_string_to_bool' ) ) {
					$old_enabled = ur_string_to_bool( $old_enabled );
				} elseif ( is_string( $old_enabled ) ) {
					$old_enabled = ( 'yes' === $old_enabled || '1' === $old_enabled || 'true' === $old_enabled || true === $old_enabled );
				}

				$new_enabled = isset( $email['enabled'] ) ? $email['enabled'] : false;
				if ( function_exists( 'ur_string_to_bool' ) ) {
					$new_enabled = ur_string_to_bool( $new_enabled );
				} elseif ( is_string( $new_enabled ) ) {
					$new_enabled = ( 'yes' === $new_enabled || '1' === $new_enabled || 'true' === $new_enabled || true === $new_enabled );
				}

				$this->save_custom_email( $email_id, $email );

				$option_key = 'ur_custom_email_enabled_' . $email_id;
				$option_value = $new_enabled ? 'yes' : 'no';
				update_option( $option_key, $option_value );

				$schedule_changed          = false;
				$timing_changed_to_instant = false;
				$email_disabled            = false;
				$old_before_after          = '';
				if ( ! empty( $old_email ) ) {
					$old_duration_unit   = isset( $old_email['duration_unit'] ) ? $old_email['duration_unit'] : 'days';
					$old_duration_value  = isset( $old_email['duration_value'] ) ? absint( $old_email['duration_value'] ) : 1;
					$old_before_after    = isset( $old_email['before_after'] ) ? $old_email['before_after'] : 'after';
					$old_delivery_timing = isset( $old_email['delivery_timing'] ) ? $old_email['delivery_timing'] : 'instant';

					$new_duration_unit   = isset( $email['duration_unit'] ) ? $email['duration_unit'] : 'days';
					$new_duration_value  = isset( $email['duration_value'] ) ? absint( $email['duration_value'] ) : 1;
					$new_before_after    = isset( $email['before_after'] ) ? $email['before_after'] : 'after';
					$new_delivery_timing = isset( $email['delivery_timing'] ) ? $email['delivery_timing'] : 'instant';

					if ( $old_duration_unit !== $new_duration_unit ||
						$old_duration_value !== $new_duration_value ||
						$old_before_after !== $new_before_after ||
						$old_delivery_timing !== $new_delivery_timing ) {
						$schedule_changed = true;
					}

					if ( 'scheduled' === $old_delivery_timing && 'instant' === $new_delivery_timing ) {
						$timing_changed_to_instant = true;
					}

					if ( $old_enabled && ! $new_enabled ) {
						$email_disabled = true;
					}
				} elseif ( ! $new_enabled ) {
						$email_disabled = true;
				}

				if ( class_exists( 'Custom_Email_Sender' ) ) {
					$sender = new Custom_Email_Sender( $this );

					if ( $email_disabled ) {
						$sender->unschedule_all_crons_for_email( $email_id );
					} elseif ( $timing_changed_to_instant ) {
						$sender->unschedule_all_crons_for_email( $email_id );
					} elseif ( $schedule_changed && isset( $email['delivery_timing'] ) && 'scheduled' === $email['delivery_timing'] ) {
						$sender->reschedule_crons_for_email( $email_id, $email );
					}

					if ( isset( $email['trigger_event'] ) && 
						'membership_expired' === $email['trigger_event'] &&
						isset( $email['delivery_timing'] ) && 
						'scheduled' === $email['delivery_timing'] ) {
						$email_before_after = isset( $email['before_after'] ) ? $email['before_after'] : 'after';
						
						if ( 'before' === $email_before_after ) {
							$should_schedule = false;
							if ( empty( $old_email ) ) {
								$should_schedule = true;
							} elseif ( 'before' !== $old_before_after ) {
								$should_schedule = true;
							} elseif ( $new_enabled && ! $old_enabled ) {
								$should_schedule = true;
							}

							if ( $should_schedule ) {
								$sender->schedule_expiry_emails_for_existing_users( $email_id, $email );
							}
						}
					}
				}
			}
		}

		/**
		 * Save email statuses from the list page.
		 */
		private function save_email_statuses() {
			if ( ! isset( $_POST['email_status'] ) || ! is_array( $_POST['email_status'] ) ) {
				return;
			}

			$emails   = $this->get_custom_emails();
			$statuses = array_map( 'sanitize_text_field', wp_unslash( $_POST['email_status'] ) );

			if ( class_exists( 'Custom_Email_Sender' ) ) {
				$sender = new Custom_Email_Sender( $this );
			}

			foreach ( $emails as $email_id => $email ) {
				$is_enabled = false;
				if ( isset( $statuses[ $email_id ] ) ) {
					$status_value = $statuses[ $email_id ];
					$is_enabled = ( '1' === $status_value || 1 === (int) $status_value );
				}

				$current_status = isset( $email['enabled'] ) && $email['enabled'] ? true : false;
				if ( function_exists( 'ur_string_to_bool' ) ) {
					$current_status = ur_string_to_bool( $current_status );
				} elseif ( is_string( $current_status ) ) {
					$current_status = ( 'yes' === $current_status || '1' === $current_status || 'true' === $current_status || true === $current_status );
				}

				if ( $current_status !== $is_enabled ) {
					$created_at = isset( $emails[ $email_id ]['created_at'] ) && ! empty( $emails[ $email_id ]['created_at'] ) ? $emails[ $email_id ]['created_at'] : current_time( 'mysql' );

					$emails[ $email_id ]['enabled'] = $is_enabled;

					unset( $emails[ $email_id ]['created_at'] );
					unset( $emails[ $email_id ]['updated_at'] );

					$emails[ $email_id ]['created_at'] = $created_at;
					$emails[ $email_id ]['updated_at'] = current_time( 'mysql' );

					// Sync with ur_custom_email_enabled_ option
					$option_key = 'ur_custom_email_enabled_' . $email_id;
					$option_value = $is_enabled ? 'yes' : 'no';
					update_option( $option_key, $option_value );

					if ( ! $is_enabled && isset( $sender ) ) {
						$sender->unschedule_all_crons_for_email( $email_id );
					} elseif ( $is_enabled && isset( $sender ) ) {
						if ( isset( $email['trigger_event'] ) &&
							'membership_expired' === $email['trigger_event'] &&
							isset( $email['delivery_timing'] ) &&
							'scheduled' === $email['delivery_timing'] ) {
							$email_before_after = isset( $email['before_after'] ) ? $email['before_after'] : 'after';
							if ( 'before' === $email_before_after && ! $current_status ) {
								$sender->schedule_expiry_emails_for_existing_users( $email_id, $email );
							}
						}
					}
				}
			}

			update_option( 'user_registration_custom_emails', $emails );
		}
	}

endif;
