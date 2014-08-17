<?php
/**
 * One of the abstract parent classes of the TaskScheduler_TaskUtility class.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_TaskUtility_Get extends TaskScheduler_TaskUtility_Edit {

    /**
     * Retrieves tasks with the given criteria.
     * 
     * @param   array|string    $asStatuses pass the task status from the followings:
     * - queued
     * - started
     * - completed
     * - lost
     * 
     * @param   boolean         $bInternal  Indicates whether or not the retrieving tasks are all internal.
     * @return  object          The WP Query return object holding the result. To retrieve the post ids call the 'posts' element like $oResult->posts.
     */
    static public function find( array $aArgs=array() ) {
                
        // Construct the query argument array.
        $_aArgs = $aArgs + array(
            'post_type'          =>    TaskScheduler_Registry::PostType_Task,
            'post_status'        =>    array( 'publish', 'private' ),    // means 'Enabled'
            'posts_per_page'     =>    -1,    // -1 for all            
            'orderby'            =>    'date ID',        // another option: 'ID',    
            'order'                =>    'DESC', // DESC: the newest comes first, 'ASC' : the oldest comes first
            'fields'            =>    'ids',    // return only post IDs by default.
        );

        $_oResults = new WP_Query( $_aArgs );
        return $_oResults;        
        
    }    
    
    
    /**
     * Checks whether the given task has a threaded task that still running.
     * 
     * @todo    Maybe later at some point, supporting some status criteria like if the threads are running or not.
     * @return  boolean        Whether it has a thread task or not. 
     */
    static public function hasThreads( $iTaskID, $bExcludeInternals=true ) {        
        
        return ( self::getThreadCount( $iTaskID, $bExcludeInternals ) > 0 );
        
    }
    
    /**
     * Returns the count of threads of the given task.
     * 
     * @return        integer        The count of threads.
     */
    static public function getThreadCount( $iTaskID, $bExcludeInternals=true ) {
        
        return TaskScheduler_ThreadUtility::getThreadsByOwnerID( $iTaskID, $bExcludeInternals )->found_posts;
        
    }        
    
}

