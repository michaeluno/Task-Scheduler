<?php
/**
 * Provides the task management system.
 *
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 *
 * @since   1.5.0
 */
class TaskScheduler_Event_Action_DeleteThreads extends TaskScheduler_Event_Action_Base {

    protected $_sActionHookName = 'task_scheduler_action_delete_threads';

    protected function _doAction() {
        $_aParams  = func_get_args();
        $_aThreads = $this->getElementAsArray( $_aParams, array( 0 ) );
        foreach( $_aThreads as $_iThreadID ) {
            wp_delete_post( $_iThreadID, true );    // true: force delete, false : trash
        }
    }

}