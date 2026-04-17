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

use \LdGroupRegistration\Modules\Classes\Ld_Group_Registration_Woocommerce as Ld_Group_Registration_Woocommerce;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Ld_Group_Registration_Edd' ) ) {
	/**
	 * Class LD Group Registration EDD
	 */
	class Ld_Group_Registration_Edd {

		/**
		 * Adding custom name if group registration product enabled
		 *
		 * @param string $download_title    Download Title.
		 * @param int    $download_id       Download ID.
		 * @param array  $item              Download item details.
		 *
		 * @return string                   Updated download title.
		 */
		public function add_edd_cart_item_name( $download_title, $download_id, $item ) {
			$value = get_post_meta( $download_id, '_is_group_purchase_active', true );
			if ( 'on' == $value ) {
				if ( isset( $item['options']['wdm_ld_group_active'] ) ) {
					$temp = $download_title . ' ' . apply_filters( 'wdm_group_registration_label_below_product_name', __( 'Group Registration', WDM_LDGR_TXT_DOMAIN ), $download_title, $download_id, $item );
					if ( array_key_exists( 'ldgr_group_name', $item['options'] ) && ! empty( $item['options']['ldgr_group_name'] ) ) {
						$temp = $download_title . __( ' - ', WDM_LDGR_TXT_DOMAIN ) . $item['options']['ldgr_group_name'];
					}
					return $temp;
				}
			}
			return $download_title;
		}

		/**
		 * Adding group purchase meta box in product post type
		 */
		public function add_product_meta_box() {
			$screens = array( 'download' );

			foreach ( $screens as $screen ) {
				add_meta_box(
					'wdm_ld_edd',
					__( 'Group purchase', WDM_LDGR_TXT_DOMAIN ),
					array( $this, 'display_group_purchase_checkbox' ),
					$screen
				);
			}
		}

		/**
		 * Group purchase checkbox in product post type
		 *
		 * @param obj $post     Post object.
		 */
		public function display_group_purchase_checkbox( $post ) {
			wp_nonce_field( 'wdm_ld_edd_value', 'wdm_ld_edd' );

			wp_enqueue_script(
				'wdm_related_courses_js',
				plugins_url(
					'js/related_courses.js',
					dirname( __FILE__ )
				),
				array( 'jquery' ),
				LD_GROUP_REGISTRATION_VERSION
			);
			$value       = get_post_meta( $post->ID, '_is_group_purchase_active', true );
			$value_show  = get_post_meta( $post->ID, '_is_checkbox_show_front_end', true );
			$paid_course = get_post_meta( $post->ID, '_is_ldgr_paid_course', true );

			// echo $paid_course;
			// if(empty($paid_course) ){
				// $ldgr_paid_course_for_leader = get_option("ldgr_global_gl_paid_course");
				// if($ldgr_paid_course_for_leader=='on'){
					// $paid_course = 'on';
				// }
			// }.

			$default_option = get_post_meta( $post->ID, '_ldgr_front_default_option', true );

			include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/ldgr-edd-group-registration-metabox.template.php';
		}

		/**
		 * Save metabox settings for group purchas
		 *
		 * @param int $post_id  ID of the post.
		 */
		public function save_metabox_settings( $post_id ) {
			// Check if our nonce is set.
			if ( ! isset( $_POST['_edd_learndash_course'] ) || ! isset( $_POST['_edd_learndash_is_course'] ) ) {
				delete_post_meta( $post_id, '_edd_learndash_course', null );
			}
			if ( ! isset( $_POST['wdm_ld_edd'] ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $_POST['wdm_ld_edd'], 'wdm_ld_edd_value' ) ) {
				return;
			}
			if ( ! isset( $_POST['wdm_ld_edd_group_registration'] ) ) {
				delete_post_meta( $post_id, '_is_group_purchase_active', null );
				delete_post_meta( $post_id, '_is_checkbox_show_front_end', null );
				delete_post_meta( $post_id, '_ldgr_front_default_option', null );
				delete_post_meta( $post_id, '_is_ldgr_paid_course', null );
			} else {
				update_post_meta( $post_id, '_is_group_purchase_active', $_POST['wdm_ld_edd_group_registration'] );
				if ( ! isset( $_POST['wdm_ld_edd_group_registration_show_front_end'] ) ) {
					delete_post_meta( $post_id, '_is_checkbox_show_front_end', null );
				} else {
					update_post_meta( $post_id, '_is_checkbox_show_front_end', $_POST['wdm_ld_edd_group_registration_show_front_end'] );
				}
				if ( ! isset( $_POST['wdm_ld_group_active'] ) ) {
					delete_post_meta( $post_id, '_ldgr_front_default_option', null );
				} else {
					update_post_meta( $post_id, '_ldgr_front_default_option', $_POST['wdm_ld_group_active'] );
				}
				if ( ! isset( $_POST['wdm_ldgr_edd_paid_course'] ) ) {
					update_post_meta( $post_id, '_is_ldgr_paid_course', 'off' );
				} else {
					update_post_meta( $post_id, '_is_ldgr_paid_course', $_POST['wdm_ldgr_edd_paid_course'] );
				}
			}
		}

		/*
		 * It will be displayed only if user checked group registration checkbox
		 */

		/**
		 * Display Group Purchase Options on Downloads page
		 *
		 * @param int   $download_id  ID of the download.
		 * @param array $args         Array of arguments.
		 */
		public function display_group_purchase_options( $download_id = 0, $args = array() ) {
			if ( edd_item_quantities_enabled() && ! edd_download_quantities_disabled( $download_id ) ) {
				$value = get_post_meta( $download_id, '_is_group_purchase_active', true );

				if ( $value == '' ) {
					return;
				}
				$value_show = get_post_meta( $download_id, '_is_checkbox_show_front_end', true );
				wp_enqueue_script(
					'wdm_single_product_edd_gr_js',
					plugins_url(
						'js/wdm_single_product_edd_gr.js',
						dirname( __FILE__ )
					),
					array( 'jquery' ),
					LD_GROUP_REGISTRATION_VERSION
				);

				wp_localize_script( 'wdm_single_product_edd_gr_js', 'wdm_gr_data', array( 'show_quantity' => $value_show ) );

				if ( 'on' == $value_show ) {
					$default_option = get_post_meta( $download_id, '_ldgr_front_default_option', true );
					?>
					<div class="wdm_group_registration">
						<input
							type="radio"
							name="wdm_ld_group_active"
							value=""
							id="wdm_gr_signle" <?php echo ( $default_option == 'individual' ) ? 'checked' : ''; ?> />
						<label for="wdm_gr_signle"> 
							<?php echo esc_html( apply_filters( 'wdm_gr_single_label', __( 'Individual', WDM_LDGR_TXT_DOMAIN ) ) ); ?>
						</label>
						<input
							type="radio"
							name="wdm_ld_group_active"
							value="on"
							id="wdm_gr_group" <?php echo ( $default_option != 'individual' ) ? 'checked' : ''; ?> />
						<label for="wdm_gr_group"> 
							<?php echo esc_html( apply_filters( 'wdm_gr_group_label', __( 'Group', WDM_LDGR_TXT_DOMAIN ) ) ); ?>
						</label>
					</div>
					<?php
				}
				$show_enroll_me = false;
				$paid_course    = get_post_meta( $download_id, '_is_ldgr_paid_course', true );
				// if(empty($paid_course)){
					// $ldgr_paid_course_for_leader = get_option("ldgr_global_gl_paid_course");
					// if($ldgr_paid_course_for_leader=='on')
					// {
						// $show_enroll_me = true;
					// }
				// }.
				if ( empty( $paid_course ) || $paid_course == 'off' ) {
					$show_enroll_me = false;
				} else {
					$show_enroll_me = true;
				}

				if ( $show_enroll_me && ! ldgr_is_user_in_group( $download_id, 'edd' ) ) {
					?>
					<div class="wdm-enroll-me-div">
						<label>
							<input type="checkbox" name="wdm_enroll_me">
							<!-- <label for="wdm_enroll_me"> -->
							<?php echo esc_html( apply_filters( 'wdm_enroll_me_label', __( 'Enroll Me', WDM_LDGR_TXT_DOMAIN ) ) ); ?>
						</label>
						<img id="wdm_enroll_help_btn" src="<?php echo plugins_url( 'media/help.png', dirname( __FILE__ ) ); ?>">
						<br>
						<span class="wdm_enroll_me_help_text" style="display: none;color: #808080;font-style: italic;">
							<?php
								echo esc_html(
									apply_filters(
										'wdm_enroll_me_help_text',
										__( 'This will add Group Leader as Group Member & will charge for it.', WDM_LDGR_TXT_DOMAIN )
									)
								);
							?>
						</span>
					</div>
					<?php
				}
			}
		}

		/**
		 * Setting cart item data for checking if group registration is checked by user
		 *
		 * @param array $item   Item details.
		 */
		public function save_edd_cart_item_data( $item ) {
			$download_id = $item['id'];
			$value       = get_post_meta( $download_id, '_is_group_purchase_active', true );
			if ( 'on' == $value ) {
				$value_show = get_post_meta( $download_id, '_is_checkbox_show_front_end', true );
				if ( 'on' != $value_show ) {
					$item['options']['wdm_ld_group_active'] = 'on';
					// return $item;.
				}

				// update_option('check_value', $item);.
				$post_data = \explode( '&', $_POST['post_data'] );
				foreach ( $post_data as $key => $value ) {
					if ( strpos( $value, 'wdm_ld_group_active' ) !== false ) {
						$post_data_ldgr = \explode( '=', $value );
						if ( 'wdm_ld_group_active' == $post_data_ldgr[0] && 'on' == $post_data_ldgr[1] ) {
							$item['options']['wdm_ld_group_active'] = $post_data_ldgr[1];
						}
					}

					if ( strpos( $value, 'wdm_enroll_me' ) !== false ) {
						$post_data_ldgr = \explode( '=', $value );
						if ( 'wdm_enroll_me' == $post_data_ldgr[0] && 'on' == $post_data_ldgr[1] ) {
							$item['options']['_add_group_leader'] = $post_data_ldgr[1];
						}
					}

					if ( strpos( $value, 'ldgr_group_name' ) !== false ) {
						$post_data_ldgr = \explode( '=', $value );
						if ( 'ldgr_group_name' == $post_data_ldgr[0] && ! empty( $post_data_ldgr[1] ) ) {
							$item['options']['ldgr_group_name'] = urldecode( $post_data_ldgr[1] );
						}
					}
				}
			}
			return $item;
		}

		/**
		 * Create group after succesfully complete the payment.
		 *
		 * @param int $payment_id       ID of the current payment transaction.
		 */
		public function create_group_on_course_payment_complete( $payment_id ) {
			$payment      = new \EDD_Payment( $payment_id );
			$user_info    = $payment->user_info;
			$cart_details = $payment->cart_details;
			update_option( 'user_info', $user_info );
			update_option( 'cart_details', $cart_details );
			$group_creation_done = get_post_meta( $payment_id, 'wdm_successful_group_creation', true );
			if ( 'done' == $group_creation_done ) {
				return;
			}
			foreach ( $cart_details as $item ) {
				$courses            = array();
				$download_id        = $item['id'];
				$quantity           = $item['quantity'];
				$group_registration = isset( $item['item_number']['options']['wdm_ld_group_active'] ) ? $item['item_number']['options']['wdm_ld_group_active'] : '';
				$learndash_courses  = get_post_meta( $download_id, '_edd_learndash_is_course', true );
				if ( isset( $learndash_courses ) && $learndash_courses == 1 ) {
					$courses = maybe_unserialize( get_post_meta( $download_id, '_edd_learndash_course', true ) );
				}

				// check whether group leader paid for course.
				$add_group_leader = isset( $item['item_number']['options']['_add_group_leader'] ) ? true : false;

				// Check if group name saved.
				$group_name = '';
				if ( array_key_exists( 'ldgr_group_name', $item['item_number']['options'] ) && ! empty( $item['item_number']['options']['ldgr_group_name'] ) ) {
					$group_name = $item['item_number']['options']['ldgr_group_name'];
				}

				if ( ! empty( $courses ) && '' != $group_registration ) {
					$uid   = $user_info['id'];
					$user1 = new \WP_User( $uid );
					$user1->add_role( 'group_leader' );
					$user1->remove_role( 'customer' );
					$user1->remove_role( 'subscriber' );
					$group_data['leader'] = $uid;
					$group_data['course'] = $courses;
					$woo_create_group     = new Ld_Group_Registration_Woocommerce();
					$woo_create_group->create_learndash_group( $group_data, $payment, $payment_id, $quantity, $download_id, 'simple', $add_group_leader, $group_name );
					update_post_meta( $payment_id, 'wdm_successful_group_creation', 'done' );
				}
			}
		}

		/**
		 * Add additional group details
		 *
		 * @param string  $html           HTMl of the quantity section.
		 * @param integer $download_id    ID of the download.
		 * @param array   $args           Additional download arguments.
		 *
		 * @return string                   Updated HTML including the group details.
		 * @since 3.8.2
		 */
		public function add_additional_group_details( $html, $download_id = 0, $args = array() ) {
			if ( edd_item_quantities_enabled() && ! edd_download_quantities_disabled( $download_id ) ) {
				$value = get_post_meta( $download_id, '_is_group_purchase_active', true );
				if ( $value == '' ) {
					return;
				}
				$value_show = get_post_meta( $download_id, '_is_checkbox_show_front_end', true );
				wp_enqueue_style(
					'ldgr-edd-styles',
					plugins_url(
						'css/ldgr-edd-style.css',
						dirname( __FILE__ )
					),
					array(),
					LD_GROUP_REGISTRATION_VERSION
				);

				$default_option = get_post_meta( $download_id, '_ldgr_front_default_option', true );

				// Fetch existing group name (if any).
				$group_name = '';
				// 1. Check for updated group name
				// 2. Check if group name set for this product
				if ( empty( $group_name ) ) {
					$group_name = $this->get_existing_group_name( $download_id );
				}
				// 3. Check if group name set in cart

				// Generate updated html.
				ob_start();
				?>
				<div class="ldgr_group_name" <?php echo ( 'individual' == $default_option ) ? 'style="display:none;"' : ''; ?>>
					<label for="ldgr_group_name"><?php esc_html_e( 'Group Name', WDM_LDGR_TXT_DOMAIN ); ?></label>
					<input type="text" name="ldgr_group_name" value="<?php echo $group_name; ?>" placeholder="<?php _e( 'Enter a name for your group', WDM_LDGR_TXT_DOMAIN ); ?>" <?php echo empty( $group_name ) ? '' : 'readonly'; ?>/>
				</div>
				<?php
				$html .= ob_get_clean();
			}
			return $html;
		}

		/**
		 * Update the title of a group
		 *
		 * @param string $group_title    Current group title.
		 * @param int    $leader         User ID of the group leader.
		 * @param int    $product_id     ID of the product.
		 * @param int    $order_id       ID of the Order.
		 * @param mixed  $item           Fetched group title if set, else empty.
		 *
		 * @return string                   Updated group title
		 * @since 3.8.2
		 */
		public function update_group_title( $group_title, $leader, $product_id, $order_id, $item ) {
			// If empty group name, return.
			if ( empty( $item ) ) {
				return $group_title;
			}

			// Check if woo order item, then return.
			if ( is_a( $item, 'WC_Order_Item' ) || is_array( $item ) || is_object( $item ) ) {
				return $group_title;
			}

			return $item;
		}

		/**
		 * Get Existing group name for the download
		 *
		 * @param int $download_id    ID of the download to get the group name for.
		 *
		 * @return string                   Group name if found, empty otherwise.
		 * @since 3.8.2
		 */
		public function get_existing_group_name( $download_id ) {
			if ( ! is_user_logged_in() ) {
				return false;
			}

			$user_id = get_current_user_id();

			if ( empty( $download_id ) ) {
				return false;
			}

			global $wpdb;

			$table = $wpdb->prefix . 'usermeta';
			// Get all group product purchases for the current user.
			$sql = "SELECT SUBSTRING_INDEX( meta_key,  '_' , -1 ) as group_id FROM $table WHERE meta_key LIKE 'wdm_group_product_%' AND user_id = $user_id AND meta_value = $download_id";

			$user_groups = $wpdb->get_col( $sql );

			if ( empty( $user_groups ) ) {
				return false;
			}

			$group_id = 0;
			foreach ( $user_groups as $g_id ) {
				if ( get_post_status( $g_id ) == 'publish' ) {
					$group_id = $g_id;
					break;
				}
			}

			if ( empty( $group_id ) ) {
				return false;
			}

			$group_name = get_the_title( $group_id );

			return $group_name;
		}
	}
}
