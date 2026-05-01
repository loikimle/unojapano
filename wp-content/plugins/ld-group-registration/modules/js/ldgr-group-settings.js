jQuery(document).on('ready', function(){
    jQuery('.ldgr-group-settings-icon').on('click', function(){
        if (! jQuery(this).hasClass('gear-open')) {
            jQuery(this).addClass('gear-open');
            jQuery('.ldgr-group-actions').slideDown();
        } else {
            jQuery(this).removeClass('gear-open');
            jQuery('.ldgr-group-actions').slideUp();
        }
    });

    jQuery('#ldgr-update-group-details').on('click', function(e){
        e.preventDefault();
        var updated_group_name = jQuery('input[name="ldgr-edit-group-name"]').val();
        var group_id = parseInt(jQuery('input[name="ldgr-edit-group-name"]').data('group_id'));

        if (0 == updated_group_name.length || 100 < updated_group_name.length) {
            alert(ldgr_loc.invalid_group_name);
            return;
        }

        if (isNaN(group_id) || 0 > group_id) {
            alert(ldgr_loc.invalid_group_id);
            return;
        }
        jQuery.ajax({
            url: ldgr_loc.ajax_url,
            type: 'post',
            dataType: 'JSON',
            data: {
                action: 'ldgr_update_group_details',
                group_id : group_id,
                group_name : updated_group_name
            },
            success : function(response){
                if ('error' == response.status) {
                    alert(ldgr_loc.common_error + ' : '+response.message);
                } else {
                    // Update Group Name
                    alert(response.message);
                    jQuery('.wdm-select-wrapper-content select[name="wdm_group_id"] option[value="'+group_id+'"]').text(updated_group_name);
                }
            }
        })
    });
});