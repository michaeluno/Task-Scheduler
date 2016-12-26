<?php
/**
 * Triggers actions associated with the tasks of the task scheduler.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Handles routine actions to be executed. 
 * 
 * @remark        This class is dependent on the TaskScheduler_ServerHeartbeat class.
 * @action        do        task_scheduler_action_after_calling_routine         Called after the routine is spawned and before the routine action is going to be triggered.
 * @action        do        task_scheduler_action_cancel_routine                Called when a routine is canceled.
 * @action        do        task_scheduler_action_before_doing_routine 
 * @action        do        task_scheduler_action_do_routine
 * @action        do        task_scheduler_action_after_doing_routine
 */
class TaskScheduler_Event_ServerHeartbeat_Loader {
    
    private $_sTransientPrefix = '';    // assigned in the constructor.
    
    public function __construct() {
        
        $this->_sTransientPrefix = TaskScheduler_Registry::TRANSIENT_PREFIX;

        // At this point, the page is loaded for a specific routine(task/thread).
        if ( ! self::isCallingAction() ) { 
            return; 
        }
            
        // Tell WordPress this is a background routine by setting the Cron flag.
        if ( ! defined( 'DOING_CRON' ) ) { 
            define( 'DOING_CRON', true );
        }
        if ( ! defined( 'WP_USE_THEMES' ) ) {
            define( 'WP_USE_THEMES', false );
        }
        ignore_user_abort( true );
    
        // Let other third-party scripts load their necessary components by hooking the 'init' action instead of calling the method right here.
        // Also the Admin Page Framework library adds user defined custom posts and custom taxonomies at the 'init' hook.
        // So executing the routine earlier than that will result on not having those components including their hooks, callbacks, and settings.
        add_action( 'init', array( $this, '_replyToDoRoutineAndExit' ), 9999 );
        
        // Keep loading WordPress...
        
    }
        
    /**
     * Executes an individual task and then exits.
     * 
     * @callback    action      init
     * @return      void
     */
    public function _replyToDoRoutineAndExit() {

        $_oRoutine = TaskScheduler_Routine::getInstance( absint( $_COOKIE[ 'server_heartbeat_action' ] ) );
        if ( ! is_object( $_oRoutine ) )  {
            exit();        
        }        
        if ( ! $_oRoutine->routine_action  ) {
            exit();                    
        }

        // Check the spawned time in case simultaneous page loads triggered the same task.
        $_nSpawnedTime = isset( $_COOKIE[ 'server_heartbeat_spawned_time' ] ) 
            ? ( string ) $_COOKIE[ 'server_heartbeat_spawned_time' ] 
            : null;
        if ( $_nSpawnedTime !== ( string ) $_oRoutine->_spawned_time ) {
            exit();
        }

        do_action( 'task_scheduler_action_after_calling_routine', $_oRoutine );
        $this->_doRoutine( $_oRoutine, $this->___getScheduledTime() );
        exit();
    
    }
        /**
         * @return      double      Cast `double` as the cookie values are string.
         * @since       1.4.3
         */
        private function ___getScheduledTime() {
            return isset( $_COOKIE[ 'server_heartbeat_scheduled_time' ] )
                ? ( double ) $_COOKIE[ 'server_heartbeat_scheduled_time' ]
                : ( double ) 0;
        }

    /**
     * Do the routine
     * 
     * @return    void
     */    
    private function _doRoutine( $oRoutine, $nScheduledTime ) {

        // Set the max execution time and wait until the exact time.
        $_dSleepSeconds            = $this->_getRequiredSleepSeconds( $oRoutine, $nScheduledTime );
        $_dServerheartbeatInterval = ( double ) TaskScheduler_Option::get( array( 'server_heartbeat', 'interval' ) );
        if ( $_dSleepSeconds > $_dServerheartbeatInterval ) {
            // The sleep duration is too long.
            do_action( "task_scheduler_action_cancel_routine" , $oRoutine );
            return;             
        }
        $_iActionLockDuration    = $this->_setMaxExecutionTime( $oRoutine, $_dSleepSeconds );
        $this->_sleep( $_dSleepSeconds );
    
        // Check the action lock.
        $_sActionLockKey = $this->_sTransientPrefix . $oRoutine->ID;
        if ( TaskScheduler_WPUtility::getTransient( $_sActionLockKey ) ) { 
            // The task is locked.
            do_action( "task_scheduler_action_cancel_routine" , $oRoutine );
            return; 
        }
    
        // Lock the action.
        do_action( "task_scheduler_action_before_doing_routine", $oRoutine );
        TaskScheduler_WPUtility::setTransient( $_sActionLockKey, time(), $_iActionLockDuration );
    
        // Do the action
        do_action( 'task_scheduler_action_do_routine', $oRoutine );
        
        // Unlock the action
        TaskScheduler_WPUtility::deleteTransient( $_sActionLockKey );
        do_action( "task_scheduler_action_after_doing_routine", $oRoutine );    // for the Volatile occurrence type
        
    }
    
        /**
         * Returns the required sleep duration in seconds.
         * 
         * @return    double    The required sleep duration in seconds.
         */
        private function _getRequiredSleepSeconds( $oRoutine, $nNextRunTimeStamp ) {
            
            $_dNextRunTimeStamp = $this->_getNextRunTimeStamp( $nNextRunTimeStamp, $oRoutine );
            $_nSleepSeconds     = ( $_dNextRunTimeStamp - microtime( true ) ) + 0;    // plus zero will convert the value to numeric.
            $_nSleepSeconds     = $_nSleepSeconds <= 0
                ? 0 
                : $_nSleepSeconds;
            return ( double ) $_nSleepSeconds;
            
        }        
            /**
             * @return      double
             * @since       1.1.1
             */
            private function _getNextRunTimeStamp( $nNextRunTimeStamp, $oRoutine ) {
                
                if ( $nNextRunTimeStamp ) {
                    return ( double ) $nNextRunTimeStamp;
                }
               
                // If the routine next run time is not set use the value of the task
                $_oTask   = TaskScheduler_Routine::getInstance( $oRoutine->owner_task_id );
                return isset( $_oTask->_next_run_time )
                    ? ( double ) $_oTask->_next_run_time
                    : ( double ) $nNextRunTimeStamp;
             
            }
            
        /**
         * Sets the required maximum script execution time.
         * 
         * @return    integer    The required duration for the action lock,
         * which represents the expected time duration (in seconds) that the routine completes.
         */
        private function _setMaxExecutionTime( $oRoutine, $nSecondsToSleep ){
                                
            // Make sure the script can sleep plus execute the action.
            $_iExpectedTaskExecutionTime    = ( int ) $oRoutine->_max_execution_time ? $oRoutine->_max_execution_time : 30;    // avoid 0 because it is used for the transient duration and if 0 the transient won't expire.
            $_dElapsedSeconds               = ( double ) timer_stop( 0, 6 );
            $_nRequiredExecutionDuration    = ceil( $nSecondsToSleep ) + $_dElapsedSeconds + $_iExpectedTaskExecutionTime;
            
            // Some servers disable this function.
            if ( ! TaskScheduler_Utility::canUseIniSet() ) {
                return ( integer ) $_iExpectedTaskExecutionTime;
            }            
            
            // If the server set max execution time is 0, the script can continue endlessly.
            $_iServerAllowedMaxExecutionTime    = TaskScheduler_Utility::getServerAllowedMaxExecutionTime( 30 );
            if ( 0 === $_iServerAllowedMaxExecutionTime || '0' === $_iServerAllowedMaxExecutionTime ) {
                return ( integer ) $_iExpectedTaskExecutionTime;
            }
            
            // If the user sets 0 to the task max execution time option, set the ini setting to 0.
            if ( 0 === $oRoutine->_max_execution_time || '0' === $oRoutine->_max_execution_time ) {
                @ini_set( 'max_execution_time', 0 );
            }
            
            // Set the expecting task execution duration.
            if ( $_nRequiredExecutionDuration > $_iServerAllowedMaxExecutionTime ) {
                @ini_set( 'max_execution_time', $_nRequiredExecutionDuration );
            }    
            
            return ( integer ) $_iExpectedTaskExecutionTime;
            
        }    

        /**
         * Sleeps until the next scheduled time
         */
        private function _sleep( $nSleepSeconds ) {

            if ( $nSleepSeconds <= 0 ) { 
                return; 
            }
            $_iSleepDurationMicroSeconds = ceil( $nSleepSeconds ) * 1000000;
            if ( $_iSleepDurationMicroSeconds > 0 ) {
                usleep( ( integer ) $_iSleepDurationMicroSeconds );
            }

        }                
                                
    /**
     * Checks if the page load is for doing action.
     * 
     * @remark    This class does not set the 'server_heartbeat_action' key but the action loader class. 
     */
    static public function isCallingAction() {
        return isset( $_COOKIE[ 'server_heartbeat_action' ] ); 
    }

}