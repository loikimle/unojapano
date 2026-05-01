<?php
/**
 * Form View: Country
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$instance = UR_Form_Field_Billing_Country::get_instance();
?>
<div class="ur-input-type-billing-country ur-admin-template">

    <div class="ur-label">
        <label><?php echo esc_html($this->get_general_setting_data( 'label' )); ?></label>

    </div>
    <div class="ur-field" data-field-key="billing_country">

        <select id="ur-input-type-country" disabled>
			<option>Select a country...</option>
        </select>

    </div>
    <?php


    $instance->get_setting();

    ?>
</div>

