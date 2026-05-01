<?php
defined( 'ABSPATH' ) || exit;


if ( $attr['enablePopup'] || ( isset( $vid_icon_redirect ) && ! $vid_icon_redirect ) ) {
	$post_loop .= '<div enableVideoPopup="' . $attr['enablePopup'] . '" enableAutoPlay="' . $attr['popupAutoPlay'] . '" class="ultp-video-icon">' . ultimate_post()->get_svg_icon( 'play_line' ) . '</div>';
} else {
	$post_loop .= '<a href="' . $titlelink . '" enableAutoPlay="' . $attr['popupAutoPlay'] . '" class="ultp-video-icon">' . ultimate_post()->get_svg_icon( 'play_line' ) . '</a>';
}
