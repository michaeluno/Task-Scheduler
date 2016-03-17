<?php
/**
 * The class that defines the Debug task for the task scheduler.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 *     
 */
abstract class TaskScheduler_Occurrence_Base extends TaskScheduler_Module_Factory {
            
    /**
     * Sets up necessary hooks and properties.
     * 
     * @since       1.0.0
     * @since       1.0.1       Made the first parameter optional.
     */
    public function __construct( $sSlug='', $asWizardClasses=array( 'TaskScheduler_Wizard_Occurrence_Default' ) ) {
        
        $sSlug = empty( $sSlug )
            ? strtolower( get_class( $this ) )
            : $sSlug;        
        
        parent::__construct( 
            $sSlug, 
            $asWizardClasses, 
            'occurrence'
        );
        
        add_filter( "task_scheduler_filter_next_run_time_{$sSlug}", array( $this, 'getNextRunTime' ), 10, 2 );
        add_action( "task_scheduler_action_after_doing_routine_of_occurrence_{$sSlug}", array( $this, 'doAfterAction' ), 10, 2 );
            
    }
    
    /*
     * Extensible methods.
     * @return      integer|float     timestamp without GMT offset.
     */
    public function getNextRunTime( $iTimestamp, $oTask ) { 
        return $iTimestamp;
    }
    public function doAfterAction( $oTask, $sExitCode ) {}
    
}