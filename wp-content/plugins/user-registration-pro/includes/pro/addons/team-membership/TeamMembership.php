<?php
/**
 * TeamMembership
 *
 * @class    TeamMembership
 */

namespace WPEverest\URTeamMembership;

use WPEverest\URTeamMembership\Admin\Admin;
use WPEverest\URTeamMembership\Emails\EmailSettings;
use WPEverest\URTeamMembership\Frontend\Frontend;
use WPEverest\URTeamMembership\Services\TeamService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TeamMembership {
	/**
	 * Admin class instance
	 *
	 * @var \Admin
	 * @since 1.0.0
	 */
	public $admin = null;

	/**
	 * Frontend class instance
	 *
	 * @var \Frontend
	 * @since 1.0.0
	 */
	public $frontend = null;

	/**
	 * Ajax.
	 *
	 * @since 1.0.0
	 *
	 * @var use WPEverest\URTeamMembership\AJAX;
	 */
	public $ajax = null;

	/**
	 * Constructor for the class.
	 *
	 * Sets the page property and registers various hooks.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_team_membership_post_type' ), 0 );

		add_action( 'init', array( $this, 'includes' ) );
		add_action(
			'ur_membership_team_membership',
			array( $this, 'include_membership_team_pricing' ),
			10,
			2
		);
	}

	/**
	 * Register the Team Membership post type.
	 *
	 * @return void
	 */
	public function register_team_membership_post_type() {
		if ( post_type_exists( 'ur_membership_team' ) ) {
			return;
		}

		register_post_type(
			'ur_membership_team',
			apply_filters(
				'user_registration_team_membership_post_type',
				array(
					'public'          => false,
					'show_ui'         => false,
					'capability_type' => 'post',
					'show_in_menu'    => false,
					'hierarchical'    => false,
					'has_archive'     => false,
					'rewrite'         => false,
					'supports'        => array( 'title' ),
					'show_in_rest'    => false,
				)
			)
		);
	}

	public function include_membership_team_pricing( $membership, $membership_details ) {
		$file = plugin_dir_path( __FILE__ ) . './Views/team-membership-pricing.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Includes.
	 */
	public function includes() {
		$this->frontend = new Frontend();
		$this->ajax     = new Ajax();
		new EmailSettings();
	}
}

new TeamMembership();
