<?php
/**
 * Template : LDGR Group Report Table Template
 * 
 * @var int     $course_id      ID of the Learndash Course.
 * @var int     $group_id       ID of the Learndash Group
 * @var bool    $rewards        True if certificate is associated with a course, else false.
 */
?>

<table id="wdm_ldgr_group_report">
    <thead>
        <tr>
            <th></th>
            <th><?php echo apply_filters(
                'wdm_ldgr_th_name',
                __('Name','wdm_ld_group'),
                $course_id,
                $group_id); ?>
            </th>
            <th><?php echo apply_filters(
                'wdm_ldgr_th_emailid',
                __('Email ID','wdm_ld_group'),
                $course_id,
                $group_id); ?>
            </th>
            <th><?php echo apply_filters(
                'wdm_ldgr_th_course_progress',
                sprintf( __('%s Progress', 'wdm_ld_group'), \LearnDash_Custom_Label::get_label( 'Course' ) ),
                $course_id,
                $group_id); ?>
            </th>
            <?php if( $rewards ) : ?>
                <th><?php echo apply_filters(
                    'wdm_ldgr_th_rewards',
                    __('Rewards','wdm_ld_group'),
                    $course_id,
                    $group_id); ?>
                </th>
            <?php endif; ?>
        </tr>
    </thead>
</table>
