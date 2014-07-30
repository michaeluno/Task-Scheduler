<?php
/**
 * The final class of the plugin admin page class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, Michael Uno
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

final class TaskScheduler_AdminPage extends TaskScheduler_AdminPage_Setup {
	
	/*
	 * Customize the menu order.
	 */
	public function _replyToBuildMenu() {
	
		parent::_replyToBuildMenu();
		
		// Logged-in users with an insufficient access level don't have the menu to be registered.
		$_sPageSlug = $this->oProp->aRootMenu['sPageSlug'];
		if ( ! isset( $GLOBALS['submenu'][ $_sPageSlug ] ) && ! is_array( $GLOBALS['submenu'][ $_sPageSlug ] ) ) { 
			return; 
		}

		// First iteration, store the menu positions of 'Task' and 'Log' and remove the 'Log' submenu 
		/* The structure of $GLOBALS['submenu'][ $_sPageSlug ] looks like this.
		 * Array (
			[0] => Array (
					[0] => Logs
					[1] => edit_posts
					[2] => edit.php?post_type=ts_log
					[3] => Logs  )
			[1] => Array (
					[0] => Tasks
					[1] => manage_options
					[2] => ts_task_list
					[3] => Tasks )
		) */

		$_iMenuPosition_Task = null;
		$_iMenuPosition_Log = null;
		foreach ( $GLOBALS['submenu'][ $_sPageSlug ] as $_iIndex => $_aSubMenu ) {
						
			if ( ! isset( $_aSubMenu[ 2 ] ) ) { continue; }
			
			if (  TaskScheduler_Registry::AdminPage_TaskList == $_aSubMenu[ 2 ] ) {	
				$_iMenuPosition_Task = $_iIndex;
			}
			
			if ( 'edit.php?post_type=' . TaskScheduler_Registry::PostType_Log == $_aSubMenu[ 2 ] ) {
				$_iMenuPosition_Log = $_iIndex;
			}

		}
			
		// Insert the Tag menu item before the Setting menu item.
		if ( isset( $_iMenuPosition_Task, $_iMenuPosition_Log ) ) {
			TaskScheduler_WPUtility::swapElements( $GLOBALS['submenu'][ $_sPageSlug ], $_iMenuPosition_Task, $_iMenuPosition_Log );
		} 
		
	}
	
}