jQuery(document).ready(function() {
    var reports_table = '';

	// tab-content
	jQuery('#wdm_ldgr_show_report').on('click', function(e){
		// e.preventDefault();
        // alert(ajax_object.group_id);
        if(jQuery('#wdm_ldgr_course_id').val() == ''){
            alert(ajax_object.course_not_selected);
            return;
        }

        // send ajax request to create table
        var data = {
            'action': 'wdm_lgdr_create_report_table',
            'course_id': jQuery('#wdm_ldgr_course_id').val(),
            'group_id': ajax_object.group_id
        };
        jQuery.ajax({
            url: ajax_object.ajax_url,
            data: data,
            type: 'post',
            dataType: 'json',
            beforeSend: function(){
                jQuery('#wdm-ldgr-overlay').css('display', 'block');
            },
            success: function(response){
                // check if rewards column is to be created
                var show_rewards = response.rewards;
                // // columns array
                var columns = [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": '<span class="dashicons dashicons-arrow-down-alt2"></span>',
                        "width" :  "10%"
                    },
                    {
                        "data": "name",
                        "orderable": false,
                        "className": "dt-body-left name"
                    },
                    {
                        "data": "email_id",
                        "orderable": false,
                        "className": "dt-body-left email" 
                    },
                    {
                        "data": "course_progress",
                        "orderable": false,
                        "className": "dt-body-center dt-head-center course-progress"
                    },
                    // { "data": "last_name", "orderable": false, "className": "dt-body-left", width: '160px' },
                    // { "data": "course_status", "orderable": false, "className": "dt-body-center dt-head-center", width: '160px' }
                    
                ];

                if(show_rewards) {
                    columns.push(
                        { 
                            "data": "reward",
                            "orderable": false,
                            "className": "dt-body-center dt-head-center rewards"
                        }
                    );
                }

                jQuery('#wdm_ldgr_group_report_wrapper').remove();

                jQuery('#tab-3').append(response.table);

                reports_table = jQuery('#wdm_ldgr_group_report').DataTable(  {
                    "responsive": true,
                    "autoWidth": false,
                    "searching": false,
                    "processing": true,
                    "serverSide": true,
                    "fixedColumns": true,
                    "order" : [],
                    "columns": columns,
                    "ajax": {
                        "url": ajax_object.ajax_url,// + "?action=wdm_display_ldgr_group_report&course_id="+jQuery('#wdm_ldgr_course_id').val()+'&group_id='+ajax_object.group_id,
                        "type": "POST",
                        "data": {
                            "action": "wdm_display_ldgr_group_report",
                            "course_id": jQuery('#wdm_ldgr_course_id').val(),
                            "group_id": ajax_object.group_id,
                            "show_rewards": show_rewards
                        },
                        "beforeSend" : function(){
                            jQuery('#wdm-ldgr-overlay').css('display', 'block');
                        },
                        "dataSrc": function(json){
                            jQuery('#wdm-ldgr-overlay').css('display', 'none');
                            jQuery('#wdm_ldgr_group_report').wrap('<div class="wdm-table-container"></div>');
                            return json.data;
                        }
                    },
                    createdRow: function(row, data, dataIndex) {
                          jQuery(row).find('td:eq(0)').attr('data-title', '');
                          jQuery(row).find('td:eq(1)').attr('data-title', 'Name');
                          jQuery(row).find('td:eq(2)').attr('data-title', 'Email Id');
                          jQuery(row).find('td:eq(3)').attr('data-title', 'Course Progress');
                          jQuery(row).find('td:eq(4)').attr('data-title', 'Rewards');
                          jQuery(row).find('td').wrapInner('<span></span>');
                      }
                });
            },
        });

    });
    
    // Add event listener for opening and closing details
    jQuery('#tab-3').on('click', '#wdm_ldgr_group_report tbody td.details-control', function () {
        var tr = jQuery(this).closest('tr');
        
        var row = reports_table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
            tr.find('span.dashicons').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
        }
        else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
            tr.find('span.dashicons').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
        }
    } );

    /* Formatting function for row details */
    function format ( d ) {
        if (d.course_report.length) {
            return d.course_report;
        }
        return '';
    }

    // User statistics
    jQuery('#tab-3').on('click', '#wdm_ldgr_group_report tbody td a.user_statistic', show_user_statistic);
    
    function show_user_statistic( e ) {
		e.preventDefault();
		
		var refId 				= 	jQuery(this).data('ref_id');
		var quizId 				= 	jQuery(this).data('quiz_id');
		var userId 				= 	jQuery(this).data('user_id');
		var statistic_nonce 	= 	jQuery(this).data('statistic_nonce');
		var post_data = {
			'action': 'wp_pro_quiz_admin_ajax_statistic_load_user',
			'func': 'statisticLoadUser',
			'data': {
				'quizId': quizId,
            	'userId': userId,
            	'refId': refId,
				'statistic_nonce': statistic_nonce,
            	'avg': 0
			}
		}
		
		jQuery('#wpProQuiz_user_overlay, #wpProQuiz_loadUserData').show();
		var content = jQuery('#wpProQuiz_user_content').hide();

		jQuery.ajax({
			type: "POST",
			url: ajax_object.ajax_url,
			dataType: "json",
			cache: false,
			data: post_data,
			error: function(jqXHR, textStatus, errorThrown ) {
			},
			success: function(reply_data) {

				if ( typeof reply_data.html !== 'undefined' ) {
					content.html(reply_data.html);
					jQuery('a.wpProQuiz_update', content).remove();
					jQuery('a#wpProQuiz_resetUserStatistic', content).remove();
					
					
					jQuery('#wpProQuiz_user_content').show();

					jQuery('#wpProQuiz_loadUserData').hide();
				
					content.find('.statistic_data').click(function() {
						jQuery(this).parents('tr').next().toggle('fast');
			
						return false;
					});
				}
			}
		});
				
		jQuery('#wpProQuiz_overlay_close').click(function() {
			jQuery('#wpProQuiz_user_overlay').hide();
		});
	}
});
