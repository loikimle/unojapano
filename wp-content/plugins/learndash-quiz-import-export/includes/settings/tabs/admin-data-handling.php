<?php
namespace LDQIE;
global $wpdb;
/**
 * Abort if this file is accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$setting_class = $GLOBALS['ldqie_options']; 
$values = get_option( "quiz_default" );
$quiz_parms = array(
    'numberposts' => -1,
    'post_type' => 'sfwd-quiz',
    'suppress_filters' => true
);
$quizzes = get_posts( $quiz_parms );
?>
<div id="setting_tabs" class="cs_ld_tabs">
    <form method="post" id="frm_ldqie">
        <div class="setting-table-wrapper">
            <table border="0">
                <thead></thead>
                <tbody>
                    <?php $setting_class->create_fields( __( "Custom Fields", "ldqie" ), "formActivated", "radio_switcher",  "", ($values["formActivated"] == 'on') ? "checked" : "", __( "Enable this option to gather data from your users before or after the quiz. All data is stored in the Quiz Statistics.", "ldqie" ), "",  "" ); ?>
                    <tr>
                        <td colspan="2">
                            <div class="custom-field-container">
                                <div class="custom-fields-wrapper">
                                    <table class="custom-field-table">
                                        <thead>
                                            <tr>
                                                <th><?php echo __( "Field name", "ldqie" ); ?></th>
                                                <th><?php echo __( "Type", "ldqie" ); ?></th>
                                                <th><?php echo __( "Required?", "ldqie" ); ?></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody class="custom-ui-sortable">
                                        <?php
                                        $custom_form_data = $values["ldqieform"];
                                        $field_type_array = array(
                                            "0" => "Text",
                                            "1" => "Textarea",
                                            "3" => "Checkbox",
                                            "7" => "Drop-Down menu",
                                            "8" => "Radio",
                                            "2" => "Number",
                                            "4" => "Email",
                                            "5" => "Yes/No",
                                            "6" => "Date",
                                        );

                                        if( !empty( $custom_form_data ) && is_array( $custom_form_data ) ) {
                                            $variation_counter = 1001;
                                            foreach ( $custom_form_data as $form_data ) {
                                                $dropdown_selected = false;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <input type="text" name="ldqieForm[<?php echo $variation_counter; ?>][fieldname]" value="<?php echo $form_data["field_name"]; ?>" class="regular-text">
                                                    </td>
                                                    <td style="position: relative;">
                                                        <select name="ldqieForm[<?php echo $variation_counter; ?>][type] class='custom-field-type'">
                                                            <?php
                                                                foreach( $field_type_array as $key => $field_type ) {
                                                                    if( $form_data["field_type"] == $key ) {
                                                                        $selected = "selected='selected'";
                                                                    } else {
                                                                        $selected = "";
                                                                    }
                                                                    ?>
                                                                    <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo __( $field_type, "ldqie" ); ?></option>
                                                                    <?php
                                                                }
                                                            ?>
                                                        </select>

                                                        <a href="#" class="editDropDown" style="<?php echo ( $form_data["field_type"] == 7 ) ? "display:inline;" : "display:none;" ?>"><?php echo __( "Edit list", "ldqie" ); ?></a>

                                                        <div class="custom-field_dropDownEditBox" style="display: none;">
                                                            <h4><?php echo __( "One entry per line", "ldqie" ); ?></h4>
                                                            <div>
                                                                <textarea rows="5" cols="50" name="ldqieForm[<?php echo $variation_counter; ?>][data]"><?php
                                                                //echo $form_data['field_data'];
                                                                    $exploded = explode( PHP_EOL, $form_data['field_data'] );
                                                                    foreach( $exploded as $explod ) {
                                                                        echo trim($explod)."\n";
                                                                    }
                                                                    ?></textarea>
                                                            </div>

                                                            <input type="button" value="<?php echo __( "OK", "ldqie" ); ?>" class="drop-down-ok button-primary">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="ldqieForm[<?php echo $variation_counter; ?>][required]" <?php echo ($form_data["required"] == "on") ? "checked" : "" ?> value="on" />
                                                    </td>
                                                    <td>
                                                        <input type="button" name="form_delete" value="Delete" class="remove-field button-secondary">
                                                        <a class="form_move button-secondary ui-sortable-handle" href="#"><?php echo __( "Move", "ldqie" ); ?></a>
                                                    </td>
                                                </tr>
                                                <?php
                                                $variation_counter++;
                                            }
                                        } else {
                                        ?>
                                        <tr>
                                            <td>
                                                <input type="text" name="ldqieForm[0][fieldname]" value="" class="regular-text">
                                            </td>
                                            <td style="position: relative;">
                                                <select name="ldqieForm[0][type] class='custom-field-type'">
                                                    <?php
                                                    $selected = "";
                                                    foreach( $field_type_array as $key => $field_type ) {
                                                        ?>
                                                        <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo __( $field_type, "ldqie" ); ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>

                                                <a href="#" class="editDropDown" style="display: none;"><?php echo __( "Edit list", "ldqie" ); ?></a>

                                                <div class="custom-field_dropDownEditBox" style="display: none;">
                                                    <h4><?php echo __( "One entry per line", "ldqie" ); ?></h4>
                                                    <div>
                                                        <textarea rows="5" cols="50" name="ldqieForm[0][data]"></textarea>
                                                    </div>

                                                    <input type="button" value="<?php echo __( "OK", "ldqie" ); ?>" class="drop-down-ok button-primary">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="checkbox" name="ldqieForm[0][required]" value="on" />
                                            </td>
                                            <td>
                                                <input type="button" name="form_delete" value="Delete" class="remove-field button-secondary">
                                                <a class="form_move button-secondary ui-sortable-handle" href="#"><?php echo __( "Move", "ldqie" ); ?></a>
                                            </td>
                                        </tr><?php
                                        }
                                        ?>
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
                                        <input type="text" name="ldqieForm[{{{data.counter}}}][fieldname]" value="" class="regular-text">
                                    </td>
                                    <td style="position: relative;">
                                        <select name="ldqieForm[{{{data.counter}}}][type]">
                                            <option value="0" selected="selected"><?php echo __( "Text", "ldqie" ); ?></option>
                                            <option value="1"><?php echo __( "Textarea", "ldqie" ); ?></option>
                                            <option value="3"><?php echo __( "Checkbox", "ldqie" ); ?></option>
                                            <option value="7"><?php echo __( "Drop-Down menu", "ldqie" ); ?></option>
                                            <option value="8"><?php echo __( "Radio", "ldqie" ); ?></option>
                                            <option value="2"><?php echo __( "Number", "ldqie" ); ?></option>
                                            <option value="4"><?php echo __( "Email", "ldqie" ); ?></option>
                                            <option value="5"><?php echo __( "Yes/No", "ldqie" ); ?></option>
                                            <option value="6"><?php echo __( "Date", "ldqie" ); ?></option>
                                        </select>

                                        <a href="#" class="editDropDown" style="display: none;"><?php echo __( "Edit list", "ldqie" ); ?></a>

                                        <div class="custom-field_dropDownEditBox" style="display: none;">
                                            <h4><?php echo __( "One entry per line", "ldqie" ); ?></h4>
                                            <div>
                                                <textarea rows="5" cols="50" name="ldqieForm[{{{data.counter}}}][data]"></textarea>
                                            </div>

                                            <input type="button" value="<?php echo __( "OK", "ldqie" ); ?>" class="drop-down-ok button-primary">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="ldqieForm[{{{data.counter}}}][required]" value="on">
                                    </td>
                                    <td>
                                        <input type="button" name="form_delete" value="Delete" class="remove-field button-secondary">
                                        <a class="form_move button-secondary ui-sortable-handle" href="#"><?php echo __( "Move", "ldqie" ); ?></a>
                                    </td>
                                </tr>
                            </script>
                            <!-- End Template -->
                        </td>
                    </tr>
                    <tr>
                        <td><span class="lbl_formShowPosition_section"><?php _e( 'Display Position', 'ldqie' ); ?></span></td>
                        <td>
                            <div class="lbl_formShowPosition_section">
                                <table>
                                    <tr><td><?php $setting_class->create_fields( "", "formShowPosition", "radio", "0", ( $values["formShowPosition"] != '1' ) ? "checked" : "", "", "", __( "On the quiz startpage", "ldqie" ) ); ?></td></tr>
                                    <tr><td><?php $setting_class->create_fields( "", "formShowPosition", "radio", "1", ( $values["formShowPosition"] == '1') ? "checked" : "", "", "", __( "At the end of the quiz (before the quiz result)", "ldqie" ) ); ?></td></tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                                                    
                    <?php $setting_class->create_fields( __( "Leaderboard", "ldqie" ), "toplistActivated", "radio_switcher",  "", ($values["toplistActivated"] == 'on') ? "checked" : "", "", "",  "" ); ?>
                    <tr>
                        <td colspan="2">
                            <div id="toplistActivated_detail" class="ldqie_sub_table">
                                <table>
                                    <tr>    
                                        <td><label for="toplistDataAddPermissions" class="label"><?php _e( 'Who can apply?', 'ldqie' ); ?></label></td>
                                        <td>
                                            <select id="toplistDataAddPermissions" class="ld-single-quiz-select2-ddl" name="toplistDataAddPermissions" style="width:300px">
                                                <option></option>
                                                <option value="1" <?php echo '1'==$values["quizRunOnceType"]?'selected':'';?>><?php _e( 'All users', 'ldqie' ); ?></option>
                                                <option value="2" <?php echo '2'==$values["quizRunOnceType"]?'selected':'';?>><?php _e( 'Registered users only', 'ldqie' ); ?></option>
                                                <option value="3" <?php echo '3'==$values["quizRunOnceType"]?'selected':'';?>><?php _e( 'Anonymous user only', 'ldqie' ); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr><?php $setting_class->create_fields( __( "Multiple Applications per user", "ldqie" ), "toplistDataAddMultiple", "radio_switcher",  "", ($values["toplistDataAddMultiple"] == 'on') ? "checked" : "", __( "", "ldqie" ), "",  "" ); ?></tr>
                                    <tr>    
                                        <td></td>
                                        <td>
                                            <div id="toplistDataAddMultiple_detail" class="ldqie_sub_table">
                                                <?php _e( 'Re-apply after', 'ldqie' ); ?>
                                                <input type="number" id="toplistDataAddBlock" name="toplistDataAddBlock" value="<?php echo $values["toplistDataAddBlock"];?>" class="" />
                                                <?php _e( 'minutes', 'ldqie' ); ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <?php $setting_class->create_fields( __( "Automatic user entry", "ldqie" ), "toplistDataAddAutomatic", "checkbox", "", ($values["toplistDataAddAutomatic"] == 'on') ? "checked" : "", "", "", "" ); ?>
                                    </tr>
                                    <tr class="toplistActivated hidden">
                                        <?php $setting_class->create_fields( __( "Number of displayed entries", "ldqie" ), "toplistDataShowLimit", "number", esc_html($values['toplistDataShowLimit']), "", "", "", "" ); ?>
                                    </tr>
                                    <tr>    
                                        <td><label for="toplistDataAddPermissions" class="label"><?php _e( 'Sort list by?', 'ldqie' ); ?></label></td>
                                        <td>
                                            <select id="toplistDataSort" class="ld-single-quiz-select2-ddl" name="toplistDataSort" style="width:300px">
                                                <option></option>
                                                <option value="1" <?php echo '1'==$values["toplistDataSort"]?'selected':'';?>><?php _e( 'Best user', 'ldqie' ); ?></option>
                                                <option value="2" <?php echo '2'==$values["toplistDataSort"]?'selected':'';?>><?php _e( 'Newest entry', 'ldqie' ); ?></option>
                                                <option value="3" <?php echo '3'==$values["toplistDataSort"]?'selected':'';?>><?php _e( 'Oldest entry', 'ldqie' ); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    
                                    <tr><?php $setting_class->create_fields( __( "Display on Quiz results page", "ldqie" ), "toplistDataShowIn_enabled", "radio_switcher",  "", ($values["toplistDataShowIn_enabled"] == 'on') ? "checked" : "", __( "", "ldqie" ), "",  "" ); ?></tr>
                                    <tr>
                                        <td colspan="2">
                                            <div id="toplistDataShowIn_enabled_detail" class="ldqie_sub_table">
                                                <table>
                                                    <tr><td>&nbsp;</td><td><?php $setting_class->create_fields( "", "toplistDataShowIn", "radio", "1", ($values["toplistDataShowIn"] == '1') ? "checked" : (($values["toplistDataShowIn"] == '') ? "checked" : ""), "", "", __( "Below the result text", "ldqie" ) ); ?></td></tr>
                                                    <tr><td>&nbsp;</td><td><?php $setting_class->create_fields( "", "toplistDataShowIn", "radio", "2", ($values["toplistDataShowIn"] == '2') ? "checked" : "", "", "", __( "In a button", "ldqie" ) ); ?></td></tr>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <?php $setting_class->create_fields( __( "Really Simple CAPTCHA", "ldqie" ), "toplistDataCaptcha", "checkbox", "", ($values["toplistDataCaptcha"] == 'on') ? "checked" : "", __( "This option requires additional plugin: Really Simple CAPTCHA", "ldqie" ), "", "" ); ?>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <?php $setting_class->create_fields( __( "Quiz Statistics", "ldqie" ), "statisticsOn", "radio_switcher",  "", ($values["statisticsOn"] == 'on') ? "checked" : "", "", "",  "" ); ?>
                    <tr>
                        <td colspan="2">
                            <div id="statisticsOn_detail" class="ldqie_sub_table">
                                <table>
                                    <tr><?php $setting_class->create_fields( __( "Front-end Profile Display", "ldqie" ), "viewProfileStatistics", "radio_switcher",  "", ($values["viewProfileStatistics"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                    <tr><?php $setting_class->create_fields( __( "Statistics IP-lock", "ldqie" ), "statisticsIpLock_enabled", "radio_switcher",  "", ($values["statisticsIpLock_enabled"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                    <tr>    
                                        <td></td>
                                        <td>
                                            <div id="statisticsIpLock_enabled_detail" class="ldqie_sub_table">
                                                <div><?php _e( 'IP-lock time limit', 'ldqie' ); ?></div>
                                                <input type="number" id="statisticsIpLock" name="statisticsIpLock" value="<?php echo $values["statisticsIpLock"];?>" class="" />
                                                <div><?php _e( 'minutes', 'ldqie' ); ?></div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>


                    <?php $setting_class->create_fields( __( "Email Notifications", "ldqie" ), "email_enabled", "radio_switcher",  "", ($values["email_enabled"] == 'on') ? "checked" : "", "", "",  "" ); ?>
                    <tr>
                        <td colspan="2">
                            <div id="email_enabled_detail" class="ldqie_sub_table">
                                <table>
                                    <tr><?php $setting_class->create_fields( __( "Admin", "ldqie" ), "email_enabled_admin", "radio_switcher",  "", ($values["email_enabled_admin"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                    <tr>    
                                        <td></td>
                                        <td> 
                                            <div id="email_enabled_admin_detail" class="ldqie_sub_table">   
                                                <div><php echo _e( "Email trigger", "ldqie" );?></div>
                                                <select id="emailNotification" class="ld-single-quiz-select2-ddl" name="emailNotification" style="width:300px">
                                                    <option></option>
                                                    <option value="2" <?php echo '2'==$values["emailNotification"]?'selected':'';?>><?php _e( 'All users', 'ldqie' ); ?></option>
                                                    <option value="1" <?php echo '1'==$values["emailNotification"]?'selected':'';?>><?php _e( 'Registered users only', 'ldqie' ); ?></option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr><?php $setting_class->create_fields( __( "User", "ldqie" ), "userEmailNotification", "radio_switcher",  "", ($values["userEmailNotification"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                </table>
                            </div>
                        </td>
                    </tr>


                    <?php $setting_class->create_fields( __( "Advanced Settings", "ldqie" ), "advanced_settings", "radio_switcher",  "", ($values["advanced_settings"] == 'on') ? "checked" : "", "", "",  "" ); ?>
                    <tr> 
                        <td colspan="2">
                            <div id="advanced_settings_detail" class="ldqie_sub_table">
                                <table>
                                    <tr><?php $setting_class->create_fields( __( "Browser Cookie Answer Protection", "ldqie" ), "timeLimitCookie_enabled", "radio_switcher",  "", ($values["timeLimitCookie_enabled"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                    <tr>    
                                        <td></td>
                                        <td> 
                                            <div id="timeLimitCookie_enabled_detail" class="ldqie_sub_table">   
                                                <div><?php _e( 'Cookie time limit', 'ldqie' ); ?></div>
                                                <input type="number" id="timeLimitCookie" name="timeLimitCookie" value="<?php echo $values["timeLimitCookie"];?>" class="" />
                                                <div><?php _e( 'seconds', 'ldqie' ); ?></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr><?php $setting_class->create_fields( __( "Associated Settings", "ldqie" ), "associated_settings_enabled", "radio_switcher",  "", ($values["associated_settings_enabled"] == 'on') ? "checked" : "", "", "",  "" ); ?></tr>
                                    <tr>    
                                        <td></td>
                                        <td> 
                                            <div id="associated_settings_enabled_detail" class="ldqie_sub_table">   
                                                <div><php _e( "Associated Quiz Database Table", "ldqie" );?></div>
                                                <select id="associated_settings" class="ld-single-quiz-select2-ddl" name="associated_settings" style="width:300px">
                                                    <option></option>
                                                    <?php 
                                                        $quiz_items = $wpdb->get_results( $wpdb->prepare( "SELECT id, name FROM " . \LDLMS_DB::get_table_name( 'quiz_master' ) . " ORDER BY %s ", 'id' ) );
                                                        if ( ! empty( $quiz_items ) ) {
                                                            foreach ( $quiz_items as $q ) {
                                                                ?>
                                                                    <option value="<?php echo $q->id;?>" <?php echo $q->id==$values["associated_settings"]?'selected':'';?>><?php echo $q->id."-".$q->name;?></option>
                                                                <?php
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
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