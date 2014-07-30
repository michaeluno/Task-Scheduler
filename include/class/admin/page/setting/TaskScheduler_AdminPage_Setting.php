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

final class TaskScheduler_AdminPage_Setting extends TaskScheduler_AdminPage_Setting_Form_Heartbeat {

	public function setUp() {
	
		$this->setRootMenuPageBySlug( TaskScheduler_Registry::AdminPage_Root );
		$this->addSubMenuItems(
			array(
				'title'			=>	__( 'Settings', 'task-scheduler' ),	// page and menu title
				'page_slug'		=>	TaskScheduler_Registry::AdminPage_Setting	// page slug
			)
		);		
			
		$this->_defineStyles();	
		$this->_defineForm();

	}
	
	
		/**
		 * Defines the styling of the admin pages.
		 * 
		 * @since	1.0.0
		 */
		protected function _defineStyles() {
						
			$this->setPageHeadingTabsVisibility( false );		// disables the page heading tabs by passing false.
			$this->setInPageTabsVisibility( true );		// disables the page heading tabs by passing false.
			$this->setPageTitleVisibility( false );
			$this->setInPageTabTag( 'h2' );				
			$this->enqueueStyle( TaskScheduler_Registry::getPluginURL( '/asset/css/admin_settings.css' ) );
			$this->setDisallowedQueryKeys( 'settings-notice' );
			$this->setDisallowedQueryKeys( 'transient_key' );
			
		}	
	
	
	
}