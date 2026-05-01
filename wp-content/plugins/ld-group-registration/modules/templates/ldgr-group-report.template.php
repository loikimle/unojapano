<?php
/**
 * Template : LDGR Group Report Template
 *
 * Show group reports for specific selected courses to group leader.
 * 
 * @param int $group_id		ID of the LearnDash Group.
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>

<div id="tab-3" class="tab-content">
	<?php
		global $learndash_assets_loaded;
	if ( ! isset( $learndash_assets_loaded['scripts']['learndash_template_script_js'] ) ) {
		$filepath = SFWD_LMS::get_template( 'learndash_template_script.js', null, null, true );
		if ( ! empty( $filepath ) ) {
			wp_enqueue_script(
				'learndash_template_script_js',
				learndash_template_url_from_path( $filepath ),
				array( 'jquery' ),
				LEARNDASH_SCRIPT_VERSION_TOKEN,
				true
			);
			$learndash_assets_loaded['scripts']['learndash_template_script_js'] = __FUNCTION__;

			$data            = array();
			$data['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$data            = array( 'json' => json_encode( $data ) );
			wp_localize_script( 'learndash_template_script_js', 'sfwd_data', $data );
		}
	}
		LD_QuizPro::showModalWindow();
	?>
	<?php
	// check if any course associated with group, any user enrolled.
	$group_courses = learndash_group_enrolled_courses( $group_id );

	/**
	 * Filter the list of courses in the group on groups dashboard.
	 *
	 * @param array $group_courses  List of courses in the group.
	 * @param int $group_id         ID of the group.
	 *
	 * @since 4.1.5
	 */
	$group_courses = apply_filters( 'ldgr_filter_group_course_list', $group_courses, $group_id );

	if ( empty( $group_courses ) ) {
		echo esc_html(
			sprintf(
				// translators: Course label.
				__( 'No %s associated with selected group!', 'wdm_ld_group' ),
				\LearnDash_Custom_Label::get_label( 'course' )
			)
		);
		echo '</div>';
		return;
	}
	?>
	<div id="wdm-ldgr-overlay">
		<img id="wdm-center-align" src="<?php echo esc_url( plugins_url( 'media/ajax-loader.gif', dirname( __FILE__ ) ) ); ?>">
	</div>
	<div class="wdm-select-wrapper">
		<h6>
			<?php
			echo esc_html(
				apply_filters(
					'wdm_ldgr_course_selection_dropdown_label',
					// translators: Course label.
					sprintf( __( 'Select %s', 'wdm_ld_group' ), \LearnDash_Custom_Label::get_label( 'Course' ) )
				)
			);
			?>
		</h6>
		<select id="wdm_ldgr_course_id" name="wdm_ldgr_course_id">
			<option value="">
				<?php
				echo esc_html(
					apply_filters(
						'wdm_ldgr_course_selection_dropdown_label',
						// translators: Course label.
						sprintf( __( 'Select %s', 'wdm_ld_group' ), \LearnDash_Custom_Label::get_label( 'Course' ) )
					)
				);
				?>
			</option>
				<?php
				foreach ( $group_courses as $group_course ) {
					$demo_title   = get_post( $group_course );
					$course_title = $demo_title->post_title;
					?>
					<option value="<?php echo esc_html( $group_course ); ?>">
						<?php echo esc_html( $course_title ); ?>
					</option>
					<?php
				}
				?>
		</select>
		<input
			type="button"
			id="wdm_ldgr_show_report"
			name="wdm_ldgr_show_report"
			value="<?php
			echo esc_html(
				apply_filters(
					'wdm_ldgr_show_report_button_label',
					__( 'Show Report', 'wdm_ld_group' )
				)
			);
			?>" />
	</div>
</div>
