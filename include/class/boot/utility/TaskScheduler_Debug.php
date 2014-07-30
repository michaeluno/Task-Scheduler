<?php
/**
 * The class that provides debugging method.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

final class TaskScheduler_Debug {
	
	static public function dump( $v, $sFilePath=null ) {
		
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
		
		echo self::get( $v, $sFilePath );
		
	}

	static public function get( $v, $sFilePath=null ) {
		
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
		
		if ( $sFilePath ) {
			self::log( $v, $sFilePath );			
		}
			
		// esc_html() has a bug that breaks with complex HTML code.
		return "<div><pre class='dump-array'>" . htmlspecialchars( print_r( $v, true ) ) . "</pre><div>";	
		
	}
					
	static public function log( $v, $sFilePath=null ) {
		
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
		
		static $_iPageLoadID;	// identifies the page load.
		$_iPageLoadID		= $_iPageLoadID ? $_iPageLoadID : uniqid();		
		$_oCallerInfo		= debug_backtrace();
		$_sCallerFunction	= isset( $_oCallerInfo[ 1 ]['function'] ) ? $_oCallerInfo[ 1 ]['function'] : '';
		$_sCallerClasss		= isset( $_oCallerInfo[ 1 ]['class'] ) ? $_oCallerInfo[ 1 ]['class'] : '';
		$sFilePath 			= ! $sFilePath
			? WP_CONTENT_DIR . DIRECTORY_SEPARATOR . get_class() . '_' . $_sCallerClasss . '_' . date( "Ymd" ) . '.log'
			: ( true === $sFilePath
				? WP_CONTENT_DIR . DIRECTORY_SEPARATOR . get_class() . '_' . date( "Ymd" ) . '.log'
				: $sFilePath
			);
		$_sHeading = date( "Y/m/d H:i:s", current_time( 'timestamp' ) ) . ' ' 
			. "{$_iPageLoadID} {$_sCallerClasss}::{$_sCallerFunction} " 
			. current_filter() . ' '
			. self::getCurrentURL();
		file_put_contents( 
			$sFilePath, 
			$_sHeading . PHP_EOL . print_r( $v, true ) . PHP_EOL . PHP_EOL,
			FILE_APPEND 
		);			
	}	
	
	/**
	 * Retrieves the currently loaded page url.
	 */
	static public function getCurrentURL() {
		$sSSL = ( !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? true:false;
		$sServerProtocol = strtolower( $_SERVER['SERVER_PROTOCOL'] );
		$sProtocol = substr( $sServerProtocol, 0, strpos( $sServerProtocol, '/' ) ) . ( ( $sSSL ) ? 's' : '' );
		$sPort = $_SERVER['SERVER_PORT'];
		$sPort = ( ( !$sSSL && $sPort=='80' ) || ( $sSSL && $sPort=='443' ) ) ? '' : ':' . $sPort;
		$sHost = isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		return $sProtocol . '://' . $sHost . $sPort . $_SERVER['REQUEST_URI'];
	}	
	
}