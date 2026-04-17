jQuery( document ).ready(function() {
// alert("hello");
    if(jQuery("input[name='wdm_ld_group_active']:checked").val() === "on" || (wdm_gr_data.show_quantity != 'on')){
        jQuery(".edd_download_quantity_wrapper").show();
        // jQuery(".wdm-enroll-me-div").show();
    }
    else{
        jQuery(".edd_download_quantity_wrapper .edd-item-quantity").val(1);
        jQuery(".edd_download_quantity_wrapper").hide();
        // jQuery(".wdm-enroll-me-div").hide();
    }

    jQuery("input[name='wdm_ld_group_active']").click(function(){
        if(jQuery(this).val() === "on"){
            jQuery(".edd_download_quantity_wrapper").slideDown();
            jQuery('div.ldgr_group_name').slideDown().find('input').focus();
            // jQuery(".wdm-enroll-me-div").show();
        }
        else{
            jQuery(".edd_download_quantity_wrapper .edd-item-quantity").val(1);
            jQuery(".edd_download_quantity_wrapper").slideUp();
            jQuery('div.ldgr_group_name').slideUp();
            // jQuery(".wdm-enroll-me-div").hide();
        }
    });

    jQuery("#wdm_enroll_help_btn").click(function(){
        jQuery(".wdm_enroll_me_help_text").toggle();
    });
});