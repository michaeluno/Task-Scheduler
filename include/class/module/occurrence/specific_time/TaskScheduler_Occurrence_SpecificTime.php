<?php
/**
 * Handles hooks for the 'specific_time' occurrence option.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Defines the 'specific_time' occurrence type.
 */
class TaskScheduler_Occurrence_SpecificTime extends TaskScheduler_Occurrence_Base {
        
    /**
     * The user constructor.
     */
    public function construct() {}
        
    /**
     * Returns the label for the slug.
     */
    public function getLabel( $sSlug ) {
        return __( 'Specific Time', 'task-scheduler' );
    }        
    
    /**
     * Returns the description of the module.
     */
    public function getDescription( $sDescription ) {
        return __( 'Triggers actions at the specified time.', 'task-scheduler' );
    }
        
    /**
     * Do something when the task finishes.
     */
    public function doAfterAction( $oTask, $sExitCode ) {}        
        
    /**
     * Returns the next run time time-stamp.
     * 
     * @return      integer|float|null     timestamp without GMT offset.
     */ 
    public function getNextRunTime( $iTimestamp, $oTask )    {
    
        $_aOptions = $oTask->getMeta( $this->sSlug );
        if ( ! isset( $_aOptions[ 'when' ] ) || ! is_array( $_aOptions[ 'when' ] ) ) {
            return $iTimestamp;
        }
        
        // Convert the string date input to time-stamp
        $_aSetTimes    = $this->___getDateToTimeStamps( $_aOptions[ 'when' ] );
                
        $_iCurrentTime = time();
        $_nLastRunTime = $oTask->_last_run_time
            ? $oTask->_last_run_time
            : 0;        
        
        // The time stamps are not calculated with GMT offset.
        foreach( $_aSetTimes as $_nSetFutureTime ) {
            
            // Ignore items that supposedly have already done.
            if ( $_nSetFutureTime <= $_nLastRunTime ) { 
                continue; 
            }
            if ( $_nSetFutureTime <= $_iCurrentTime ) {
                continue;
            }
            
            // Return the first found item that have not passed the last executed time.
            return $_nSetFutureTime;
            
        }
        return 0;    // will be n/a 
                
    }
        /**
         * Converts the given date-times to GMT calculated time-stamps.
         * 
         * @remark      The plugin uses non-GMT-calculated timestamps. On the other hand, the module UI form that let the user set date-time 
         * assumes the time of the GMT is calculated. So here subtracting GMT offset seconds from the current timestamp.
         * @return      array
         */        
        private function ___getDateToTimeStamps( array $aDateTimes ) {
            
            $_aTimeStamps = array();
            foreach( $aDateTimes as $_sDateTime ) {
                $_aTimeStamps[] = $this->getStringToTime( $_sDateTime ) - ( get_option( 'gmt_offset' ) * 60*60 );
            }
            asort( $_aTimeStamps );            
            return $_aTimeStamps;
            
        }
    
}