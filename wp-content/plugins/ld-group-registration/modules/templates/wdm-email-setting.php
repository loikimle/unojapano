<?php
// Email Template Callback
?>
	<?php do_action( 'ldgr_action_email_settings_start' ); ?>
	<div>
		<h2><?php _e( 'Group Registration Email Setting', WDM_LDGR_TXT_DOMAIN ); ?></h2>

		<form name="wdm-gr-email-frm" method="POST">

			<?php do_action( 'ldgr_action_email_settings_form_start' ); ?>

			<div class="accordion"><b><?php _e( 'Admin Email Setting', WDM_LDGR_TXT_DOMAIN ); ?></b></div>
			<div class="panel">
				<br><table>
					<tr>
						<th colspan="3" class="wdm-email-head"></th>
					</tr>
					<tr>
						<td class="wdm-label">
							<label for="wdm-gr-admin-email"><?php _e( 'Email(s) : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td colspan="2">
							<input type="email" name="wdm-gr-admin-email" id="wdm-gr-admin-email" size="50" value="<?php echo $admin_email; ?>" multiple>
							<span class="wdm-help-txt"><?php _e( 'Enter email address(s) to whom admin emails should go. Add multiple as comma separated. <br/> Default : Admin Email', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
					</tr>
				</table><br>
			</div><br>
			<div class="accordion">
				<label class="wdm-switch">
					<input
						type="checkbox"
						name="wdm-gr-gl-rmvl-enable"
						<?php echo ( $wdm_gr_gl_rmvl_enable != 'off' ) ? 'checked' : ''; ?>
						>
					<span class="wdm-slider round"></span>
				</label>
				<b><?php _e( 'When admin accepts the user removal request (Group Leader) ', WDM_LDGR_TXT_DOMAIN ); ?></b>
			</div>
			<div class="panel">
				<br><table>
					<tr>
						<td class="wdm-label">
							<label for="wdm-gr-gl-rmvl-sub"><?php _e( 'Subject : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<input type="text" name="wdm-gr-gl-rmvl-sub" id="wdm-gr-gl-rmvl-sub" size="50" value="<?php echo $gl_rmvl_sub; ?>">
							<span class="wdm-help-txt"><?php _e( 'Enter Subject for Email sent to Group Leader when Admin accepts removal request <br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
					</tr>
					<tr>
						<td class="wdm-label">
							<label for="wdm-gr-gl-rmvl-body"><?php _e( 'Body : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<?php
								$editor_settings = array(
									// 'wpautop'=>true,
									'media_buttons'    => false,
									'drag_drop_upload' => false,
									'textarea_rows'    => 15,
									'textarea_name'    => 'wdm-gr-gl-rmvl-body',
								);
								wp_editor(
									stripslashes( $gl_rmvl_body ),
									'wdm-gr-gl-rmvl-body',
									$editor_settings
								);
								?>
							<span class="wdm-help-txt"><?php _e( 'Enter Content for Email sent to Group Leader(s) when Admin accepts removal request <br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
						<td class="wdm-var-sec">
							<div>
								<span class="wdm-var-head"><?php _e( 'Available Variables', WDM_LDGR_TXT_DOMAIN ); ?></span>
								<ul>
									<li><b>{group_title}</b> : <?php _e( 'Displays Group Title', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_email}</b> : <?php _e( 'Displays User Email who is removed from Group', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{group_leader_name}</b> : <?php _e( 'Displays Group Leaders Name', WDM_LDGR_TXT_DOMAIN ); ?></li>
								</ul>
							</div>
						</td>
					</tr>
				</table><br>
			</div><br>
			<div class="accordion">
				<label class="wdm-switch">
					<input
						type="checkbox"
						name="wdm-gr-gl-acpt-enable"
						<?php echo ( $wdm_gr_gl_acpt_enable != 'off' ) ? 'checked' : ''; ?>
					>
					<span class="wdm-slider round"></span>
				</label>
				<b><?php _e( 'When admin rejects the user removal request (Group Leader) ', WDM_LDGR_TXT_DOMAIN ); ?></b>
			</div>
			<div class="panel">
				<br><table>
					<tr>
						<td class="wdm-label">
							<label for="wdm-gr-gl-acpt-sub"><?php _e( 'Subject : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<input type="text" name="wdm-gr-gl-acpt-sub" id="wdm-gr-gl-acpt-sub" size="50" value="<?php echo $gl_acpt_sub; ?>">
							<span class="wdm-help-txt"><?php _e( 'Enter Subject for Email sent to Group Leader(s) when Admin rejects removal request <br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
					</tr>
					<tr>
						<td class="wdm-label">
							<label for="wdm-gr-gl-acpt-body"><?php _e( 'Body : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<?php
								$editor_settings = array(
									// 'wpautop'=>true,
									'media_buttons'    => false,
									'drag_drop_upload' => false,
									'textarea_rows'    => 15,
									'textarea_name'    => 'wdm-gr-gl-acpt-body',
								);
								wp_editor(
									stripslashes( $gl_acpt_body ),
									'wdm-gr-gl-acpt-body',
									$editor_settings
								);
								?>
							<span class="wdm-help-txt"><?php _e( 'Enter Content for Email sent to Group Leader(s) when Admin rejects removal request <br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
						<td class="wdm-var-sec">
							<div>
								<span class="wdm-var-head"><?php _e( 'Available Variables', WDM_LDGR_TXT_DOMAIN ); ?></span>
								<ul>
									<li><b>{group_title}</b> : <?php _e( 'Displays Group Title', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_email}</b> : <?php _e( 'Displays User Email who is removed from Group', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{group_leader_name}</b> : <?php _e( 'Displays Group Leaders Name', WDM_LDGR_TXT_DOMAIN ); ?></li>
								</ul>
							</div>
						</td>
					</tr>
				</table><br>
			</div><br>
			<div class="accordion">
				<label class="wdm-switch">
					<input
						type="checkbox"
						name="wdm-a-rq-rmvl-enable"
						<?php echo ( $wdm_a_rq_rmvl_enable != 'off' ) ? 'checked' : ''; ?>
					>
					<span class="wdm-slider round"></span>
				</label>
				<b><?php _e( 'When Group Leader requests to remove user (Admin) ', WDM_LDGR_TXT_DOMAIN ); ?></b>
			</div>
			<div class="panel">
				<br><table>
					<tr>
						<td class="wdm-label">
							<label for="wdm-a-rq-rmvl-sub"><?php _e( 'Subject : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<input type="text" name="wdm-a-rq-rmvl-sub" id="wdm-a-rq-rmvl-sub" size="50" value="<?php echo $a_rq_rmvl_sub; ?>">
							<span class="wdm-help-txt"><?php _e( 'Enter Subject for Email sent to Admin when Group Leader request to remove user<br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
					</tr>
					<tr>
						<td class="wdm-label">
							<label for="wdm-a-rq-rmvl-body"><?php _e( 'Body : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<?php
								$editor_settings = array(
									// 'wpautop'=>true,
									'media_buttons'    => false,
									'drag_drop_upload' => false,
									'textarea_rows'    => 15,
									'textarea_name'    => 'wdm-a-rq-rmvl-body',
								);
								wp_editor(
									stripslashes( $a_rq_rmvl_body ),
									'wdm-a-rq-rmvl-body',
									$editor_settings
								);
								?>
							<span class="wdm-help-txt"><?php _e( 'Enter Content for Email sent to Admin when Group Leader request to remove user<br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
						<td class="wdm-var-sec">
							<div>
								<span class="wdm-var-head"><?php _e( 'Available Variables', WDM_LDGR_TXT_DOMAIN ); ?></span>
								<ul>
									<li><b>{group_title}</b> : <?php _e( 'Displays Group Title', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_email}</b> : <?php _e( 'Displays User Email who is removed from Group', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{group_edit_link}</b> : <?php _e( 'Displays Group Edit Link', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{group_leader_name}</b> : <?php _e( 'Displays Group Leaders Name', WDM_LDGR_TXT_DOMAIN ); ?></li>
								</ul>
							</div>
						</td>
					</tr>
				</table><br>
			</div><br>
			<div class="accordion">
				<label class="wdm-switch">
					<input
						type="checkbox"
						name="wdm-u-add-gr-enable"
						<?php echo ( $wdm_u_add_gr_enable != 'off' ) ? 'checked' : ''; ?>
					>
					<span class="wdm-slider round"></span>
				</label>
				<b><?php _e( 'When User gets added into Group (User) ', WDM_LDGR_TXT_DOMAIN ); ?></b>
			</div>
			<div class="panel">
				<br><table>
					<tr>
						<td class="wdm-label">
							<label for="wdm-u-add-gr-sub"><?php _e( 'Subject : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<input type="text" name="wdm-u-add-gr-sub" id="wdm-u-add-gr-sub" size="50" value="<?php echo $u_add_gr_sub; ?>">
							<span class="wdm-help-txt"><?php _e( 'Enter Subject for Email sent to User when added in a group <br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
					</tr>
					<tr>
						<td class="wdm-label">
							<label for="wdm-u-add-gr-body"><?php _e( 'Body : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<?php
								$editor_settings = array(
									// 'wpautop'=>true,
									'media_buttons'    => false,
									'drag_drop_upload' => false,
									'textarea_rows'    => 15,
									'textarea_name'    => 'wdm-u-add-gr-body',
								);
								wp_editor(
									stripslashes( $u_add_gr_body ),
									'wdm-u-add-gr-body',
									$editor_settings
								);
								?>
							<span class="wdm-help-txt"><?php _e( 'Enter Subject for Email sent to User when added in a group <br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
						<td class="wdm-var-sec">
							<div>
								<span class="wdm-var-head"><?php _e( 'Available Variables', WDM_LDGR_TXT_DOMAIN ); ?></span>
								<ul>
									<li><b>{group_title}</b> : <?php _e( 'Displays Group Title', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{course_list}</b> : <?php _e( 'Displays Course List', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{group_leader_name}</b> : <?php _e( 'Displays Group Leader Name', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_first_name}</b> : <?php _e( "Displays User's First Name", WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_last_name}</b> : <?php _e( "Displays User's Last Name", WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{login_url}</b> : <?php _e( 'Displays Login URL', WDM_LDGR_TXT_DOMAIN ); ?></li>
								</ul>
							</div>
						</td>
					</tr>
				</table><br>
			</div><br>
			<div class="accordion">
				<label class="wdm-switch">
					<input
						type="checkbox"
						name="wdm-u-ac-crt-enable"
						<?php echo ( $wdm_u_ac_crt_enable != 'off' ) ? 'checked' : ''; ?>
					>
					<span class="wdm-slider round"></span>
				</label>
				<b><?php _e( 'When User accounts get created and added into Group (User) ', WDM_LDGR_TXT_DOMAIN ); ?></b>
			</div>
			<div class="panel">
				<br><table>
					<tr>
						<td class="wdm-label">
							<label for="wdm-u-ac-crt-sub"><?php _e( 'Subject : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<input type="text" name="wdm-u-ac-crt-sub" id="wdm-u-ac-crt-sub" size="50" value="<?php echo $u_ac_crt_sub; ?>">
							<span class="wdm-help-txt"><?php _e( 'Enter Subject for Email sent to User when when User accounts get created and added into Group <br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
					</tr>
					<tr>
						<td class="wdm-label">
							<label for="wdm-u-ac-crt-body"><?php _e( 'Body : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<?php
								$editor_settings = array(
									// 'wpautop'=>true,
									'media_buttons'    => false,
									'drag_drop_upload' => false,
									'textarea_rows'    => 15,
									'textarea_name'    => 'wdm-u-ac-crt-body',
								);
								wp_editor(
									stripslashes( $u_ac_crt_body ),
									'wdm-u-ac-crt-body',
									$editor_settings
								);
								?>
							<span class="wdm-help-txt"><?php _e( 'Enter Content for Email sent to User when when User accounts get created and added into Group <br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
						<td class="wdm-var-sec">
							<div>
								<span class="wdm-var-head"><?php _e( 'Available Variables', WDM_LDGR_TXT_DOMAIN ); ?></span>
								<ul>
									<li><b>{group_title}</b> : <?php _e( 'Displays Group Title', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{site_name}</b> : <?php _e( 'Displays Site Name', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_first_name}</b> : <?php _e( "Displays User's First Name", WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_last_name}</b> : <?php _e( "Displays User's Last Name", WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_email}</b> : <?php _e( "Displays User's Email", WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_password}</b> : <?php _e( "Displays User's Password", WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{course_list}</b> : <?php _e( 'Displays Course List', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{group_leader_name}</b> : <?php _e( 'Displays Group Leaders Name', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{login_url}</b> : <?php _e( 'Displays Login URL', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{reset_password}</b> : <?php _e( 'Displays Reset Password link for user', 'wdm_ld_group' ); ?></li>
								</ul>
							</div>
						</td>
					</tr>
				</table><br>
			</div><br>
			<div class="accordion">
				<label class="wdm-switch">
					<input
						type="checkbox"
						name="wdm-a-u-ac-crt-enable"
						<?php echo ( $wdm_a_u_ac_crt_enable != 'off' ) ? 'checked' : ''; ?>
					>
					<span class="wdm-slider round"></span>
				</label>
				<b><?php _e( 'When User account gets created (Admin) ', WDM_LDGR_TXT_DOMAIN ); ?></b>
			</div>
			<div class="panel">
				<br><table>
					<tr>
						<td class="wdm-label">
							<label for="wdm-a-u-ac-crt-sub"><?php _e( 'Subject : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<input type="text" name="wdm-a-u-ac-crt-sub" id="wdm-a-u-ac-crt-sub" size="50" value="<?php echo $a_u_ac_crt_sub; ?>">
							<span class="wdm-help-txt"><?php _e( 'Enter Subject for Email sent to Admin when User account gets created <br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
					</tr>
					<tr>
						<td class="wdm-label">
							<label for="wdm-a-u-ac-crt-body"><?php _e( 'Body : ', WDM_LDGR_TXT_DOMAIN ); ?></label>
						</td>
						<td>
							<?php
								$editor_settings = array(
									// 'wpautop'=>true,
									'media_buttons'    => false,
									'drag_drop_upload' => false,
									'textarea_rows'    => 15,
									'textarea_name'    => 'wdm-a-u-ac-crt-body',
								);
								wp_editor(
									stripslashes( $a_u_ac_crt_body ),
									'wdm-a-u-ac-crt-body',
									$editor_settings
								);
								?>
							<span class="wdm-help-txt"><?php _e( 'Enter Content for Email sent to Admin when User account gets created <br/> Default : leave blank', WDM_LDGR_TXT_DOMAIN ); ?></span>
						</td>
						<td class="wdm-var-sec">
							<div>
								<span class="wdm-var-head"><?php _e( 'Available Variables', WDM_LDGR_TXT_DOMAIN ); ?></span>
								<ul>
									<li><b>{group_title}</b> : <?php _e( 'Displays Group Title', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{site_name}</b> : <?php _e( 'Displays Site Name', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_email}</b> : <?php _e( "Displays User's Email", WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{user_login}</b> : <?php _e( "Displays User's Login", WDM_LDGR_TXT_DOMAIN ); ?></li>
								</ul>
							</div>
						</td>
					</tr>
				</table><br>
			</div><br>

			<div class="accordion">
				<label class="wdm-switch">
					<input
						type="checkbox"
						name="wdm-gr-reinvite-enable"
						<?php echo ( $wdm_gr_reinvite_enable != 'off' ) ? 'checked' : ''; ?>
					>
					<span class="wdm-slider round"></span>
				</label>
				<b><?php _e( 'When Group Leader sends ReInvite Email (User) ', WDM_LDGR_TXT_DOMAIN ); ?></b>
			</div>
			<div class="panel">
				<br><table>
					<tr>
						<td class="wdm-label">
							<label for="wdm-gr-reinvite-sub"><?php _e( 'Subject : ', 'wdm_ld_group' ); ?></label>
						</td>
						<td>
							<input type="text" name="wdm-gr-reinvite-sub" id="wdm-gr-reinvite-sub" size="50" value="<?php echo htmlentities( $wdm_reinvite_sub ); ?>">
							<span class="wdm-help-txt"><?php _e( 'Enter Subject for Email sent to user to Re-Invite. <br/> Default : leave blank', 'wdm_ld_group' ); ?></span>
						</td>
					</tr>
					<tr>
						<td class="wdm-label">
							<label for="wdm-gr-reinvite-body"><?php _e( 'Body : ', 'wdm_ld_group' ); ?></label>
						</td>
						<td>
							<?php
								$editor_settings = array(
									// 'wpautop'=>true,
									'media_buttons'    => false,
									'drag_drop_upload' => false,
									'textarea_rows'    => 15,
									'textarea_name'    => 'wdm-gr-reinvite-body',
								);
								wp_editor(
									stripslashes( $wdm_reinvite_body ),
									'wdm-gr-reinvite-body',
									$editor_settings
								);
								?>
							<span class="wdm-help-txt"><?php _e( 'Enter Content for Email sent to user to Re-Invite. <br/> Default : leave blank', 'wdm_ld_group' ); ?></span>
						</td>
						<td class="wdm-var-sec">
							<div>
								<span class="wdm-var-head"><?php _e( 'Available Variables', 'wdm_ld_group' ); ?></span>
								<ul>
									<li><b>{group_title}</b> : <?php _e( 'Displays Group Title', WDM_LDGR_TXT_DOMAIN ); ?></li>
									<li><b>{reset_password}</b> : <?php _e( 'Displays Reset Password link for user', 'wdm_ld_group' ); ?></li>
									<li><b>{site_name}</b> : <?php _e( 'Displays Site Name', 'wdm_ld_group' ); ?></li>
									<li><b>{user_first_name}</b> : <?php _e( "Displays User's First Name", 'wdm_ld_group' ); ?></li>
									<li><b>{user_last_name}</b> : <?php _e( "Displays User's Last Name", 'wdm_ld_group' ); ?></li>
									<li><b>{user_email}</b> : <?php _e( "Displays User's Email", 'wdm_ld_group' ); ?></li>
									<li><b>{course_list}</b> : <?php _e( 'Displays Course List', 'wdm_ld_group' ); ?></li>
									<li><b>{group_leader_name}</b> : <?php _e( 'Displays Group Leaders Name', 'wdm_ld_group' ); ?></li>
									<li><b>{login_url}</b> : <?php _e( 'Displays Login URL', 'wdm_ld_group' ); ?></li>
								</ul>
							</div>
						</td>
					</tr>
				</table><br>
			</div>

			<?php do_action( 'ldgr_action_email_settings_form_end' ); ?>

			<?php submit_button( __( 'Save', WDM_LDGR_TXT_DOMAIN ) ); ?>

			<?php wp_nonce_field( 'wdm_gr_email_setting', 'sbmt_wdm_gr_email_setting' ); ?>
		</form>
	</div>
	<?php do_action( 'ldgr_action_email_settings_end' ); ?>
