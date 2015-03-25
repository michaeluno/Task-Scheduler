<?php
/**
 * The class that defines the Debug task for the task scheduler.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, <Michael Uno>
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
     */
    public function __construct( $sSlug, $asWizardClasses=array() )     {
        
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
     */
    public function getNextRunTime( $iTimestamp, $oTask ) { 
        return $iTimestamp;
    }
    public function doAfterAction( $oTask, $sExitCode ) {}
    
}