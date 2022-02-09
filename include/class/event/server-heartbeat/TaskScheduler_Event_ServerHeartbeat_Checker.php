<?php
/**
 * Checks next scheduled routines and if there are within the heartbeat interval, spawns them.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author      Michael Uno
 * @authorurl   http://michaeluno.jp
 * @since       1.0.0
 */

 /**
  * 
  * @action do  task_scheduler_action_before_calling_routine    Performed right before a routine is spanwed.
  */
class TaskScheduler_Event_ServerHeartbeat_Checker {
    
    /**
     * The check-action transient key.
     */
    static public $sCheckActionTransientKey      = 'TS_checking_actions';
    static public $sRecheckActionTransientKey    = 'TS_rechecking_actions';
    
    /**
     * Sets up hooks.
     */
    public function __construct() {

        add_action( 'task_scheduler_action_spawn_routine', array( $this, '_replyToSpawnTheRoutine' ), 10, 4 );
        add_action( 'task_scheduler_action_check_scheduled_actions', array( $this, '_replyToCheckScheduledActions' ) );
        
        // If doing actions, return.
        if ( isset( $_COOKIE[ 'server_heartbeat_action' ] ) ) {
            return;
        }
        
        // Do not check actions in certain pages.
        if ( isset( $GLOBALS[ 'pagenow' ] ) && in_array( $GLOBALS[ 'pagenow' ], array( 'admin-ajax.php', ) ) ) {
            return;
        }
        
        // If this is not a server-heartbeat background page load or a page load to check scheduled actions manually, do nothing
        if ( 
            ! (
                TaskScheduler_ServerHeartbeat::isBackground()
                || $this->___isManualPageLoad()
            )
        ) {
            return;
        }
        
        // Check a check-action lock - this prevents that while checking and spawning routines, another page load do the same and triggers the same tasks at the same time.
        if ( TaskScheduler_WPUtility::getTransient( self::$sCheckActionTransientKey ) ) {
            return;
        }

        // At this point, the page load can spawn routines.
        // Letting the site load and wait till the 'wp_loaded' hook is required to load the custom taxonomy that the plugin uses.
        add_action( 'wp_loaded', array( $this, '_replyToSpawnRoutines' ), 1 );    // set the high priority because the sleep sub-routine also hooks the same action.


    }
        /**
         * Checks if the page is loaded by manually to check actions.
         * 
         * When the user disables the server heartbeat and uses own Cron jobs to check actions, 
         * the user accesses the site with the 'task_scheduler_checking_actions' key in the request url.
         */
        private function ___isManualPageLoad() {

            if ( isset( $GLOBALS[ 'pagenow' ] ) && in_array( $GLOBALS[ 'pagenow' ], array( 'wp-cron.php' ) ) ) {
                return false;
            }
            
            // Check if the server heartbeat is on.
            if ( TaskScheduler_Option::get( array( 'server_heartbeat', 'power' ) ) ) {
                return false;
            }            
            return isset( $_REQUEST[ 'task_scheduler_checking_actions' ] ) && $_REQUEST[ 'task_scheduler_checking_actions' ];   // sanitization unnecessary
            
        }

    /**
     * Spawns scheduled tasks.
     *
     * @callback    add_action      wp_loaded
     * @return      void
     */
    public function _replyToSpawnRoutines() {

        $_iSecondsFromNowToCheck        = TaskScheduler_Utility::canUseIniSet() 
            ? TaskScheduler_Option::get( array( 'server_heartbeat', 'interval' ) ) 
            : 0; // if the maximum execution time cannot be changed, only pick ones that exceeds the scheduled time.

        $_iMaxAllowedNumberOfRoutines   = TaskScheduler_Option::get( array( 'routine', 'max_background_routine_count' ) );
        $_aScheduledRoutines            = TaskScheduler_RoutineUtility::getScheduled( $_iSecondsFromNowToCheck, $_iMaxAllowedNumberOfRoutines );
        $_iProcessingCount              = TaskScheduler_RoutineUtility::getProcessingCount();
        $_iAllowedNumberOfRoutines      = $_iMaxAllowedNumberOfRoutines - $_iProcessingCount;
        $_aScheduledRoutines            = array_slice( $_aScheduledRoutines, 0, $_iAllowedNumberOfRoutines );
        $_nNow                          = microtime( true );

        // Set a check-action lock 
        TaskScheduler_WPUtility::deleteTransient( self::$sRecheckActionTransientKey );
        TaskScheduler_WPUtility::setTransient( self::$sCheckActionTransientKey, $_nNow, 60 );

        // Parse the retrieved routines.
        // If it is a task, update the next scheduled time, create a routine and return.
        // If it is a routine, spawn it.
        // If it is a thread, check if the owner routine exists or not. If not, delete itself; otherwise, spawn it.        
        foreach ( $_aScheduledRoutines as $_iItemID ) {
        
            $_oTask = TaskScheduler_Routine::getInstance( $_iItemID );
            if ( ! is_object( $_oTask ) ) { 
                continue; 
            }

            // If it is a thread and the owner task does not exist, delete the thread and skip the iteration.
            if ( $this->___isThreadWithoutOwnerRoutine( $_oTask ) ) {
                $_oTask->delete();
                continue;
            }

            do_action( 
                'task_scheduler_action_spawn_routine', 
                $_iItemID,
                $_oTask->_next_run_time, // 1.1.1+ this next run time value is still the one used for the database query above.
                true, // update the next run time
                false // force
            );

        }
        
        TaskScheduler_WPUtility::deleteTransient( self::$sCheckActionTransientKey );
        
        // If the transient value is different from the set one right before the loop, it means that another process has requested a check.
        if ( TaskScheduler_WPUtility::getTransient( self::$sRecheckActionTransientKey ) ) {
            TaskScheduler_WPUtility::deleteTransient( self::$sRecheckActionTransientKey );
            TaskScheduler_ServerHeartbeat::beat();
        }

        
    }   
        /**
         * Checks if the given object is a thread and its owner task exists or not. 
         * 
         * @since       1.1.1
         * @return      boolean
         * @param       TaskScheduler_Routine $oRoutine
         */
        private function ___isThreadWithoutOwnerRoutine( $oRoutine ) {

            if ( ! $oRoutine->isThread() ) {
                return false;
            }
            return ! is_object( TaskScheduler_Routine::getInstance( $oRoutine->owner_routine_id ) );

        }
        
    /**
     * Spawns the given routine.
     * 
     * @param    integer        $iRoutineID         The task/routine ID
     * @param    integer|float  $nScheduledTime     The scheduled run time.
     * @param    boolean        $bUpdateNextRunTime Whether to schedule the next run.
     * @param    boolean        $bForce             Whether to spawn the routine by ignoring the action lock.
     * @callback action         task_scheduler_action_spawn_routine
     * @return   void
     */
    public function _replyToSpawnTheRoutine( $iRoutineID, $nScheduledTime, $bUpdateNextRunTime=true, $bForce=false ) {

        // First check if it is a task  
        $_oRoutine = TaskScheduler_Routine::getInstance( $iRoutineID );
        if ( ! ( $_oRoutine instanceof TaskScheduler_Routine ) ) {
            return;
        }

        // For tasks, create a routine object.
        $_bIsTask = $_oRoutine->isTask();
        // [1.6.1+] If it is not called by force, check if there are already running routines by this task. If there are, do not create it.
        if ( $_bIsTask && ! $bForce ) {
           if ( count( TaskScheduler_RoutineUtility::getSpawnedByHeartbeatByID( $_oRoutine->ID ) ) ) {
               return;
           }
        }
        if ( $_bIsTask ) {
            $this->___updateTaskStatus( $_oRoutine, $nScheduledTime, $bUpdateNextRunTime );
            $_oRoutine = $this->___getRoutineFromTask( $_oRoutine );
            if ( ! isset( $_oRoutine->ID ) ) {
                return;
            }
            // [1.6.1+] Flag whether it is spawned forcefully to prevent unnecessary duplicated routines from spawning later.
            $_oRoutine->setMeta( '_spawned_by_force', ( string ) ( integer ) $bForce ); // convert true to '1' and false to '0' of string value to be queried easily
            $iRoutineID = $_oRoutine->ID;
        }

        // For routine instances,
        if ( $_oRoutine->isRoutine() ) {
            $this->___updateRoutineStatus( $_oRoutine );
        }
        
        // Let other subroutines update routine meta such as `_is_spawned` etc.
        $_nCurrentMicrotime = microtime( true );
        do_action( 
            'task_scheduler_action_before_calling_routine', 
            TaskScheduler_Routine::getInstance( $iRoutineID ), 
            $_nCurrentMicrotime
        );

        $_aDebugInfo = defined( 'WP_DEBUG' ) && WP_DEBUG
            ? array( 'spawning_routine' => $iRoutineID )
            : array();
        
        TaskScheduler_ServerHeartbeat::loadPage(
            add_query_arg( $_aDebugInfo, trailingslashit( site_url() ) ),    // the Apache log indicates that if a trailing slash misses, it redirects to the url WITH it.
            array( // cookies
                'server_heartbeat_id'                => '',    // do not set the id so that the server heartbeat does not think it is a background call.
                // 1.3.2+ Cast string for the bug in WordPress v4.6 https://core.trac.wordpress.org/ticket/37768
                'server_heartbeat_action'            => ( string ) $iRoutineID,
                'server_heartbeat_scheduled_time'    => ( string ) $nScheduledTime,
                'server_heartbeat_spawned_time'      => ( string ) $_nCurrentMicrotime,
                // 1.5.0
                'server_heartbeat_force_spawn'       => ( string ) $bForce,
            ),
            'spawn_routine' // context
        );        

        // Do not call the action, 'task_scheduler_action_after_calling_routine', here
        // because the action will be called in the next page load and it is not called yet.
    
    }    
        /**
         * Create a routine object from a task.
         * @since       1.1.1
         * @param       TaskScheduler_Routine $oTask
         * @return      TaskScheduler_Routine|null
         */
        private function ___getRoutineFromTask( $oTask ) {
            
            // Create a routine to spawn.
            $_iRoutineID = TaskScheduler_RoutineUtility::derive( 
                $oTask->ID,  // the owner task id.
                array( 
                    'parent_routine_log_id'    => $oTask->log( __( 'Starting a routine.', 'task-scheduler' ), 0, true ),
                ),
                array(),    // system taxonomy terms
                $oTask->_force_execution   // allow duplicate
            );
            return TaskScheduler_Routine::getInstance( $_iRoutineID );
        
        }

        /**
         * Updates the task meta data.
         *
         * @param TaskScheduler_Routine $oTask
         * @param integer|double        $nScheduledTime
         * @param boolean               $bUpdateNextRunTime
         */
        private function ___updateTaskStatus( $oTask, $nScheduledTime, $bUpdateNextRunTime ) {
            
            $oTask->deleteMeta( '_exit_code' );
            $_sPreviousTaskStatus     = $oTask->_routine_status;
            $_iMaxTaskExecutionTime   = ( int ) $oTask->_max_execution_time;
            $oTask->setMeta( '_count_call',     $oTask->getMeta( '_count_call' ) + 1 );
            
            // When a task status is switched from Disabled to Enabled, the scheduled time is behind the current time.
            // In that case, set the current time to the last run time.
            // Otherwise, use the scheduled time (which is usually ahead because the routine is spawned prior to the actual scheduled time)
            // as the last run time.
            $_nNow = microtime( true );
            $oTask->setMeta( 
                '_last_run_time',  
                $nScheduledTime >= $_nNow 
                    ? $nScheduledTime 
                    : $_nNow 
            );
            
            // Update the scheduled time. If the user manually calls a routine, the scheduled time is set. 
            // In that case, do not update the scheduled time.
            if ( ! $bUpdateNextRunTime ) {
                return;
            }
            $oTask->setMeta( 
                '_next_run_time', 
                apply_filters( 
                    "task_scheduler_filter_next_run_time_{$oTask->occurrence}", 
                    microtime( true ), // '', // @todo time()
                    $oTask 
                ) 
            );

        }

        /**
         * Updates the routine meta data
         *
         * @param TaskScheduler_Routine $oRoutine
         */
        private function ___updateRoutineStatus( $oRoutine ) {

            $oRoutine->setMeta( '_count_call',     $oRoutine->getMeta( '_count_call' ) + 1 );
            $oRoutine->setMeta( '_last_run_time',  microtime( true ) );
            $_iNextRunTime = apply_filters(
                "task_scheduler_filter_next_run_time_{$oRoutine->occurrence}",
                microtime( true ),
                $oRoutine
            );
            $oRoutine->setMeta( '_next_run_time',  $_iNextRunTime );

        }

    /**
     * Checks scheduled actions in a background page load.
     *
     * If there are scheduled ones and reach the scheduled time, they will be spawned.
     *
     */
    public function _replyToCheckScheduledActions() {

        if ( TaskScheduler_WPUtility::getTransient( self::$sCheckActionTransientKey ) ) {
            TaskScheduler_WPUtility::setTransient( self::$sRecheckActionTransientKey, microtime( true ), 60 );
            return;
        }
        TaskScheduler_ServerHeartbeat::beat();

    }

}
