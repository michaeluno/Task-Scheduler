<?php
/**
 * One of the abstract classes of the plugin admin page class for the wizard pages.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

 /**
  * 
  * @filter		apply	task_scheduler_admin_filter_saving_wizard_options	Applies to the wizard options array that is about to be saved in the transient. This lets modules to insert custom keys into the options array.
  */
abstract class TaskScheduler_AdminPage_Wizard_Validation extends TaskScheduler_AdminPage_Wizard_Start {

	/**
	 * The validation handler of the wizard admin pages for the entire class.
	 */
	public function validation_TaskScheduler_AdminPage_Wizard( $aInput, $aOldInput, $oAdminPage ) {

		$_aWizardOptions = isset( $aInput[ '_wizard_options' ] ) ? $aInput[ '_wizard_options' ] : array();
	
		// Check if necessary keys are set. If the transient is expired, the necessary elements will miss. In that case, let the user start over the process.
		if ( ! isset( $_aWizardOptions['post_title'] ) || ! $_aWizardOptions['post_title'] ) {
			$this->setSettingNotice( __( 'The wizard session has been expired. Please start from the beginning.', 'task-scheduler' ) );
			die( TaskScheduler_PluginUtility::goToAddNewPage() );
		}		
		
		// The wizard options are stored in the '_wizard_options' element
		$_aSavedValues = $this->_saveValidatedWizardOptions( $_aWizardOptions );
		
		// Passing a dummy value will prevent the framework from displaying an admin notice.
		return array( 'dummy value' );	
		
	}
		
		/**
		 * Saves validated wizard options.
		 * 
		 * @remark	The scope is 'protected' because the extending edit module class will use this method.
		 */
		protected function _saveValidatedWizardOptions( array $aWizardOptions ) {

			if ( ! isset( $_GET['transient_key'] ) ) {
				return;
			}	

			$aWizardOptions = apply_filters( 'task_scheduler_admin_filter_saving_wizard_options', $aWizardOptions );
			set_transient( $_GET['transient_key'], $aWizardOptions, 30*60 );
			return $aWizardOptions;
			
		}
	
}