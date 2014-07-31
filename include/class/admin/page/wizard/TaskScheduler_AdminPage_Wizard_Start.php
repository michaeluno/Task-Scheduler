<?php
/**
 * One of the base classes of the plugin admin page class for the wizard pages.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_AdminPage_Wizard_Start extends TaskScheduler_AdminPageFramework {
// abstract class TaskScheduler_AdminPage_Wizard_Start extends AdminPageFramework {
	
	public function start() {
				
		$this->_disableAddNewButton();
		
		$this->_registerCustomFieldTypes();
	
			
	}
		
		/**
		 * Disables the Add New link of the task post type and redirect to the wizard start page.
		 */
		private function _disableAddNewButton() {
		
			if ( ! $this->oProp->bIsAdmin ) { return; }
			
			if ( ! in_array( $this->oUtil->getPageNow(), array( 'post-new.php' ) ) ) { return; }
				
			if ( $this->oUtil->getCurrentPostType() != TaskScheduler_Registry::PostType_Task ) { return; }
			
			TaskScheduler_PluginUtility::goToAddNewPage();		
			
		}
		
		/**
		 * Registers custom field types.
		 * 
		 * @remark	The scope is 'protected' because the extending Edit Module class will use this method.
		 */
		protected function _registerCustomFieldTypes() {
			
			if ( ! $this->oProp->bIsAdmin ) { return; }
			
			$_sClassName = get_class( $this );
			new TaskScheduler_DateTimeCustomFieldType( $_sClassName );		
			new TaskScheduler_TimeCustomFieldType( $_sClassName );		
			new TaskScheduler_DateCustomFieldType( $_sClassName );		
			new TaskScheduler_AutoCompleteCustomFieldType( $_sClassName );		
			new TaskScheduler_RevealerCustomFieldType( $_sClassName );	
		
		}
			
}