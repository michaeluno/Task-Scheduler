<?php
/**
 * Admin Page Framework - Field Type Pack
 * 
 * http://en.michaeluno.jp/task-scheduler/
 * Copyright (c) 2014-2015 Michael Uno
 * 
 */

if ( ! class_exists( 'TaskScheduler_TimeRangeCustomFieldType' ) ) :

/**
 * Defines the time range field type.
 * 
 * <h3>Field Type Specific Arguments</h3>
 * <ul>
 *  <li>`date_format` - (string) date format default: `yy/mm/dd`.</li>
 *  <li>`time_format` - (string) date format default: `H:mm`.</li>
 *  <li>`options` - (array) the option values passed to the <a target="_blank" href="http://trentrichardson.com/examples/timepicker/#tp-options">`datetimepicker()`</a> jQuery plugin method.</li>
 *  <li>`options[from]` - (array) the option values passed to the <a target="_blank" href="http://trentrichardson.com/examples/timepicker/#tp-options">`datetimepicker()`</a> jQuery plugin method, which applies to the <em>From</em> input.</li>
 *  <li>`options[to]` - (array) the option values passed to the <a target="_blank" href="http://trentrichardson.com/examples/timepicker/#tp-options">`datetimepicker()`</a> jQuery plugin method, which applies to the <em>To</em> input.</li>
 * </ul>
 * 
 * <h3>Example</h3>
 * <code>
 *  array( // Single time_range picker
 *      'field_id'      => 'time_range',
 *      'title'         => __( 'Time Range', 'task-scheduler' ),
 *      'type'          => 'time_range',
 *  ),    
 *  array( // Sortable and repeatable time_range picker
 *      'field_id'      => 'time_range_repeatable',
 *      'title'         => __( 'Repeatable Time Range', 'task-scheduler' ),
 *      'type'          => 'time_range',
 *      'time_format'   => 'HH:mm:ss',
 *      'repeatable'    => true,
 *      'sortable'      => true,     
 *  ), 
 * </code>
 * 
 * @since       1.0.0
 * @package     TaskScheduler_AdminPageFrameworkFieldTypePack
 * @subpackage  CustomFieldType
 * @version     1.0.0
 */
class TaskScheduler_TimeRangeCustomFieldType extends TaskScheduler_AdminPageFramework_FieldType {
        
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'time_range', );
    
    /**
     * Defines the default key-values of this field type. 
     * 
     * @remark            $_aDefaultKeys holds shared default key-values defined in the base class.
     */
    protected $aDefaultKeys = array(
        'date_format'       => 'yy/mm/dd',
        'time_format'       => 'H:mm',
        'label_min_width'   => 40, // in pixels
        'label'             => array(
            'from'  => null,
            'to'    => null,
        ),
        'attributes'        => array(
            'from'  => array(
                'size'        => 16,
                'maxlength'   => 400,
            ),
            'to'    => array(
                'size'        => 16,
                'maxlength'   => 400,
            ),            
        ),    
        'options'          => array(
            'from'  => array(
                'showButtonPanel' => false,
            ),                          
            'to'    => array(        
                'showButtonPanel' => false,            
            ),
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
            array( 'src' => dirname( __FILE__ ) . '/js/apf_date_range.js', ),    
            array( 'src' => dirname( __FILE__ ) . '/js/jquery-ui-timepicker-addon.min.js', 'dependencies' => array( 'jquery-ui-datepicker' ) ),
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
                    added_repeatable_field: function( oCloned, sFieldType, sFieldTagID, sCallType ) {
            
                        /* If it is not this field type, do nothing. */
                        if ( jQuery.inArray( sFieldType, {$_aJSArray} ) <= -1 ) {
                            return;
                        }
            
                        /* If the input tag is not found, do nothing  */
                        if ( oCloned.find( 'input.timepicker' ).length <= 0 ) {
                            return;
                        }
                        
                        /* (Re)bind the date picker script */
                        var oTimePickerInput = jQuery( oCloned ).find( 'input.timepicker.from' );
                        var oTimePickerInput_To = jQuery( oCloned ).find( 'input.timepicker.to' );
                        var sOptionID = jQuery( oCloned ).closest( '.task-scheduler-sections' ).attr( 'id' )
                            + '_' 
                            + jQuery( oCloned ).closest( '.task-scheduler-fields' ).attr( 'id' );    // sections id + _ + fields id 
                        var aOptions_From = jQuery( '#' + oTimePickerInput.attr( 'id' ) ).getDateTimePickerOptions( sOptionID + '_from' );
                        var aOptions_To = jQuery( '#' + oTimePickerInput_To.attr( 'id' ) ).getDateTimePickerOptions( sOptionID + '_to' );
                        oTimePickerInput.apf_time_range( oTimePickerInput_To.attr( 'id' ), aOptions_From, aOptions_To );                        
                    
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
            .form-table td .task-scheduler-field-time_range label {
                display: inline-block;
                width:    auto;
                padding-right: 1em;
            }
            .form-table td .task-scheduler-field-time_range .task-scheduler-repeatable-field-buttons {
                margin-bottom: 0;
            }
            " . PHP_EOL;
    }    
        
    /**
     * Returns the output of this field type.
     */
    protected function getField( $aField ) { 
        
        // Attributes
        $_aInputAttributes_From = array(
            'type'    => 'text',
            'id'      => $aField['input_id'] . '_from',
            'name'    => $aField['_input_name'] . '[from]',
            'value'   => isset( $aField['attributes']['value'][ 'from' ] ) ? $aField['attributes']['value'][ 'from' ] : null,
        ) + $aField['attributes']['from'] + $aField['attributes'];
        $_aInputAttributes_From['class']    .= ' from timepicker';
        $_aInputAttributes_To = array(
            'type'    => 'text',
            'id'      => $aField['input_id'] . '_to',
            'name'    => $aField['_input_name'] . '[to]',
            'value'   => isset( $aField['attributes']['value'][ 'to' ] ) ? $aField['attributes']['value'][ 'to' ] : '',
        ) + $aField['attributes']['to'] + $aField['attributes'];
        $_aInputAttributes_To['class'] .= ' to timepicker';
    
        // Labels
        $aField['label']['from'] = isset( $aField['label']['from'] ) ? $aField['label']['from'] : __( 'From', 'task-scheduler' ) . ':';
        $aField['label']['to'] = isset( $aField['label']['to'] ) ? $aField['label']['to'] : __( 'To', 'task-scheduler' ) . ':';
        
        // Options
        $_aOptions_From = $this->_getSubOptions( 'from', $aField['options'] );
        $_aOptions_To = $this->_getSubOptions( 'to', $aField['options'] );
                
        return 
            $aField['before_label']
            . "<div class='task-scheduler-input-label-container'>"
                . "<label for='{$aField['input_id']}_from'>"
                    . $aField['before_input']
                    . ( $aField['label'] 
                        ? "<span class='task-scheduler-input-label-string' style='min-width:" . $this->sanitizeLength( $aField['label_min_width'] ) . ";'>" . $aField['label']['from'] . "</span>"
                        : "" 
                    )
                    . "<input " . $this->generateAttributes( $_aInputAttributes_From ) . " />"
                    . $aField['after_input']
                . "</label>"
                . "<label for='{$aField['input_id']}_to'>"
                    . $aField['before_input']
                    . ( $aField['label'] 
                        ? "<span class='task-scheduler-input-label-string' style='min-width:" . $this->sanitizeLength( $aField['label_min_width'] ) . ";'>" . $aField['label']['to'] . "</span>"
                        : "" 
                    )
                    . "<input " . $this->generateAttributes( $_aInputAttributes_To ) . " />"
                    . $aField['after_input']
                . "</label>"                
                . "<label><div class='repeatable-field-buttons'></div></label>"    // the repeatable field buttons will be replaced with this element.
            . "</div>"
            . $this->_getDatePickerEnablerScript( $aField['input_id'], $aField['time_format'], $_aOptions_From, $_aOptions_To )
            . $aField['after_label'];
        
    }    

        /**
         * A helper function for the above _replyToGetField() method.
         */
        protected function _getDatePickerEnablerScript( $sInputID, $sTimeFormat, $asOptions_From, $asOptions_To ) {
            
            $_sInputID_From   = $sInputID . '_from';
            $_sInputID_To     = $sInputID . '_to';
            $_sOptions_From   = $this->_getEncodedOptions( $asOptions_From, $sTimeFormat );
            $_sOptions_To     = $this->_getEncodedOptions( $asOptions_To, $sTimeFormat );
            return     
                "<script type='text/javascript' class='time-picker-enabler-script' >            
                    jQuery( document ).ready( function() {
                        jQuery( '#{$_sInputID_From}' ).apf_time_range( '{$_sInputID_To}', {$_sOptions_From}, {$_sOptions_To} );
                    });
                </script>";
        }
            /**
             * Returns the JSON encoded options.
             */
            private function _getEncodedOptions( $asOptions, $sTimeFormat ) {
                if ( is_array( $asOptions ) ) {                
                    $aOptions = $asOptions;
                    $aOptions['timeFormat'] = isset( $aOptions['timeFormat'] ) ? $aOptions['timeFormat'] : $sTimeFormat;
                    return json_encode( ( array ) $aOptions );    
                } 
                return ( string ) $asOptions;    
            }        
        /**
         * Returns the option array of the given sub-option key.
         * 
         * This is used for sub-option elements. In this field type, there are 'from' and 'to' sub-elements.
         * The user can set the shared options in the first depth of the 'options' argument array. And in the first depth,
         * the 'from' and 'to' argument arrays can be set and they take their precedence. 
         */
        protected function _getSubOptions( $sKey, array $aOptions ) {
            
            static $_aBuiltinSubOptionKeys = array( 'from', 'to' );
            $_asSubOptions = isset( $aOptions[ $sKey ] ) ? $aOptions[ $sKey ] : array();
            foreach( $_aBuiltinSubOptionKeys as $_sSubOptionKey )  {
                unset( $aOptions[ $_sSubOptionKey ] );                
            }
            return is_array( $_asSubOptions )
                ? $_asSubOptions + $aOptions
                : $_asSubOptions;    // string
                
        }        
}        
endif;