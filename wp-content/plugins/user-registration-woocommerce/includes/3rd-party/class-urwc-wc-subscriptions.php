<?php
/**
 * URWC_WC_Subscriptions
 *
 * Support for WooCommerce subscription extention
 *
 * @class    URWC_WC_Subscriptions
 * @version  1.0.0
 * @package  3rd-party/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URWC_WC_Subscriptions Class
 */
class URWC_WC_Subscriptions {

	/**
	 * Hanldes the hooks for subscriptions extension support
	 *
	 * @since 1.0.4
	 */
	public function __construct() {
		add_filter( 'user_registration_account_menu_items', array( $this, 'user_registration_account_menu_items_subscriptions' ) );
		add_action( 'user_registration_account_subscriptions_endpoint', array( $this, 'wc_endpoint_content' ) );
		add_action( 'user_registration_account_view-subscription_endpoint', array( $this, 'get_view_subscription_template' ) );
		add_filter( 'wcs_get_view_subscription_url', array( $this, 'woocommerce_get_view_subscription_url' ), 10, 2 );
	}

	/**
	 * Injects the subscriptions menu item to account page tabs
	 *
	 * @since  1.0.4
	 *
	 * @param $items
	 * @return array
	 */
	function user_registration_account_menu_items_subscriptions( $items ) {

		$key    = 'orders';
		$offset = array_search( $key, array_keys( $items ) );
		$result = array_merge(
			array_slice( $items, 0, $offset ),
			array(
				get_option( 'woocommerce_myaccount_subscriptions_endpoint', 'subscriptions' )             => __( 'Subscriptions', 'user-registration-woocommerce' ),
			),
			array_slice( $items, $offset, null )
		);
		return $result;
	}

	/**
	 * Endpoint HTML content.
	 *
	 * @since 1.0.4
	 *
	 * @param int $current_page
	 */
	public function wc_endpoint_content( $current_page = 1 ) {

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );

		wc_get_template( 'myaccount/subscriptions.php', array( 'current_page' => $current_page ), '', plugin_dir_path( WC_Subscriptions::$plugin_file ) . 'templates/' );
	}

	/**
	 * Load the view subscription template.
	 *
	 * @since 1.0.4
	 */
	public static function get_view_subscription_template( $subscription_id ) {
		$subscription = wcs_get_subscription( absint( $subscription_id ) );
		if ( ! $subscription || ! current_user_can( 'view_order', $subscription->get_id() ) ) {
			echo '<div class="user-registration-error">' . esc_html__( 'Invalid Subscription.', 'user-registration-woocommerce' ) . ' <a href="' . esc_url( ur_get_page_permalink( 'myaccount' ) ) . '">' . esc_html__( 'My Account', 'user-registration-woocommerce' ) . '</a>' . '</div>';
			return;
		}
		wc_get_template( 'myaccount/view-subscription.php', compact( 'subscription' ), '', plugin_dir_path( WC_Subscriptions::$plugin_file ) . 'templates/' );
	}

	/**
	 * Modify the view subscription url.
	 *
	 * @since 1.0.4
	 *
	 * @return string view subscription url
	 */
	public function woocommerce_get_view_subscription_url( $url, $id ) {
		return ur_wc_get_endpoint_url( 'view-subscription', $id, $url );
	}
}

new URWC_WC_Subscriptions();
