jQuery(document).ready( function($) {


    // Edit Template Event
    $('.ultp-edit-template').on('click', function(e) {
        e.preventDefault();
        const that = $(this)
        const url = new URL($(this).attr('href'));
        const post_ID = url.searchParams.get('post');
        saveData(that, 'update&post_id='+post_ID);
    });


    // Add Condition Button in Editor
    if (document.readyState === 'loading') {
        window.addEventListener( 'load', appendImportButton );
    } else {
        appendImportButton();
    }
    function appendImportButton() {
        setTimeout(function() {
            let toolbar = document.querySelector( '.edit-post-header__toolbar' );
            if ( ! toolbar ) {
                toolbar = document.querySelector( '.edit-post-header-toolbar' );
                if ( ! toolbar ) {
                    return;
                }
            }
            let buttonDiv = document.createElement( 'div' );
            let html = `<div class="sab-toolbar-insert-layout">`;
            html += '<button id="builder-condition" class="ultp-popup-button"><span class="dashicons dashicons-admin-settings"></span> Edit Conditions</button>';
            html += `</div>`;
            buttonDiv.innerHTML = html;
            toolbar.appendChild( buttonDiv );
            document.getElementById( 'builder-condition' ).addEventListener( 'click', conditionButton );
        }, 0 );
    }


    // Condition Button Event
    function conditionButton() {
        const current = window.location.href;
        const url = new URL(current);
        showCondition( url.searchParams.get("post"), current );
    }


    // Edit Template Action
    $('.ultp-builder-conditions').on('click', function(e) {
        e.preventDefault();
        const current = $(this).attr('href');
        const url = new URL(current);
        initSet();
        showCondition( url.searchParams.get("post"), current );
    });


    // Show Condition Data
    function showCondition( post_id, current ) {
        $('.ultp-edit-template').show();
        $('.ultp-builder, .ultp-edit-template').addClass('active');
        $('.ultp-new-template').removeClass('active');
        $('.ultp-edit-template').attr('href', current);
        $.ajax({
            url: builder_option.ajax,
            type: 'POST',
            data: {
                action: 'ultp_edit',
                _wpnonce: builder_option.security,
                post_id: post_id
            },
            success: function(data) {
                const retunData = JSON.parse(data);
                if(Object.keys(retunData).length > 0) {
                    if (retunData.title) {
                        $('.ultp-title').val(retunData.title);
                    }
                    if (retunData.type) {
                        $('[name=post_filter]').val(retunData.type);
                    }
                    if (retunData.conditions) {
                        retunData.conditions.forEach(element => {
                            const data = element.split('/')
                            if(data[0] == 'include') {
                                if (data.length <= 3) {
                                    $('input[name='+data[data.length - 1]+']').prop( "checked", true );
                                }
                            }
                        });
                    }
                    appendOption(retunData.author_id, 'author');
                    if (retunData.taxonomy) {
                        Object.keys(retunData.taxonomy).forEach( element => {
                            appendOption(retunData.taxonomy[element], element);
                        });
                    }
                }
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
            },
        });
    }
    

    // Append Option Data
    function appendOption(data, type) {
        if (typeof data != 'undefined') {
            $('.select-'+type).empty();
            $('.select-'+type).closest('.ultp-multi-select').find('.multi-select-action ul').empty();
            $.each(data, function(i,val) {
                $('.select-'+type).append(
                    '<option value="' + val.id + '" selected>' + val.text + '</option>'
                ).trigger('change');

                $('.select-'+type).closest('.ultp-multi-select').find('.multi-select-action ul').append(
                    '<li class="multi-select-single" data-id="' + val.id + '">'+ val.text +' <span class="multi-select-close" data-id="' + val.id + '"> x </span></li>'
                )
            });
        } else {
            $('.select-'+type+' option').remove().trigger('change')
        }
    }


    // Save Data
    function saveData(that, operation) {
        const submitData =  that.closest('form').serialize();
        $.ajax({
            url: builder_option.ajax,
            type: 'POST',
            data: submitData+'&operation='+operation,
            beforeSend: function() {
                $('.ultp-new-template').removeClass('active');
            },
            success: function(data) {
                if (operation == 'insert') {
                    $('.ultp-edit-template').show().attr('href', data.replace("&amp;", "&"));
                } else {
                    const url = window.location.href;
                    if (url.indexOf('&action=edit') === -1) {
                        window.location.href = data.replace("&amp;", "&");
                    } else {
                        $('.ultp-builder, .ultp-new-template, .ultp-edit-template').removeClass('active');
                    }
                }
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
            },
        });
    }


    // New Template Create Action
    $('.ultp-new-template').on('click', function(e){
        e.preventDefault();
        if ( $('.ultp-title').val().length == 0 ) {
            $('.ultp-message').text('Empty Title !');
        } else {
            saveData($(this), 'insert')
        }
    });


    // New Template Button Popup Action
    $('.page-title-action').on('click', function(e) {
        const href = $(this).attr('href')
        $('.ultp-builder')[0].reset();
        $('.ultp-edit-template').hide();
        initSet();
        if(href.indexOf('post_type=ultp_builder') > 0){
            e.preventDefault();
            $('.ultp-builder, .ultp-new-template').addClass('active');
            $('.ultp-edit-template').removeClass('active');
        }
    });


    // Popup Close Action
    $('.ultp-builder-close').on('click', function(e) {
        $('.ultp-builder, .ultp-new-template, .ultp-edit-template').removeClass('active');
    });


    // Template Type
    if( $("select[name=template_type]").length > 0 ) {
        let tabData = '';
        function getActivate(val){
            const url = new URL(window.location.href);
            let type = url.searchParams.get("template_type");
            type = (type == null) ? 'all' : type;
            if (type == val) {
                return 'nav-tab-active';
            } else {
                return '';
            }
        }
        function getURL(val) {
            let url = window.location.href
            url = url.split('template_type=')
            return url[0] + 'template_type=' + (typeof(url[1]) != 'undefined' ? val : 'all' )
        }
        $( "select[name=template_type] option" ).each(function() {
            const value = $(this).val();
            tabData += '<a href="'+getURL(value)+'" class="'+getActivate(value)+' wpxpo-tab-index nav-tab">'+$(this).text()+'</a>';
        });
        $('.wp-header-end').after('<div class="nav-tab-wrapper">'+ tabData +'</div><br/>');
    }


    // Initial Set Value
    function initSet(){
        $(".ultp-single-select").each(function(e) {
            $(this).prop('checked', false);
        });
        $(".ultp-multi-select").each(function(e) {
            $(this).find('.multi-select-data').html('');
            $(this).find('.multi-select-action').html('<ul></ul>');
        });
    }


    // Set Option Value
    function setOption(selector){
        selector = selector.find('.multi-select-data');
        let $html = '<ul>';
        (selector.val()||[]).forEach( item => {
            $html += '<li class="multi-select-single" data-id="'+item+'">'+selector.find('option[value="'+item+'"]').text()+' <span class="multi-select-close" data-id="'+item+'"> x </span></li>';
        });
        $html += '</ul>';
        selector.closest('.ultp-multi-select').find('.multi-select-action').html(selector.val() ? $html : '');
    }


    // Search Keyup
    $('.ultp-item-search').on('change paste keyup', function(e){
        e.preventDefault();
        getAjaxData($(this).closest('.ultp-multi-select'), $(this).val());
    });


    // Search Button Click
    $('.ultp-multi-select-action').on('click', function(e){
        e.preventDefault();
        const dropdown = $(this).closest('.ultp-multi-select').find('.ultp-search-dropdown')
        if(dropdown.hasClass('active')){
            dropdown.removeClass('active')
        }else{
            getAjaxData($(this).closest('.ultp-multi-select'), '');
            dropdown.addClass('active');
        }
    });


    // Single Dropdown Click
    $(document).on('click', '.multi-select-single', function(e){
        e.preventDefault();
        const selector = $(this).closest('.ultp-multi-select').find('.multi-select-data');
        if($.inArray($(this).data('id').toString(), selector.val()) === -1){
            selector.append( '<option value="' + $(this).data('id') + '" selected>' + $(this).text() + '</option>').trigger('change');
            selector.find('option').not(':selected').remove();
        }
        setOption($(this).closest('.ultp-multi-select'));
    });


    // Close Button Click
    $(document).on('click', '.multi-select-close', function(e){
        e.preventDefault();
        e.stopPropagation();
        const selector = $(this).closest('.ultp-multi-select').find('.multi-select-data');
        selector.find("option[value='"+$(this).data('id')+"']").remove().trigger('change');
        $(this).closest('.multi-select-single').remove(); 
    });


    // Get AJAX Search Data
    function getAjaxData(selector, searchTerm){
        const parent = selector.find('.ultp-search-results')
        $.ajax({
            url: builder_option.ajax,
            type: 'POST',
            dataType: 'json',
            data:{
                action: 'ultp_search',
                _wpnonce: builder_option.security,
                type: selector.find('.multi-select-data').data('type'),
                term: searchTerm
            },
            success: function(data) {
                let $html = '<ul>';
                data.forEach( item => {
                    $html += '<li class="multi-select-single" data-id="'+item.id+'">'+item.text+'</li>';
                });
                $html += '</ul>';
                parent.html($html);
                return data;
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
            },
        });
    }


    // Outside Search Close
    $(document).mouseup(function(e){
        let container = $(".ultp-search-dropdown");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            $('.ultp-search-dropdown').removeClass('active');
        }
    });

    // Add Media Video
    $('.ultp-add-media').on('click', function(e){
        e.preventDefault();
        const items_frame = wp.media.frames.items = wp.media({
            title: 'Add to Gallery',
            button: { text: 'Select'},
            library: {
                type: [ 'video' ]
            },
        });
        items_frame.open().on('select', function(e){
            const uploaded_image = items_frame.state().get('selection').first();
            const video_url = uploaded_image.toJSON().url;
            if (video_url) {
                $('input[name=feature-video]').val(video_url);
            }
        });
    });

});