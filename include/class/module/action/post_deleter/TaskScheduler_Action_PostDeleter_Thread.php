<?php
/**
 * The class that defines the action of Delete Posts for the Task Scheduler plugin.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

 /**
  * Creates a thread that deletes posts that matches the user set argument.
  * 
  */
class TaskScheduler_Action_PostDeleter_Thread extends TaskScheduler_Action_Base {
        
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
        return __( 'Deleting Posts', 'task-scheduler' );
    }
            
    /**
     * Deletes posts.
     * 
     * Return 'NOT_DELETE' not to delete the thread to continue, otherwise, to delete the thread.
     */
    public function doAction( $isExitCode, $oThread ) {
        
        $_oTask = $oThread->getOwner();
        if ( ! is_object( $_oTask ) ) { return 1; }

        $_aThreadMeta = $oThread->getMeta();
        if ( 
            ! isset( 
                $_aThreadMeta[ 'post_type_of_deleting_posts' ],
                $_aThreadMeta[ 'post_statuses_of_deleting_posts' ],
                $_aThreadMeta[ 'taxonomy_of_deleting_posts' ],
                $_aThreadMeta[ 'term_ids_of_deleting_posts' ]
            )
        ) {
            $_oTask->log( 'Required keys are missing.', $oThread->parent_routine_log_id );
            return 1;    // failed
        }

        // Process up to 20 posts by default.
        $_iNumberOfPostsToDelete = isset( $_aThreadMeta[ 'number_of_posts_to_delete_per_routine' ] )
            ? $_aThreadMeta[ 'number_of_posts_to_delete_per_routine' ]
            : 20;
            
        $_aPostIDsToDelete = $this->_getPostIDs(
            $_aThreadMeta[ 'post_type_of_deleting_posts' ],
            $_aThreadMeta[ 'post_statuses_of_deleting_posts' ],
            $_aThreadMeta[ 'taxonomy_of_deleting_posts' ],
            $_aThreadMeta[ 'term_ids_of_deleting_posts' ],
            $_iNumberOfPostsToDelete + 1    
        );
        
        // If not found, finish the task.
        if ( ! count( $_aPostIDsToDelete ) ) {
            $_oTask->log( 'No more post IDs found to to delete.', $oThread->parent_routine_log_id );
            return 1;
        }
        
        // Divide the found posts by 20 - this should creates two chunks, the first part to delete and the rest to check if there are remained posts.
        $_aChunks_PostIDs = array_chunk( $_aPostIDsToDelete, $_iNumberOfPostsToDelete );
        $_bHasRemain      = isset( $_aChunks_PostIDs[ 1 ] ) && 0 < count( $_aChunks_PostIDs[ 1 ] );
        
        // Do delete posts - we are going to delete up to 20 items to prevent exhausting the PHP max execution time.
        $_aDeleted = array();
        foreach( $_aChunks_PostIDs[ 0 ] as $_iPostID ) {
            if ( wp_delete_post( $_iPostID, true ) ) {
                $_aDeleted[] = $_iPostID;
            }
        }
        if ( ! empty( $_aDeleted ) ) {
            $_oTask->log( 'Deleted the posts: ' . implode( ', ', $_aDeleted ), $oThread->parent_routine_log_id );
        } 
        
        // When deleting Task Scheduler logs (as they are a custom post type), this routine itself adds logs so in the next call it will find the log entries again.
        // That causes infinite recursion. To prevent that, check the remaining number of posts here and if there is no more, exit the task.
        if ( ! $_bHasRemain ) {
            $_oTask->log( 'Deleted all the posts.', $oThread->parent_routine_log_id );
            return 1;    
        }
        
        // Keep continuing. The system will not delete the thread if 'NOT_DELETE' is passed as the exit code.
        do_action( 'task_scheduler_action_check_shceduled_actions' );
        $oThread->setMeta( '_next_run_time', microtime( true ) );
        $oThread->setMeta( '_routine_status','queued' );
        return 'NOT_DELETE';
        
    }
        
        /**
         * Returns an array of post IDs 
         */
        private function _getPostIDs( $asPostType, $asPostStatus, $sTaxonomy=-1, $aTerms=array(), $iLimit=200 ) {
            
            // Construct the query argument array.
            $_aQueryArgs = array(
                'post_type'      => $asPostType,
                'post_status'    => array_keys( array_filter( $asPostStatus ) ),
                'posts_per_page' => $iLimit,   // -1 for all            
                'orderby'        => 'date ID', // another option: 'ID',    
                'order'          => 'ASC', // 'DESC': the newest comes first, 'ASC': the oldest comes first
                'fields'         => 'ids', // return only post IDs by default.
            );
            if ( $sTaxonomy && '-1' !== ( string ) $sTaxonomy && ! empty( $aTerms ) ) {
                $_aQueryArgs['tax_query'] = array(
                    'relation'     => 'AND',
                    array(
                        'taxonomy' => $sTaxonomy,
                        'field'    => 'term_id',
                        'terms'    => array_keys( array_filter( $aTerms ) ),
                    ),
                );                            
            }

            $_oResults = new WP_Query( $_aQueryArgs );
            return $_oResults->posts;        
                        
        }
        
}