(function ( $, global ) {
    var element;
    // var wdm_snackbar = {

    //     _hideSnackBar : function(){
    //          setTimeout(function(){ element.removeClass('show') }, 5000);
    //     },

    //     _showSnackBar : function(){
    //         if( ! element.hasClass('show') ) {
    //             element.addClass('show');
    //         }
    //         this._hideSnackBar();
    //         return this;
    //     },

    //     _setMessage : function($message){
    //         element.html('<div class="cpb-card-text">'+$message+'</div>');
    //         return this;
    //     },

    //     _addSnackbarClass : function(){
    //         if(! element.hasClass('wdm-snackbar')) {
    //             element.addClass('wdm-snackbar cpb-card');
    //         }
    //         return this;
    //     },

    //     _createHTMLelement : function(){

    //         if( $('.wdm-snackbar').length <= 0) {
    //             element = $(document.createElement('div'));
    //         } else {
    //             element = $('.wdm-snackbar');
    //         }

    //         return this;
    //     }
    // };
    /*
     * Previous code had timing issues on multiple clicks.
     */
    var previous = null; //check for snackbar present already in DOM
    var snackBarWrapper = function($message){
        // wdm_snackbar._createHTMLelement()._addSnackbarClass()._setMessage($message)._showSnackBar();
        // jQuery('body').append(element);


    /*
        For recurring clicks: if snackbar element present in dom (in case timeout has not completed), hide the previous one and show the new one.
    */
    if (previous) {
      previous.dismiss();
    }
    /*
        generate snackbar element.
    */
    var snackbar = document.createElement('div');
    snackbar.className = 'wdm-snackbar cpb-card show';
    snackbar.dismiss = function() {
      this.style.opacity = 0;
    };
    /*
        generate text wrapper and append $message
    */
    var text = document.createElement('div');
    text.className = 'cpb-card-text';
    text.innerHTML = $message;
    snackbar.appendChild(text);

    /*
        Set time for which the snackbar will be displayed
    */
    setTimeout(function() {
      if (previous === this) {
        previous.dismiss();
      }
    }.bind(snackbar), 2000);

    /*
        On timeout, remove the snackbar from DOM
    */
    snackbar.addEventListener('transitionend', function(event, elapsed) {
      if (event.propertyName === 'opacity' && this.style.opacity == 0) {
        this.parentElement.removeChild(this);
        if (previous === this) {
          previous = null;
        }
      }
    }.bind(snackbar));

    /*
      Set the check to snackbar present. Append element and add style to snackbar on click trigger.
    */
    previous = snackbar;
    document.body.appendChild(snackbar);
    // In order for the animations to trigger, I have to force the original style to be computed, and then change it.
    getComputedStyle(snackbar).right;
    snackbar.style.right = '10px';
    snackbar.style.opacity = 1;
    };
    global.snackbar = snackBarWrapper;

}( jQuery, window ));
