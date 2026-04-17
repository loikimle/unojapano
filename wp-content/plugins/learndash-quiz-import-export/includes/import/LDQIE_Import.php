<?php
namespace LDQIE;
/**
 * Import Quiz
 *
 * @class     LDQIE_Import
 * @version   1.0.0
 * @package   LDQIE/Classes/Import
 * @category  Class
 * @author    WooNinjas 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;


/**
 * LDQIE_Import Class.
 */
class LDQIE_Import {

    /**
     * Class Constructor
     */
    public function __construct () {
        add_action ( "init", array( __CLASS__, "include_libraries" ) );
        add_action ( "wp_ajax_ld_qie_import_quiz", [$this, 'import_quiz_cb'] );
        add_action ( "wp_ajax_ld_qie_search_questions", [$this, 'search_questions_cb'] );
        add_action ( "wp_ajax_ld_qie_get_question_details", [$this, 'get_question_details_cb'] );
        add_action ( "wp_ajax_ld_qie_import_questions", [$this, 'import_questions_cb'] );
        add_action ( "admin_menu", array( __CLASS__, "import_page" ) );
        add_action ( "admin_notices", array( __CLASS__, "process_import" ), 100 );
    }

    public function import_questions_cb() {
        if( !wp_doing_ajax() ) {
            return;
        }

        $response = array(
            'status' => 'error',
            'message' => __('An error occurred while processing your request')
        );

        $response = $this->process_import();

        if( $response['status'] == 'success' && !empty($response['data']['quiz_id']) ) {

            $quiz_id = $response['data']['quiz_id'];
            if( isset($_POST['question_ids']) && !empty($_POST['question_ids']) && is_array($_POST['question_ids']) ) {
                foreach ($_POST['question_ids'] as $question_id) {
                    $this->linkQuestionToQuiz($question_id,$quiz_id);
                }
            }
        }

        $this->send_json($response);
    }

    private function linkQuestionToQuiz($question_post_id, $quiz_post_id) {
        $questionMapper = new \WpProQuiz_Model_QuestionMapper();

        $question_pro_id = get_post_meta( $question_post_id, 'question_pro_id', true );
        $quiz_pro_id = absint( get_post_meta( $quiz_post_id, 'quiz_pro_id', true ) );
        $question_pro  = $questionMapper->fetchById( $question_pro_id );

        $quiz_post_id = learndash_get_quiz_id_by_pro_quiz_id( $quiz_pro_id );
        learndash_update_setting( $question_post_id, 'quiz', $quiz_post_id );
        learndash_proquiz_sync_question_fields( $question_post_id, $question_pro );
        learndash_set_question_quizzes_dirty( $question_post_id );
    }

    public function get_question_details_cb() {
        if( !wp_doing_ajax() ) {
            return;
        }

        $response = array(
            'status' => 'error',
            'message' => __('An error occurred while processing your request')
        );

        $question_meta_fields = array(
            'question_points',
            'question_type',
            'question_pro_category',
        );

        if( !empty($_POST) && isset($_POST['question_id']) ) {
            $question_post_id = absint($_POST['question_id']);
            $question_pro_id = get_post_meta($question_post_id, 'question_pro_id', true);
            $question_metas = get_post_meta($question_post_id);
            $questionMapper = new \WpProQuiz_Model_QuestionMapper();
            $question  = $questionMapper->fetch( $question_pro_id );
            $data = leandash_get_question_pro_fields( $question_pro_id, array( 'title', 'category_name','points','question', 'answer_type', 'category_id' ) );
            /*print_r($data); exit;
            $data = array(
                'question_title' => $question->getTitle(),
                'question_text' => $question->getQuestion(),
                'question_answer_type' => $question->getAnswerType(),
                'question_points' => $question->getPoints(),
                'question_category' => $question->getCategoryName(),
                'question_category_id' => $question->getCategoryId(),
            );*/
            $data['question'] = wp_trim_words( $data['question'], 30, '...' );
            $response = array(
                'status' => 'success',
                'data' => $data
            );
        }

        $this->send_json($response);
    }

    private function send_json($response) {
        if( $response['status'] == 'error' ) {
            wp_send_json($response, 400);
        }

        wp_send_json($response);
        exit;
    }

    public function ld_qie_the_posts_where( $clause = '' ) {
        global $wpdb;
        $clause .= " AND ((" . $wpdb->prefix . "postmeta.meta_value IS NULL  ))";

        return $clause;
    }

    public function search_questions_cb() {

        if( !wp_doing_ajax() ) {
            return;
        }

        /*if( !check_ajax_referer('ld-cms-nonce', 'security')) {
            return;
        }*/

        $per_page_count = 5;

        $page = isset($_GET['page']) ? absint($_GET['page']) : 1;

        $args = array(
            'posts_per_page' => $per_page_count,
            'post_type' => learndash_get_post_type_slug('question'),
            'post_status' => 'publish',
            'paged' => $page,
        );

        if(isset($_GET['search'])) {
            $args['s'] = sanitize_text_field($_GET['search']);
        }

        if(isset($_GET['existing_question_ids'])) {
            $args['post__not_in'] = is_array($_GET['existing_question_ids']) && !empty($_GET['existing_question_ids']) ? $_GET['existing_question_ids'] : array();
        }

        //$quizzes_options = learndash_get_option( learndash_get_post_type_slug('quiz') );
        $is_shared_quiz_questions = \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Quizzes_Builder', 'shared_questions' ) === 'yes';

        if(!$is_shared_quiz_questions) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => 'quiz_id',
                    'value' => '',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => 'quiz_id',
                    'value' => '',
                    'compare' => '='
                ),
                array(
                    'key' => 'quiz_id',
                    'value' => '0',
                    'compare' => '='
                )

            );
        }

        $question_query = new \WP_Query( $args ); //Used inside template below
        $total_count = $question_query->found_posts;
        $data = array();

        foreach($question_query->posts as $post) {
            $post_data = array(
                'id' => $post->ID,
                'text' => $post->post_title
            );

            $data[] = $post_data;
        }

        $response = array('data' => $data, 'count_filtered' => $total_count);

        wp_send_json( $response );
    }

    public function import_quiz_cb() {

        $response = array(
            'status' => 'error',
            'message' => __('An unknown error occurred while importing file.', 'ldqie')
        );

        if( !empty($_POST) && wp_doing_ajax() ) {
            if( !empty($_FILES) ) {
                $user_id = get_current_user_id();
                $file_name = $user_id . '_' .$_FILES['ldqie_import_file']['name'];
                $ext = strtolower(sanitize_text_field(pathinfo( $file_name, PATHINFO_EXTENSION )));

                if ( 'xls' == $ext ) {

                    $dirs = wp_upload_dir();
                    $basedir = trailingslashit( $dirs[ 'basedir' ] );
                    $baseurl = trailingslashit( $dirs[ 'baseurl' ] );

                    $ldqie_xls_directory = $basedir.'quiz-import';
                    if ( ! file_exists( $ldqie_xls_directory ) && ! is_dir( $ldqie_xls_directory ) ) {
                        mkdir( $ldqie_xls_directory );
                    }

                    $inputFileName = trailingslashit( $ldqie_xls_directory ) . $_FILES["ldqie_import_file"]["name"];
                    move_uploaded_file ( $_FILES["ldqie_import_file"]["tmp_name"], $inputFileName );

                    /** automatically detect the correct reader to load for this file type */
                    $objReaderType = IOFactory::identify($inputFileName);

                    $objReader = IOFactory::createReader($objReaderType);

                    $objReader->setReadDataOnly(true);

                    /**  Load only the rows and columns that match our filter to PHPExcel  **/
                    $objPHPExcel = $objReader->load ( $inputFileName );

                    //$objPHPExcel->getActiveSheet()->toArray ( null, true, true, true );

                    /** get all sheet names from the file **/
                    $worksheetNames = $objPHPExcel->getSheetNames ( $inputFileName );

                    if( count( $worksheetNames ) == 1 ) {

                        $sheet_rows = $objPHPExcel->getActiveSheet()->toArray ( null, true, true, true );
                        //print_r($sheet_rows);

                        /*$cols = array('B','C','D','E','G');
                        foreach ($sheet_rows as $row => $row_cols) {
                            foreach ($row_cols as $col => $col_value) {
                                if (in_array($col, $cols)) {
                                    echo "{$col} = {$col_value}\n";
                                }

                            }
                        }*/

                        $response = array(
                            'status' => 'success',
                            'data' => $sheet_rows[1]
                        );

                    } else {
                        $response['message'] = __( "Only single sheet/quiz can be imported.", "ldqie" );
                    }

                } else {
                    $response['message'] = __('Please choose an XLS file format', 'ldqie');
                }

            } else {
                $response['message'] = __('Please choose an XLS file first', 'ldqie');
            }
        }

        $this->send_json($response);
    }

    /**
     * Include Libraries
     */
    public static function include_libraries () {
        if ( file_exists( INCLUDES_DIR . "import/Import_Helper.php" ) ) {
            require_once INCLUDES_DIR . "import/Import_Helper.php";
        }

        /**
         * Load external PHPSpreadsheet library
         */
        if( file_exists( INCLUDES_DIR . 'library/phpspreadsheet/vendor/autoload.php' ) )
            require_once INCLUDES_DIR . 'library/phpspreadsheet/vendor/autoload.php';

        if ( file_exists( dirname ( DIR ) . "/sfwd-lms/includes/vendor/wp-pro-quiz/lib/helper/WpProQuiz_Helper_ImportXml.php" ) ) {
            require_once dirname ( DIR ) . "/sfwd-lms/includes/vendor/wp-pro-quiz/lib/helper/WpProQuiz_Helper_ImportXml.php";
        }

        if ( ! function_exists( 'post_exists' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/post.php' );
        }
    }

    /**
     * Adds menu page
     * to Import quizzes
     */
    public static function import_page () {
        $values = get_option( "quiz_default" );
        $minimum_role = '';
        if( isset( $values[ 'minimum_role_to_administer' ] ) )
            $minimum_role = trim( $values[ 'minimum_role_to_administer' ] );
        if( empty( $minimum_role ) ) {
            $minimum_role = "manage_options";
        }
        
        add_menu_page (
            __( "Quiz Import", "ldqie" ),
            "LD Quiz Import",
            $minimum_role,
            "ldqie-quiz-import",
            array ( __CLASS__, "ld_qie_quiz_import_form" )
        );
    }

    /**
     * Quiz Import View
     */
    public static function ldqie_quiz_import_view () {
        $form = "<div class='wrap'>";
        $form .= "<h1>" . __( "Import Quiz from Excel", "ldqie" ) . "</h1>";
        $form .= "<h3><i>". __( "Please choose an MS Excel file (.xls) to import.", "ldqie" ) . "</i></h3>";
        $form .= "<form class='ldqie_import_form' enctype='multipart/form-data' name='ldqie_import_form' method='post' action='". admin_url( 'admin.php?page=ldqie-quiz-import' ) ."'>";
        $form .= "<input type='hidden' name='ldqie_import_hidden' value='ldqie_import'>";

        $form .="<div class='container import-excel-page'>
            <div class='content'>
                <div class='box'>
                    <input type='file' name='ldqie_import_file' id='file-7' class='inputfile inputfile-6' accept='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel' />
                    <label for='file-7'><span></span> <strong><svg xmlns='http://www.w3.org/2000/svg' width='20' height='17' viewBox='0 0 20 17'><path d='M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z'/></svg> Choose a file&hellip;</strong></label>
                </div>
            </div>
        </div>";

        $form .= "<div class='submit' style='text-align:center;''>";
        $values = get_option( "quiz_default" );
        if( $values['AllowExistingQuestionImport'] == 'yes' ) {
            $form .= "<input class='button button-primary import-btn import-existing-question-btns' type='button' name='import-btn' value='" . __( "Import Quiz", "ldqie" ) ."' />";
        } else {
            $form .= "<input class='button button-primary import-btn' type='submit' name='Submit' value='" . __( "Import Quiz", "ldqie" ) ."' />";
        }
        $form .= "</div>";
        $form .= "<hr />";
        $form .= "</form>";

        echo $form;
    }

    public static function ld_qie_quiz_import_form() {
        $values = get_option( 'quiz_default', 'no' );
        ?>
        <div class="wrap">

            <div id='ld_qie_import_messages'></div>
            <h1><?php _e( "Import Quiz from New Excel", "ldqie" ); ?></h1>
            <h3><i><?php _e( "Please choose an MS Excel file (.xls) to import.", "ldqie" ); ?></i></h3>


            <div id="ld_qie_content_wrap">
                <form id="ld_qie_import_quiz_form" action="" method="post" enctype="multipart/form-data">
                    <!--<div id="ld_qie_loader_spinner" style="display: none;">
                        <p><img src="<?php /*echo ASSETS_URL . 'loader_spinner.gif'; */?>"></p>
                    </div>-->
                    <div id="ld_qie_loader_spinner" style="display: none;">
                    <div  class="ld_qie_spinner" ></div>
                    </div>
        <?php if( $values['AllowExistingQuestionImport'] == 'yes' ): ?>
            <input type="hidden" name="action" id="ld_qie_action" value="ld_qie_import_quiz">
        <?php else: ?>
            <input type="hidden" name="action" id="ld_qie_action" value="ld_qie_import_questions">
        <?php endif; ?>

                    <div id="file_upload_content">
                        <div class="import-excel-page">
                            <div class="content">
                                <div class="box">
                                    <input type="file" id="ldqie_import_file" name="ldqie_import_file" class="inputfile inputfile-6" />
                                    <label for="ldqie_import_file"><span></span> <strong><svg xmlns='http://www.w3.org/2000/svg' width='20' height='17' viewBox='0 0 20 17'><path d='M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z'/></svg> Choose a file&hellip;</strong></label>

                                </div>
                            </div>
                        </div>
                        <div class="submit" style="text-align: center;">
                            <input class="button button-primary import-btn import-existing-question-btns" type="submit" name="import-btn" value="<?php _e( "Import Quiz", "ldqie" ); ?>" />
                        </div>
                    </div>

                    <div id="add_questions_content" style="display: none;">
                        <?php if( $values['AllowExistingQuestionImport'] == 'yes' ): ?>
                            <div id="ld_qie_search_box">
                                <select id="ld_qie_qestion_search" data-placeholder="Type question title to search" name="question_ids[]"></select>
                                <input type="hidden" id="ld_qie_selected_question_id" value="">
                                <button id="ld_qie_add_question_button" class="button button-primary ld_qie_float_right"><?php _e('Add Question', 'ldqie'); ?></button>
                            </div>
                        <?php endif; ?>

                        <div  class="import-excel-page">
                            <div id="add_questions_table" class="content-full">

                            </div>
                            <div class="ld_qie_clear"></div>
                        </div>
                        <div class="submit" style="text-align: center;">
                            <input class="button button-primary import-btn float-button" id="ld_qie_process_questions" type="submit" value="<?php _e( "Process Questions", 'ldqie' ); ?>" />
                            <button class="button button-primary import-btn float-button import-existing-question-btns" type="button"  id="ld_qie_cancel_process_questions_button"><?php _e( "Cancel", "ldqie" ); ?></button>
                        </div>
                    </div>
                </form>
            </div>
            <hr>
            </div>

        <?php
    }

    /**
     * Extract data from the input question/answer
     * 
     * @param $answer_data
     * 
     * return $string_data
     */
    public static function extract_answer_data( $answer_data, $answer_type, $column = '' ) {

        preg_match_all("/\[[^\]]*\]/", $answer_data, $matches);
        $found = $matches[0];
        $final_text = $answer_data;
        if( count( $found ) > 0 ) {

            $allowed_imgs = [ 'png', 'jpg', 'jpeg', 'gif', 'tiff' ];
            foreach( $found as $str ) {

                $temp = str_replace("[", "", $str);
                $temp = str_replace("]", "", $temp);
                
                $may_be_url = substr( $temp, -5 );
                $is_img = false;
                $image = '';
                if( strpos( $temp, '.' ) !== false ) {
                    $img_ext = explode( '.', $may_be_url );
                    if( in_array( trim( $img_ext[1] ), $allowed_imgs ) ) {
                        $is_img = true;
                        $image = '<img src="'.trim( $temp ).'" />'. ' ';

                        if( 'assessment_answer' == $answer_type && 'answer' == $column ) {
                            $image = '[<img src="'.trim( $temp ).'" />]'. ' ';
                        }
                    }
                }

                if( !$is_img ) {
                    if( 'assessment_answer' == $answer_type ) {
                        if( 'answer' == $column ) {
                            if( 'latex' == $temp ) {
                                $image = '[['.$temp.']';
                            } elseif( '/latex' == $temp ) {
                                $image = '['.$temp.']]';
                            }
                        } else {
                            $image = '['.$temp.']';
                        }
                    } else {
                        $image = '['.$temp.']';
                    }
                }
                
                
                if( !empty( $image ) ) {
                    $final_text = str_replace( $str, $image, $final_text );
                }

                
            }
        } 
        
        return $final_text;
    }

    /**
     * Initiate import process
     *
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
    */
    public static function process_import () { 
        
        ini_set('max_execution_time', 300);
        $quiz_post_id = null;

        $response = array(
                'status' => 'error',
                'message' => __('An unknown error occurred while processing your request','ld_qie'),
        );

        if ( array_key_exists( "ldqie_import_file", $_FILES) && !empty($_FILES["ldqie_import_file"]["name"]) ) {
            $sheet_name = $file_name = $_FILES["ldqie_import_file"]["name"];
            $ext = pathinfo( $file_name, PATHINFO_EXTENSION );

            if ( strtolower($ext)  == 'xls' ) {

                $dirs = wp_upload_dir();
				$basedir = trailingslashit( $dirs[ 'basedir' ] );
				$baseurl = trailingslashit( $dirs[ 'baseurl' ] );

				$ldqie_xls_directory = $basedir.'quiz-import';
				if ( ! file_exists( $ldqie_xls_directory ) && ! is_dir( $ldqie_xls_directory ) ) {
					mkdir( $ldqie_xls_directory );         
				}

                $inputFileName = trailingslashit( $ldqie_xls_directory ) . $_FILES["ldqie_import_file"]["name"];
                move_uploaded_file ( $_FILES["ldqie_import_file"]["tmp_name"], $inputFileName );

                /** automatically detect the correct reader to load for this file type */
                $objReaderType = IOFactory::identify($inputFileName);

                $objReader = IOFactory::createReader($objReaderType);

                $objReader->setReadDataOnly(true);

                /**  Load only the rows and columns that match our filter to PHPExcel  **/
                $objPHPExcel = $objReader->load ( $inputFileName );

                //$objPHPExcel->getActiveSheet()->toArray ( null, true, true, true );

                /** get all sheet names from the file **/
                $worksheetNames = $objPHPExcel->getSheetNames ( $inputFileName );

                if( count( $worksheetNames ) == 1 ) {

                    $sheets = array();
                    foreach ( $worksheetNames as $key => $sheetName ) {

                        /** set the current active worksheet by name **/
                        $objPHPExcel->setActiveSheetIndexByName ( $sheetName );
                        $objPHPExcel->getActiveSheet();

                        /** create an assoc array with the sheet name as key and the sheet contents array as value **/
                        $sheets[$sheetName] = $objPHPExcel->getActiveSheet()->toArray ( null, true, true, true );
                        $sheet_name = $sheetName;
                    }


                    $quiz_array = array();
                    foreach ( $sheets as $key => $sheet ) {
                        $index = array();
                        foreach ( $sheet as $key_inner => $values ) {
                            $values_s = array_filter ( $values );
                            if ( !empty ( $values_s ) ) {
                                $values_d = $values;
                            } else {
                                $values_d = $values_s;
                            }

                            if ( $key_inner == 1 ) {
                                foreach ( $values as $value ) {
                                    if ( !empty( $value ) ) {
                                        $index[] = str_replace ( " ","", preg_replace ( "/\s\s+/", "", strtolower ( $value ) ) );
                                    }
                                }
                            } else {
                                $index_num = 0;
                                foreach ( $values_d as $value ) {
                                    $index_value = $index[$index_num];
                                    if ( !is_null ( $index_value )  ) {
                                        $quiz_array[$key_inner][$index_value] = $value;
                                        $index_num++;
                                    }
                                }
                            }
                        }
                    }

                    $quiz_title = '';

                    $current_data = array();
                    $questions_array = array();
                    $loop_count = 0;

                    foreach ( $quiz_array as $key => $ques_array ) {

                        if( empty( $quiz_title ) ) {
                            $quiz_title = is_null( $ques_array["quiztitle"] ) ? substr( $file_name, 0, strlen( $file_name )-4 ) : $ques_array["quiztitle"];
                        }
                        $current_data["question_type"] = strtolower ( $ques_array["question"] );
                        $current_data["category"] = $ques_array["category"];
                        $current_data["title"] = $ques_array["title"];
                        $current_data["total_points"] = $ques_array["totalpoint"];
                        $current_data["different_points_for_each_answer"] = $ques_array["differentpointsforeachanswer"];
                        $current_data["question_text"] = $ques_array["questiontext"];
                        $current_data["answer_type"] = strtolower ( $ques_array["answertype"] );
                        $current_data["total_answer"] = $ques_array["totalanswer"];

                        for ( $a=1; $a <= $current_data["total_answer"]; $a++ ) {
                            $answer_index = "answer" . $a;
                            $point_index = "point" . $a;
                            if ( $current_data["question_type"] == "matrix_sort_answer" ) {
                                $criterion = explode( '}{', str_replace( '} {', '}{', $ques_array[$answer_index] ) );
                                $sort_element = substr( $criterion[1], 0, -1 );

                                $current_data["answer_count"][$a]["sort_element"] = $sort_element;
                                $current_data["answer_count"][$a][$answer_index] = substr( $criterion[0], 1 );
                                $current_data["answer_count"][$a][$point_index] = $ques_array[$point_index];
                            } else {
                                if( is_bool( $ques_array[$answer_index] ) ) {

                                    if( $ques_array[$answer_index] ) {
                                        $ques_array[$answer_index] = 'true';
                                    } else {
                                        $ques_array[$answer_index] = 'false';
                                    }
                                }
                                $current_data["answer_count"][$a][$answer_index] = $ques_array[$answer_index];
                                $current_data["answer_count"][$a][$point_index] = $ques_array[$point_index];
                            }
                        }

                        $current_data["answer"]                         = $ques_array["answer"];
                        $current_data["message_with_correct_answer"]    = $ques_array["messagewithcorrectanswer"];
                        $current_data["message_with_incorrect_answer"]  = $ques_array["messagewithincorrectanswer"];
                        $current_data["hint"]                           = $ques_array["hint"];

                        if ( $current_data["question_type"] == "multiple" ) {
                            $someArray = explode ( "|", $current_data["answer"] );
                            $tempArray = array();
                            foreach ( $someArray as $index => $value ) {
                                $tempArray[$index + 1] = $value;
                            }
                            $multi_ans_val = $tempArray;
                        }

                        $questions_array[$loop_count]["question"]["answerType"] = $current_data["question_type"];

                        if( $current_data["answer_type"] == "html" ) {
                            $questions_array[$loop_count]["question"]["title"] = self::extract_answer_data( $current_data["title"], strtolower( $current_data["question_type"] ), 'title' );
                        } else {
                            $questions_array[$loop_count]["question"]["title"] = $current_data["title"];
                        }

                        $questions_array[$loop_count]["question"]["showPointsInBox"] = false;
                        $questions_array[$loop_count]["question"]["answerPointsActivated"] = ( ( strtolower ( $current_data["different_points_for_each_answer"] ) == "yes" ) ? true : false );

                        if( $current_data["answer_type"] == "html" ) {
                            $questions_array[$loop_count]["question"]["questionText"] = self::extract_answer_data( $current_data["question_text"], strtolower( $current_data["question_type"] ), 'questionText' );
                        } else {
                            $questions_array[$loop_count]["question"]["questionText"] = $current_data["question_text"];
                        }

                        $questions_array[$loop_count]["question"]["correctMsg"] = self::extract_answer_data( $current_data["message_with_correct_answer"], strtolower( $current_data["message_with_correct_answer"] ), 'correctMsg' );
                        $questions_array[$loop_count]["question"]["incorrectMsg"] = self::extract_answer_data( $current_data["message_with_incorrect_answer"], strtolower( $current_data["question_type"] ), 'incorrectMsg' );
                        $questions_array[$loop_count]["question"]["tipMsg"]["msg"] = self::extract_answer_data( $current_data["hint"], strtolower( $current_data["question_type"] ), 'hint' );
                        $questions_array[$loop_count]["question"]["tipMsg"]["hint"] = ( $current_data["hint"] ) ? true : false;
                        $questions_array[$loop_count]["question"]["category"] = $current_data["category"];
                        $questions_array[$loop_count]["question"]["correctSameText"] = false;
                        $questions_array[$loop_count]["question"]["answerPointsDiffModusActivated"] = false;
                        $questions_array[$loop_count]["question"]["disableCorrect"] = false;
                        
                        if ( $current_data["question_type"] == "cloze_answer" ) {
                            if ( $current_data["answer_type"] == "html" ) {
                                $image_html = self::extract_answer_data( $current_data['answer'], strtolower( $current_data["question_type"] ), 'answer' );
                            
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["text"] = $image_html;
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["html"] = true;
        
                            } else {
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["text"] = $current_data["answer"];
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["html"] = false;
                            }

                            $questions_array[$loop_count]["question"]["answer"]["answerList"]["stortText"]["text"] = "";
                            $questions_array[$loop_count]["question"]["answer"]["answerList"]["stortText"]["html"] = false;

                            preg_match_all ( "#\{(.*?)(?:\|(\d+))?(?:[\s]+)?\}#im", $current_data["answer"], $matches );
                            $points = 0;
                            $maxPoints = 0;
                            foreach ( $matches[2] as $match ) {
                                if ( empty ( $match ) ) {
                                    $match = 1;
                                }

                                $points += $match;
                                $maxPoints = max ( $maxPoints, $match );
                            }

                            if ( $current_data["different_points_for_each_answer"] == "yes" ) {
                                $questions_array[$loop_count]["question"]["answer"]["points"] = $points;
                                $questions_array[$loop_count]["question"]["answer"]["maxPoints"] = $maxPoints;
                            } else {
                                $questions_array[$loop_count]["question"]["answer"]["points"] = ( ( $current_data["total_points"] ) ? $current_data["total_points"] : 1 );
                            }

                        } elseif ( $current_data["question_type"] == "free_answer" ) {
                            if ( $current_data["answer_type"] == "html" ) {
                                $image_html = self::extract_answer_data( $current_data['answer'], strtolower( $current_data["question_type"] ), 'answer' );

                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["text"] = $image_html;
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["html"] = true;
                            } else {
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["text"] = $current_data["answer"];
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["html"] = false;
                            }

                            $questions_array[$loop_count]["question"]["answer"]["answerList"]["stortText"]["text"] = "";
                            $questions_array[$loop_count]["question"]["answer"]["answerList"]["stortText"]["html"] = false;

                            $questions_array[$loop_count]["question"]["answer"]["points"] = ( ( $current_data["total_points"] ) ? $current_data["total_points"] : 1 );

                        } elseif ( $current_data["question_type"] == "assessment_answer" ) {

                            if ( $current_data["answer_type"] == "html" ) {
                                $image_html = self::extract_answer_data( $current_data['answer'], strtolower ( $current_data["question_type"] ), 'answer' );
                            
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["text"] = $image_html;
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["html"] = true;
                            } else {
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["text"] = $current_data["answer"];
                                $questions_array[$loop_count]["question"]["answer"]["answerList"]["answerText"]["html"] = false;
                            }

                            $questions_array[$loop_count]["question"]["answer"]["answerList"]["stortText"]["text"] = "";
                            $questions_array[$loop_count]["question"]["answer"]["answerList"]["stortText"]["html"] = false;

                            preg_match_all ( "#\[(.*?)(?:\|(\d+))?(?:[\s]+)?\]#im", $current_data["answer"], $matches );

                            $points = 0;
                            $maxPoints = 0;
                            foreach ( $matches[2] as $match ) {
                                if ( empty ( $match ) ) {
                                    $match = 1;
                                }

                                $points += $match;
                                $maxPoints = max ( $maxPoints, $match );
                            }

                            $questions_array[$loop_count]["question"]["answer"]["points"] = $points;
                            $questions_array[$loop_count]["question"]["answer"]["maxPoints"] = $maxPoints;

                        } elseif ( $current_data["question_type"] == "essay" ) {

                            $essayArray = explode ( "|", $current_data["answer"] );

                            $questions_array[$loop_count]["question"]["answer"]["answerList"]["grade"]["gradeType"] = trim( $essayArray[0] );
                            $questions_array[$loop_count]["question"]["answer"]["answerList"]["grade"]["gradeProgressions"] = trim( $essayArray[1] );

                            $questions_array[$loop_count]["question"]["answer"]["answerList"]["stortText"]["text"] = "";
                            $questions_array[$loop_count]["question"]["answer"]["answerList"]["stortText"]["html"] = false;

                            $questions_array[$loop_count]["question"]["answer"]["points"] = ( ( $current_data["total_points"] ) ? $current_data["total_points"] : 1 );

                        } else {
                            $total_points = 0;
                            
                            for ( $opt = 1; $opt <= $current_data["total_answer"]; $opt++ ) {
                                $ans = $current_data["answer_count"][$opt]["answer".$opt];
                                if ( ( !is_null ( $ans ) && !empty ( $ans ) ) || $ans=='0' ) {

                                    $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["points"] = $current_data["answer_count"][$opt]["point".$opt];
                                    if ( $current_data["question_type"] == "single" ) {
                                        if( $total_points < intval( $current_data["answer_count"][$opt]["point".$opt] ) )
                                            $total_points = (int) $current_data["answer_count"][$opt]["point".$opt];
                                    } else {
                                        $total_points = $total_points + (int) $current_data["answer_count"][$opt]["point".$opt];
                                    }

                                    if ( $current_data["question_type"] == "multiple" ) {
                                        if ( in_array( $opt, $multi_ans_val ) ) {
                                            $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["correct"] = true;
                                        } else {
                                            $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["correct"] = false;
                                        }
                                    } else {
                                        if ( $opt == $current_data["answer"] ) {
                                            $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["correct"] = true;
                                        } else {
                                            $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["correct"] = false;
                                        }
                                    }

                                    if ( $current_data["answer_type"] == "html" ) {
                                        $image_html = self::extract_answer_data( $current_data[ "answer_count" ][ $opt ][ "answer".$opt ], strtolower ( $current_data["question_type"] ) );
                                        $questions_array[ $loop_count ][ "question" ][ "answer" ][ "answerList" ][$opt][ "answerText" ][ "text" ] = $image_html;
                                        
                                    } else {

                                        if( is_bool( $current_data["answer_count"][$opt]["answer".$opt] ) ) {

                                            if( $current_data["answer_count"][$opt]["answer".$opt] ) {
                                                $current_data["answer_count"][$opt]["answer".$opt] = 'true';
                                            } else {
                                                $current_data["answer_count"][$opt]["answer".$opt] = 'false';
                                            }
                                        }

                                        $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["answerText"]["text"] = $current_data["answer_count"][$opt]["answer".$opt];
                                    }

                                    if ( $current_data["question_type"] == "matrix_sort_answer" ) {
                                        if ( $current_data["answer_type"] == "html" ) {
                                            $image_html = self::extract_answer_data( $current_data["answer_count"][$opt]["sort_element"], strtolower ( $current_data["question_type"] ) );
                                            $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["stortText"]["text"] = $image_html;
                                            $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["stortText"]["html"] = true;
                                        } else {
                                            $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["stortText"]["text"] = $current_data["answer_count"][$opt]["sort_element"];
                                        }    
                                    } else {
                                        $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["stortText"] = array();
                                    }

                                    if ( $current_data["answer_type"] == "html" ) {
                                        $questions_array[$loop_count]["question"]["answer"]["answerList"][$opt]["stortText"]["html"] = true;
                                    }
                                }
                            }

                            if ( $current_data["different_points_for_each_answer"] == "yes" ) {
                                $questions_array[$loop_count]["question"]["answer"]["points"] = $total_points;
                            } else {
                                $questions_array[$loop_count]["question"]["answer"]["points"] = ( ( $current_data["total_points"] ) ? $current_data["total_points"] : 1 );
                            }
                        }
                        $loop_count++;
                    }

                    update_option("quiz_title", $quiz_title);
                    
                    $ids = array ( 0 => 0 );
                    $quizMapperXls = new \LDQIE_ImportXls ();
                    do_action( "ldqie_before_quiz_import" );
                    $success = $quizMapperXls->saveImportXls ( $ids, $questions_array );
                    
                    unlink ( $inputFileName );
                    if ( $success ) {
                        $quiz_post_id = $success[0];
                        
                        $imports_total = intval ( get_option( 'ld_imports_total' ) );
                        if( intval( $imports_total ) == 0 || empty( $imports_total ) ) {
                            $imports_total = 1;
                        }   else {
                            $imports_total += 1;
                        }
                        update_option( 'ld_imports_total', $imports_total );
                        
                        $quiz = get_post_meta( $quiz_post_id, '_sfwd-quiz', true );
                        $values = get_option( "quiz_default" );
                        if( ! is_array( $values ) || count( $values ) == 0 ) {
                            $values = array();
                        } 
                        
                        if( is_array($values) && count( $values ) > 0 ) {
                            
                            $quiz['sfwd-quiz_course'] = $values['course'];
                            $quiz['sfwd-quiz_lesson'] = $values['lesson'];
                            $quiz['sfwd-quiz_startOnlyRegisteredUser'] = $values['startOnlyRegisteredUser'];
                            $quiz['sfwd-quiz_prerequisiteList'] = $values['prerequisiteList'];
                            if( isset( $values['prerequisite'] ) ) {
                                $quiz['sfwd-quiz_prerequisite'] = $values['prerequisite'];
                            }
                            $quiz['sfwd-quiz_retry_restrictions']   = $values['retry_restrictions'];
                            $quiz['sfwd-quiz_repeats'] = $values['repeats'];
                            $quiz['sfwd-quiz_quizRunOnceType'] = $values['quizRunOnceType'];
                            $quiz['sfwd-quiz_quizRunOnceCookie'] = $values['quizRunOnceCookie'];
                            $quiz['sfwd-quiz_passingpercentage'] = $values['passingpercentage'];
                            $quiz['sfwd-quiz_certificate'] = $values['certificate'];
                            $quiz["sfwd-quiz_threshold"] = $values['threshold'];
                            if( intval( $values['threshold'] ) > 0 )  {
                                $quiz["sfwd-quiz_threshold"] = ($values['threshold']/100);
                            }
                            
                            $timeLimit = $values['timeLimit'];
                            $timeLimitVal = 0;
                            if( is_array( $timeLimit ) && count( $timeLimit ) > 0 ) {
                                $timeLimitVal = (intval( $timeLimit['hh'] ) * 3600 ) + ( intval( $timeLimit['mm'] ) * 60 ) +  intval( $timeLimit['ss'] );
                            }

                            $quiz['sfwd-quiz_timeLimit'] = $timeLimitVal;
                            $quiz['sfwd-quiz_forcingQuestionSolve'] = $values['forcingQuestionSolve'];
                            $quiz['sfwd-quiz_quiz_time_limit_enabled'] = $values['quiz_time_limit_enabled'];
                            $quiz['sfwd-quiz_quiz_materials_enabled'] = $values['quiz_materials_enabled'];
                            $quiz['sfwd-quiz_quiz_materials'] = $values['quiz_materials'];
                            $quiz['sfwd-quiz_custom_sorting'] = $values['custom_sorting'];
                            $quiz['sfwd-quiz_autostart'] = $values['autostart'];
                            $quiz['sfwd-quiz_showReviewQuestion'] = $values['showReviewQuestion'];
                            $quiz['sfwd-quiz_quizSummaryHide'] = ($values['quizSummaryHide']=='on')?'':$values['quizSummaryHide'];
                            $quiz['sfwd-quiz_skipQuestionDisabled'] = ($values['skipQuestionDisabled']=='on')?'':$values['skipQuestionDisabled'];
                            $quiz['sfwd-quiz_sortCategories'] = $values['sortCategories'];
                            $quiz['sfwd-quiz_questionRandom'] = $values['questionRandom'];
                            $quiz['sfwd-quiz_showMaxQuestion'] = $values['showMaxQuestion'];
                            $quiz['sfwd-quiz_showMaxQuestionValue'] = $values['showMaxQuestionValue'];
                            $quiz['sfwd-quiz_showPoints'] = $values['showPoints'];
                            $quiz['sfwd-quiz_showCategory'] = $values['showCategory'];
                            $quiz['sfwd-quiz_hideQuestionPositionOverview'] = ($values['hideQuestionPositionOverview']=='on')?'':$values['hideQuestionPositionOverview'];
                            $quiz['sfwd-quiz_hideQuestionNumbering'] = ($values['hideQuestionNumbering']=='on')?'':$values['hideQuestionNumbering'];
                            $quiz['sfwd-quiz_numberedAnswer'] = $values['numberedAnswer'];
                            $quiz['sfwd-quiz_answerRandom'] = $values['answerRandom'];
                            
                            if( trim( $values['quizModus'] ) == "0" ) {
                                if( trim( $values['quizModus_single_feedback'] )=='end' && trim( $values['quizModus_single_back_button'] )=='on' ) {
                                    $quiz['sfwd-quiz_quizModus'] = 1;
                                } else if( trim( $values['quizModus_single_feedback'] )=='end' && empty( $values['quizModus_single_back_button'] ) ) {
                                    $quiz['sfwd-quiz_quizModus'] = 0;
                                } else {
                                    $quiz['sfwd-quiz_quizModus'] = 2;
                                }
                            } else {
                                $quiz['sfwd-quiz_quizModus'] = 3;
                            }

                            $quiz['sfwd-quiz_quizModus_multiple_questionsPerPage'] = $values['quizModus_multiple_questionsPerPage'];
                            $quiz['sfwd-quiz_quizModus_single_back_button'] = trim($values['quizModus_single_back_button']);
                            $quiz['sfwd-quiz_quizModus_single_feedback'] = $values['quizModus_single_feedback'];
                            $quiz['sfwd-quiz_titleHidden'] = ($values['titleHidden']=='on')?'':$values['titleHidden'];
                            $quiz['sfwd-quiz_resultText'] = $values['resultText'];
                            $quiz['sfwd-quiz_btnRestartQuizHidden'] = ($values['btnRestartQuizHidden']=='on')?0:1; 
                            $quiz['sfwd-quiz_showAverageResult'] = ($values['showAverageResult']=='on')?'on':''; 
                            $quiz['sfwd-quiz_showCategoryScore'] = ($values['showCategoryScore']=='on')?'on':'';
                            $quiz['sfwd-quiz_hideResultPoints'] = ($values['hideResultPoints']=='on')?'':$values['hideResultPoints'];
                            $quiz['sfwd-quiz_hideResultCorrectQuestion'] = ($values['hideResultCorrectQuestion']=='on')?'':$values['hideResultCorrectQuestion']; 
                            $quiz['sfwd-quiz_hideResultQuizTime'] = ($values['hideResultQuizTime']=='on')?'':$values['hideResultQuizTime'];
                            $quiz['sfwd-quiz_hideAnswerMessageBox'] = ($values['hideAnswerMessageBox']=='on')?'':$values['hideAnswerMessageBox'];
                            $quiz['sfwd-quiz_disabledAnswerMark'] = ($values['disabledAnswerMark']=='on')?'':$values['disabledAnswerMark'];
                            $quiz['sfwd-quiz_btnViewQuestionHidden'] = ($values['btnViewQuestionHidden']=='on')?0:1; 
                            $quiz['sfwd-quiz_quiz_result_messages'] = isset( $values['quiz_result_messages'] ) ? 'on' : '';
                            $quiz['sfwd-quiz_resultGradeEnabled'] = $values['resultGradeEnabled'];
                            
                            $quiz['sfwd-quiz_custom_answer_feedback'] = ($values['custom_answer_feedback']=='on')?'':$values['custom_answer_feedback'];
                            $quiz['sfwd-quiz_custom_result_data_display'] = $values['custom_result_data_display'];
                            $quiz['sfwd-quiz_associated_settings_enabled'] = $values['associated_settings_enabled']; 
                            $quiz['sfwd-quiz_associated_settings'] = $values['associated_settings'];
                            
                            $quiz['sfwd-quiz_toplistDataShowIn_enabled'] = $values['toplistDataShowIn_enabled'];
                            $quiz['sfwd-quiz_statisticsIpLock_enabled'] = $values['statisticsIpLock_enabled'];
                            $quiz['sfwd-quiz_formActivated'] = $values['formActivated'];
                            $quiz['sfwd-quiz_formShowPosition'] = $values['formShowPosition'];
                            
                            $quiz['sfwd-quiz_toplistDataAddPermissions'] = $values['toplistDataAddPermissions'];
                            $quiz['sfwd-quiz_toplistDataAddMultiple'] = $values['toplistDataAddMultiple'];
                            $quiz['sfwd-quiz_toplistDataAddBlock'] = $values['toplistDataAddBlock'];
                            $quiz['sfwd-quiz_toplistDataAddAutomatic'] = $values['toplistDataAddAutomatic'];
                            $quiz['sfwd-quiz_toplistDataShowLimit'] = $values['toplistDataShowLimit'];
                            $quiz['sfwd-quiz_toplistDataSort'] = $values['toplistDataSort'];
                            $quiz['sfwd-quiz_toplistActivated'] = $values['toplistActivated'];
                            $quiz['sfwd-quiz_toplistDataShowIn'] = $values['toplistDataShowIn'];
                            $quiz['sfwd-quiz_toplistDataCaptcha'] = $values['toplistDataCaptcha'];
                            
                            if( $values['statisticsOn'] == 'on' ) {
                                $quiz['sfwd-quiz_statisticsOn'] = $values['statisticsOn'];
                                $quiz['sfwd-quiz_viewProfileStatistics'] = ($values['viewProfileStatistics']=='on')?'on':'';
                            } else {
                                $quiz['sfwd-quiz_statisticsOn'] = '0';
                                $quiz['sfwd-quiz_viewProfileStatistics'] = '0';
                            }
                            
                            $quiz['sfwd-quiz_statisticsIpLock'] = intval($values['statisticsIpLock']) * 60;
                            $quiz['sfwd-quiz_email_enabled'] = $values['email_enabled'];
                            $quiz['sfwd-quiz_email_enabled_admin'] = $values['email_enabled_admin'];
                            $quiz['sfwd-quiz_emailNotification'] = $values['emailNotification'];
                            $quiz['sfwd-quiz_userEmailNotification'] = $values['userEmailNotification'];
                            $quiz['sfwd-quiz_timeLimitCookie_enabled'] = $values['timeLimitCookie_enabled'];
                            $quiz['sfwd-quiz_timeLimitCookie'] = $values['timeLimitCookie'];
                            $quiz['sfwd-quiz_advanced_settings'] = $values['advanced_settings'];
                            
                            
                            if( $values['timeLimitCookie_enabled'] == 'on' ) {
                                update_post_meta( $quiz_post_id, '_timeLimitCookie', $values['timeLimitCookie'] );
                            }

                            if( $values['viewProfileStatistics'] == 'on' ) {
                                update_post_meta( $quiz_post_id, '_viewProfileStatistics', '1' );
                            }

                            if( isset( $values['course'] ) ) {
                                update_post_meta( $quiz_post_id, 'course_id', $quiz["sfwd-quiz_course"] );
                            }
                            if( isset( $values['lesson'] ) ) {
                                update_post_meta( $quiz_post_id, 'lesson_id', $quiz["sfwd-quiz_lesson"] );
                            }
                            update_post_meta( $quiz_post_id, '_sfwd-quiz', $quiz );
                            
                        }

                        /*echo "<div class='hidden notice ldqie-notice-success is-dismissible'>";
                        echo "<p><b>".$quiz_title. __( " imported successfully! ", "ldqie" ) ." </b>";
                        echo "<a href='". add_query_arg ( array(
                                "post" => $quiz_post_id,
                                "action" => "edit"
                            ), admin_url( "post.php" ) ) ."' class='default-button'>". __( "Edit Quiz", "ldqie" ) ."</a></p>";
                        echo "</div>";*/

                        $quiz_edit_link = add_query_arg ( array(
                            'post' => $quiz_post_id,
                            'action' => 'edit'
                        ), admin_url( 'post.php' ));

                        $message = sprintf(__('%s imported successfully <a href="%s">Edit</a>','ldqie'), $quiz_title, $quiz_edit_link);

                        $response = array(
                            'status' => 'success',
                            'data' => array(
                                'quiz_id' => $quiz_post_id,
                                'message' => $message,
                            )
                        );

                        delete_option('quiz_title');
                        do_action( "ldqie_after_quiz_import" );
                    } else {
                        delete_option('quiz_title');

                        $message = __( "Try Again", "ldqie" );
                        $response['message'] = $message;

                        /*$class = 'hidden notice ldqie-notice-warning is-dismissible';
                        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );*/

                    }
                } else {
                    delete_option('quiz_title');

                    $message = __( "Only single sheet/quiz can be imported.", "ldqie" );
                    $response['message'] = $message;

                    /*$class = 'hidden notice ldqie-notice-warning is-dismissible';
                    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );*/

                }

            } else {
                $message = __( "Only .xls file can be imported.", "ldqie" );
                $response['message'] = $message;
            }
        } else {
            $message = __( "No file has been selected", "ldqie" );
            $response['message'] = $message;
        }

        return $response;
    }
}