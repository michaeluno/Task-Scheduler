<?php
/**
 * 
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Defines the 'Create Task' hidden tab page.
 * 
 * One of the base classes of the plugin admin page class for the wizard pages.
 * @since   1.4.0
 */
class TaskScheduler_AdminPage_Wizard__Tab__CreateTask extends TaskScheduler_AdminPage_Tab_Base {
    
    /**
     * A callback method triggered when the 'wizard_create_task' tab is loaded in the 'ts_add_new' page.
     * 
     * This is the last page of the wizard and it creates a task based on the previous user inputs.
     * This page does not add any elements but redirects the user to the `Manage Tasks` page after creating a task.
     * 
     * @since           1.4.0
     * @callback        action      load_{page slug}_{tab slug}
     */    
    public function replyToLoadTab( $oFactory ) {
        
        $_aWizardOptions = $oFactory->getWizardOptions();
        $oFactory->deleteWizardOptions();

        // Check the required keys - the user may comeback to the page from the browser's Back button.
        if ( ! isset( $_aWizardOptions[ 'post_title' ], $_aWizardOptions[ 'occurrence' ], $_aWizardOptions[ 'routine_action' ] ) ) {

            $oFactory->setSettingNotice( __( 'Required data are missing. Please start from the beginning.', 'task-scheduler' ) );
            exit( TaskScheduler_PluginUtility::goToAddNewPage() );
            
        }

        // Drop unnecessary form elements.
        $_aWizardOptions = $oFactory->dropUnnecessaryWizardOptions( $_aWizardOptions );

        // Add advanced options - these will appear in the Advanced meta box in the task edit page.
        $_aWizardOptions[ '_max_root_log_count' ] = TaskScheduler_Option::get( array( 'task_default', 'max_root_log_count' ) );
        $_aWizardOptions[ '_max_execution_time' ] = TaskScheduler_Option::get( array( 'task_default', 'max_execution_time' ) );
        $_aWizardOptions[ '_force_execution' ]    = false;

        // Create a task as post and schedule the next run time.
        $_iPostID = TaskScheduler_TaskUtility::add( $_aWizardOptions );
        if ( $_iPostID ) {
            $_oTask    = TaskScheduler_Routine::getInstance( $_iPostID );
            $_oTask->setNextRunTime();
            // @todo: perform the heartbeat only if the next scheduled time is very close.
            do_action( 'task_scheduler_action_check_scheduled_actions' );
            $oFactory->setSettingNotice( __( 'A task has been created.', 'task-scheduler' ), 'updated' );
        }
        
        // Go to the task listing table page.
        exit( TaskScheduler_PluginUtility::goToTaskListingPage() );
        
    }
            
}
