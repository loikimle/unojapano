<?php
/**
 * Admin View: Page - Mailchimp Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ur-export-users-page">
	<div class="nav-tab-content">
		<div class="nav-tab-inside">
			<div class="mailchimp-wrapper">
				<div id="mailchimp_div" class="postbox">
					<h3 class="hndle"><?php esc_html_e( 'MailChimp Accounts Settings', 'user-registration-mailchimp' ); ?></h3>

					<div class="inside">

						<p>
							<label class="ur-label"><?php esc_html_e( 'MailChimp API Key', 'user-registration-mailchimp' ); ?></label>
							<input type="text" name="ur_mailchimp_api_key" id="ur_mailchimp_api_key" placeholder="<?php esc_attr_e( 'Enter the MailChimp API Key', 'user-registration-mailchimp' ); ?>" class="ur-input forms-list"/>
						</p>
						<p>
							<label class="ur-label"><?php esc_html_e( 'MailChimp Account Name', 'user-registration-mailchimp' ); ?></label>
							<input type="text" name="ur_mailchimp_account_name" id="ur_mailchimp_account_name" placeholder="<?php esc_attr_e( 'Enter a Account Name', 'user-registration-mailchimp' ); ?>" class="ur-input forms-list"/>
						</p>

						<div class="publishing-action">
							<button type="button" class="button button-primary ur_mailchimp_account_action_button" name="user_registration_mailchimp_account"><?php esc_attr_e( 'Connect', 'user-registration-mailchimp' ); ?></button>
						</div>


					</div>
				</div>
				<?php
				$connected_accounts = get_option( 'ur_mailchimp_accounts', array() );
				if ( ! empty( $connected_accounts ) ) {
					?>
				<div id="mailchimp_accounts" class="postbox">
						<table class="ur-account-list-table">
							<tbody>
							<?php
							foreach ( $connected_accounts as $key => $list ) {
								?>
										<tr>
											<td><strong><?php echo sanitize_text_field( $list['label'] ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?></strong></td>
											<td>Connected on <?php echo $list['date']; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?></td>
											<td>
												<a href='#' class='disconnect ur-mailchimp-disconnect-account' data-key='<?php echo $list['api_key']; ?>' ><?php echo esc_html__( 'Disconnect', 'user-regisration-mailchimp' ); ?></a>
											</td>
										</tr>
										<?php

							}
							?>
							</tbody>
						</table>
				</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</div>
