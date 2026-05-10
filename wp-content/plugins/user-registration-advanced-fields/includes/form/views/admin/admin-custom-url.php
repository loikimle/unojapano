<?php
/**
 * Form View: Custom URL
 *
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-url ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>

	</div>
	<div class="ur-field" data-field-key="custom_url">
		<input type="url" id="ur-input-type-custom-url" disabled>
	</div>
	<?php

	UR_Form_Field_Custom_Url::get_instance()->get_setting();

	?>
</div>
