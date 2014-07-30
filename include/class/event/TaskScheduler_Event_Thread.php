<?php
/**
 * Handles events for threads.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

/**
 * 
 * @action	do		task_scheduler_action_before_doing_thread
 * @action	add		task_scheduler_action_after_doing_thread	Called when a thread finishes.
 */
class TaskScheduler_Event_Thread {
		
	/**
	 * Sets up hooks and properties.
	 */
	public function __construct() {

		add_action( 'task_scheduler_action_before_doing_thread', array( $this, '_replyToStartThread' ), 1 );	// set a higher priority as it sets a log	
		add_action( 'task_scheduler_action_after_doing_thread', array( $this, '_replyToCompleteThread' ), 10, 2 );
		
	}
	
	/**
	 * Do some updates of task before staring it.
	 */
	public function _replyToStartThread( $oTask ) {	}
	
	
	/**
	 * Do clean-ups when a thread completes.
	 */
	public function _replyToCompleteThread( $oThread, $isExitCode ) {
				
		if ( ! is_object( $oThread ) ) { return; }
		
		// Threads also can be constant, in that case the routine status needs to be reset. For volatile threads, they will be deleted anyway.
		$oThread->setMeta( '_routine_status', 'queued' );		
		
		// The owner task may have been deleted especially if it is a system internal task.
		$_oTask = $oThread->getOwner();
		if ( ! is_object( $_oTask ) ) { return; }
				
		// For internal threads, do not add any log.
		if ( has_term( array( 'internal' ), TaskScheduler_Registry::Taxonomy_SystemLabel, $oThread->ID ) ) { return; }
				
		$_oTask->log( sprintf( __( 'Finished the thread: %1$s', 'task-scheduler' ), $oThread->ID ), $oThread->parent_routine_log_id );
		
		// If 'NOT_DELETE' is passed to the exit code, the action wants to cancel the deletion of the thread, which means the thread will be loaded again.
		// This is assumed that all the threads are the 'volatile' occurrence type.
		if ( 'NOT_DELETE' === $isExitCode ) {
			return;
		}
		
		// If no thread is found besides this task, it means this is the last thread.
		if ( 1 < $_oTask->getThreadCount() ) {	return; }		
		
		$_oTask->setMeta( '_routine_status',	'inactive' );
		$_oTask->setMeta( '_exit_code', 	$isExitCode );
		$_oTask->setMeta( '_count_exit',	$_oTask->_count_exit + 1 );
		$oThread->deleteMeta( '_spawned_time' );
		$_oTask->log( 
			__( 'Completed all the threads.', 'task-scheduler' )
			. ' ' . sprintf( __( 'Exit Code: %1$s', 'task-scheduler' ), $isExitCode ),
			$oThread->parent_routine_log_id 
		);
			
	}
	
}