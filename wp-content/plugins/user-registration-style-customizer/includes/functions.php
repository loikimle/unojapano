<?php

/**
 * Enqueue fonts.
 *
 * @param string $font_family Font Family.
 * @param mixed $load_locally Load font stylesheet locally.
 * @return void
 */
function ursc_enqueue_fonts( $font_family = '', $load_locally = false ) {

	if ( ! empty( $font_family ) ) {
		$font_url = 'https://fonts.googleapis.com/css?family=' . ur_clean( $font_family );

		if ( ur_string_to_bool( $load_locally ) ) {
			$font_url = wptt_get_webfont_url( $font_url );
		}

		wp_enqueue_style( 'user-registration-google-fonts', $font_url, array(), '1.0.0', 'all' );
	}
}
