<?php
/**
 * Background Cron action
 *
 * @file The Cron file
 * @package HMWP/Cron
 * @since 4.0.0
 */

class HMWP_Controllers_Cron {


	public function __construct() {
		add_action( HMWP_CRON_ONCE, array ( $this, 'processCronOnce' ) );
	}

	/**
	 * Register the cron once.
	 */
	public function registerOnce() {

		// Clear any pending once event so you can re-schedule reliably
		wp_clear_scheduled_hook( HMWP_CRON_ONCE );

		// Give it a small delay to avoid edge cases with "time()"
		wp_schedule_single_event( time() , HMWP_CRON_ONCE );

	}

	/**
	 * Register the cron interval.
	 */
	public function registerInterval() {

		add_filter( 'cron_schedules', array( $this, 'setInterval' ) );

		//Activate the cron job if not exists.
		if ( ! wp_next_scheduled( HMWP_CRON ) ) {
			wp_schedule_event( time(), 'hmwp_every_minute', HMWP_CRON );
		}

	}

	/**
	 * Add a custom schedule interval to the existing schedules.
	 *
	 * @param array $schedules An array of the existing cron schedules.
	 *
	 * @return array The modified array of cron schedules with the new custom interval added.
	 */
	function setInterval( $schedules ) {

		$schedules['hmwp_every_minute'] = array(
			'display'  => 'every 1 minute',
			'interval' => 60
		);

		return $schedules;
	}

	/**
	 * Executes the scheduled cron job to verify and update cache plugins.
	 *
	 * This method checks the cache plugins and updates the paths in the cache files
	 * to ensure compatibility with the current configuration.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function processCron() {
		// Check the cache plugins and change the paths in the cache files.
		HMWP_Classes_ObjController::getClass( 'HMWP_Models_Compatibility' )->checkCacheFiles();
	}

	public function processCronOnce() {
		HMWP_Classes_ObjController::getClass( 'HMWP_Controllers_SecurityCheck' )->doSecurityCheck();
	}

}
