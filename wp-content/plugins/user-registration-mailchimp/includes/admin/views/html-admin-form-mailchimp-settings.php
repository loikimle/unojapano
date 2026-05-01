<?php
/**
 * Admin View: Form - Mailchimp Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="mailchimp-settings" >
	<h3><?php echo esc_html__( 'MailChimp', 'user-registration-mailchimp' ); ?></h3>
	<?php
	if ( ! empty( $connected_accounts ) ) {
		?>
	<div class="ur-mailchimp-add-button-wrapper">
		<button class="ur-mailchimp-add-connection-button button button-primary"><?php echo esc_html__( 'Add New Connection', 'user-registration-mailchimp' ); ?></button>
	</div>
	<div class="ur-mailchimp_settings-container">
		<div class="ur-content-wrap ur-mailchimp-settings-wrapper">
			<div class="ur-mailchimp-settings">
				<?php
				foreach ( $integration_settings as $connection ) {
					?>
						<div class="user-registration-card ur-mb-2 ur-mailchimp-settings-content-wrap" data-connection-id="<?php echo esc_attr( $connection['connection_id'] ); ?>">
							<div class="user-registration-card__header ur-d-flex ur-align-items-center ur-p-3">
								<h4 class="user-registration-card__title">
								<?php echo esc_html__( $connection['name'], 'user-registration-mailchimp' ); ?>
								</h4>
								<div class="user-registration-card__button">
									<button class="user-registration-card__toggle button button-secondary button-icon">
										<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="6 9 12 15 18 9"></polyline></svg>
									</button>
									<button class="user-registration-card__remove button button-secondary button-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M18 6 6 18M6 6l12 12"/>
										</svg>
									</button>
								</div>
							</div>
							<div class="user-registration-card__body ur-p-3" style="display:none;">
								<p class=""><?php echo esc_html__( 'Select Account', 'user-registration-mailchimp' ); ?> </p>
								<select id="ur_mailchimp_account" class="">
								<?php
								foreach ( $connected_accounts as $key => $value ) {
									$selected = $value['api_key'] === $connection['api_key'] ? 'selected=selected' : '';
									?>
											<option value="<?php echo esc_attr( $value['api_key'] ); ?>" <?php echo esc_attr( $selected ); ?> ><?php echo esc_html( $value['label'] ); ?></option>
										<?php
								}
								?>
								</select>
								<?php echo sprintf( '%s', $this->output_account_lists( $form_id, $connection ) ); ?>
							</div>
						</div>
						<?php

				}
				?>
			</div>
		</div>
	</div>
		<?php
	} else {
		?>
		<p class='user-registration-notice user-registration-notice-info'>
		<?php
		echo wp_kses_post(
			sprintf(
			/* translators: %s: payment settings URL */
				__( 'Please add a Connection from <a href="%s">settings panel</a>.', 'user-registration-mailchimp' ),
				esc_url( admin_url( 'admin.php?page=user-registration-settings&tab=user-registration-mailchimp' ) )
			)
		);
		?>
		</p>
		<?php
	}
	?>
</div>
