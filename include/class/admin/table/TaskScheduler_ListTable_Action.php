<?php
/**
 * Handles actions sent to the form of the list table of Task Scheduler tasks. 
 *    
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014-2016, Michael Uno
 * @author      Michel Uno
 * @authorurl   http://michaeluno.jp
 * @since       1.0.0 
*/

if ( ! class_exists( 'WP_List_Table' ) ) { 
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TaskScheduler_ListTable_Action extends WP_List_Table {
    
    /**
     * Defines the bulk actions.
     */ 
    public function get_bulk_actions() {
        
        $_aGET = $_GET + array(
            'status' => '',
        );
        switch( $_aGET[ 'status' ] ) {
            
            default:
            case 'enabled':        
                return array(
                    'disable'       => __( 'Disable', 'task-scheduler' ),
                    'reset_status'  => __( 'Reset Status', 'task-scheduler' ),
                    'reset_counts'  => __( 'Reset Counts', 'task-scheduler' ),
                );
            
            case 'disabled':
                return array(
                    'enable'    => __( 'Enable', 'task-scheduler' ),
                    'delete'    => __( 'Delete', 'task-scheduler' ),
                );
                
            case 'routine':
                return array(
                    'delete'    => __( 'Delete', 'task-scheduler' ),
                );
                
            case 'thread':
                return array(
                    'delete'    => __( 'Delete', 'task-scheduler' ),
                );
                
        }
        
    }
    
    /**
     * Deals with the bulk actions.
     * 
     * Called from outside.
     */
    public function process_bulk_action() {
            
        // the key is defined as the singular slug and inserted in the input check boxes of the cb column.
        if ( ! isset( $_REQUEST[ 'task_scheduler_task' ], $_REQUEST[ 'task_scheduler_nonce' ] ) ) { 
            return; 
        }
            
        if ( false === $this->getNonce( $_REQUEST[ 'task_scheduler_nonce' ] ) ) {
            $this->setAdminNotice( __( 'The action has been already done or is invalid.', 'task-scheduler' ) );
            return;
        }

        $_iApplied     = 0;
        $_sAdminNotice = '';
        switch( strtolower( $this->current_action() ) ) {
            case 'enable':
                foreach( ( array ) $_REQUEST[ 'task_scheduler_task' ] as $_sTaskPostID ) {
                    $_oTask = TaskScheduler_Routine::getInstance( $_sTaskPostID );
                    $_oTask->enable();                            
                    $this->setAdminNotice( __( 'The task has been enabled.', 'task-scheduler' ), 'updated' );                    
                }
                break;            
            case 'disable':
                foreach( ( array ) $_REQUEST[ 'task_scheduler_task' ] as $_sTaskPostID ) {
                    $_oTask = TaskScheduler_Routine::getInstance( $_sTaskPostID );
                    $_oTask->disable();                
                    $this->setAdminNotice( __( 'The task has been disabled.', 'task-scheduler' ), 'updated' );                    
                }
                break;
            case 'delete':
                foreach( ( array ) $_REQUEST[ 'task_scheduler_task' ] as $_sTaskPostID ) {
                    
                    $_sKey = array_search( $_sTaskPostID, $this->aData );
                    if ( false !== $_sKey ) {
                        unset( $this->aData[ $_sKey ] );
                    }        
                    $_oTask = TaskScheduler_Routine::getInstance( $_sTaskPostID );
                    if ( is_object( $_oTask ) ) {    // sometimes the routine is already deleted by a different process
                        $_oTask->delete();
                    }
                    $this->setAdminNotice( __( 'The task has been deleted.', 'task-scheduler' ), 'updated' );
                }                
                break;
            case 'run':
                foreach( ( array ) $_REQUEST[ 'task_scheduler_task' ] as $_sTaskPostID ) {
                    $_oTask = TaskScheduler_Routine::getInstance( $_sTaskPostID );
                    $_oTask->start( microtime( true ) + $_iApplied );                    
                    $this->setAdminNotice( __( 'The task has been called.', 'task-scheduler' ), 'updated' );
                    $_iApplied++;
                }
                break;
            case 'reset_status':
                foreach( ( array ) $_REQUEST[ 'task_scheduler_task' ] as $_sTaskPostID ) {
                    $_oTask = TaskScheduler_Routine::getInstance( $_sTaskPostID );
                    $_oTask->resetStatus();
                    $_iApplied++;
                    $this->setAdminNotice( __( 'Reset the status.', 'task-scheduler' ), 'updated' );
                }                    
                break;    
            case 'reset_counts':
                foreach( ( array ) $_REQUEST[ 'task_scheduler_task' ] as $_sTaskPostID ) {
                    $_oTask = TaskScheduler_Routine::getInstance( $_sTaskPostID );
                    $_oTask->resetCounts();
                    $_iApplied++;
                    $this->setAdminNotice( __( 'Reset the counts.', 'task-scheduler' ), 'updated' );
                }                                
                break;
            case 'clone':
                foreach( ( array ) $_REQUEST[ 'task_scheduler_task' ] as $_sTaskPostID ) {
                    $_oCloneTask = new TaskScheduler_CloneTask( $_sTaskPostID );
                    $_ioTask      = $_oCloneTask->perform();
                    if ( is_wp_error( $_ioTask ) ) {
                       $this->setAdminNotice( $_ioTask->get_error_message() );
                    } else {
                        $_iApplied++;
                    }
                }
                if ( $_iApplied ) { 
                    $this->setAdminNotice( __( 'Cloned a task.', 'task-scheduler' ), 'updated' );
                }
                break;
            default:
                break;    // do nothing.
                
        }   
        
        // Remove the nonce
        $this->deleteNonce( $_REQUEST['task_scheduler_nonce'] );
        
    }
    
}