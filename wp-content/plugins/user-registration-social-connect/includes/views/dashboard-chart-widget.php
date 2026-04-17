<?php
/**
 * Dashboard Chart widget for users conected through social media.
 *
 * @author  WPEverest
 * @package UserRegistrationSocialConnect/includes/views
 * @since   1.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
	<div class="ursc-dashboard-widget">
		<div class="ursc-loading">
			<div class="spinner"></div>
		</div>
		<div class="ursc-chart" style="display:none">
			<canvas id="ursc-chart-report-area">Your browser does not support the canvas element.</canvas>
			<div class="ursc-chart-report-legends"></div>
			<div class="ursc-dashboard-widget-statictics">
				<span class="ursc-total-social-reg">Total Social Registration: <span class="ursc-total">0</span></span>
			</div>
		</div>
		<div class='no-user-found' style="display:none">
			<img src="<?php echo plugins_url( '/', URSC_PLUGIN_FILE ) . 'assets/images/empty-registration.png'; ?>" alt="No User Found!" />
			<span class="no-user-message">There is no registration yet!</span>
		</div>
	</div>
<?php
	/**
	 * Dashboard Widget.
	 *
	 * @since 1.2.1
	 */
	do_action( 'user_registration_social_connect_dashboard_chart_widget_end' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
