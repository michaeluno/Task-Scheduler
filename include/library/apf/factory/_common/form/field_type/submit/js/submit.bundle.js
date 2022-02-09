/*! Admin Page Framework - Submit Field Type 1.0.0 */
(function($){
  
  $( document ).ready( function(){

    $( '.task-scheduler-field-submit .submit-confirm-container input[type=checkbox]' ).each( function( index, value ){
      $( this ).closest( '.task-scheduler-field-submit' ).find( 'input[type=submit]' ).on( 'click', function( event ){
        var _fieldSubmit = $( this ).closest( '.task-scheduler-field-submit' );
        _fieldSubmit.find( '.submit-confirmation-warning' ).remove(); // previous error message
        var _confirmCheckbox = $( this ).closest( '.task-scheduler-field-submit' ).find( '.submit-confirm-container input[type=checkbox]' );
        if ( ! _confirmCheckbox.length ) {
          return true;
        }
        if ( _confirmCheckbox.is( ':checked' ) ) {
          return true;
        }
        // At this point, the checkbox is not checked.
        var _sErrorTag = "<p class='field-error submit-confirmation-warning'><span>* " + _confirmCheckbox.attr( 'data-error-message' ) + "</span></p>";
        _fieldSubmit.find( '.submit-confirm-container' ).append( _sErrorTag );
        return false;
      } );
    });
  } );
  
}(jQuery));