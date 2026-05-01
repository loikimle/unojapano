
( function( $ ) {
    var template = wp.template( 'fields-group' );

    var i = 1;
    // Add
    $( '.custom-field-container' ).on( 'click', '.add-field', function() {
        $( '.custom-fields-wrapper tbody.custom-ui-sortable' ).append( template( { 'counter': i } ) );
        i++;
    } );

    // Remove
    $( '.custom-field-container' ).on( 'click', '.remove-field', function() {
        $( this ).closest( 'tr' ).remove();
        i--;
    } );

    $( document ).on( 'change', '.custom-ui-sortable tr td select', function() {
        var self = $(this);
        if( '7' == self.val() ) {
            self.siblings( '.editDropDown' ).show();
        } else {
            self.siblings( '.editDropDown' ).hide();
        }
    } );

    $( '.custom-field-container' ).on( 'click', '.editDropDown', function() {
        var self = $(this);
        self.siblings('.custom-field_dropDownEditBox').show();
    } );

    $( '.custom-field-container' ).on( 'click', 'input[type="button"].drop-down-ok', function() {
        var self = $(this);
        self.parents('.custom-field_dropDownEditBox').hide();
    } );

} ) ( jQuery );