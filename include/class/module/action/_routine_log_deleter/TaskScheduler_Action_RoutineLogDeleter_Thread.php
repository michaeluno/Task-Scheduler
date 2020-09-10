<?php
/**
 * The class that defines the behavior of threads of the Delete Task Log action for the Task Scheduler plugin.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * The thread class of the routine log deleter task class.
 * 
 */
class TaskScheduler_Action_RoutineLogDeleter_Thread extends TaskScheduler_Action_Base {

    protected $sSlug = 'task_scheduler_action_delete_each_task_log';

    /**
     * The user constructor.
     * 
     * This method is automatically called at the end of the class constructor.
     */
    public function construct() {}
    
    /**
     * Returns the readable label of this action.
     * 
     * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
     */    
    public function getLabel( $sLabel ) {
        return __( 'Thread of Deleting Task Log ', 'task-scheduler' );
    }
    
    /**
     * Defines the behavior of the action.
     */
    public function doAction( $isExitCode, $oThread ) {
                                            
        $_aThreadMeta = $oThread->getMeta();
        if ( ! $oThread->_target_task_id ) {            
            return 0;    // failed
        }                    
    
        // If the max root log count is not set, it means to delete them all.
        if ( ! $oThread->_max_root_log_count_of_the_target ) {                    
            foreach( TaskScheduler_LogUtility::getLogIDs( $oThread->_target_task_id ) as $_iLogID ) {
                if ( TaskScheduler_LogUtility::doesPostExist( $_iLogID ) ) {
                    wp_delete_post( $_iLogID, true );
                }
            }            
            return 1;
        }
        
        $_aRootLogIDs = TaskScheduler_LogUtility::getRootLogIDs( $oThread->_target_task_id );
        $_iNumberToDelete = count( $_aRootLogIDs ) - ( int ) $oThread->_max_root_log_count_of_the_target;
        if ( $_iNumberToDelete < 1 ) {
            return 1;
        }
        
        $_iDeleted = 0;
        foreach( $_aRootLogIDs as $_iIndex => $_iRootLogID ) {
            
            // Delete the root log and its children.
            $_iDeleted = $_iDeleted + TaskScheduler_LogUtility::deleteChildLogs( $_iRootLogID );
            if ( TaskScheduler_LogUtility::doesPostExist( $_iRootLogID ) ) {
                $_vDelete   = wp_delete_post( $_iRootLogID, true );                        
                $_iDeleted  = $_vDelete ? $_iDeleted + 1 : $_iDeleted;
            }

            if ( $_iIndex + 1 >= $_iNumberToDelete ) {
                break;
            }
        }            
        return 1;
    }
        
}