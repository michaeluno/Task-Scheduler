<?php
/**
 * Admin Page Framework - Field Type Pack
 * 
 * http://en.michaeluno.jp/task-scheduler/
 * Copyright (c) 2014-2015 Michael Uno
 * 
 */

if ( ! class_exists( 'TaskScheduler_RevealerCustomFieldType' ) ) :

/**
 * Defines the revealer field type.
 * 
 * This field type allows the user to hide and reveal chosen HTML elements.
 * 
 * <h3>Field Type Specific Arguments</h3>
 * <ul>
 *  <li>`select_type` - (string) The selector type such as drop-down list, check boxes, or radio buttons.. Accepted values are `select`, `radio`, or `checkbox`.</li>
 *  <li>`attributes` - (array) The array that defines the HTML attributes of the field elements.
 *      <ul>
 *          <li>`select` - (array) The attributes applied the select tag.</li>
 *          <li>`optgroup` - (array) The attributes applied the optgroup tag.</li>
 *          <li>`option` - (array) The attributes applied the option tag.</li>
 *      </ul>
 *  </li>
 *  <li>`label` - (array) Specifies the element to toggle the visibility. Set the jQuery selector of the element to the array key and it will be toggled when the user selects it.</li>
 *  <li>`selectors` - (array) Specifies the selectors of the target element to level in each key of the `label` argument. If this argument is set, the above label key will not be used.</li>
 * </ul>
 * 
 * <h3>Example</h3>
 * <code>
 *  array(
 *      'field_id'      => 'revealer_field_by_id',
 *      'type'          => 'revealer',     
 *      'title'         => __( 'Reveal Hidden Fields', 'task-scheduler' ),
 *      'default'       => 'undefined',
 *      'label'         => array( // the keys represent the selector to reveal, in this case, their tag id : #fieldrow-{section id}_{field id}
 *          'undefined' => __( '-- Select a Field --', 'task-scheduler' ),     
 *          '#fieldrow-revealer_revealer_field_option_a' => __( 'Option A', 'task-scheduler' ),     
 *          '#fieldrow-revealer_revealer_field_option_b, #fieldrow-revealer_revealer_field_option_c' => __( 'Option B and C', 'task-scheduler' ),
 *          '#fieldrow-revealer_another_revealer_field' => __( 'Another Revealer', 'task-scheduler' ),
 *      ),
 *      'description'   => __( 'Specify the selectors to reveal in the <code>label</code> argument keys in the field definition array.', 'task-scheduler' ),
 *  ),
 *  array(
 *      'field_id'      => 'revealer_field_option_a',
 *      'type'          => 'textarea',     
 *      'default'       => __( 'Hi there!', 'task-scheduler' ),
 *      'hidden'        => true,
 *  ),
 *  array(
 *      'field_id'      => 'revealer_field_option_b',     
 *      'type'          => 'password',     
 *      'description'   => __( 'Type a password.', 'task-scheduler' ),     
 *      'hidden'        => true,
 *  ),
 *  array(
 *      'field_id'      => 'revealer_field_option_c',
 *      'type'          => 'text',     
 *      'description'   => __( 'Type text.', 'task-scheduler' ),     
 *      'hidden'        => true,
 *  ),
 *  array(
 *      'field_id'      => 'another_revealer_field',
 *      'type'          => 'revealer',  
 *      'select_type'   => 'radio',
 *      'title'         => __( 'Another Hidden Field', 'task-scheduler' ),
 *      'label'         => array( // the keys represent the selector to reveal, in this case, their tag id : #fieldrow-{field id}
 *          '.revealer_field_option_d' => __( 'Option D', 'task-scheduler' ),     
 *          '.revealer_field_option_e' => __( 'Option E', 'task-scheduler' ),
 *          '.revealer_field_option_f' => __( 'Option F', 'task-scheduler' ),
 *      ),
 *      'hidden'        => true,
 *      'default'       => '.revealer_field_option_e',
 *      'delimiter'     => '<br /><br />',
 *      // Sub-fields
 *      array(
 *          'type'          => 'textarea',     
 *          'class'         => array(
 *              'field' => 'revealer_field_option_d',
 *          ),
 *          'label'         => '',
 *          'default'       => '',
 *          'delimiter'     => '',
 *      ),        
 *      array(
 *          'type'          => 'radio',
 *          'label'         => array(
 *              'a' => __( 'A', 'task-scheduler' ),
 *              'b' => __( 'B', 'task-scheduler' ),
 *              'c' => __( 'C', 'task-scheduler' ),
 *          ),
 *          'default'       => 'a',
 *          'class'         => array(
 *              'field' => 'revealer_field_option_e',
 *          ),
 *          'delimiter'     => '',
 *      ),                        
 *      array(
 *          'type'          => 'select',     
 *          'label'         => array(
 *              'i'     => __( 'i', 'task-scheduler' ),
 *              'ii'    => __( 'ii', 'task-scheduler' ),
 *              'iii'   => __( 'iii', 'task-scheduler' ),
 *          ),                
 *          'default'       => 'ii',
 *          'class'         => array(
 *              'field' => 'revealer_field_option_f',
 *          ),
 *          'delimiter'     => '',
 *      ),   
 *      
 *  ), 
 * </code>
 * 
 * @since       1.0.0
 * @package     TaskScheduler_AdminPageFrameworkFieldTypePack
 * @subpackage  CustomFieldType
 * @version     1.0.5
 */
class TaskScheduler_RevealerCustomFieldType extends TaskScheduler_AdminPageFramework_FieldType {
        
    /**
     * Defines the field type slugs used for this field type.
     */
    public $aFieldTypeSlugs = array( 'revealer', );

    /**
     * Defines the default key-values of this field type.
     *
     * @remark            $_aDefaultKeys holds shared default key-values defined in the base class.
     */
    protected $aDefaultKeys = array(
        'select_type'   => 'select',        // accepts 'radio', 'checkbox'
        'is_multiple'   => false,
        'selectors'     => array(),
        'attributes'    => array(
            'select'    => array(
                'size'          => 1,
                'autofocusNew'  => null,
                'multiple'      => null,    // set 'multiple' for multiple selections. If 'is_multiple' is set, it takes the precedence.
                'required'      => null,
            ),
            'optgroup'  => array(),
            'option'    => array(),
        ),
    );

    /**
     * Indicates whether the JavaScirpt script is inserted or not.
     */
    private static $_bIsLoaded = false;

    /**
     * Loads the field type necessary components.
     */
    protected function setUp() {

        if ( ! self::$_bIsLoaded ) {
            wp_enqueue_script( 'jquery' );
            self::$_bIsLoaded = add_action( 'admin_print_footer_scripts', array( $this, '_replyToAddRevealerjQueryPlugin' ) );
        }

        $this->_checkFrameworkVersion();

    }

        /**
         * @return      void
         */
        private function _checkFrameworkVersion() {

            // Requires Admin Page Framework 3.7.1+
            if (
                method_exists( $this, 'getFrameworkVersion' )
                && version_compare( '3.7.1', $this->_getSuffixRemoved( $this->getFrameworkVersion(), '.dev'  ), '<=' )
            ) {
                return;
            }

            trigger_error(
                $this->getFrameworkName() . ': '
                . sprintf(
                    __( 'This revealer field type version requires Admin Page Framework %1$s to function properly.', 'task-scheduler' )
                    . ' ' . __( 'You are using the framework version %2$s.', 'task-scheduler' ),
                    '3.7.1',
                    $this->getFrameworkVersion()
                ),
                E_USER_WARNING
            );

        }
        /**
         * @return  string
         */
        private function _getSuffixRemoved( $sString, $sSuffix ) {

            $_iLength = strlen( $sSuffix );
            if ( substr( $sString, $_iLength * -1 ) !== $sSuffix ) {
                return $sString;
            }
            return substr( $sString, 0, $_iLength * - 1 );

        }

    /**
     * Returns an array holding the urls of enqueuing scripts.
     */
    protected function getEnqueuingScripts() {
        return array(
            // array( 'src'    => dirname( __FILE__ ) . '/js/jquery.knob.js', 'dependencies'    => array( 'jquery' ) ),
        );
    }

    /**
     * Returns an array holding the urls of enqueuing styles.
     */
    protected function getEnqueuingStyles() {
        return array();
    }


    /**
     * Returns the field type specific JavaScript script.
     */
    protected function getScripts() {
        $_aJSArray      = json_encode( $this->aFieldTypeSlugs );
        $_sDoubleQuote  = '\"';
        return <<<JAVASCRIPTS

/* The below function will be triggered when a new repeatable field is added. Since the APF repeater script does not
    renew the color piker element (while it does on the input tag value), the renewal task must be dealt here separately. */
jQuery( document ).ready( function(){
    
    jQuery().registerTaskScheduler_AdminPageFrameworkCallbacks( {     
        /**
         * Called when a field of this field type gets repeated.
         */
        repeated_field: function( oCloned, aModel ) {
            oCloned.find( 'select[data-reveal],input[type=\"checkbox\"][data-reveal],input[type=\"radio\"][data-reveal]' )
                .setTaskScheduler_AdminPageFrameworkRevealer();          
        },
    },
    {$_aJSArray}
    );
});
JAVASCRIPTS;
    }

    /**
     * Returns IE specific CSS rules.
     */
    protected function getIEStyles() { return ''; }

    /**
     * Returns the field type specific CSS rules.
     */
    protected function getStyles() {
        return "";
    }


    /**
     * Returns the output of the geometry custom field type.
     *
     */
    /**
     * Returns the output of the field type.
     */
    protected function getField( $aField ) {

        $_aOutput   = array();
        $aField     = $this->_sanitizeInnerFieldArray( $aField );
        $_aOutput[] = $this->getFieldOutput( $aField );
        $_aOutput[] = $this->_getRevealerScript( $aField[ 'input_id' ] );
        $_aLabels   = empty( $aField[ 'selectors' ] )
            ? $aField[ 'label' ]
            : array_flip( $this->getAsArray( $aField[ 'selectors' ] ) );
        switch( $aField[ 'select_type' ] ) {
            default:
            case 'select':
            case 'radio':
                $_aOutput[] = $this->_getConcealerScript( $aField[ 'input_id' ], $_aLabels, $aField[ 'value' ] );
                break;
            case 'checkbox':
                if ( is_string( $aField[ 'label' ] ) ) {
                    $_aSelections = empty( $aField[ 'value' ] )
                        ? array()
                        : $this->getAsArray( $aField[ 'selectors' ] );
                } else {
                    $_aSelections = is_array( $aField[ 'value' ] )
                        ? array_keys( array_filter( $aField[ 'value' ] ) )
                        : $aField[ 'label' ];
                }
                $_aOutput[] = $this->_getConcealerScript( $aField[ 'input_id' ], $_aLabels, $_aSelections );
                break;
        }
        return implode( PHP_EOL, $_aOutput );

    }

        /**
         * Sanitize (re-format) the field definition array to get the field output by the select type.
         *
         * @since       3.4.0
         */
        private function _sanitizeInnerFieldArray( array $aField ) {

            // The revealer field type has its own description element.
            unset(
                $aField[ 'description' ],
                $aField[ 'title' ]
            );

            // The revealer script of check boxes needs the reference of the selector to reveal.
            // For radio and select input types, the key of the label array can be used but for the checkbox input type,
            // the value attribute needs to be always 1 (for cases of key of zero '0') so the selector needs to be separately stored.
            $_aSelectors = $this->getAsArray( $aField[ 'selectors' ] );
            switch( $aField[ 'select_type' ] ) {
                default:
                case 'select':
                    foreach( $this->getAsArray( $aField[ 'label' ] ) as $_sKey => $_sLabel ) {
                        // If the user sets the 'selectors' argument, its value will be used; otherwise, the label key will be used.
                        $_sSelector = $this->getElement( $_aSelectors, array( $_sKey ), $_sKey );
                        $aField[ 'attributes' ][ 'select' ] = array(
                            'data-reveal' => $_sSelector, // this is only for identifying the select element is of the revealer field type, not for referencing.
                        );
                        $aField[ 'attributes' ][ 'option' ][ $_sKey ] = array(
                                'data-reveal'   => $_sSelector,
                            )
                            + $this->getElementAsArray( $aField[ 'attributes' ], array( 'option', $_sKey ) );
                    }
                    break;
                case 'radio':
                case 'checkbox':
                    // for a single item
                    if ( is_string( $aField[ 'label' ] ) ) {
                        $_sSelector = $this->getElement( $_aSelectors, array( 0 ), '0' );
                        $aField[ 'attributes' ] = array(
                            'data-reveal'   => $_sSelector,
                        ) + $aField[ 'attributes' ];
                        break;
                    }
                    // for multiple items
                    foreach( $this->getAsArray( $aField[ 'label' ] ) as $_sKey => $_sLabel ) {
                        // If the user sets the 'selectors' argument, its value will be used; otherwise, the label key will be used.
                        $_sSelector = $this->getElement( $_aSelectors, array( $_sKey ), $_sKey );
                        $aField[ 'attributes' ][ $_sKey ] = array(
                                'data-reveal'   => $_sSelector,
                            )
                            + $this->getElementAsArray( $aField[ 'attributes' ], $_sKey );
                    }
                    break;

            }

            // Set the select_type to the type argument.
            return array(
                    'type' => $aField[ 'select_type' ]
                ) + $aField;

        }

        private function _getRevealerScript( $sInputID ) {
            return
                "<script type='text/javascript' >"
                    . '/* <![CDATA[ */ '
                    . "jQuery( document ).ready( function(){
                        jQuery('select[data-id=\"{$sInputID}\"][data-reveal],input[data-id=\"{$sInputID}\"][data-reveal]')
                            .setTaskScheduler_AdminPageFrameworkRevealer();
                    });"
                    . ' /* ]]> */'
                . "</script>";
        }
        private function _getConcealerScript( $sSelectorID, $aLabels, $asCurrentSelection ) {

            $aLabels            = $this->getAsArray( $aLabels );
            $_aCurrentSelection = $this->getAsArray( $asCurrentSelection );
            unset( $_aCurrentSelection[ 'undefined' ] );    // an internal reserved key
            if( ( $_sKey = array_search( 'undefined' , $_aCurrentSelection ) ) !== false ) {
                unset( $_aCurrentSelection[ $_sKey ] );
            }
            $_sCurrentSelection = json_encode( $_aCurrentSelection );

            unset( $aLabels[ 'undefined' ] );
            $aLabels        = array_keys( $aLabels );
            $_sJSONLabels   = json_encode( $aLabels );    // encode it to be usable in JavaScript
            $_sSelectors    = implode( ',', $aLabels );
            return
                "<script type='text/javascript' class='task-scheduler-revealer-field-type-concealer-script'>"
                    . '/* <![CDATA[ */ '
                    . "jQuery( document ).ready( function(){

                        jQuery.each( {$_sJSONLabels}, function( iIndex, sValue ) {
                        
                            /* If it is the selected item, show it */
                            if ( jQuery.inArray( sValue, {$_sCurrentSelection} ) !== -1 ) { 
                                jQuery( sValue ).fadeIn();
                                return true;    // continue
                            }
                            jQuery( sValue ).hide();
                                
                        });
                        
                        // Embed all the selectors for the field so that the other members can be referred when showing an item. 
                        // This is especially needed for repeatable sections.
                        jQuery( 'select[data-id=\"{$sSelectorID}\"][data-reveal], input[type=radio][data-id=\"{$sSelectorID}\"], input[type=checkbox][data-id=\"{$sSelectorID}\"][data-reveal]' )
                            .attr( 'data-selectors', '{$_sSelectors}' );
                            
                        // Trigger  the reveler event.
                        jQuery( 'select[data-id=\"{$sSelectorID}\"][data-reveal], input:checked[type=radio][data-id=\"{$sSelectorID}\"], input:checked[type=checkbox][data-id=\"{$sSelectorID}\"][data-reveal]' )
                            .trigger( 'change' );
                    });"
                    . ' /* ]]> */'
                . "</script>";

        }

    /**
     * Adds the revealer jQuery plugin.
     * @since            3.0.0
     */
    public function _replyToAddRevealerjQueryPlugin() {

        $_sScript = "
( function ( $ ) {
    
    /**
     * Binds the revealer event to the element.
     */
    $.fn.setTaskScheduler_AdminPageFrameworkRevealer = function() {
        apfRevealerOnChange = function() {
            
            var _sTargetSelector        = $( this ).is( 'select' )
                ? $( this ).children( 'option:selected' ).data( 'reveal' )
                : $( this ).data( 'reveal' );
                        
            // For check-boxes       
            if ( $( this ).is( ':checkbox' ) ) {
                var _oElementToReveal       = $( _sTargetSelector );
                if ( $( this ).is( ':checked' ) ) {
                    _oElementToReveal.fadeIn();
                } else {
                    _oElementToReveal.hide();    
                }                      
                return;
            }
            
            // For other types (select and radio).
            var _oElementToReveal       = $( _sTargetSelector );

            // Elements to hide
            var _sSelectors = $( this ).data( 'selectors' );            
            $( _sSelectors ).not( ':selected, :checked' ).hide();

            // Hide the previously hidden element.
            $( _sLastRevealedSelector ).hide();    
                                
            // Store the last revealed item in the local and the outer local variables.
            _sLastRevealedSelector = _sTargetSelector;
            
            if ( 'undefined' === _sTargetSelector ) { 
                return; 
            }
            _oElementToReveal.fadeIn();                                       
            
        }
        var _sLastRevealedSelector;
        this.unbind( 'change', apfRevealerOnChange ); // for repeatable fields               
        this.change( apfRevealerOnChange );
        
    };
                
}( jQuery ));";

        echo "<script type='text/javascript' class='task-scheduler-revealer-jQuery-plugin'>"
                . '/* <![CDATA[ */ '
                . $_sScript
                . ' /* ]]> */'
            . "</script>";

    }
    
}
endif;