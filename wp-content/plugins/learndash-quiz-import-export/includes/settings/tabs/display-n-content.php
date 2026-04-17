<?php
namespace LDQIE;
/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$certificates = get_posts( array( 'post_type' => 'sfwd-certificates' , 'numberposts' => -1 ) );
$setting_class = $GLOBALS['ldqie_options']; 
$values = get_option( "quiz_default" );
?>
<div id="setting_tabs" class="cs_ld_tabs"> 
    <form method="post" id="frm_ldqie">
        <div class="setting-table-wrapper">
            <table border="0">
                <thead></thead>
                <tbody>
                    <?php $setting_class->create_fields( __( "Quiz Materials", "ldqie" ), "quiz_materials_enabled", "radio_switcher",  "", ($values["quiz_materials_enabled"] == 'on') ? "checked" : "", __( "List and display support materials for the quiz. This is visible to any user having access to the quiz.", "ldqie" ), "",  "" ); ?>
                    <tr>
                        <td><label id="lbl_quiz_materials"><?php _e( "Description", "ldqie" );?></label></td>
                        <td>
                            <?php $setting_class->create_fields( "", "quiz_materials", "textarea", stripslashes( esc_textarea($values["quiz_materials"]) ) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <?php $setting_class->create_fields( __( "Auto Start", "ldqie" ), "autostart", "checkbox", "", ($values["autostart"] == 'on') ? "checked" : "", __( "If you enable this option, the quiz will start automatically after the page is loaded.", "ldqie" ), "", __( "Activate", "ldqie" ) ); ?>
                    </tr>
                    <tr>    
                        <td><label for="quizModus" class="label"><?php _e( 'Question Display', 'ldqie' ); ?></label></td>
                        <td>
                            <select id="quizModus" class="ld-single-quiz-select2-ddl" name="quizModus" style="width:300px">
                                <option></option>
                                <option value="0" <?php echo '0'==$values["quizModus"]?'selected':'';?>><?php _e( 'One question at a time', 'ldqie' ); ?></option> 
                                <option value="3" <?php echo '3'==$values["quizModus"]?'selected':'';?>><?php _e( 'All questions at once (or paginated)', 'ldqie' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>    
                        <td></td>
                        <td>
                            <div id="quizModus_multiple_questionsPerPage_detail">
                                <input type="number" id="quizModus_multiple_questionsPerPage" value="<?php echo $values["quizModus_multiple_questionsPerPage"];?>" name="quizModus_multiple_questionsPerPage">
                                <div><?php _e( "questions per page (0 = all)", "ldqie" );?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <div class="ldqie_sub_quizModus_table">
                                <table>
                                    <tr><td><?php $setting_class->create_fields( "", "quizModus_single_feedback", "radio", "end", ($values["quizModus_single_feedback"] == 'end') ? "checked" : (($values["quizModus_single_feedback"] == '') ? "checked" : ""), "", "", __( "Display results at the end only", "ldqie" ) ); ?></td></tr>
                                    <tr>    
                                        <td><div id="quizModus_single_back_button_div" style="<?php echo $values["quizModus_single_feedback"] == 'end'?'display:block':'display:none';?>"><input type="checkbox" id="quizModus_single_back_button" <?php echo ($values["quizModus_single_back_button"] == 'on') ? "checked" : "";?> value="on" name="quizModus_single_back_button" /> <?php _e( "Display Back button", "ldqie" );?></div></td>
                                    </tr>
                                    <tr><td><?php $setting_class->create_fields( "", "quizModus_single_feedback", "radio", "each", ($values["quizModus_single_feedback"] == 'each') ? "checked" : "", "", "", __( "Display results after each submitted answer", "ldqie" ) ); ?></td></tr>
                                </table>
                            
                            </div>
                        </td>
                    </tr>
                    <?php $setting_class->create_fields( __( "Question Overview Table", "ldqie" ), "showReviewQuestion", "radio_switcher",  "", ($values["showReviewQuestion"] == 'on') ? "checked" : "", __( "An overview table will be shown for all questions.", "ldqie" ), "",  "" ); ?>
                    <tr>
                        <td colspan="2">
                            <div id="showReviewQuestion_detail" class="ldqie_sub_table">
                                <table>
                                    <tr><?php $setting_class->create_fields( __( "Quiz Summary", "ldqie" ), "quizSummaryHide", "radio_switcher",  "", ($values["quizSummaryHide"] == 'on') ? "checked" : "","", "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Skip Question", "ldqie" ), "skipQuestionDisabled", "radio_switcher",  "", ($values["skipQuestionDisabled"] == 'on') ? "checked" : "", __( "", "ldqie" ), "",  "" ); ?></tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <?php $setting_class->create_fields( __( "Custom Question Ordering", "ldqie" ), "custom_sorting", "radio_switcher",  "", ($values["custom_sorting"] == 'on') ? "checked" : "", "", "",  "" ); ?>
                    <tr>
                        <td colspan="2">
                            <div id="custom_sorting_detail" class="ldqie_sub_table">
                                <table>
                                    <tr>
                                        <?php $setting_class->create_fields( __( "Sort by Category", "ldqie" ), "sortCategories", "checkbox", "", ($values["sortCategories"] == 'on') ? "checked" : "", "", "", ""); ?>
                                    </tr>
                                    <tr><?php $setting_class->create_fields( __( "Randomize Order", "ldqie" ), "questionRandom", "radio_switcher",  "", ($values["questionRandom"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>
                                            <div id="showMaxQuestion_opts_detail" style="<?php echo $values["questionRandom"] == 'on'?'display:block':'display:none';?>">
                                                <table>
                                                    <tr><td><?php $setting_class->create_fields( "", "showMaxQuestion", "radio", "", ($values["showMaxQuestion"] == '') ? "checked" : "", "", "", __( "Display all questions", "ldqie" ) ); ?></td></tr>
                                                    <tr><td><?php $setting_class->create_fields( "", "showMaxQuestion", "radio", "on", ($values["showMaxQuestion"] == 'on') ? "checked" : "", "", "", __( "Display subset of questions", "ldqie" ) ); ?></td></tr>
                                                    <tr>
                                                        <td>
                                                            <div id="showMaxQuestion_detail" class="ldqie_sub_table">
                                                                <input type="number" id="showMaxQuestionValue" name="showMaxQuestionValue" value="<?php echo $values["showMaxQuestionValue"];?>" class="" />
                                                                <div><?php _e( 'out of total questions.', 'ldqie' ); ?></div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <?php $setting_class->create_fields( __( "Additional Question Options", "ldqie" ), "custom_question_elements", "radio_switcher",  "", ($values["custom_question_elements"] == 'on') ? "checked" : "", __( "Any enabled elements below will be displayed in each Question.", "ldqie" ), "",  "" ); ?>
                    <tr>
                        <td colspan="2">
                            <div id="custom_question_elements_detail" class="ldqie_sub_table">
                                <table>
                                    <tr><?php $setting_class->create_fields( __( "Point Value", "ldqie" ), "showPoints", "radio_switcher",  "", ($values["showPoints"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Question Category", "ldqie" ), "showCategory", "radio_switcher",  "", ($values["showCategory"] == 'on') ? "checked" : "", __( "", "ldqie" ), "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Question Position", "ldqie" ), "hideQuestionPositionOverview", "radio_switcher",  "", ($values["hideQuestionPositionOverview"] == 'on') ? "checked" : "", __( "", "ldqie" ), "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Question Numbering ", "ldqie" ), "hideQuestionNumbering", "radio_switcher",  "", ($values["hideQuestionNumbering"] == 'on') ? "checked" : "", __( "", "ldqie" ), "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Number Answers", "ldqie" ), "numberedAnswer", "radio_switcher",  "", ($values["numberedAnswer"] == 'on') ? "checked" : "", __( "", "ldqie" ), "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Randomize Answers", "ldqie" ), "answerRandom", "radio_switcher",  "", ($values["answerRandom"] == 'on') ? "checked" : "", __( "Answer display will be randomized within any given question.", "ldqie" ), "",  "" ); ?></tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <?php $setting_class->create_fields( __( "Quiz Title", "ldqie" ), "titleHidden", "radio_switcher",  "", ($values["titleHidden"] == 'on') ? "checked" : "", __( "A second quiz title will be displayed on the Quiz Post. This option is recommended if displaying Quizzes via Shortcode.", "ldqie" ), "",  __( "", "ldqie" ) ); ?>
                    
                </tbody>
            </table>
            <div class="submit-button">
                <input type="submit" class="button-primary" value="Update Settings">
            </div>
        </div>
        <?php wp_nonce_field( 'ldqie_settings', 'ldqie_settings_field' ); ?>
    </form>
</div>