<?php
/**
 * The class that defines the Delete Task Log action for the Task Scheduler plugin.
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
class TaskScheduler_Action_RoutineLogDeleter extends TaskScheduler_Action_Base {
		
	/**
	 * The user constructor.
	 * 
	 * This method is automatically called at the end of the class constructor.
	 */
	public function construct() {
			
		new TaskScheduler_Action_RoutineLogDeleter_Thread( 'task_scheduler_action_delete_each_task_log' );
					
	}
	
	/**
	 * Returns the readable label of this action.
	 * 
	 * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
	 */
	public function getLabel( $sLabel ) {
		return __( 'Delete Task Log', 'task-scheduler' );
	}
		
	/**
	 * Defines the behavior of the action.
	 */
	public function doAction( $sExitCode, $oTask ) {
		
		$_iTargetTaskID	= $oTask->_target_routine_id;
		$_iRemain 		= $oTask->_max_root_log_count_of_the_target;
					
		// Check if the task has logs
		$_iRootLogs = TaskScheduler_LogUtility::getRootLogCount( $_iTargetTaskID );
		if ( $_iRootLogs <= $_iRemain ) {
			// Exit code: passing 'DELETE' will tell the system to delete the task.
			// This task is a system internal task and the 'constant' occurrence type is automatically set when this routine is created.
			return 'DELETE';	
		}
		
		$_aThreadOptions = array(
		
			'_next_run_time'		=>	microtime( true ),	// add an offset so that they will be loaded with a delay of a second each.
			'routine_action'			=>	'task_scheduler_action_delete_each_task_log',
			'post_title'			=>	sprintf( __( 'Thread %1$s of %2$s', 'task-scheduler' ), 1, $oTask->post_title ),
			'_max_root_log_count'	=>	0,	// disable logs of the thread itself.
			'log_id'				=>	0,	// do not inherit any parent log id 
			
			// The action specific meta keys
			'_target_routine_id'		=>	$_iTargetTaskID,
			'_max_root_log_count_of_the_target'	=>	$_iRemain,
			
		);	
		
		TaskScheduler_ThreadUtility::add( $oTask->ID, $_aThreadOptions );	// be careful not to pass the target task ID here.
		
		// Check actions in the background.
		do_action( 'task_scheduler_action_check_shceduled_actions' );
		return 1;	// exit code. Returning a value will make the task status inactive from processing.
				
	}	
				
}