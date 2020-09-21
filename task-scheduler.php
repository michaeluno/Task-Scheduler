<?php
/** 
 *  Plugin Name:    Task Scheduler
 *  Plugin URI:     http://en.michaeluno.jp/
 *  Description:    Provides an enhanced task management system for WordPress.
 *  Author:         miunosoft (Michael Uno)
 *  Author URI:     http://michaeluno.jp
 *  Version:        1.5.2b01
 */

/**
 * The base class of the registry class which provides basic plugin information.
 * 
 * The inclusion list generator script also refer to the constants. 
 */
class TaskScheduler_Registry_Base {

    const VERSION        = '1.5.2b01';    // <--- DON'T FORGET TO CHANGE THIS AS WELL!!
    const NAME           = 'Task Scheduler';
    const DESCRIPTION    = 'Provides an enhanced task management system for WordPress.';
    const URI            = 'http://en.michaeluno.jp/';
    const AUTHOR         = 'miunosoft (Michael Uno)';
    const AUTHOR_URI     = 'http://en.michaeluno.jp/';
    const COPYRIGHT      = 'Copyright (c) 2014-2016, Michael Uno';
    const LICENSE        = 'GPL v2 or later';
    const CONTRIBUTORS   = '';
    
}

/**
 * Provides plugin information.
 */
final class TaskScheduler_Registry extends TaskScheduler_Registry_Base {
            
    // The plugin itself uses these values.
    const TRANSIENT_PREFIX          = 'TS_';    // Up to 8 characters as transient name allows 45 characters or less ( 40 for site transients ) so that md5 (32 characters) can be added
    const TEXT_DOMAIN               = 'task-scheduler';
    const TEXT_DOMAIN_PATH          = '/language';
        
    // These properties will be defined in the setUp() method.
    static public $sFilePath = '';
    static public $sDirPath  = '';    
        
    /**
     * The plugin option key used for the options table.
     */
    static public $aOptionKeys = array(
        'main'    => 'ts_option',
    );
    
    /**
     * Used admin pages.
     */
    static public $aAdminPages = array(
        // key => 'page slug'
        'root'          => 'TaskScheduler_AdminPage',    // the root menu page slug
        'task_list'     => 'ts_task_list',
        'add_new'       => 'ts_add_new',
        'edit_module'   => 'ts_edit_module',
        'setting'       => 'ts_settings',
        'system'        => 'ts_system',
    );
        // Backward compatibility
        const AdminPage_AddNew          = 'ts_add_new';
        
    /**
     * Used post types.
     * 
     * @remark      each slug character length should not exceed 20 characters.
     */
    static public $aPostTypes = array(
        'task'      => 'ts_task',
        'routine'   => 'ts_routine',
        'thread'    => 'ts_thread',
        'log'       => 'ts_log',
    );

    /**
     * Used taxonomies.
     */
    static public $aTaxonomies = array(
        'system'    => 'task_scheduler_system_label',
    );

    /**
     * Requirements.
     */    
    static public $aRequirements = array(
        'php' => array(
            'version'   => '5.2.4',
            'error'     => 'The plugin requires the PHP version %1$s or higher.',
        ),
        'wordpress'         => array(
            'version'   => '3.7',
            'error'     => 'The plugin requires the WordPress version %1$s or higher.',
        ),
        'mysql'             => array(
            'version'   => '5.0',
            'error'     => 'The plugin requires the MySQL version %1$s or higher.',
        ),
        'functions'         => '', // disabled
        // array(
            // e.g. 'mblang' => 'The plugin requires the mbstring extension.',
        // ),
        'classes'           => '', // disabled
        // array(
            // e.g. 'DOMDocument' => 'The plugin requires the DOMXML extension.',
        // ),
        'constants'         => '', // disabled
        // array(
            // e.g. 'THEADDONFILE' => 'The plugin requires the ... addon to be installed.',
            // e.g. 'APSPATH' => 'The script cannot be loaded directly.',
        // ),
        'files'             => '', // disabled
        // array(
            // e.g. 'home/my_user_name/my_dir/scripts/my_scripts.php' => 'The required script could not be found.',
        // ),
    );  
        
    /**
     * Sets up static properties.
     */
    static function setUp( $sPluginFilePath=null ) {
                        
        self::$sFilePath = $sPluginFilePath ? $sPluginFilePath : __FILE__;
        self::$sDirPath  = dirname( self::$sFilePath );
        
    }    
    
    /**
     * Returns the URL with the given relative path to the plugin path.
     * 
     * Example:  TaskScheduler_Registry::getPluginURL( 'asset/css/meta_box.css' );
     */
    static public function getPluginURL( $sRelativePath='' ) {
        return plugins_url( $sRelativePath, self::$sFilePath );
    }

    /**
     * Returns an information array of this class.
     * 
     * @since       1.0.0
     * @return      array
     */
    static public function getInfo() {
        $_oReflection = new ReflectionClass( __CLASS__ );
        return $_oReflection->getConstants()
            + $_oReflection->getStaticProperties()
        ;
    }        
    
}
 
// Return if accessed directly. Do not exit as the header class for the inclusion script need to access the registry class.
if ( ! defined( 'ABSPATH' ) ) { 
    return; 
}
TaskScheduler_Registry::setUp( __FILE__ );

// Run the bootstrap script.
include( dirname( __FILE__ ) . '/include/library/apf/admin-page-framework.php' );
include( dirname( __FILE__ ) . '/include/class/TaskScheduler_Bootstrap.php' );    
new TaskScheduler_Bootstrap( __FILE__ );
