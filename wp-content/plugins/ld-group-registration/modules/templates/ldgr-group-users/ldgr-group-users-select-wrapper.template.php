<?php
/**
 * LDGR Group Users [wdm_group_users] shortcode group select wrapper display template
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates/ldgr-group-users
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<div class="wdm-select-wrapper-content">

	<?php if ( ! $need_to_restrict ) : ?>
		<div class="ldgr-group-settings-wrap">
			<img
				class="ldgr-group-settings-icon"
				src="<?php echo esc_url( plugins_url( 'media/gear.svg', dirname( __DIR__ ) ) ); ?>"
				alt="group settings" />
			<?php do_action('ldgr_actions_list', $group_id); ?>
		</div>
	<?php endif; ?>		

	<h3>
		<?php
			echo esc_html(
				apply_filters(
					'wdm_group_selection_dropdown_label',
					__( 'Group', 'wdm_ld_group' )
				)
			);
			?>
	</h3>

	<select name="wdm_group_id">
		<?php $Ld_Group_Registration_Groups->display_group_select_list_html( $group_id, $group_ids, $user_data ); ?>
	</select>

	<?php $Ld_Group_Registration_Groups->show_subscription_errors( $need_to_restrict, $subscription_id, $sub_current_status ); ?>

	<?php if ( ! $need_to_restrict ) : ?>

		<div class="wdm-registration-wrapper">
			<?php if ( apply_filters( 'wdm_display_group_user_count', true ) ) : ?>
				<p class='wdm-registration-left'>
					<?php
					echo esc_html(
						apply_filters(
							'wdm_registration_left_label',
							__( 'Users Registration Left', 'wdm_ld_group' )
						)
					);
					?>
					 :
					<?php
					echo ( $is_unlimited ) ? esc_html(
							apply_filters(
								'ldgr_unlimited_seats_label',
								__( 'Unlimited', WDM_LDGR_TXT_DOMAIN ),
								$group_id
							)
						) : esc_html(
							apply_filters(
								'wdm_registration_left_count_disp',
								$grp_limit_count,
								$group_id
							)
						);
					?>
				</p>
			<?php endif; ?>

			<?php if ( apply_filters( 'wdm_display_group_add_users_link', true ) ) : ?>
				<?php $Ld_Group_Registration_Groups->add_new_users_link( $group_id ); ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
<div>
	<div class="ldgr-group-actions" style="display:none;">
		<div>
			<label for="ldgr-edit-group-name"><?php esc_html_e( 'Group Name', 'wdm_ld_group' ); ?></label>
			<input
				type="text"
				name="ldgr-edit-group-name"
				data-group_id="<?php echo $group_id; ?>"
				value="<?php echo esc_html( $Ld_Group_Registration_Groups->get_selected_group_name( $group_id, $user_data ) ); ?>"/>
		</div>
		<br>
		<button id="ldgr-update-group-details"><?php esc_html_e( 'Update', 'wdm_ld_group' ); ?></button>
	</div>
	<?php if ( 'on' == $ldgr_group_courses ) : ?>
		<div class="wdm_group_course_detail">
			<p class="wdm_course_list_title">
				<?php
					echo esc_html(
						apply_filters(
							'wdm_course_list_title',
							__( 'Associated Courses', 'wdm_ld_group' ),
							$group_id
						)
					);
				?>
			</p>
			<?php if ( empty( $group_courses ) ) : ?>
				<p class='wdm_no_course_message'>
					<?php
						echo esc_html(
							apply_filters(
								'wdm_no_course_message',
								__( 'No Courses Found.', 'wdm_ld_group' ),
								$group_id
							)
						);
					?>
				</p>
			<?php else : ?>
				<ul>
					<?php foreach ( $group_courses as $course_id ) : ?>
						<li><?php echo esc_html( get_the_title( $course_id ) ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
