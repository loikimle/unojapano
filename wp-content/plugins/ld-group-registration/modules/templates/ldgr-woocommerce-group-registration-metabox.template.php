<?php
/**
 * LDGR Woocommerce Group Registration Metabox Template
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<?php do_action( 'ldgr_woo_product_metabox_start', $post ); ?>
<table>
	<tr>
		<td>
			<input
				type="checkbox"
				id="wdm_ld_group_registration"
				name="wdm_ld_group_registration"
				<?php echo ( $value != '' ) ? 'checked' : ''; ?>
			/>
		</td>
		<th style="text-align: left;">
			<?php echo apply_filters( 'wdm_group_registration_label', __( 'Enable Group registration', WDM_LDGR_TXT_DOMAIN ) ); ?>
		</th>
	</tr>
</table>

<table class="wdm_show_other_option" style="padding: 10px;">
	<tr>
		<td>
			<input
				type="checkbox"
				id="wdm_show_front_option"
				name="wdm_ld_group_registration_show_front_end"
				<?php echo ( $value_show != '' ) ? 'checked' : ''; ?>
			/>
		</td>
		<td>
			<?php
				echo apply_filters(
					'wdm_show_front_label',
					__( "Allow users to check <strong><em>'Enable Group Registration'</em></strong> on the Front End", WDM_LDGR_TXT_DOMAIN )
				);
				?>
		</td>
	</tr>
	<tr class="wdm-default-front-option">
		<td></td>
		<td colspan="2">
			<p style="font-weight: bold;">
				<?php _e( 'What would be the default option ? ', WDM_LDGR_TXT_DOMAIN ); ?>
			</p>
			<p>
				<input
					type="radio"
					name="wdm_ld_group_active"
					value="individual"
					<?php echo ( $default_option == 'individual' ) ? 'checked' : ''; ?> 
				/>
				<?php echo apply_filters( 'wdm_gr_single_label', __( 'Individual', WDM_LDGR_TXT_DOMAIN ) ); ?>
				<input
					type="radio"
					name="wdm_ld_group_active"
					value="group"
					<?php echo ( $default_option != 'individual' ) ? 'checked' : ''; ?>
				/>
				<?php echo apply_filters( 'wdm_gr_group_label', __( 'Group', WDM_LDGR_TXT_DOMAIN ) ); ?>
			</p>
		</td>
	</tr>
	<tr>
		<td>
			<input
				type="checkbox"
				name="wdm_ldgr_paid_course"
				<?php echo ( $paid_course == 'on' ) ? 'checked' : ''; ?>
			/>
		</td>
		<td style="text-align: left;">
			<?php
				echo apply_filters(
					'wdm_local_pay_course_label',
					__( 'Ask Group Leader to pay for course access', WDM_LDGR_TXT_DOMAIN )
				);
			?>
		</td>
	</tr>
	<tr>
        <td>
            <input
                type="checkbox"
                name="ldgr_enable_unlimited_members"
                id="ldgr_enable_unlimited_members"
                <?php echo ($is_unlimited == 'on') ? 'checked' : '';?>
            />
        </td>
        <td style="text-align: left;">
            <?php
                echo apply_filters(
                    'ldgr_enable_unlimited_members_label',
                    __('Unlimited Members', WDM_LDGR_TXT_DOMAIN)
                );
            ?>
        </td>
    </tr>
    <tr class="ldgr-unlimited-group-members-settings" <?php echo ( $is_unlimited !== 'on' ) ? 'style="display: none;"' : ''; ?>">
        <td>
		</td>
        <td>
			<!-- <p>
                <label for="ldgr_unlimited_members_option_label">
                    <?php _e('Label for unlimited members', WDM_LDGR_TXT_DOMAIN) ?> :
                </label>
                <input type="text" name="ldgr_unlimited_members_option_label" class="text" id="ldgr_unlimited_members_option_label" value="<?php echo $unlimited_label;?>"/>
            </p> -->
            <p>
                <label for="ldgr_unlimited_members_option_price">
                    <?php _e('Price for unlimited members', WDM_LDGR_TXT_DOMAIN) ?> :
                </label>
                <input type="text" name="ldgr_unlimited_members_option_price" class="text wc_input_price" id="ldgr_unlimited_members_option_price" value="<?php echo $unlimited_price;?>"/>
            </p>
        </td>
    </tr>
</table>
<?php do_action( 'ldgr_woo_product_metabox_end', $post ); ?>
