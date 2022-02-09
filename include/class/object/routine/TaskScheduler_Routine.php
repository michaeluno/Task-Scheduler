<?php
/**
 * Creates a task object.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 * Creates a task/routine/thread object.
 * 
 * @remark    Do not use the constructor. Use the getInstance() method to instantiate an task object.
 */
final class TaskScheduler_Routine extends TaskScheduler_Routine_Taxonomy {
            
    /**
     * Returns a task object instance.
     * 
     * This is a modified version of the get_instance() method of WP_Post.
     * 
     * @see         wp-includes/post.php
     * @param       integer $iPostID
     * @return      TaskScheduler_Routine|boolean
     */
    static public function getInstance( $iPostID ) {
        
        global $wpdb;
        
        $_sClassName = get_class();
        $iPostID = ( int ) $iPostID;
        if ( ! $iPostID ) {  
            return false; 
        }
        
        $_oPost = wp_cache_get( $iPostID, 'posts' );
        if ( ! $_oPost ) {
            $_oPost = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1", $iPostID ) );
            if ( ! $_oPost ) { return false; }
            $_oPost = sanitize_post( $_oPost, 'raw' );
            wp_cache_add( $_oPost->ID, $_oPost, 'posts' );
            
        } elseif ( empty( $_oPost->filter ) ) {
            $_oPost = sanitize_post( $_oPost, 'raw' );
        }
        return new $_sClassName( $_oPost );
        
    }
        
    /**
     * Sets the next scheduled time.
     * 
     * @return    integer    the set time stamp; 0 if fails.
     */ 
    public function setNextRunTime( $iTimeStamp=null ) {
    
        if ( ! $this->occurrence ) { 
            return 0; 
        }
            
        $iTimeStamp = apply_filters( 
            "task_scheduler_filter_next_run_time_{$this->occurrence}", 
            $iTimeStamp 
                ? $iTimeStamp 
                : $this->_next_run_time, 
            $this 
        );
        $this->setMeta( '_next_run_time', $iTimeStamp );        
        return $iTimeStamp;
        
    }    
    
    /**
     * Deletes the task.
     */
    public function delete() {
        return wp_delete_post( $this->ID, true );    // true: force delete, false : trash
    }
        
    /**
     * Start the task.
     * Called from the task listing table page with the `Run Now` action link.
     * @return      void
     */
    public function start( $nTargetTime=null, $bForce=false ) {
        do_action( 
            'task_scheduler_action_spawn_routine', 
            $this->ID, 
            $nTargetTime ? $nTargetTime : microtime( true ),   // scheduled time time
            false,   // whether to update the next run time
            $bForce
        );
    }
    
    /**
     * Resets the counts
     */
    public function resetCounts() {
        
        $this->setMeta( '_count_run', 0 );
        $this->setMeta( '_count_exit', 0 );
        $this->setMeta( '_count_call', 0 );
        $this->setMeta( '_count_hung', 0 );        
        
    }
    
    /**
     * Resets the routine status
     */
    public function resetStatus() {
        
        $this->setMeta( '_routine_status', 'ready' );
        $this->deleteMeta( '_is_spawned' );        
        $this->deleteMeta( '_spawned_time' );        
        
    }
    
    /**
     * Enables the task.
     */
    public function enable() {
        TaskScheduler_RoutineUtility::enable( $this->ID );
    }
    
    /**
     * Disables the task
     */
    public function disable() {
        TaskScheduler_RoutineUtility::disable( $this->ID );
    }
    
    /**
     * Checks if the task is enabled
     */
    public function isEnabled() {
        return ( in_array( $this->post_status, array( 'publish', 'private' ), true ) );
    }
    
    /**
     * Checks whether the object is a task.
     * @return    boolean    True if it is a task.
     */
    public function isTask() {
        return ( TaskScheduler_Registry::$aPostTypes[ 'task' ] === $this->post_type );
    }
    
    /**
     * Checks whether the object is a routine.
     */
    public function isRoutine() {
        return ( TaskScheduler_Registry::$aPostTypes[ 'routine' ] === $this->post_type );
    }

    /**
     * Returns the type whether it is a task, routine, or thread.
     */
    public function getType() {
        switch( $this->post_type ) {
            case TaskScheduler_Registry::$aPostTypes[ 'routine' ]:
                return 'routine';
            case TaskScheduler_Registry::$aPostTypes[ 'thread' ]:
                return 'thread';
            case TaskScheduler_Registry::$aPostTypes[ 'task' ]:
                return 'task';                
            default:
                return '';
        }
    }
    /**
     * Checks if the task has threads.
     * 
     * @remark    For tasks.
     * @return    boolean Whether it has threads or not.
     */
    public function hasThreads() {
        return TaskScheduler_RoutineUtility::hasThreads( $this->ID );    
    }

    /**
     * Returns the owning thread count.
     * 
     * @remark    For tasks.
     * @return    integer    The count of owning threads.
     */    
    public function getThreadCount() {
        return TaskScheduler_RoutineUtility::getThreadCount( $this->ID );
    }
    
    /**
     * Returns the parent task ID.
     * 
     * @remark    For tasks.
     * @return    integer    The parent task ID.
     */
    public function getParentID() {
        // @todo: complete this    
    }
    
    /**
     * Returns the parent task object instance.
     * 
     * @return    object|false    
     */
    public function getParent() {
        // @todo: complete this
    }
    
    /*
     * Magic Methods
     */
    public function __get( $sName ) {
        
        if ( metadata_exists( 'post', $this->ID, $sName ) ) {
            $this->$sName = get_post_meta( $this->ID, $sName, true );
            return $this->$sName;
        }
        
        // At this point, the meta key does not exist. Since the parent __get() method will return false if not key is set,
        // Specify keys that should not return false but null.
        if ( in_array( $sName, array( '_exit_code', '_is_spawned', '_spawned_time' ) ) ) {
            $this->$sName = null;
            return null;
        }
        if ( in_array( $sName, array( 'parent_routine_log_id', '_count_exit', '_count_run', '_count_call', '_count_hung' ) ) ) {
            $this->$sName = 0;
            return 0;
        }
        
        return parent::__get( $sName );
        
    }
    
    // public function __set( $sName, $vValue ) {
        
        // if ( metadata_exists( 'post', $this->ID, $sName ) ) {
            // update_post_meta( $this->ID, $sName, $vValue );
        // }
        
        // parent::__set( $sName, $vValue );
    // }
    
    
}