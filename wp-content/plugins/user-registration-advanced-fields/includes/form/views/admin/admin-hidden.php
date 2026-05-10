<?php
/**
 * Form View: Hidden
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ur-input-type-hidden ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>

	</div>
	<div class="ur-field" data-field-key="hidden">

		<input type="text" id="ur-input-type-hidden" placeholder="<?php echo esc_attr( $this->get_general_setting_data( 'placeholder' ) ); ?>" disabled />

	</div>

	<?php

	UR_Form_Field_hidden::get_instance()->get_setting();

	?>
</div>
