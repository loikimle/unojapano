<?php
/**
 * EDD Group Registration Metabox Template
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<table>
	<tr>
		<th style="text-align: left;">
			<?php
			echo esc_html(
				apply_filters(
					'wdm_group_registration_label',
					__( 'Enable Group registration', WDM_LDGR_TXT_DOMAIN )
				)
			);
			?>
			 : </th>
		<td>
			<input
				type="checkbox"
				id="wdm_ld_group_registration"
				name="wdm_ld_edd_group_registration" <?php echo ( '' != $value ) ? 'checked' : ''; ?>>
		</td>
	</tr>
</table>
<table class="wdm_show_other_option">
	<tr>
		<th>
			<?php
				echo esc_html(
					apply_filters(
						'wdm_show_front_label',
						__( "Allow users to check 'Enable Group Registration' on the Front End", WDM_LDGR_TXT_DOMAIN )
					)
				);
				?>
			 : </th>
		<td>
			<input
				type="checkbox"
				id="wdm_show_front_option"
				name="wdm_ld_edd_group_registration_show_front_end" 
				<?php echo ( '' != $value_show ) ? 'checked' : ''; ?>>
		</td>
	</tr>
	<tr class="wdm-default-front-option">
		<td colspan="2">
			<span style="font-weight: bold;">
				<?php esc_html_e( 'Default Option : ', WDM_LDGR_TXT_DOMAIN ); ?>
			</span>
			<input
				type="radio"
				name="wdm_ld_group_active"
				value="individual" <?php echo ( 'individual' == $default_option ) ? 'checked' : ''; ?> >
					<?php echo esc_html( apply_filters( 'wdm_gr_single_label', __( 'Individual', WDM_LDGR_TXT_DOMAIN ) ) ); ?>
			<input
				type="radio"
				name="wdm_ld_group_active"
				value="group" <?php echo ( 'individual' != $default_option ) ? 'checked' : ''; ?> >
					<?php echo esc_html( apply_filters( 'wdm_gr_group_label', __( 'Group', WDM_LDGR_TXT_DOMAIN ) ) ); ?>
		</td>
	</tr>
	<tr>
		<th style="text-align: left;">
			<?php
			echo esc_html(
				apply_filters(
					'wdm_local_pay_course_label',
					__( 'Ask Group Leader to pay for course', WDM_LDGR_TXT_DOMAIN )
				)
			);
			?>
			: </th>
		<td>
			<input
				type="checkbox"
				name="wdm_ldgr_edd_paid_course" <?php echo ( 'on' == $paid_course ) ? 'checked' : ''; ?> />
		</td>
	</tr>
</table>
