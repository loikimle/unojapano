<?php
/**
 * Email wrapper template
 *
 * This template wraps email body content with styled header and footer.
 * The $body_content variable will be inserted where indicated.
 *
 * @var string $body_content Email body content to wrap.
 * @var string $current_year Current year for copyright.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $current_year ) ) {
	$current_year = date( 'Y' );
}

$header_enable     = ur_string_to_bool( get_option( 'user_registration_email_template_header_enable', 'no' ) );
$header_logo       = get_option( 'user_registration_email_template_header_logo', '' );
$header_text       = get_option( 'user_registration_email_template_header_text', '' );
$footer_enable     = ur_string_to_bool( get_option( 'user_registration_email_template_footer_enable', 'no' ) );
$footer_content    = get_option( 'user_registration_email_template_footer_content', '' );
$header_text_color = '#000000';
$header_logo_align = 'left';
$header_bg_color   = '#FFFFFF';

$is_preview  = isset( $_GET['ur_email_preview'] ) && 'email_template_option' === sanitize_text_field( wp_unslash( $_GET['ur_email_preview'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$email_width = $is_preview ? '600px' : '50%';
$max_width   = '600px';

if ( empty( $footer_content ) ) {
	$footer_content = '
<p style="margin: 0 0 12px 0; color: #6c757d; font-size: 13px; line-height: 1.5;">© ' . date( 'Y' ) . ' {{blog_info}}. All rights reserved.</p>
<p style="margin: 0; font-size: 14px; line-height: 1.6;"><a href="{{home_url}}" style="color: #4A90E2; text-decoration: none; font-weight: 500;">{{blog_info}} Team</a></p>';
}

$logo_html            = '';
$logo_alignment_style = 'text-align: ' . esc_attr( $header_logo_align ) . ';';

if ( ! empty( $header_logo ) ) {
	$logo_html = '<div style="position: relative; z-index: 1; display: inline-block;">
		<img src="' . esc_url( $header_logo ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" style="max-width: 200px; max-height: 60px; height: auto; display: block; width: auto;" />
	</div>';
}

$header_text_html = '';
if ( ! empty( $header_text ) ) {
	$header_text_html = '<div style="position: relative; z-index: 1; margin-top: 6px; color: ' . esc_attr( $header_text_color ) . '; font-size: 18px; line-height: 1.5;">
		' . wp_kses_post( $header_text ) . '
	</div>';
}

$responsive_styles = '<style type="text/css">
	@media only screen and (max-width: 600px) {
		.email-wrapper-outer {
			padding: 20px 0 !important;
		}
		.email-wrapper-inner {
			width: 100% !important;
			max-width: 100% !important;
			margin: 0 !important;
			border-radius: 0 !important;
		}
		.email-header {
			padding: 20px 15px !important;
			border-radius: 0 !important;
		}
		.email-body {
			padding: 25px 15px !important;
		}
		.email-footer {
			padding: 20px 15px !important;
		}
		.email-logo img {
			max-width: 150px !important;
			max-height: 50px !important;
		}
		.email-header-text {
			font-size: 16px !important;
			margin-top: 10px !important;
		}
		.email-footer p {
			font-size: 12px !important;
		}
		.email-footer a {
			font-size: 13px !important;
		}
	}
	@media only screen and (max-width: 480px) {
		.email-wrapper-outer {
			padding: 10px 0 !important;
		}
		.email-header {
			padding: 15px 10px !important;
		}
		.email-body {
			padding: 20px 10px !important;
		}
		.email-footer {
			padding: 15px 10px !important;
		}
		.email-logo img {
			max-width: 120px !important;
			max-height: 40px !important;
		}
		.email-header-text {
			font-size: 14px !important;
		}
	}
</style>';

$header = apply_filters(
	'user_registration_email_template_header',
	$responsive_styles . '
	<div class="email-wrapper-outer" style="font-family: Arial, sans-serif; padding: 100px 0;">
	<div class="email-wrapper-inner" style="width: ' . esc_attr( $email_width ) . '; max-width: ' . esc_attr( $max_width ) . '; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
	<!-- Header -->
	<div class="email-header" style="background-color: ' . esc_attr( $header_bg_color ) . '; padding: 30px; position: relative; overflow: hidden; border-radius: 12px 12px 0 0;">
		<!-- Logo -->
		<div style="' . esc_attr( $logo_alignment_style ) . '">
			<div class="email-logo">' . $logo_html . '</div>
			<div class="email-header-text">' . $header_text_html . '</div>
		</div>
	</div>

	<!-- Body Content -->
	<div class="email-body" style="padding: 40px 30px; background-color: #ffffff; border-top: 1px solid #e0e0e0;">'
);


$footer = apply_filters(
	'user_registration_email_template_footer',
	'</div>

	<!-- Footer -->
	<div class="email-footer" style="padding: 30px; background-color: #ffffff; border-top: 1px solid #e0e0e0; text-align: left;">
		' . $footer_content . '
	</div>

</div>
</div>'
);

$is_already_wrapped = false !== strpos( $body_content, 'email-wrapper-outer' );
$show_header        = $header_enable && ! empty( $header_logo );

if ( $show_header ) {
	if ( $is_already_wrapped ) {
		preg_match( '/<style[^>]*>.*?<\/style>/s', $body_content, $style_matches );
		$existing_styles = ! empty( $style_matches[0] ) ? $style_matches[0] : '';

		$content_clean = preg_replace( '/<style[^>]*>.*?<\/style>/s', '', $body_content );

		$body_div_start = strpos( $content_clean, '<div class="email-body"' );
		if ( false === $body_div_start ) {
			$body_div_start = strpos( $content_clean, "<div class='email-body'" );
		}

		if ( false !== $body_div_start ) {
			$tag_end = strpos( $content_clean, '>', $body_div_start );
			if ( false !== $tag_end ) {
				$content_start = $tag_end + 1;
				$content_end   = strpos( $content_clean, '</div>', $content_start );
				if ( false !== $content_end ) {
					$inner_content = trim( substr( $content_clean, $content_start, $content_end - $content_start ) );
				} else {
					preg_match( '/<div[^>]*class=["\']email-body["\'][^>]*>(.*?)<\/div>/s', $content_clean, $matches );
					$inner_content = ! empty( $matches[1] ) ? trim( $matches[1] ) : $body_content;
				}
			} else {
				$inner_content = $body_content;
			}
		} else {
			preg_match( '/<div[^>]*class=["\']email-body["\'][^>]*>(.*?)<\/div>/s', $content_clean, $matches );
			$inner_content = ! empty( $matches[1] ) ? trim( $matches[1] ) : $body_content;
		}

		if ( ! empty( $existing_styles ) ) {
			$header_without_styles = str_replace( $responsive_styles, '', $header );
			echo $existing_styles . $header_without_styles;
		} else {
			echo $header;
		}
		echo $inner_content;
		if ( $footer_enable && ! empty( $footer_content ) ) {
			echo $footer;
		} else {
			echo '</div></div>';
		}
	} else {
		echo $header;
		echo $body_content;
		if ( $footer_enable && ! empty( $footer_content ) ) {
			echo $footer;
		} else {
			echo '</div></div>';
		}
	}
} elseif ( $footer_enable && ! empty( $footer_content ) ) {
	if ( $is_already_wrapped ) {
		$content_clean = preg_replace( '/<style[^>]*>.*?<\/style>/s', '', $body_content );
		preg_match( '/<div[^>]*class=["\']email-body["\'][^>]*>(.*?)<\/div>/s', $content_clean, $matches );
		$inner_content = ! empty( $matches[1] ) ? trim( $matches[1] ) : $body_content;
	} else {
		$inner_content = $body_content;
	}

		$wrapper_start = $responsive_styles . '
		<div class="email-wrapper-outer" style="font-family: Arial, sans-serif; padding: 100px 0;">
		<div class="email-wrapper-inner" style="width: ' . esc_attr( $email_width ) . '; max-width: ' . esc_attr( $max_width ) . '; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
		<!-- Body Content -->
		<div class="email-body" style="padding: 40px 30px; background-color: #ffffff;">';

		echo $wrapper_start;
		echo $inner_content;
		echo $footer;
} elseif ( $is_already_wrapped ) {
		echo $body_content;
} elseif ( function_exists( 'ur_wrap_email_body_content' ) ) {
		echo ur_wrap_email_body_content( $body_content );
} else {
	echo $body_content;
}
