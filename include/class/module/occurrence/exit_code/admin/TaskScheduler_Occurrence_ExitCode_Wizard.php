<?php
/**
 * Creates wizard pages for the 'exit_code' occurrence type.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

final class TaskScheduler_Occurrence_ExitCode_Wizard extends TaskScheduler_Wizard_Occurrence_Base {
    
    /**
     * Stores the submit form values to set the meta data.
     */
    private $_aSubmit = array();
    
    /**
     * User constructor.
     */
    public function construct() {}
    
    /**
     * Returns the field definition arrays.
     * 
     * @remark        The field definition structure must follows the specification of Admin Page Framework v3.
     */ 
    public function getFields() {
    
        return array(        
            array(    
                'field_id'            => 'exit_code',
                'title'               => __( 'Exit Code', 'task-scheduler' ),
                'type'                => 'text',
            ),        
            array(    
                'field_id'            => 'task_ids',
                'title'               => __( 'Tasks', 'task-scheduler' ),
                'type'                => 'autocomplete',
                'description'         => __( 'Leave this empty to apply to any tasks.', 'task-scheduler' ),
                'settings'            => add_query_arg( array( 'request' => 'autocomplete', 'post_types' => TaskScheduler_Registry::$aPostTypes[ 'task' ], 'post_status' => 'private', ) + $_GET, admin_url( TaskScheduler_AdminPageFramework_WPUtility::getPageNow() ) ),
                'settings2'           => array(    // equivalent to the second parameter of the tokenInput() method
                    // 'tokenLimit'        =>    1,
                    'preventDuplicates' => true,
                    'hintText'          => __( 'Type a task name.', 'task-scheduler' ),
                    'theme'             => 'admin_page_framework',    
                    'searchDelay'       => 5,    // 50 milliseconds. Default: 300
                ),                
            ),                        
        );
        
    }    
        
    public function validateSettings( /* $aInput, $aOldInput, $oAdminPage, $aSubmitInfo */ ) { 
        
        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInput      = $_aParams[ 0 ];
        $aOldInput   = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];             
        
        $_bIsValid = true;
        $_aErrors = array();
            
        if ( ! isset( $aInput['exit_code'] ) || '' == $aInput['exit_code'] ) {

            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors[ $this->_sSectionID ][ 'exit_code' ] = __( 'An exit code need to be set.', 'task-scheduler' );
            $_bIsValid = false;            
            
        } 
        if ( ! isset( $aInput['task_ids'] ) || '' == $aInput['task_ids'] ) {

            // $aVariable[ 'sectioni_id' ]['field_id']
            $_aErrors[ $this->_sSectionID ][ 'task_ids' ] = __( 'Task IDs are required.', 'task-scheduler' );
            $_bIsValid = false;            
            
        } 
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            return array();
            
        }    
        
        unset( $aInput['submit'] );
        
        // Now these options will be stored in the 'on_exit_code' meta key. However, the exit code event handler needs the meta data being saved in the top level
        // to perform the query and process the tasks with the 'on_exit_code' occurrence type.
        $this->_aSubmit = $aInput;
        add_filter( "task_scheduler_admin_filter_saving_wizard_options", array( $this, '_replyToSetTopLevelMetaData' ), 10, 3 );
        
        return $aInput; 
        
    }
        /**
         * Checks if an action is selected.
         */
        function _isActionSelected( $sActionSlug ) {
            return ( '-1' === $sActionSlug || -1 === $sActionSlug )
                ? false 
                : true;
        }
    
    /**
     * Sets the submit form data in the top level of the wizard options so that they will be stored as the top level meta keys.
     */
    public function _replyToSetTopLevelMetaData( $aWizardOptions ) {
            
        if ( empty( $this->_aSubmit ) ) {
            return $aWizardOptions;
        }
        
        // At the moment, set only two exit code options.
        $aWizardOptions[ '__on_exit_code' ]          = $this->_aSubmit[ 'exit_code' ];
        $aWizardOptions[ '__on_exit_code_task_ids' ] = $this->_getSetTaskIDs( $this->_aSubmit['task_ids'] );
        if ( empty( $aWizardOptions[ '__on_exit_code_task_ids' ] ) ) {
            unset( $aWizardOptions[ '__on_exit_code_task_ids' ] );
        }
        return $aWizardOptions;
                
    }
        /**
         * Returns an array of set task IDs.
         * 
         * The structure of the JSON array will look like
         * [{"id":405,"name":"3 Minutes Task"},{"id":407,"name":"My Task"}]
         */
        private function _getSetTaskIDs( $sJSONTaskIDs ) {
            
            if ( ! $sJSONTaskIDs ) { 
                return array(); 
            }
            $_aJSONTaskIDs = json_decode( $sJSONTaskIDs, true );
            $_aTaskIDs = array();
            foreach( $_aJSONTaskIDs as $_aTaskID ) {
                if ( ! isset( $_aTaskID['id'] ) ) { 
                    continue; 
                }
                $_aTaskIDs[] = $_aTaskID['id'];
            }
            return $_aTaskIDs;
            
        }
    
}