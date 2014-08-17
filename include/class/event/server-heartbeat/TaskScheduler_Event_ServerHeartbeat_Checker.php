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
        
    public function __construct() {

        add_action( 'task_scheduler_action_spawn_routine', array( $this, '_replyToSpawnRoutine' ), 10, 2 );
        add_action( 'task_scheduler_action_check_shceduled_actions', array( $this, '_replyToCheckScheduledActions' ) );
        
        // If doing actions, return.
        if ( isset( $_COOKIE[ 'server_heartbeat_action' ] ) ) {
            return;
        }
        
        // Do not check actions in certain pages.
        if ( isset( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], array( 'admin-ajax.php', ) ) ) {
            return;
        }
        
        // If this is not a server-heartbeat background page load or a page load to check scheduled actions manually
        if ( 
            ! ( TaskScheduler_ServerHeartbeat::isBackground() 
            || $this->_isManualPageLoad() )
        ) {
            return;
        }
        
        // Check a check-action lock - this prevents that while checking and spawning routines, another page load do the same and triggers the same tasks at the same time.
        if ( TaskScheduler_WPUtility::getTransientWithoutCache( self::$sCheckActionTransientKey ) ) {
            return;
        }
        
        // At this point, the page load deserves spawning routines.        
        // Letting the site load and wait till the 'wp_loaded' hook is required to load the custom taxonomy that the plugin uses.
        add_action( 'wp_loaded', array( $this, '_replyToSpawnRoutines' ), 1 );    // set the high priority because the sleep sub-routine also hooks the same action.
        return;
        
    
        
    }    
    
        /**
         * Checks if the page is loaded by manually to check actions.
         * 
         * When the user disables the server heartbeat and uses own Cron jobs to check actions, 
         * the user accesses the site with the 'task_scheduler_checking_actions' key in the request url.
         */
        private function _isManualPageLoad() {

            if ( isset( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], array( 'wp-cron.php' ) ) ) {
                return false;
            }
            
            // Check if the server heartbeat is on.
            if ( TaskScheduler_Option::get( array( 'server_heartbeat', 'power' ) ) ) {
                return false;
            }            
            return isset( $_REQUEST['task_scheduler_checking_actions'] ) && $_REQUEST['task_scheduler_checking_actions'];
            
        }

    /**
     * Spawns scheduled tasks.
     */
    public function _replyToSpawnRoutines() {

        $_iSecondsFromNowToCheck        = TaskScheduler_Utility::canUseIniSet() ? TaskScheduler_Option::get( array( 'server_heartbeat', 'interval' ) ) : 0; // if the maximum execution time cannot be changed, only pick ones that exceeds the scheduled time.
        $_iMaxAllowedNumberOfRoutines   = TaskScheduler_Option::get( array( 'routine', 'max_background_routine_count' ) );
        $_aScheduledRoutines            = TaskScheduler_RoutineUtility::getScheduled( $_iSecondsFromNowToCheck, $_iMaxAllowedNumberOfRoutines );
        $_iProcessingCount              = TaskScheduler_RoutineUtility::getProcessingCount();
        $_iAllowedNumberOfRoutines      = $_iMaxAllowedNumberOfRoutines - $_iProcessingCount;
        $_aScheduledRoutines            = array_slice( $_aScheduledRoutines, 0, $_iAllowedNumberOfRoutines );
        $_nNow                          = microtime( true );

        // Set a check-action lock 
        delete_transient( self::$sRecheckActionTransientKey );
        set_transient( self::$sCheckActionTransientKey, $_nNow, 60 );
        
        // Parse the retrieved routines.
        // If it is a task, update the next scheduled time, create a routine and return.
        // If it is a routine, spawn it.
        // If it is a thread, check if the owner routine exists or not. If not, delete itself; otherwise, spawn it.
        foreach ( $_aScheduledRoutines as $_iRoutineID ) {        
        
            $_oTask = TaskScheduler_Routine::getInstance( $_iRoutineID );
            if ( ! is_object( $_oTask ) ) { continue; }    
                             
            // Check if the owner task exists or not. If not, delete the thread and skip the iteration.
            if ( $_oTask->isThread() && ! is_object( TaskScheduler_Routine::getInstance( $_oTask->owner_routine_id ) ) ) {
                $_oTask->delete();                   
            }

            do_action( 'task_scheduler_action_spawn_routine', $_iRoutineID );
            
        }
        
        delete_transient( self::$sCheckActionTransientKey );
        
        // If the transient value is different from the set one right before the loop, it means that another process has requested a check.
        if ( TaskScheduler_WPUtility::getTransientWithoutCache( self::$sRecheckActionTransientKey ) ) {
            delete_transient( self::$sRecheckActionTransientKey );
            TaskScheduler_ServerHeartbeat::beat();
        }        
        
    }
                
    /**
     * Spawns the given routine.
     * 
     * @param    integer    $iRoutineID        The routine ID
     * @param    numeric    $nScheduledTime    The scheduled run time. This is also used to determine whether the call is made manually or automatically. 
     * If the server heartbeat is running and pulsates, it does not the scheduled time but if the user presses the 'Run Now' link in the task listing table,
     * it sets the scheduled time.
     */
    public function _replyToSpawnRoutine( $iRoutineID, $nScheduledTime=null ) {

        // First check if it is a task 
        $_oRoutine = TaskScheduler_Routine::getInstance( $iRoutineID );
        if ( ! is_object( $_oRoutine ) ) { return; }            

        // For tasks,
        if ( $_oRoutine->isTask() ) {
               
            $_iTaskID   = $iRoutineID;
            $_oTask     = $_oRoutine;
            $this->_updateTaskStatus( $_oTask, $nScheduledTime );
                            
            // Create a routine and spawn it.
            $iRoutineID = TaskScheduler_RoutineUtility::derive( 
                $_iTaskID,  // the owner task id.
                array( 
                    'parent_routine_log_id'    =>  $_oTask->log( __( 'Starting a routine.', 'task-scheduler' ), 0, true ),
                ),
                array(),    // system taxonomy terms
                $_oTask->_force_execution   // allow duplicate
            );
            $_oRoutine = TaskScheduler_Routine::getInstance( $iRoutineID );

        }
        
        // For routine instances,
        if ( $_oRoutine->isRoutine() ) {
            $this->_updateRoutineStatus( $_oRoutine );
        }
        
        $_nCurrentMicrotime = microtime( true );
        do_action( 'task_scheduler_action_before_calling_routine', TaskScheduler_Routine::getInstance( $iRoutineID ), $_nCurrentMicrotime );
        
        $_aDebugInfo = defined( 'WP_DEBUG' ) && WP_DEBUG
            ? array( 'spawning_routine' => $iRoutineID )
            : array();
        
        TaskScheduler_ServerHeartbeat::loadPage(
            add_query_arg( $_aDebugInfo, trailingslashit( site_url() ) ),    // the Apache log indicates that if a trailing slash misses, it redirects to the url WITH it.
            array(
                'server_heartbeat_id'                => '',    // do not set the id so that the server heartbeat does not think it is a background call.
                'server_heartbeat_action'            => $iRoutineID,
                'server_heartbeat_scheduled_time'    => $nScheduledTime,
                'server_heartbeat_spawned_time'      => $_nCurrentMicrotime,
            ),
            'spawn_routine'
        );        

        // Do not call the action, 'task_scheduler_action_after_calling_routine', here.
        // This is because the action will be called in the next page load and it is not called yet.
    
    }    
    
        /**
         * Updates the task meta data.
         */
        private function _updateTaskStatus( $oTask, $nScheduledTime=0 ) {
            
            $oTask->deleteMeta( '_eixt_code' );
            $_sPreviousTaskStatus     = $oTask->_routine_status;
            $_iMaxTaskExecutionTime   = ( int ) $oTask->_max_execution_time;
            $oTask->setMeta( '_count_call',     $oTask->getMeta( '_count_call' ) + 1 );
            $oTask->setMeta( '_last_run_time',  microtime( true ) );
            
            // Update the scheduled time. If the user manually calls a routine, the scheduled time is set. 
            // In that case, do not update the scheduled time.
            if ( ! $nScheduledTime ) {
                $oTask->setMeta( '_next_run_time',  apply_filters( "task_scheduler_filter_next_run_time_{$oTask->occurrence}", '', $oTask ) );
            }

        }
        
        /**
         * Updates the routine meta data
         */
        private function _updateRoutineStatus( $oRoutine ) {
            
            $oRoutine->setMeta( '_count_call',     $oRoutine->getMeta( '_count_call' ) + 1 );
            $oRoutine->setMeta( '_last_run_time',  microtime( true ) );             
            $oRoutine->setMeta( '_next_run_time',  apply_filters( "task_scheduler_filter_next_run_time_{$oRoutine->occurrence}", '', $oRoutine ) );
            
        }

    /**
     * Checks scheduled actions in a background page load.
     * 
     * If there are scheduled ones and reach the scheduled time, they will be spawned.
     * 
     */
    public function _replyToCheckScheduledActions() {
        
        if ( get_transient( self::$sCheckActionTransientKey ) ) {
            set_transient( self::$sRecheckActionTransientKey, microtime( true ), 60 );
            return;
        }
        TaskScheduler_ServerHeartbeat::beat();
        
    }
    
}
