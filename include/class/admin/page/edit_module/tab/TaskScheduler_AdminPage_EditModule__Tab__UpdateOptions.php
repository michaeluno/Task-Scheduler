<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Defines the 'Update Module Options' tab page.
 * 
 * One of the base classes of the plugin admin page class for the Edit Module Options pages.
 * @since   1.4.0
 */
class TaskScheduler_AdminPage_EditModule__Tab__UpdateOptions extends TaskScheduler_AdminPage_Tab_Base {

    /**
     * A callback method triggered when the 'update_module' tab is loaded in the 'ts_edit_module' page. 
     * 
     * @since           1.4.0
     * @callback        action      load_{page slug}_{tab slug}
     */ 
    public function replyToLoadTab( $oFactory ) {
        

        $_aWizardOptions = $oFactory->getWizardOptions();
        $oFactory->deleteWizardOptions();

        // Check the required keys - the user may comeback to the page from the browser's Back button.
        if ( ! isset( $_GET[ 'post' ] ) ) {
            $_bDebugInfo = $oFactory->oUtil->isDebugMode()
                ? '<h4>$_GET</h4><pre>' . print_r( $_GET, true ) . '</pre>'
                    . '<h4>$_aWizardOptions' . __METHOD__ . '</h4><pre>' . print_r( $_aWizardOptions, true ) . '</pre>'
                : '';
            $oFactory->setSettingNotice( 
                __( 'The wizard session has been expired. Please start from the beginning.', 'task-scheduler' ) 
                . $_bDebugInfo
            );
            exit( TaskScheduler_PluginUtility::goToEditTaskPage() );            
        }

        // Drop unnecessary form elements. The method is defined in the base class.
        $_bUpdateSchedule   = isset( $_aWizardOptions['_update_next_schedule'] ) 
            ? $_aWizardOptions[ '_update_next_schedule' ]
            : false;
        $_aWizardOptions    = $oFactory->dropUnnecessaryWizardOptions( $_aWizardOptions );

        // Update the meta.
        TaskScheduler_WPUtility::updatePostMeta( $_GET[ 'post' ], $_aWizardOptions );        
        if ( $_bUpdateSchedule ) {            
            $_oTask         = TaskScheduler_Routine::getInstance( $_GET[ 'post' ] );
            $_nLastRunTime  = $_oTask->_last_run_time;
            $_oTask->deleteMeta( '_last_run_time' );    // The Fixed Interval occurrence type uses the last run time meta data.
            $_oTask->setNextRunTime();
            $_oTask->setMeta( '_last_run_time', $_nLastRunTime );
        }
        $oFactory->setSettingNotice( __( 'The task has been updated.', 'task-scheduler' ), 'updated' );
            
        // Go to the task listing table page.
        exit( TaskScheduler_PluginUtility::goToEditTaskPage() );
        
    }
    
}
