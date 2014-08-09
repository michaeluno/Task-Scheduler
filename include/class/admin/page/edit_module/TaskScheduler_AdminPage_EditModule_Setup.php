<?php
/**
 * One of the base classes of the editing module options pages.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

abstract class TaskScheduler_AdminPage_EditModule_Setup extends TaskScheduler_AdminPage_EditModule_Tab_Occurrence {
	
	/**
	 * Defines the admin pages of the plugin.
	 * 
	 * @since	1.0.0
	 */	 	
	public function setUp() {
			
		$this->setRootMenuPageBySlug( TaskScheduler_Registry::AdminPage_Root );
		$this->addSubMenuItems(
			array(
				'title'			=>	__( 'Edit Module Options', 'task-scheduler' ),	// page and menu title
				'page_slug'		=>	TaskScheduler_Registry::AdminPage_EditModule,	// page slug
				'show_in_menu'	=>	false,		// do not add in the sub menu
			)
		);		
		
		$this->addInPageTabs(
			TaskScheduler_Registry::AdminPage_EditModule,	// the target page slug
			array(	// this is needed to be the parent tab of module tabs. (the wizard base class assumes the parent tab exists with this slug.)
				'tab_slug'			=>	'wizard',	
				'title'				=>	__( 'Edit Module Options', 'task-scheduler' ),
				'order'				=>	1,	// this must be the 'default' tab
				'show_in_page_tab'	=>	false,	
			)
		);				
		
		$this->_sTransientKey = isset( $_GET['transient_key'] ) && $_GET['transient_key'] ? $_GET['transient_key'] : TaskScheduler_Registry::TransientPrefix . uniqid();
		$this->_defineStyles();	// the method is defined in one of the base classes.
		
		add_action( "load_" . TaskScheduler_Registry::AdminPage_EditModule, array( $this, '_replyToDefineFormElements' ) );
		
		parent::setUp();
		
	}
	
	/**
	 * Called when the framework page loads.
	 */
	public function _replyToDefineFormElements( $oAdminPage ) {
		
		$this->_registerCustomFieldTypes();
		$this->_defineForm();
		
	}

}