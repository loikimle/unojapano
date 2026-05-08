<?php
$return_url = admin_url( 'admin.php?page=user-registration-team' );
?>
<div class="ur-admin-page-topnav" id="ur-lists-page-topnav">
	<div class="ur-page-title__wrapper">
		<div class="ur-page-title__wrapper--left">
			<a class="ur-text-muted ur-border-right ur-d-flex ur-mr-2 ur-pl-2 ur-pr-2" href="<?php echo esc_attr( $return_url ); ?>">
				<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
			</a>
			<div class="ur-page-title__wrapper--left-menu">
				<div class="ur-page-title__wrapper--left-menu__items">
					<p>
						<?php echo esc_html_e( 'Edit Team', 'user-registration' ); ?>
					</p>
				</div>
			</div>
		</div>
		<div class="ur-page-title__wrapper--right">
			<div class="ur-page-title__wrapper--right-menu">
				<div class="ur-page-title__wrapper--right-menu__items">
					<?php
					$save_btn_class  = 'ur-team-update-btn';
					$create_btn_text = esc_html__( 'Update', 'user-registration' );
					require __DIR__ . '/./footer-actions.php'
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="ur-membership">
	<div
		class="ur-membership-tab-contents-wrapper ur-registered-from ur-align-items-center ur-justify-content-center">
		<div class="ur-team-membership-content">
			<div class="user-registration-card">
				<div class="user-registration-card__body">
					<form id="ur-membership-team-form" class="ur-flex-grow-1" method="post">
						<div id="ur-membership-team-fields">
							<?php if ( ! empty( $team['ID'] ) ) : ?>
								<?php
								$team_membership_id   = get_post_meta( $team['ID'], 'urm_membership_id', true );
								$team_membership_name = '';
								if ( ! empty( $team_membership_id ) ) {
									$team_membership_post = get_post( $team_membership_id );
									if ( $team_membership_post ) {
										$team_membership_name = $team_membership_post->post_title;
									}
								}
								?>
								<div class="ur-membership-input-container ur-d-flex ur-align-items-center ur-p-1" style="gap:20px; margin-bottom: 10px;">
									<div class="ur-label" style="width: 30%">
										<label>
										<?php
												printf(
													esc_html__( 'Membership (id: %d):', 'user-registration' ),
													esc_html( $team_membership_id )
												);
										?>
										</label>
									</div>
									<div style="width: 100%">
									<?php
									echo esc_html( $team_membership_name );
									?>
									</div>
								</div>
							<?php endif; ?>
							<div class="ur-membership-input-container ur-d-flex ur-align-items-center ur-p-1" style="gap:20px;">
								<div class="ur-label" style="width: 30%">
									<label
										for="ur-input-type-team-name">
										<?php esc_html_e( 'Team Name', 'user-registration' ); ?>
										<span style="color:red">*</span> :
									</label>
								</div>
								<div class="ur-input-type-team-name ur-admin-template" style="width: 100%">
									<div class="ur-field" data-field-key="team_name">
										<input type="text" data-key-name="Team Name"
												id="ur-input-type-team-name" name="ur_team_name"
												style="width: 100%"
												autocomplete="off"
												value="<?php echo isset( $team['team_name'] ) && ! empty( $team['team_name'] ) ? esc_html( $team['team_name'] ) : ''; ?>"
												required>
									</div>
								</div>
							</div>
							<div class="ur-membership-selection-container ur-p-1 ur-d-flex ur-align-items-center ur-mt-3" style="gap:20px;">
								<div class="ur-label" style="width: 30%">
									<label
										for="ur-membership-team-leader"><?php esc_html_e( 'Team Leader', 'user-registration' ); ?>
										<span style="color:red">*</span> :
									</label>
								</div>
								<div class="ur-field ur-d-flex ur-align-items-center"
									data-field-key="team_leader" style="width: 100%; gap: 20px;">
									<select name="team_leader" id="ur-membership-team-leader" data-key-name="Team Leader" required>
										<?php foreach ( $users as $user ) : ?>
											<option value="<?php echo esc_attr( $user->user_email ); ?>" <?php selected( isset( $team['team_leader'] ) ? $team['team_leader']['ID'] : '', $user->ID ); ?>>
												<?php echo esc_html( $user->user_email . ' (' . $user->display_name . ')' ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="ur-membership-selection-container ur-p-1 ur-d-flex ur-align-items-center ur-mt-3" style="gap:20px;">
								<div class="ur-label" style="width: 30%">
									<label
										for="ur-membership-team-members">
										<?php
										printf(
											esc_html__( 'Members (Max: %s)', 'user-registration' ),
											esc_html( $team['meta']['urm_team_seats'] ?? 0 )
										);
										?>
										<span style="color:red">*</span> :
									</label>
								</div>
								<div class="ur-field ur-d-flex ur-align-items-center"
									data-field-key="team_members" style="width: 100%; gap: 20px;">
									<input type="hidden" name="max_team_seats" id="ur-membership-max-team-seats" value="<?php echo esc_attr( $team['meta']['urm_team_seats'] ?? 0 ); ?>">
									<select name="team_members[]" id="ur-membership-team-members" data-key-name="Members" class="user-membership-team-enhanced-select2"
										multiple
										required>
										<?php foreach ( $users as $user ) : ?>
											<option value="<?php echo esc_attr( $user->user_email ); ?>" data-user-id="<?php echo esc_attr( $user->ID ); ?>" <?php selected( in_array( $user->user_email, $team['meta']['urm_member_emails'] ?? [], true ), true ); ?>>
												<?php echo esc_html( $user->user_email . ' (' . $user->display_name . ')' ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>

							</div>
							<?php
							$used_seats    = ! empty( $team['meta']['urm_used_seats'] ) ? absint( $team['meta']['urm_used_seats'] ) : 0;
							$current_seats = ! empty( $team['meta']['urm_team_seats'] ) ? absint( $team['meta']['urm_team_seats'] ) : 0;
							?>
							<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mt-3" style="gap:20px;">
								<div class="ur-label" style="width: 30%">
									<label
										for="ur-input-type-team-seats">
										<?php esc_html_e( 'Team Seats', 'user-registration' ); ?>
										<span style="color:red">*</span> :
									</label>
								</div>
								<div class="ur-input-type-team-seats ur-admin-template" style="width: 100%">
									<div class="ur-field" data-field-key="team_seats">
										<input type="number"
												data-key-name="Team Seats"
												id="ur-input-type-team-seats"
												name="ur_team_seats"
												style="width: 100%"
												min="<?php echo esc_attr( $used_seats ); ?>"
												value="<?php echo esc_attr( $current_seats ); ?>"
												required>
										<?php if ( $used_seats > 0 ) : ?>
											<p style="margin-top: 5px; color: #666; font-size: 12px;margin-bottom:0">
												<?php
												printf(
													esc_html__( 'Currently %1$d seat(s) are occupied. Minimum seats cannot be less than %2$d.', 'user-registration' ),
													$used_seats,
													$used_seats
												);
												?>
											</p>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</form>
					<?php
					if ( ! empty( $team['ID'] ) ) {
						if ( ! class_exists( 'WPEverest\URTeamMembership\Admin\MembershipTeamListTable' ) ) {
							require_once __DIR__ . '/../Admin/MembershipTeamListTable.php';
						}
						$members_table = new \WPEverest\URTeamMembership\Admin\MembershipTeamListTable( $team['ID'] );
						$members_table->prepare_items();
						?>
						<hr style="margin-top:40px;margin-bottom:40px">
						<div id="user-registration-base-list-table-page">
							<div class="user-registration-base-list-table-heading" style="position:relative;margin-bottom:10px;">
								<h3><?php esc_html_e( 'Team Members', 'user-registration' ); ?></h3>
							</div>
							<form id="membership-team-list" method="get" class="user-registration-base-list-table-form">
								<input type="hidden" name="page" value="user-registration-team" />
								<input type="hidden" name="post_id" value="<?php echo esc_attr( $team['ID'] ); ?>" />
								<input type="hidden" name="action" value="edit" />
								<?php $members_table->display(); ?>
							</form>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>

