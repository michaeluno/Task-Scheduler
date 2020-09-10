<?php
/**
 * Handles events for routines(tasks and threads).
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * 
 * @remark    The term routine is used to refer to the both 'thread' and 'task'. 
 * @action    add       task_scheduler_action_before_calling_routine             Called when a routine is about to be called.
 * @action    add       task_scheduler_action_cancel_routine                     Called when a routine is canceled.
 * @action    add       task_scheduler_action_before_doing_routine               Called before a routine action gets triggered.
 * @action    add       task_scheduler_action_do_routine                         Called when a routine action needs to be performed.
 * @action    add|do    task_scheduler_action_after_doing_action                 Executes right after a routien aciton is performed.
 * @action    add       task_scheduler_action_after_doing_routine                Called after a routine action is performed.
 * @action    do        task_scheduler_action_after_doing_{task_or_thread}       Executes when a routine is finished.
 * @action    do        task_scheduler_action_after_doing_routine_of_occurrence_{occurrence slug}        Executed when a task is finished.
 */
class TaskScheduler_Event_Routine {
        
    /**
     * Sets up hooks.
     */
    public function __construct() {

        add_action( 'task_scheduler_action_before_calling_routine', array( $this, '_replyToDoBeforeSpawnRoutine' ), 10, 2 );
        add_action( 'task_scheduler_action_cancel_routine',         array( $this, '_replyToCancelRoutine' ) );
        add_action( 'task_scheduler_action_before_doing_routine',   array( $this, '_replyToDoBeforeRoutine' ) );
        add_action( 'task_scheduler_action_do_routine',             array( $this, '_replyToDoRoutine' ) );
        add_action( 'task_scheduler_action_after_doing_action',     array( $this, '_replyToDoAfterRoutineAction' ), 10, 2 );
        add_action( 'task_scheduler_action_after_doing_routine',    array( $this, '_replyToCompleteRoutine' ) );
        
    }

    /**
     * Called when the task is about to be spawned.
     * @callback    action      task_scheduler_action_before_calling_routine
     * @return      void
     */
    public function _replyToDoBeforeSpawnRoutine( $oRoutine, $nSpawnedTime ) {
        
        if ( ! is_object( $oRoutine ) ) { 
            return;
        }
        $oRoutine->deleteMeta( '_exit_code' );
        $_sPreviousTaskStatus     = $oRoutine->_routine_status;
        $_iMaxTaskExecutionTime   = ( integer ) $oRoutine->_max_execution_time;
        
        // Store the previous task status in a transient. This is used to cancel the routine.
        $_sLoadID = TaskScheduler_Registry::TRANSIENT_PREFIX . md5( $nSpawnedTime );
        TaskScheduler_WPUtility::setTransient( $_sLoadID, $_sPreviousTaskStatus, $_iMaxTaskExecutionTime ? $_iMaxTaskExecutionTime : 30 );    // avoid setting 0 for the expiration duration.
        
        $oRoutine->setMeta( '_routine_status', 'awaiting' );     // the 'Force Execution' task option will ignore this status if enabled. Otherwise, it is used to determine scheduled routines.
        $oRoutine->setMeta( '_is_spawned', true );               // used to determine scheduled routines
        $oRoutine->setMeta( '_spawned_time', $nSpawnedTime );    // used to cancel the routine and to detect the hung
        $oRoutine->setMeta( '_count_call', ( ( integer ) $oRoutine->getMeta( '_count_call' ) ) + 1 );

        if ( $oRoutine->isRoutine() && $oRoutine->getMeta( '_hung_routine_handle_type' ) ) {
            do_action( 'task_scheduler_action_add_hung_routine_handler_thread', $oRoutine );
        }
        
    }
    
    /**
     * Gets triggered when a routine is cancelled.
     */
    public function _replyToCancelRoutine( $oRoutine ) {
    
        // Check the previous task status.
        $_nSpawnedMicrotime      = $oRoutine->getMeta( '_spawned_time' );
        $_sTransientKey          = TaskScheduler_Registry::TRANSIENT_PREFIX . md5( $_nSpawnedMicrotime );
        $_sPreviousTaskStatus    = TaskScheduler_WPUtility::getTransient( $_sTransientKey );
        if ( $_sPreviousTaskStatus ) {
            $oRoutine->setMeta( '_routine_status', $_sPreviousTaskStatus );
        }
        $oRoutine->deleteMeta( '_is_spawned' );
        TaskScheduler_WPUtility::deleteTransient( $_sTransientKey );
        
    }
    
    /**
     * Do some preparation before stating the routine.
     */
    public function _replyToDoBeforeRoutine( $oRoutine ) {

        $oRoutine->setMeta( '_routine_status', 'processing' );
        $oRoutine->setMeta( '_last_run_time', microtime( true ) );
        $oRoutine->deleteMeta( '_is_spawned' );    
    
    }

    /**
     * Executes the action of the task.
     * 
     * @remark      For convenience, the action of the task is called 'action' to imply it performs an action that needs for the task.
     * However, technically speaking, it is performed as a WordPress filter to get the exit code to be returned.
     * 
     * @callback    action      task_scheduler_action_do_routine
     * @return      void
     */
    public function _replyToDoRoutine( $oRoutine ) {

        $_bIsThread = $oRoutine->isThread();
        $_iLogID    = $oRoutine->log( $this->_getLogText( $oRoutine ), 0, true ); 
        
        if ( $_bIsThread ) {
            do_action( "task_scheduler_action_before_doing_thread" , $oRoutine );
        }

        // Do the action and get the return code - note that _exit_code is not updated yet in the db here. The update will be taken cared of in the callback of the following hook.
        $oRoutine->_exit_code = apply_filters( 
            $oRoutine->routine_action,     // the action name
            null,         // the 1st argument: the exit code - null is given 
            $oRoutine     // the 2nd argument: the routine object
        );

        do_action( "task_scheduler_action_after_doing_action", $oRoutine, $oRoutine->_exit_code );
        
        // Do after-treatment - note that the exit code matters and changes the behavior of the callback functions of the followings.
        if ( $_bIsThread ) {
            do_action( "task_scheduler_action_after_doing_thread", $oRoutine, $oRoutine->_exit_code );
        }

        do_action( "task_scheduler_action_after_doing_routine_of_occurrence_{$oRoutine->occurrence}", $oRoutine, $oRoutine->_exit_code );    // for the Volatile occurrence type    
                        
    }    
        /**
         * Returns log text to indicate the routine is starting.
         */    
        private function _getLogText( $oRoutine ) {
            
            $_aLogs = array();
            $_aLogs[] = $oRoutine->isThread() 
                ? __( 'Starting the thread.', 'task-scheduler' ) 
                : __( 'Starting the routine.', 'task-scheduler' );
            $_aLogs[] = __( 'ID', 'task-scheduler' ) . ': ' . $oRoutine->ID;
            $_aLogs[] = __( 'Action', 'task-scheduler' ) . ': ' . apply_filters( "task_scheduler_filter_label_action_{$oRoutine->routine_action}", $oRoutine->routine_action );
            return $_aLogs;
        
        }
    
    /**
     * Updates the routine status and meta data and leave log items for the routine.
     * 
     * This method gets triggered right after the routine action is performed.
     *
     * @param TaskScheduler_Routine $oRoutine
     * @param integer|string    $sExitCode
     */
    public function _replyToDoAfterRoutineAction( $oRoutine, $sExitCode ) {

        if ( ! is_object( $oRoutine ) || ! $oRoutine->isRoutine() ) {
            return;
        }
        
        // At this point, the passed routine is not 'thread' and it is the 'routine' type.
        $_aLog          = array();
        $_bHasThreads   = $oRoutine->hasThreads();
        $_bIsInternal   = $oRoutine->hasTerm( 'internal' );        
        $_oTask         = TaskScheduler_Routine::getInstance( $oRoutine->owner_task_id );
        if ( ! is_object( $_oTask ) ) {
            return;
        }
        
        // Regardless of the occurrence type, if there are threads, keeps the status to be 'processing', leave the log and go back.
        if ( $_bHasThreads ) {
            $_aLog[] = __( 'Still have threads.', 'task-scheduler' );
            $_aLog[] = null !== $sExitCode ? ' ' . __( 'Exit Code', 'task-scheduler' ) . ': ' . $sExitCode : '';
            $oRoutine->log( $_aLog, $oRoutine->parent_routine_log_id );
            return;
        }
        
        // If there are no more threads, update the owner task status and meta data.
        if ( null !== $sExitCode ) {
            
            // Update the routine status.
            $oRoutine->setMeta( '_count_run',     ( int ) $_oTask->_count_run + 1 );
            $oRoutine->setMeta( '_routine_status', 'queued' );  // restore to the default status
            
            // Update the task status.
            if ( ! $_bIsInternal ) {
                $_oTask->setMeta( '_exit_code',     $sExitCode );
                $_oTask->setMeta( '_count_exit',    ( int ) $_oTask->getMeta( '_count_exit' ) + 1 );
                $_oTask->setMeta( '_count_run',     ( int ) $_oTask->_count_run + 1 );
            }
            
        }            
         
        // Leave the log item.
        $_aLog[] = null !== $sExitCode
            ? __( 'Completed the task.', 'task-scheduler' ) . ' ' . __( 'Exit Code', 'task-scheduler' ) . ': ' . $sExitCode
            : __( 'The action did not return an exit code.', 'task-scheduler' );
        $oRoutine->log( $_aLog, $oRoutine->parent_routine_log_id );
        
        // Check the number of logs and if exceeded, create a task to remove them.
        if ( ! $oRoutine->hasTerm( 'delete_log' ) && $_oTask->getRootLogCount() > ( int ) $_oTask->_max_root_log_count ) {
            do_action( 'task_scheduler_action_add_log_deletion_task', $_oTask );
        }
        
        // If the next scheduled time is very close, check the actions in the background.
        $_iHeartbeatInterval    = ( int ) TaskScheduler_Option::get( array( 'server_heartbeat', 'interval' ) );
        $_nSum                  = microtime( true ) + $_iHeartbeatInterval;
        if ( $_nSum > $_oTask->_next_run_time ) {
            do_action( 'task_scheduler_action_check_scheduled_actions' );
        }    
    
    }

    /**
     * Deals with cleaning up the routine.
     */
    public function _replyToCompleteRoutine( $oRoutine ) {

        $oRoutine->setMeta( '_count_run',    $oRoutine->_count_run + 1 );
                
        // Clean the previous status transient
        $_nSpawnedMicrotime    = $oRoutine->getMeta( '_spawned_time' );
        $_sTransientKey        = TaskScheduler_Registry::TRANSIENT_PREFIX . md5( $_nSpawnedMicrotime );        
        TaskScheduler_WPUtility::deleteTransient( $_sTransientKey );
                
    }
    
}