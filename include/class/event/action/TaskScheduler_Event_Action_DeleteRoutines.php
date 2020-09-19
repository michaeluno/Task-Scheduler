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
class TaskScheduler_Event_Action_DeleteRoutines extends TaskScheduler_Event_Action_Base {

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
        if ( ! ( $_oPost instanceof WP_Post ) ) {
            return;
        }
        if ( ! $this->_shouldDelete( $_oPost ) ) {
            return;
        }
        $this->_scheduleDeletingPosts( $_oPost );

    }
        /**
         * Only allows the ts_task post type.
         * @param WP_Post $oPost
         * @return bool
         * @since   1.5.0
         */
        protected function _shouldDelete( WP_Post $oPost ) {
            return $oPost->post_type === TaskScheduler_Registry::$aPostTypes[ 'task' ];
        }
        /**
         * @param WP_Post $oPost
         * @since   1.5.0
         */
        protected function _scheduleDeletingPosts( WP_Post $oPost ) {
            $_aQuery = array(
                'post_type'         => array(
                    TaskScheduler_Registry::$aPostTypes[ 'routine' ],
                ),
                'meta_query'        => array(
                    array(
                        'key'       => 'owner_task_id',
                        'value'     => $oPost->ID,
                        'compare'   => '=',
                    )
                ),
            );
            $_oWPQuery = TaskScheduler_RoutineUtility::find( $_aQuery );
            if ( empty( $_oWPQuery->posts ) ) {
                return;
            }
            $_aChunks = array_chunk( $_oWPQuery->posts, 100 );
            foreach( $_aChunks as $_aChunk ) {
                $this->scheduleSingleWPCronTask( 'task_scheduler_action_delete_routines', array( $_aChunk ) );
            }

        }

    /**
     * @callback action task_scheduler_action_delete_routines
     * @return void
     */
    protected function _doAction() {
        $_aParams   = func_get_args() + array( array() );
        $_aPosts    = $this->getAsArray( $_aParams[ 0 ] );
        foreach( $_aPosts as $_iPostID ) {
            wp_delete_post( $_iPostID, true );    // true: force delete, false : trash
        }
    }

}