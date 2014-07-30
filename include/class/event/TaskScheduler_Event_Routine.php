<?php
/**
 * Handles events for routines(tasks and threads).
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

/**
 * 
 * @remark	The term routine is used to refer to the both 'thread' and 'task'. 
 * @filter	apply	task_scheduler_filter_next_run_time_{occurrence slug}	Applies to the next run time of the task.
 * @action	add		task_scheduler_action_before_calling_routine			Called when a routine is about to be called.
 * @action	add		task_scheduler_action_cancel_routine					Called when a routine is canceled.
 * @action	add		task_scheduler_action_before_doing_routine				Called before a routine action gets triggered.
 * @action	add		task_scheduler_action_do_routine						Called when a routine action needs to be performed.
 * @action	add		task_scheduler_action_after_doing_routine				Called after a routine action is performed.
 * @action	do		task_scheduler_action_after_doing_{task_or_thread}		Executed when a routine is finished.
 * @action	do		task_scheduler_action_after_doing_routine_of_occurrence_{occurrence slug}		Executed when a task is finished.
 */
class TaskScheduler_Event_Routine {
		
	/**
	 * Sets up hooks and properties.
	 */
	public function __construct() {

		add_action( 'task_scheduler_action_before_calling_routine',	array( $this, '_replyToSpawnRoutine' ) );
		add_action( 'task_scheduler_action_cancel_routine', 		array( $this, '_replyToCancelRoutine' ) );
		add_action( 'task_scheduler_action_before_doing_routine', 	array( $this, '_replyToDoBeforeRoutine' ) );
		add_action( 'task_scheduler_action_do_routine', 			array( $this, '_replyToDoRoutine' ), 10, 2 ); // set a higher priority as it creates a log line.
		add_action( 'task_scheduler_action_after_doing_routine',	array( $this, '_replyToCompleteRoutine' ) );
		
	}

	/**
	 * Called when the task is about to be spawned.
	 */
	public function _replyToSpawnRoutine( $oRoutine ) {
		
		if ( ! is_object( $oRoutine ) ) { return; }
		$oRoutine->deleteMeta( '_eixt_code' );
		$_sPreviousTaskStatus = $oRoutine->_routine_status;
		
		$_iMaxTaskExecutionTime = ( int ) $oRoutine->_max_execution_time;
		$_nCurrentMicroTime = microtime( true );
		
		// Store the previous task status in a transient. This is used to cancel a routine.
		$_sLoadID = TaskScheduler_Registry::TransientPrefix . md5( $_nCurrentMicroTime );
		set_transient( $_sLoadID, $_sPreviousTaskStatus, $_iMaxTaskExecutionTime ? $_iMaxTaskExecutionTime : 30 );	// avoid setting 0 for the expiration duration.
		
		$oRoutine->setMeta( '_routine_status', 'awaiting' );	// the 'Force Execution' task option will ignore this status if enabled. Otherwise, it is used to determine scheduled routines.
		$oRoutine->setMeta( '_is_spawned',		true );			// used to determine scheduled routines
		$oRoutine->setMeta( '_spawned_time',	$_nCurrentMicroTime );	// used to cancel the routine and to detect the hung 
		$oRoutine->setMeta( '_count_call',	$oRoutine->getMeta( '_count_call' ) + 1 );
		
TaskScheduler_Debug::log( 'spawned a task: ' . $oRoutine->ID );		
		if ( $oRoutine->isTask() ) {
			
			// Pass the spawned time and the thread will compare the passed spawned time and the currently set spawned time 
			// to identify the dealing task is the one that needs to be taken cared of as there is a possibility that forced execution 
			// spawns multiple instances of routines.
TaskScheduler_Debug::log( 'adding a hung handler thread: ' . $oRoutine->post_title );					
			do_action( 'task_scheduler_action_add_hung_routine_handler_thread', $oRoutine );
			
		}
		
	}

	
	/**
	 * Gets triggered when a routine is canceled.
	 */
	public function _replyToCancelRoutine( $oRoutine ) {
	
		// Check the previous task status.
		$_nSpawnedMicrotime		= $oRoutine->getMeta( '_spawned_time' );
		$_sTransientKey			= TaskScheduler_Registry::TransientPrefix . md5( $_nSpawnedMicrotime );
		$_sPreviousTaskStatus	= get_transient( $_sTransientKey );
		if ( $_sPreviousTaskStatus ) {
			$oRoutine->setMeta( '_routine_status', $_sPreviousTaskStatus );
TaskScheduler_Debug::log( 'Reverted the task status to ' . $_sPreviousTaskStatus );					
		}
		$oRoutine->deleteMeta( '_is_spawned' );
		delete_transient( $_sTransientKey );
		
	}
	
	/**
	 * Do some preparation before stating the routine.
	 */
	public function _replyToDoBeforeRoutine( $oRoutine ) {

		$oRoutine->setMeta( '_routine_status',	'processing' );
		$oRoutine->setMeta( '_last_run_time',	microtime( true ) );
		$oRoutine->deleteMeta( '_is_spawned' );	
	
	}

	/**
	 * Executes the action of the task.
	 * 
	 * @remark	For convenience, the action of the task is called 'action' to imply it performs an action that needs for the task.
	 * However, technically speaking, it is performed as a WordPress filter to get the exit code to be returned.
	 */
	public function _replyToDoRoutine( $oRoutine, $nNextScheduledTime=0 )	 {

		// Do actions.		
		$_bIsThread = $oRoutine->isThread();
		$_sThreadOrTask = $_bIsThread ? 'thread' : 'task';
		
		// Log
		$_iLogID = $oRoutine->log( $this->_getLogText( $oRoutine ), 0, true ); 
				
		do_action( "task_scheduler_action_before_doing_{$_sThreadOrTask}" , $oRoutine );

		// Update the next scheduled time only if this is an automated execution. If the user chose "Run Now", do not update it.
		// Do this after the above 'task_scheduler_action_before_doing_{...}' action because the _last_run_time meta needs to be updated to calculate the next run time for some occurrence types.
		// Also, the next scheduled time needs to be set before the task is executed because the task may hang.
		if ( ! $nNextScheduledTime ) {			
			$oRoutine->setMeta( '_next_run_time',	apply_filters( "task_scheduler_filter_next_run_time_{$oRoutine->occurrence}", '', $oRoutine ) );
		}		
		
		// Do the action and get the return code - note that _exit_code is not updated yet in the db here. The update will be taken cared of in the callback of the following hook.
		$oRoutine->_exit_code = apply_filters( 
			$oRoutine->routine_action, 	// the action name 
			null, 		// the exit code - null is given 
			$oRoutine 	// the routine object
		);
			
		// Do after-treatment - note that the exit code matters and changes the behavior of the callback functions of the followings.
		do_action( "task_scheduler_action_after_doing_{$_sThreadOrTask}", $oRoutine, $oRoutine->_exit_code );
		do_action( "task_scheduler_action_after_doing_routine_of_occurrence_{$oRoutine->occurrence}", $oRoutine, $oRoutine->_exit_code );	// for the Volatile occurrence type	
						
	}	
		/**
		 * Returns log text to indicate the routine is starting.
		 */	
		private function _getLogText( $oRoutine ) {
			
			$_aLogs = array();
			$_aLogs[] = $oRoutine->isThread() 
				? __( 'Starting the thread.', 'task-scheduler' ) 
				: __( 'Starting the task.', 'task-scheduler' );
			$_aLogs[] = __( 'ID', 'task-scheduler' ) . ': ' . $oRoutine->ID;
			$_aLogs[] = __( 'Action', 'task-scheduler' ) . ': ' . apply_filters( "task_scheduler_filter_label_action_{$oRoutine->routine_action}", $oRoutine->routine_action );
			return $_aLogs;
		
		}
	/**
	 * Deals with cleaning up the routine.
	 */
	public function _replyToCompleteRoutine( $oRoutine ) {
		
		$oRoutine->setMeta( '_count_run',	$oRoutine->_count_run + 1 );
		
		// Clean the previous status transient
		$_nSpawnedMicrotime	= $oRoutine->getMeta( '_spawned_time' );
		$_sTransientKey		= TaskScheduler_Registry::TransientPrefix . md5( $_nSpawnedMicrotime );		
		delete_transient( $_sTransientKey );
		
		// Do not delete the hung routine detector here because there are types tasks that keeps the routine status to be 'processing' 
		// while the child tasks or threads are running.
		
	}
	
}