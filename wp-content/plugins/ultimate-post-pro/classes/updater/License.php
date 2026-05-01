<?php
namespace ULTP_PRO;

defined('ABSPATH') || exit;

class License{

    private $page_slug   = 'ultp-license';
    private $server_url  = 'https://www.wpxpo.com';
    private $item_id     = 181;
    private $name        = 'PostX Pro';
    private $version     = ULTP_PRO_VER;
    private $slug        = 'ultimate-post-pro/ultimate-post-pro.php';

    public function __construct(){
        add_action('admin_init',    array($this, 'edd_license_updater'));
        add_action('admin_init',    array($this, 'edd_activate_license'));
        add_action('admin_menu',    array($this, 'menu_page_callback'), 11);
    }
    
    public function edd_license_updater() {

        if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
            require_once ULTP_PRO_PATH.'classes/updater/EDD_SL_Plugin_Updater.php';
        }

        $license_key = trim( get_option( 'edd_ultp_license_key' ) );

        $edd_updater = new \EDD_SL_Plugin_Updater(
            $this->server_url,
            $this->slug,
            array(
                'version' => $this->version,
                'license' => $license_key,
                'item_id' => $this->item_id,
                'author'  => $this->name,
                'url'     => home_url(),
                'beta'    => false,
            )
        );

    }

    public function menu_page_callback() {
        add_submenu_page(
            'ultp-settings',
            __('License', 'ultimate-post-pro'),
            __('License', 'ultimate-post-pro'),
            'manage_options',
            $this->page_slug,
            array($this, 'edd_license_page'),
            25
        );
    }

    public function edd_license_page() {
        $license = get_option( 'edd_ultp_license_key' );
        $status  = get_option( 'edd_ultp_license_status' );
        $this->license_css();
        ?>
        <div class="ultp-option-body">
            <div class="ultp-setting-header">
                <div class="ultp-setting-header-info">
                    <img class="ultp-setting-header-img" src="<?php echo ULTP_PRO_URL.'assets/img/logo-option.svg'; ?>" alt="<?php _e('PostX', 'ultimate-post-pro'); ?>">
                    <h1>
                        <?php _e('Welcome to <strong>PostX Pro</strong> - Version', 'ultimate-post-pro'); ?><span> <?php echo ULTP_PRO_VER; ?></span>
                    </h1>
                    <p><?php esc_html_e('Most Powerful & Advanced Gutenberg Post Grid Blocks', 'ultimate-post-pro'); ?><a href="https://wordpress.org/support/plugin/ultimate-post/reviews/#new-post"><?php esc_html_e('Rate the plugin', 'ultimate-post-pro'); ?><span>★★★★★<span></a></p>
                </div>
            </div>
            <div class="ultp-content-wrap">
                <form method="post" action="options.php">
                    <?php settings_fields('edd_ultp_license'); ?>
                    <?php 
                        if (isset($_GET['note'])) {
                            echo '<div style="padding:.75rem 1.25rem; border-radius:.25rem; color:#721c24; background-color:#f8d7da; border-color:#f5c6cb;">'.$_GET['note'].'</div>';
                        }
                    ?>
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row" valign="top">
                                    <?php _e('License Key', 'ultimate-post-pro'); ?>
                                </th>
                                <td>
                                    <?php wp_nonce_field( 'edd_download_nonce', 'edd_download_nonce' ); ?>
                                    <input id="edd_ultp_license_key" name="edd_ultp_license_key" type="password" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
                                    <?php if( $status !== false && $status == 'valid' ) { ?>
                                        <span style="color: #ffffff;background-color: #34a853;padding: 2px 7px 4px;border-radius: 3px;line-height: 30px;"><?php _e('Your License Key is Activated.', 'ultimate-post-pro'); ?></span>
                                    <?php } else { ?>
                                        <span style="color: #ffffff;background-color: #da0505;padding: 2px 7px 4px;border-radius: 3px;line-height: 30px;"><?php _e('Your License Key is Not Activated', 'ultimate-post-pro'); ?></span>
                                    <?php } ?>
                                    <br/><label class="description" for="edd_ultp_license_key"><?php _e('Enter your license key', 'ultimate-post-pro'); ?></label>
                                    
                                    <?php if( $status !== false && $status != 'valid' ) { ?>
                                        <br/>
                                        <div class="wrap"><?php _e('Your License is not Activated.', 'ultimate-post-pro'); ?> <a class="page-title-action" target="_blank" href="https://www.wpxpo.com/postx/"><?php _e('Buy Pro', 'ultimate-post-pro'); ?></a></div>
                                        <div class="wrap"><a href="https://docs.wpxpo.com/docs/postx/getting-started/pro-version-installation/" target="_blank">How to activate license key?</a></div>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th></th>
                                <td><?php submit_button(__('Check & Save License', 'ultimate-post-pro')); ?></td>
                            </tr>
                            <tr>
                                <?php if( $status !== false && $status != 'valid' ) { ?>
                                    <th><?php _e('How to Activate Licence?', 'ultimate-post-pro'); ?></th>
                                    <td><a target="_blank" class="button" href="https://docs.wpxpo.com/docs/postx/getting-started/pro-version-installation/"><?php _e('Read Documentation', 'ultimate-post-pro'); ?></a></td>
                                <?php } ?>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        <?php
    }


     /**
	 * License Page CSS
     * 
     * @since v.1.0.0
	 * @param NULL
	 * @return STRING
	 */
	public function license_css() { ?>
		<style type="text/css">
            .ultp-option-body {
                position: relative;
                font-size: 15px;
                max-width: calc( 100% + 20px);
                display:block;
                margin-left: -20px;
                background: #f2f2f2;
            }
            .ultp-setting-header {
                padding: 30px 40px;
                margin-bottom: 20px;
                box-sizing: border-box;
                background: #fff;
                text-align: center;
                border-bottom: 1px solid #dedede;
            }
            .ultp-setting-header2 {
                margin-bottom: 0;
                border-bottom: none;
            }
            .ultp-setting-header img {
                margin-left:auto;
            }
            .ultp-setting-header-info img {
                margin-bottom: 8px;
            }
            .ultp-setting-header-info h1 {
                margin: 0;
                font-weight: 300;
                font-size: 24px;
                line-height: normal;
                color: #000;
            }
            .ultp-setting-header-info p {
                font-size: 14px;
                margin-top: 10px;
                margin-bottom: 0;
                font-weight:300;
                color: #656565;
            }
            .ultp-setting-header-info p a {
                margin-left: 5px;
                text-decoration: none;
                color: #037fff;
            }
            .ultp-setting-header-info p a span {
                color: #FF9920;
                margin-left: 7px;
                font-size: 14px;
                letter-spacing: 4px;
            }
            .ultp-content-wrap {
                padding: 20px;
                max-width: 1110px;
                margin: 0 auto;
                box-sizing: border-box;
            }
            .ultp-setting-header-img {
                max-width: 180px;
            }
        </style>
    <?php
    }


    public function edd_activate_license() {

        if(isset($_POST['edd_ultp_license_key'])){
            if( ! check_admin_referer( 'edd_download_nonce', 'edd_download_nonce' ) ) {
                return '';
            }

            $license = trim( sanitize_text_field( $_POST['edd_ultp_license_key'] ) );
            update_option( 'edd_ultp_license_key', $license);
    
            $api_params = array(
                'edd_action' => 'activate_license',
                'license'    => $license,
                'item_id'    => $this->item_id,
                'url'        => home_url()
            );
    
            $response = wp_remote_post( $this->server_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __('An error occurred, please try again.', 'ultimate-post-pro');
            } else {
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                if ( false === $license_data->success ) {
                    switch( $license_data->error ) {
                        case 'expired' :
                            $message = sprintf(
                                __('Your license key expired on %s.', 'ultimate-post-pro'),
                                date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                            );
                            break;
                        case 'revoked' :
                            $message = __('Your license key has been disabled.', 'ultimate-post-pro');
                            break;
                        case 'missing' :
                            $message = __('Invalid license.', 'ultimate-post-pro');
                            break;
                        case 'invalid' :
                        case 'site_inactive':
                            $message = __( 'Your license is not active for this URL.', 'ultimate-post-pro' );
                            break;
                        case 'item_name_mismatch':
                            $message = __( 'This appears to be an invalid license key.', 'ultimate-post-pro' );
                            break;
                        case 'no_activations_left':
                            $message = __( 'Your license key has reached its activation limit.', 'ultimate-post-pro' );
                            break;
                        default :
                            $message = __( 'An error occurred, please try again.', 'ultimate-post-pro' );
                            break;
                    }
                }
    
                if ( ! empty( $message ) ) {
                    $base_url = admin_url( 'admin.php?page=' . $this->page_slug );
                    $redirect = add_query_arg( array( 'sl_activation' => 'false', 'note' => urlencode( $message ) ), $base_url );
                    wp_redirect( $redirect );
                }
                update_option( 'edd_ultp_license_status', $license_data->license );
                wp_redirect( admin_url( 'admin.php?page=' . $this->page_slug ) );
                exit();
            }
        }
    }
}