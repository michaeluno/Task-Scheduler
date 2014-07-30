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

abstract class TaskScheduler_AdminPage_Wizard_Setup extends TaskScheduler_AdminPage_Wizard_Tab_CreateTask {
	
	/**
	 * Defines the admin pages of the plugin.
	 * 
	 * @since	1.0.0
	 */	 	
	public function setUp() {
			
		// $this->setRootMenuPageBySlug( 'edit.php?post_type=' . TaskScheduler_Registry::PostType_Task );
		$this->setRootMenuPageBySlug( TaskScheduler_Registry::AdminPage_Root );
		$this->addSubMenuItems(
			array(
				'title'			=>	__( 'Add New Task', 'task-scheduler' ),	// page and menu title
				'page_slug'		=>	TaskScheduler_Registry::AdminPage_AddNew,	// page slug
				// 'show_in_menu'	=>	false,		// do not add in the sub menu
			)
		);		
		
		$this->addInPageTabs(
			TaskScheduler_Registry::AdminPage_AddNew,	// the target page slug
			array(	// the wizard starting page.
				'tab_slug'			=>	'wizard',	
				'title'				=>	__( 'Wizard', 'task-scheduler' ),
				'order'				=>	1,	// this must be the 'default' tab
				'show_in_page_tab'	=>	false,
			),	
			array(	// the hidden page that let the user select an action.
				'tab_slug'			=>	'wizard_select_action',	
				'title'				=>	__( 'Select Action', 'task-scheduler' ),
				'show_in_page_tab'	=>	false,
			),									
			array(	// the hidden page that deals with creating a redirected task by inserting the transient options as the meta data of a newly creating a custom post type post.
				'tab_slug'			=>	'wizard_create_task',	
				'title'				=>	__( 'Create Task', 'task-scheduler' ),
				'show_in_page_tab'	=>	false,
			)
		);				
		
		$this->_defineStyles();
		
		
		$this->_sTransientKey = isset( $_GET['transient_key'] ) && $_GET['transient_key'] ? $_GET['transient_key'] : TaskScheduler_Registry::TransientPrefix . uniqid();
		$this->_setWizard( $this->_sTransientKey );
		$this->_setWizard_SelectAction( $this->_sTransientKey );
				
	}
	
	
	/**
	 * Defines the styling of the admin pages.
	 * 
	 * @since	1.0.0
	 */
	protected function _defineStyles() {
					
		$this->setPageHeadingTabsVisibility( false );		// disables the page heading tabs by passing false.
		$this->setInPageTabsVisibility( false );		// disables the page heading tabs by passing false.
		// $this->setPageTitleVisibility( false );
		$this->setInPageTabTag( 'h2' );				
		$this->enqueueStyle( TaskScheduler_Registry::getPluginURL( '/asset/css/admin_wizard.css' ) );
		$this->setDisallowedQueryKeys( 'settings-notice' );
		$this->setDisallowedQueryKeys( 'transient_key' );
		$this->setPluginSettingsLinkLabel( '' );	// pass an empty string.		
		
	}	
	

}