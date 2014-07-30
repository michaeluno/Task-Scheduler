<?php
/**
 * One of the abstract parent classes of the TaskScheduler_TaskUtility class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_TaskUtility_Add extends TaskScheduler_TaskUtility_Get {

	/**
	 * Represents the default meta key-values of the task post type posts.
	 *
	 */
	static public $aDefaultMeta = array(
	
		// Required internals
		'_routine_status'			=>	null,	// string	inactive, awaiting, processing
		'_next_run_time'		=>	null,	// float
		'_count_call'			=>	0,		// integer - represents the count that the task is called (triggered)
		'_count_exit'			=>	0,		// integer - represents the count that the task returned an exit code (indicated completion).
		'_count_run'			=>	0,		// integer - represents the count that the task has been executed. (which does not mean the task did what the user expects but it did finish running)
		'_count_hung'			=>	0,		// integer - represents the count that the task got hung.
		
		// Required
		'routine_action'		=>	null,	// string	the target action hook name
		'argument'				=>	null,	// array	the arguments(parameters) passed to the action
		'occurrence'			=>	null,	// string	The slug of the occurrence type.
		
		// Not sure why the underscore is not pre-pended <-- maybe when creating a thread, this is needed and to third party developers need to know this.
		'log_id'				=>	null,	// integer	the last set log id.
		
		// Advanced options - they also have a prefix of a underscore to prevent conflicts with third-party extensions.
		'_max_root_log_count'	=>	0,
// TODO: assign a server set PHP max execution time here.
		'_max_execution_time'	=>	null,	// integer	whenever retrieve this value, assign the server set maximum execution time.
		
	);	
		
	/**
	 * Creates a task with the given options.
	 * 
	 * The difference with the add() method is that it can be canceled if the same task already exists.
	 * This is used to create an internal task that should not add a new one if the same task already exists.
	 * Note that it does not check taxonomy terms. Only the post title and the meta keys-values.
	 * 
	 * @since	1.0.0
	 */
	static public function create( array $aTaskOptions, array $aSystemTaxonomyTerms=array(), $bAllowDuplicate=false ) {
		
		if ( ! $bAllowDuplicate && self::hasSameTask( $aTaskOptions, $aSystemTaxonomyTerms ) ) {
TaskScheduler_Debug::log( 'the same task already exists.' );
TaskScheduler_Debug::log( $aTaskOptions );
			return 0;
		}

		return self::add( $aTaskOptions, $aSystemTaxonomyTerms, $bAllowDuplicate );
		
	}
		static private function hasSameTask( $aTaskOptions, $aSystemTaxonomyTerms ) {
				
			$_aTaskMeta = $aTaskOptions;
				
			// The top level arguments
			$_aCheckingTopLevelArguments = array(
				'post_title',	//     => [ <string> ] // The title of your post.
			);
			// The following change dynamically so don't check
			$_aNotCheckingTopLevelArguments = array(
				'ID'             , // [ <post id> ] // Are you updating an existing post?
				'tax_input',	// => [ array( <taxonomy> => <array | string> ) ] // For custom taxonomies. Default empty.
				'post_type',	//     => [ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] // Default 'post'.
				'post_author',	//    => [ <user ID> ] // The user ID number of the author. Default is the current user ID.
				'post_status',	//   => [ 'draft' | 'publish' | 'pending'| 'future' | 'private' | custom registered status ] // Default 'draft'.
				'post_content'   , // [ <string> ] // The full text of the post.
				'post_name'      , // [ <string> ] // The name (slug) for your post			
				'post_parent'    , // [ <post ID> ] // Sets the parent of the new post, if any. Default 0.
				'menu_order'     , // [ <order> ] // If new post is a page, sets the order in which it should appear in supported menus. Default 0.
				'ping_status',	//    => [ 'closed' | 'open' ] // Pingbacks or trackbacks allowed. Default is the option 'default_ping_status'.
				'to_ping'        , // // Space or carriage return-separated list of URLs to ping. Default empty string.
				'pinged'         , // // Space or carriage return-separated list of URLs that have been pinged. Default empty string.
				'post_password'  , // [ <string> ] // Password for post, if any. Default empty string.
				'guid'           , // // Skip this and let Wordpress handle it, usually.
				'post_content_filtered' , // // Skip this and let Wordpress handle it, usually.
				'post_excerpt'   , // [ <string> ] // For all your post excerpt needs.
				'post_date'      , // [ Y-m-d H:i:s ] // The time post was made.
				'post_date_gmt'  , // [ Y-m-d H:i:s ] // The time post was made, in GMT.
				'comment_status' , // [ 'closed' | 'open' ] // Default is the option 'default_comment_status', or 'closed'.
				'post_category'  , // [ array(<category id>, ...) ] // Default empty.
				'tags_input'     , // [ '<tag>, <tag>, ...' | array ] // Default empty.			
				'page_template'  , // [ <string> ] // Requires name of template file, eg template.php. Default empty.
			);
			$_aQueryArguments = array( );
			// Extract the top level argument elements and remvoe them from the meta array.
			foreach ( $_aCheckingTopLevelArguments as $_sCheckingKey ) {
				if ( isset( $_aTaskMeta[ $_sCheckingKey ] ) ) {
					$_aQueryArguments[ $_sCheckingKey ] = $_aTaskMeta[ $_sCheckingKey ];
					unset( $_aTaskMeta[ $_sCheckingKey ] );
				}
			}
			// Remove the unchecking items from the meta array. 
			foreach ( $_aNotCheckingTopLevelArguments as $_sCheckingKey ) {
				unset( $_aTaskMeta[ $_sCheckingKey ] );
			}
			// Now $_aTaskMeta contains the meta keys. Drop values that are not string, integer, nor boolean.
			foreach ( $_aTaskMeta as $_sMetaKey => $_vValue ) {
				if ( is_string( $_vValue ) || is_numeric( $_vValue ) || is_bool( $_vValue ) ) {
					continue;
				}
				unset( $_aTaskMeta[ $_sMetaKey ] );
			}
					
			// Compose the 'meta_query' argument element.
			$_aMetaQuery = array( 'relation' =>	'AND' );
			foreach ( $_aTaskMeta as $_sKey => $_vValue ) {
				$_aMetaQuery[] = array(
					'key'	=>	$_sKey,
					'value'	=>	$_vValue,
					'compare'	=>	'IN',				
				);
			}
			$_aQueryArguments[ 'meta_query' ] = $_aMetaQuery;
			$_aQueryArguments[ 'post_status ' ] = 'any';
			$_aQueryArguments[ 'posts_per_page' ] = 1;
			 
			$_aResults = self::find( $_aQueryArguments );
			return $_aResults->found_posts ? true : false;
			
		}
	/**
	 * Adds a task with the given options.
	 * 
	 * Fore required keys, see the above $aDefaultMeta property.
	 */
	static public function add( array $aTaskOptions, array $aSystemTaxonomyTerms=array() ) {
		
		// the uniteArrays() will override the null elements 
		$aTaskOptions = self::uniteArrays( 
			array(
				'_count_call'		=>	0,
				'_count_exit'		=>	0,
				'_count_run'		=>	0,
				// 'post_parent'		=>	$iParentTaskID,	//<-- this used to be always 0 but we are going to allow child tasks in near future.
			),
			$aTaskOptions	// since the method is performed recursively, but the 'tax_input' should not be recursively merged, use the union operator. 
			+ array(
				'post_status'		=>	'private',
				'_routine_status'		=>	'inactive',
				'tax_input'    		=>	array( 
					TaskScheduler_Registry::Taxonomy_SystemLabel => $aSystemTaxonomyTerms
				),	
			) 
		);
		unset( 
			$aTaskOptions['_is_spawned'], 		// if this is set, the action will not be loaded
			$aTaskOptions['_last_run_time'],	// a new task should not have a last executed time
			$aTaskOptions['_exit_code']			// a new task should not have an exit code
		);
		
		$_iTaskID = self::insertPost( $aTaskOptions, TaskScheduler_Registry::PostType_Task );
		
		// Add terms because the 'tax_input' argument does not take effect for some reasons when multiple terms are set.
 		$_aSystemInternalTerms = isset( $aTaskOptions['tax_input'][ TaskScheduler_Registry::Taxonomy_SystemLabel ] )
			? $aTaskOptions['tax_input'][ TaskScheduler_Registry::Taxonomy_SystemLabel ]
			: array();
		if ( ! empty( $_aSystemInternalTerms ) ) {
			wp_set_object_terms( $_iTaskID, $_aSystemInternalTerms, TaskScheduler_Registry::Taxonomy_SystemLabel, true );
		} 
// TaskScheduler_Debug::log( $aTaskOptions );		
		return $_iTaskID;
					
	}
		
}