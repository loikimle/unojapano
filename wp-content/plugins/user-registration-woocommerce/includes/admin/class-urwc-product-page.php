<?php
/**
 * UserRegistration WooCommerce Product Page Settings.
 *
 * @class    URWC_Woocommerce_Product_Page
 * @version  1.5.0
 * @package  UserRegistrationWooCommerce/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'URWC_Woocommerce_Product_Page ' ) ) :

	/**
	 * URWC_Woocommerce_Product_Page Class
	 */
	class URWC_Woocommerce_Product_Page {

		/**
		 * Setting Id.
		 *
		 * @var string
		 */
		public $product_id = false;
		/**
		 * User registration product field key
		 *
		 * @var string
		 */
		private $urwc_product_page_fields_option_key = 'user_registration_woocommerce_product_page_fields';
		/**
		 * User registration product field key
		 *
		 * @var string
		 */
		private $urwc_product_page_settings_form = 'user_registration_woocommerce_product_page_settings_form';

		/**
		 * Product
		 *
		 * @var string
		 */
		public $product = false;

		/**
		 * Constructor.
		 */
		public function __construct() {
			// WooCommerce admin actions.
			add_action( 'woocommerce_product_data_tabs', array( $this, 'urwc_product_data_tabs' ), 10, 1 );
			add_action( 'woocommerce_product_data_panels', array( $this, 'urwc_product_data' ), 10 );
			add_action( 'save_post', array( $this,'urwc_save_product_tab_settings' ), 10, 3 );
		}


		/**
		 * WooCommerce - Product data tabs.
		 *
		 * @since 1.5.0
		 * @param array $product_data_tabs list of product tabs.
		 */
		public function urwc_product_data_tabs( $product_data_tabs ) {
			$product_data_tabs['urwc_woocommerce_product_tabs'] = array(
				'label'  => __( 'UR Form', 'user-registration-woocommerce' ),
				'target' => 'urwc_product_data',
			);
			return $product_data_tabs;
		}

		/** WooCommerce - Product data panels.
		 *
		 * @since 1.5.0
		 */
		public function urwc_product_data() {
			// Check product.
			if ( ! self::urwc_product_set() ) {
				return;
			}

			if ( false === $this->product_id ) {
				return;
			}
			$product_page_settings = $this->get_settings();
			echo '<div id="urwc_product_data" class="panel woocommerce_options_panel">';
			$this->output( $product_page_settings );
			echo '</div>';
		}

		/**
		 * Set the product.
		 *
		 * @param int $product_id Product ID.
		 */
		public function urwc_product_set( $product_id = false ) {

			if ( false === $product_id ) {

				$this->product_id = self::urwc_product_id_get();

			} else {

				$this->product_id = $product_id;
			}

			if (
				empty( $this->product_id ) ||
				( 'product' !== get_post_type( $this->product_id ) )
			) {

				$this->product_id = false;

				return false;
			}

			// Read product.
			$this->product = wc_get_product( $this->product_id );
			if ( empty( $this->product ) ) {

				return false;
			}

			// Product is good.
			return $this->product;

		}

		/** Get product ID
		 *
		 * @param string $method method.
		 */
		public function urwc_product_id_get( $method = 'queried_object' ) {

			switch ( $method ) {

				case 'queried_object':
					$queried_object = get_queried_object();
					$post_id        = ( ! is_null( $queried_object ) && isset( $queried_object->ID ) ) ? $queried_object->ID : false;
					break;

				case 'post':
					global $post;
					$post_id = ( $post && isset( $post->ID ) ) ? $post->ID : false;
					break;

			}

			/** Check post ID
			 *
			 * @param $post_id Post ID.
			 */
			if (
			empty( $post_id ) ||
			( 'product' !== get_post_type( $post_id ) )
			) {

				switch ( $method ) {

					case 'queried_object':
						$post_id = self::urwc_product_id_get( 'post' );
						break;

					case 'post':
						$post_id = self::urwc_product_id_get( 'root' );
						break;

					default:
						return false;
				}
			}

			return $post_id;
		}

		/**
		 * Output the settings in product page.
		 *
		 * @param array $settings Settings.
		 */
		public function output( $settings ) {
			$this->output_fields( $settings );

			$product_form_key             = $this->urwc_product_page_settings_form . '_' . $this->product_id;
			$form_id                      = get_post_meta( $this->product_id, $product_form_key, true );
			$product_page_fields_meta_key = $this->urwc_product_page_fields_option_key . '_' . $this->product_id;
			echo '<div class="user_registration_woocommerce_form_fields_wrapper">';
				$this->urwc_product_display_form_field_lists( $form_id, $product_page_fields_meta_key );
			echo '</div>';
		}

		/**
		 * Output for fields.
		 *
		 * @param array $options Settings.
		 */
		public function output_fields( $options ) {
			$settings = '';
			if ( is_array( $options ) && ! empty( $options ) ) {
				if ( isset( $options['sections'] ) ) {

					foreach ( $options['sections'] as $id => $section ) {
						if ( ! isset( $section['type'] ) ) {
							continue;
						}

						if ( 'card' === $section['type'] ) {
							$settings .= '<div class="user-registration-card ur-mt-4 ur-border-0">';

							$header_css = '';
							if ( isset( $section['preview_link'] ) ) {
								$header_css = 'display:flex; justify-content: space-between;';
							}

							$settings .= '<div class="user-registration-card__header ur-border-0" style="' . esc_attr( $header_css ) . '">';
							if ( ! empty( $section['title'] ) ) {
								$settings .= '<h3 class="user-registration-card__title">' . esc_html( strtoupper( $section['title'] ) );

								if ( isset( $section['back_link'] ) ) {
									$settings .= wp_kses_post( $section['back_link'] );
								}

								$settings .= '</h3>';
							}

							if ( isset( $section['preview_link'] ) ) {
								$settings .= wp_kses_post( $section['preview_link'] );
							}

							$settings .= '</div>';

							if ( ! empty( $section['desc'] ) ) {
								$settings .= '<p class="ur-p-tag">' . wptexturize( wp_kses_post( $section['desc'] ) ) . '</p>';
							}
							$settings .= '<div class="user-registration-card__body pt-0 pb-0">';

							if ( ! empty( $id ) ) {
								do_action( 'user_registration_settings_' . sanitize_title( $id ) );
							}
						}
						if ( is_array( $section['settings'] ) || is_object( $section['settings'] ) ) {
							foreach ( $section['settings'] as $key => $value ) {

								if ( ! isset( $value['type'] ) ) {
									continue;
								}

								if ( ! isset( $value['id'] ) ) {
									$value['id'] = '';
								}
								if ( ! isset( $value['row_class'] ) ) {
									$value['row_class'] = '';
								}
								if ( ! isset( $value['rows'] ) ) {
									$value['rows'] = '';
								}
								if ( ! isset( $value['cols'] ) ) {
									$value['cols'] = '';
								}
								if ( ! isset( $value['title'] ) ) {
									$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
								}
								if ( ! isset( $value['class'] ) ) {
									$value['class'] = '';
								}
								if ( ! isset( $value['css'] ) ) {
									$value['css'] = '';
								}
								if ( ! isset( $value['default'] ) ) {
									$value['default'] = '';
								}
								if ( ! isset( $value['desc'] ) ) {
									$value['desc'] = '';
								}
								if ( ! isset( $value['desc_tip'] ) ) {
									$value['desc_tip'] = false;
								}
								// Custom attribute handling.
								$custom_attributes = array();

								if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
									foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
										$custom_attributes[] = esc_attr( $attribute ) . '=' . esc_attr( $attribute_value ) . '';
									}
								}
								$tooltip_html = '';

								// Switch based on type.
								switch ( $value['type'] ) {
									// Select boxes.
									case 'select':
									case 'multiselect':
										$option_value = get_post_meta( $this->product_id, $value['id'], true ) ? get_post_meta( $this->product_id, $value['id'], true ) : $value['default'];

										$settings .= '<div class="user-registration-global-settings">';
										$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
										$settings .= '<div class="user-registration-global-settings--field">';
										$multiple  = '';
										$type      = '';
										if ( 'multiselect' == $value['type'] ) {
											$type     = '[]';
											$multiple = 'multiple="multiple"';
										}

										$settings .= '<select
												name="' . esc_attr( $value['id'] ) . '' . $type . '"
												id="' . esc_attr( $value['id'] ) . '"
												style="' . esc_attr( $value['css'] ) . '"
												class="' . esc_attr( $value['class'] ) . '"
												' . esc_attr( implode( ' ', $custom_attributes ) ) . '
												' . esc_attr( $multiple ) . '>';

										foreach ( $value['options'] as $key => $val ) {
											$selected = '';

											if ( is_array( $option_value ) ) {
												$selected = selected( in_array( $key, $option_value ), true, false );
											} else {
												$selected = selected( $option_value, $key, false );
											}

											$settings .= '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>';
											$settings .= esc_html( $val );
											$settings .= '</option>';
										}

										$settings .= '</select>';
										$settings .= '</div>';
										$settings .= '</div>';
										break;
								}
								$settings .= ' </div > ';
								$settings .= ' </div > ';
							}
						}
					}
				}
			}
			echo $settings; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		/**
		 * Woocommerece product page settings.
		 */
		public function urwc_woocommerece_product_page_settings() {
			$forms    = ur_get_all_user_registration_form();
			$forms[0] = __( 'None', 'user-registration-woocommerce' );
			ksort( $forms );

			$product_page_settings = apply_filters(
				'user_registration_woocommerce_product_page_settings',
				array(
					'title'    => __( 'User Registration', 'user-registration-woocommerce' ),
					'sections' => array(
						'user_registration_woocommerce_product_page_settings' => array(
							'title'    => esc_html__( 'User Registration Form', 'user-registration-woocommerce' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'             => __( 'Select Registration Form', 'user-registration-woocommerce' ),
									'desc'              => __( 'Choose registration form to sync with WooCommerce.', 'user-registration-woocommerce' ),
									'id'                => 'user_registration_woocommerce_product_page_settings_form_' . $this->product_id,
									'default'           => 'None',
									'type'              => 'select',
									'class'             => 'ur-enhanced-select urwc-product-tab-panel-select',
									'css'               => 'min-width: 350px;',
									'desc_tip'          => true,
									'custom_attributes' => array( 'product_form_field_key' => $this->urwc_product_page_fields_option_key . '_' . $this->product_id ),
									'options'           => $forms,
								),
							),
						),
					),
				)
			);
			return $product_page_settings;
		}
		/**
		 * Get Global Settings.
		 */
		public function get_settings() {
			return $this->urwc_woocommerece_product_page_settings();
		}

		/**
		 * Save product tab settings.
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post Post.
		 * @param   bool   $update Update.
		 */
		public function urwc_save_product_tab_settings( $post_id, $post, $update ) {
			$screen    = function_exists('get_current_screen')? get_current_screen() : false;
			$screen_id = $screen ? $screen->id : '';

			if ( '' === $screen_id || 'product' !== $screen_id ) {
				return;
			}
			// update the form id.
			$form_id = isset( $_POST[ $this->urwc_product_page_settings_form . '_' . $post_id ] ) ? sanitize_text_field( $_POST[ $this->urwc_product_page_settings_form . '_' . $post_id ] ) : null; // phpcs:ignore
			if ( ! is_null( $form_id ) ) {
				update_post_meta( $post_id, $this->urwc_product_page_settings_form . '_' . $post_id, $form_id );
			}

			// update the form field list.
			$product_page_fields_option_key = $this->urwc_product_page_fields_option_key . '_' . $post_id;
			$form_fields                    = isset( $_POST[ $product_page_fields_option_key ] ) ? $_POST[ $product_page_fields_option_key ] : array();// phpcs:ignore

			$saved_sync_field_data                     = get_option( $product_page_fields_option_key, array() );
				$sync_field_data[ 'form-' . $form_id ] = $form_fields;
			if ( empty( $saved_sync_field_data ) ) {
				update_option( $product_page_fields_option_key, $sync_field_data );
			} else {
				$new_sync_data = array_merge( $saved_sync_field_data, $sync_field_data );
				update_option( $product_page_fields_option_key, $new_sync_data );
			}
		}

		/**
		 * Product page form list.
		 *
		 * @param int    $form_id Form ID.
		 * @param string $product_page_fields_meta_key Meta key for fields for product page.
		 */
		public function urwc_product_display_form_field_lists( $form_id, $product_page_fields_meta_key ) {
			if ( $form_id && $product_page_fields_meta_key ) {
				$woocommerce_field_table_list = new URWC_Field_Table_List();
				$woocommerce_field_table_list->display_table_list( $form_id, $product_page_fields_meta_key );
			}
		}
	}
endif;

return new URWC_Woocommerce_Product_Page();
