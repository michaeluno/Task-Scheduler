<?php
/**
 * Handles events for routine exits.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

/**
 * 
 * @action	add	task_scheduler_action_after_doing_task
 * @action	add	task_scheduler_action_after_doing_thread
 */
class TaskScheduler_Event_Exit {
		
	public function __construct() {
		
		add_action( 'task_scheduler_action_after_doing_task', array( $this, '_replyToHandleTaskExits' ), 100, 2 );
		add_action( 'task_scheduler_action_after_doing_thread', array( $this, '_replyToHandleThreadExits' ), 100, 2 );
		
	}

	/**
	 * Called when a thread exits.
	 */
	public function _replyToHandleThreadExits( $oThread, $isExitCode ) {
		
		if ( ! is_object( $oThread ) ) { return; }
		
		// For internal threads, do not add any log.
		if ( has_term( array( 'internal' ), TaskScheduler_Registry::Taxonomy_SystemLabel, $oThread->ID ) ) { return; }		
		
		// Do what the exit code tells if it is one of the pre-defined ones.
		$this->_doExitCode( $isExitCode, $oThread );
		
		$_oTask = $oThread->getOwner();
		if ( ! is_object( $_oTask ) ) { return; }
		
		if ( 1 < $_oTask->getThreadCount() ) {	return; }
		
		$this->_doTasksOnExitCode( $isExitCode, $_oTask );	
		
	}

	/**
	 * Called when a task exits.
	 * 
	 * Retrieves tasks registered with the 'Exit Code' occurrence type and spawns the task that matches the criteria.
	 */
	public function _replyToHandleTaskExits( $oRoutine, $isExitCode ) {
						
		$this->_doExitCode( $isExitCode, $oRoutine );
						
		$this->_doTasksOnExitCode( $isExitCode, $oRoutine );
		
	}

	/**
	 * Performs pre-defined exit code commands.
	 * 
	 * Curently there is only the 'DELETE' command.
	 * 
	 */
	private function _doExitCode( $isExitCode, $oRoutine ) {
		
		if ( 'DELETE' === $isExitCode ) {
			$oRoutine->delete();	
		}
		
	}	
	
	/**
	 * Performs the tasks registered for the given exit code.
	 */
	private function _doTasksOnExitCode( $isExitCode, $oRoutine ) {
		
		$_aFoundTasks = $this->_getTasksOnExitCode( $isExitCode, $oRoutine->ID );

// TaskScheduler_Debug::log( 'exit code: ' . $isExitCode . ' routine id: ' . $oRoutine->ID );
// TaskScheduler_Debug::log( $_aFoundTasks );
		foreach( $_aFoundTasks as $_iTaskID ) {
			do_action( 'task_scheduler_action_spawn_routine', $_iTaskID, microtime( true ) );
		}		
		
	}
		private function _getTasksOnExitCode( $isExitCode, $iSubjectRoutineID ) {

			$_oResult = TaskScheduler_TaskUtility::find(
				array(
					'post__not_in'	=> array( $iSubjectRoutineID ),
					'meta_query'	=> array(
						array(
							'key'		=>	'occurrence',
							'value'		=>	'on_exit_code',
						),					
						array(
							'key'		=>	'__on_exit_code',
							'value'		=>	$isExitCode,
						),
						// It is saved like this 'a:1:{i:0;i:405;}'
						array(
							'key'		=>	'__on_exit_code_task_ids',
							'value'		=>	':' . $iSubjectRoutineID . ';',	// searches the value of a serialized array 
							'compare'	=>	'LIKE',
						),						
					)
				)
			);
			return $_oResult->posts;
			
		}
	
}
