<?php
/**
 * UserRegistration Pro Settings class.
 *
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'User_Registration_Pro_Settings' ) ) :

	/**
	 * User_Registration_Pro_Settings Setting
	 */
	class User_Registration_Pro_Settings extends UR_Settings_Page {

		/**
		 * Redirect class
		 */
		 public $redirect_type = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'user-registration-pro';
			$this->label = esc_html__( 'Extras', 'user-registration' );

			add_filter( 'user_registration_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'user_registration_sections_' . $this->id, array( $this, 'output_sections' ) );
			add_action( 'user_registration_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'user_registration_settings_save_' . $this->id, array( $this, 'save' ) );
			add_filter( 'wp_editor_settings', array( $this, 'user_registration_pro_editor_settings' ) );
			add_filter( 'show_user_registration_setting_message', array( $this, 'filter_notice' ) );
			add_filter( 'user-registration-setting-save-label', array( $this, 'filter_label' ) );
			add_filter( 'user_registration_admin_field_role_redirect_settings', array( $this, 'ur_role_based_redirection_mapping_table' ), 10, 2 );
			$this->redirect_type['User_Registration_Settings_Redirection_After_Login'] = include 'redirect/class-ur-settings-redirection-after-login.php';
			$this->redirect_type['User_Registration_Settings_Redirection_After_Registration'] = include 'redirect/class-ur-settings-redirection-after-registeration.php';
			$this->redirect_type['User_Registration_Settings_Redirection_After_Logout'] = include 'redirect/class-ur-settings-redirection-after-logout.php';
			add_filter( 'ur_pro_settings_redirection_after_login',array( $this, 'save_custom_options' ),10,2 );
			add_filter( 'ur_pro_settings_redirection_after_logout',array( $this, 'save_custom_options' ),10,2 );
			add_filter( 'ur_pro_settings_redirection_after_registration',array( $this, 'save_custom_options' ),10,2 );
		}

		/**
		 * Add this page to settings.
		 *
		 * @param  array $pages Pages.
		 * @return mixed
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		public function get_redirect_type() {
			return $this->redirect_type;
		}
		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'' => __( 'Popups', 'user-registration' ),
				'role-based-redirection' => __('Role based Redirection', 'user-registration'),
			);

			return apply_filters( 'user_registration_get_sections_' . $this->id, $sections );
		}

		/**
		 * Change tinymce editor settings.
		 *
		 * @param  array $settings All settings.
		 * @return mixed
		 */
		public function user_registration_pro_editor_settings( $settings ) {

			// Check if the tab is of user registration pro addon and handle text editor separately.
			if ( isset( $_GET['tab'] ) && 'user-registration-pro' === $_GET['tab'] ) {
				$settings['media_buttons'] = false;
				$settings['textarea_rows'] = 4;
			}
			return $settings;
		}

		/**
		 * Get Add New Popup Settings.
		 *
		 * @return array.
		 */
		public function get_add_new_popup_settings() {
			$all_forms = ur_get_all_user_registration_form();

			$popup_id = isset( $_REQUEST['edit-popup'] ) ? $_REQUEST['edit-popup'] : '';

			$args   = array(
				'post_type'   => 'ur_pro_popup',
				'post_status' => array( 'publish', 'trash' ),
			);
			$popups = new WP_Query( $args );

			foreach ( $popups->posts as  $item ) {

				if ( $popup_id == $item->ID ) {
					$popup_content = json_decode( $item->post_content );
				}
			}

			$popup_type = array(
				'registration' => 'Registration',
				'login'        => 'Login',
			);

			$header_title = '';
			if ( isset( $popup_content ) ) {
				$header_title = sprintf( __( '%s', 'user-registration' ), ucfirst( $popup_content->popup_title ) );
			} else {
				$header_title = __( 'Add new Popup', 'user-registration' );
			}

			$settings = apply_filters(
				'user_registration_get_add_new_popup_settings',
				array(
					'title'          => esc_html( $header_title ),
					'back_link'      => esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=user-registration-pro&section=popups' ) ),
					'back_link_text' => __( 'Back to all Popups', 'user-registration' ),
					'sections'       => array(
						'edit_popup_display_settings' => array(
							'title'    => __( 'Display Popup', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Enable this popup', 'user-registration' ),
									'desc'     => __( 'Enable', 'user-registration' ),
									'id'       => 'user_registration_pro_enable_popup',
									'type'     => 'checkbox',
									'desc_tip' => __( 'Check to enable popup.', 'user-registration' ),
									'css'      => 'min-width: 350px;',
									'default'  => isset( $popup_content ) && 1 == $popup_content->popup_status ? 'yes' : 'no',
								),
								array(
									'title'    => __( 'Select popup type', 'user-registration' ),
									'desc'     => __( 'Select either the popup is registration or login type.', 'user-registration' ),
									'id'       => 'user_registration_pro_popup_type',
									'type'     => 'select',
									'class'    => 'ur-enhanced-select user-registration-pro-select-popup-type',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
									'options'  => $popup_type,
									'default'  => isset( $popup_content ) ? $popup_content->popup_type : array_values( $popup_type )[0],
								),
							),
						),
						'edit_popup_content'          => array(
							'title'    => __( 'Popup Content', 'user-registrations' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Popup Name', 'user-registrations' ),
									'desc'     => __( 'Enter the title of popup.', 'user-registrations' ),
									'id'       => 'user_registration_pro_popup_title',
									'type'     => 'text',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
									'default'  => isset( $popup_content ) ? $popup_content->popup_title : '',
								),
								array(
									'title'    => __( 'Popup Header Content', 'user-registrations' ),
									'desc'     => __( 'Here you can put header content.', 'user-registrations' ),
									'id'       => 'user_registration_pro_popup_header_content',
									'type'     => 'tinymce',
									'default'  => isset( $popup_content ) ? $popup_content->popup_header : '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
								array(
									'title'     => __( 'Select form', 'user-registrations' ),
									'desc'      => __( 'Select which registration form to render in popup.', 'user-registrations' ),
									'id'        => 'user_registration_pro_popup_registration_form',
									'type'      => 'select',
									'row_class' => 'single-registration-select',
									'class'     => 'ur-enhanced-select user-registration-pro-select-registration-form',
									'css'       => 'min-width: 350px;',
									'desc_tip'  => true,
									'options'   => $all_forms,
									'default'   => isset( $popup_content->form ) ? $popup_content->form : array_values( $all_forms )[0],
								),
								array(
									'title'    => __( 'Popup Footer Content', 'user-registrations' ),
									'desc'     => __( 'Here you can put footer content.', 'user-registrations' ),
									'id'       => 'user_registration_pro_popup_footer_content',
									'type'     => 'tinymce',
									'default'  => isset( $popup_content ) ? $popup_content->popup_footer : '',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
								),
							),
						),
						'edit_popup_appearance'       => array(
							'title'    => __( 'Popup Appearance', 'user-registrations' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Select Popup Size', 'user-registrations' ),
									'desc'     => __( 'Select which size of popup you want.', 'user-registrations' ),
									'id'       => 'user_registration_pro_popup_size',
									'type'     => 'select',
									'class'    => 'ur-enhanced-select',
									'css'      => 'min-width: 350px;',
									'desc_tip' => true,
									'options'  => array(
										'default'     => 'Default',
										'large'       => 'Large',
										'extra_large' => 'Extra Large',
									),
									'default'  => isset( $popup_content->popup_size ) ? $popup_content->popup_size : 'default',
								),
							),
						),
					),
				)
			);

			return apply_filters( 'user_registration_get_add_new_popup_settings_' . $this->id, $settings );
		}

		/**
		 * Role based Redirection Settings.
		 *
		 * @since 3.0.0
		 * @return array.
		 */
		public function get_role_based_redirection_settings() {

			$settings = apply_filters(
				'user_registration_role_based_redirection_settings',
				 array(
					'title' => __( 'Role Based Redirection', 'user-registration' ),
					'sections' => array (
						'role_based_redirection_settings'       => array(
							'title'    => __( 'Configure Role based Redirection', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'title'    => __( 'Enable Role based Redirection', 'user-registration' ),
									'desc'     => __( 'Handles role based redirection to a specific page after login or registration.', 'user-registration' ),
									'id'       => 'user_registration_pro_role_based_redirection',
									'type'     => 'checkbox',
									'css'      => 'min-width: 350px;',
									'default'  => 'no',
								),
								array(
									'type' => 'role_redirect_settings',
									'id' => 'user_registration_pro_role_based_redirection_settings',
								)
							),
						),
				),
			)
			);
			return apply_filters( 'user_registration_get_role_based_redirection_settings_' . $this->id, $settings );
		}

		/**
		 * Add Role Mapping table inside role based redirection settings page.
		 *
		 * @param [type] $settings
		 * @param [type] $option
		 */
		public function ur_role_based_redirection_mapping_table( $settings, $option ) {
			$settings .= '<tr valign="top">';
			$settings .= '<td class="ur_emails_wrapper" colspan="2">';
			$settings .= '<table class="ur_emails widefat" cellspacing="0">';
			$settings .= '<thead>';
			$settings .= '<tr>';

			$columns = apply_filters(
						'user_registration_role_redirect_setting_columns',
						array(
							'name'    => __( 'Redirection Type', 'user-registration' ),
							'actions' => __( 'Configure', 'user-registration' ),
						)
					);
			foreach ( $columns as $key => $column ) {
				$settings .= '<th style="padding-left:15px" class="ur-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
			}
			$settings .= '</tr>';
			$settings .= '</thead>';
			$settings .= '<tbody>';
			$redirect_type = $this->get_redirect_type();
			foreach ( $redirect_type as $type ) {
				$settings .= '<tr><td class="ur-email-settings-table">';
				$settings .= '<a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=user-registration-pro&section=ur_settings_' . $type->id . '' ) .
												'">' . __( $type->title, 'user-registration' ) . '</a>';
				$settings .=  ur_help_tip( __( $type->description, 'user-registration' ) );
				$settings .= '</td>';
				$settings .= '<td class="ur-email-settings-table">';
				$settings .= '<a class="button tips" data-tip="' . esc_attr__( 'Configure', 'user-registration' ) . '" href="' . admin_url( 'admin.php?page=user-registration-settings&tab=user-registration-pro&section=ur_settings_' . $type->id . '' ) . '"><span class="dashicons dashicons-admin-generic"></span> </a>';
				$settings .= '</td>';
				$settings .= '</tr>';
			}

			$settings .= '</tbody>';
			$settings .= '</table>';
			$settings .= '</td>';
			$settings .= '</tr>';

			return $settings;
		}

		/**
		 * Output sections.
		 */
		public function output_sections() {
			global $current_section;

			$sections = $this->get_sections();

			if ( empty( $sections ) ) {
				return;
			}

			echo '<ul class="subsubsub">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				if ( 'add-new-popup' === $current_section && 'popups' === $id ) {
					echo '<li><a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="current">' . $label . '</a> ' . ' </li>';
				} else {
					echo '<li><a href="' . admin_url( 'admin.php?page=user-registration-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ' </li>';
				}
			}
		}

		/**
		 * Outputs Pro Page
		 *
		 * @return void
		 */
		public function output() {
			global $current_section;

			$redirect_type = $this->get_redirect_type();
			foreach ($redirect_type as $type) {

				if ( $current_section == 'ur_settings_'. $type->id .'' ) {
					$settings = new $type();
					$settings = $settings->get_settings();
				}
			}
			$settings = isset( $settings ) ? $settings : $this->get_settings();

			UR_Admin_Settings::output_fields( $settings );
			switch ( $current_section ) {
				case '':
				case 'popups':
					$GLOBALS['hide_save_button'] = true;
					// Add screen option.
					add_screen_option(
						'per_page',
						array(
							'default' => 20,
							'option'  => 'user_registration_pro_popups_per_page',
						)
					);

					if ( isset( $_REQUEST['success'] ) ) {
						if ( 'popup-created' === ( $_REQUEST['success'] ) ) {
							echo '<div id="message" class="inline updated"><p><strong>' . __( 'Popup successfully generated.', 'user-registrations' ) . '</strong></p></div>';
						} else {
							echo '<div id="message" class="inline updated"><p><strong>' . __( 'Popup successfully updated.', 'user-registrations' ) . '</strong></p></div>';
						}
					}
					echo '</form>';
					User_Registration_Pro_Admin::user_registration_pro_popup_list_table_output();
					break;
				case 'add-new-popup':
					$handle_action = user_registration_pro_popup_settings_handler();

					if ( $handle_action === true ) {

						if ( ! isset( $_REQUEST['edit-popup'] ) ) {
							$success = 'popup-created';
						} else {
							$success = 'popup-edited';
						}
						wp_redirect( admin_url( 'admin.php?page=user-registration-settings&tab=user-registration-pro&section=popups&success=' . $success ) );
					}

					$settings = $this->get_add_new_popup_settings();
					UR_Admin_Settings::output_fields( $settings );

					return;
				break;
				case 'role-based-redirection':
					$settings = $this->get_role_based_redirection_settings();
					UR_Admin_Settings::output_fields( $settings );
					break;
			}
		}

			/**
			 * Filter Notice for pro tab.
			 *
			 * @return bool
			 */
		public function filter_notice() {
			global $current_tab;

			if ( 'user-registration-pro' === $current_tab ) {
				return false;
			}

			return true;
		}

		/**
		 * Filter submit button label for certain tabs and sections.
		 *
		 * @param  string $label Label
		 * @return string        Label
		 */
		public function filter_label( $label ) {
			global $current_tab;
			global $current_section;

			if ( 'user-registration-pro' === $current_tab && 'add-new-popup' === $current_section ) {

				if ( ! isset( $_REQUEST['edit-popup'] ) ) {
					return __( 'Add Popup', 'user-registrations' );
				} else {
					return __( 'Update Popup', 'user-registrations' );
				}
			}

			return $label;
		}

			/**
			 * Save settings
			 */
		public function save() {

			global $current_section;

			$is_custom_option = false;
			$option_name = '';
			$option_value = array();
			$redirect_type = $this->get_redirect_type();

			foreach ( $redirect_type as $type ) {

				if ( $current_section === 'ur_settings_'. $type->id ) {
					$is_custom_option = true;
					$option_name = 'ur_pro_settings_'. $type->id ;
					$option_value = apply_filters( $option_name, $type->id, $option_value );
				}
			}

			// Check current section and handle save action accordingly.
			if ( 'add-new-popup' === $current_section ) {
				$settings = $this->get_add_new_popup_settings();
			} elseif ( 'role-based-redirection' === $current_section ) {
				$settings = $this->get_role_based_redirection_settings();
			}
			$settings = isset( $settings ) ? $settings : $this->get_settings();

			if( ! $is_custom_option ) {
				UR_Admin_Settings::save_fields( $settings );
			}else{
				update_option( $option_name, $option_value );
			}
		}

		/**
		 * Save all custom options.
		 *
		 * @param string $section_id section ID.
		 * @param array $option_value Option value
		 * @return array
		 */
		public function save_custom_options($section_id, $option_value=array() ) {
			switch( $section_id ){
				case 'redirection_after_login':
				case 'redirection_after_logout':
				case 'redirection_after_registration':
					foreach ( ur_get_default_admin_roles() as $key => $value ) {
						$option_value[$key] = isset($_POST[$key])? wp_unslash( absint( $_POST[$key]) ) : '';
					}
				break;
			}
			return $option_value;
		}
	}
	endif;
return new User_Registration_Pro_Settings();
