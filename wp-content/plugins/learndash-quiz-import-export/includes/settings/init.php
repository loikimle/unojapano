<?php
namespace LDQIE;
/**
 * Plugin Settings
 *
 * @class     Settings
 * @version   2.5
 * @package   LDQIE/Classes/Settings
 * @category  Class
 * @author    WooNinjas
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Autoload classes for the plugin
spl_autoload_register ( __NAMESPACE__ . "\Main::autoloader" );

/**
 * Class Main for plugin initiation
 */
final class Main {
    public static $version = "3.1";

    // Main instance
    protected static $_instance = null;
    private static $pages = ["ld-quiz-import_page_ldqie-settings", "toplevel_page_ldqie-quiz-import", "edit-sfwd-quiz" ];
    protected function __construct () {
        register_activation_hook ( __FILE__, array ( $this, "activation" ) );
        register_deactivation_hook ( __FILE__, array ( $this, "deactivation" ) );

        // Upgrade
        add_action ( "plugins_loaded", array ( $this, "upgrade" ) );

        add_action ( "before_delete_post", array ( $this, "remove_question_on_delete_quiz" ),10, 1 );

        if (! empty($GLOBALS['pagenow']) && 'edit.php' === $GLOBALS['pagenow'])
            add_action('admin_init', array( $this, 'confirm_remove_question_on_delete_quiz' ), 10, 1);

        // Adding settings tab
        add_filter( "plugin_action_links_" . plugin_basename( DIR_FILE ), function( $links ) {
            return array_merge( $links, array(
                sprintf(
                    '<a href="%s">Settings</a>',
                    admin_url( "admin.php?page=ldqie-settings" )
                ),
            ));
        });
        
        add_action ( "admin_enqueue_scripts", array ( $this, "admin_enqueue_scripts" ) );
        add_action( 'admin_menu', array( $this, 'ld_remove_extra_admin_menu' ), 9999 );
        add_filter( 'user_has_cap', array( $this, 'ld_add_extra_caps' ), 9999 );
        new Mapping_Import();
        new LDQIE_Import();
        new LDQIE_Export();
        $GLOBALS['ldqie_options'] = new Settings();
        new LDQIE_License();

        $this->load_initial_default_values();
    }
    
    /**
     * Adds the extra capabilities to manage the quiz/question
     * 
     * @param $allcaps
     * 
     * @return $allcaps
     */
    function ld_add_extra_caps($allcaps){

        $values = get_option( "quiz_default" );
        if( isset( $values['minimum_role_to_administer'] ) && !empty( trim( $values['minimum_role_to_administer'] ) ) && trim( $values['minimum_role_to_administer'] ) != 'manage_options' ) {
            $user = new \WP_User( get_current_user_id() );
            if( ! in_array( 'administrator', $user->roles ) ) {
                $editor_role_test = trim( $values['minimum_role_to_administer'] );
                if( trim( $values['minimum_role_to_administer'] ) == 'publish_posts' ) {
                    $editor_role_test = 'delete_others_posts';
                } 
                
                if( array_key_exists( $values['minimum_role_to_administer'], $allcaps ) || array_key_exists( $editor_role_test, $allcaps )  ) {
                    $allcaps[ 'read_course' ] = 1;
                    if( isset( $values['allow_quiz_publish'] ) && $values["allow_quiz_publish"] != "no" )
                        $allcaps[ 'publish_courses' ] = 1;
                    $allcaps[ 'edit_published_courses' ] = 1;
                    $allcaps[ 'delete_published_courses' ] = 1;
                    $allcaps[ 'edit_courses' ] = 1;
                    $allcaps[ 'wpProQuiz_add_quiz' ] = 1;
                    $allcaps[ 'wpProQuiz_edit_quiz' ] = 1;
                    $allcaps[ 'wpProQuiz_delete_quiz' ] = 1;
                    $allcaps[ 'wpProQuiz_show' ] = 1;
                }
            }
        }
    
        return $allcaps;
    }  

    /**
     * Remove the extra menu items from the menu
     * 
     * @return none
     */
    function ld_remove_extra_admin_menu() {
        $values = get_option( "quiz_default" );
        if( isset( $values['minimum_role_to_administer'] ) && !empty( trim( $values['minimum_role_to_administer'] ) ) && trim( $values['minimum_role_to_administer'] ) != 'manage_options' ) {
            $user = new \WP_User( get_current_user_id() );
            if( ! in_array( 'administrator', $user->roles ) ) {
                
                $editor_role_test = trim( $values['minimum_role_to_administer'] );
                if( trim( $values['minimum_role_to_administer'] ) == 'publish_posts' ) {
                    $editor_role_test = 'delete_others_posts';
                }
                
                if( array_key_exists( $values['minimum_role_to_administer'], $user->allcaps ) ||  array_key_exists( $editor_role_test, $user->allcaps )  ) {
                    remove_submenu_page( 'learndash-lms', 'edit.php?post_type=sfwd-courses' );
                    remove_submenu_page( 'learndash-lms', 'edit.php?post_type=sfwd-lessons' );
                    remove_submenu_page( 'learndash-lms', 'edit.php?post_type=sfwd-topic' );
                    remove_submenu_page( 'learndash-lms', 'edit.php?post_type=sfwd-certificates' );
                }
            }
        }
    }

    /**
     * Remove attached question when quiz is deleted from the database
     * 
     * @param $post_id
     * 
     * @return none
     */
    public function remove_question_on_delete_quiz( $post_id ) {
        
        $post_type = get_post_type( $post_id );
        if( $post_type == 'sfwd-quiz' ) {
            $questions = get_post_meta( $post_id, 'ld_quiz_questions', true );
            if( is_array( $questions ) && count( $questions ) > 0 ) {
                $questionMapper = new \WpProQuiz_Model_QuestionMapper();
                foreach( $questions as $question_post_id=>$question_id ) {
                    $questionMapper->delete($question_id);
                    wp_delete_post( $question_post_id );
                }
            }
        }
        
    }
    
    /**
     * Displays a confirmation dialog before deleting the quiz.
     */
    function confirm_remove_question_on_delete_quiz(  ) {
     
        if( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sfwd-quiz' ) {
            if( isset( $_GET['post_status'] ) && $_GET['post_status'] == 'trash' ) {
                $values = get_option( "quiz_default" );
                if( $values["DiscardQuizQuestion"] == "yes" ) {
                    wp_enqueue_script ( "ldqie-admin-quiz-confirmation-js", ASSETS_URL . "js/ldqie-admin-quiz-confirmation.js" );
                    wp_localize_script( 'ldqie-admin-quiz-confirmation-js', 'ldqieConfQuizVars', array( 'message' => __( 'Are you sure? This action will delete all of the associated quiz question too. You can turn off this feature from the "LD Quiz Import -> Settings -> Plugin Options" page.', "ldqie" ) ) );
                }
            }
        }
    }

    /**
     * Install default quiz settings even if user deletes it..
     */
    public static function load_initial_default_values() {
        
        $default_values = get_option( "quiz_default" );

        if( empty( $default_values ) ) {
            $form_data = array();
            $form_data["quizModus_multiple_questionsPerPage"] = "0"; 
            $form_data["formShowPosition"] = "0"; 
            $form_data["course"] = ""; 
            $form_data["lesson"] = "";
            $form_data["retry_restrictions"] = "";
            $form_data["repeats"] = "";
            $form_data["passingpercentage"] = "";
            $form_data["certificate"] = "";
            $form_data["threshold"] = "";
            $form_data["quiz_time_limit_enabled"] = "";
            $form_data["quiz_materials_enabled"] = "";
            $form_data["quiz_materials"] = "";
            $form_data["custom_sorting"] = "";
            $form_data["quizModus_single_back_button"] = "";
            $form_data["quizModus_single_feedback"] = "";
            $form_data["quiz_result_messages"] = "";
            $form_data["custom_answer_feedback"] = "";
            $form_data["custom_result_data_display"] = "";
            $form_data["associated_settings_enabled"] = "";
            $form_data["associated_settings"] = "";
            $form_data["toplistDataShowIn_enabled"] = "";
            $form_data["statisticsIpLock_enabled"] = "";
            $form_data["email_enabled"] = "";
            $form_data["email_enabled_admin"] = "";
            $form_data["timeLimitCookie_enabled"] = "";
            $form_data["advanced_settings"] = "";
            $form_data["toplistDataCaptcha"] = "";
            $form_data["text"] = ""; 
            $form_data["titleHidden"] = "";
            $form_data["btnRestartQuizHidden"] = "";
            $form_data["btnViewQuestionHidden"] = "";
            $form_data["questionRandom"] = "";
            $form_data["answerRandom"] = "";
            $form_data["sortCategories"] = "";
            $form_data["timeLimit"] = "";
            $form_data["timeLimitCookie"] = "";
            $form_data["statisticsOn"] = "on";
            $form_data["statisticsIpLock"] = "";
            $form_data["viewProfileStatistics"] = "on";
            $form_data["quizRunOnce"] = "";
            $form_data["quizRunOnceType"] = "1";
            $form_data["quizRunOnceCookie"] = "";
            $form_data["showMaxQuestion"] = "";
            $form_data["showMaxQuestionValue"] = "1";
            $form_data["showMaxQuestionPercent"] = "";
            $form_data["prerequisite"] = "";
            $form_data["prerequisiteList"] = "";
            $form_data["showReviewQuestion"] = "";
            $form_data["quizSummaryHide"] = "";
            $form_data["skipQuestionDisabled"] = "";
            $form_data["emailNotification"] = "0";
            $form_data["userEmailNotification"] = "";
            $form_data["autostart"] = "";
            $form_data["startOnlyRegisteredUser"] = "";
            $form_data["showPoints"] = "";
            $form_data["numberedAnswer"] = "";
            $form_data["hideAnswerMessageBox"] = "";
            $form_data["disabledAnswerMark"] = "";
            $form_data["forcingQuestionSolve"] = "";
            $form_data["hideQuestionPositionOverview"] = "";
            $form_data["hideQuestionNumbering"] = "";
            $form_data["showCategory"] = "";
            $form_data["showAverageResult"] = "";
            $form_data["showCategoryScore"] = "";
            $form_data["hideResultCorrectQuestion"] = "";
            $form_data["hideResultQuizTime"] = "";
            $form_data["hideResultPoints"] = "";
            $form_data["quizModus"] = "0";
            $form_data["questionsPerPage"] = "";
            $form_data["toplistActivated"] = "";
            $form_data["toplistDataAddPermissions"] = "1";
            $form_data["toplistDataAddAutomatic"] = "";
            $form_data["toplistDataSort"] = "1";
            $form_data["toplistDataAddMultiple"] = "";
            $form_data["toplistDataAddBlock"] = "1";
            $form_data["toplistDataShowLimit"] = "1";
            $form_data["toplistDataShowIn"] = "0";
            $form_data["resultText"] = "";
            $form_data["result_text"] = "";
            $form_data["resultGradeEnabled"] = "";
            $form_data["resultTextGrade"] = "";
            $form_data["DiscardOldQuestion"] = "yes";
            $form_data["DiscardQuizQuestion"] = "yes";
            $form_data["minimum_role_to_administer"] = "manage_options";
            $form_data["allow_quiz_publish"] = "yes";
            $form_data["AllowExistingQuestionImport"] = "yes";
            update_option( "quiz_default", $form_data );
        }
    }

    /**
     * Load Plugin's Classes
     *
     * @param $class
     */
    public static function autoloader ( $class ) {
        $class = str_replace ( __NAMESPACE__ . "\\" , "" , $class );
        if ( file_exists ( INCLUDES_DIR  . $class . ".php" ) ) {
            include INCLUDES_DIR  . $class . ".php";
        } elseif ( file_exists ( INCLUDES_DIR  . "export" . DIRECTORY_SEPARATOR . $class . ".php" ) ) {
            include INCLUDES_DIR  . "export/" .$class . ".php";
        } elseif ( file_exists ( INCLUDES_DIR  . "import" . DIRECTORY_SEPARATOR . $class . ".php" ) ) {
            include INCLUDES_DIR  . 'import/' .$class . '.php';
        } elseif ( file_exists ( INCLUDES_DIR  . "settings" . DIRECTORY_SEPARATOR . $class . ".php" ) ) {
            include INCLUDES_DIR  . 'settings/' .$class . '.php';
        }
    }

    /**
     * @return $this
     */
    public static function instance () {
        if ( is_null ( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Activation function hook
     *
     * @return void
     */
    public static function activation () {
        if ( !current_user_can ( "activate_plugins" ) )
            return;

        update_option( "ldqie_version", self::$version );
    }

    /**
     * Deactivation function hook
     * No used in this plugin
     *
     * @return void
     */
    public static function deactivation () {
        delete_option( "quiz_default" );
    }

    public static function upgrade() {
        if ( get_option ( "ldqie_version" ) != self::$version ) {
        }
    }

    /**
     * Enqueue scripts on admin
     */
    public static function admin_enqueue_scripts () {

        $screen = get_current_screen();
        if(!in_array($screen->id, self::$pages)) {
            return;
        }
        
        wp_enqueue_style ( "jquery-css", ASSETS_URL . "css/jquery-ui.css", array(), self::$version );
        wp_enqueue_style ( "ldqie-admin-css", ASSETS_URL . "css/ldqie-admin.css", array( "jquery-css" ), self::$version );

        wp_enqueue_script( 'ldqie-admin-select2', ASSETS_URL . '/js/select2/select2.min.js', array( 'jquery' ), '', true );
        wp_enqueue_style( 'ldqie-admin-select2-css', ASSETS_URL . '/js/select2/select2.css' );

        $deps = array(
            "jquery",
            'jquery-ui-core',
            'jquery-ui-sortable',
            "jquery-ui-tabs"
        );

        wp_enqueue_script ( "ldqie-admin-js", ASSETS_URL . "js/ldqie-admin.js", $deps, self::$version, true );
        wp_enqueue_script ( "ld-qie-admin-quiz-import-js", ASSETS_URL . "js/ld_qie_admin_quiz_import.js", $deps, self::$version, true );

        $ldqieQuizJSVars = array( 'ajax_url' => admin_url( 'admin-ajax.php' ) );

        $ld_qie_quiz_import_options = get_option( 'quiz_default', 'no' );
        $ldqieQuizJSVars['allowExistingQuestionImport'] = false;
        if( isset($ld_qie_quiz_import_options['AllowExistingQuestionImport']) ) {
            $ldqieQuizJSVars['allowExistingQuestionImport'] = $ld_qie_quiz_import_options['AllowExistingQuestionImport'] == 'yes';
        }


        wp_localize_script( 'ldqie-admin-js', 'ldqieQuizVars', $ldqieQuizJSVars );


        if( "ld-quiz-import_page_ldqie-settings" == $screen->id && array_key_exists( "tab", $_GET ) ) {
            wp_enqueue_script( 'repeatable-field-js', ASSETS_URL . "js/repeatable.js", [ 'jquery', 'jquery-ui-sortable', 'underscore', 'wp-util' ], '1.0', true );
        }
    }
}

/**
 * Main instance
 *
 * @return Main
 */
function LDQIE() {
    if ( !is_plugin_active ( "sfwd-lms/sfwd_lms.php" ) ) {
        return;
    }
    return Main::instance();
}

add_action( "plugins_loaded", __NAMESPACE__ . "\LDQIE", 101 );