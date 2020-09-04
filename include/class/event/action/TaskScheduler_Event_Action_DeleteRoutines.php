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
class TaskScheduler_Event_Action_DeleteRoutines extends TaskScheduler_Event_Action_DeleteThreads {

    protected $_sActionHookName = 'task_scheduler_action_delete_routines';

    /**
     * TaskScheduler_Event_Action_DeleteThreads constructor.
     */
    protected function _construct() {
        add_action( 'before_delete_post', array( $this, 'replyToDeletePosts' ), 10, 1 );
    }

    /**
     * Called when a post is delete.
     * @param $iPostID
     * @callback    action after_delete_post
     */
    public function replyToDeletePosts( $iPostID ) {

        $_oPost = get_post( $iPostID );
        if ( $_oPost->post_type !== TaskScheduler_Registry::$aPostTypes[ 'task' ] ) {
            return;
        }
        $_aQuery = array(
            'post_type'         => array(
                TaskScheduler_Registry::$aPostTypes[ 'routine' ],
            ),
            'meta_query'        => array(
                array(
                    'key'       => 'owner_task_id',
                    'value'     => $iPostID,
                    'compare' => '=',
                )
            ),
        );
        $_oWPQuery = TaskScheduler_RoutineUtility::find( $_aQuery );
        if ( ! empty( $_oWPQuery->posts ) ) {
            wp_schedule_single_event( time(), 'task_scheduler_action_delete_routines', array( $_oWPQuery->posts ) );
            if ( ! $this->hasBeenCalled( 'check_wp_cron' ) ) {
                TaskScheduler_ServerHeartbeat::loadPage( '', array(), 'beat' );
            }
        }

    }

    /**
     * @callback action task_scheduler_action_delete_routines
     */
    protected function _doAction() {
        $_aParams   = func_get_args();
        $_aRoutines = $this->getElementAsArray( $_aParams, array( 0 ) );
        foreach( $_aRoutines as $_iRoutineID ) {
            $_oRoutine = TaskScheduler_Routine::getInstance( $_iRoutineID );
            $_oRoutine->delete();
        }
    }

}