<?php
/**
 * Form View: Input Type User Shipping Company
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ur-input-type-shipping-city ur-admin-template">
	
	<div class="ur-label">
		<label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

	</div>
	<div class="ur-field" data-field-key="shipping_city">

		<input type="text" id="ur-input-type-shipping-city"
			   placeholder="<?php echo esc_attr($this->get_general_setting_data( 'placeholder' )); ?>" disabled/>

	</div>
	<?php

	UR_Form_Field_Shipping_City::get_instance()->get_setting();

	?>
</div>

