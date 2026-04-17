<?php
namespace ULTP_PRO;

defined('ABSPATH') || exit;

class Notice {
    public function __construct(){
		add_action('admin_init', array($this, 'admin_init_callback'));
		add_action('wp_ajax_ultp_install', array($this, 'ultp_install_callback'));
		add_action('admin_action_ultp_activate', array($this, 'ultp_activate_callback'));
		add_action('wp_ajax_ultp_dismiss_notice', array($this, 'set_dismiss_notice_callback'));
	}

	
	// Dismiss Notice Callback
	public function set_dismiss_notice_callback() {
		if (!wp_verify_nonce($_REQUEST['wpnonce'], 'ultp-nonce')) {
			return ;
		}
		update_option( 'ultp_dismiss_notice', 'yes' );
	}


	public function admin_init_callback(){
		if (!file_exists(WP_PLUGIN_DIR.'/ultimate-post/ultimate-post.php')) {
			add_action('admin_notices', array($this, 'ultp_installation_notice_callback'));
		} else if (file_exists(WP_PLUGIN_DIR.'/ultimate-post/ultimate-post.php') && ! is_plugin_active('ultimate-post/ultimate-post.php')) {
			add_action('admin_notices', array($this, 'ultp_activation_notice_callback'));
		}
	}


	public function ultp_installation_notice_callback() {
		if (!get_option('ultp_dismiss_notice')) {
			$this->ultp_notice_css();
			$this->ultp_notice_js();
			?>
			<div class="wc-install">
				<img width="150" src="<?php echo ULTP_PRO_URL.'assets/img/ultp.png'; ?>" alt="logo" />
				<div class="wc-install-body">
					<a class="wc-dismiss-notice" data-security=<?php echo wp_create_nonce('ultp-nonce'); ?>  data-ajax=<?php echo admin_url('admin-ajax.php'); ?> href="#"><span class="dashicons dashicons-no-alt"></span> <?php _e('Dismiss', 'ultimate-post-pro'); ?></a>
					<h3><?php _e('Welcome to PostX Pro.', 'ultimate-post-pro'); ?></h3>
					<div><?php _e('PostX Pro is a Gutenberg block plugin. To use this plugins you have to install and activate PostX Free.', 'ultimate-post-pro'); ?></div>
					<a class="ultp-install-btn button button-primary button-hero" href="<?php echo add_query_arg(array('action' => 'ultp_install'), admin_url()); ?>"><span class="dashicons dashicons-image-rotate"></span><?php _e('Install PostX', 'ultimate-post-pro'); ?></a>
					<div id="installation-msg"></div>
				</div>
			</div>
			<?php
		}
	}

	public function ultp_activation_notice_callback() {
		if (!get_option('ultp_dismiss_notice')) {
			$this->ultp_notice_css();
			$this->ultp_notice_js();
			?>
			<div class="wc-install">
				<img width="150" src="<?php echo ULTP_PRO_URL.'assets/img/ultp.png'; ?>" alt="logo" />
				<div class="wc-install-body">
					<a class="wc-dismiss-notice" data-security=<?php echo wp_create_nonce('ultp-nonce'); ?>  data-ajax=<?php echo admin_url('admin-ajax.php'); ?> href="#"><span class="dashicons dashicons-no-alt"></span> <?php _e('Dismiss', 'ultimate-post-pro'); ?></a>
					<h3><?php _e('Welcome to PostX Pro.', 'ultimate-post-pro'); ?></h3>
					<div><?php _e('PostX Pro is a Gutenberg Block plugin. To use this plugins you have to install and activate PostX Free.', 'ultimate-post-pro'); ?></div>
					<a class="button button-primary button-hero" href="<?php echo add_query_arg(array('action' => 'ultp_activate'), admin_url()); ?>"><?php _e('Activate PostX', 'ultimate-post-pro'); ?></a>
				</div>
			</div>
			<?php
		}
	}


	public function ultp_notice_css() {
		?>
		<style type="text/css">
            .wc-install {
                display: -ms-flexbox;
                display: flex;
                align-items: center;
                background: #fff;
                margin-top: 40px;
                width: calc(100% - 50px);
                border: 1px solid #ccd0d4;
                padding: 15px;
                border-radius: 4px;
            }   
            .wc-install img {
                margin-right: 20px; 
            }
            .wc-install-body {
                -ms-flex: 1;
                flex: 1;
            }
            .wc-install-body > div {
                max-width: 450px;
                margin-bottom: 20px;
            }
            .wc-install-body h3 {
                margin-top: 0;
                font-size: 24px;
                margin-bottom: 15px;
            }
            .ultp-install-btn {
                margin-top: 15px;
                display: inline-block;
            }
			.wc-install .dashicons{
				display: none;
				animation: dashicons-spin 1s infinite;
				animation-timing-function: linear;
			}
			.wc-install.loading .dashicons {
				display: inline-block;
				margin-top: 12px;
				margin-right: 5px;
			}
			@keyframes dashicons-spin {
				0% {
					transform: rotate( 0deg );
				}
				100% {
					transform: rotate( 360deg );
				}
			}
			.wc-dismiss-notice {
				position: relative;
				text-decoration: none;
				float: right;
				right: 26px;
			}
			.wc-dismiss-notice .dashicons{
				display: inline-block;
    			text-decoration: none;
				animation: none;
			}
		</style>
		<?php
	}


	public function ultp_notice_js() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				'use strict';
				$(document).on('click', '.ultp-install-btn', function(e){
					e.preventDefault();
					const $that = $(this);
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {install_plugin: 'ultimate-post', action: 'ultp_install'},
						beforeSend: function(){
                                $that.parents('.wc-install').addClass('loading');
                        },
						success: function (data) {
							$('#installation-msg').html(data);
							$that.parents('.wc-install').remove();
						},
						complete: function () {
							$that.parents('.wc-install').removeClass('loading');
						}
					});
				});

				// Dismiss notice
				$(document).on('click', '.wc-dismiss-notice', function(e){
					e.preventDefault();
					const that = $(this);
					$.ajax({
						url: that.data('ajax'),
						type: 'POST',
						data: { 
							action: 'ultp_dismiss_notice', 
							wpnonce: that.data('security')
						},
						success: function (data) {
							that.parents('.wc-install').hide("slow", function() { that.parents('.wc-install').remove(); });
						},
						error: function(xhr) {
							console.log('Error occured. Please try again' + xhr.statusText + xhr.responseText );
						},
					});
				});
				
			});
		</script>
		<?php
	}


	public function ultp_install_callback(){
		include(ABSPATH . 'wp-admin/includes/plugin-install.php');
		include(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

		if (! class_exists('Plugin_Upgrader')){
			include(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');
		}
		if (! class_exists('Plugin_Installer_Skin')) {
			include( ABSPATH . 'wp-admin/includes/class-plugin-installer-skin.php' );
		}

		$plugin = 'ultimate-post';

		$api = plugins_api( 'plugin_information', array(
			'slug' => $plugin,
			'fields' => array(
				'short_description' => false,
				'sections' => false,
				'requires' => false,
				'rating' => false,
				'ratings' => false,
				'downloaded' => false,
				'last_updated' => false,
				'added' => false,
				'tags' => false,
				'compatibility' => false,
				'homepage' => false,
				'donate_link' => false,
			),
		) );

		if ( is_wp_error( $api ) ) {
			wp_die( $api );
		}

		$title = sprintf( __('Installing Plugin: %s', 'ultimate-post-pro'), $api->name . ' ' . $api->version );
		$nonce = 'install-plugin_' . $plugin;
		$url = 'update.php?action=install-plugin&plugin=' . urlencode( $plugin );

		$upgrader = new \Plugin_Upgrader( new \Plugin_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
		$upgrader->install($api->download_link);
		die();
	}


	public function ultp_activate_callback(){
		activate_plugin('ultimate-post/ultimate-post.php');
		wp_redirect(admin_url('admin.php?page=ultp-settings'));
		exit();
	}

}