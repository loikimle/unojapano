<?php
/**
 * User Registration Content Restriction Meta Boxes
 *
 * Sets up the write panels used by custom post types.
 *
 * @class    URCR_Admin_Meta_Boxes
 * @version  1.0.0
 * @package  UserRegistrationContentRestriction/Admin/Meta Boxes
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URCR_Admin_Meta_Boxes Class
 */
if ( ! defined( 'UR_PLUGIN_FILE' ) ) {
	return;
}
if ( ! class_exists( 'UR_Meta_Boxes' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-meta-boxes.php';
}

class URCR_Admin_Meta_Box extends UR_Meta_Boxes {

    /**
     * Constructor.
     */

    public function __construct() {
        $this->id = 'content_restriction';

        if ( get_option( 'user_registration_content_restriction_enable' ) == "no" ) {
            return;
        }

        if ( is_admin() ) {
            add_action( 'load-post.php', array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
        }

    }

    public function register_scripts() {
		// enqueue scripts here.
		wp_enqueue_script( 'selectWoo' );
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        wp_register_script( 'custom-js', URCR()->plugin_url() . '/assets/custom' . $suffix . '.js', array( 'jquery' ), '3.5.4' );
		wp_enqueue_script( 'custom-js' );
    }

    public function register_styles() {
			// enqueue styles here.
			wp_enqueue_style( 'select2' );
    }

    /**
     * Meta box initialization.
     */
    public function init_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
    }

    /**
     * Adds the meta box.
     */
    public function add_metabox() {
        add_meta_box(
            'urcr-meta-box',
            __( 'Restrict This Content', 'user-registration-content-restriction' ),
            array( $this, 'render_metabox' ),
            $screen = null,
            'advanced',
            'default'
        );
    }

    /**
     * Renders the meta box.
     */
    public function render_metabox( $post ) {

        echo "<p>" . esc_html__( 'Use shortcode [urcr_restrict]....[/urcr_restrict] to restrict partial contents.', 'user-registration-content-restriction' ) . "</p>";
        $this->ur_metabox_checkbox(
            array(
				'id'    => 'urcr_meta_checkbox',
				'label' => 'Restrict Full Content: ',
				'type'  => 'Checkbox',
			)
        );

        $this->ur_metabox_checkbox(
			array(
				'id'    => 'urcr_meta_override_global_settings',
				'label' => 'Override Global Settings ',
				'type'  => 'Checkbox',
			)
        );

        $this->ur_metabox_select(
			array(
				'id'      => 'urcr_allow_to',
				'label'   => __( 'Allow Access To: ', 'user-registration-content-restriction' ),
				'options' => array( 'All Logged In Users', 'Choose Specific Roles', 'Guest Users' ),
				'desc'    => __( 'Only select this if you want to override global setting for allow option', 'user-registration-content-restriction' ),
				'class'   => 'ur-enhanced-select',
			)
        );

        $this->ur_metabox_multiple_select(
			array(
				'id'      => 'urcr_meta_roles[]',
				'label'   => __( 'Allow Access To Roles: ', 'user-registration-content-restriction' ),
				'options' => urcr_get_all_roles(),
				'desc'    => __( 'Only select this if you want to override global setting for access roles', 'user-registration-content-restriction' ),
				'class'   => 'ur-enhanced-select',
			)
        );

        do_action( 'render_metabox_complete' );
    }

    /**
     * Handles saving the meta box.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @return null
     */
    public function save_metabox( $post_id, $post ) {

        if ( empty( $_POST ) ) {
            return false;
        }

        $checkbox = isset( $_POST['urcr_meta_checkbox'] ) ? $_POST['urcr_meta_checkbox'] : '';

        $override_global_settings = isset( $_POST['urcr_meta_override_global_settings'] ) ? $_POST['urcr_meta_override_global_settings'] : '';

        $allow_to = isset( $_POST['urcr_allow_to'] ) ? $_POST['urcr_allow_to'] : '';

        $array_of_roles = isset( $_POST['urcr_meta_roles'] ) ? $_POST['urcr_meta_roles'] : '';

        update_post_meta( $post_id, 'urcr_meta_checkbox', $checkbox );

        update_post_meta( $post_id, 'urcr_meta_override_global_settings', $override_global_settings );

        update_post_meta( $post_id, 'urcr_allow_to', $allow_to );

        update_post_meta( $post_id, 'urcr_meta_roles', $array_of_roles );

        // Add nonce for security and authentication.
        $nonce_name   = isset( $_POST['custom_nonce'] ) ? $_POST['custom_nonce'] : '';
        $nonce_action = 'custom_nonce_action';

        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }

        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
    }
}

new URCR_Admin_Meta_Box();
