<?php

namespace WPEverest\URM\Pro\FileDownloads\Taxonomies;

abstract class Base {

	/**
	 * @return string
	 */
	abstract public function get_taxonomy();

	/**
	 * @return array<string, mixed>
	 */
	abstract public function get_taxonomy_args();

	/**
	 * @return array<string>
	 */
	abstract public function get_object_types();

	/**
	 * @return void
	 */
	public function register() {
		if ( $this->is_registered() ) {
			return;
		}

		$args   = apply_filters(
			"user_registration_file_downloads_{$this->get_taxonomy()}_taxonomy_args",
			$this->get_taxonomy_args(),
			$this->get_taxonomy()
		);
		$result = register_taxonomy( $this->get_taxonomy(), $this->get_object_types(), $args );

		if ( is_wp_error( $result ) ) {
			// TODO: may be implement logging.
			return;
		}

		do_action( "user_registration_file_downloads_{$this->get_taxonomy()}_taxonomy_registered", $this->get_taxonomy() );
	}

	/**
	 * @return boolean
	 */
	public function is_registered() {
		return taxonomy_exists( $this->get_taxonomy() );
	}
}
