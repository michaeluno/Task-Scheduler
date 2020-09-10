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
    static public function get( $v, $sFilePath=null, $bEscape=true, $iStringLengthLimit=0, $iArrayDepthLimit=0 ) {
        
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return '';
        }
        return parent::get( $v, $sFilePath, $bEscape, $iStringLengthLimit, $iArrayDepthLimit );
        
    }
                    
    static public function log( $v, $sFilePath=null, $iTrace=0, $iStringLengthLimit=99999, $iArrayDepthLimit=50 ) {
        
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

        parent::log( $v, $sFilePath, $iTrace, $iStringLengthLimit, $iArrayDepthLimit );

    }
    
}