<?php
namespace LDQIE;
/**
 * Export Quiz
 *
 * @class     LDQIE_Export
 * @version   2.1
 * @package   LDQIE/Classes/Export
 * @category  Class
 * @author    WooNinjas
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;


/**
 * LDQIE_Export Class.
 */
class LDQIE_Export {

    /**
     * Class Constructor
     */
    public function __construct() {
        add_filter ( "manage_sfwd-quiz_posts_columns", array( __CLASS__, "quiz_post_columns" ) );
        add_action ( "manage_posts_custom_column" , array( __CLASS__, "quiz_post_columns_data" ), 10, 2 );
        add_action ( "admin_init" , array( __CLASS__, "export_quiz" ) );

        add_action( 'manage_posts_extra_tablenav', array( __CLASS__, 'admin_quiz_list_top_bar_button' ), 20, 1 );
    }
    
    /**
     * Add a new action button in bottom bar
     *
     * @param $which
     * @return none
     */
    public static function admin_quiz_list_top_bar_button( $which ) {
        global $typenow;
        
        if ( 'sfwd-quiz' === $typenow && 'bottom' === $which ) {
            
            echo "<div class='alignleft actions'><a href='admin.php?page=ldqie-quiz-import' class='button action'>" .__( "Import Quizzes", "ldqie" )."</a></div>";
        }
    }

    /**
     * Add Export column
     *
     * @param $columns
     * @return array
     */
    public function quiz_post_columns ( $columns ) {
        $new_columns = array(
            'export' => __( "Quiz Export", "ldqie" )
        );
        return array_merge ( $columns, $new_columns );
    }

    /**
     * Add export button to quizes
     *
     * @param $column
     * @param $post_id
     */
    public static function quiz_post_columns_data ( $column, $post_id ) {
        switch ( $column ) {
            case "export":
                self::get_export_button ( $post_id );
                break;
        }
    }

    /**
     * Export button HTML
     *
     * @param $post_id
     */
    public static function get_export_button ( $post_id ) {
        global $post;
        $download_url = add_query_arg ( array ( "post_type" => $post->post_type, "quiz_export" => true, "q_post_id" => $post_id ), admin_url() );
        echo "<a href='" . $download_url . "' class='ldqie-btn'>" . __( "Export", "ldqie" ) . "</a>";
    }

    /**
     * Quiz ID
     *
     * @param $post_id
     * @return mixed
     */
    public static function get_quiz_id ( $post_id ) {
        $quiz_meta = get_post_meta ( $post_id, "_sfwd-quiz", true );
        return $quiz_meta["sfwd-quiz_quiz_pro"];
    }

    /**
     * Quiz Data
     *
     * @param $quiz_id
     * @return array|null|object
     */
    public static function get_quiz_data ( $quiz_post_id, $quiz_id ) {
        global $wpdb;
        $questions = learndash_get_quiz_questions( $quiz_post_id );
        
        // The Loop
        $question_ids = array(0);
        foreach ( $questions as $key => $val ) {
            $question_ids[] = $val;
        }

        $qry = "SELECT * FROM " . \LDLMS_DB::get_table_name( 'quiz_question' ) . " WHERE id in( ".implode( ",", $question_ids )." ) and online=1";
        $question_data =  $wpdb->get_results ( $qry, ARRAY_A );

        $count = array();
        foreach ( $question_data as $q_data ) {
            $answers = unserialize ( $q_data["answer_data"] );
            $count[] = count ( $answers );
        }

        if( count( $count ) > 0 ) {
            return array ( "question_data" => $question_data, "max_answer" => max( $count ) );
        } else {
            return array ( "question_data" => $question_data, "max_answer" => 0 );
        }

    }
    
    /**
     * Quiz Data
     *
     * @param $quiz_id
     * @return array|null|object
     */
    public static function get_real_quiz_data ( $post_id, $quiz_id ) {
        global $wpdb;
        $questions = learndash_get_quiz_questions( $post_id );

        // The Loop
        $question_ids = array(0);
        foreach ( $questions as $key => $val ) {
            $question_ids[] = $val;
        }

        $questions = "SELECT * FROM " . \LDLMS_DB::get_table_name( 'quiz_question' ) . " WHERE  id in( ".implode( ",", $question_ids )." ) and online=1 order by `sort` asc";
        $question_data =  $wpdb->get_results ($questions, ARRAY_A );

        $count = array();
        foreach ( $question_data as $q_data ) {
            $answers = unserialize ( $q_data["answer_data"] );
            $count[] = count ( $answers );
        }

        if( count( $count ) > 0 ) {
            return array ( "question_data" => $question_data, "max_answer" => max( $count ) );
        } else {
            return array ( "question_data" => $question_data, "max_answer" => 0 );
        }
    }

    /**
     * Question Category
     *
     * @param $category_id
     * @return array|null|object
     */
    public static function get_category ( $category_id ) {
        global $wpdb;
        $category_method = "SELECT category_name FROM " . \LDLMS_DB::get_table_name( 'quiz_category' ) . " WHERE category_id = %d";
        $catgory =  $wpdb->get_results ( $wpdb->prepare( $category_method, $category_id ), ARRAY_A );
        if( is_array( $catgory ) && count( $catgory ) > 0 ) {
            if( isset( $catgory[0]["category_name"] ) && !empty( $catgory[0]["category_name"] ) ) {
                return $catgory[0]["category_name"];
            } 
        }
        
        return '';
    }

    /**
     * Get answer type
     *
     * @param $quiz_post_id
     * @param $question_id 
     * @return mixed
     */
    public static function get_answer_type ( $quiz_post_id, $question_id ) {
        $answer_type = "";
        $quiz_id = self::get_quiz_id ( $quiz_post_id );
        $quiz_data = self::get_quiz_data ( $quiz_post_id, $quiz_id );

        if( !empty( $quiz_data ) && is_array( $quiz_data ) ) {
            foreach ( $quiz_data["question_data"] as $q_data ) {
                if( isset( $q_data["id"] ) && $q_data["id"] ==  $question_id ) {
                    $answers = unserialize ( $q_data["answer_data"] );
                    for ( $loop_num = 1; $loop_num <= $quiz_data["max_answer"]; $loop_num++ ) {
                        if ( isset( $answers[$loop_num - 1] ) ) {
                            if( $q_data["answer_type"] == "cloze_answer" ||
                                $q_data["answer_type"] == "free_answer" ||
                                $q_data["answer_type"] == "essay" ||
                                $q_data["answer_type"] == "assessment_answer" ) {
                                $answer_type = "html";
                            } else {
                                if( $answers[$loop_num - 1]->isHtml() ) {
                                    $answer_type = "html";
                                } else {
                                    $answer_type = "text";
                                }
                            }
                        }
                    }
                    break;
                } else {
                    continue;
                }
            }
        }
        return $answer_type;
    }

    public static function convert_imgtag_to_export( $answer, $answer_type = '' ) {
        $new_answer = $answer;
        preg_match_all('/<img[^>]+>/i',$answer, $rawimagearray,PREG_SET_ORDER); 
        foreach( $rawimagearray as $img ) {
            
            preg_match_all('/(src)=("[^"]*")/i',$img[0], $img_info );
            
            $ImageSource = str_replace('"', '', $img_info[2][0]);

            if( 'assessment_answer' == $answer_type ) {
                $new_answer = str_replace( $img[0], $ImageSource, $new_answer );
            } else {
                $new_answer = str_replace( $img[0], '['.$ImageSource.']', $new_answer );
            }
        }

        return $new_answer;
    }

    /**
     * Export quiz in CSV format
     */
    public static function export_quiz() {
        
        if ( !isset( $_GET["quiz_export"] ) && !isset( $_GET["q_post_id"] ) )
            return;

        $exp_type = 'xls';
        $post_id = intval( $_GET["q_post_id"] );



        // create php excel object
        $doc = new Spreadsheet();

        // set active sheet 
        $doc->setActiveSheetIndex(0);

        $quiz_id = self::get_quiz_id ( $post_id );
        $quiz_data = self::get_real_quiz_data ( $post_id, $quiz_id );
        $sheet_name = get_the_title ( $post_id );
        $sheet_name = stripslashes( html_entity_decode ( $sheet_name ) );
        
        $header_row = array (
            "quiztitle" => "Quiz Title",
            "question_type" => "Question",
            "category" => "Category",
            "title" => "Title",
            "total_points" => "Total Point",
            "different_points_for_each_answer" => "Different Points for each answer",
            "question_text" => "Question Text",
            "answer_type" => "Answer Type"
        );

        $data_rows = array();
        $quiz_title = $sheet_name;

        foreach ( $quiz_data["question_data"] as $indexinner => $q_data ) {
            if( $indexinner > 0 ) {
                $quiz_title = '';
            }

            $rows = array (
                "quiztitle" => $quiz_title,
                "question_type" => $q_data["answer_type"],
                "category" => self::get_category ( $q_data["category_id"] ),
                "title" => $q_data["title"],
                "total_points" => $q_data["points"],
                "different_points_for_each_answer" => ( $q_data["answer_points_activated"] ) ? "yes" : "no",
                "question_text" => self::convert_imgtag_to_export( $q_data["question"] ),
                "answer_type" => self::get_answer_type ( $post_id, $q_data["id"] )
            );

            $answers = unserialize ( $q_data["answer_data"] );
           
            $correct_answer = '';
            $final_answer = '';
            for ( $loop_num = 1; $loop_num <= $quiz_data["max_answer"]; $loop_num++ ) {
                $header_row["answer" . $loop_num] = "Answer " . $loop_num;
                $header_row["point" . $loop_num] = "Point " . $loop_num;
                if ( isset( $answers[$loop_num - 1] ) ) 
                {
                    if( $q_data["answer_type"] == "cloze_answer" || $q_data["answer_type"] == "free_answer" || $q_data["answer_type"] == "assessment_answer" ) {
                        $correct_answer .= $answers[$loop_num - 1]->getAnswer();
                    } elseif( $q_data["answer_type"] == "essay" ) {
                        $essay_ans = rtrim( $answers[$loop_num - 1]->getGradedType() . " | " . $answers[$loop_num - 1]->getGradingProgression() , " |");
                        $correct_answer .= $essay_ans;
                    } else {
                        $answer = $answers[$loop_num - 1]->getAnswer();
                        if( $rows["answer_type"] == "html" ) {
                            $answer = self::convert_imgtag_to_export( $answer );
                        }

                        if( $q_data["answer_type"] == "matrix_sort_answer" ) {
                            $answerfld = self::convert_imgtag_to_export( $answers[$loop_num - 1]->getAnswer() );
                            $sfield = self::convert_imgtag_to_export( $answers[$loop_num - 1]->getSortString() );
                            if( ! empty( strval($answerfld) ) || strval($answerfld)=="0" )
                                $final_answer = "{".$answer."}{".$sfield."}";
                            else
                                $final_answer = "";
                        } else {
                            $final_answer = $answer;
                        }
                    }
                } else {
                    $final_answer = "";
                }

                if ( $q_data["answer_type"] == "cloze_answer" || $q_data["answer_type"] == "free_answer" || $q_data["answer_type"] == "assessment_answer" || $q_data["answer_type"] == "essay" ) {
                    $rows["answer" . $loop_num] =  "";
                } else {
                    $rows["answer" . $loop_num] =  ( $final_answer || $final_answer=='0' || $final_answer == 0 ) ? $final_answer : "";
                }

                $rows["point" . $loop_num] =  ( isset( $answers[$loop_num - 1] ) )  ? strval($answers[$loop_num - 1]->getPoints()) : " ";
                if ( $q_data["answer_type"] == "multiple" ) {
                    if ( isset( $answers[$loop_num - 1] ) && $answers[$loop_num - 1]->isCorrect() ) {
                        $correct_answer .= $loop_num . "|";
                    } else {
                        $correct_answer .= "|";
                    }
                } else {
                    if ( isset( $answers[$loop_num - 1] ) && $answers[$loop_num - 1]->isCorrect() ) {
                        $correct_answer .= $loop_num;
                    }
                }
            }

            $header_row["answer"] = "Answer";
            $header_row["toal_answer"] = "Total Answer";
            $header_row["message_with_correct_answer"] = "Message with correct answer";
            $header_row["message_with_incorrect_answer"] = "Message with incorrect answer";
            $header_row["hint"] = "Hint";

            $rows["answer"] = ( substr( $correct_answer, -1 ) == "|" ) ? substr_replace( $correct_answer, "", -1 ) : self::convert_imgtag_to_export( $correct_answer, $q_data["answer_type"] );
            $rows["total_answer"] = $quiz_data["max_answer"];
            $rows["message_with_correct_answer"] = self::convert_imgtag_to_export( $q_data["correct_msg"] );
            $rows["message_with_incorrect_answer"] = self::convert_imgtag_to_export( $q_data["incorrect_msg"] );
            if ( $q_data["tip_enabled"] ) {
                $rows["hint"] = self::convert_imgtag_to_export( $q_data["tip_msg"] );
            }
            if( count( $data_rows ) == 0 )
                $data_rows[] = $header_row;
            $data_rows[] = $rows;
        } 
        if( count( $data_rows ) == 0 ) {
            $data_rows[] = $header_row;
        }

        $doc->getActiveSheet()->fromArray( $data_rows );
        //$doc->getActiveSheet()->setTitle( addslashes( $sheet_name ) );

        $filename = 'quiz-'.date( "Y-m-d h:i:s" ).'.'.$exp_type;

        //force user to download the Excel file without writing it to server's HD
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
        header("Cache-Control: max-age=0");

        //mime type
        //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
        //if you want to save it as .XLSX Excel 2007 format

        if( trim( $exp_type ) == "xlsx" ) {
            $objWriter = new Xlsx($doc);
            $objWriter->setOffice2003Compatibility(true);
        } else {
            $objWriter = new Xls($doc);
        }

        $exports_total = intval( get_option( 'ld_exports_total' ) );
        if( intval( $exports_total ) == 0 || empty( $exports_total ) ) {
            $exports_total = 1;
        }   else {
            $exports_total += 1;
        }

        update_option( 'ld_exports_total', $exports_total );

        ob_clean();
        $objWriter->save('php://output');
        exit;
    }
}