<?php
/**
 * One of the abstract classes of the editing module options pages.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_AdminPage_EditModule_Start extends TaskScheduler_AdminPage_Wizard {
	
	public function start() {
					
		$this->_registerCustomFieldTypes();
			
	}
				
		/**
		 * Registers custom field types.
		 * @deprecaed Use the parent method.
		 */
		// private function _registerCustomFieldTypes() {
			
			// if ( ! $this->oProp->bIsAdmin ) { return; }
			
			// $_sClassName = get_class( $this );
			// new TaskScheduler_DateTimeCustomFieldType( $_sClassName );		
			// new TaskScheduler_TimeCustomFieldType( $_sClassName );		
			// new TaskScheduler_DateCustomFieldType( $_sClassName );				
			// new TaskScheduler_AutoCompleteCustomFieldType( $_sClassName );		
		
		// }
		
	/**
	 * Will be defined in the extended class.
	 */
	public function setUp() {}
			
}