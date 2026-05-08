<?php
/**
 * User_Registration Style Customizer setup
 *
 * @package User_Registration_Style_Customizer
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main User_Registration Style Customizer Class.
 *
 * @class User_Registration_Style_Customizer
 */
final class User_Registration_Style_Customizer {
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.8';
	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;
	/**
	 * Initialize the plugin.
	 */
	private function __construct() {

		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks if user registration is installed.
		$ur_pro_plugins_path = WP_PLUGIN_DIR . URSC_DS . 'user-registration-pro' . URSC_DS . 'user-registration.php';

		if ( file_exists( $ur_pro_plugins_path ) ) {

			$ur_pro_plugin_file_path = 'user-registration-pro/user-registration.php';
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( is_plugin_active( $ur_pro_plugin_file_path ) ) {

				if ( defined( 'UR_VERSION' ) && version_compare( UR_VERSION, '4.0.0', '>=' ) ) {
					$this->configs();
					$this->includes();

					// Hooks.
					add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
					add_action( 'user_registration_form_shortcode_scripts', array( $this, 'enqueue_shortcode_scripts' ) );
					add_action( 'user_registration_before_customer_login_form', array( $this, 'enqueue_login_shortcode_scripts' ) );
					add_action( 'user_registration_form_builder_wrapper_footer', array( $this, 'output_form_designer' ) );
					add_action( 'user_registration_admin_field_link', array( $this, 'render_customize_login_button' ) );
					add_filter( 'user_registration_login_options_settings', array( $this, 'login_option_customizer_button' ) );
					add_action( 'user_registration_after_form_duplication', array( $this, 'user_registration_duplicate_form_styles' ), 10, 2 );
				} else {
					add_action( 'admin_notices', array( $this, 'user_registration_missing_notice' ) );
				}
			} else {

				add_action( 'admin_notices', array( $this, 'user_registration_missing_notice' ) );

			}
		} else {
			add_action( 'admin_notices', array( $this, 'user_registration_missing_notice' ) );

		}
	}


	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/user-registration-style-customizer/user-registration-style-customizer-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/user-registration-style-customizer-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'user-registration-style-customizer' );
		load_textdomain( 'user-registration-style-customizer', WP_LANG_DIR . '/user-registration-style-customizer/user-registration-style-customizer-' . $locale . '.mo' );
		load_plugin_textdomain( 'user-registration-style-customizer', false, plugin_basename( dirname( UR_STYLE_CUSTOMIZER_PLUGIN_FILE ) ) . '/languages' );
	}
	/**
	 * Configs.
	 */
	private function configs() {

		if ( ! isset( $_GET['ur-customize-login'] ) ) {
			require_once __DIR__ . '/configs/registration/ur-style-customizer-templates-configs.php';
			require_once __DIR__ . '/configs/registration/ur-style-customizer-form-wrapper-configs.php';
			require_once __DIR__ . '/configs/registration/ur-style-customizer-field-label-configs.php';
			require_once __DIR__ . '/configs/registration/ur-style-customizer-field-description-configs.php';
			require_once __DIR__ . '/configs/registration/ur-style-customizer-field-styles-configs.php';
			require_once __DIR__ . '/configs/registration/ur-style-customizer-radio-checkbox-styles-configs.php';
			require_once __DIR__ . '/configs/registration/ur-style-customizer-section-title-configs.php';
			require_once __DIR__ . '/configs/registration/ur-style-customizer-button-configs.php';
			require_once __DIR__ . '/configs/registration/ur-style-customizer-messages-configs.php';
		} else {
			require_once __DIR__ . '/configs/login/ur-style-customizer-templates-configs.php';
			require_once __DIR__ . '/configs/login/ur-style-customizer-form-wrapper-configs.php';
			require_once __DIR__ . '/configs/login/ur-style-customizer-field-label-configs.php';
			require_once __DIR__ . '/configs/login/ur-style-customizer-field-styles-configs.php';
			require_once __DIR__ . '/configs/login/ur-style-customizer-checkbox-styles-configs.php';
			require_once __DIR__ . '/configs/login/ur-style-customizer-button-configs.php';
			require_once __DIR__ . '/configs/login/ur-style-customizer-messages-configs.php';
		}
	}
	/**
	 * Includes.
	 */
	private function includes() {
		require_once __DIR__ . '/functions.php';
		require_once __DIR__ . '/class-ur-style-customizer-api.php';
		require_once __DIR__ . '/class-ur-style-customizer-ajax.php';
		require_once __DIR__ . '/libraries/wptt-webfont-loader.php';
	}
	/**
	 * Get the customizer url.
	 */
	private function get_customizer_url() {
		$form_id        = isset( $_GET['edit-registration'] ) ? absint( $_GET['edit-registration'] ) : false; // WPCS: input var ok, CSRF ok.
		$customizer_url = esc_url_raw(
			add_query_arg(
				array(
					'ur-style-customizer' => true,
					'form_id'             => $form_id,
					'return'              => rawurlencode(
						add_query_arg(
							array(
								'page'              => 'add-new-registration',
								'edit-registration' => $form_id,
							),
							admin_url( 'admin.php' )
						)
					),
				),
				admin_url( 'customize.php' )
			)
		);
		return $customizer_url;
	}

	/**
	 * Get the login customizer url.
	 */
	private function get_login_customizer_url() {
		$customizer_url = esc_url_raw(
			add_query_arg(
				array(
					'ur-style-customizer' => true,
					'ur-customize-login'  => true,
					'return'              => rawurlencode(
						add_query_arg(
							array(
								'page'    => 'user-registration-settings',
								'tab'     => 'general',
								'section' => 'login-options',
							),
							admin_url( 'admin.php' )
						)
					),
				),
				admin_url( 'customize.php' )
			)
		);
		return $customizer_url;
	}

	/**
	 * Enqueue scripts.
	 */
	public function admin_enqueue_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		// Register admin scripts.
		wp_register_style( 'user-registration-customize-admin', plugins_url( '/assets/css/customize-admin.css', UR_STYLE_CUSTOMIZER_PLUGIN_FILE ), array(), self::VERSION );
		// Add RTL support for admin styles.
		wp_style_add_data( 'user-registration-customize-admin', 'rtl', 'replace' );
		// Admin styles for UR Admin pages only.
		if ( in_array( $screen_id, ur_get_screen_ids(), true ) ) {
			wp_enqueue_style( 'user-registration-customize-admin' );
		}
	}

	/**
	 * Enqueue shortcode scripts.
	 *
	 * @param array $atts Shortcode Attributes.
	 */
	public function enqueue_shortcode_scripts( $atts ) {

		$form_id       = absint( $atts['id'] );
		$upload_dir    = wp_upload_dir( null, false );
		$style_options = get_option( 'user_registration_styles' );

		// Enqueue shortcode styles.
		if ( file_exists( trailingslashit( $upload_dir['basedir'] ) . 'user_registration_styles/user-registration-' . $form_id . '.css' ) ) {
			wp_enqueue_style( 'user-registration-style-' . $form_id, trailingslashit( $upload_dir['baseurl'] ) . 'user_registration_styles/user-registration-' . $form_id . '.css', array(), filemtime( trailingslashit( $upload_dir['basedir'] ) . 'user_registration_styles/user-registration-' . $form_id . '.css' ), 'all' );
		}

		// Enqueue google fonts styles.
		if ( isset( $style_options[ $form_id ]['wrapper']['font_family'] ) && '' !== $style_options[ $form_id ]['wrapper']['font_family'] ) {
			$load_font_locally = isset( $style_options[ $form_id ]['wrapper']['load_fonts_locally'] ) ? $style_options[ $form_id ]['wrapper']['load_fonts_locally'] : false;
			$font_family       = $style_options[ $form_id ]['wrapper']['font_family'];

			ursc_enqueue_fonts( $font_family, $load_font_locally );
		}
	}

	/**
	 * Enqueue login shortcode scripts.
	 */
	public function enqueue_login_shortcode_scripts() {

		$upload_dir    = wp_upload_dir( null, false );
		$style_options = get_option( 'user_registration_login_styles' );

		// Enqueue shortcode styles.
		if ( file_exists( trailingslashit( $upload_dir['basedir'] ) . 'user_registration_styles/user-registration-login.css' ) ) {
			wp_enqueue_style( 'user-registration-style-login', trailingslashit( $upload_dir['baseurl'] ) . 'user_registration_styles/user-registration-login.css', array(), filemtime( trailingslashit( $upload_dir['basedir'] ) . 'user_registration_styles/user-registration-login.css' ), 'all' );
		}

		// Enqueue google fonts styles.
		if ( isset( $style_options['wrapper']['font_family'] ) && '' !== $style_options['wrapper']['font_family'] ) {
			$load_font_locally = isset( $style_options['wrapper']['load_fonts_locally'] ) ? $style_options['wrapper']['load_fonts_locally'] : false;
			$font_family       = $style_options['wrapper']['font_family'];

			ursc_enqueue_fonts( $font_family, $load_font_locally );
		}
	}

	/**
	 * Output form designer.
	 */
	public function output_form_designer() {
		?>
		<a href="<?php echo esc_url( $this->get_customizer_url() ); ?>" class="button button-primary button-icon button-icon-round button-style-customizer" title="<?php esc_attr_e( 'Form Designer', 'user-registration-style-customizer' ); ?>">
			<span class="dashicons dashicons-admin-appearance"></span>
		</a>
		<?php
	}

	/**
	 * Render Login customize button.
	 *
	 * @param array $value Setting value.
	 */
	public function render_customize_login_button( $value ) {
		$field_description = UR_Admin_Settings::get_field_description( $value );
		?>
		<tr valign="top" class="<?php echo esc_attr( $value['row_class'] ); ?>">
			<th scope="row" class="titledesc">
				<label><?php echo esc_attr( $value['title'] ); ?></label>
				<?php echo $field_description['tooltip_html']; ?>
			</th>
			<td>
				<?php
				if ( isset( $value['buttons'] ) && is_array( $value['buttons'] ) ) {
					foreach ( $value['buttons'] as $button ) {
						?>
						<a
							href="<?php echo esc_url( $button['href'] ); ?>"
							class="button <?php echo esc_attr( $button['class'] ); ?>">
							<?php echo esc_html( $button['title'] ); ?>
						</a>
						<?php
					}
				}
				?>
				<?php echo ( isset( $value['desc'] ) && isset( $value['desc_tip'] ) && true !== $value['desc_tip'] ) ? '<p class="description" >' . esc_html( $value['desc'] ) . '</p>' : ''; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Setting option for login customizer button.
	 *
	 * @param array $settings Settings.
	 */
	public function login_option_customizer_button( $settings ) {
		$login_options_settings                                     = array_merge(
			$settings['sections']['login_options_settings']['settings'],
			array(
				array(
					'title'    => __( 'Customize Login Form', 'user-registration-style-customizer' ),
					'desc'     => __( 'Make the login form more elegant and unique. Customize the design styles for form wrapper, fields, texts, button and more.', 'user-registration-style-customizer' ),
					'desc_tip' => __( 'Customize the design style for login form.', 'user-registration-style-customizer' ),
					'type'     => 'link',
					'id'       => 'user_registration_style_customizer_customize_button',
					'buttons'  => array(
						array(
							'title' => __( 'Customize Login Form', 'user-registration-style-customizer' ),
							'href'  => $this->get_login_customizer_url(),
							'class' => 'button-customize-login',
						),
					),
				),
			)
		);
		$settings['sections']['login_options_settings']['settings'] = $login_options_settings;

		return $settings;
	}
	/**
	 * User Registration fallback notice .
	 */
	public function user_registration_missing_notice() {
		/* translators: %s: user-registration plugin link */
		echo '<div class="error notice is-dismissible"><p>' . sprintf( esc_html__( 'User Registration Style Customizer requires %s version 4.0.0 or greater to work', 'user-registration-style-customizer' ), '<a href="https://wpuserregistration.com/" target="_blank">' . esc_html__( 'User Registration Pro', 'user-registration-style-customizer' ) . '</a>' ) . '</p></div>';
	}



	/**
	 * Deprecates old plugin missing notice.
	 *
	 * @deprecated 1.0.2
	 *
	 * @return void
	 */
	public function user_registation_missing_notice() {
		ur_deprecated_function( 'User_Registration_Style_Customizer::user_registation_missing_notice', '1.0.2', 'User_Registration_Style_Customizer::user_registration_missing_notice' );
	}

	/**
	 * Copy and save the saved styles of a form for duplicated form.
	 *
	 * @param int    $current_form_id ID of the current form that has been duplicated.
	 * @param [type] $duplicated_form_id ID of duplicated form.
	 */
	public function user_registration_duplicate_form_styles( $current_form_id, $duplicated_form_id ) {

		if ( file_exists( wp_upload_dir()['basedir'] . '/user_registration_styles/user-registration-' . absint( $current_form_id ) . '.css' ) ) {
			copy( wp_upload_dir()['basedir'] . '/user_registration_styles/user-registration-' . absint( $current_form_id ) . '.css', wp_upload_dir()['basedir'] . '/user_registration_styles/user-registration-' . absint( $duplicated_form_id ) . '.css' );

			$file_path = wp_upload_dir()['basedir'] . '/user_registration_styles/user-registration-' . absint( $duplicated_form_id ) . '.css';

			$current_form_selector    = 'user-registration-form-' . absint( $current_form_id );
			$duplicated_form_selector = 'user-registration-form-' . absint( $duplicated_form_id );

			$file_contents = file_get_contents( $file_path );

			if ( strpos( $file_contents, $current_form_selector ) !== false ) {
				while ( strpos( $file_contents, $current_form_selector ) !== false ) {
					$file_contents = str_replace( $current_form_selector, $duplicated_form_selector, $file_contents );
				}

				file_put_contents( $file_path, $file_contents );
			}
		}
	}
}
