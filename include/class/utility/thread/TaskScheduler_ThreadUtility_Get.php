<?php
/**
 * One of the abstract parent classes of the TaskScheduler_ThreadUtility class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_ThreadUtility_Get extends TaskScheduler_ThreadUtility_Edit {
	
	/**
	 * Retrieves tasks with the given criteria.
	 * 
	 * @param	array|string	$asStatuses	pass the task status from the followings:
	 * - queued
	 * - started
	 * - completed
	 * - lost
	 * 
	 * @param	boolean		$bInternal		Indicates whether or not the retrieving tasks are all internal.
	 * @return	object			The WP Query return object holding the result. To retrieve the post ids call the 'posts' element like $oResult->posts.
	 */
	static public function find( array $aArgs=array() ) {
				
		// Construct the query argument array.
		$_aArgs = $aArgs + array(
			'post_type'			=>	TaskScheduler_Registry::PostType_Thread,
			'post_status'		=>	array( 'publish', 'private' ),
			'posts_per_page' 	=>	-1,	// -1 for all			
            'orderby'			=>	'date ID',		// another option: 'ID',	
			'order'				=>	'DESC', // DESC: the newest comes first, 'ASC' : the oldest comes first
			'fields'			=>	'ids',	// return only post IDs
		);

		$_oResults = new WP_Query( $_aArgs );
// TaskScheduler_Debug::log( $_aArgs );
// TaskScheduler_Debug::log( $_oResults->posts );
		return $_oResults;		
		// return $_oResults->posts;		
		
	}		
		
	/**
	 * Returns the threaded tasks.
	 * 
	 * @remark		If the method is called in a timing that the custom taxonomy has not been registered, the taxonomy query won't take effect.
	 * @param		integer	$iOwnerTaskID		The owner task ID
	 * @param		boolean	$bExcludeInternals	Whether or not the internal threads should be counted.
	 * @return		object	The WP_Query object that holds the results.
	 */
	static public function getThreadsByOwnerID( $iOwnerTaskID, $bExcludeInternals=true ) {

		$_aArgs = array(
			'meta_query'	=>	array(	
				array(
					'key'		=>	'owner_routine_id',
					'value'		=>	$iOwnerTaskID,
				),											
			),				
			'tax_query' => array(
				array(	// exclude the internal threads
					'taxonomy'	=>	TaskScheduler_Registry::Taxonomy_SystemLabel,
					'field'		=>	'slug',
					'terms'		=>	array( 'internal' ),
					'operator'	=>	'NOT IN'			
				)
			),
		);	
		if ( ! $bExcludeInternals ) {
			unset( $_aArgs['tax_query'] );
		}
		return self::find( $_aArgs );
		
	}	
	
}

