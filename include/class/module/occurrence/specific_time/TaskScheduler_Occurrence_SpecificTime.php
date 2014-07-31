<?php
/**
 * Handles hooks for the 'specific_time' occurrence option.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

/**
 * Defines the 'specific_time' occurrence type.
 */
class TaskScheduler_Occurrence_SpecificTime extends TaskScheduler_Occurrence_Base {
		
	/**
	 * The user constructor.
	 */
	public function construct() {}
		
	/**
	 * Returns the label for the slug.
	 */
	public function getLabel( $sSlug ) {
		return __( 'Specific Time', 'task-scheduler' );
	}		
	
	/**
	 * Returns the description of the module.
	 */
	public function getDescription( $sDescription ) {
		return __( 'Triggers actions at the specified time.', 'task-scheduler' );
	}
		
	/**
	 * Do something when the task finishes.
	 */
	public function doAfterAction( $oTask, $sExitCode ) {}		
		
	/**
	 * Returns the next run time time-stamp.
	 */ 
	public function getNextRunTime( $iTimestamp, $oTask )	{
	
		$_aOpitons = $oTask->getMeta( $this->sSlug );
		if ( ! isset( $_aOpitons['when'] ) || ! is_array( $_aOpitons['when'] ) ) {
			return $iTimestamp;
		}
		
		// Convert the string date input to time-stamp
		$_aSetTimes = $this->_convertDateToTimeStamp( $_aOpitons['when'] );
				
		// Compare with the last run time.
		$_nLastRunTime = $oTask->_last_run_time
			? $oTask->_last_run_time
			: 0;		
		foreach( $_aSetTimes as $_nSetTime ) {
			
			// Ignore items that supposingly have already done.
			if ( $_nSetTime < $_nLastRunTime ) { continue; }
			
			// Return the first found item that have not passed the last executed time.
			return $_nSetTime;
			
		}
		
		return $iTimestamp;
		
	}
		/**
		 * Converts the given date-times to GMT calculated time-stamps.
		 */		
		private function _convertDateToTimeStamp( array $aDateTimes ) {
			
			$_aTimeStamps = array();
			foreach( $aDateTimes as $_sDateTime ) {
				$_aTimeStamps[] = strtotime( $_sDateTime ) - ( get_option( 'gmt_offset' ) * 60*60 );
			}
			asort( $_aTimeStamps );			
			return $_aTimeStamps;
			
		}
	
}