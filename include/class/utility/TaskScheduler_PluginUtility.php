<?php
/**
 * Provides utility methods which use the Task Scheduler plugin specific components.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

class TaskScheduler_PluginUtility extends TaskScheduler_WPUtility {
	
	/**
	 * 
	 * This is used to display stored options that are not editable in meta boxes.
	 */
	static public function getListFromAssociativeArray( array $aArray ) {
		
		$_aList = array();
		foreach( $aArray as $_sKey => $_vValue ) {
			$_aList[] = "<li>" 
					. "<span class='key_label'>" . $_sKey . "</span>" 
					. "<span class='value_container'>" . ( is_array( $_vValue ) ? self::getListFromAssociativeArray( $_vValue ) : $_vValue ) . "</span>" 
				. "</li>";
		}
		return "<ul class='key-value-container'>"
				. implode( PHP_EOL, $_aList )
			. "</ul>";

	}
	
	/**
	 * Redirects the user to the Add New wizard page.
	 */
	public static function goToAddNewPage() {
		wp_safe_redirect( 
			add_query_arg( 
				array( 
					'page'		=>	TaskScheduler_Registry::AdminPage_AddNew,
				), 
				admin_url( 'admin.php' ) 
			)
		);		
	}
	
	/**
	 * Redirects the user to the Edit Module wizard page.
	 */ 
	public static function goToEditTaskPage() {		
		wp_safe_redirect( self::getEditTaskPageURL() );
	}	
	
	/**
	 * Returns the module editing page url.
	 */
	public static function getEditTaskPageURL() {
		return 	add_query_arg(
			array( 
				'post'		=>	isset( $_GET['post'] ) ? $_GET['post'] : '',
				'action'	=>	'edit',
			),
			admin_url( "post.php" )
		);		
	}	
	
	/**
	 * Redirects the user to the Edit Module wizard page.
	 */ 
	public static function goToModuleEditPage()	 {		
		wp_safe_redirect( self::getModuleEditPageURL() );
	}	
	
	/**
	 * Returns the module editing page url.
	 */
	public static function getModuleEditPageURL( $_aGetQuery=array() ) {
		return 	add_query_arg( 
			$_aGetQuery
			+ array( 
				'page'		=>	TaskScheduler_Registry::AdminPage_EditModule,
			), 
			admin_url( 'admin.php' ) 
		);		
	}
			
	/**
	 * Redirects the user to the task listing page.
	 */
	public static function goToTaskListingPage() {
		wp_safe_redirect( self::getTaskListingPageURL() );
	}
	
	/**
	 * Returns the url of the task listing page.
	 */
	static public function getTaskListingPageURL( array $aQueryArgs=array() ) {
		return add_query_arg( 
			$aQueryArgs + array( 
				'page'		=>	TaskScheduler_Registry::AdminPage_TaskList,
			), 
			admin_url( 'admin.php' ) 
		);
	}	

}