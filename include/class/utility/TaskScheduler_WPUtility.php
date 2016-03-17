<?php
/**
 * The class that provides utility methods which use WordPress built-in functions.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

class TaskScheduler_WPUtility extends TaskScheduler_WPUtility_Option {

    /**
     * Returns the label of the given taxonomy slug.
     * 
     * This is used as the labels to construct a 'select' input field.
     * 
     * @since            1.0.0
     */
    static public function getTaxonomiyLabelBySlug( $sTaxonomySlug ) {
        
        $_oTaxonomy = get_taxonomy( $sTaxonomySlug );
        return isset( $_oTaxonomy->labels->name )
            ? $_oTaxonomy->labels->name
            : '';
        
    }
    
    /**
     * Returns an array holding the list of associated taxonomies of the specified post type.
     * 
     * This is used as the labels to construct a 'select' input field.
     * 
     * @since            1.0.0
     */
    static public function getTaxonomiesByPostTypeSlug( $sPostTypeSlug ) {
        
        $_aLabels = array();
        $_aTaxonomyObjects = get_object_taxonomies( $sPostTypeSlug, 'objects' );
        foreach( $_aTaxonomyObjects as $_sTaxonomySlug => $_oTaxonomy ) {
            if ( 'post_format' === $_sTaxonomySlug ) {
                continue;
            }
            if ( ! isset( $_oTaxonomy->labels->name ) ) { 
                continue;
            }
            $_aLabels[ $_sTaxonomySlug ] = $_oTaxonomy->labels->name;
        }        
        return $_aLabels;
        
    }    
    
    /**
     * Returns the post type label by the given post type slug.
     * 
     * @since            1.0.0
     */
    static public function getPostTypeLabel( $sPostTypeSlug )  {
        
        $_oPostType = get_post_type_object( $sPostTypeSlug );
        return isset( $_oPostType->labels->singular_name )
            ? $_oPostType->labels->singular_name
            : '';
        
    }    
    
    /**
     * Removes the transient rows stored in the options database table with the given prefix string.
     */
    static public function clearTransients( $sPrefix )  {
        
        if ( ! isset( $GLOBALS['wpdb'], $GLOBALS['table_prefix'] ) ) { 
            return;
        }
        
        $GLOBALS['wpdb']->query( "DELETE FROM `" . $GLOBALS['table_prefix'] . "options` WHERE `option_name` LIKE ( '_transient_%{$sPrefix}%' )" );
        $GLOBALS['wpdb']->query( "DELETE FROM `" . $GLOBALS['table_prefix'] . "options` WHERE `option_name` LIKE ( '_transient_timeout_%{$sPrefix}%' )" );        
        
    }
        
    /**
     * Returns the readable date-time string.
     */
    static public function getRedableMySQLDate( $sMySQLDate, $sDateTimeFormat=null, $bAdjustGMT=false ) { 

        return self::getSiteReadableDate( 
            mysql2date( 'U' , $sMySQLDate ), 
            $sDateTimeFormat, 
            $bAdjustGMT 
        );
    
    }
        
    /**
     * Returns the readable date-time string.
     */
    static public function getSiteReadableDate( $iTimeStamp, $sDateTimeFormat=null, $bAdjustGMT=false ) {
                
        static $_iOffsetSeconds, $_sDateFormat, $_sTimeFormat;
        $_iOffsetSeconds = $_iOffsetSeconds 
            ? $_iOffsetSeconds 
            : get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
        $_sDateFormat = $_sDateFormat
            ? $_sDateFormat
            : get_option( 'date_format' );
        $_sTimeFormat = $_sTimeFormat
            ? $_sTimeFormat
            : get_option( 'time_format' );    
        $sDateTimeFormat = $sDateTimeFormat
            ? $sDateTimeFormat
            : $_sDateFormat . ' ' . $_sTimeFormat;
        
        if ( ! $iTimeStamp ) {
            return 'n/a';
        }
        $iTimeStamp = $bAdjustGMT ? $iTimeStamp + $_iOffsetSeconds : $iTimeStamp;
        return date_i18n( $sDateTimeFormat, $iTimeStamp );
            
    }
    
    /**
     * Unschedules WP Cron events by action name.
     * 
     * Sometimes multiple events are scheduled for an action name. In that case, using wp_clear_scheduled_hook() does 
     * not clear all the scheduled ones and an event remains. This method is to prevent such an issue.
     * 
     */
    static public function unscheduleWPCronEventsByName( $asEventNames ) {
        
        $aEventNames = ( array ) $asEventNames;
        $_aCronEvents = _get_cron_array();
        foreach ( $_aCronEvents as $__nTimeStamp => $__aEvent ) {
            
            foreach( $aEventNames as $sEventName ) {
                if ( isset( $_aCronEvents[ $__nTimeStamp ][ $sEventName ] ) ) {
                    unset( $_aCronEvents[ $__nTimeStamp ] );
                }
            }
            
        }
        _set_cron_array( $_aCronEvents );
    }        
    
}