<?php
/**
 * The class that creates the wizard pages.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

 /**
  * @filter    apply    task_scheduler_admin_filter_wizard_options        Applies to the wizard options to be set to the form options.
  * @filter    add      task_scheduler_admin_filter_get_wizard_options    Applies the wizard options.
  */
class TaskScheduler_AdminPage_Wizard extends TaskScheduler_AdminPage_Wizard_Setup {
    
    public function start() {
    
        parent::start();
        
        add_filter( 
            'task_scheduler_admin_filter_get_wizard_options', 
            array( $this, '_replyToGetWizardOptions' ), 
            10, 
            2 
        );
        
    }
    
    /**
     * Sets the form options.
     * 
     * Each module will store their options in the '_wizard_options' custom section in the page-options-validation callback in the module's wizard base class.
     * In addition, _this_ wizard class will save the '_wizard_options' element in the class validation callback method into the transient.
     * The saved transient again here gets loaded and assigned as the form options.
     * 
     * @callback    filter options_{class name}
     * @return      array
     */
    public function options_TaskScheduler_AdminPage_Wizard( $aOptions ) {

        // Since the wizard options do not have a section dimension (in the first depth), store the options in each section.
        $_aOptions = apply_filters( 
            'task_scheduler_admin_filter_wizard_options', 
            $this->getWizardOptions() 
        );

        return array(
            // section id    => field values.
            'wizard'                => $_aOptions, // the first wizard tab
            'wizard_select_action'  => $_aOptions, // the task action selection tab
            '_wizard_options'       => $_aOptions, // for each module.
        );

    }
    
    /**
     * Saves the wizard options.
     * 
     * @since       unknown
     * @since       1.4.0       Changed the visibility scope to `public` from `protected`.
     * @since       1.4.0       Renamed from `_saveWizardOptions()`.
     * @return      array
     */
    public function saveWizardOptions( $sTransientKey, array $aMergingOptions ) {
        
        $_aStoredOptions = TaskScheduler_WPUtility::getTransient( $sTransientKey );
        $_aStoredOptions = $_aStoredOptions 
            ? $_aStoredOptions 
            : array();
        $_aSavingOptions = $aMergingOptions + $_aStoredOptions;    // not recursive for repeatable fields. 
        $_aSavingOptions = array_filter( $_aSavingOptions );
        unset( $_aSavingOptions[ 'prevnext' ] );
        TaskScheduler_WPUtility::setTransient( $sTransientKey, $_aSavingOptions, 30*60 );    // 30 minutes     
        return $_aSavingOptions;
        
    }
    
    /**
     * Returns the wizard options stored in the transient.
     * 
     * @since       unknown
     * @since       1.4.0       Changed the visibility scope to `public` from `protected` as this is accessed from the wizard base class.
     * @since       1.4.0       Renamed from `_getWizardOptions()`.
     * @return      mixed|null|array
     */
    public function getWizardOptions( $sKey='' ) {
        
        static $_aWizardOptions;
        $_sTransientKey = isset( $_GET[ 'transient_key' ] ) 
            ? $_GET[ 'transient_key' ]
            : '';
        
        // If already retrieved, use it.
        $_aWizardOptions = isset( $_aWizardOptions ) && false !== $_aWizardOptions
            ? $_aWizardOptions
            : TaskScheduler_WPUtility::getTransient( $_sTransientKey );

        // If the key is not set, return the entire array.
        if ( empty( $sKey ) ) {
            return is_array( $_aWizardOptions )
                ? $_aWizardOptions
                : array();
        }
        // Otherwise, return the element specified with the key.
        return isset( $_aWizardOptions[ $sKey ] )
            ? $_aWizardOptions[ $sKey ]
            : null;
        
    }    
    
    /**
     * Deletes the wizard option transients.
     * 
     * @since       1.4.0       Changed the visibility scope to `public` from `protected` as delegatin classes access it.
     * @since       1.4.0       Renamed from `_deleteWizardOptions()`.
     * @return      void
     */
    public function deleteWizardOptions( $sTransientKey='' ) {
        
        $sTransientKey = $sTransientKey 
            ? $sTransientKey
            : ( 
                isset( $_GET[ 'transient_key' ] ) 
                    ? $_GET[ 'transient_key' ] 
                    : '' 
            );
        TaskScheduler_WPUtility::deleteTransient( $sTransientKey );
        
    }
       
    /**
     * Removes unnecessary elements from the saving wizard options array.
     * 
     * @since       unknown
     * @since       1.4.0       Changed the visibility scope to `public` from `protected` as delegatin classes access it.
     * @since       1.4.0       Renamed from `_dropUnnecessaryWizardOptions()`.
     */
    public function dropUnnecessaryWizardOptions( array $aWizardOptions ) {

        unset( 
            $aWizardOptions[ 'prevnext' ], 
            $aWizardOptions[ 'transient_key' ], 
            $aWizardOptions[ 'previous_urls' ],
            $aWizardOptions[ 'action_label' ],
            $aWizardOptions[ 'occurrence_label' ],
            $aWizardOptions[ 'excerpt' ]    // @todo: find out when the 'excerpt' element gets added
        );
        
        // Remove section keys that are used for modules with multiple screens.
        $_sMainActionSlug   = $aWizardOptions[ 'routine_action' ];
        $_aSectionSlugs     = apply_filters( "task_scheduler_admin_filter_wizard_slugs_{$_sMainActionSlug}", array() );
        foreach( $_aSectionSlugs as $_sSectionSlug ) {
            if ( $_sSectionSlug === $_sMainActionSlug ) { 
                continue; 
            }
            unset( $aWizardOptions[ $_sSectionSlug ] );
        }    
        
        // Some are added while going back and force in the wizard screens.
        $_aOccurrenceSlugs = apply_filters( 'task_scheduler_admin_filter_field_labels_wizard_occurrence', array() );
        $_aOccurrenceSlugs = array_keys( $_aOccurrenceSlugs );
        foreach( $_aOccurrenceSlugs as $_sOccurrenceSlug ) {
            if ( $_sOccurrenceSlug === $aWizardOptions['occurrence'] ) { 
                continue; 
            }
            unset( $aWizardOptions[ $_sOccurrenceSlug ] );            
        }
        $_aActionSlugs = apply_filters( 'task_scheduler_admin_filter_field_labels_wizard_action', array() );
        $_aActionSlugs = array_keys( $_aActionSlugs );
        foreach( $_aActionSlugs as $_sActionSlug ) {
            if ( $_sActionSlug === $aWizardOptions['routine_action'] ) { 
                continue; 
            }
            unset( $aWizardOptions[ $_sActionSlug ] );                        
        }
    
        return $aWizardOptions;
        
    }       
       
       
    /**
     * Get the redefined routine action field definition array.
     * 
     * Used to redefine the 'routine_action' field of the 'wizard_select_action' section.
     * 
     * @remark    The scope is protected because the extending Edit Module class also uses it.
     * @since     unknown
     * @since     1.4.0     Renamed
     * @since     1.4.0     Changed the scope from `protected`.
     * @return    array
     */
    public function getRoutineActionField( $aField ) {
        
        $_sRoutineActionSlug = $this->getWizardOptions( 'routine_action' );
        $aField[ 'label' ]   = apply_filters( 
            'task_scheduler_admin_filter_field_labels_wizard_action',
            array()
        );

        // Set the default value.
        $aField[ 'value' ] = array_key_exists ( $_sRoutineActionSlug, $aField[ 'label' ] )
            ? "#description-{$_sRoutineActionSlug}"
            : -1;

        // Convert the keys to the 'revealer' field type specification.
        $_aLabels = array(
            -1    =>    '--- ' . __( 'Select Action', 'task-scheduler' ) . ' ---',
        );
        $_aDescriptions = array();
        foreach( $aField[ 'label' ] as $_sSlug => $_sLabel ) {
            
            $_aLabels[ "#description-{$_sSlug}" ] = $_sLabel;
            
            // Create action description hidden elements.
            $_sDescription    = apply_filters( "task_scheduler_filter_description_action_{$_sSlug}", '' );
            if ( ! $_sDescription ) { 
                continue; 
            }
            $_aDescriptions[ $_sSlug ] = "<p id='description-{$_sSlug}' class='description' style='display: none;'>"
                . $_sDescription
             . "</p>";            
             
        }

        // Selectors
        $aField[ 'selectors' ] = array(
            -1 => '.custom-action, .arguments'
        );

        $aField[ 'label' ] = $_aLabels;
        $aField[ 'after_fieldset' ]  = implode( PHP_EOL, $_aDescriptions );
        return $aField;
        
    }
       
       
       
    /**
     * Retrieves the wizard options of the given transient key.
     * 
     * Used by the `TaskScheduler_Wizard_Base` class.
     * 
     * @callback        filter      task_scheduler_admin_filter_get_wizard_options
     */
    public function _replyToGetWizardOptions( $vDefault, $sKey='' ) {

        $_vReturn = $this->getWizardOptions( $sKey );

        if ( is_null( $_vReturn ) ) {
            return $vDefault;
        }
        if ( empty( $sKey ) && is_array( $_vReturn ) && empty( $_vReturn ) ) {
            return $vDefault;
        }
        return $_vReturn;
        
    }
    
}
