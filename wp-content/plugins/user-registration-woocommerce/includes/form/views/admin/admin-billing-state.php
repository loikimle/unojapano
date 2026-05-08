<?php
/**
 * Form View: Input Type User Billing Company
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ur-input-type-billing-state ur-admin-template">
	
	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="billing_state">

		<input type="text" id="ur-input-type-billing-state"
			   placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>" disabled/>

	</div>
	<?php

	UR_Form_Field_Billing_State::get_instance()->get_setting();

	?>
</div>

