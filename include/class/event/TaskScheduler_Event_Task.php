<?php
/**
 * Handles events for tasks.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

/**
 * 
 * @action	add		task_scheduler_action_before_doing_task	
 * @action	add		task_scheduler_action_after_doing_task			Called when a task completes
 */
class TaskScheduler_Event_Task {
		
	/**
	 * Sets up hooks and properties.
	 */
	public function __construct() {

		add_action( 'task_scheduler_action_before_doing_task', array( $this, '_replyToStartTask' ), 1 ); // set a higher priority as it sets a log
		add_action( 'task_scheduler_action_after_doing_task', array( $this, '_replyToCompleteTask' ), 10, 2 );	
		
	}

	/**
	 * Do some updates of task before staring it.
	 */
	public function _replyToStartTask( $oTask ) {}
	
	/**
	 * Do some updates of task after finishing it.
	 */
	public function _replyToCompleteTask( $oTask, $sExitCode ) {
		
		$_bHasThreads = $oTask->hasThreads();
	
		// Update the task options (post meta)			
		if ( null !== $sExitCode ) {
			$oTask->setMeta( '_exit_code', $sExitCode );
			$oTask->setMeta( '_count_exit',	( int ) $oTask->getMeta( '_count_exit' ) + 1 );
		}		
		if ( ! $_bHasThreads ) {
			$oTask->setMeta( '_routine_status', 'inactive' );
			$oTask->deleteMeta( '_spawned_time' );
		}
		
		// Leave some log items.
		$oTask->log( $this->_getLogText( $_bHasThreads, $sExitCode ), $oTask->log_id );
		
		// Check the number of logs and if exceeded, create a task to remove them.
		if ( $oTask->getRootLogCount() > ( int ) $oTask->_max_root_log_count ) {
			do_action( 'task_scheduler_action_add_log_deletion_task', $oTask );
		}
		
		// If the next scheduled time is very close, check the actions in the background.
		$_iHeartbeatInterval = ( int ) TaskScheduler_Option::get( array( 'server_heartbeat', 'interval' ) );
		$_nSum	= microtime( true ) + $_iHeartbeatInterval;
		if ( $_nSum > $oTask->_next_run_time ) {
			TaskScheduler_ServerHeartbeat::beat();
		}
		
	}
		
		/**
		 * Returns log text to indicate the task completion. 
		 */
		private function _getLogText( $bHasThreads, $sExitCode ) {
				
			$_aLog = array();
			if ( $bHasThreads ) {
				
				$_aLog[] = __( 'Still have threaded tasks.', 'task-scheduler' );
				$_sLog[] = null !== $sExitCode
					? ' ' . __( 'Exit Code', 'task-scheduler' ) . ': ' . $sExitCode
					: '';
				return $_aLog;		
				
			} 
		
			$_aLog[] = null !== $sExitCode
				? __( 'Completed the task.', 'task-scheduler' ) . ' ' . __( 'Exit Code', 'task-scheduler' ) . ': ' . $sExitCode
				: __( 'The task did not return an exit code.', 'task-scheduler' );
			return $_aLog;
					
			
			
		}
	
}
