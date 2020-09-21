<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2020, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

 /**
  * The class that defines the PHP Script action thread for the Task Scheduler plugin.
  * 
  * @since      1.4.0
  */
class TaskScheduler_Action_PHPScript_Thread extends TaskScheduler_Action_Base {

    protected $sSlug = 'task_scheduler_action_run_individual_php_script';

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
        return __( 'Run a PHP Script', 'task-scheduler' );
    }
    
    /**
     * Defines the behavior of the task action.
     * 
     * Volatile child tasks to send only one email.
     */
    public function doAction( $isExitCode, $oThread ) {

        $_aThreadMeta = $oThread->getMeta();
        if ( 
            ! isset( 
                $_aThreadMeta[ 'php_script_path' ]
            ) 
        ) {
            return 0;    // failed
        }
        
        $_sPath = $this->_getPathFormatted( $_aThreadMeta[ 'php_script_path' ] );
        
        if ( ! file_exists( $_sPath ) ) {
            return 0;   // file not found
        }
        
        return include( $_sPath );
    
    }
        /**
         * @since       1.4.0
         * @return      string
         */
        private function _getPathFormatted( $sRelativePath ) {
            
            return $_SERVER[ 'DOCUMENT_ROOT' ] . $sRelativePath;
            
        }
            
}
