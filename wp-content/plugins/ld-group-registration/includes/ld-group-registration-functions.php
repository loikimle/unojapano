<?php
/**
 * Common functions
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

// namespace LdGroupRegistration\Includes;

/**
 * Send emails using woocommerce template
 *
 * @param string $send_to       Send to email address.
 * @param string $subject       Subject of the email.
 * @param string $message       Message to be sent.
 * @param string $headers       Email headers.
 * @param array  $attachments   Email attachments.
 * @param array  $extra_data     Additional data related to type of email and group ID.
 */
function ldgr_send_group_mails( $send_to, $subject, $message, $headers = '', $attachments = array(), $extra_data = array() ) {

	/**
	 * Filter the group email extra data
	 *
	 * @since 4.1.2
	 *
	 * @param array $extra_data     Additional data related to type of email sent and the group ID.
	 */
	$extra_data = apply_filters( 'ldgr_group_email_extra_data', $extra_data );

	/**
	 * Filter group email recipient email address
	 *
	 * @since 4.1.2
	 *
	 * @param string $send_to       Email address of the recipient.
	 * @param array $extra_data     Additional information related to the emails to be sent.
	 */
	$send_to = apply_filters( 'ldgr_group_email_to', $send_to, $extra_data );

	/**
	 * Filter group email subject
	 *
	 * @since 4.1.2
	 *
	 * @param string $subject       Subject of the email to be sent.
	 * @param array $extra_data     Additional information related to the emails to be sent.
	 */
	$subject = apply_filters( 'ldgr_group_email_subject', $subject, $extra_data );

	/**
	 * Filter group email body
	 *
	 * @since 4.1.2
	 *
	 * @param string $message       Body of the email to be sent.
	 * @param array $extra_data     Additional information related to the emails to be sent.
	 */
	$message = apply_filters( 'ldgr_group_email_message', $message, $extra_data );

	/**
	 * Filter group email headers
	 *
	 * @since 4.1.2
	 *
	 * @param string $headers       Headers of the email to be sent.
	 * @param array $extra_data     Additional information related to the emails to be sent.
	 */
	$headers = apply_filters( 'ldgr_group_email_headers', $headers, $extra_data );

	/**
	 * Filter group email attachments
	 *
	 * @since 4.1.2
	 *
	 * @param string $attachments   Attachments of the email to be sent.
	 * @param array $extra_data     Additional information related to the emails to be sent.
	 */
	$attachments = apply_filters( 'ldgr_group_email_attachments', $attachments, $extra_data );

	// Select mailer.
	$mailer = 'wp';

	if ( class_exists( 'WooCommerce' ) ) {
		// WooCommerce.
		$mailer = 'woocommerce';
	} elseif ( class_exists( 'EDD_Emails' ) ) {
		// EDD.
		$mailer = 'edd';
	}

	/**
	 * Filter whether to send emails using Woocommerce, EDD or default WP mails.
	 *
	 * @since 4.1.4
	 *
	 * @param string $mailer    The notification method to be used to send emails.
	 * @param array $extra_data Additional information related to the emails to be sent.
	 */
	$mailer = apply_filters( 'ldgr_filter_notification_mailer', $mailer, $extra_data );

	switch ( $mailer ) {
		case 'woocommerce':
			global $woocommerce;
			$mailer  = $woocommerce->mailer();
			$message = $mailer->wrap_message( $subject, $message );
			$mailer->send( $send_to, $subject, $message, $headers, $attachments );
			break;
		case 'edd':
			EDD()->emails->send( $send_to, $subject, $message, $attachments );
			break;
		case 'wp':
			// Add filter to format HTML emails.
			add_filter( 'wp_mail_content_type', 'ldgr_set_mail_content_type' );
			wp_mail( $send_to, $subject, $message, $headers, $attachments );
			// Reset it to what it was before.
			remove_filter( 'wp_mail_content_type', 'ldgr_set_mail_content_type' );
			break;
		default:
			/**
			 * Allow 3rd party plugins to use different emails to send emails
			 *
			 * @since 4.1.4
			 *
			 * @param string $mailer        The notification method to be used to send emails.
			 * @param array  $extra_data    Additional information related to the emails to be sent.
			 * @param string $send_to       Email address of the recipient.
			 * @param string $subject       Subject of the email to be sent.
			 * @param string $message       Body of the email to be sent.
			 * @param string $headers       Headers of the email to be sent.
			 * @param string $attachments   Attachments of the email to be sent.
			 */
			do_action( 'ldgr_action_custom_notification_mail', $mailer, $extra_data, $send_to, $subject, $message, $headers, $attachments );
			break;
	}
}

/**
 * Set mail content type to HTML
 *
 * @since   4.1.2
 *
 * @return string
 */
function ldgr_set_mail_content_type() {
	/**
	 * Set Group Registrations email content type
	 *
	 * @param string $content_type      Set mail content type to HTML(default) or plain text.
	 */
	return apply_filters( 'ldgr_set_mail_content_type', 'text/html' );
}

/**
 * Remove add to cart button from shop page.
 */
function wdm_remove_loop_button() {
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
}

/**
 * Redirect to checkout
 */
function wdm_redirect_to_checkout() {
	global $woocommerce;
	$checkout_url = $woocommerce->cart->get_checkout_url();

	return $checkout_url;
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
function ldgr_str_lreplace( $search, $replace, $subject ) {
	$pos = strrpos( $subject, $search );

	if ( false !== $pos ) {
		$subject = substr_replace( $subject, $replace, $pos, strlen( $search ) );
	}

	return $subject;
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
function ldgr_get_order_subscription_ids( $order, $product_id, $order_id ) {
	if ( ! isset( $order ) ) {
		return array();
	}
	if ( ! ( $order instanceof \WC_Order ) ) {
		return array();
	}
	$subscription_keys = array();
	$subscriptions     = \wcs_get_subscriptions_for_order( $order, array( 'product_id' => $product_id ) );
	if ( ! empty( $subscriptions ) ) {
		foreach ( $subscriptions as $sub_key => $subscription ) {
			$subscription_keys[] = $sub_key;
			$subscription        = $subscription;
		}
	}
	return $subscription_keys;
}
/**
 * Get product type
 *
 * @param int $product_id   ID of the product.
 * @return string           Type of the product.
 */
function ldgr_get_woo_product_type( $product_id ) {
	if ( ! isset( $product_id ) || ( 'product' != get_post_type( $product_id ) ) ) {
		return '';
	}
	$product_details = \wc_get_product( $product_id );
	return $product_details->get_type();
}

/**
 * Is group leader restricted to perform actions.
 *
 * @param int $user_id      ID of the user.
 * @param int $group_id     ID of the group.
 *
 * @return boolean          True if group leader has access, false otherwise.
 */
function is_group_leader_restricted_to_perform_actions( $user_id, $group_id ) {
	if ( 'groups' != get_post_type( $group_id ) || ( ! user_can( $user_id, 'group_leader' ) && ! user_can( $user_id, 'manage_options' ) ) ) {
		return false;
	}
	$type = get_post_meta( $group_id, 'wdm_group_reg_product_type_' . $group_id, true );
	if ( ( 'subscription' == $type ) || ( 'variable-subscription' == $type ) ) {
		$subscription_id = get_post_meta( $group_id, 'wdm_group_subscription_' . $group_id, true );
		if ( ! empty( $subscription_id ) ) {
			$total_hold_sub = get_user_meta( $user_id, '_wdm_total_hold_subscriptions', true );
			if ( ! empty( $total_hold_sub ) && in_array( $subscription_id, $total_hold_sub ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Get group IDs for which the user is group leader
 *
 * @param int $user_id  ID of the user.
 *
 * @return array        List of group ids.
 */
function ldgr_get_leader_group_ids( $user_id = 0 ) {
	global $wpdb;

	// If empty get current user id.
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$group_ids = array();
	if ( ! empty( $user_id ) ) {
		if ( ( learndash_is_admin_user( $user_id ) ) ) {
			$group_ids = learndash_get_groups( true );
		} else {
			$sql_str   = $wpdb->prepare( 'SELECT usermeta.meta_value as group_ids FROM ' . $wpdb->usermeta . ' as usermeta INNER JOIN ' . $wpdb->posts . " as posts ON posts.ID=usermeta.meta_value WHERE  user_id = %d  AND meta_key LIKE %s AND (posts.post_status = 'publish' OR posts.post_status = 'draft')", $user_id, 'learndash_group_leaders_%' );
			$group_ids = $wpdb->get_col( $sql_str );
		}
	}
	return apply_filters( 'ldgr_get_admin_group_ids', $group_ids, $user_id );
}

/**
 * Checks whether the current user has already purchased the product and member of group.
 *
 * @param int    $product_id   ID of the product.
 * @param string $plugin       'edd' for EDD or 'wc' for Woocommerce.
 *
 * @return boolean             True if user is in group and has purchased the product, else false.
 */
function ldgr_is_user_in_group( $product_id, $plugin = 'wc' ) {

	if ( ! is_user_logged_in() ) {
		return false;
	}

	$current_user = wp_get_current_user();
	$user_id      = $current_user->ID;

	$already_purchased = ( 'edd' == $plugin ) ? edd_has_user_purchased( $user_id, $product_id ) :
	wc_customer_bought_product( $current_user->user_email, $user_id, $product_id );

	if ( $already_purchased ) {

		global $wpdb;

		$sql = "SELECT SUBSTRING_INDEX( meta_key,  '_' , -1 ) AS group_id FROM " . $wpdb->prefix . 'usermeta WHERE user_id = ' . $user_id . " AND meta_key LIKE '%wdm_group_product_%' AND meta_value LIKE '" . $product_id . "'";

		$user_groups = $wpdb->get_col( $sql );

		if ( ! empty( $user_groups ) ) {
			foreach ( $user_groups as $group_id ) {
				$if_user = learndash_is_user_in_group( $user_id, $group_id );
				if ( $if_user ) {
					return true;
				}
			}
		}
	}

	return false;
}

/**
 * Using the parent product id it checks whether the package feature is enabled or not.
 *
 * @param int $product_id   ID of the product.
 *
 * @return boolean          True if package enabled, else false.
 */
function ldgr_check_package_enabled( $product_id ) {
	$enable_package = false;

	$var_product = \wc_get_product( $product_id );

	if ( $var_product->get_type() == 'variable' ) {
		$child_var = $var_product->get_children();
		if ( ! empty( $child_var ) ) {
			foreach ( $child_var as $var_id ) {
				$ena_pack = get_post_meta( $var_id, 'wdm_gr_package_' . $var_id, true );
				if ( ! empty( $ena_pack ) && $ena_pack == 'yes' ) {
					$enable_package = true;
					break;
				}
			}
		}
	}
	return $enable_package;
}

/**
 * Get templates passing attributes and including the file.
 *
 * @param string $template_path Template path.
 * @param array  $args          Arguments. (default: array).
 * @param bool   $return        Whether to return the result or echo. (default: false).
 */
function ldgr_get_template( $template_path, $args = array(), $return = false ) {
	// Check if template exists.
	if ( empty( $template_path ) ) {
		return '';
	}

	// Check if arguments set
	if ( ! empty( $args ) && is_array( $args ) ) {
        extract( $args ); // @codingStandardsIgnoreLine
	}

	/**
	 * Allow 3rd party plugins to filter template arguments.
	 *
	 * @since 4.1.2
	 *
	 * @param array  $args          Template arguments
	 * @param string $template_path Template path
	 */
	$args = apply_filters( 'ldgr_filter_template_args', $args, $template_path );
	/**
	 * Allow 3rd party plugins to filter template path.
	 *
	 * @since 4.1.2
	 *
	 * @param string $template_path Template path
	 * @param array  $args          Template arguments
	 */
	$template_path = apply_filters( 'ldgr_filter_template_path', $template_path, $args );

	// Whether to capture contents in output buffer.
	if ( $return ) {
		ob_start();
	}

	/**
	 * Allow 3rd party plugins to perform actions before template is rendered.
	 *
	 * @since 4.1.2
	 *
	 * @param array  $args          Template arguments
	 * @param string $template_path Template path
	 */
	do_action( 'ldgr_action_before_template', $args, $template_path );

	include $template_path;

	/**
	 * Allow 3rd party plugins to perform actions after template is rendered.
	 *
	 * @since 4.1.2
	 *
	 * @param array  $args          Template arguments
	 * @param string $template_path Template path
	 */
	do_action( 'ldgr_action_after_template', $args, $template_path );

	// Return buffered contents.
	if ( $return ) {
		$contents = ob_get_clean();

		/**
		 * Allow 3rd party plugins to filter returned contents
		 *
		 * @since 4.1.2
		 *
		 * @param string $contents      HTML content rendered by the template
		 * @param array  $args          Template arguments
		 */
		return apply_filters( 'ldgr_filter_get_template_contents', $contents, $args );
	}
}

/**
 * Check if group leader
 *
 * @param int $user_id  ID of the user.
 * @return bool         True if user is group leader, false otherwise.
 *
 * @since   4.0.3
 */
function ldgr_check_if_group_leader( $user_id ) {
	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}
	if ( function_exists( 'learndash_is_group_leader_user' ) ) {
		if ( learndash_is_group_leader_user( $user_id ) ) {
			return true;
		}
		return false;
	} else {
		if ( is_group_leader( $user_id ) ) {
			return true;
		}
		return false;
	}
}

/**
 * Get date in site timezone
 *
 * @param string $timestamp     Valid timestamp to be converted to site timezone.
 *
 * @return string               Date string in site timezone.
 *
 * @since 4.1.3
 */
function ldgr_date_in_site_timezone( $timestamp ) {
	if ( empty( $timestamp ) ) {
		return '';
	}

	// Fetch site timezone.
	/**
	 * Filter the timezone for the returned date
	 *
	 * @since 4.1.3
	 *
	 * @param string $site_timezone     Site timezone
	 */
	$site_timezone = apply_filters( 'ldgr_filter_date_in_site_timezone_timezone', get_option( 'timezone_string' ) );

	// If not set, default to UTC timezone.
	if ( empty( $site_timezone ) ) {
		$site_timezone = 'UTC';
	}

	// @todo: Fetch from options in future.
	$format = 'd-m-Y';

	// If empty format, set default format.
	if ( empty( $format ) ) {
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$format      = $date_format . ' - ' . $time_format;
	}

	// Set return date format.
	/**
	 * Filter the datetime format for the returned date
	 *
	 * @since 4.1.3
	 *
	 * @param string $format        Valid PHP datetime format.
	 * @param string $timestamp     Unix timestamp of the date.
	 */
	$format = apply_filters( 'ldgr_filter_date_in_site_timezone_format', $format, $timestamp );

	$date = new DateTime();
	$date->setTimezone( new DateTimeZone( $site_timezone ) );
	$date->setTimestamp( $timestamp );
	$converted_date_string = $date->format( $format );

	/**
	 * Filter the date string to be returned.
	 *
	 * @since 4.1.3
	 *
	 * @param string $converted_date_string     Converted date string to be returned.
	 * @param object $date                      DateTime object of the returned date.
	 */
	return apply_filters( 'ldgr_filter_date_in_site_timezone', $converted_date_string, $date );
}

/**
 * Get time of day for a date.
 *
 * @param string $datestring    Date string to get time of day for.
 * @param string $time_of_day   Time of the day. Beginning of Day (BOD) or End of Day (EOD).
 *                              Defaults to BOD.
 * @return string $timestamp    Timestamp with the time of day for the date provided.
 *
 * @since 4.1.3
 */
function ldgr_get_date_time_of_day( $datestring, $time_of_day = 'BOD' ) {
	$date_details = array();
	if ( ! empty( $datestring ) ) {
		$date_details = explode( '-', $datestring );
	}

	/**
	 * Filter the timezone for the returned date
	 *
	 * @since 4.1.3
	 *
	 * @param string $site_timezone     Site timezone
	 */
	$site_timezone = apply_filters( 'ldgr_filter_date_in_site_timezone', get_option( 'timezone_string' ) );

	// If not set, default to UTC timezone.
	if ( empty( $site_timezone ) ) {
		$site_timezone = 'UTC';
	}

	$date = new DateTime();
	$date->setTimezone( new DateTimeZone( $site_timezone ) );
	if ( ! empty( $date_details ) ) {
		$date->setDate( $date_details[2], $date_details[1], $date_details[0] );
	}

	switch ( $time_of_day ) {
		case 'BOD':
			$date->setTime( 0, 0, 0 );
			break;

		case 'EOD':
			$date->setTime( 23, 59, 59 );
			break;
	}

	/**
	 * Filter timestamp returned for get time of day for the date
	 *
	 * @since 4.1.3
	 *
	 * @param string $timestamp     Timestamp returned.
	 * @param object $date          DateTime class object of the returned date.
	 * @param string $datestring    Date string in Y-m-d format.
	 * @param string $time_of_day   Time of day
	 */
	return apply_filters( 'ldgr_filter_get_date_time_of_day', $date->getTimestamp(), $date, $datestring, $time_of_day );
}
