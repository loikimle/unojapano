<?php
/**
 * Group code user enrollment form
 *
 * @var string  $enable_recaptcha
 * @var string  $ldgr_recaptcha_site_key
 * @var string  $ldgr_enable_gdpr
 * @var string  $ldgr_gdpr_checkbox_message
 *
 * @since 4.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<?php if ( 'on' == $enable_recaptcha ) : ?>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<div class="ldgr-group-code-enrollment-form-container">

	<?php do_action( 'ldgr_action_before_group_code_enrollment_form' ); ?>

	<div class="ldgr-group-code-messages">
		<span class="ldgr-message-close">&times;</span>
		<span class="ldgr-message-text"></span>
	</div>

	<form id="ldgr-group-code-enrollment-form" class="ldgr-form" type="post">        

		<div class="ldgr-form-field">
			<label for="ldgr-user-group-code">
				<?php esc_html_e( 'Group Code', WDM_LDGR_TXT_DOMAIN ); ?>
			</label>
			<input type="text" name="ldgr-user-group-code" id="ldgr-user-group-code" autocomplete="off" required />
		</div>

		<?php wp_nonce_field( 'ldgr-group-code-enrollment-form', 'ldgr_nonce' ); ?>

		<?php if ( 'on' == $enable_recaptcha ) : ?>
			<div class="g-recaptcha ldgr-form-field" data-sitekey="<?php echo esc_attr( $ldgr_recaptcha_site_key ); ?>"></div>
		<?php endif; ?>

		<?php if ( 'on' === $ldgr_enable_gdpr ) : ?>
			<p>
				<label for="ldgr-user-gdpr-check">
					<input type="checkbox" name="ldgr-user-gdpr-check" id="ldgr-user-gdpr-check" required />
					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $ldgr_gdpr_checkbox_message;
					?>
				</label>
			</p>
		<?php endif; ?>

		<button type="submit" id="ldgr-user-enroll-form-submit"><?php esc_html_e( 'Submit', WDM_LDGR_TXT_DOMAIN ); ?></button>

	</form>


	<div class="ldgr-black-screen">
		<span style="margin-bottom:10px;"><?php esc_html_e( 'Please wait...', WDM_LDGR_TXT_DOMAIN ); ?></span>
		<span class="dashicons dashicons-update spin"></span>
	</div>

	<?php do_action( 'ldgr_action_after_group_code_enrollment_form' ); ?>

</div>
