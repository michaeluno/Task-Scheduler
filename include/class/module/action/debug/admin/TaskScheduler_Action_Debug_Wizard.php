<?php
/**
 * Creates wizard pages for the 'Debug' action.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

final class TaskScheduler_Action_Debug_Wizard extends TaskScheduler_Wizard_Action_Base {
		
	public function _replyToSetRedirectURL( $sRedirectURL, $aWizardOptions ) {
		return add_query_arg( 
			array( 
				'tab'				=> $this->sNextTabSlug, 
				'transient_key'		=> $this->_sTransientKey,
			)
		);		
	}
	
}