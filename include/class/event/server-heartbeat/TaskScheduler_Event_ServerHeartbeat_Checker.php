<?php
/**
 * Checks next scheduled routines and if there are within the heartbeat interval, spawns them.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

 /**
  * 
  */
class TaskScheduler_Event_ServerHeartbeat_Checker {
	
	/**
	 * The check-action transient key.
	 */
	static public $sCheckActionTransientKey		= 'TS_checking_actions';
	static public $sRecheckActionTransientKey	= 'TS_rechecking_actions';
		
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
		add_action( 'wp_loaded', array( $this, '_replyToSpawnRoutines' ), 1 );	// set the high priority because the sleep sub-routine also hooks the same action.
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
		
		$_iSecondsFromNow				= TaskScheduler_Utility::canUseIniSet() ? TaskScheduler_Option::get( array( 'server_heartbeat', 'interval' ) ) : 0; // if the maximum execution time cannot be changed, only pick ones that exceeds the scheduled time.
		$_iMaxAllowedNumberOfRoutines	= TaskScheduler_Option::get( array( 'routine', 'max_background_routine_count' ) );
		$_aScheduledRoutines			= TaskScheduler_RoutineUtility::getScheduled( $_iSecondsFromNow, $_iMaxAllowedNumberOfRoutines );
		$_iProcessingCount				= TaskScheduler_RoutineUtility::getProcessingCount();
		$_iAllowedNumberOfRoutines		= $_iMaxAllowedNumberOfRoutines - $_iProcessingCount;
		$_aScheduledRoutines			= array_slice( $_aScheduledRoutines, 0, $_iAllowedNumberOfRoutines );
		$_nNow							= microtime( true );

		// Set a check-action lock 
		delete_transient( self::$sRecheckActionTransientKey );
		set_transient( self::$sCheckActionTransientKey, $_nNow, 60 );
		
		foreach ( $_aScheduledRoutines as $_iRoutineID ) {		
		
			$_oTask = TaskScheduler_Routine::getInstance( $_iRoutineID );
			if ( ! is_object( $_oTask ) ) { continue; }	
			if ( ! $_oTask->_force_execution && ! in_array( $_oTask->_routine_status, array( 'inactive', 'queued' ) )  ) { continue; }
			
			// Check if the owner task exists or not. If not, delete the thread and skip the iteration.
			if ( $_oTask->isThread() && ! is_object( TaskScheduler_Routine::getInstance( $_oTask->owner_routine_id ) ) ) {
				$_oTask->delete();	
				continue;				
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
	 * @param	integer	$iRoutineID		The routine ID
	 * @param	numeric	$nScheduledTime	The scheduled run time. This is also used to determine whether the call is made manually or automatically. 
	 * If the server heartbeat is running and pulsates, it does not the scheduled time but if the user presses the 'Run Now' link in the task listing table,
	 * it sets the scheduled time.
	 */
	public function _replyToSpawnRoutine( $iRoutineID, $nScheduledTime=null ) {
		
		$_nCurrentMicrotime = microtime( true );
		do_action( 'task_scheduler_action_before_calling_routine', TaskScheduler_Routine::getInstance( $iRoutineID ), $_nCurrentMicrotime );
		
		$_aDebugInfo = defined( 'WP_DEBUG' ) && WP_DEBUG
			? array( 'spawning_routine' => $iRoutineID )
			: array();
		
		TaskScheduler_ServerHeartbeat::loadPage(
			add_query_arg( $_aDebugInfo, trailingslashit( site_url() ) ),	// the Apache log indicates that if a trailing slash misses, it redirects to the url WITH it.
			array(
				'server_heartbeat_id'				=>	'',	// do not set the id so that the server heartbeat does not think it is a background call.
				'server_heartbeat_action'			=>	$iRoutineID,
				'server_heartbeat_scheduled_time'	=>	$nScheduledTime,
				'server_heartbeat_spawned_time'		=>	$_nCurrentMicrotime,
			),
			'spawn_routine'
		);		

		// Do not call the action, 'task_scheduler_action_after_calling_routine', here.
		// This is because the action will be called in the next page load and it is not called yet.
	
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
