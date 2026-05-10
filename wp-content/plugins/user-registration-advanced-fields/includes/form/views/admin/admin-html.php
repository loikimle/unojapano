<?php
/**
 * Form View: HTML
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ur-input-type-textarea ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>
	</div>

	<div class="ur-field" data-field-key="html">
		<div class="description"><?php esc_html_e( 'Contents of this field are not displayed in the admin area.', 'user-registration-advanced-fields' ); ?></div>
	</div>
	<?php

	UR_Form_Field_Html::get_instance()->get_setting();

	?>
</div>
