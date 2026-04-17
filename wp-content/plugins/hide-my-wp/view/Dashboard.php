<?php defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' ); ?>
<?php if ( ! isset( $view ) ) { return; } ?>
<?php
$do_check = false;

// Set the alert if security wasn't check
if ( HMWP_Classes_Tools::getOption( 'hmwp_security_alert' ) ) {
	if ( ! get_option( HMWP_SECURITY_CHECK ) ) {
		$do_check = true;
	} elseif ( $securitycheck_time = get_option( HMWP_SECURITY_CHECK_TIME ) ) {
		if ( ( isset( $securitycheck_time['timestamp'] ) && time() - $securitycheck_time['timestamp'] > ( 3600 * 24 * 7 ) ) ) {
			$do_check = true;
		}
	} else {
		$do_check = true;
	}
}
?>
<style>
    .wp_loading {
        border: 16px solid #f3f3f3;
        border-top: 16px solid #b0794a;
        border-radius: 50%;
        width: 80px;
        height: 80px;
        animation: spin 2s linear infinite;
        margin: 20px auto 0 auto;
    }

    .wp_button {
        display: block;
        font-weight: 400;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        border-radius: 0;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        padding: .9rem 1.5rem;
        font-size: 1rem;
        line-height: 1;
        color: #fff !important;
        background-color: #007cba;
        border-color: #405c7b;
        margin: 1rem auto;
        text-decoration: none;
        max-width: 300px;
        cursor: pointer;
    }

    .wp_button_default {
        background: #f3f5f6;
        border-color: #007cba;
        -webkit-box-shadow: 0 0 0 1px #007cba;
        box-shadow: 0 0 0 1px #007cba;
        color: #016087 !important;
        outline: 2px solid transparent;
        outline-offset: 0;
    }

    .hmwp_widget_content {
        container-type: inline-size;
        container-name: hmwpwrap;
    }

    @container hmwpwrap (max-width: 599px) {
        td.hmwp_widget_log {
            display: none !important;
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>

<div class="hmwp_widget_content" style="position: relative;">
	<?php if ( ! $do_check ) { ?>
        <div style="text-align: center">

            <table style="margin:auto">
                <tr>
                    <td class="hmwp_widget_security">
						<?php if ( ( ( count( $view->riskreport ) * 100 ) / count( $view->risktasks ) ) > 90 ) { ?>
                            <a href="<?php echo HMWP_Classes_Tools::getSettingsUrl( 'hmwp_securitycheck' ) ?>"><img src="<?php echo _HMWP_ASSETS_URL_ . 'img/speedometer_danger.png' ?>" alt="" style="max-width: 75%; height: auto; margin: 10px auto;"/></a>
                            <div style="font-size: 1rem; font-style: italic; text-align: center; color: red;"><?php echo sprintf( esc_html__( "Your website security %sis extremely weak%s. %sMany hacking doors are available.", 'hide-my-wp' ), '<strong>', '</strong>', '<br />' ) ?></div>
						<?php } elseif ( ( ( count( $view->riskreport ) * 100 ) / count( $view->risktasks ) ) > 50 ) { ?>
                            <a href="<?php echo HMWP_Classes_Tools::getSettingsUrl( 'hmwp_securitycheck' ) ?>"><img src="<?php echo _HMWP_ASSETS_URL_ . 'img/speedometer_low.png' ?>" alt="" style="max-width: 75%; height: auto; margin: 10px auto;"/></a>
                            <div style="font-size: 1rem; font-style: italic; text-align: center; color: red;"><?php echo sprintf( esc_html__( "Your website security %sis very weak%s. %sMany hacking doors are available.", 'hide-my-wp' ), '<strong>', '</strong>', '<br />' ) ?></div>
						<?php } elseif ( ( ( count( $view->riskreport ) * 100 ) / count( $view->risktasks ) ) > 20 ) { ?>
                            <a href="<?php echo HMWP_Classes_Tools::getSettingsUrl( 'hmwp_securitycheck' ) ?>"><img src="<?php echo _HMWP_ASSETS_URL_ . 'img/speedometer_medium.png' ?>" alt="" style="max-width: 75%; height: auto; margin: 10px auto;"/></a>
                            <div style="font-size: 1rem; font-style: italic; text-align: center; color: orangered;"><?php echo sprintf( esc_html__( "Your website security is still weak. %sSome of the main hacking doors are still available.", 'hide-my-wp' ), '<br />' ) ?></div>
						<?php } elseif ( ( ( count( $view->riskreport ) * 100 ) / count( $view->risktasks ) ) > 0 ) { ?>
                            <img src="<?php echo _HMWP_ASSETS_URL_ . 'img/speedometer_better.png' ?>" alt="" style="max-width: 75%; height: auto; margin: 10px auto;"/>
                            <div style="font-size: 1rem; font-style: italic; text-align: center; color: orangered;"><?php echo sprintf( esc_html__( "Your website security is getting better. %sJust make sure you complete all the security tasks.", 'hide-my-wp' ), '<br />' ) ?></div>
						<?php } else { ?>
                            <a href="<?php echo HMWP_Classes_Tools::getSettingsUrl( 'hmwp_securitycheck' ) ?>"><img src="<?php echo _HMWP_ASSETS_URL_ . 'img/speedometer_high.png' ?>" alt="" style="max-width: 75%; height: auto; margin: 10px auto;"/></a>
                            <div style="font-size: 1rem; font-style: italic; text-align: center; color: green;"><?php echo sprintf( esc_html__( "Lite Mode is fully set up. If you want even fewer bot scans, Premium adds advanced hack protection and can block up to %s 99%% of automated attacks %s.", 'hide-my-wp' ), '<strong>', '</strong>' ) ?></div>
						<?php } ?>


                    </td>
					<?php

                    $stats = array();

                    if ( ! $stats['block_ip'] ) {
                        $stats['block_ip'] = '-';
                    }
                    if ( ! $stats['alerts'] ) {
                        $stats['alerts'] = '-';
                    }
                    ?>
                    <td class="hmwp_widget_log" style="width: 40%">
                        <table>
                            <tr>
                                <td colspan="2" style="padding: 15px 0;">
                                    <h6><?php echo esc_html__( 'Last 30 days Security Stats', 'hide-my-wp' ); ?></h6>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align:top; text-align: center; margin: 0;padding: 0; width: 220px;">
                                    <div style="font-size: 1.2rem; border: 2px solid #34B262; padding: 5px; margin: 5px 15px;">
                                        <a href="<?php echo HMWP_Classes_Tools::getSettingsUrl( 'hmwp_brute#tab=blocked', true ) ?>" style="text-decoration: none"><?php echo esc_html( $stats['block_ip'] ) ?></a>
                                    </div>
                                    <div style="font-size: 1rem;"><?php echo esc_html__( 'Brute Force IPs Blocked', 'hide-my-wp' ); ?></div>
                                </td>
                                <td style="vertical-align:top; text-align: center; margin: 0;padding: 0; width: 220px;">
                                    <div style="font-size: 1.2rem; border: 2px solid #C18032; padding: 5px; margin: 5px 15px;">
                                        <a href="<?php echo HMWP_Classes_Tools::getSettingsUrl( 'hmwp_log#tab=report', true ) ?>" style="text-decoration: none"><?php echo esc_html( $stats['alerts'] ) ?></a>
                                    </div>
                                    <div style="font-size: 1rem;"><?php echo esc_html__( 'Alert Emails Sent', 'hide-my-wp' ); ?></div>
                                </td>
                            </tr>
                            <?php if ( ! HMWP_Classes_Tools::getOption( 'hmwp_activity_log' ) ) { ?>
                                <tr>
                                    <td colspan="2" style="padding: 20px 0;">
                                        <a href="<?php echo HMWP_Classes_Tools::getSettingsUrl( 'hmwp_log#tab=log', true ) ?>" class="wp_button"><?php echo esc_html__( 'Activate Events Log', 'hide-my-wp' ); ?></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

		<?php if ( ( ( count( $view->riskreport ) * 100 ) / count( $view->risktasks ) ) > 0 ) { ?>
            <div style="margin: 40px 0;">
                <div style="font-size: 1.4rem; margin-bottom: 20px; text-align: center;"><?php echo esc_html__( 'Urgent Security Actions Required', 'hide-my-wp' ) ?>:</div>
                <ul style="margin: 0;padding: 0;list-style: none;">
					<?php foreach ( $view->riskreport as $function => $row ) { ?>
                        <li style="margin: 10px 0;padding: 10px;line-height: 30px;border: 1px solid #f3ebd0; border-left: 2px solid #d63638;"> <?php echo wp_kses_post( $row['solution'] ) ?></li>
					<?php } ?>
                </ul>

            </div>
		<?php } ?>
	<?php } ?>

    <div style="text-align: center; margin-top: 30px">
        <form id="hmwp_securitycheck" method="POST">
			<?php wp_nonce_field( 'hmwp_widget_securitycheck', 'hmwp_nonce' ) ?>
            <input type="hidden" name="action" value="hmwp_widget_securitycheck"/>
        </form>
        <a href="<?php echo HMWP_Classes_Tools::getSettingsUrl( 'hmwp_securitycheck', true ) ?>" class="wp_button"><?php echo esc_html__( 'Run Full Security Check', 'hide-my-wp' ); ?></a>
    </div>

    <?php if (count( $view->risktasks )) { ?>
        <div style="font-size: 0.95rem; text-align: center; margin: 40px auto 20px auto; max-width: 600px;">
            <?php echo sprintf( esc_html__( "If WP Ghost helped you, please consider leaving a 5-star review. It supports updates and keeps the Lite version strong. %s", 'hide-my-wp' ), '<a href="https://wordpress.org/support/plugin/hide-my-wp/reviews/?filter=5#new-post" target="_blank"><span class="hmwp-stars" aria-hidden="true"><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></span></a>'); ?>
        </div>
    <?php } ?>

</div>

<script>
    (function ($) {
        $.fn.hmwp_widget_recheck = function () {
            var $this = this;
            var $div = $this.find('.inside');
            $div.find('.hmwp_widget_content').css('opacity', 0.3);
            $div.find('.hmwp_widget_content').find('button').prop("disabled", true);
            $div.find('.hmwp_widget_content').after('<div class="wp_loading" style="width: 30px;height: 30px;margin:5px auto;"></div>');

            $.post(
                ajaxurl,
                $('form#hmwp_securitycheck').serialize()
            ).done(function (response) {
                if (typeof response.data !== 'undefined') {
                    $div.html(response.data);
                }
            }).error(function () {
                $div.html('');
            });
        };

        $(document).ready(function () {

            $('#hmwp_dashboard_widget').find('.recheck_security').on('click', function () {
                $('#hmwp_dashboard_widget').hmwp_widget_recheck();
            });

			<?php if($do_check) { ?>
            // $('#hmwp_dashboard_widget').hmwp_widget_recheck();
            // $('#hmwp_securitycheck_widget').hmwp_widget_recheck();
			<?php }?>
        });
    })(jQuery);

</script>
