<?php
/**
 * Enrolled users tab contents display template
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates/ldgr-group-users/tabs
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<div id="tab-1" class="tab-content current">
	<input type='button' id='bulk_remove' value='<?php esc_html_e( 'Bulk Remove', 'wdm_ld_group' ); ?>'>
	<table id='wdm_group'>
		<thead>
			<tr>
				<th><input type="checkbox" name="select_all" class="bb-custom-check"></th>
				<th><?php esc_html_e( 'Name', 'wdm_ld_group' ); ?></th>
				<th><?php esc_html_e( 'Email', 'wdm_ld_group' ); ?></th>
				<th><?php esc_html_e( 'Action', 'wdm_ld_group' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Put in a method.
			$default = array( 'removal_request' => array() );
			if ( ! empty( $users ) ) {
				$removal_request['removal_request'] = maybe_unserialize( get_post_meta( $group_id, 'removal_request', true ) );
				$removal_request                    = array_filter( $removal_request );

				$removal_request = wp_parse_args( $removal_request, $default );
				$removal_request = $removal_request['removal_request'];

				$ldgr_reinvite_user = get_option( 'ldgr_reinvite_user' );
				$reinvite_class_data = 'wdm-reinvite';
				$reinvite_text_data  = apply_filters( 'wdm_change_reinvite_label', __( 'Re-Invite', 'wdm_ld_group' ) );

				foreach ( $users as $k => $value ) {
					$user_data = get_user_by( 'id', $value );
					?>
					<tr>
						<td class="select_action">
							<input
								type="checkbox"
								name="bulk_select"
								data-user_id ="<?php echo esc_html( $value ); ?>"
								data-group_id="<?php echo esc_html( $group_id ); ?>">
						</td>
						<td data-title="Name">
							<p>
							<?php
								echo esc_html( get_user_meta( $value, 'first_name', true ) . ' ' . get_user_meta( $value, 'last_name', true ) );
							?>
							</p>
						</td>
						<td data-title="Email">
							<p><?php echo esc_html( $user_data->user_email ); ?></p>
						</td>
						<?php
						if ( ! in_array( $value, $removal_request ) ) {
							$class_data = 'wdm_remove';
							$text_data  = __( 'Remove', 'wdm_ld_group' );
						} else {
							$class_data = 'request_sent';
							$text_data  = __( 'Request sent', 'wdm_ld_group' );
						}
						?>
						<td class="ldgr-actions">
							<?php if ( 'on' == $ldgr_reinvite_user ) { ?>
								<a
									href="#"
									data-user_id ="<?php echo esc_html( $value ); ?>"
									data-group_id="<?php echo esc_html( $group_id ); ?>"
									class="<?php echo esc_html( $reinvite_class_data ); ?> button">
									<?php echo esc_html( $reinvite_text_data ); ?>
								</a>
								&nbsp;
							<?php } ?>
							<?php if ( apply_filters( 'wdm_ldgr_remove_user_button', true, $value, $group_id ) ) { ?>
								<a
									href="#"
									data-user_id ="<?php echo esc_html( $value ); ?>"
									data-group_id="<?php echo esc_html( $group_id ); ?>"
									class="<?php echo esc_html( $class_data ); ?> button">
									<?php echo esc_html( $text_data ); ?>
								</a>
							<?php } ?>
							<?php do_action( 'ldgr_group_row_action', $value, $group_id ); ?>
						</td>
					</tr>
					<?php
				}
			}
			?>
		</tbody>
	</table>
</div>

</form><!-- End of first Tab  -->
