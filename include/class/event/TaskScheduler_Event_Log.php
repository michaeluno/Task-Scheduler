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
     * @remark  Triggered when a post gets deleted with the 'before_delete_post' hook.
     * @param   integer $iPostID
     */
    public function _replyToDeleteLog( $iPostID ) {

        if ( ! in_array( get_post_type( $iPostID ), array( TaskScheduler_Registry::$aPostTypes[ 'task' ], TaskScheduler_Registry::$aPostTypes[ 'routine' ] ), true ) ) {
            return;
        }

        // If the task itself is the delete log task, do not create the same task again.
        if ( has_term( array( 'delete_log' ), TaskScheduler_Registry::$aTaxonomies[ 'system' ], $iPostID ) ) {
            return;
        }

        $_oProcess = TaskScheduler_Routine::getInstance( $iPostID );
        if ( $_oProcess->isTask() ) {
            $this->___doForTask( $iPostID );
        }

        // At this point, it is the routine type
        $this->___doForRoutine( $_oProcess );
        
    }
        /**
         * @param TaskScheduler_Routine $oRoutine
         */
        private function ___doForRoutine( $oRoutine ) {

            if ( $oRoutine->hasTerm( 'delete_log' ) ) {
                return;
            }
            $_oTask = $oRoutine->getOwner();
            if ( $_oTask->getRootLogCount() > ( int ) $_oTask->_max_root_log_count ) {
                do_action( 'task_scheduler_action_add_log_deletion_task', $_oTask );
            }
        }

        private function ___doForTask( $iPostID ) {
            // if the task does not have any log, do not create the log deletion task.
            if ( ! TaskScheduler_LogUtility::getLogCount( $iPostID ) ) {
                return;
            }
            $this->___addLogDeleteTask( $iPostID, 0 );    // 0: delete all log entries.
        }
    
    /**
     *
     * @callback    add_action  task_scheduler_action_add_log_deletion_task
     * @param   integer|TaskScheduler_Routine   $ioTask
     */
    public function _replyToAddLogDeletionTask( $ioTask ) {
        
        $_oTask = is_object( $ioTask ) ? $ioTask : TaskScheduler_Routine::getInstance( $ioTask );
        if ( ! is_object( $_oTask ) ) {
            return;
        }
        $this->___addLogDeleteTask( $_oTask->ID, $_oTask->_max_root_log_count );
        
    }
        /**
         * Adds a system internal task that deletes logs.
         *
         * @param integer   $iTargetTaskID
         * @param integer   $iMaxRootLogCountOfTheSubjectTask
         * @return void
         */
        private function ___addLogDeleteTask( $iTargetTaskID, $iMaxRootLogCountOfTheSubjectTask=0 ) {

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
                do_action( 'task_scheduler_action_check_scheduled_actions' );
            }        

        } 
    
}
