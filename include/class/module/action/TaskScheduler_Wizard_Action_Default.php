<?php
/**
 * Provides an enhanced task management system for WordPress.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 */

/**
 * Defines the default wizard screen for the action module.
 * 
 * @since       1.0.1
 */
final class TaskScheduler_Wizard_Action_Default extends TaskScheduler_Wizard_Action_Base {
    
    /**
     * Returns the default next screen url.
     * @since       1.0.1
     */
    public function _replyToSetRedirectURL( $sRedirectURL, $aWizardOptions ) {
        return add_query_arg( 
            array( 
                'tab'           => $this->sNextTabSlug, 
                'transient_key' => $this->_sTransientKey,
            )
        );        
    }    
    
}