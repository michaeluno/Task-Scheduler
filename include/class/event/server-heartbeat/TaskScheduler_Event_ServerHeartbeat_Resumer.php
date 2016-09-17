<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Resumes the server heartbeat if it is dead.
 * 
 * @action        add            task_scheduler_action_check_heartbeat_hourly
 */
class TaskScheduler_Event_ServerHeartbeat_Resumer {
        
    /**
     * 
     */
    public function __construct() {
        add_action( 
            'task_scheduler_action_check_heartbeat_hourly', 
            array( $this, 'task_scheduler_action_check_heartbeat_hourly' ) 
        );
    }
    
    /**
     * Resumes the server heartbeat if it is dead.
     * @return      void
     */
    public function task_scheduler_action_check_heartbeat_hourly() {
        $this->resume();
    }
    
    /**
     * Resumes the server heartbeat.
     * 
     * @return      void
     */
    static public function resume() {

        // Check if the server heart beat is enabled
        $_bIsEnabled = TaskScheduler_Option::get( array( 'server_heartbeat', 'power' ), true );
        if ( ! $_bIsEnabled ) { 
            return; 
        }
    
        $_bIsAlive = TaskScheduler_ServerHeartbeat::isAlive();
        if ( $_bIsAlive ) { 
            return; 
        }

        TaskScheduler_ServerHeartbeat::run();
        
    }
    
}
