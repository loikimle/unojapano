jQuery(document).ready(function(){
	jQuery('body').on('click', '.wdm-reinvite', function(e) {
		e.preventDefault();
		jQuery(this).parent().append('<img id="wdm_ajax_loader" src="' + wdm_data.ajax_loader + '">');
		var user_id = jQuery(this).data('user_id');
            var group_id = jQuery(this).data('group_id');
            jQuery.ajax({
                type: "post",
                dataType: 'json',
                url: wdm_data.ajaxurl,
                data: {
                    action: 'wdm_send_reinvite_mail',
                    user_id: user_id,
                    group_id: group_id
                },
                success: function(response) {
                    jQuery.each(response, function(j, k) {
                        switch (j) {
                            case 'success':
                                alert(k);
                                jQuery('#wdm_ajax_loader').remove();
                                break;
                            case 'error':
                                alert(k);
                                jQuery('#wdm_ajax_loader').remove();
                                break;
                        }
                    });
                }
            });
	});
});