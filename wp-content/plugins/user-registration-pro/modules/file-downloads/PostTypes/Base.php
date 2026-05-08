<?php

namespace WPEverest\URM\Pro\FileDownloads\PostTypes;

abstract class Base {

	/**
	 * @return string
	 */
	abstract public function get_post_type();

	/**
	 * @return array<string, mixed>
	 */
	abstract public function get_post_type_args();

	/**
	 * @return void
	 */
	public function register() {
		if ( $this->is_registered() ) {
			return;
		}

		$args   = apply_filters(
			"user_registration_file_downloads_{$this->get_post_type()}_post_type_args",
			$this->get_post_type_args(),
			$this->get_post_type()
		);
		$result = register_post_type( $this->get_post_type(), $args );

		if ( is_wp_error( $result ) ) {
			// TODO: may be implement logging.
			return;
		}

		do_action( "user_registration_file_downloads_{$this->get_post_type()}_post_type_registered", $this->get_post_type() );
	}

	/**
	 * @return boolean
	 */
	public function is_registered() {
		return post_type_exists( $this->get_post_type() );
	}
}
