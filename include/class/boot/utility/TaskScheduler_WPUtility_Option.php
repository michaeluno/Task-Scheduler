<?php
/**
 * One of the abstract class providing utility methods which use WordPress built-in functions.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

abstract class TaskScheduler_WPUtility_Option extends TaskScheduler_WPUtility_Post {
 
    /**
     * Stores whether the page is loaded in the network admin or not.
     * @since 1.0.0
     */
    static private $_bIsNetworkAdmin;
    
    /**
     * Deletes the given transient.
     *
     * @since 1.0.0
     */
    static public function deleteTransient( $sTransientKey ) {

        // temporarily disable $_wp_using_ext_object_cache
        global $_wp_using_ext_object_cache;  
        $_bWpUsingExtObjectCacheTemp = $_wp_using_ext_object_cache; 
        $_wp_using_ext_object_cache = false;

        self::$_bIsNetworkAdmin = isset( self::$_bIsNetworkAdmin ) ? self::$_bIsNetworkAdmin : is_network_admin();

        $_vTransient = ( self::$_bIsNetworkAdmin ) ? delete_site_transient( $sTransientKey ) : delete_transient( $sTransientKey );

        // reset prior value of $_wp_using_ext_object_cache
        $_wp_using_ext_object_cache = $_bWpUsingExtObjectCacheTemp; 

        return $_vTransient;
    }
    /**
     * Retrieves the given transient.
     * 
     * @since   1.0.0
     */    
    static public function getTransient( $sTransientKey, $vDefault=null ) {

        // temporarily disable $_wp_using_ext_object_cache
        global $_wp_using_ext_object_cache;  
        $_bWpUsingExtObjectCacheTemp = $_wp_using_ext_object_cache; 
        $_wp_using_ext_object_cache = false;

        self::$_bIsNetworkAdmin = isset( self::$_bIsNetworkAdmin ) ? self::$_bIsNetworkAdmin : is_network_admin();

        $_vTransient = ( self::$_bIsNetworkAdmin ) ? get_site_transient( $sTransientKey ) : get_transient( $sTransientKey );    

        // reset prior value of $_wp_using_ext_object_cache
        $_wp_using_ext_object_cache = $_bWpUsingExtObjectCacheTemp; 

        return null === $vDefault 
            ? $_vTransient
            : ( false === $_vTransient 
                ? $vDefault
                : $_vTransient
            );        
            
    }
    /**
     * Sets the given transient.
     * @since 1.0.0
     */
    static public function setTransient( $sTransientKey, $vValue, $iExpiration=0 ) {

        // temporarily disable $_wp_using_ext_object_cache
        global $_wp_using_ext_object_cache;  
        $_bWpUsingExtObjectCacheTemp = $_wp_using_ext_object_cache; 
        $_wp_using_ext_object_cache = false;

        self::$_bIsNetworkAdmin = isset( self::$_bIsNetworkAdmin ) ? self::$_bIsNetworkAdmin : is_network_admin();
        
        $_vTransient = ( self::$_bIsNetworkAdmin ) ? set_site_transient( $sTransientKey, $vValue, $iExpiration ) : set_transient( $sTransientKey, $vValue, $iExpiration );

        // reset prior value of $_wp_using_ext_object_cache
        $_wp_using_ext_object_cache = $_bWpUsingExtObjectCacheTemp; 

        return $_vTransient;     
    }
 
    /**
     * Retrieve the transient value directly from the database.
     * 
     * Similar to the built-in get_transient() method but this one does not use the stored cache in the memory.
     */
    static public function getTransientWithoutCache( $sTransientKey ) {
    
        if ( wp_using_ext_object_cache() ) {
            // Skip local cache and force re-fetch of doing_cron transient in case
            // another processes updated the cache
            return wp_cache_get( $sTransientKey, 'transient', true );
        }             
    
        global $wpdb;            
        $_oRow = $wpdb->get_row( 
            $wpdb->prepare( 
                "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1",
                '_transient_' . $sTransientKey
            ) 
        );
        return is_object( $_oRow ) ? $_oRow->option_value: false;
        
    }
    
}