<?php
namespace LDQIE;
/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$course_parms = array(
    'numberposts' => -1,
    'post_type' => 'sfwd-courses',
    'suppress_filters' => true
);
$courses = get_posts( $course_parms );

$lesson_parms = array(
    'numberposts' => -1,
    'post_type' => 'sfwd-lessons',
    'suppress_filters' => true
);
$lessons = get_posts( $lesson_parms );

$setting_class = $GLOBALS['ldqie_options']; 
$values = get_option( "quiz_default" );
?>
<div id="setting_tabs" class="cs_ld_tabs">
    <form method="post">
        <div class="setting-table-wrapper">
            <table border="0">
                <thead></thead>
                <tbody>
                    <tr>
                        <td><label for="learndash-quiz-access-settings_course" class="label"><?php _e( 'Associated Course', 'ldqie' ); ?></label></td>
                        <td>
                            <select id="learndash-quiz-access-settings_course" class="ld-single-quiz-select2-ddl" name="course" style="width:300px">
                                <option></option>
                                <?php foreach( $courses as $course) { ?>
                                    <option value="<?php echo $course->ID;?>" <?php echo $course->ID==$values['course']?'selected':'';?>><?php echo $course->post_title;?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">
                            <label for="learndash-quiz-access-settings_lesson"><?php _e( 'Associated Lesson', 'ldqie' ); ?></label>
                        </td>
                        <td align="left">
                            <select id="learndash-quiz-access-settings_lesson" class="ld-single-quiz-select2-ddl" name="lesson">
                                <option></option>
                                <?php foreach( $lessons as $lesson) { ?>
                                    <option value="<?php echo $lesson->ID;?>" <?php echo $lesson->ID==$values['lesson']?'selected':'';?>><?php echo $lesson->post_title;?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="prerequisite">
                        <td>
                            <label for="prerequisiteList" class="label"><?php _e( "Quiz Prerequisites", "ldqie" ); ?></label>
                        </td>
                        <td>
                            <select name="prerequisiteList[]" class="ld-single-quiz-select2-ddl" multiple id="prerequisiteList" multiple="multiple">
                                <option></option>
                                <?php
                                $quiz_query = new \WP_Query( array( "post_type" => "sfwd-quiz", "post_status" => "publish", "post_per_page" => -1 ) );
                                if( $quiz_query->have_posts() ) {
                                    $loop_num = 0;
                                    while( $quiz_query->have_posts() ) {
                                        $quiz_query->the_post();
                                        if( in_array( get_post_meta ( get_the_ID(), "quiz_pro_id", true ), $values['prerequisiteList'] ) ) {
                                            $selected = 'selected';
                                        } else {
                                            $selected = '';
                                        }
                                        echo "<option value='".get_post_meta ( get_the_ID(), "quiz_pro_id", true )."' ".$selected.">".get_the_title()."</option>";
                                        $loop_num++;
                                    }
                                    wp_reset_query();
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <?php $setting_class->create_fields( __( "Allowed Users", "ldqie" ), "startOnlyRegisteredUser", "checkbox", "", ($values["startOnlyRegisteredUser"] == 'on') ? "checked" : "", __( "This option is especially useful if administering Quizzes via shortcodes on non-course pages, or if your Course are open but you wish only authenticated users to take the Quiz.", "ldqie" ), "", __( "Only registered users can take this quiz", "ldqie" ) ); ?>
                    </tr>
                </tbody>
            </table>
            <div class="submit-button">
                <input type="submit" class="button-primary" value="Update Settings">
            </div>
        </div>
        <?php wp_nonce_field( 'ldqie_settings', 'ldqie_settings_field' ); ?>
    </form>
</div>           