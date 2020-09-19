<?php
/**
 * Creates a meta box for advanced task options.
 * 
 * @since            1.0.0
 */
class TaskScheduler_MetaBox_Advanced extends TaskScheduler_MetaBox_Base {
                
    /**
     * Adds form fields for the basic options.
     * 
     */ 
    public function setUp() {

        $this->addSettingFields(
            array(
                'field_id'        => '_max_root_log_count',
                'title'           => __( 'Max Count of Log Entries', 'task-scheduler' ),
                'type'            => 'number',
                'attributes'      => array(
                    'min'    => 0,
                    'step'   => 1,
                ),
                'description'    => __( 'Set the maximum number of log items.', 'task-scheduler' )
                    . ' ' . __( 'Set 0 to disable it.', 'task-scheduler' ),
            ),
            array(
                'field_id'        => '_max_execution_time',
                'title'           => __( 'Max Task Execution Time', 'task-scheduler' ),
                'type'            => 'number',
                'after_label'     => ' ' .__( 'second(s)', 'task-scheduler' ),
                'description'     => __( 'Set the expected duration that the task will take to complete.', 'task-scheduler' ),
                'attributes'      => array(
                    'min'    => 0,
                    'step'   => 1,
                    'max'    => TaskScheduler_WPUtility::canUseIniSet() 
                        ? null
                        : TaskScheduler_WPUtility::getServerAllowedMaxExecutionTime( 30 ),
                ),                
            ),
            array(
                'field_id'        => '_force_execution',
                'title'           => __( 'Force Execution', 'task-scheduler' ),
                'type'            => 'checkbox',
                'label'           => __( 'Execute the action even when the last routine is not completed.', 'task-scheduler' ),
                'default'         => false,
            ),        
            array(
                'field_id'        => '_hung_routine_handle_type',
                'title'           => __( 'Handling Hung Routines', 'task-scheduler' ),
                'type'            => 'radio',
                'label'           => array(
                    0   => __( 'Do nothing.', 'task-scheduler' ),
                    1   => sprintf( 
                        __( 'Terminate the routine and change the status to <code>%1$s</code>.', 'task-scheduler' ),
                        __( 'Ready', 'task-scheduler' )
                    ),
                    2   => __( 'Terminate and remove the routine.', 'task-scheduler' ),
                ),
                'description'     => __( 'Choose which action you would like to take for hung routines which exceed the set execution time.', 'task-scheduler' ),
                'default'         => 0,
                'label_min_width'   => '100%',
            ),
            array()
        );    
    
    }
                
    /**
     * Validation methods
     * 
     * @callback        filter      validation_ + extended class name
     */
    public function validation_TaskScheduler_MetaBox_Advanced( /* $aInput, $aOldInput, $oAdminPage, $aSubmitInfo */ ) {

        $_aParams    = func_get_args() + array(
            null, null, null, null
        );
        $aInput      = $_aParams[ 0 ];
        $aOldInput   = $_aParams[ 1 ];
        $oAdminPage  = $_aParams[ 2 ];
        $aSubmitInfo = $_aParams[ 3 ]; 
    
        // Sanitize values
        if ( isset( $aInput['_max_root_log_count'] ) ) {
            $aInput['_max_root_log_count'] = $this->oUtil->fixNumber( 
                $aInput['_max_root_log_count'],
                TaskScheduler_Option::get( array( 'task_default', 'max_root_log_count' ) ),
                0
            );
        }
        if ( isset( $aInput['_max_execution_time'] ) ) {
            $aInput['_max_execution_time'] = $this->oUtil->fixNumber( 
                $aInput['_max_execution_time'],
                TaskScheduler_Option::get( array( 'task_default', 'max_execution_time' ) ),
                0
            );
        }        
        
        $this->___checkLogs( $aInput[ '_max_root_log_count' ] );
        
        return $aInput;
        
    }
        
        /**
         * Checks the routine logs.
         * @param integer $iMaxRootLogCount
         */
        private function ___checkLogs( $iMaxRootLogCount ) {

            if ( ! isset( $_REQUEST[ 'post_ID' ] ) ) {
                return; 
            }
            $_iTaskID    = $_REQUEST[ 'post_ID' ];
            $_oTask      = TaskScheduler_Routine::getInstance( $_iTaskID );
            if ( ! is_object( $_oTask ) ) { 
                return; 
            }
            
            // Check the number of logs and if exceeded, create a task to remove them.
            if ( $_oTask->getRootLogCount() > ( int ) $iMaxRootLogCount ) {
                TaskScheduler_PluginUtility::scheduleSingleWPCronTask( 'task_scheduler_action_delete_log_items_of_task', array( $_iTaskID ) );
            }                
            
        }
        
}
