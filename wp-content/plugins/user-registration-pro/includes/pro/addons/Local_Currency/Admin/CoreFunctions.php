<?php
/**
 * Local Currency
 *
 * Local\ Currency Main Page
 *
 * @class    Local_Currency
 * @package  Local_Currency
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Local_Currency\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local_Currency
 *
 * @since 6.0.0
 */
class CoreFunctions {

	/**
	 * Render local currency table.
	 *
	 * @since 6.0.0
	 */
	public static function render_local_currencies_table(){
		$table = new PricingZoneTable();
		$table->prepare_items();
		?>
			<hr style="margin-top:40px;margin-bottom:40px">
			<div id="user-registration-local-currency-table-page">
			<form id="membership-team-list" method="get" class="user-registration-base-list-table-form">
				<?php $table->display_page(); ?>
			</form>
			</div>
		<?php
	}

	/**
	 * Render local currencies create form.
	 *
	 * @since 6.0.0
	 */
	public static function render_local_currencies_create_form( $post_id = '' ){
		$current_currency = get_option( 'user_registration_payment_currency', 'USD' );
		$currencies       = ur_get_currencies_with_symbols();

		$zone_name        = '';
		$zone_description = '';
		$exchange_rate    = '';
		$countries_value  = array();
		$currency_value   = '';
		$conversion_type  = 'manual';

		if ( ! empty( $post_id ) ) {
			$pricing_zone = self::ur_get_pricing_zone_by_id( $post_id );

		}

		if ( ! empty( $pricing_zone ) && ! empty( $pricing_zone['meta'] ) ) {
			$meta = $pricing_zone['meta'];

			$zone_name        = $meta['ur_local_currencies_zone_name'] ?? '';
			$zone_description = $meta['ur_local_currencies_zone_description'] ?? '';
			$exchange_rate    = $meta['ur_local_currencies_exchange_rate'] ?? '';
			$countries_value  = $meta['ur_local_currencies_countries'] ?? array();
			$currency_value   = $meta['ur_local_currency'][0] ?? '';
			$conversion_type  = $meta['ur_local_currencies_conversion_type'] ?? 'manual';
		}

		ob_start();
		?>
		<div class="ur-local-currencies-form">
			<form id="ur-local-currencies-add-form" >
			<div class="ur-local-currencies-form__row">
				<div class="ur-local-currencies-form__label">
					<label>
						<?php echo __( 'Zone Name', 'user-registration' ); ?> <span class="required">*</span>
					</label>
				</div>

				<div class="ur-local-currencies-form__field">
					<input
						type="text"
						class="ur-local-currencies-form__input"
						placeholder="Enter price zone name"
						name="ur_local_currencies_zone_name"
						value="<?php echo esc_attr( $zone_name ); ?>"
					/>
				</div>
			</div>

			<div class="ur-local-currencies-form__row">
				<div class="ur-local-currencies-form__label">
					<label><?php echo __( 'Zone Description', 'user-registration' ); ?></label>
				</div>

				<div class="ur-local-currencies-form__field">
					<input
						type="text"
						class="ur-local-currencies-form__input"
						placeholder="Zone description"
						name="ur_local_currencies_zone_description"
						value="<?php echo esc_attr( $zone_description ); ?>"
					/>
				</div>
			</div>

			<div class="ur-local-currencies-form__row">
				<div class="ur-local-currencies-form__label">
					<label>
						<?php echo __( 'Conversion Type', 'user-registration' ); ?> <span class="required">*</span>
					</label>
				</div>

				<div class="ur-local-currencies-form__field">
					<div class="ur-local-currencies-form__exchange">
						<div class="">
							<input
								type="radio"
								class="ur-local-currencies-form__input ur-local-currencies-form__input--small"
								name="ur_local_currencies_conversion_type"
								id="ur-local-currencies-manual-conversion-type"
								value="manual"
								<?php checked( $conversion_type, 'manual' ); ?>
							/><label for="ur-local-currencies-manual-conversion-type">Manual</label>
						</div>
						<div class="">
							<input
								type="radio"
								class="ur-local-currencies-form__input ur-local-currencies-form__input--small"
								name="ur_local_currencies_conversion_type"
								value="automatic"
								id="ur-local-currencies-automatic-conversion-type"
								<?php checked( $conversion_type, 'automatic' ); ?>
							/><label for="ur-local-currencies-automatic-conversion-type">Automatic</label>
						</div>
					</div>
				</div>
			</div>

			<div class="ur-local-currencies-form__row ur-local-currency-exchange-rate" <?php echo ( 'automatic' == $conversion_type ? 'style="display:none;"' : '' ) ?> >
				<div class="ur-local-currencies-form__label">
					<label>
						<?php echo __( 'Exchange Rate', 'user-registration' ); ?> <span class="required">*</span>
					</label>
				</div>

				<div class="ur-local-currencies-form__field">
					<div class="ur-local-currencies-form__exchange">
						<span class="ur-local-currencies-form__exchange-prefix">
							1 <?php echo esc_html( $current_currency ); ?> =
						</span>

						<input
							type="number"
							step="1"
							class="ur-local-currencies-form__input ur-local-currencies-form__input--small"
							min="1"
							name="ur_local_currencies_exchange_rate"
							value="<?php echo esc_attr( $exchange_rate ); ?>"
						/>
					</div>
				</div>
			</div>

			<div class="ur-local-currencies-form__row">
				<div class="ur-local-currencies-form__label">
					<label>
						<?php echo __( 'Countries', 'user-registration' ) ; ?> <span class="required">*</span>
					</label>
				</div>

				<?php
					$countries = ur_get_country_lists();
				?>
				<div class="ur-local-currencies-form__field">
					<select
						class="ur-local-currencies-form__select ur-local-currencies-countries"
						name="ur_local_currencies_countries[]"
						multiple="multiple"
					>
						<?php foreach ( $countries as $key => $name ) : ?>
							<option
								value="<?php echo esc_attr( $key ); ?>"
								<?php selected( in_array( $key, $countries_value, true ), true ); ?>
							>
								<?php echo esc_html( ucfirst( $name ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="ur-local-currencies-form__row">
				<div class="ur-local-currencies-form__label">
					<label>
						Currency <span class="required">*</span>
					</label>
				</div>

				<div class="ur-local-currencies-form__field">
					<select class="ur-local-currencies-form__select" name="ur_local_currency">
						<?php foreach ( $currencies as $key => $value ) : ?>
							<option
								value="<?php echo esc_attr( $key ); ?>"
								<?php selected( $currency_value, $key ); ?>
							>
								<?php echo esc_html( $value ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</form>
	</div>
	<?php
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Render local currency settings on Membership.
	 *
	 * @since 6.0.0
	 */
	public static function ur_render_local_currency_settings( $membership_details ){
		?>
			<div class="ur-membership-local-currency-outer-wrapper">
				<div class="ur-membership-selection-container ur-d-flex ur-mt-2 ur-align-items-center"
					style="gap:20px;">
					<div class="ur-label" style="width: 30%">
						<label class="ur-membership-local-currency-action"
								for="ur-membership-local-currency-action"><?php esc_html_e( 'Enable Local Currency :', 'user-registration' ); ?>
						</label>
					</div>
					<div class="ur-toggle-section m1-auto" style="width: 100%">
						<span class="user-registration-toggle-form">

						<?php
							$local_currency_details   = isset( $membership_details[ 'local_currency'] ) ? $membership_details[ 'local_currency'] : array();
							$is_local_currency_enable = ur_string_to_bool( isset( $local_currency_details[ 'is_enable' ] ) ? $local_currency_details[ 'is_enable' ] : '0' );
						?>
							<input
								data-key-name="Enable Local Currency"
								id="ur-membership-local-currency-action" type="checkbox"
								class="user-registration-switch__control hide-show-check enabled"

								name="ur_membership_local_currency"
								style="width: 100%; text-align: left"
								<?php echo $is_local_currency_enable ? esc_attr( 'checked' ) : ''; ?>
								>
							<span class="slider round"></span>
						</span>
					</div>
				</div>
			</div>

			<?php
				$args = array(
					'post_type'      => 'urm_price_zone',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				);

				$local_currency_details = get_posts( $args );

				foreach ( $local_currency_details as $detail ):
					$zone_id     = $detail->ID;
					$saved_zones = isset( $membership_details['local_currency']['zones'] )
						? $membership_details['local_currency']['zones']
						: array();

					$zone_data = isset( $saved_zones[ $zone_id ] )
						? $saved_zones[ $zone_id ]
						: array();

					$is_zone_enabled  = ur_string_to_bool( ! empty( $zone_data['enable' ] ) ? $zone_data['enable'] : '0' );
					$pricing_method   = ! empty( $zone_data['pricing_method'] ) ? $zone_data['pricing_method'] : 'exchange';
					$manual_price     = ! empty( $zone_data['manual_price'] ) ? $zone_data['manual_price'] : '';

				?>
			<div class="ur-local-currency-card <?php echo $is_zone_enabled ? '' : 'collapsed'; ?>"
				data-zone-id="<?php echo esc_attr( $zone_id ); ?>"
				style="<?php echo esc_attr( $is_zone_enabled ? '' : 'display: none' ); ?>"
				>
				<div class="ur-local-currency-header">
					<div class="ur-local-currency-info">
					<span class="ur-local-currency-label"><?php echo esc_html( $detail->post_title ); ?></span>
					<button class="ur-local-currency-collapse-btn">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
						<circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
						<path d="M8 7V11M8 5V5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
						</svg>
					</button>
					</div>
					<div class="ur-local-currency-controls">
					<div class="ur-toggle-section">
						<span class="user-registration-toggle-form">
						<input
							class="ur-local-currency-toggle-input"
							type="checkbox"
							name="ur_local_currency_enable"
							<?php checked( $is_zone_enabled ); ?>
							data-zone-id="<?php echo esc_attr( $zone_id ); ?>"
							>
						<span class="slider round"></span>
						</span>
					</div>
					<button class="ur-local-currency-collapse-btn">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
						<path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
					</div>
				</div>

				<div class="ur-local-currency-content hidden">
					<div class="ur-local-currency-pricing-method">
					<div class="ur-local-currency-method-header">
						<span class="ur-local-currency-method-label"><?php echo esc_html( 'Pricing Method', 'user-registration' ); ?></span>
						<button class="ur-local-currency-info-icon">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
							<circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
							<path d="M8 7V11M8 5V5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
						</svg>
						</button>
					</div>

					<div class="ur-local-currency-radio-group">
						<label class="ur-local-currency-radio-option">
						<input
							type="radio"
							name="pricing-method-<?php echo esc_attr( $zone_id ); ?>"
							value="exchange"
							<?php checked( $pricing_method, 'exchange' ); ?>
						>
						<span class="ur-local-currency-radio-custom"></span>
						<span class="ur-local-currency-radio-label"><?php echo esc_html( 'Calculate prices by the exchange rate.', 'user-registration' ); ?></span>
						</label>

						<label class="ur-local-currency-radio-option">
						<input
							type="radio"
							name="pricing-method-<?php echo esc_attr( $zone_id ); ?>"
							value="manual"
							<?php checked( $pricing_method, 'manual' ); ?>
						>

						<span class="ur-local-currency-radio-custom"></span>
						<span class="ur-local-currency-radio-label"><?php echo esc_html( 'Set prices manually.', 'user-registration' ); ?></span>
						</label>
						<input
							type="number"
							min="1"
							step="1"
							class="local-currency-manual-local-price"
							name="local_currency_manual_local_price"
							value="<?php echo esc_attr( $manual_price ); ?>"
							<?php echo ( 'manual' !== $pricing_method ) ? 'style="display:none"' : ''; ?>
						>

					</div>
					<div class="ur-local-currency-message hidden ur-local-currency-<?php echo esc_attr( $zone_id ); ?>-message"></div>
					</div>
				</div>
			</div>
		<?php
			endforeach;
	}

	/**
	 * Get local currency details for membership.
	 */
	public static function ur_get_local_currency_details_for_membership( $id ){
		$membership_details = json_decode( get_post_meta( $id, 'ur_membership', true ) );
		$local_currency_details = ! empty( $membership_details->local_currency ) ? $membership_details->local_currency : array();

		if ( empty( $local_currency_details->is_enable ) ) {
			return array();
		}

		return ( array )$local_currency_details;
	}

	/**
	 * Get all pricing zone data.
	 *
	 * @since 6.0.0
	 */
	public static function ur_get_all_pricing_zone_data(){
		$args = array(
			'post_type'      => 'urm_price_zone',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);

		$zones = get_posts( $args );

		$pricing_zones = array();

		if ( ! empty( $zones ) ) {
			foreach ( $zones as $zone ) {

				$post_id = $zone->ID;

				$all_meta = get_post_meta( $post_id );

				$meta = array();
				foreach ( $all_meta as $key => $value ) {
					$meta[ $key ] = maybe_unserialize( $value[0] );
				}

				$pricing_zones[ $post_id ] = array(
					'ID'    => $post_id,
					'title' => $zone->post_title,
					'slug'  => $zone->post_name,
					'meta'  => $meta,
				);
			}
		}

		return $pricing_zones;
	}

	/**
	 * Get pricing zone data by ID.
	 *
	 * @since 6.0.0
	 *
	 * @param int $id
	 * @return array|null
	 */
	public static function ur_get_pricing_zone_by_id( $id ) {

		$zone = get_post( $id );

		if ( ! $zone || 'urm_price_zone' !== $zone->post_type ) {
			return null;
		}

		$data = get_post_meta( $id );

		$meta = array();
		foreach ( $data as $key => $value ) {
			$meta[ $key ] = maybe_unserialize( $value[0] );
		}

		$pricing_zone = array(
			'ID'    => $id,
			'title' => $zone->post_title,
			'slug'  => $zone->post_name,
			'meta'  => $meta,
		);

		return $pricing_zone;
	}

	/**
	 * Get local amount after conversion.
	 *
	 * @since 6.0.0
	 */
	public static function ur_get_amount_after_conversion( $amount, $currency ,$pricing_data, $local_currency_data, $ur_zone_id ){
		$exchange_rate = ! empty( $pricing_data[ 'meta' ][ 'ur_local_currencies_exchange_rate' ] ) ? $pricing_data[ 'meta' ][ 'ur_local_currencies_exchange_rate' ] : '1';
		$ur_local_currency = ! empty( $pricing_data[ 'meta' ][ 'ur_local_currency' ][0] ) ? $pricing_data[ 'meta' ][ 'ur_local_currency' ][0] : 'USD';
		$pricing_method = ! empty( $local_currency_data['zones'][ $ur_zone_id ] ) ? $local_currency_data['zones'][ $ur_zone_id ][ 'pricing_method' ] : 'exchange';
		$ur_local_currencies_conversion_type = ! empty( $pricing_data[ 'meta' ][ 'ur_local_currencies_conversion_type' ] ) ? $pricing_data[ 'meta' ][ 'ur_local_currencies_conversion_type' ] : 'manual';

			if ( 'automatic' == $ur_local_currencies_conversion_type ) {
				$all_exchange_rates = Api::ur_get_exchange_rate();
				if ( get_option( 'user_registration_payment_currency', 'USD' ) == $all_exchange_rates[ 'base'] ) {
					$exchange_rate = ! empty( $all_exchange_rates[ 'rates' ][ $ur_local_currency ] ) ? $all_exchange_rates[ 'rates' ][ $ur_local_currency ] : '1';
				}
			}
		if ( 'exchange' == $pricing_method ) {
			$amount = $amount * $exchange_rate;
		}elseif ( 'manual' == $pricing_method ) {
			$amount = ! empty( $local_currency_data['zones'][ $ur_zone_id ] ) ? $local_currency_data['zones'][ $ur_zone_id ][ 'manual_price' ] : '1';
		}

		return $amount;
	}

	/**
	 * Get user ip.
	 *
	 * @since 6.0.0
	 */
	public static function ur_get_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Get price zone by country.
	 *
	 * @since 6.0.0
	 */
	public static function ur_get_price_zone_by_country( $country ) {
		if ( ! $country ) {
			return null;
		}

		$query = new \WP_Query(
			array(
				'post_type'      => 'urm_price_zone',
				'post_status'    => 'active',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => 'ur_local_currencies_countries',
						'value'   => maybe_serialize( strtoupper( $country ) ),
						'compare' => 'LIKE',
					),
				),
			)
		);

		$pricing_zone_id = 0;

		if ( ! empty( $query->posts ) ) {
			foreach ( $query->posts as $post_id ) {
				$pricing_zone_id = $post_id;
				break;
			}
		}

		return self::ur_get_pricing_zone_by_id( $pricing_zone_id );
	}

}
