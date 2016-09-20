<?php
/**
 * Admin Page Framework - Field Type Pack
 * 
 * http://en.michaeluno.jp/task-scheduler/
 * Copyright (c) 2014-2015 Michael Uno
 * 
 */

if ( ! class_exists( 'TaskScheduler_DateTimeCustomFieldType' ) ) :

/**
 * Defines the date-time field type.
 * 
 * <h3>Field Type Specific Arguments</h3>
 * <ul>
 *  <li>`date_format` - (string) date format default: `yy/mm/dd`.</li>
 *  <li>`time_format` - (string) date format default: `H:mm`.</li>
 *  <li>`options` - (array) the option values passed to the <a target="_blank" href="http://trentrichardson.com/examples/timepicker/#tp-options">`datetimepicker()`</a> jQuery plugin method.</li>
 * </ul>
 * <h3>Example</h3>
 * <code>
 *  array( // Single date-time picker
 *      'field_id'      => 'date_time',
 *      'type'          => 'date_time',
 *      'title'         => __( 'Date & Time', 'task-scheduler' ),
 *      'date_format' => 'yy-mm-dd', // yy/mm/dd is the default format.
 *      'time_format' => 'H:mm', // H:mm is the default format.
 *  ),     
 *  array( // Multiple date-time pickers
 *      'field_id'      => 'dates_time_multiple',
 *      'type'          => 'date_time',
 *      'title'         => __( 'Multiple Date and Time', 'task-scheduler' ),
 *      'label'         => __( 'Default', 'task-scheduler' ), 
 *      'time_format'   => 'H:mm',
 *      'date_format'   => 'yy-mm-dd', // yy/mm/dd is the default format.
 *      'delimiter'     => '<br />',     
 *      'attributes'    => array(
 *          'size' => 24,
 *      ),     
 *      array(
 *          'label'         => __( 'AM PM', 'task-scheduler' ), 
 *          'time_format'   => 'hh:mm tt',
 *      ),
 *      array(
 *          'label'         => __( 'Time Zone', 'task-scheduler' ), 
 *          'time_format'   => 'hh:mm tt z',
 *      ),
 *      array(
 *          'label'         => __( 'Number Of Months', 'task-scheduler' ), 
 *          'options'       => array(
 *              'numberOfMonths' => 3,
 *          ),
 *      ),     
 *      array(
 *          'label'         => __( 'Min & Max Dates', 'task-scheduler' ), 
 *          'options'       => array(
 *              'numberOfMonths'    => 2,
 *              'minDate'           => 0,
 *              'maxDate'           => 30,
 *          ),
 *      ),     
 *  ),
 *  array( // Repeatable date time picker
 *      'field_id'      => 'date_time_repeatable',
 *      'type'          => 'date_time',
 *      'title'         => __( 'Repeatable Date & Time Fields', 'task-scheduler' ),
 *      'repeatable'    => true,
 *      'options'       => array(
 *          'timeFormat'    => 'HH:mm:ss',
 *          'stepHour'      => 2,
 *          'stepMinute'    => 10,
 *          'stepSecond'    => 10,
 *      ),
 *  ),    
 *  array( // Sortable date_time picker fields
 *      'field_id'      => 'date_time_sortable',
 *      'type'          => 'date_time',
 *      'title'         => __( 'Sortable', 'task-scheduler' ),
 *      'sortable'      => true,
 *      'attributes'    => array(
 *          'size' => 30,
 *      ),
 *      'options'       => array(     
 *          'timeFormat'    => 'HH:mm z',
 *          'timezoneList'  => array(
 *              array(
 *                  'value' => -300,
 *                  'label' => __( 'Eastern', 'task-scheduler' ),
 *              ),
 *              array(
 *                  'value' => -360,
 *                  'label' => __( 'Central', 'task-scheduler' ),
 *              ),     
 *              array(
 *                  'value' => -420,
 *                  'label' => __( 'Mountain', 'task-scheduler' ),
 *              ),     
 *              array(
 *                  'value' => -480,
 *                  'label' => __( 'Pacific', 'task-scheduler' ),
 *              ),     
 *          ),
 *      ),
 *      array(), // the second item
 *      array(), // the third item
 *  ),
 * </code>
 * 
 * @since       1.0.0
 * @package     TaskScheduler_AdminPageFrameworkFieldTypePack
 * @subpackage  CustomFieldType
 * @version     1.0.0
 */
class TaskScheduler_DateTimeCustomFieldType extends TaskScheduler_AdminPageFramework_FieldType {
    
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'date_time', );
    
    /**
     * Defines the default key-values of this field type. 
     * 
     * @remark            $_aDefaultKeys holds shared default key-values defined in the base class.
     */
    protected $aDefaultKeys = array(
        'date_format'    => 'yy/mm/dd',
        'time_format'    => 'H:mm',
        'attributes'     => array(
            'size'       => 16,
            'maxlength'  => 400,
        ),
        'options'        => array(
            'showButtonPanel'    =>    false,
        ),
    );
    
    /**
     * Loads the field type necessary components.
     */ 
    protected function setUp() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-slider' );
    }    

    /**
     * Returns an array holding the urls of enqueuing scripts.
     */
    protected function getEnqueuingScripts() { 
        return array(
            array( 'src'    => dirname( __FILE__ ) . '/js/jquery-ui-timepicker-addon.min.js', 'dependencies'    => array( 'jquery-ui-datepicker' ) ),
            array( 'src'    => dirname( __FILE__ ) . '/js/datetimepicker-option-handler.js', ),            
        );
    }    
    
    /**
     * Returns an array holding the urls of enqueuing styles.
     */
    protected function getEnqueuingStyles() { 
        return array(
            dirname( __FILE__ ) . '/css/jquery-ui-1.10.3.min.css',
            dirname( __FILE__ ) . '/css/jquery-ui-timepicker-addon.min.css',
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
                    added_repeatable_field: function( oCloned, sFieldType, sFieldTagID, iCallerType ) {
            
                        /* If it is not this field type, do nothing. */
                        if ( jQuery.inArray( sFieldType, {$_aJSArray} ) <= -1 ) {
                            return;
                        }

                        /* If the input tag is not found, do nothing  */
                        if ( oCloned.find( 'input.datetime_picker' ).length <= 0 ) {
                            return;
                        }
                        
                        /* (Re)bind the date picker script */
                        var oDateTimePickerInput = jQuery( oCloned ).find( 'input.datetime_picker' );
                        oDateTimePickerInput.removeClass( 'hasDatepicker' );
                        var sOptionID = jQuery( oCloned ).closest( '.task-scheduler-sections' ).attr( 'id' ) 
                            + '_' 
                            + jQuery( oCloned ).closest( '.task-scheduler-fields' ).attr( 'id' );    // sections id + _ + fields id 
                        var aOptions = jQuery( '#' + oDateTimePickerInput.attr( 'id' ) ).getDateTimePickerOptions( sOptionID );
                        oDateTimePickerInput.datetimepicker( aOptions );                        
                        
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
    protected function getStyles() { return ""; }    
        
    /**
     * Returns the output of this field type.
     */
    protected function getField( $aField ) { 
            
        $aInputAttributes = array(
            'type'              =>    'text',
            'data-date_format'  => $aField['date_format'],
            'data-time_format'  => $aField['time_format'],
        ) + $aField['attributes'];
        $aInputAttributes['class'] .= ' datetime_picker';
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
            . $this->_getDateTimePickerEnablerScript( $aField['input_id'], $aField['date_format'], $aField['time_format'], $aField['options'] )
            . $aField['after_label'];
        
    }    

        /**
         * A helper function for the above getDateField() method.
         * 
         */
        protected function _getDateTimePickerEnablerScript( $sInputID, $sDateFormat, $sTimeFormat, $asOptions ) {
            
            if ( is_array( $asOptions ) ) {                
                $aOptions = $asOptions;
                $aOptions['dateFormat'] = isset( $aOptions['dateFormat'] ) ? $aOptions['dateFormat'] : $sDateFormat;
                $aOptions['timeFormat'] = isset( $aOptions['timeFormat'] ) ? $aOptions['timeFormat'] : $sTimeFormat;
                $_sOptions = json_encode( ( array ) $aOptions );    
            } else {
                $_sOptions = ( string ) $asOptions;    
            }
            return 
                "<script type='text/javascript' class='date-time-picker-enabler-script'>
                    jQuery( document ).ready( function() {
                        jQuery( document ).on( 'focus', 'input#{$sInputID}:not(.hasDatepicker)', function() {
                            jQuery( this ).datetimepicker( {$_sOptions} );
                        });                                               
                        var sOptionID = jQuery( '#{$sInputID}' ).closest( '.task-scheduler-sections' ).attr( 'id' ) + '_' + jQuery( '#{$sInputID}' ).closest( '.task-scheduler-fields' ).attr( 'id' );
                        jQuery( '#{$sInputID}' ).setDateTimePickerOptions( sOptionID, {$_sOptions});                                
                    });
                </script>";            
        }
    
}
endif;