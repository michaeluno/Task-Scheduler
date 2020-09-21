<?php
/**
 * Creates wizard pages for the 'exit_code' occurrence type.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
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
     * @return          array
     */ 
    public function getFields( /* $oAdminPage */ ) {

        return array(
            array(
                'field_id'            => 'exit_code',
                'title'               => __( 'Exit Code', 'task-scheduler' ),
                'type'                => 'text',
                'description'         => array(
                    __( 'For multiple exit codes, separate them by commas.', 'task-scheduler' ) . ' e.g. <code>400, 500</code>'
                ),
                'attributes'          => array(
                    'style' => 'min-width: 600px;',
                ),
            ),
            array(
                'field_id'            => 'negate',
                'title'               => __( 'Negate', 'task-scheduler' ),
                'type'                => 'checkbox',
                'label'               => __( 'Trigger the action when the exit code is not the set one.', 'task-scheduler' ),
            ),
            array(    
                'field_id'            => 'task_ids',
                'title'               => __( 'Tasks', 'task-scheduler' ),
                'type'                => 'autocomplete',
                'description'         => __( 'Leave this empty to apply to any tasks.', 'task-scheduler' ),
                'settings'            => add_query_arg(
                    array(
                        'request'       => 'autocomplete',
                        'post_types'    => TaskScheduler_Registry::$aPostTypes[ 'task' ],
                        'post_status'   => 'private',
                    ) + $_GET,
                    admin_url( TaskScheduler_AdminPageFramework_WPUtility::getPageNow() )
                ),
                'settings2'           => array(    // equivalent to the second parameter of the tokenInput() method
                    // 'tokenLimit'        =>    1,
                    'preventDuplicates' => true,
                    'hintText'          => __( 'Type a task name.', 'task-scheduler' ),
                    'theme'             => 'admin_page_framework',    
                    'searchDelay'       => 5,    // 50 milliseconds. Default: 300
                ),
                'min_width'             => '100%',
                'attributes'          => array(
                    'style' => 'min-width: 600px;',
                ),
            ),                        
        );

    }    


    public function validateSettings( /* $aInputs, $aOldInputs, $oAdminPage, $aSubmitInfo */ ) { 
        
        $_aParams    = func_get_args() + array( null, null, null, null );
        $aInputs     = $_aParams[ 0 ];
        $aOldInputs  = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ];             
        
        $_bIsValid   = true;
        $_aErrors    = array();
        if ( ! isset( $aInputs[ 'exit_code' ] ) || '' == $aInputs[ 'exit_code' ] ) {

            // $aVariable[ 'section_id' ][ 'field_id' ]
            $_aErrors[ $this->_sSectionID ][ 'exit_code' ] = __( 'An exit code need to be set.', 'task-scheduler' );
            $_bIsValid = false;            
            
        } 
        if ( ! isset( $aInputs[ 'task_ids' ] ) || '' == $aInputs[ 'task_ids' ] ) {

            // $aVariable[ 'section_id' ][ 'field_id' ]
            $_aErrors[ $this->_sSectionID ][ 'task_ids' ] = __( 'Task IDs are required.', 'task-scheduler' );
            $_bIsValid = false;            
            
        } 
        
        if ( ! $_bIsValid ) {

            // Set the error array for the input fields.
            $oAdminPage->setFieldErrors( $_aErrors );        
            $oAdminPage->setSettingNotice( __( 'Please try again.', 'task-scheduler' ) );
            return array();
            
        }    
        
        unset( $aInputs[ 'prevnext' ] );
        
        // Now these options will be stored in the 'on_exit_code' meta key. However, the exit code event handler needs the meta data being saved in the top level
        // to perform the query and process the tasks with the 'on_exit_code' occurrence type.
        $this->_aSubmit = $aInputs;
        add_filter( "task_scheduler_admin_filter_saving_wizard_options", array( $this, '_replyToSetTopLevelMetaData' ), 10, 3 );
        
        return $aInputs; 
        
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
     * Sets the submitted form data in the top level of the wizard options so that they will be stored as the top level meta keys.
     */
    public function _replyToSetTopLevelMetaData( $aWizardOptions ) {

        remove_filter( "task_scheduler_admin_filter_saving_wizard_options", array( $this, '_replyToSetTopLevelMetaData' ), 10 );
        if ( empty( $this->_aSubmit ) ) {
            return $aWizardOptions;
        }

        $_aTaskIDs   = $this->___getSetTaskIDs( $this->_aSubmit[ 'task_ids' ] );
        // This is important for SQL queried. Format: {task id}|{task id}|{task id} e.g. 213|3932|6323
        $aWizardOptions[ '__on_exit_code_task_ids' ] = '|' . implode( '|', $_aTaskIDs ) . '|';
        $_aExitCodes = TaskScheduler_PluginUtility::getAsArray( preg_split( "/[,]\s*/", trim( $this->_aSubmit[ 'exit_code' ] ), 0, PREG_SPLIT_NO_EMPTY ) );
        $aWizardOptions[ '__on_exit_code' ]          = '|' . implode( '|', $_aExitCodes ) . '|';
        $aWizardOptions[ '__on_exit_code_negate' ]   = ( boolean ) $this->_aSubmit[ 'negate' ]; // this key must exist
        return $aWizardOptions;

    }
        /**
         * Returns an array of set task IDs.
         * 
         * The structure of the JSON array will look like
         * [{"id":405,"name":"3 Minutes Task"},{"id":407,"name":"My Task"}]
         * @return array
         */
        private function ___getSetTaskIDs( $sJSONTaskIDs ) {
            
            if ( ! $sJSONTaskIDs ) { 
                return array(); 
            }
            $_aJSONTaskIDs = TaskScheduler_PluginUtility::getAsArray( json_decode( $sJSONTaskIDs, true ) );
            $_aTaskIDs     = array();
            foreach( $_aJSONTaskIDs as $_aTaskID ) {
                if ( ! isset( $_aTaskID[ 'id' ] ) ) { 
                    continue; 
                }
                $_aTaskIDs[] = $_aTaskID[ 'id' ];
            }
            return $_aTaskIDs;
            
        }
    
}