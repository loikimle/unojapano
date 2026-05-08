<?php
/**
 * Custom Email Save Handler
 *
 * @class    Custom_Email_Save
 * @version
 * @package  UserRegistration/Modules/CustomEmail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Custom_Email_Save' ) ) :

	class Custom_Email_Save {

		/**
		 * Get basic email data from form.
		 *
		 * @param string $email_id Email ID.
		 * @return array Basic email data.
		 */
		public function get_basic_email_data( $email_id ) {
			$enabled = 'no';
			if ( isset( $_POST[ 'ur_custom_email_enabled_' . $email_id ] ) ) {
				$enabled_value = sanitize_text_field( wp_unslash( $_POST[ 'ur_custom_email_enabled_' . $email_id ] ) );
				$enabled = ( '1' === $enabled_value || 1 === (int) $enabled_value ) ? 'yes' : 'no';
			}

			return array(
				'name'          => isset( $_POST[ 'ur_custom_email_name_' . $email_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ur_custom_email_name_' . $email_id ] ) ) : '',
				'description'   => isset( $_POST[ 'ur_custom_email_description_' . $email_id ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ 'ur_custom_email_description_' . $email_id ] ) ) : '',
				'enabled'       => $enabled,
				'send_to_admin' => isset( $_POST[ 'ur_custom_email_send_to_admin_' . $email_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ur_custom_email_send_to_admin_' . $email_id ] ) ) : 'no',
			);
		}

		/**
		 * Get timing/duration data from form.
		 *
		 * @param string $email_id Email ID.
		 * @return array Timing and duration data.
		 */
		public function get_timing_data( $email_id ) {
			$trigger_event   = isset( $_POST[ 'ur_custom_email_trigger_event_' . $email_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ur_custom_email_trigger_event_' . $email_id ] ) ) : '';
			$before_after    = isset( $_POST[ 'ur_custom_email_duration_before_after_' . $email_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ur_custom_email_duration_before_after_' . $email_id ] ) ) : 'after';

			$after_only_triggers = array(
				'member_signs_up',
				'membership_cancellation',
				'membership_upgrade',
				'membership_downgrade',
				'membership_renewal_success',
				'membership_renewal_failed',
			);

			if ( in_array( $trigger_event, $after_only_triggers, true ) && 'before' === $before_after ) {
				$before_after = 'after';
			}

			return array(
				'trigger_event'   => $trigger_event,
				'delivery_timing' => isset( $_POST[ 'ur_custom_email_delivery_timing_' . $email_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ur_custom_email_delivery_timing_' . $email_id ] ) ) : 'instant',
				'duration_unit'   => isset( $_POST[ 'ur_custom_email_duration_unit_' . $email_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ur_custom_email_duration_unit_' . $email_id ] ) ) : 'days',
				'duration_value'  => isset( $_POST[ 'ur_custom_email_duration_value_' . $email_id ] ) ? absint( $_POST[ 'ur_custom_email_duration_value_' . $email_id ] ) : 1,
				'before_after'    => $before_after,
			);
		}

		/**
		 * Get subscription data from form.
		 *
		 * @param string $email_id Email ID.
		 * @return array Subscription data.
		 */
		public function get_subscription_data( $email_id ) {
			$membership = array();
			if ( isset( $_POST[ 'ur_custom_email_membership_' . $email_id ] ) && is_array( $_POST[ 'ur_custom_email_membership_' . $email_id ] ) ) {
				$membership = array_map( 'sanitize_text_field', wp_unslash( $_POST[ 'ur_custom_email_membership_' . $email_id ] ) );
			}
			return array(
				'send_to'          => isset( $_POST[ 'ur_custom_email_send_to_' . $email_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ur_custom_email_send_to_' . $email_id ] ) ) : '',
				'membership'       => $membership,
				'override_default' => isset( $_POST[ 'ur_custom_email_override_default_' . $email_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ur_custom_email_override_default_' . $email_id ] ) ) : 'no',
			);
		}

		/**
		 * Get email data from form.
		 *
		 * @param string $email_id Email ID.
		 * @return array Email data.
		 */
		public function get_email_data( $email_id ) {
			return array(
				'email_subject' => isset( $_POST[ 'ur_custom_email_subject_' . $email_id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ur_custom_email_subject_' . $email_id ] ) ) : '',
				'email_content' => isset( $_POST[ 'ur_custom_email_content_' . $email_id ] ) ? wp_kses_post( wp_unslash( $_POST[ 'ur_custom_email_content_' . $email_id ] ) ) : '',
			);
		}

		/**
		 * Process and save email data.
		 *
		 * @param string $email_id Email ID.
		 * @param array  $emails   Existing emails array.
		 * @return array|false Processed email data or false on validation failure.
		 */
		public function process_email_data( $email_id, $emails ) {
			$basic_data        = $this->get_basic_email_data( $email_id );
			$timing_data       = $this->get_timing_data( $email_id );
			$subscription_data = $this->get_subscription_data( $email_id );
			$email_data        = $this->get_email_data( $email_id );

			if ( empty( $basic_data['name'] ) ) {
				return false;
			}

			$email_data_array = array(
				'name'             => $basic_data['name'],
				'description'      => $basic_data['description'],
				'enabled'          => ur_string_to_bool( $basic_data['enabled'] ),
				'send_to_admin'    => ur_string_to_bool( $basic_data['send_to_admin'] ),
				'trigger_event'    => $timing_data['trigger_event'],
				'delivery_timing'  => $timing_data['delivery_timing'],
				'duration_unit'    => $timing_data['duration_unit'],
				'duration_value'   => $timing_data['duration_value'],
				'before_after'     => $timing_data['before_after'],
				'send_to'          => $subscription_data['send_to'],
				'membership'       => $subscription_data['membership'],
				'override_default' => ur_string_to_bool( $subscription_data['override_default'] ),
				'email_subject'    => $email_data['email_subject'],
				'email_content'    => $email_data['email_content'],
			);

			if ( isset( $emails[ $email_id ] ) ) {
				$existing_created_at = isset( $emails[ $email_id ]['created_at'] ) ? $emails[ $email_id ]['created_at'] : current_time( 'mysql' );

				$email_data_array = array_merge( $emails[ $email_id ], $email_data_array );

				$created_at = $existing_created_at;
				unset( $email_data_array['created_at'] );
				unset( $email_data_array['updated_at'] );

				$email_data_array['created_at'] = $created_at;
			} else {
				$email_data_array['created_at'] = current_time( 'mysql' );
			}

			$email_data_array['updated_at'] = current_time( 'mysql' );

			return $email_data_array;
		}
	}

endif;
