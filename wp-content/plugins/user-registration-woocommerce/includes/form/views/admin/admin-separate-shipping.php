<?php
/**
 * Form View: Input Type Separate_Shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$choices = isset( $this->admin_data->advance_setting->choices ) ? explode( ',', trim( $this->admin_data->advance_setting->choices, ',' ) ) : array();
?>

<div class="ur-input-type-separate-shipping ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>

	</div>
	<div class="ur-field" data-field-key="separate_shipping">

		<?php
		if(count($choices)<1){
			echo "<input type = 'checkbox'  value='1' disabled/>";
		}
		foreach ( $choices as $choice ) {
			echo "<input type = 'checkbox'  value='" . esc_attr( $choice ) . "' disabled/>" . esc_html( trim( $choice ) ) . '<br>';
		}
		?>

	</div>
	<?php

	UR_Form_Field_Separate_Shipping::get_instance()->get_setting();

	?>
</div>

