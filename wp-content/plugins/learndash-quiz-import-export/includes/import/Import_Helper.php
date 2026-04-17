<?php
/**
 * Class MT_WpProQuiz_ImportXls
 */
if( class_exists( "WpProQuiz_Helper_ImportXml" ) ) {

    class LDQIE_ImportXls extends WpProQuiz_Helper_ImportXml {

        /**
         * Quiz Model from XML
         *
         * @param SimpleXMLElement $xml
         * @return null|WpProQuiz_Model_Quiz
         */
        private function createQuizModelXls () {

            $values = get_option( "quiz_default" );
            if( ! is_array( $values ) || count( $values ) == 0 ) {
                $values = array();
            }
            
            $quiz_title = get_option( "quiz_title" );

            $model = new WpProQuiz_Model_Quiz();
            
            $model->setName ( trim ( $quiz_title ) );
            $model->setTitleHidden ( ( array_key_exists( "titleHidden", $values) && !empty($values["titleHidden"]) ) ? false : true );
            $model->setQuestionRandom ( ( array_key_exists( "questionRandom", $values) && $values["questionRandom"] == "on" ) ? true : false );
            $model->setAnswerRandom ( ( array_key_exists( "answerRandom", $values) && $values["answerRandom"] == "on" ) ? true : false );
            
            $timeLimit = $values['timeLimit'];
            $timeLimitVal = 0;
            if( is_array( $timeLimit ) && count( $timeLimit ) > 0 ) {
                $timeLimitVal += (intval( $timeLimit['hh'] ) * 3600 ) + ( intval( $timeLimit['mm'] ) * 60 ) +  intval( $timeLimit['ss'] );
            }
            $model->setTimeLimit ( $timeLimitVal );
            $model->setTimeLimitCookie ( intval($values["timeLimitCookie"]) );
            
            $model->setQuizSummaryHide( trim($values['quizSummaryHide'])=='on'?false:true);
            
            $model->setResultText ( trim (  array_key_exists( "resultText", $values) ? $values["resultText"] : "" ) );
            $model->setResultGradeEnabled ( ( array_key_exists( "resultGradeEnabled", $values) && $values["resultGradeEnabled"] == "on" ) ? true : false );

            $model->setCategoryName ( trim ( "" ) );

            if ( $model->isResultGradeEnabled() ) {
                $resultArray = array ( "text" => array(), "prozent" => array() );
                $resultArray["text"] = trim ( ( string ) (  array_key_exists( "resultText", $values) ? $values["resultText"] : "" ) );
                $resultArray["prozent"] = trim ( ( string ) (   array_key_exists( "resultTextGrade", $values) ? $values["resultTextGrade"] : ""  ) );

                $model->setResultText ( $values["result_text"] );
            } else {
                $model->setResultText ( trim ( ( string ) array_key_exists( "resultText", $values) ? $values["resultText"] : "" ) );
            }
            
            $model->setShowPoints ( ( array_key_exists( "showPoints", $values) && $values["showPoints"] == "on" ) ? true : false );
            $model->setBtnRestartQuizHidden ( ( array_key_exists( "btnRestartQuizHidden", $values) && $values["btnRestartQuizHidden"] == "on" ) ? false : true );
            $model->setBtnViewQuestionHidden ( ( array_key_exists( "btnViewQuestionHidden", $values) && $values["btnViewQuestionHidden"] == "on" ) ? false : true );
            $model->setNumberedAnswer ( ( array_key_exists( "numberedAnswer", $values) && $values["numberedAnswer"] == "on" ) ? true : false );
            $model->setHideAnswerMessageBox ( ( array_key_exists( "hideAnswerMessageBox", $values) && $values["hideAnswerMessageBox"] == "on" ) ? false : true );
            $model->setDisabledAnswerMark ( ( array_key_exists( "disabledAnswerMark", $values) && $values["disabledAnswerMark"] == "on" ) ? false : true );
            
            if( array_key_exists( "statisticsOn", $values) && $values["statisticsOn"] == "on" ) {
                $model->setStatisticsOn ( ( array_key_exists( "statisticsOn", $values) && $values["statisticsOn"] == "on" ) ? true : false ); 
                $model->setViewProfileStatistics ( ( array_key_exists( "viewProfileStatistics", $values) && $values["viewProfileStatistics"] == "on" ) ? true : false );
            } else {
                $model->setStatisticsOn ( 0 ); 
                $model->setViewProfileStatistics ( 0 );
            }
            
            $model->setStatisticsIpLock ( ( int ) array_key_exists( "statisticsIpLock", $values) ? (intval($values['statisticsIpLock']) * 60): 0 );
            $model->setQuizRunOnce ( ( array_key_exists( "quizRunOnce", $values) && $values["quizRunOnce"] == "on" ) ? true : false );
            $model->setQuizRunOnceCookie ( ( array_key_exists( "quizRunOnceCookie", $values) && $values["quizRunOnceCookie"] == "on" ) ? true : false );
            $model->setQuizRunOnceType ( ( int ) array_key_exists( "quizRunOnceType", $values) ? $values["quizRunOnceType"] : "" );
            $model->setShowMaxQuestion ( ( array_key_exists( "showMaxQuestion", $values) && $values["showMaxQuestion"] == "on" ) ? true : false );
            $model->setShowMaxQuestionValue ( (int) array_key_exists( "showMaxQuestionValue", $values) ? $values["showMaxQuestionValue"] : "" );
            $model->setShowMaxQuestionPercent ( ( array_key_exists( "showMaxQuestionPercent", $values) && $values["showMaxQuestionPercent"] == "on" ) ? true : false );
            $model->setToplistActivated ( ( array_key_exists( "toplistActivated", $values) && $values["toplistActivated"] == "on" ) ? true : false );
            $model->setToplistDataAddPermissions ( (int) array_key_exists( "toplistDataAddPermissions", $values) ? $values["toplistDataAddPermissions"] : "" );
            $model->setToplistDataSort ( (int) array_key_exists( "toplistDataSort", $values) ? $values["toplistDataSort"] : "" );
            $model->setToplistDataAddMultiple ( ( array_key_exists( "toplistDataAddMultiple", $values) && $values["toplistDataAddMultiple"] == "on" ) ? true : false );
            $model->setToplistDataAddBlock ( (int) array_key_exists( "toplistDataAddBlock", $values) ? $values["toplistDataAddBlock"] : "" );
            $model->setToplistDataShowLimit ( (int) array_key_exists( "toplistDataShowLimit", $values) ? $values["toplistDataShowLimit"] : "" );
            $model->setToplistDataShowIn ( (int) array_key_exists( "toplistDataShowIn", $values) ? $values["toplistDataShowIn"] : "1" );
            $model->setToplistDataCaptcha ( false );
            $model->setToplistDataAddAutomatic ( ( array_key_exists( "toplistDataAddAutomatic", $values) && $values["toplistDataAddAutomatic"] == "on" ) ? true : false );
            $model->setShowAverageResult ( ( array_key_exists( "showAverageResult", $values) && $values["showAverageResult"] == "on" ) ? true : false );
            $model->setPrerequisite ( ( array_key_exists( "prerequisite", $values) && $values["prerequisite"] == "on" ) ? true : false );
            
            if( trim( $values['quizModus'] ) == "0" ) {
                if( trim( $values['quizModus_single_feedback'] )=='end' && trim( $values['quizModus_single_back_button'] )=='on' ) {
                    $model->setQuizModus ( 1 );
                } else if( trim( $values['quizModus_single_feedback'] )=='end' && empty( $values['quizModus_single_back_button'] ) ) {
                    $model->setQuizModus ( 0 ); 
                } else {
                    $model->setQuizModus ( 2 );
                }
            } else {
                $model->setQuizModus ( 3 );
            }
            
            $model->setShowReviewQuestion ( ( array_key_exists( "showReviewQuestion", $values) && $values["showReviewQuestion"] == "on" ) ? true : false );
            $model->setQuizSummaryHide ( ( array_key_exists( "quizSummaryHide", $values) && $values["quizSummaryHide"] == "on" ) ? false : true );
            $model->setSkipQuestionDisabled ( (  array_key_exists( "skipQuestionDisabled", $values) && $values["skipQuestionDisabled"] == "on" ) ?  false : true );
            $model->setEmailNotification ( (int) array_key_exists( "emailNotification", $values) ? $values["emailNotification"] : "" );
            $model->setUserEmailNotification ( ( array_key_exists( "userEmailNotification", $values) && $values["userEmailNotification"] == "on" ) ? true : false );
            $model->setShowCategoryScore ( ( array_key_exists( "showCategoryScore", $values) && $values["showCategoryScore"] == "on" ) ? true : false );
            $model->setHideResultCorrectQuestion ( ( array_key_exists( "hideResultCorrectQuestion", $values) && $values["hideResultCorrectQuestion"] == "on" ) ? false : true );
            $model->setHideResultQuizTime ( ( array_key_exists( "hideResultQuizTime", $values) && $values["hideResultQuizTime"] == "on" ) ? false : true );
            $model->setHideResultPoints ( ( array_key_exists( "hideResultPoints", $values) && $values["hideResultPoints"] == "on" ) ? false : true );
            $model->setAutostart ( ( array_key_exists( "autostart", $values) && $values["autostart"] == "on" ) ? true : false );
            $model->setForcingQuestionSolve ( ( array_key_exists( "forcingQuestionSolve", $values) && $values["forcingQuestionSolve"] == "on" ) ? true : false );
            $model->setHideQuestionPositionOverview ( ( array_key_exists( "hideQuestionPositionOverview", $values) && $values["hideQuestionPositionOverview"] == "on" ) ? false : true );
            $model->setHideQuestionNumbering ( ( array_key_exists( "hideQuestionNumbering", $values) && $values["hideQuestionNumbering"] == "on" ) ? false : true );

            $model->setStartOnlyRegisteredUser ( ( array_key_exists( "startOnlyRegisteredUser", $values) && $values["startOnlyRegisteredUser"] == "on" ) ? true : false );
            $model->setSortCategories ( ( array_key_exists( "sortCategories", $values) && $values["sortCategories"] == "on" ) ? true : false );
            $model->setShowCategory ( ( array_key_exists( "showCategory", $values) && $values["showCategory"] == "on" ) ? true : false );

            $model->setQuestionsPerPage ( (int) array_key_exists( "quizModus_multiple_questionsPerPage", $values) ? $values["quizModus_multiple_questionsPerPage"] : "" );

            $model->setFormActivated ( array_key_exists( "formActivated", $values) ? $values["formActivated"] : "" );
            $model->setFormShowPosition ( array_key_exists( "formShowPosition", $values) ? $values["formShowPosition"] : "" );
            
            if ( $model->getName() == "" ) {
                return null;
            }
            
            return $model;
        }

        /**
         * Question MOdel from XML
         *
         * @param DOMDocument $xml
         * @return NULL|WpProQuiz_Model_Question
         */
        private function createQuestionModelXml ( $question, $sortindex = 0 ) {
            $model = new WpProQuiz_Model_Question();

            $model->setTitle ( trim( $question["title"] ) );
            $model->setQuestion ( trim( $question["questionText"] ) );
            $model->setCorrectMsg ( trim( $question["correctMsg"] ) );
            $model->setIncorrectMsg ( trim( $question["incorrectMsg"] ) );
            $model->setAnswerType ( trim( $question["answerType"] ) );
            $model->setCorrectSameText ( $question["correctSameText"] );
            $model->setSort( $sortindex );
            $model->setTipMsg ( trim( $question["tipMsg"]["msg"] ) );

            if ( isset( $question["tipMsg"] ) && isset( $question["tipMsg"]["hint"] ) ) {
                $model->setTipEnabled ( $question["tipMsg"]["hint"] );
            }

            $model->setPoints ( $question["answer"]["points"] );
            $model->setShowPointsInBox ( $question["showPointsInBox"] );
            $model->setAnswerPointsActivated ( $question["answerPointsActivated"] );
            $model->setAnswerPointsDiffModusActivated ( $question["answerPointsDiffModusActivated"] );
            $model->setDisableCorrect ( $question["disableCorrect"] );
            $model->setCategoryName ( trim( $question["category"] ) );

            $answerData = array();

            if( isset( $question["answer"]["answerList"] ) ) {
                if( $question["answerType"] == "essay" ) {
                    $answer = $question["answer"]["answerList"];
 
                    $answerModel = new WpProQuiz_Model_AnswerTypes();
                    if ( isset( $answer["correct"] ) ) {
                        if( $answer["correct"] ) {
                            $answerModel->setCorrect ( $answer["correct"] );
                        } else {
                            $answerModel->setCorrect ( false );
                        }
                    } else {
                        $answerModel->setCorrect ( false );
                    }

                    if( isset( $question["answer"]["points"] ) ) {
                        $answerModel->setPoints ( $question["answer"]["points"] );
                    } else {
                        $answerModel->setPoints ( 0 );
                    }

                    if( isset( $answer["grade"]["gradeType"] ) ) {
                        $answerModel->setGraded ( true );
                        $answerModel->setGradedType ( trim( $answer["grade"]["gradeType"] ) );
                    }

                    if( isset( $answer["grade"]["gradeProgressions"] ) ) {
                        $answerModel->setGradingProgression ( trim( $answer["grade"]["gradeProgressions"] ) );
                    }

                    if( isset( $answer["stortText"]["text"] ) ) {
                        if( $answer["stortText"]["text"] ) {
                            $answerModel->setSortString ( trim ( $answer["stortText"]["text"] ) );
                        } else {
                            $answerModel->setSortString ( trim ( "" ) );
                        }
                    } else {
                        $answerModel->setSortString ( trim ( "" ) );
                    }

                    if ( isset( $answer["stortText"]["html"] ) ) {
                        if( $answer["stortText"]["html"] ) {
                            $answerModel->setHtml ( $answer["stortText"]["html"] );
                            $answerModel->setSortStringHtml ( $answer["stortText"]["html"] );
                        } else {
                            $answerModel->setSortStringHtml ( false );
                            $answerModel->setHtml ( false );
                        }
                    } else {
                        $answerModel->setHtml ( false );
                        $answerModel->setSortStringHtml ( false );
                    }

                    $answerData[] = $answerModel;
                } else {
                    foreach ( $question["answer"]["answerList"] as $answer ) {
                        $answerModel = new WpProQuiz_Model_AnswerTypes();
                        if ( isset( $answer["correct"] ) ) {
                            if( $answer["correct"] ) {
                                $answerModel->setCorrect ( $answer["correct"] );
                            } else {
                                $answerModel->setCorrect ( false );
                            }
                        } else {
                            $answerModel->setCorrect ( false );
                        }

                        if( isset( $answer["points"] ) ) {
                            $answerModel->setPoints ( $answer["points"] );
                        } else {
                            $answerModel->setPoints ( 0 );
                        }

                        if( isset( $answer["answerText"]["text"] ) ) {
                            $answerModel->setAnswer ( trim ( $answer["answerText"]["text"] ) );
                        } elseif( isset( $answer["text"] ) ) {
                            $answerModel->setAnswer ( trim ( $answer["text"] ) );
                        } else {
                            $answerModel->setAnswer ( trim ( "" ) );
                        }

                        if ( isset( $answer["answerText"]["html"] ) ) {
                            if( $answer["answerText"]["html"] ) {
                                $answerModel->setHtml ( $answer["answerText"]["html"] );
                            } else {
                                $answerModel->setHtml ( false );
                            }
                        } elseif ( isset( $answer["html"] ) ) {
                            if( $answer["html"] ) {
                                $answerModel->setHtml ( $answer["html"] );
                            } else {
                                $answerModel->setHtml ( false );
                            }
                        } else {
                            $answerModel->setHtml ( false );
                        }

                        if ( isset( $answer["answerText"]["html"] ) ) {
                            if( $answer["answerText"]["html"] ) {
                                $answerModel->setHtml ( $answer["answerText"]["html"] );
                            } else {
                                $answerModel->setHtml ( false );
                            }
                        } else {
                            $answerModel->setHtml ( false );
                        }

                        if( isset( $answer["gradeType"] ) ) {
                            $answerModel->setGraded ( true );
                            $answerModel->setGradedType ( trim( $answer["gradeType"] ) );
                        }

                        if( isset( $answer["gradeProgressions"] ) ) {
                            $answerModel->setGradingProgression ( trim( $answer["gradeProgressions"] ) );
                        }

                        if( isset( $answer["stortText"]["text"] ) ) {
                            if( $answer["stortText"]["text"] ) {
                                $answerModel->setSortString ( trim ( $answer["stortText"]["text"] ) );
                            } else {
                                $answerModel->setSortString ( trim ( "" ) );
                            }
                        } else {
                            $answerModel->setSortString ( trim ( "" ) );
                        }
                        
                        if ( isset( $answer["stortText"]["html"] ) ) {
                            if( $answer["stortText"]["html"] ) {
                                $answerModel->setHtml ( $answer["stortText"]["html"] );
                                $answerModel->setSortStringHtml ( $answer["stortText"]["html"] );
                            } else {
                                $answerModel->setSortStringHtml ( false );
                                $answerModel->setHtml ( false );
                            }
                        } else {
                            $answerModel->setHtml ( false );
                            $answerModel->setSortStringHtml ( false );
                        }

                        $answerData[] = $answerModel;
                    }
                }
            }
            
            $model->setAnswerData ( $answerData );

            //Check
            if ( trim ( $model->getAnswerType() ) == "" ) {
                return null;
            }

            if ( trim ( $model->getQuestion() ) == "" ) {
                return null;
            }

            if ( trim ( $model->getTitle() ) == "" ) {
                return null;
            }

            if ( count ( $model->getAnswerData() ) == 0 ) {
                return null;
            }
            return $model;
        }

        /**
         * Get Data to Import
         *
         * @param $quiz_xml
         * @return array|bool
         */
        public function getImportDataXls ( $quiz_xml ){

            $a = array( "master" => array(), "question" => array(), "forms" => array() );
            $i = 0;
            
            if ( !is_array( $quiz_xml ) || empty( $quiz_xml ) ) {
                _e( "Data could not be loaded.", "ldqie" );
                return false;
            }

            if ( is_array( $quiz_xml ) && !empty( $quiz_xml ) ) {
                $quizModel = $this->createQuizModelXls ();

                    if ( $quizModel !== null ) {
                        $quizModel->setId ( $i++ );

                        $a["master"][] = $quizModel;

                        foreach ( $quiz_xml as $quiz ) {
                        if ( !empty( $quiz ) ) {
                            $index = 0;
                            
                            foreach ( $quiz as $question ) {
                                $index++;
                                $questionModel = $this->createQuestionModelXml( $question, $index );

                                if( $question[ 'answerType' ] == 'matrix_sort_answer' ) {
                                    $answer_data = $questionModel->getAnswerData();
                                    $new_answer_data = array();
                                    foreach( $answer_data as $key=>$ansr ) {
                                        if( $ansr->getAnswer()=="0" && empty( $ansr->getSortString() ) ) {
                                            if( isset( $question[ 'answer' ] ) && is_array( $question[ 'answer' ] ) ) {
                                                if( isset( $question[ 'answer' ][ 'answerList' ] ) && isset( $question[ 'answer' ][ 'answerList' ] ) ) {
                                                    $answer = $question[ 'answer' ][ 'answerList' ][$key];
                                                    if( $answer ) {
                                                        if( isset( $answer["stortText"]["text"] ) ) {
                                                            $value = $answer["stortText"]["text"];
                                                        }
                                                        if( $value == '0' || $value == 0 )
                                                            $ansr->setSortString( "0" );
                                                    }
                                                    
                                                }
                                            }
                                            
                                        }
                                        if( trim( $ansr->getAnswer() ) != '' )
                                            $new_answer_data[] = $ansr;
                                    }
                                    
                                    if( count( $new_answer_data ) > 0 ) {
                                        $questionModel->setAnswerData( $new_answer_data );
                                    }
                                }
                                
                                if ( $questionModel !== null ) {
                                    $a["question"][$quizModel->getId()][] = $questionModel;
                                }
                            }
                        }
                    }
                }
            }
           
            return $a;
        }

        /**
         * Save form settings 
         *
         * @param $quiz_post_id quiz post id in post table
         * 
         * @return none
         */
        public function save_quiz_forms( $quiz_post_id ) {
            global $wpdb;
            $quiz_pro_id = get_post_meta ( $quiz_post_id, "quiz_pro_id", true );
            if( intval( $quiz_pro_id ) > 0 ) {


                $wpdb->delete ( LDLMS_DB::get_table_name('quiz_form'), array ( "quiz_id" => $quiz_pro_id ) );
   
                $values = get_option( "quiz_default" );
                $ldqieform = $values["ldqieform"];
                if( is_array( $ldqieform ) && count( $ldqieform ) > 0 ) {
                    $formMapper = new WpProQuiz_Model_FormMapper();
                    $sort = 0;
                    $forms = array();
                    foreach( $ldqieform as $frm ) {
                        $form = new WpProQuiz_Model_Form();
                        $form->setQuizId( $quiz_pro_id );
                        $form->setFieldname( $frm['field_name'] );
                        $form->setType( $frm['field_type'] );
                        $form->setRequired( ( $frm['required']=='on'?'1':'0') );
                        if(!empty( $frm['field_data'] ) ) {
                            $exploded = explode( PHP_EOL, trim( $frm['field_data'] ) );
                            $form->setData( $exploded );
                        }
                        
                        $form->setSort( $sort++ );
                        $forms[] = $form;
                    }
                    $formMapper->update( $forms );
                }
            }
        }

        public function saveQuestion( $question, $quiz_post_id=0 ) {

            $question_pro_id = $question->getId();
            $question_insert_post = array();
            $question_insert_post['post_type']    = learndash_get_post_type_slug( 'question' );
            $question_insert_post['post_status']  = 'publish';
            $question_insert_post['post_title']   = addslashes( $question->getTitle() );
            $question_insert_post['post_content'] = addslashes( $question->getQuestion() );
            $question_insert_post['menu_order'] = $question->getSort();
            $quiz_pro_id = $question->getQuizId();

            if ( ! empty( $quiz_pro_id ) ) {
				$quiz_post_ids = learndash_get_quiz_post_ids(  $quiz_pro_id );
			} else {
                $quiz_post_ids = array();
            }
            if ( ! empty( $quiz_post_ids ) ) {
                $quiz_post = get_post( $quiz_post_ids[0] );
                if ( ( $quiz_post ) && ( is_a( $quiz_post, 'WP_Post' ) ) ) {
                    $question_insert_post['post_author'] = $quiz_post->post_author;
                    $question_insert_post['post_date'] = $quiz_post->post_date;
                }
            }

            $question_insert_post_id = wp_insert_post( $question_insert_post );

            if ( ( $question_insert_post_id ) && ( ! is_wp_error( $question_insert_post_id ) ) ) {
				learndash_proquiz_sync_question_fields( $question_insert_post_id, $question );
				//learndash_proquiz_sync_question_category( $question_insert_post_id, $question_pro );

				if ( ( $question ) && ( is_a( $question, 'WpProQuiz_Model_Question' ) ) ) {
					// Create the association between the question post and the quiz post(s).
					if ( ( ! empty( $quiz_pro_id ) ) && ( ! empty( $quiz_post_ids ) ) ) {
						foreach ( $quiz_post_ids as $idx => $quiz_post_id ) {
							if ( 0 === $idx ) {
								learndash_update_setting( $question_insert_post_id, 'quiz', absint( $quiz_post_id ) );
								$quiz_primary_post_id = learndash_get_quiz_primary_shared( $quiz_pro_id, false );
								if ( empty( $quiz_primary_post_id ) ) {
									update_post_meta( $quiz_post_id, 'quiz_pro_primary_' . $quiz_pro_id, $quiz_pro_id );
								}

								if ( ( isset( $quiz_builder_option['shared_questions'] ) ) && ( 'yes' !== $quiz_builder_option[ 'shared_questions'] ) ) {
									break;
								}
                            }
                            
							add_post_meta( $question_insert_post_id, 'ld_quiz_' . absint( $quiz_post_id ), absint( $quiz_post_id ), true );
						}
					}
                }
                update_post_meta( $question_insert_post_id, 'quiz_id', absint( $quiz_post_id ) );
                learndash_set_question_quizzes_dirty( $question_insert_post_id );
                learndash_proquiz_sync_question_fields( $question_insert_post_id, $question_pro_id );
                learndash_proquiz_sync_question_category( $question_insert_post_id, $question_pro_id );
            }

            return $question_insert_post_id;
        }

        /**
         * Updated the questions list in meta
         *
         * @param $questions
         * @param $quiz_post_id
         */
        public function saveQuizMeta( $questions, $quiz_post_id=0 ) {
            
            $values = get_option( "quiz_default" );
            if( trim( $values['DiscardOldQuestion'] ) == 'yes' ) {
                update_post_meta( $quiz_post_id, 'ld_quiz_questions', $questions );
            } else {
                $qst_final = get_post_meta( $quiz_post_id, 'ld_quiz_questions',true );
                foreach( $questions as $key=>$value ) {
                    $qst_final[ $key ] = $value;
                }
                
                update_post_meta( $quiz_post_id, 'ld_quiz_questions', $qst_final );
            }
        }

        /**
         * Modified categoryMapper function for case sensitive category names issue
         *
         * @return array
         */
        public function getCategoryArrayForImport() {
            global $wpdb;
            $tableCategory = LDLMS_DB::get_table_name( 'quiz_category' );

            $r = array();

            $results = $wpdb->get_results("SELECT * FROM {$tableCategory}", ARRAY_A);

            foreach ($results as $row) {
                $r[$row['category_name']] = (int)$row['category_id'];
            }

            return $r;
        }

        /**
         * Import Quiz to DB
         *
         * @param $ids
         * @param $quiz_xml
         * @return int|WP_Error
         */
        public function saveImportXls ( $ids, $quiz_xml ) {
            $quizMapper = new WpProQuiz_Model_QuizMapper();
            $questionMapper = new WpProQuiz_Model_QuestionMapper();
            $categoryMapper = new WpProQuiz_Model_CategoryMapper();
            $formMapper = new WpProQuiz_Model_FormMapper();
            
            $data = $this->getImportDataXls ( $quiz_xml );
            $categoryArray = $this->getCategoryArrayForImport();
            $r_post_id = 0;
            foreach ( $data["master"] as $quiz ) {
                if ( get_class($quiz) !== "WpProQuiz_Model_Quiz" )
                    continue;
                $questions_ids = [];
                $oldId = $quiz->getId();

                if( $ids !== false && ! in_array ( $oldId, $ids ) )
                    continue;

                $quiz->setId ( 0 );

                $user_id = get_current_user_id();

                global $wpdb;
                $post_exists = $wpdb->get_results( "SELECT ID FROM `".$wpdb->prefix."posts` WHERE post_title = '". get_option( 'quiz_title' ) ."' and post_type = 'sfwd-quiz' and post_status = 'publish'", OBJECT );
                if ( $post_exists ) {
                    if( is_array( $post_exists ) ) {
                        foreach( $post_exists as $post ) {
                            $post_exists = $post->ID;
                            break;
                        }
                    }

                    $ld_quiz_pro_id = get_post_meta ( $post_exists, "quiz_pro_id", true );
                    
                    $quiz->setId ( $ld_quiz_pro_id );
                    $quizMapper->save ( $quiz );
                    $r_post_id = $post_exists;
                    global $wpdb;
                    $values = get_option( "quiz_default" );
                    $update_array = array (
                        "ID"           => $post_exists,
                        "post_title"   => get_option( "quiz_title" ),
                        "post_content"   => array_key_exists( "text", $values ) ? $values["text"] : "",
                        "post_type" => "sfwd-quiz",
                        "post_status" => "publish",
                        "post_author" => $user_id
                    );

                    if( isset( $values["prerequisiteList"] ) && !empty( $values["prerequisiteList"] ) ) {
                        $prerequisiteMapper = new WpProQuiz_Model_PrerequisiteMapper();
                        $prerequisiteMapper->delete($ld_quiz_pro_id);
                        $prerequisiteMapper->save($ld_quiz_pro_id, $values["prerequisiteList"]);
                    }

                    wp_update_post ( $update_array );
                    learndash_update_setting ( $post_exists, "quiz_pro", $ld_quiz_pro_id );

                    $sort = 1;

                    if( trim( $values['DiscardOldQuestion'] ) == 'yes' ) {

                        $questionMapper->deleteByQuizId($ld_quiz_pro_id);

                        $questions_post_ids = learndash_get_quiz_questions($post_exists);
                        if(!empty($questions_post_ids)) {
                            $questions_post_ids = array_keys($questions_post_ids);
                        }

                        foreach( $questions_post_ids as $question_post_id ) {
                            wp_delete_post( $question_post_id, true );
                        }

                    } else {

                        $maxSort = $questionMapper->getMaxSort($ld_quiz_pro_id);

                        if( !empty($maxSort) ) {
                            $sort = absint($maxSort)+1;
                        }
                    }

                    if ( is_array ( $data["question"][$oldId] ) && count( $data["question"][$oldId] ) > 0 ) {

                        foreach ( $data["question"][$oldId] as $question ) {
                            
                            if ( get_class ( $question ) !== "WpProQuiz_Model_Question" )
                                continue;

                            $question->setQuizId ( $ld_quiz_pro_id );
                            //$question->setId ( 0 );
                            $question->setSort ( $sort );
                            $question->setCategoryId ( 0 );
                            if ( trim ( $question->getCategoryName() ) != "" ) {
                                if ( isset ( $categoryArray[ $question->getCategoryName() ] ) ) {
                                    $question->setCategoryId ( $categoryArray[ $question->getCategoryName() ] );
                                } else {
                                    $categoryModel = new WpProQuiz_Model_Category();
                                    $categoryModel->setCategoryName ( $question->getCategoryName() );
                                    $categoryMapper->save ( $categoryModel );

                                    $question->setCategoryId ( $categoryModel->getCategoryId() );

                                    $categoryArray[ $question->getCategoryName() ] = $categoryModel->getCategoryId();
                                }
                            }
                            
                            $questionMapper->save ( $question );
                            
                            $questions_ins_id = $this->saveQuestion( $question, $post_exists );
                            $questions_ids[$questions_ins_id] = $question->getId();

                            $sort++;
                        }
                    }

                    $this->save_quiz_forms( $post_exists );

                } else {
                    $quizMapper->save ( $quiz );
                    $values = get_option('quiz_default');
                    $quiz_post_id = wp_insert_post ( array (
                        "post_title" => get_option('quiz_title'),
                        "post_content"   => ( isset( $values['text'] ) && !empty( $values['text'] ) ) ? $values['text'] : "",
                        "post_type" => "sfwd-quiz",
                        "post_status" => "publish",
                        "post_author" => $user_id
                    ) );
                    
                    $r_post_id = $quiz_post_id;
                    
                    update_post_meta($quiz_post_id, "_timeLimitCookie", $values['timeLimitCookie']);
                    update_post_meta($quiz_post_id, "quiz_pro_id", $quiz->getId());
                    learndash_update_setting ( $quiz_post_id, "quiz_pro", $quiz->getId() );
                    if( isset( $values["prerequisiteList"] ) && !empty( $values["prerequisiteList"] ) ) {
                        $prerequisiteMapper = new WpProQuiz_Model_PrerequisiteMapper();
                        $prerequisiteMapper->save($quiz->getId(), $values["prerequisiteList"]);
                    }

                    if ( isset ( $data["forms"] ) && isset( $data["forms"][$oldId] ) ) {
                        $sort = 0;

                        foreach ( $data["forms"][$oldId] as $form ) {
                            $form->setQuizId ( $quiz->getId() );
                            $form->setSort ( $sort++ );
                        }

                        $formMapper->update ( $data["forms"][$oldId] );
                    }

                    $sort = 1;
                    if ( is_array( $data["question"][$oldId] ) && count( $data["question"][$oldId] ) > 0 ) {
                        
                        foreach ( $data["question"][$oldId] as $question ) {
                            
                            if ( get_class ( $question ) !== "WpProQuiz_Model_Question" )
                                continue;

                            $question->setQuizId ( $quiz->getId() );
                            $question->setId ( 0 );
                            $question->setSort ( $sort );
                            $question->setCategoryId ( 0 );
                            if ( trim ( $question->getCategoryName() ) != "" ) {
                                if ( isset ( $categoryArray[ $question->getCategoryName() ] ) ) {
                                    $question->setCategoryId ( $categoryArray[ $question->getCategoryName() ] );
                                } else {
                                    $categoryModel = new WpProQuiz_Model_Category();
                                    $categoryModel->setCategoryName ( $question->getCategoryName() );
                                    $categoryMapper->save ( $categoryModel );

                                    $question->setCategoryId ( $categoryModel->getCategoryId() );

                                    $categoryArray[ $question->getCategoryName() ] = $categoryModel->getCategoryId();
                                }
                            }

                            $questionMapper->save ( $question );
                            $questions_ins_id = $this->saveQuestion( $question, $quiz_post_id );
                            $questions_ids[ $questions_ins_id] = $question->getId();

                            $sort++;
                        }
                    }

                    $this->save_quiz_forms( $quiz_post_id );
                }

                $this->saveQuizMeta( $questions_ids, $r_post_id );
            }

            return array( $r_post_id, $questions_ids );
        }
    }
}