<?php
/**
 * Creates a wizard page for action modules that do not specify a wizard class.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2015, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.1
 */

final class TaskScheduler_Wizard_Default extends TaskScheduler_Wizard_Action_Base {
        
    public function _replyToSetRedirectURL( $sRedirectURL, $aWizardOptions ) {
        return add_query_arg( 
            array( 
                'tab'           => $this->sNextTabSlug, 
                'transient_key' => $this->_sTransientKey,
            )
        );        
    }
    
}