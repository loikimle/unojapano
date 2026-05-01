(function( $ ) { 'use strict';
    $( document ).ready( function() {
        var LAQ_Admin = {
            init: function() {
                $( '.submitdelete' ).on( 'click', function(){
                    if( ! confirm( ldqieConfQuizVars.message ) ) {
                        return false;
                    }
                });
                $( '.actions #delete_all' ).on( 'click', function(){
                    if( ! confirm( ldqieConfQuizVars.message ) ) {
                        return false;
                    }

                    return true;
                });

                $( '.bulkactions #doaction' ).on( 'click', function(){
                    var delLabel = $(this).parent().find( '#bulk-action-selector-top' ).val();
                    if( delLabel=='delete' ) {
                        if( ! confirm( ldqieConfQuizVars.message ) ) {
                            return false;
                        }    
                    }

                    return true;
                });
                
            }
        }
        LAQ_Admin.init();
    });
})( jQuery ); 