<?php
/**
 * Template : Group Registrations Left Metabox
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<table>
	<tr>
		<th><?php echo esc_html( __( 'Group Registrations left', 'wdm_ld_group' ) ); ?> : </th>
		<td><input type="number" min="0" name="wdm_ld_group_registration_left" value="<?php echo ( $is_unlimited ) ? 99999 : esc_html( $group_limit ); ?>"></td>
	</tr>
</table>
<h2 class="ldgr-removal-req-header">
	<?php
		echo esc_html( __( 'User Removal Request', 'wdm_ld_group' ) );
	?>
</h2>
<div class="ldgr-bulk-actions">
	<input type='button' class = 'button' id='bulk_accept' value='<?php echo esc_html( __( 'Bulk Accept', 'wdm_ld_group' ) ); ?>'>
	<input type='button' class = 'button' id='bulk_reject' value='<?php echo esc_html( __( 'Bulk Reject', 'wdm_ld_group' ) ); ?>'>
</div>
<table id="wdm_admin">
	<thead>
		<th class="ldgr-bulk-select"><input type="checkbox" name="select_all"></th>
		<th><?php echo esc_html( __( 'Username', 'wdm_ld_group' ) ); ?></th>
		<th><?php echo esc_html( __( 'Action', 'wdm_ld_group' ) ); ?></th>
	</thead>
	<tbody>
	<?php if ( ! empty( $removal_request ) ) : ?>
		<?php foreach ( $removal_request as $key => $user_id ) : ?>
			<?php $user_data = get_user_by( 'id', $user_id ); ?>
				<tr>
					<td class="select_action ldgr-bulk-select">
						<input
							type="checkbox"
							name="bulk_select"
							data-user_id ="<?php echo esc_html( $user_id ); ?>"
							data-group_id="<?php echo esc_html( $group_id ); ?>"
						/>
					</td>
					<td>
						<center>
							<?php echo esc_html( $user_data->user_email ); ?>
						</center>
					</td>
					<td>
						<center>
							<a
								href="#"
								data-user_id="<?php echo esc_html( $user_id ); ?>"
								data-group_id="<?php echo esc_html( $group_id ); ?>"
								class="button wdm_accept">
								<?php echo esc_html( __( 'Accept', 'wdm_ld_group' ) ); ?>
							</a>
							<a
								href="#"
								data-user_id="<?php echo esc_html( $user_id ); ?>"
								data-group_id="<?php echo esc_html( $group_id ); ?>"
								class="button wdm_reject">
								<?php echo esc_html( __( 'Reject', 'wdm_ld_group' ) ); ?>
							</a>
						</center>
					</td>
				</tr>
			<?php unset( $key ); ?>
		<?php endforeach; ?>
	<?php endif; ?>
	</tbody>
</table>
