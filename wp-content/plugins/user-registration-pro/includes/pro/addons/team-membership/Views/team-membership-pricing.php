<?php
/**
 * team-membership-pricing.php
 *
 * @package URTeamMembership
 */

// Ensure $membership_details is defined and is an array.
$membership_details = ( isset( $membership_details ) && is_array( $membership_details ) ) ? $membership_details : [];

// Feature flags / defaults
$is_team_pricing_enabled = ! empty( $membership_details['team_pricing'] );
$membership_type         = $membership_details['type'] ?? '';

?>
<!-- membership team pricing -->
<div id="ur-membership-team-pricing-container"
	class="<?php echo in_array( $membership_type, array( 'paid', 'subscription' ), true ) ? 'ur-d-flex' : 'ur-d-none'; ?>"
	style="gap:20px;">
	<div class="ur-label ur-mb-0" style="width: 30%">
		<label class="ur-mb-0" for="ur-membership-team-pricing">
			<?php esc_html_e( 'Team Pricing:', 'user-registration' ); ?>
		</label>
	</div>

	<div class="ur-toggle-section m1-auto ur-d-block" style="width: 100%;">
		<span class="user-registration-toggle-form">
			<input
				data-key-name="Team Pricing"
				id="ur-membership-team-pricing"
				type="checkbox"
				class="user-registration-switch__control hide-show-check enabled"
				name="ur_membership_team_pricing"
				<?php echo $is_team_pricing_enabled ? 'checked="checked"' : ''; ?>
				style="width: 100%; text-align: left" />
			<span class="slider round"></span>
		</span>
		<!-- membership team pricing fields -->
		<div id="ur-team-pricing-container"
			style="<?php echo esc_attr( $is_team_pricing_enabled ? '' : 'display: none;' ); ?>"
			class="ur-mt-5">

	<?php
	$team_pricing = $membership_details['team_pricing'] ?? [];
	if ( empty( $team_pricing ) ) {
		$team_pricing[] = array(
			'seat_model'           => 'fixed',
			'team_name'            => '',
			'team_plan_type'       => 'one-time',
			'team_duration_value'  => '',
			'team_duration_period' => '',
			'team_size'            => 0,
			'team_price'           => 0,
			'minimum_seats'        => 0,
			'maximum_seats'        => 0,
			'pricing_model'        => 'per_seat',
			'per_seat_price'       => 0,
			'tiers'                => array(),
		);
	}

	foreach ( $team_pricing as $g_index => $team ) :
		$seat_model           = $team['seat_model'] ?? 'fixed';
		$pricing_model        = $team['pricing_model'] ?? 'per_seat';
		$team_plan_type       = $team['team_plan_type'] ?? 'one-time';
		$team_duration_value  = $team['team_duration_value'] ?? '';
		$team_duration_period = $team['team_duration_period'] ?? '';
		$tiers                = $team['tiers'] ?? array();
		?>
		<div class="user-registration-card ur-team-pricing-wrapper ur-mb-5" data-pricing-wrapper-id="<?php echo esc_attr( $g_index ); ?>">
			<div style="text-align: end;">
				<button title="<?php echo esc_attr_x( 'Remove', 'button title', 'user-registration' ); ?>" class="button button-icon button-danger ur-remove-team-pricing-btn">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>

			<div class="user-registration-card__body ur-d-flex ur-flex-column" style="gap: 20px">
				<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
					<div class="ur-label" style="width: 28%">
						<label><?php esc_html_e( 'Seat Model', 'user-registration' ); ?></label>
					</div>
					<div style="width: 100%">
						<div class="ur-team-toggle-tabs" style="width: 250px;">
							<input type="radio"
									name="ur_seat_model[<?php echo esc_attr( $g_index ); ?>]"
									value="fixed"
									id="fixed-seats-<?php echo esc_attr( $g_index ); ?>"
								<?php echo ( 'fixed' === $seat_model ) ? 'checked="checked"' : ''; ?> />
							<label for="fixed-seats-<?php echo esc_attr( $g_index ); ?>" class="tab-label"><?php esc_html_e( 'Fixed Seats', 'user-registration' ); ?></label>

							<input type="radio"
									name="ur_seat_model[<?php echo esc_attr( $g_index ); ?>]"
									value="variable"
									id="variable-seats-<?php echo esc_attr( $g_index ); ?>"
								<?php echo ( 'variable' === $seat_model ) ? 'checked="checked"' : ''; ?> />
							<label for="variable-seats-<?php echo esc_attr( $g_index ); ?>" class="tab-label"><?php esc_html_e( 'Variable Seats', 'user-registration' ); ?></label>
						</div>
					</div>
				</div>

				<div class="ur-membership-input-container ur-d-flex ur-p-1" style="gap:20px;">
					<div class="ur-label" style="width: 28%">
						<label for="ur-input-type-team-name-<?php echo esc_attr( $g_index ); ?>">
							<?php esc_html_e( 'Team Name', 'user-registration' ); ?>
						</label>
					</div>
					<div class="ur-input-type-team-name" style="width: 100%">
						<div class="ur-field" data-field-key="team_name">
							<input type="text"
									data-key-name="<?php esc_attr_e( 'Team Name', 'user-registration' ); ?>"
									id="ur-input-type-team-name-<?php echo esc_attr( $g_index ); ?>"
									name="ur_team_name[<?php echo esc_attr( $g_index ); ?>]"
									style="width: 100%"
									autocomplete="off"
									value="<?php echo esc_attr( $team['team_name'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'e.g., Small Team, Enterprise', 'user-registration' ); ?>" />
						</div>
					</div>
				</div>

				<!-- Team Membership -->
				<div class="user-registration-card__body">
					<div id="ur-membership-main-fields" class="ur-mb-0" style="gap: 0;">
						<!-- Membership Type -->
						<div class="ur-membership-selection-container ur-d-flex ur-p-1" style="gap:20px;">
							<div class="ur-label" style="width: 30%">
								<label for="ur-team-membership-free-type"><?php esc_html_e( 'Type :', 'user-registration' ); ?></label>
							</div>
							<div class="ur-input-type-select ur-admin-template" style="width: 100%">
								<div class="ur-field ur-d-flex" data-field-key="radio">
									<!-- One Time -->
									<label class="ur-membership-types" for="ur_team_plan_type_one_<?php echo esc_attr( $g_index ); ?>">
										<div class="ur-membership-type-title ur-d-flex ur-align-items-center">
											<input data-key-name="Type" id="ur_team_plan_type_one_<?php echo esc_attr( $g_index ); ?>" type="radio" style="margin: 0"
													value="one-time" name="ur_team_plan_type[<?php echo esc_attr( $g_index ); ?>]" class="ur_membership_paid_type"
												<?php echo ( 'one-time' === $team_plan_type ) ? 'checked="checked"' : ''; ?> />
											<label class="ur-p-2" for="ur_team_plan_type_one_<?php echo esc_attr( $g_index ); ?>">
												<b class="user-registration-image-label"><?php esc_html_e( 'One-Time Payment', 'user-registration' ); ?></b>
											</label>
										</div>
									</label>
									<!-- Subscription Type -->
									<label class="ur-membership-types <?php echo ! UR_PRO_ACTIVE ? 'upgradable-type' : ''; ?>"
											for="ur_team_plan_type_sub_<?php echo esc_attr( $g_index ); ?>">
										<div class="ur-membership-type-title ur-d-flex ur-align-items-center">
											<input data-key-name="Type" id="ur_team_plan_type_sub_<?php echo esc_attr( $g_index ); ?>" style="margin: 0"
													type="radio" value="subscription" name="ur_team_plan_type[<?php echo esc_attr( $g_index ); ?>]"
													class="ur_membership_paid_type"
												<?php echo ( 'subscription' === $team_plan_type ) ? 'checked="checked"' : ''; ?> />
												<?php echo ! UR_PRO_ACTIVE ? 'disabled' : ''; ?>
											<label class="ur-p-2" for="ur_team_plan_type_sub_<?php echo esc_attr( $g_index ); ?>">
												<b class="user-registration-image-label"><?php esc_html_e( 'Subscription Based', 'user-registration' ); ?></b>
											</label>
										</div>
									</label>
								</div>
							</div>
						</div>

						<!-- Paid Plan Fields -->
						<div id="paid-plan-container"
							class="ur-mt-5"
							style="<?php echo ( 'subscription' === $team_plan_type ) ? '' : 'display: none;'; ?>">

							<!-- Membership Duration -->
							<div
								class="ur-membership-selection-container ur-p-1 ur-mt-3 ur-subscription-fields <?php echo isset( $membership_details['type'] ) && 'subscription' === $membership_details['type'] ? 'ur-d-flex' : 'ur-d-flex'; ?>"
								id="ur-team-membership-duration-container" style="gap:20px;">
								<div class="ur-label" style="width: 30%">
									<label for="ur-team-membership-duration">
										<?php esc_html_e( 'Billing Cycle', 'user-registration' ); ?>
										:
									</label>
								</div>
								<div class="ur-field ur-d-flex ur-align-items-center" data-field-key="membership_duration"
									style="gap: 20px;">
									<input data-key-name="Duration Value"
											value="<?php echo isset( $team_duration_value ) ? esc_attr( $team_duration_value ) : ''; ?>"
											class="" type="number" name="ur_team_duration_value[<?php echo esc_attr( $g_index ); ?>]"
											autocomplete="off" id="ur-team-membership-duration-value" min="1">
								</div>
								<select id="ur-team-membership-duration" data-key-name="Duration"
										class="ur-subscription-fields <?php echo isset( $membership_details['type'] ) && 'subscription' === $membership_details['type'] ? '' : ''; ?>"
										name="ur_team_duration_period[<?php echo esc_attr( $g_index ); ?>]"
										style="width: 15%">
									<option
										value="day" <?php echo isset( $team_duration_period ) && 'day' === $team_duration_period ? 'selected="selected"' : ''; ?>>
										Day(s)
									</option>
									<option
										value="week" <?php echo isset( $team_duration_period ) && 'week' === $team_duration_period ? 'selected="selected"' : ''; ?>>
										Week(s)
									</option>
									<option
										value="month" <?php echo isset( $team_duration_period ) && 'month' === $team_duration_period ? 'selected="selected"' : ''; ?>>
										Month(s)
									</option>
									<option
										value="year" <?php echo isset( $team_duration_period ) && 'year' === $team_duration_period ? 'selected="selected"' : ''; ?>>
										Year(s)
									</option>
								</select>
							</div>
						</div>


					</div>
				</div>
				<div class="ur-team-fixed-seats-field" style="<?php echo ( 'fixed' === $seat_model ) ? '' : 'display:none;'; ?>">
					<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mb-5" style="gap:20px;">
						<div class="ur-label" style="width: 28%">
							<label for="ur-input-type-team-size-<?php echo esc_attr( $g_index ); ?>">
								<?php esc_html_e( 'Team Size', 'user-registration' ); ?>
							</label>
						</div>
						<div class="ur-input-type-team-size" style="width: 100%">
							<div class="ur-field" data-field-key="team_size">
								<input type="number"
										data-key-name="<?php esc_attr_e( 'Team Size', 'user-registration' ); ?>"
										id="ur-input-type-team-size-<?php echo esc_attr( $g_index ); ?>"
										name="ur_team_size[<?php echo esc_attr( $g_index ); ?>]"
										style="width: 20%"
										min="0"
										value="<?php echo esc_attr( $team['team_size'] ?? 0 ); ?>"
										placeholder="<?php esc_attr_e( 'e.g., 10', 'user-registration' ); ?>" />
							</div>
						</div>
					</div>

					<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mb-5" style="gap:20px;">
						<div class="ur-label" style="width: 28%">
							<label for="ur-input-type-team-pricing-<?php echo esc_attr( $g_index ); ?>">
								<?php esc_html_e( 'Team Pricing', 'user-registration' ); ?>
							</label>
						</div>
						<div class="ur-input-type-team-pricing" style="width: 100%">
							<div class="ur-field" data-field-key="team_pricing">
								<input type="number"
										data-key-name="<?php esc_attr_e( 'Team Pricing', 'user-registration' ); ?>"
										id="ur-input-type-team-pricing-<?php echo esc_attr( $g_index ); ?>"
										name="ur_team_pricing[<?php echo esc_attr( $g_index ); ?>]"
										style="width: 20%"
										min="0"
										value="<?php echo esc_attr( $team['team_price'] ?? 0 ); ?>" />
							</div>
						</div>
					</div>

				</div>

				<div class="ur-team-variable-seats-field" style="<?php echo ( 'variable' === $seat_model ) ? '' : 'display:none;'; ?>">
					<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mb-5" style="gap:20px;">
						<div class="ur-label" style="width: 28%">
							<label for="ur-input-type-minimum-seats-<?php echo esc_attr( $g_index ); ?>">
								<?php esc_html_e( 'Minimum Seats', 'user-registration' ); ?>
							</label>
						</div>
						<div class="ur-input-type-minimum-seats" style="width: 100%">
							<div class="ur-field" data-field-key="minimum_seats">
								<input type="number"
										data-key-name="<?php esc_attr_e( 'Minimum Seats', 'user-registration' ); ?>"
										id="ur-input-type-minimum-seats-<?php echo esc_attr( $g_index ); ?>"
										name="ur_minimum_seats[<?php echo esc_attr( $g_index ); ?>]"
										style="width: 20%"
										min="0"
										value="<?php echo esc_attr( $team['minimum_seats'] ?? 0 ); ?>"
										placeholder="<?php esc_attr_e( 'e.g., 5', 'user-registration' ); ?>" />
							</div>
						</div>
					</div>
					<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mb-5" style="gap:20px;">
						<div class="ur-label" style="width: 28%">
							<label for="ur-input-type-maximum-seats-<?php echo esc_attr( $g_index ); ?>">
								<?php esc_html_e( 'Maximum Seats', 'user-registration' ); ?>
							</label>
						</div>
						<div class="ur-input-type-maximum-seats" style="width: 100%">
							<div class="ur-field" data-field-key="maximum_seats">
								<input type="number"
										data-key-name="<?php esc_attr_e( 'Maximum Seats', 'user-registration' ); ?>"
										id="ur-input-type-maximum-seats-<?php echo esc_attr( $g_index ); ?>"
										name="ur_maximum_seats[<?php echo esc_attr( $g_index ); ?>]"
										style="width: 20%"
										min="0"
										value="<?php echo esc_attr( $team['maximum_seats'] ?? 0 ); ?>"
										placeholder="<?php esc_attr_e( 'e.g., 50', 'user-registration' ); ?>" />
							</div>
						</div>
					</div>

					<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mb-5" style="gap:20px;">
						<div class="ur-label" style="width: 28%">
							<label><?php esc_html_e( 'Pricing Model', 'user-registration' ); ?></label>
						</div>
						<div style="width: 100%">
							<div class="ur-team-toggle-tabs" style="width: 250px">
								<input type="radio" name="ur_pricing_model[<?php echo esc_attr( $g_index ); ?>]" value="per_seat" id="per-seat-<?php echo esc_attr( $g_index ); ?>" <?php echo ( 'per_seat' === $pricing_model ) ? 'checked="checked"' : ''; ?> />
								<label for="per-seat-<?php echo esc_attr( $g_index ); ?>" class="tab-label"><?php esc_html_e( 'Per Seat', 'user-registration' ); ?></label>

								<input type="radio" name="ur_pricing_model[<?php echo esc_attr( $g_index ); ?>]" value="tier" id="tier-<?php echo esc_attr( $g_index ); ?>" <?php echo ( 'tier' === $pricing_model ) ? 'checked="checked"' : ''; ?> />
								<label for="tier-<?php echo esc_attr( $g_index ); ?>" class="tab-label"><?php esc_html_e( 'Create Tiers', 'user-registration' ); ?></label>
							</div>
						</div>
					</div>

					<div class="ur-team-per-seats-field" style="<?php echo ( 'per_seat' === $pricing_model ) ? '' : 'display:none;'; ?>">
						<div class="ur-membership-input-container ur-d-flex ur-p-1 ur-mb-5" style="gap:20px;">
							<div class="ur-label" style="width: 28%">
								<label for="ur-input-type-per-seat-pricing-<?php echo esc_attr( $g_index ); ?>">
									<?php esc_html_e( 'Pricing (Per Seat)', 'user-registration' ); ?>
								</label>
							</div>
							<div class="ur-input-type-per-seat-pricing" style="width: 100%">
								<div class="ur-field" data-field-key="per_seat_pricing">
									<input type="number"
											data-key-name="<?php esc_attr_e( 'Per seat pricing', 'user-registration' ); ?>"
											id="ur-input-type-per-seat-pricing-<?php echo esc_attr( $g_index ); ?>"
											name="ur_per_seat_pricing[<?php echo esc_attr( $g_index ); ?>]"
											style="width: 20%"
											min="0"
											value="<?php echo esc_attr( $team['per_seat_price'] ?? 0 ); ?>" />
								</div>
							</div>
						</div>
					</div>

							<div id="" class="ur-team-tier-field ur-mt-5" style="<?php echo ( 'tier' === $pricing_model ) ? '' : 'display:none;'; ?>">
								<?php if ( empty( $tiers ) ) : ?>
									<div class="ur-team-tier-field-wrapper ur-d-flex ur-align-items-end ur-mb-5" data-tier-wrapper-id="0" style="gap:20px">
										<div class="ur-membership-input-container ur-flex-grow-1">
											<div class="ur-label ur-mb-3">
												<label> <?php esc_html_e( 'From', 'user-registration' ); ?> </label>
											</div>
											<div class="ur-input-type-tier-from">
												<div class="ur-field" data-field-key="tier_from">
													<input type="number" data-key-name="<?php esc_attr_e( 'Tier From', 'user-registration' ); ?>" name="ur_tier_from[0][0]" style="width: 100%" min="0" value="0" />
												</div>
											</div>
										</div>

										<div class="ur-membership-input-container ur-flex-grow-1">
											<div class="ur-label ur-mb-3">
												<label> <?php esc_html_e( 'To', 'user-registration' ); ?> </label>
											</div>
											<div class="ur-input-type-tier-to">
												<div class="ur-field" data-field-key="tier_to">
													<input type="number" data-key-name="<?php esc_attr_e( 'Tier To', 'user-registration' ); ?>" name="ur_tier_to[0][0]" style="width: 100%" min="0" value="0" />
												</div>
											</div>
										</div>

										<div class="ur-membership-input-container ur-flex-grow-1">
											<div class="ur-label ur-mb-3">
												<label> <?php esc_html_e( 'Per Seat Price', 'user-registration' ); ?> </label>
											</div>
											<div class="ur-input-type-tier-per-seat-price">
												<div class="ur-field" data-field-key="tier_per_seat_price">
													<input type="number" data-key-name="<?php esc_attr_e( 'Tier Per Seat Price', 'user-registration' ); ?>" name="ur_tier_per_seat_price[0][0]" style="width: 100%" min="0" value="0" />
												</div>
											</div>
										</div>

								<button title="<?php echo esc_attr_x( 'Remove Tier', 'button title', 'user-registration' ); ?>" class="button button-icon button-danger ur-remove-tier-btn">
									<span class="dashicons dashicons-no-alt"></span>
								</button>
							</div>
						<?php else : ?>
							<?php foreach ( $tiers as $t_index => $tier ) : ?>
								<div class="ur-team-tier-field-wrapper ur-d-flex ur-align-items-end ur-mb-5" data-tier-wrapper-id="<?php echo esc_attr( $t_index ); ?>" style="gap:20px">
									<div class="ur-membership-input-container ur-flex-grow-1">
										<div class="ur-label ur-mb-3">
											<label><?php esc_html_e( 'From', 'user-registration' ); ?></label>
										</div>
										<div class="ur-input-type-tier-from">
											<div class="ur-field" data-field-key="tier_from">
												<input type="number"
														data-key-name="<?php esc_attr_e( 'Tier From', 'user-registration' ); ?>"
														name="ur_tier_from[<?php echo esc_attr( $g_index ); ?>][<?php echo esc_attr( $t_index ); ?>]"
														style="width: 100%"
														min="0"
														value="<?php echo esc_attr( $tier['tier_from'] ?? 0 ); ?>" />
											</div>
										</div>
									</div>

									<div class="ur-membership-input-container ur-flex-grow-1">
										<div class="ur-label ur-mb-3">
											<label><?php esc_html_e( 'To', 'user-registration' ); ?></label>
										</div>
										<div class="ur-input-type-tier-to">
											<div class="ur-field" data-field-key="tier_to">
												<input type="number"
														data-key-name="<?php esc_attr_e( 'Tier To', 'user-registration' ); ?>"
														name="ur_tier_to[<?php echo esc_attr( $g_index ); ?>][<?php echo esc_attr( $t_index ); ?>]"
														style="width: 100%"
														min="0"
														value="<?php echo esc_attr( $tier['tier_to'] ?? 0 ); ?>" />
											</div>
										</div>
									</div>

									<div class="ur-membership-input-container ur-flex-grow-1">
										<div class="ur-label ur-mb-3">
											<label><?php esc_html_e( 'Per Seat Price', 'user-registration' ); ?></label>
										</div>
										<div class="ur-input-type-tier-per-seat-price">
											<div class="ur-field" data-field-key="tier_per_seat_price">
												<input type="number"
														data-key-name="<?php esc_attr_e( 'Tier Per Seat Price', 'user-registration' ); ?>"
														name="ur_tier_per_seat_price[<?php echo esc_attr( $g_index ); ?>][<?php echo esc_attr( $t_index ); ?>]"
														style="width: 100%"
														min="0"
														value="<?php echo esc_attr( $tier['tier_per_seat_price'] ?? 0 ); ?>" />
											</div>
										</div>
									</div>

									<button title="<?php echo esc_attr_x( 'Remove Tier', 'button title', 'user-registration' ); ?>" class="button button-icon button-danger ur-remove-tier-btn">
										<span class="dashicons dashicons-no-alt"></span>
									</button>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>


						<div class="ur-text-center ur-add-tier-btn-wrapper">
							<button class="button button-secondary ur-add-tier-btn">
								+ <?php esc_html_e( 'Add Tier', 'user-registration' ); ?>
							</button>
						</div>
					</div>
				</div>

			</div>
		</div>
	<?php endforeach; ?>

			<div class="ur-text-center" id="ur-add-team-pricing-btn-wrapper">
				<button class="button button-secondary" id="ur-add-team-pricing-btn">
					+ <?php esc_html_e( 'Add Another Team Pricing', 'user-registration' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>


