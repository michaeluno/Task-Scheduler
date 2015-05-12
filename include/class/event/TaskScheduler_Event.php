<?php
/**
 * Handles plugin events.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

class TaskScheduler_Event {
        
    public function __construct() {
        
        $this->_loadOccurrenceModules();
        $this->_loadActionModules();
        $this->_loadRoutines();
        $this->_loadServerHeartbeat();
        
    }
 
        /**
         * Loads occurrence modules.
         * @since       1.0.1
         */
        private function _loadOccurrenceModules() {
            
            new TaskScheduler_Occurrence_FixedInterval( 
                'fixed_interval', 
                'TaskScheduler_Occurrence_FixedInterval_Wizard'
            );
            new TaskScheduler_Occurrence_SpecificTime( 
                'specific_time', 
                'TaskScheduler_Occurrence_SpecificTime_Wizard' 
            );
            new TaskScheduler_Occurrence_Daily( 
                'daily', 
                'TaskScheduler_Occurrence_Daily_Wizard'
            );
            new TaskScheduler_Occurrence_ExitCode( 
                'on_exit_code', 
                'TaskScheduler_Occurrence_ExitCode_Wizard'
            );
            new TaskScheduler_Occurrence_Volatile( 
                'volatile',
                array() // internal, no wizard
            );
            new TaskScheduler_Occurrence_Constant( 
                'constant',
                array() // internal, no wizard
            );
            
        }
        /**
         * Loads action modules.
         * @since       1.0.1
         */
        private function _loadActionModules() {
            
            new TaskScheduler_Action_PostDeleter( 
                'task_scheduler_action_delete_post', 
                array(
                    'TaskScheduler_Action_PostDeleter_Wizard',
                    'TaskScheduler_Action_PostDeleter_Wizard_2',
                    'TaskScheduler_Action_PostDeleter_Wizard_3'
                ) 
            );
            new TaskScheduler_Action_Debug( 'task_scheduler_action_debug' );
            new TaskScheduler_Action_Email( 
                'task_scheduler_action_email', 
                'TaskScheduler_Action_Email_Wizard' 
            );
            new TaskScheduler_Action_RoutineLogDeleter( 
                'task_scheduler_action_delete_task_log', 
                array()     // internal, no wizard
            );
            
        }
        
        /**
         * Routines (tasks and threads)
         * @since       1.0.1
         */
        private function _loadRoutines() {
            new TaskScheduler_Event_Routine;
            new TaskScheduler_Event_Thread;
            new TaskScheduler_Event_Log;
            new TaskScheduler_Event_Exit;            
        }
        
        /**
         * Server heartbeat - the checker and the loader classes need to be loaded after calling the pulsate() method.
         * 
         * This is for when the user disables the server heartbeat.
         * `pulsate()` will terminate the page load so that actions won't be triggered.
         * 
         * @since       1.0.1
         */
        private function _loadServerHeartbeat() {
            TaskScheduler_ServerHeartbeat::pulsate();
            new TaskScheduler_Event_ServerHeartbeat_Option;
            new TaskScheduler_Event_ServerHeartbeat_Checker;
            new TaskScheduler_Event_ServerHeartbeat_Loader;
            new TaskScheduler_Event_ServerHeartbeat_Resumer;
        }
        
}
