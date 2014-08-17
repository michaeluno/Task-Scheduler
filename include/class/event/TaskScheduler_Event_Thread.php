<?php
/**
 * Handles events for threads.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * 
 * @action    do        task_scheduler_action_before_doing_thread
 * @action    add        task_scheduler_action_after_doing_thread    Called when a thread finishes.
 */
class TaskScheduler_Event_Thread {
        
    /**
     * Sets up hooks and properties.
     */
    public function __construct() {

        add_action( 'task_scheduler_action_before_doing_thread', array( $this, '_replyToStartThread' ), 1 );    // set a higher priority as it sets a log    
        add_action( 'task_scheduler_action_after_doing_thread', array( $this, '_replyToCompleteThread' ), 10, 2 );
        
    }
    
    /**
     * Do some updates of task before staring it.
     */
    public function _replyToStartThread( $oTask ) {    }
    
    
    /**
     * Do clean-ups when a thread completes.
     */
    public function _replyToCompleteThread( $oThread, $isExitCode ) {
                
        if ( ! is_object( $oThread ) ) { return; }
        
        // Threads also can be constant, in that case the routine status needs to be reset. For volatile threads, they will be deleted anyway.
        $oThread->setMeta( '_routine_status', 'queued' );  // restore the default status.
        
        // The owner task may have been deleted especially if it is a system internal task.
        $_oRoutine = $oThread->getOwner();
        if ( ! is_object( $_oRoutine ) ) { return; }
                
        // For internal threads, do not add any log.
        if ( has_term( array( 'internal', ), TaskScheduler_Registry::Taxonomy_SystemLabel, $oThread->ID ) ) { return; }
                
        $_oRoutine->log( sprintf( __( 'Finished the thread: %1$s', 'task-scheduler' ), $oThread->ID ), $oThread->parent_routine_log_id );
        
        // If 'NOT_DELETE' is passed to the exit code, the action wants to cancel the deletion of the thread, which means the thread will be loaded again.
        // It is based on the premise that all the threads are the 'volatile' occurrence type.
        if ( 'NOT_DELETE' === $isExitCode ) {
            return;
        }
        
        // If no thread is found besides this thread instance, it means this is the last thread.
        if ( 1 < $_oRoutine->getThreadCount() ) { return; }        
        
// @TODO: make sure if this is necessary and if it should be done to the routine object and task object.
$oThread->deleteMeta( '_spawned_time' );

        // Now update the owner task.
        $_oTask = $_oRoutine->getOwner();
        $_oTask->setMeta( '_exit_code',     $isExitCode );
        $_oTask->setMeta( '_count_exit',    $_oTask->_count_exit + 1 );
        $_oTask->log( 
            __( 'Completed all the threads.', 'task-scheduler' )
            . ' ' . sprintf( __( 'Exit Code: %1$s', 'task-scheduler' ), $isExitCode ),
            $_oRoutine->parent_routine_log_id 
        );
        
        do_action( 'task_scheduler_action_exit_routine', $_oRoutine, $isExitCode );
        $_oRoutine->delete();
        
    }
    
}