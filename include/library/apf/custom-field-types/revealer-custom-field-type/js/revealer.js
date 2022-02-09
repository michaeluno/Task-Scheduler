/**
 * @version 1.1.0
 */
( function ( $ ) {

    /**
     * Binds the revealer event to the element.
     */
    $.fn.setTaskScheduler_AdminPageFrameworkRevealer = function() {
        this.off( 'change', apfRevealerOnChange ); // for repeatable fields
        this.on( 'change', apfRevealerOnChange );
    };

    apfRevealerOnChange = function() {

        var _sTargetSelector        = $( this ).is( 'select' )
            ? $( this ).children( 'option:selected' ).data( 'reveal' )
            : $( this ).data( 'reveal' );

        var _bGlobal                = $( this ).is( 'select' )
            ? $( this ).children( 'option:selected' ).data( 'global' )
            : $( this ).data( 'global' );

        var _oElementToReveal;

        // For check-boxes
        if ( $( this ).is( ':checkbox' ) ) {
            _oElementToReveal       = _bGlobal
                ? $( _sTargetSelector )
                : $( this ).closest( '.task-scheduler-section' ).find( _sTargetSelector );
            if ( $( this ).is( ':checked' ) ) {
                _oElementToReveal.fadeIn();
            } else {
                _oElementToReveal.hide();
            }
            return;
        }

        // For other types (select and radio).

        // Hide all the subject elements first
        var _sSelectors   = $( this ).data( 'selectors' );
        var _oAllSubjects = _bGlobal
            ? $( _sSelectors )
            : $( this ).closest( '.task-scheduler-section' ).find( _sSelectors );
        _oAllSubjects.hide();

        // Get the revealing elements
        _oElementToReveal       = _bGlobal
            ? $( _sTargetSelector )
            : $( this ).closest( '.task-scheduler-section' ).find( _sTargetSelector );

        if ( ! _oElementToReveal.length  ) {
            return;
        }

        // The <select> type supports `undefined` to unselect all.
        if ( 'undefined' === _sTargetSelector ) {
            return;
        }

        // Reveal the element
        _oElementToReveal.fadeIn();

    }

  $( document ).ready( function(){
    $().registerTaskScheduler_AdminPageFrameworkCallbacks( {
        /**
         * Called when a field of this field type gets repeated.
         */
      repeated_field: function( oCloned, aModel ) {

        // Using a timer here this is because registering a callback somehow gets ignored when done immediately.
        // Probably, the cloned element is not appended yet and it causes trouble.
        var _oCloned = oCloned;
        setTimeout( function(){
          _oCloned.find( 'select[data-reveal],input[type=\"checkbox\"][data-reveal],input[type=\"radio\"][data-reveal]' )
            .setTaskScheduler_AdminPageFrameworkRevealer();
        }, 100 );

      },
    },
    apfRevealerFieldType.fieldTypeSlugs    // subject field type slugs
    );
  } );


  /* The below function will be triggered when a new repeatable field is added. Since the APF repeater script does not
      renew the color piker element (while it does on the input tag value), the renewal task must be dealt here separately. */
  $( document ).ready( function(){

    if ( 'undefined' === typeof apfRevealerFieldType ) {
      console.log( 'APF Revealer Field Type:', 'The required data is not passed to the script.' );
      return;
    }

    if ( parseInt( apfRevealerFieldType.debugMode ) ) {
      console.log( 'APF Revealer Field Type:', apfRevealerFieldType );
    }

    $.each( apfRevealerFieldType.fields, function( inputID, field ) {

      var _oInputs = $( 'select[data-id="' + inputID + '"][data-reveal],input[data-id="' + inputID + '"][data-reveal]');
      _oInputs.setTaskScheduler_AdminPageFrameworkRevealer();

      // Update the revealing state. Only trigger change that is checked
      $( 'select[data-id="' + inputID + '"][data-reveal],input[data-id="' + inputID + '"][data-reveal]:checked')
        .trigger( 'change' );

    });

  });    
    
}( jQuery ));