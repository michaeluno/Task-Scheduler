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
		
		$this->setRootMenuPage( 
			__( 'Task Scheduler', 'task-scheduler' ),
			TaskScheduler_Registry::getPluginURL( '/asset/image/menu_icon_16x16.png' )
		);
		$this->addSubMenuItems(
			array(
				'title'			=>	__( 'Tasks', 'task-scheduler' ),	// page and menu title
				'page_slug'		=>	TaskScheduler_Registry::AdminPage_TaskList	// page slug				
			),
			array()
		);
									
	}
	
	public function load_TaskScheduler_AdminPage() {	// load_{instantiated class name}
		
		$this->setPageHeadingTabsVisibility( false );		// disables the page heading tabs by passing false.
		// $this->setInPageTabsVisibility( true );		// disables the page heading tabs by passing false.
		$this->setPageTitleVisibility( true );
		// $this->setInPageTabTag( 'h2' );				
		$this->enqueueStyle( TaskScheduler_Registry::getPluginURL( '/asset/css/ts_task_list.css' ) );
		$this->setDisallowedQueryKeys( array( 'settings-notice', 'task_scheduler_nonce', 'action', 'transient_key', 'task_scheduler_task' ) );
		$this->setPluginSettingsLinkLabel( '' );	// pass an empty string.		
	
	}	

}