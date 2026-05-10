<?php
/**
 * User Registration Profile Completeness Helper methods.
 *
 * @package WPEverest\UserRegistration\ProfileCompleteness
 *
 * @since 1.0.0
 */

namespace WPEverest\UserRegistration\ProfileCompleteness;

/**
 * Helper methods for User Registration Profile Completeness.
 *
 * @since 1.0.0
 */
class Helpers {
	/**
	 * Checks whether the Profile Completeness is enabled or not.
	 *
	 * @param int $form_id The ID of the form to check.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Returns true if enabled, false otherwise.
	 */
	public static function is_enabled_profile_completeness( $form_id ) {

		return ur_string_to_bool( ur_get_single_post_meta( $form_id, 'user_registration_enable_profile_completeness', false ) );

	}
}
