<?php
/**
 * Provides the common data shared among plugin files.
 * 
 * To use the class, first call the setUp() static method, which sets up the necessary properties.
 * 
 * @package			Task Scheduler
 * @copyright   	Copyright (c) 2014, <Michael Uno>
 * @author			Michael Uno
 * @authorurl		http://michaeluno.jp
 * @since			1.0.0
*/
final class TaskScheduler_Registry {

	const OptionKey				= 'ts_option';
	const TransientPrefix		= 'TS_';	// Up to 8 as transient name allows 45 characters or less ( 40 for site transients ) so that md5 (32 characters) can be added
	const AdminPage_Root		= 'TaskScheduler_AdminPage';
	const AdminPage_TaskList	= 'ts_task_list';
	const AdminPage_AddNew		= 'ts_add_new';
	const AdminPage_EditModule	= 'ts_edit_module';
	const AdminPage_Setting		= 'ts_settings';
	const AdminPage_System		= 'ts_system';
	const TextDomain			= 'task-scheduler';
	const PostType_Task			= 'ts_task';		// up to 20 characters
	const PostType_Thread		= 'ts_thread';		// up to 20 characters
	const PostType_Log			= 'ts_log';			// up to 20 characters
	const Taxonomy_SystemLabel	= 'task_scheduler_system_label';
	
	// These properties will be defined when performing setUp() method.
	static public $sPluginFilePath ='';	// must set a value as it will be checked in setUp()
	static public $sPluginDirPath ='';
	static public $sPluginName ='';
	static public $sPluginURI ='';
	static public $sPluginDescription ='';
	static public $sPluginAuthor ='';
	static public $sPluginAuthorURI ='';
	static public $sPluginVersion ='';
	static public $sPluginTextDomain ='';
	static public $sPluginDomainPath ='';
	static public $sPluginNetwork ='';
	static public $sPluginSiteWide ='';
	static public $sPluginStoreURI ='';

	static function setUp( $sPluginFilePath=null ) {
		
		self::$sPluginFilePath = $sPluginFilePath
			? $sPluginFilePath 
			: dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'responsive-grid-system.php' ;
		self::$sPluginDirPath = dirname( self::$sPluginFilePath );
		self::$sPluginURI = plugins_url( '', self::$sPluginFilePath );
		
		$_aPluginData = get_file_data( 
			self::$sPluginFilePath, 
			array(
				'sPluginName'				=> 'Plugin Name',
				'sPluginURI'				=> 'Plugin URI',
				'sPluginVersion'			=> 'Version',
				'sPluginDescription'		=> 'Description',
				'sPluginAuthor'				=> 'Author',
				'sPluginAuthorURI'			=> 'Author URI',
				'sPluginTextDomain'			=> 'Text Domain',
				'sPluginDomainPath'			=> 'Domain Path',
				'sPluginNetwork'			=> 'Network',
				'sPluginSiteWide'			=> 'Site Wide Only',	// Site Wide Only is deprecated in favor of Network.
				'sPluginStoreURI'			=> 'Store URI',
				'sRequiredPHPVersion'		=> 'Required PHP Version',
				'sRequiredWordPressVersion' => 'Required WordPress Version',
			),
			'' 	// do not give a context
		);
		
		foreach( $_aPluginData as $sKey => $sValue ) {
			if ( isset( self::${$sKey} ) ) {	// must be checked as get_file_data() returns a filtered result
				self::${$sKey} = $sValue;
			}	
		}
	}	
	
	public static function getPluginURL( $sRelativePath='' ) {
		return plugins_url( $sRelativePath, self::$sPluginFilePath );
	}

	
}