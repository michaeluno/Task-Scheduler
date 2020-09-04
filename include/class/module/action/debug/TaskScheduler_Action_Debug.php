<?php
/**
 * The class that defines the Debug action for the Task Scheduler plugin.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

class TaskScheduler_Action_Debug extends TaskScheduler_Action_Base {

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
     * @param integer|string $isExitCode
     * @param TaskScheduler_Routine $oRoutine
     * @return  integer|string
     */
    public function doAction( $isExitCode, $oRoutine ) {

        TaskScheduler_Debug::log( 'Called from the Debug action.' );
        TaskScheduler_Debug::log( $oRoutine->getMeta() );
        return 1; // Exit Code

    }

}
