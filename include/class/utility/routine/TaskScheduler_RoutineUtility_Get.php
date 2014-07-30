<?php
/**
 * One of the abstract parent classes of the TaskScheduler_RoutineUtility class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_RoutineUtility_Get extends TaskScheduler_RoutineUtility_Edit {

	/**
	 * Retrieves tasks with the given criteria.
	 * 
	 * @param	array|string	$asStatuses	pass the task status from the followings:
	 * - queued
	 * - started
	 * - completed
	 * - lost
	 * 
	 * @param	boolean			$bInternal		Indicates whether or not the retrieving tasks are all internal.
	 * @return	object			The WP Query return object holding the result. To retrieve the post ids call the 'posts' element like $oResult->posts.
	 */
	static public function find( array $aArgs=array() ) {
				
		// Construct the query argument array.
		$_aArgs = $aArgs + array(
			'post_type'			=>	array( TaskScheduler_Registry::PostType_Task, TaskScheduler_Registry::PostType_Thread ),
			'post_status'		=>	array( 'publish', 'private' ),
			'posts_per_page' 	=>	-1,	// -1 for all			
            'orderby'			=>	'date ID',		// another option: 'ID',	
			'order'				=>	'DESC', // DESC: the newest comes first, 'ASC' : the oldest comes first
			'fields'			=>	'ids',	// return only post IDs by default.
		);

		$_oResults = new WP_Query( $_aArgs );
// TaskScheduler_Debug::log( $_aArgs );
// TaskScheduler_Debug::log( $_oResults->posts );
		return $_oResults;		
		// return $_oResults->posts;		
		
	}	
		
	/**
	 * Returns the count of processing routines.
	 */
	static public function getProcessingCount( $iLimit=-1 ) {
		
		return count( self::getProcessing( $iLimit ) );
		
	}
	
	/**
	 * Returns the array of holding routine(task/thread) IDs that are processing and awaiting.
	 * 
	 * @return		array		An array holding the found routine(post) IDs.
	 */
	static public function getProcessing( $iLimit=-1 ) {
		
		$_aArgs = array(
			'posts_per_page' 	=>	$iLimit,	// -1 for all			
			'meta_query'	=>	array(
				'relation'	=>	'AND',	// or 'OR' can be specified	
				array(	// do not select tasks of empty values of the _next_run_time key.
					'key'		=>	'_routine_status',
					'value'		=>	array( 'processing', 'awaiting' ),
					'compare'	=>	'IN',
				),							
			),
		);		
		$_oResults = self::find( $_aArgs );
// TaskScheduler_Debug::log( 'next scheduled', true );
// TaskScheduler_Debug::log( $_oResults->posts, true );
		return $_oResults->posts;			
		
	}
			
	/**
	 * Returns the array of holding routine(task/thread) IDs that have a next scheduled time.
	 * 
	 * @return		array		An array holding the found routine(post) IDs.
	 */
	static public function getScheduled( $iSecondsFromNow=30, $iLimit=-1 ) {
		
		$_aArgs = array(
			'posts_per_page' 	=>	$iLimit,	// -1 for all			
            'orderby'			=>	'meta_value_num',	// 'ID' also works
            'meta_key'			=>	'_next_run_time',
			'order'				=>	'ASC',	// the oldest comes first because this is for processing task, not listing table.
			'meta_query'	=>	array(
				'relation'	=>	'AND',
				array(	// do not select tasks of empty values of the _next_run_time key.
					'key'		=>	'_next_run_time',
					'value'		=>	array( 0, '' ),
					'compare'	=>	'NOT IN',
				),				
				array(
					'key'		=>	'_next_run_time',
					'value'		=>	microtime( true ) + $iSecondsFromNow, 
					'type'		=>	'numeric',
					'compare'	=>	'<=',
				),				
				array(
					'key'		=>	'_is_spawned',
					'value'		=>	'_', 	// for the issue #23268 see https://core.trac.wordpress.org/ticket/23268
					'compare'	=>	'NOT EXISTS',
				),								
			),
		);		
		
		$_oResults = self::find( $_aArgs );
// TaskScheduler_Debug::log( 'next scheduled', true );
// TaskScheduler_Debug::log( $_oResults->posts, true );
		return $_oResults->posts;			
		
	}
	

			
	
}

