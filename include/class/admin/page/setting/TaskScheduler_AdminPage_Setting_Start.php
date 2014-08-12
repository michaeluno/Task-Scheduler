<?php
/**
 * One of the abstract class of the plugin admin page class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_AdminPage_Setting_Start extends TaskScheduler_AdminPageFramework {
// abstract class TaskScheduler_AdminPage_Setting_Start extends AdminPageFramework {
	
	public function setUp() {}
	protected function _defineInPageTabs() {}
	protected function _defineForm() {}
	
	/**
	 * The callback for the options array.
	 */
	public function options_TaskScheduler_AdminPage_Setting( $aOptions ) {
		return $aOptions + TaskScheduler_Option::$aDefaults;
	}
	
}