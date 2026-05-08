<?php
/**
 * Custom Email Display Handler
 *
 * @class    Custom_Email_Display
 * @version
 * @package  UserRegistration/Modules/CustomEmail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Custom_Email_Display' ) ) :

	class Custom_Email_Display {

		/**
		 * Data handler instance.
		 *
		 * @var Custom_Email_Data
		 */
		private $data_handler;

		/**
		 * Constructor.
		 *
		 * @param Custom_Email_Data $data_handler Data handler instance.
		 */
		public function __construct( $data_handler ) {
			$this->data_handler = $data_handler;
		}

		/**
		 * Custom email notification setting display.
		 *
		 * @param string $settings Settings HTML.
		 * @param mixed  $value    Field value.
		 * @return string Settings HTML with custom emails table.
		 */
		public function custom_email_notification_setting( $settings, $value ) {
			$emails = $this->data_handler->get_custom_emails();

			$settings .= '<tr valign="top">';
			$settings .= '<td class="ur_emails_wrapper" colspan="2">';
			$settings .= '<table class="ur_emails widefat" cellspacing="0">';
			$settings .= '<thead>';
			$settings .= '<tr>';

			$columns = apply_filters(
				'user_registration_custom_email_setting_columns',
				array(
					'name'    => __( 'Email', 'user-registration' ),
					'status'  => __( 'Status', 'user-registration' ),
					'preview' => __( 'Preview', 'user-registration' ),
					'actions' => __( 'Configure', 'user-registration' ),
				)
			);

			foreach ( $columns as $key => $column ) {
				$settings .= '<th style="padding-left:15px" class="ur-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
			}
			$settings .= '</tr>';
			$settings .= '</thead>';
			$settings .= '<tbody>';

			if ( ! empty( $emails ) ) {
				foreach ( $emails as $email_id => $email ) {
					$status = isset( $email['enabled'] ) && $email['enabled'] ? true : false;
					$title  = isset( $email['name'] ) ? $email['name'] : ( isset( $email['title'] ) ? $email['title'] : __( 'Untitled Email', 'user-registration' ) );

					$settings .= '<tr><td class="ur-email-settings-table">';
					$settings .= '<a href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=email&section=ur_settings_custom_email_' . esc_attr( $email_id ) ) ) . '">' . esc_html( $title ) . '</a>';
					if ( ! empty( $email['description'] ) ) {
						$settings .= ur_help_tip( $email['description'] );
					}
					$settings .= '</td>';
					$settings .= '<td class="ur-email-settings-table">';
					$hidden_input = '<input type="hidden" name="email_status[' . esc_attr( $email_id ) . ']" value="0" />';
					$label     = '<div class="ur-toggle-section"><span class="user-registration-toggle-form user-registration-email-status-toggle" >' . $hidden_input . '<input type="checkbox" name="email_status[' . esc_attr( $email_id ) . ']" value="1" data-email-id="' . esc_attr( $email_id ) . '" id="email_' . esc_attr( $email_id ) . '"' . ( $status ? "checked='checked'" : '' ) . '"/><span class="slider round"></span></span></div>';
					$settings .= '<label class="ur-email-status" style="' . ( $status ? 'color:green;font-weight:500;' : 'color:red;font-weight:500;' ) . '">';
					$settings .= $label;
					$settings .= '</label>';
					$settings .= '</td>';
					$settings .= '<td class="ur-email-settings-table">';
					$settings .= '<a class="button tips user-registration-email-preview" rel="noreferrer noopener" target="_blank" data-tip="' . esc_attr__( 'Preview', 'user-registration' ) . '" href="' . esc_url(
						add_query_arg(
							array(
								'ur_custom_email_preview' => $email_id,
							),
							home_url()
						)
					) . '"><span class="dashicons dashicons-visibility"></span></a>';
					$settings .= '</td>';
					$settings .= '<td class="ur-email-settings-table">';
					$settings .= '<div class="ur-email-actions-wrapper">';
					$settings .= '<a class="button tips ur-email-actions-toggle" href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=email&from=custom-email&section=ur_settings_custom_email_' . esc_attr( $email_id ) ) ) . '" data-email-id="' . esc_attr( $email_id ) . '"><span class="dashicons dashicons-admin-generic"></span></a>';
					$settings .= '<div class="ur-email-actions-dropdown" data-email-id="' . esc_attr( $email_id ) . '">';
					$settings .= '<a href="' . esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=email&from=custom-email&section=ur_settings_custom_email_' . esc_attr( $email_id ) ) ) . '" class="ur-email-action-edit">';
					$settings .= '<span class="dashicons dashicons-edit"></span> ' . esc_html__( 'Edit', 'user-registration' );
					$settings .= '</a>';
					$settings .= '<a href="#" class="ur-email-action-delete" data-email-id="' . esc_attr( $email_id ) . '">';
					$settings .= '<span class="dashicons dashicons-trash"></span> ' . esc_html__( 'Delete', 'user-registration' );
					$settings .= '</a>';
					$settings .= '</div>';
					$settings .= '</div>';
					$settings .= '</td>';
					$settings .= '</tr>';
				}
			} else {
				$settings .= '<tr><td colspan="4" style="text-align:center;padding:20px;">' . esc_html__( 'No custom emails found. Click "Add New Email" to create one.', 'user-registration' ) . '</td></tr>';
			}

			$settings .= '</tbody>';
			$settings .= '</table>';
			$settings .= '</td>';
			$settings .= '</tr>';

			return $settings;
		}
	}

endif;
