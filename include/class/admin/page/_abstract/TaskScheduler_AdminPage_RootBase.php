<?php
/**
 * Task Scheduler
 * 
 * Provides an enhanced task management system for WordPress.
 * 
 * http://en.michaeluno.jp/amazon-auto-inks/
 * Copyright (c) 2014-2016 Michael Uno; Licensed GPLv2
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
     * @since       1.4.0
     * @return      void
     */
    protected function construct( $oFactory ) {}
    
}