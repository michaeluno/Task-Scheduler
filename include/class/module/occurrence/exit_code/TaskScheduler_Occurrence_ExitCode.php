<?php
/**
 * Handles hooks for the Exit Code (on_exit_code) occurrence option.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

/**
 * Defines the Exit Code (on_exit_code) occurrence type.
 */
class TaskScheduler_Occurrence_ExitCode extends TaskScheduler_Occurrence_Base {
		
	/**
	 * User constructor.
	 */
	public function construct() {}
		
	/**
	 * Returns the label for the slug.
	 */
	public function getLabel( $sSlug ) {		
		return __( 'Exit Code', 'task-scheduler' );	
	}		
	
	/**
	 * Returns the description of the module.
	 */
	public function getDescription( $sDescription ) {
		return __( 'Triggers actions when the specified exit code is received.', 'task-scheduler' );
	}		
	
	/**
	 * Do something when the task finishes.
	 */
	public function doAfterAction( $oTask, $sExitCode ) {}
		
	/**
	 * Returns the next run time time-stamp.
	 */ 
	public function getNextRunTime( $iTimeStamp, $oTask )	{
		return '';		// 0 or ''	will prevent the routine from being loaded
	}
				
}