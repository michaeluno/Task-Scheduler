<?php
/**
 * A class that handles logs.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

class TaskScheduler_Log extends TaskScheduler_Routine_Log {
		
	/**
	 * Returns a task object instance.
	 * 
	 * This is a modified version of the get_instance() method of WP_Post.
	 * 
	 * @see		wp-includes/post.php
	 */
	static public function getInstance( $iPostID ) {
		
		global $wpdb;
		
		$_sClassName = get_class();
		$iPostID = ( int ) $iPostID;
		if ( ! $iPostID ) {  return false; }
		
		$_oPost = wp_cache_get( $iPostID, 'posts' );
		if ( ! $_oPost ) {
			$_oPost = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1", $iPostID ) );
			if ( ! $_oPost ) { return false; }
			$_oPost = sanitize_post( $_oPost, 'raw' );
			wp_cache_add( $_oPost->ID, $_oPost, 'posts' );
			
		} elseif ( empty( $_oPost->filter ) ) {
			$_oPost = sanitize_post( $_oPost, 'raw' );
		}
		return new $_sClassName( $_oPost );
		
	}
	
	
	
}
