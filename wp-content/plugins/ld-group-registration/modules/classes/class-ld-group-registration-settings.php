<?php
/**
 * Settings Module
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/classes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace LdGroupRegistration\Modules\Classes;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Ld_Group_Registration_Settings' ) ) {
	/**
	 * LD Group Registration Settings
	 */
	class Ld_Group_Registration_Settings {
		/**
		 * Add settings menu
		 */
		public function add_settings_menu() {
			add_submenu_page(
				'learndash-lms',
				__( 'Group Registration Settings', WDM_LDGR_TXT_DOMAIN ),
				__( 'Group Registration Settings', WDM_LDGR_TXT_DOMAIN ),
				'manage_options',
				'wdm-ld-gr-setting',
				array( $this, 'display_settings_menu' )
			);
		}

		/**
		 * Display settings menu for group registration
		 */
		public function display_settings_menu() {
			wp_enqueue_script(
				'wdm-ldgr-setting-js',
				plugins_url(
					'js/wdm-setting.js',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);

			$current_tab = 'general';
			if ( isset( $_GET['tab'] ) ) {
				$current_tab = $_GET['tab'];
			}

			$tab_headers = array(
				'general'        => __( 'General Settings', WDM_LDGR_TXT_DOMAIN ),
				'email'          => __( 'Email Settings', WDM_LDGR_TXT_DOMAIN )
			);

			$tab_headers = apply_filters( 'ldgr_setting_tab_headers', $tab_headers );
			?>
			<h1 class="nav-tab-wrapper">
				<?php foreach ( $tab_headers as $key => $value ) : ?>
					<a class="nav-tab <?php echo( ( $current_tab == $key ) ? 'nav-tab-active' : '' ); ?> " href="?page=wdm-ld-gr-setting&tab=<?php echo $key; ?>">
						<?php echo $value; ?>
					</a>
				<?php endforeach; ?>
			</h1>
			<?php

			switch ( $current_tab ) {
				case 'general':
					$this->display_global_settings();
					break;
				case 'email':
					$this->display_email_settings();
					break;
				case 'wdm-extensions':
					$this->display_promotions_page();
					break;
			}

			do_action( 'ldgr_settings_tab_content_end', $current_tab );
		}

		/**
		 * Display email settings
		 */
		public function display_email_settings() {
			wp_enqueue_style(
				'wdm-admin_css',
				plugins_url(
					'css/wdm-admin.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);
			if ( isset( $_POST['sbmt_wdm_gr_email_setting'] ) && wp_verify_nonce( $_POST['sbmt_wdm_gr_email_setting'], 'wdm_gr_email_setting' ) ) {

				$admin_email = $_POST['wdm-gr-admin-email'];
				update_option( 'wdm-gr-admin-email', trim( $admin_email ) );

				$gl_rmvl_sub = $_POST['wdm-gr-gl-rmvl-sub'];
				update_option( 'wdm-gr-gl-rmvl-sub', trim( $gl_rmvl_sub ) );
				$gl_rmvl_body = $_POST['wdm-gr-gl-rmvl-body'];
				update_option( 'wdm-gr-gl-rmvl-body', trim( $gl_rmvl_body ) );
				$gl_acpt_sub = $_POST['wdm-gr-gl-acpt-sub'];
				update_option( 'wdm-gr-gl-acpt-sub', trim( $gl_acpt_sub ) );
				$gl_acpt_body = $_POST['wdm-gr-gl-acpt-body'];
				update_option( 'wdm-gr-gl-acpt-body', trim( $gl_acpt_body ) );

				$a_rq_rmvl_sub = $_POST['wdm-a-rq-rmvl-sub'];
				update_option( 'wdm-a-rq-rmvl-sub', trim( $a_rq_rmvl_sub ) );
				$a_rq_rmvl_body = $_POST['wdm-a-rq-rmvl-body'];
				update_option( 'wdm-a-rq-rmvl-body', trim( $a_rq_rmvl_body ) );

				$u_add_gr_sub = $_POST['wdm-u-add-gr-sub'];
				update_option( 'wdm-u-add-gr-sub', trim( $u_add_gr_sub ) );
				$u_add_gr_body = $_POST['wdm-u-add-gr-body'];
				update_option( 'wdm-u-add-gr-body', trim( $u_add_gr_body ) );
				$u_ac_crt_sub = $_POST['wdm-u-ac-crt-sub'];
				update_option( 'wdm-u-ac-crt-sub', trim( $u_ac_crt_sub ) );
				$u_ac_crt_body = $_POST['wdm-u-ac-crt-body'];
				update_option( 'wdm-u-ac-crt-body', trim( $u_ac_crt_body ) );

				$a_u_ac_crt_sub = $_POST['wdm-a-u-ac-crt-sub'];
				update_option( 'wdm-a-u-ac-crt-sub', trim( $a_u_ac_crt_sub ) );
				$a_u_ac_crt_body = $_POST['wdm-a-u-ac-crt-body'];
				update_option( 'wdm-a-u-ac-crt-body', trim( $a_u_ac_crt_body ) );

				// Save ReInvite Email setting.
				$wdm_reinvite_sub  = stripslashes( $_POST['wdm-gr-reinvite-sub'] );
				$wdm_reinvite_body = $_POST['wdm-gr-reinvite-body'];

				update_option( 'wdm-reinvite-sub', trim( $wdm_reinvite_sub ) );
				update_option( 'wdm-reinvite-body', trim( $wdm_reinvite_body ) );

				// Save email enable/disable settings
				$wdm_gr_gl_rmvl_enable = 'on';
				if ( ! array_key_exists( 'wdm-gr-gl-rmvl-enable', $_POST ) ) {
					$wdm_gr_gl_rmvl_enable = 'off';
				}

				update_option( 'wdm_gr_gl_rmvl_enable', $wdm_gr_gl_rmvl_enable );

				$wdm_gr_gl_acpt_enable = 'on';
				if ( ! array_key_exists( 'wdm-gr-gl-acpt-enable', $_POST ) ) {
					$wdm_gr_gl_acpt_enable = 'off';
				}

				update_option( 'wdm_gr_gl_acpt_enable', $wdm_gr_gl_acpt_enable );

				$wdm_a_rq_rmvl_enable = 'on';
				if ( ! array_key_exists( 'wdm-a-rq-rmvl-enable', $_POST ) ) {
					$wdm_a_rq_rmvl_enable = 'off';
				}

				update_option( 'wdm_a_rq_rmvl_enable', $wdm_a_rq_rmvl_enable );

				$wdm_u_add_gr_enable = 'on';
				if ( ! array_key_exists( 'wdm-u-add-gr-enable', $_POST ) ) {
					$wdm_u_add_gr_enable = 'off';
				}

				update_option( 'wdm_u_add_gr_enable', $wdm_u_add_gr_enable );

				$wdm_u_ac_crt_enable = 'on';
				if ( ! array_key_exists( 'wdm-u-ac-crt-enable', $_POST ) ) {
					$wdm_u_ac_crt_enable = 'off';
				}

				update_option( 'wdm_u_ac_crt_enable', $wdm_u_ac_crt_enable );

				$wdm_a_u_ac_crt_enable = 'on';
				if ( ! array_key_exists( 'wdm-a-u-ac-crt-enable', $_POST ) ) {
					$wdm_a_u_ac_crt_enable = 'off';
				}

				update_option( 'wdm_a_u_ac_crt_enable', $wdm_a_u_ac_crt_enable );

				$wdm_gr_reinvite_enable = 'on';
				if ( ! array_key_exists( 'wdm-gr-reinvite-enable', $_POST ) ) {
					$wdm_gr_reinvite_enable = 'off';
				}

				update_option( 'wdm_gr_reinvite_enable', $wdm_gr_reinvite_enable );

				?>
				<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
					<p><strong><?php esc_html_e( 'Settings saved.', WDM_LDGR_TXT_DOMAIN ); ?></strong></p>
				</div>
				<div class="notice notice-info is-dismissible">
					<p>
						<strong>
							<?php esc_html_e( 'Note: Any empty fields will be replaced by defaults', WDM_LDGR_TXT_DOMAIN ); ?>
						</strong>
					</p>
				</div>
				<?php
			}

			$admin_email = get_option( 'wdm-gr-admin-email' );
			if ( empty( $admin_email ) ) {
				$admin_email = get_bloginfo( 'admin_email' );
				update_option( 'wdm-gr-admin-email', trim( $admin_email ) );
			}

			$gl_rmvl_sub  = get_option( 'wdm-gr-gl-rmvl-sub' );
			$gl_rmvl_sub  = empty( $gl_rmvl_sub ) ? WDM_GR_GL_RMVL_SUB : $gl_rmvl_sub;
			$gl_rmvl_body = get_option( 'wdm-gr-gl-rmvl-body' );
			$gl_rmvl_body = empty( $gl_rmvl_body ) ? WDM_GR_GL_RMVL_BODY : $gl_rmvl_body;

			$gl_acpt_sub  = get_option( 'wdm-gr-gl-acpt-sub' );
			$gl_acpt_sub  = empty( $gl_acpt_sub ) ? WDM_GR_GL_ACPT_SUB : $gl_acpt_sub;
			$gl_acpt_body = get_option( 'wdm-gr-gl-acpt-body' );
			$gl_acpt_body = empty( $gl_acpt_body ) ? WDM_GR_GL_ACPT_BODY : $gl_acpt_body;

			$a_rq_rmvl_sub  = get_option( 'wdm-a-rq-rmvl-sub' );
			$a_rq_rmvl_sub  = empty( $a_rq_rmvl_sub ) ? WDM_A_RQ_RMVL_SUB : $a_rq_rmvl_sub;
			$a_rq_rmvl_body = get_option( 'wdm-a-rq-rmvl-body' );
			$a_rq_rmvl_body = empty( $a_rq_rmvl_body ) ? WDM_A_RQ_RMVL_BODY : $a_rq_rmvl_body;

			$u_add_gr_sub  = get_option( 'wdm-u-add-gr-sub' );
			$u_add_gr_sub  = empty( $u_add_gr_sub ) ? WDM_U_ADD_GR_SUB : $u_add_gr_sub;
			$u_add_gr_body = get_option( 'wdm-u-add-gr-body' );
			$u_add_gr_body = empty( $u_add_gr_body ) ? WDM_U_ADD_GR_BODY : $u_add_gr_body;

			$u_ac_crt_sub  = get_option( 'wdm-u-ac-crt-sub' );
			$u_ac_crt_sub  = empty( $u_ac_crt_sub ) ? WDM_U_AC_CRT_SUB : $u_ac_crt_sub;
			$u_ac_crt_body = get_option( 'wdm-u-ac-crt-body' );
			$u_ac_crt_body = empty( $u_ac_crt_body ) ? WDM_U_AC_CRT_BODY : $u_ac_crt_body;

			$a_u_ac_crt_sub  = get_option( 'wdm-a-u-ac-crt-sub' );
			$a_u_ac_crt_sub  = empty( $a_u_ac_crt_sub ) ? WDM_A_U_AC_CRT_SUB : $a_u_ac_crt_sub;
			$a_u_ac_crt_body = get_option( 'wdm-a-u-ac-crt-body' );
			$a_u_ac_crt_body = empty( $a_u_ac_crt_body ) ? WDM_A_U_AC_CRT_BODY : $a_u_ac_crt_body;

			// ReInvite Email Setting.
			$wdm_reinvite_sub  = get_option( 'wdm-reinvite-sub' );
			$wdm_reinvite_sub  = empty( $wdm_reinvite_sub ) ? WDM_REINVITE_SUB : $wdm_reinvite_sub;
			$wdm_reinvite_body = get_option( 'wdm-reinvite-body' );
			$wdm_reinvite_body = empty( $wdm_reinvite_body ) ? WDM_REINVITE_BODY : $wdm_reinvite_body;

			// Fetch email enable/disable settings
			$wdm_gr_gl_rmvl_enable = get_option( 'wdm_gr_gl_rmvl_enable' );
			$wdm_gr_gl_acpt_enable = get_option( 'wdm_gr_gl_acpt_enable' );
			$wdm_a_rq_rmvl_enable = get_option( 'wdm_a_rq_rmvl_enable' );
			$wdm_u_add_gr_enable = get_option( 'wdm_u_add_gr_enable' );
			$wdm_u_ac_crt_enable = get_option( 'wdm_u_ac_crt_enable' );
			$wdm_a_u_ac_crt_enable = get_option( 'wdm_a_u_ac_crt_enable' );
			$wdm_gr_reinvite_enable = get_option( 'wdm_gr_reinvite_enable' );

			wp_enqueue_style(
				'wdm-email_css',
				plugins_url(
					'css/wdm-email.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);
			wp_enqueue_script(
				'wdm-email_js',
				plugins_url(
					'js/wdm-email.js',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);

			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/wdm-email-setting.php';
		}

		/**
		 * Display promotions page
		 */
		public function display_promotions_page() {
			if ( false === ( $extensions = get_transient( '_ldgr_extensions_data' ) ) ) {
				$extensions_json = wp_remote_get(
					'https://wisdmlabs.com/products-thumbs/ld_extensions.json',
					array(
						'user-agent' => 'Group Registration Extensions Page',
					)
				);

				if ( ! is_wp_error( $extensions_json ) ) {
					$extensions = json_decode( wp_remote_retrieve_body( $extensions_json ) );

					if ( $extensions ) {
						set_transient( '_ldgr_extensions_data', $extensions, 72 * HOUR_IN_SECONDS );
					}
				}
			}
			wp_enqueue_style(
				'wdm-extension_css',
				plugins_url(
					'css/wdm-gr-extension.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);
			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/other-extensions.php';
			unset( $extensions );
		}

		/**
		 * Display global settings
		 */
		public function display_global_settings() {
			wp_enqueue_style(
				'wdm-admin_css',
				plugins_url(
					'css/wdm-admin.css',
					dirname( __FILE__ )
				),
				array(),
				LD_GROUP_REGISTRATION_VERSION
			);

			$ldgr_admin_approval        = get_option( 'ldgr_admin_approval' );
			$ldgr_group_limit           = get_option( 'ldgr_group_limit' );
			$ldgr_reinvite_user         = get_option( 'ldgr_reinvite_user' );
			$ldgr_group_courses         = get_option( 'ldgr_group_courses' );
			$ldgr_user_redirects        = get_option( 'ldgr_user_redirects' );
			$ldgr_redirect_group_leader = get_option( 'ldgr_redirect_group_leader' );
			$ldgr_redirect_group_user   = get_option( 'ldgr_redirect_group_user' );
			$ldgr_unlimited_members_label = get_option( 'ldgr_unlimited_members_label' );
			// $ldgr_logo_enabled			= get_option( 'ldgr_logo_enabled' );
			// $ldgr_logo_url				= get_option( 'ldgr_logo_url' );
			// $ldgr_paid_course_for_leader = get_option("ldgr_global_gl_paid_course");

			if ( isset( $_POST['sbmt_ldgr_setting'] ) && wp_verify_nonce( $_POST['sbmt_ldgr_setting'], 'ldgr_setting' )
			) {
				$ldgr_admin_approval = '';
				if ( isset( $_POST['ldgr_admin_approval'] ) ) {
					$ldgr_admin_approval = $_POST['ldgr_admin_approval'];
				}

				update_option( 'ldgr_admin_approval', $ldgr_admin_approval );
				// $ldgr_admin_approval = $_POST['ldgr_admin_approval'];

				$ldgr_group_limit = '';
				if ( isset( $_POST['ldgr_group_limit'] ) ) {
					$ldgr_group_limit = $_POST['ldgr_group_limit'];
				}

				update_option( 'ldgr_group_limit', $ldgr_group_limit );
				// $ldgr_group_limit = $_POST['ldgr_group_limit'];

				$ldgr_reinvite_user = '';
				if ( isset( $_POST['ldgr_reinvite_user'] ) ) {
					$ldgr_reinvite_user = $_POST['ldgr_reinvite_user'];
				}

				update_option( 'ldgr_reinvite_user', $ldgr_reinvite_user );

				$ldgr_group_courses = '';
				if ( isset( $_POST['ldgr_group_courses'] ) ) {
					$ldgr_group_courses = $_POST['ldgr_group_courses'];
				}

				update_option( 'ldgr_group_courses', $ldgr_group_courses );

				// Check if redirects enabled
				$ldgr_user_redirects = '';
				if ( isset( $_POST['ldgr_user_redirects'] ) ) {
					$ldgr_user_redirects = $_POST['ldgr_user_redirects'];
				}
				update_option( 'ldgr_user_redirects', $ldgr_user_redirects );

				if ( 'on' == $ldgr_user_redirects ) {
					// Save redirect settings
					$group_leader_redirect = intval( $_POST['ldgr_redirect_group_leader'] );
					$group_user_redirect   = intval( $_POST['ldgr_redirect_group_user'] );

					if ( ! empty( $group_leader_redirect ) ) {
						update_option( 'ldgr_redirect_group_leader', $group_leader_redirect );
					}

					if ( ! empty( $group_user_redirect ) ) {
						update_option( 'ldgr_redirect_group_user', $group_user_redirect );
					}
				}

				// Check if group logo enabled
				// $ldgr_logo_enabled = '';
				// if ( isset( $_POST['ldgr_logo_enabled'] ) ) {
				// 	$ldgr_logo_enabled = $_POST['ldgr_logo_enabled'];
				// }
				// update_option( 'ldgr_logo_enabled', $ldgr_logo_enabled );

				// if ( 'on' == $ldgr_logo_enabled ) {
				// 	$ldgr_logo_url = '';
				// 	if ( isset( $_POST['ldgr_logo_url'] ) ) {
				// 		$ldgr_logo_url = $_POST['ldgr_logo_url'];
				// 	}
				// 	update_option( 'ldgr_logo_url', $ldgr_logo_url );
				// }

				// Check if any label set for unlimited member options.
				$ldgr_unlimited_members_label = '';
				if ( isset( $_POST['ldgr_unlimited_members_label'] ) ) {
					$ldgr_unlimited_members_label = $_POST['ldgr_unlimited_members_label'];
				}
				update_option( 'ldgr_unlimited_members_label', $ldgr_unlimited_members_label );

			}

			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/wdm-global-setting.php';
		}

		/**
		 * Add settings page link on dashboard
		 *
		 * @param array $links  Array of all the links.
		 */
		public function add_settings_page_link( $links ) {
			$settings_link = array(
				'<a href="' . admin_url( 'admin.php?page=wdm-ld-gr-setting' ) . '">' . __( 'Settings', WDM_LDGR_TXT_DOMAIN ) . '</a>',
			);
			return array_merge( $links, $settings_link );
		}

		/**
		 * Handle redirects for woocommerce logins
		 *
		 * @param string $redirect_to   URL to be redirected to.
		 * @param obj    $user             User details object.
		 *
		 * @return string               Updated redirect URL.
		 */
		public function handle_woo_login_redirect( $redirect_to, $user ) {
			return $this->handle_wp_login_redirect( $redirect_to, '', $user );
		}

		/**
		 * Handle all login redirects
		 *
		 * @param string $redirect_to   URL to redirect to.
		 * @param string $requested     Requested URL.
		 * @param obj    $user             User object.
		 *
		 * @return string               Updated URL to be redirected to.
		 */
		public function handle_wp_login_redirect( $redirect_to, $requested, $user ) {
			// If unsuccessful login, return.
			if ( is_wp_error( $user ) || empty( $user ) ) {
				return $redirect_to;
			}

			// Check if user redirects enabled.
			$user_redirects = get_option( 'ldgr_user_redirects' );

			// If disabled then return.
			if ( 'on' != $user_redirects ) {
				return $redirect_to;
			}

			// Get settings for group leader redirection.
			$redirect_group_leader = get_option( 'ldgr_redirect_group_leader' );

			if ( ! empty( $redirect_group_leader ) ) {
				// Check if groupleader.
				if ( learndash_is_group_leader_user( $user->ID ) || user_can($user->ID, 'manage_options')) {
					return get_page_link( $redirect_group_leader );
				}
			}

			// Get settings for group user redirection.
			$redirect_group_user = get_option( 'ldgr_redirect_group_user' );

			if ( ! empty( $redirect_group_user ) ) {
				// Check if group user (i.e. Subscriber) and only one role.
				if ( in_array( 'subscriber', $user->roles ) && 1 == count( $user->roles ) ) {
					return get_page_link( $redirect_group_user );
				}
			}

			return $redirect_to;
		}

		/**
		 * Add Feedback Setting Tab
		 *
		 * @param array $setting_tabs
		 *
		 * @return array
		 * @since 4.1.0
		 */
		public function add_feedback_and_other_setting_tab_header( $setting_tabs ) {
			if ( ! array_key_exists( 'feedback', $setting_tabs ) ) {
				$setting_tabs['feedback']	=	__( 'Feedback', WDM_LDGR_TXT_DOMAIN );
			}

			if ( ! array_key_exists( 'wdm-extensions', $setting_tabs ) ) {
				$setting_tabs['wdm-extensions']	=	__( 'Other Extensions', WDM_LDGR_TXT_DOMAIN );
			}

			return $setting_tabs;
		}

		/**
		 * Display the feedback tab
		 *
		 * @param string $current_tab   Currently active tab.
		 */
		public function display_feedback_tab_contents( $current_tab ) {
			if ( 'feedback' === $current_tab ) {
				$template_path = 'templates/ldgr-feedback-form.template.php';
				$template_path = apply_filters( 'ldgr_feedback_template_path', $template_path, $current_tab );
				include_once plugin_dir_path( dirname( __FILE__ ) ) . $template_path;
			}
		}
	}
}
