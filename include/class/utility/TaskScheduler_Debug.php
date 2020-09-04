<?php
/**
 * The class that provides debugging method.
 * 
 * @package      Task Scheduler
 * @copyright    Copyright (c) 2014-2016, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://michaeluno.jp
 * @since        1.0.0
 */

final class TaskScheduler_Debug extends TaskScheduler_AdminPageFramework_Debug{
    
    static public function dump( $v, $sFilePath=null ) {
        
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }
        parent::dump( $v, $sFilePath );
        
    }

    /**
     * @param $v
     * @param null $sFilePath
     * @param bool $bEscape
     *
     * @return string
     */
    static public function get( $v, $sFilePath=null, $bEscape=true ) {
        
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return '';
        }
        return parent::get( $v, $sFilePath, $bEscape );
        
    }
                    
    static public function log( $v, $sFilePath=null ) {
        
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        $_oCallerInfo        = debug_backtrace();
        $_sCallerClass       = isset( $_oCallerInfo[ 1 ][ 'class' ] ) ? $_oCallerInfo[ 1 ][ 'class' ] : '';
        $sFilePath           = ! $sFilePath
            ? WP_CONTENT_DIR . DIRECTORY_SEPARATOR . get_class() . '_' . $_sCallerClass . '_' . date( "Ymd" ) . '.log'
            : ( true === $sFilePath
                ? WP_CONTENT_DIR . DIRECTORY_SEPARATOR . get_class() . '_' . date( "Ymd" ) . '.log'
                : $sFilePath
            );

        parent::log( $v, $sFilePath );

    }
    
}