<?php
namespace LDQIE;
/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$setting_class = $GLOBALS['ldqie_options']; 
$values = get_option( "quiz_default" );
$result_text = $values['result_text'];
if( empty( $result_text ) || ! is_array( $result_text ) ) {
    $result_text = array();
}
?>
<div id="setting_tabs" class="cs_ld_tabs">
    <form method="post" id="frm_ldqie">
        <div class="setting-table-wrapper">
            <table border="0">
                <thead></thead>
                <tbody>
                    <?php $setting_class->create_fields( __( "Result Message(s)", "ldqie" ), "quiz_result_messages", "radio_switcher",  "", ($values["quiz_result_messages"] == 'on') ? "checked" : "", __( "When enabled, the first message will be diplayed to ALL users. To customize the message based on earned score, add new Graduation Levels and set the 'From' field to the desired grade.", "ldqie" ), "",  "" ); ?>
                    <tr>
                        <td colspan="2">
                            <div id="results_text_activation_div" class="ldqie_sub_table">
                                <div class="custom-field-container">
                                    <div class="custom-fields-wrapper">
                                        <table class="custom-field-table">
                                            <thead>
                                                <tr>
                                                    <td>
                                                        <?php
                                                            $def_text = '';
                                                            if( isset( $result_text[ 'text' ] ) && is_array($result_text[ 'text' ]) )  { 
                                                                $text = $result_text[ 'text' ];
                                                                $def_text = $text[ 0 ];
                                                            }
                                                        ?>
                                                        <?php $setting_class->create_fields( __( "Result Text", "ldqie" ), "resultText[]", "textarea", esc_textarea( $def_text ) ); ?>
                                                        <div class="ldqi_bottom_bar">
                                                            <input name="resultTextGrade[]" readonly="readonly" class="ldqie_small_text" value="0"> percent (Will be displayed, when result-percent is &gt;= <span class="resultProzent">0</span>%)									
                                                            <input type="hidden" value="1" id="resultactiv" name="resultactiv[]" />
                                                        </div>
                                                    </td>
                                                </tr>
                                            </thead>                         
                                            <tbody class="custom-ui-sortable">
                                                <?php 
                                                    if( isset( $result_text[ 'text' ] ) && is_array($result_text[ 'text' ]) )  { 
                                                        $text = $result_text[ 'text' ];
                                                        $prozent = $result_text[ 'prozent' ];
                                                        for( $i = 1; $i < count( $text ); $i++ ) { 
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <?php $setting_class->create_fields( __( "Result Text", "ldqie" ), "resultText[]", "textarea", esc_textarea( $text[$i] ) ); ?>
                                                            <div class="ldqi_bottom_bar">
                                                                from: <input name="resultTextGrade[]" class="ldqie_small_text" value="<?php echo esc_html(floatval($prozent[$i]));?>"> percent (Will be displayed, when result-percent is &gt;= <span class="resultProzent">0</span>%)									
                                                                <input type="button" class="button-primary ldqie_lnk_btns remove-field button-secondary" value="Delete">
                                                                <a class="button-primary ldqie_lnk_btns form_move button-secondary ui-sortable-handle" href="#"><?php echo __( "Move", "ldqie" ); ?></a>
                                                                <div style="clear: right;"></div>
                                                                <input type="hidden" value="1" id="resultactiv" name="resultactiv[]" />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } } ?>
                                            </tbody>
                                        </table>
                                        <div style="margin-top: 10px;">
                                            <input type="button" name="form_add" class="add-field button-secondary" value="Add field" />
                                        </div>
                                    </div>
                                </div>
                                <!-- Template --> 
                                <script type="text/html" id="tmpl-fields-group" class="sortable">
                                    <tr class="ui-state-default">
                                        <td>
                                            <?php $setting_class->create_fields( __( "Result Text", "ldqie" ), "resultText[]", "textarea", esc_textarea( $values['resultText'] ) ); ?>
                                            <div class="ldqi_bottom_bar">
                                                from: <input name="resultTextGrade[]" class="ldqie_small_text small-text" value="<?php echo esc_html(floatval( $values['resultTextGrade'] ) );?>"> percent (Will be displayed, when result-percent is &gt;= <span class="resultProzent">0</span>%)									
                                                <input type="button" class="button-primary ldqie_lnk_btns remove-field button-secondary" value="Delete">
                                                <a class="button-primary ldqie_lnk_btns form_move button-secondary ui-sortable-handle" href="#"><?php echo __( "Move", "ldqie" ); ?></a>
                                                <div style="clear: right;"></div>
                                                <input type="hidden" value="1" id="resultactiv" name="resultactiv[]" />
                                            </div>
                                        </td>
                                    </tr>
                                </script>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <?php $setting_class->create_fields( __( "Restart Quiz button", "ldqie" ), "btnRestartQuizHidden", "radio_switcher",  "", ($values["btnRestartQuizHidden"] == 'on') ? "checked" : "", "", "",  "" ); ?>
                    </tr>
                    <tr>
                        <?php $setting_class->create_fields( __( "Custom Results Display", "ldqie" ), "custom_result_data_display", "radio_switcher",  "", ($values["custom_result_data_display"] == 'on') ? "checked" : "", __( "Enable the items you wish to display on the Result Page" ), "",  "" ); ?>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div id="custom_result_data_display_detail" class="ldqie_sub_table">
                                <table>
                                    <tr><?php $setting_class->create_fields( __( "Average Score", "ldqie" ), "showAverageResult", "radio_switcher",  "", ($values["showAverageResult"] == 'on') ? "checked" : "", __( "Display the average score of all users who took the quiz.", "ldqie" ), "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Category Score", "ldqie" ), "showCategoryScore", "radio_switcher",  "", ($values["showCategoryScore"] == 'on') ? "checked" : "", __( "Display the score achieved for each Question Category", "ldqie" ), "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Overall Score", "ldqie" ), "hideResultPoints", "radio_switcher",  "", ($values["hideResultPoints"] == 'on') ? "checked" : "", __( "The achieved Quiz score is NOT be displayed on the Results page", "ldqie" ), "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "No. of Correct Answers", "ldqie" ), "hideResultCorrectQuestion", "radio_switcher",  "", ($values["hideResultCorrectQuestion"] == 'on') ? "checked" : "", __( "The number of correctly answered Questions is NOT displayed on the Results page.", "ldqie" ), "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Time Spent", "ldqie" ), "hideResultQuizTime", "radio_switcher",  "", ($values["hideResultQuizTime"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <?php $setting_class->create_fields( __( "Custom Answer Feedback", "ldqie" ), "custom_answer_feedback", "radio_switcher",  "", ($values["custom_answer_feedback"] == 'on') ? "checked" : "", __( "Select which data users should be able to view when reviewing their submitted questions." ), "",  "" ); ?>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div id="custom_answer_feedback_detail" class="ldqie_sub_table">
                                <table>
                                    <tr><?php $setting_class->create_fields( __( "Correct / Incorrect Messages", "ldqie" ), "hideAnswerMessageBox", "radio_switcher",  "", ($values["hideAnswerMessageBox"] == 'on') ? "checked" : "",  "", "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Correct / Incorrect Answer Marks", "ldqie" ), "disabledAnswerMark", "radio_switcher",  "", ($values["disabledAnswerMark"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "View Questions Button", "ldqie" ), "btnViewQuestionHidden", "radio_switcher",  "", ($values["btnViewQuestionHidden"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                </table>
                            </div>
                        </td>
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