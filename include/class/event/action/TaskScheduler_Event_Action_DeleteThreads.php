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
class TaskScheduler_Event_Action_DeleteThreads extends TaskScheduler_Event_Action_DeleteRoutines {

    protected $_sActionHookName = 'task_scheduler_action_delete_threads';

    /**
     * @param WP_Post $oPost
     * @return bool
     * @since   1.5.0
     */
    protected function _shouldDelete( WP_Post $oPost ) {
        return in_array(
            $oPost->post_type,
            array(
                TaskScheduler_Registry::$aPostTypes[ 'routine' ],
                TaskScheduler_Registry::$aPostTypes[ 'thread' ],  // it's possible that there is a user who creates threads from a thread.
            ),
            true
        );
    }

    /**
     * Deletes belonging threads to the routine.
     * It could be thousands of them so do it in the background.
     * @param WP_Post $oPost
     * @since   1.5.0
     */
    protected function _scheduleDeletingPosts( WP_Post $oPost ) {

        $_oWPQuery = TaskScheduler_ThreadUtility::getThreadsByOwnerID( $oPost->ID );
        if ( empty( $_oWPQuery->posts ) ) {
            return;
        }
        $_aChunks = array_chunk( $_oWPQuery->posts, 100 );
        foreach( $_aChunks as $_aChunk ) {
            $this->scheduleSingleWPCronTask( 'task_scheduler_action_delete_threads', array( $_aChunk ) );
        }
    }
    
}