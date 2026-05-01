<?php
/**
 * Form View: Input Type User Billing Company
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ur-input-type-billing-address-2 ur-admin-template">
	
	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="billing_address_2">

		<input type="text" id="ur-input-type-billing-address-2"
			   placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>" disabled/>

	</div>
	<?php

	UR_Form_Field_Billing_Address_2::get_instance()->get_setting();

	?>
</div>

