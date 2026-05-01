<?php

namespace PowerpackElements\Extensions;

// Powerpack Elements classes
use PowerpackElements\Base\Extension_Base;
use PowerpackElements\Classes\PP_Posts_Helper;

// Elementor classes
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Conditions Extension
 *
 * Adds display conditions to elements
 *
 * @since 1.4.7
 */
class Extension_Display_Conditions extends Extension_Base {

	/**
	 * Is Common Extension
	 *
	 * Defines if the current extension is common for all element types or not
	 *
	 * @since 1.4.7
	 * @access protected
	 *
	 * @var bool
	 */
	protected $is_common = true;

	/**
	 * A list of scripts that the widgets is depended in
	 *
	 * @since 1.4.7
	 **/
	public function get_script_depends() {
		return [];
	}

	/**
	 * The description of the current extension
	 *
	 * @since 1.4.7
	 **/
	public static function get_description() {
		return __( 'Adds display conditions to widgets and sections allowing you to show them depending on authentication, roles, date and time of day.', 'powerpack' );
	}

	/**
	 * Is disabled by default
	 *
	 * Return wether or not the extension should be disabled by default,
	 * prior to user actually saving a value in the admin page
	 *
	 * @access public
	 * @since 1.4.7
	 * @return bool
	 */
	public static function is_default_disabled() {
		return true;
	}

	/**
	 * Add common sections
	 *
	 * @since 1.4.7
	 *
	 * @access protected
	 */
	protected function add_common_sections_actions() {

		// Activate sections for widgets
		add_action( 'elementor/element/common/section_custom_css/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		// Activate sections for sections
		add_action( 'elementor/element/section/section_custom_css/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		// Activate sections for widgets if elementor pro
		add_action( 'elementor/element/common/section_custom_css_pro/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		// Activate sections for sections if elementor pro
		add_action( 'elementor/element/section/section_custom_css_pro/after_section_end', function( $element, $args ) {
			$this->add_common_sections( $element, $args );
		}, 10, 2 );

	}

	/**
	 * Add Actions
	 *
	 * @since 1.4.7
	 *
	 * @access protected
	 */
	protected function add_actions() {
		//error_log( print_r( \PowerpackElements\Powerpackplugin::instance(), true  ));
		$module = \PowerpackElements\Powerpackplugin::instance()->modules_manager->get_modules( 'display-conditions' );
		$module->add_actions();
	}

	protected function render_editor_notice( $settings ) {
		?><span><?php _e( 'This widget is displayed conditionally.', 'powerpack' ); ?></span>
		<?php
	}
}
