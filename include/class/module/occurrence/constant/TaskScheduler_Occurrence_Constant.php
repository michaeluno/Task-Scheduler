<?php
/**
 * Handles hooks for the 'volatile' occurrence option.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

/**
 * Defines the constant occurrence type.
 * 
 * This is internal and used for internal threaded child tasks.
 * 
 */
class TaskScheduler_Occurrence_Constant extends TaskScheduler_Occurrence_Base {
		
	/**
	 * The user constructor.
	 */
	public function construct() {}
	
	/**
	 * Returns the label for the slug.
	 */
	public function getLabel( $sSlug ) {
		return __( 'Constant', 'task-scheduler' );
	}			
	
	/**
	 * Returns the description of the module.
	 */
	public function getDescription( $sDescription ) {
		return __( 'Triggers actions constantly, used by system internal routines.', 'task-scheduler' );
	}			
	
	/**
	 * Deletes stored tasks.
	 */
	public function doAfterAction( $oTask, $isExitCode ) {}
	
	/**
	 * Returns the next run time time-stamp.
	 * 
	 * The constant occurrence type persistently schedules the task with the current type.
	 * So the task action has to have its mechanism to delete the task by itself.
	 */ 
	public function getNextRunTime( $iTimestamp, $oTask ) {
		return microtime( true ) + 3;	// give at least 3 seconds of interval
	}	
	
}