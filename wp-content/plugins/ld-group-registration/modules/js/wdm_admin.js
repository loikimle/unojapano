jQuery(document).ready(function ($) {
    var wdm_datatable= jQuery('#wdm_admin').DataTable({
        "columnDefs": [
            { "orderable": false, "targets": 0 }
        ]
    });

    var group_ids = [];

    jQuery(document).on('click', 'thead input[name="select_all"]', function(e){
        if(this.checked){
            jQuery('#wdm_admin tbody input[type="checkbox"]:not(:checked)').trigger('click');
        } else {
            jQuery('#wdm_admin tbody input[type="checkbox"]:checked').trigger('click');
        }
    });


    // Function which performs action on all successfull ajax responses
    function ldgr_on_success(response, id, status, selectedRow, group_id)
    {
        jQuery.each(response, function(status, message) {
            switch (status) {
                case 'success':
                    snackbar(message);
                    jQuery('#wdm_admin tr td a[data-user_id = "'+id+'"]').siblings('img#wdm_ajax_loader').remove();
                    if (jQuery('#wdm_admin tr td.select_action input[data-user_id = "'+id+'"]').length) {
                        jQuery('#wdm_admin tr td.select_action input[data-user_id = "'+id+'"]').prop('checked', false); // uncheck the selected checkbox
                    }
                    jQuery('#learndash_group_users-'+group_id+' > table > tbody > tr > td.learndash-binary-selector-section-right > select > option[value="'+id+'"]').remove();
                    break;
                case 'group_limit':
                    // Changes the count for left user enrollment
                    if (parseInt(jQuery('[name="wdm_ld_group_registration_left"]').val()) < parseInt(message)) {
                        jQuery('[name="wdm_ld_group_registration_left"]').attr('value',message);
                    }
                    break;
                case 'error':
                    snackbar(message);
                    jQuery('#wdm_admin tr td a[data-user_id = "'+id+'"]').siblings('img#wdm_ajax_loader').remove();
                    break;
            }
            selectedRow.addClass('selected');
            wdm_datatable.row('.selected').remove().draw(false); // Remove the row
        });
    }

    // Code to accept bulk user removal request
    jQuery('body').on('click','#bulk_accept',function (e) {

        e.preventDefault();

        // Informs user that nothing is selected
        if (jQuery('#wdm_admin tbody input[type="checkbox"]:checked').length == 0) {
            alert(wdm_ajax.no_user_selected);
            return false;
        }

        var group_id = '';
        var user_ids = [];
        var selectedRow = []; //holds the pointers to selected rows

        // fetches user and group id for all selected rows and adds ajax loader to selected rows
        jQuery('#wdm_admin tbody input[type="checkbox"]:checked').each(function(){
            jQuery(this).parent().parent().find('td:last-child center').append('<img id="wdm_ajax_loader" src="' + wdm_ajax.ajax_loader + '">');
            var user_id = jQuery(this).data('user_id');
            selectedRow[user_id] = jQuery(this).parent().parent();
            user_ids.push(user_id);
            group_id = jQuery(this).data('group_id');
        });

        jQuery.ajax({
            url: wdm_ajax.ajax_url,
            type: 'post',
            dataType: 'JSON',
            data: {
                action: 'bulk_group_request_accept',
                group_id : group_id,
                user_ids : user_ids
            },
            timeout: 30000,
            success: function(response) {
                jQuery.each(response, function(id, value) {
                    ldgr_on_success(value, id, status, selectedRow[id], group_id);
                });
                jQuery('#wdm_admin thead input[name="select_all"]').attr('checked', false);
            }
        });
    });


    // Code to reject bulk user removal request
    jQuery('body').on('click','#bulk_reject',function (e) {
        e.preventDefault();

        if (jQuery('#wdm_admin tbody input[type="checkbox"]:checked').length == 0) {
            alert(wdm_ajax.no_user_selected);
            return false;
        }

        var group_id = '';
        var user_ids = [];
        var selectedRow = []; //holds the pointers to selected rows

        // fetches user and group id for all selected rows and adds ajax loader to selected rows
        jQuery('#wdm_admin tbody input[type="checkbox"]:checked').each(function(){
            jQuery(this).parent().parent().find('td:last-child center').append('<img id="wdm_ajax_loader" src="' + wdm_ajax.ajax_loader + '">');
            var user_id = jQuery(this).data('user_id');
            selectedRow[user_id] = jQuery(this).parent().parent();
            user_ids.push(user_id);
            group_id = jQuery(this).data('group_id');
        });

        // ajax request to bulk rejection
        jQuery.ajax({
            url: wdm_ajax.ajax_url,
            type: 'post',
            dataType: 'JSON',
            data: {
                action: 'bulk_group_request_reject',
                group_id : group_id,
                user_ids : user_ids
            },
            timeout: 30000,
            success: function ( response ) {
                jQuery.each(response, function(id, value) {
                    ldgr_on_success(value, id, status, selectedRow[id], group_id);
                });
                jQuery('#wdm_admin thead input[name="select_all"]').attr('checked', false);
            }
        });
    });

    // Code to accept a single user removal request
    jQuery('body').on('click','.wdm_accept',function (e) {

        e.preventDefault();
        var temp = jQuery(this);
        jQuery(this).parent().append('<img id="wdm_ajax_loader" src="'+wdm_ajax.ajax_loader+'">');
        var group_id = jQuery(this).data('group_id');
        var user_id = jQuery(this).data('user_id');
        var selectedRow = jQuery(this).parent().parent().parent();
        jQuery.ajax({
            url: wdm_ajax.ajax_url,
            type: 'post',
            dataType: 'JSON',
            data: {
                action: 'wdm_ld_group_request_accept',
                group_id : group_id,
                user_id : user_id
            },
            timeout: 30000,
            success: function ( response ) {
                ldgr_on_success(response, user_id, status, selectedRow, group_id);
            }
        });
    });


    // Code to reject a single user removal request
    jQuery('body').on('click','.wdm_reject',function (e) {
        e.preventDefault();
        var temp = jQuery(this);
        jQuery(this).parent().append('<img id="wdm_ajax_loader" src="'+wdm_ajax.ajax_loader+'">');
        var group_id = jQuery(this).data('group_id');
        var user_id = jQuery(this).data('user_id');
        var parent = jQuery(this).parent().parent().parent();
        var selectedRow = jQuery(this).parent().parent().parent();

        jQuery.ajax({
            url: wdm_ajax.ajax_url,
            type: 'post',
            dataType: 'JSON',
            data: {
                action: 'wdm_ld_group_request_reject',
                group_id : group_id,
                user_id : user_id
            },
            timeout: 30000,
            success: function ( response ) {
                ldgr_on_success(response, user_id, status, selectedRow, group_id);
            }
        });
    });

    $('table tr').each(function(){
        $(this).find('th').first().addClass('first');
        $(this).find('th').last().addClass('last');
        $(this).find('td').first().addClass('first');
        $(this).find('td').last().addClass('last');
    });

    $('table tr').first().addClass('row-first');
    $('table tr').last().addClass('row-last');
});