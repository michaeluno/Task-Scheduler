<?php
/* 
	Plugin Name:	Task Scheduler (beta)
	Plugin URI:		http://en.michaeluno.jp/
	Description:	Provides a task management system.
	Author:			miunosoft (Michael Uno)
	Author URI:		http://michaeluno.jp
	Version:		1.0.0b08
*/

final class TaskScheduler_Registry {
		
	// Modules may refer to these values.
	const Version					= '1.0.0b07';
	const Name						= 'Task Scheduler';
	const PluginURI					= 'http://en.michaeluno.jp/';
	const Author					= 'miunosoft (Michael Uno)';
	const AuthorURI					= 'http://en.michaeluno.jp/';
	const TextDomainPath			= './language';
	
	// The plugin itself uses these values.
	const OptionKey					= 'ts_option';
	const TransientPrefix			= 'TS_';	// Up to 8 characters as transient name allows 45 characters or less ( 40 for site transients ) so that md5 (32 characters) can be added
	const AdminPage_Root			= 'TaskScheduler_AdminPage';	// the root menu page slug
	const AdminPage_TaskList		= 'ts_task_list';
	const AdminPage_AddNew			= 'ts_add_new';
	const AdminPage_EditModule		= 'ts_edit_module';
	const AdminPage_Setting			= 'ts_settings';
	const AdminPage_System			= 'ts_system';
	const TextDomain				= 'task-scheduler';
	const PostType_Task				= 'ts_task';		// up to 20 characters
	const PostType_Thread			= 'ts_thread';		// up to 20 characters
	const PostType_Log				= 'ts_log';			// up to 20 characters
	const Taxonomy_SystemLabel		= 'task_scheduler_system_label';
	const RequiredPHPVersion		= '5.2.1';
	const RequiredWordPressVersion	= '3.7';
		
	// These properties will be defined in the setUp() method.
	static public $sFilePath	= '';
	static public $sDirPath		= '';
	static public $sFileURI		= '';
	
	/**
	 * Sets up static properties.
	 */
	static function setUp( $sPluginFilePath=null ) {
						
		self::$sFilePath	= $sPluginFilePath ? $sPluginFilePath : __FILE__;
		self::$sDirPath		= dirname( self::$sFilePath );
		self::$sFileURI		= plugins_url( '', self::$sFilePath );
		
	}	
	
	/**
	 * Returns the URL with the given relative path to the plugin path.
	 * 
	 * Example:  TaskScheduler_Registry::getPluginURL( 'asset/css/meta_box.css' );
	 */
	public static function getPluginURL( $sRelativePath='' ) {
		return plugins_url( $sRelativePath, self::$sFilePath );
	}

}

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

TaskScheduler_Registry::setUp( __FILE__ );

include_once( dirname( __FILE__ ). '/include/class/boot/TaskScheduler_Bootstrap.php' );
new TaskScheduler_Bootstrap( __FILE__ );