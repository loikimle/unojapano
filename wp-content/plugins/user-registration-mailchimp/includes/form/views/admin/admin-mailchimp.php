<?php
/**
 * Form View: MailChimp.
 *
 * @package  UserRegistrationMailChimp/Form/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ur-input-type-mailchimp ur-admin-template">

	<div class="ur-label">

		<label><?php echo $this->get_general_setting_data( 'label' ); ?></label>

	</div>
	<div class="ur-field" data-field-key="mailchimp">

		<input id="ur-input-type-mailchimp" type="checkbox" disabled/>

	</div>
	<?php

	UR_MailChimp::get_instance()->get_setting();

	?>
	<div style="clear:both"></div>
</div>
