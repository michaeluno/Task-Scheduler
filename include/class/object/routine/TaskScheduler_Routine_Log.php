<?php
/**
 * One of the abstract classes of the TaskScheduler_Routine class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_Routine_Log extends TaskScheduler_Routine_Meta {

	/**
	 * Checks wither the task accepts logs.
	 * 
	 * @return	boolean	Whether or not a log can be added to the task.
	 */
	public function canLog( $iTargetTaskID=null ) {
		
		if ( $iTargetTaskID == $this->ID || is_null( $iTargetTaskID ) ) {
			return $this->_max_root_log_count ? true : false;
		}
		
		$_oTargetTask = TaskScheduler_Routine::getInstance( $iTargetTaskID );
		return is_object( $_oTargetTask ) ? ( $_oTargetTask->_max_root_log_count ) : false;
				
	}
		
	/**
	 * Leaves a log in the task/thread.
	 * 
	 * @return	integer	The log id. 0 if failed.
	 * 
	 * @param	array|string	$asLog			The log text to add. If an array is passed, it will be joined and converted to text.
	 * @param	integer			$iParentLogID	The parent log ID to add the new log to.
	 * @param	boolean			$bUpdateMeta	Whether or not the meta 'log_id' should be updated. This meta key should hold the currently executing routine's root log id.
	 */
	public function log( $asLog, $iParentLogID=0, $bUpdateMeta=false ) {
		
		// If this object is a thread (or can be a task ), it might want to log to the owner task. 
		// If so, check the given parent log ID's owner can accept a log.
		$_iTargetTaskID = ! $iParentLogID ? $this->ID : get_post_meta( $iParentLogID, '_routine_id', true );		
		if ( ! $this->canLog( $_iTargetTaskID ) ) {
TaskScheduler_Debug::log( 'cannot log' );
// TaskScheduler_Debug::log( 
	// array(
		// 'routine ID'		=> $this->ID,
		// 'post title'		=> $this->post_title,
		// 'routine type'		=> $this->isThread() ? 'Thread' : 'Task',
		// 'target task ID'	=> $_iTargetTaskID,
		// 'parent log ID'		=> $iParentLogID,
	// )
// );		
			return 0;
		}
		
		// If no parent log is given and if this object is a thread, 
		if ( ! $iParentLogID && $this->isThread() ) {
			$iParentLogID = $this->parent_routine_log_id ? $this->parent_routine_log_id : 0;
		}
TaskScheduler_Debug::log( 'Logging' );		
TaskScheduler_Debug::log( 
	array(
		'current hook'		=> current_filter(),
		'routine ID'		=> $this->ID,
		'post title'		=> $this->post_title,
		'routine type'		=> $this->isThread() ? 'Thread' : 'Task',
		'target task ID'	=> $_iTargetTaskID,
		'parent log ID'		=> $iParentLogID,
	)
);		
		$_iLogID = TaskScheduler_LogUtility::log( $iParentLogID ? $iParentLogID : $this->ID , $asLog );
		if ( $bUpdateMeta ) {
			$this->setMeta( 'log_id', $_iLogID );
		}
		return $_iLogID;
		
	}
	
	/**
	 * Returns the count of the log entries of the task.
	 */
	public function getLogCount() {
		
		return count( $this->getLogIDs() );
	}
	
	/**
	 * Returns the logs associated with this task.
	 * 
	 * @return	array	holding the IDs of of found log entries.
	 */
	public function getLogIDs() {
		
		return TaskScheduler_LogUtility::getLogIDs( $this->ID );
		
	}
	
	/**
	 * Returns the count of the root log entries of the task.
	 */
	public function getRootLogCount() {
		
		return count( $this->getRootLogIDs() );
	}
	
	/**
	 * Returns the 
	 * 
	 * @return	array	holding the IDs of found log entries.
	 */
	public function getRootLogIDs() {
		
		return TaskScheduler_LogUtility::getRootLogIDs( $this->ID );
		
	}
		
}