<?php
/**
 * LDGR Group Users [wdm_group_users] shortcode tabs wrapper display template
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates/ldgr-group-users
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<div id = "wdm_groups_tab" class="wdm-tabs-wrapper">

		<div class="wdm-tabs-inner-links">
			<ul class="tabs">
				<?php foreach ( $tab_headers as $header ) : ?>
					<?php
					if ( $this->not_required_tab( $header ) ) {
						continue; }
					?>
					<li class="tab-link current" data-tab="tab-<?php echo esc_html( $header['id'] ); ?>">
						<a href="#" class="wdm-for-desktop">
							<?php
								echo esc_html(
									apply_filters(
										$header['slug'],
										$header['title']
									)
								);
							?>
						</a>
						<a href="#" class="wdm-for-mobile">
							<img src="<?php echo esc_url( $header['icon'] ); ?>">
							<?php
								echo esc_html(
									apply_filters(
										$header['slug'],
										$header['title']
									)
								);
							?>
						</a>
					</li>
				<?php endforeach; ?>

				<span id="wdm-border-bottom"></span>

			</ul>
		</div>

		<?php foreach ( $tab_contents as $content ) : ?>
			<?php do_action( 'ldgr_action_before_group_tab_' . $content['id'], $content ); ?>
			<?php include( $content['template'] ); ?>
			<?php do_action( 'ldgr_action_after_group_tab_' . $content['id'], $content ); ?>
		<?php endforeach; ?>

</div> <!-- End of tabs-wrapper -->
