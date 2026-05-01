<?php
/**
 * Group code user registration form
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

<div class="ldgr-group-code-registration-form-container">

	<?php do_action( 'ldgr_action_before_group_code_registration_form' ); ?>

	<div class="ldgr-group-code-messages">
		<span class="ldgr-message-close">&times;</span>
		<span class="ldgr-message-text"></span>
	</div>

	<form id="ldgr-group-code-registration-form" class="ldgr-form" type="post">

		<div class="ldgr-form-field">
			<label for="ldgr-user-first-name">
				<?php esc_html_e( 'First Name', WDM_LDGR_TXT_DOMAIN ); ?>
			</label>
			<input type="text" name="ldgr-user-first-name" id="ldgr-user-first-name" required />
		</div>

		<div class="ldgr-form-field">
			<label for="ldgr-user-last-name">
				<?php esc_html_e( 'Last Name', WDM_LDGR_TXT_DOMAIN ); ?>
			</label>
			<input type="text" name="ldgr-user-last-name" id="ldgr-user-last-name" required />
		</div>

		<div class="ldgr-form-field">
			<label for="ldgr-user-username">
				<?php esc_html_e( 'Username', WDM_LDGR_TXT_DOMAIN ); ?>
			</label>
			<input type="text" name="ldgr-user-username" id="ldgr-user-username" required />
		</div>

		<div class="ldgr-form-field">
			<label for="ldgr-user-email">
				<?php esc_html_e( 'User Email', WDM_LDGR_TXT_DOMAIN ); ?>
			</label>
			<input type="email" name="ldgr-user-email" id="ldgr-user-email" required />
		</div>

		<div class="ldgr-form-field">
			<label for="ldgr-user-password">
				<?php esc_html_e( 'User Password', WDM_LDGR_TXT_DOMAIN ); ?>
			</label>
			<input type="password" name="ldgr-user-password" id="ldgr-user-password" required />
		</div>

		<div class="ldgr-form-field">
			<label for="ldgr-user-confirm-password">
				<?php esc_html_e( 'Confirm Password', WDM_LDGR_TXT_DOMAIN ); ?>
			</label>
			<input type="password" name="ldgr-user-confirm-password" id="ldgr-user-confirm-password" required />
		</div>

		<?php
			/**
			 * Allow 3rd party addons to add custom fields before group code field.
			 *
			 * @since 4.1.2
			 */
			do_action( 'ldgr_action_registration_form_before_group_code_field' );
		?>

		<div class="ldgr-form-field">
			<label for="ldgr-user-group-code">
				<?php esc_html_e( 'Group Code', WDM_LDGR_TXT_DOMAIN ); ?>
			</label>
			<input type="text" name="ldgr-user-group-code" id="ldgr-user-group-code" autocomplete="off" required />
		</div>

		<?php wp_nonce_field( 'ldgr-group-code-registration-form', 'ldgr_nonce' ); ?>

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

		<button type="submit" id="ldgr-user-reg-form-submit"><?php esc_html_e( 'Submit', WDM_LDGR_TXT_DOMAIN ); ?></button>

	</form>
	<div class="ldgr-black-screen">
		<span style="margin-bottom:10px;"><?php esc_html_e( 'Please wait...', WDM_LDGR_TXT_DOMAIN ); ?></span>
		<span class="dashicons dashicons-update spin"></span>
	</div>
	<?php do_action( 'ldgr_action_after_group_code_registration_form' ); ?>

</div>
