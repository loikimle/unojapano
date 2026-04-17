<?php
/**
 * Template : LDGR group name box
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<br>
<br>
<div class="<?php echo esc_html( $group_section_classes ); ?>" <?php echo ( 'individual' == $default_option ) ? 'style="display:none;"' : ''; ?>>
	<label for="ldgr_group_name">
		<strong><?php esc_html_e( 'Group Name', WDM_LDGR_TXT_DOMAIN ); ?></strong>
	</label>
	<?php if ( empty( $variation_ids ) ) : ?>
		<input
			type="text"
			name="ldgr_group_name"
			value="<?php echo esc_html( $group_name ); ?>"
			placeholder="<?php esc_html_e( 'Enter a name for your Group', WDM_LDGR_TXT_DOMAIN ); ?>"
			data-product-id = "<?php echo esc_html( $product_id ); ?>"
			<?php echo empty( $group_name ) ? '' : 'readonly'; ?>
		/>
	<?php else : ?>
		<?php foreach ( $group_name as $variation => $details ) : ?>
			<input
				id="<?php echo esc_html( 'ldgr_variation_' . $variation ); ?>"
				class="ldgr_variation_group_options <?php echo esc_html( $this->check_for_default_variation_class( $variation, $default_attributes ) ); ?>"
				type="<?php echo ( $details['in_cart'] && empty( $details['value'] ) ) ? 'hidden' : 'text'; ?>"
				name="<?php echo esc_html( 'ldgr_group_name_' . $variation ); ?>"
				value="<?php echo esc_html( $details['value'] ); ?>"
				placeholder="<?php esc_html_e( 'Enter a name for your Group', WDM_LDGR_TXT_DOMAIN ); ?>"
				data-product-id = "<?php echo esc_html( $variation ); ?>"
				<?php echo empty( $details['value'] ) ? '' : 'readonly'; ?>
			/>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
