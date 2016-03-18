<?php
/**
 * Provides methods to clone posts.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 */

 /**
  * Clones a task.
  * 
  * Used by the post listing table action links.
  * 
  * @since      1.2.0
  */
class TaskScheduler_CloneTask extends TaskScheduler_ClonePost {


    /**
     * Performs clone.
     * 
     * @return      integer|WP_Error         The newly created post ID.
     */
    public function perform() {
        
        $_ioNewTaskID = parent::perform();
        
        if ( is_wp_error( $_ioNewTaskID ) ) {
            return $_ioNewTaskID;
        }

        // Reset counts and status.
        $_oTask = TaskScheduler_Routine::getInstance( $_ioNewTaskID );
        $_oTask->resetCounts();        
        $_oTask->resetStatus();

        return $_ioNewTaskID;
        
    }
    
}