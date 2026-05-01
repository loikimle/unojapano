<div id="dashboard-widgets-wrap">
	<div id="dashboard-widgets" class="metabox-holder">
		<div class="meta-box-sortables">
			<div class="urcr-settings-widget postbox user-registration-card">
				<div class="urcr-settings-widget-header user-registration-card__header ur-border-0">
					<div class="hndle ui-sortable-handle ur-d-flex ur-align-items-center ur-border-0">
						<h4 class="ur-h4 ur-m-0"><?php esc_html_e( 'Choose actions for this rule?', 'user-registration-content-restriction' ); ?></h4>
						<div class="ur-d-flex ur-ml-auto">
							<button type="button" class="handlediv"><span class="toggle-indicator"></span></button>
						</div>
					</div>
				</div>
				<div class="inside user-registration-card__body ur-border-top ur-pt-2">
					<div class="main urcr-rule-actions-container">
						<!-- URCR Actions -->
						<div class="urcr-label-input-pair urcr-rule-action ur-row ur-align-items-center ur-form-group">
							<label class="urcr-label-container ur-col-4">
								<span class="urcr-target-content-label"><?php esc_html_e( 'Action', 'user-registration-content-restriction' ); ?></span>
								<span class="urcr-puncher"></span>
								<span class="user-registration-help-tip" data-tip="<?php esc_html_e( 'Action to perform for restricting the specified contents', 'user-registration-content-restriction' ); ?>"></span>
							</label>
							<div class="urcr-input-container ur-col-8">
								<select class="urcr-rule-action-type-input urcr-enhanced-select2" data-placeholder="<?php esc_html_e( 'Select an Action', 'user-registration-content-restriction' ); ?>">
									<option></option>
									<option value="message"><?php esc_html_e( 'Show Message', 'user-registration-content-restriction' ); ?></option>
									<option value="redirect"><?php esc_html_e( 'Redirect', 'user-registration-content-restriction' ); ?></option>
									<option value="redirect_to_local_page"><?php esc_html_e( 'Redirect to a Local Page', 'user-registration-content-restriction' ); ?></option>
									<option value="ur-form"><?php esc_html_e( 'Show UR Form', 'user-registration-content-restriction' ); ?></option>
									<option value="shortcode"><?php esc_html_e( 'Render Shortcode', 'user-registration-content-restriction' ); ?></option>
								</select>
							</div>
						</div>
						<div class="urcr-title-body-pair urcr-rule-action-input-container urcrra-message-input-container ur-row ur-form-group" style="display:none;">
							<div class="urcr-title ur-col-4">
								<?php esc_html_e( 'Restriction Message', 'user-registration-content-restriction' ); ?>
							</div>
							<div class="urcr-body ur-col-8">
							<?php
								$value    = esc_html__( 'You do not have permission to access this content.', 'user-registration-content-restriction' );
								$settings = array(
									'quicktags'  => array( 'buttons' => 'em,strong,link' ),
									'tinymce'    => array(
										'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
										'theme_advanced_buttons2' => '',
									),
									'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
								);

								wp_editor( $value, 'urcr-rule-action-message-input', $settings );
								?>
							</div>
						</div>
						<div class="urcr-title-body-pair urcr-rule-action-input-container urcrra-redirect-input-container ur-row ur-form-group" style="display:none;">
							<div class="urcr-title ur-col-4">
								<?php esc_html_e( 'Redirection URL', 'user-registration-content-restriction' ); ?>
							</div>
							<div class="urcr-body ur-col-8">
								<input type="url" class="urcr-input" placeholder="<?php esc_attr_e( 'Enter a URL to redirect to...' ); ?>"/>
								<div class="urcr-notice urcr-notice-warning">
									<p><b><?php esc_html_e( 'Warning', 'user-registration-content-restriction' ); ?>:</b> <?php esc_html_e( 'Empty redirect URL will redirect to the admin page.', 'user-registration-content-restriction' ); ?></p>
								</div>
							</div>
						</div>
						<div class="urcr-title-body-pair urcr-rule-action-input-container urcrra-redirect-to-local-page-input-container ur-row ur-form-group" style="display:none;">
							<div class="urcr-title ur-col-4">
								<?php esc_html_e( 'Redirect to a local page', 'user-registration-content-restriction' ); ?>
							</div>
							<div class="urcr-body ur-col-8"></div>
						</div>
						<div class="urcr-title-body-pair urcr-rule-action-input-container urcrra-ur-form-input-container ur-row ur-form-group" style="display:none;">
							<div class="urcr-title ur-col-4">
								<?php esc_html_e( 'Display User Registration Form', 'user-registration-content-restriction' ); ?>
							</div>
							<div class="urcr-body ur-col-8"></div>
						</div>
						<div class="urcr-title-body-pair urcr-rule-action-input-container urcrra-shortcode-input-container ur-row ur-form-group" style="display:none;">
							<div class="urcr-title ur-col-4">
								<?php esc_html_e( 'Render a Shortcode', 'user-registration-content-restriction' ); ?>
							</div>
							<div class="urcr-body ur-col-8"></div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>
