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

abstract class TaskScheduler_AdminPage_Start extends TaskScheduler_AdminPageFramework {
	
	public function start() {
		
		// Adds a plugin settings link.
		if ( in_array( $this->oProp->sPageNow, array( 'plugins.php' ) ) && 'plugin' == $this->oProp->aScriptInfo['sType'] ) {
			add_filter( 
				'plugin_action_links_' . plugin_basename( $this->oProp->aScriptInfo['sPath'] ),
				array( $this, '_replyToInsertLink' ),
				100	// lower priority make it insert left hand side
			);				
		}		
				
		// Correct the redirecting url when the post(task/thread) is updated in post.php.
		add_filter( 'redirect_post_location', array( $this, '_replyToModifyRedirectURLAfterUpdatingTask' ), 10, 2 );
	
	}
	
		/**
		 * Inserts a link in the the plugin title cell of the plugin listing table.
		 */
		public function _replyToInsertLink( $aLinks ) {
						
			$_sLink = add_query_arg(
				array( 
					'page'	=>	 TaskScheduler_Registry::AdminPage_TaskList,
				),
				admin_url( 'admin.php' )
			);
			array_unshift(	
				$aLinks,
				"<a href='{$_sLink}'>" . __( 'Manage Tasks', 'task-scheduler' ) . "</a>"
			); 
			return $aLinks;					
			
		}
	
	/**
	 * Modifies the redirect url applied when the task/thread is updated.
	 */
	public function _replyToModifyRedirectURLAfterUpdatingTask( $sURL, $iPostID ) {

		$_sPostType = get_post_type( $iPostID );
		if ( in_array( $_sPostType, array( TaskScheduler_Registry::PostType_Task, TaskScheduler_Registry::PostType_Thread ) ) ) {
			
			$_oRoutine = TaskScheduler_Routine::getInstance( $iPostID );
			$_aQueryArgs = ! $_oRoutine->isEnabled() ? array( 'status' => 'disabled' ) : array();
			return TaskScheduler_PluginUtility::getTaskListingPageURL( $_aQueryArgs );
			
		}
		return $sURL;
	}
	
}