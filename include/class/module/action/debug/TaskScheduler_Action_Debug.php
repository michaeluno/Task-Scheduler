<?php
/**
 * The class that defines the Debug action for the Task Scheduler plugin.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

class TaskScheduler_Action_Debug extends TaskScheduler_Action_Base {

    protected $sSlug = 'task_scheduler_action_debug';

    /**
     * The user constructor.
     * 
     * This method is automatically called at the end of the class constructor.
     */
    public function construct() {}
        
    /**
     * Returns the readable label of this action.
     * 
     * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
     */
    public function getLabel( $sLabel ) {
        return __( 'Debug', 'task-scheduler' );
    }
    
    /**
     * Returns the description of the module.
     */
    public function getDescription( $sDescription ) {
        return __( 'Creates a log file in the wp-content folder.', 'task-scheduler' );
    }
    
    /**
     * Defines the behavior of the action.
     *
     * @param  integer|string        $isExitCode
     * @param  TaskScheduler_Routine $oRoutine
     * @return integer
     */
    public function doAction( $isExitCode, $oRoutine ) {

        $_aMeta = $oRoutine->getMeta();
        TaskScheduler_Debug::log( 'Called from the Debug action.', 'debug_action.' . $_aMeta[ 'owner_task_id' ] );
        TaskScheduler_Debug::log(
            [
                'Now'           => $this->getSiteReadableDate( time(), 'Y-m-d G:i:s', true ),
                'Spawned Time'  => $this->getSiteReadableDate( $_aMeta[ '_spawned_time' ], 'Y-m-d G:i:s', true ),
                'Last Run Time' => $this->getSiteReadableDate( isset( $_aMeta[ '_last_run_time' ] ) ? $_aMeta[ '_last_run_time' ] : 0, null, true ),
                'Next Run Time' => $this->getSiteReadableDate( $_aMeta[ '_next_run_time' ], 'Y-m-d G:i:s', true ),
            ] + $_aMeta,
            'task_scheduler_action_debug_' . $_aMeta[ 'owner_task_id' ]
        );
        return 1; // Exit Code

    }

}