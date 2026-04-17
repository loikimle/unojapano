jQuery( document ).ready(function() {

    jQuery("#wdm_enroll_help_btn").click(function(){
        jQuery(".wdm_enroll_me_help_text").toggle();
    });

    if(wdm_gr_data.default_script == 'front'){

        if(jQuery("input[name='wdm_ld_group_active']:checked").val() === "on"){
            jQuery(".quantity").show();
            jQuery(".wdm-enroll-me-div").show();
        }
        else{
            jQuery(".quantity .qty").val(1);
            jQuery(".quantity").hide();
            jQuery(".wdm-enroll-me-div").hide();
        }
        
        // @todo: Better solution for future.
        // var quantity_display = 'block';
        // setTimeout(function(){
        //     quantity_display = jQuery('.quantity').css('display');
        // }, 500 );


        jQuery("input[name='wdm_ld_group_active']").click(function(){
            if(jQuery(this).val() === "on"){
                jQuery(".quantity").show();
                jQuery(".wdm-enroll-me-div").show();
                jQuery('div.ldgr_group_name').slideDown().find('input').focus();
            }
            if(jQuery(this).val() !== "on") {
                jQuery(".quantity .qty").val(1);
                jQuery(".quantity").hide();
                jQuery(".wdm-enroll-me-div").hide();
                jQuery('div.ldgr_group_name').slideUp();
            }
        });

        jQuery("body").on("change",".variations select",function(){
            if(jQuery("input[name='wdm_ld_group_active']:checked").val() === "on"){
                jQuery(".quantity").show();
                jQuery(".wdm-enroll-me-div").show();
            }
            else{
                jQuery(".quantity .qty").val(1);
                jQuery(".quantity").hide();
                jQuery(".wdm-enroll-me-div").hide();
            }
        });
    }
    if(wdm_gr_data.default_script == 'package'){

        // jQuery("div.quantity .qty").val(1);
        // jQuery("div.quantity").hide();
        // jQuery("body").on("change",".variations select",function(){
        //     jQuery("div.quantity").hide();
        //     jQuery("div.quantity .qty").val(1);
        // });
    }
    if(wdm_gr_data.cal_enroll){

        jQuery(".variation_id").on("change paste keyup",function(e){
            // e.preventDefault();
            // alert(jQuery(".variation_id").val());
            var cur_var = jQuery(".variation_id").val();
            if(cur_var == '' || cur_var == 0){
                return false;
            }
            // variations
            jQuery(".variations").append('<img id="wdm_ajax_loader" src="'+wdm_gr_data.ajax_loader+'">');
             jQuery.ajax({
                url: wdm_gr_data.ajax_url,
                type: 'post',
                data: {
                    action: 'wdm_show_enroll_option',
                    cur_var : cur_var,
                    type : 'wc'
                },
                success: function ( response ) {
                    // console.log(response);
                    if(response == true || response == 1){
                        jQuery(".wdm-enroll-me-div").hide();
                    }
                    else{
                        jQuery(".wdm-enroll-me-div").show();
                    }
                    jQuery('#wdm_ajax_loader').remove();
                }
            });
            // alert(cur_var);
        });
    }

    if ( jQuery('.variations_form').length ) {
        var product_variations = jQuery('.variations_form').data('product_variations');
        if ( ! product_variations.length ) {
            return;
        }

        var attribute_name = '';
        var attribute_value = '';
        jQuery("body").on("change",".variations select",function(){
            attribute_name = jQuery(this).data('attribute_name');
            attribute_value = jQuery(this).val();

            product_variations.forEach(variation => {
                // Check for same attributes
                if (attribute_value == variation.attributes[attribute_name]) {
                    if ('hidden' != jQuery('#ldgr_variation_' + variation.variation_id ).attr('type')) {
                        jQuery('.ldgr_variation_group_options').hide();
                        jQuery('#ldgr_variation_' + variation.variation_id ).show();
                        jQuery('.ldgr_variations').show();
                        if ( 'individual' == wdm_gr_data.default_option ) {
                            jQuery('.ldgr_group_name').hide();
                        }
                    } else {
                        jQuery('.ldgr_variations').hide();
                    }
                }
            });
        });
    }

    jQuery('#ldgr-unlimited-member-check').on('change', function(){
        var checked = jQuery(this).is(':checked');
        if (checked) {
            jQuery(".quantity .qty").val(1).hide();
            jQuery("input[name='wdm_ld_group_active']").prop('checked', true);
            jQuery('.wdm_group_registration').hide();
            jQuery('.ldgr_group_name').show();
        } else {
            jQuery(".quantity .qty").show();
            jQuery('.wdm_group_registration').show();
        }
    });
});
