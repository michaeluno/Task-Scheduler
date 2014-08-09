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
					
		// $this->_registerCustomFieldTypes();
			
	}
						
	/**
	 * Will be defined in the extended class.
	 */
	public function setUp() {}
	protected function _defineForm() {}
	
}