<?php
namespace LDQIE;
/**
 * License Class.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LDQIE_License {
	private $license_key_field = null;

	/**
	 * @var License_Handler
	 */
	private $license_handler = null;

	public function __construct() {
		$this->license_key_field = "wn_" . strtolower( __NAMESPACE__ ) . "_license_key";

		add_action( 'init', [ $this, 'plugin_init' ] );
		add_action( 'admin_menu', array( $this, 'ldqie_license_menu' ) );
		add_action( 'admin_notices', array( $this, 'show_license_expire_or_invalid' ), 20 );

		/**
		 * Enable these for local testing
		 */
		# add_filter( 'edd_sl_api_request_verify_ssl', '__return_false', 10, 2 );
		# add_filter( 'https_ssl_verify', '__return_false' );
		# add_filter( 'http_request_host_is_external', '__return_true', 10, 3 );
	}

	public function plugin_init() {
		if ( ! current_user_can( 'manage_options' ) || ! is_admin() )
			return;

        require_once 'License_Handler.php';

		$plugin_data = get_plugin_data( DIR_FILE );
		$this->license_handler = new License_Handler( DIR_FILE, $plugin_data['Name'], $plugin_data['Version'], $plugin_data['AuthorName'], $this->license_key_field );
	}

	public function show_license_expire_or_invalid() {
		if ( ! isset( $this->license_handler ) )
			return;

		$license_setting_url = add_query_arg( array( "page" => "ldqie-license-settings" ), admin_url( "admin.php" ) );
		$error_msg           = '';
		$success_msg         = '';
		$submission          = isset( $_POST['ldqie_activate_license'] ) || isset( $_POST['ldqie_deactivate_license'] );
		$invalid_license_err = __( 'Please enter a valid license key and for <strong> LearnDash Quiz Import/Export</strong> to recieve latest updates. <a href="' . esc_attr( $license_setting_url ) . '">License Settings</a>', 'ldqie' );
		$expired_license_err = __( 'Your License for <strong> LearnDash Quiz Import/Export</strong> has been expired. You will not recieve any future updates for this addon. Please purchase the addon from our site, <a href="https://wooninjas.com/wn-products/learndash-quiz-importexport/">here</a> to recieve a valid license key.', 'ldqie' );

		if ( $submission ) {
			if ( $this->license_handler->is_active() ) {
				$success_msg = __( 'License Activated!', 'ldqie' );
			} else if ( $this->license_handler->is_expired() ) {
			    $error_msg = $expired_license_err;
			} else if ( $this->license_handler->last_err() ) {
			    $error_msg = $invalid_license_err;
			} else if ( ! $this->license_handler->is_active() ) {
				$success_msg = __( 'License Deactivated!', 'ldqie' );
			}
        } else {
			if ( $this->license_handler->is_expired() ) {
				$error_msg = $expired_license_err;
			} else if ( ! $this->license_handler->is_active() ) {
				$error_msg = $invalid_license_err;
			}
        }

		if ( $success_msg ) { ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo $success_msg; ?></p>
            </div>
			<?php
		} else if ( $error_msg ) { ?>
            <div class="error notice">
                <p><?php echo $error_msg; ?></p>
            </div>
			<?php
		}
	}

	public function ldqie_license_menu() {
		$values = get_option( "quiz_default" );
        $minimum_role = '';
        if( isset( $values[ 'minimum_role_to_administer' ] ) )
            $minimum_role = trim( $values[ 'minimum_role_to_administer' ] );
        if( empty( $minimum_role ) ) {
            $minimum_role = "manage_options";
        }

		add_submenu_page(
			"ldqie-quiz-import",
			"Quiz Import/Export Default Settings",
			"License Settings",
			$minimum_role,
			"ldqie-license-settings",
			array( $this, ( "ldqie_license_management_page" ) )
		);
	}

	public function ldqie_license_management_page() {
		?>
        <div class="wrap">
            <h2><?php _e( 'License Configuration', 'ldqie' ); ?></h2>
            <h3><?php _e( "Please enter the license key for this product to get automatic updates. You were emailed the license key when you purchased this item", "ldqie" ) ?></h3>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th style="width:100px;"><label
                                    for="<?php echo $this->license_key_field ?>"><?php _e( "License Key", "ldqie" ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" id="<?php echo $this->license_key_field ?>"
                                   placeholder="Enter license key provided with plugin"
                                   name="<?php echo $this->license_key_field ?>"
                                   value="<?php echo get_option( "wn_ldqie_license_key" ); ?>"
								<?php echo ( $this->license_handler->is_active() ) ? 'readonly' : ''; ?>>
                        </td>
                    </tr>
                </table>
                <p class="submit">
					<?php if ( ! $this->license_handler->is_active() ) : ?>
                        <input type="submit" name="ldqie_activate_license" value="<?php _e( "Activate", "ldqie" ); ?>"
                               class="button-primary"/>
					<?php endif; ?>

					<?php if ( $this->license_handler->is_active() ) : ?>
                        <input type="submit" name="ldqie_deactivate_license" value="<?php _e( "Deactivate", "ldqie" ); ?>"
                               class="button-primary"/>
					<?php endif; ?>
                </p>
            </form>
        </div>
		<?php
	}

	/**
	 * @return License_Handler
	 */
	public function get_license_handler() {
		return $this->license_handler;
	}

	/**
	 * @return string
	 */
	public function get_license_key_field() {
		return $this->license_key_field;
	}
}
