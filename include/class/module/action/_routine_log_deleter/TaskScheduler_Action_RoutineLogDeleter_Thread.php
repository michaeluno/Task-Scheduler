<?php
/**
 * The class that defines the behavior of threads of the Delete Task Log action for the Task Scheduler plugin.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

 /**
  * The thread class of the routine log deleter task class.
  * 
  */
class TaskScheduler_Action_RoutineLogDeleter_Thread extends TaskScheduler_Action_Base {
		
	/**
	 * The user constructor.
	 * 
	 * This method is automatically called at the end of the class constructor.
	 */
	public function construct() {	
	}
	
	/**
	 * Returns the readable label of this action.
	 * 
	 * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
	 */	
	public function getLabel( $sLabel ) {
		return __( 'Thread of Deleting Task Log ', 'task-scheduler' );
	}
	
	/**
	 * Defines the behavior of the action.
	 */
	public function doAction( $isExitCode, $oThread ) {
											
		$_aThreadMeta = $oThread->getMeta();
		if ( ! $oThread->_target_routine_id ) {
TaskScheduler_Debug::log( 'failed to receive the meta data of the thread.' );				
TaskScheduler_Debug::log( $_aThreadMeta );				
			return 0;	// failed
		}					
	
TaskScheduler_Debug::log( 'Log ids' );
TaskScheduler_Debug::log( TaskScheduler_LogUtility::getLogIDs( $oThread->_target_routine_id ) );
		
		// If the max root log count is not set, it means to delete them all.
		if ( ! $oThread->_max_root_log_count_of_the_target ) {

TaskScheduler_Debug::log( 'deleting logs' );
					
			foreach( TaskScheduler_LogUtility::getLogIDs( $oThread->_target_routine_id ) as $_iLogID ) {
				if ( TaskScheduler_LogUtility::doesPostExist( $_iLogID ) ) {
					wp_delete_post( $_iLogID, true );
				}
			}			
			return 1;
		}
		
		$_aRootLogIDs = TaskScheduler_LogUtility::getRootLogIDs( $oThread->_target_routine_id );
		$_iNumberToDelete = count( $_aRootLogIDs ) - ( int ) $oThread->_max_root_log_count_of_the_target;
TaskScheduler_Debug::log( 'the number to delete: ' .  $_iNumberToDelete );
		if ( $_iNumberToDelete < 1 ) {
TaskScheduler_Debug::log( 'No need to delete logs. The number of deleting logs is ' . $_iNumberToDelete );				
			return 1;
		}
		
		foreach( $_aRootLogIDs as $_iIndex => $_iRootLogID ) {
			
			// Delete the root log and its children.
			TaskScheduler_LogUtility::deleteChildLogs( $_iRootLogID, $oThread->_target_routine_id );
			if ( TaskScheduler_LogUtility::doesPostExist( $_iRootLogID ) ) {
			
				$_vDelete = wp_delete_post( $_iRootLogID, true );
TaskScheduler_Debug::log( 'deleted ' . $_iRootLogID  );								
TaskScheduler_Debug::log( $_vDelete  );								
			}

			if ( $_iIndex + 1 >= $_iNumberToDelete ) {
				break;
			}
		}			
		return 1;
	}
		
}
