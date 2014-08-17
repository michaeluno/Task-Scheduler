<?php
/**
 * One of the base classes of the editing module options pages.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_AdminPage_EditModule_Tab_UpdateModuleOptions extends TaskScheduler_AdminPage_EditModule_Validation {
    
    /**
     * Adds the 'update_module' tab.
     * 
     * @since    1.0.0
     */         
    protected function _defineInPageTabs() {
                
        $this->addInPageTabs(
            TaskScheduler_Registry::AdminPage_EditModule,    // the target page slug        
            array(    // the options will be redirected to this page and saved and redirected to post.php 
                'tab_slug'            =>    'update_module',    
                'title'                =>    __( 'Update Module Options', 'task-scheduler' ),
                'show_in_page_tab'    =>    false,
            )
        );                
        
        parent::_defineInPageTabs();
        
        add_action( "load_" . TaskScheduler_Registry::AdminPage_EditModule . "_update_module", array( $this, '_replyToLoadUpdateModuleTab' ) );
        
    }
        
    /**
     * A callback method triggered when the 'update_module' tab is loaded in the 'ts_edit_module' page.
     * 
     * @since            1.0.0
     */
    public function _replyToLoadUpdateModuleTab( $oAdminPage ) {

        $_aWizardOptions = $this->_getWizardOptions();
        $this->_deleteWizardOptions();

        // Check the required keys - the user may comeback to the page from the browser's Back button.
        if ( ! isset( $_GET['post'] ) ) {
            $this->setSettingNotice( __( 'The wizard session has been expired. Please start from the beginning.', 'task-scheduler' ) );
            exit( TaskScheduler_PluginUtility::goToEditTaskPage() );            
        }

        // Drop unnecessary form elements. The method is defined in the base class.
        $_bUpdateSchedule    = isset( $_aWizardOptions['_update_next_schedule'] ) ? $_aWizardOptions['_update_next_schedule'] : false;
        $_aWizardOptions    = $this->_dropUnnecessaryWizardOptions( $_aWizardOptions );

        // Update the meta.
        TaskScheduler_WPUtility::updatePostMeta( $_GET['post'], $_aWizardOptions );        
        if ( $_bUpdateSchedule ) {            
            $_oTask    = TaskScheduler_Routine::getInstance( $_GET['post'] );
            $_nLastRunTime = $_oTask->_last_run_time;
            $_oTask->deleteMeta( '_last_run_time' );    // The Filxed Interval occurence type uses the last run time meta data.
            $_oTask->setNextRunTime();
            $_oTask->setMeta( '_last_run_time', $_nLastRunTime );
        }
        $this->setSettingNotice( __( 'The task has been updated.', 'task-scheduler' ), 'updated' );
            
        // Go to the task listing table page.
         exit( TaskScheduler_PluginUtility::goToEditTaskPage() );
        
    }
        
        /**
         * Drops unnecessary elements from the wizard options array.
         */
        protected function _dropUnnecessaryWizardOptions( array $aWizardOptions ) {
            
            unset( 
                // The WordPress core adds these meta data but the plugin does not need these.
                $aWizardOptions['_edit_lock'],
                $aWizardOptions['_edit_last'],
                $aWizardOptions['_update_next_schedule']
            );
            
            return parent::_dropUnnecessaryWizardOptions( $aWizardOptions );

        }        
    
}