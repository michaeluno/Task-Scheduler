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

abstract class TaskScheduler_LogUtility_Add extends TaskScheduler_LogUtility_Base {

    /**
     * Appends a log to the specified task(post).
     * @return integer    the created log id.
     */
    static public function log( $iTaskOrLogID, $asLog ) {
        
        if ( ! $iTaskOrLogID ) { return 0; }
        
        $_sLog = is_array( $asLog ) ? implode( ' ', $asLog ) : $asLog;
        return TaskScheduler_Registry::$aPostTypes[ 'log' ] == get_post_type( $iTaskOrLogID )
            ? self::logByParentLogID( $iTaskOrLogID, $_sLog )
            : self::logByTaskID( $iTaskOrLogID, $_sLog );
        
    }    
        /**
         * Creates a log to the top level associated with the given task.
         * 
         */
        static private function logByTaskID( $iTaskID, $sLog ) {            
            
            return self::_insertLog( 
                array(
                    'post_content'        =>    $sLog,
                    'post_excerpt'        =>    $sLog,                
                    'post_status'        =>    'private',
                    'post_title'        =>    self::_getExcerpt( $sLog ),
                    'post_parent'        =>    $iTaskID,    // works even with a different post type as a parent. This is for wp_list_page                
                    '_routine_id'        =>    $iTaskID,
                ) 
            );
        }    
        /**
         * Creates a log under the specified parent log.
         */
        static private function logByParentLogID( $iParentLogID, $sLog ) {
            
            return self::_insertLog( 
                array(
                    'post_content'        =>    $sLog,
                    'post_excerpt'        =>    $sLog,
                    'post_status'        =>    'private',
                    'post_title'        =>    self::_getExcerpt( $sLog ),
                    'post_parent'        =>    $iParentLogID,
                    '_routine_id'            =>    get_post_meta( $iParentLogID, '_routine_id', true ),
                ) 
            );
            
        }
            /**
             * Creates a log under the specified parent log.
             */
            static private function _insertLog( array $aPostArgs ) {
                    
                $_iLogID = self::insertPost( $aPostArgs, TaskScheduler_Registry::$aPostTypes[ 'log' ] );
                
                // Set the post slug as it seems that the same post slug is assigned to different log posts.
                wp_update_post( 
                    array(
                        'ID'        =>    $_iLogID,
                        'post_name'    =>    'task_scheduler_log_' . $_iLogID // the slug
                    ) 
                );            
                return $_iLogID;
                
            }
                    
            /**
             * Returns shortened text.
             */
            static private function _getExcerpt( $sText, $iMaxChars=250 ) {
                
                $_sSubstr = function_exists( 'mb_substr' ) ? 'mb_substr' : 'substr';
                $_sStrlen = function_exists( 'mb_strlen' ) ? 'mb_strlen' : 'strlen';
                
                return $_sSubstr( $sText, 0, $iMaxChars ) . ( $_sStrlen( $sText ) > $iMaxChars ? '...' : '' );            
                
            }    
}