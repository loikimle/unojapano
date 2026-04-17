<?php
/**
 * Template : LDGR Detailed Course Report Template
 *
 * @var int     $course_id      ID of the LearnDash Course.
 * @var int     $user_id        ID of the WP User to generate course report.
 * @var array   $quiz_attempts  Array of quiz attempts data for the course.
 * @var array   $progress       Progress of the course.
 */
?>

<div class="flip" style="clear: both; display:block;">

	<div class="learndash_profile_heading course_overview_heading">
		<?php
		printf(
			esc_html_x(
				'%s Progress Overview',
				'placeholder: Course',
				'learndash'
			),
			LearnDash_Custom_Label::get_label( 'course' )
		);
		?>
	</div>

	<div>
		<dd 
			class="course_progress"
			title='
			<?php
			echo sprintf(
				esc_html_x(
					'%1$d out of %2$d steps completed',
					'placeholder: completed steps, total steps',
					'learndash'
				),
				$progress['completed'],
				$progress['total']
			);
			?>
			'>
			<div class="course_progress_blue" style='width: <?php echo esc_attr( $progress['percentage'] ); ?>%;'>
		</dd>

		<div class="right">
			<?php
			echo sprintf(
				esc_html_x(
					'%1$s%% Complete',
					'placeholder: percent complete',
					'learndash'
				),
				$progress['percentage']
			);
			?>
		</div>
	</div>

	<?php if ( ! empty( $quiz_attempts[ $course_id ] ) ) { ?>

		<div class="learndash_profile_quizzes clear_both">

			<div class="learndash_profile_quiz_heading">
				<div class="quiz_title"><?php echo LearnDash_Custom_Label::get_label( 'quizzes' ); ?></div>
				<div class="certificate"><?php esc_html_e( 'Certificate', 'learndash' ); ?></div>
				<div class="scores"><?php esc_html_e( 'Score', 'learndash' ); ?></div>
				<div class="statistics"><?php esc_html_e( 'Statistics', 'learndash' ); ?></div>
				<div class="quiz_date"><?php esc_html_e( 'Date', 'learndash' ); ?></div>
			</div>

			<?php foreach ( $quiz_attempts[ $course_id ] as $k => $quiz_attempt ) : ?>
				<?php
					$certificateLink = null;

				if ( ( isset( $quiz_attempt['has_graded'] ) ) &&
						( true === $quiz_attempt['has_graded'] ) &&
						( true === LD_QuizPro::quiz_attempt_has_ungraded_question( $quiz_attempt ) ) ) {
					$status = 'pending';
				} else {
					$certificateLink = @$quiz_attempt['certificate']['certificateLink'];
					$status          = empty( $quiz_attempt['pass'] ) ? 'failed' : 'passed';
				}

					$quiz_title = ! empty( $quiz_attempt['post']->post_title ) ?
						$quiz_attempt['post']->post_title :
						@$quiz_attempt['quiz_title'];

					$quiz_link = ! empty( $quiz_attempt['post']->ID ) ?
						learndash_get_step_permalink(
							intval( $quiz_attempt['post']->ID ),
							$course_id
						) : '#';
				?>
				<?php if ( ! empty( $quiz_title ) ) : ?>
					<div class='<?php echo esc_attr( $status ); ?>'>

						<div class="quiz_title">
							<span class='<?php echo esc_attr( $status ); ?>_icon'></span>
							<a href='<?php echo esc_attr( $quiz_link ); ?>'><?php echo esc_attr( $quiz_title ); ?></a>
						</div>

						<div class="certificate">
							<?php if ( ! empty( $certificateLink ) ) : ?>
								<a href='<?php echo esc_attr( $certificateLink ); ?>&time=<?php echo esc_attr( $quiz_attempt['time'] ); ?>'
									target="_blank">
								<div class="certificate_icon"></div></a>
							<?php else : ?>
								<?php echo '-'; ?>
							<?php endif; ?>
						</div>

						<div class="scores">
							<?php
							if ( ( isset( $quiz_attempt['has_graded'] ) ) &&
								( true === $quiz_attempt['has_graded'] ) &&
								( true === LD_QuizPro::quiz_attempt_has_ungraded_question( $quiz_attempt ) ) ) :
								?>
								<?php echo esc_html_x( 'Pending', 'Pending Certificate Status Label', 'learndash' ); ?>
							<?php else : ?>
								<?php echo round( $quiz_attempt['percentage'], 2 ); ?>%
							<?php endif; ?>
						</div>

						<div class="statistics">
						<?php
						if ( ( learndash_is_admin_user() ) || ( learndash_is_group_leader_user() ) ) {
							if ( ( ! isset( $quiz_attempt['statistic_ref_id'] ) ) || ( empty( $quiz_attempt['statistic_ref_id'] ) ) ) {
								$quiz_attempt['statistic_ref_id'] = learndash_get_quiz_statistics_ref_for_quiz_attempt(
									$user_id,
									$quiz_attempt
								);
							}

							if ( ( isset( $quiz_attempt['statistic_ref_id'] ) ) && ( ! empty( $quiz_attempt['statistic_ref_id'] ) ) ) {
								/**
								 *   @since 2.3
									* See snippet on use of this filter https://bitbucket.org/snippets/learndash/5o78q
									*/
								if ( apply_filters(
									'show_user_profile_quiz_statistics',
									get_post_meta(
										$quiz_attempt['post']->ID,
										'_viewProfileStatistics',
										true
									),
									$user_id,
									$quiz_attempt,
									basename( __FILE__ )
								)
								) {
									?>
										<a class="user_statistic"
											data-statistic_nonce="<?php
											echo wp_create_nonce(
												'statistic_nonce_' . $quiz_attempt['statistic_ref_id'] . '_' . get_current_user_id() . '_' . $user_id
											);
											?>"
											data-user_id="<?php echo $user_id; ?>"
											data-quiz_id="<?php echo $quiz_attempt['pro_quizid']; ?>"
											data-ref_id="<?php echo intval( $quiz_attempt['statistic_ref_id'] ); ?>"
											href="#"
										>
											<div class="statistic_icon"></div>
										</a>
										<?php
								}
							}
						}
						?>
						</div>

						<div class="quiz_date">
							<?php echo learndash_adjust_date_time_display( $quiz_attempt['time'] ); ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php } ?>

</div>
