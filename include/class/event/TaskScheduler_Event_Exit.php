<?php
/**
 * Handles events for routine exits.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * 
 * @action    add    task_scheduler_action_after_doing_action
 * @action    add    task_scheduler_action_exit_routine         Triggered when all the threads finish and exit the routine.
 */
class TaskScheduler_Event_Exit {
        
    public function __construct() {
        
        add_action( 'task_scheduler_action_after_doing_action', array( $this, '_replyToHandleRoutineExits' ), 100, 2 );
        add_action( 'task_scheduler_action_after_doing_action', array( $this, '_replyToHandleThreadExits' ), 100, 2 );
        add_action( 'task_scheduler_action_exit_routine', array( $this, '_replyToHandleRoutineExits' ), 100, 2 );
        
    }

    /**
     * Called when a thread exits.
     */
    public function _replyToHandleThreadExits( $oThread, $isExitCode ) {
        
        if ( ! is_object( $oThread ) ) { return; }
        
        // For internal threads, do not add any log.
        if ( $oThread->hasTerm( 'internal' ) ) { return; }
        
        // Do what the exit code tells if it is one of the pre-defined ones.
        $this->_doExitCode( $isExitCode, $oThread );
        
    }
    
    /**
     * Called when a task exits.
     * 
     * Retrieves tasks registered with the 'Exit Code' occurrence type and spawns the task that matches the criteria.
     */
    public function _replyToHandleRoutineExits( $oRoutine, $isExitCode ) {

        if ( ! $oRoutine->isRoutine() ) { return; }

        $this->_doExitCode( $isExitCode, $oRoutine );
        
        if ( $oRoutine->hasTerm( 'internal' ) ) { return; }
                    
        $this->_doTasksOnExitCode( $isExitCode, $oRoutine );
        
    }

    /**
     * Performs pre-defined exit code commands.
     * 
     * Currently there is only the 'DELETE' command.
     * 
     */
    private function _doExitCode( $isExitCode, $oRoutine ) {
        
        if ( 'DELETE' === $isExitCode ) {
            $oRoutine->delete();    
        }
        
    }    
    
    /**
     * Performs the tasks registered for the given exit code.
     */
    private function _doTasksOnExitCode( $isExitCode, $oRoutine ) {
        
        $_oTask     = $oRoutine->getOwner();
        if ( ! is_object( $_oTask ) ) { 
            return;             
        }        

        $_aFoundTasks = $this->_getTasksOnExitCode( $isExitCode, $_oTask->ID );
            
        foreach( $_aFoundTasks as $_iTaskID ) {
            do_action( 
                'task_scheduler_action_spawn_routine', 
                $_iTaskID, 
                microtime( true ),  // scheduled time - current time
                false   // whether to update next run time
            );
        }        
        
    }
        private function _getTasksOnExitCode( $isExitCode, $iSubjectRoutineID ) {

            $_oResult = TaskScheduler_TaskUtility::find(
                array(
                    'post__not_in'  => array( $iSubjectRoutineID ),
                    'meta_query'    => array(
                        array(
                            'key'        => 'occurrence',
                            'value'      => 'on_exit_code',
                        ),                  
                        array(              
                            'key'        => '__on_exit_code',
                            'value'      => $isExitCode,
                        ),
                        // It is saved like this 'a:1:{i:0;i:405;}'
                        array(
                            'key'        => '__on_exit_code_task_ids',
                            'value'      => ':' . $iSubjectRoutineID . ';',    // searches the value of a serialized array 
                            'compare'    => 'LIKE',
                        ),                        
                    )
                )
            );
            return $_oResult->posts;
            
        }
    
}
