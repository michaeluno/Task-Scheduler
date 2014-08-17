<?php
/**
 * One of the abstract classes of the TaskScheduler_Routine class.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_Routine_Log extends TaskScheduler_Routine_Meta {

    /**
     * Checks wither the task accepts logs.
     * 
     * @return    boolean    Whether or not a log can be added to the task.
     */
    public function canLog( $iTargetTaskID=null ) {
        
        if ( $iTargetTaskID == $this->ID || is_null( $iTargetTaskID ) ) {
            return $this->_max_root_log_count ? true : false;
        }
        
        $_oTargetTask = TaskScheduler_Routine::getInstance( $iTargetTaskID );
        return is_object( $_oTargetTask ) ? ( $_oTargetTask->_max_root_log_count ) : false;
                
    }
        
    /**
     * Leaves a log in the task.
     * 
     * @return    integer    The log id. 0 if failed.
     * 
     * @param    array|string   $asLog         The log text to add. If an array is passed, it will be joined and converted to text.
     * @param    integer        $iParentLogID  The parent log ID to add the new log to.
     * @param    boolean        $bUpdateMeta   Whether or not the meta 'log_id' should be updated. This meta key should hold the currently executing routine's root log id.
     */
    public function log( $asLog, $iParentLogID=0, $bUpdateMeta=false ) {
        
        // If this object is a routine or thread, it wants to leave logs to the owner task.
        // So, check if the routine/thread and the owner task accept logs.
        $_iTargetTaskID = $iParentLogID ? get_post_meta( $iParentLogID, '_routine_id', true ) : $this->ID;
        if ( ! $this->canLog( $this->ID ) ) {
            return 0;
        }
        if ( ! $this->canLog( $_iTargetTaskID ) ) {
            return 0;
        }
        
        // If no parent log is given and if this object is a thread or a routine, 
        if ( ! $iParentLogID && ! $this->isTask() ) {
            $iParentLogID = $this->parent_routine_log_id ? $this->parent_routine_log_id : 0;
        }
    
        $_iLogID = TaskScheduler_LogUtility::log( $iParentLogID ? $iParentLogID : $this->ID , $asLog );
        if ( $bUpdateMeta ) {
            $this->setMeta( 'log_id', $_iLogID );
        }
        return $_iLogID;
        
    }
    
    /**
     * Returns the count of the log entries of the task.
     */
    public function getLogCount() {
        
        return count( $this->getLogIDs() );
    }
    
    /**
     * Returns the logs associated with this task.
     * 
     * @return    array    holding the IDs of of found log entries.
     */
    public function getLogIDs() {
        
        return TaskScheduler_LogUtility::getLogIDs( $this->ID );
        
    }
    
    /**
     * Returns the count of the root log entries of the task.
     */
    public function getRootLogCount() {
        
        return count( $this->getRootLogIDs() );
    }
    
    /**
     * Returns the 
     * 
     * @return    array    holding the IDs of found log entries.
     */
    public function getRootLogIDs() {
        
        return TaskScheduler_LogUtility::getRootLogIDs( $this->ID );
        
    }
        
}