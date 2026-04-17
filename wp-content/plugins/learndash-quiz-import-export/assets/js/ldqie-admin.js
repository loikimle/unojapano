jQuery(document).ready( function ($) {
    $( "#setting_tabs" ).tabs().parents(".ldqie_settings_wrapper").show();

    $('.ld-single-quiz-select2-ddl').select2({
        placeholder: 'Select an option',dropdownAutoWidth : true, allowClear:true
    });

    $('#frm_ldqie #quiz_time_limit_enabled').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#ldqie_timelimit_extra_opts').css('display', 'block');
        } else {
            $('#ldqie_timelimit_extra_opts').css('display', 'none');
        }
    }).trigger('change');

    $('#frm_ldqie #retry_restrictions').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#ldqie_retry_restrictions_extra_opts').css('display', 'block');
        } else {
            $('#ldqie_retry_restrictions_extra_opts').css('display', 'none');
        }
    }).trigger('change');

    $('#frm_ldqie #quizRunOnceType').on( 'change', function() {
        
        var selected_val = $(this).val();
        if( selected_val == '2' ) {
            $('#quizRunOnceCookie_detail').css('display', 'none');
        } else {
            $('#quizRunOnceCookie_detail').css('display', 'block');
        }
    }).trigger('change');

    $('#frm_ldqie #questionRandom').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#showMaxQuestion_opts_detail').css('display', 'block');
        } else {
            $('#showMaxQuestion_opts_detail').css('display', 'none');
        }
    }).trigger('change');

    $('#frm_ldqie .showMaxQuestion').on( 'change', function() {
        var is_selected = $(this).val();
        if( is_selected == 'on' ) {
            $('#showMaxQuestion_detail').css('display', 'block');
        } else {
            $('#showMaxQuestion_detail').css('display', 'none');
        }
    }).trigger('change');

    $('#frm_ldqie #quiz_result_messages').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#results_text_activation_div').css('display', 'block');
        } else {
            $('#results_text_activation_div').css('display', 'none');
        }
    }).trigger('change');

    $('#frm_ldqie #custom_result_data_display').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#custom_result_data_display_detail').css('display', 'block');
        } else {
            $('#custom_result_data_display_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #custom_answer_feedback').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#custom_answer_feedback_detail').css('display', 'block');
        } else {
            $('#custom_answer_feedback_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #formActivated').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('.custom-field-container').css('display', 'block');
            $('.lbl_formShowPosition_section').css('display', 'block');
        } else {
            $('.custom-field-container').css('display', 'none');
            $('.lbl_formShowPosition_section').css('display', 'none');
        }
    }).trigger('change');

    $('#frm_ldqie #toplistDataAddMultiple').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#toplistDataAddMultiple_detail').css('display', 'block');
        } else {
            $('#toplistDataAddMultiple_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #toplistDataShowIn_enabled').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#toplistDataShowIn_enabled_detail').css('display', 'block');
        } else {
            $('#toplistDataShowIn_enabled_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #statisticsOn').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#statisticsOn_detail').css('display', 'block');
        } else {
            $('#statisticsOn_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #statisticsIpLock_enabled').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#statisticsIpLock_enabled_detail').css('display', 'block');
        } else {
            $('#statisticsIpLock_enabled_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #email_enabled').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#email_enabled_detail').css('display', 'block');
        } else {
            $('#email_enabled_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #email_enabled_admin').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#email_enabled_admin_detail').css('display', 'block');
        } else {
            $('#email_enabled_admin_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #advanced_settings').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#advanced_settings_detail').css('display', 'block');
        } else {
            $('#advanced_settings_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #timeLimitCookie_enabled').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#timeLimitCookie_enabled_detail').css('display', 'block');
        } else {
            $('#timeLimitCookie_enabled_detail').css('display', 'none');
        }
    }).trigger('change');

    $('#frm_ldqie #associated_settings_enabled').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#associated_settings_enabled_detail').css('display', 'block');
        } else {
            $('#associated_settings_enabled_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #quiz_materials_enabled').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#frm_ldqie #lbl_quiz_materials').css('display', 'none');
            $('#frm_ldqie #quiz_materials').css('display', 'block');
        } else {
            $('#frm_ldqie #lbl_quiz_materials').css('display', 'none');
            $('#frm_ldqie #quiz_materials').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #quizModus').on( 'change', function() {
        var is_selected = $(this).val();
        if( is_selected == '3' ) {
            $('#quizModus_multiple_questionsPerPage_detail').css('display', 'block');
            $('.ldqie_sub_quizModus_table').css('display', 'none');
        } else if( is_selected == '0' ) {
            $('#quizModus_multiple_questionsPerPage_detail').css('display', 'none');
            $('.ldqie_sub_quizModus_table').css('display', 'block');
        }
    }).trigger('change');
    
    $('#frm_ldqie .quizModus_single_feedback').on( 'click', function() {
        var is_selected = $(this).val();
        if( is_selected == 'end' ) {
            $('#quizModus_single_back_button_div').css('display', 'block');
        } else {
            $('#quizModus_single_back_button_div').css('display', 'none');
        }
    });
    

    $('#frm_ldqie #toplistActivated').on( 'change', function() {
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#toplistActivated_detail').css('display', 'block');
        } else {
            $('#toplistActivated_detail').css('display', 'none');
        }
    }).trigger('change');
    


    $('#frm_ldqie #showReviewQuestion').on( 'change', function() {
        
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#showReviewQuestion_detail').css('display', 'block');
        } else {
            $('#showReviewQuestion_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #custom_sorting').on( 'change', function() {
        
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#custom_sorting_detail').css('display', 'block');
        } else {
            $('#custom_sorting_detail').css('display', 'none');
        }
    }).trigger('change');
    
    $('#frm_ldqie #custom_question_elements').on( 'change', function() {
        
        var is_selected = $(this).attr('checked');
        if( is_selected ) {
            $('#custom_question_elements_detail').css('display', 'block');
        } else {
            $('#custom_question_elements_detail').css('display', 'none');
        }
    }).trigger('change');
    

    $( '#learndash-quiz-access-settings_course' ).on( 'change', function() {
        var courses = $( '#learndash-quiz-access-settings_course' ).select2("val");
        
        $.ajax({
            type : "post",
            url : ldqieQuizVars.ajax_url,
            data : { action: "ldqie_load_lessons",  course_ids : courses },
            dataType: "json",
            success: function( response ) {
                $( '#learndash-quiz-access-settings_lesson' ).select2( 'destroy' ).empty( ).select2( { data: response, placeholder: 'Select an option', ropdownAutoWidth : true,allowClear:true } );
                $( '#learndash-quiz-access-settings_lesson' ).trigger('change');
            }
        });
    } );

    $('#frm_ldqie #certificate').on( 'change', function() {
        
        var selected_val = $(this).val();
        if( selected_val ) {
            $('#ldqie_certificate_extra_opts').css('display', 'block');
        } else {
            $('#ldqie_certificate_extra_opts').css('display', 'none');
        }
    }).trigger('change');
    

    $('#my_checkbox_id').change( function() {
        
        console.log($(this).attr('checked'));
    }).trigger('change');

    $('#resultGradeEnabled').change( function() {
        
        if( $('#resultGradeEnabled').prop('checked') ) {
            $('#results_text_activation').css('display', 'block');
            $('#results_text_no_activation').css('display', 'none');
        } else {
            $('#results_text_activation').css('display', 'none');
            $('#results_text_no_activation').css('display', 'block');
        }
    }).trigger('change');
    
    $('.ldqie_small_text').change( function() {
        var Prozent = $( this ).val();
        if( parseInt( Prozent ) > 100 ) {
            Prozent = 100;
            $( this ).val( Prozent );
        }
        if( parseInt( Prozent ) < 0 ) {
            Prozent = 0;
            $( this ).val( Prozent );
        }

        $( this ).parent().find('.resultProzent').html( Prozent );
    }).trigger('change');
    
    $('.checkbox:checkbox:checked').each( function ( index, value ) {
        var self = $(value);
        $( "#setting_tabs table tr." + self.attr( "name" ) ).show();
    });

    $( "#setting_tabs input[type='checkbox']" ).click( function() {
        if( $( this ).prop( "checked" ) == true ) {
            $( "#setting_tabs table tr." + $(this).attr( "name" ) ).show();
        } else {
            $( "#setting_tabs table tr." + $(this).attr( "name" ) ).toggle( this.checked );
        }
    });

    $( ".ldqie-notice-success, .ldqie-notice-warning" ).removeClass( "hidden" );

    $( ".custom-ui-sortable" ).sortable();
    $( ".custom-ui-sortable" ).disableSelection();
});

/*
    By Osvaldas Valutis, www.osvaldas.info
    Available for use under the MIT License
*/

'use strict';

;( function( $, window, document, undefined )
{
    jQuery( '.inputfile' ).each( function()
    {
        var $input   = $( this ),
            $label   = $input.next( 'label' ),
            labelVal = $label.html();

        $input.on( 'change', function( e )
        {
            var fileName = '';

            if( this.files && this.files.length > 1 )
                fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
            else if( e.target.value )
                fileName = e.target.value.split( '\\' ).pop();

            if( fileName )
                $label.find( 'span' ).html( fileName );
            else
                $label.html( labelVal );
        });

        // Firefox bug fix
        $input
        .on( 'focus', function(){ $input.addClass( 'has-focus' ); })
        .on( 'blur', function(){ $input.removeClass( 'has-focus' ); });
    });
})( jQuery, window, document );
