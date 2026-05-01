<?php
/**
 * UserRegistrationSocialConnect Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistrationSocialConnect/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return mixed
 */
function user_registration_social_login_templates() {

	$templates = apply_filters(
		'user_registration_social_login_template',
		array(

			'ursc_theme_1' => sprintf( __( 'Style 1 %1$s', 'user-registration-social-connect' ), '<br/><img style="margin-top:10px;" src="' . URSC()->plugin_url() . '/assets/images/ursc_theme_1.jpg"/>' ),
			'ursc_theme_2' => sprintf( __( 'Style 2 %1$s', 'user-registration-social-connect' ), '<br/><img style="margin-top:10px;" src="' . URSC()->plugin_url() . '/assets/images/ursc_theme_2.jpg"/>' ),
			'ursc_theme_3' => sprintf( __( 'Style 3 %1$s', 'user-registration-social-connect' ), '<br/><img style="margin-top:10px;" src="' . URSC()->plugin_url() . '/assets/images/ursc_theme_3.jpg"/>' ),
			'ursc_theme_4' => sprintf( __( 'Style 4 %1$s', 'user-registration-social-connect' ), '<br/><img style="margin-top:10px;" src="' . URSC()->plugin_url() . '/assets/images/ursc_theme_4.jpg"/>' ),

		)
	);

	return $templates;
}

/**
 * @return mixed
 */
function user_registration_social_networks() {

	$networks = array(

		'facebook' => array(
			'enable_id'  => 'user_registration_social_setting_enable_facebook_connect',
			'key_id'     => 'user_registration_social_setting_facebook_app_id',
			'secret_id'  => 'user_registration_social_setting_facebook_app_secret',
			'login_text' => 'user_registration_social_login_with_facebook_text',
		),
		'twitter'  => array(
			'enable_id'  => 'user_registration_social_setting_enable_twitter_connect',
			'key_id'     => 'user_registration_social_setting_twitter_consumer_key',
			'secret_id'  => 'user_registration_social_setting_twitter_consumer_secret',
			'login_text' => 'user_registration_social_login_with_twitter_text',

		),
		'google'   => array(
			'enable_id'  => 'user_registration_social_setting_enable_google_connect',
			'key_id'     => 'user_registration_social_setting_google_client_id',
			'secret_id'  => 'user_registration_social_setting_google_client_secret',
			'login_text' => 'user_registration_social_login_with_google_text',

		),
		'linkedin' => array(
			'enable_id'  => 'user_registration_social_setting_enable_linkedin_connect',
			'key_id'     => 'user_registration_social_setting_linkedin_client_id',
			'secret_id'  => 'user_registration_social_setting_linkedin_client_secret',
			'login_text' => 'user_registration_social_login_with_linkedin_text',

		),

	);

	return apply_filters( 'user-registration-registered-social-networks', $networks );
}

/**
 * Get color of social networks for chart.
 *
 * @param string $network Network name.
 * @return string
 */
function ursc_get_social_chart_color( $network ) {

	switch ( $network ) {
		case 'facebook':
			$color = '#547CCE';
			break;
		case 'twitter':
			$color = '#46B8FF';
			break;
		case 'google':
			$color = '#FF6464';
			break;
		case 'linkedin':
			$color = '#0077B5';
			break;
		default:
			$color = '#0BB9AE';
			break;
	}

	return $color;
}

/**
 * @param $key
 * @param $value
 */
function user_registration_social_connect_set_session( $key, $value ) {

	if ( session_status() === PHP_SESSION_NONE ) {
		session_start();
	}

	$_SESSION['user_registration_social_connect'][ $key ] = $value;
}

/**
 * @param $key
 * @param $value
 */
function user_registration_session_start() {

	if ( session_status() === PHP_SESSION_NONE ) {
		session_start();
	}

}

/**
 * @param $key
 */
function user_registration_social_connect_unset_session( $key ) {
	if ( session_status() === PHP_SESSION_NONE ) {
		session_start();
	}

	if ( isset( $_SESSION['user_registration_social_connect'] ) ) {

		if ( isset( $_SESSION['user_registration_social_connect'][ $key ] ) ) {

			unset( $_SESSION['user_registration_social_connect'][ $key ] );
		}
	}

}

/**
 * @param $key
 */

function user_registration_social_connect_get_session( $key ) {
	if ( session_status() === PHP_SESSION_NONE ) {
		session_start();
	}

	if ( isset( $_SESSION['user_registration_social_connect'] ) ) {

		if ( isset( $_SESSION['user_registration_social_connect'][ $key ] ) ) {

			return ( $_SESSION['user_registration_social_connect'][ $key ] );
		}
	}

	return false;

}

/**
 * @param $notices
 */
function user_registration_social_connect_admin_notice() {

	$class = 'notice notice-error';

	$message = ursc_is_compatible();

	if ( 'YES' !== $message ) {

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), ( $message ) );
	}
}

/**
 * Deprecate plugin missing notice.
 *
 * @deprecated 1.3.4
 *
 * @return void
 */
function ursc_admin_notices() {
	ur_deprecated_function( 'ursc_admin_notices', '1.3.4', 'user_registration_social_connect_admin_notice' );
}

function ursc_check_plugin_compatibility() {

	add_action( 'admin_notices', 'user_registration_social_connect_admin_notice', 10 );

}

/**
 * @param bool $show_notice
 */
function ursc_is_compatible() {

	$ur_plugins_path = WP_PLUGIN_DIR . URSC_DS . 'user-registration' . URSC_DS . 'user-registration.php';
	$ur_pro_plugins_path = WP_PLUGIN_DIR . URSC_DS . 'user-registration-pro' . URSC_DS . 'user-registration.php';

	if ( ! file_exists( $ur_plugins_path ) && ! file_exists( $ur_pro_plugins_path ) ) {
		return __( 'Please install <code>user-registration-pro</code> plugin to use <code>user-registration-social-connect</code> addon.', 'user-registration-social-connect' );
	}

	$ur_plugin_file_path = 'user-registration/user-registration.php';
	$ur_pro_plugin_file_path = 'user-registration-pro/user-registration.php';

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( $ur_plugin_file_path ) && ! is_plugin_active( $ur_pro_plugin_file_path ) ) {
		return __( 'Please activate <code>user-registration-pro</code> plugin to use <code>user-registration-social-connect</code> addon.', 'user-registration-social-connect' );
	}
	if ( function_exists( 'UR' ) ) {
		$user_registration_version = UR()->version;
	} else {
		$user_registration_version = get_option( 'user_registration_version' );
	}

	if ( ! is_plugin_active( $ur_pro_plugin_file_path ) ) {

		if ( version_compare( $user_registration_version, '1.1.0', '<' ) ) {
			return __( 'Please update your <code>user-registration</code> plugin(to at least 1.1.0 version) to <code>use user-registration-social-connect</code> addon.', 'user-registration-social-connect' );
		}
	} else {

		if ( version_compare( $user_registration_version, '3.0.0', '<' ) ) {
			return __( 'Please update your <code>user-registration-pro</code> plugin(to at least 3.0.0 version) to use <code>user-registration-social-connect</code> addon.', 'user-registration-social-connect' );
		}
	}

	return 'YES';

}

/**
 * @param $redirect
 */
function ursc_custom_redirect( $redirect ) {
	if ( headers_sent() ) { // Use JavaScript to redirect if content has been previously sent (not recommended, but safe)
		echo '<script language="JavaScript" type="text/javascript">window.location=\'';
		echo $redirect;
		echo '\';</script>';
	} else { // Default Header Redirect
		header( 'Location: ' . $redirect );
	}
	exit;
}

function ursc_flush_all() {

	if ( session_status() === PHP_SESSION_NONE ) {
		session_start();
	}
	if ( isset( $_SESSION['user_registration_social_connect'] ) ) {

		unset( $_SESSION['user_registration_social_connect'] );
	}

	global $ursc_response_global;

	unset( $ursc_response_global );
}

/**
 * @return mixed
 */
function ursc_social_login_redirect() {

	return apply_filters( 'user_registration_social_connect_login_redirect', admin_url() );
}

/**
 * @param $session
 *
 * @return bool
 */
function ursc_check_network_session_exists( $session ) {

	if ( isset( $session['email'] ) && isset( $session['username'] ) && isset( $session['network'] ) && ( $session['network'] == 'google' || isset( $session['profile'] ) ) && isset( $session['has_email'] ) ) {

		return true;
	}

	return false;
}

/**
 * @param $session
 *
 * @return bool
 */
function ursc_has_matched_with_session( $session, $data ) {

	if ( ! ursc_check_network_session_exists( $session ) ) {

		return false;
	}
	if ( ! $session['has_email'] ) {

		$session['email'] = $data['email'];

	}
	unset( $session['has_email'] );

	$result = array_diff_assoc( $session, $data );

	return count( $result ) > 0 ? false : true;
}

/**
 * @return mixed
 */
function ursc_social_api_settings() {

	return apply_filters(
		'user_registration_social_settings',
		array(
			'title' =>  __( 'API settings', 'user-registration-social-connect' ),
			'sections' => array (
				'user_registration_social_facebook_options' => array(
					'title' => __( 'Facebook', 'user-registration-social-connect' ),
					'type'  => 'card',
					'desc'  => '',
					'settings' => array(
						array(
							'row_class' => 'ursc_enable_disable ursc_facebook_enable',
							'title'     => __( 'Enable facebook ?', 'user-registration-social-connect' ),
							'desc'      => __( 'Tick here if you want to enable facebook login.', 'user-registration-social-connect' ),
							'id'        => 'user_registration_social_setting_enable_facebook_connect',
							'default'   => 'no',
							'type'      => 'checkbox',
							'autoload'  => false,
						),
						array(
							'row_class' => 'ursc_hidden ursc_facebook_app_id',
							'title'     => __( 'Facebook App ID', 'user-registration-social-connect' ),
							'desc'      => sprintf( __( 'Get app id from  %1$s facebook %2$s.', 'user-registration-social-connect' ), '<a href="https://developers.facebook.com/apps/" target="_blank">', '</a>' ),
							'default'   => '',
							'id'        => 'user_registration_social_setting_facebook_app_id',
							'type'      => 'text',
							'autoload'  => false,
							'desc_tip'  => true,
							'css'       => 'min-width: 350px;',

						),
						array(
							'row_class' => 'ursc_hidden ursc_facebook_app_secret',
							'title'     => __( 'Facebook App secret', 'user-registration-social-connect' ),
							'desc'      => sprintf( __( 'Get app secret from  %1$s facebook %2$s.', 'user-registration-social-connect' ), '<a href="https://developers.facebook.com/apps/" target="_blank">', '</a>' ),
							'default'   => '',
							'id'        => 'user_registration_social_setting_facebook_app_secret',
							'type'      => 'text',
							'autoload'  => false,
							'desc_tip'  => true,
							'css'       => 'min-width: 350px;',

						),
					),
				),
				'user_registration_social_twitter_options' => array(
					'title' => __( 'Twitter', 'user-registration-social-connect' ),
					'type'  => 'card',
					'desc'  => '',
					'settings' => array(
						array(
							'row_class' => 'ursc_enable_disable ursc_twitter_enable',
							'title'     => __( 'Enable twitter?', 'user-registration-social-connect' ),
							'desc'      => __( 'Tick here if you want to enable twitter login.', 'user-registration-social-connect' ),
							'id'        => 'user_registration_social_setting_enable_twitter_connect',
							'default'   => 'no',
							'type'      => 'checkbox',
							'autoload'  => false,
						),
						array(
							'row_class' => 'ursc_hidden ursc_twitter_consumer_key',
							'title'     => __( 'Twitter Consumer Key', 'user-registration-social-connect' ),
							'desc'      => sprintf( __( 'Get Consumer Key from  %1$s twitter %2$s.', 'user-registration-social-connect' ), '<a href="https://apps.twitter.com/app/" target="_blank">', '</a>' ),
							'default'   => '',
							'id'        => 'user_registration_social_setting_twitter_consumer_key',
							'type'      => 'text',
							'autoload'  => false,
							'desc_tip'  => true,
							'css'       => 'min-width: 350px;',

						),
						array(
							'row_class' => 'ursc_hidden ursc_twitter_consumer_secret',
							'title'     => __( 'Twitter Consumer Secret', 'user-registration-social-connect' ),
							'desc'      => sprintf( __( 'Get consumer secret from  %1$s twitter %2$s.', 'user-registration-social-connect' ), '<a href="https://apps.twitter.com/app/" target="_blank">', '</a>' ),
							'default'   => '',
							'id'        => 'user_registration_social_setting_twitter_consumer_secret',
							'type'      => 'text',
							'autoload'  => false,
							'desc_tip'  => true,
							'css'       => 'min-width: 350px;',

						),
					),
				),
				'user_registration_social_google_options' => array(
					'title' => __( 'Google', 'user-registration-social-connect' ),
					'type'  => 'card',
					'desc'  => '',
					'settings' => array(
						array(
							'row_class' => 'ursc_enable_disable ursc_google_enable',
							'title'     => __( 'Enable google?', 'user-registration-social-connect' ),
							'desc'      => __( 'Tick here if you want to enable google login.', 'user-registration-social-connect' ),
							'id'        => 'user_registration_social_setting_enable_google_connect',
							'default'   => 'no',
							'type'      => 'checkbox',
							'autoload'  => false,
						),
						array(
							'row_class' => 'ursc_hidden ursc_google_client_id',
							'title'     => __( 'Google Client ID', 'user-registration-social-connect' ),
							'desc'      => sprintf( __( 'Get Client ID from  %1$s google %2$s.', 'user-registration-social-connect' ), '<a href="https://console.developers.google.com/apis/" target="_blank">', '</a>' ),
							'default'   => '',
							'id'        => 'user_registration_social_setting_google_client_id',
							'type'      => 'text',
							'autoload'  => false,
							'desc_tip'  => true,
							'css'       => 'min-width: 350px;',

						),
						array(
							'row_class' => 'ursc_hidden ursc_google_client_secret',
							'title'     => __( 'Google Client Secret', 'user-registration-social-connect' ),
							'desc'      => sprintf( __( 'Get Client Secret from  %1$s google %2$s.', 'user-registration-social-connect' ), '<a href="https://console.developers.google.com/apis/" target="_blank">', '</a>' ),
							'default'   => '',
							'id'        => 'user_registration_social_setting_google_client_secret',
							'type'      => 'text',
							'autoload'  => false,
							'desc_tip'  => true,
							'css'       => 'min-width: 350px;',

						),
					),
				),
				'user_registration_social_linkedin_options' => array(
					'title' => __( 'LinkedIn', 'user-registration-social-connect' ),
					'type'  => 'card',
					'desc'  => '',
					'settings' => array(
						array(
							'row_class' => 'ursc_enable_disable ursc_linkedin_enable',
							'title'     => __( 'Enable linkedin?', 'user-registration-social-connect' ),
							'desc'      => __( 'Tick here if you want to enable linkedin login.', 'user-registration-social-connect' ),
							'id'        => 'user_registration_social_setting_enable_linkedin_connect',
							'default'   => 'no',
							'type'      => 'checkbox',
							'autoload'  => false,
						),
						array(
							'row_class' => 'ursc_hidden ursc_linkedin_client_id',
							'title'     => __( 'Linkedin Client ID', 'user-registration-social-connect' ),
							'desc'      => sprintf( __( 'Get Client ID from  %1$s linkedin %2$s.', 'user-registration-social-connect' ), '<a href="https://www.linkedin.com/developer/apps" target="_blank">', '</a>' ),
							'default'   => '',
							'id'        => 'user_registration_social_setting_linkedin_client_id',
							'type'      => 'text',
							'autoload'  => false,
							'desc_tip'  => true,
							'css'       => 'min-width: 350px;',

						),
						array(
							'row_class' => 'ursc_hidden ursc_linkedin_client_secret',
							'title'     => __( 'Linkedin Client Secret', 'user-registration-social-connect' ),
							'desc'      => sprintf( __( 'Get Client Secret from  %1$s linkedin %2$s.', 'user-registration-social-connect' ), '<a href="https://www.linkedin.com/developer/apps" target="_blank">', '</a>' ),
							'default'   => '',
							'id'        => 'user_registration_social_setting_linkedin_client_secret',
							'type'      => 'text',
							'autoload'  => false,
							'desc_tip'  => true,
							'css'       => 'min-width: 350px;',

						),
					),
				),
			),
		)
	);

}

/**
 * @return mixed
 */
function ursc_social_advance_settings() {

	$forms    = ur_get_all_user_registration_form();
	$forms[0] = __( 'None', 'user-registration-social-connect' );
	ksort( $forms );

	return apply_filters(
		'user_registration_other_social_settings',
		array(
			'title' =>  __( 'Advance Settings', 'user-registration-social-connect' ),
			'sections' => array (
				'user_registration_social_advance_options' => array(
					'title' => __( 'Social Login', 'user-registration-social-connect' ),
					'type'  => 'card',
					'desc'  => '',
					'settings' => array(
						array(
							'title'    => __( 'Enable social registration ?', 'user-registration-social-connect' ),
							'desc'     => __( 'Tick here if you want to enable social registration', 'user-registration-social-connect' ),
							'id'       => 'user_registration_social_setting_enable_social_registration',
							'default'  => 'yes',
							'type'     => 'checkbox',
							'autoload' => false,
						),
						array(
							'title'    => __( 'Display social buttons in registration ?', 'user-registration-social-connect' ),
							'desc'     => __( 'Tick here if you want to display social buttons in registration', 'user-registration-social-connect' ),
							'id'       => 'user_registration_social_setting_display_social_buttons_in_registration',
							'default'  => 'no',
							'type'     => 'checkbox',
							'autoload' => false,
						),
						array(
							'title'    => __( 'Default user role', 'user-registration-social-connect' ),
							'desc'     => __( 'This option lets you choose user role for social registration.', 'user-registration-social-connect' ),
							'id'       => 'user_registration_social_setting_default_user_role',
							'default'  => 'subscriber',
							'type'     => 'select',
							'class'    => 'ur-enhanced-select',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
							'options'  => ur_get_default_admin_roles(),
						),
						array(
							'title'    => __( 'Connect socially connected user with form', 'user-registration-social-connect' ),
							'desc'     => __( 'This option lets you integrate the user to user registration form.', 'user-registration-social-connect' ),
							'id'       => 'user_registration_social_setting_form_integration',
							'default'  => 'None',
							'type'     => 'select',
							'class'    => 'ur-enhanced-select',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
							'options'  => $forms,
						),
						array(
							'title'    => __( 'Social login position', 'user-registration-social-connect' ),
							'desc'     => __( 'Select position for social login. (This option doesn\'t work on WordPress Login form)', 'user-registration-social-connect' ),
							'id'       => 'user_registration_social_login_position',
							'default'  => 'bottom',
							'type'     => 'select',
							'class'    => 'ur-enhanced-select',
							'css'      => 'min-width: 350px;',
							'desc_tip' => true,
							'options'  => array(
								'bottom' => __( 'Bottom', 'user-registration-social-connect' ),
								'top'    => __( 'Top', 'user-registration-social-connect' ),
							),
						),

						array(
							'title'    => __( 'Social login templates', 'user-registration-social-connect' ),
							'desc'     => __( 'Select template for social login', 'user-registration-social-connect' ),
							'default'  => 'ursc_theme_4',
							'id'       => 'user_registration_social_login_template',
							'type'     => 'radio',
							'autoload' => false,
							'desc_tip' => true,
							'options'  => user_registration_social_login_templates(),

						),
					),
				),
				'user_registration_social_advance_text_options' => array(
					'title' => __( 'Social Text', 'user-registration-social-connect' ),
					'type'  => 'card',
					'desc'  => '',
					'settings' => array(
						array(
							'title'    => __( 'Login with facebook text', 'user-registration-social-connect' ),
							'desc'     => __( 'Login with facebook string.', 'user-registration-social-connect' ),
							'default'  => __( 'Login with facebook', 'user-registration-social-connect' ),
							'id'       => 'user_registration_social_login_with_facebook_text',
							'type'     => 'text',
							'autoload' => false,
							'desc_tip' => true,
							'css'      => 'min-width: 350px;',
						),
						array(
							'title'    => __( 'Login with twitter text', 'user-registration-social-connect' ),
							'desc'     => __( 'Login with twitter string.', 'user-registration-social-connect' ),
							'default'  => __( 'Login with twitter', 'user-registration-social-connect' ),
							'id'       => 'user_registration_social_login_with_twitter_text',
							'type'     => 'text',
							'autoload' => false,
							'desc_tip' => true,
							'css'      => 'min-width: 350px;',
						),
						array(
							'title'    => __( 'Login with google text', 'user-registration-social-connect' ),
							'desc'     => __( 'Login with google string.', 'user-registration-social-connect' ),
							'default'  => __( 'Login with google', 'user-registration-social-connect' ),
							'id'       => 'user_registration_social_login_with_google_text',
							'type'     => 'text',
							'autoload' => false,
							'desc_tip' => true,
							'css'      => 'min-width: 350px;',
						),
						array(
							'title'    => __( 'Login with linkedin text', 'user-registration-social-connect' ),
							'desc'     => __( 'Login with linkedin string.', 'user-registration-social-connect' ),
							'default'  => __( 'Login with linkedin', 'user-registration-social-connect' ),
							'id'       => 'user_registration_social_login_with_linkedin_text',
							'type'     => 'text',
							'autoload' => false,
							'desc_tip' => true,
							'css'      => 'min-width: 350px;',
						),
					),
				),
			),
		)
	);

}

/**
 * @return string
 *
 * @param string $user_name
 */
function ursc_get_username( $user_name, $email = '' ) {
	if ( ! empty( $email ) ) {
		$user_id = email_exists( $email );
		if ( false !== $user_id ) {
			$user = get_userdata( $user_id );
			return $user->user_login;
		}
	}

	$username = $user_name;
	$i        = 1;
	while ( username_exists( $username ) ) {
		$username = $user_name . '_' . $i;
		$i++;
	}
	return $username;
}

/**
 * Get user reports.
 *
 * @return array
 */
function ursc_get_user_report() {
	global $wpdb;

	$form_users = get_users(
		array(
			'meta_key' => 'ur_form_id',
		)
	);

	$social_user_results = $wpdb->get_results(
		"SELECT {$wpdb->prefix}users.ID, {$wpdb->prefix}usermeta.meta_key FROM {$wpdb->prefix}users, {$wpdb->prefix}usermeta WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}usermeta.user_id AND {$wpdb->prefix}usermeta.meta_key LIKE 'user_registration_social_connect_%_username'",
		ARRAY_N
	);

	$user_report = array(
		'social'    => $social_user_results,
		'reg_forms' => $form_users,
	);

	return $user_report;
}
