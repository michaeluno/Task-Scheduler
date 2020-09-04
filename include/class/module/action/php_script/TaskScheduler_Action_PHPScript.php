<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 */

/**
 * The class that defines the Email action for the Task Scheduler plugin.
 * @since      1.4.0
 */
class TaskScheduler_Action_PHPScript extends TaskScheduler_Action_Base {

    protected $sSlug = 'task_scheduler_action_php';

    /**
     * The user constructor.
     * 
     * This method is automatically called at the end of the class constructor.
     */
    public function construct() {
        new TaskScheduler_Action_PHPScript_Thread( '', array() ); // // internal, no wizard
    }
    
    /**
     * Returns the readable label of this action.
     * 
     * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
     */
    public function getLabel( $sLabel ) {
        return __( 'Run PHP Scripts', 'task-scheduler' );
    }
    
    /**
     * Returns the description of the module.
     */
    public function getDescription( $sDescription ) {
        return __( 'Runs specified PHP scripts.', 'task-scheduler' );
    }    
        
    /**
     * Defines the behavior of the task action.
     */
    public function doAction( $isExitCode, $oRoutine ) {
        
        $_aTaskMeta = $oRoutine->getMeta();
        if ( 
            ! isset( 
                $_aTaskMeta[ $this->sSlug ],
                $_aTaskMeta[ $this->sSlug ][ 'php_script_paths' ]
            ) 
            || ! is_array( $_aTaskMeta[ $this->sSlug ][ 'php_script_paths' ]  ) 
        ) {
            return 0;    // failed
        }
        
        // Handle each PHP script per thread (spawn a subroutine in the background each)
        $_iThreads = 0;
        $_iCount   = 0;
        foreach( $_aTaskMeta[ $this->sSlug ][ 'php_script_paths' ] as $_sPath ) {
            
            $_iCount++;
            
            $_aThreadOptions = array(

                // Overriding
                'post_title'            => sprintf( __( 'Thread %1$s of %2$s', 'task-scheduler' ), $_iCount + 1, $oRoutine->post_title ),
                '_next_run_time'        => microtime( true ) + $_iCount,    // add an offset so that they will be loaded with a delay of a second each.            
                
                // Routine specific options
                'php_script_path' => $_sPath,
                
            );

            $_iThreadTaskID = $this->createThread(
                'task_scheduler_action_run_individual_php_script',
                $oRoutine,
                $_aThreadOptions
            );
            $_iThreads      = $_iThreadTaskID ? ++$_iThreads : $_iThreads;

        }
        
        // Check actions in the background.
        if ( $_iThreads ) {
            do_action( 'task_scheduler_action_check_scheduled_actions' );
        }
        
        return null;    // exit code: do not log; it will be, when the threads finish.
        
    }
            
}
