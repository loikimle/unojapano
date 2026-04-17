<?php
/**
 * Form View: Range
 *
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$style = "display:none;";

if ( "true" === $this->get_advance_setting_data( 'enable_prefix_postfix' ) ) {
	$style = "";
}

$payment = "display:none";

if ( "true" === $this->get_advance_setting_data( 'enable_payment_slider' ) ) {
	$payment = "";
}

?>
<div class="ur-input-type-range ur-admin-template">

	<div class="ur-label">
		<label><?php echo esc_html( $this->get_general_setting_data( 'label' ) ); ?></label>

	</div>
	<?php if ( class_exists( 'User_Registration_Payments' )) { ?>
	<span class="ur-payment-slider-label ur-payment-slider-sign" style=<?php esc_attr_e( $payment ); ?>>
	<?php $currency   = get_option( 'user_registration_payment_currency', 'USD' );
		  $currencies = ur_payment_integration_get_currencies();
		   echo ("true" === $this->get_advance_setting_data( 'enable_payment_slider' ) ? esc_html( $currency . ' ' . $currencies[ $currency ]['symbol'] ) : esc_html( $currency . ' ' . $currencies[ $currency ]['symbol'] ) ) ?>
	</span>
	<?php } ?>
	<div class="ur-field" data-field-key="range">
		<div class="ur-admin-range-row">
			<div class="ur-admin-range-field-sec">
				<span class="ur-range-slider-label ur-range-slider-prefix" style=<?php esc_attr_e( $style ); ?> ><?php echo ( "null" !== $this->get_advance_setting_data( 'range_prefix' ) && "true" === $this->get_advance_setting_data( 'enable_text_prefix_postfix' ) ) ? $this->get_advance_setting_data( 'range_prefix' ) : ( $this->get_advance_setting_data( 'range_min' ) ? $this->get_advance_setting_data( 'range_min' ) : "0" ); ?></span>

				<input type="range" id="ur-input-type-range" disabled />
				<span class="ur-range-slider-label ur-range-slider-postfix" style=<?php esc_attr_e( $style ); ?> ><?php echo ( "null" !== $this->get_advance_setting_data( 'range_postfix' ) && "true" === $this->get_advance_setting_data( 'enable_text_prefix_postfix' ) ) ? $this->get_advance_setting_data( 'range_postfix' ) : ( $this->get_advance_setting_data( 'range_max' ) ? $this->get_advance_setting_data( 'range_max' ) : "10" ); ?></span>
		    </div>
		    <div class="ur-admin-range-number">
				<input type="number" class="ur-range-input" disabled/>
				<span class="ur-range-slider-reset-icon dashicons dashicons-image-rotate"></span>
			</div>
	  	</div>
	</div>

	<?php

	UR_Form_Field_Range::get_instance()->get_setting();

	?>
</div>
