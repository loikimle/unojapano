jQuery(document).ready(function ($) {
    jQuery('[name="quantity"]').hide();
    jQuery('[name="wdm_ld_group_active"]').click(function (e) {
       // e.preventDefault();
       var checked = jQuery(this).attr('checked');
       console.log(jQuery(this).attr('checked'))    ;
       if (checked == '' || checked == null) {
        jQuery('[name="quantity"]').attr('value',1);
        jQuery('[name="quantity"]').hide();
    } else {
        jQuery('[name="quantity"]').show();
    }
});
});