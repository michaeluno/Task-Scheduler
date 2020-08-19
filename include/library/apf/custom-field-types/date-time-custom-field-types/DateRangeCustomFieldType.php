<?php
/**
 * Admin Page Framework - Field Type Pack
 * 
 * http://en.michaeluno.jp/task-scheduler/
 * Copyright (c) 2014-2015 Michael Uno
 */

if ( ! class_exists( 'TaskScheduler_DateRangeCustomFieldType' ) ) :

/**
 * Defines the date range field type.
 * 
 * <h3>Field Type Specific Arguments</h3>
 * <ul>
 *  <li>`date_format` - (string) date format default: `yy/mm/dd`.</li>
 *  <li>`options` - (array) the option values passed to the <a target="_blank" href="http://api.jqueryui.com/datepicker/">`datepicker()`</a> jQuery method.</li>
 *  <li>`options[from]` - (array) the option values passed to the <a target="_blank" href="http://api.jqueryui.com/datepicker/">`datepicker()`</a> jQuery method, which applies to the <em>From</em> input.</li>
 *  <li>`options[to]` - (array) the option values passed to the <a target="_blank" href="http://api.jqueryui.com/datepicker/">`datepicker()`</a> jQuery method, which applies to the <em>To</em> input.</li>
 * </ul>
 * <h3>Example</h3>
 * <code>
 *  array( // Single date_range picker
 *      'field_id'      => 'date_range',
 *      'title'         => __( 'Date Range', 'task-scheduler' ),
 *      'type'          => 'date_range',
 *  ),     
 *  array( // Single date_range picker
 *      'field_id'      => 'date_range_repeatable',
 *      'title'         => __( 'Repeatable Date Range', 'task-scheduler' ),
 *      'type'          => 'date_range',
 *      'repeatable'    => true,
 *      'sortable'      => true,
 *      'options'       => array(
 *          'numberOfMonths' => 2,
 *      ),
 *  ),  
 * </code>
 * 
 * @since       1.0.0
 * @package     TaskScheduler_AdminPageFrameworkFieldTypePack
 * @subpackage  CustomFieldType
 * @version     1.0.1
 */
class TaskScheduler_DateRangeCustomFieldType extends TaskScheduler_AdminPageFramework_FieldType {
        
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'date_range', );
    
    /**
     * Defines the default key-values of this field type. 
     * 
     * @remark            $_aDefaultKeys holds shared default key-values defined in the base class.
     */
    protected $aDefaultKeys = array(
        'date_format'       => 'yy/mm/dd',
        'label_min_width'   => 40, // in pixels
        'label'             => array(
            'from'  =>    null,
            'to'    =>    null,
        ),
        'attributes'        => array(
            'from'    =>    array(
                'size'        => 10,
                'maxlength'   => 400,
            ),
            'to'    =>    array(
                'size'        => 10,
                'maxlength'   => 400,
            ),            
        ),    
        'options'        =>    array(
            'from'  =>    array(
                'showButtonPanel'    => false,
            ),
            'to'    =>    array(
                'showButtonPanel'    => false,            
            ),
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
            array( 'src' => dirname( __FILE__ ) . '/js/apf_date_range.js', ),    
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
         * Called when a field of this field type gets repeated.
         */
        repeated_field: function( oCloned, aModel ) {
            /* (Re)bind the date picker script */
            var oDatePickerInput    = jQuery( oCloned ).find( 'input.datepicker.from' );    
            var oDatePickerInput_To = jQuery( oCloned ).find( 'input.datepicker.to' );    
            var sOptionID           = jQuery( oCloned ).closest( '.task-scheduler-sections' ).attr( 'id' ) 
                + '_' 
                + jQuery( oCloned ).closest( '.task-scheduler-fields' ).attr( 'id' );    // sections id + _ + fields id 
            var aOptions_From = jQuery( '#' + oDatePickerInput.attr( 'id' ) ).getDateTimePickerOptions( sOptionID + '_from' );
            var aOptions_To   = jQuery( '#' + oDatePickerInput_To.attr( 'id' ) ).getDateTimePickerOptions( sOptionID + '_to' );
            oDatePickerInput.apf_date_range( oDatePickerInput_To.attr( 'id' ), aOptions_From, aOptions_To );
         
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
    protected function getStyles() {
        
        return "/* Date Picker */
            .ui-datepicker.ui-widget.ui-widget-content.ui-helper-clearfix.ui-corner-all {
                display: none;
            }        
            .form-table td .task-scheduler-field-date_range label {
                display: inline-block;
                width:    auto;
                padding-right: 1em;
            }
            .form-table td .task-scheduler-field-date_range .task-scheduler-repeatable-field-buttons {
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
            'type'     => 'text',
            'id'       => $aField['input_id'] . '_from',
            'name'     => $aField['_input_name'] . '[from]',
            'value'    => isset( $aField['attributes']['value'][ 'from' ] ) ? $aField['attributes']['value'][ 'from' ] : null,
        ) + $aField['attributes']['from'] + $aField['attributes'];
        $_aInputAttributes_From['class'] .= ' from datepicker';
        $_aInputAttributes_To = array(
            'type'     => 'text',
            'id'       => $aField['input_id'] . '_to',
            'name'     => $aField['_input_name'] . '[to]',
            'value'    => isset( $aField['attributes']['value'][ 'to' ] ) ? $aField['attributes']['value'][ 'to' ] : null,
        ) + $aField['attributes']['to'] + $aField['attributes'];
        $_aInputAttributes_To['class'] .= ' to datepicker';
    
        // Labels
        $aField['label']['from'] = isset( $aField['label']['from'] ) ? $aField['label']['from'] : __( 'From', 'task-scheduler' ) . ':';
        $aField['label']['to']   = isset( $aField['label']['to'] ) ? $aField['label']['to'] : __( 'To', 'task-scheduler' ) . ':';
        
        // Options
        $_aOptions_From = $this->_getSubOptions( 'from', $aField['options'] );
        $_aOptions_To   = $this->_getSubOptions( 'to', $aField['options'] );
                
        return 
            $aField['before_label']
            . "<div class='task-scheduler-input-label-container'>"
                . "<label for='{$aField['input_id']}_from'>"
                    . $aField['before_input']
                    . ( $aField['label'] 
                        ? "<span class='task-scheduler-input-label-string' style='min-width:" . $this->getLengthSanitized( $aField['label_min_width'] ) . ";'>" . $aField['label']['from'] . "</span>"
                        : "" 
                    )
                    . "<input " . $this->getAttributes( $_aInputAttributes_From ) . " />"
                    . $aField['after_input']
                . "</label>"
                . "<label for='{$aField['input_id']}_to'>"
                    . $aField['before_input']
                    . ( $aField['label'] 
                        ? "<span class='task-scheduler-input-label-string' style='min-width:" . $this->getLengthSanitized( $aField['label_min_width'] ) . ";'>" . $aField['label']['to'] . "</span>"
                        : "" 
                    )
                    . "<input " . $this->getAttributes( $_aInputAttributes_To ) . " />"
                    . $aField['after_input']
                . "</label>"                
                . "<label><div class='repeatable-field-buttons'></div></label>"    // the repeatable field buttons will be replaced with this element.
            . "</div>"
            . $this->_getDatePickerEnablerScript( $aField['input_id'], $aField['date_format'], $_aOptions_From, $_aOptions_To )
            . $aField['after_label'];
        
    }    

        /**
         * A helper function for the above _replyToGetField() method.
         */
        protected function _getDatePickerEnablerScript( $sInputID, $sDateFormat, $asOptions_From, $asOptions_To ) {
            
            $_sInputID_From  = $sInputID . '_from';
            $_sInputID_To    = $sInputID . '_to';
            $_sOptions_From  = $this->_getEncodedOptions( $asOptions_From, $sDateFormat );
            $_sOptions_To    = $this->_getEncodedOptions( $asOptions_To, $sDateFormat );
            return     
                "<script type='text/javascript' class='date-picker-enabler-script' >            
                    jQuery( document ).ready( function() {
                        jQuery( '#{$_sInputID_From}' ).apf_date_range( '{$_sInputID_To}', {$_sOptions_From}, {$_sOptions_To} );
                    });
                </script>";
        }
            /**
             * Returns the JSON encoded options.
             */
            private function _getEncodedOptions( $asOptions, $sDateFormat ) {
                if ( is_array( $asOptions ) ) {                
                    $aOptions = $asOptions;
                    $aOptions['dateFormat'] = isset( $aOptions['dateFormat'] ) ? $aOptions['dateFormat'] : $sDateFormat;
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