<?php
/**
 * New users tab contents display template
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates/ldgr-group-users/tabs
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<?php if ( ! $need_to_restrict ) : ?>
	<?php do_action( 'wdm_after_enrolled_users_detail', $group_id, $group_limit ); ?>

	<div id="tab-2" class="tab-content">
		<?php
			$group_limit_count = get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
			$is_unlimited = get_post_meta( $group_id, 'ldgr_unlimited_seats', true );
		?>
		<?php if ( $group_limit_count > 0 || $is_unlimited ) : ?>
			<ul class="wdm-adduser-tabs">
				<li class="wdm-tab-link current" data-tab="tab-add-user">
					<a href="#">
						<span class="dashicons dashicons-admin-users"></span>
						<span><?php echo esc_html__( 'Add User', 'wdm_ld_group' ); ?></span>
					</a>
				</li>
				<li class="wdm-tab-link" data-tab="tab-upload-csv">
					<a href="#">
						<span class="dashicons dashicons-upload"></span>
						<span><?php echo esc_html__( 'Upload Users', 'wdm_ld_group' );?></span>
					</a>
				</li>
			</ul>
			<div id="tab-upload-csv" class="wdm-tab-content">
				<div class="wdm_upload">
					<form enctype="multipart/form-data" name="import-upload-form" id="import-upload-form" method="post" class="wp-upload-form" >
						<div class="wdm-new-user-content">
							<div class="wdm-upload-handler">
								<label for="uploadcsv" class="button"><?php echo __( 'Choose File', 'wdm_ld_group' ); ?></label>
								<input type="file" id="uploadcsv" name="uploadcsv" size="25" />

								<div id="ldgr-upload-file-info">
									<p id="ldgr-upload-file-name"><?php esc_html_e('File Name:', 'wdm_ld_group') ?>
										<span></span>
									</p>
									<p id="ldgr-upload-file-size"><?php esc_html_e('File Size:', 'wdm_ld_group') ?>
										<span></span>
									</p>
								</div>

								<div class="wdm-download-csv">
									<span>
										<?php echo esc_html__( 'Download sample csv', 'wdm_ld_group' ); ?>
									</span>
									<a download href="<?php echo esc_url( plugins_url( 'modules/sample/demo.csv', WDM_LDGR_PLUGIN_FILE ) ); ?>" title="<?php echo esc_html__( 'Download sample csv', 'wdm_ld_group' ); ?>">
										<span class="dashicons dashicons-download"></span>
									</a>
								</div>
								<input type='hidden' name='wdm_upload_check' value='1'>
								<input type="hidden" name="wdm_group_id" value='<?php echo $group_id; ?>'>
								<input type='submit' name='save_data' value='<?php echo esc_html__( 'Upload', 'wdm_ld_group' ); ?>' id='wdm_submit_upload'>
							</div>
						</div>
						<?php wp_nonce_field( 'wdm_ldgr_csv_upload_enroll', 'wdm_ldgr_csv_upload_enroll_field' ); ?>
					</form>
				</div>
			</div>
			<div id="tab-add-user" class="wdm-tab-content current">
				<form id='wdm_add_user_fields' method='post'>
					<input type='hidden' name='wdm_add_user_check' value='1'>
					<input type='hidden' name='wdm_group_id' value='<?php echo $group_id; ?>'>
					<table id='add_user_data' >
						<thead>
							<tr>
								<th><?php echo esc_html__( 'First Name', 'wdm_ld_group' ); ?></th>
								<th><?php echo esc_html__( 'Last Name', 'wdm_ld_group' ); ?></th>
								<th><?php echo esc_html__( 'Email', 'wdm_ld_group' ); ?></th>
								<th><?php echo esc_html__( 'Action', 'wdm_ld_group' ); ?></th>
							</tr>
						</thead>
						<tbody id='add_details'>
							<tr id='wdm_members_name'>
								<td data-title="First Name">
									<input type='text' name='wdm_members_fname[]' required placeholder="<?php echo esc_html__( 'First Name', 'wdm_ld_group' ); ?>">
								</td>
								<td data-title="Last Name">
									<input type='text' name='wdm_members_lname[]' placeholder="<?php echo esc_html__( 'Last Name', 'wdm_ld_group' ); ?>">
								</td>
								<td data-title="Email">
									<input type='email' name='wdm_members_email[]' required placeholder="<?php echo esc_html__( 'Email', 'wdm_ld_group' ); ?>">
								</td>
								<td id="wdm-clear">
									<a href="#" class="wdm-add-user-btn"
									title=<?php esc_html_e("Add", "wdm_ld_group"); ?>>
										<span class="dashicons dashicons-plus"></span>
									</a>
									<a href="#" class="wdm_clear_data"
									title="<?php esc_html_e("Clear", "wdm_ld_group"); ?>">
										<span>
											<img src="<?php echo $clear_icon; ?>" alt="<?php esc_html_e("Clear", "wdm_ld_group"); ?>">
										</span>
									</a>
								</td>
							</tr>
						</tbody>
					</table>
				<?php if ( $group_limit_count > 1 ) { ?>
						<input type='button' class='wdm_add_users' value='<?php echo esc_html__( 'Add more', 'wdm_ld_group' ); ?>'>
					<?php } ?>
					<input type='submit' value='<?php echo esc_html__( 'Submit', 'wdm_ld_group' ); ?>' id='wdm_submit'>
				</form>
			</div>
		<?php endif; ?>
	</div> <!-- End of Second Tab -->
<?php endif; ?>
