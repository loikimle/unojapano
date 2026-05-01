<?php
namespace ULTP\blocks;

defined( 'ABSPATH' ) || exit;

class Button {
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register() {
		register_block_type(
			'ultimate-post/button',
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	public function render( $attr, $content ) {

		if ( ultimate_post()->is_dc_active( $attr ) && isset( $attr['dc'] ) ) {

			// [ $text, $url ] = \ULTP\DCService::get_dc_content_for_rich_text( $attr );
			$dc_text_val = \ULTP\DCService::get_dc_content_for_rich_text( $attr );
			$text        = isset( $dc_text_val['0'] ) ? $dc_text_val['0'] : '';
			$url         = isset( $dc_text_val['1'] ) ? $dc_text_val['1'] : '';

			// Replacing URL with dynamic content
			if ( ! empty( $url ) ) {
				$pattern     = '/href="([^"]*)"/';
				$replacement = 'href="' . esc_url( $url ) . '"';
				$content     = preg_replace( $pattern, $replacement, $content );
			}

			$content = \ULTP\DCService::replace( $content, wp_kses( $text, ultimate_post()->ultp_allowed_html_tags() ) );
		}

		return $content;
	}
}
