<?php
/**
 * One of the abstract parent classes of the TaskScheduler_TaskUtility class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_TaskUtility_Add extends TaskScheduler_TaskUtility_Get {

    /**
     * Represents the default meta key-values of the task post type posts.
     *
     */
    static public $aDefaultMeta = array(
    
        // Required internals
        '_routine_status'        =>    null,    // string    ready, awaiting, processing
        '_next_run_time'         =>    null,    // float
        '_count_call'            =>    0,        // integer - represents the count that the task is called (triggered)
        '_count_exit'            =>    0,        // integer - represents the count that the task returned an exit code (indicated completion).
        '_count_run'             =>    0,        // integer - represents the count that the task has been executed. (which does not mean the task did what the user expects but it did finish running)
        '_count_hung'            =>    0,        // integer - represents the count that the task got hung.
        
        // Required
        'routine_action'         =>    null,    // string    the target action hook name
        'argument'               =>    null,    // array    the arguments(parameters) passed to the action
        'occurrence'             =>    null,    // string    The slug of the occurrence type.
        
        // Not sure why the underscore is not pre-fixed <-- maybe when creating a thread, this is needed and to third party developers need to know this.
        'log_id'                 =>    null,    // integer    the last set log id.
        
        // Advanced options - they also have a prefix of a underscore to prevent conflicts with third-party extensions.
        '_max_root_log_count'    =>    0,
// TODO: assign a server set PHP max execution time here.
        '_max_execution_time'    =>    null,    // integer    whenever retrieve this value, assign the server set maximum execution time.
        
    );    
        
    /**
     * Creates a task with the given options.
     * 
     * The difference with the add() method is that it can be canceled if the same task already exists.
     * This is used to create an internal task that should not add a new one if the same task already exists.
     * Note that it does not check taxonomy terms. Only the post title and the meta keys-values.
     * 
     * @since    1.0.0
     */
    static public function create( array $aTaskOptions, array $aSystemTaxonomyTerms=array(), $bAllowDuplicate=false ) {
        
        if ( ! $bAllowDuplicate && self::hasSameRoutine( $aTaskOptions, array( __CLASS__, 'find' ) ) ) {
            return 0;
        }

        return self::add( $aTaskOptions, $aSystemTaxonomyTerms, $bAllowDuplicate );
        
    }

    /**
     * Adds a task with the given options.
     * 
     * For required keys, see the above $aDefaultMeta property.
     */
    static public function add( array $aTaskOptions, array $aSystemTaxonomyTerms=array() ) {
        
        // the uniteArrays() will override the null elements 
        $aTaskOptions = self::uniteArrays( 
            array(
                '_count_call'       => 0,
                '_count_exit'       => 0,
                '_count_run'        => 0,
                // 'post_parent'       =>    $iParentTaskID,    //<-- this used to be always 0 but we are going to allow child tasks in near future.
            ),
            $aTaskOptions    // since the method is performed recursively, but the 'tax_input' should not be recursively merged, use the union operator. 
            + array(
                'post_status'          => 'private',
                '_routine_status'      => 'ready',
                'tax_input'            => array( 
                    TaskScheduler_Registry::Taxonomy_SystemLabel => $aSystemTaxonomyTerms
                ),    
            ) 
            + self::$aDefaultMeta
        );
        unset( 
            $aTaskOptions['_is_spawned'],          // if this is set, the action will not be loaded
            $aTaskOptions['_last_run_time'],       // a new task should not have a last executed time
            $aTaskOptions['_exit_code']            // a new task should not have an exit code
        );
        
        // This allows a custom post type to be passed.
        $_sPostType = isset( $aTaskOptions['post_type'] ) ? $aTaskOptions['post_type'] : TaskScheduler_Registry::PostType_Task;
        unset( $aTaskOptions['post_type'] );
        $_iTaskID   = self::insertPost( $aTaskOptions, $_sPostType );
        
        // Add terms because the 'tax_input' argument does not take effect for some reasons when multiple terms are set.
         $_aSystemInternalTerms = isset( $aTaskOptions['tax_input'][ TaskScheduler_Registry::Taxonomy_SystemLabel ] )
            ? $aTaskOptions['tax_input'][ TaskScheduler_Registry::Taxonomy_SystemLabel ]
            : array();
        if ( ! empty( $_aSystemInternalTerms ) ) {
            wp_set_object_terms( $_iTaskID, $_aSystemInternalTerms, TaskScheduler_Registry::Taxonomy_SystemLabel, true );
        } 

        return $_iTaskID;
                    
    }
         
}