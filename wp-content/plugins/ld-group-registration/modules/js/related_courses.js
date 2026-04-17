jQuery(document).ready(function(){
   // jQuery('.show_if_course').each(function(){
   //                  jQuery(this).addClass('show_if_simple');
   //                  jQuery(this).show();
   //              });

   jQuery("#wdm_show_front_option").on("change",function(){
  	if(jQuery("#wdm_show_front_option").is(":checked")){
    		jQuery(".wdm-default-front-option").show();
  	}
  	else{
  		jQuery(".wdm-default-front-option").hide();
  	}
   });

   jQuery("#wdm_show_front_option").trigger('change');

  jQuery("#wdm_ld_group_registration").on("change",function(){
    if(jQuery("#wdm_ld_group_registration").is(":checked")){
        jQuery(".wdm_show_other_option").show();
    }
    else{
      jQuery(".wdm_show_other_option").hide();
    }
  });
   jQuery("#wdm_ld_group_registration").trigger('change');


   // alert(jQuery("#wdm_show_front_option").is(":checked"));

    jQuery("#ldgr_enable_unlimited_members").on("change",function(){
		if(jQuery(this).is(":checked")){
			jQuery(".ldgr-unlimited-group-members-settings").show();
		}
		else{
			jQuery(".ldgr-unlimited-group-members-settings").hide();
		}
 	});
});