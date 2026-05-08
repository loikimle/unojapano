<?php
$return_url = admin_url( 'admin.php?page=user-registration-coupons' );

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
						<?php echo isset( $_GET['post_id'] ) ? esc_html_e( 'Edit Coupon', 'user-registration' ) : esc_html_e( 'Create New Coupon', 'user-registration' ); ?>
					</p>
				</div>
			</div>
		</div>
		<div class="ur-page-title__wrapper--right">
			<div class="ur-page-title__wrapper--right-menu">
				<div class="ur-page-title__wrapper--right-menu__items">
					<div class="ur-page-title__wrapper--actions">
						<div class="ur-page-title__wrapper--actions-status">
							<p>Status</p>
							<span class="separator">|</span>
							<div class="visible ur-d-flex ur-align-items-center" style="gap: 5px">
								<div class="ur-toggle-section">
									<span class="user-registration-toggle-form">
										<input
										data-key-name="<?php echo esc_attr__( 'coupon_status', 'user-registration-membership' ); ?>"
										id="ur-coupon-status" type="checkbox"
										class="user-registration-switch__control hide-show-check enabled ur-coupon-input"
										value="1"
										<?php echo ( isset( $coupon_details['coupon_status'] ) ? ( $coupon_details['coupon_status'] == true ? 'checked' : '' ) : 'checked' ); ?>
										?>
										<span class="slider round"></span>
									</span>
								</div>
							</div>
						</div>
						<div class="submit ur-d-flex ur-justify-content-end" style="gap: 10px">
							<button class="button-primary save-coupon-btn">
								<?php echo esc_html__( isset( $_REQUEST['post_id'] ) ? 'Update' : 'Create', 'user-registration' ); ?>
							</button>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
<div class="ur-coupon-tab-contents-wrapper ur-p-8 ur-d-flex ur-align-items-center ur-justify-content-center">
	<form id="ur-coupon-create-form" method="post" style="width: 80%">
		<div class="user-registration-card">
			<div id="ur-coupon-form-container" class="ur-d-flex ur-p-4 ur-flex-column" style="gap: 20px;">
				<div id="left-body" class="">
					<!--						coupon name-->
					<div class="ur-coupon-input-container ur-d-flex ur-align-items-center ur-p-3" style="gap:20px;">
						<div class="ur-label" style="width: 30%">
							<label
								for="ur-input-type-coupon-name"><?php esc_html_e( 'Coupon Name', 'user-registration' ); ?>
							</label>
						</div>
						<div class="ur-input-type-coupon-name ur-admin-template" style="width: 100%">
							<div class="ur-field" data-field-key="coupon_name">
								<input
									class="ur-coupon-input"
									type="text"
									data-key-name="<?php echo esc_attr__( 'coupon_name', 'user-registration' ); ?>"
									id="ur-input-type-coupon-name" name="ur_coupon_name"
									style="width: 100%"
									value="<?php echo ( isset( $coupon ) && ! empty( $coupon ) ) ? $coupon->post_title : ''; ?>"
								>
							</div>
						</div>
					</div>
					<!--						Coupon code-->
					<div class="ur-coupon-input-container ur-d-flex ur-align-items-center ur-p-3" style="gap:20px;">
						<div class="ur-label" style="width: 30%">
							<label
								for="ur-input-type-coupon-code"><?php esc_html_e( 'Coupon Code', 'user-registration' ); ?>
								<span style="color:red">*</span>
							</label>
						</div>
						<div class="ur-input-type-coupon-code ur-admin-template" style="width: 100%">
							<div class="ur-field">
								<input type="text"
										class="ur-coupon-input"
										data-key-name="<?php echo esc_attr__( 'coupon_code', 'user-registration-membership' ); ?>"
										id="ur-input-type-coupon-code" name="ur_coupon_code"
										style="width: 100%"
										required
										value="<?php echo ( isset( $coupon_details ) && ! empty( $coupon_details ) ) ? $coupon_details['coupon_code'] : ''; ?>"
								>
							</div>
						</div>
					</div>
					<!--						Discount Type-->
					<div class="ur-coupon-selection-container ur-d-flex ur-align-items-center ur-p-3" style="gap:20px;">
						<div class="ur-label" style="width: 30%">
							<label for="ur-coupon-discount-type-fixed">
								<?php esc_html_e( 'Discount Type :', 'user-registration' ); ?>
								<span style="color:red">*</span>
							</label>
						</div>

						<div class="ur-input-type-select ur-admin-template" style="width: 100%">
							<div class="ur-field ur-d-flex ur-coupon-type-field" data-field-key="radio">

								<!-- Fixed Discount -->
								<label class="ur-coupon-types" for="ur-coupon-discount-type-fixed">
									<div class="ur-coupon-type-title ur-d-flex ur-align-items-center">
										<input
											data-key-name="<?php echo esc_attr__( 'coupon_discount_type_fixed', 'user-registration' ); ?>"
											id="ur-coupon-discount-type-fixed"
											type="radio"
											value="fixed"
											name="ur_coupon_discount_type"
											style="margin: 0"
											<?php echo isset( $coupon_details['coupon_discount_type'] ) && 'fixed' === $coupon_details['coupon_discount_type'] ? 'checked' : 'checked'; ?>
											required
										>
										<label class="ur-p-2" for="ur-coupon-discount-type-fixed">
											<b class="user-registration-image-label">
												<?php esc_html_e( 'Fixed Discount', 'user-registration' ); ?>
											</b>
										</label>
									</div>
								</label>

								<!-- Percent Based -->
								<label class="ur-coupon-types ur-coupon-type-field" for="ur-coupon-discount-type-percent">
									<div class="ur-coupon-type-title ur-d-flex ur-align-items-center">
										<input
											data-key-name="<?php echo esc_attr__( 'coupon_discount_type_percent', 'user-registration' ); ?>"
											id="ur-coupon-discount-type-percent"
											type="radio"
											value="percent"
											name="ur_coupon_discount_type"
											style="margin: 0"
											<?php echo isset( $coupon_details['coupon_discount_type'] ) && 'percent' === $coupon_details['coupon_discount_type'] ? 'checked' : ''; ?>
											required
										>
										<label class="ur-p-2" for="ur-coupon-discount-type-percent">
											<b class="user-registration-image-label">
												<?php esc_html_e( 'Percent Based', 'user-registration' ); ?>
											</b>
										</label>
									</div>
								</label>

							</div>
						</div>
					</div>

					<!--						Discount-->
					<div class="ur-coupon-input-container ur-d-flex ur-align-items-center ur-p-3" style="gap:20px;">
						<div class="ur-label" style="width: 30%">
							<label
								for="ur-input-type-coupon-discount"><?php esc_html_e( 'Discount Amount/Percent', 'user-registration' ); ?>
								<span style="color:red">*</span>
							</label>
						</div>
						<div class="ur-input-type-coupon-discount ur-admin-template" style="width: 100%">
							<div class="ur-field ur-coupon-type-field">
								<input type="text"
										class="ur-coupon-input"
										data-key-name="<?php echo esc_attr__( 'coupon_discount', 'user-registration-membership' ); ?>"
										id="ur-input-type-coupon-discount-type" name="ur_coupon_discount"
										style="width: 100%"
										min="0"
										value="<?php echo isset( $coupon_details ) && ! empty( $coupon_details ) ? $coupon_details['coupon_discount'] : ''; ?>"
										required>
							</div>
						</div>

					</div>

					<!--						start date-->
					<div class="ur-coupon-input-container ur-d-flex ur-align-items-center ur-p-3" style="gap:15px;">
						<div class="ur-label" style="width: 23%">
							<label
								for="ur-input-type-coupon-start-date"><?php esc_html_e( 'Start Date', 'user-registration' ); ?>
								<span style="color:red">*</span>
							</label>
						</div>
						<div class="ur-input-type-coupon-start-date ur-admin-template" >
							<div class="ur-field ur-coupon-type-field">
								<input
									data-key-name="<?php echo esc_attr__( 'start_date', 'user-registration' ); ?>"
									class="ur-coupon-input ur-start-date"
									type="datetime-local"
									id="ur-input-type-coupon-start-date" name="ur_start_date"
									style="width: 100%"
									value="<?php echo ( isset( $coupon_details ) && ! empty( $coupon_details ) ) ? $coupon_details['coupon_start_date'] : date( 'Y-m-d\TH:i' ); ?>"
									required
								>
							</div>
						</div>
					</div>
					<!--						end date-->
					<div class="ur-coupon-input-container ur-d-flex ur-align-items-center ur-p-3" style="gap:15px;">
						<div class="ur-label" style="width: 23%">
							<label
								for="ur-input-type-coupon-end-date"><?php esc_html_e( 'End Date', 'user-registration' ); ?>
								<!-- <span style="color:red">*</span> -->
							</label>
						</div>
						<div class="ur-input-type-coupon-end-date ur-admin-template" >
							<div class="ur-field ur-coupon-type-field">
								<input
									data-key-name="<?php echo esc_attr__( 'end_date', 'user-registration' ); ?>"
									class="ur-coupon-input ur-end-date"
									type="datetime-local"
									id="ur-input-type-coupon-end-date" name="ur_end_date"
									style="width: 100%"
									value="<?php echo ( isset( $coupon_details ) && ! empty( $coupon_details ) ) ? $coupon_details['coupon_end_date'] : ''; ?>"
									>
							</div>
						</div>
					</div>
					<?php
					$is_membership_active = ur_check_module_activation( 'membership' );
					?>
					<!--					coupon applicable for-->
					<div class="ur-coupon-input-container ur-d-flex ur-align-items-center ur-p-3" style="gap:20px;">
						<div class="ur-label" style="width: 30%">
							<label
								for="ur-input-type-coupon-for"><?php esc_html_e( 'Applicable For', 'user-registration' ); ?>
							</label>
						</div>
						<div class="ur-input-type-coupon-name ur-admin-template" style="width: 100%">
							<div class="ur-field">
								<select
									data-key-name="<?php echo esc_attr__( 'coupon_for', 'user-registration' ); ?>"
									id="ur-input-type-coupon-for"
									name="ur_coupon_for">
									<option
										value="empty"><?php echo __( 'Select an option.', 'user-registration' ); ?></option>
									<option value="form"
										<?php echo ( isset( $coupon_details ) && ! empty( $coupon_details ) && $coupon_details['coupon_for'] === 'form' ) ? 'selected="selected"' : ''; ?>>
										<?php echo __( 'Form', 'user-registration' ); ?>
									</option>
									<?php
									if ( $is_membership_active ) :
										?>
									<option value="membership"
										<?php echo ( isset( $coupon_details ) && ! empty( $coupon_details ) && $coupon_details['coupon_for'] === 'membership' ) ? 'selected="selected"' : ''; ?>>
										<?php echo __( 'Membership', 'user-registration' ); ?>
									</option>
										<?php
									endif;
									?>
								</select>
							</div>
						</div>
					</div>
					<!--						Membership-->
					<?php
					if ( $is_membership_active ) :
						?>
					<div
						class="ur-coupon-input-container coupon-hidden-select ur-p-3 ur-align-items-center  <?php echo ( isset( $coupon_details ) && ! empty( $coupon_details ) && $coupon_details['coupon_for'] === 'membership' ) ? 'ur-d-flex ' : 'ur-d-none'; ?> "
						data-value="membership"
						style="gap:20px;">
						<div class="ur-label" style="width: 30%">
							<label
								for="ur-input-type-coupon-membership"><?php esc_html_e( 'Applicable Membership', 'user-registration' ); ?>
								<span style="color:red">*</span>
							</label>
						</div>

						<div class="ur-input-type-coupon-name ur-admin-template" style="width: 100%">
							<div class="ur-field">
								<select
									data-key-name="<?php echo esc_attr__( 'coupon_membership', 'user-registration' ); ?>"
									id="ur-input-type-coupon-membership"
									name="ur_coupon_membership"
									class="coupon-enhanced-select2 ur-coupon-input"
									multiple="multiple"
								>
									<?php
									$selected_memberships = array();
									if ( isset( $coupon_details ) && ! empty( $coupon_details ) && $coupon_details['coupon_for'] === 'membership' ) {
										$selected_memberships = json_decode( $coupon_details['coupon_membership'], 'true' );
									}
									foreach ( $memberships as $k => $membership ) :
										?>
										<option
											value="<?php echo esc_attr( $k ); ?>" <?php echo in_array( $k, $selected_memberships ) ? 'selected' : ''; ?>>
											<?php echo esc_html( $membership ); ?>
										</option>
										<?php
									endforeach;
									?>
								</select>
							</div>
						</div>
					</div>
						<?php
					endif;
					?>
					<?php
					$selected_forms = array();
					if ( isset( $coupon_details ) && ! empty( $coupon_details ) && $coupon_details['coupon_for'] === 'form' ) {
						$selected_forms = json_decode( $coupon_details['coupon_form'], 'true' );
					}
					?>
					<!--					Forms-->
					<div
						class="ur-coupon-input-container coupon-hidden-select ur-p-3 <?php echo ( isset( $coupon_details ) && ! empty( $coupon_details ) && $coupon_details['coupon_for'] === 'form' ) ? 'ur-d-flex' : 'ur-d-none'; ?> "
						data-value="form"
						style="gap:20px;">
						<div class="ur-label" style="width: 30%">
							<label
								for="ur-input-type-coupon-form"><?php esc_html_e( 'Applicable Forms', 'user-registration' ); ?>
								<span style="color:red">*</span>
							</label>
						</div>
						<div class="ur-input-type-coupon-form ur-admin-template" style="width: 100%">
							<div class="ur-field">
								<select
									data-key-name="<?php echo esc_attr__( 'coupon_form', 'user-registration' ); ?>"
									id="ur-input-type-coupon-form"
									name="ur_coupon_form_id"
									class="coupon-enhanced-select2 ur-coupon-input"
									multiple="multiple"
								>
									<?php
									$selected_forms = array();
									if ( isset( $coupon_details ) && ! empty( $coupon_details ) && $coupon_details['coupon_for'] === 'form' ) {
										$selected_forms = json_decode( $coupon_details['coupon_form'], 'true' );
									}

									foreach ( $forms as $k => $form ) :
										?>
										<option
											value="<?php echo esc_attr( $k ); ?>" <?php echo in_array( $k, $selected_forms ) ? 'selected' : ''; ?>>
											<?php echo esc_html( $form ); ?>
										</option>
										<?php
									endforeach;
									?>
								</select>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</form>
</div>
