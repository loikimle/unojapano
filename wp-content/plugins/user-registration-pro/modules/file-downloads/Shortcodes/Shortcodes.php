<?php

namespace WPEverest\URM\Pro\FileDownloads\Shortcodes;

use WPEverest\URM\Pro\FileDownloads\Taxonomies\Taxonomy;

defined( 'ABSPATH' ) || exit;

class Shortcodes {

	/**
	 * @return void
	 */
	public function register() {
		add_shortcode( 'urm_files', [ $this, 'render_files_shortcode' ] );
	}

	/**
	 * Usage:
	 * [urm_files ids="1,2,3"]
	 * [urm_files categories="cat1,cat2"]
	 * [urm_files ids="1,2" category_ids="3,4"]
	 *
	 * @param array<string, mixed> $atts
	 * @return string
	 */
	public function render_files_shortcode( $atts ) {
		$atts = shortcode_atts(
			[
				'ids'            => '',
				'categories'     => '',
				'category_ids'   => '',
				'show_file_size' => false,
				'show_file_type' => false,
			],
			$atts,
			'urm_files'
		);

		return do_blocks( $this->generate_block_markup( $atts ) );
	}

	/**
	 * @param array<string, mixed> $atts
	 * @return string
	 */
	private function generate_block_markup( $atts ) {
		$block_attrs      = $this->parse_block_attributes( $atts );
		$has_file_ids     = ! empty( $block_attrs['fileIds'] );
		$has_category_ids = ! empty( $block_attrs['categoryIds'] );

		if ( ! $has_file_ids && ! $has_category_ids ) {
			return '';
		}

		if ( $has_file_ids && $has_category_ids ) {
			$block_attrs['displayMode'] = 'both';
		} elseif ( $has_category_ids ) {
			$block_attrs['displayMode'] = 'categories';
		} else {
			$block_attrs['displayMode'] = 'ids';
		}

		$json_attrs = wp_json_encode( $block_attrs, JSON_UNESCAPED_SLASHES );

		return sprintf(
			'<!-- wp:urfd/file-downloads %s /-->',
			$json_attrs
		);
	}

	/**
	 * @param array<string, mixed> $atts
	 * @return array<string, mixed>
	 */
	private function parse_block_attributes( $atts ) {
		$block_attrs = [];

		$file_ids = $this->parse_file_ids( $atts );
		if ( ! empty( $file_ids ) ) {
			$block_attrs['fileIds'] = $file_ids;
		}

		$category_ids = $this->parse_category_ids( $atts );
		if ( ! empty( $category_ids ) ) {
			$block_attrs['categoryIds'] = $category_ids;
		}

		if ( ! empty( $atts['show_file_size'] ) ) {
			$block_attrs['showFileSize'] = filter_var( $atts['show_file_size'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( ! empty( $atts['show_file_type'] ) ) {
			$block_attrs['showFileType'] = filter_var( $atts['show_file_type'], FILTER_VALIDATE_BOOLEAN );
		}

		return $block_attrs;
	}

	/**
	 * @param array<string, mixed> $atts
	 * @return array<int>
	 */
	private function parse_file_ids( $atts ) {
		$ids = [];

		if ( ! empty( $atts['id'] ) ) {
			if ( strpos( $atts['id'], ',' ) !== false ) {
				$ids = array_merge( $ids, explode( ',', $atts['id'] ) );
			} else {
				$ids[] = (int) $atts['id'];
			}
			$ids[] = $atts['id'];
		}

		if ( ! empty( $atts['ids'] ) ) {
			$ids = array_merge( $ids, explode( ',', $atts['ids'] ) );
		}

		$ids = array_map( 'intval', $ids );
		$ids = array_filter(
			$ids,
			function ( $id ) {
				return $id > 0;
			}
		);

		return array_values( array_unique( $ids ) );
	}

	/**
	 * @param array<string, mixed> $atts
	 * @return array<int>
	 */
	private function parse_category_ids( $atts ) {
		$category_slugs = [];
		$category_ids   = [];

		if ( ! empty( $atts['category'] ) ) {
			if ( strpos( $atts['category'], ', ' ) !== false ) {
				$category_slugs = array_merge( $category_slugs, explode( ', ', $atts['category'] ) );
			} else {
				$category_slugs[] = $atts['category'];
			}
		}

		if ( ! empty( $atts['categories'] ) ) {
			$category_slugs = array_merge( $category_slugs, explode( ',', $atts['categories'] ) );
		}

		if ( ! empty( $atts['category_id'] ) ) {
			if ( strpos( $atts['category_id'], ', ' ) !== false ) {
				$parsed_ids   = array_map( 'intval', explode( ', ', $atts['category_id'] ) );
				$category_ids = array_merge( $category_ids, $parsed_ids );
			} else {
				$category_ids[] = (int) $atts['category_id'];
			}
		}

		if ( ! empty( $atts['category_ids'] ) ) {
			$parsed_ids   = array_map( 'intval', explode( ',', $atts['category_ids'] ) );
			$category_ids = array_merge( $category_ids, $parsed_ids );
		}
		foreach ( $category_slugs as $slug ) {
			$term = get_term_by( 'slug', trim( $slug ), Taxonomy::FILE_CATEGORY );
			if ( $term && ! is_wp_error( $term ) ) {
				$category_ids[] = $term->term_id;
			}
		}

		$category_ids = array_filter(
			$category_ids,
			function ( $id ) {
				return $id > 0;
			}
		);

		return array_values( array_unique( $category_ids ) );
	}
}
