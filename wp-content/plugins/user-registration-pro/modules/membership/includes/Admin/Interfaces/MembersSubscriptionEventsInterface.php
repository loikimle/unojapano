<?php
/**
 * URMembership Interfaces.
 *
 * @package  URMembership/MembersSubscriptionEventsInterface
 * @category Interface
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Interfaces;

/**
 * Interface MembersSubscriptionEventsInterface.
 *
 * @package  URMembership\MembersSubscriptionEventsInterface
 * @category Interface
 */
interface MembersSubscriptionEventsInterface extends BaseInterface {

	/**
	 * Get Subscription Events
	 *
	 * @param int $subscription_id Subscription Id.
	 *
	 * @return mixed
	 */
	public function get_subscription_events_by_subscription_id( $subscription_id );
}
