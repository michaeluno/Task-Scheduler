<?php
/**
 * One of the abstract classes of the TaskScheduler_Routine class.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_Routine_Thread extends TaskScheduler_Routine_Log {
        

    /**
     * Checks whether the object is a thread.
     */
    public function isThread() {
        return ( TaskScheduler_Registry::$aPostTypes[ 'thread' ] == $this->post_type );
    }        
    
    /**
     * Returns the owner task ID.
     * 
     * @remark    For threads.
     * @return    integer    The owner task ID.
     */
    public function getOwnerID() {
        if ( $this->isThread() ) {
            return $this->owner_routine_id;
        } 
        if ( $this->isRoutine() ) {
            return $this->owner_task_id;
        }
        return 0;
    }
    
    /**
     * Returns an task object instance of the owner.
     * 
     * @return    false|object        If failed, false; otherwise, the owner task object.
     */
    public function getOwner() {        
        return $this->isThread() || $this->isRoutine() ? TaskScheduler_Routine::getInstance( $this->getOwnerID() ) : false;
    }
    
    /**
     * Returns the owner's thread count.
     * 
     * @remark    For tasks.
     * @return    integer    The count of owning threads.
     */    
    public function getOwnerThreadCount() {        
        return $this->isThread() ? TaskScheduler_RoutineUtility::getThreadCount( $this->getOwnerID() ) : 0;
    }    
        
}
