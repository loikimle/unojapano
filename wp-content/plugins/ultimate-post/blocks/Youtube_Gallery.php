<?php

namespace ULTP\blocks;

defined( 'ABSPATH' ) || exit;

class Youtube_Gallery {

	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function get_attributes() {

		return array(
			'blockId'                   => '',
			'previewImg'                => '',
			'advanceId'                 => '',
			'className'                 => '',
			'playlistIdOrUrl'           => 'PLPidnGLSR4qcAwVwIjMo1OVaqXqjUp_s4',
			'youTubeApiKey'             => '',
			'cacheDuration'             => '0',
			'sortBy'                    => 'date',
			'layout'             => 'inline',
			'videosPerPage'             => (object) array( 'lg' => 6 ),
			'showVideoTitle'            => true,
			'videoTitleLength'          => (object) array( 'lg' => 50, 'md' => 50, 'sm' => 50 ),
			'loadMoreEnable'            => true,
			'moreButtonLabel'           => 'More Videos',
			'autoplay'                  => true,
			'autoplayPlaylist'          => false,
			'loop'                      => false,
			'mute'                      => false,
			'showPlayerControl'         => true,
			'hideYoutubeLogo'           => false,
			'showDescription'           => false,
			'videoDescriptionLength'    => (object) array( 'lg' => 100, 'md' => 100, 'sm' => 100 ),
			'imageHeightRatio'          => 'maxres',
			'galleryColumn'             => (object) array( 'lg' => 3, 'md' => 2, 'sm' => 1 ),
			'displayType'               => 'grid',
			'enableListView'            => false,
			'playIcon'                  => 'youtube_logo_icon_solid',
			'enableAnimation'			  => false,
			'defaultYoutubeIcon'           => true,
		);
	}

	public function register() {
		register_block_type(
			'ultimate-post/youtube-gallery',
			array(
				'editor_script'   => 'ultp-blocks-editor-script',
				'editor_style'    => 'ultp-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	public function enqueue_scripts() {
		wp_enqueue_script(
			'ultp-youtube-gallery-block',
			ULTP_URL . 'assets/js/ultp-youtube-gallery-block.js',
			array( 'jquery' ),
			ULTP_VER,
			true
		);
	}

	public function content( $attr, $noAjax ) {
		$attr = wp_parse_args( $attr, $this->get_attributes() );
		$content = '';
		$attr['className']      = isset( $attr['className'] ) && $attr['className'] ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';
		$attr['align']          = isset( $attr['align'] ) && $attr['align'] ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['align'] ) : '';
		$attr['advanceId']      = isset( $attr['advanceId'] ) ? sanitize_html_class( $attr['advanceId'] ) : '';
		$attr['blockId']        = isset( $attr['blockId'] ) ? sanitize_html_class( $attr['blockId'] ) : '';

		$block_class = 'ultp-post-grid-block  ultp-block-' . $attr['blockId'];
		if ( $attr['align'] ) {
			$block_class .= ' align' . $attr['align'];
		}
		if ( $attr['className'] ) {
			$block_class .= ' ' . $attr['className'];
		}

		$videos_per_page = isset( $attr['videosPerPage'] ) ? $attr['videosPerPage'] : array( 'lg' => 9, 'md' => 6, 'sm' => 3 );
		$video_title_length = isset( $attr['videoTitleLength'] ) ? $attr['videoTitleLength'] : array( 'lg' => 50, 'md' => 50, 'sm' => 50 );
		$video_description_length = isset( $attr['videoDescriptionLength'] ) ? $attr['videoDescriptionLength'] : array( 'lg' => 100, 'md' => 100, 'sm' => 100 );
		$gallery_column = isset( $attr['galleryColumn'] ) ? $attr['galleryColumn'] : array( 'lg' => 3, 'md' => 2, 'sm' => 1 );

		$block_name = 'youtube-gallery';

		$content .= '<div '. ( $attr['advanceId'] ? 'id="' . $attr['advanceId'] . '" ' : '' ) . ' class="wp-block-ultimate-post-' . $block_name . ' ultp-block-' . $attr['blockId'] . '' . ( $attr['align'] ? ' align' . $attr['align'] : '' ) . '' . ( $attr['className'] ? ' ' . $attr['className'] : '' ) . '"';
		$content .= ' data-playlist="' . esc_attr( $attr['playlistIdOrUrl'] ) . '"';
		$content .= ' data-api-key="' . esc_attr( $attr['youTubeApiKey'] ) . '"';
		$content .= ' data-cache-duration="' . esc_attr( $attr['cacheDuration'] ) . '"';
		$content .= ' data-sort-by="' . esc_attr( $attr['sortBy'] ) . '"';
		$content .= ' data-gallery-layout="' . esc_attr( $attr['layout'] ) . '"';
		$content .= ' data-videos-per-page="' . esc_attr( wp_json_encode( $videos_per_page ) ) . '"';
		$content .= ' data-show-video-title="' . ( $attr['showVideoTitle'] ? '1' : '0' ) . '"';
		$content .= ' data-video-title-length="' . esc_attr( wp_json_encode( $video_title_length ) ) . '"';
		$content .= ' data-load-more-enable="' . ( $attr['loadMoreEnable'] ? '1' : '0' ) . '"';
		$content .= ' data-more-button-label="' . esc_attr( $attr['moreButtonLabel'] ) . '"';
		$content .= ' data-autoplay="' . ( $attr['layout'] == 'playlist' ? ( $attr['autoplayPlaylist'] ? '1' : '0' ) : ( $attr['autoplay'] ? '1' : '0' ) ) . '"';
		$content .= ' data-loop="' . ( $attr['loop'] ? '1' : '0' ) . '"';
		$content .= ' data-mute="' . ( $attr['mute'] ? '1' : '0' ) . '"';
		$content .= ' data-show-player-control="' . ( $attr['showPlayerControl'] ? '1' : '0' ) . '"';
		$content .= ' data-hide-youtube-logo="' . ( $attr['hideYoutubeLogo'] ? '1' : '0' ) . '"';
		$content .= ' data-show-description="' . ( $attr['showDescription'] ? '1' : '0' ) . '"';
		$content .= ' data-video-description-length="' . esc_attr( wp_json_encode( $video_description_length ) ) . '"';
		$content .= ' data-image-height-ratio="' . esc_attr( $attr['imageHeightRatio'] ) . '"';
		$content .= ' data-gallery-column="' . esc_attr( wp_json_encode( $gallery_column ) ) . '"';
		$content .= ' data-display-type="' . esc_attr( $attr['displayType'] ) . '"';
		$content .= ' data-enable-list-view="' . ( $attr['enableListView'] ? '1' : '0' ) . '"';
		$content .= ' data-enable-youtube-icon="' . ( $attr['defaultYoutubeIcon'] ? '1' : '0' ) . '"';
		$content .= ' data-enable-icon-animation="' . ( $attr['enableAnimation'] ? '1' : '0' ) . '"';
		$content .= ' data-img-height="' . ( $attr['imageHeightRatio']  ) . '"';
		$content .= '>';

		$grid_class = 'ultp-ytg-view-grid ultp-layout-' . esc_attr( $attr['layout'] );
		if ( $attr['enableListView'] ) {
			$grid_class .= ' ultp-ytg-view-list';
		}
		if ( $attr['imageHeightRatio'] !== 'custom' ) {
			$grid_class .= ' ultp-ratio-height';
		}
		$grid_class .= ' ultp-ytg-' . esc_attr( $attr['displayType'] );

		$content .= '<div class="ultp-block-wrapper ultp-ytg-block ultp-wrapper-block">';
		$content .= '<div class="ultp-ytg-container ' . esc_attr( $grid_class ) . '">';
		$content .= '<div class=""></div>';
		$content .= '</div>';
		if ( $attr['loadMoreEnable']  &&  $attr['layout'] != 'playlist') {
			$content .= '<div class="ultp-ytg-loadmore-btn">';
			$content .= esc_html( $attr['moreButtonLabel'] );
			$content .= '</div>';
		}
		if($attr['playIcon']) {
			$content .= '<div class="ultp-ytg-play__icon" style="display:none;" >';
			$content .= $attr['defaultYoutubeIcon'] ? '<svg
										xmlns="http://www.w3.org/2000/svg"
										viewBox="0 0 28.57 20"
										preserveAspectRatio="xMidYMid meet"
									>
										<g>
											<path
												d="M27.9727 3.12324C27.6435 1.89323 26.6768 0.926623 25.4468 0.597366C23.2197 2.24288e-07 14.285 0 14.285 0C14.285 0 5.35042 2.24288e-07 3.12323 0.597366C1.89323 0.926623 0.926623 1.89323 0.597366 3.12324C2.24288e-07 5.35042 0 10 0 10C0 10 2.24288e-07 14.6496 0.597366 16.8768C0.926623 18.1068 1.89323 19.0734 3.12323 19.4026C5.35042 20 14.285 20 14.285 20C14.285 20 23.2197 20 25.4468 19.4026C26.6768 19.0734 27.6435 18.1068 27.9727 16.8768C28.5701 14.6496 28.5701 10 28.5701 10C28.5701 10 28.5677 5.35042 27.9727 3.12324Z"
												fill="#FF0000"
											/>
											<path
												d="M11.4253 14.2854L18.8477 10.0004L11.4253 5.71533V14.2854Z"
												fill="white"
											/>
										</g>
									</svg>' : ultimate_post()->get_svg_icon( $attr['playIcon']);
			$content .= '</div>';
		}
		
		$content .= '</div>';
		$content .= '</div>';
		return $content;
	}
}
