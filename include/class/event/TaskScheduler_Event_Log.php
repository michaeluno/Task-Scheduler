<?php
/**
 * Handles events for routine logs.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * 
 * @action    add    before_delete_post                            Triggered when a post is about to be deleted.
 * @action    add    task_scheduler_action_add_log_deletion_task    
 */
class TaskScheduler_Event_Log {
        
    /**
     * Sets up hooks and properties.
     */
    public function __construct() {
        
        add_action( 'before_delete_post', array( $this, '_replyToDeleteLog' ) );
        add_action( 'task_scheduler_action_add_log_deletion_task', array( $this, '_replyToAddLogDeletionTask' ) );
        
    }
        
    /**
     * Creates a system internal task that deletes all the logs associated with the task.
     * 
     * @remark    Triggered when a post gets deleted with the 'before_delete_post' hook.
     */
    public function _replyToDeleteLog( $iPostID ) {
        
        if ( TaskScheduler_Registry::PostType_Task != get_post_type( $iPostID ) ) { return; }        
        
        // If the task itself is the delete log task, do not create the same task again.
        if ( has_term( array( 'delete_log' ), TaskScheduler_Registry::Taxonomy_SystemLabel, $iPostID ) ) { return; }
        
        // if the task does not have any log, do not create the log deletion task.
        if ( ! TaskScheduler_LogUtility::getLogCount( $iPostID ) ) { return; }
        
        $this->_addLogDeleteTask( $iPostID, 0 );    // 0: delete all log entries.
        
    }
    
    /**
     * 
     */
    public function _replyToAddLogDeletionTask( $ioTask ) {
        
        $_oTask = is_object( $ioTask ) ? $ioTask : TaskScheduler_Routine::getInstance( $ioTask );
        if ( is_object( $_oTask ) ) {
            $this->_addLogDeleteTask( $_oTask->ID, $_oTask->_max_root_log_count );            
        }
        
    }
    
        /**
         * Adds a system internal task that deletes logs.
         */
        private function _addLogDeleteTask( $iTargetTaskID, $iMaxRootLogCountOfTheSubjectTask=0 ) {

            // Create a task that deletes the logs of the task
            $_iRoutineID = TaskScheduler_RoutineUtility::derive( 
                $iTargetTaskID,
                array(
                    'post_title'                        => sprintf( __( 'Delete Logs of %1$s' ), $iTargetTaskID ),
                    'post_excerpt'                      => sprintf( __( 'Deletes the logs of the task %1$s. This task will be gone when unnecessary logs are deleted.' ), $iTargetTaskID ),
                    'routine_action'                    => 'task_scheduler_action_delete_task_log',    // string    the target action hook name
                    'occurrence'                        => 'constant',    // string    The slug of the occurrence type.
                    '_max_root_log_count'               => 0,    // disable the log
                    '_target_task_id'                   => $iTargetTaskID,
                    '_max_root_log_count_of_the_target' => $iMaxRootLogCountOfTheSubjectTask,
                ),
                array( 'system', 'internal', 'delete_log' )    // taxonomy terms.
            );
            if ( $_iRoutineID ) {
                $_oRoutine = TaskScheduler_Routine::getInstance( $_iRoutineID );
                $_oRoutine->setNextRunTime();
                do_action( 'task_scheduler_action_check_shceduled_actions' );
            }        

        } 
    
}
