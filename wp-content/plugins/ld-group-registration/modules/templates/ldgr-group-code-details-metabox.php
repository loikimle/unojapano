<?php
/**
 * Group Code Details Metabox Template
 *
 * @var mixed $group_code_from
 * @var mixed $group_code_to
 * @var mixed $group_code_enrollment_count
 * @var mixed $group_code_related_groups
 * @var mixed $group_code_validation_check
 * @var mixed $code_enrolled_users
 *
 * @since 4.1.0
 */
?>
<div class="ldgr-group-code-metabox-details">
	<table>
		<tbody>
			<!-- Date Range -->
			<tr>
				<th>
					<label for="ldgr-code-date-range-from">
						<?php esc_html_e( 'From Date', WDM_LDGR_TXT_DOMAIN ); ?>
					</label>
				</th>
				<td>
					<input type="text" name="ldgr-code-date-range-from" class="ldgr-code-date-range-from" autocomplete="off" value="<?php echo esc_html( ldgr_date_in_site_timezone( $group_code_from ) ); ?>" readonly />
				</td>
			</tr>
			<tr>
				<th>
					<label for="ldgr-code-date-range-to">
						<?php esc_html_e( 'To Date', WDM_LDGR_TXT_DOMAIN ); ?>
					</label>
				</th>
				<td>
					<input type="text" name="ldgr-code-date-range-to" class="ldgr-code-date-range-to" autocomplete="off" value="<?php echo esc_html( ldgr_date_in_site_timezone( $group_code_to ) ); ?>" readonly />
				</td>
			</tr>
			<!-- Groups List -->
			<tr>
				<th>
					<label for="ldgr-code-groups">
						<?php esc_html_e( 'Group', WDM_LDGR_TXT_DOMAIN ); ?>
					</label>
				</th>
				<td>
					<select name="ldgr-code-groups" class="ldgr-code-groups">
						<?php foreach ( $group_list as $group_id ) : ?>
							<option value="<?php echo esc_attr( $group_id ); ?>" <?php selected( $group_code_related_groups, $group_id ); ?>>
								<?php echo esc_html( get_the_title( $group_id ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<!-- Number of Enrollments -->
			<tr>
				<th>
					<label for="ldgr-code-limit">
						<?php esc_html_e( 'Number of Enrollments', WDM_LDGR_TXT_DOMAIN ); ?>
					</label>
				</th>
				<td>
					<input type="number" name="ldgr-code-limit" class="ldgr-code-limit" min="1" value="<?php echo esc_html( $group_code_enrollment_count ); ?>" required/>
				</td>
			</tr>

			<?php if ( ! empty( $code_enrolled_users ) ) : ?>
				<tr>
					<th>
						<label>
							<?php esc_html_e( 'Number of Enrolled Users', WDM_LDGR_TXT_DOMAIN ); ?>
						</label>
					</th>
					<td>
						<strong><span><?php echo esc_attr( count( $code_enrolled_users ) ); ?></span></strong>
					</td>
				</tr>
			<?php endif; ?>
			<!-- Validation Rules -->
			<tr>
				<th>
					<span>
						<?php esc_html_e( 'Validation Rules', WDM_LDGR_TXT_DOMAIN ); ?>
					</span>
				</th>
				<td>
					<label for="ldgr-code-validation-check" class="ldgr-switch">
						<input type="checkbox" name="ldgr-code-validation-check" class="ldgr-code-validation-check" <?php checked( $group_code_validation_check, 'on' ); ?>/>
						<span class="ldgr-switch-slider round"></span>
					</label>
				</td>
			</tr>
			<tr class="ldgr-code-validation" <?php echo ( 'on' != $group_code_validation_check ) ? 'style="display:none;"' : ''; ?>>
				<th>
					<label for="ldgr-code-ip-validation">
						<span><?php esc_html_e( 'IP Address', WDM_LDGR_TXT_DOMAIN ); ?></span>
						<span class="dashicons dashicons-info"></span>
						<span class="ldgr-tooltip"><?php esc_html_e( 'Enter IP address to validate during enrollment ( eg. 10.10.10.10 )', WDM_LDGR_TXT_DOMAIN ); ?></span>
					</label>
				</th>
				<td>
					<input name="ldgr-code-ip-validation" class="ldgr-code-ip-validation" type="text" value="<?php echo esc_html( $group_code_ip_validation ); ?>"/>
				</td>
			</tr>
			<tr class="ldgr-code-validation" <?php echo ( 'on' != $group_code_validation_check ) ? 'style="display:none;"' : ''; ?>>
				<th>
					<label for="ldgr-code-domain-validation">
						<span><?php esc_html_e( 'Domain Name', WDM_LDGR_TXT_DOMAIN ); ?></span>
						<span class="dashicons dashicons-info"></span>
						<span class="ldgr-tooltip"><?php esc_html_e( 'Enter email domain name to validate during enrollment ( eg. gmail.com )', WDM_LDGR_TXT_DOMAIN ); ?></span>
					</label>
				</th>
				<td>
					<input name="ldgr-code-domain-validation" class="ldgr-code-domain-validation" type="text" value="<?php echo esc_html( $group_code_domain_validation ); ?>"/>
				</td>
			</tr>
		</tbody>
	</table>

	<?php wp_nonce_field( 'ldgr-create-group-code-' . $user_id, 'ldgr_nonce' ); ?>
</div>
