<?php
/**
 * Cleans up the plugin options.
 *    
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2013-2016, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/* 
 * Plugin specific constant. 
 * We are going to load the main file to get the registry class. And in the main file, 
 * if this constant is set, it will return after declaring the registry class.
 **/
if ( ! defined( 'DOING_PLUGIN_UNINSTALL' ) ) {
    define( 'DOING_PLUGIN_UNINSTALL', true  );
}

/**
 * Set the main plugin file name here.
 */
$_sMaingPluginFileName  = 'task-scheduler.php';
if ( file_exists( dirname( __FILE__ ). '/' . $_sMaingPluginFileName ) ) {
   include( $_sMaingPluginFileName );
}

if ( ! class_exists( 'TaskScheduler_Registry' ) ) {
    return;
}

// 1. Delete transients
$_aPrefixes = array(
    TaskScheduler_Registry::TRANSIENT_PREFIX, // the plugin transients
    'apf_',      // the admin page framework transients
);
foreach( $_aPrefixes as $_sPrefix ) {
    if ( ! $_sPrefix ) { 
        continue; 
    }
    $GLOBALS['wpdb']->query( "DELETE FROM `" . $GLOBALS[ 'table_prefix' ] . "options` WHERE `option_name` LIKE ( '_transient_%{$_sPrefix}%' )" );
    $GLOBALS['wpdb']->query( "DELETE FROM `" . $GLOBALS[ 'table_prefix' ] . "options` WHERE `option_name` LIKE ( '_transient_timeout_%{$_sPrefix}%' )" );
}

// 2. Delete options
$_aOptions = get_option( TaskScheduler_Registry::$aOptionKeys[ 'main' ], array() );
$_bDelete  = isset( $_aOptions[ 'reset' ][ 'reset_on_uninstall' ] ) 
    ? $_aOptions[ 'reset' ][ 'reset_on_uninstall' ]
    : false;

if ( ! $_bDelete ) {
    return;
}
    
array_walk_recursive( 
    TaskScheduler_Registry::$aOptionKeys, // subject array
    'delete_option'   // function name
);
