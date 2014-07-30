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

abstract class TaskScheduler_Routine_Meta extends TaskScheduler_Routine_Option {
				
	/**
	 * Sets the given meta data.
	 */
	public function setMeta( $sKey, $vValue ) {
		
		$this->$sKey = $vValue;
		update_post_meta( $this->ID, $sKey, $vValue );
		
	}
	
	/**
	 * Deletes the meta data.
	 */
	public function deleteMeta( $sKey ) {
		unset( $this->$sKey );
		delete_post_meta( $this->ID, $sKey );
	}
	
	/**
	 * Returns the meta data of the task/thread.
	 * 
	 * @param	string	The meta key to retrieve. If it is empty, an array of meta data will be returned.
	 * @return	mixed	The stored data as a meta key. If the first parameter is empty, an array of all the associated meta data will be returned.
	 */
	public function getMeta( $sKey='' ) {
	
		if ( ! $sKey ) {
			
			$_aMeta = TaskScheduler_WPUtility::getPostMetas( $this->ID ) 
				+ ( $this->isTask() 
					? TaskScheduler_RoutineUtility::$aDefaultMeta
					: TaskScheduler_ThreadUtility::$aDefaultMeta
				);
			
			$_aMeta['_max_execution_time'] = isset( $_aMeta['_max_execution_time'] )
				? $_aMeta['_max_execution_time']
				: TaskScheduler_Utility::getServerAllowedMaxExecutionTime( 30 );
				
			foreach( $_aMeta as $_sKey => $_vValue ) {
				$this->$_sKey = $_vValue;
			}
			return $_aMeta;
			
		}
		
		$this->$sKey = get_post_meta( $this->ID, $sKey, true );
		return $this->$sKey;
		
	}

}
