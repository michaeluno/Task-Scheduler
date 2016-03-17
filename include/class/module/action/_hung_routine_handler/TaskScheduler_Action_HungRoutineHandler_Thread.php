<?php
/**
 * The class that defines the Hung Routine Handler action for the Task Scheduler plugin.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Creates a 'volatile' occurrence type thread with the 'system' and 'internal' labels that checks if the task is hung or not.
 * 
 * @action    add    task_scheduler_action_add_hung_routine_handler_thread    Called when a routine is about to be spawned.
 */
class TaskScheduler_Action_HungRoutineHandler_Thread extends TaskScheduler_Action_Base {
        
    /**
     * The user constructor.
     * 
     * This method is automatically called at the end of the class constructor.
     */
    public function construct() {        
        add_action( 
            'task_scheduler_action_add_hung_routine_handler_thread', 
            array( $this, '_replyToAddHungRoutineHandlerThread' ) 
       );
    }
        /**
         * This is called when a routine is going to be spawned.
         * 
         * @callback        action      task_scheduler_action_add_hung_routine_handler_thread
         */
        public function _replyToAddHungRoutineHandlerThread( $oRoutine ) {
            
            if ( ! $oRoutine->_max_execution_time ) {
                return;
            }
            
            $_aThreadOptions = array(
            
                '_next_run_time'        => 10 + microtime( true ) + ( int ) $oRoutine->_max_execution_time,
                'routine_action'        => $this->sSlug,
                'post_title'            => sprintf( __( 'Hung Routine Handler of "%1$s"', 'task-scheduler' ), $oRoutine->post_title ),
                'post_excerpt'          => sprintf( __( 'Do some clearance if the task "%1$s" is hung.', 'task-scheduler' ), $oRoutine->post_title ),
                '_max_root_log_count'   => 0,    // disable logs of the thread itself.
                'log_id'                => 0,    // do not inherit any parent log id 
                
                // The action specific meta data.
                '_owner_spawned_time'   => $oRoutine->_spawned_time,
                
            );    
            
            $_iThreadID = TaskScheduler_ThreadUtility::derive( 
                $oRoutine->ID, 
                $_aThreadOptions, 
                array( 'system', 'internal' ) 
            );
            
        }    
    
    /**
     * Returns the readable label of this action.
     * 
     * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
     */
    public function getLabel( $sLabel ) {
        return __( 'Handle Hung Task', 'task-scheduler' );
    }
    
    /**
     * Defines the behavior of the action.
     * 
     * @callback        filter      $this->sSlug
     */
    public function doAction( $isExitCode, $oThread ) {

        $_oRoutine    = TaskScheduler_Routine::getInstance( $oThread->owner_routine_id );

        // Check if the owner routine is hung
        if ( ! in_array( $_oRoutine->_routine_status, array( 'processing', 'awaiting' ) ) ) {
            // this thread will be deleted automatically as the occurrence type is 'volatile'
            return 1;    
        }        

        // If the owner spawned time is not same as the stored owner spawned time of the thread, it means another process has been started.
        if ( $oThread->_owner_spawned_time != $_oRoutine->_spawned_time ) {
            return 1;
        }

        // If the owner task has threads and still processing, it is not hung. 
        if ( $_oRoutine->hasThreads() && 'processing' == $_oRoutine->_routine_status ) {
            $oThread->setMeta( '_next_run_time', 10 + microtime( true ) + ( int ) $_oRoutine->_max_execution_time );
            $oThread->setMeta( '_routine_status', 'queued' );
            // passing 'NOT_DELETE' will prevent the thread from being deleted.
            return 'NOT_DELETE';    
        }
        
        // At this point, the task can be considered being hung.

        // Increment the hung count first. 
        $_oRoutine->setMeta( '_count_hung', ( int ) $_oRoutine->getMeta( '_count_hung' ) + 1 );
        $_oTask  = $_oRoutine->getOwner();
        $_oTask->setMeta( '_count_hung', ( int ) $_oTask->getMeta( '_count_hung' ) + 1 );        
        
        // Handle the hung routine.
        $_iHandleType = ( integer ) $_oRoutine->getMeta( '_hung_routine_handle_type' );
        if ( 1 === $_iHandleType ) {
            $_oRoutine->setMeta( '_routine_status', 'ready' );
        } else if ( 2 == $_iHandleType ){
            $_oRoutine->delete(); 
        }
        
        return 1;    // exit code.
                
    }    

}
