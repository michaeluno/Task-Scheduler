<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * http://en.michaeluno.jp/amazon-auto-inks/
 * Copyright (c) 2014-2020 Michael Uno; Licensed GPLv2
 */

/**
 * Provides an abstract base for bases.
 * 
 * @since       1.4.0
 */
abstract class TaskScheduler_AdminPage_RootBase extends TaskScheduler_PluginUtility {
    
    /**
     * Stores callback method names.
     * 
     * @since   1.4.0
     */
    protected $aMethods = array(
        'replyToLoadPage',
        'replyToDoPage',
        'replyToDoAfterPage',
        'replyToLoadTab',
        'replyToDoTab',
        'validate',
    );

    /**
     * Handles callback methods.
     * @since       1.4.0
     * @return      mixed
     * @param       string $sMethodName
     * @param       array  $aArguments
     */
    public function __call( $sMethodName, $aArguments ) {
        
        if ( in_array( $sMethodName, $this->aMethods ) ) {
            return isset( $aArguments[ 0 ] ) 
                ? $aArguments[ 0 ] 
                : null;
        }       
        
        trigger_error( 
            TaskScheduler_Registry::NAME . ' : ' . sprintf( 
                __( 'The method is not defined: %1$s', 'task-scheduler' ),
                $sMethodName 
            ), 
            E_USER_WARNING 
        );        
    }

    /**
     * A user constructor.
     * @param TaskScheduler_AdminPageFramework $oFactory
     * @return      void
     * @since       1.4.0
     * @since       1.5.2   Renamed from `construct()` (added an underscore prefix).
     */
    protected function _construct( $oFactory ) {}

    /**
     * @since  1.5.21
     * @return array
     */
    protected function _getArguments() {
        return array();
    }
    
}