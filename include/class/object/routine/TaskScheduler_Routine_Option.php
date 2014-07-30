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

/**
 * Provides methods which deal with plugin options.
 * 
 */
abstract class TaskScheduler_Routine_Option extends TaskScheduler_Routine_Base {
	

	
	/**
	 * Returns a time in a readable format.
	 */
	public function getReadableTime( $nTimestamp, $sDateTimeFormat='Y/m/d G:i:s', $bfAdjustGMT=false ) {
		return TaskScheduler_WPUtility::getSiteReadableDate( $nTimestamp, $sDateTimeFormat, $bfAdjustGMT );
	}
		
}
