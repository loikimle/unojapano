<?php
namespace LDQIE;
/**
 * Plugin Settings
 *
 * @class     Settings
 * @version   1.0.0
 * @package   LDQIE/Classes/Settings
 * @category  Class
 * @author    WooNinjas
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings Class.
 */
class Settings {
    
    /**
     * Class Constructor
     */
    public function __construct() {

        add_action ( "admin_menu", array( __CLASS__, "settings_page" ), 1001 );
        add_action ( "current_screen", array( __CLASS__, "save_settings" ) );
        add_filter ( 'admin_footer_text', array( __CLASS__, 'remove_footer_admin' ) );

        add_action ( 'wp_ajax_ldqie_load_lessons', [ __CLASS__, 'ld_ldqie_load_lessons' ] );
    }
    
    function ld_ldqie_load_lessons() {
        $course_ids = $_POST['course_ids'];
        
        $results = array();
        if( is_array( $course_ids ) && count( $course_ids ) > 0 ) {

            foreach( $course_ids as $course_id ) {
                $lessons = learndash_get_lesson_list( $course_id );
                foreach( $lessons as $lesson ) {
                    $results[] = [ "id" => $lesson->ID, "text" => $lesson->post_title ];
                }
            }
        } else {
            $lessons = learndash_get_lesson_list( $course_ids );
            foreach( $lessons as $lesson) { 
                $results[] = [ "id" => $lesson->ID,"text"=> $lesson->post_title ];
            }
        }
        wp_send_json( $results );
    }

    /**
     * Save default settings
     */
    public static function save_settings() {
        $screen = get_current_screen();
        
        if( $screen->id === "ld-quiz-import_page_ldqie-settings" ) {
            if (current_user_can("manage_options")) {
                if (!empty($_POST) && check_admin_referer('ldqie_settings', 'ldqie_settings_field')) {
                    $tab = $_REQUEST['tab'];
                    if( empty( $tab ) ) {
                        $tab = 'general';
                    }
                    $form_data = get_option( "quiz_default" );
                    if( ! isset( $form_data ) || ! is_array( $form_data ) ) {
                        $form_data = array();
                    }
                    switch( $tab ) {
                        case "general":
                            $form_data["text"] = sanitize_text_field( $_POST["text"] );
                            break;
                        case "quiz_access":
                            $form_data["course"] = sanitize_text_field( $_POST["course"] );
                            $form_data["lesson"] = sanitize_text_field( $_POST["lesson"] );
                            $form_data["prerequisiteList"] = array_map( 'esc_attr', isset( $_POST["prerequisiteList"] ) ? $_POST["prerequisiteList"] : "" );
                            $form_data["startOnlyRegisteredUser"] = isset( $_POST["startOnlyRegisteredUser"] ) ? $_POST["startOnlyRegisteredUser"] : "";
                            break;
                        case "prog_and_restrict":
                            $form_data["passingpercentage"]         = sanitize_text_field( $_POST["passingpercentage"] );
                            $form_data["certificate"]          = sanitize_text_field( $_POST["certificate"] );
                            if( ! empty( $form_data["certificate"] ) ) {
                                $form_data["threshold"] = sanitize_text_field( $_POST["threshold"] );
                            } else {
                                $form_data["threshold"] = '';
                            }
                            
                            $form_data["retry_restrictions"]        = sanitize_text_field( $_POST["retry_restrictions"] );///
                            if( isset( $form_data["retry_restrictions"] ) && $form_data["retry_restrictions"]=='on' ) {
                                $form_data["repeats"]           = sanitize_text_field( $_POST["repeats"] );
                                $form_data["quizRunOnceType"]   = sanitize_text_field( $_POST["quizRunOnceType"] );
                                
                                if( $form_data["quizRunOnceType"] != '2' ) {
                                    $form_data["quizRunOnceCookie"]   = isset( $_POST["quizRunOnceCookie"] ) ? 'on' : "";
                                } else {
                                    $form_data["quizRunOnceCookie"]   = '';    
                                }
                            } else {
                                $form_data["repeats"]           = '';
                                $form_data["quizRunOnceType"]   = '';
                                $form_data["quizRunOnceCookie"]   = '';
                            }

                            $form_data["forcingQuestionSolve"]      = sanitize_text_field( $_POST["forcingQuestionSolve"] );
                            $form_data["quiz_time_limit_enabled"]   = sanitize_text_field( $_POST["quiz_time_limit_enabled"] );///
                            if( isset( $form_data["quiz_time_limit_enabled"] ) && $form_data["quiz_time_limit_enabled"]=='on' ) {
                                $form_data["timeLimit"]                 = array_map( 'esc_attr',  isset( $_POST["timeLimit"] ) ? $_POST["timeLimit"] : "" );
                            } else {
                                $form_data["timeLimit"] = '';
                            }
                            
                            break;
                        case "display_n_content":
                            $form_data["quiz_materials_enabled"]    = sanitize_text_field( $_POST["quiz_materials_enabled"] );
                            if( $form_data["quiz_materials_enabled"]   ==  'on' ) {
                                $form_data["quiz_materials"]            = sanitize_text_field( $_POST["quiz_materials"] );
                            } else {
                                $form_data["quiz_materials"]            = '';
                            }
                            $form_data["quizModus"] = ( isset( $_POST["quizModus"] ) && !empty( $_POST["quizModus"] ) ) ? intval($_POST["quizModus"]) : "0";
                            if( intval($_POST["quizModus"])==3 ) {
                                $form_data["quizModus_multiple_questionsPerPage"] = ( isset( $_POST["quizModus_multiple_questionsPerPage"] ) && !empty( $_POST["quizModus_multiple_questionsPerPage"] ) ) ? $_POST["quizModus_multiple_questionsPerPage"] : "";///
                                $form_data["quizModus_single_feedback"]     = '';
                                $form_data["quizModus_single_back_button"] = '';
                            } if( intval($_POST["quizModus"])==0 ) {
                                $form_data["quizModus_multiple_questionsPerPage"] = '';
                                $form_data["quizModus_single_feedback"]     = ( isset( $_POST["quizModus_single_feedback"] ) && !empty( $_POST["quizModus_single_feedback"] ) ) ? $_POST["quizModus_single_feedback"] : "";///
                                $form_data["quizModus_single_back_button"]  = ( isset( $_POST["quizModus_single_back_button"] ) && !empty( $_POST["quizModus_single_back_button"] ) ) ? trim($_POST["quizModus_single_back_button"]) : "";///
                            }
                            
                            
                            $form_data["autostart"]                 = sanitize_text_field( $_POST["autostart"] );
                            $form_data["showReviewQuestion"]        = sanitize_text_field( $_POST["showReviewQuestion"] );
                            if( $form_data["showReviewQuestion"]=='on' ) {
                                $form_data["quizSummaryHide"]        = sanitize_text_field( $_POST["quizSummaryHide"] );
                                $form_data["skipQuestionDisabled"]   = sanitize_text_field( $_POST["skipQuestionDisabled"] );
                            } else {
                                $form_data["quizSummaryHide"]     = '';
                                $form_data["skipQuestionDisabled"]     = '';
                            }
                            
                            $form_data["custom_sorting"]        = sanitize_text_field( $_POST["custom_sorting"] );///
                            if( $form_data["custom_sorting"]   ==  'on' ) {
                                $form_data["sortCategories"]   = sanitize_text_field( $_POST["sortCategories"] );
                                $form_data["questionRandom"]   = sanitize_text_field( $_POST["questionRandom"] );
                                $form_data["showMaxQuestion"]  = sanitize_text_field( $_POST["showMaxQuestion"] );
                                $form_data["showMaxQuestionValue"]  = sanitize_text_field( $_POST["showMaxQuestionValue"] );

                            } else {
                                $form_data["sortCategories"]            = '';
                                $form_data["questionRandom"]            = '';
                                $form_data["showMaxQuestion"]           = '';
                                $form_data["showMaxQuestionValue"]      = '';
                            }
                            $form_data["custom_question_elements"]        = sanitize_text_field( $_POST["custom_question_elements"] );///
                            if( $form_data["custom_question_elements"]=='on' ) {
                                $form_data["showPoints"]   = sanitize_text_field( $_POST["showPoints"] );
                                $form_data["showCategory"]   = sanitize_text_field( $_POST["showCategory"] );
                                $form_data["hideQuestionPositionOverview"]   = sanitize_text_field( $_POST["hideQuestionPositionOverview"] );
                                $form_data["hideQuestionNumbering"]   = sanitize_text_field( $_POST["hideQuestionNumbering"] );
                                $form_data["numberedAnswer"]   = sanitize_text_field( $_POST["numberedAnswer"] );
                                $form_data["answerRandom"]   = sanitize_text_field( $_POST["answerRandom"] );
                            } else {
                                $form_data["showPoints"]     = '';
                                $form_data["showCategory"]     = '';
                                $form_data["hideQuestionPositionOverview"]     = '';
                                $form_data["hideQuestionNumbering"]     = '';
                                $form_data["numberedAnswer"]     = '';
                                $form_data["answerRandom"]     = ''; 
                            }
                            
                            $form_data["titleHidden"]        = sanitize_text_field( $_POST["titleHidden"] );
                            break;
                        case "results_page_display":
                            $form_data["quiz_result_messages"] = ( isset( $_POST["quiz_result_messages"] ) && ! empty( $_POST["quiz_result_messages"] ) ) ? $_POST["quiz_result_messages"] : "";///
                            if( $form_data["quiz_result_messages"] == 'on' ) {
                                $resultText = ( isset( $_POST["resultText"] ) && !empty( $_POST["resultText"] ) ) ? $_POST["resultText"] : "";
                                $resultTextGrade = ( isset( $_POST["resultTextGrade"] ) && !empty( $_POST["resultTextGrade"] ) ) ? $_POST["resultTextGrade"] : "";
                                $resultactiv = ( isset( $_POST["resultactiv"] ) && !empty( $_POST["resultactiv"] ) ) ? $_POST["resultactiv"] : array();
                                $form_data["result_text"] = [ 'text'=>$resultText, 'prozent'=>$resultTextGrade, 'activ' => $resultactiv ];
                            } else{
                                $form_data["result_text"] = [];
                            }
                            $form_data["resultGradeEnabled"] = ( isset( $_POST["resultGradeEnabled"] ) && ! empty( $_POST["resultGradeEnabled"] ) ) ? $_POST["resultGradeEnabled"] : "";
                            if( $form_data["resultGradeEnabled"] == 'on' ) {
                                
                                $resultText = ( isset( $_POST["resultText"] ) && !empty( $_POST["resultText"] ) ) ? $_POST["resultText"] : "";
                                $resultTextGrade = ( isset( $_POST["resultTextGrade"] ) && !empty( $_POST["resultTextGrade"] ) ) ? $_POST["resultTextGrade"] : "";
                                $form_data["result_text"] = [ 'text'=>$resultText, 'prozent'=>$resultTextGrade ];

                            } else {
                                $form_data["resultText"] = ( isset( $_POST["resultTextsingle"] ) && !empty( $_POST["resultTextsingle"] ) ) ? $_POST["resultTextsingle"] : "";
                                $form_data["resultTextGrade"] = ( isset( $_POST["resultTextGrade"] ) && !empty( $_POST["resultTextGrade"] ) ) ? floatval($_POST["resultTextGrade"]) : "";
                            }

                            $form_data["btnRestartQuizHidden"]          = sanitize_text_field( $_POST["btnRestartQuizHidden"] );
                            
                            $form_data["custom_result_data_display"]    = sanitize_text_field( $_POST["custom_result_data_display"] );
                            if( $form_data["custom_result_data_display"] == 'on' ) {
                                $form_data["showAverageResult"] = ( isset( $_POST["showAverageResult"] ) && ! empty( $_POST["showAverageResult"] ) ) ? $_POST["showAverageResult"] : "";    
                                $form_data["showCategoryScore"] = ( isset( $_POST["showCategoryScore"] ) && ! empty( $_POST["showCategoryScore"] ) ) ? $_POST["showCategoryScore"] : "";    
                                $form_data["hideResultPoints"] = ( isset( $_POST["hideResultPoints"] ) && ! empty( $_POST["hideResultPoints"] ) ) ? $_POST["hideResultPoints"] : "";    
                                $form_data["hideResultCorrectQuestion"] = ( isset( $_POST["hideResultCorrectQuestion"] ) && ! empty( $_POST["hideResultCorrectQuestion"] ) ) ? $_POST["hideResultCorrectQuestion"] : "";    
                                $form_data["hideResultQuizTime"] = ( isset( $_POST["hideResultQuizTime"] ) && ! empty( $_POST["hideResultQuizTime"] ) ) ? $_POST["hideResultQuizTime"] : "";    
                            } else {
                                $form_data["showAverageResult"] = '';
                                $form_data["showCategoryScore"] = '';
                                $form_data["hideResultPoints"] = '';
                                $form_data["hideResultCorrectQuestion"] = '';
                                $form_data["hideResultQuizTime"] = '';
                            }
                            
                            $form_data["custom_answer_feedback"]    = sanitize_text_field( $_POST["custom_answer_feedback"] );///
                            if( $form_data["custom_answer_feedback"] == 'on' ) {
                                $form_data["hideAnswerMessageBox"] = ( isset( $_POST["hideAnswerMessageBox"] ) && ! empty( $_POST["hideAnswerMessageBox"] ) ) ? $_POST["hideAnswerMessageBox"] : "";    
                                $form_data["disabledAnswerMark"] = ( isset( $_POST["disabledAnswerMark"] ) && ! empty( $_POST["disabledAnswerMark"] ) ) ? $_POST["disabledAnswerMark"] : "";    
                                $form_data["btnViewQuestionHidden"] = ( isset( $_POST["btnViewQuestionHidden"] ) && ! empty( $_POST["btnViewQuestionHidden"] ) ) ? $_POST["btnViewQuestionHidden"] : "";    
                            } else {
                                $form_data["hideAnswerMessageBox"] = '';
                                $form_data["disabledAnswerMark"] = '';
                                $form_data["btnViewQuestionHidden"] = '';
                            }
                            break;                        
                        case "admin_data_handling":
                            $form_data["formActivated"]    = sanitize_text_field( $_POST["formActivated"] );
                            if( $form_data["formActivated"] == 'on' ) {
                                $form_fields = array();
                                $form_data["ldqieform"] = array();
                                if( isset($_POST["ldqieForm"]) ) {
                                    $ldqie_custom_form = $_POST["ldqieForm"];
                                    if( is_array( $ldqie_custom_form ) ) {
                                        foreach( $ldqie_custom_form as $custom_form ) {
                                            $form_fields[] =  array(
                                                'field_name' => ( isset( $custom_form["fieldname"] ) && ! empty( $custom_form["fieldname"] ) ? sanitize_text_field( $custom_form["fieldname"] ) : "" ),
                                                'field_type' => ( isset( $custom_form["type"] ) && ! empty( $custom_form["type"] ) ? sanitize_text_field( $custom_form["type"] ) : "" ),
                                                'field_data' => ( isset( $custom_form["data"] ) && ! empty( $custom_form["data"] ) ? $custom_form["data"] : "" ),
                                                'required' => ( isset( $custom_form["required"] ) && "on" == $custom_form["required"]  ? "on" : "off" )
                                            );
                                        }
                                    }
                                }
                                $form_data["ldqieform"] = $form_fields;
                                $form_data["formShowPosition"] = ( isset( $_POST["formShowPosition"] ) && ! empty( $_POST["formShowPosition"] ) ) ? $_POST["formShowPosition"] : "0";    
                            } else {
                                $form_data["ldqieform"] = array();
                                $form_data["formShowPosition"] = '0';
                            }
                            $form_data["toplistActivated"]    = sanitize_text_field( $_POST["toplistActivated"] );
                            if( $form_data["toplistActivated"] == 'on' ) {
                                
                                $form_data["toplistDataAddPermissions"] = ( isset( $_POST["toplistDataAddPermissions"] ) && ! empty( $_POST["toplistDataAddPermissions"] ) ) ? $_POST["toplistDataAddPermissions"] : "";    
                                $form_data["toplistDataAddMultiple"] = ( isset( $_POST["toplistDataAddMultiple"] ) && ! empty( $_POST["toplistDataAddMultiple"] ) ) ? $_POST["toplistDataAddMultiple"] : "";    
                                if( $form_data["toplistDataAddMultiple"] == 'on' ) {
                                    $form_data["toplistDataAddBlock"] = ( isset( $_POST["toplistDataAddBlock"] ) && ! empty( $_POST["toplistDataAddBlock"] ) ) ? $_POST["toplistDataAddBlock"] : "";                
                                } else {
                                    $form_data["toplistDataAddBlock"] = ''; 
                                }

                                $form_data["toplistDataAddAutomatic"] = ( isset( $_POST["toplistDataAddAutomatic"] ) && ! empty( $_POST["toplistDataAddAutomatic"] ) ) ? $_POST["toplistDataAddAutomatic"] : "";    
                                $form_data["toplistDataSort"] = ( isset( $_POST["toplistDataSort"] ) && ! empty( $_POST["toplistDataSort"] ) ) ? $_POST["toplistDataSort"] : "";    
                                $form_data["toplistDataShowLimit"] = ( isset( $_POST["toplistDataShowLimit"] ) && ! empty( $_POST["toplistDataShowLimit"] ) ) ? $_POST["toplistDataShowLimit"] : "";    
                               

                                $form_data["toplistDataShowIn_enabled"] = ( isset( $_POST["toplistDataShowIn_enabled"] ) && ! empty( $_POST["toplistDataShowIn_enabled"] ) ) ? $_POST["toplistDataShowIn_enabled"] : ""; ///   
                                if( $form_data["toplistDataShowIn_enabled"] == 'on' ) {
                                    $form_data["toplistDataShowIn"] = ( isset( $_POST["toplistDataShowIn"] ) && ! empty( $_POST["toplistDataShowIn"] ) ) ? $_POST["toplistDataShowIn"] : "1";                
                                } else {
                                    $form_data["toplistDataShowIn"] = '';
                                }                             

                                $form_data["toplistDataCaptcha"] = ( isset( $_POST["toplistDataCaptcha"] ) && ! empty( $_POST["toplistDataCaptcha"] ) ) ? $_POST["toplistDataCaptcha"] : "";    ///
                            } else {
                                $form_data["toplistDataAddPermissions"] = '';
                                $form_data["toplistDataAddMultiple"] = '';
                                $form_data["toplistDataAddBlock"] = '';
                                $form_data["toplistDataAddAutomatic"] = '';
                                $form_data["toplistDataSort"] = '';
                                $form_data["toplistDataShowLimit"] = '';
                                $form_data["toplistDataShowIn_enabled"] = '';
                                $form_data["toplistDataShowIn"] = '';
                                $form_data["toplistDataCaptcha"] = '';
                            }
                        
                            $form_data["statisticsOn"]    = sanitize_text_field( $_POST["statisticsOn"] );
                            if( $form_data["statisticsOn"] == 'on' ) {
                                $form_data["statisticsIpLock_enabled"] = ( isset( $_POST["statisticsIpLock_enabled"] ) && ! empty( $_POST["statisticsIpLock_enabled"] ) ) ? $_POST["statisticsIpLock_enabled"] : "";///
                                $form_data["viewProfileStatistics"] = ( isset( $_POST["viewProfileStatistics"] ) && ! empty( $_POST["viewProfileStatistics"] ) ) ? $_POST["viewProfileStatistics"] : "";
                                if( $form_data["viewProfileStatistics"] == 'on' ) {
                                    $form_data["statisticsIpLock"] = ( isset( $_POST["statisticsIpLock"] ) && ! empty( $_POST["statisticsIpLock"] ) ) ? $_POST["statisticsIpLock"] : "0";
                                } else {
                                    $form_data["statisticsIpLock"] = '';
                                }
                            } else {
                                $form_data["statisticsOn"] = '';
                                $form_data["statisticsIpLock_enabled"] = '';
                                $form_data["viewProfileStatistics"] = '';
                                $form_data["statisticsIpLock"] = '';
                            }
                            
                            $form_data["email_enabled"]    = sanitize_text_field( $_POST["email_enabled"] );///
                            if( $form_data["email_enabled"] == 'on' ) {
                                $form_data["userEmailNotification"] = ( isset( $_POST["userEmailNotification"] ) && ! empty( $_POST["userEmailNotification"] ) ) ? $_POST["userEmailNotification"] : "";
                                $form_data["email_enabled_admin"] = ( isset( $_POST["email_enabled_admin"] ) && ! empty( $_POST["email_enabled_admin"] ) ) ? $_POST["email_enabled_admin"] : "";///
                                if( $form_data["email_enabled_admin"] == 'on' ) {
                                    $form_data["emailNotification"] = ( isset( $_POST["emailNotification"] ) && ! empty( $_POST["emailNotification"] ) ) ? $_POST["emailNotification"] : "0";
                                } else {
                                    $form_data["emailNotification"] = '';
                                }
                            } else {
                                $form_data["userEmailNotification"] = '';
                                $form_data["email_enabled_admin"] = '';
                                $form_data["emailNotification"] = '';
                            }

                            $form_data["advanced_settings"]    = sanitize_text_field( $_POST["advanced_settings"] );
                            if( $form_data["advanced_settings"] == 'on' ) {
                                $form_data["timeLimitCookie_enabled"] = ( isset( $_POST["timeLimitCookie_enabled"] ) && ! empty( $_POST["timeLimitCookie_enabled"] ) ) ? $_POST["timeLimitCookie_enabled"] : "";
                                if( $form_data["timeLimitCookie_enabled"] == 'on' ) {
                                    $form_data["timeLimitCookie"] = ( isset( $_POST["timeLimitCookie"] ) && ! empty( $_POST["timeLimitCookie"] ) ) ? $_POST["timeLimitCookie"] : "0";
                                } else {
                                    $form_data["timeLimitCookie"] = '';
                                }
                                
                                $form_data["associated_settings_enabled"] = ( isset( $_POST["associated_settings_enabled"] ) && ! empty( $_POST["associated_settings_enabled"] ) ) ? $_POST["associated_settings_enabled"] : "";
                                if( $form_data["associated_settings_enabled"] == 'on' ) {
                                    $form_data["associated_settings"] = ( isset( $_POST["associated_settings"] ) && ! empty( $_POST["associated_settings"] ) ) ? $_POST["associated_settings"] : "0";
                                } else {
                                    $form_data["associated_settings"] = '';
                                }
                            }
                            break;                            
                        case "plugin-options":
                            $form_data["DiscardOldQuestion"] = ( isset( $_POST["DiscardOldQuestion"] ) && ! empty( $_POST["DiscardOldQuestion"] ) ) ? 'yes' : "no";
                            $form_data["DiscardQuizQuestion"] = ( isset( $_POST["DiscardQuizQuestion"] ) && ! empty( $_POST["DiscardQuizQuestion"] ) ) ? 'yes' : "no";
                            $form_data["AllowExistingQuestionImport"] = ( isset( $_POST["AllowExistingQuestionImport"] ) && ! empty( $_POST["AllowExistingQuestionImport"] ) ) ? 'yes' : "no";

                            if ( current_user_can( 'manage_options' ) ) {
                                $form_data["minimum_role_to_administer"] = sanitize_text_field( $_POST["minimum_role_to_administer"] );
                                $form_data["allow_quiz_publish"] = sanitize_text_field( $_POST["allow_quiz_publish"] );
                            }
                            break; 
                        
                    }
                    update_option( "quiz_default", $form_data );
                    add_action( "ldqie_before_tabs", array( __CLASS__, "save_settings_notification" ) );
                }
            }
        }
    }

    /**
     * Setting notification
     */
    public function save_settings_notification() {
        ?>
        <div class="hidden notice ldqie-notice-success is-dismissible">
            <p><?php _e( "Settings saved", "ldqie" ); ?></p>
        </div> <?php
    }

    /**
     * Fields Generator
     *
     * @param string $label
     * @param $name
     * @param $field_type
     * @param string $field_value
     * @param string $hint
     * @param string $before_text
     * @param string $after_text
     */
    public function create_fields( $label = "", $name, $field_type, $field_value = "", $checked = "", $hint = "", $before_text = "", $after_text = "" ) {

        if( empty( $field_type ) || is_null( $field_type ) ) return;
        if( empty( $name ) || is_null( $name ) ) return;

        if( "checkbox" === $field_type ) {
            echo "<td>";
            if( !empty( $label ) ) {
                echo "<label for='". $name ."' class='label'>". $label . "</label>";
            } else {
                echo "&nbsp;";
            }
            echo "</td>";
            echo "<td>";
            echo $before_text . " <input type='" . $field_type . "' ". $checked ."  class='checkbox' id='". $name ."' name='" . $name . "' /> " .$after_text;
            if( !empty( $hint ) ) {
                echo "<span class='hint'>". $hint ."</span>";
            }
            echo "</td>";
        } elseif( "text" === $field_type || "number" === $field_type ) {
            echo "<td>";
            if( !empty( $label ) ) {
                echo "<label for='". $name ."' class='label'>". $label . "</label>";
            } else {
                echo "&nbsp;";
            }
            echo "</td>";
            echo "<td>";
            $description_text = ( empty( $field_value ) ? "" : $field_value );
            echo $before_text . " <input type='" . $field_type . "' id='". $name ."' value='" . $description_text . "' name='" . $name . "' /> " .$after_text;
            if( !empty( $hint ) ) {
                echo "<span class='hint'>". $hint ."</span>";
            }
            echo "</td>";
        } elseif( "textarea" === $field_type ) {
            if( !empty( $label ) ) {
                echo "<label for='". $name ."' class='label-textarea'>". $label . "</label>";
            }
            echo $before_text . " <textarea id='". $name ."' cols='70' rows='7' name='" . $name . "' />".$field_value."</textarea> " .$after_text;
            if( !empty( $hint ) ) {
                echo "<span class='hint'>". $hint ."</span>";
            }
        } elseif( "radio" === $field_type ) {
            echo $before_text . " <input type='" . $field_type . "' ". $checked ." class='". $name ."' value='" . $field_value . "' name='" . $name . "' /> " .$after_text;
            if( !empty( $hint ) ) {
                echo "<span class='hint'>". $hint ."</span>";
            }
        } elseif( "radio_switcher" === $field_type ) {
            echo "<td>";
            if( !empty( $label ) ) {
                echo "<label for='". $name ."' class='label'>". $label . "</label>";
            } else {
                echo "&nbsp;";
            }
            echo "</td>";
            echo "<td>";

            echo '<label class="ldqie-switch ">';
            echo $before_text . " <input type='checkbox' ". $checked ."  class='checkbox' id='". $name ."' name='" . $name . "' /> " .$after_text;
            echo '<span class="slider round"></span>';
            echo '</label>';

            
            if( !empty( $hint ) ) {
                echo "<span class='hint'>". $hint ."</span>";
            }
            echo "</td>";
        }
    }

    /**
     * Add Setting Page
     */
    public static function settings_page() {

        $values = get_option( "quiz_default" );
        $minimum_role = '';
        if( isset( $values[ 'minimum_role_to_administer' ] ) )
            $minimum_role = trim( $values[ 'minimum_role_to_administer' ] );
        if( empty( $minimum_role ) ) {
            $minimum_role = "manage_options";
        }

        /**
         * Add Setting Page
         */
        add_submenu_page(
            "ldqie-quiz-import",
            "Quiz Import/Export Default Settings",
            "Import/Export Settings",
            $minimum_role,
            "ldqie-settings",
            array( __CLASS__, ( "settings_data" ) )
        );
    }

    /**
     * Setting Page Content
     */
    public static function settings_data() {
        $page_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

        $settings_sections = array(
            // 'general_options' => array(
            //     'title' => __( 'General Options', 'ldqie' ),
            //     'icon' => 'fa',
            // ),
            'general' => array(
                'title' => __( 'General Options', 'ldqie' ),
                'icon' => 'fa',
            ),
            
            'quiz_access' => array(
                'title' => __( 'Quiz Access', 'ldqie' ),
                'icon' => 'fa',
            ),
            'prog_and_restrict' => array(
                'title' => __( 'Progression and Restriction', 'ldqie' ),
                'icon' => 'fa',
            ),
            'display_n_content' => array(
                'title' => __( 'Display and Content', 'ldqie' ),
                'icon' => 'fa',
            ),
            'results_page_display' => array(
                'title' => __( 'Result Page Display', 'ldqie' ),
                'icon' => 'fa',
            ),
            'admin_data_handling' => array(
                'title' => __( 'Administrative and Data Handling', 'ldqie' ),
                'icon' => 'fa',
            ),


            // 'question_options' => array(
            //     'title' => __( 'Question Options', 'ldqie' ),
            //     'icon' => 'fa',
            // ),
            // 'result_options' => array(
            //     'title' => __( 'Result Options', 'ldqie' ),
            //     'icon' => 'fa',
            // ),
            // 'quiz_mode' => array(
            //     'title' => __( 'Quiz Mode', 'ldqie' ),
            //     'icon' => 'fa',
            // ),
            // 'leaderboard' => array(
            //     'title' => __( 'Leaderboard', 'ldqie' ),
            //     'icon' => 'fa',
            // ),
            // 'results_text' => array(
            //     'title' => __( 'Result Text', 'ldqie' ),
            //     'icon' => 'fa',
            // ),
            // 'quiz_custom_fields' => array(
            //     'title' => __( 'Quiz Custom Fields', 'ldqie' ),
            //     'icon' => 'fa',
            // ),



            'status' => array(
                'title' => __( 'Status', 'ldqie' ),
                'icon' => 'fa',
            ),
            'plugin-options' => array(
                'title' => __( 'Plugin Options', 'ldqie' ),
                'icon' => 'fa',
            ),

            
        );

        $settings_sections = apply_filters( 'ld_quiz_settings_sections', $settings_sections );
        
        ?>

        <div class="wrap" >
            
            <div id="icon-options-general" class="icon32"></div>
            <h2><?php _e( 'Settings ', 'ldqie' ); ?></h2>
            <div class="nav-tab-wrapper">
                <?php
                    
                    foreach( $settings_sections as $key => $section ) {
                        ?>
                            <a href="?page=ldqie-settings&tab=<?php echo $key; ?>" class="nav-tab <?php echo $page_tab == $key ? 'nav-tab-active' : ''; ?>">
                                <i class="fa <?php echo $section['icon']; ?>" aria-hidden="true"></i> <?php _e( $section['title'], 'ldqie' ); ?>
                            </a>
                        <?php
                    }
                ?>
            </div>
            <?php
                foreach( $settings_sections as $key => $section ) {
                    if( $page_tab == $key ) {
                        $key = str_replace( '_', '-', $key );
                        include( 'tabs/' . $key . '.php' );
                    }
                }
            ?>
        </div>
        <?php
    }

    /**
     * Add footer branding
     *
     * @param $footer_text
     * @return mixed
     */
    public static function remove_footer_admin ( $footer_text ) {
        if( isset( $_GET["page"] ) && ( $_GET["page"] == "ldqie-quiz-import" || $_GET["page"] == "ldqie-settings" ) ){
            _e('Fueled by <a href="http://www.wordpress.org" target="_blank">WordPress</a> | developed and designed by <a href="https://wooninjas.com" target="_blank">The WooNinjas</a></p>');
        } else {
            return $footer_text;
        }
    }
}