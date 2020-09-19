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

        self::$_bIsNetworkAdmin = isset( self::$_bIsNetworkAdmin ) 
            ? self::$_bIsNetworkAdmin 
            : is_network_admin();

        $_vTransient = self::$_bIsNetworkAdmin 
            ? delete_site_transient( $sTransientKey ) 
            : delete_transient( $sTransientKey );

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
        $_wp_using_ext_object_cache  = false;

        self::$_bIsNetworkAdmin = isset( self::$_bIsNetworkAdmin ) 
            ? self::$_bIsNetworkAdmin 
            : is_network_admin();

        $_vTransient = self::$_bIsNetworkAdmin 
            ? get_site_transient( $sTransientKey ) 
            : get_transient( $sTransientKey );    

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
        $_wp_using_ext_object_cache  = false;

        self::$_bIsNetworkAdmin = isset( self::$_bIsNetworkAdmin ) ? self::$_bIsNetworkAdmin : is_network_admin();
        
        $_vTransient = self::$_bIsNetworkAdmin
            ? set_site_transient( $sTransientKey, $vValue, $iExpiration ) 
            : set_transient( $sTransientKey, $vValue, $iExpiration );

        // reset prior value of $_wp_using_ext_object_cache
        $_wp_using_ext_object_cache = $_bWpUsingExtObjectCacheTemp; 

        return $_vTransient;     
        
    }
 
    /**
     * @param $sTransientKey
     * @param null $mDefault
     * @return array
     * @since 1.5.1
     */
    static public function getTransientWithoutCacheAsArray( $sTransientKey, $mDefault=null ) {
        return self::getAsArray(
            self::getTransientWithoutCache( $sTransientKey, $mDefault )
        );
    }

    /**
     * Retrieve the transient value directly from the database.
     *
     * Similar to the built-in get_transient() method but this one does not use the stored cache in the memory.
     * Used for checking a lock in a sub-routine that should not run simultaneously.
     *
     * @param   string  $sTransientKey
     * @param   mixed   $mDefault
     * @sicne   1.5.0
     * @since   1.5.1   Added the `$mDefault` parameter.
     * @return  mixed
     */
    static public function getTransientWithoutCache( $sTransientKey, $mDefault=null ) {

        /**
         * @var wpdb $_oWPDB
         */
        $_oWPDB         = $GLOBALS[ 'wpdb' ];
        $_sTableName    = $_oWPDB->options;
        $_sSQLQuery     = "SELECT o1.option_value FROM `{$_sTableName}` o1"
            . " INNER JOIN `{$_sTableName}` o2"
            . " WHERE o1.option_name = %s "
            . " AND o2.option_name = %s "
            . " AND o2.option_value >= UNIX_TIMESTAMP() " // timeout value >= current time
            . " LIMIT 1";
        $_mData = $_oWPDB->get_var(
            $_oWPDB->prepare(
                $_sSQLQuery,
                '_transient_' . $sTransientKey,
                '_transient_timeout_' . $sTransientKey
            )
        );
        return is_null( $_mData )
            ? $mDefault
            : maybe_unserialize( $_mData );

    }

    
}