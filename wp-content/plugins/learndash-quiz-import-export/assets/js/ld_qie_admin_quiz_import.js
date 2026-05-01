(function( $ ) { 'use strict';
    $( document ).ready( function() {
        var LD_QIE_Admin = {
            questionContent: $('#add_questions_content'),
            questionTable: $('#add_questions_table'),
            allowedColumns: ['B','C','H','E','G'],
            existingQuestions: [],
            init: function() {

                $('#ld_qie_import_quiz_form').on('submit', LD_QIE_Admin.submitImportQuestionsForm);
                $(document).on('click', '#ld_qie_add_question_button', LD_QIE_Admin.addQuestionRow);
                $(document).on('click', '#ld_qie_cancel_process_questions_button', LD_QIE_Admin.cancelProcessQuestions);
                $(document).on('click', '.ld_qie_delete a', LD_QIE_Admin.removeQuestionRow);
                LD_QIE_Admin.resetForm();
                LD_QIE_Admin.initSelect2();
            },
            cancelProcessQuestions: function(e) {
                e.preventDefault();
                LD_QIE_Admin.resetForm();
            },
            removeQuestionRow: function(e) {
                e.preventDefault();

                var question_id = $(this).data('question_id');

                //Remove table row
                $(this).parent('td.ld_qie_delete').parent('tr').remove();

                //Remove question_id from selected question_ids array
                LD_QIE_Admin.existingQuestions = LD_QIE_Admin.existingQuestions.filter(function(value) {
                    return question_id != value;
                });
            },
            submitImportQuestionsForm: function(e) {
                e.preventDefault();

                var formData = new FormData($(this)[0]);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    cache: false,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    beforeSend: function( jqXHR, settings ) {
                        $('#ld_qie_loader_spinner').show();
                        $('#ld_qie_import_messages').html('');
                    },
                    success: function ( data, textStatus, jqXHR ) {

                        var current_action = $('#ld_qie_action').val();
                        if(current_action == 'ld_qie_import_quiz') {
                            $('#ld_qie_action').val('ld_qie_import_questions');
                            $('#file_upload_content').hide();

                            LD_QIE_Admin.processTable(data.data);
                            LD_QIE_Admin.questionContent.show();
                        } else {
                            LD_QIE_Admin.showMessage(data.status, data.data.message);
                            LD_QIE_Admin.resetForm();

                        }
                    },
                    error: function( jqXHR, textStatus, errorThrown ) {
                        $('#ldqie_import_file').val('').change();
                        LD_QIE_Admin.showMessage(jqXHR.responseJSON.status, jqXHR.responseJSON.message);
                    },
                    complete: function(jqXHR, textStatus) {
                        $('#ld_qie_loader_spinner').hide();
                    }
                });
            },
            resetForm: function() {
                $('#file_upload_content').show();
                LD_QIE_Admin.questionContent.hide();
                LD_QIE_Admin.questionTable.html('');
                if(ldqieQuizVars.allowExistingQuestionImport) {
                    $('#ld_qie_action').val('ld_qie_import_quiz');
                } else {
                    $('#ld_qie_action').val('ld_qie_import_questions');
                }
                $('#ldqie_import_file').val('').change();
                LD_QIE_Admin.existingQuestions = [];
            },
            showMessage:function(status, message) {
                $('#ld_qie_import_messages').html( '<div class="notice notice-' + status + ' is-dismissible"><p>' + message + '</p><!--<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>--></div>' );
            },
            processTable: function(data) {

                var html = '<table class="table ld_qie_table" style="display: none;">';
                    html += '<tr>';
                    html += '<th>#</th>';
                    $.each(data, function (col, col_value) {
                        if(LD_QIE_Admin.allowedColumns.includes(col)) {
                            html += '<th>' + col_value + '</th>';
                        }
                    });
                    html += '<th></th>';
                    html += '</tr>';
                    html += '</table>';

                    LD_QIE_Admin.questionTable.html(html);
            },
            addQuestionRow: function(e) {
                e.preventDefault();
                var question_id = $('#ld_qie_selected_question_id').val();

                if(!question_id) {
                    return;
                }

                if( !LD_QIE_Admin.existingQuestions.includes(question_id) ) {
                    LD_QIE_Admin.existingQuestions.push(question_id);
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'ld_qie_get_question_details',
                        question_id: question_id,
                    },
                    beforeSend: function( jqXHR, settings ) {
                        $('#ld_qie_loader_spinner').show();
                    },
                    success: function ( response, textStatus, jqXHR ) {

                        var i;
                        var html = '';
                        var row_number = LD_QIE_Admin.questionTable.find('tr').length;
                        var allowed_cols = [
                            'answer_type',
                            'category_name',
                            'title',
                            'points',
                            'question'
                        ];

                        html += '<tr>';
                        html += '<td>' + row_number + '</td>';

                        $.each(response.data, function(col, value) {
                            if(allowed_cols.includes(col)) {
                                html += '<td>' + value + '</td>';
                            }
                        });

                        html += '<td class="ld_qie_delete"><a href="#delete" data-question_id="' + question_id + '">X</a><input type="hidden" name="question_ids[]" value="' + question_id + '"></td>';
                        html += '</tr>';

                        LD_QIE_Admin.questionTable.find('tr:last').after(html);

                        if(row_number == 1) {
                            $('.ld_qie_table').show();
                        }

                        var select = $('#ld_qie_qestion_search');
                        select.val('').change();
                        $('#ld_qie_selected_question_id').val('');
                    },
                    error: function( jqXHR, textStatus, errorThrown ) {
                        LD_QIE_Admin.showMessage(jqXHR.responseJSON.status, jqXHR.responseJSON.message);
                    },
                    complete: function(jqXHR, textStatus) {
                        $('#ld_qie_loader_spinner').hide();
                    }

                });
            },
            initSelect2: function() {
                var select = $('#ld_qie_qestion_search');
                    $(select).select2({
                        minimumInputLength: 3,
                        ajax: {
                            url: ajaxurl,
                            dataType: 'json',
                            data: function (params) {
                                var query = {
                                    search: params.term,
                                    page: params.page || 1,
                                    action: 'ld_qie_search_questions',
                                    existing_question_ids: LD_QIE_Admin.existingQuestions,
                                };

                                return query;
                            },
                            processResults: function (data, params) {
                                params.page = params.page || 1;

                                return {
                                    results: data.data,
                                    pagination: {
                                        more: (params.page * 10) < data.count_filtered
                                    }
                                };
                            }
                        }
                    });

                    $(select).on('select2:select', function (e) {
                        var data = e.params.data;
                        var question_id = data.id;

                        if( !LD_QIE_Admin.existingQuestions.includes(question_id) ) {
                            LD_QIE_Admin.existingQuestions.push(question_id);
                        }

                        $('#ld_qie_selected_question_id').val(question_id);
                    });

            }
        };

        LD_QIE_Admin.init();
    });
})( jQuery );