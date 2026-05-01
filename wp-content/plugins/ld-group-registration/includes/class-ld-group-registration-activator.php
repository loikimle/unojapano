<?php
/**
 * Fired during plugin activation
 *
 * @link       https://wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/includes
 */

namespace LdGroupRegistration\Includes;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
class Ld_Group_Registration_Activator {

	/**
	 * Activation Sequence
	 *
	 * Perform necessary actions such as creating Group Dashboard page, and saving meta for the same.
	 *
	 * @since    4.0
	 */
	public function activate() {
		global $wdm_grp_plugin_data;

		$ldgr_group_users_page = get_option( 'wdm_group_users_page' );

		if ( '' == $ldgr_group_users_page ) {
			$course_create_page = array(
				'post_title'   => __( 'Groups Dashboard', WDM_LDGR_TXT_DOMAIN ),
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '[wdm_group_users]',
				'post_author'  => get_current_user_id(),
			);

			$group_users_page_id = wp_insert_post( $course_create_page );
			update_option( 'wdm_group_users_page', $group_users_page_id );
		}
	}

	/**
	 * Admin Activation Sequence
	 *
	 * Check for plugin dependencies on plugin activation.
	 *
	 * @since    4.0
	 */
	public function admin_activate() {
		if ( ! class_exists( 'SFWD_LMS' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			unset( $_GET['activate'] );
			add_action( 'admin_notices', array( $this, 'handle_admin_notices' ) );
		}
		if ( ! ( class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' ) ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			unset( $_GET['activate'] );
			add_action( 'admin_notices', array( $this, 'handle_admin_notices' ) );
		}

		if ( ! ( class_exists( 'learndash_woocommerce' ) || class_exists( 'LearnDash_EDD' ) ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			unset( $_GET['activate'] );
			add_action( 'admin_notices', array( $this, 'handle_admin_notices' ) );
		}
	}

	/**
	 * Handle admin notices
	 */
	public function handle_admin_notices() {
		if ( ! class_exists( 'SFWD_LMS' ) ) {
			?>
		<div class='error'><p>
				<?php
				echo esc_html( __( "LearnDash LMS plugin is not active. In order to make the 'LearnDash Group Registration' plugin work, you need to install and activate LearnDash LMS first.", WDM_LDGR_TXT_DOMAIN ) );
				?>
			</p></div>

			<?php

		}
		if ( ! ( class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' ) ) ) {
			?>
		<div class='error'><p>
				<?php
				echo esc_html( __( "WooCommerce plugin is not active. In order to make the 'LearnDash Group Registration' plugin work, you need to install and activate WooCommerce first.", WDM_LDGR_TXT_DOMAIN ) );
				?>
			</p></div>

			<?php

		}

		if ( class_exists( 'WooCommerce' ) && ! class_exists( 'learndash_woocommerce' ) ) {
			?>
		<div class='error'><p>
				<?php
				echo esc_html( __( "LearnDash WooCommerce Integration plugin is not active. In order to make the 'LearnDash Group Registration' plugin work, you need to install and activate LearnDash WooCommerce Integration  first.", WDM_LDGR_TXT_DOMAIN ) );
				?>
			</p></div>

			<?php

		} elseif ( class_exists( 'Easy_Digital_Downloads' ) && ! class_exists( 'LearnDash_EDD' ) ) {
			?>
		<div class='error'><p>
				<?php
				echo esc_html( __( "LearnDash EDD Integration plugin is not active. In order to make the 'LearnDash Group Registration' plugin work, you need to install and activate LearnDash EDD Integration first.", WDM_LDGR_TXT_DOMAIN ) );
				?>
			</p></div>

			<?php

		} elseif ( ! ( class_exists( 'learndash_woocommerce' ) || class_exists( 'LearnDash_EDD' ) ) ) {
			?>
		<div class='error'><p>
				<?php
				echo esc_html( __( "LearnDash WooCommerce Integration or LearnDash EDD Integration plugin is not active. In order to make the 'LearnDash Group Registration' plugin work, you need to install and activate LearnDash WooCommerce Integration or LearnDash EDD Integration first.", WDM_LDGR_TXT_DOMAIN ) );
				?>
			</p></div>

			<?php

		}
	}

	/**
	 * Handle upgrade notices if any
	 *
	 * @param array $data
	 * @param array $response
	 *
	 * @since 4.1.0
	 */
	public function handle_update_notices( $data, $response ) {
		if( isset( $data['upgrade_notice'] ) ) {
			printf(
				'<div class="update-message">%s</div>',
				wpautop( $data['upgrade_notice'] )
			);
		}
	}
}
