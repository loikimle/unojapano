jQuery(document).on('ready', function() {
    jQuery('form#ldgr-group-code-registration-form').on('submit', function(event) {
        event.preventDefault();

        var $black_screen = jQuery('.ldgr-black-screen');
        jQuery.ajax({
            url: group_code_reg_loc.ajax_url,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'ldgr-submit-group-code-reg-form',
                nonce: jQuery('#ldgr_nonce').val(),
                form: jQuery(this).serialize()
            },
            beforeSend: function() {
                $black_screen.css('display', 'flex');
            },
            complete: function() {
                $black_screen.css('display', 'none');
            },
            success: function( response ) {
                if ( response.redirect ) {
                    ldgr_success_redirect( response.redirect_url );
                } else {
                    ldgr_display_message( response );
                }
            }
        });
    });

    jQuery('form#ldgr-group-code-enrollment-form').on('submit', function(event) {
        event.preventDefault();

        var $black_screen = jQuery('.ldgr-black-screen');
        jQuery.ajax({
            url: group_code_reg_loc.ajax_url,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'ldgr-submit-group-code-enroll-form',
                nonce: jQuery('#ldgr_nonce').val(),
                form: jQuery(this).serialize()
            },
            beforeSend: function() {
                $black_screen.css('display', 'flex');
            },
            complete: function() {
                $black_screen.css('display', 'none');
            },
            success: function( response ) {
                if ( response.redirect ) {
                    ldgr_success_redirect( response.redirect_url );
                } else {
                    ldgr_display_message( response );
                }
            }
        });
    });

    // Close messages
    jQuery( '.ldgr-message-close' ).on( 'click', function() {
        jQuery('.ldgr-group-code-messages .ldgr-message-text').html( '' );
        jQuery('.ldgr-group-code-messages' ).removeClass( 'success error' );
        jQuery('.ldgr-group-code-messages' ).css('opacity', 0);
    });
});

/**
 * Display success or error message on group code form submission.
 *
 * @param {object} data 
 */
function ldgr_display_message( data ) {
    jQuery('.ldgr-group-code-messages').stop(true, true);
    jQuery('.ldgr-group-code-messages .ldgr-message-text').html( data.msg );
    jQuery('.ldgr-group-code-messages' ).removeClass( 'success error' );

    if ( 'error' == data.type) {
        jQuery('.ldgr-group-code-messages' ).addClass( 'error' ).css('opacity', 1);
    } else {
        jQuery('.ldgr-group-code-messages' ).addClass( 'success' ).css('opacity', 1);
        jQuery('.ldgr-form').trigger('reset');
    }
    jQuery('html, body').animate({
        scrollTop: jQuery('.ldgr-group-code-messages').offset().top - 100
    });
    jQuery('.ldgr-group-code-messages').animate({opacity: 0}, 8000, 'swing');
}

/**
 * Redirect user to a page on successful enrollment.
 *
 * @param {string} redirect_url 
 */
function ldgr_success_redirect( redirect_url ) {
    window.location.href = redirect_url;
}
