<?php
/**
 * Deprecated functions
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace wisdmlabs\ldgroups;

/**
 * LDGR Deprecated function controller
 *
 * @param string $function      Name of function.
 * @param string $version       Version from which the function was deprecated.
 * @param string $replacement   The replacement function to be called.
 */
function ldgr_deprecated_function( $function, $version, $replacement ) {
	do_action( 'deprecated_function_run', $function, $replacement, $version );
	$log_string  = "The {$function} function is <strong>deprecated</strong> since version {$version}.";
	$log_string .= $replacement ? " Replace with {$replacement}." : '';
	error_log( $log_string );
}

 /**
  * Send Group Emails
  *
  * @param string $send_to       Send to email address.
  * @param string $subject       Subject of the email.
  * @param string $message       Message to be sent.
  * @param string $headers       Email headers.
  * @param string $attachments   Email attachments.
  */
function wdm_ld_group_mail( $send_to, $subject, $message, $headers = '', $attachments = '' ) {
	ldgr_deprecated_function( 'wdm_ld_group_mail', '4.0', 'ldgr_send_group_mails' );
	return ldgr_send_group_mails( $send_to, $subject, $message, $headers = '', $attachments );
}

/**
 * Replacing string
 *
 * @param string $search    Search for string.
 * @param string $replace   Replace with string.
 * @param string $subject   String to perform replacement operation.
 *
 * @return string           Subject after performing replacement operations.
 */
function wdm_str_lreplace( $search, $replace, $subject ) {
	ldgr_deprecated_function( 'wdm_str_lreplace', '4.0', 'ldgr_str_lreplace' );
	return ldgr_str_lreplace( $search, $replace, $subject );
}

/**
 * Custom function to fetch all the subscription details for the particular order.
 *
 * @param obj $order        Order object.
 * @param int $product_id   ID of the product.
 * @param int $order_id     ID of the order.
 *
 * @return array            Keys of the subscriptions in the order.
 */
function wdmGetOrderSubscriptionIds( $order, $product_id, $order_id ) {
	ldgr_deprecated_function( 'wdmGetOrderSubscriptionIds', '4.0', 'ldgr_get_order_subscription_ids' );
	return ldgr_get_order_subscription_ids( $order, $product_id, $order_id );
}

/**
 * Get product type
 *
 * @param int $product_id   ID of the product.
 * @return string           Type of the product.
 */
function wdmGetProductType( $product_id ) {
	ldgr_deprecated_function( 'wdmGetProductType', '4.0', 'ldgr_get_woo_product_type' );
	return ldgr_get_woo_product_type( $product_id );
}

/**
 * Is group leader restricted to perform actions.
 *
 * @param int $user_id      ID of the user.
 * @param int $group_id     ID of the group.
 *
 * @return boolean          True if group leader has access, false otherwise.
 */
function isGleaderRestrictedToPerformActions( $user_id, $group_id ) {
	ldgr_deprecated_function( 'isGleaderRestrictedToPerformActions', '4.0', 'is_group_leader_restricted_to_perform_actions' );
	return is_group_leader_restricted_to_perform_actions( $user_id, $group_id );
}

/**
 * Get group IDs for which the user is group leader
 *
 * @param int $user_id  ID of the user.
 *
 * @return array        List of group ids.
 */
function wdmGetAdminGroupIds( $user_id ) {
	ldgr_deprecated_function( 'wdmGetAdminGroupIds', '4.0', 'ldgr_get_leader_group_ids' );
	return ldgr_get_leader_group_ids( $user_id );
}

/**
 * Checks whether the current user has already purchased the product and member of group.
 *
 * @param int    $product_id   ID of the product.
 * @param string $plugin       'edd' for EDD or 'wc' for Woocommerce.
 *
 * @return boolean             True if user is in group and has purchased the product, else false.
 */
function wdmCheckUserinGroup( $product_id, $plugin ) {
	ldgr_deprecated_function( 'wdmCheckUserinGroup', '4.0', 'ldgr_is_user_in_group' );
	return ldgr_is_user_in_group( $product_id, $plugin );
}

/**
 * Using the parent product id it checks whether the package feature is enabled or not.
 *
 * @param int $product_id   ID of the product.
 *
 * @return boolean          True if package enabled, else false.
 */
function wdmCheckPackageEnable( $product_id ) {
	ldgr_deprecated_function( 'wdmCheckPackageEnable', '4.0', 'ldgr_check_package_enabled' );
	return ldgr_check_package_enabled( $product_id );
}
