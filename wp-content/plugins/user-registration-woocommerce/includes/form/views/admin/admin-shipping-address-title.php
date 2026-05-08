<?php
/**
 * Form View: Section Title
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-shipping-address-title ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>

	<div class="ur-field" data-field-key="shipping_address_title">
	</div>
	
	<?php
		UR_Form_Field_Shipping_Address_Title::get_instance()->get_setting();
	?>
</div>