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

abstract class TaskScheduler_AdminPage_Setup extends TaskScheduler_AdminPage_Form {
	
	/**
	 * Sets-up necessary settings to create pages.
	 */
	public function setUp() {
		
		// 1. Define the pages
		$this->_definePages();
		
		// 2. Define the styling
		$this->_defineStyles();
							
	}
	
		/**
		 * Defines the admin pages of the plugin.
		 * 
		 * @since	1.0.0
		 */	 
		private function _definePages() {
			
			$this->setRootMenuPage( 
				__( 'Task Scheduler', 'task-scheduler' ),
				TaskScheduler_Registry::getPluginURL( '/asset/image/menu_icon_16x16.png' )
			);
			$this->addSubMenuItems(
				array(
					'title'			=>	__( 'Tasks', 'task-scheduler' ),	// page and menu title
					'page_slug'		=>	TaskScheduler_Registry::AdminPage_TaskList	// page slug				
				),
				array(
					'title'			=>	__( 'Test Page', 'task-scheduler' ),
					'page_slug'		=>	'testing_page',
				),
				array()
			);
							
		}

		/**
		 * Defines the styling of the admin pages.
		 * 
		 * @since	1.0.0
		 */
		private function _defineStyles() {
						
			$this->setPageHeadingTabsVisibility( false );		// disables the page heading tabs by passing false.
			// $this->setInPageTabsVisibility( true );		// disables the page heading tabs by passing false.
			$this->setPageTitleVisibility( true );
			// $this->setInPageTabTag( 'h2' );				
			$this->enqueueStyle( TaskScheduler_Registry::getPluginURL( '/asset/css/ts_task_list.css' ) );
			$this->setDisallowedQueryKeys( array( 'settings-notice', 'task_scheduler_nonce', 'action', 'transient_key', 'task_scheduler_task' ) );
			$this->setPluginSettingsLinkLabel( '' );	// pass an empty string.		
			
		}

}