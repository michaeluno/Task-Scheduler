<?php
/**
 * Admin Page Framework - Field Type Pack
 * 
 * http://en.michaeluno.jp/task-scheduler/
 * Copyright (c) 2014-2015 Michael Uno
 * 
 */

if ( ! class_exists( 'TaskScheduler_TimeCustomFieldType' ) ) :

/**
 * Defines the time field type.
 * 
 * <h3>Field Type Specific Arguments</h3>
 * <ul>
 *  <li>`time_format` - (string) date format default: `H:mm`.</li>
 *  <li>`options` - (array) the option values passed to the <a target="_blank" href="http://trentrichardson.com/examples/timepicker/#tp-options">`datetimepicker()`</a> jQuery plugin method.</li>
 * </ul>
 * 
 * <h3>Example</h3>
 * <code>
 *  array( // Single time picker
 *      'field_id'      => 'time',
 *      'type'          => 'time',
 *      'title'         => __( 'Time', 'task-scheduler' ),
 *      'time_format'   => 'H:mm', // H:mm is the default format.
 *  ),
 *  array( // Repeatable time picker fields
 *      'field_id'      => 'time_repeatable',
 *      'type'          => 'time',
 *      'title'         => __( 'Repeatable Time Fields', 'task-scheduler' ),
 *      'repeatable'    => true,
 *      'options'       => array(     
 *          'hourGrid'      => 4,
 *          'minuteGrid'    => 10,
 *          'timeFormat'    => 'hh:mm tt',
 *      ),
 *      'description'   => __( 'The grid option is set.', 'task-scheduler' ), 
 *  ),
 *  array( // Sortable 
 *      'field_id'      => 'time_sortable',
 *      'type'          => 'time',
 *      'title'         => __( 'Sortable', 'task-scheduler' ),
 *      'sortable'      => true,
 *      'options'       => array(
 *          'hourMin' => 8,
 *          'hourMax' => 16,
 *      ),
 *      'description'   => __( 'The maximum and minimum hours are set.', 'task-scheduler' ), 
 *      array(), // the second item
 *      array(), // the third item
 *  ),     
 * </code>
 * 
 * @see         http://trentrichardson.com/examples/timepicker/#tp-options
 * @since       1.0.0
 * @package     TaskScheduler_AdminPageFrameworkFieldTypePack
 * @subpackage  CustomFieldType
 * @version     1.0.1
 */
class TaskScheduler_TimeCustomFieldType extends TaskScheduler_AdminPageFramework_FieldType {

    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'time', );
    
    /**
     * Defines the default key-values of this field type. 
     * 
     * @remark            $_aDefaultKeys holds shared default key-values defined in the base class.
     */
    protected $aDefaultKeys = array(
        'time_format'   => 'H:mm',
        'attributes'    => array(
            'size'      => 10,
            'maxlength' => 400,
        ),    
        'options'       => array(
            'showButtonPanel' => false,
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
            array( 'src' => dirname( __FILE__ ) . '/js/datetimepicker-option-handler.js', ),    
            array( 'src' => dirname( __FILE__ ) . '/js/jquery-ui-timepicker-addon.min.js', 'dependencies'    => array( 'jquery-ui-datepicker' ) ),
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
         * Called when a field of this field type gets repeated.
         */
        repeated_field: function( oCloned, aModel ) {

            /* (Re)bind the date picker script */
            var oTimePickerInput = jQuery( oCloned ).find( 'input.time_picker' );    
            oTimePickerInput.removeClass( 'hasDatepicker' );
            var sOptionID = jQuery( oCloned ).closest( '.task-scheduler-sections' ).attr( 'id' ) 
                + '_' 
                + jQuery( oCloned ).closest( '.task-scheduler-fields' ).attr( 'id' );    // sections id + _ + fields id 
            var aOptions = jQuery( '#' + oTimePickerInput.attr( 'id' ) ).getDateTimePickerOptions( sOptionID );
            oTimePickerInput.timepicker( aOptions );        
        
        },                      
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
     * Returns the output of the field type.
     */
    protected function getField( $aField ) { 
            
        $aInputAttributes = array(
            'type'    =>    'text',
            'data-time_format'    => $aField['time_format'],
        ) + $aField['attributes'];
        $aInputAttributes['class']    .= ' time_picker';
        return 
            $aField['before_label']
            . "<div class='task-scheduler-input-label-container'>"
                . "<label for='{$aField['input_id']}'>"
                    . $aField['before_input']
                    . ( $aField['label'] && ! $aField['repeatable']
                        ? "<span class='task-scheduler-input-label-string' style='min-width:" . $this->getLengthSanitized( $aField['label_min_width'] ) . ";'>" . $aField['label'] . "</span>"
                        : "" 
                    )
                    . "<input " . $this->getAttributes( $aInputAttributes ) . " />"    // this method is defined in the base class
                    . $aField['after_input']
                    . "<div class='repeatable-field-buttons'></div>"    // the repeatable field buttons will be replaced with this element.
                . "</label>"
            . "</div>"
            . $this->_getTimePickerEnablerScript( $aField['input_id'], $aField['time_format'], $aField['options'] )
            . $aField['after_label'];
        
    }    
    
        /**
         * A helper function for the above getDateField() method.
         * 
         */
        protected function _getTimePickerEnablerScript( $sInputID, $sTimeFormat, $asOptions ) {
            
            if ( is_array( $asOptions ) ) {                
                $aOptions = $asOptions;
                $aOptions['timeFormat'] = isset( $aOptions['timeFormat'] ) ? $aOptions['timeFormat'] : $sTimeFormat;
                $_sOptions = json_encode( ( array ) $aOptions );    
            } else {
                $_sOptions = ( string ) $asOptions;    
            }            
            return 
                "<script type='text/javascript' class='time-picker-enabler-script'>
                    jQuery( document ).ready( function() {
                        jQuery( document ).on( 'focus', 'input#{$sInputID}:not(.hasDatepicker)', function() {
                            jQuery( '#{$sInputID}' ).timepicker({$_sOptions});
                        });                                            
                        var sOptionID = jQuery( '#{$sInputID}' ).closest( '.task-scheduler-sections' ).attr( 'id' ) + '_' + jQuery( '#{$sInputID}' ).closest( '.task-scheduler-fields' ).attr( 'id' );
                        jQuery( '#{$sInputID}' ).setDateTimePickerOptions( sOptionID, {$_sOptions});                            
                    });
                </script>";
        }
    
}
endif;