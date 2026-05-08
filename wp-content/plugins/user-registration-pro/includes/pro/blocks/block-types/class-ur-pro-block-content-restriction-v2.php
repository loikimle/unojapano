<?php
/**
 * User registration content restriction block.
 *
 * @since xx.xx.xx
 * @package user-registration
 */

defined( 'ABSPATH' ) || exit;
/**
 * Block registration form class.
 */
class UR_Pro_Block_Content_Restriction_V2 extends UR_Block_Abstract {
	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'content-restriction-v2';

	/**
	 * Build html.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function build_html( $content ) {

		$attr = $this->attributes;

		$control_type      = isset( $attr['controlType'] ) ? $attr['controlType'] : 'access';
		$control_case      = isset( $attr['controlCase'] ) ? $attr['controlCase'] : '';
		$specific_roles    = isset( $attr['accessSpecificRoles'] ) ? $attr['accessSpecificRoles'] : '';
		$memberships_roles = isset( $attr['accessMembershipRoles'] ) ? $attr['accessMembershipRoles'] : '';
		$message_type      = isset( $attr['restrictionMessageType'] ) ? $attr['restrictionMessageType'] : 'global';

		$default_message  = '<h3>' . __( 'Membership Required', 'user-registration' ) . '</h3>';
		$default_message .= '<p>' . __( 'This content is available to members only.', 'user-registration' ) . '</p>';
		$default_message .= '<p>' . __( 'Sign up to unlock access or log in if you already have an account.', 'user-registration' ) . '</p>';
		$default_message .= '<p>{{sign_up}} {{log_in}}</p>';

		$content_restriction_message = get_option( 'user_registration_content_restriction_message', $default_message );

		if ( 'custom' === $message_type ) {
			$content_restriction_message = isset( $attr['customRestrictionMessage'] ) ? $attr['customRestrictionMessage'] : $default_message;
		}

		$content_restriction_message = apply_filters( 'user_registration_process_smart_tags', $content_restriction_message );
		if ( function_exists( 'apply_shortcodes' ) ) {
			$content_restriction_message = apply_shortcodes( $content_restriction_message );
		} else {
			$content_restriction_message = do_shortcode( $content_restriction_message );
		}

		$current_user_role    = is_user_logged_in() ? wp_get_current_user()->roles[0] : 'guest';
		$is_membership_active = ur_check_module_activation( 'membership' );

		if ( $is_membership_active ) {
			$members_subscription = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
			$subscription         = $members_subscription->get_member_subscription( wp_get_current_user()->ID );

			$current_user_membership   = array();
			$is_user_membership_active = false;

			if ( ! empty( $subscription ) && is_array( $subscription ) ) {
				foreach ( $subscription as $sub ) {
					if ( ! empty( $sub['item_id'] ) ) {
						$current_user_membership[] = $sub['item_id'];
					}
					if ( ! empty( $sub['status'] ) && 'active' === $sub['status'] ) {
						$is_user_membership_active = true;
					}
				}
			}
		}

		$matched = false;

		switch ( $control_case ) {
			case 'all_logged_in_users':
				$matched = is_user_logged_in();
				break;

			case 'choose_specific_roles':
				if ( ! empty( $specific_roles ) && in_array( $current_user_role, $specific_roles, true ) ) {
					$matched = true;
				}
				break;

			case 'guest_users':
				$matched = ! is_user_logged_in();
				break;

			case 'memberships':
				if ( ! empty( $memberships_roles ) && is_array( $current_user_membership ) && $is_user_membership_active ) {
					$common = array_intersect( $current_user_membership, $memberships_roles );
					if ( ! empty( $common ) ) {
						$matched = true;
					}
				}
				break;
		}

		$show_content = ( 'access' === $control_type && $matched ) || ( 'restrict' === $control_type && ! $matched );

		if ( $show_content ) {
			return $content;
		} else {
			ob_start();
			urcr_get_template(
				'base-restriction-template.php',
				array(
					'message'    => wp_kses_post( $content_restriction_message ),
					'login_url'  => '',
					'signup_url' => '',
				)
			);
			return ob_get_clean();

		}
	}
}
