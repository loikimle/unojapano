<?php
/**
 * EDD Module
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/classes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace LdGroupRegistration\Modules\Classes;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Ld_Group_Registration_Woocommerce' ) ) {
	/**
	 * LD Group Registration Woocommerce
	 */
	class Ld_Group_Registration_Woocommerce {

		/**
		 * Group Registration order item meta key for group order
		 *
		 * @var string
		 */
		protected $ldgr_order_item_key;

		/**
		 * Group Registration order item meta key for group name
		 *
		 * @var string
		 */
		protected $ldgr_group_name_item_key;

		public function __construct() {
			$this->ldgr_order_item_key      = '_ldgr_is_group_reg_order';
			$this->ldgr_group_name_item_key = '_ldgr_group_name';
		}
		/**
		 * Handle woocommerce add to cart validation.
		 *
		 * @param bool $passed       Validation status.
		 * @param int  $product_id   ID of the product.
		 * @param int  $quantity     Quantity for the product.
		 *
		 * @return bool              Updated validation.
		 */
		public function handle_woo_add_to_cart_validation( $passed, $product_id, $quantity ) {
			if ( isset( $_GET['resubscribe'] ) ) {
				return true;
			}
			$value = get_post_meta( $product_id, '_is_group_purchase_active', true );
			if ( $value == 'on' ) {
				$value_show     = get_post_meta( $product_id, '_is_checkbox_show_front_end', true );
				$enable_package = ldgr_check_package_enabled( $product_id );
				if ( $value_show == 'on' && ! $enable_package ) {
					if ( isset( $_POST['wdm_ld_group_active'] ) ) {
						if ( 'on' != $_POST['wdm_ld_group_active'] ) {
							global $woocommerce;
							$items = $woocommerce->cart->get_cart();
							foreach ( $items as $key => $item ) {
								if ( isset( $item['wdm_ld_group_active'] ) ) {
									continue;
								}
								if ( $item['product_id'] == $product_id ) {
									wc_add_notice( __( 'Product already exists in cart.', WDM_LDGR_TXT_DOMAIN ), 'error' );
									return false;
								}
							}
							if ( $quantity > 1 ) {
								wc_add_notice( __( 'Only 1 quantity allowed.', WDM_LDGR_TXT_DOMAIN ), 'error' );
								return false;
							}
						}
					} else {
						wc_add_notice( __( 'Select type of product.', WDM_LDGR_TXT_DOMAIN ), 'error' );
						return false;
					}
				}
			}
			return true;
		}

		/**
		 * Update woocommerce cart item quantity
		 *
		 * @param string $product_quantity  Cart item quantity HTML.
		 * @param string $cart_item_key     Cart item key.
		 * @param array  $cart_item          Cart item details.
		 *
		 * @return string                   Updated cart item quantity HTML.
		 */
		public function woo_update_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
			$product_id = $cart_item['product_id'];
			$value      = get_post_meta( $product_id, '_is_group_purchase_active', true );
			if ( $value == 'on' ) {
				$value_show = get_post_meta( $product_id, '_is_checkbox_show_front_end', true );
				if ( 'on' == $value_show && ! isset( $cart_item['wdm_ld_group_active'] ) ) {
					$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
					return $product_quantity;
				}
			}
			return $product_quantity;
		}

		/**
		 * Update Cart item name on group product purchase
		 *
		 * @param string $product_title     Product title in cart.
		 * @param array  $cart_item          Cart item details.
		 * @param string $cart_item_key     Cart item key.
		 *
		 * @return string                   Updated product title in cart.
		 */
		public function woo_update_cart_item_name( $product_title, $cart_item, $cart_item_key ) {
			$product_id = $cart_item['product_id'];
			$value      = get_post_meta( $product_id, '_is_group_purchase_active', true );
			if ( 'on' == $value ) {
				$value_show     = get_post_meta( $product_id, '_is_checkbox_show_front_end', true );
				$enable_package = ldgr_check_package_enabled( $product_id );
				if ( 'on' != $value_show || isset( $cart_item['wdm_ld_group_active'] ) || $enable_package ) {
					$temp = $product_title . '<br>' . apply_filters( 'wdm_group_registration_label_below_product_name', __( 'Group Registration', WDM_LDGR_TXT_DOMAIN ), $product_title, $cart_item_key, $cart_item );
					if ( array_key_exists( 'ldgr_group_name', $cart_item ) && ! empty( $cart_item['ldgr_group_name'] ) ) {
						$temp = $product_title . '<br>' . __( '-  ', WDM_LDGR_TXT_DOMAIN ) . stripslashes( $cart_item['ldgr_group_name'] );
					}

					// Check for variations.
					$variation_id = array_key_exists( 'variation_id', $cart_item ) ? intval( $cart_item['variation_id'] ) : '';
					if ( ! empty( $variation_id ) && array_key_exists( 'ldgr_group_name_' . $variation_id, $cart_item ) && ! empty( $cart_item[ 'ldgr_group_name_' . $variation_id ] ) ) {
						$temp = $product_title . '<br>' . __( '-  ', WDM_LDGR_TXT_DOMAIN ) . stripslashes( $cart_item[ 'ldgr_group_name_' . $variation_id ] );
					}
					return $temp;
				}
			}

			return $product_title;
		}

		/**
		 * Adding 'Group Registration' item meta if group_registration enabled by user
		 *
		 * @param int   $item_id    ID of Item in the cart.
		 * @param array $values     Item data.
		 */
		public function update_woo_order_item_meta( $item_id, $values ) {
			$product_id = $values['product_id'];
			// $product_id = $item->get_product_id();
			$value = get_post_meta( $product_id, '_is_group_purchase_active', true );
			if ( 'on' == $value ) {
				$value_show     = get_post_meta( $product_id, '_is_checkbox_show_front_end', true );
				$enable_package = ldgr_check_package_enabled( $product_id );
				// $values = $item['legacy_values'];
				if ( 'on' != $value_show || isset( $values['wdm_ld_group_active'] ) || $enable_package ) {
					// wc_add_order_item_meta($item_id, 'Group Registration', $values[ 'wdm_ld_group_active' ]);.
					wc_add_order_item_meta(
						$item_id,
						__( 'Group Registration', WDM_LDGR_TXT_DOMAIN ),
						'<span class="dashicons dashicons-yes"></span>'
					);
					// Add hidden meta to be used for detecting group order
					wc_add_order_item_meta( $item_id, $this->ldgr_order_item_key, 1 );

					if ( array_key_exists( 'ldgr_group_name', $values ) && ! empty( $values['ldgr_group_name'] ) ) {
						wc_add_order_item_meta( $item_id, __( 'Group Name', WDM_LDGR_TXT_DOMAIN ), stripslashes( $values['ldgr_group_name'] ) );
						// Add hidden meta to be used for detecting group name
						wc_add_order_item_meta( $item_id, $this->ldgr_group_name_item_key, stripslashes( $values['ldgr_group_name'] ) );
					}
					// Check for variations.
					$variation_id = array_key_exists( 'variation_id', $values ) ? intval( $values['variation_id'] ) : '';
					if ( ! empty( $variation_id ) && array_key_exists( 'ldgr_group_name_' . $variation_id, $values ) && ! empty( $values[ 'ldgr_group_name_' . $variation_id ] ) ) {
						wc_add_order_item_meta( $item_id, __( 'Group Name', WDM_LDGR_TXT_DOMAIN ), stripslashes( $values[ 'ldgr_group_name_' . $variation_id ] ) );
						// Add hidden meta to be used for detecting group name
						wc_add_order_item_meta( $item_id, $this->ldgr_group_name_item_key, stripslashes( $values[ 'ldgr_group_name_' . $variation_id ] ) );
					}
				}
			}
			if ( isset( $values['wdm_enroll_me'] ) ) {
				wc_add_order_item_meta( $item_id, '_add_group_leader', 'on' );
			}
		}

		/**
		 * Checking if group registration enabled by the user for product
		 *
		 * @param array  $item   Item details.
		 * @param array  $values Array of values.
		 * @param string $key    Item key.
		 *
		 * @return array        Updated item details.
		 */
		public function check_group_registration_status_for_product( $item, $values, $key ) {
			$product_id = $values['product_id'];
			$value      = get_post_meta( $product_id, '_is_group_purchase_active', true );
			if ( 'on' == $value ) {
				$value_show     = get_post_meta( $product_id, '_is_checkbox_show_front_end', true );
				$enable_package = ldgr_check_package_enabled( $product_id );
				if ( 'on' != $value_show || array_key_exists( 'wdm_ld_group_active', $values ) || $enable_package ) {
					// $item[ 'wdm_ld_group_active' ] = $values[ 'wdm_ld_group_active' ];
					$item['wdm_ld_group_active'] = 'on';
					if ( array_key_exists( 'ldgr_group_name', $values ) ) {
						$item['ldgr_group_name'] = stripslashes( $values['ldgr_group_name'] );
					}
					// Check for variations.
					$variation_id = array_key_exists( 'variation_id', $values ) ? intval( $values['variation_id'] ) : '';
					if ( ! empty( $variation_id ) && array_key_exists( 'ldgr_group_name_' . $variation_id, $values ) ) {
						$item[ 'ldgr_group_name_' . $variation_id ] = stripslashes( $values[ 'ldgr_group_name_' . $variation_id ] );
					}
				}
			}

			if ( array_key_exists( 'wdm_enroll_me', $values ) ) {
				$item['wdm_enroll_me'] = 'on';
			}

			return $item;
		}

		/**
		 * Setting cart item data for checking if group registration is checked by user
		 *
		 * @param array $cart_item_meta     Cart item metadata.
		 * @param int   $product_id         ID of the product.
		 *
		 * @return array                Updated cart item metadata.
		 */
		public function save_cart_item_data( $cart_item_meta, $product_id ) {
			$value = get_post_meta( $product_id, '_is_group_purchase_active', true );
			if ( 'on' == $value ) {
				$value_show     = get_post_meta( $product_id, '_is_checkbox_show_front_end', true );
				$enable_package = ldgr_check_package_enabled( $product_id );
				if ( 'on' != $value_show || ( isset( $_POST['wdm_ld_group_active'] ) && '' != $_POST['wdm_ld_group_active'] ) || $enable_package ) {
					// $cart_item_meta[ 'wdm_ld_group_active' ] = $_POST[ 'wdm_ld_group_active' ];
					$cart_item_meta['wdm_ld_group_active'] = 'on';
					if ( array_key_exists( 'ldgr_group_name', $_POST ) ) {
						$cart_item_meta['ldgr_group_name'] = stripslashes( $_POST['ldgr_group_name'] );
					}

					// Check for variations.
					$variation_id = array_key_exists( 'variation_id', $_POST ) ? intval( $_POST['variation_id'] ) : '';
					if ( ! empty( $variation_id ) && array_key_exists( 'ldgr_group_name_' . $variation_id, $_POST ) ) {
						$cart_item_meta[ 'ldgr_group_name_' . $variation_id ] = stripslashes( $_POST[ 'ldgr_group_name_' . $variation_id ] );
					}
				}
			}
			if ( isset( $_POST['wdm_enroll_me'] ) ) {
				$cart_item_meta['wdm_enroll_me'] = 'on';
			}
			update_option( 'wdm_cart_test', $cart_item_meta );
			return $cart_item_meta;
		}

		/**
		 * Display group registration options on product single page.
		 */
		public function display_woo_group_registration_options() {
			global $post;

			$product_id = $post->ID;
			$value      = get_post_meta( $product_id, '_is_group_purchase_active', true );
			if ( $value == '' ) {
				return;
			}

			$enable_package = ldgr_check_package_enabled( $product_id );

			$value_show     = get_post_meta( $product_id, '_is_checkbox_show_front_end', true );
			$default_option = get_post_meta( $product_id, '_ldgr_front_default_option', true );
			wp_enqueue_script(
				'wdm_single_product_gr_js',
				plugins_url(
					'js/wdm_single_product_gr.js',
					dirname( __FILE__ )
				),
				array( 'jquery' ),
				LD_GROUP_REGISTRATION_VERSION,
				true
			);
			wp_enqueue_style(
				'wdm_single_product_gr_css',
				plugins_url(
					'css/wdm_single_product_gr.css',
					dirname( __FILE__ )
				)
			);
			$default_script = '';

			if ( 'on' == $value_show && ! $enable_package ) {
				$default_script = 'front';
			} elseif ( $enable_package ) {
				$default_script = 'package';
			}

			$cal_enroll = false;
			if ( is_user_logged_in() ) {
				$cal_enroll = true;
			}

			wp_localize_script(
				'wdm_single_product_gr_js',
				'wdm_gr_data',
				array(
					'default_script' => $default_script,
					'ajax_url'       => admin_url( 'admin-ajax.php' ),
					'ajax_loader'    => plugins_url( 'media/ajax-loader.gif', dirname( __FILE__ ) ),
					'cal_enroll'     => $cal_enroll,
					'default_option' => $default_option,
				)
			);

			if ( 'on' == $value_show && ! $enable_package ) {
				?>
				<div class="wdm_group_registration">
					<input type="radio" name="wdm_ld_group_active" value="" id="wdm_gr_signle" <?php echo ( 'individual' == $default_option ) ? 'checked' : ''; ?>>
					<label for="wdm_gr_signle"> <?php echo esc_html( apply_filters( 'wdm_gr_single_label', __( 'Individual', WDM_LDGR_TXT_DOMAIN ) ) ); ?></label>
					<input type="radio" name="wdm_ld_group_active" value="on" id="wdm_gr_group" <?php echo ( 'individual' != $default_option || $enable_package ) ? 'checked' : ''; ?>>
					<label for="wdm_gr_group"> <?php echo esc_html( apply_filters( 'wdm_gr_group_label', __( 'Group', WDM_LDGR_TXT_DOMAIN ) ) ); ?></label>
				</div>
				<?php
			}
			$show_enroll_me = false;
			$paid_course    = get_post_meta( $product_id, '_is_ldgr_paid_course', true );

			if ( empty( $paid_course ) || 'off' == $paid_course ) {
				$show_enroll_me = false;
			} else {
				$show_enroll_me = true;
			}

			if ( $show_enroll_me && ! ldgr_is_user_in_group( $product_id ) ) {
				?>
				<div class="wdm-enroll-me-div">
					<label>
						<input type="checkbox" name="wdm_enroll_me">
						<!-- <label for="wdm_enroll_me"> -->
						<?php echo esc_html( apply_filters( 'wdm_enroll_me_label', __( 'Enroll Me', WDM_LDGR_TXT_DOMAIN ) ) ); ?>
					</label>
					<img id="wdm_enroll_help_btn" src="<?php echo esc_url( plugins_url( 'media/help.png', dirname( __FILE__ ) ) ); ?>"><br>
					<span class="wdm_enroll_me_help_text" style="display: none;color: #808080;font-style: italic;">
						<?php echo esc_html( apply_filters( 'wdm_enroll_me_help_text', __( 'This will add Group Leader as Group Member & will charge for it.', WDM_LDGR_TXT_DOMAIN ) ) ); ?>
					</span>
				</div>
				<?php
			}
		}

		/**
		 * Check whether order is a renewal order
		 *
		 * @param int $order_id     ID of the order.
		 *
		 * @return bool             True if renewal, false otherwise.
		 */
		public function woo_is_renewal_order( $order_id ) {
			if ( function_exists( 'wcs_order_contains_renewal' ) ) {
				if ( \wcs_order_contains_renewal( $order_id ) ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Creating group on Woocommerce order completion
		 *
		 * @param int $order_id     ID of the completed order.
		 */
		public function handle_group_creation_on_order_completion( $order_id ) {
			if ( $this->woo_is_renewal_order( $order_id ) ) {
				return;
			}

			$order      = new \WC_Order( $order_id );
			$product_id = null;
			$group_data = array();
			$items      = $order->get_items();

			$group_creation_done = get_post_meta( $order_id, 'wdm_successful_group_creation', true );

			if ( 'done' == $group_creation_done ) {
				return;
			}

			if ( WC_VERSION < '3.0.0' ) {
				foreach ( $items as $item ) {
					$product_id         = $item['product_id'];
					$quantity           = apply_filters( 'wdm_modify_total_number_of_registrations', $item['qty'], $product_id, $order_id );
					$product_type       = ldgr_get_woo_product_type( $product_id );
					$group_registration = isset( $item[ __( 'Group Registration', WDM_LDGR_TXT_DOMAIN ) ] ) ? $item[ __( 'Group Registration', WDM_LDGR_TXT_DOMAIN ) ] : '';

					if ( empty( $group_registration ) ) {
						// Get hidden meta to be used for detecting group order
						$group_registration = isset( $item[ $this->ldgr_order_item_key ] );
						$group_registration = empty( $group_registration ) ? '' : $group_registration;
					}

					// check whether group leader paid for course.
					$add_group_leader = isset( $item['_add_group_leader'] ) ? true : false;

					// $courses = maybe_unserialize(get_post_meta($product_id, '_related_course', true));
					$courses = '';
					$uid     = $order->get_user_id();
					if ( $product_type == 'subscription' || $product_type == 'variable-subscription' ) {
						if ( isset( $item['variation_id'] ) && $item['variation_id'] != '' ) {
							$product_id = $item['variation_id'];
						}
						// check if the order is resubscription order.
						if ( wcs_order_contains_resubscribe( $order ) ) { // order is rubscribe order
							// get the subscription id.
							$subscription_ids = ldgr_get_order_subscription_ids( $order, $product_id, $order_id );
							// replace old subscription id with new subscription id
							$old_subscription_id = get_post_meta( $order_id, '_subscription_resubscribe', true );

							// update the entry in the post meta and user meta.
							$subscription_id = $subscription_ids[0]; // since resubscribe will only have single subscription always.
							global $wpdb;
							$sql      = 'SELECT post_id from ' . $wpdb->prefix . "postmeta WHERE meta_key LIKE 'wdm_group_subscription_%' and meta_value LIKE {$old_subscription_id}";
							$group_id = $wpdb->get_var( $sql );

							update_post_meta( $group_id, 'wdm_group_subscription_' . $group_id, $subscription_id );

							// get the group out of draft state
							$tot_hld_subscription = get_user_meta( $uid, '_wdm_total_hold_subscriptions' );
							if ( ( $key_to_pop = array_search( $old_subscription_id, $tot_hld_subscription ) ) !== false ) {
								unset( $tot_hld_subscription[ $key_to_pop ] );
							}
							update_user_meta( $uid, '_wdm_total_hold_subscriptions', $tot_hld_subscription );
							$post = array(
								'ID'          => $group_id,
								'post_status' => 'publish',
							);
							\wp_update_post( $post );
							return;
						}
					}
					if ( $product_type == 'variable-subscription' || $product_type == 'variable' ) {
						$variation_id = $item['variation_id'];
						if ( ! empty( $variation_id ) ) {
							$courses = maybe_unserialize( get_post_meta( $variation_id, '_related_course', true ) );
						}
						$product_id = $variation_id;
					} else {
						$courses = maybe_unserialize( get_post_meta( $product_id, '_related_course', true ) );
					}
					if ( array_sum( $courses ) && '' != $group_registration ) {
						$user1 = new \WP_User( $uid );
						if ( ! user_can( $uid, 'manage_options' ) ) {
							$user1->add_role( 'group_leader' );
							$user1->remove_role( 'customer' );
							$user1->remove_role( 'subscriber' );
						}
						$group_data['leader'] = $uid;
						$group_data['course'] = $courses;
						$this->create_learndash_group( $group_data, $order, $order_id, $quantity, $product_id, $product_type, $add_group_leader, $item );
						update_post_meta( $order_id, 'wdm_successful_group_creation', 'done' );
					} elseif ( ! empty( $courses ) ) {
						foreach ( $courses as $c_id ) {
							ld_update_course_access( $uid, $c_id );
						}
					}
				}
			} else {
				foreach ( $items as $key_item_id => $item ) {
					$key_item_id = $key_item_id;

					$default_quantity = $item['qty'];
					$product_id       = $item['product_id'];
					$product_type     = ldgr_get_woo_product_type( $product_id );
					$courses          = '';
					$uid              = $order->get_user_id();
					if ( $product_type == 'subscription' || $product_type == 'variable-subscription' ) {
						if ( isset( $item['variation_id'] ) && $item['variation_id'] != '' ) {
							$product_id = $item['variation_id'];
						}
						// check if the order is resubscription order
						if ( wcs_order_contains_resubscribe( $order ) ) { // order is rubscribe order
							// get the subscription id
							$subscription_ids = ldgr_get_order_subscription_ids( $order, $product_id, $order_id );
							// replace old subscription id with new subscription id
							$old_subscription_id = get_post_meta( $order_id, '_subscription_resubscribe', true );

							// update the entry in the post meta and user meta
							$subscription_id = $subscription_ids[0]; // since resubscribe will only have single subscription always
							global $wpdb;
							$sql      = 'SELECT post_id from ' . $wpdb->prefix . "postmeta WHERE meta_key LIKE 'wdm_group_subscription_%' and meta_value LIKE {$old_subscription_id}";
							$group_id = $wpdb->get_var( $sql );

							update_post_meta( $group_id, 'wdm_group_subscription_' . $group_id, $subscription_id );

							// get the group out of draft state
							$tot_hld_subscription = get_user_meta( $uid, '_wdm_total_hold_subscriptions' );
							if ( ( $key_to_pop = array_search( $old_subscription_id, $tot_hld_subscription ) ) !== false ) {
								unset( $tot_hld_subscription[ $key_to_pop ] );
							}
							update_user_meta( $uid, '_wdm_total_hold_subscriptions', $tot_hld_subscription );
							$post = array(
								'ID'          => $group_id,
								'post_status' => 'publish',
							);
							\wp_update_post( $post );
							return;
						}
					}
					if ( $product_type == 'variable-subscription' || $product_type == 'variable' ) {
						$variation_id = $item['variation_id'];

						// check if enabled for package
						$enable_package = ldgr_check_package_enabled( $product_id );
						if ( $enable_package ) {
							$package_qty      = get_post_meta( $variation_id, 'wdm_gr_package_seat_' . $variation_id, true );
							$default_quantity = ! empty( $package_qty ) ? ( $package_qty * $default_quantity ) : $default_quantity;
						}

						if ( ! empty( $variation_id ) ) {
							$courses    = maybe_unserialize( get_post_meta( $variation_id, '_related_course', true ) );
							$product_id = $variation_id;
						}
					} else {
						$courses = maybe_unserialize( get_post_meta( $product_id, '_related_course', true ) );
					}

					$quantity = apply_filters( 'wdm_modify_total_number_of_registrations', $default_quantity, $product_id, $order_id );

					$group_registration = isset( $item[ __( 'Group Registration', WDM_LDGR_TXT_DOMAIN ) ] ) ? $item[ __( 'Group Registration', WDM_LDGR_TXT_DOMAIN ) ] : '';

					if ( empty( $group_registration ) ) {
						// Get hidden meta to be used for detecting group order
						$group_registration = isset( $item[ $this->ldgr_order_item_key ] );
						$group_registration = empty( $group_registration ) ? '' : $group_registration;
					}

					// check whether group leader paid for course
					$add_group_leader = isset( $item['_add_group_leader'] ) ? true : false;

					if ( array_sum( $courses ) && $group_registration != '' ) {
						$user1 = new \WP_User( $uid );
						if ( ! user_can( $uid, 'manage_options' ) ) {
							$user1->add_role( 'group_leader' );
							$user1->remove_role( 'customer' );
							$user1->remove_role( 'subscriber' );
						}
						$group_data['leader'] = $uid;
						$group_data['course'] = $courses;
						$this->create_learndash_group( $group_data, $order, $order_id, $quantity, $product_id, $product_type, $add_group_leader, $item );
						update_post_meta( $order_id, 'wdm_successful_group_creation', 'done' );
					} elseif ( ! empty( $courses ) ) {
						foreach ( $courses as $c_id ) {
							ld_update_course_access( $uid, $c_id );
						}
					}
				}
			}
		}

		/**
		 * Create learndash group process
		 *
		 * @param array  $data           Contains the leader and courses data.
		 * @param object $order          WC Order Object.
		 * @param int    $order_id       Order ID.
		 * @param int    $quantity       Quantity.
		 * @param int    $product_id     Product ID.
		 * @param string $product_type   Type of product.
		 * @param bool   $add_group_leader Whether to add gorup leader or not.
		 * @param object $item           Order Item.
		 */
		public function create_learndash_group( $data, $order, $order_id = 1, $quantity = 1, $product_id = 0, $product_type = 'simple', $add_group_leader = false, $item ) {
			global $wpdb;
			$user_data       = get_user_by( 'id', $data['leader'] );
			$username        = $user_data->user_login;
			$subscription_id = '';
			$group_id        = '';
			if ( 'subscription' == $product_type || 'variable-subscription' == $product_type ) {
				$subscription_ids = ldgr_get_order_subscription_ids( $order, $product_id, $order_id );
				$subscription_id  = $subscription_ids[ count( $subscription_ids ) - 1 ];
				// $sql = "SELECT meta_key FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'wdm_group_product_%' AND meta_value LIKE '{$product_id}' AND user_id = ".$data[ 'leader' ];
				$group_id = '';
			} else {
				$sql         = "SELECT SUBSTRING_INDEX( meta_key,  '_' , -1 ) AS group_id FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'wdm_group_product_%' AND meta_value LIKE '{$product_id}' AND user_id = " . $data['leader'];
				$user_groups = $wpdb->get_col( $sql );

				foreach ( $user_groups as $g_id ) {
					if ( get_post_status( $g_id ) == 'publish' ) {
						$group_id = $g_id;
						break;
					}
				}
			}

			// decrease group limit by 1 if group leader is paid for itself.
			if ( $add_group_leader ) {
				$quantity = $quantity - 1;
			}

			// Filter to change the Quantity for the group when product is purchased.
			$quantity = apply_filters( 'wdm_change_group_quantity', $quantity, $order_id, $product_id, $item );

			$group_enroll_course = $data['course'];
			if ( is_numeric( $group_enroll_course ) ) {
				$group_enroll_course = array( $group_enroll_course );
			}

			// Check whether to restrict course access for group leader.
			$ldgr_gl_course_access = get_option( 'ldgr_gl_course_access' );

			if ( empty( $ldgr_gl_course_access ) ) {
				$ldgr_gl_course_access = 'on';
			}

			if ( 'on' !== $ldgr_gl_course_access ) {
				// error_log( 'Since GL course access disabled, removing course access for group leader' );
				foreach ( $group_enroll_course as $course_id ) {
					ld_update_course_access( $data['leader'], $course_id, true );
				}
			}

			if ( '' == $group_id ) {
				$author_id  = 1;
				$title_sql  = "SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $product_id";
				$temp_title = $wpdb->get_var( $title_sql );
				$title      = apply_filters( 'wdm_group_name', $username . ' - ' . $temp_title, $data['leader'], $product_id, $order_id, $item );
				// Set the post ID so that we know the post was created successfully.
				$post_id = wp_insert_post(
					array(
						'comment_status' => 'closed',
						'ping_status'    => 'closed',
						'post_author'    => $author_id,
						'post_title'     => $title,
						'post_status'    => 'publish',
						'post_type'      => 'groups',
					)
				);

				foreach ( $group_enroll_course as $course_id ) {
					update_post_meta( $course_id, 'learndash_group_enrolled_' . $post_id, time() );
				}
				learndash_set_groups_administrators( $post_id, array( $data['leader'] ) );
				update_user_meta( $data['leader'], 'wdm_group_product_' . $post_id, $product_id );
				update_post_meta( $post_id, 'wdm_group_users_limit_' . $post_id, $quantity );
				if ( ! empty( $subscription_id ) ) {
					update_post_meta( $post_id, 'wdm_group_subscription_' . $post_id, $subscription_id );
				}

				// check if group leader has paid for course.
				if ( $add_group_leader ) {
					ld_update_group_access( $data['leader'], $post_id );
				} else {
					// If group leader is not paid for course then remove course access
					// As we are replacing the product id with variation id.
					if ( 'variable-subscription' == $product_type || 'variable' == $product_type ) {
						$parent      = new \WC_Product_Variation( $product_id );
						$paid_course = get_post_meta( $parent->get_parent_id(), '_is_ldgr_paid_course', true );
					} else {
						$paid_course = get_post_meta( $product_id, '_is_ldgr_paid_course', true );
					}
					if ( empty( $paid_course ) || $paid_course == 'off' ) {
						foreach ( $group_enroll_course as $course_id ) {
							ld_update_course_access( $data['leader'], $course_id );
						}
					} else {
						foreach ( $group_enroll_course as $course_id ) {
							ld_update_course_access( $data['leader'], $course_id, true );
						}
					}
				}
				/**
				 * Fired after a new group is created
				 *
				 * @since 1.0.0
				 *
				 * @param int $post_id
				 * @param int $product_id
				 * @param int $order_id
				 * @param object $order
				 * @param object $item
				 */
				do_action( 'ldgr_action_after_create_group', $post_id, $product_id, $order_id, $order, $item );
				do_action( 'wdm_created_new_group_using_ldgr', $post_id, $product_id, $order_id, $order );
			} else {
				// Remove course access only if user is not added in group.
				$group_users = learndash_get_groups_user_ids( $group_id, true );

				if ( ! in_array( $data['leader'], $group_users ) ) {
					if ( $add_group_leader ) {
						ld_update_group_access( $data['leader'], $group_id );
					} else {
						// If group leader is not paid for course then remove course access.
						// As we are replacing the product id with variation id.
						if ( 'variable-subscription' == $product_type || 'variable' == $product_type ) {
							$parent      = new \WC_Product_Variation( $product_id );
							$paid_course = get_post_meta( $parent->get_parent_id(), '_is_ldgr_paid_course', true );
						} else {
							$paid_course = get_post_meta( $product_id, '_is_ldgr_paid_course', true );
						}
						if ( empty( $paid_course ) || 'off' == $paid_course ) {
							// $ldgr_paid_course_for_leader = get_option("ldgr_global_gl_paid_course");
							// if($ldgr_paid_course_for_leader=='on')
							// {
							// foreach ($group_enroll_course as $course_id) {
							// ld_update_course_access($data['leader'],$course_id,true);
							// }
							// }
						} else {
							foreach ( $group_enroll_course as $course_id ) {
								ld_update_course_access( $data['leader'], $course_id, true );
							}
						}
					}
				} elseif ( $add_group_leader ) {
					$quantity = $quantity + 1;
				}

				// Update if not unlimited group seat purchase
				$is_unlimited = wc_get_order_item_meta( $item->get_id(), '_ldgr_unlimited_seats', true );
				if ( 'Yes' != $is_unlimited_product ) {
					$limit  = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
					$limit += $quantity;
					update_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, $limit );
				}
				do_action( 'ldgr_action_after_update_group', $group_id, $product_id, $order_id, $order, $item );
			}
			unset( $order );
		}

		/**
		 * Adding group purchase meta box in product post type.
		 */
		public function add_group_purchase_metabox() {
			$screens = array( 'product' );

			foreach ( $screens as $screen ) {
				add_meta_box(
					'wdm_ld_woo',
					__( 'Group purchase', WDM_LDGR_TXT_DOMAIN ),
					array( $this, 'create_group_checkbox' ),
					$screen
				);
			}
		}

		/**
		 * Group purchase checkbox in product post type.
		 *
		 * @param obj $post     Post object.
		 */
		public function create_group_checkbox( $post ) {
			wp_enqueue_script(
				'wdm_related_courses_js',
				plugins_url( 'js/related_courses.js', dirname( __FILE__ ) ),
				array( 'jquery' ),
				LD_GROUP_REGISTRATION_VERSION
			);
			wp_nonce_field( 'wdm_ld_woo_value', 'wdm_ld_woo' );

			$value       = get_post_meta( $post->ID, '_is_group_purchase_active', true );
			$value_show  = get_post_meta( $post->ID, '_is_checkbox_show_front_end', true );
			$paid_course = get_post_meta( $post->ID, '_is_ldgr_paid_course', true );

			$default_option = get_post_meta( $post->ID, '_ldgr_front_default_option', true );

			$is_unlimited = get_post_meta( $post->ID, 'ldgr_enable_unlimited_members', 1 );
			// $unlimited_label = get_post_meta($post->ID, 'ldgr_unlimited_members_option_label', 1);
			$unlimited_label = get_option( 'ldgr_unlimited_members_label' );
			$unlimited_price = get_post_meta( $post->ID, 'ldgr_unlimited_members_option_price', 1 );

			$template = apply_filters( 'ldgr_product_metabox_path', WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-woocommerce-group-registration-metabox.template.php', $post );

			// Include template.
			include $template;
		}

		/**
		 * Save group purchase settings on product publish
		 *
		 * @param int $post_id  ID of the post.
		 */
		public function save_group_purchase_options( $post_id ) {
			 // Check if our nonce is set.
			if ( ! isset( $_POST['wdm_ld_woo'] ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $_POST['wdm_ld_woo'], 'wdm_ld_woo_value' ) ) {
				return;
			}
			if ( ! isset( $_POST['wdm_ld_group_registration'] ) ) {
				delete_post_meta( $post_id, '_is_group_purchase_active', null );
				delete_post_meta( $post_id, '_is_checkbox_show_front_end', null );
				delete_post_meta( $post_id, '_is_ldgr_paid_course', null );
				delete_post_meta( $post_id, '_ldgr_front_default_option', null );
			} else {

				if ( ! isset( $_POST['wdm_ld_group_registration_show_front_end'] ) ) {
					delete_post_meta( $post_id, '_is_checkbox_show_front_end', null );
				}
				if ( ! isset( $_POST['wdm_ldgr_paid_course'] ) || empty( $_POST['wdm_ldgr_paid_course'] ) ) {
					update_post_meta( $post_id, '_is_ldgr_paid_course', 'off' );
				} else {
					update_post_meta( $post_id, '_is_ldgr_paid_course', $_POST['wdm_ldgr_paid_course'] );
				}

				if ( ! isset( $_POST['wdm_ld_group_active'] ) ) {
					delete_post_meta( $post_id, '_ldgr_front_default_option', null );
				} else {
					update_post_meta( $post_id, '_ldgr_front_default_option', $_POST['wdm_ld_group_active'] );
				}

				update_post_meta( $post_id, '_is_group_purchase_active', $_POST['wdm_ld_group_registration'] );
				update_post_meta(
					$post_id,
					'_is_checkbox_show_front_end',
					$_POST['wdm_ld_group_registration_show_front_end']
				);
			}

		}

		/**
		 * Ajax check and show enroll option
		 */
		public function ajax_show_enroll_option_callback() {
			$cur_var = filter_input( INPUT_POST, 'cur_var', FILTER_SANITIZE_NUMBER_INT );
			$type    = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
			if ( ! empty( $cur_var ) || 0 != $cur_var ) {
				$enrolled = ldgr_is_user_in_group( $cur_var, $type );
				echo esc_html( $enrolled );
				die();
			}
			echo false;
			die();
		}

		/**
		 * Add Group details on the product single page
		 *
		 * @since 3.8.2
		 */
		public function woo_add_group_details() {
			global $post, $woocommerce;

			// Check if post or woo cart empty.
			if ( empty( $post ) || empty( $woocommerce ) || empty( $woocommerce->cart ) ) {
				return;
			}

			$cart_items = $woocommerce->cart->get_cart_contents();
			$in_cart    = false;

			$product_id   = $post->ID;
			$product_type = ldgr_get_woo_product_type( $product_id );

			$value = get_post_meta( $product_id, '_is_group_purchase_active', true );

			// Check if group purchase active.
			if ( '' == $value ) {
				return;
			}

			// Get product group name.
			$group_name = '';

			// If variable product.
			$variation_group_names = array();
			if ( 'variable' == $product_type ) {
				$variation_ids = $this->get_product_variation_ids( $product_id );
				foreach ( $variation_ids as $variation_id ) {
					$in_cart                                = false;
					$variation_group_names[ $variation_id ] = array(
						'value'   => $this->get_group_name( $variation_id, $cart_items, $product_type, $in_cart ),
						'in_cart' => $in_cart,
					);
				}
			} else {
				$group_name = $this->get_group_name( $product_id, $cart_items, $product_type, $in_cart );
			}

			// If product already in cart and without group name then do not allow group naming now.
			if ( $in_cart && empty( $group_name ) && empty( $variation_group_names ) ) {
				return;
			}

			$enable_package = ldgr_check_package_enabled( $product_id );

			$default_option = get_post_meta( $product_id, '_ldgr_front_default_option', true );

			// if ( ! $enable_package ) {
			if ( 'variable' == $product_type ) {
				$this->display_group_name_box( $product_id, $variation_group_names, $default_option, $product_type );
			} else {
				$this->display_group_name_box( $product_id, $group_name, $default_option, $product_type );
			}
			// }
		}

		/**
		 *  Update Group Title for groups created from WC Orders
		 *
		 * @param string $group_title   Group Title to be updated.
		 * @param int    $leader        Group leader user ID.
		 * @param int    $product_id    Product ID.
		 * @param int    $order_id      Order ID.
		 * @param object $item          Order Item.
		 *
		 * @return string group_title   Updated Group Title
		 * @since 3.8.2
		 */
		public function woo_update_group_title( $group_title, $leader, $product_id, $order_id, $item ) {
			// If order item not found or empty, return.
			if ( empty( $item ) ) {
				return $group_title;
			}

			// Check if woo order item.
			if ( ! is_a( $item, 'WC_Order_Item' ) ) {
				return $group_title;
			}

			// Fetch saved group name, if any.
			$group_name = wc_get_order_item_meta( $item->get_id(), __( 'Group Name', WDM_LDGR_TXT_DOMAIN ), true );

			// Check hidden meta for group name
			if ( empty( $group_name ) ) {
				$group_name = wc_get_order_item_meta( $item->get_id(), $this->ldgr_group_name_item_key, true );
			}
			// If found set the group name.
			if ( ! empty( $group_name ) ) {
				$group_title = $group_name;
			}

			return $group_title;
		}

		/**
		 * Get list of product ids for all the group products purchased by the user
		 *
		 * @param integer $user_id        User ID.
		 *
		 * @return array    $product_ids    List of group product ids purchased by the customer or false.
		 * @since 3.8.2
		 */
		public function get_customer_group_products( $user_id = 0 ) {
			$product_ids = false;
			if ( empty( $user_id ) ) {
				$current_user = wp_get_current_user();
				$user_id      = $current_user->ID;
			}

			if ( 0 == $user_id ) {
				return false;
			}

			// GET USER ORDERS (COMPLETED + PROCESSING).
			$customer_orders = get_posts(
				array(
					'numberposts' => -1,
					'meta_key'    => '_customer_user',
					'meta_value'  => $user_id,
					'post_type'   => wc_get_order_types(),
					'post_status' => 'completed',
				)
			);

			// LOOP THROUGH ORDERS AND GET PRODUCT IDS.
			if ( ! $customer_orders ) {
				return false;
			}

			$product_ids = array();
			foreach ( $customer_orders as $customer_order ) {
				$order = wc_get_order( $customer_order->ID );
				$items = $order->get_items();
				foreach ( $items as $item ) {
					$group_registration = false;
					$group_registration = wc_get_order_item_meta( $item->get_id(), __( 'Group Registration', WDM_LDGR_TXT_DOMAIN ), true );

					// Check for additional meta key
					$group_registration = empty( $group_registration ) ? wc_get_order_item_meta( $item->get_id(), $this->ldgr_order_item_key, 1 ) : $group_registration;

					if ( empty( $group_registration ) ) {
						continue;
					}
					$product_id                           = $item->get_product_id();
					$product_ids[ $customer_order->ID ][] = $product_id;
				}
			}
			// $product_ids = array_unique($product_ids);

			return $product_ids;
		}

		/**
		 * Check if product bought by customer and get order item details for that order
		 *
		 * @param int   $product_id         ID of the product.
		 * @param array $customer_products  List of group products bought by the customer, grouped by order as key.
		 *
		 * @return array    $product_details    Details about product order and order item if found, else false for both values.
		 * @since 3.8.2
		 */
		public function get_existing_product_details( $product_id, $customer_products ) {
			$product_details = array(
				'order'  => false,
				'status' => false,
			);

			if ( empty( $product_id ) || empty( $customer_products ) ) {
				return $product_details;
			}

			foreach ( $customer_products as $order_id => $order_products ) {
				if ( in_array( $product_id, $order_products ) ) {
					$item_id = $this->get_order_item_for_product( $order_id, $product_id );
					if ( false !== $item_id ) {
						$product_details = array(
							'item'   => $item_id,
							'status' => true,
						);

						return $product_details;
					}
				}
			}

			return $product_details;
		}

		/**
		 * Get the order item ID for a product purchased by the customer
		 *
		 * @param int $order_id     ID of the order to check for order items.
		 * @param int $product_id   ID of the product to check against in each order item.
		 *
		 * @return mixed            ID of the order item if found, false otherwise.
		 * @since 3.8.2
		 */
		public function get_order_item_for_product( $order_id, $product_id ) {
			if ( empty( $order_id ) || empty( $product_id ) ) {
				return false;
			}

			$order = new \WC_Order( $order_id );
			$items = $order->get_items();
			foreach ( $items as $item ) {
				if ( $product_id == $item['product_id'] ) {
					return $item->get_id();
				}
			}

			return false;
		}

		/**
		 * Get the group name for a previously bought ldgr group product
		 *
		 * @param int   $product_id         ID of the woocommerce product.
		 * @param array $customer_products  List of group products bought by the customer, grouped by order as key.
		 *
		 * @return string   $group_name     Existing group name if found, empty otherwise.
		 * @since 3.8.2
		 */
		public function get_existing_group_name( $product_id, $customer_products ) {
			$group_name      = '';
			$product_details = $this->get_existing_product_details( $product_id, $customer_products );
			if ( $product_details['status'] ) {
				$item_id    = $product_details['item'];
				$group_name = wc_get_order_item_meta( $item_id, __( 'Group Name', WDM_LDGR_TXT_DOMAIN ), true );
				// Check hidden meta for group name
				if ( empty( $group_name ) ) {
					$group_name = wc_get_order_item_meta( $item->get_id(), $this->ldgr_group_name_item_key, true );
				}
			}

			return $group_name;
		}

		/**
		 * Get Updated Group name
		 *
		 * @param int $product_id     ID of the product to get the group name for.
		 *
		 * @return string                   The group name if found, empty otherwise
		 * @since 3.8.2
		 */
		public function get_updated_group_name( $product_id ) {
			$group_name = '';
			$user_id    = get_current_user_id();

			if ( empty( $user_id ) || empty( $product_id ) ) {
				return $group_name;
			}

			global $wpdb;

			$table = $wpdb->prefix . 'usermeta';

			$sql = "SELECT SUBSTRING_INDEX( meta_key,  '_' , -1 ) as group_id FROM $table WHERE meta_key LIKE 'wdm_group_product_%' AND user_id = $user_id AND meta_value = $product_id";

			$user_groups = $wpdb->get_col( $sql );

			$group_id = 0;
			foreach ( $user_groups as $g_id ) {
				if ( get_post_status( $g_id ) == 'publish' ) {
					$group_id = $g_id;
					break;
				}
			}

			if ( empty( $group_id ) ) {
				return $group_name;
			}

			$group_name = get_the_title( $group_id );

			return $group_name;
		}

		/**
		 * Display group name box
		 *
		 * @param int    $product_id        ID of the product.
		 * @param int    $group_name        ID of the group.
		 * @param string $default_option    Default option to be displayed.
		 * @param string $product_type      Type of the product.
		 */
		public function display_group_name_box( $product_id, $group_name, $default_option, $product_type ) {
			$group_section_classes = 'ldgr_group_name';
			if ( 'variable' == $product_type ) {
				$variation_ids          = $this->get_product_variation_ids( $product_id );
				$group_section_classes .= ' ldgr_variations';

				$product_variations = new \WC_Product_Variable( $product_id );
				$default_attributes = $product_variations->get_default_attributes();

				// Check for multiple attributes.
				if ( count( $default_attributes ) > 1 ) {
					// Since currently only one attribute supported, remove the others.
					$attr_count = count( $default_attributes );
					while ( ! empty( $default_attributes ) && 1 < $attr_count ) {
						array_pop( $default_attributes );
						$attr_count = count( $default_attributes );
					}
				}
			}

			$template = apply_filters(
				'ldgr_group_name_box_template_path',
				WDM_LDGR_PLUGIN_DIR . '/modules/templates/ldgr-group-name-box.template.php',
				$product_id,
				$group_name
			);

			include $template;
		}

		/**
		 * Get product variation IDs.
		 *
		 * @param int $product_id    ID of the product.
		 * @return array             List of variation ids if found, else false.
		 */
		public function get_product_variation_ids( $product_id ) {
			if ( empty( $product_id ) ) {
				return false;
			}
			$variation_ids      = array();
			$product_variations = new \WC_Product_Variable( $product_id );
			$product_variations = $product_variations->get_available_variations();
			foreach ( $product_variations as $variation ) {
				array_push( $variation_ids, $variation['variation_id'] );
			}

			return $variation_ids;
		}

		/**
		 * Get group name for the product.
		 *
		 * @param int    $product_id     ID of the product.
		 * @param array  $cart_items     List of items in the cart.
		 * @param string $product_type   Type of the product.
		 * @param bool   $in_cart        Whether the item is in the cart or not.
		 *
		 * @return string $group_name    Group name for the product.
		 */
		public function get_group_name( $product_id, $cart_items, $product_type, &$in_cart ) {
			$group_name = '';

			// No need to fetch existing/updated group name for subscriptions.
			if ( 'subscription' !== $product_type && 'variable-subscription' !== $product_type ) {
				// 1. Check for updated group name
				$group_name = $this->get_updated_group_name( $product_id );

				// 2. Check if group name set
				// Get all customer products
				$customer_products = $this->get_customer_group_products();

				if ( empty( $group_name ) && ! empty( $customer_products ) ) {
					$group_name = $this->get_existing_group_name( $product_id, $customer_products );
				}
			}

			// 3. Check if group name set in cart
			if ( empty( $group_name ) && ! empty( $cart_items ) ) {
				foreach ( $cart_items as $cart_item ) {
					if ( array_key_exists( 'variation_id', $cart_item ) ) {
						// Check if current variation product and the one in cart are same.
						if ( $product_id != $cart_item['variation_id'] ) {
							continue;
						}
					} elseif ( $product_id !== $cart_item['product_id'] ) {
						// Check if current product and the one in cart are same.
						continue;
					}

					// Check if group registration enabled on the product in cart.
					if ( ! array_key_exists( 'wdm_ld_group_active', $cart_item ) || 'on' !== $cart_item['wdm_ld_group_active'] ) {
						continue;
					}

					$in_cart = true;
					// Check if group name assigned to the product in cart.
					if ( array_key_exists( 'ldgr_group_name', $cart_item ) && ! empty( $cart_item['ldgr_group_name'] && empty( $group_name ) ) ) {
						$group_name = stripslashes( $cart_item['ldgr_group_name'] );
						break;
					}

					// Check if group name assigned to the variation in cart.
					if ( array_key_exists( 'variation_id', $cart_item ) ) {
						if ( array_key_exists( 'ldgr_group_name_' . $cart_item['variation_id'], $cart_item ) && ! empty( $cart_item[ 'ldgr_group_name_' . $cart_item['variation_id'] ] && empty( $group_name ) ) ) {
							$group_name = stripslashes( $cart_item[ 'ldgr_group_name_' . $cart_item['variation_id'] ] );
							break;
						}
					}
				}
			}
			return $group_name;
		}

		/**
		 * Check for default selected attribute for a variable product and add a class
		 *
		 * @param int   $variation_id         ID of the variation.
		 * @param array $default_attributes   Default attribute and its value for the product.
		 *
		 * @return string                     default variation class to be added if found, else empty string.
		 */
		public function check_for_default_variation_class( $variation_id, $default_attributes ) {
			$variation_classes = '';
			if ( empty( $variation_id ) ) {
				return $variation_classes;
			}

			$variation_details = new \WC_Product_Variation( $variation_id );

			$variation_attribute = $variation_details->get_variation_attributes();

			$default_key   = key( $default_attributes );
			$variation_key = key( $variation_attribute );

			if ( 'attribute_' . $default_key == $variation_key ) {
				$default_value   = array_shift( $default_attributes );
				$variation_value = array_shift( $variation_attribute );
				if ( $default_value == $variation_value ) {
					$variation_classes = 'ldgr_default_variation';
				}
			}

			return $variation_classes;
		}

		/**
		 * Hide group registration order item meta on admin side
		 *
		 * @param array $hidden_meta
		 * @return array
		 */
		public function hide_admin_group_reg_order_meta( $hidden_meta ) {
			if ( ! in_array( $this->ldgr_order_item_key, $hidden_meta ) ) {
				$hidden_meta[] = $this->ldgr_order_item_key;
			}

			if ( ! in_array( $this->ldgr_group_name_item_key, $hidden_meta ) ) {
				$hidden_meta[] = $this->ldgr_group_name_item_key;
			}

			return $hidden_meta;
		}
	}
}
