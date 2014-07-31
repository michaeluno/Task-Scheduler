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
 * Defines the volatile occurrence type.
 * 
 * This is internal and used for internal threaded child tasks.
 * 
 */
class TaskScheduler_Occurrence_Volatile extends TaskScheduler_Occurrence_Base {
		
	/**
	 * The user constructor.
	 */
	public function construct() {}
	
	/**
	 * Returns the label for the slug.
	 */
	public function getLabel( $sSlug ) {
		return __( 'Volatile', 'task-scheduler' );
	}			
	
	/**
	 * Deletes stored tasks.
	 */
	public function doAfterAction( $oRoutine, $sExitCode ) {

		if ( 'NOT_DELETE' === $sExitCode ) { 	
			return; 
		}	
		$vRet = $oRoutine->delete();
		
	}	
	
}