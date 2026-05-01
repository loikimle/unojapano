<!-- Wrapped with WP Widget UI -->
<div class="urcr-settings-widget postbox urcr-conditional-logic-item user-registration-card"  data-store-id="{{{ID}}}" data-type="group">
	<div class="urcr-settings-widget-header user-registration-card__header ur-border-0">
		<div class="hndle ui-sortable-handle ur-d-flex ur-align-items-center ur-border-0">
			<h4 class="urcr-widget-header-label ur-h4 ur-m-0"><?php esc_html_e( 'Sub Logic Group', 'user-registration-content-restriction' ); ?></h4>
			<div class="user-registration-button-group urcr-logic-gates-container ur-ml-2">
				<span class="urbg-item button button-tertiary urcr-logic-gate urcr-logic-gate-{{{ID}}} {{{logic_gate:OR}}}" data-value="OR"><?php esc_html_e( 'OR', 'user-registration-content-restriction' ); ?></span>
				<span class="urbg-item button button-tertiary urcr-logic-gate urcr-logic-gate-{{{ID}}} {{{logic_gate:AND}}}" data-value="AND"><?php esc_html_e( 'AND', 'user-registration-content-restriction' ); ?></span>
				<span class="urbg-item button button-tertiary urcr-logic-gate urcr-logic-gate-{{{ID}}} {{{logic_gate:NOT}}}" data-value="NOT"><?php esc_html_e( 'NOT', 'user-registration-content-restriction' ); ?></span>
			</div>
			<div class="ur-d-flex ur-ml-auto">
				<select class="button button-tertiary urcr-add-new-conditional-logic-field urcr-constant-selection-enabled">
					<option class="urcr-logic-field-placeholder" selected hidden disabled>+ <?php esc_html_e( 'Add Field', 'user-registration-content-restriction' ); ?></option>
					<optgroup label="<?php esc_html_e( 'User Based', 'user-registration-content-restriction' ); ?>">
						<option value="roles"><?php esc_html_e( 'Roles', 'user-registration-content-restriction' ); ?></option>
						<option value="user_registered_date"><?php esc_html_e( 'User Registered Date', 'user-registration-content-restriction' ); ?></option>
						<option value="user_state"><?php esc_html_e( 'User State', 'user-registration-content-restriction' ); ?></option>
					</optgroup>
					<optgroup label="<?php esc_html_e( 'User Assets Based', 'user-registration-content-restriction' ); ?>">
						<option value="email_domain"><?php esc_html_e( 'Email Domain', 'user-registration-content-restriction' ); ?></option>
						<option value="post_count"><?php esc_html_e( 'Minimum Public Posts Count', 'user-registration-content-restriction' ); ?></option>
					</optgroup>
					<optgroup label="<?php esc_html_e( 'Others', 'user-registration-content-restriction' ); ?>">
						<option value="capabilities"><?php esc_html_e( 'Capabilities', 'user-registration-content-restriction' ); ?></option>
						<option value="registration_source"><?php esc_html_e( 'User Registration Source', 'user-registration-content-restriction' ); ?></option>
					</optgroup>
				</select>
				<button type="button" class="button button-secondary urcr-add-new-conditional-logic-group ur-mx-1">+ <?php esc_html_e( 'Add Group', 'user-registration-content-restriction' ); ?></button>
				<button type="button" title="Remove" class="button button-icon button-danger urcrcl-trash urcr-trash ur-mr-1">
					<span class="dashicons dashicons-trash"></span>
				</button>
				<button type="button" class="handlediv"><span class="toggle-indicator"></span></button>
			</div>
		</div>
	</div>
	<div class="inside urcr-cld-wrapper user-registration-card__body ur-border-top ur-pt-2" id="urcr-cld-wrapper-{{{ID}}}">
		<div class="main urcr-conditional-logic-definitions" id="urcr-conditional-logic-definitions-{{{ID}}}">
		</div>
	</div>
</div>
