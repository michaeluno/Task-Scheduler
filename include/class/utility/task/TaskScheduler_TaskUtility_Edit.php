<?php
/**
 * One of the abstract parent classes of the TaskScheduler_TaskUtility class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_TaskUtility_Edit extends TaskScheduler_TaskUtility_Base {
    
    /**
     * Enables the task.
     * 
     * It changes the post status to 'private' from 'pending'.
     */
    static public function enable( $iTaskID ) {
        
        wp_update_post( 
            array(
                'ID'            =>    $iTaskID,
                'post_status'   =>    'private',
            ) 
        );        
        update_post_meta( $iTaskID, '_routine_status', 'ready' );
        
    }
    
    /**
     * Disables the task.
     * 
     * It changes the post status to 'pending' from 'private'.
     */
    static public function disable( $iTaskID ) {
        
        wp_update_post( 
            array(
                'ID'            =>    $iTaskID,
                'post_status'   =>    'pending',
            ) 
        );        
        update_post_meta( $iTaskID, '_routine_status', 'ready' );
        
    }
        
}