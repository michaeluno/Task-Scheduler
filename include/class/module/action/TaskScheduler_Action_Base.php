<?php
/**
 * The class that defines the Debug task for the task scheduler.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 *     
 */
abstract class TaskScheduler_Action_Base extends TaskScheduler_Module_Factory {
                
    /**
     * Sets up hooks and properties.
     * 
     * @since       1.0.0
     * @since       1.0.1       If a slug is not specified, lower-cased class name gets automatically applied.
     */
    public function __construct( $sSlug='', $asWizardClasses=array( 'TaskScheduler_Wizard_Action_Default' ) ) {
        
        $sSlug = empty( $sSlug )
            ? (
                $this->sSlug
                    ? $this->sSlug
                    : strtolower( get_class( $this ) )
            )
            : $sSlug;
        
        parent::__construct( 
            $sSlug, 
            $asWizardClasses, 
            'action'    // the module type
        );
        
        /**
         * For action type extensions, the slug is used for the action hook name.
         * Here the callback is hooked up with a filter, not action, in order to receive and return an exit code.
         * This is a bit confusing but don't worry about it.
         */
        add_filter( $sSlug, array( $this, 'doAction' ), 10, 3 );

    }

    /**
     * 
     * @callback        filter      $this->sSlug
     */
    public function doAction( $isExitCode, $oRoutine ) {}

    /**
     * Creates a thread.
     *
     * @since  1.5.0
     * @remark A wrapper method for `TaskScheduler_ThreadUtility::derive()`.
     * @param  string                $sThreadActionHookName The action hook name of the thread.
     * @param  TaskScheduler_Routine $oRoutine              The owner routine object.
     * @param  array                 $aThreadOptions        Thread arguments.
     * @param  array                 $aSystemTaxonomyTerms  Taxonomy terms for the system.
     * @param  boolean               $bAllowDuplicate       Whether to allow duplicate threads to be created.
     * @return integer               The thread ID.
     */
    public function createThread( $sThreadActionHookName, $oRoutine, array $aThreadOptions, array $aSystemTaxonomyTerms=array(), $bAllowDuplicate=false ) {
        $aThreadOptions = $aThreadOptions + array(
            'routine_action'        => $sThreadActionHookName,
            'post_title'            => sprintf( __( 'Thread of %1$s', 'task-scheduler' ), $oRoutine->post_title ),
            'parent_routine_log_id' => $oRoutine->log_id,
            '_next_run_time'        => microtime( true ),
            '_max_root_log_count'   => $oRoutine->_max_root_log_count,
        );
        return TaskScheduler_ThreadUtility::derive( $oRoutine->ID, $aThreadOptions, $aSystemTaxonomyTerms, $bAllowDuplicate );
    }

}