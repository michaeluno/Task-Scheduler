<?php
/**
 * One of the base classes of the plugin admin page class for the wizard pages.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Defines the behaviour when the user finishes defining a task.
 * 
 */
abstract class TaskScheduler_AdminPage_Wizard_Tab_CreateTask extends TaskScheduler_AdminPage_Wizard_Tab_SelectAction {

    /**
     * A callback method triggered when the 'wizard_create_task' tab is loaded in the 'ts_add_new' page.
     * 
     * @since           1.0.0
     * @callback        action      load_ + page slug + tab
     */
    public function load_ts_add_new_wizard_create_task() {   

        $_aWizardOptions = $this->_getWizardOptions();
        $this->_deleteWizardOptions();

        // Check the required keys - the user may comeback to the page from the browser's Back button.
        if ( ! isset( $_aWizardOptions['post_title'], $_aWizardOptions['occurrence'], $_aWizardOptions['routine_action'] ) ) {

            $this->setSettingNotice( __( 'Required data are missing. Please start from the beginning.', 'task-scheduler' ) );
            exit( TaskScheduler_PluginUtility::goToAddNewPage() );
            
        }

        // Drop unnecessary form elements.
        $_aWizardOptions = $this->_dropUnnecessaryWizardOptions( $_aWizardOptions );
        
        // Add advanced options - these will appear in the Advanced meta box in the task edit page.
        $_aWizardOptions['_max_root_log_count'] = TaskScheduler_Option::get( array( 'task_default', 'max_root_log_count' ) );
        $_aWizardOptions['_max_execution_time'] = TaskScheduler_Option::get( array( 'task_default', 'max_execution_time' ) );
        $_aWizardOptions['_force_execution']    = false;

        // Create a task as post and schedule the next run time.
        $_iPostID = TaskScheduler_TaskUtility::add( $_aWizardOptions );
        if ( $_iPostID ) {
            $_oTask    = TaskScheduler_Routine::getInstance( $_iPostID );
            $_oTask->setNextRunTime();
            // @todo: perform the heartbeat only if the next scheduled time is very close.
            do_action( 'task_scheduler_action_check_shceduled_actions' );
            $this->setSettingNotice( __( 'A task has been created.', 'task-scheduler' ), 'updated' );
        }
        
        // Go to the task listing table page.
        exit( TaskScheduler_PluginUtility::goToTaskListingPage() );
        
    }
        
        /**
         * Removes unnecessary elements from the saving wizard options array.
         */
        protected function _dropUnnecessaryWizardOptions( array $aWizardOptions ) {

            unset( 
                $aWizardOptions['submit'], 
                $aWizardOptions['transient_key'], 
                $aWizardOptions['previous_urls'],
                $aWizardOptions['action_label'],
                $aWizardOptions['occurrence_label'],
                $aWizardOptions['excerpt']    // @todo: find out when the 'excerpt' element gets added
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
    
}