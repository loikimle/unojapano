<?php
/**
 * LDGR Group Users [wdm_group_users] shortcode display template
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates/ldgr-group-users
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<form id='wdm_search_submit' method='post'>

	<?php // $this->display_logo(); ?>

	<div class='wdm-notification-messages'>
		<?php $this->show_notification_messages(); ?>
	</div>

	<div class="wdm-select-wrapper">
		<?php $this->show_group_select_wrapper( $group_id, $subscription_id, $need_to_restrict, $sub_current_status ); ?>
		<?php do_action( 'wdm_after_select_product', $group_id, $group_limit ); ?>
	</div>

	<?php if ( ! $need_to_restrict ) : ?>
		<?php $this->show_group_registrations_tabs( $group_id, $need_to_restrict ); ?>
	<?php endif; ?>
