<?php
/**
 * Form View: Input Type User Billing Company
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ur-input-type-billing-company ur-admin-template">
	
	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="billing_company">

		<input type="text" id="ur-input-type-billing-company"
			   placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>" disabled/>

	</div>
	<?php

	UR_Form_Field_Billing_Company::get_instance()->get_setting();

	?>
</div>

