<?php
?>

<div class="wdm-ldgr-setting-div">
	<form name="frm_ld_gr_setting" method="POST">
	<table>
		<tr>
			<th>
				<label for="ldgr_admin_approval">
				<?php
				echo apply_filters( 'gr_admin_approve_label', __( 'Allow Group Leader to Remove Members from the Group (without Admin Approval) : ', WDM_LDGR_TXT_DOMAIN ) );
				?>
				</label>
				<div>
					<label class="wdm_help_text">
					<?php
					_e( 'It allows the Group Leader to remove a member from group without sending a request to the admin.', WDM_LDGR_TXT_DOMAIN );
					?>
					</label>
				</div>
			</th>
			<td>
				<label class="wdm-switch">
				  <input type="checkbox" name="ldgr_admin_approval" 
					<?php
					echo ( $ldgr_admin_approval == 'on' ) ? 'checked' : '';
					?>
					 >
				  <span class="wdm-slider round"></span>
				</label>
			</td>
		</tr>
		<tr>
			<th>
				<label for="ldgr_group_limit">
				<?php
				echo apply_filters( 'gr_group_limit_label', __( 'Fix Group Limit : ', WDM_LDGR_TXT_DOMAIN ) );
				?>
				</label>
				<div>
					<label class="wdm_help_text">
					<?php
					_e( 'Restrict users to be added to a group on removing currently added users.', WDM_LDGR_TXT_DOMAIN );
					?>
					</label>
				</div>
			</th>
			<td>
				<label class="wdm-switch">
				  <input type="checkbox" name="ldgr_group_limit" 
					<?php
					echo ( $ldgr_group_limit == 'on' ) ? 'checked' : '';
					?>
					 >
				  <span class="wdm-slider round"></span>
				</label>
			</td>
		</tr>
		<tr>
			<th>
				<label for="ldgr_reinvite_user">
				<?php
				echo apply_filters( 'gr_reinvite_user_label', __( 'Allow Group Leader to ReInvite Group Users : ', WDM_LDGR_TXT_DOMAIN ) );
				?>
				</label>
				<div>
					<label class="wdm_help_text">
					<?php
					_e( 'Enable this option if you want to allow Group Leader to ReInvite Group Users via email. It allows user to reset password.', WDM_LDGR_TXT_DOMAIN );
					?>
					</label>
				</div>
			</th>
			<td>
				<label class="wdm-switch">
				  <input type="checkbox" name="ldgr_reinvite_user" 
					<?php
					echo ( $ldgr_reinvite_user == 'on' ) ? 'checked' : '';
					?>
					 >
				  <span class="wdm-slider round"></span>
				</label>
			</td>
		</tr>

		<!-- ldgr_group_courses -->
		<tr>
			<th>
				<label for="ldgr_group_courses">
				<?php
				echo apply_filters( 'gr_group_courses_label', __( 'Display the Courses associated with Group : ', WDM_LDGR_TXT_DOMAIN ) );
				?>
				</label>
				<div>
					<label class="wdm_help_text">
					<?php
					_e( 'Enable this option if you want to display the Courses of a Group on Group Registration page.', WDM_LDGR_TXT_DOMAIN );
					?>
					</label>
				</div>
			</th>
			<td>
				<label class="wdm-switch">
				  <input type="checkbox" name="ldgr_group_courses" 
					<?php
					echo ( $ldgr_group_courses == 'on' ) ? 'checked' : '';
					?>
					 >
				  <span class="wdm-slider round"></span>
				</label>
			</td>
		</tr>        

		<tr>
			<th>
				<label for="ldgr_user_redirects">
					<?php
					echo apply_filters(
						'gr_user_redirects_label',
						__( 'Redirect users after successfull login : ', WDM_LDGR_TXT_DOMAIN )
					);
					?>
				</label>
				<div>
					<label class="wdm_help_text">
						<?php
						_e(
							'Enable this option if you wish to redirect users to specific pages after login.',
							WDM_LDGR_TXT_DOMAIN
						);
						?>
					</label>
				</div>
			</th>
			<td>
				<label class="wdm-switch">
				  <input type="checkbox" name="ldgr_user_redirects" <?php echo ( $ldgr_user_redirects == 'on' ) ? 'checked' : ''; ?> >
				  <span class="wdm-slider round"></span>
				</label>
			</td>
		</tr>

		<tr class='ldgr-user-redirects-settings' <?php echo ( $ldgr_user_redirects == 'on' ) ? '' : 'style="display: none"'; ?>>
			<td>
				<div>
					<p>
						<label for="ldgr_redirect_group_leader">
							<?php _e( 'Redirect Group Leader', WDM_LDGR_TXT_DOMAIN ); ?>
						</label>
						<?php
							echo wp_dropdown_pages(
								array(
									'name'              => 'ldgr_redirect_group_leader',
									'echo'              => 0,
									'show_option_none'  => __( '&mdash; Select &mdash;' ),
									'option_none_value' => '0',
									'selected'          => get_option( 'ldgr_redirect_group_leader' ),
								)
							);
							?>
					</p>
					<p>
						<label for="ldgr_redirect_group_user">
							<?php _e( 'Redirect Group User', WDM_LDGR_TXT_DOMAIN ); ?>
						</label>
						<?php
							echo wp_dropdown_pages(
								array(
									'name'              => 'ldgr_redirect_group_user',
									'echo'              => 0,
									'show_option_none'  => __( '&mdash; Select &mdash;' ),
									'option_none_value' => '0',
									'selected'          => get_option( 'ldgr_redirect_group_user' ),
								)
							);
							?>
					</p>
				</div>
			</td>
		</tr>

		<tr>
			<th>
				<label for="ldgr_unlimited_members">
					<?php
					echo apply_filters(
						'ldgr_unlimited_members_label',
						__( 'Enter a label for Unlimited Members: ', WDM_LDGR_TXT_DOMAIN )
					);
					?>
				</label>
				<div>
					<label class="wdm_help_text">
						<?php
						_e(
							'This label will be used on the product, cart and checkout pages for referring the unlimited seats options',
							WDM_LDGR_TXT_DOMAIN
						);
						?>
					</label>
				</div>
			</th>
			<td>
				<input type="text" name="ldgr_unlimited_members_label" value="<?php echo esc_attr( $ldgr_unlimited_members_label ); ?>" />
			</td>
		</tr>

		<tr>
			<td colspan="2">
			<?php
			submit_button( 'Save' );
			?>
			<?php
			wp_nonce_field( 'ldgr_setting', 'sbmt_ldgr_setting' );
			?>
			</td>
		</tr>
	</table>
	</form>
</div>
