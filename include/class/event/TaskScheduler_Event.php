<?php
/**
 * Handles plugin events.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

class TaskScheduler_Event {
        
    public function __construct() {
        
        $this->___loadEvents();
        $this->___loadOccurrenceModules();
        $this->___loadActionModules();
        $this->___loadRoutines();
        $this->___loadServerHeartbeat();
        
    }

        /**
         * Loads generic plugin events.
         * @since   1.5.0
         */
        private function ___loadEvents() {
            new TaskScheduler_Event_Action_DeleteThreads;
            new TaskScheduler_Event_Action_DeleteRoutines;
            new TaskScheduler_Event_Action_DeleteLogItems;
        }

        /**
         * Loads occurrence modules.
         * @since       1.0.1
         */
        private function ___loadOccurrenceModules() {
            
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
        private function ___loadActionModules() {
            
            new TaskScheduler_Action_PostDeleter( 
                'task_scheduler_action_delete_post', 
                array(
                    'TaskScheduler_Action_PostDeleter_Wizard',
                    'TaskScheduler_Action_PostDeleter_Wizard_2',
                    'TaskScheduler_Action_PostDeleter_Wizard_3'
                ) 
            );
            new TaskScheduler_Action_Debug;
            new TaskScheduler_Action_Email( '', 'TaskScheduler_Action_Email_Wizard' ); // wizard class name

            // 1.1.0+
            new TaskScheduler_Action_TransientCleaner(
                'task_scheduler_action_clean_transients',
                'TaskScheduler_Action_TransientCleaner_Wizard' // wizard class name
            );
            
            // 1.1.0+ Revived
            new TaskScheduler_Action_HungRoutineHandler_Thread( '', array() );     // internal, no wizard

            // 1.3.0+
            new TaskScheduler_Action_WebCheck(
                'task_scheduler_action_web_check',
                'TaskScheduler_Action_WebCheck_Wizard' // wizard class name
            );
            
            // 1.4.0+
            new TaskScheduler_Action_PHPScript( '', 'TaskScheduler_Action_PHPScript_Wizard' );

        }
        
        /**
         * Routines (tasks and threads)
         * @since       1.0.1
         */
        private function ___loadRoutines() {
            new TaskScheduler_Event_Routine;
            new TaskScheduler_Event_Thread;
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
        private function ___loadServerHeartbeat() {
            TaskScheduler_ServerHeartbeat::pulsate();
            new TaskScheduler_Event_ServerHeartbeat_Option;
            new TaskScheduler_Event_ServerHeartbeat_Checker;
            new TaskScheduler_Event_ServerHeartbeat_Loader;
            new TaskScheduler_Event_ServerHeartbeat_Resumer;
        }
        
}
