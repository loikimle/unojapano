<?php
/**
 * URAF_AJAX
 *
 * AJAX Event Handler
 *
 * @class    URAF_AJAX
 * @since  1.3.0
 * @package  UserRegistrationAdvancedFields/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URAF_AJAX Class
 */
class URAF_AJAX {

	/**
	 * Hooks in ajax handlers.
	 */
	public static function init() {

		self::add_ajax_events();

	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {

		$ajax_events = array(
			'method_upload' => true,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {

			add_action( 'wp_ajax_uraf_profile_picture_upload_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {

				add_action(
					'wp_ajax_nopriv_uraf_profile_picture_upload_' . $ajax_event,
					array(
						__CLASS__,
						$ajax_event,
					)
				);
			}
		}
	}


	/**
	 * User input dropped function.
	 */
	public static function method_upload() {

		check_ajax_referer( 'uraf_profile_picture_upload_nonce', 'security' );

		$nonce = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : false;

		$flag = wp_verify_nonce( $nonce, 'uraf_profile_picture_upload_nonce' );

		if ( true != $flag || is_wp_error( $flag ) ) {

			wp_send_json_error(
				array(
					'message' => __( 'Nonce error, please reload.', 'user-registration-advanced-fields' ),
				)
			);
		}

		$upload = isset( $_FILES['file'] ) ? $_FILES['file'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// valid extension for image.
		$valid_extensions = 'image/jpeg,image/gif,image/png';
		$form_id          = 0;

		// In case of registration form, a form_id parameter will be sent to identify the form,
		if ( isset( $_REQUEST['form_id'] ) && 'undefined' !== $_REQUEST['form_id'] ) {
			$form_id = $_REQUEST['form_id'];
		} else {
			// In case of edit profile if form_id is not sent as a paramater then extract the form id from the current user_id.
			$user_id = get_current_user_id();

			if ( $user_id > 0 ) {
				$form_id = ur_get_form_id_by_userid( $user_id );
			}
		}

		$ur_form = UR()->form->get_form( $form_id );

		if ( empty( $ur_form ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'The form you are trying to submit not found.', 'user-registration-advanced-fields' ),
				)
			);
		}
		$field_data           = ur_get_field_data_by_field_name( $form_id, 'profile_pic_url' );
		$valid_extensions     = ! empty( $field_data['advance_setting']->valid_file_type ) ? implode( ', ', $field_data['advance_setting']->valid_file_type ) : $valid_extensions;
		$valid_extension_type = explode( ',', $valid_extensions );
		$valid_extension_type = array_map( 'trim', $valid_extension_type );
		$valid_ext            = array();

		foreach ( $valid_extension_type as $key => $value ) {
			$image_extension   = explode( '/', $value );
			$valid_ext[ $key ] = $image_extension[1];

			if ( 'jpeg' === $image_extension[1] ) {
				$index               = count( $valid_extension_type );
				$valid_ext[ $index ] = 'jpg';
			}
		}

		$src_file_name  = isset( $upload['name'] ) ? $upload['name'] : '';
		$file_extension = strtolower( pathinfo( $src_file_name, PATHINFO_EXTENSION ) );
		if ( function_exists( 'mime_content_type' ) ) {
			$file_mime_type = isset( $upload['tmp_name'] ) ? mime_content_type( $upload['tmp_name'] ) : '';
		} else {
			$upload_file_info = isset( $upload['tmp_name'] ) ? wp_check_filetype_and_ext( $upload['tmp_name'], $upload['name'] ) : '';
			$file_mime_type   = ! empty( $upload_file_info ) ? $upload_file_info['type'] : '';
		}

		if ( ! in_array( $file_mime_type, $valid_extension_type ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid file type, please contact with site administrator.', 'user-registration' ),
				)
			);
		}

		if ( ! isset( $_POST['is_snapshot'] ) || ! isset( $_POST['is_snapshot'] ) ) {
			// Validates if the uploaded file has the acceptable extension.
			if ( ! in_array( $file_extension, $valid_ext ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Invalid file type, please contact with site administrator.', 'user-registration-advanced-fields' ),
					)
				);
			}
		}
		$max_size = wp_max_upload_size();

		$max_uploaded_size_option_value = isset( $field_data['advance_setting']->max_upload_size ) ? $field_data['advance_setting']->max_upload_size : '';

		if ( isset( $max_uploaded_size_option_value ) && '' !== $max_uploaded_size_option_value ) {
			$max_upload_size_options_value = $max_uploaded_size_option_value * 1024;
		} else {
			$max_upload_size_options_value = $max_size;
		}

		if ( ! isset( $upload['size'] ) || ( isset( $upload['size'] ) && $upload['size'] < 1 ) ) {

			wp_send_json_error(
				array(
					/* translators: %s - Max Size */
					'message' => sprintf( __( 'Please upload a picture with size less than %s', 'user-registration-advanced-fields' ), size_format( $max_size ) ),
				)
			);
		} elseif ( $upload['size'] > $max_upload_size_options_value ) {
			wp_send_json_error(
				array(
					/* translators: %s - Max Size */
					'message' => sprintf( __( 'Please upload a picture with size less than %s', 'user-registration-advanced-fields' ), size_format( $max_upload_size_options_value ) ),
				)
			);
		}

		$upload_path = ur_get_tmp_dir();

		// Checks if the upload directory has the write premission.
		if ( ! wp_is_writable( $upload_path ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Upload path permission deny.', 'user-registration-advanced-fields' ),
				)
			);

		}
		$upload_path = $upload_path . '/';
		$file_name   = wp_unique_filename( $upload_path, $upload['name'] );
		$file_path   = $upload_path . sanitize_file_name( $file_name );

		if ( move_uploaded_file( $upload['tmp_name'], $file_path ) ) {

			if ( isset( $_REQUEST['cropped_image'] ) ) {
				// Retrieves cropped picture dimensions from ajax request.
				$value              = $_REQUEST['cropped_image'];
				$cropped_image_size = json_decode( stripslashes( $value ), true );

				include_once ABSPATH . 'wp-admin/includes/image.php';

				// Retrieves original picture height and width.
				list( $original_image_width, $original_image_height ) = getimagesize( $file_path );

				// Determines the type of uploaded picture and treats them differently.
				switch ( $upload['type'] ) {
					case 'image/png':
						$img_r = imagecreatefrompng( $file_path );
						break;
					case 'image/gif':
						$img_r = imagecreatefromgif( $file_path );
						break;
					default:
						$img_r = imagecreatefromjpeg( $file_path );
				}

				$cropped_image_holder_width  = rtrim( $cropped_image_size['holder_width'], 'px' );
				$cropped_image_holder_height = rtrim( $cropped_image_size['holder_height'], 'px' );

				// Calculates the actual portion of original picture where the cropping is applied.
				$cropped_image_width  = absint( $cropped_image_size['w'] * $original_image_width / $cropped_image_holder_width );
				$cropped_image_left   = absint( $cropped_image_size['x'] * $original_image_width / $cropped_image_holder_width );
				$cropped_image_height = absint( $cropped_image_size['h'] * $original_image_height / $cropped_image_holder_height );
				$cropped_image_right  = absint( $cropped_image_size['y'] * $original_image_height / $cropped_image_holder_height );

				// Creates a frame of original height and width and copies the cropped picture portion to the frame.
				$dst_r = wp_imageCreateTrueColor( $original_image_width, $original_image_height );

				imagecopyresampled( $dst_r, $img_r, 0, 0, $cropped_image_left, $cropped_image_right, $original_image_width, $original_image_height, $cropped_image_width, $cropped_image_height );

				// Retrieves and Resizes the cropped picture to a size defined by user in filter or default of 150 by 150.
				list( $image_width, $image_height ) = apply_filters( 'user_registration_cropped_image_size', array( 150, 150 ) );
				$dest_r                             = wp_imageCreateTrueColor( $image_width, $image_height );
				imagecopyresampled( $dest_r, $dst_r, 0, 0, 0, 0, $image_width, $image_height, $original_image_width, $original_image_height );

				// Replaces the original picture with the cropped picture.
				$img_r = imagejpeg( $dest_r, $file_path );
			}

			$files = array(
				'file_name'      => $file_name,
				'file_path'      => $file_path,
				'file_extension' => $file_extension,
			);

			$attachment_id = wp_rand();
			ur_clean_tmp_files();
			$url = UR_UPLOAD_URL . 'temp-uploads/' . sanitize_file_name( $file_name );
			wp_send_json_success(
				array(
					'attachment_id'       => $attachment_id,
					'upload_files'        => crypt_the_string( maybe_serialize( $files ), 'e' ),
					'profile_picture_url' => $url,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'File cannot be uploaded.', 'user-registration-advanced-fields' ),
				)
			);
		}
	}
}

URAF_AJAX::init();
