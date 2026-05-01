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
                    <tr>
                        <?php $setting_class->create_fields( __( "Passing Score", "ldqie" ), "passingpercentage", "number", esc_html( $values["passingpercentage"] ), "", "", "", __( "%", "ldqie" ) ); ?>
                    </tr>
                    <tr>
                        <td><label for="certificate" class="label"><?php _e( 'Quiz Certificate', 'ldqie' ); ?></label></td>
                        <td>
                            <select id="certificate" class="ld-single-quiz-select2-ddl" name="certificate" style="width:300px">
                                <option></option>
                                <?php foreach( $certificates as $certificate) { ?>
                                    <option value="<?php echo $certificate->ID;?>" <?php echo $certificate->ID==$values["certificate"]?'selected':'';?>><?php echo $certificate->post_title;?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div id="ldqie_certificate_extra_opts" class="ldqie_sub_table">
                                <?php 
                                    $threshold = floatval( $values["threshold"] );
                                ?>
                                <table border="0"> 
                                    <tbody>
                                        <tr>
                                            <td><label for="threshold" class="label"><?php _e( 'Certificate Awarded for', 'ldqie' ); ?></label></td>
                                            <td><input type="number" id="threshold" step="0.01" value="<?php echo $threshold;?>" name="threshold"> <?php _e( '% score', 'ldqie' ); ?></td>                                    
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <?php $setting_class->create_fields( __( "Restrict Quiz Retakes", "ldqie" ), "retry_restrictions", "radio_switcher",  "", ($values["retry_restrictions"] == 'on') ? "checked" : "", "", "", "" ); ?>
                    <tr>
                        <td colspan="2">
                                    
                            <div id="ldqie_retry_restrictions_extra_opts" class="ldqie_sub_table">
                                <table border="0"> 
                                    <tr>
                                        <?php $setting_class->create_fields( __( "Number of Retries Allowed", "ldqie" ), "repeats", "number", esc_html( $values["repeats"] ), "", __( "You must input a whole number value or leave blank to default to 0.", "ldqie" ), "", "" ); ?>
                                    </tr>
                                    <tr>    
                                        <td><label for="quizRunOnceType" class="label"><?php _e( 'Retries Applicable to', 'ldqie' ); ?></label></td>
                                        <td>
                                            <select id="quizRunOnceType" class="ld-single-quiz-select2-ddl" name="quizRunOnceType" style="width:300px">
                                                <option></option>
                                                <option value="1" <?php echo '1'==$values["quizRunOnceType"]?'selected':'';?>><?php _e( 'All users', 'ldqie' ); ?></option>
                                                <option value="2" <?php echo '2'==$values["quizRunOnceType"]?'selected':'';?>><?php _e( 'Registered users only', 'ldqie' ); ?></option>
                                                <option value="3" <?php echo '3'==$values["quizRunOnceType"]?'selected':'';?>><?php _e( 'Anonymous user only', 'ldqie' ); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <div id="quizRunOnceCookie_detail">
                                                <input type="checkbox" class="checkbox" <?php echo isset( $values["quizRunOnceCookie"] ) && $values["quizRunOnceCookie"]=='on'?'checked':'';?> id="quizRunOnceCookie" name="quizRunOnceCookie">
                                                <span class="hint">
                                                   <?php _e( 'Use a cookie to restrict ALL users, including anonymous visitors', 'ldqie' ); ?>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <?php $setting_class->create_fields( __( "Question Completion", "ldqie" ), "forcingQuestionSolve", "checkbox", "", ($values["forcingQuestionSolve"] == 'on') ? "checked" : "", "", "", __( "All Questions required to complete", "ldqie" ) ); ?>
                    </tr>
                    <?php $setting_class->create_fields( __( "Time Limit", "ldqie" ), "quiz_time_limit_enabled", "radio_switcher",  "", ($values["quiz_time_limit_enabled"] == 'on') ? "checked" : "", "", "", "" ); ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            
                            <div id="ldqie_timelimit_extra_opts" class="ldqie_sub_table">
                                <div>
                                    <?php _e( 'Automatically Submit After', 'ldqie' ); ?>
                                </div>
                                <?php
                                    $timeLimit = $values["timeLimit"];
                                    
                                    $hh = '';
                                    $mm = '';
                                    $ss = '';
                                    if( is_array( $timeLimit ) && count( $timeLimit ) == 3 ) {
                                        $hh = $timeLimit['hh'];
                                        $mm = $timeLimit['mm'];
                                        $ss = $timeLimit['ss'];
                                    }
                                ?>
                                <div class="ld_timer_selector">
                                    <span class="screen-reader-text"><?php _e( 'Hour', 'ldqie' ); ?></span>
                                    <input type="number" min="0" max="23" placeholder="HH" class="ld_date_hh learndash-section-field learndash-section-field-timer-entry small-text" name="timeLimit[hh]" value="<?php echo $hh;?>" size="2" maxlength="2" autocomplete="off">:<span class="screen-reader-text"><?php _e( 'Minute', 'ldqie' ); ?></span><input type="number" min="0" max="59" placeholder="MM" class="ld_date_mn learndash-section-field learndash-section-field-timer-entry small-text" name="timeLimit[mm]" value="<?php echo $mm;?>" size="2" maxlength="2" autocomplete="off">:<span class="screen-reader-text"><?php _e( 'Seconds', 'ldqie' ); ?></span><input type="number" min="0" max="59" placeholder="SS" class="ld_date_ss learndash-section-field learndash-section-field-timer-entry small-text" name="timeLimit[ss]" value="<?php echo $ss;?>" size="2" maxlength="2" autocomplete="off"></div>
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
