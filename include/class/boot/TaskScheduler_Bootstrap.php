<?php
/**
 *	Handles the initial set-up for the plugin.
 *	
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 * 
 */

/**
 * 
 * @action		schedule	task_scheduler_action_check_heartbeat_hourly	Scheduled via the activation hook.
 * @action		do			task_scheduler_action_after_loading_plugin		Triggered when all the plugin components are loaded. Extension plugins should use this hook to add modules.
 */
final class TaskScheduler_Bootstrap {
	
	function __construct( $sPluginFilePath ) {
		
		// 0. The class properties.
		$this->_sFilePath = $sPluginFilePath;
		$this->_bIsAdmin = is_admin();
		
		// 1. Define constants.
		// $this->_defineConstants();
		
		// 2. Set global variables.
		// $this->_setGlobalVariables();
			
		// 3. Set up auto-load classes.
		$this->_loadClasses( $this->_sFilePath );

		// 4. Set up activation hook.
		register_activation_hook( $this->_sFilePath, array( $this, '_replyToDoWhenPluginActivates' ) );
		
		// 5. Set up deactivation hook.
		register_deactivation_hook( $this->_sFilePath, array( $this, '_replyToDoWhenPluginDeactivates' ) );
		
		// 6. Set up localization.
		$this->_localize();
		
		// 7. Check requirements.
		add_action( 'admin_init', array( $this, '_replyToCheckRequirements' ) );
		
		// 8. Schedule to load plugin specific components.
		add_action( 'plugins_loaded', array( $this, '_replyToLoadPluginComponents' ) );
						
	}	
	
	// private function _defineConstants() {}
	
	/**
	 * Sets up global variables.
	 */
	private function _setGlobalVariables() {}
	
	/**
	 * Register classes to be auto-loaded.
	 * 
	 */
	private function _loadClasses( $sFilePath ) {
		
		$_sPluginDir =  dirname( $sFilePath );
		
		// If the include script fails, the auto loader class is not defined.
		if ( class_exists( 'TaskScheduler_AutoLoad' ) ) {
			return;
		}
		
		// These lines should only be read if the inclusion script or the minified class file is disabled manually.
		$this->_includeLibraries();							
		include( $_sPluginDir . '/include/class/boot/TaskScheduler_AutoLoad.php' );	
		
		// Register the classes.
		new TaskScheduler_AutoLoad( $_sPluginDir . '/include/class' );	
				
	}
		/**
		 * Includes third-party libraries.
		 */
		protected function _includeLibraries() {
					
			include( TaskScheduler_Registry::$sDirPath . '/include/library/admin-page-framework/task-scheduler-admin-page-framework.min.php' );
			include( TaskScheduler_Registry::$sDirPath . '/include/library/admin-page-framework/autocomplete-custom-field-type/TaskScheduler_AutoCompleteCustomFieldType.php' );
			include( TaskScheduler_Registry::$sDirPath . '/include/library/admin-page-framework/date-time-custom-field-types/TaskScheduler_DateRangeCustomFieldType.php' );
			include( TaskScheduler_Registry::$sDirPath . '/include/library/admin-page-framework/date-time-custom-field-types/TaskScheduler_DateTimeCustomFieldType.php' );
			include( TaskScheduler_Registry::$sDirPath . '/include/library/admin-page-framework/date-time-custom-field-types/TaskScheduler_DateTimeRangeCustomFieldType.php' );
			include( TaskScheduler_Registry::$sDirPath . '/include/library/admin-page-framework/date-time-custom-field-types/TaskScheduler_DateCustomFieldType.php' );
			include( TaskScheduler_Registry::$sDirPath . '/include/library/admin-page-framework/date-time-custom-field-types/TaskScheduler_TimeCustomFieldType.php' );
			include( TaskScheduler_Registry::$sDirPath . '/include/library/admin-page-framework/date-time-custom-field-types/TaskScheduler_TimeRangeCustomFieldType.php' );
			include( TaskScheduler_Registry::$sDirPath . '/include/library/admin-page-framework/revealer-custom-field-type/TaskScheduler_RevealerCustomFieldType.php' );
						
		}	

	/**
	 * 
	 * @since			2.1
	 */
	public function _replyToCheckRequirements() {

		new TaskScheduler_Requirements( 
			$this->_sFilePath,
			array(
				'php' => array(
					'version'	=>	TaskScheduler_Registry::RequiredPHPVersion,
					'error'		=>	__( 'The plugin requires the PHP version %1$s or higher.', 'task-scheduler' ),
				),
				'wordpress' => array(
					'version'	=>	TaskScheduler_Registry::RequiredWordPressVersion,
					'error'		=>	__( 'The plugin requires the WordPress version %1$s or higher.', 'task-scheduler' ),
				),
				// 'mysql'	=>	array(
					// 'version'	=>	'5.5.24',
					// 'error' => __( 'The plugin requires the MySQL version %1$s or higher.', 'task-scheduler' ),
				// ),
				'functions' => array(
					'curl_version' => sprintf( __( 'The plugin requires the %1$s to be installed.', 'task-scheduler' ), 'the cURL library' ),
				),
				// 'classes' => array(
					// 'DOMDocument' => sprintf( __( 'The plugin requires the <a href="%1$s">libxml</a> extension to be activated.', 'pseudo-image' ), 'http://www.php.net/manual/en/book.libxml.php' ),
				// ),
				'constants'	=> array(),
			)
		);	
		
	}

	/**
	 * The plugin activation callback method.
	 */	
	public function _replyToDoWhenPluginActivates() {
		
		// In case the server disables wp-cron, start the heartbeat manually.
		add_action( 'shutdown', array( $this, '_replyToStartServerHeartbeat' ) );
		
		// Schedule a heartbeat resuming event.
		if ( ! wp_next_scheduled( 'task_scheduler_action_check_heartbeat_hourly' ) ) {
			
			// Run the server heartbeat with the stored options. (if options are not set, it will use  the default settings.)
			wp_schedule_single_event( time(), 'task_scheduler_action_check_heartbeat_hourly' );					
			
			// Schedule hourly heartbeat checking event
			wp_schedule_event( time(), 'hourly', 'task_scheduler_action_check_heartbeat_hourly' );
			
		}		
		
	}
		public function _replyToStartServerHeartbeat() {

			$this->_replyToLoadPluginComponents();
			TaskScheduler_Event_ServerHeartbeat_Resumer::resume();
			
		}
	/**
	 * The plugin deactivation callback method.
	 */
	public function _replyToDoWhenPluginDeactivates() {
							
		// Delete transients
		TaskScheduler_WPUtility::clearTransients( TaskScheduler_Registry::TransientPrefix );

		// Remove the server heartbeat resume WP Cron event.
		TaskScheduler_WPUtility::unscheduleWPCronEventsByName( 'task_scheduler_action_check_heartbeat_hourly' );
		
		// Delete options
		if ( TaskScheduler_Option::get( array( 'reset', 'reset_upon_deactivation' ) ) ) {
			delete_option( TaskScheduler_Registry::OptionKey );
		}
	}	
	
	/**
	 * Load localization files.
	 */
	private function _localize() {
		
		load_plugin_textdomain( 
			TaskScheduler_Registry::TextDomain, 
			false, 
			dirname( plugin_basename( $this->_sFilePath ) ) . '/language/'
		);
		
		if ( $this->_bIsAdmin ) {
			load_plugin_textdomain( 
				'admin-page-framework', 
				false, 
				dirname( plugin_basename( $this->_sFilePath ) ) . '/language/'
			);		
		}
		
	}		
	
	/**
	 * Loads the plugin specific components. 
	 * 
	 * @remark		All the necessary classes should have been already loaded.
	 */
	public function _replyToLoadPluginComponents() {

		// 1. Events - handles background processes and hooks. This should be loaded earlier than the admin classes as some callbacks use the hooks of the admin page framework.
		new TaskScheduler_Event;	

		// 2. Post types - we have three custom post types. One is for tasks, another is for threads, and the last is for logs.
		new TaskScheduler_PostType_Task( TaskScheduler_Registry::PostType_Task, null, $this->_sFilePath );
		new TaskScheduler_PostType_Thread( TaskScheduler_Registry::PostType_Thread, null, $this->_sFilePath );
		new TaskScheduler_PostType_Log( TaskScheduler_Registry::PostType_Log, null, $this->_sFilePath );
		
		// 3. Admin pages
		if ( $this->_bIsAdmin ) {

			// 3.1. Root
			$this->oAdminPage = new TaskScheduler_AdminPage( '', $this->_sFilePath );

			// 3.2. Add New
			new TaskScheduler_AdminPage_Wizard( '', $this->_sFilePath );		// passing an empty string will disable saving options.
			
			// 3.3. Edit Module Options
			new TaskScheduler_AdminPage_EditModule( '', $this->_sFilePath );	// passing an empty string will disable saving options.
			
			// 3.4. Settings
			new TaskScheduler_AdminPage_Setting( TaskScheduler_Registry::OptionKey, $this->_sFilePath );
			
			// 3.5. System - will be implemented at some point in the future.
			// new TaskScheduler_AdminPage_System( TaskScheduler_Registry::OptionKey, $this->_sFilePath );	
			
			// 3.6. Meta Boxes for task editing page (post.php).
			$this->_registerMetaBoxes();
		
		}			
		
		// Modules should use this hook.
		do_action( 'task_scheduler_action_after_loading_plugin' );
		
	}

		/**
		 * Registers meta boxes.
		 */
		protected function _registerMetaBoxes() {
			
			if ( ! isset( $GLOBALS['pagenow'] ) || 'post.php' !== $GLOBALS['pagenow'] ) {
				return;
			}
						
			new TaskScheduler_MetaBox_Main(
				'task_scheduler_meta_box_main',
				__( 'Main', 'task-scheduler' ),
				array( TaskScheduler_Registry::PostType_Task, TaskScheduler_Registry::PostType_Thread ),
				'normal',	// context
				'high'
			);		
			new TaskScheduler_MetaBox_Occurrence(
				'task_scheduler_meta_box_occurrence',
				__( 'Occurrence', 'task-scheduler' ),
				array( TaskScheduler_Registry::PostType_Task, TaskScheduler_Registry::PostType_Thread ),
				'normal',	// context
				'default'		// priority
			);
			new TaskScheduler_MetaBox_Action(
				'task_scheduler_meta_box_action',
				__( 'Action', 'task-scheduler' ),
				array( TaskScheduler_Registry::PostType_Task, TaskScheduler_Registry::PostType_Thread ),
				'normal',	// context
				'low'		// priority
			);			
			new TaskScheduler_MetaBox_Advanced(
				'task_scheduler_meta_box_advanced',
				__( 'Advanced', 'task-scheduler' ),
				array( TaskScheduler_Registry::PostType_Task, TaskScheduler_Registry::PostType_Thread ),
				'advanced',	// context
				'default'
			);
			new TaskScheduler_MetaBox_Submit(
				'task_scheduler_meta_box_submit',
				__( 'Update', 'task-scheduler' ),
				array( TaskScheduler_Registry::PostType_Task, TaskScheduler_Registry::PostType_Thread ),
				'side',
				'high'
			);
		
		} 
		
}