<?php
/**
 * User Registration Pro Shortcodes.
 *
 * @class    User_Registration_Pro_Shortcodes
 * @version  1.0.0
 * @package  UserRegistrationPro/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User_Registration_Pro_Shortcodes Class
 */
class User_Registration_Pro_Shortcodes {

	public static $parts = false;

	/**
	 * Init Shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'user_registration_popup' => __CLASS__ . '::popup',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param string[] $function
	 * @param array    $atts (default: array())
	 * @param array    $wrapper
	 *
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'user-registration-modal',
			'before' => null,
			'after'  => null,
		)
	) {
		ob_start();

		echo empty( $wrapper['before'] ) ? '<div id="user-registration" class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		return ob_get_clean();
	}

	/**
	 * User Registration Pro Popup shortcode.
	 *
	 * @param mixed $atts
	 */
	public static function popup( $atts ) {

		if ( empty( $atts ) || ! isset( $atts['id'] ) ) {
			return '';
		}

		ob_start();
		self::render_popup( $atts );
		return ob_get_clean();
	}

	/**
	 * Output for popup.
	 *
	 * @since 1.0.1 Recaptcha only
	 * @param array $attributes Attributes supplied in shortcode.
	 */
	public static function render_popup( $attributes ) {
		$popup_id = $attributes['id'];
		$post     = get_post( $popup_id );

		if ( isset( $post ) && isset( $post->post_content ) ) {
			$popup_content = json_decode( $post->post_content );

			$popup_status = isset( $popup_content->popup_status ) ? $popup_content->popup_status : '';

			if ( $popup_status ) {
				$current_user_capability = apply_filters( 'ur_registration_user_capability', 'create_users' );

				if ( ( is_user_logged_in() && current_user_can( $current_user_capability ) ) || ! is_user_logged_in() ) {
					$display = 'display:block;';

					include UR_ABSPATH . 'templates/pro/popup-registration.php';
				}
			}
		} else {
			echo '<h2>' . esc_html__( 'Popup not found', 'user-registration' ) . '</h2>';
		}
	}

}
