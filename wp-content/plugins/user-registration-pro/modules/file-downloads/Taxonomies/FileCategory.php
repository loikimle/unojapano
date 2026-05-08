<?php

namespace WPEverest\URM\Pro\FileDownloads\Taxonomies;

use WPEverest\URM\Pro\FileDownloads\Controllers\V1\FileCategoriesController;
use WPEverest\URM\Pro\FileDownloads\PostTypes\PostType;
use WPEverest\URM\Pro\FileDownloads\Taxonomies\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FileCategory extends Base {

	public function __construct() {
		add_action( "user_registration_file_downloads_{$this->get_taxonomy()}_taxonomy_registered", [ $this, 'on_registration' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_taxonomy() {
		return Taxonomy::FILE_CATEGORY;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_object_types() {
		return [ PostType::FILE ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_taxonomy_args() {
		return [
			'labels'                => [
				'name'                       => __( 'File Categories', 'user-registration' ),
				'singular_name'              => __( 'File Category', 'user-registration' ),
				'search_items'               => __( 'Search file categories', 'user-registration' ),
				'popular_items'              => __( 'Popular file categories', 'user-registration' ),
				'all_items'                  => __( 'All file categories', 'user-registration' ),
				'parent_item'                => __( 'Parent file category', 'user-registration' ),
				'parent_item_colon'          => __( 'Parent file category:', 'user-registration' ),
				'edit_item'                  => __( 'Edit file category', 'user-registration' ),
				'view_item'                  => __( 'View file category', 'user-registration' ),
				'update_item'                => __( 'Update file category', 'user-registration' ),
				'add_new_item'               => __( 'Add new file category', 'user-registration' ),
				'new_item_name'              => __( 'New file category name', 'user-registration' ),
				'separate_items_with_commas' => __( 'Separate file categories with commas', 'user-registration' ),
				'add_or_remove_items'        => __( 'Add or remove file categories', 'user-registration' ),
				'choose_from_most_used'      => __( 'Choose from the most used file categories', 'user-registration' ),
				'not_found'                  => __( 'No file categories found', 'user-registration' ),
				'no_terms'                   => __( 'No file categories', 'user-registration' ),
				'items_list_navigation'      => __( 'File categories list navigation', 'user-registration' ),
				'items_list'                 => __( 'File categories list', 'user-registration' ),
				'most_used'                  => __( 'Most used', 'user-registration' ),
				'back_to_items'              => __( '&larr; Back to file categories', 'user-registration' ),
			],
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'show_in_nav_menus'     => false,
			'show_in_rest'          => true,
			'rest_base'             => 'file-categories',
			'rest_namespace'        => 'user-registration-pro/v1',
			'rest_controller_class' => FileCategoriesController::class,
			'hierarchical'          => true,
			'query_var'             => false,
			'rewrite'               => false,
		];
	}

	public function on_registration() {}
}
