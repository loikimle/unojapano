<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/user-registration-woocommerce/woocommerce-login-register.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpeverest.com/user-registration/template-structure/
 * @package UserRegistrationWooCommerce/Templates
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$form_id = get_option( 'user_registration_woocommerce_settings_form', 0 );

if ( 0 < $form_id ) {
	$registration_shortcode = sprintf( '[user_registration_form id=%s]', $form_id );

	apply_filters( 'user_registration_woocommerce_login_registration_form_before_notice', ur_print_notices() );
	do_action( 'user_registration_woocommerce_before_customer_login_registration_form' );
	?>

	<div class="ur-frontend-form login-registration">
		<div class="ur-form-row">
			<div class="ur-form-grid">
				<h2 class="ur-form-title"><?php echo __( 'Login', 'user-registration' ); ?></h2>
				<?php echo do_shortcode( '[user_registration_my_account]' ); ?>
			</div>
			<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) { ?>
			<div class="ur-form-grid">
				<h2 class="ur-form-title"><?php echo __( 'Registration', 'user-registration' ); ?></h2>
				<?php echo do_shortcode( $registration_shortcode ); ?>
			</div>
			<?php } ?>
		</div>
	</div>

	<?php
	do_action( 'user_registration_woocommerce_after_login_registration_form' );
}
?>
