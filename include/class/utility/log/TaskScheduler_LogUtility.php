<?php
/**
 * Provides methods to manage tasks.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

final class TaskScheduler_LogUtility extends TaskScheduler_LogUtility_Get {
    
    /**
     * Deletes child logs.
     * 
     * @return    integer    The count of deletion.
     */
    static public function deleteChildLogs( $iRootLogID ) {
        
        $_iDeleted = 0;
        foreach( self::getChildLogIDs( $iRootLogID ) as $_iChildLogID ) {
            
            // Once it happened that an array is passed 
            if ( ! is_numeric( $_iChildLogID ) ) {        
                continue;
            }
            
            if ( self::doesPostExist( $_iChildLogID ) ) {     // simultaneous page loads can have deleted it
                $_vReturn = wp_delete_post( $_iChildLogID, true );
                $_iDeleted = $_vReturn ? ++$_iDeleted : $_iDeleted;
            }
            
        }
        return $_iDeleted;
    
    }    
    
}