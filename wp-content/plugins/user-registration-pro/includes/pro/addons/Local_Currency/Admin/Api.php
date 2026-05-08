<?php
/**
 * Local Currency Api
 *
 * @class    Api
 * @package  Api
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Local_Currency\Admin;

use GeoIp2\WebService\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Api
 */
class Api {

	/**
	 * Open Exchange Rates API base URL.
	 *
	 * @var string
	 */
	private static $open_exchange_url = 'https://openexchangerates.org/api/';

	/**
	 * Get exchange rate
	 *
	 * @return array|false
	 */
	public static function ur_get_exchange_rate() {
		$option_key    = 'urm_local_currency_rates_by_open_exchange';
		$timestamp_key = 'urm_local_currency_rates_by_open_exchange_timestamp';
		$license_key   = get_option( 'user_registration_open_exchange_key', '' );

		$last_saved = get_option( $timestamp_key, 0 );

		$base_currency = apply_filters( 'ur_local_currency_open_exchange_rate_base_currency', get_option( 'user_registration_payment_currency', 'USD' ) );

		if ( apply_filters( 'ur_local_currency_reset_exchange_rate', time() - $last_saved > 24 * HOUR_IN_SECONDS ) ){
			$url = self::$open_exchange_url . 'latest.json?app_id=' . $license_key . '&base=' . $base_currency;

			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );

				if ( $data ) {
					update_option( $option_key, $data );
					update_option( $timestamp_key, time() );
				}
			}
		}

		return get_option( $option_key, false );
	}

	/**
	 * Get local currency and pricing zone based on user geolocation
	 *
	 * @param bool $enable_geolocation
	 * @return array
	 */
	public static function ur_get_local_currency_by_geolocation( $enable_geolocation = false ) {
		$country_code = 'US';
		$local_currency_by_country = '';
		$pricing_zone_by_country = [];

		if ( $enable_geolocation ) {
			$max_mind_key        = get_option( 'user_registration_max_mind_key', '' );
			$max_mind_account_id = get_option( 'user_registration_max_mind_account_id', '' );

			$test_mode_enable = ur_string_to_bool(
				get_option( 'user_registration_local_currency_by_geolocation_test_mode', 0 )
			);
			$test_country = get_option( 'user_registration_local_currency_test_country', 'US' );

			if ( $test_mode_enable && ! empty( $test_country ) ) {
				$country_code = strtoupper( $test_country );
			} else {
				$user_ip = CoreFunctions::ur_get_user_ip();

				$is_invalid_ip = (
					empty( $user_ip ) ||
					in_array( $user_ip, [ '127.0.0.1', '::1' ], true ) ||
					false === filter_var(
						$user_ip,
						FILTER_VALIDATE_IP,
						FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
					)
				);

				if ( ! $is_invalid_ip && ! empty( $max_mind_key ) && ! empty( $max_mind_account_id ) ) {
					try {
						$client = new Client( (int) $max_mind_account_id, $max_mind_key );
						$record = $client->country( $user_ip );

						if ( ! empty( $record->country->isoCode ) ) {
							$country_code = strtoupper( $record->country->isoCode );
						}
					} catch ( \Throwable $e ) {
						$country_code = 'US';
					}
				}
			}

			$pricing_zone_by_country = CoreFunctions::ur_get_price_zone_by_country( $country_code );
			if ( ! empty( $pricing_zone_by_country ) ) {
				$local_currency_by_country = $pricing_zone_by_country['meta']['ur_local_currency'][0];
			}
		}

		return [
			'country_code' => $country_code,
			'pricing_zone' => $pricing_zone_by_country,
			'local_currency' => $local_currency_by_country,
		];
	}
}
