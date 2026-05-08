<?php
/**
 * User_Registration Style Customizer Ajax
 *
 * @package User_Registration_Style_Customizer
 * @since   1.0.5
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main User_Registration Style Customizer Ajax Class.
 *
 * @class UR_Style_Customizer_Ajax
 */
final class UR_Style_Customizer_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_save_template', array( $this, 'save_template' ) );
		add_action( 'wp_ajax_delete_template', array( $this, 'delete_template' ) );
	}

	/**
	 * Save styles as a template.
	 *
	 * @return void
	 */
	public function save_template() {
		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'save_template' ) ) {
			wp_send_json_error(
				array(
					__( 'Nonce error. Please refresh the page.', 'user-registration-style-customizer' )
				)
			);
			exit;
		}

		$form_id = isset( $_POST['form_id'] ) ? sanitize_text_field( wp_unslash( $_POST['form_id'] ) ) : '0';

		$template_name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

		if ( empty( $template_name ) ) {
			$template_name = strval( time() );
		}

		$template_slug = str_replace( ' ', '-', strtolower( $template_name ) );

		if ( 0 !== absint( $form_id ) ) {
			$this->handle_save_registration_template( $form_id, $template_name, $template_slug );
		} else {
			$this->handle_save_login_template( $template_name, $template_slug );
		}
		exit;
	}

	/**
	 * Handler for saving new registration template.
	 *
	 * @param [int] $form_id Form ID.
	 * @param [string] $template_name Template Name.
	 * @param [string] $template_slug Template Slug.
	 * @return void
	 */
	public function handle_save_registration_template( $form_id, $template_name, $template_slug ) {
		$templates     = json_decode( get_transient( 'ur_style_templates' ) );
		$styles        = get_option( 'user_registration_styles' );

		if ( isset( $templates->styles->$template_slug ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Template name exists. Please change the template name and try again.', 'user-registration-style-customizer' )
				)
			);
			exit;
		}

		$template        = new stdClass();
		$template->title = $template_name;
		$template->thumb = 'https://raw.githubusercontent.com/wpeverest/user-registration-form-templates/master/images/default.png';
		$template->data  = $styles[ $form_id ];

		unset( $template->data['template'] );

		foreach ( $template->data as $element => $styles ) {
			foreach ( $styles as $style => $value ) {
				if ( ur_is_json( $value ) ) {
					$template->data[ $element ][ $style ] = json_decode( $value );
				}
			}
		}

		$template->data = wp_json_encode( $template->data );

		$templates->styles->$template_slug = $template;

		set_transient( 'ur_style_templates', wp_json_encode( $templates ) );

		wp_send_json_success(
			array(
				'template_id' => $template_slug,
				'message' => __( 'Template saved successfully. Please reload the page to view changes. Reload Now?', 'user-registration-style-customizer' ),
			)
		);
		exit;
	}

	/**
	 * Handler for saving new login template.
	 *
	 * @param [string] $template_name Template Name.
	 * @param [string] $template_slug Template Slug.
	 * @return void
	 */
	public function handle_save_login_template( $template_name, $template_slug ) {
		$templates     = json_decode( get_transient( 'ur_login_style_templates' ) );
		$styles        = get_option( 'user_registration_login_styles' );

		if ( isset( $templates->styles->$template_slug ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Template name exists. Please change the template name and try again.', 'user-registration-style-customizer' )
				)
			);
			exit;
		}

		$template        = new stdClass();
		$template->title = $template_name;
		$template->thumb = 'https://raw.githubusercontent.com/wpeverest/user-registration-form-templates/master/images/default.png';
		$template->data  = $styles;

		unset( $template->data['template'] );

		foreach ( $template->data as $element => $styles ) {
			foreach ( $styles as $style => $value ) {
				if ( ur_is_json( $value ) ) {
					$template->data[ $element ][ $style ] = json_decode( $value );
				}
			}
		}

		$template->data = wp_json_encode( $template->data );

		$templates->styles->$template_slug = $template;

		set_transient( 'ur_login_style_templates', wp_json_encode( $templates ) );

		wp_send_json_success(
			array(
				'template_id' => $template_slug,
				'message' => __( 'Template saved successfully. Please reload the page to view changes. Reload Now?', 'user-registration-style-customizer' ),
			)
		);
		exit;
	}


	/**
	 * Delete template.
	 *
	 * @return void
	 */
	public function delete_template() {
		$nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'delete_template' ) ) {
			wp_send_json_error( __( 'Nonce error. Please refresh the page.', 'user-registration-style-customizer' ) );
			exit;
		}

		try {
			$template_slug = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

			if ( 'registration' === sanitize_text_field( wp_unslash( $_POST['customize'] ) ) ) {
				$templates     = json_decode( get_transient( 'ur_style_templates' ) );

				if ( isset( $templates->styles->$template_slug ) ) {
					unset( $templates->styles->$template_slug );
				}

				set_transient( 'ur_style_templates', wp_json_encode( $templates ) );
			} elseif ( 'login' === sanitize_text_field( wp_unslash( $_POST['customize'] ) ) ) {
				$templates     = json_decode( get_transient( 'ur_login_style_templates' ) );

				if ( isset( $templates->styles->$template_slug ) ) {
					unset( $templates->styles->$template_slug );
				}

				set_transient( 'ur_login_style_templates', wp_json_encode( $templates ) );
			}

			wp_send_json_success( __( 'Template deleted successfully.', 'user-registration-style-customizer' ) );
		}
		catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}

		exit;
	}
}

new UR_Style_Customizer_Ajax();
