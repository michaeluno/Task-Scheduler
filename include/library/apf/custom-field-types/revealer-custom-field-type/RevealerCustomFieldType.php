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
 * @version     1.1.1
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
        'is_global'     => false,            // When true, the show/hide action will be applied to the global DOM structure. Otherwise (foe false), the selector will be applied withing the residing section.
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
     * Loads the field type necessary components.
     */
    protected function setUp() {

        add_action( 'admin_footer', array( $this, 'replyToEnqueueScripts' ), 1 );

        // for front-end forms
        add_action( 'wp_footer', array( $this, 'replyToEnqueueScripts' ), 1 );
        add_action( 'embed_footer', array( $this, 'replyToEnqueueScripts' ), 1 );

        $this->___checkFrameworkVersion();

    }
        /**
         */
        private function ___checkFrameworkVersion() {

            // Requires Admin Page Framework 3.7.1+
            if (
                method_exists( $this, 'getFrameworkVersion' )
                && version_compare( '3.7.1', $this->___getSuffixRemoved( $this->getFrameworkVersion(), '.dev'  ), '<=' )
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
            private function ___getSuffixRemoved( $sString, $sSuffix ) {
                $_iLength = strlen( $sSuffix );
                if ( substr( $sString, $_iLength * -1 ) !== $sSuffix ) {
                    return $sString;
                }
                return substr( $sString, 0, $_iLength * - 1 );
            }

    /**
     * @var array
     * @since 1.1.0
     * @remark This is static to support more than one revealer instances in one page.
     */
    static public $aAddedFields = array();

    /**
     * @callback admin_footer
     * @since 1.1.0
     */
    public function replyToEnqueueScripts() {
        $_aData = array(
            'fieldTypeSlugs' => $this->aFieldTypeSlugs,
            'fields'         => self::$aAddedFields,
            'debugMode'    => $this->isDebugMode(),
        );
        wp_enqueue_script(
            'apfRevealerFieldType',
            $this->getResolvedSRC( dirname( __FILE__ ) . '/js/revealer.js' ),
            array( 'jquery' ),
            false
        );
        wp_localize_script( 'apfRevealerFieldType', 'apfRevealerFieldType', $_aData );
    }

    /**
     * Returns an array holding the urls of enqueuing scripts.
     */
    protected function getEnqueuingScripts() {
        return array();
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
        return '';
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

        $_aOutput    = array();
        $_aSelectors = $this->___getSelectors( $aField );
        $aField      = $this->___getInnerFieldArraySanitized( $aField, $_aSelectors );
        $_aOutput[]  = $this->getFieldOutput( $aField );
        $_aSelected  = array(); // the selected values

        // Store the field information to pass to the revealer JavaScript script.
        self::$aAddedFields[ $aField[ 'input_id' ] ] = array(
            'isGlobal'  => $aField[ 'is_global' ],
            'inputID'   => $aField[ 'input_id' ],
            'selectors' => $_aSelectors,
            'selected'  => $_aSelected,
            'type'      => $aField[ 'select_type' ],
        );
        return implode( PHP_EOL, $_aOutput );

    }
        private function ___getSelectors( array $aField ) {
            if ( ! empty( $aField[ 'selectors' ] ) ) {
                return $this->getAsArray( $aField[ 'selectors' ] );
            }
            return array_keys( $this->getAsArray( $aField[ 'label' ] ) );
        }
        /**
         * Sanitize (re-format) the field definition array to get the field output by the select type.
         *
         * @since       3.4.0
         */
        private function ___getInnerFieldArraySanitized( array $aField, array $aSelectors ) {

            // The revealer field type has its own description element.
            unset(
                $aField[ 'description' ],
                $aField[ 'title' ]
            );

            // When the `hidden` argument is turned on, the outer field row should be hidden.
            // If the inner fieldset is hidden, it cannot be revealed.
            $aField[ 'hidden' ] = false;

            // The revealer script of check boxes needs the reference of the selector to reveal.
            // For radio and select input types, the key of the label array can be used but for the checkbox input type,
            // the value attribute needs to be always 1 (for cases of key of zero '0') so the selector needs to be separately stored.
            $_sSelectors = implode( ',', $aSelectors );
            switch( $aField[ 'select_type' ] ) {
                default:
                case 'select':
                    foreach( $this->getAsArray( $aField[ 'label' ] ) as $_sKey => $_sLabel ) {
                        // If the user sets the 'selectors' argument, its value will be used; otherwise, the label key will be used.
                        $_sSelector = $this->getElement( $aSelectors, array( $_sKey ), $_sKey );
                        $aField[ 'attributes' ][ 'select' ] = array(
                            'data-reveal' => $_sSelector, // this is only for identifying the select element is of the revealer field type, not for referencing.
                            // Embed all the selectors for the field so that the other members can be referred when showing an item.
                            // This is especially needed for repeatable sections.
                            'data-selectors' => $_sSelectors,
                        );
                        $aField[ 'attributes' ][ 'option' ][ $_sKey ] = array(
                                'data-reveal'   => $_sSelector,
                                'data-global'   => ( integer ) $aField[ 'is_global' ],
                            )
                            + $this->getElementAsArray( $aField[ 'attributes' ], array( 'option', $_sKey ) );
                    }
                    break;
                case 'radio':
                case 'checkbox':
                    // for a single item
                    if ( is_string( $aField[ 'label' ] ) ) {
                        $_sSelector = $this->getElement( $aSelectors, array( 0 ), '0' );
                        $aField[ 'attributes' ] = array(
                            'data-reveal'    => $_sSelector,
                            'data-global'    => ( integer ) $aField[ 'is_global' ],
                            'data-selectors' => $_sSelectors,
                        ) + $aField[ 'attributes' ];
                        break;
                    }
                    // for multiple items
                    foreach( $this->getAsArray( $aField[ 'label' ] ) as $_sKey => $_sLabel ) {
                        // If the user sets the 'selectors' argument, its value will be used; otherwise, the label key will be used.
                        $_sSelector = $this->getElement( $aSelectors, array( $_sKey ), $_sKey );
                        $aField[ 'attributes' ][ $_sKey ] = array(
                                'data-reveal'    => $_sSelector,
                                'data-global'    => ( integer ) $aField[ 'is_global' ],
                                'data-selectors' => $_sSelectors,
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

}
endif;