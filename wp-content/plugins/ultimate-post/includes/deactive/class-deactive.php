<?php //phpcs:ignore
/**
 * Plugin Deactivation Handler.
 *
 * @since
 */

namespace ULTP\Includes\Deactive;

use ULTP\Includes\Durbin\DurbinClient;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin deactivation feedback and reporting.
 */
class Deactive {

	private $plugin_slug = 'ultimate-post';

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow ) {
			add_action( 'admin_footer', array( $this, 'get_source_data_callback' ) );
		}
		add_action( 'wp_ajax_ultp_deactive_plugin', array( $this, 'send_plugin_data' ) );
	}

	/**
	 * Send plugin deactivation data to remote server.
	 *
	 * @param string|null $type Optional. Unused for now.
	 * @return void
	 */
	public function send_plugin_data() {
		DurbinClient::send( DurbinClient::DEACTIVATE_ACTION );
	}

	/**
	 * Output deactivation modal markup, CSS, and JS.
	 *
	 * @return void
	 */
	public function get_source_data_callback() {
		$this->deactive_container_css();
		$this->deactive_container_js();
		$this->deactive_html_container();
	}

	/**
	 * Get deactivation reasons and field settings.
	 *
	 * @return array[] List of deactivation options.
	 */
	public function get_deactive_settings() {
		return array(
			array(
                'id'            => 'not-working',
                'input'         => false,
                'text'          => __( "The plugin isn’t working properly.", "product-blocks" )
            ),
            array(
                'id'            => 'limited-features',
                'input'         => false,
                'text'          => __( "Limited features on the free version.", "product-blocks" )
            ),
            array(
                'id'            => 'better-plugin',
                'input'         => true,
                'text'          => __( "I found a better plugin.", "product-blocks" ),
                'placeholder'   => __( "Please share which plugin.", "product-blocks" ),
            ),
            array(
                'id'            => 'temporary-deactivation',
                'input'         => false,
                'text'          => __( "It's a temporary deactivation.", "product-blocks" )
            ),
            array(
                'id'            => 'other',
                'input'         => true,
                'text'          => __( "Other.", "product-blocks" ),
                'placeholder'   => __( "Please share the reason.", "product-blocks" ),
            ),
		);
	}

	/**
	 * Output HTML for the deactivation modal.
	 *
	 * @return void
	 */
	public function deactive_html_container() {
		?>
		<div class="ultp-modal" id="ultp-deactive-modal">
			<div class="ultp-modal-wrap">
			
				<div class="ultp-modal-header">
					<h2><?php esc_html_e( 'Quick Feedback', 'ultimate-post' ); ?></h2>
					<button class="ultp-modal-cancel"><span class="dashicons dashicons-no-alt"></span></button>
				</div>

				<div class="ultp-modal-body">
					<h3><?php esc_html_e( 'If you have a moment, please let us know why you are deactivating PostX:', 'ultimate-post' ); ?></h3>
					<ul class="ultp-modal-input">
						<?php foreach ( $this->get_deactive_settings() as $key => $setting ) { ?>
							<li>
								<label>
									<input type="radio" <?php echo 0 == $key ? 'checked="checked"' : ''; ?> id="<?php echo esc_attr( $setting['id'] ); ?>" name="<?php echo esc_attr( $this->plugin_slug ); ?>" value="<?php echo esc_attr( $setting['text'] ); ?>">
									<div class="ultp-reason-text"><?php echo esc_html( $setting['text'] ); ?></div>
									<?php if ( isset( $setting['input'] ) && $setting['input'] ) { ?>
										<textarea placeholder="<?php echo esc_attr( $setting['placeholder'] ); ?>" class="ultp-reason-input <?php echo $key == 0 ? 'ultp-active' : ''; ?> <?php echo esc_html( $setting['id'] ); ?>"></textarea>
									<?php } ?>
								</label>
							</li>
						<?php } ?>
					</ul>
				</div>

				<div class="ultp-modal-footer">
					<a class="ultp-modal-submit ultp-btn ultp-btn-primary" href="#"><?php esc_html_e( 'Submit & Deactivate', 'ultimate-post' ); ?><span class="dashicons dashicons-update rotate"></span></a>
					<a class="ultp-modal-deactive" href="#"><?php esc_html_e( 'Skip & Deactivate', 'ultimate-post' ); ?></a>
				</div>
				
			</div>
		</div>
		<?php
	}

	/**
	 * Output inline CSS for the modal.
	 *
	 * @return void
	 */
	public function deactive_container_css() {
		?>
		<style type="text/css">
			.ultp-modal {
				position: fixed;
				z-index: 99999;
				top: 0;
				right: 0;
				bottom: 0;
				left: 0;
				background: rgba(0,0,0,0.5);
				display: none;
				box-sizing: border-box;
				overflow: scroll;
			}
			.ultp-modal * {
				box-sizing: border-box;
			}
			.ultp-modal.modal-active {
				display: block;
			}
			.ultp-modal-wrap {
				max-width: 870px;
				width: 100%;
				position: relative;
				margin: 10% auto;
				background: #fff;
			}
			.ultp-reason-input{
				display: none;
			}
			.ultp-reason-input.ultp-active{
				display: block;
			}
			.rotate{
				animation: rotate 1.5s linear infinite; 
			}
			@keyframes rotate{
				to{ transform: rotate(360deg); }
			}
			.ultp-popup-rotate{
				animation: popupRotate 1s linear infinite; 
			}
			@keyframes popupRotate{
				to{ transform: rotate(360deg); }
			}
			#ultp-deactive-modal {
				background: rgb(0 0 0 / 85%);
				overflow: hidden;
			}
			#ultp-deactive-modal .ultp-modal-wrap {
				max-width: 570px;
				border-radius: 5px;
				margin: 5% auto;
				overflow: hidden
			}
			#ultp-deactive-modal .ultp-modal-header {
				padding: 17px 30px;
				border-bottom: 1px solid #ececec;
				display: flex;
				align-items: center;
				background: #f5f5f5;
			}
			#ultp-deactive-modal .ultp-modal-header .ultp-modal-cancel {
				padding: 0;
				border-radius: 100px;
				border: 1px solid #b9b9b9;
				background: none;
				color: #b9b9b9;
				cursor: pointer;
				transition: 400ms;
			}
			#ultp-deactive-modal .ultp-modal-header .ultp-modal-cancel:focus {
				color: red;
				border: 1px solid red;
				outline: 0;
			}
			#ultp-deactive-modal .ultp-modal-header .ultp-modal-cancel:hover {
				color: red;
				border: 1px solid red;
			}
			#ultp-deactive-modal .ultp-modal-header h2 {
				margin: 0;
				padding: 0;
				flex: 1;
				line-height: 1;
				font-size: 20px;
				text-transform: uppercase;
				color: #8e8d8d;
			}
			#ultp-deactive-modal .ultp-modal-body {
				padding: 25px 30px;
			}
			#ultp-deactive-modal .ultp-modal-body h3{
				padding: 0;
				margin: 0;
				line-height: 1.4;
				font-size: 15px;
			}
			#ultp-deactive-modal .ultp-modal-body ul {
				margin: 25px 0 10px;
			}
			#ultp-deactive-modal .ultp-modal-body ul li {
				display: flex;
				margin-bottom: 10px;
				color: #807d7d;
			}
			#ultp-deactive-modal .ultp-modal-body ul li:last-child {
				margin-bottom: 0;
			}
			#ultp-deactive-modal .ultp-modal-body ul li label {
				align-items: center;
				width: 100%;
			}
			#ultp-deactive-modal .ultp-modal-body ul li label input {
				padding: 0 !important;
				margin: 0;
				display: inline-block;
			}
			#ultp-deactive-modal .ultp-modal-body ul li label textarea {
				margin-top: 8px;
				width: 100% !important;
			}
			#ultp-deactive-modal .ultp-modal-body ul li label .ultp-reason-text {
				margin-left: 8px;
				display: inline-block;
			}
			#ultp-deactive-modal .ultp-modal-footer {
				padding: 0 30px 30px 30px;
				display: flex;
				align-items: center;
			}
			#ultp-deactive-modal .ultp-modal-footer .ultp-modal-submit {
				display: flex;
				align-items: center;
				padding: 12px 22px;
				border-radius: 3px;
				background: #037fff;
				color: #fff;
				font-size: 16px;
				font-weight: 600;
				text-decoration: none;
			}
			#ultp-deactive-modal .ultp-modal-footer .ultp-modal-submit span {
				margin-left: 4px;
				display: none;
			}
			#ultp-deactive-modal .ultp-modal-footer .ultp-modal-submit.loading span {
				display: block;
			}
			#ultp-deactive-modal .ultp-modal-footer .ultp-modal-deactive {
				margin-left: auto;
				color: #c5c5c5;
				text-decoration: none;
			}
			.wpxpo-btn-tracking-notice {
				display: flex;
				align-items: center;
				flex-wrap: wrap;
				padding: 5px 0;
			}
			.wpxpo-btn-tracking-notice .wpxpo-btn-tracking {
				margin: 0 5px;
				text-decoration: none;
			}
		</style>
		<?php
	}

	/**
	 * Output inline JavaScript for the modal logic.
	 *
	 * @return void
	 */
	public function deactive_container_js() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				'use strict';

				// Modal Radio Input Click Action
				$('.ultp-modal-input input[type=radio]').on( 'change', function(e) {
					$('.ultp-reason-input').removeClass('ultp-active');
					$('.ultp-modal-input').find( '.'+$(this).attr('id') ).addClass('ultp-active');
				});

				// Modal Cancel Click Action
				$( document ).on( 'click', '.ultp-modal-cancel', function(e) {
					$( '#ultp-deactive-modal' ).removeClass( 'modal-active' );
				});
				
				$(document).on('click', function(event) {
					const $popup = $('#ultp-deactive-modal');
					const $modalWrap = $popup.find('.ultp-modal-wrap');

					if ( !$modalWrap.is(event.target) && $modalWrap.has(event.target).length === 0 && $popup.hasClass('modal-active')) {
						$popup.removeClass('modal-active');
					}
				});

				// Deactivate Button Click Action
				$( document ).on( 'click', '#deactivate-ultimate-post', function(e) {
					e.preventDefault();
					e.stopPropagation();
					$( '#ultp-deactive-modal' ).addClass( 'modal-active' );
					$( '.ultp-modal-deactive' ).attr( 'href', $(this).attr('href') );
					$( '.ultp-modal-submit' ).attr( 'href', $(this).attr('href') );
				});

				// Submit to Remote Server
				$( document ).on( 'click', '.ultp-modal-submit', function(e) {
					e.preventDefault();
					
					$(this).addClass('loading');
					const url = $(this).attr('href')

					$.ajax({
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						type: 'POST',
						data: { 
							action: 'ultp_deactive_plugin',
							cause_id: $('#ultp-deactive-modal input[type=radio]:checked').attr('id'),
							cause_title: $('#ultp-deactive-modal .ultp-modal-input input[type=radio]:checked').val(),
							cause_details: $('#ultp-deactive-modal .ultp-reason-input.ultp-active').val()
						},
						success: function (data) {
							$( '#ultp-deactive-modal' ).removeClass( 'modal-active' );
							window.location.href = url;
						},
						error: function(xhr) {
							console.log( 'Error occured. Please try again' + xhr.statusText + xhr.responseText );
						},
					});

				});

			});
		</script>
		<?php
	}
}
