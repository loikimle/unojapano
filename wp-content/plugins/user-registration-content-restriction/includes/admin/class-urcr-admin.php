<?php
/**
 * UserRegistrationContentRestriction Admin.
 *
 * @class    URCR_Admin
 * @version  2.0.0
 * @package  UserRegistrationContentRestriction/Admin
 * @category Admin
 * @author   WPEverest
 */

defined( 'ABSPATH' ) || exit;

/**
 * URCR_Admin Class
 */
class URCR_Admin {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		urcr_check_plugin_compatibility();

		if ( urcr_is_compatible() !== 'YES' ) {
			return;
		}

		/**
		 * Remove unnecessary notices.
		 */
		add_action( 'in_admin_header', array( __CLASS__, 'hide_unrelated_notices' ) );

		/**
		 * Register admin menus.
		 */
		add_action( 'admin_menu', array( $this, 'add_urcr_menus' ), 70 );

		/**
		 * Register ajax handlers.
		 */
		add_action( 'wp_ajax_urcr_create_access_rule', array( $this, 'ajax_create_content_access_rule_handler' ) );
		add_action( 'wp_ajax_urcr_save_access_rule', array( $this, 'ajax_save_access_rule_handler' ) );
		add_action( 'wp_ajax_urcr_save_access_rule_as_draft', array( $this, 'ajax_save_access_rule_as_draft_handler' ) );

		/**
		 * Register a settings in the core settings list.
		 */
		add_filter( 'user_registration_get_settings_pages', array( $this, 'add_content_restriction_setting' ), 10, 1 );
	}

	/**
	 * Remove Notices.
	 */
	public static function hide_unrelated_notices() {
		global $wp_filter;

		// Return on other than access rule creator page.
		if ( empty( $_REQUEST['page'] ) || 'user-registration-content-restriction' !== $_REQUEST['page'] ) {
			return;
		}

		foreach ( array( 'user_admin_notices', 'admin_notices', 'all_admin_notices' ) as $wp_notice ) {
			if ( ! empty( $wp_filter[ $wp_notice ]->callbacks ) && is_array( $wp_filter[ $wp_notice ]->callbacks ) ) {
				foreach ( $wp_filter[ $wp_notice ]->callbacks as $priority => $hooks ) {
					foreach ( $hooks as $name => $arr ) {
						// Remove all notices except user registration plugins notices.
						if ( ! strstr( $name, 'user_registration_' ) ) {
							unset( $wp_filter[ $wp_notice ]->callbacks[ $priority ][ $name ] );
						}
					}
				}
			}
		}
	}

	/**
	 * Add admin menus for Content Restriction settings.
	 *
	 * @since 2.0.0
	 */
	public function add_urcr_menus() {
		$rules_page = add_submenu_page(
			'user-registration',
			__( 'Content Restriction - Content Access Rules', 'user-registration-content-restriction' ),
			__( 'Access Rules', 'user-registration-content-restriction' ),
			'edit_posts',
			'user-registration-content-restriction',
			array(
				$this,
				'render_content_restriction_page',
			)
		);

		add_action( 'load-' . $rules_page, array( $this, 'content_restriction_initializations' ) );
	}

	/**
	 * Do initializations before loading content restriction pages.
	 *
	 * @since 2.0.0
	 */
	public function content_restriction_initializations() {
		if ( isset( $_GET['page'] ) && 'user-registration-content-restriction' === $_GET['page'] ) {

			$action_page = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

			switch ( $action_page ) {
				case 'add_new_urcr_content_access_rule':
					break;

				default:
					global $content_access_rules_table_list;

					require_once __DIR__ . '/class-urcr-admin-content-access-rules-table-list.php';
					$content_access_rules_table_list = new URCR_Admin_Content_Access_Rules_Table_List();
					$content_access_rules_table_list->process_actions();
					break;
			}
		}
	}

	/**
	 * Render content restriction page.
	 *
	 * @since 2.0.0
	 */
	public function render_content_restriction_page() {
		$action_page = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

		switch ( $action_page ) {
			case 'add_new_urcr_content_access_rule':
				$this->render_content_access_rules_creator();
				break;

			default:
				$this->render_content_access_rules_viewer();
				break;
		}
	}

	/**
	 * Render settings page to manage Content Access Rules.
	 *
	 * @since 2.0.0
	 */
	public function render_content_access_rules_creator() {
		include __DIR__ . '/content-access-rules-creator.php';
	}

	/**
	 * Render settings page with Content Access Rules.
	 *
	 * @since 2.0.0
	 */
	public function render_content_access_rules_viewer() {
		global $content_access_rules_table_list;

		if ( ! $content_access_rules_table_list ) {
			return;
		}

		$content_access_rules_table_list->display_page();
	}

	/**
	 * Include Content Restriction settings in the settings list.
	 *
	 * @param array $settings List of setting pages.
	 */
	function add_content_restriction_setting( $settings ) {
		if ( class_exists( 'UR_Settings_Page' ) ) {
			$settings[] = include 'settings/class-urcr-settings-file.php';
		}
	}
}

return new URCR_Admin();
