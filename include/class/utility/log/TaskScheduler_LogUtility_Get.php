<?php
/**
 * One of the abstract parent classes of the TaskScheduler_LogUtility class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_LogUtility_Get extends TaskScheduler_LogUtility_Add {
    
    /**
     * Retrieves logs with the given criteria.
     * 
     * @param    array|string    $aArgs    WP_Query argument
     * @param    boolean            $bInternal        Indicates whether or not the retrieving tasks are all internal.
     * @return    object            The WP Query return object holding the result. To retrieve the post ids call the 'posts' element like $oResult->posts.
     */
    static public function find( array $aArgs=array() ) {
                
        // Construct the query argument array.
        $_aArgs = $aArgs + array(
            'post_type'            =>    TaskScheduler_Registry::$aPostTypes[ 'log' ],
            'post_status'        =>    array( 'publish', 'private' ),
            'posts_per_page'     =>    -1,    // -1 for all            
            'orderby'            =>    'date ID',        // another option: 'ID',    
            'order'                =>    'DESC', // DESC: the newest comes first, 'ASC' : the oldest comes first
            'fields'            =>    'ids',    // return only post IDs
        );

        $_oResults = new WP_Query( $_aArgs );
        return $_oResults;        
        // return $_oResults->posts;        
        
    }    
        
    /**
     * Returns the child logs.
     * 
     * @return    array    Holding the post objects.
     */
    static public function getChildLogs( $iRootLogID ) {
        
        return get_children( 
            array(
                'post_parent' => $iRootLogID,
                'post_type' => TaskScheduler_Registry::$aPostTypes[ 'log' ],            
            )
        );        

    }
    
    /**
     * Returns the child logs.
     * 
     * @return    array    Holding the IDs of logs.
     */    
    static public function getChildLogIDs( $iRootLogID ) {
        
        $_aResults = get_children( 
            array(
                'post_parent' => $iRootLogID,
                'post_type' => TaskScheduler_Registry::$aPostTypes[ 'log' ],        
            ),
            'ARRAY_A'
        );        
        return array_values( $_aResults );        
        
    }
    
    /**
     * Returns the root logs associated with the task.
     * 
     * @return    array    holding the IDs of logs.
     */
    static public function getRootLogIDs( $iTaskID ) {
        
        $_aResults = self::find(
            array(
                'order'                =>    'ASC', // DESC: the newest comes first, 'ASC' : the oldest comes first
                'post_parent'    =>    $iTaskID,
            )
        );
        return array_values( $_aResults->posts );
        
    }
    
    /**
     * Returns the number of root logs. (the top level logs written to the task.)
     * 
     * Not top level logs (child logs) are created by sub-routines and those logs are not counted.
     * 
     * @return    integer    The count of the found posts.
     */
    static public function getRootLogCount( $iTaskID ) {
        
        return count( self::getRootLogIDs( $iTaskID ) );
                
    }
    
    /**
     * Returns the logs associated with the task.
     *
     * @return    array    holding the IDs of found logs.
     */
    static public function getLogIDs( $iTaskID ) {
        
        $_aResults = self::find(
            array(
                'order'                =>    'ASC', // DESC: the newest comes first, 'ASC' : the oldest comes first
                'meta_query'    =>    array(
                    array(    
                        'key'        =>    '_routine_id',
                        'value'        =>    $iTaskID,
                    ),                                    
                ),                
            )
        );
        return array_values( $_aResults->posts );
        
    }
    
    /**
     * Returns the count of logs associated with the task.
     */
    static public function getLogCount( $iTaskID ) {
        
        return count( self::getLogIDs( $iTaskID ) );
                
    }
}