<?php
/**
 * The class that creates the wizard pages.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
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
            $this->_getWizardOptions() 
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
     */
    protected function _saveWizardOptions( $sTransientKey, array $aMergingOptions ) {
        
        $_aStoredOptions = TaskScheduler_WPUtility::getTransient( $sTransientKey );
        $_aStoredOptions = $_aStoredOptions 
            ? $_aStoredOptions 
            : array();
        $_aSavingOptions = $aMergingOptions + $_aStoredOptions;    // not recursive for repeatable fields. 
        $_aSavingOptions = array_filter( $_aSavingOptions );
        unset( $_aSavingOptions[ 'submit' ] );
        TaskScheduler_WPUtility::setTransient( $sTransientKey, $_aSavingOptions, 30*60 );    // 30 minutes     
        return $_aSavingOptions;
        
    }
    
    /**
     * Returns the wizard options stored in the transient.
     */
    protected function _getWizardOptions( $sKey='' ) {
        
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
     */
    protected function _deleteWizardOptions( $sTransientKey='' ) {
        
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
     * Retrieves the wizard options of the given transient key.
     * 
     * @callback        filter      task_scheduler_admin_filter_get_wizard_options
     */
    public function _replyToGetWizardOptions( $vDefault, $sKey='' ) {

        $_vReturn = $this->_getWizardOptions( $sKey );

        if ( is_null( $_vReturn ) ) {
            return $vDefault;
        }
        if ( empty( $sKey ) && is_array( $_vReturn ) && empty( $_vReturn ) ) {
            return $vDefault;
        }
        return $_vReturn;
        
    }
    
}
