<?php

/**
 * All Dashboard handler
 *
 * @package ULTP\Dashboard
 * @since 4.1.11
 */

namespace ULTP;

defined( 'ABSPATH' ) || exit;

use ULTP\Includes\Durbin\DurbinClient;

/**
 * Dashboard class.
 *
 * Handles the admin dashboard for Ultimate Post plugin.
 *
 * @package ULTP\Dashboard
 * @since 4.0.0
 */
class Dashboard {
	/**
	 * Setup class.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'ultp_register_route' ) );
	}

	/**
	 * REST API Action
	 *
	 * @since 4.1.11
	 * @return void
	 */
	public function ultp_register_route() {
		register_rest_route(
			'ultp/v2',
			'/addon_block_action/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'addon_block_action' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'ultp/v2',
			'/save_plugin_settings/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'save_plugin_settings' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'ultp/v2',
			'/get_all_settings/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'get_all_settings' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'ultp/v2',
			'/dashborad/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'get_dashboard_callback' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'ultp/v2',
			'/wizard_site_status/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'wizard_site_status_callback' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'ultp/v2',
			'/send_initial_plugin_data/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'send_initial_plugin_data_callback' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'ultp/v2',
			'/initial_setup_complete/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'initial_setup_complete_callback' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);

		register_rest_route(
			'ultp/v2',
			'/template_action/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'template_page_action' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);
	}

	/**
	 * Save addon/blocks on/off data.
	 *
	 * @since v.3.0.0
	 * @param OBJECT $server The REST server request object.
	 * @return ARRAY. Inserted post URL and status.
	 */
	public function addon_block_action( $server ) {
		$post        = $server->get_params();
		$addon_name  = isset( $post['key'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['key'] ) : '';
		$addon_value = isset( $post['value'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['value'] ) : '';
		if ( $addon_name ) {
			$addon_data                              = ultimate_post()->get_setting();
			$addon_data[ $addon_name ]               = $addon_value;
			$GLOBALS['ultp_settings'][ $addon_name ] = $addon_value;
			update_option( 'ultp_options', $addon_data );
		}
		return array(
			'success' => true,
			'message' => ( $addon_value == 'true' || $addon_value == 'false' ) ? ( $addon_value == 'true' ? __( 'The addon has been enabled.', 'ultimate-post' ) : __( 'The addon has been disabled.', 'ultimate-post' ) ) : ( $addon_value == 'yes' ? __( 'The block has been enabled.', 'ultimate-post' ) : __( 'The block has been disabled.', 'ultimate-post' ) ),
		);
	}

	/**
	 * Save settings of option panel.
	 *
	 * @since v.3.0.0
	 * @param OBJECT $server The REST server request object.
	 * @return OBJECT REST response object.
	 */
	public function save_plugin_settings( $server ) {
		$post = $server->get_params();
		$data = ultimate_post()->ultp_rest_sanitize_params( $post['settings'] );
		if ( count( $data ) > 0 ) {
			foreach ( $data as $key => $val ) {
				ultimate_post()->set_setting( $key, $val );
			}
			do_action( 'ultimate_post_after_setting_save' );
		}
		return rest_ensure_response(
			array(
				'success'     => true,
				'message'     => __( 'You have successfully saved the settings data.', 'ultimate-post' ),
				'wishListArr' => wp_json_encode( $data ),
			)
		);
	}

	/**
	 * Get all settings of option panel.
	 *
	 * @since v.3.0.0
	 * @param OBJECT $server The REST server request object.
	 * @return OBJECT REST response object.
	 */
	public function get_all_settings( $server ) {
		return rest_ensure_response(
			array(
				'success'  => true,
				'settings' => ultimate_post()->get_setting(),
			)
		);
	}

	/**
	 * Saved template & custom font actions.
	 *
	 * @since v.3.0.0
	 * @param OBJECT $server The REST server request object.
	 * @return ARRAY Inserted post URL and status.
	 */
	public function get_dashboard_callback( $server ) {
		$post         = $server->get_params();
		$request_type = isset( $post['type'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['type'] ) : '';
		$pType        = isset( $post['pType'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['pType'] ) : '';
		switch ( $request_type ) {

			case 'saved_templates':
				$post_per_page = 10;
				$data          = array();
				$args          = array(
					'post_type'      => $pType,
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => $post_per_page,
					'paged'          => isset( $post['pages'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['pages'] ) : 1,
				);

				if ( isset( $post['search'] ) ) {
					$args['paged'] = 1;
					$args['s']     = ultimate_post()->ultp_rest_sanitize_params( $post['search'] );
				}

				$the_query = new \WP_Query( $args );
				if ( $the_query->have_posts() ) {
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						$final = array(
							'id'     => get_the_ID(),
							'title'  => get_the_title(),
							'date'   => get_the_modified_date( 'Y/m/d h:i a' ),
							'status' => get_post_status(),
							'edit'   => get_edit_post_link(),
						);

						if ( $pType == 'ultp_custom_font' ) {
							$final    = array_merge(
								$final,
								array(
									'woff'  => false,
									'woff2' => false,
									'ttf'   => false,
									'svg'   => false,
									'eot'   => false,
								)
							);
							$settings = get_post_meta( get_the_ID(), '__font_settings', true );
							foreach ( $settings as $key => $value ) {
								if ( $value['ttf'] ) {
									$final['ttf'] = true;
								}
								if ( $value['svg'] ) {
									$final['svg'] = true;
								}
								if ( $value['eot'] ) {
									$final['eot'] = true;
								}
								if ( $value['woff'] ) {
									$final['woff'] = true;
								}
								if ( $value['woff2'] ) {
									$final['woff2'] = true;
								}
							}
							$final['font_settings'] = $settings;
						}
						$data[] = $final;
					}
				}
				wp_reset_postdata();
				return array(
					'success' => true,
					'data'    => $data,
					'new'     => ( $pType == 'ultp_custom_font' ? admin_url( 'post-new.php?post_type=ultp_custom_font' ) : admin_url( 'post-new.php?post_type=ultp_templates' ) ),
					'found'   => $the_query->found_posts,
					'pages'   => $the_query->max_num_pages,
				);
				break;

			case 'action_draft':
			case 'action_publish':
				if ( isset( $post['ids'] ) && is_array( $post['ids'] ) ) {
					$post_ids = ultimate_post()->ultp_rest_sanitize_params( $post['ids'] );
					foreach ( $post_ids as $id ) {
						wp_update_post(
							array(
								'ID'          => $id,
								'post_status' => str_replace( 'action_', '', $request_type ),
							)
						);
					}
					return array(
						'success' => true,
						'message' => __( 'Status changed for selected items.', 'ultimate-post' ),
					);
				}
				break;

			case 'action_delete':
				if ( isset( $post['ids'] ) && is_array( $post['ids'] ) ) {
					$post_ids = ultimate_post()->ultp_rest_sanitize_params( $post['ids'] );
					foreach ( $post_ids as $id ) {
						wp_delete_post( $id, true );
					}
				}
				return array(
					'success' => true,
					'message' => __( 'The selected item is deleted.', 'ultimate-post' ),
				);

			case 'support_data':
				$user_info = get_userdata( get_current_user_id() );
				$name      = $user_info->first_name . ( $user_info->last_name ? ' ' . $user_info->last_name : '' );
				return array(
					'success' => true,
					'data'    => array(
						'name'  => $name ? $name : $user_info->user_login,
						'email' => $user_info->user_email,
					),
				);

			case 'support_action':
				$api_params    = array(
					'user_name'  => isset( $post['name'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['name'] ) : '',
					'user_email' => isset( $post['email'] ) ? sanitize_email( $post['email'] ) : '',
					'subject'    => isset( $post['subject'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['subject'] ) : '',
					'desc'       => isset( $post['desc'] ) ? sanitize_textarea_field( $post['desc'] ) : '',
				);
				$response      = wp_remote_get(
					'https://wpxpo.com/wp-json/v2/support_mail',
					array(
						'method'  => 'POST',
						'timeout' => 120,
						'body'    => $api_params,
					)
				);
				$response_data = json_decode( $response['body'] );
				$success       = ( isset( $response_data->success ) && $response_data->success ) ? true : false;

				return array(
					'success' => $success,
					'message' => $success ? __( 'New Support Ticket has been Created.', 'ultimate-post' ) : __( 'New Support Ticket is not Created Due to Some Issues.', 'ultimate-post' ),
				);
				break;

			case 'helloBarAction':
				set_transient( 'ultp_helloBar' . ULTP_HELLOBAR, 'hide', 1296000 );
				return array(
					'success' => true,
					'message' => __( 'Notice is removed.', 'ultimate-post' ),
				);
				break;
			case 'generalDiscountAction':
				set_transient( 'ultp_generalDiscount', 'hide', 60 * DAY_IN_SECONDS );
				return array(
					'success' => true,
					'message' => __( 'Notice is removed.', 'ultimate-post' ),
				);
				break;

			default:
				// code...
				break;
		}
	}

	/**
	 * Wizard site status callback.
	 *
	 * @since v.3.0.0
	 * @return OBJECT. REST response object.
	 */
	public static function wizard_site_status_callback() {
		if ( ! ( isset( $_POST['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wpnonce'] ) ), 'ultp-nonce' ) ) ) {
			die();
		}
		if ( isset( $_POST['siteType'] ) ) {
			$site_type = ultimate_post()->ultp_rest_sanitize_params( $_POST['siteType'] );
			update_option( '__ultp_site_type', $site_type );
		}
		DurbinClient::send( DurbinClient::WIZARD_ACTION );

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Send plugin data when initial setup.
	 *
	 * @since v.2.8.1
	 * @param OBJECT $server The REST server request object.
	 */
	public function send_initial_plugin_data_callback( $server ) {
		$post = $server->get_params();
		if ( ! ( isset( $post['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $post['wpnonce'] ) ), 'ultp-nonce' ) ) ) {
			die();
		}

		$site = isset( $post['site'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['site'] ) : '';

		DurbinClient::send( DurbinClient::WIZARD_ACTION );
	}

	/**
	 * Initial plugin setup complete callback.
	 *
	 * @since v.2.8.1
	 * @param OBJECT $server The REST server request object.
	 * @return OBJECT REST response object.
	 */
	public static function initial_setup_complete_callback( $server ) {
		$post = $server->get_params();
		if ( ! ( isset( $post['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $post['wpnonce'] ) ), 'ultp-nonce' ) ) ) {
			die();
		}
		ultimate_post()->set_setting( 'init_setup', 'yes' );
		return rest_ensure_response(
			array(
				'success'  => true,
				'redirect' => admin_url( 'admin.php?page=ultp-settings#home' ),
			)
		);
	}

	/**
	 * Delete template page and handle builder, saved templates, custom font actions.
	 *
	 * @since v.2.6.6 Shifted from RequestAPI since v4.0.3
	 * @param OBJECT $server The REST server request object.
	 * @return ARRAY Success message.
	 */
	public function template_page_action( $server ) {
		$post     = $server->get_params();
		$message  = '';
		$s_Type   = isset( $post['type'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['type'] ) : '';
		$s_id     = isset( $post['id'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['id'] ) : '';
		$s_status = isset( $post['status'] ) ? ultimate_post()->ultp_rest_sanitize_params( $post['status'] ) : '';

		if ( $s_Type && $s_id ) {
			if ( $s_Type == 'delete' ) {
				if ( isset( $post['section'] ) && $post['section'] == 'builder' ) { // phpcs:ignore WordPress.Security
					$conditions   = get_option( 'ultp_builder_conditions', array() );
					$builder_type = get_post_meta( $s_id, '__ultp_builder_type', true );
					if ( isset( $conditions[ $builder_type ][ $s_id ] ) ) {
						unset( $conditions[ $builder_type ][ $s_id ] );
						update_option( 'ultp_builder_conditions', $conditions );
					}
				}
				wp_delete_post( $s_id, true );
				$message = __( 'Template has been deleted.', 'ultimate-post' );
			} elseif ( $s_Type == 'duplicate' ) {
				$fromBuilder     = ( isset( $post['section'] ) && $post['section'] == 'builder' ) ? true : false; // phpcs:ignore WordPress.Security
				$post_id         = $s_id;
				$r_post          = get_post( $post_id );
				$current_user    = wp_get_current_user();
				$new_post_author = $current_user->ID;
				if ( isset( $r_post ) && $r_post != null ) {
					$args = array(
						'post_author'  => $new_post_author,
						'post_content' => str_replace( array( 'u0022', 'u002d' ), array( '\u0022', '\u002d' ), $r_post->post_content ),
						'post_excerpt' => $r_post->post_excerpt,
						'post_name'    => $r_post->post_name,
						'post_status'  => $fromBuilder ? 'draft' : $r_post->post_status,
						'post_title'   => $r_post->post_title,
						'post_type'    => $r_post->post_type,
					);
				}
				$new_post_id = wp_insert_post( $args );

				$css = get_post_meta( $post_id, '_ultp_css', true );
				update_post_meta( $new_post_id, '_ultp_css', $css );
				update_post_meta( $new_post_id, '_ultp_active', 'yes' );

				if ( $fromBuilder ) {
					$type = get_post_meta( $post_id, '__ultp_builder_type', true );
					update_post_meta( $new_post_id, '__ultp_builder_type', $type );

					$width = get_post_meta( $post_id, '__container_width', true );
					update_post_meta( $new_post_id, '__container_width', $width );

					$sidebar = get_post_meta( $post_id, '__builder_sidebar', true );
					update_post_meta( $new_post_id, '__builder_sidebar', $sidebar );

					$widget_area = get_post_meta( $post_id, '__builder_widget_area', true );
					update_post_meta( $new_post_id, '__builder_widget_area', $widget_area );

					$conditions = get_option( 'ultp_builder_conditions', array() );
					if ( $conditions && $type ) {
						if ( isset( $conditions[ $type ][ $post_id ] ) ) {
							$conditions[ $type ][ $new_post_id ] = $conditions[ $type ][ $post_id ];
							update_option( 'ultp_builder_conditions', $conditions );
						}
					}
				}
				$message = __( 'Template has been duplicated.', 'ultimate-post' );
			} elseif ( $s_Type == 'status' ) {
				if ( $s_status ) {
					wp_update_post(
						array(
							'ID'          => $s_id,
							'post_status' => $s_status,
						)
					);
				}
				$message = __( 'Status has been changed.', 'ultimate-post' );
			}
		}

		return array(
			'success' => true,
			'message' => $message,
		);
	}
}
