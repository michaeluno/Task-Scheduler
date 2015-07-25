<?php
/**
 * An abstract class that defines the columns of the Task Scheduler task listing table.
 *
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michel Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0 
*/

abstract class TaskScheduler_ListTable_Column extends TaskScheduler_ListTable_Action {
            
    public function get_columns() {
        
        return array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'name'      => __( 'Task Name', 'task-scheduler' ),
            'details'   => __( 'Details', 'task-scheduler' ),
            'status'    => __( 'Status', 'task-scheduler' ),
            // 'status' => __( 'Status', 'task-scheduler' ) . ' / ' . __( 'Count', 'task-scheduler' ),
            // 'time'   => __( 'Time', 'task-scheduler' ),
            'last_run'  => __( 'Last Run', 'task-scheduler' ),
            'next_run'  => __( 'Next Run', 'task-scheduler' ),
        );
        
    }
    public function get_sortable_columns() {
        return array(
            'last_run' => array( '_last_run_time', false ),    // true means it's already sorted
            'next_run' => array( '_next_run_time', false ),
        );
    }
    
    /**
     * 
     * @remark      'column_' + 'default'
     */
    public function column_default( $aItem, $sColumnName ) {    
        switch( $sColumnName ){      
            default:
                // return print_r( $aItem, true ); //Show the whole array for troubleshooting purposes
        }    
    }
    
    /**
     * 
     * @remark      column_ + cb
     */
    public function column_cb( $oRoutine ){   
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  
            /*$2%s*/ $oRoutine->ID                //The value of the checkbox should be the record's id
        );
    }
    
    /**
     * 
     * @remark      column_ + 'name'
     */
    public function column_name( $oRoutine ) {    
                            
        // Build row action links
        $_aActions = array(
            'edit'      => sprintf( '<a href="%s">' . __( 'Edit', 'task-scheduler' ) . '</a>', get_edit_post_link( $oRoutine->ID, true ) ),
            'enable'    => sprintf( '<a href="%s">' . __( 'Enable', 'task-scheduler' ) . '</a>', $this->getQueryURL( array( 'action' => 'enable', 'task_scheduler_task' => $oRoutine->ID, 'task_scheduler_nonce' => $this->sNonce ) ) ),
            'disable'   => sprintf( '<a href="%s">' . __( 'Disable', 'task-scheduler' ) . '</a>', $this->getQueryURL( array( 'action' => 'disable', 'task_scheduler_task' => $oRoutine->ID, 'task_scheduler_nonce' => $this->sNonce ) ) ),
            'delete'    => sprintf( '<a href="%s">' . __( 'Delete', 'task-scheduler' ) . '</a>', $this->getQueryURL( array( 'action' => 'delete', 'task_scheduler_task' => $oRoutine->ID, 'task_scheduler_nonce' => $this->sNonce ) ) ),
            'view'      => sprintf( 
                '<a href="%s" rel="permalink" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $oRoutine->post_title ) ) . '">' 
                    . __( 'View', 'task-scheduler' ) 
                . '</a>', 
                get_permalink( $oRoutine->ID )  
            ),            
            'run'       =>    sprintf( '<a href="%s">' . __( 'Run Now', 'task-scheduler' ) . '</a>', add_query_arg( array( 'action' => 'run', 'task_scheduler_task' => $oRoutine->ID, 'task_scheduler_nonce' => $this->sNonce ) ) ),
        );
        if ( isset( $_GET['status'] ) && 'disabled' == $_GET['status'] ) {
            unset( $_aActions['disable'], $_aActions['run'] );
        }
        if ( ! isset( $_GET['status'] ) || 'enabled' == $_GET['status'] ) {    // the default Enabled view
            unset( $_aActions['enable'], $_aActions['delete']  );
        }
        if ( isset( $_GET['status'] ) && in_array( $_GET['status'], array( 'system', 'thread' ) ) ) {
            unset( $_aActions['edit'], $_aActions['view'] );    
        }
        if ( $oRoutine->isEnabled() ) {
            unset( $_aActions['enable'] );    
        } else {
            unset( $_aActions['disable'] );    
        }
        
        $_aIDBox = array( 
            "<p class='routine-id-container'><span class='id-label'>" . __( 'ID', 'task-scheduler' ) . ":</span><span class='routine-id'>{$oRoutine->ID}</span></p>",
            $oRoutine->isThread() 
                ? "<p class='routine-id-container'><span class='id-label'>" . __( 'Owner', 'task-scheduler' ) . ":</span><span class='routine-id'>{$oRoutine->owner_routine_id}</span></p>"
                : ''
        );
        
        return "<div class='task-listing-table id-label-container'>" . implode( PHP_EOL, $_aIDBox ).  "</div>" 
            . "<p class='post-title page-title column-title'><strong>" . $oRoutine->post_title . "</strong></p>"
            . $this->row_actions( $_aActions );
            
    }
    
    /**
     * 
     */
    public function column_details( $oRoutine ) {    
        
        $_sOccurrenceLabel    = apply_filters( "task_scheduler_filter_label_occurrence_{$oRoutine->occurrence}", $oRoutine->occurrence );
        $_sActionLabel        = apply_filters( "task_scheduler_filter_label_action_{$oRoutine->routine_action}", $oRoutine->routine_action );
        return "<p class='description'>" . $oRoutine->post_excerpt . "</p>"
            . "<p><span class='description-label'>" . __( 'Occurrence', 'task-scheduler' ) . ":</span> " . $_sOccurrenceLabel . "</p>"
            . "<p><span class='description-label'>" . __( 'Action', 'task-scheduler' ) . ":</span> " . $_sActionLabel . "</p>"
            ;        
            
    }    
    
    public function column_status( $oRoutine ) {
    
        $_sStatusLabel = $this->_getStatusLabel( $oRoutine->_routine_status );
        $_iThreads     = $oRoutine->getThreadCount();
        $_sHasThreads  = $_iThreads 
            ? sprintf( __( '%1$s threads', 'task-scheduler' ), $_iThreads )
            : __( 'No thread', 'task-scheduler' );
        $_sElapsedTime = in_array( $oRoutine->_routine_status, array( 'processing', 'awaiting' ) )
            ? $this->_getReadableElapsedTime( $oRoutine )
            : '';
        return "<p title='" . esc_attr( __( 'Status', 'task-scheduler' ) ) . "'>{$_sStatusLabel}</p>"
            . "<p title='" . esc_attr( __( 'Threads', 'task-scheduler' ) ) . "'>{$_sHasThreads}</p>"
            . ( $_sElapsedTime ? "<p title='" . esc_attr( __( 'Elapsed Time', 'task-scheduler' ) ) . "'> " . sprintf( __( 'Elapsed %1$s', 'task-scheduler' ), $_sElapsedTime ) . "</p>" : '' );

    }
        private function _getReadableElapsedTime( $oRoutine ) {
            
            if ( ! $oRoutine->_spawned_time ) {
                return __( 'n/a', 'task-scheduler' );
            }
            
            $_iElapsedSeconds = time() - ( int ) $oRoutine->_spawned_time;
            if ( $_iElapsedSeconds < 60 ) {
                return sprintf( __( '%1$s seconds', 'task-scheduler' ), $_iElapsedSeconds );
            }
            
            return human_time_diff( ( int ) $oRoutine->_spawned_time, time() );
            
        }
        private function _getStatusLabel( $sStatus ) {
                
            switch ( $sStatus ) {
                case '':
                case false:
                case null:
                    return __( 'n/a', 'task-scheduler' );
                case 'queued':
                    return __( 'Queued', 'task-scheduler' );
                case 'ready':
                    return __( 'Ready', 'task-scheduler' );
                case 'processing':
                    return __( 'Processing', 'task-scheduler' );                    
                case 'awaiting':
                    return __( 'Awaiting', 'task-scheduler' );                    
                default:
                    return ucwords( $sStatus );
                break;
            }            
            
        }
    
    public function column_last_run( $oRoutine ) {        
        
        $_sExitCountDescription = __( 'Indicates how many times a valid exit code has been returned.', 'task-scheduler' );
        $_sExitCodeDescription  = __( 'Indicates the last exit code returned from the action.', 'task-scheduler' );
        $_aOutput               = array();
        $_aOutput[]             = "<p>" 
                . $oRoutine->getReadableTime( $oRoutine->_last_run_time, 'Y/m/d G:i:s', true )
            . "</p>";
        if ( $oRoutine->isTask() ) {
            $_aOutput[] = "<p title='" . esc_attr( $_sExitCountDescription ) . "'>"
                    . "<span class='description-label'>" . __( 'Exit Count', 'task-scheduler' ) . ":</span>"
                    . ( $oRoutine->_count_exit ? $oRoutine->_count_exit : 0 )
                . "</p>";            
        }
        if ( $oRoutine->_count_hung ) {
            $_aOutput[] = "<p>"
                    . "<span class='description-label'>" . __( 'Hung Count', 'task-scheduler' ) . ":</span>"
                    . $oRoutine->_count_hung
                . "</p>";                        
        }
        return implode( PHP_EOL, $_aOutput );
            
    }
    public function column_next_run( $oRoutine ) {
        
        $_sCallCountDescription   = __( 'Indicates how many times the action has been called.', 'task-scheduler' );
        $_sRunCountDescription    = __( 'Indicates how many times the action has run.', 'task-scheduler' )
            . ' &#10;' . __( 'This does not necessarily mean the action did the expected job.', 'task-scheduler' );
        return "<p>" 
                . $oRoutine->getReadableTime( $oRoutine->_next_run_time, 'Y/m/d G:i:s', true )
            . "</p>"
            . "<p title='" . esc_attr( $_sCallCountDescription ) . "'>"
                . "<span class='description-label'>" . __( 'Call Count', 'task-scheduler' ) . ":</span>"
                . ( $oRoutine->_count_call ? $oRoutine->_count_call : 0 )
            . "</p>"
            . "<p title='" . esc_attr( $_sRunCountDescription ) . "'>"
                . "<span class='description-label'>" . __( 'Run Count', 'task-scheduler' ) . ":</span>"
                . ( $oRoutine->_count_run ? $oRoutine->_count_run : 0 )
            . "</p>";    
            
    }    
        
}