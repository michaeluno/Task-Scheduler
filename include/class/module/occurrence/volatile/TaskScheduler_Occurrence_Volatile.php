<?php
/**
 * Handles hooks for the 'volatile' occurrence option.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
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
     * Returns the description of the module.
     */
    public function getDescription( $sDescription ) {
        return __( 'Triggers actions only once, used mostly by threads.', 'task-scheduler' );
    }        
        
    /**
     * Deletes stored tasks.
     */
    public function doAfterAction( $oRoutine, $sExitCode ) {

        if ( 'NOT_DELETE' === $sExitCode ) {     
            return; 
        }    
        
        // If the routine status is not changed, it means the routine wants to wait for threads to complete. In that case, the routine should be kept yet.
        if ( 'processing' === $oRoutine->_routine_status ) {
            return;
        }
        
        $vRet = $oRoutine->delete();
        
    }    
    
}