<?php
/**
 * Subscription Events Service
 *
 * @package URMembership/Admin/Services
 */

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembersSubscriptionEventsRepository;

/**
 * Subscription Events Service Class.
 */
class SubscriptionEventsService {

	/**
	 * Events Repository.
	 *
	 * @var MembersSubscriptionEventsRepository
	 */
	protected $events_repository;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->events_repository = new MembersSubscriptionEventsRepository();
	}

	/**
	 * Log a subscription event
	 *
	 * @param array $data Event data.
	 *
	 * @return bool|int
	 */
	public function log_event( array $data ) {

		$defaults = array(
			'subscription_id' => 0,
			'user_id'         => 0,
			'event_type'      => '',
			'event_status'    => null,
			'title'           => '',
			'message'         => '',
			'reference_id'    => null,
			'meta'            => null,
		);

		$data = wp_parse_args( $data, $defaults );

		if ( empty( $data['subscription_id'] ) || empty( $data['event_type'] ) ) {
			return false;
		}

		return $this->events_repository->insert_event( $data );
	}

	/**
	 * Logs subscription created event.
	 *
	 * @param int   $subscription_id The Subscription ID.
	 * @param int   $user_id The User ID.
	 * @param array $meta Additional meta information.
	 */
	public function subscription_created( $subscription_id, $user_id, $meta = array() ) {
		return $this->log_event(
			array(
				'subscription_id' => $subscription_id,
				'user_id'         => $user_id,
				'event_type'      => 'created',
				'event_status'    => 'success',
				'title'           => __( 'Subscription Created', 'user-registration' ),
				'message'         => ! empty( $meta['message'] ) ? $meta['message'] : __( 'Subscription was successfully created.', 'user-registration' ),
				'meta'            => $meta,
				'reference_id'    => isset( $meta['order_id'] ) ? $meta['order_id'] : null,
			)
		);
	}

	/**
	 * Logs subscription trial started event.
	 *
	 * @param int   $subscription_id The Subscription ID.
	 * @param int   $user_id The User ID.
	 * @param array $meta Additional meta information.
	 */
	public function trial_started( $subscription_id, $user_id, $meta = array() ) {
		return $this->log_event(
			array(
				'subscription_id' => $subscription_id,
				'user_id'         => $user_id,
				'event_type'      => 'trial_started',
				'event_status'    => 'info',
				'title'           => __( 'Trial Started', 'user-registration' ),
				'message'         => ! empty( $meta['message'] ) ? $meta['message'] : sprintf(
					/* translators: 1: Trial end date */
					__( 'Trial started and will end on %s.', 'user-registration' ),
					date_i18n( get_option( 'date_format' ), strtotime( $meta['trial_end_date'] ) )
				),
				'meta'            => $meta,
			)
		);
	}

	/**
	 * Logs subscription trial ended event.
	 *
	 * @param int   $subscription_id The Subscription ID.
	 * @param int   $user_id The User ID.
	 * @param array $meta Additional meta information.
	 */
	public function trial_ended( $subscription_id, $user_id, $meta = array() ) {
		return $this->log_event(
			array(
				'subscription_id' => $subscription_id,
				'user_id'         => $user_id,
				'event_type'      => 'trial_ended',
				'event_status'    => 'info',
				'title'           => __( 'Trial Ended', 'user-registration' ),
				'message'         => ! empty( $meta['message'] ) ? $meta['message'] : sprintf(
					/* translators: 1: Trial end date */
					__( 'Trial ended on %s.', 'user-registration' ),
					date_i18n( get_option( 'date_format' ), strtotime( $meta['trial_end_date'] ) )
				),
				'meta'            => $meta,
			)
		);
	}

	/**
	 * Logs subscription renewed event.
	 *
	 * @param int   $subscription_id The Subscription ID.
	 * @param int   $user_id The User ID.
	 * @param array $meta Additional meta information.
	 */
	public function subscription_renewed( $subscription_id, $user_id, $meta = array() ) {
		return $this->log_event(
			array(
				'subscription_id' => $subscription_id,
				'user_id'         => $user_id,
				'event_type'      => 'renewed',
				'event_status'    => 'success',
				'title'           => __( 'Subscription Renewal Successful', 'user-registration' ),
				'message'         => ! empty( $meta['message'] ) ? $meta['message'] : __( 'Subscription renewal processed successfully.', 'user-registration' ),
				'meta'            => $meta,
			)
		);
	}

	/**
	 * Logs subscription cancelled event.
	 *
	 * @param int    $subscription_id The Subscription ID.
	 * @param int    $user_id The User ID.
	 * @param string $mode Cancellation mode ('immediately' or 'expiry').
	 * @param array  $meta Additional meta information.
	 */
	public function subscription_canceled( $subscription_id, $user_id, $mode = 'expiry', $meta = array() ) {
		return $this->log_event(
			array(
				'subscription_id' => $subscription_id,
				'user_id'         => $user_id,
				'event_type'      => 'canceled',
				'event_status'    => 'danger',
				'title'           => __( 'Subscription Canceled', 'user-registration' ),
				'message'         => ! empty( $meta['message'] ) ? $meta['message'] : ( 'expiry' === $mode
					? __( 'Subscription will be canceled at the end of the billing period.', 'user-registration' )
					: __( 'Subscription was canceled immediately.', 'user-registration' ) ),
				'meta'            => $meta,
			)
		);
	}

	/**
	 * Logs subscription expired event.
	 *
	 * @param int   $subscription_id The Subscription ID.
	 * @param int   $user_id The User ID.
	 * @param array $meta Additional meta information.
	 */
	public function subscription_expired( $subscription_id, $user_id, $meta = array() ) {
		return $this->log_event(
			array(
				'subscription_id' => $subscription_id,
				'user_id'         => $user_id,
				'event_type'      => 'expired',
				'event_status'    => 'info',
				'title'           => __( 'Subscription Expired', 'user-registration' ),
				'message'         => ! empty( $meta['message'] ) ? $meta['message'] : __( 'Subscription has expired.', 'user-registration' ),
				'meta'            => $meta,
			)
		);
	}

	/**
	 * Logs subscription reactivated event.
	 *
	 * @param int   $subscription_id The Subscription ID.
	 * @param int   $user_id The User ID.
	 * @param array $meta Additional meta information.
	 */
	public function subscription_reactivated( $subscription_id, $user_id, $meta = array() ) {
		return $this->log_event(
			array(
				'subscription_id' => $subscription_id,
				'user_id'         => $user_id,
				'event_type'      => 'reactivated',
				'event_status'    => 'warning',
				'title'           => __( 'Subscription Reactivated', 'user-registration' ),
				'message'         => ! empty( $meta['message'] ) ? $meta['message'] : __( 'Subscription has been reactivated.', 'user-registration' ),
				'meta'            => $meta,
			)
		);
	}

	/**
	 * Logs subscription upgraded event.
	 *
	 * @param int    $subscription_id The Subscription ID.
	 * @param int    $user_id The User ID.
	 * @param string $from Previous membership level.
	 * @param string $to New membership level.
	 * @param array  $meta Additional meta information.
	 */
	public function subscription_upgraded( $subscription_id, $user_id, $meta = array() ) {
		return $this->log_event(
			array(
				'subscription_id' => $subscription_id,
				'user_id'         => $user_id,
				'event_type'      => 'upgraded',
				'event_status'    => 'success',
				'title'           => __( 'Subscription Upgraded', 'user-registration' ),
				'message'         => ! empty( $meta['message'] ) ? $meta['message'] : $meta['message'] ?? sprintf(
					/* translators: 1: Previous membership level 2: New membership level */
					__( 'Membership upgraded from %1$s to %2$s.', 'user-registration' ),
					$meta['from'] ?? 'unknown',
					$meta['to'] ?? 'unknown'
				),
				'meta'            => $meta,
			)
		);
	}

	/**
	 * Get all events for a specific subscription.
	 *
	 * @param int $subscription_id The Subscription ID.
	 * @param int $limit Number of events to retrieve.
	 * @param int $offset Offset for pagination.
	 */
	public function get_events( $subscription_id, $limit = 20, $offset = 0 ) {
		return $this->events_repository->get_subscription_events_by_subscription_id(
			$subscription_id,
			$limit,
			$offset
		);
	}

	/**
	 * Get total number of events for the subscription id.
	 *
	 * @param int $subscription_id The subscription id.
	 */
	public function get_total_events( $subscription_id ) {
		return $this->events_repository
		->get_total_events_by_subscription_id( $subscription_id );
	}

	/**
	 * Render Subscription Events Section.
	 *
	 * @param array $events Subscription Events.
	 */
	public function ur_render_subscription_events_section( array $events ) {
		?>
		<div class="ur-subscription__events">
			<ol class="ur-subscription__events-timeline" role="list">
				<?php
				if ( empty( $events ) ) :
					?>
					<li class="ur-subscription__events-empty">
						<?php esc_html_e( 'No activity yet.', 'user-registration' ); ?>
					</li>
					<?php
				else :
					foreach ( $events as $event ) :

						$title        = $event['title'] ?? '';
						$message      = $event['message'] ?? '';
						$created_at   = $event['created_at'] ?? '';
						$event_status = strtolower( $event['event_status'] ?? '' );
						$event_type   = strtolower( $event['event_type'] ?? '' );
						$reference_id = $event['reference_id'] ?? '';
						$status_class = $this->ur_get_event_status_class( $event_status, $event_type );

						$date = '';
						if ( $created_at ) {
							$timestamp = strtotime( $created_at );
							if ( $timestamp ) {
								$date = date_i18n( 'F j, Y \a\t g:i A', $timestamp );
							}
						}

						$meta = array();
						if ( ! empty( $event['meta'] ) ) {
							$decoded = json_decode( stripslashes( $event['meta'] ), true );
							if ( is_array( $decoded ) ) {
								$meta = $decoded;
							}
						}
						?>
						<li class="ur-subscription__event <?php echo esc_attr( $status_class ); ?>">
							<span class="ur-subscription__event-marker" aria-hidden="true"></span>

							<div class="ur-subscription__event-body">
								<div class="ur-subscription__event-main">
									<h4 class="ur-subscription__event-title">
										<?php echo esc_html( $title ); ?>
									</h4>

									<?php if ( $date ) : ?>
										<div class="ur-subscription__event-date">
											<?php echo '( ' . esc_html( $date ) . ' )'; ?>
										</div>
									<?php endif; ?>
								</div>

								<?php if ( $message ) : ?>
									<div class="ur-subscription__event-message">
										<?php echo esc_html( $message ); ?>
									</div>
								<?php endif; ?>

								<div class="ur-subscription__event-meta">
									<?php
									foreach ( $this->ur_format_event_meta_lines_html( $event_type, $meta ) as $line ) :
										?>
										<div class="ur-subscription__event-meta-line">
											<?php echo wp_kses_post( $line ); ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</li>
						<?php
					endforeach;
				endif;
				?>
			</ol>
		</div>
		<?php
	}

	/**
	 * Get subscription event status class.
	 *
	 * @param string $status Subscription event status.
	 * @param string $type Event Type.
	 * @return string
	 */
	public function ur_get_event_status_class( string $status, string $type ): string {

		switch ( $status ) {
			case 'success':
				return 'ur-subscription__event--success';
			case 'warning':
				return 'ur-subscription__event--warning';
			case 'failed':
			case 'error':
			case 'danger':
				return 'ur-subscription__event--danger';
		}

		return 'ur-subscription__event--default';
	}

	/**
	 * Html for event meta lines.
	 *
	 * @param string $event_type Event Type.
	 * @param array  $meta Extra information.
	 * @return array
	 */
	public function ur_format_event_meta_lines_html( string $event_type, array $meta ): array {

		$lines = array();

		// if ( ! empty( $meta['payment_method'] ) ) {
		// $span[] = sprintf(
		// * translators: 1: Payment method used by user.*/
		// __( 'Payment method: %s', 'user-registration' ),
		// ucfirst( $meta['payment_method'] )
		// );
		// }

		// if ( ! empty( $meta['transaction_id'] ) ) {
		// $span[] = sprintf(
		// * translators: 1: The Transaction ID */
		// __( 'Transaction ID: %s', 'user-registration' ),
		// $meta['transaction_id']
		// );
		// }

		// if ( ! empty( $span ) ) {
		// $lines[] = implode( ', ', $span );
		// }

		if ( ! empty( $meta['next_billing_date'] ) && 'created' === $event_type ) {

			$date    = strtotime( $meta['next_billing_date'] );
			$pretty  = $date ? date_i18n( 'F j, Y', $date ) : $meta['next_billing_date'];
			$lines[] = esc_html__( 'Next renewal date updated to', 'user-registration' ) . ' ' . esc_html( $pretty );
		}

		return $lines;
	}
}
