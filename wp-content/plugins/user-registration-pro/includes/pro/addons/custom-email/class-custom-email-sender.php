<?php
/**
 * Custom Email Sender
 *
 * @class    Custom_Email_Sender
 * @version
 * @package  UserRegistration/Modules/CustomEmail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Custom_Email_Sender' ) ) :

	class Custom_Email_Sender {

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
			$this->init_hooks();
		}

		/**
		 * Initialize hooks for email triggers.
		 */
		private function init_hooks() {
			add_action( 'user_registration_after_register_user_action', array( $this, 'trigger_after_member_signs_up' ), 20, 3 );
			add_action( 'user_registration_membership_renewed', array( $this, 'trigger_membership_renewal' ), 20, 2 );
			add_action( 'user_registration_membership_renewal_failed', array( $this, 'trigger_membership_renewal_failed' ), 20, 2 );
			add_action( 'ur_membership_subscription_event_triggered', array( $this, 'handle_subscription_event' ), 10, 1 );
			add_action( 'ur_membership_order_status_failed', array( $this, 'handle_order_status_failed' ), 10, 3 );
			add_action( 'ur_send_custom_email', array( $this, 'send_scheduled_email' ), 10, 3 );
			add_action( 'init', array( $this, 'schedule_daily_check' ) );
			add_action( 'ur_custom_email_daily_check', array( $this, 'process_scheduled_emails' ) );
			add_action( 'ur_custom_email_process_batch', array( $this, 'process_batch_scheduled_emails' ), 10, 2 );
			add_action( 'ur_custom_email_schedule_existing_users', array( $this, 'schedule_expiry_emails_for_existing_users' ), 10, 2 );
			add_action( 'ur_membership_expiry_date_manually_updated', array( $this, 'handle_manual_expiry_date_update' ), 10, 3 );
			add_filter( 'user_registration_should_override_default_email', array( $this, 'filter_should_override_default_email' ), 10, 5 );
		}

		/**
		 * Trigger emails for after member signs up.
		 *
		 * @param array $valid_form_data Form data.
		 * @param int   $form_id        Form ID.
		 * @param int   $user_id        User ID.
		 */
		public function trigger_after_member_signs_up( $valid_form_data, $form_id, $user_id ) {
			$selected_membership_id = 0;

			if ( ! empty( $valid_form_data ) && is_array( $valid_form_data ) ) {
				foreach ( $valid_form_data as $field_name => $field_data ) {
					if ( ! is_object( $field_data ) && ! is_array( $field_data ) ) {
						continue;
					}

					$field_key   = '';
					$field_value = '';

					if ( is_object( $field_data ) ) {
						if ( isset( $field_data->extra_params ) && is_object( $field_data->extra_params ) && isset( $field_data->extra_params->field_key ) ) {
							$field_key = $field_data->extra_params->field_key;
						} elseif ( isset( $field_data->extra_params ) && is_array( $field_data->extra_params ) && isset( $field_data->extra_params['field_key'] ) ) {
							$field_key = $field_data->extra_params['field_key'];
						}

						if ( isset( $field_data->value ) ) {
							$field_value = $field_data->value;
						}

						if ( empty( $field_key ) && isset( $field_data->field_type ) ) {
							$field_type = $field_data->field_type;
							if ( 'membership' === $field_type || 'user_registration_membership' === $field_type ) {
								$field_key = 'membership';
							}
						}
					} elseif ( is_array( $field_data ) ) {
						if ( isset( $field_data['extra_params'] ) ) {
							$extra_params = $field_data['extra_params'];
							if ( is_array( $extra_params ) && isset( $extra_params['field_key'] ) ) {
								$field_key = $extra_params['field_key'];
							} elseif ( is_object( $extra_params ) && isset( $extra_params->field_key ) ) {
								$field_key = $extra_params->field_key;
							}
						}

						if ( isset( $field_data['value'] ) ) {
							$field_value = $field_data['value'];
						}

						if ( empty( $field_key ) && isset( $field_data['field_type'] ) ) {
							$field_type = $field_data['field_type'];
							if ( 'membership' === $field_type || 'user_registration_membership' === $field_type ) {
								$field_key = 'membership';
							}
						}
					}

					if ( 'membership' === $field_key || 'user_registration_membership' === $field_key ) {
						if ( is_numeric( $field_value ) ) {
							$selected_membership_id = absint( $field_value );
						} elseif ( is_string( $field_value ) && ! empty( $field_value ) ) {
							$decoded = json_decode( $field_value, true );
							if ( is_array( $decoded ) && isset( $decoded['value'] ) ) {
								$selected_membership_id = absint( $decoded['value'] );
							} elseif ( preg_match( '/^(\d+):/', $field_value, $matches ) ) {
								$selected_membership_id = absint( $matches[1] );
							} elseif ( is_numeric( trim( $field_value ) ) ) {
								$selected_membership_id = absint( trim( $field_value ) );
							}
						}

						if ( $selected_membership_id > 0 ) {
							break;
						}
					}
				}

				if ( $selected_membership_id <= 0 ) {
					$membership_field_names = array( 'urm_membership', 'membership', 'subscription_plan' );

					foreach ( $membership_field_names as $field_name ) {
						if ( isset( $valid_form_data[ $field_name ] ) ) {
							$field_data = $valid_form_data[ $field_name ];

							if ( is_object( $field_data ) && isset( $field_data->value ) ) {
								$field_value = $field_data->value;
							} elseif ( is_array( $field_data ) && isset( $field_data['value'] ) ) {
								$field_value = $field_data['value'];
							} elseif ( is_string( $field_data ) || is_numeric( $field_data ) ) {
								$field_value = $field_data;
							} else {
								continue;
							}

							if ( is_numeric( $field_value ) ) {
								$selected_membership_id = absint( $field_value );
							} elseif ( is_string( $field_value ) && ! empty( $field_value ) ) {
								$decoded = json_decode( $field_value, true );
								if ( is_array( $decoded ) && isset( $decoded['value'] ) ) {
									$selected_membership_id = absint( $decoded['value'] );
								} elseif ( preg_match( '/^(\d+):/', $field_value, $matches ) ) {
									$selected_membership_id = absint( $matches[1] );
								} elseif ( is_numeric( trim( $field_value ) ) ) {
									$selected_membership_id = absint( trim( $field_value ) );
								}
							}

							if ( $selected_membership_id > 0 ) {
								break;
							}
						}
					}
				}
			}

			if ( $selected_membership_id <= 0 ) {
				$user_membership_plan_id = get_user_meta( $user_id, 'ur_user_membership_plan_id', true );
				if ( ! empty( $user_membership_plan_id ) ) {
					$selected_membership_id = absint( $user_membership_plan_id );
				}
			}

			$extra_data = array();
			if ( $selected_membership_id > 0 ) {
				$extra_data['membership_id'] = $selected_membership_id;
			}

			$this->process_emails( 'member_signs_up', $user_id, $extra_data );

			if ( $selected_membership_id > 0 || ! empty( $extra_data['membership_id'] ) ) {
				$this->process_membership_expiry_before_emails_on_signup( $user_id, $extra_data );
			}
		}

		/**
		 * Trigger emails for after subscription cancellation.
		 *
		 * @param int $user_id User ID.
		 * @param int $membership_id Membership ID.
		 */
		public function trigger_after_subscription_cancellation( $user_id, $membership_id ) {
			$this->process_emails( 'membership_cancellation', $user_id, array( 'membership_id' => $membership_id ) );
		}

		/**
		 * Trigger emails for membership renewal.
		 *
		 * @param int $user_id User ID.
		 * @param int $membership_id Membership ID.
		 */
		public function trigger_membership_renewal( $user_id, $membership_id ) {
			$transient_key = 'ur_renewal_email_sent_' . $user_id . '_' . $membership_id;
			$recent_sent   = get_transient( $transient_key );

			if ( $recent_sent ) {
				return;
			}

			set_transient( $transient_key, true, 60 );

			$this->process_emails( 'membership_renewal_success', $user_id, array( 'membership_id' => $membership_id ) );

			$this->reschedule_membership_expiry_before_emails_on_renewal( $user_id, $membership_id );
		}

		/**
		 * Trigger emails for membership expiry.
		 *
		 * @param int $user_id User ID.
		 * @param int $membership_id Membership ID.
		 */
		public function trigger_membership_expiry( $user_id, $membership_id ) {
			$this->process_emails( 'membership_expired', $user_id, array( 'membership_id' => $membership_id ) );
		}

		/**
		 * Handle subscription events for upgrade/downgrade/cancellation.
		 *
		 * @param array $payload Event payload.
		 */
		public function handle_subscription_event( $payload ) {
			if ( ! isset( $payload['event_type'] ) || ! isset( $payload['member_id'] ) ) {
				return;
			}

			$event_type = $payload['event_type'];
			$user_id    = absint( $payload['member_id'] );
			$meta       = isset( $payload['meta'] ) && is_array( $payload['meta'] ) ? $payload['meta'] : array();

			if ( 'upgraded' === $event_type ) {
				$membership_id     = 0;
				$old_membership_id = 0;
				$subscription_id   = isset( $payload['subscription_id'] ) ? absint( $payload['subscription_id'] ) : 0;

				$user_membership_process = get_user_meta( $user_id, 'urm_membership_process', true );
				if ( ! empty( $user_membership_process ) && is_array( $user_membership_process ) && isset( $user_membership_process['upgrade'] ) ) {
					foreach ( $user_membership_process['upgrade'] as $upgrade_key => $upgrade_data ) {
						if ( isset( $upgrade_data['from'] ) && isset( $upgrade_data['subscription_id'] ) && $upgrade_data['subscription_id'] == $subscription_id ) {
							$old_membership_id = absint( $upgrade_data['from'] );
							break;
						} elseif ( isset( $upgrade_data['from'] ) ) {
							$old_membership_id = absint( $upgrade_data['from'] );
						}
					}
				}

				if ( $old_membership_id <= 0 && isset( $meta['from_membership_id'] ) ) {
					$old_membership_id = absint( $meta['from_membership_id'] );
				}

				if ( $old_membership_id <= 0 && $subscription_id > 0 && class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
					try {
						$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
						$subscription            = $subscription_repository->get_subscription_by_subscription_id( $subscription_id );
						if ( ! empty( $subscription ) && is_array( $subscription ) ) {
							if ( isset( $subscription['previous_item_id'] ) ) {
								$old_membership_id = absint( $subscription['previous_item_id'] );
							}
						}
					} catch ( \Exception $e ) {
					}
				}

				if ( isset( $meta['subscription_id'] ) ) {
					$membership_id = absint( $meta['subscription_id'] );
				} elseif ( isset( $payload['subscription_id'] ) ) {
					$membership_id = absint( $payload['subscription_id'] );
				}

				if ( $membership_id <= 0 && class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
					try {
						$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
						$user_subscription       = $subscription_repository->get_member_subscription( $user_id );
						if ( ! empty( $user_subscription ) && is_array( $user_subscription ) && isset( $user_subscription['item_id'] ) ) {
							$membership_id = absint( $user_subscription['item_id'] );
						}
					} catch ( \Exception $e ) {
					}
				}

				if ( $membership_id <= 0 ) {
					$user_membership_id = get_user_meta( $user_id, 'ur_user_membership_plan_id', true );
					if ( ! empty( $user_membership_id ) ) {
						$membership_id = absint( $user_membership_id );
					}
				}

				$this->process_emails(
					'membership_upgrade',
					$user_id,
					array(
						'meta' => $meta,
					)
				);
			}

			if ( 'downgraded' === $event_type ) {
				$membership_id = 0;

				if ( isset( $meta['subscription_id'] ) ) {
					$membership_id = absint( $meta['subscription_id'] );
				} elseif ( isset( $payload['subscription_id'] ) ) {
					$membership_id = absint( $payload['subscription_id'] );
				}

				if ( $membership_id <= 0 && class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
					try {
						$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
						$user_subscription       = $subscription_repository->get_member_subscription( $user_id );
						if ( ! empty( $user_subscription ) && is_array( $user_subscription ) && isset( $user_subscription['item_id'] ) ) {
							$membership_id = absint( $user_subscription['item_id'] );
						}
					} catch ( \Exception $e ) {
					}
				}

				if ( $membership_id <= 0 ) {
					$user_membership_id = get_user_meta( $user_id, 'ur_user_membership_plan_id', true );
					if ( ! empty( $user_membership_id ) ) {
						$membership_id = absint( $user_membership_id );
					}
				}

				$this->process_emails(
					'membership_downgrade',
					$user_id,
					array(
						'membership_id' => $membership_id,
						'meta'          => $meta,
					)
				);
			}

			if ( 'canceled' === $event_type || 'cancelled' === $event_type ) {
				$membership_id   = 0;
				$subscription_id = isset( $payload['subscription_id'] ) ? absint( $payload['subscription_id'] ) : 0;

				if ( $subscription_id > 0 && class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
					try {
						$subscription_repository   = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
						$current_user_subscription = $subscription_repository->get_membership_by_subscription_id( $subscription_id, false );
						if ( ! empty( $current_user_subscription ) && is_array( $current_user_subscription ) && isset( $current_user_subscription['item_id'] ) ) {
							$membership_id = absint( $current_user_subscription['item_id'] );
						}
					} catch ( \Exception $e ) {
					}
				}

				if ( $membership_id <= 0 && class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
					try {
						$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
						$user_subscription       = $subscription_repository->get_member_subscription( $user_id );
						if ( ! empty( $user_subscription ) && is_array( $user_subscription ) && isset( $user_subscription['item_id'] ) ) {
							$membership_id = absint( $user_subscription['item_id'] );
						}
					} catch ( \Exception $e ) {
					}
				}

				if ( $membership_id <= 0 ) {
					$user_membership_id = get_user_meta( $user_id, 'ur_user_membership_plan_id', true );
					if ( ! empty( $user_membership_id ) ) {
						$membership_id = absint( $user_membership_id );
					}
				}

				$this->process_emails(
					'membership_cancellation',
					$user_id,
					array(
						'membership_id' => $membership_id,
						'meta'          => $meta,
					)
				);
			}

			if ( 'expired' === $event_type ) {
				$membership_id   = 0;
				$subscription_id = isset( $payload['subscription_id'] ) ? absint( $payload['subscription_id'] ) : 0;

				if ( isset( $meta['membership_id'] ) && $meta['membership_id'] > 0 ) {
					$membership_id = absint( $meta['membership_id'] );
				}

				if ( $membership_id <= 0 && $subscription_id > 0 && class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
					try {
						$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
						$subscription            = $subscription_repository->get_subscription_by_subscription_id( $subscription_id );
						if ( ! empty( $subscription ) && is_array( $subscription ) && isset( $subscription['item_id'] ) ) {
							$membership_id = absint( $subscription['item_id'] );
						}
					} catch ( \Exception $e ) {
					}
				}

				if ( $membership_id <= 0 && class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
					try {
						$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
						$user_subscription       = $subscription_repository->get_member_subscription( $user_id );
						if ( ! empty( $user_subscription ) && is_array( $user_subscription ) ) {
							if ( isset( $user_subscription['item_id'] ) ) {
								$membership_id = absint( $user_subscription['item_id'] );
							} elseif ( isset( $user_subscription[0] ) && is_array( $user_subscription[0] ) && isset( $user_subscription[0]['item_id'] ) ) {
								$membership_id = absint( $user_subscription[0]['item_id'] );
							}
						}
					} catch ( \Exception $e ) {
					}
				}

				if ( $membership_id <= 0 ) {
					$user_membership_id = get_user_meta( $user_id, 'ur_user_membership_plan_id', true );
					if ( ! empty( $user_membership_id ) ) {
						$membership_id = absint( $user_membership_id );
					}
				}

				$this->process_emails(
					'membership_expired',
					$user_id,
					array(
						'membership_id'   => $membership_id,
						'subscription_id' => $subscription_id,
						'meta'            => $meta,
					)
				);
			}
		}

		/**
		 * Handle order status failed event.
		 *
		 * @param int   $order_id Order ID.
		 * @param array $order   Order data.
		 * @param string $status  Order status.
		 */
		public function handle_order_status_failed( $order_id, $order, $status ) {
			if ( 'failed' !== $status ) {
				return;
			}

			$user_id         = isset( $order['user_id'] ) ? absint( $order['user_id'] ) : 0;
			$membership_id   = isset( $order['post_id'] ) ? absint( $order['post_id'] ) : 0;
			$subscription_id = isset( $order['subscription_id'] ) ? absint( $order['subscription_id'] ) : 0;

			if ( $user_id <= 0 || $membership_id <= 0 ) {
				return;
			}

			$is_renewal = false;
			if ( function_exists( 'urm_get_membership_process' ) ) {
				$membership_process = urm_get_membership_process( $user_id );
				if ( ! empty( $membership_process['renew'] ) && is_array( $membership_process['renew'] ) && in_array( $membership_id, $membership_process['renew'], true ) ) {
					$is_renewal = true;
				}
			}

			if ( ! $is_renewal && $subscription_id > 0 ) {
				if ( class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
					try {
						$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
						$user_subscription       = $subscription_repository->get_member_subscription( $user_id );
						if ( ! empty( $user_subscription ) ) {
							$is_renewal = true;
						}
					} catch ( \Exception $e ) {
					}
				}
			}

			if ( $is_renewal ) {
				$this->trigger_membership_renewal_failed( $user_id, $membership_id );
			}
		}

		/**
		 * Trigger emails for membership renewal failed.
		 *
		 * @param int $user_id User ID.
		 * @param int $membership_id Membership ID.
		 */
		public function trigger_membership_renewal_failed( $user_id, $membership_id ) {
			$this->process_emails( 'membership_renewal_failed', $user_id, array( 'membership_id' => $membership_id ) );
		}

		/**
		 * Trigger emails for membership upgrade.
		 *
		 * @param int $user_id User ID.
		 * @param int $membership_id Membership ID.
		 */
		public function trigger_membership_upgrade( $user_id, $membership_id ) {
			$this->process_emails( 'membership_upgrade', $user_id, array( 'membership_id' => $membership_id ) );
		}

		/**
		 * Trigger emails for membership downgrade.
		 *
		 * @param int $user_id User ID.
		 * @param int $membership_id Membership ID.
		 */
		public function trigger_membership_downgrade( $user_id, $membership_id ) {
			$this->process_emails( 'membership_downgrade', $user_id, array( 'membership_id' => $membership_id ) );
		}

		/**
		 * Process emails for a specific trigger.
		 *
		 * @param string $trigger_type Trigger type.
		 * @param int    $user_id      User ID.
		 * @param array  $extra_data   Extra data to pass to email.
		 */
		private function process_emails( $trigger_type, $user_id, $extra_data = array() ) {
			$emails = $this->data_handler->get_custom_emails();

			if ( empty( $emails ) ) {
				return;
			}

			$user = get_userdata( $user_id );
			if ( ! $user ) {
				return;
			}

			$user_membership = $this->get_user_membership( $user_id );

			if ( 'member_signs_up' === $trigger_type && ! empty( $extra_data['selected_during_registration'] ) && ! empty( $extra_data['membership_id'] ) && $extra_data['membership_id'] > 0 ) {
				$user_membership = array( (string) $extra_data['membership_id'] );
			} elseif ( empty( $user_membership ) && ! empty( $extra_data['membership_id'] ) && $extra_data['membership_id'] > 0 ) {
				$user_membership[] = (string) $extra_data['membership_id'];
			}

			$old_membership_for_matching = array();
			if ( 'membership_upgrade' === $trigger_type && ! empty( $extra_data['old_membership_id'] ) && $extra_data['old_membership_id'] > 0 ) {
				$old_membership_for_matching[] = (string) $extra_data['old_membership_id'];
			}

			foreach ( $emails as $email_id => $email ) {
				$is_enabled = isset( $email['enabled'] ) ? $email['enabled'] : false;
				if ( function_exists( 'ur_string_to_bool' ) ) {
					$is_enabled = ur_string_to_bool( $is_enabled );
				} elseif ( is_string( $is_enabled ) ) {
					$is_enabled = ( 'yes' === $is_enabled || '1' === $is_enabled || 'true' === $is_enabled );
				}
				if ( ! $is_enabled ) {
					continue;
				}

				$email_trigger = isset( $email['trigger_event'] ) ? $email['trigger_event'] : ( isset( $email['sent_on'] ) ? $email['sent_on'] : '' );
				if ( $email_trigger !== $trigger_type ) {
					continue;
				}

				$sent_to = isset( $email['send_to'] ) ? $email['send_to'] : 'all_members';

				$email_timing = isset( $email['delivery_timing'] ) ? $email['delivery_timing'] : 'instant';

				if ( 'admin' === $sent_to ) {
					if ( 'instant' === $email_timing ) {
						$this->send_email_to_admin( $email, $user, $extra_data );
					} elseif ( 'scheduled' === $email_timing ) {
						$this->schedule_email( $email_id, $email, 0, $trigger_type, $extra_data, true );
					}
				} elseif ( 'all_members' === $sent_to ) {
					if ( ! $this->is_sign_up_member( $user_id ) ) {
						continue;
					}

					if ( 'instant' === $email_timing ) {
						$this->send_email( $email, $user, $extra_data );
					} elseif ( 'scheduled' === $email_timing ) {
						$this->schedule_email( $email_id, $email, $user_id, $trigger_type, $extra_data );
					}
				} elseif ( 'specific_memberships' === $sent_to ) {
					$membership_to_check = $user_membership;

					if ( 'member_signs_up' === $trigger_type && ! empty( $extra_data['selected_during_registration'] ) && ! empty( $extra_data['membership_id'] ) && $extra_data['membership_id'] > 0 ) {
						$membership_to_check = array( (string) $extra_data['membership_id'] );
					} elseif ( 'membership_upgrade' === $trigger_type && ! empty( $old_membership_for_matching ) ) {
						$membership_to_check = $old_membership_for_matching;
					}

					if ( ! $this->matches_membership( $email, $membership_to_check ) ) {
						continue;
					}

					if ( 'instant' === $email_timing ) {
						$this->send_email( $email, $user, $extra_data );
					} elseif ( 'scheduled' === $email_timing ) {
						$this->schedule_email( $email_id, $email, $user_id, $trigger_type, $extra_data );
					}
				}
			}
		}

		/**
		 * Handle manual expiry date update from admin interface.
		 * Reschedules membership expiry "before" emails when expiry date is manually updated.
		 *
		 * @param int    $user_id      User ID.
		 * @param int    $membership_id Membership ID.
		 * @param string $new_expiry_date New expiry date.
		 */
		public function handle_manual_expiry_date_update( $user_id, $membership_id, $new_expiry_date ) {
			$this->reschedule_membership_expiry_before_emails_on_renewal( $user_id, $membership_id );
		}

		/**
		 * Reschedule membership expiry emails with "before" timing when membership is renewed.
		 * This updates existing scheduled emails to use the new expiry date.
		 *
		 * @param int $user_id      User ID.
		 * @param int $membership_id Membership ID.
		 */
		private function reschedule_membership_expiry_before_emails_on_renewal( $user_id, $membership_id ) {
			$emails = $this->data_handler->get_custom_emails();

			if ( empty( $emails ) ) {
				return;
			}

			$user = get_userdata( $user_id );
			if ( ! $user ) {
				return;
			}

			$new_expiry_date = $this->get_membership_expiry_date_for_user( $user_id, $membership_id );

			if ( ! $new_expiry_date ) {
				return;
			}

			$user_membership = $this->get_user_membership( $user_id );
			if ( empty( $user_membership ) && $membership_id > 0 ) {
				$user_membership[] = (string) $membership_id;
			}

			$extra_data = array( 'membership_id' => $membership_id );

			foreach ( $emails as $email_id => $email ) {
				$is_enabled = isset( $email['enabled'] ) ? $email['enabled'] : false;
				if ( function_exists( 'ur_string_to_bool' ) ) {
					$is_enabled = ur_string_to_bool( $is_enabled );
				} elseif ( is_string( $is_enabled ) ) {
					$is_enabled = ( 'yes' === $is_enabled || '1' === $is_enabled || 'true' === $is_enabled );
				}
				if ( ! $is_enabled ) {
					continue;
				}

				$email_trigger = isset( $email['trigger_event'] ) ? $email['trigger_event'] : ( isset( $email['sent_on'] ) ? $email['sent_on'] : '' );
				if ( 'membership_expired' !== $email_trigger ) {
					continue;
				}

				$email_timing = isset( $email['delivery_timing'] ) ? $email['delivery_timing'] : 'instant';
				if ( 'instant' === $email_timing ) {
					continue;
				}

				$before_after = isset( $email['before_after'] ) ? $email['before_after'] : 'after';
				if ( 'before' !== $before_after ) {
					continue;
				}

				$sent_to = isset( $email['send_to'] ) ? $email['send_to'] : 'all_members';

				$should_schedule = false;
				$is_admin_email  = false;

				if ( 'admin' === $sent_to ) {
					$should_schedule = true;
					$is_admin_email  = true;
				} elseif ( 'all_members' === $sent_to ) {
					if ( $this->is_sign_up_member( $user_id ) ) {
						$should_schedule = true;
					}
				} elseif ( 'specific_memberships' === $sent_to ) {
					$membership_to_check = $user_membership;
					if ( empty( $membership_to_check ) && $membership_id > 0 ) {
						$membership_to_check = array( (string) $membership_id );
					}

					if ( $this->matches_membership( $email, $membership_to_check ) ) {
						$should_schedule = true;
					}
				}

				if ( $should_schedule ) {
					$this->unschedule_existing_crons( $email_id, $is_admin_email ? 0 : $user_id, 'membership_expired' );

					if ( $is_admin_email ) {
						$this->schedule_email( $email_id, $email, 0, 'membership_expired', $extra_data, true );
					} else {
						$this->schedule_email( $email_id, $email, $user_id, 'membership_expired', $extra_data );
					}
				}
			}
		}

		/**
		 * Process membership expiry emails with "before" scheduling when user signs up.
		 * This schedules emails to be sent before the membership expires based on the expiry date.
		 *
		 * @param int   $user_id    User ID.
		 * @param array $extra_data Extra data including membership_id.
		 */
		private function process_membership_expiry_before_emails_on_signup( $user_id, $extra_data = array() ) {
			$emails = $this->data_handler->get_custom_emails();

			if ( empty( $emails ) ) {
				return;
			}

			$user = get_userdata( $user_id );
			if ( ! $user ) {
				return;
			}

			$membership_id = isset( $extra_data['membership_id'] ) ? absint( $extra_data['membership_id'] ) : 0;

			$expiry_date = $this->get_membership_expiry_date_for_user( $user_id, $membership_id );

			if ( ! $expiry_date ) {
				return;
			}

			$user_membership = $this->get_user_membership( $user_id );
			if ( empty( $user_membership ) && $membership_id > 0 ) {
				$user_membership[] = (string) $membership_id;
			}

			foreach ( $emails as $email_id => $email ) {
				$is_enabled = isset( $email['enabled'] ) ? $email['enabled'] : false;
				if ( function_exists( 'ur_string_to_bool' ) ) {
					$is_enabled = ur_string_to_bool( $is_enabled );
				} elseif ( is_string( $is_enabled ) ) {
					$is_enabled = ( 'yes' === $is_enabled || '1' === $is_enabled || 'true' === $is_enabled );
				}
				if ( ! $is_enabled ) {
					continue;
				}

				$email_trigger = isset( $email['trigger_event'] ) ? $email['trigger_event'] : ( isset( $email['sent_on'] ) ? $email['sent_on'] : '' );
				if ( 'membership_expired' !== $email_trigger ) {
					continue;
				}

				$email_timing = isset( $email['delivery_timing'] ) ? $email['delivery_timing'] : 'instant';
				if ( 'instant' === $email_timing ) {
					continue;
				}

				$before_after = isset( $email['before_after'] ) ? $email['before_after'] : 'after';
				if ( 'before' !== $before_after ) {
					continue;
				}

				$sent_to = isset( $email['send_to'] ) ? $email['send_to'] : 'all_members';

				if ( 'admin' === $sent_to ) {
					$this->schedule_email( $email_id, $email, 0, 'membership_expired', $extra_data, true );
				} elseif ( 'all_members' === $sent_to ) {
					if ( ! $this->is_sign_up_member( $user_id ) ) {
						continue;
					}
					$this->schedule_email( $email_id, $email, $user_id, 'membership_expired', $extra_data );
				} elseif ( 'specific_memberships' === $sent_to ) {
					$membership_to_check = $user_membership;
					if ( empty( $membership_to_check ) && $membership_id > 0 ) {
						$membership_to_check = array( (string) $membership_id );
					}

					if ( ! $this->matches_membership( $email, $membership_to_check ) ) {
						continue;
					}

					$this->schedule_email( $email_id, $email, $user_id, 'membership_expired', $extra_data );
				}
			}
		}

		/**
		 * Get membership expiry date for a user.
		 *
		 * @param int $user_id      User ID.
		 * @param int $membership_id Membership ID (optional, for newly assigned memberships).
		 * @return string|false Expiry date in Y-m-d format or false.
		 */
		private function get_membership_expiry_date_for_user( $user_id, $membership_id = 0 ) {
			if ( ! ur_check_module_activation( 'membership' ) ) {
				return false;
			}

			$subscription_data = $this->get_user_subscription_data( $user_id );

			if ( ! empty( $subscription_data ) && isset( $subscription_data['expiry_date'] ) && ! empty( $subscription_data['expiry_date'] ) ) {
				return $subscription_data['expiry_date'];
			}

			$expiry_date = get_user_meta( $user_id, 'ur_membership_expiry_date', true );
			if ( $expiry_date ) {
				return $expiry_date;
			}

			if ( $membership_id > 0 ) {
				$expiry_date = $this->calculate_expiry_date_from_membership( $membership_id, $user_id );
				if ( $expiry_date ) {
					return $expiry_date;
				}
			}

			return false;
		}

		/**
		 * Calculate expiry date from membership plan for a new user.
		 *
		 * @param int $membership_id Membership ID.
		 * @param int $user_id       User ID.
		 * @return string|false Expiry date in Y-m-d format or false.
		 */
		private function calculate_expiry_date_from_membership( $membership_id, $user_id ) {
			$membership = get_post( $membership_id );
			if ( ! $membership || 'ur_membership' !== $membership->post_type ) {
				return false;
			}

			$membership_meta = get_post_meta( $membership_id, 'ur_membership', true );
			if ( empty( $membership_meta ) ) {
				return false;
			}

			$membership_meta = json_decode( wp_unslash( $membership_meta ), true );
			if ( ! is_array( $membership_meta ) ) {
				return false;
			}

			$user       = get_userdata( $user_id );
			$start_date = $user && isset( $user->user_registered ) ? $user->user_registered : current_time( 'mysql' );

			if ( isset( $membership_meta['type'] ) && 'subscription' === $membership_meta['type'] ) {
				if ( isset( $membership_meta['subscription']['duration'] ) && isset( $membership_meta['subscription']['value'] ) ) {
					$duration      = absint( $membership_meta['subscription']['value'] );
					$duration_unit = isset( $membership_meta['subscription']['duration'] ) ? $membership_meta['subscription']['duration'] : 'days';

					$expiry_timestamp = strtotime( $start_date . ' +' . $duration . ' ' . $duration_unit );
					if ( $expiry_timestamp ) {
						return date( 'Y-m-d', $expiry_timestamp );
					}
				}
			}

			if ( isset( $membership_meta['type'] ) && 'fixed' === $membership_meta['type'] ) {
				if ( isset( $membership_meta['fixed']['expiry_date'] ) && ! empty( $membership_meta['fixed']['expiry_date'] ) ) {
					return date( 'Y-m-d', strtotime( $membership_meta['fixed']['expiry_date'] ) );
				}
			}

			return false;
		}

		/**
		 * Get user membership.
		 *
		 * @param int $user_id User ID.
		 * @return array User membership.
		 */
		private function get_user_membership( $user_id ) {
			$membership = array();

			if ( ur_check_module_activation( 'membership' ) ) {
				$user_membership_id = get_user_meta( $user_id, 'ur_user_membership_plan_id', true );

				if ( ! empty( $user_membership_id ) ) {
					$user_membership_id = (string) $user_membership_id;

					$membership_plan = get_post( (int) $user_membership_id );
					if ( $membership_plan && 'ur_membership' === $membership_plan->post_type && 'publish' === $membership_plan->post_status ) {
						$membership[] = $user_membership_id;
					}
				}

				if ( class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
					try {
						$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
						$user_subscription       = $subscription_repository->get_member_subscription( $user_id );

						if ( ! empty( $user_subscription ) && is_array( $user_subscription ) ) {
							if ( isset( $user_subscription['item_id'] ) ) {
								$membership[] = (string) $user_subscription['item_id'];
							} elseif ( isset( $user_subscription[0] ) && is_array( $user_subscription[0] ) && isset( $user_subscription[0]['item_id'] ) ) {
								foreach ( $user_subscription as $subscription ) {
									if ( isset( $subscription['item_id'] ) ) {
										$membership[] = (string) $subscription['item_id'];
									}
								}
							}
						}
					} catch ( \Exception $e ) {
					}
				}
			}

			/**
			 * Filter user membership for custom email matching.
			 *
			 * @param array $membership User membership.
			 * @param int   $user_id     User ID.
			 */
			$membership = apply_filters( 'user_registration_custom_email_user_membership', $membership, $user_id );

			$membership = array_map( 'strval', $membership );

			return $membership;
		}

		/**
		 * Check if email matches user membership.
		 *
		 * @param array $email          Email data.
		 * @param array $user_membership User membership.
		 * @return bool True if matches, false otherwise.
		 */
		private function matches_membership( $email, $user_membership ) {
			$email_membership = isset( $email['membership'] ) ? $email['membership'] : array();

			if ( ! is_array( $email_membership ) ) {
				$email_membership = array();
			}

			$email_membership = array_map( 'strval', $email_membership );

			if ( empty( $email_membership ) ) {
				return true;
			}

			if ( in_array( 'all', $email_membership, true ) ) {
				return true;
			}

			if ( empty( $user_membership ) ) {
				return false;
			}

			$user_membership = array_map( 'strval', $user_membership );

			foreach ( $user_membership as $user_subscription ) {
				if ( in_array( $user_subscription, $email_membership, true ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check if a user is a sign-up member.
		 *
		 * @param int $user_id User ID.
		 * @return bool True if user is a sign-up member, false otherwise.
		 */
		private function is_sign_up_member( $user_id ) {
			$form_id = get_user_meta( $user_id, 'ur_form_id', true );

			if ( empty( $form_id ) ) {
				return false;
			}

			$user = get_userdata( $user_id );
			if ( ! $user ) {
				return false;
			}

			if ( in_array( 'administrator', (array) $user->roles, true ) ) {
				return false;
			}

			if ( empty( $user->user_email ) || ! is_email( $user->user_email ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Schedule expiry emails for existing users when membership_expired email with "before" scheduling is enabled.
		 *
		 * @param string $email_id Email ID.
		 * @param array  $email    Email data.
		 */
		public function schedule_expiry_emails_for_existing_users( $email_id, $email ) {
			if ( ! ur_check_module_activation( 'membership' ) ) {
				return;
			}

			$before_after = isset( $email['before_after'] ) ? $email['before_after'] : 'after';
			if ( 'before' !== $before_after ) {
				return;
			}

			$trigger_event = isset( $email['trigger_event'] ) ? $email['trigger_event'] : '';
			if ( 'membership_expired' !== $trigger_event ) {
				return;
			}

			$delivery_timing = isset( $email['delivery_timing'] ) ? $email['delivery_timing'] : 'instant';
			if ( 'scheduled' !== $delivery_timing ) {
				return;
			}

			$users_with_memberships = $this->get_users_with_membership();

			if ( empty( $users_with_memberships ) ) {
				return;
			}

			$scheduled_count = 0;
			$batch_size      = apply_filters( 'ur_custom_email_existing_users_batch_size', 20 );

			$user_batches = array_chunk( $users_with_memberships, $batch_size );

			foreach ( $user_batches as $batch_index => $user_batch ) {
				foreach ( $user_batch as $user_id ) {
					$user = get_userdata( $user_id );
					if ( ! $user ) {
						continue;
					}

					if ( $this->has_scheduled_email_for_user( $email_id, $user_id, 'membership_expired', 'before' ) ) {
						continue;
					}

					$user_membership = $this->get_user_membership( $user_id );
					if ( empty( $user_membership ) ) {
						continue;
					}

					$expiry_date = $this->get_membership_expiry_date_for_user( $user_id, ! empty( $user_membership ) ? $user_membership[0] : 0 );
					if ( ! $expiry_date ) {
						continue;
					}

					$sent_to = isset( $email['send_to'] ) ? $email['send_to'] : 'all_members';

					if ( 'admin' === $sent_to ) {
						$extra_data = array( 'membership_id' => ! empty( $user_membership ) ? $user_membership[0] : 0 );
						$this->schedule_email( $email_id, $email, 0, 'membership_expired', $extra_data, true );
						++$scheduled_count;
					} elseif ( 'all_members' === $sent_to ) {
						if ( ! $this->is_sign_up_member( $user_id ) ) {
							continue;
						}
						$extra_data = array( 'membership_id' => ! empty( $user_membership ) ? $user_membership[0] : 0 );
						$this->schedule_email( $email_id, $email, $user_id, 'membership_expired', $extra_data );
						++$scheduled_count;
					} elseif ( 'specific_memberships' === $sent_to ) {
						if ( ! $this->matches_membership( $email, $user_membership ) ) {
							continue;
						}
						$extra_data = array( 'membership_id' => ! empty( $user_membership ) ? $user_membership[0] : 0 );
						$this->schedule_email( $email_id, $email, $user_id, 'membership_expired', $extra_data );
						++$scheduled_count;
					}
				}

				if ( $batch_index < count( $user_batches ) - 1 ) {
					usleep( 100000 );
				}
			}
		}

		/**
		 * Check if an email is already scheduled for a user.
		 *
		 * @param string $email_id     Email ID.
		 * @param int    $user_id      User ID.
		 * @param string $trigger_type Trigger type.
		 * @param string $before_after Optional. 'before' or 'after' to check specific timing. Default empty to check any.
		 * @return bool True if scheduled, false otherwise.
		 */
		private function has_scheduled_email_for_user( $email_id, $user_id, $trigger_type, $before_after = '' ) {
			if ( $this->is_action_scheduler_available() && function_exists( 'as_get_scheduled_actions' ) && class_exists( 'ActionScheduler_Store' ) ) {
				try {
					$status_pending = ActionScheduler_Store::STATUS_PENDING;
					$args           = array(
						'hook'   => 'ur_send_custom_email',
						'status' => $status_pending,
						'group'  => 'ur_custom_email',
					);

					$scheduled_actions = as_get_scheduled_actions( $args, OBJECT );

					foreach ( $scheduled_actions as $action ) {
						if ( ! is_object( $action ) || ! method_exists( $action, 'get_args' ) ) {
							continue;
						}

						$action_args = $action->get_args();
						if ( empty( $action_args ) || count( $action_args ) < 2 ) {
							continue;
						}

						$cron_email_id     = isset( $action_args[0] ) ? $action_args[0] : '';
						$cron_user_id      = isset( $action_args[1] ) ? absint( $action_args[1] ) : 0;
						$cron_extra        = isset( $action_args[2] ) && is_array( $action_args[2] ) ? $action_args[2] : array();
						$cron_trigger      = isset( $cron_extra['cron_trigger_type'] ) ? $cron_extra['cron_trigger_type'] : '';
						$cron_before_after = isset( $cron_extra['cron_before_after'] ) ? $cron_extra['cron_before_after'] : '';

						if ( $cron_email_id === $email_id && $cron_user_id === $user_id && $cron_trigger === $trigger_type ) {
							if ( empty( $before_after ) || $cron_before_after === $before_after ) {
								return true;
							}
						}
					}
				} catch ( \Exception $e ) {
				}
			}

			$crons = _get_cron_array();
			if ( empty( $crons ) ) {
				return false;
			}

			foreach ( $crons as $timestamp => $cron ) {
				if ( ! isset( $cron['ur_send_custom_email'] ) ) {
					continue;
				}

				foreach ( $cron['ur_send_custom_email'] as $hook_key => $hook_data ) {
					$args = isset( $hook_data['args'] ) ? $hook_data['args'] : array();

					if ( empty( $args ) || count( $args ) < 2 ) {
						continue;
					}

					$cron_email_id     = isset( $args[0] ) ? $args[0] : '';
					$cron_user_id      = isset( $args[1] ) ? absint( $args[1] ) : 0;
					$cron_extra        = isset( $args[2] ) && is_array( $args[2] ) ? $args[2] : array();
					$cron_trigger      = isset( $cron_extra['cron_trigger_type'] ) ? $cron_extra['cron_trigger_type'] : '';
					$cron_before_after = isset( $cron_extra['cron_before_after'] ) ? $cron_extra['cron_before_after'] : '';

					if ( $cron_email_id === $email_id && $cron_user_id === $user_id && $cron_trigger === $trigger_type ) {
						if ( empty( $before_after ) || $cron_before_after === $before_after ) {
							return true;
						}
					}
				}
			}

			return false;
		}

		/**
		 * Schedule email.
		 *
		 * @param string $email_id Email ID.
		 * @param array  $email    Email data.
		 * @param int    $user_id     User ID.
		 * @param string $trigger_type Trigger type.
		 * @param array  $extra_data  Extra data.
		 * @param bool   $is_admin   Whether this is an admin-only email.
		 */
		private function schedule_email( $email_id, $email, $user_id, $trigger_type, $extra_data = array(), $is_admin = false ) {
			$duration_unit  = isset( $email['duration_unit'] ) ? $email['duration_unit'] : 'days';
			$duration_value = isset( $email['duration_value'] ) ? absint( $email['duration_value'] ) : 1;
			$before_after   = isset( $email['before_after'] ) ? $email['before_after'] : 'after';

			$after_only_triggers = array(
				'member_signs_up',
				'membership_cancellation',
				'membership_upgrade',
				'membership_downgrade',
				'membership_renewal_success',
				'membership_renewal_failed',
			);

			if ( in_array( $trigger_type, $after_only_triggers, true ) && 'before' === $before_after ) {
				$before_after = 'after';
			}

			$delay_seconds = $this->calculate_delay_seconds( $duration_value, $duration_unit );

			$timestamp = time();

			if ( 'before' === $before_after ) {
				$event_date = $this->get_event_date( $trigger_type, $user_id, $extra_data );

				if ( $event_date ) {
					$timestamp = strtotime( $event_date ) - $delay_seconds;

					if ( $timestamp <= time() ) {
						if ( $is_admin ) {
							$this->send_email_to_admin( $email, null, $extra_data );
						} else {
							$user = get_userdata( $user_id );
							if ( $user ) {
								$this->send_email( $email, $user, $extra_data );
							}
						}
						return;
					}
				} else {
					$timestamp = time() + $delay_seconds;
				}
			} else {
				$event_date = $this->get_event_date( $trigger_type, $user_id, $extra_data );

				if ( $event_date ) {
					$event_timestamp = strtotime( $event_date );

					$time_threshold = 5 * MINUTE_IN_SECONDS;
					if ( 'membership_cancellation' === $trigger_type && $event_timestamp > ( time() - $time_threshold ) ) {
						$timestamp = time() + $delay_seconds;
					} else {
						$timestamp = $event_timestamp + $delay_seconds;
					}

					if ( $timestamp <= time() ) {
						if ( $is_admin ) {
							$this->send_email_to_admin( $email, null, $extra_data );
						} else {
							$user = get_userdata( $user_id );
							if ( $user ) {
								$this->send_email( $email, $user, $extra_data );
							}
						}
						return;
					}
				} else {
					$timestamp = time() + $delay_seconds;
				}
			}

			$this->unschedule_existing_crons( $email_id, $user_id, $trigger_type );

			$cron_identifier = $this->generate_cron_identifier( $email_id, $user_id, $trigger_type, $timestamp, $before_after, $duration_value, $duration_unit );

			$extra_data['id'] = $email_id;
			// $extra_data['email']               = $email;
			if ( $user_id > 0 ) {
				$user = get_userdata( $user_id );
				if ( $user ) {
					$extra_data['user_id']    = $user_id;
					$extra_data['user_name']  = $user->user_login;
					$extra_data['user_email'] = $user->user_email;
				}
			}
			$extra_data['cron_identifier']     = $cron_identifier;
			$extra_data['cron_scheduled_time'] = $timestamp;
			$extra_data['cron_scheduled_date'] = date( 'Y-m-d H:i:s', $timestamp );
			$extra_data['cron_trigger_type']   = $trigger_type;
			$extra_data['cron_before_after']   = $before_after;
			$extra_data['cron_duration']       = $duration_value . ' ' . $duration_unit;

			$this->store_cron_metadata(
				$cron_identifier,
				array(
					'email_id'       => $email_id,
					'user_id'        => $user_id,
					'trigger_type'   => $trigger_type,
					'scheduled_time' => $timestamp,
					'scheduled_date' => date( 'Y-m-d H:i:s', $timestamp ),
					'before_after'   => $before_after,
					'duration'       => $duration_value . ' ' . $duration_unit,
					'is_admin'       => $is_admin,
				)
			);

			if ( $this->is_action_scheduler_available() ) {
				as_schedule_single_action(
					$timestamp,
					'ur_send_custom_email',
					array(
						$email_id,
						$user_id,
						$extra_data,
					),
					'ur_custom_email',
					false
				);
			} else {
				wp_schedule_single_event(
					$timestamp,
					'ur_send_custom_email',
					array(
						$email_id,
						$user_id,
						$extra_data,
					)
				);
			}
		}

		/**
		 * Unschedule existing crons for the same email, user, and trigger combination.
		 * This ensures that when schedule changes, old cron is removed and new one is created.
		 *
		 * @param string $email_id Email ID.
		 * @param int    $user_id User ID.
		 * @param string $trigger_type Trigger type.
		 */
		private function unschedule_existing_crons( $email_id, $user_id, $trigger_type ) {
			if ( $this->is_action_scheduler_available() && function_exists( 'as_get_scheduled_actions' ) && class_exists( 'ActionScheduler_Store' ) ) {
				try {
					$status_pending = ActionScheduler_Store::STATUS_PENDING;
					$args           = array(
						'hook'   => 'ur_send_custom_email',
						'status' => $status_pending,
						'group'  => 'ur_custom_email',
					);

					$scheduled_actions = as_get_scheduled_actions( $args, OBJECT );

					foreach ( $scheduled_actions as $action ) {
						if ( ! is_object( $action ) || ! method_exists( $action, 'get_args' ) ) {
							continue;
						}

						$action_args = $action->get_args();
						if ( empty( $action_args ) || count( $action_args ) < 2 ) {
							continue;
						}

						$cron_email_id = isset( $action_args[0] ) ? $action_args[0] : '';
						$cron_user_id  = isset( $action_args[1] ) ? absint( $action_args[1] ) : 0;
						$cron_extra    = isset( $action_args[2] ) && is_array( $action_args[2] ) ? $action_args[2] : array();
						$cron_trigger  = isset( $cron_extra['cron_trigger_type'] ) ? $cron_extra['cron_trigger_type'] : '';

						if ( $cron_email_id === $email_id && $cron_user_id === $user_id && $cron_trigger === $trigger_type ) {
							as_unschedule_action( 'ur_send_custom_email', $action_args, 'ur_custom_email' );

							if ( isset( $cron_extra['cron_identifier'] ) ) {
								$this->remove_cron_metadata( $cron_extra['cron_identifier'] );
							}
						}
					}
				} catch ( \Exception $e ) {
				}
			}

			if ( ! $this->is_action_scheduler_available() ) {
				$crons = _get_cron_array();

				if ( empty( $crons ) ) {
					return;
				}

				foreach ( $crons as $timestamp => $cron ) {
					if ( ! isset( $cron['ur_send_custom_email'] ) ) {
						continue;
					}

					foreach ( $cron['ur_send_custom_email'] as $hook_key => $hook_data ) {
						$args = isset( $hook_data['args'] ) ? $hook_data['args'] : array();

						if ( empty( $args ) || count( $args ) < 2 ) {
							continue;
						}

						$cron_email_id = isset( $args[0] ) ? $args[0] : '';
						$cron_user_id  = isset( $args[1] ) ? absint( $args[1] ) : 0;
						$cron_extra    = isset( $args[2] ) && is_array( $args[2] ) ? $args[2] : array();
						$cron_trigger  = isset( $cron_extra['cron_trigger_type'] ) ? $cron_extra['cron_trigger_type'] : '';

						if ( $cron_email_id === $email_id && $cron_user_id === $user_id && $cron_trigger === $trigger_type ) {
							wp_unschedule_event( $timestamp, 'ur_send_custom_email', $args );

							if ( isset( $cron_extra['cron_identifier'] ) ) {
								$this->remove_cron_metadata( $cron_extra['cron_identifier'] );
							}
						}
					}
				}
			}
		}

		/**
		 * Remove cron metadata by identifier.
		 *
		 * @param string $cron_identifier Cron identifier.
		 */
		private function remove_cron_metadata( $cron_identifier ) {
			$option_key   = 'ur_custom_email_cron_metadata';
			$all_metadata = get_option( $option_key, array() );

			if ( isset( $all_metadata[ $cron_identifier ] ) ) {
				unset( $all_metadata[ $cron_identifier ] );
				update_option( $option_key, $all_metadata, false );
			}
		}

		/**
		 * Unschedule all crons for an email.
		 *
		 * @param string $email_id Email ID.
		 */
		public function unschedule_all_crons_for_email( $email_id ) {
			if ( $this->is_action_scheduler_available() && function_exists( 'as_get_scheduled_actions' ) && class_exists( 'ActionScheduler_Store' ) ) {
				try {
					$status_pending = ActionScheduler_Store::STATUS_PENDING;
					$args           = array(
						'hook'   => 'ur_send_custom_email',
						'status' => $status_pending,
						'group'  => 'ur_custom_email',
					);

					$scheduled_actions = as_get_scheduled_actions( $args, OBJECT );

					foreach ( $scheduled_actions as $action ) {
						if ( ! is_object( $action ) || ! method_exists( $action, 'get_args' ) ) {
							continue;
						}

						$action_args = $action->get_args();
						if ( empty( $action_args ) || count( $action_args ) < 1 ) {
							continue;
						}

						$cron_email_id = isset( $action_args[0] ) ? $action_args[0] : '';
						$cron_extra    = isset( $action_args[2] ) && is_array( $action_args[2] ) ? $action_args[2] : array();

						if ( $cron_email_id === $email_id ) {
							as_unschedule_action( 'ur_send_custom_email', $action_args, 'ur_custom_email' );

							if ( isset( $cron_extra['cron_identifier'] ) ) {
								$this->remove_cron_metadata( $cron_extra['cron_identifier'] );
							}
						}
					}
				} catch ( \Exception $e ) {
				}
			}

			if ( ! $this->is_action_scheduler_available() ) {
				$crons = _get_cron_array();

				if ( empty( $crons ) ) {
					return;
				}

				foreach ( $crons as $timestamp => $cron ) {
					if ( ! isset( $cron['ur_send_custom_email'] ) ) {
						continue;
					}

					foreach ( $cron['ur_send_custom_email'] as $hook_key => $hook_data ) {
						$args = isset( $hook_data['args'] ) ? $hook_data['args'] : array();

						if ( empty( $args ) || count( $args ) < 2 ) {
							continue;
						}

						$cron_email_id = isset( $args[0] ) ? $args[0] : '';
						$cron_extra    = isset( $args[2] ) && is_array( $args[2] ) ? $args[2] : array();

						if ( $cron_email_id === $email_id ) {
							wp_unschedule_event( $timestamp, 'ur_send_custom_email', $args );

							if ( isset( $cron_extra['cron_identifier'] ) ) {
								$this->remove_cron_metadata( $cron_extra['cron_identifier'] );
							}
						}
					}
				}
			}
		}

		/**
		 * Reschedule existing crons for an email when settings change.
		 *
		 * @param string $email_id Email ID.
		 * @param array  $new_email New email settings.
		 */
		public function reschedule_crons_for_email( $email_id, $new_email ) {
			if ( $this->is_action_scheduler_available() && function_exists( 'as_get_scheduled_actions' ) && class_exists( 'ActionScheduler_Store' ) ) {
				try {
					$status_pending = ActionScheduler_Store::STATUS_PENDING;
					$args           = array(
						'hook'   => 'ur_send_custom_email',
						'status' => $status_pending,
						'group'  => 'ur_custom_email',
					);

					$scheduled_actions = as_get_scheduled_actions( $args, OBJECT );

					foreach ( $scheduled_actions as $action ) {
						if ( ! is_object( $action ) || ! method_exists( $action, 'get_args' ) ) {
							continue;
						}

						$action_args = $action->get_args();
						if ( empty( $action_args ) || count( $action_args ) < 2 ) {
							continue;
						}

						$cron_email_id = isset( $action_args[0] ) ? $action_args[0] : '';
						$cron_user_id  = isset( $action_args[1] ) ? absint( $action_args[1] ) : 0;
						$cron_extra    = isset( $action_args[2] ) && is_array( $action_args[2] ) ? $action_args[2] : array();
						$cron_trigger  = isset( $cron_extra['cron_trigger_type'] ) ? $cron_extra['cron_trigger_type'] : '';

						if ( $cron_email_id === $email_id ) {
							as_unschedule_action( 'ur_send_custom_email', $action_args, 'ur_custom_email' );

							if ( isset( $cron_extra['cron_identifier'] ) ) {
								$this->remove_cron_metadata( $cron_extra['cron_identifier'] );
							}

							$user = get_userdata( $cron_user_id );
							if ( $user && isset( $new_email['delivery_timing'] ) && 'scheduled' === $new_email['delivery_timing'] ) {
								$clean_extra_data = $cron_extra;
								unset( $clean_extra_data['cron_identifier'] );
								unset( $clean_extra_data['cron_scheduled_time'] );
								unset( $clean_extra_data['cron_scheduled_date'] );
								unset( $clean_extra_data['cron_before_after'] );
								unset( $clean_extra_data['cron_duration'] );
								unset( $clean_extra_data['email'] );
								unset( $clean_extra_data['id'] );

								$is_admin = ( isset( $new_email['send_to'] ) && 'admin' === $new_email['send_to'] ) || ( isset( $new_email['send_to_admin'] ) && $new_email['send_to_admin'] );

								$this->schedule_email( $email_id, $new_email, $cron_user_id, $cron_trigger, $clean_extra_data, $is_admin );
							}
						}
					}
				} catch ( \Exception $e ) {
				}
			}

			if ( ! $this->is_action_scheduler_available() ) {
				$crons = _get_cron_array();

				if ( empty( $crons ) ) {
					return;
				}

				foreach ( $crons as $timestamp => $cron ) {
					if ( ! isset( $cron['ur_send_custom_email'] ) ) {
						continue;
					}

					foreach ( $cron['ur_send_custom_email'] as $hook_key => $hook_data ) {
						$args = isset( $hook_data['args'] ) ? $hook_data['args'] : array();

						if ( empty( $args ) || count( $args ) < 2 ) {
							continue;
						}

						$cron_email_id = isset( $args[0] ) ? $args[0] : '';
						$cron_user_id  = isset( $args[1] ) ? absint( $args[1] ) : 0;
						$cron_extra    = isset( $args[2] ) && is_array( $args[2] ) ? $args[2] : array();
						$cron_trigger  = isset( $cron_extra['cron_trigger_type'] ) ? $cron_extra['cron_trigger_type'] : '';

						if ( $cron_email_id === $email_id ) {
							wp_unschedule_event( $timestamp, 'ur_send_custom_email', $args );

							if ( isset( $cron_extra['cron_identifier'] ) ) {
								$this->remove_cron_metadata( $cron_extra['cron_identifier'] );
							}

							$user = get_userdata( $cron_user_id );
							if ( $user && isset( $new_email['delivery_timing'] ) && 'scheduled' === $new_email['delivery_timing'] ) {
								$clean_extra_data = $cron_extra;
								unset( $clean_extra_data['cron_identifier'] );
								unset( $clean_extra_data['cron_scheduled_time'] );
								unset( $clean_extra_data['cron_scheduled_date'] );
								unset( $clean_extra_data['cron_before_after'] );
								unset( $clean_extra_data['cron_duration'] );
								unset( $clean_extra_data['email'] );
								unset( $clean_extra_data['id'] );

								$is_admin = ( isset( $new_email['send_to'] ) && 'admin' === $new_email['send_to'] ) || ( isset( $new_email['send_to_admin'] ) && $new_email['send_to_admin'] );

								$this->schedule_email( $email_id, $new_email, $cron_user_id, $cron_trigger, $clean_extra_data, $is_admin );
							}
						}
					}
				}
			}
		}

		/**
		 * Generate unique cron identifier based on trigger and schedule time.
		 *
		 * @param string $email_id Email ID.
		 * @param int    $user_id User ID.
		 * @param string $trigger_type Trigger type.
		 * @param int    $timestamp Scheduled timestamp.
		 * @param string $before_after Before or after.
		 * @param int    $duration_value Duration value.
		 * @param string $duration_unit Duration unit.
		 * @return string Unique cron identifier.
		 */
		private function generate_cron_identifier( $email_id, $user_id, $trigger_type, $timestamp, $before_after, $duration_value, $duration_unit ) {
			$identifier_parts = array(
				'trigger' => $trigger_type,
				'email'   => $email_id,
				'user'    => $user_id,
				'time'    => $timestamp,
				'timing'  => $before_after,
				'delay'   => $duration_value . '_' . $duration_unit,
			);

			$identifier_string = implode( '_', $identifier_parts );
			$hash              = substr( md5( $identifier_string ), 0, 8 );

			$readable_identifier = sprintf(
				'%s_%s_%d_%s_%d%s_%s',
				sanitize_key( $trigger_type ),
				sanitize_key( $email_id ),
				$user_id,
				$before_after,
				$duration_value,
				substr( $duration_unit, 0, 1 ),
				$hash
			);

			return $readable_identifier;
		}

		/**
		 * Store cron metadata for identification.
		 *
		 * @param string $cron_identifier Cron identifier.
		 * @param array  $metadata Cron metadata.
		 */
		private function store_cron_metadata( $cron_identifier, $metadata ) {
			$option_key   = 'ur_custom_email_cron_metadata';
			$all_metadata = get_option( $option_key, array() );

			$all_metadata[ $cron_identifier ] = array_merge(
				$metadata,
				array(
					'created_at' => current_time( 'mysql' ),
				)
			);

			$thirty_days_ago = time() - ( 30 * DAY_IN_SECONDS );
			foreach ( $all_metadata as $key => $meta ) {
				if ( isset( $meta['scheduled_time'] ) && $meta['scheduled_time'] < $thirty_days_ago ) {
					unset( $all_metadata[ $key ] );
				}
			}

			update_option( $option_key, $all_metadata, false );
		}

		/**
		 * Get cron metadata by identifier.
		 *
		 * @param string $cron_identifier Cron identifier.
		 * @return array|false Cron metadata or false if not found.
		 */
		public static function get_cron_metadata( $cron_identifier ) {
			$option_key   = 'ur_custom_email_cron_metadata';
			$all_metadata = get_option( $option_key, array() );

			if ( isset( $all_metadata[ $cron_identifier ] ) ) {
				return $all_metadata[ $cron_identifier ];
			}

			return false;
		}

		/**
		 * Get all cron metadata.
		 *
		 * @return array All cron metadata.
		 */
		public static function get_all_cron_metadata() {
			$option_key = 'ur_custom_email_cron_metadata';
			return get_option( $option_key, array() );
		}

		/**
		 * Get event date for triggers.
		 *
		 * @param string $trigger_type Trigger type.
		 * @param int    $user_id      User ID.
		 * @param array  $extra_data   Extra data.
		 * @return string|false Event date in Y-m-d format or false.
		 */
		private function get_event_date( $trigger_type, $user_id, $extra_data = array() ) {
			if ( 'member_signs_up' === $trigger_type ) {
				$user = get_userdata( $user_id );
				if ( $user && isset( $user->user_registered ) ) {
					return $user->user_registered;
				}
				return false;
			}

			if ( ! ur_check_module_activation( 'membership' ) ) {
				return false;
			}

			$subscription_data = $this->get_user_subscription_data( $user_id );

			if ( 'membership_expired' === $trigger_type || strpos( $trigger_type, 'membership_expired' ) !== false ) {
				if ( ! empty( $subscription_data ) && isset( $subscription_data['expiry_date'] ) && ! empty( $subscription_data['expiry_date'] ) ) {
					return $subscription_data['expiry_date'];
				}
				$expiry_date = get_user_meta( $user_id, 'ur_membership_expiry_date', true );
				if ( $expiry_date ) {
					return $expiry_date;
				}
			} elseif ( 'membership_renewal_success' === $trigger_type || strpos( $trigger_type, 'membership_renewal_success' ) !== false ) {
				if ( ! empty( $subscription_data ) && isset( $subscription_data['next_billing_date'] ) && ! empty( $subscription_data['next_billing_date'] ) ) {
					return $subscription_data['next_billing_date'];
				}
				if ( ! empty( $subscription_data ) && isset( $subscription_data['expiry_date'] ) && ! empty( $subscription_data['expiry_date'] ) ) {
					return $subscription_data['expiry_date'];
				}
			} elseif ( 'membership_renewal_failed' === $trigger_type ) {
				if ( ! empty( $subscription_data ) && isset( $subscription_data['next_billing_date'] ) && ! empty( $subscription_data['next_billing_date'] ) ) {
					return $subscription_data['next_billing_date'];
				}
				if ( ! empty( $subscription_data ) && isset( $subscription_data['expiry_date'] ) && ! empty( $subscription_data['expiry_date'] ) ) {
					return $subscription_data['expiry_date'];
				}
			} elseif ( 'membership_cancellation' === $trigger_type ) {
				$cancellation_date = get_user_meta( $user_id, 'ur_membership_cancellation_date', true );
				if ( $cancellation_date ) {
					$cancellation_timestamp = strtotime( $cancellation_date );
					if ( $cancellation_timestamp !== false ) {
						return $cancellation_date;
					}
				}
				return false;
			} elseif ( 'membership_upgrade' === $trigger_type || 'membership_downgrade' === $trigger_type ) {
				$change_date = get_user_meta( $user_id, 'ur_membership_change_date', true );
				if ( $change_date ) {
					return $change_date;
				}
				if ( ! empty( $subscription_data ) && isset( $subscription_data['expiry_date'] ) && ! empty( $subscription_data['expiry_date'] ) ) {
					return $subscription_data['expiry_date'];
				}
			}

			return false;
		}

		/**
		 * Get user subscription data.
		 *
		 * @param int $user_id User ID.
		 * @return array Subscription data.
		 */
		private function get_user_subscription_data( $user_id ) {
			$subscription_data = array();

			if ( ! ur_check_module_activation( 'membership' ) ) {
				return $subscription_data;
			}

			if ( class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
				try {
					$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
					$subscription            = $subscription_repository->get_member_subscription( $user_id );

					if ( ! empty( $subscription ) && is_array( $subscription ) ) {
						$subscription_item = null;

						if ( isset( $subscription[0] ) ) {
							$subscription_item = $subscription[0];

							if ( is_object( $subscription_item ) ) {
								$subscription_item = (array) $subscription_item;
							}
						} elseif ( isset( $subscription['expiry_date'] ) || isset( $subscription['item_id'] ) || isset( $subscription['user_id'] ) ) {
							$subscription_item = $subscription;

							if ( is_object( $subscription_item ) ) {
								$subscription_item = (array) $subscription_item;
							}
						}

						if ( $subscription_item && ( is_array( $subscription_item ) || is_object( $subscription_item ) ) ) {
							if ( is_object( $subscription_item ) ) {
								$subscription_item = (array) $subscription_item;
							}

							$subscription_data = array(
								'expiry_date'       => isset( $subscription_item['expiry_date'] ) ? $subscription_item['expiry_date'] : '',
								'next_billing_date' => isset( $subscription_item['next_billing_date'] ) ? $subscription_item['next_billing_date'] : '',
								'status'            => isset( $subscription_item['status'] ) ? $subscription_item['status'] : '',
							);
						}
					}
				} catch ( \Exception $e ) {
				}
			}

			if ( empty( $subscription_data ) || ( isset( $subscription_data['expiry_date'] ) && empty( $subscription_data['expiry_date'] ) ) ) {
				$expiry_date = get_user_meta( $user_id, 'ur_membership_expiry_date', true );
				if ( $expiry_date ) {
					if ( empty( $subscription_data ) ) {
						$subscription_data = array();
					}
					$subscription_data['expiry_date'] = $expiry_date;
				}
			}

			return $subscription_data;
		}

		/**
		 * Check if Action Scheduler is available and initialized.
		 *
		 * @return bool True if Action Scheduler is available, false otherwise.
		 */
		private function is_action_scheduler_available() {
			if ( ! function_exists( 'as_schedule_single_action' ) || ! function_exists( 'as_schedule_recurring_action' ) ) {
				return false;
			}

			if ( class_exists( 'ActionScheduler' ) && method_exists( 'ActionScheduler', 'is_initialized' ) ) {
				return ActionScheduler::is_initialized( __FUNCTION__ );
			}

			return true;
		}

		/**
		 * Schedule daily check for subscription-based emails.
		 */
		public function schedule_daily_check() {
			if ( ! ur_check_module_activation( 'membership' ) ) {
				return;
			}

			if ( $this->is_action_scheduler_available() ) {
				if ( false === as_next_scheduled_action( 'ur_custom_email_daily_check', null, 'ur_custom_email' ) ) {
					as_schedule_recurring_action(
						time(),
						DAY_IN_SECONDS,
						'ur_custom_email_daily_check',
						array(),
						'ur_custom_email',
						true
					);
				}
			} elseif ( ! wp_next_scheduled( 'ur_custom_email_daily_check' ) ) {
					wp_schedule_event( time(), 'daily', 'ur_custom_email_daily_check' );
			}
		}

		/**
		 * Process scheduled emails for before/after subscription events.
		 * This method now schedules batch processing actions instead of processing all at once.
		 */
		public function process_scheduled_emails() {
			$emails = $this->data_handler->get_custom_emails();

			if ( empty( $emails ) ) {
				return;
			}

			$users_to_check = array();

			if ( ur_check_module_activation( 'membership' ) ) {
				$users_to_check = $this->get_users_with_membership();
			}

			$all_users = get_users( array( 'fields' => 'ID' ) );
			foreach ( $all_users as $user_id ) {
				if ( ! in_array( $user_id, $users_to_check, true ) ) {
					$users_to_check[] = $user_id;
				}
			}

			$batch_size   = apply_filters( 'ur_custom_email_batch_size', 50 );
			$user_batches = array_chunk( $users_to_check, $batch_size );

			foreach ( $user_batches as $batch_index => $user_batch ) {
				if ( $this->is_action_scheduler_available() ) {
					as_schedule_single_action(
						time() + ( $batch_index * 30 ), // Stagger batches by 30 seconds.
						'ur_custom_email_process_batch',
						array(
							$user_batch,
							$emails,
						),
						'ur_custom_email',
						false
					);
				} else {
					$this->process_batch_scheduled_emails( $user_batch, $emails );
				}
			}
		}

		/**
		 * Process a batch of scheduled emails for before/after subscription events.
		 *
		 * @param array $user_batch Array of user IDs to process.
		 * @param array $emails     Array of email configurations.
		 */
		public function process_batch_scheduled_emails( $user_batch, $emails = null ) {
			if ( null === $emails ) {
				$emails = $this->data_handler->get_custom_emails();
			}

			if ( empty( $emails ) || empty( $user_batch ) ) {
				return;
			}

			foreach ( $user_batch as $user_id ) {
				$user = get_userdata( $user_id );
				if ( ! $user ) {
					continue;
				}

				$user_membership   = $this->get_user_membership( $user_id );
				$subscription_data = $this->get_user_subscription_data( $user_id );

				foreach ( $emails as $email_id => $email ) {
					if ( ! isset( $email['enabled'] ) || ! $email['enabled'] ) {
						continue;
					}

					$email_timing = isset( $email['delivery_timing'] ) ? $email['delivery_timing'] : 'instant';
					if ( 'instant' === $email_timing ) {
						continue;
					}

					$sent_to = isset( $email['send_to'] ) ? $email['send_to'] : ( isset( $email['sent_to'] ) ? $email['sent_to'] : 'all_members' );

					if ( 'all_members' === $sent_to ) {
						if ( ! $this->is_sign_up_member( $user_id ) ) {
							continue;
						}
					} elseif ( 'specific_memberships' === $sent_to ) {
						if ( ! $this->matches_membership( $email, $user_membership ) ) {
							continue;
						}
					}

					$trigger_type = isset( $email['trigger_event'] ) ? $email['trigger_event'] : ( isset( $email['sent_on'] ) ? $email['sent_on'] : '' );
					$before_after = isset( $email['before_after'] ) ? $email['before_after'] : 'after';

					$after_only_triggers = array(
						'member_signs_up',
						'membership_cancellation',
						'membership_upgrade',
						'membership_downgrade',
						'membership_renewal_success',
						'membership_renewal_failed',
					);

					if ( in_array( $trigger_type, $after_only_triggers, true ) && 'before' === $before_after ) {
						$before_after = 'after';
					}

					if ( 'membership_expired' === $trigger_type && 'before' === $before_after ) {
						$this->check_before_trigger( $email_id, $email, $user, $trigger_type, $subscription_data );
					}

					if ( 'after' === $before_after ) {
						$this->check_after_trigger( $email_id, $email, $user, $trigger_type, $subscription_data );
					}
				}
			}
		}

		/**
		 * Check and process "before" triggers.
		 *
		 * @param string $email_id Email ID.
		 * @param array  $email Email data.
		 * @param WP_User $user User object.
		 * @param string $trigger_type Trigger type.
		 * @param array  $subscription_data Subscription data.
		 */
		private function check_before_trigger( $email_id, $email, $user, $trigger_type, $subscription_data ) {
			if ( ! isset( $email['enabled'] ) || ! $email['enabled'] ) {
				return;
			}

			if ( 'membership_expired' !== $trigger_type ) {
				return;
			}

			$before_after = isset( $email['before_after'] ) ? $email['before_after'] : 'after';
			if ( 'before' !== $before_after ) {
				return;
			}

			$event_date = $this->get_event_date( $trigger_type, $user->ID, array() );

			if ( ! $event_date ) {
				return;
			}

			$duration_unit  = isset( $email['duration_unit'] ) ? $email['duration_unit'] : 'days';
			$duration_value = isset( $email['duration_value'] ) ? absint( $email['duration_value'] ) : 1;
			$delay_seconds  = $this->calculate_delay_seconds( $duration_value, $duration_unit );

			$event_timestamp = strtotime( $event_date );
			$target_date     = $event_timestamp - $delay_seconds;
			$today_end       = strtotime( 'tomorrow' ) - 1;

			$target_date_passed = ( $target_date <= $today_end );

			if ( $target_date_passed ) {
				$sent_key     = 'ur_custom_email_sent_' . $email_id . '_' . $trigger_type . '_' . $event_date;
				$already_sent = get_user_meta( $user->ID, $sent_key, true );

				if ( ! $already_sent ) {
					$sent_to = isset( $email['send_to'] ) ? $email['send_to'] : ( isset( $email['sent_to'] ) ? $email['sent_to'] : 'all_members' );

					if ( 'admin' === $sent_to ) {
						$this->send_email_to_admin( $email, $user, array() );
					} else {
						$this->send_email( $email, $user, array() );
					}

					update_user_meta( $user->ID, $sent_key, time() );
				}
			}
		}

		/**
		 * Check and process "after" triggers.
		 *
		 * @param string $email_id Email ID.
		 * @param array  $email Email data.
		 * @param WP_User $user User object.
		 * @param string $trigger_type Trigger type.
		 * @param array  $subscription_data Subscription data.
		 */
		private function check_after_trigger( $email_id, $email, $user, $trigger_type, $subscription_data ) {
			if ( ! isset( $email['enabled'] ) || ! $email['enabled'] ) {
				return;
			}

			$before_after = isset( $email['before_after'] ) ? $email['before_after'] : 'after';
			if ( 'after' !== $before_after ) {
				return;
			}

			$event_date = $this->get_event_date( $trigger_type, $user->ID, array() );

			if ( empty( $event_date ) ) {
				return;
			}

			$event_timestamp = strtotime( $event_date );
			if ( false === $event_timestamp ) {
				return;
			}

			$event_date_normalized = date( 'Y-m-d', $event_timestamp );
			$event_day_start       = strtotime( $event_date_normalized . ' 00:00:00' );

			$duration_unit  = isset( $email['duration_unit'] ) ? $email['duration_unit'] : 'days';
			$duration_value = isset( $email['duration_value'] ) ? absint( $email['duration_value'] ) : 1;
			$delay_seconds  = $this->calculate_delay_seconds( $duration_value, $duration_unit );

			$target_date = $event_day_start + $delay_seconds;
			$today_end   = strtotime( 'tomorrow' ) - 1;

			$event_occurred     = ( $event_day_start <= $today_end );
			$target_date_passed = ( $target_date <= $today_end );

			if ( $event_occurred && $target_date_passed ) {
				$sent_key     = 'ur_custom_email_sent_' . $email_id . '_' . $trigger_type . '_' . $event_date;
				$already_sent = get_user_meta( $user->ID, $sent_key, true );

				if ( ! $already_sent ) {
					$sent_to = isset( $email['send_to'] ) ? $email['send_to'] : ( isset( $email['sent_to'] ) ? $email['sent_to'] : 'all_members' );

					if ( 'admin' === $sent_to ) {
						$this->send_email_to_admin( $email, $user, array() );
					} else {
						$this->send_email( $email, $user, array() );
					}

					update_user_meta( $user->ID, $sent_key, time() );
				}
			}
		}

		/**
		 * Send email to admin.
		 *
		 * @param array   $email      Email data.
		 * @param WP_User|null $user  User object (null for admin-only emails).
		 * @param array   $extra_data Extra data.
		 */
		private function send_email_to_admin( $email, $user = null, $extra_data = array() ) {
			$admin_email = get_option( 'admin_email' );
			if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
				return;
			}

			$email_subject = isset( $email['email_subject'] ) ? $email['email_subject'] : __( 'Custom Email', 'user-registration' );
			$email_content = isset( $email['email_content'] ) ? $email['email_content'] : '';

			if ( empty( $email_content ) ) {
				return;
			}

			$email_subject = sprintf( __( '[Admin Copy] %s', 'user-registration' ), $email_subject );

			$user_id = isset( $user->ID ) ? $user->ID : 0;

			$values = array(
				'user_id'     => $user_id,
				'email'       => isset( $user->user_email ) ? $user->user_email : '',
				'username'    => isset( $user->user_login ) ? $user->user_login : '',
				'first_name'  => $user_id ? get_user_meta( $user_id, 'first_name', true ) : '',
				'last_name'   => $user_id ? get_user_meta( $user_id, 'last_name', true ) : '',
				'blogname'    => get_option( 'blogname' ),
				'blog_info'   => get_bloginfo( 'name' ),
				'home_url'    => get_home_url(),
				'site_url'    => get_site_url(),
				'admin_email' => get_option( 'admin_email' ),
			);

			$values = array_merge( $values, (array) $extra_data );

			$values = apply_filters( 'user_registration_smart_tag_values', $values );

			$form_id    = $user_id && function_exists( 'ur_get_form_id_by_userid' ) ? ur_get_form_id_by_userid( $user_id ) : '';
			$name_value = array();

			if ( $form_id && function_exists( 'user_registration_form_data' ) ) {
				$profile = user_registration_form_data( $user_id, $form_id );
				foreach ( (array) $profile as $key => $field ) {
					$field_name  = isset( $field->field_name ) ? $field->field_name : '';
					$field_value = isset( $field->value ) ? $field->value : '';
					if ( ! empty( $field_name ) ) {
						if ( function_exists( 'ur_format_field_values' ) ) {
							$name_value[ $field_name ] = ur_format_field_values( $field_name, $field_value );
						} else {
							$name_value[ $field_name ] = $field_value;
						}
					}
				}
				$name_value = apply_filters( 'user_registration_process_smart_tag', $name_value, array(), $form_id, $user_id );
			}

			if ( class_exists( 'UR_Emailer' ) && method_exists( 'UR_Emailer', 'parse_smart_tags' ) ) {
				$email_subject = UR_Emailer::parse_smart_tags( $email_subject, $values, $name_value );
				$email_content = UR_Emailer::parse_smart_tags( $email_content, $values, $name_value );
			} else {
				$search        = array( '{{user_id}}', '{{email}}', '{{username}}', '{{first_name}}', '{{last_name}}', '{{blogname}}', '{{blog_info}}', '{{home_url}}', '{{site_url}}', '{{admin_email}}' );
				$replace       = array(
					$values['user_id'],
					$values['email'],
					$values['username'],
					$values['first_name'],
					$values['last_name'],
					$values['blogname'],
					$values['blog_info'],
					$values['home_url'],
					$values['site_url'],
					$values['admin_email'],
				);
				$email_subject = str_replace( $search, $replace, $email_subject );
				$email_content = str_replace( $search, $replace, $email_content );
			}

			if ( function_exists( 'ur_wrap_email_body_content' ) ) {
				$email_content = ur_wrap_email_body_content( $email_content );
			}

			$template_id = $form_id && function_exists( 'ur_get_single_post_meta' ) ? ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' ) : '';
			if ( defined( 'UR_PRO_ACTIVE' ) && UR_PRO_ACTIVE && function_exists( 'ur_get_email_template_wrapper' ) ) {
				$email_content = ur_get_email_template_wrapper( $email_content, false );
			}

			if ( class_exists( 'UR_Emailer' ) && method_exists( 'UR_Emailer', 'ur_get_header' ) ) {
				$header = UR_Emailer::ur_get_header();
			} else {
				$header = array( 'Content-Type: text/html; charset=UTF-8' );
			}

			if ( class_exists( 'UR_Emailer' ) && method_exists( 'UR_Emailer', 'user_registration_process_and_send_email' ) ) {
				UR_Emailer::user_registration_process_and_send_email(
					$admin_email,
					$email_subject,
					$email_content,
					$header,
					array(),
					$template_id
				);
			} elseif ( function_exists( 'wp_mail' ) ) {
					wp_mail( $admin_email, $email_subject, $email_content, $header );
			}
		}

		/**
		 * Get users with active membership.
		 *
		 * @return array User IDs.
		 */
		private function get_users_with_membership() {
			$user_ids = array();

			if ( ! ur_check_module_activation( 'membership' ) ) {
				return $user_ids;
			}

			$users_with_plan = get_users(
				array(
					'meta_key'     => 'ur_user_membership_plan_id',
					'meta_compare' => 'EXISTS',
				)
			);

			foreach ( $users_with_plan as $user ) {
				$user_ids[] = $user->ID;
			}

			$users_with_expiry = get_users(
				array(
					'meta_key'     => 'ur_membership_expiry_date',
					'meta_compare' => 'EXISTS',
				)
			);

			foreach ( $users_with_expiry as $user ) {
				if ( ! in_array( $user->ID, $user_ids, true ) ) {
					$user_ids[] = $user->ID;
				}
			}

			if ( class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) && count( $user_ids ) < 100 ) {
				try {
					$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();

					$potential_users = get_users(
						array(
							'fields'     => 'ID',
							'date_query' => array(
								array(
									'after' => '2 years ago',
								),
							),
							'exclude'    => $user_ids,
						)
					);

					$checked   = 0;
					$max_check = 50;

					foreach ( $potential_users as $user_id ) {
						if ( $checked >= $max_check ) {
							break;
						}
						++$checked;

						try {
							$subscription = $subscription_repository->get_member_subscription( $user_id );
							if ( ! empty( $subscription ) && is_array( $subscription ) ) {
								$has_expiry = false;
								if ( isset( $subscription[0] ) && is_array( $subscription[0] ) ) {
									$has_expiry = ! empty( $subscription[0]['expiry_date'] );
								} elseif ( isset( $subscription['expiry_date'] ) && ! empty( $subscription['expiry_date'] ) ) {
									$has_expiry = true;
								}

								if ( $has_expiry && ! in_array( $user_id, $user_ids, true ) ) {
									$user_ids[] = $user_id;
								}
							}
						} catch ( \Exception $e ) {
						}
					}
				} catch ( \Exception $e ) {
				}
			}

			return $user_ids;
		}

		/**
		 * Calculate delay in seconds based on duration unit and value.
		 *
		 * @param int    $value Duration value.
		 * @param string $unit  Duration unit (second, minutes, days, weeks, months).
		 * @return int Delay in seconds.
		 */
		private function calculate_delay_seconds( $value, $unit ) {
			switch ( $unit ) {
				case 'second':
					return $value;
				case 'minutes':
					return $value * 60;
				case 'days':
					return $value * DAY_IN_SECONDS;
				case 'weeks':
					return $value * WEEK_IN_SECONDS;
				case 'months':
					return $value * MONTH_IN_SECONDS;
				default:
					return $value * DAY_IN_SECONDS;
			}
		}

		/**
		 * Send scheduled email.
		 *
		 * @param string $email_id Email ID.
		 * @param int    $user_id     User ID.
		 * @param array  $extra_data  Extra data.
		 */
		public function send_scheduled_email( $email_id, $user_id, $extra_data = array() ) {
			if ( isset( $extra_data['email'] ) && is_array( $extra_data['email'] ) ) {
				$email = $extra_data['email'];
			} else {
				$emails = $this->data_handler->get_custom_emails();

				if ( ! isset( $emails[ $email_id ] ) ) {
					return;
				}

				$email = $emails[ $email_id ];
			}

			$is_enabled = isset( $email['enabled'] ) ? $email['enabled'] : false;
			if ( function_exists( 'ur_string_to_bool' ) ) {
				$is_enabled = ur_string_to_bool( $is_enabled );
			} elseif ( is_string( $is_enabled ) ) {
				$is_enabled = ( 'yes' === $is_enabled || '1' === $is_enabled || 'true' === $is_enabled );
			}
			if ( ! $is_enabled ) {
				return;
			}

			$sent_to = isset( $email['sent_to'] ) ? $email['sent_to'] : 'all_members';

			if ( 'specific_memberships' === $sent_to && $user_id > 0 ) {
				$user_membership = $this->get_user_membership( $user_id );

				if ( empty( $user_membership ) && ! empty( $extra_data['membership_id'] ) ) {
					$user_membership[] = (string) $extra_data['membership_id'];
				}

				if ( ! $this->matches_membership( $email, $user_membership ) ) {
					return;
				}
			}

			$clean_extra_data = $extra_data;
			if ( isset( $clean_extra_data['email'] ) ) {
				unset( $clean_extra_data['email'] );
			}
			if ( isset( $clean_extra_data['id'] ) ) {
				unset( $clean_extra_data['id'] );
			}

			if ( 'admin' === $sent_to ) {
				$this->send_email_to_admin( $email, null, $clean_extra_data );
			} elseif ( $user_id > 0 ) {
				$user = get_userdata( $user_id );
				if ( $user ) {
					$this->send_email( $email, $user, $clean_extra_data );
				}
			}
		}

		/**
		 * Send email.
		 *
		 * @param array  $email   Email data.
		 * @param WP_User $user     User object.
		 * @param array  $extra_data Extra data.
		 */
		private function send_email( $email, $user, $extra_data = array() ) {
			$is_enabled = isset( $email['enabled'] ) ? $email['enabled'] : false;
			if ( function_exists( 'ur_string_to_bool' ) ) {
				$is_enabled = ur_string_to_bool( $is_enabled );
			} elseif ( is_string( $is_enabled ) ) {
				$is_enabled = ( 'yes' === $is_enabled || '1' === $is_enabled || 'true' === $is_enabled );
			}
			if ( ! $is_enabled ) {
				return;
			}

			$email_subject = isset( $email['email_subject'] ) ? $email['email_subject'] : __( 'Custom Email', 'user-registration' );
			$email_content = isset( $email['email_content'] ) ? $email['email_content'] : '';

			if ( empty( $email_content ) ) {
				return;
			}

			$user_email = isset( $user->user_email ) ? $user->user_email : '';
			$user_id    = isset( $user->ID ) ? $user->ID : 0;

			if ( empty( $user_email ) || ! is_email( $user_email ) ) {
				return;
			}

			$values = array(
				'user_id'     => $user_id,
				'email'       => $user_email,
				'username'    => isset( $user->user_login ) ? $user->user_login : '',
				'first_name'  => get_user_meta( $user_id, 'first_name', true ),
				'last_name'   => get_user_meta( $user_id, 'last_name', true ),
				'blogname'    => get_option( 'blogname' ),
				'blog_info'   => get_bloginfo( 'name' ),
				'home_url'    => get_home_url(),
				'site_url'    => get_site_url(),
				'admin_email' => get_option( 'admin_email' ),
			);

			$values = array_merge( $values, (array) $extra_data );

			$values = apply_filters( 'user_registration_smart_tag_values', $values );

			$form_id    = function_exists( 'ur_get_form_id_by_userid' ) ? ur_get_form_id_by_userid( $user_id ) : '';
			$name_value = array();

			if ( $form_id && function_exists( 'user_registration_form_data' ) ) {
				$profile = user_registration_form_data( $user_id, $form_id );
				foreach ( (array) $profile as $key => $field ) {
					$field_name  = isset( $field->field_name ) ? $field->field_name : '';
					$field_value = isset( $field->value ) ? $field->value : '';
					if ( ! empty( $field_name ) ) {
						if ( function_exists( 'ur_format_field_values' ) ) {
							$name_value[ $field_name ] = ur_format_field_values( $field_name, $field_value );
						} else {
							$name_value[ $field_name ] = $field_value;
						}
					}
				}
				$name_value = apply_filters( 'user_registration_process_smart_tag', $name_value, array(), $form_id, $user_id );
			}

			if ( class_exists( 'UR_Emailer' ) && method_exists( 'UR_Emailer', 'parse_smart_tags' ) ) {
				$email_subject = UR_Emailer::parse_smart_tags( $email_subject, $values, $name_value );
				$email_content = UR_Emailer::parse_smart_tags( $email_content, $values, $name_value );
			} else {
				$search        = array( '{{user_id}}', '{{email}}', '{{username}}', '{{first_name}}', '{{last_name}}', '{{blogname}}', '{{blog_info}}', '{{home_url}}', '{{site_url}}', '{{admin_email}}' );
				$replace       = array(
					$values['user_id'],
					$values['email'],
					$values['username'],
					$values['first_name'],
					$values['last_name'],
					$values['blogname'],
					$values['blog_info'],
					$values['home_url'],
					$values['site_url'],
					$values['admin_email'],
				);
				$email_subject = str_replace( $search, $replace, $email_subject );
				$email_content = str_replace( $search, $replace, $email_content );
			}

			if ( function_exists( 'ur_wrap_email_body_content' ) ) {
				$email_content = ur_wrap_email_body_content( $email_content );
			}

			$template_id = $form_id && function_exists( 'ur_get_single_post_meta' ) ? ur_get_single_post_meta( $form_id, 'user_registration_select_email_template' ) : '';
			if ( defined( 'UR_PRO_ACTIVE' ) && UR_PRO_ACTIVE && function_exists( 'ur_get_email_template_wrapper' ) ) {
				$email_content = ur_get_email_template_wrapper( $email_content, false );
			}

			if ( class_exists( 'UR_Emailer' ) && method_exists( 'UR_Emailer', 'ur_get_header' ) ) {
				$header = UR_Emailer::ur_get_header();
			} else {
				$header = array( 'Content-Type: text/html; charset=UTF-8' );
			}

			if ( class_exists( 'UR_Emailer' ) && method_exists( 'UR_Emailer', 'user_registration_process_and_send_email' ) ) {
				UR_Emailer::user_registration_process_and_send_email(
					$user_email,
					$email_subject,
					$email_content,
					$header,
					array(),
					$template_id
				);
			} elseif ( function_exists( 'wp_mail' ) ) {
					wp_mail( $user_email, $email_subject, $email_content, $header );
			}
		}

		/**
		 * Filter callback for checking if custom email should override default email.
		 *
		 * @param bool   $override      Override value from previous filters.
		 * @param string $trigger_type  Trigger type (member_signs_up, membership_expired, membership_cancellation).
		 * @param string $sent_to       Sent to type (all_members, specific_memberships, admin).
		 * @param int    $user_id       User ID (optional, for membership checks).
		 * @param int    $membership_id Membership ID (optional, for membership checks).
		 * @return bool True if default email should be prevented, false otherwise.
		 */
		public function filter_should_override_default_email( $override, $trigger_type, $sent_to, $user_id = 0, $membership_id = 0 ) {
			if ( true === $override ) {
				return $override;
			}

			return self::should_override_default_email( $trigger_type, $sent_to, $user_id, $membership_id );
		}

		/**
		 * Check if a custom email should override the default email for a trigger.
		 *
		 * @param string $trigger_type Trigger type (member_signs_up, membership_expired, membership_cancellation).
		 * @param string $sent_to      Sent to type (all_members, specific_memberships, admin).
		 * @param int    $user_id      User ID (optional, for membership checks).
		 * @param int    $membership_id Membership ID (optional, for membership checks).
		 * @return bool True if default email should be prevented, false otherwise.
		 */
		public static function should_override_default_email( $trigger_type, $sent_to, $user_id = 0, $membership_id = 0 ) {
			$allowed_triggers = array( 'member_signs_up', 'membership_expired', 'membership_cancellation' );
			if ( ! in_array( $trigger_type, $allowed_triggers, true ) ) {
				return false;
			}

			$emails = get_option( 'user_registration_custom_emails', array() );

			if ( empty( $emails ) ) {
				return false;
			}

			foreach ( $emails as $email ) {
				$is_enabled = isset( $email['enabled'] ) ? $email['enabled'] : false;
				if ( function_exists( 'ur_string_to_bool' ) ) {
					$is_enabled = ur_string_to_bool( $is_enabled );
				} elseif ( is_string( $is_enabled ) ) {
					$is_enabled = ( 'yes' === $is_enabled || '1' === $is_enabled || 'true' === $is_enabled );
				}
				if ( ! $is_enabled ) {
					continue;
				}

				$email_trigger = isset( $email['trigger_event'] ) ? $email['trigger_event'] : ( isset( $email['sent_on'] ) ? $email['sent_on'] : '' );
				if ( $email_trigger !== $trigger_type ) {
					continue;
				}

				$email_timing = isset( $email['delivery_timing'] ) ? $email['delivery_timing'] : 'instant';
				if ( 'scheduled' === $email_timing ) {
				}

				$override_default = isset( $email['override_default'] ) ? $email['override_default'] : false;
				if ( function_exists( 'ur_string_to_bool' ) ) {
					$override_default = ur_string_to_bool( $override_default );
				} elseif ( is_string( $override_default ) ) {
					$override_default = ( 'yes' === $override_default || '1' === $override_default || 'true' === $override_default );
				}
				if ( ! $override_default ) {
					continue;
				}

				$email_sent_to = isset( $email['sent_to'] ) ? $email['sent_to'] : 'all_members';
				if ( $email_sent_to !== $sent_to ) {
					continue;
				}

				if ( 'specific_memberships' === $sent_to && $user_id > 0 ) {
					$email_memberships = isset( $email['membership'] ) ? $email['membership'] : array();
					if ( empty( $email_memberships ) || ! is_array( $email_memberships ) ) {
						continue;
					}

					if ( in_array( 'all', $email_memberships, true ) ) {
						return true;
					}

					if ( $membership_id > 0 && in_array( (string) $membership_id, $email_memberships, true ) ) {
						return true;
					}

					if ( $membership_id <= 0 ) {
						$user_membership_ids = array();

						if ( function_exists( 'ur_check_module_activation' ) && ur_check_module_activation( 'membership' ) ) {
							$user_membership_id = get_user_meta( $user_id, 'ur_user_membership_plan_id', true );
							if ( ! empty( $user_membership_id ) ) {
								$user_membership_ids[] = (string) $user_membership_id;
							}

							if ( class_exists( '\WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository' ) ) {
								try {
									$subscription_repository = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
									$user_subscription       = $subscription_repository->get_member_subscription( $user_id );
									if ( ! empty( $user_subscription ) && is_array( $user_subscription ) && isset( $user_subscription['item_id'] ) ) {
										$user_membership_ids[] = (string) $user_subscription['item_id'];
									}
								} catch ( \Exception $e ) {
								}
							}
						}

						foreach ( $user_membership_ids as $user_membership_id ) {
							if ( in_array( $user_membership_id, $email_memberships, true ) ) {
								return true;
							}
						}
					}
				} else {
					return true;
				}
			}

			return false;
		}
	}

endif;
