<?php
/**
 * Task Scheduler
 * 
 * @copyright    Copyright (c) 2014-2020, Michael Uno
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 */

/**
 * Defines the Transient Cleaner action for the Task Scheduler plugin.
 * 
 * @since      1.1.0
 */
class TaskScheduler_Action_TransientCleaner extends TaskScheduler_Action_Base {
        
    /**
     * The user constructor.
     * 
     * This method is automatically called at the end of the class constructor.
     */
    public function construct() {}
    
    /**
     * Returns the readable label of this action.
     * 
     * This will be called when displaying the action in an pull-down select option, task listing table, or notification email message.
     */
    public function getLabel( $sLabel ) {
        return __( 'Clean Transients', 'task-scheduler' );
    }
    
    /**
     * Returns the description of the module.
     */
    public function getDescription( $sDescription ) {
        return __( 'Deletes expired transient items.', 'task-scheduler' );
    }    
        
    /**
     * Defines the behavior of the task action.
     */
    public function doAction( $isExitCode, $oRoutine ) {
        
        $_aTaskMeta = $oRoutine->getMeta();
        
        // Check required arguments.
        if ( 
            ! isset( 
                $_aTaskMeta[ $this->sSlug ],
                $_aTaskMeta[ $this->sSlug ][ 'transient_prefix' ],
                $_aTaskMeta[ $this->sSlug ][ 'transient_type' ]
            ) 
        ) {
            return 0;    // failed
        }
        
        // Clean transients.
        $_iCountSiteTransients    = 0;
        if ( '2' !== ( string ) $_aTaskMeta[ $this->sSlug ][ 'transient_type' ] ) {
            $_iCountSiteTransients = $this->_cleanExpiredTransients_Site( $_aTaskMeta[ $this->sSlug ][ 'transient_prefix' ] );
        }
        $_iCountNetworkTransients = 0;
        if ( '1' !== ( string ) $_aTaskMeta[ $this->sSlug ][ 'transient_type' ] ) {
            $_iCountNetworkTransients = $this->_cleanExpiredTransients_Network( $_aTaskMeta[ $this->sSlug ][ 'transient_prefix' ] );
        }
        
        // Log
        $oRoutine->log( 
            sprintf(
                __( 'Cleaned %1$s transients.', 'task-scheduler' ),
                $_iCountSiteTransients + $_iCountNetworkTransients
            ) 
        );
     
        // Exit Code
        return 1;
        
    }
    
        /**
         * 
         * @since       1.1.0
         * @return      integer     The total found expired transients.
         */
        private function _cleanExpiredTransients_Site( $sTransientPrefix ) {
         
            global $wpdb;

            $sTransientPrefix = str_replace( 
                '_',
                '\_',
                $sTransientPrefix
            );
            $_aTransientNames  = $wpdb->get_col(
                $wpdb->prepare(
                    "
                    SELECT REPLACE(option_name, '_transient_timeout_', '') AS transient_name
                    FROM {$wpdb->options}
                    WHERE option_name LIKE '\_transient\_timeout\_{$sTransientPrefix}_%%'
                    AND option_value < %s
                    ",
                    time()   // current timestamp
                )
            );
            foreach( $_aTransientNames as $_sTransientName ) {
                get_transient( $_sTransientName );
            }
            return count( $_aTransientNames );
            
        }    
        /**
         * 
         * @since       1.1.0
         * @return      integer     The total found expired transients.
         */
        private function _cleanExpiredTransients_Network( $sTransientPrefix ) {
         
            if ( ! is_multisite() ) {
                return 0;
            }
            
            global $wpdb;

            $sTransientPrefix = str_replace( 
                '_',
                '\_',
                $sTransientPrefix
            );
            $_aTransientNames  = $wpdb->get_col(
                $wpdb->prepare(
                    "
                    SELECT REPLACE(meta_key, '_site_transient_timeout_', '') AS transient_name
                    FROM {$wpdb->sitemeta}
                    WHERE meta_key LIKE '\_site\_transient\_timeout\_{$sTransientPrefix}_%%'
                    AND meta_value < %s
                    ",
                    time()   // current timestamp
                )
            );            
            foreach ( $_aTransientNames as $_sTransientName ) {
                get_site_transient( $_sTransientName );
            }            
            return count( $_aTransientNames );
            
        }    
        
}