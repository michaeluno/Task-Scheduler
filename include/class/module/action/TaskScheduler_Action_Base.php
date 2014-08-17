<?php
/**
 * The class that defines the Debug task for the task scheduler.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author        Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

/**
 *     
 */
abstract class TaskScheduler_Action_Base extends TaskScheduler_Module_Factory {
                
    /**
     * Sets up hooks and properties.
     */
    public function __construct( $sSlug, $asWizardClasses=array() ) {
        
        parent::__construct( $sSlug, $asWizardClasses, 'action' );
        if ( ! $sSlug ) { return; }
        
        // For action type extensions, the slug is used for the action hook name.
        // Here the callback is hooked up with a filter, not action, in order to receive and return an exit code.
        // This is a bit confusing but don't worry about it.
        add_filter( $sSlug, array( $this, 'doAction' ), 10, 3 );
            
    }
    
    public function doAction( $isExitCode, $oRoutine ) {}
    
}
