<?php
/**
 * Users Module
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/classes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace LdGroupRegistration\Modules\Classes;

if ( ! class_exists( 'Ld_Group_Registration_Users' ) ) {
	/**
	 * Class LD Group Registration Users
	 */
	class Ld_Group_Registration_Users {
		/**
		 * Restrict users after a subscription is put on hold
		 *
		 * @param obj $subscription_obj     Subscription that is put on hold.
		 */
		public function restrict_users_after_sub_put_on_hold( $subscription_obj ) {
			if ( WC_VERSION < '3.0.0' ) {
				$order_id = $subscription_obj->order->id;
				$order    = new \WC_Order( $order_id );
			} else {
				$order_id = $subscription_obj->get_parent_id();
				$order    = $subscription_obj->get_parent();
			}

			$order_user_id   = $order->get_user_id();
			$group_id        = '';
			$is_gleader      = user_can( $order_user_id, 'group_leader' );
			$subscription_id = $subscription_obj->id;
			$group_id        = $this->get_group_associated_to_subscription( $subscription_id );
			if ( ! $is_gleader || empty( $group_id ) ) {
				return;
			}
			$tot_hld_subscription = get_user_meta( $order_user_id, '_wdm_total_hold_subscriptions', true );
			if ( empty( $tot_hld_subscription ) ) {
				$tot_hld_subscription = array();
			}
			if ( ! in_array( $subscription_id, $tot_hld_subscription ) ) {
				$tot_hld_subscription[] = $subscription_id;
			}
			update_user_meta( $order_user_id, '_wdm_total_hold_subscriptions', $tot_hld_subscription );
			$post = array(
				'ID'          => $group_id,
				'post_status' => 'draft',
			);
			\wp_update_post( $post );
		}
		/**
		 * Give access to users after a subscription is active
		 *
		 * @param obj $subscription_obj     Subscription that is activated.
		 */
		public function give_access_to_users_after_sub_active( $subscription_obj ) {
			if ( WC_VERSION < '3.0.0' ) {
				$order_id = $subscription_obj->order->id;
				$order    = new \WC_Order( $order_id );
			} else {
				$order_id = $subscription_obj->get_parent_id();
				$order    = $subscription_obj->get_parent();
			}
			$order_user_id   = $order->get_user_id();
			$group_id        = '';
			$is_gleader      = user_can( $order_user_id, 'group_leader' );
			$subscription_id = $subscription_obj->id;
			$group_id        = $this->get_group_associated_to_subscription( $subscription_id );
			if ( ! $is_gleader || empty( $group_id ) ) {
				return;
			}
			$total_hold_sub = get_user_meta( $order_user_id, '_wdm_total_hold_subscriptions', true );
			if ( ! empty( $total_hold_sub ) && ( in_array( $subscription_id, $total_hold_sub ) ) ) {
				$key = array_search( $subscription_id, $total_hold_sub );
				unset( $total_hold_sub[ $key ] );
				update_user_meta( $order_user_id, '_wdm_total_hold_subscriptions', $total_hold_sub );
			}
			$post = array(
				'ID'          => $group_id,
				'post_status' => 'publish',
			);
			\wp_update_post( $post );
		}
		/**
		 * Get group associated to a subscriptoin
		 *
		 * @param int $subscription_id  ID of the subscription.
		 *
		 * @return int                  ID of the group, else empty string.
		 */
		public function get_group_associated_to_subscription( $subscription_id ) {
			if ( empty( $subscription_id ) ) {
				return '';
			}
			$group_id = '';
			global $wpdb;
			$sql      = 'SELECT post_id from ' . $wpdb->prefix . "postmeta WHERE meta_key LIKE 'wdm_group_subscription_%' and meta_value LIKE {$subscription_id}";
			$group_id = $wpdb->get_var( $sql );
			return $group_id;
		}
		/**
		 * Save additional data related to subscription
		 *
		 * @param int $group_id     ID of the group.
		 * @param int $product_id   ID of the product.
		 * @param int $order_id     ID of the order.
		 */
		public function save_additional_data( $group_id, $product_id, $order_id ) {
			$product_type = $this->get_product_type( $product_id );
			if ( ! empty( $product_type ) ) {
				update_post_meta( $group_id, 'wdm_group_reg_product_type_' . $group_id, $product_type );
			}
			update_post_meta( $group_id, 'wdm_group_reg_order_id_' . $group_id, $order_id );
		}

		/**
		 * Get product type
		 *
		 * @param int $product_id   ID of the product.
		 * @return string           Type of product.
		 */
		public function get_product_type( $product_id ) {
			if ( ! isset( $product_id ) ) {
				return '';
			}
			$org_prd_id = '';
			if ( 'product' == get_post_type( $product_id ) ) {
				$org_prd_id = $product_id;
			} elseif ( 'product_variation' == get_post_type( $product_id ) ) {
				$variable_product = new \WC_Product_Variation( $product_id );
				$org_prd_id       = $variable_product->get_parent_id();
			}
			if ( $org_prd_id ) {
				$product_details = \wc_get_product( $org_prd_id );
				return $product_details->get_type();
			}
			return '';
		}
		/**
		 * Modify product title on group registration page
		 *
		 * @param string $title     Title of the product.
		 * @param int    $group_id     ID of the group
		 * @return string           Updated title of the product.
		 */
		public function modify_product_title_on_grp_reg_page( $title, $group_id ) {
			if ( ! isset( $group_id ) ) {
				return $title;
			}
			$type = get_post_meta( $group_id, 'wdm_group_reg_product_type_' . $group_id, true );
			if ( ( 'subscription' == $type ) || ( 'variable-subscription' == $type ) ) {
				$subscription_id = get_post_meta( $group_id, 'wdm_group_subscription_' . $group_id, true );
				$sub_title       = ! empty( $subscription_id ) ? str_replace( 'Protected: ', '', get_the_title( $subscription_id ) ) . ' : ' . $title : $title;
				return $sub_title;
			}
			return $title;
		}
		/**
		 * Handle paid course access for group leader
		 *
		 * @param boolean $allow_acccess    Whether access is allowed.
		 * @param obj     $order            WC_Order object.
		 * @param mixed   $current_filter   Current filter.
		 *
		 * @return boolean
		 */
		public function handle_group_leader_paid_course_access( $allow_acccess, $order, $current_filter ) {

			$items = $order->get_items();

			foreach ( $items as $key_item_id => $item ) {
				$product_id   = $item['product_id'];
				$product_type = ldgr_get_woo_product_type( $product_id );
				if ( 'variable-subscription' == $product_type || 'variable' == $product_type ) {
					$product_id = $item['variation_id'];
				}

				$courses = get_post_meta( $product_id, '_related_course', true );

				if ( ! empty( $courses ) && array_sum( $courses ) > 0 ) {
					$add_group_leader = isset( $item['_add_group_leader'] ) ? true : false;
					if ( false == $add_group_leader ) {
						$paid_course = get_post_meta( $product_id, '_is_ldgr_paid_course', true );
						if ( ! empty( $paid_course ) && 'on' == $paid_course ) {
							return false;
						}
					}
				}
			}
			return $allow_acccess;
		}
	}
}
