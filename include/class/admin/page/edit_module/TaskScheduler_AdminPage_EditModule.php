<?php
/**
 * The final class of the editing module options pages.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

final class TaskScheduler_AdminPage_EditModule extends TaskScheduler_AdminPage_EditModule_Setup {

    /**
     * Sets the form options.
     * 
     * Each module will store their options in the '_wizard_options' custom section in the page-options-validation callback in the module's wizard base class.
     * In addition, _this_ wizard class will save the '_wizard_options' element in the class validation callback method into the transient.
     * The saved transient again here gets loaded and assigned as the form options.
     * 
     */
    public function options_TaskScheduler_AdminPage_EditModule( $aOptions ) {
        
        $_aWizardOptions = $this->getWizardOptions();
        
        // If the wizard options are empty, assume it is the first page of the editing module wizard screen.
        if ( empty( $_aWizardOptions ) && isset( $_GET[ 'transient_key' ] ) ) { // sanitization unnecessary
            $_aWizardOptions = $this->_setWizardOptions( TaskScheduler_Utility::getHTTPQueryGET( 'transient_key' ) );
        }
        
        // This filter lets multiple wizard screens set their options.
        $_aWizardOptions = apply_filters( 'task_scheduler_admin_filter_wizard_options', $_aWizardOptions );
        return array(
            // section id    => field values.
            'edit_action'     => $_aWizardOptions, // for each module.
            'edit_occurrence' => $_aWizardOptions, // for each module.
            '_wizard_options' => $_aWizardOptions, // for each module.
        );

    }
        /**
         * 
         * @return    array    The set options array.
         */
        private function _setWizardOptions( $sTransientKey ) {
            
            if ( ! isset( $_GET[ 'post' ] ) ) { // sanitization unnecessary
                return array();
            }
            
            $_aPostMeta = TaskScheduler_WPUtility::getPostMetas( absint( $_GET[ 'post' ] ) );       // sanitization done
            TaskScheduler_WPUtility::setTransient( $sTransientKey, $_aPostMeta, 30*60 );    // 30 minutes
            return $_aPostMeta;
            
        }
        
    /**
     * Drops unnecessary elements from the wizard options array.
     * 
     * @remark      overrides the method defined in one of the parent classes.
     */
    public function dropUnnecessaryWizardOptions( array $aWizardOptions ) {
        
        unset( 
            // The WordPress core adds these meta data but the plugin does not need these.
            $aWizardOptions['_edit_lock'],
            $aWizardOptions['_edit_last'],
            $aWizardOptions['_update_next_schedule']
        );
        
        return parent::dropUnnecessaryWizardOptions( $aWizardOptions );

    }          
    
}