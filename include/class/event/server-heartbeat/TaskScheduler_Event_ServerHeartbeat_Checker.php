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
		
	public function __construct() {
		
		add_action( 'task_scheduler_action_spawn_routine', array( $this, '_replyToSpawnRoutine' ), 10, 2 );
		
		// If this is a server-heartbeat background page load and not doing actions, spawn tasks.
		if ( 
			TaskScheduler_ServerHeartbeat::isBackground() 
			&& ! isset( $_COOKIE[ 'server_heartbeat_action' ] )
		) {
// TaskScheduler_Debug::log( 'checking next scheduled tasks' );
			
			// Letting the site load and wait till the 'wp_loaded' hook is required to load the custom taxonomy that the plugin uses.
			add_action( 'wp_loaded', array( $this, '_replyToSpawnRoutines' ), 1 );	// set the high priority because the sleep sub-routine also hooks the same action.
			return;
			
		} 	
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
$_aInfo = array(
	'Heartbeat_ID' => $_COOKIE['server_heartbeat_id'],
	'Context' => $_COOKIE['server_heartbeat_context'],
	'Max_Allowed_Routine_Count' => $_iMaxAllowedNumberOfRoutines,
	'Found_Scheduled' => ( count( $_aScheduledRoutines ) ),
	'Processing_Count' => $_iProcessingCount,
	'Allowed_Number_of_Routines' => $_iAllowedNumberOfRoutines,
	'Spawning_Count' => count( $_aScheduledRoutines ),
);
TaskScheduler_Debug::log( 'Querying routines: ' . http_build_query( $_aInfo, '', ', ' ) );		
		foreach ( $_aScheduledRoutines as $_iRoutineID ) {		
		
			$_oTask = TaskScheduler_Routine::getInstance( $_iRoutineID );
			if ( ! is_object( $_oTask ) ) { continue; }	
			if ( ! $_oTask->_force_execution && ! in_array( $_oTask->_routine_status, array( 'inactive', 'queued' ) )  ) { continue; }
			
if ( $_oTask->isTask() ) {	
	$_aInfo = array(
		'task id'			=>	'ID: ' . $_oTask->ID,
		'thread exists' 	=>	'Type: ' . ( $_oTask->isThread() ? 'Thread' : 'Task' ),
		'status'			=>	'Status: ' . $_oTask->_routine_status,
		'next_run_readable'	=>	'Next Time: ' . TaskScheduler_WPUtility::getSiteReadableDate( $_oTask->_next_run_time, 'Y/m/d G:i:s', true ),
	);
	TaskScheduler_Debug::log( 
		'Spawning the task: ' . implode( ', ', $_aInfo )
	);
}
			// Check if the owner task exists or not. If not, delete the thread and skip the iteration.
			if ( $_oTask->isThread() && ! is_object( TaskScheduler_Routine::getInstance( $_oTask->owner_routine_id ) ) ) {
TaskScheduler_Debug::log( 'the parsed thread does not have an owner so deleting: ' . $_oTask->ID );
				$_oTask->delete();	
				continue;				
			}
			
			do_action( 'task_scheduler_action_spawn_routine', $_iRoutineID );
			
		}
		
	}
	
	
	/**
	 * Spawns the given routine.
	 * 
	 * @param	integer	$iRoutineID		The routine ID
	 * @param	numeric	$nScheduledTime	The scheduled run time. This is also used to determine whether the call is made manually or automatically.
	 */
	public function _replyToSpawnRoutine( $iRoutineID, $nScheduledTime=null ) {

		do_action( 'task_scheduler_action_before_calling_routine', TaskScheduler_Routine::getInstance( $iRoutineID ) );
		
		$_aDebugInfo = defined( 'WP_DEBUG' ) && WP_DEBUG
			? array( 'spawning_routine' => $iRoutineID )
			: array();
		
		TaskScheduler_ServerHeartbeat::loadPage(
			add_query_arg( $_aDebugInfo, trailingslashit( site_url() ) ),	// the Apache log indicates that if a trailing slash misses, it redirects to the url WITH it.
			array(
				'server_heartbeat_id'				=>	'',	// do not set the id so that the server heartbeat does not think it is a background call.
				'server_heartbeat_action'			=>	$iRoutineID,
				'server_heartbeat_scheduled_time'	=>	$nScheduledTime,
			),
			'spawn_routine'
		);		

		// Do not call the action, 'task_scheduler_action_after_calling_routine', here.
		// This is because the action will be called in the next page load and it is not called yet.
	
	}	
	
}
