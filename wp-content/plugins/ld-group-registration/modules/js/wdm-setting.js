jQuery(document).ready(function ($) {
	jQuery(".nav-tab-learndash-lms_page_learndash_lms_addons").parent().hide();

	jQuery('input[name="ldgr_user_redirects"]').on('change', function(){
		var $this = jQuery(this);
		if ($this.prop('checked')) {
			jQuery('.ldgr-user-redirects-settings').slideDown('fast');
		} else {
			jQuery('.ldgr-user-redirects-settings').slideUp('fast');
		}
	});

	jQuery('input[name="ldgr_group_code_enable_recaptcha"]').on('change', function(){
		var $this = jQuery(this);
		if ($this.prop('checked')) {
			jQuery('.ldgr-recaptcha-settings').slideDown('fast');
		} else {
			jQuery('.ldgr-recaptcha-settings').slideUp('fast');
		}
	});

	jQuery('input[name="ldgr_logo_enabled"]').on('change', function(){
		var $this = jQuery(this);
		if ($this.prop('checked')) {
			jQuery('.ldgr-group-logo-settings').slideDown('fast');
		} else {
			jQuery('.ldgr-group-logo-settings').slideUp('fast');
		}
	});

	// Upload leader logo
	var file_frame; // variable for the wp.media file_frame

	// attach a click event (or whatever you want) to some element on your page
	$( '#ldgr-upload-logo-btn' ).on( 'click', function( event ) {
		event.preventDefault();

		// if the file_frame has already been created, just reuse it
		if ( file_frame ) {
			file_frame.open();
			return;
		}

		file_frame = wp.media.frames.file_frame = wp.media({
			// title: $( this ).data( 'uploader_title' ),
			// button: {
			// 	text: $( this ).data( 'uploader_button_text' ),
			// },
			// multiple: false // set this to true for multiple file selection
		});

		file_frame.on( 'select', function() {
			attachment = file_frame.state().get('selection').first().toJSON();

			// do something with the file here
			$( '#ldgr-leader-logo-url' ).val( attachment.url );
			$( '#ldgr-leader-logo-image' ).attr('src', attachment.url);
		});

		file_frame.open();
	});


	// Toggle between option to display message or redirect on successfull group code enrollment.
	jQuery('input[name="ldgr_group_code_redirect"]').on('change', function(){
		var $this = jQuery(this);
		if ($this.prop('checked')) {
			jQuery('.ldgr-enrollment-message-div').hide().addClass('ldgr-hide');
			jQuery('.ldgr-enrollment-redirect-tip').hide().addClass('ldgr-hide');
			jQuery('.ldgr-enrollment-redirect-div').fadeIn('slow').removeClass('ldgr-hide');
			jQuery('.ldgr-enrollment-message-tip').fadeIn('slow').removeClass('ldgr-hide');
		} else {
			jQuery('.ldgr-enrollment-redirect-div').hide().addClass('ldgr-hide');
			jQuery('.ldgr-enrollment-message-tip').hide().addClass('ldgr-hide');
			jQuery('.ldgr-enrollment-message-div').fadeIn('slow').removeClass('ldgr-hide');
			jQuery('.ldgr-enrollment-redirect-tip').fadeIn('slow').removeClass('ldgr-hide');
		}
	});

	jQuery('input[name="ldgr_enable_gdpr"]').on('change', function(){
		var $this = jQuery(this);
		if ($this.prop('checked')) {
			jQuery('.ldgr-gdpr-checkbox-div').slideDown('fast');
		} else {
			jQuery('.ldgr-gdpr-checkbox-div').slideUp('fast');
		}
	});
});