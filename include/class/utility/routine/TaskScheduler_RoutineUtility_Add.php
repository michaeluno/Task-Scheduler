<?php
/**
 * One of the abstract parent classes of the TaskScheduler_RoutineUtility class.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_RoutineUtility_Add extends TaskScheduler_RoutineUtility_Get {
    
     /**
     * Represents the default meta key-values of the routine post type posts.
     *
     */
    static public $aDefaultMeta = array(
    
        // Required internals
        '_routine_status'       => 'queued',   // string    ready, awaiting, processing
        '_next_run_time'        => null,       // float
        '_count_call'           => 0,          // integer - represents the count that the task is called (triggered)
        '_count_run'            => 0,          // integer - represents the count that the task has been executed. (which does not mean the task did what the user expects but it did finish running)

        
        // Required
        'routine_action'        => null,       // string    the target action hook name
        'argument'              => array(),    // array    the arguments(parameters) passed to the action
        'occurrence'            => 'volatile', // string    The slug of the occurrence type.
        
        'owner_task_id'         => null,
        'parent_routine_log_id' => null,      // integer    the parent log id
        
        // Advanced options - they also have a prefix of a underscore to prevent conflicts with third-party extensions.
// @todo: assign a server set PHP max execution time here.
        '_max_execution_time'   => null,      // integer    whenever retrieve this value, assign the server set maximum execution time.

    );       
    
    /**
     * Derives a routine from the given task and the options.
     */
    static public function derive( $iTaskID, array $aRoutineOptions=array(), array $aSystemTaxonomyTerms=array(), $bAllowDuplicate=false ) {
        
        $_oTask         = TaskScheduler_Routine::getInstance( $iTaskID );
        if ( ! is_object( $_oTask ) ) {
            return 0;
        }
        
        $_aTaskMeta     = TaskScheduler_WPUtility::getPostMetas( $iTaskID );
        unset( 
            $_aTaskMeta[ '_count_hung' ],
            $_aTaskMeta[ '_count_exit' ],
            $_aTaskMeta[ '_count_call' ],
            $_aTaskMeta[ '_count_run' ]
        );
        $aRoutineOptions  = $aRoutineOptions + array(
            'post_title'            => sprintf( __( 'Routine instance of %1$s', 'task-scheduler' ), $_oTask->post_title ),
            'post_type'             => TaskScheduler_Registry::$aPostTypes[ 'routine' ],
            '_routine_status'       => 'queued',
            'occurrence'            => 'volatile',
            'owner_task_id'         => $iTaskID,
            'parent_routine_log_id' => 0,
            '_next_run_time'        => microtime( true ), 
        ) 
        + $_aTaskMeta 
        + self::$aDefaultMeta;
        
        if ( ! $bAllowDuplicate && self::hasSameRoutine( $aRoutineOptions, array( __CLASS__, 'find' ) ) ) {
            return 0;
        }
        $_aTerms = wp_get_post_terms( 
            $iTaskID,
            TaskScheduler_Registry::$aTaxonomies[ 'system' ], 
            array( "fields" => "names" ) 
        );
        
        return self::create( $aRoutineOptions, array_merge( $_aTerms, $aSystemTaxonomyTerms ), $bAllowDuplicate );
                
    }
    
}