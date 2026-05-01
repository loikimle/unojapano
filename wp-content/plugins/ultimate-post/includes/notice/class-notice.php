<?php //phpcs:ignore
namespace ULTP\Includes\Notice;

defined( 'ABSPATH' ) || exit;

use ULTP\Includes\Durbin\Xpo;
use ULTP\Includes\Durbin\DurbinClient;

/**
 * Plugin Notice
 */
class Notice {


	/**
	 * Notice version
	 *
	 * @var string
	 */
	private $notice_version = 'v5132';

	/**
	 * Notice JS/CSS applied
	 *
	 * @var boolean
	 */
	private $notice_js_css_applied = false;


	/**
	 * Notice Constructor
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices_callback' ) );
		add_action( 'admin_init', array( $this, 'set_dismiss_notice_callback' ) );

		// REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );

		// Woocommerce Install Action
		// add_action( 'wp_ajax_ultp_install', array( $this, 'install_activate_plugin' ) ); // this ajax not called anywhere in future need to removed, that is arise patchstack security issue
	}

	/**
	 * Registers REST API endpoints.
	 *
	 * @return void
	 */
	public function register_rest_route() {
		$routes = array(
			// Hello Bar.
			array(
				'endpoint'            => 'hello_bar',
				'methods'             => 'POST',
				'callback'            => array( $this, 'hello_bar_callback' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			),
		);

		foreach ( $routes as $route ) {
			register_rest_route(
				'ultp',
				$route['endpoint'],
				array(
					array(
						'methods'             => $route['methods'],
						'callback'            => $route['callback'],
						'permission_callback' => $route['permission_callback'],
					),
				)
			);
		}
	}

	/**
	 * Hellobar config
	 *
	 * @return array
	 */
	public static function get_hellobar_config() {
		return array(
			'ultp_helloBar_spring_sale_2026_1' => Xpo::get_transient_without_cache( 'ultp_helloBar_spring_sale_2026_1' ),
			'ultp_helloBar_spring_sale_2026_2' => Xpo::get_transient_without_cache( 'ultp_helloBar_spring_sale_2026_2' ),
			'ultp_helloBar_spring_sale_2026_3' => Xpo::get_transient_without_cache( 'ultp_helloBar_spring_sale_2026_3' ),
		);
	}

	/**
	 * Handles Hello Bar dismissal action via REST API .
	 *
	 * @param \WP_REST_Request $request REST request object .
	 * @return \WP_REST_Response
	 */
	public function hello_bar_callback( \WP_REST_Request $request ) {
		$request_params = $request->get_params();
		$type           = isset( $request_params['type'] ) ? $request_params['type'] : '';
		$id             = isset( $request_params['id'] ) ? $request_params['id'] : '';

		if ( 'hello_bar' === $type && ! empty( $id ) ) {
			Xpo::set_transient_without_cache( $id, 'hide', 1296000 );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Hello Bar Action performed', 'ultimate-post' ),
			),
			200
		);
	}

	/**
	 * Set Notice Dismiss Callback
	 *
	 * @return void
	 */
	public function set_dismiss_notice_callback() {

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wpnonce'] ?? '' ) ), 'ultp-nonce' ) ) {
			return;
		}

		$durbin_key = sanitize_text_field( wp_unslash( $_GET['ultp_durbin_key'] ?? '' ) );

		// Durbin notice dismiss.
		if ( ! empty( $durbin_key ) ) {
			Xpo::set_transient_without_cache( 'ultp_durbin_notice_' . $durbin_key, 'off' );

			if ( 'get' === sanitize_text_field( wp_unslash( $_GET['ultp_get_durbin'] ?? '' ) ) ) {
				DurbinClient::send( DurbinClient::ACTIVATE_ACTION );
			}
		}

		// Install notice dismiss.
		$install_key = sanitize_text_field( wp_unslash( $_GET['ultp_install_key'] ?? '' ) );
		if ( ! empty( $install_key ) ) {
			Xpo::set_transient_without_cache( 'ultp_install_notice_' . $install_key, 'off' );
		}
		$notice_key = sanitize_text_field( wp_unslash( $_GET['ultp_notice'] ?? '' ) );
		if ( ! empty( $notice_key ) ) {
			$interval = (int) sanitize_text_field( wp_unslash( $_GET['ultp_interval'] ?? '' ) );
			if ( ! empty( $interval ) ) {
				Xpo::set_transient_without_cache( 'ultp_get_pro_notice_' . $notice_key, 'off', $interval );
			} else {
				Xpo::set_transient_without_cache( 'ultp_get_pro_notice_' . $notice_key, 'off' );
			}
		}
	}

	/**
	 * Admin Notices Callback
	 *
	 * @return void
	 */
	public function admin_notices_callback() {
		$this->ultp_dashboard_notice_callback();
		$this->ultp_dashboard_durbin_notice_callback();
	}

	/**
	 * Admin Dashboard Notice Callback
	 *
	 * @return void
	 */
	public function ultp_dashboard_notice_callback() {
		$this->ultp_dashboard_banner_notice();
		$this->ultp_dashboard_content_notice();
	}

	/**
	 * Dashboard Banner Notice
	 *
	 * @return void
	 */
	public function ultp_dashboard_banner_notice() {
		$ultp_db_nonce  = wp_create_nonce( 'ultp-nonce' );
		$banner_notices = array(
			array(
				'key'                => 'ultp_flash_sale_2026_1',
				'start'              => '2026-02-19 00:00 Asia/Dhaka',
				'end'                => '2026-02-23 23:59 Asia/Dhaka', // format YY-MM-DD always set time 23:59 and zone Asia/Dhaka.
				'countdown_duration' => 259200, // Duration in seconds.
				'brand_color'        => '#0322ff',

				'left_image'         => ULTP_URL . 'assets/img/dashboard_banner/2026_flash_sale_logo.png',
				'right_image'        => ULTP_URL . 'assets/img/dashboard_banner/2026_final_hours_and_flash_sale_right_btn.png',
				'bg_image'           => ULTP_URL . 'assets/img/dashboard_banner/2026_spring_sale_bg.png',
				'text'               => 'Hurry Before It Ends!',
				'countdown_color'    => '#3CF357',
				'url'                => Xpo::generate_utm_link(
					array(
						'utmKey' => 'flash_sale',
					)
				),

				'visibility'         => ! Xpo::is_lc_active(),
			),
			array(
				'key'                => 'ultp_final_hour_sale_2026_1',
				'start'              => '2026-02-25 00:00 Asia/Dhaka',
				'end'                => '2026-03-01 23:59 Asia/Dhaka', // format YY-MM-DD always set time 23:59 and zone Asia/Dhaka.
				'brand_color'        => '#0322ff',
				'left_image'         => ULTP_URL . 'assets/img/dashboard_banner/2026_final_hours_sale_logo.png',
				'right_image'        => ULTP_URL . 'assets/img/dashboard_banner/2026_final_hours_and_flash_sale_right_btn.png',
				'bg_image'           => ULTP_URL . 'assets/img/dashboard_banner/2026_final_hours_and_flash_sale_bg.png',
				'text'               => 'Hurry Before It Ends!',
				'countdown_duration' => 172800, // Duration in seconds.
				'countdown_color'    => '#0322ff',
				'url'                => Xpo::generate_utm_link(
					array(
						'utmKey' => 'final_hour',
					)
				),

				'visibility'         => ! Xpo::is_lc_active(),
			),
			array(
				'key'                => 'ultp_banner_spring_sale_2026_1',
				'start'              => '2026-04-05 00:00 Asia/Dhaka',
				'end'                => '2026-04-14 23:59 Asia/Dhaka', // format YY-MM-DD always set time 23:59 and zone Asia/Dhaka.

				'brand_color'        => '#0322ff',

				'left_image'         => ULTP_URL . 'assets/img/dashboard_banner/spring_sale/2026_spring_sale_offer.png',
				'right_image'        => ULTP_URL . 'assets/img/dashboard_banner/spring_sale/2026_spring_sale_button.png',
				'bg_image'           => ULTP_URL . 'assets/img/dashboard_banner/spring_sale/2026_spring_sale_bg.png',
				'text'               => 'Hurry Before It Ends!',
				'countdown_duration' => 259200, // Duration in seconds.
				// 'countdown_color'    => '#0322ff',
				'countdown_color'    => '#000',
				'url'                => Xpo::generate_utm_link(
					array(
						'utmKey' => 'spring_sale',
					)
				),

				'visibility'         => ! Xpo::is_lc_active(),
			),
		);
		foreach ( $banner_notices as $notice ) {
			$notice_key = isset( $notice['key'] ) ? $notice['key'] : $this->notice_version;

			if ( isset( $_GET['ultp_notice'] ) && $notice_key === sanitize_text_field(wp_unslash($_GET['ultp_notice'])) ) { // phpcs:ignore
				continue;
			}

			$current_time = gmdate( 'U' );
			$notice_start = gmdate( 'U', strtotime( $notice['start'] ) );
			$notice_end   = gmdate( 'U', strtotime( $notice['end'] ) );
			if ( $current_time >= $notice_start && $current_time <= $notice_end && $notice['visibility'] ) {

				$notice_transient = Xpo::get_transient_without_cache( 'ultp_get_pro_notice_' . $notice_key );

				if ( 'off' === $notice_transient ) {
					continue;
				}

				if ( ! $this->notice_js_css_applied ) {
					$this->ultp_banner_notice_js();
					$this->notice_js_css_applied = true;
				}
				$query_args = array(
					'ultp_notice' => $notice_key,
					'wpnonce'     => $ultp_db_nonce,
				);
				if ( isset( $notice['repeat_interval'] ) && $notice['repeat_interval'] ) {
					$query_args['ultp_interval'] = $notice['repeat_interval'];
				}
				?>
				<style>
					@media only screen and (max-width: 1200px) {
						.ultp-notice-wrapper.notice .ultp-banner-logo-img {
							max-width: 230px;
						}
						.ultp-banner-grab-btn-img {
							max-width: 130px;
						}
						.ultp-notice-countdown {
							font-size: 18px;
						}
						.ultp-notice-banner-title {
							font-size: 20px;
						}
					}
				</style>
				<div 
					class="ultp-notice-wrapper notice" 
					style="
						height:90px;
						border-radius: 7px 0px 0px 7px;
						padding:0;
						position:relative;
						box-sizing: border-box;
						border-left: 3px solid <?php echo esc_attr( $notice['brand_color'] ); ?>;
						background-image: url('<?php echo esc_attr( $notice['bg_image'] ); ?>');
				">
					<a 
						class="wc-dismiss-notice dashicons dashicons-no-alt" 
						style="
							position: absolute;
							top: 1px;
							right: 1px;
							border-radius: 50%;
							background-color: black;
							color: white;
							font-size: 14px;
							display: flex;
							align-items: center;
							justify-content: center;
						"
						aria-label="<?php esc_html_e( 'Close Banner', 'ultimate-post' ); ?>"
						href="<?php echo esc_url( add_query_arg( $query_args ) ); ?>">
					</a>

					<a style="width:100%;text-decoration: none;" target="_blank" href="<?php echo esc_url( $notice['url'] ); ?>">
						<div style="
							display: flex;
							justify-content: space-between;
							align-items: center;
							max-width: 1358px;
							margin: 0px auto;
							padding: 10px 15px;
							height: 90px;
							box-sizing: border-box;

						">
							<img class="ultp-banner-logo-img" style="" loading="lazy" src="<?php echo esc_url( $notice['left_image'] ); ?>" />
							<div style="
								display: flex;
								flex-direction: column;
								gap: 4px;
								align-items: center;
								justify-content: center;
								font-weight: 700;
								font-size: 28px;
								color: #000;
								line-height: 32px;
							">
								<span class="ultp-notice-banner-title">
									<?php echo esc_html( $notice['text'] ); ?>
								</span>	
								<div 
									class="ultp-notice-countdown" 
									style="
										color: <?php echo esc_attr( $notice['countdown_color'] ); ?>;
									"
									data-notice-key="<?php echo esc_attr( $notice_key . '-countdown' ); ?>" 
									data-duration="<?php echo esc_attr( $notice['countdown_duration'] ); ?>">
									00:00:00:00
								</div>
							</div>
							<img class="ultp-banner-grab-btn-img" style="" loading="lazy" src="<?php echo esc_url( $notice['right_image'] ); ?>" />
						</div>
					</a>
				</div>
				<?php
			}
		}
	}

	/**
	 * Banner JS
	 *
	 * @return void
	 */
	public function ultp_banner_notice_js() {
		?>
		<script type="text/javascript">
			jQuery(function($) {
				'use strict';

				const storagePrefix = 'ultp_notice_countdown_';

				const formatCountdown = function(seconds) {
					const days = Math.floor(seconds / 86400);
					const hours = Math.floor((seconds % 86400) / 3600);
					const minutes = Math.floor((seconds % 3600) / 60);
					const secs = seconds % 60;

					return String(days).padStart(2, '0') + ':' + String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
				};

				const parseDurationToSeconds = function(duration) {
					if (typeof duration === 'number' && Number.isFinite(duration) && duration > 0) {
						return Math.floor(duration);
					}

					const durationString = String(duration || '').trim();
					if (/^\d+$/.test(durationString)) {
						return parseInt(durationString, 10);
					}

					return 0;
				};

				const nowInSeconds = function() {
					return Math.floor(Date.now() / 1000);
				};

				$('.ultp-notice-countdown').each(function() {
					const countdownElement = $(this);
					const noticeKey = String(countdownElement.data('noticeKey') || '');
					const duration = parseDurationToSeconds(countdownElement.data('duration'));

					if (!noticeKey || duration <= 0) {
						return;
					}

					const storageKey = storagePrefix + noticeKey;
					let endAt = 0;

					try {
						const storedDataRaw = window.localStorage.getItem(storageKey);
						if (storedDataRaw) {
							const storedData = JSON.parse(storedDataRaw);
							if (storedData && parseInt(storedData.duration, 10) === duration) {
								endAt = parseInt(storedData.endAt, 10) || 0;
							}
						}
					} catch (error) {
						endAt = 0;
					}

					const saveTimerState = function(nextEndAt) {
						try {
							window.localStorage.setItem(
								storageKey,
								JSON.stringify({
									endAt: nextEndAt,
									duration: duration,
								})
							);
						} catch (error) {
							// No-op.
						}
					};

					const resetTimer = function(currentTime) {
						endAt = currentTime + duration;
						saveTimerState(endAt);
					};

					const tick = function() {
						const currentTime = nowInSeconds();

						if (endAt <= currentTime) {
							resetTimer(currentTime);
						}

						const remaining = Math.max(endAt - currentTime, 0);
						countdownElement.text(formatCountdown(remaining));
					};

					if (endAt <= nowInSeconds()) {
						resetTimer(nowInSeconds());
					}

					tick();
					window.setInterval(tick, 1000);
				});
			});
		</script>
		<?php
	}


	/**
	 * The Durbin Html
	 *
	 * @return void
	 */
	public function ultp_dashboard_durbin_notice_callback() {
		$durbin_key = 'ultp_durbin_dc1';

		if (
			isset( $_GET['ultp_durbin_key'] ) || // phpcs:ignore
			'off' === Xpo::get_transient_without_cache( 'ultp_durbin_notice_' . $durbin_key )
		) {
			return;
		}

		if ( ! $this->notice_js_css_applied ) {
			$this->notice_js_css_applied = true;
		}

		$ultp_db_nonce = wp_create_nonce( 'ultp-nonce' );

		?>
		<style>
				.ultp-consent-box {
					width: 656px;
					padding: 16px;
					border: 1px solid #070707;
					border-left-width: 4px;
					border-radius: 4px;
					background-color: #fff;
					position: relative;
					width: 100%;
					box-sizing: border-box;
				}
				.ultp-consent-content {
					display: flex;
					justify-content: flex-start;
					align-items: flex-end;
					gap: 26px;
				}

				.ultp-consent-text-first {
					font-size: 14px;
					font-weight: 600;
					color: #070707;
				}
				.ultp-consent-text-last {
					margin: 4px 0 0;
					font-size: 14px;
					color: #070707;
				}

				.ultp-consent-accept {
					background-color: #070707;
					color: #fff;
					border: none;
					padding: 6px 10px;
					border-radius: 4px;
					cursor: pointer;
					font-size: 12px;
					font-weight: 600;
					text-decoration: none;
				}
				.ultp-consent-accept:hover {
					background-color:rgb(38, 38, 38);
					color: #fff;
				}
			</style>
			<div class="ultp-consent-box ultp-notice-wrapper notice data_collection_notice">
			<div class="ultp-consent-content">
			<div class="ultp-consent-text">
			<div class="ultp-consent-text-first"><?php esc_html_e( 'Want to help make PostX even more awesome?', 'ultimate-post' ); ?></div>
			<div class="ultp-consent-text-last">
					<?php esc_html_e( 'Allow us to collect diagnostic data and usage information. see ', 'ultimate-post' ); ?>
			<a href="https://www.wpxpo.com/data-collection-policy/" target="_blank" ><?php esc_html_e( 'what we collect.', 'ultimate-post' ); ?></a>
			</div>
			</div>
			<a
					class="ultp-consent-accept"
					href=
					<?php
									echo esc_url(
										add_query_arg(
											array(
												'ultp_durbin_key' => $durbin_key,
												'ultp_get_durbin' => 'get',
												'wpnonce' => $ultp_db_nonce,
											)
										)
									);
					?>
									class="ultp-notice-close"
			><?php esc_html_e( 'Accept & Close', 'ultimate-post' ); ?></a>
			</div>
			<a href=
				<?php
							echo esc_url(
								add_query_arg(
									array(
										'ultp_durbin_key' => $durbin_key,
										'wpnonce'         => $ultp_db_nonce,
									)
								)
							);
				?>
				class="ultp-notice-close"
				style="
					position: absolute;
					right: 2px;
					top: 5px;
					text-decoration: unset;
					color: #b6b6b6;
					font-family: dashicons;
					font-size: 16px;
					font-style: normal;
					font-weight: 400;
					line-height: 20px;
				"
			>
				<span 
				style="font-size: 14px;"
				class="ultp-notice-close-icon dashicons dashicons-dismiss"> </span></a>
			</div>
		<?php
	}
	/**
	 * Dashboard Content Notice
	 *
	 * @return void
	 */
	public function ultp_dashboard_content_notice() {
		$content_notices = array(
			array(
				'key'                => 'ultp_dashboard_content_notice_spring_sale_v1',
				'start'              => '2026-03-16 00:00 Asia/Dhaka',
				'end'                => '2026-03-25 23:59 Asia/Dhaka',
				'url'                => Xpo::generate_utm_link(
					array(
						'utmKey' => 'content_notice',
					)
				),
				'visibility'         => ! Xpo::is_lc_active(),
				'content_heading'    => __( 'Spring Sale:', 'ultimate-post' ),
				'content_subheading' => __( 'PostX offers are live - Enjoy %s off on PostX Pro.', 'ultimate-post' ),
				'discount_content'   => ' up to 50% OFF',
				'brand_color'        => '#0322ff',
				'icon'               => ULTP_URL . 'assets/img/dashboard_banner/spring_sale/2026_spring_sale_brand_logo.png',
				'button_text'        => __( 'Claim Your Discount!', 'ultimate-post' ),
				'is_discount_logo'   => true,
				'border_color'       => '#0322ff',
			),
			array(
				'key'                => 'ultp_dashboard_content_notice_spring_sale_v2',
				'start'              => '2026-03-26 00:00 Asia/Dhaka',
				'end'                => '2026-04-04 23:59 Asia/Dhaka',
				'url'                => Xpo::generate_utm_link(
					array(
						'utmKey' => 'content_notice',
					)
				),
				'visibility'         => ! Xpo::is_lc_active(),
				'content_heading'    => __( 'Spring Sale:', 'ultimate-post' ),
				'content_subheading' => __( 'PostX offers are live - Enjoy %s off on PostX Pro.', 'ultimate-post' ),
				'discount_content'   => ' up to 50% OFF',
				'brand_color'        => '#0322ff',
				'icon'               => ULTP_URL . 'assets/img/dashboard_banner/spring_sale/2026_spring_discount_logo.png',
				'button_text'        => __( 'Claim Your Discount!', 'ultimate-post' ),
				'is_discount_logo'   => true,
				'border_color'       => '#0322ff',
			),

		);

		$ultp_db_nonce = wp_create_nonce( 'ultp-nonce' );

		foreach ( $content_notices as $key => $notice ) {
			$notice_key = isset( $notice['key'] ) ? $notice['key'] : $this->notice_version;
			if ( isset( $_GET['ultp_notice'] ) && $notice_key === $_GET['ultp_notice'] ) {
				continue;
			} else {
				$border_color = $notice['border_color'];

				$current_time = gmdate( 'U' );
				$notice_start = gmdate( 'U', strtotime( $notice['start'] ) );
				$notice_end   = gmdate( 'U', strtotime( $notice['end'] ) );
				if ( $current_time >= $notice_start && $current_time <= $notice_end && $notice['visibility'] ) {
					$notice_transient = Xpo::get_transient_without_cache( 'ultp_get_pro_notice_' . $notice_key );

					if ( 'off' !== $notice_transient ) {

						$query_args = array(
							'ultp_notice' => $notice_key,
							'wpnonce'     => $ultp_db_nonce,
						);
						if ( isset( $notice['repeat_interval'] ) && $notice['repeat_interval'] ) {
							$query_args['ultp_interval'] = $notice['repeat_interval'];
						}

						$url = isset( $notice['url'] ) ? $notice['url'] : Xpo::generate_utm_link(
							array(
								'utmKey' => 'content_notice',
							)
						);

						?>

						<style id="ultp-notice-css" type="text/css">
							.ultp-content-notice-wrapper {
								border: 1px solid #c3c4c7;
								border-left: 3px solid #037fff;
								margin: 15px 0 !important;
								display: flex;
								align-items: center;
								background-color: #eef0f4;
								width: 100%;
								padding: 10px 0;
								position: relative;
								box-sizing: border-box;
							}

							.ultp-content-notice-wrapper.notice {
								margin: 10px 0;
								width: calc(100% - 20px);
							}

							.wrap .ultp-content-notice-wrapper.notice {
								width: 100%;
							}

							.ultp-content-notice-icon {
								margin-left: 15px;
							}

							.ultp-content-notice-discout-icon {
								margin-left: 10px;
							}

							.ultp-content-notice-icon img {
								max-width: 42px;
								height: 70px;
							}

							.ultp-content-notice-discout-icon img {
								height: 70px;
								width: 70px;
								object-fit: contain;
							}

							.ultp-notice-content-wrapper {
								display: flex;
								flex-direction: column;
								gap: 8px;
								font-size: 14px;
								line-height: 20px;
								margin-left: 15px;
							}

							.ultp-content-notice-buttons {
								display: flex;
								align-items: center;
								gap: 15px;
							}

							.ultp-content-notice-btn {
								font-weight: 600;
								text-transform: uppercase !important;
								padding: 2px 10px !important;
								background-color: #0322ff;
								border: none !important;
							}

							.ultp-content-discount_btn {
								background-color: #ffffff;
								text-decoration: none;
								border: 1px solid #0322ff;
								padding: 5px 10px;
								border-radius: 5px;
								font-weight: 500;
								text-transform: uppercase;
								color: #0322ff !important;
							}

							.ultp-content-notice-close {
								position: absolute;
								right: 2px;
								top: 5px;
								text-decoration: none;
								color: #b6b6b6;
								font-family: dashicons;
								font-size: 16px;
								line-height: 20px;
							}

							.ultp-content-notice-close-icon {
								font-size: 14px;
							}
						</style>
					<div class="ultp-content-notice-wrapper notice data_collection_notice" 
					style="border-left: 3px solid <?php echo esc_attr( $border_color ); ?>;"
					> 
						<?php
						if ( $notice['is_discount_logo'] ) {
							?>
								<div class="ultp-content-notice-discout-icon"> <img src="<?php echo esc_url( $notice['icon'] ); ?>"/>  </div>
							<?php
						} else {
							?>
								<div class="ultp-content-notice-icon"> <img src="<?php echo esc_url( $notice['icon'] ); ?>"/>  </div>
							<?php
						}
						?>
						
						<div class="ultp-notice-content-wrapper">
							<div class="">
								<strong><?php printf( esc_html( $notice['content_heading'] ) ); ?> </strong>
						<?php
						printf(
							wp_kses_post( $notice['content_subheading'] ),
							'<strong>' . esc_html( $notice['discount_content'] ) . '</strong>'
						);
						?>
							</div>
							<div class="ultp-content-notice-buttons">
							<?php if ( isset( $notice['is_discount_logo'] ) && $notice['is_discount_logo'] ) : ?>
									<a class="ultp-content-discount_btn" href="<?php echo esc_url( $url ); ?>" target="_blank">
										<?php echo esc_html( $notice['button_text'] ); ?>
									</a>
								<?php else : ?>
									<a class="ultp-content-notice-btn button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank" style="background-color: <?php echo ! empty( $notice['background_color'] ) ? esc_attr( $notice['background_color'] ) : '#86a62c'; ?>;">
									<?php echo esc_html( $notice['button_text'] ); ?>
										
									</a>
								<?php endif; ?>
							</div>
						</div>
						<a href=
							<?php
							echo esc_url(
								add_query_arg(
									$query_args
								)
							);
							?>
						class="ultp-content-notice-close"><span class="ultp-content-notice-close-icon dashicons dashicons-dismiss"> </span></a>
					</div>
								<?php
					}
				}
			}
		}
	}
}
