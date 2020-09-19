<?php
/**
 * Handles events for routine logs.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 *
 * @since 1.5.1
 */
class TaskScheduler_Event_Action_DeleteLogItems extends TaskScheduler_Event_Action_DeleteRoutines {

    protected $_sActionHookName = 'task_scheduler_action_delete_log_items';

    protected function _construct() {
        add_action( 'before_delete_post', array( $this, 'replyToScheduleLogTruncation' ) );
        add_action( 'task_scheduler_action_delete_log_items_of_task', array( $this, 'replyToTruncateLogOfTask' ) );
    }

    /**
     * @param integer $iPostID
     * @return void
     * @callback add_action before_delete_post
     */
    public function replyToScheduleLogTruncation( $iPostID ) {

        if ( ! in_array( get_post_type( $iPostID ), array( TaskScheduler_Registry::$aPostTypes[ 'task' ], TaskScheduler_Registry::$aPostTypes[ 'routine' ] ), true ) ) {
            return;
        }

        $_oProcess = TaskScheduler_Routine::getInstance( $iPostID );
        if ( $_oProcess->isTask() ) {
            $this->___scheduleForTask( $iPostID );
            return;
        }
        $this->___scheduleForRoutine( $_oProcess );

    }
        /**
         * Schedule deleting all of the log items belonging to the given task.
         * @param  integer $iTaskID  The task ID.
         * @return integer The number of scheduled items.
         */
        private function ___scheduleForTask( $iTaskID ) {

            $_iScheduled = 0;

            $_aLogIDs    = TaskScheduler_LogUtility::getLogIDs( $iTaskID );
            if ( empty( $_aLogIDs ) ) {
                return $_iScheduled;
            }

            $_aChunks    = array_chunk( $_aLogIDs, 100 );
            foreach( $_aChunks as $_aChunk ) {
                $_iResult    = ( integer ) $this->scheduleSingleWPCronTask( 'task_scheduler_action_delete_log_items', array( $_aChunk ) );
                $_iScheduled = $_iScheduled + $_iResult;
            }
            return $_iScheduled;

        }
        /**
         * Schedule truncating log items of a task that the given routine belongs to.
         * @param TaskScheduler_Routine $oRoutine
         * @return integer The number of scheduled items.
         */
        private function ___scheduleForRoutine( $oRoutine ) {

            $_oTask            = $oRoutine->getOwner();
            $_iMaxRootLogCount = ( integer ) $_oTask->_max_root_log_count;
            if ( 0 === $_iMaxRootLogCount ) {
                return $$this->___scheduleForTask( $_oTask->ID );   // delete them all
            }
            if ( $_oTask->getRootLogCount() <= $_iMaxRootLogCount ) {
                return 0;
            }
            $_iRootLogCount   = TaskScheduler_LogUtility::getRootLogCount( $_oTask->ID );
            $_iNumberToDelete = $_iRootLogCount - $_iMaxRootLogCount;
            if ( $_iNumberToDelete < 1 ) {
                return 0;
            }
            return ( integer ) $this->scheduleSingleWPCronTask( 'task_scheduler_action_delete_log_items_of_task', array( $_oTask->ID ) );

        }

    /**
     * Scheduled to be called when a routine is about to be deleted and it has some log items to truncate.
     *
     * @param integer $iTaskID The subject task ID
     * @callback add_action task_scheduler_action_delete_log_items_of_task
     */
    public function replyToTruncateLogOfTask( $iTaskID ) {

        $_oTask           = TaskScheduler_Routine::getInstance( $iTaskID );
        if ( ! ( $_oTask instanceof TaskScheduler_Routine ) ) {
            return;
        }

        $_aRootLogIDs     = TaskScheduler_LogUtility::getRootLogIDs( $iTaskID );
        $_iNumberToDelete = count( $_aRootLogIDs ) - ( integer ) $_oTask->_max_root_log_count;
        foreach( $_aRootLogIDs as $_iIndex => $_iRootLogID ) {

            TaskScheduler_LogUtility::deleteChildLogs( $_iRootLogID );
            wp_delete_post( $_iRootLogID, true );
            
            if ( $_iIndex + 1 >= $_iNumberToDelete ) {
                break;
            }
            
        }

    }

}
