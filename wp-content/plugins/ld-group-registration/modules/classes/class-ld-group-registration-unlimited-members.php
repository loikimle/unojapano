<?php
/**
 * Unlimited Members Module
 *
 * @since      4.1.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/classes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace LdGroupRegistration\Modules\Classes;

defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'Ld_Group_Registration_Unlimited_Members' ) ) {
    /**
	 * Class LD Group Registration Unlimited Members
	 */
    class Ld_Group_Registration_Unlimited_Members
    {
        const UNLIMITED_SEATS_COUNT = 9999999;

        /**
         * Save metabox options for unlimited member settings
         *
         * @param int $post_id
         *
         * @since 4.1.0
         */
        public function save_unlimited_member_settings($post_id)
        {
            if (!isset($_POST[ 'wdm_ld_woo' ]) && !wp_verify_nonce($_POST[ 'wdm_ld_woo' ], 'wdm_ld_woo_value')) {
                return;
            }

            if (array_key_exists('ldgr_enable_unlimited_members', $_POST) && 'on' == $_POST['ldgr_enable_unlimited_members']) {
                update_post_meta($post_id, 'ldgr_enable_unlimited_members', $_POST['ldgr_enable_unlimited_members']);
            } else {
                delete_post_meta($post_id, 'ldgr_enable_unlimited_members');
            }
            
            /* if (array_key_exists('ldgr_unlimited_members_option_label', $_POST) && ! empty($_POST['ldgr_enable_unlimited_members']) && 'on' == $_POST['ldgr_enable_unlimited_members']) {
                update_post_meta($post_id, 'ldgr_unlimited_members_option_label', $_POST['ldgr_unlimited_members_option_label']);
            } else {
                delete_post_meta($post_id, 'ldgr_unlimited_members_option_label');
            } */

            if (array_key_exists('ldgr_unlimited_members_option_price', $_POST) && ! empty($_POST['ldgr_unlimited_members_option_price']) && 'on' == $_POST['ldgr_enable_unlimited_members']) {
                update_post_meta($post_id, 'ldgr_unlimited_members_option_price', floatval($_POST['ldgr_unlimited_members_option_price']));
            } else {
                delete_post_meta($post_id, 'ldgr_unlimited_members_option_price');
            }
        }

        /**
         * Display unlimited members product options
         *
         * @since 4.1.0
         */
        public function display_unlimited_members_product_options()
        {
            global $post;
            $product_id = $post->ID;
            $value = get_post_meta($product_id, '_is_group_purchase_active', true);
            if ($value == '') {
                return;
            }

            $is_unlimited = get_post_meta($product_id, 'ldgr_enable_unlimited_members', 1);
            if ('on' !== $is_unlimited) {
                return;
            }

            // $unlimited_label = get_post_meta($product_id, 'ldgr_unlimited_members_option_label', true);
            $unlimited_label = get_option( 'ldgr_unlimited_members_label' );
            $unlimited_price = get_post_meta($product_id, 'ldgr_unlimited_members_option_price', true);

            if (empty($unlimited_price)) {
                return;
            }

            if ( empty( $unlimited_label ) ) {
                $unlimited_label = __( 'Unlimited Members', WDM_LDGR_TXT_DOMAIN );
            }

            return ldgr_get_template(
                WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-single-product-unlimited-members.template.php',
                array(
                    'unlimited_price'  =>  $unlimited_price,
                    'unlimited_label'  =>  $unlimited_label
                )
            );
        }

        /**
         * Save product options for unlimited member details
         *
         * @param array $cart_item_data
         * @param int $product_id
         *
         * @return array
         *
         * @since 4.1.0
         */
        public function save_unlimited_members_product_options($cart_item_data, $product_id)
        {
            $value = get_post_meta($product_id, '_is_group_purchase_active', true);
            if ('on' === $value) {
                // Check if group registration enabled on the product in cart
                if (! array_key_exists('wdm_ld_group_active', $cart_item_data) || 'on' !== $cart_item_data['wdm_ld_group_active']) {
                    return $cart_item_data;
                }
                if( isset( $_POST['ldgr_unlimited_member_check'] ) && 'yes' === $_POST['ldgr_unlimited_member_check'] ) {
                    $cart_item_data[ "ldgr_unlimited_members" ] = "YES";     
                    $cart_item_data[ "ldgr_unlimited_member_price" ] = floatval($_POST['ldgr_unlimited_member_price']);     
                }
            }
            return $cart_item_data;
        }

        /**
         * Calculate the product price for unlimited members option
         *
         * @param object $cart_object
         *
         * @return object
         *
         * @since 4.1.0
         */
        public function calculate_unlimited_members_product_price($cart_object)
        {
            if( !WC()->session->__isset( "reload_checkout" )) {
                foreach ( WC()->cart->get_cart() as $key => $value ) {
                    // Check if group registration enabled on the product in cart
                    if (! array_key_exists('wdm_ld_group_active', $value) || 'on' !== $value['wdm_ld_group_active']) {
                        continue;
                    }
                    if(isset( $value["ldgr_unlimited_members"]) && isset($value['ldgr_unlimited_member_price'])) {
                        $additionalPrice = floatval($value['ldgr_unlimited_member_price']);
                        if( method_exists( $value['data'], "set_price" ) ) {
                            /* Woocommerce 3.0 + */
                            $value['data']->set_price( $additionalPrice );
                        } else {
                            /* Version before 3.0 */
                            $value['data']->price = ( $additionalPrice );                    
                        }           
                    }
                }
            }
        }

        /**
         * Render details on the cart and checkout page
         *
         * @param array $cart_data
         * @param object $cart_item
         *
         * @return array
         *
         * @since 4.1.0
         */
        public function render_details_on_cart_and_checkout($cart_data, $cart_item = null)
        {
            if (! array_key_exists('wdm_ld_group_active', $cart_item) || 'on' !== $cart_item['wdm_ld_group_active']) {
                return $cart_data;
            }
            $meta_items = array();
            /* Woo 2.4.2 updates */
            if( !empty( $cart_data ) ) {
                $meta_items = $cart_data;
            }
            $product_id = intval( $cart_item['product_id'] );
            // $label = get_post_meta($product_id, 'ldgr_unlimited_members_option_label', true);
            $label = get_option( 'ldgr_unlimited_members_label' );
            $label = empty( $label ) ? __( 'Unlimited Members', WDM_LDGR_TXT_DOMAIN ) : $label;

            if( isset( $cart_item["ldgr_unlimited_members"] ) ) {
                $meta_items[] = array(
                    "name" => $label,
                    "value" => '<span class="dashicons dashicons-yes"></span>'
                );
            }

            return $meta_items;
        }

        /**
         * Update the order meta details
         *
         * @param int $item_id
         * @param array $values
         * @param int $cart_item_key
         *
         * @since 4.1.0
         */
        public function update_order_meta_details($item_id, $values, $cart_item_key)
        {
            $product_id = intval( $values['product_id'] );
            // $label = get_post_meta($product_id, 'ldgr_unlimited_members_option_label', true);
            $label = get_option( 'ldgr_unlimited_members_label' );
            $label = empty( $label ) ? __( 'Unlimited Members', WDM_LDGR_TXT_DOMAIN ) : $label;

            if( isset( $values["ldgr_unlimited_members"] ) ) {
                wc_add_order_item_meta(
                    $item_id,
                    $label,
                    '<span class="dashicons dashicons-yes"></span>'
                );
                wc_add_order_item_meta(
                    $item_id,
                    '_ldgr_unlimited_seats',
                    'Yes'
                );
            }
        }

        /**
         * Remove the quantity field for unlimited member option
         *
         * @param int $product_quantity
         * @param int $cart_item_key
         * @param object $cart_item
         *
         * @return int
         *
         * @since 4.1.0
         */
        public function remove_quantity_for_unlimited_member_products($product_quantity, $cart_item_key, $cart_item)
        {
            // Check if group registration enabled on the product in cart
            if (! array_key_exists('wdm_ld_group_active', $cart_item) || 'on' !== $cart_item['wdm_ld_group_active']) {
                return $product_quantity;
            }

            if(isset( $cart_item["ldgr_unlimited_members"]) && 'YES' === $cart_item['ldgr_unlimited_members'] && isset($cart_item['ldgr_unlimited_member_price'])) {
                $product_quantity = sprintf( '%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity'] );
            }
            return $product_quantity;
        }

        /**
         * Update group quantity to unlimited
         *
         * @param int $qnty
         * @param int $order_id
         * @param int $product_id
         * @param object $item
         *
         * @return int
         *
         * @since 4.1.0
         */
        public function update_group_quantity_to_unlimited($qnty, $order_id, $product_id, $item)
        {
            // If order item not found or empty, return
            if (empty($item)) {
                return $qnty;
            }

            // Check if woo order item
            if (! is_a($item, 'WC_Order_Item')) {
                return $qnty;
            }

            // Check if unlimited members order item,
            $unlimited_members = wc_get_order_item_meta($item->get_id(), '_ldgr_unlimited_seats', true);

            if ( 'Yes' === $unlimited_members ) {
                $qnty = self::UNLIMITED_SEATS_COUNT;
            }

            return $qnty;
        }

        /**
         * Set unlimited seats meta for groups on order completion
         *
         * @param int $group_id
         * @param int $product_id
         * @param int $order_id
         * @param object $order
         * @param object $item
         *
         * @since 4.1.0
         */
        public function update_group_meta_for_unlimited_seats( $group_id, $product_id, $order_id, $order, $item ) {
            // Check if unlimited seats meta set
            $is_unlimited = wc_get_order_item_meta( $item->get_id(), '_ldgr_unlimited_seats', true );
            if ( 'Yes' == $is_unlimited ) {
                update_post_meta( $group_id, 'ldgr_unlimited_seats', 1 );
            }
        }

        /**
         * Hide woocommerce order item meta for unlimited seats
         *
         * @param array $meta_keys  List of hidden order item meta keys.
         * @return array            Updated list of hidden order item meta keys.
         * @since 4.1.0
         */
        public function hide_unlimited_seats_order_meta( $meta_keys ) {
            if ( ! in_array( '_ldgr_unlimited_seats', $meta_keys ) ) {
                $meta_keys[] = '_ldgr_unlimited_seats';
            }
            return $meta_keys;
        }
    }
}
