<?php
/**
 * Admin Page Framework - Field Type Pack
 * 
 * http://en.michaeluno.jp/task-scheduler/
 * Copyright (c) 2014-2015 Michael Uno
 * 
 */

if ( ! class_exists( 'TaskScheduler_DateCustomFieldType' ) ) :

/**
 * Defines the date field type.
 * 
 * <h3>Field Type Specific Arguments</h3>
 * <ul>
 *  <li>`date_format` - (string) date format default: `yy/mm/dd`.</li>
 *  <li>`options` - (array) the option values passed to the <a target="_blank" href="http://api.jqueryui.com/datepicker/">`datepicker()`</a> jQuery method.</li>
 * </ul>
 * <h3>Example</h3>
 * <code>
 *  array( // Single date picker
 *      'field_id'      => 'date',
 *      'title'         => __( 'Date', 'task-scheduler' ),
 *      'type'          => 'date',
 *  ),     
 *  array( // Custom date format - use a unix timestamp.
 *      'field_id'      => 'date_custom_date_format',
 *      'title'         => __( 'Date Format', 'task-scheduler' ),
 *      'type'          => 'date',
 *      'date_format'   => '@',
 *      'attributes'    => array(
 *          'size'  => 16,
 *      ),
 *  ),                 
 *  array( // Repeatable date picker fields
 *      'field_id'      => 'date_repeatable',
 *      'type'          => 'date',
 *      'title'         => __( 'Repeatable', 'task-scheduler' ),
 *      'repeatable'    =>    true,
 *      'date_format'   => 'yy-mm-dd', // yy/mm/dd is the default format.
 *      'options'       => array(
 *          'numberOfMonths' => 2,
 *      ),
 *  ),     
 *  array( // Sortable date picker fields
 *      'field_id'      => 'date_sortable',
 *      'type'          => 'date',
 *      'title'         => __( 'Sortable', 'task-scheduler' ),
 *      'sortable'      => true,
 *      'options'       => '{
 *          minDate: new Date(2010, 11, 20, 8, 30),
 *          maxDate: new Date(2010, 11, 31, 17, 30)
 *      }',     
 *      array(), // the second item
 *      array(), // the third item
 *  ),  
 * </code>
 * 
 * @see         http://api.jqueryui.com/datepicker/
 * @since       1.0.0
 * @package     TaskScheduler_AdminPageFrameworkFieldTypePack
 * @subpackage  CustomFieldType
 * @version     1.0.0
 */
class TaskScheduler_DateCustomFieldType extends TaskScheduler_AdminPageFramework_FieldType {
        
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'date', );
    
    /**
     * Defines the default key-values of this field type. 
     * 
     * @remark            $_aDefaultKeys holds shared default key-values defined in the base class.
     */
    protected $aDefaultKeys = array(
        'date_format'    => 'yy/mm/dd',
        'attributes'     => array(
            'size'      => 10,
            'maxlength' => 400,
        ),    
        'options'        => array(
            'showButtonPanel'    => false,
        ),        
    );
        
    /**
     * Loads the field type necessary components.
     */ 
    protected function setUp() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
    }    

    
    /**
     * Returns an array holding the urls of enqueuing scripts.
     */
    protected function getEnqueuingScripts() { 
        return array(
            array( 'src' => dirname( __FILE__ ) . '/js/datetimepicker-option-handler.js', ),
        );
    }    

    /**
     * Returns an array holding the urls of enqueuing styles.
     */
    protected function getEnqueuingStyles() { 
        return array(
            dirname( __FILE__ ) . '/css/jquery-ui-1.10.3.min.css',
        );
    }    
    
    /**
     * Returns the field type specific JavaScript script.
     */ 
    protected function getScripts() { 

        $_aJSArray = json_encode( $this->aFieldTypeSlugs );
        /*    The below function will be triggered when a new repeatable field is added. */
        return "
            jQuery( document ).ready( function(){
                jQuery().registerTaskScheduler_AdminPageFrameworkCallbacks( {        
                    /**
                     * The repeatable field callback for the add event.
                     * 
                     * @param    object    oCloned
                     * @param    string    the field type slug
                     * @param    string    the field container tag ID
                     * @param    integer    the caller type. 1 : repeatable sections. 0 : repeatable fields.
                     */                        
                    added_repeatable_field: function( oCloned, sFieldType, sFieldTagID ) {
            
                        /* If it is not this field type, do nothing. */
                        if ( jQuery.inArray( sFieldType, {$_aJSArray} ) <= -1 ) {
                            return;
                        }
                        
                        /* If the input tag is not found, do nothing  */
                        if ( oCloned.find( 'input.datepicker' ).length <= 0 ) {
                            return;
                        }
                        
                        /* (Re)bind the date picker script */
                        var oDatePickerInput = jQuery( oCloned ).find( 'input.datepicker' );    
                        oDatePickerInput.removeClass( 'hasDatepicker' );
                        var sOptionID = jQuery( oCloned ).closest( '.task-scheduler-sections' ).attr( 'id' ) 
                            + '_' 
                            + jQuery( oCloned ).closest( '.task-scheduler-fields' ).attr( 'id' );    // sections id + _ + fields id 
                        var aOptions = jQuery( '#' + oDatePickerInput.attr( 'id' ) ).getDateTimePickerOptions( sOptionID );
                        oDatePickerInput.datepicker( aOptions );                        
 
                    }
                                        
                },
                {$_aJSArray}
                );
            });        
        
        " . PHP_EOL;
        
    } 

    /**
     * Returns IE specific CSS rules.
     */
    protected function getIEStyles() { return ''; }

    /**
     * Returns the field type specific CSS rules.
     */ 
    protected function getStyles() {
        
        return "/* Date Picker */
            .ui-datepicker.ui-widget.ui-widget-content.ui-helper-clearfix.ui-corner-all {
                display: none;
            }        
            " . PHP_EOL;
    }    
    
    
    /**
     * Returns the output of this field type.
     */
    protected function getField( $aField ) { 
            
        $aInputAttributes = array(
            'type'              => 'text',
            'data-date_format'  => $aField['date_format'],
        ) + $aField['attributes'];
        $aInputAttributes['class'] .= ' datepicker';
        return 
            $aField['before_label']
            . "<div class='task-scheduler-input-label-container'>"
                . "<label for='{$aField['input_id']}'>"
                    . $aField['before_input']
                    . ( $aField['label'] && ! $aField['repeatable']
                        ? "<span class='task-scheduler-input-label-string' style='min-width:" . $this->sanitizeLength( $aField['label_min_width'] ) . ";'>" . $aField['label'] . "</span>"
                        : "" 
                    )
                    . "<input " . $this->generateAttributes( $aInputAttributes ) . " />"    // this method is defined in the base class
                    . $aField['after_input']
                    . "<div class='repeatable-field-buttons'></div>"    // the repeatable field buttons will be replaced with this element.
                . "</label>"
            . "</div>"
            . $this->_getDatePickerEnablerScript( $aField['input_id'], $aField['date_format'], $aField['options'] )
            . $aField['after_label'];
        
    }    
        /**
         * A helper function for the above _replyToGetField() method.
         * 
         */
        protected function _getDatePickerEnablerScript( $sInputID, $sDateFormat, $asOptions  ) {
            
            if ( is_array( $asOptions ) ) {                
                $aOptions = $asOptions;
                $aOptions['dateFormat'] = isset( $aOptions['dateFormat'] ) ? $aOptions['dateFormat'] : $sDateFormat;
                $_sOptions = json_encode( ( array ) $aOptions );    
            } else {
                $_sOptions = ( string ) $asOptions;    
            }
            return     // data-id='{$sID}'
                "<script type='text/javascript' class='date-picker-enabler-script' >
                    jQuery( document ).ready( function() {
                        jQuery( document ).on( 'focus', 'input#{$sInputID}:not(.hasDatepicker)', function() {
                            jQuery( this ).datepicker( {$_sOptions} );
                        });
                        var sOptionID = jQuery( '#{$sInputID}' ).closest( '.task-scheduler-sections' ).attr( 'id' ) + '_' + jQuery( '#{$sInputID}' ).closest( '.task-scheduler-fields' ).attr( 'id' );
                        jQuery( '#{$sInputID}' ).setDateTimePickerOptions( sOptionID, {$_sOptions});                                
                    });
                </script>";
        }

}
endif;