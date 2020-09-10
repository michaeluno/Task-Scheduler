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
     * @param TaskScheduler_Routine $oThread
     * @param integer|string $isExitCode
     * @callback    add_action  task_scheduler_action_after_doing_action
     * @return void
     */
    public function _replyToHandleThreadExits( $oThread, $isExitCode ) {
        
        if ( ! is_object( $oThread ) ) {
            return;
        }
        
        // For internal threads, do not add any log.
        if ( $oThread->hasTerm( 'internal' ) ) {
            return;
        }
        
        // Do what the exit code tells if it is one of the pre-defined ones.
        $this->___doOnExit( $isExitCode, $oThread );
        
    }
    
    /**
     * Called when a task exits.
     * 
     * Retrieves tasks registered with the 'Exit Code' occurrence type and spawns the task that matches the criteria.
     *
     * @param TaskScheduler_Routine $oRoutine
     * @param integer|string $isExitCode
     * @callback    add_action  task_scheduler_action_after_doing_action
     * @return void
     */
    public function _replyToHandleRoutineExits( $oRoutine, $isExitCode ) {

        if ( ! $oRoutine->isRoutine() ) {
            return;
        }

        $this->___doOnExit( $isExitCode, $oRoutine );
        
        if ( $oRoutine->hasTerm( 'internal' ) ) {
            return;
        }
                    
        $this->___doTasksOnExitCode( $isExitCode, $oRoutine );
        
    }

    /**
     * Performs pre-defined exit code commands.
     * 
     * Currently there is only the 'DELETE' command.
     *
     * @param integer|string $isExitCode
     * @param TaskScheduler_Routine $oRoutine
     */
    private function ___doOnExit( $isExitCode, $oRoutine ) {
        
        if ( 'DELETE' === $isExitCode ) {
            $oRoutine->delete();    
        }
        
    }    
    
    /**
     * Performs the tasks registered for the given exit code.
     * @param integer|string $isExitCode
     * @param TaskScheduler_Routine $oRoutine
     */
    private function ___doTasksOnExitCode( $isExitCode, $oRoutine ) {
        
        $_oTask     = $oRoutine->getOwner();
        if ( ! is_object( $_oTask ) ) { 
            return;             
        }

        $_aFoundTasks = $this->___getTasksOnExitCode( $isExitCode, $_oTask->ID );
        foreach( $_aFoundTasks as $_iTaskID ) {
            do_action( 
                'task_scheduler_action_spawn_routine', 
                $_iTaskID, 
                microtime( true ),  // scheduled time - current time
                false,   // whether to update next run time
                false
            );
        }        
        
    }

        /**
         * @remark  Including `OR` and with deeply nested items, the query takes very long so separate it
         * @param   $isExitCode
         * @param   $iSubjectTaskID
         *
         * @return array
         */
        private function ___getTasksOnExitCode( $isExitCode, $iSubjectTaskID ) {

            $_aWP38CompatValue = version_compare( $GLOBALS[ 'wp_version' ], '3.9', '>=' )
                ? array()
                : array(
                    'value' => 'WHATEVER_VALUE_FOR_WP38_OR_BELOW'
                );
            $_oResult = TaskScheduler_TaskUtility::find(
                array(
                    'post__not_in'  => array( $iSubjectTaskID ),
                    'meta_query'    => array(
                        'relation'  => 'AND',
                        array(
                            'key'        => 'occurrence',
                            'value'      => 'on_exit_code',
                        ),
                        array(
                            // Searches the value of a serialized array. It is saved like this 'a:1:{i:0;i:405;}'
                            'key'        => '__on_exit_code_task_ids',
                            'value'      => ':' . $iSubjectTaskID . ';',
                            'compare'    => 'LIKE',
                        ),
                        array(
                            'key'        => '__on_exit_code',
                            'value'      => $isExitCode,
                        ),
                    ),
                )
            );
            $_aPosts  = $_oResult->posts;
            $_oResult = TaskScheduler_TaskUtility::find(
                array(
                    'post__not_in'  => array( $iSubjectTaskID ),
                    'meta_query'    => array(
                        'relation'  => 'AND',
                        array(
                            'key'        => 'occurrence',
                            'value'      => 'on_exit_code',
                        ),
                        array(
                            'key'        => '__on_exit_code_task_ids',
                            'value'      => '|' . $iSubjectTaskID . '|',
                            'compare'    => 'LIKE',
                        ),
                        array(
                            'relation'  => 'OR',
                            array(
                                'relation'  => 'AND',
                                array(
                                    // Searches the value of a serialized array. It is saved like this 'a:1:{i:0;i:405;}'
                                    'key'        => '__on_exit_code',
                                    'value'      => '|' . $isExitCode . '|',
                                    'compare'    => 'LIKE',
                                ),
                                array(
                                    'key'        => '__on_exit_code_negate',
                                    'value'      => false,
                                ),
                            ),
                            array(
                                'relation'  => 'AND',
                                array(
                                    // Searches the value of a serialized array. It is saved like this 'a:1:{i:0;i:405;}'
                                    'key'        => '__on_exit_code',
                                    'value'      => '|' . $isExitCode . '|',
                                    'compare'    => 'NOT LIKE',
                                ),
                                array(
                                    'key'        => '__on_exit_code_negate',
                                    'value'      => true,
                                ),
                            ),
                        ),
                    ), // 'meta_query'
                ) // find() 1st param
            ); // TaskScheduler_TaskUtility::find()
            return array_unique( array_merge( $_aPosts, $_oResult->posts ) );

/*          Somehow this is too heavy
            $_oResult = TaskScheduler_TaskUtility::find(
                array(
                    'post__not_in'  => array( $iSubjectTaskID ),
                    'meta_query'    => array(
                        'relation'  => 'AND',
                        array(
                            'key'        => 'occurrence',
                            'value'      => 'on_exit_code',
                        ),
                        array(
                            'relation'  => 'OR',
                            // 1.5.0 or below
                            array(
                                'relation'  => 'AND',
                                array(
                                    // Searches the value of a serialized array. It is saved like this 'a:1:{i:0;i:405;}'
                                    'key'        => '__on_exit_code_task_ids',
                                    'value'      => ':' . $iSubjectTaskID . ';',
                                    'compare'    => 'LIKE',
                                ),
                                array(
                                    'key'        => '__on_exit_code',
                                    'value'      => $isExitCode,
                                    'compare'    => '=',
                                ),
                            ),
                            // 1.5.0 or above
                            array(
                                'relation'  => 'AND',
                                array(
                                    'key'        => '__on_exit_code_task_ids',
                                    'value'      => '|' . $iSubjectTaskID . '|',
                                    'compare'    => 'LIKE',
                                ),
//                                array(
//                                    'key'        => '__on_exit_code_negate',
//                                    'compare'    => 'EXISTS'
//                                ) + $_aWP38CompatValue,
                                array(
                                    'relation'  => 'OR',
                                    array(
                                        'relation'  => 'AND',
                                        array(
                                            // Searches the value of a serialized array. It is saved like this 'a:1:{i:0;i:405;}'
                                            'key'        => '__on_exit_code',
                                            'value'      => '|' . $isExitCode . '|',
                                            'compare'    => 'LIKE',
                                        ),
                                        array(
                                            'key'        => '__on_exit_code_negate',
                                            'value'      => false,
                                        ),
                                    ),
                                    array(
                                        'relation'  => 'AND',
                                        array(
                                            // Searches the value of a serialized array. It is saved like this 'a:1:{i:0;i:405;}'
                                            'key'        => '__on_exit_code',
                                            'value'      => '|' . $isExitCode . '|',
                                            'compare'    => 'NOT LIKE',
                                        ),
                                        array(
                                            'key'        => '__on_exit_code_negate',
                                            'value'      => true,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ), // 'meta_query'
                ) // find() 1st param
            ); // TaskScheduler_TaskUtility::find()
*/

            return $_oResult->posts;

        }

}