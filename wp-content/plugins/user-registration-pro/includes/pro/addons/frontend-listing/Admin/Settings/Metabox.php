<?php
/**
 * URFrontendListing Admin Settings Page Metabox
 *
 * Handles the rendering and saving of frontend listing settings
 * within a custom post type metabox interface.
 *
 * @package    URFrontendListing
 * @subpackage Admin/Settings
 * @since      x.x.x
 */

namespace WPEverest\URFrontendListing\Admin\Settings;

use WPEverest\URMembership\Admin\Services\MembershipService;
defined( 'ABSPATH' ) || exit;

/**
 * Settings metabox for Frontend Listings.
 *
 * Manages the settings metabox for the frontend listings post type, including:
 * - Rendering the tabbed settings UI.
 * - Outputting form fields.
 * - Handling AJAX saves and dynamic field option fetching.
 *
 * @since x.x.x
 */
class Metabox extends \UR_Meta_Boxes {

	/**
	 * Post ID.
	 *
	 * @since x.x.x
	 * @var int
	 */
	private $post_id = 0;

	/**
	 * Post object.
	 *
	 * @since x.x.x
	 * @var \WP_Post|null
	 */
	private $post = null;

	private $membership_service;

	/**
	 * Constructor.
	 *
	 * Registers hooks for metabox setup, script enqueuing, and AJAX handling.
	 *
	 * @since x.x.x
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'remove_default_metaboxes' ), 99 );
		add_action( 'add_meta_boxes', array( $this, 'add_settings_metabox' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_urfl_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_urfl_get_card_field_options', array( $this, 'ajax_get_card_field_options' ) );
		add_action( 'wp_ajax_urfl_save_title', array( $this, 'ajax_save_title' ) );
		if ( class_exists( '\WPEverest\URMembership\Admin\Services\MembershipService' ) ) {
			$this->membership_service = new MembershipService();
		} else {
			$this->membership_service = null;
		}
	}

	/**
	 * Remove default metaboxes.
	 *
	 * Removes the legacy metaboxes that are replaced by the new settings interface.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function remove_default_metaboxes() {
		remove_meta_box( 'user_registration_frontend_listing_general', 'ur_frontend_listings', 'normal' );
		remove_meta_box( 'user_registration_frontend_listing_filter', 'ur_frontend_listings', 'normal' );
		remove_meta_box( 'user_registration_frontend_listing_search', 'ur_frontend_listings', 'normal' );
		remove_meta_box( 'user_registration_frontend_listing_pagination', 'ur_frontend_listings', 'normal' );
		remove_meta_box( 'user_registration_frontend_listing_shortcode', 'ur_frontend_listings', 'side' );
		remove_meta_box( 'submitdiv', 'ur_frontend_listings', 'side' );
	}

	/**
	 * Add settings metabox.
	 *
	 * Registers the main settings metabox for the frontend listings post type.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function add_settings_metabox() {
		add_meta_box(
			'urfl_settings_page',
			__( 'Frontend Listing Settings', 'user-registration-frontend-listing' ),
			array( $this, 'render_settings_page' ),
			'ur_frontend_listings',
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * Loads required CSS and JavaScript files for the settings page.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( empty( $screen ) || 'ur_frontend_listings' !== $screen->id ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if ( ! wp_style_is( 'ur-snackbar', 'registered' ) ) {
			wp_register_style(
				'ur-snackbar',
				UR()->plugin_url() . '/assets/css/ur-snackbar/ur-snackbar.css',
				array(),
				'1.0.0'
			);
		}

		wp_register_script( 'ur-snackbar', UR()->plugin_url() . '/assets/js/ur-snackbar/ur-snackbar' . $suffix . '.js', array(), '1.0.0', true );
		wp_enqueue_script( 'ur-snackbar' );

		wp_enqueue_style( 'ur-snackbar' );

		wp_enqueue_style( 'selectWoo' );
		wp_enqueue_script( 'selectWoo' );

		wp_enqueue_script(
			'user-registration-frontend-listing-admin',
			UR()->plugin_url() . '/assets/js/pro/admin/user-registration-frontend-listing-admin' . $suffix . '.js',
			array( 'jquery', 'selectWoo', 'jquery-ui-sortable' ),
			UR_VERSION,
			true
		);

		wp_enqueue_style(
			'ur-member-directory-css',
			UR()->plugin_url() . '/assets/css/user-registration-member-directory-admin.css',
			array(),
			UR_VERSION
		);

		wp_localize_script(
			'user-registration-frontend-listing-admin',
			'urfl_settings',
			array(
				'ur_frontend_listing_advanced_filter_options' => ur_frontend_listing_advanced_filter(),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'urfl_save_settings' ),
				'list_url' => admin_url( 'edit.php?post_type=ur_frontend_listings' ),
				'i18n'     => array(
					'saving'       => __( 'Saving...', 'user-registration-frontend-listing' ),
					'saved'        => __( 'Settings Saved!', 'user-registration-frontend-listing' ),
					'save_error'   => __( 'Error saving settings', 'user-registration-frontend-listing' ),
					'save_changes' => __( 'Save Changes', 'user-registration-frontend-listing' ),
				),
			)
		);
	}


	/**
	 * Render settings page.
	 *
	 * Outputs the complete settings interface including tabs and form fields.
	 *
	 * @since x.x.x
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render_settings_page( $post ) {
		$this->post_id = $post->ID;
		$this->post    = $post;

		$view_profile_enabled = $this->get_meta( 'user_registration_frontend_listings_view_profile', '0' );
		$profile_hidden       = ( '1' !== $view_profile_enabled );
		$back_url             = admin_url( 'admin.php?page=user-registration-frontend-list' );

		wp_nonce_field( 'urfl_settings_nonce', 'urfl_nonce' );
		?>
		<div class="ur-admin-page-topnav" id="urfl-page-topnav">
			<div class="ur-page-title__wrapper">
				<div class="ur-page-title__wrapper--left">
					<a class="ur-text-muted ur-border-right ur-d-flex ur-mr-2 ur-pl-2 ur-pr-2" href="<?php echo esc_url( $back_url ); ?>" title="<?php esc_attr_e( 'Back to member directory listing', 'user-registration-frontend-listing' ); ?>">
						<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
							<line x1="19" y1="12" x2="5" y2="12"></line>
							<polyline points="12 19 5 12 12 5"></polyline>
						</svg>
					</a>

					<div class="ur-page-title__wrapper--left-menu">
						<div class="ur-page-title__wrapper--left-menu__items ur-page-title__wrapper--steps">
							<button class="ur-page-title__wrapper--steps-btn ur-page-title__wrapper--steps-btn-active urfl-tab-link" data-step="0" data-tab="general-settings" id="urfl-general-tab">
								<div class="ur-page-title__wrapper--steps-wrapper">
									<div class="urm-membership--stepper-icon">
										<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
											<path d="M20.182 12a1.284 1.284 0 0 0-.291-.814l-.085-.093-6.9-6.9a1.282 1.282 0 0 0-1.813 0l-6.899 6.9a1.287 1.287 0 0 0-.278 1.398c.064.155.158.296.277.415l6.9 6.9a1.287 1.287 0 0 0 .907.376 1.283 1.283 0 0 0 .906-.376l6.9-6.899a1.286 1.286 0 0 0 .376-.907ZM22 12a3.101 3.101 0 0 1-.804 2.082l-.105.11-6.899 6.9a3.1 3.1 0 0 1-4.385 0l-6.898-6.9a3.099 3.099 0 0 1 0-4.385l6.898-6.898a3.103 3.103 0 0 1 4.385 0l6.898 6.898A3.104 3.104 0 0 1 22 12Z"></path>
										</svg>
									</div>
									<span><?php esc_html_e( 'General', 'user-registration-frontend-listing' ); ?></span>
								</div>
							</button>

							<hr class="ur-page-title__wrapper--steps-separator" <?php echo $profile_hidden ? 'style="display:none;"' : ''; ?>>

							<button class="ur-page-title__wrapper--steps-btn urfl-tab-link" data-step="1" data-tab="profile-page-settings" id="urfl-profile-tab" <?php echo $profile_hidden ? 'style="display:none;"' : ''; ?>>
								<div class="ur-page-title__wrapper--steps-wrapper">
									<div class="urm-membership--stepper-icon">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
											<path fill="currentColor" d="M1 21a9 9 0 0 1 12.174-8.423 1 1 0 0 1-.705 1.871A7.002 7.002 0 0 0 3 21a1 1 0 0 1-2 0M21 15.124a1.124 1.124 0 0 0-1.837-.87l-.082.075-4.01 4.012a1 1 0 0 0-.253.427l-.582 1.994 1.995-.582a1 1 0 0 0 .426-.251l4.014-4.01.074-.082c.164-.2.255-.452.255-.713m2 0c0 .828-.33 1.623-.915 2.209l-4.014 4.01a3 3 0 0 1-1.28.757l-2.87.838a1.5 1.5 0 0 1-1.86-1.86l.838-2.87.058-.18a3 3 0 0 1 .7-1.101l4.01-4.012A3.124 3.124 0 0 1 23 15.124"/>
											<path fill="currentColor" d="M14 8a4 4 0 1 0-8 0 4 4 0 0 0 8 0m2 0A6 6 0 1 1 4 8a6 6 0 0 1 12 0"/>
										</svg>
									</div>
									<span><?php esc_html_e( 'Profile Page', 'user-registration-frontend-listing' ); ?></span>
								</div>
							</button>

							<hr class="ur-page-title__wrapper--steps-separator">

							<button class="ur-page-title__wrapper--steps-btn urfl-tab-link" data-step="2" data-tab="advanced-settings" id="urfl-advanced-tab">
								<div class="ur-page-title__wrapper--steps-wrapper">
									<div class="urm-membership--stepper-icon">
										<svg xmlns="http://www.w3.org/2000/svg" fill="#4e4e4e" viewBox="0 0 24 24">
											<path d="M14 16a1 1 0 1 1 0 2H5a1 1 0 1 1 0-2h9Zm5-10a1 1 0 1 1 0 2h-9a1 1 0 0 1 0-2h9Z"></path>
											<path d="M19 17a2 2 0 1 0-4 0 2 2 0 0 0 4 0Zm2 0a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM9 7a2 2 0 1 0-4 0 2 2 0 0 0 4 0Zm2 0a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"></path>
										</svg>
									</div>
									<span><?php esc_html_e( 'Advanced', 'user-registration-frontend-listing' ); ?></span>
								</div>
							</button>
						</div>
					</div>
				</div>
				<div class="ur-page-title__wrapper--right">
						<div class="ur-page-title__wrapper--right-menu">
						<div class="ur-page-title__wrapper--right-menu__item">
							<div class="ur-page-title__wrapper--actions">
							<div class="ur-page-title__wrapper--actions-publish">
								<button class="button button-primary urfl-save-btn" id="urfl-save-settings"> <?php esc_html_e( 'Save', 'user-registration-frontend-listing' ); ?> </button>
							</div>
							</div>
						</div>
						</div>
					</div>
			</div>
		</div>

		<?php $this->render_editable_title( $post ); ?>

		<div class="urfl-settings-wrapper">
			<div class="urfl-tabs-content">
				<div id="general-settings" class="urfl-tab-content active">
					<?php $this->render_general_settings(); ?>
				</div>

				<div id="profile-page-settings" class="urfl-tab-content <?php echo $profile_hidden ? 'urfl-profile-hidden' : ''; ?>">
					<?php $this->render_profile_page_settings(); ?>
				</div>

				<div id="advanced-settings" class="urfl-tab-content">
					<?php $this->render_advanced_settings(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render editable title section.
	 *
	 * Outputs an editable title field that updates the post title.
	 *
	 * @since x.x.x
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	private function render_editable_title( $post ) {
		$post_title = $post->post_title;

		if ( empty( $post_title ) || 'Auto Draft' === $post_title ) {
			$post_title = __( 'Members Directory', 'user-registration-frontend-listing' );
		}
		?>
		<div class="user-registration-editable-title ur-form-name-wrapper ur-my-4">
			<input
				name="member-directory-name"
				id="member-directory-name"
				type="text"
				class="member-directory-name-editable-title__input member-directory-name-name regular-text menu-item-textbox"
				value="<?php echo esc_attr( $post_title ); ?>"
				data-editing="false"
				readonly
			>
			<span
				id="member-directory-name-edit-button"
				class="user-registration-editable-title__icon ur-edit-form-name dashicons dashicons-edit"
				title="<?php esc_attr_e( 'Edit title', 'user-registration-frontend-listing' ); ?>"
			></span>
		</div>
		<?php
	}

	/**
	 * Render general settings section.
	 *
	 * Outputs the general settings including display, layout, and access settings.
	 *
	 * @since x.x.x
	 * @return void
	 */
	private function render_general_settings() {
		?>
		<div class="urfl-section" data-section="general">
			<div class="urfl-section-header">
				<h3><?php esc_html_e( 'General', 'user-registration-frontend-listing' ); ?></h3>
			</div>

			<div class="urfl-section-content">
				<?php
				$this->render_select_field(
					array(
						'id'      => 'user_registration_frontend_listings_ur_only',
						'label'   => __( 'Show users based on', 'user-registration-frontend-listing' ),
						'options' => array(
							'0' => __( 'Memberships', 'user-registration-frontend-listing' ),
							'1' => __( 'Forms', 'user-registration-frontend-listing' ),
							'2' => __( 'All Users', 'user-registration-frontend-listing' ),
						),
						'tooltip' => __( 'Choose whether to display all users or only users registered through specific forms.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_multiselect_field(
					array(
						'id'      => 'user_registration_frontend_listings_ur_forms',
						'label'   => __( 'Choose forms to display registered users', 'user-registration-frontend-listing' ),
						'options' => ur_get_all_user_registration_form(),
						'tooltip' => __( 'Select the registration forms whose users you want to display.', 'user-registration-frontend-listing' ),
						'class'   => 'urfl-forms-select',
					)
				);

				$this->render_multiselect_field(
					array(
						'id'      => 'user_registration_member_directory_ur_membership_type',
						'label'   => __( 'Choose memberships', 'user-registration-frontend-listing' ),
						'options' => $this->get_membership_options(),
						'tooltip' => __( 'Select the membership type.', 'user-registration-frontend-listing' ),
						'class'   => 'urfl-forms-select',
					)
				);

				$this->render_toggle_field(
					array(
						'id'      => 'user_registration_frontend_listings_view_profile',
						'label'   => __( 'Enable view profile', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Enable this to show a view profile button for each user.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_text_field(
					array(
						'id'      => 'user_registration_frontend_listings_view_profile_button_text',
						'label'   => __( 'View profile button text', 'user-registration-frontend-listing' ),
						'default' => __( 'View Profile', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Text to display on the view profile button.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_layout_selector(
					array(
						'id'      => 'user_registration_frontend_listings_layout',
						'label'   => __( 'Listings Layout', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Choose how the user listings will be displayed.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_card_fields_settings(
					array(
						'selected_id'          => 'user_registration_frontend_listings_lists_fields',
						'order_id'             => 'user_registration_frontend_listings_lists_fields_order',
						'selected_label'       => __( 'Select info to show in cards', 'user-registration-frontend-listing' ),
						'selected_placeholder' => __( 'Select fields...', 'user-registration-frontend-listing' ),
						'order_label'          => __( 'Order of fields in cards', 'user-registration-frontend-listing' ),
						'tooltip'              => __( 'Choose fields and order for grid cards.', 'user-registration-frontend-listing' ),
						'empty_text'           => __( 'Select fields above to add them here.', 'user-registration-frontend-listing' ),
						'options'              => urfl_card_fields_grouped_options_by_selection( $this->post_id ),
						'default_selected'     => array(),
					)
				);

				$this->render_toggle_field(
					array(
						'id'      => 'user_registration_frontend_listings_display_profile_picture',
						'label'   => __( 'Enable Profile Picture', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Show profile pictures in the listing cards.', 'user-registration-frontend-listing' ),
					)
				);

				?>
			</div>
		<?php
		$is_new_installation = ur_string_to_bool( get_option( 'urm_is_new_installation', '' ) );

		if ( ! $is_new_installation ) :
			?>
		<div class="urfl-section-header" data-section="access">
			<h3><?php esc_html_e( 'Access', 'user-registration-frontend-listing' ); ?></h3>
		</div>

		<div class="urfl-section-content">
					<?php
					$this->render_toggle_field(
						array(
							'id'      => 'user_registration_frontend_listings_allow_guest',
							'label'   => __( 'Allow Access To Guest', 'user-registration-frontend-listing' ),
							'tooltip' => __( 'Allow guest users to view the listing page.', 'user-registration-frontend-listing' ),
						)
					);

					$this->render_text_field(
						array(
							'id'      => 'user_registration_frontend_listings_access_denied_text',
							'label'   => __( 'Access denied text', 'user-registration-frontend-listing' ),
							'default' => __( 'Please login to view this page.', 'user-registration-frontend-listing' ),
							'tooltip' => __( 'Message shown to users who do not have access.', 'user-registration-frontend-listing' ),
						)
					);
					?>
		</div>

		<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Render profile page settings section.
	 *
	 * Outputs the profile display and filter settings fields.
	 *
	 * @since x.x.x
	 * @return void
	 */
	private function render_profile_page_settings() {
		?>
		<div class="urfl-section" data-section="profile-display">
			<div class="urfl-section-header">
				<h3><?php esc_html_e( 'Profile Display', 'user-registration-frontend-listing' ); ?></h3>
			</div>

			<div class="urfl-section-content">
				<?php
				$this->render_toggle_field(
					array(
						'id'      => 'user_registration_frontend_listings_view_profile_display_profile_picture',
						'label'   => __( 'Enable Profile Picture (View Profile page)', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Show profile picture on the View Profile page.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_ordered_grouped_multiselect_field(
					array(
						'id'         => 'user_registration_frontend_listings_card_fields',
						'order_id'   => 'user_registration_frontend_listings_card_fields_order',
						'label'      => __( 'Details to display in View Profile page', 'user-registration-frontend-listing' ),
						'options'    => ur_frontend_listing_include_fields_in_view_profile(),
						'tooltip'    => __( 'Select fields and reorder them for the View Profile page.', 'user-registration-frontend-listing' ),
						'empty_text' => __( 'Select fields above to add them here.', 'user-registration-frontend-listing' ),
					)
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render advanced settings section.
	 *
	 * Outputs the search and pagination settings fields.
	 *
	 * @since x.x.x
	 * @return void
	 */
	private function render_advanced_settings() {
		?>
		<div class="urfl-section" data-section="filter">
			<div class="urfl-section-header">
				<h3><?php esc_html_e( 'Filter', 'user-registration-frontend-listing' ); ?></h3>
			</div>

			<div class="urfl-section-content">
				<?php
				$this->render_toggle_field(
					array(
						'id'      => 'user_registration_frontend_listings_sort_by',
						'label'   => __( 'Display Sorter', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Allow users to sort the listing.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_select_field(
					array(
						'id'      => 'user_registration_frontend_listings_default_sorter',
						'label'   => __( 'Default user sorter', 'user-registration-frontend-listing' ),
						'options' => ur_frontend_listing_sort_filter(),
						'tooltip' => __( 'Set the default sorting order for listings.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_multiselect_field(
					array(
						'id'      => 'user_registration_frontend_listings_role_restriction',
						'label'   => __( 'Restrict By Role', 'user-registration-frontend-listing' ),
						'options' => ur_get_default_admin_roles(),
						'tooltip' => __( 'Select roles to exclude from the listing.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_multiselect_field(
					array(
						'id'      => 'user_registration_frontend_listings_filter_by_user_status',
						'label'   => __( 'Select User Status', 'user-registration-frontend-listing' ),
						'options' => ur_get_all_user_status(),
						'tooltip' => __( 'Display only users with selected approval status.', 'user-registration-frontend-listing' ),
					)
				);
				?>
			</div>
			<div class="urfl-section-header" data-section="search">
				<h3><?php esc_html_e( 'Search', 'user-registration-frontend-listing' ); ?></h3>
			</div>

			<div class="urfl-section-content">
				<?php
				$this->render_toggle_field(
					array(
						'id'      => 'user_registration_frontend_listings_search_form',
						'label'   => __( 'Display Search Form', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Show a search form for users to search the listing.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_multiselect_field(
					array(
						'id'      => 'user_registration_frontend_listings_search_fields',
						'label'   => __( 'Search User According To', 'user-registration-frontend-listing' ),
						'options' => ur_frontend_listing_user_search_fields(),
						'tooltip' => __( 'Select fields that can be searched.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_toggle_field(
					array(
						'id'      => 'user_registration_frontend_listings_advanced_filter',
						'label'   => __( 'Display Advanced Filter', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Enable advanced filtering options.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_advanced_filter_field(
					array(
						'id'      => 'user_registration_frontend_listings_advanced_filter_fields',
						'label'   => __( 'Advanced Filter Fields', 'user-registration-frontend-listing' ),
						'options' => ur_frontend_listing_advanced_filter(),
						'tooltip' => __( 'Select fields for advanced filtering.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_text_field(
					array(
						'id'      => 'user_registration_frontend_listing_search_placeholder_text',
						'label'   => __( 'Search box placeholder text', 'user-registration-frontend-listing' ),
						'default' => __( 'Enter something to search.', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Placeholder text for the search input.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_text_field(
					array(
						'id'      => 'user_registration_frontend_listings_search_button_text',
						'label'   => __( 'Search button text', 'user-registration-frontend-listing' ),
						'default' => __( 'Search', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Text for the search button.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_text_field(
					array(
						'id'      => 'user_registration_frontend_listings_no_users_found_text',
						'label'   => __( 'No users found text', 'user-registration-frontend-listing' ),
						'default' => __( 'Sorry, No users are found.', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Message shown when no users match the search.', 'user-registration-frontend-listing' ),
					)
				);
				?>
			</div>
			<div class="urfl-section-header" data-section="pagination">
				<h3><?php esc_html_e( 'Pagination', 'user-registration-frontend-listing' ); ?></h3>
			</div>

			<div class="urfl-section-content">
				<?php
				$this->render_toggle_field(
					array(
						'id'      => 'user_registration_frontend_listings_amount_filter',
						'label'   => __( 'Display Amount Filter', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Allow users to change the number of profiles per page.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_select_field(
					array(
						'id'      => 'user_registration_frontend_listings_default_page_filter',
						'label'   => __( 'Default number of profiles per page', 'user-registration-frontend-listing' ),
						'options' => ur_frontend_listing_amount_filter(),
						'tooltip' => __( 'Set how many profiles to show per page by default.', 'user-registration-frontend-listing' ),
					)
				);

				$this->render_text_field(
					array(
						'id'      => 'user_registration_frontend_listings_filtered_user_message',
						'label'   => __( 'Filtered users message', 'user-registration-frontend-listing' ),
						'default' => __( 'Showing %qty% out of %total% users.', 'user-registration-frontend-listing' ),
						'tooltip' => __( 'Message showing the count of displayed users. Use %qty% and %total% placeholders.', 'user-registration-frontend-listing' ),
					)
				);
				?>
			</div>






		<?php
	}

	private function urfl_truthy_to_one( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}
		$v = strtolower( trim( (string) $value ) );
		return in_array( $v, array( '1', 'yes', 'on', 'true' ), true ) ? '1' : '0';
	}

	/**
	 * Get meta value.
	 *
	 * Retrieves a post meta value with optional default fallback.
	 *
	 * @since x.x.x
	 * @param string $key     Meta key.
	 * @param mixed  $default Default value if meta not found.
	 * @return mixed Meta value or default.
	 */
	private function get_meta( $key, $default = '' ) {
		$value = get_post_meta( $this->post_id, $key, true );

		if ( ( '' === $value || null === $value ) && 'user_registration_frontend_listings_view_profile_display_profile_picture' === $key ) {
			$value = get_post_meta( $this->post_id, 'user_registration_frontend_listings_display_profile_picture', true );
		}

		if ( 'user_registration_frontend_listings_layout' === $key ) {
			if ( 'Grid' === $value ) {
				$value = '0';
			} elseif ( 'Lists' === $value ) {
				$value = '1';
			}
		}

		$checkbox_keys = array(
			'user_registration_frontend_listings_allow_guest',
			'user_registration_frontend_listings_display_profile_picture',
			'user_registration_frontend_listings_sort_by',
			'user_registration_frontend_listings_view_profile',
			'user_registration_frontend_listings_search_form',
			'user_registration_frontend_listings_advanced_filter',
			'user_registration_frontend_listings_amount_filter',
			'user_registration_frontend_listings_view_profile_display_profile_picture',
		);

		if ( in_array( $key, $checkbox_keys, true ) ) {
			$value = $this->urfl_truthy_to_one( $value );
		}

		if ( 'user_registration_frontend_listings_ur_only' === $key ) {
			$v = strtolower( trim( (string) $value ) );
			if ( in_array( $v, array( 'yes', 'on', '1', 'true' ), true ) ) {
				$value = '1';
			} elseif ( '' === $v ) {
				$value = '2';
			}
		}

		$array_keys = array(
			'user_registration_frontend_listings_role_restriction',
			'user_registration_frontend_listings_filter_by_user_status',
			'user_registration_frontend_listings_search_fields',
			'user_registration_frontend_listings_ur_forms',
			'user_registration_member_directory_ur_membership_type',
			'user_registration_frontend_listings_card_fields',
			'user_registration_frontend_listings_lists_fields',
		);

		if ( in_array( $key, $array_keys, true ) && ! is_array( $value ) ) {
			$value = ( '' === $value || null === $value ) ? array() : (array) $value;
		}

		return ( '' !== $value && null !== $value ) ? $value : $default;
	}

	/**
	 * Render select field.
	 *
	 * Outputs a single-select dropdown field.
	 *
	 * @since x.x.x
	 * @param array $args Field arguments including id, label, options, tooltip.
	 * @return void
	 */
	private function render_select_field( $args ) {
		$value = $this->get_meta( $args['id'], isset( $args['default'] ) ? $args['default'] : '' );
		?>
		<div class="urfl-field urfl-field-select">
			<div class="urfl-field-label">
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
				<?php $this->render_tooltip( $args ); ?>
			</div>

			<div class="urfl-field-input">
				<select name="<?php echo esc_attr( $args['id'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" class="urfl-select">
					<?php foreach ( $args['options'] as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php
	}

	/**
	 * Render multiselect field.
	 *
	 * Outputs a multi-select dropdown field.
	 *
	 * @since x.x.x
	 * @param array $args Field arguments including id, label, options, tooltip, class.
	 * @return void
	 */
	private function render_multiselect_field( $args ) {
		$value = $this->get_meta( $args['id'], array() );

		if ( ! is_array( $value ) ) {
			$value = array();
		}

		$value_str = array_map( 'strval', $value );
		$class     = isset( $args['class'] ) ? $args['class'] : '';
		?>
		<div class="urfl-field urfl-field-multiselect <?php echo esc_attr( $class ); ?>">
			<div class="urfl-field-label">
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
				<?php $this->render_tooltip( $args ); ?>
			</div>

			<div class="urfl-field-input">
				<select
					name="<?php echo esc_attr( $args['id'] ); ?>[]"
					id="<?php echo esc_attr( $args['id'] ); ?>"
					class="urfl-multiselect"
					multiple
				>
					<?php foreach ( (array) $args['options'] as $key => $label ) : ?>
						<?php $key_str = (string) $key; ?>
						<option
							value="<?php echo esc_attr( $key_str ); ?>"
							<?php echo in_array( $key_str, $value_str, true ) ? 'selected' : ''; ?>
						>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php
	}

	/**
	 * Render grouped multiselect field.
	 *
	 * Outputs a multi-select dropdown with optgroup support.
	 *
	 * @since x.x.x
	 * @param array $args Field arguments including id, label, options (grouped), tooltip.
	 * @return void
	 */
	private function render_grouped_multiselect_field( $args ) {
		$value = $this->get_meta( $args['id'], array() );

		if ( ! is_array( $value ) ) {
			$value = array();
		}

		$value_str = array_map( 'strval', $value );
		?>
		<div class="urfl-field urfl-field-multiselect">
			<div class="urfl-field-label">
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
				<?php $this->render_tooltip( $args ); ?>
			</div>

			<div class="urfl-field-input">
				<select
					name="<?php echo esc_attr( $args['id'] ); ?>[]"
					id="<?php echo esc_attr( $args['id'] ); ?>"
					class="urfl-multiselect"
					multiple
				>
					<?php foreach ( (array) $args['options'] as $group ) : ?>
						<?php if ( isset( $group['field_list'] ) && is_array( $group['field_list'] ) ) : ?>
							<optgroup label="<?php echo esc_attr( $group['form_label'] ); ?>">
								<?php foreach ( $group['field_list'] as $field_key => $field_label ) : ?>
									<?php $field_key_str = (string) $field_key; ?>
									<option
										value="<?php echo esc_attr( $field_key_str ); ?>"
										<?php echo in_array( $field_key_str, $value_str, true ) ? 'selected' : ''; ?>
									>
										<?php echo esc_html( $field_label ); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php
	}

	/**
	 * Render ordered grouped multiselect field.
	 *
	 * Stores:
	 * - Selected values in $args['id'] as an array (backward compatible).
	 * - Ordering in $args['order_id'] as a JSON string.
	 *
	 * @since x.x.x
	 * @param array $args Field arguments.
	 * @return void
	 */
	private function render_ordered_grouped_multiselect_field( $args ) {
		$selected = $this->get_meta( $args['id'], array() );

		if ( ! is_array( $selected ) ) {
			$selected = array();
		}

		$selected = array_values( array_filter( array_map( 'strval', $selected ), 'strlen' ) );

		$order_raw = $this->get_meta( $args['order_id'], '' );
		$order     = ! empty( $order_raw ) ? json_decode( $order_raw, true ) : array();

		if ( ! is_array( $order ) || empty( $order ) ) {
			$order = $selected;
		}

		$flat = array();
		foreach ( (array) $args['options'] as $group ) {
			if ( isset( $group['field_list'] ) && is_array( $group['field_list'] ) ) {
				foreach ( $group['field_list'] as $k => $lbl ) {
					$flat[ (string) $k ] = (string) $lbl;
				}
			}
		}

		$selected_set = array_fill_keys( $selected, true );
		$order        = array_values(
			array_filter(
				array_map( 'strval', (array) $order ),
				function ( $k ) use ( $selected_set, $flat ) {
					return isset( $selected_set[ $k ] ) && isset( $flat[ $k ] );
				}
			)
		);

		if ( empty( $order ) && ! empty( $selected ) ) {
			$order = $selected;
		}

		$empty_text = isset( $args['empty_text'] ) ? $args['empty_text'] : __( 'Select fields above to add them here.', 'user-registration-frontend-listing' );
		?>
		<div class="urfl-field urfl-field-ordered-grouped-multiselect" data-urfl-ordered="1">
			<div class="urfl-field-label">
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
				<?php $this->render_tooltip( $args ); ?>
			</div>

			<div class="urfl-field-input">
				<select
					name="<?php echo esc_attr( $args['id'] ); ?>[]"
					id="<?php echo esc_attr( $args['id'] ); ?>"
					class="urfl-multiselect urfl-ordered-fields-select"
					multiple
					data-order-input="<?php echo esc_attr( $args['order_id'] ); ?>"
				>
					<?php foreach ( (array) $args['options'] as $group ) : ?>
						<?php if ( isset( $group['field_list'] ) && is_array( $group['field_list'] ) ) : ?>
							<optgroup label="<?php echo esc_attr( $group['form_label'] ); ?>">
								<?php foreach ( $group['field_list'] as $field_key => $field_label ) : ?>
									<?php $k = (string) $field_key; ?>
									<option value="<?php echo esc_attr( $k ); ?>" <?php echo in_array( $k, $selected, true ) ? 'selected' : ''; ?>>
										<?php echo esc_html( (string) $field_label ); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>

				<div class="urfl-ordered-fields-order" data-options='<?php echo wp_json_encode( $flat ); ?>'>
					<?php if ( ! empty( $order ) ) : ?>
						<?php foreach ( $order as $k ) : ?>
							<?php
							if ( ! isset( $flat[ $k ] ) ) {
								continue;
							}
							?>
							<div class="urfl-ordered-field-row" data-key="<?php echo esc_attr( $k ); ?>">
								<span class="urfl-drag-handle">
									<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
										<path d="M7 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222C7.985 14.222 7 13.227 7 12Zm0-7.778C7 2.995 7.985 2 9.2 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222A2.21 2.21 0 0 1 7 4.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 9.2 22C7.985 22 7 21.005 7 19.778ZM13.6 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222c-1.215 0-2.2-.995-2.2-2.222Zm0-7.778C13.6 2.995 14.585 2 15.8 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222 2.21 2.21 0 0 1-2.2-2.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 15.8 22c-1.215 0-2.2-.995-2.2-2.222Z"></path>
									</svg>
								</span>

								<span class="urfl-ordered-field-label"><?php echo esc_html( $flat[ $k ] ); ?></span>

								<button type="button" class="urfl-remove-ordered-field" aria-label="<?php esc_attr_e( 'Remove', 'user-registration-frontend-listing' ); ?>">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<line x1="18" y1="6" x2="6" y2="18"></line>
										<line x1="6" y1="6" x2="18" y2="18"></line>
									</svg>
								</button>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<p class="urfl-order-empty-message"><?php echo esc_html( $empty_text ); ?></p>
					<?php endif; ?>
				</div>

				<input
					type="hidden"
					name="<?php echo esc_attr( $args['order_id'] ); ?>"
					id="<?php echo esc_attr( $args['order_id'] ); ?>"
					class="urfl-ordered-fields-order-value"
					value="<?php echo esc_attr( $order_raw ); ?>"
				/>
			</div>
		</div>
		<?php
	}

	/**
	 * Render text field.
	 *
	 * Outputs a single-line text input field.
	 *
	 * @since x.x.x
	 * @param array $args Field arguments including id, label, default, tooltip.
	 * @return void
	 */
	private function render_text_field( $args ) {
		$value = $this->get_meta( $args['id'], isset( $args['default'] ) ? $args['default'] : '' );
		?>
		<div class="urfl-field urfl-field-text">
			<div class="urfl-field-label">
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
				<?php $this->render_tooltip( $args ); ?>
			</div>

			<div class="urfl-field-input">
				<input
					type="text"
					name="<?php echo esc_attr( $args['id'] ); ?>"
					id="<?php echo esc_attr( $args['id'] ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					class="urfl-text-input"
				/>
			</div>
		</div>
		<?php
	}

	/**
	 * Render toggle field.
	 *
	 * Outputs a toggle switch checkbox field.
	 *
	 * @since x.x.x
	 * @param array $args Field arguments including id, label, tooltip.
	 * @return void
	 */
	private function render_toggle_field( $args ) {
		$value = $this->get_meta( $args['id'], '0' );
		?>
		<div class="urfl-field urfl-field-toggle">
			<div class="urfl-field-label">
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
				<?php $this->render_tooltip( $args ); ?>
			</div>

			<div class="urfl-field-input">
				<label class="urfl-toggle">
					<input
						type="checkbox"
						name="<?php echo esc_attr( $args['id'] ); ?>"
						id="<?php echo esc_attr( $args['id'] ); ?>"
						value="1"
						<?php checked( $value, '1' ); ?>
					/>
					<span class="urfl-toggle-slider"></span>
				</label>
			</div>
		</div>
		<?php
	}

	/**
	 * Render layout selector field.
	 *
	 * Outputs a visual layout selection component.
	 *
	 * @since x.x.x
	 * @param array $args Field arguments including id, label, tooltip.
	 * @return void
	 */
	private function render_layout_selector( $args ) {
		$value = $this->get_meta( $args['id'], '0' );
		?>
		<div class="urfl-field urfl-field-layout">
			<div class="urfl-field-label">
				<label><?php echo esc_html( $args['label'] ); ?></label>
				<?php $this->render_tooltip( $args ); ?>
			</div>

			<div class="urfl-field-input">
				<div class="urfl-layout-options">
					<label class="urfl-layout-option <?php echo '1' === $value ? 'active' : ''; ?>">
						<input type="radio" name="<?php echo esc_attr( $args['id'] ); ?>" value="1" <?php checked( $value, '1' ); ?> />
						<div class="urfl-layout-icon">
						<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 130 67"><path d="M39.842 17.706a1 1 0 0 1 1-1h48a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-48a1 1 0 0 1-1-1zM39.842 30.706a1 1 0 0 1 1-1h48a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-48a1 1 0 0 1-1-1zM39.842 43.706a1 1 0 0 1 1-1h48a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-48a1 1 0 0 1-1-1z"/></svg>
						</div>
						<span class="urfl-layout-label"><?php esc_html_e( 'List', 'user-registration-frontend-listing' ); ?></span>
					</label>

					<label class="urfl-layout-option <?php echo ( '0' === $value || '' === $value ) ? 'active' : ''; ?>">
						<input type="radio" name="<?php echo esc_attr( $args['id'] ); ?>" value="0" <?php checked( $value, '0' ); ?> />
						<div class="urfl-layout-icon">
							<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 130 67"><path d="M44.342 16.706a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-14a2 2 0 0 1-2-2zM67.342 16.706a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-14a2 2 0 0 1-2-2zM44.342 37.706a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-14a2 2 0 0 1-2-2zM67.342 37.706a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-14a2 2 0 0 1-2-2z"/></svg>
						</div>
						<span class="urfl-layout-label"><?php esc_html_e( 'Grid', 'user-registration-frontend-listing' ); ?></span>
					</label>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render advanced filter field.
	 *
	 * Outputs a sortable filter field selector.
	 *
	 * @since x.x.x
	 * @param array $args Field arguments including id, label, options, tooltip.
	 * @return void
	 */
	private function render_advanced_filter_field( $args ) {
		$args = wp_parse_args(
			(array) $args,
			array(
				'id'         => '',
				'label'      => '',
				'options'    => array(),
				'tooltip'    => '',
				'empty_text' => __( 'Select fields above to add them here.', 'user-registration-frontend-listing' ),
			)
		);

		if ( empty( $args['id'] ) ) {
			return;
		}

		$value         = $this->get_meta( $args['id'], '' );
		$decoded_value = array();

		if ( is_string( $value ) && '' !== $value ) {
			$tmp = json_decode( $value, true );
			if ( is_array( $tmp ) ) {
				$decoded_value = $tmp;
			}
		}

		$known_keys          = array_keys( $args['options'] );
		$selected_known_keys = array();

		foreach ( $decoded_value as $key => $label ) {
			if ( in_array( $key, $known_keys, true ) ) {
				$selected_known_keys[] = $key;
			}
		}

		$drag_handle_svg = '<svg width="12" height="20" viewBox="0 0 12 20" fill="none" xmlns="http://www.w3.org/2000/svg">'
			. '<rect width="4" height="4" fill="#9ca3af"></rect>'
			. '<rect x="8" width="4" height="4" fill="#9ca3af"></rect>'
			. '<rect y="8" width="4" height="4" fill="#9ca3af"></rect>'
			. '<rect x="8" y="8" width="4" height="4" fill="#9ca3af"></rect>'
			. '<rect y="16" width="4" height="4" fill="#9ca3af"></rect>'
			. '<rect x="8" y="16" width="4" height="4" fill="#9ca3af"></rect>'
			. '</svg>';

		$remove_svg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
			. '<line x1="18" y1="6" x2="6" y2="18"></line>'
			. '<line x1="6" y1="6" x2="18" y2="18"></line>'
			. '</svg>';
		?>
		<div class="urfl-field urfl-field-advanced-filter">
			<div class="urfl-field-label">
				<label for="<?php echo esc_attr( $args['id'] ); ?>_select">
					<?php echo esc_html( $args['label'] ); ?>
				</label>
				<?php $this->render_tooltip( $args ); ?>
			</div>

			<div class="urfl-field-input">
				<select
					class="urfl-multiselect urfl-advanced-filter-select"
					id="<?php echo esc_attr( $args['id'] ); ?>_select"
					name="<?php echo esc_attr( $args['id'] ); ?>_select[]"
					multiple="multiple"
					data-placeholder="<?php esc_attr_e( 'Select fields...', 'user-registration-frontend-listing' ); ?>"
					data-options='<?php echo wp_json_encode( $args['options'] ); ?>'
				>
					<?php foreach ( $args['options'] as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php echo in_array( $key, $selected_known_keys, true ) ? 'selected' : ''; ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<div class="urfl-ordered-fields-order urfl-advanced-filter-order" data-options='<?php echo wp_json_encode( $args['options'] ); ?>'>
					<?php if ( ! empty( $decoded_value ) ) : ?>
						<?php foreach ( $decoded_value as $key => $label ) : ?>
							<?php if ( in_array( $key, $known_keys, true ) ) : ?>
								<div class="urfl-advanced-filter-item" data-type="option" data-key="<?php echo esc_attr( $key ); ?>">
									<span class="urfl-drag-handle">
										<?php echo $drag_handle_svg; // phpcs:ignore ?>
									</span>
									<span class="urfl-filter-label"><?php echo esc_html( $label ); ?></span>
									<button type="button" class="urfl-remove-filter" aria-label="<?php esc_attr_e( 'Remove', 'user-registration-frontend-listing' ); ?>">
										<?php echo $remove_svg; // phpcs:ignore ?>
									</button>
								</div>
							<?php else : ?>
								<div class="urfl-advanced-filter-item" data-type="custom" data-key="">
									<span class="urfl-drag-handle">
										<?php echo $drag_handle_svg; // phpcs:ignore ?>
									</span>
									<div class="urfl-custom-field">
										<input
											type="text"
											class="urfl-custom-label"
											placeholder="<?php esc_attr_e( 'Field Label', 'user-registration-frontend-listing' ); ?>"
											value="<?php echo esc_attr( $label ); ?>"
										/>
										<input
											type="text"
											class="urfl-custom-key"
											placeholder="<?php esc_attr_e( 'Meta Key ( eg. meta_key_123, meta_key_456 )', 'user-registration-frontend-listing' ); ?>"
											value="<?php echo esc_attr( $key ); ?>"
										/>
									</div>
									<button type="button" class="urfl-remove-filter" aria-label="<?php esc_attr_e( 'Remove', 'user-registration-frontend-listing' ); ?>">
										<?php echo $remove_svg; // phpcs:ignore ?>
									</button>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php else : ?>
						<p class="urfl-order-empty-message"><?php echo esc_html( $args['empty_text'] ); ?></p>
					<?php endif; ?>
				</div>

				<input
					type="hidden"
					name="<?php echo esc_attr( $args['id'] ); ?>"
					id="<?php echo esc_attr( $args['id'] ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					class="urfl-advanced-filter-value"
				/>
			</div>
		</div>
		<?php
	}



	/**
	 * Renders the Add field in metabox.
	 *
	 * @since 1.1.0
	 * @param array $field Metabox Field.
	 */
	public function ur_metabox_custom_field( $field ) {

		global $thepostid, $post;

		$get_meta_data = get_post_meta( $post->ID, $field['id'], true );

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'urfl-input';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = ( isset( $get_meta_data ) && '' !== $get_meta_data ) ? $get_meta_data : $field['value'];
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['desc']          = isset( $field['desc'] ) ? $field['desc'] : '';

		echo '<div class="urfl-field ur-metabox-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';
		echo '<div class="urfl-field-label ur-metabox-field-label">';
		echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
		echo ur_help_tip( $field['desc'] );
		echo '</div>';
		echo '<div class=" urfl-field-input ur-metabox-field-detail">';

		// Create a select field wrapper.
		$field_wrapper = '<select id="user_registration_frontend_listings_advanced_filter_fields_selector" class="ur-select">';

		foreach ( $field['options'] as $option_key => $option_value ) {
			$field_wrapper .= '<option value="' . esc_attr( $option_key ) . '" >' . esc_html( $option_value ) . '</option>';
		}
		$field_wrapper .= '</select>';
		$field_wrapper .= '<div class="user_registration_frontend_listings_advanced_filter_fields_list">';
		if ( ! empty( $field['value'] ) ) {
			$field_value = (array) json_decode( $field['value'] );
			$count       = 1;

			foreach ( $field_value as $key => $value ) {
				$field_wrapper .= '<div class="user_registration_frontend_listings_advanced_filter_fields_container selected-options">';
				$field_wrapper .= '<div class="ur-draggable-option">';
				$field_wrapper .= '<div class="selected-option-container">';
				$field_wrapper .= '<svg width="12" height="20" viewBox="0 0 12 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<rect width="4" height="4" fill="#4C5477"></rect>
									<rect x="8" width="4" height="4" fill="#4C5477"></rect>
									<rect y="16" width="4" height="4" fill="#4C5477"></rect>
									<rect x="8" y="16" width="4" height="4" fill="#4C5477"></rect>
									<rect y="8" width="4" height="4" fill="#4C5477"></rect>
									<rect x="8" y="8" width="4" height="4" fill="#4C5477"></rect>
								</svg>';

				if ( isset( $field['options'][ $key ] ) ) {
					// Create a select field wrapper.
					$field_wrapper .= '<select id="user_registration_frontend_listings_advanced_filter_fields_' . $count . '" class="user_registration_frontend_listings_advanced_filter_fields_map ur-selected">';

					foreach ( $field['options'] as $option_key => $option_value ) {
						$field_wrapper .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $key === $option_key, true, false ) . '>' . esc_html( $option_value ) . '</option>';

					}
					$field_wrapper .= '</select>';
				} else {
					// Create a Meta Key and Field Label input field pair.
					$field_wrapper .= '<div class="user_registration_frontend_listings_advanced_filter_fields_map ur-input-group" id="user_registration_frontend_listings_advanced_filter_fields_' . $count . '">';
					$field_wrapper .= '<input type="text" id="user_registration_frontend_listings_advanced_filter_fields_' . $count . '_meta_label" class="user-registration-frontend-listing-advance-filter-meta-mapper ur-select custom-input" placeholder="Field Label" value="' . esc_attr( $value ) . '">';
					$field_wrapper .= '<input type="text" id="user_registration_frontend_listings_advanced_filter_fields_' . $count . '_meta_key" class="user-registration-frontend-listing-advance-filter-meta-mapper ur-select custom-input" placeholder="Meta Key( eg. meta_key_123, meta_key_456 )" value="' . esc_attr( $key ) . '">';
					$field_wrapper .= '</div>';
				}

				$field_wrapper .= '</div>';
				$field_wrapper .= '<span class="user_registration_frontend_listings_advanced_filter_fields_remove delete-option">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="feather feather-trash-2" viewBox="0 0 24 24">
											<path d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m-6 5v6m4-6v6"></path>
										</svg>
									</span>';
				$field_wrapper .= '</div>';
				$field_wrapper .= '</div>';

				++$count;
			}
		}
		echo $field_wrapper; // phpcs:ignore

		echo '</div>';
		echo '<input type="hidden" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" value="' . esc_attr( $field['value'] ) . '">';
		echo '</div>';
		echo '</div>';
	}


	/**
	 * Render card fields settings.
	 *
	 * Outputs a field selector with drag-and-drop ordering.
	 *
	 * @since x.x.x
	 * @param array $args Field arguments including selected_id, order_id, labels, options.
	 * @return void
	 */
	private function render_card_fields_settings( $args ) {

		$args = wp_parse_args(
			(array) $args,
			array(
				'selected_id'          => '',
				'order_id'             => '',
				'selected_label'       => '',
				'selected_placeholder' => '',
				'order_label'          => '',
				'tooltip'              => '',
				'options'              => array(),
				'default_selected'     => array(),
			)
		);

		if ( empty( $args['selected_id'] ) || empty( $args['order_id'] ) ) {
			return;
		}

		$order_raw = $this->get_meta( $args['order_id'], '' );
		$order     = array();

		if ( is_string( $order_raw ) && $order_raw !== '' ) {
			$tmp = json_decode( $order_raw, true );
			if ( is_array( $tmp ) ) {
				$order = $tmp;
			}
		}

		$groups  = isset( $args['options'] ) ? (array) $args['options'] : array();
		$options = urfl_flatten_grouped_options( $groups );

		$order = array_values(
			array_filter(
				(array) $order,
				function ( $key ) use ( $options ) {
					return isset( $options[ (string) $key ] );
				}
			)
		);

		if ( empty( $order ) ) {
			$legacy_selected = $this->get_meta( $args['selected_id'], array() );

			if ( empty( $legacy_selected ) && ! empty( $args['default_selected'] ) ) {
				$legacy_selected = (array) $args['default_selected'];
			}

			if ( is_string( $legacy_selected ) ) {
				$decoded = json_decode( $legacy_selected, true );
				if ( is_array( $decoded ) ) {
					$legacy_selected = $decoded;
				} else {
					$legacy_selected = array_filter( array_map( 'trim', explode( ',', $legacy_selected ) ) );
				}
			}

			$legacy_selected = array_values(
				array_filter(
					array_map( 'strval', (array) $legacy_selected ),
					function ( $k ) use ( $options ) {
						return isset( $options[ (string) $k ] );
					}
				)
			);

			$order = $legacy_selected;

			if ( empty( $order_raw ) && ! empty( $order ) ) {
				$order_raw = wp_json_encode( $order );
			}
		}
		$empty_text = isset( $args['empty_text'] ) ? $args['empty_text'] : __( 'Select fields above to add them here.', 'user-registration-frontend-listing' );
		?>
		<div class="urfl-field urfl-field-card-fields urfl-grid-only" style="display:none;">
		<div class="urfl-field-label">
			<label for="<?php echo esc_attr( $args['selected_id'] ); ?>_select"><?php echo esc_html( $args['selected_label'] ); ?></label>
			<?php $this->render_tooltip( $args ); ?>
		</div>

		<div class="urfl-field-input">
			<select
				class="urfl-multiselect urfl-card-fields-select"
				id="<?php echo esc_attr( $args['selected_id'] ); ?>_select"
				name="<?php echo esc_attr( $args['selected_id'] ); ?>[]"
				multiple="multiple"
				data-placeholder="<?php echo esc_attr( $args['selected_placeholder'] ); ?>"
				data-groups='<?php echo wp_json_encode( $groups ); ?>'
				data-options='<?php echo wp_json_encode( $options ); ?>'
			>
				<?php foreach ( $groups as $group ) : ?>
					<?php
					$g_label   = isset( $group['label'] ) ? (string) $group['label'] : '';
					$g_options = isset( $group['options'] ) && is_array( $group['options'] ) ? $group['options'] : array();

					if ( '' === $g_label || empty( $g_options ) ) {
						continue;
					}
					?>
					<optgroup label="<?php echo esc_attr( $g_label ); ?>">
						<?php foreach ( $g_options as $key => $label ) : ?>
							<?php $k = (string) $key; ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php echo in_array( $k, array_map( 'strval', $order ), true ) ? 'selected' : ''; ?>>
								<?php echo esc_html( (string) $label ); ?>
							</option>
						<?php endforeach; ?>
					</optgroup>
				<?php endforeach; ?>
			</select>

			<div class="urfl-card-fields-order-section">
				<div class="urfl-card-fields-order" data-options='<?php echo wp_json_encode( $options ); ?>'>
					<?php if ( ! empty( $order ) ) : ?>
						<?php foreach ( $order as $key ) : ?>
							<?php
							if ( ! isset( $options[ (string) $key ] ) ) {
								continue;
							}
							?>
							<div class="urfl-card-field-row" data-key="<?php echo esc_attr( (string) $key ); ?>">
								<span class="urfl-drag-handle">
									<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
										<path d="M7 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222C7.985 14.222 7 13.227 7 12Zm0-7.778C7 2.995 7.985 2 9.2 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222A2.21 2.21 0 0 1 7 4.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 9.2 22C7.985 22 7 21.005 7 19.778ZM13.6 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222c-1.215 0-2.2-.995-2.2-2.222Zm0-7.778C13.6 2.995 14.585 2 15.8 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222 2.21 2.21 0 0 1-2.2-2.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 15.8 22c-1.215 0-2.2-.995-2.2-2.222Z"></path>
									</svg>
								</span>
								<span class="urfl-card-field-label"><?php echo esc_html( $options[ (string) $key ] ); ?></span>

								<!-- ADD REMOVE BUTTON -->
								<button type="button" class="urfl-remove-card-field" aria-label="<?php esc_attr_e( 'Remove', 'user-registration-frontend-listing' ); ?>">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<line x1="18" y1="6" x2="6" y2="18"></line>
										<line x1="6" y1="6" x2="18" y2="18"></line>
									</svg>
								</button>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<p class="urfl-order-empty-message"><?php echo esc_html( $empty_text ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<input
				type="hidden"
				name="<?php echo esc_attr( $args['order_id'] ); ?>"
				id="<?php echo esc_attr( $args['order_id'] ); ?>"
				class="urfl-card-fields-order-value"
				value="<?php echo esc_attr( $order_raw ); ?>"
			/>
		</div>
	</div>
		<?php
	}


	/**
	 * Render tooltip.
	 *
	 * Outputs a help tooltip icon with hover text.
	 *
	 * @since x.x.x
	 * @param array $args Field arguments containing tooltip key.
	 * @return void
	 */
	private function render_tooltip( $args ) {
		if ( empty( $args['tooltip'] ) ) {
			return;
		}
		?>
		<span class="ur-md-tooltip user-registration-help-tip" data-tip="<?php echo esc_attr( $args['tooltip'] ); ?>"></span>
		<?php
	}

	/**
	 * Save settings via AJAX.
	 *
	 * Validates permissions and nonce, sanitizes submitted settings, updates post meta,
	 * and publishes auto-draft posts when needed.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function ajax_save_settings() {
		check_ajax_referer( 'urfl_save_settings', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'user-registration-frontend-listing' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'user-registration-frontend-listing' ) ) );
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			wp_send_json_error( array( 'message' => __( 'Post not found.', 'user-registration-frontend-listing' ) ) );
		}

		if ( 'auto-draft' === $post->post_status ) {
			$post_title = isset( $_POST['post_title'] )
				? sanitize_text_field( wp_unslash( $_POST['post_title'] ) )
				: __( 'Frontend Listing', 'user-registration-frontend-listing' );

			if ( '' === trim( $post_title ) ) {
				$post_title = __( 'Frontend Listing', 'user-registration-frontend-listing' ) . ' #' . $post_id;
			}

			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'publish',
					'post_title'  => $post_title,
				)
			);
		}

		$settings  = isset( $_POST['settings'] ) ? (array) $_POST['settings'] : array();
		$field_ids = ur_frontend_listings_metabox_field_ids();

		$checkbox_fields = array(
			'user_registration_frontend_listings_allow_guest',
			'user_registration_frontend_listings_display_profile_picture',
			'user_registration_frontend_listings_sort_by',
			'user_registration_frontend_listings_view_profile',
			'user_registration_frontend_listings_search_form',
			'user_registration_frontend_listings_advanced_filter',
			'user_registration_frontend_listings_amount_filter',
			'user_registration_frontend_listings_view_profile_display_profile_picture',
		);

		$multiselect_fields = array(
			'user_registration_frontend_listings_role_restriction',
			'user_registration_frontend_listings_filter_by_user_status',
			'user_registration_frontend_listings_search_fields',
			'user_registration_frontend_listings_ur_forms',
			'user_registration_member_directory_ur_membership_type',
			'user_registration_frontend_listings_card_fields',
			'user_registration_frontend_listings_lists_fields',
		);

		foreach ( $field_ids as $field_id ) {
			if ( array_key_exists( $field_id, $settings ) ) {
				$value = $settings[ $field_id ];

				if ( is_array( $value ) ) {
					$value = array_values(
						array_filter(
							array_map(
								static function ( $v ) {
									return sanitize_text_field( wp_unslash( $v ) );
								},
								$value
							),
							'strlen'
						)
					);
				} else {
					$value = sanitize_text_field( wp_unslash( $value ) );
				}

				update_post_meta( $post_id, $field_id, $value );
				continue;
			}

			if ( in_array( $field_id, $checkbox_fields, true ) ) {
				update_post_meta( $post_id, $field_id, '0' );
				continue;
			}

			if ( in_array( $field_id, $multiselect_fields, true ) ) {
				update_post_meta( $post_id, $field_id, array() );
			}
		}

		wp_send_json_success(
			array(
				'message'  => __( 'Settings saved successfully.', 'user-registration-frontend-listing' ),
				'post_id'  => $post_id,
				'redirect' => admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
			)
		);
	}


	/**
	 * Get membership options for select fields.
	 *
	 * @return array [ membership_id => membership_label ]
	 */
	private function get_membership_options() {
		if ( ! $this->membership_service ) {
			return array();
		}

		$memberships = $this->membership_service->list_active_memberships();

		if ( ! is_array( $memberships ) ) {
			return array();
		}

		$options = array();

		foreach ( $memberships as $membership ) {

			$id    = isset( $membership['ID'] ) ? (int) $membership['ID'] : 0;
			$label = '';

			if ( isset( $membership['title'] ) && $membership['title'] !== '' ) {
				$label = (string) $membership['title'];
			}

			if ( ! $id || '' === $label ) {
				continue;
			}

			$options[ (string) $id ] = $label;
		}
		return $options;
	}


	/**
	 * Get card field options via AJAX.
	 *
	 * Returns card field options based on current selection mode.
	 * Accepts live form values to provide real-time updates without saving.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function ajax_get_card_field_options() {
		check_ajax_referer( 'urfl_save_settings', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'user-registration-frontend-listing' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'user-registration-frontend-listing' ) ) );
		}

		$mode = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : null;

		$forms = null;
		if ( isset( $_POST['forms'] ) ) {
			$forms = is_array( $_POST['forms'] ) ? array_map( 'absint', (array) $_POST['forms'] ) : array();
		}

		$memberships = null;
		if ( isset( $_POST['memberships'] ) ) {
			$memberships = is_array( $_POST['memberships'] )
				? array_map(
					static function ( $v ) {
						return sanitize_text_field( wp_unslash( $v ) );
					},
					(array) $_POST['memberships']
				)
				: array();
		}

		$groups  = urfl_card_fields_grouped_options_by_selection( $post_id, $mode, $forms, $memberships );
		$options = urfl_flatten_grouped_options( $groups );

		wp_send_json_success(
			array(
				'groups'  => $groups,
				'options' => $options,
				'mode'    => $mode,
			)
		);
	}

	/**
	 * Save title via AJAX.
	 *
	 * Updates the post title for the listing post.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function ajax_save_title() {
		check_ajax_referer( 'urfl_save_settings', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'user-registration-frontend-listing' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'user-registration-frontend-listing' ) ) );
		}

		$post_title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';

		if ( '' === trim( $post_title ) ) {
			wp_send_json_error( array( 'message' => __( 'Title cannot be empty.', 'user-registration-frontend-listing' ) ) );
		}

		$result = wp_update_post(
			array(
				'ID'         => $post_id,
				'post_title' => $post_title,
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message'    => __( 'Title saved successfully.', 'user-registration-frontend-listing' ),
				'post_title' => $post_title,
			)
		);
	}
}

new Metabox();
