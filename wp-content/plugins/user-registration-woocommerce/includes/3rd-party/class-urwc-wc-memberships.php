<?php
/**
 * URWC_WC_Subscriptions
 *
 * Support for WooCommerce Memberships extention
 *
 * @class    URWC_WC_Memberships
 * @version  1.0.0
 * @package  3rd-party/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URWC_WC_Memberships Class
 */
class URWC_WC_Memberships {

	/** @var string the endpoint query var key used by the Members Area */
	private $query_var = 'members_area';

	/** @var string the endpoint used by the Members Area */
	private $endpoint;

	/** @var bool whether the installation is using pretty permalinks (true) or query strings (false) as URL rewrite structure */
	private $using_permalinks;

	/**
	 * Initializes the hooks.
	 *
	 * @since 1.0.4
	 */
	public function __construct() {

		$this->using_permalinks = get_option( 'permalink_structure' );
		$this->endpoint         = $this->using_permalinks ? get_option( 'woocommerce_myaccount_members_area_endpoint', 'members-area' ) : $this->query_var;
		add_filter( 'user_registration_account_menu_items', array( $this, 'user_registration_account_menu_items_memberships' ) );
		add_action( "user_registration_account_{$this->endpoint}_endpoint", array( $this, 'wc_endpoint_content' ) );
		// add_filter( 'ur_get_template', array( $this, 'get_members_area_navigation_template' ), 1, 2 );
	}

	/**
	 * Inject menu item for Memberships on account page
	 *
	 * @since  1.0.4
	 *
	 * @param $items
	 * @return array
	 */
	function user_registration_account_menu_items_memberships( $items ) {

		$key    = 'orders';
		$offset = array_search( $key, array_keys( $items ) );
		$result = array_merge(
			array_slice( $items, 0, $offset ),
			array(
				get_option( 'woocommerce_myaccount_members_area_endpoint', 'members-area' )             => __( 'Memberships', 'user-registration-woocommerce' ),
			),
			array_slice( $items, $offset, null )
		);
		return $result;
	}

	/**
	 * Endpoint HTML content.
	 *
	 * @since  1.0.4
	 *
	 * @param int $current_page
	 */
	public function wc_endpoint_content() {
		wc_memberships()->get_frontend_instance()->get_members_area_instance()->output_members_area();
	}

	/**
	 * Adjusts User Registration template loading to replace the account navigation with members area menu items.
	 *
	 * @since 1.0.4
	 *
	 * @param string $located the located template
	 * @param string $template_name the template name
	 * @return string template to load
	 */
	public function get_members_area_navigation_template( $located, $template_name ) {

		if ( 'myaccount/navigation.php' === $template_name && wc_memberships()->get_frontend_instance()->get_members_area_instance()->is_members_area_section() ) {
			$located = wc_locate_template( 'myaccount/my-membership-navigation.php' );
		}

		return $located;
	}
}

new URWC_WC_Memberships();
