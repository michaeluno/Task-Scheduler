<?php
/**
 * The class that defines the Debug action for the Task Scheduler plugin.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

class TaskScheduler_Action_Debug extends TaskScheduler_Action_Base {

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
		return __( 'Debug', 'task-scheduler' );
	}
	
	/**
	 * Returns the description of the module.
	 */
	public function getDescription( $sDescription ) {
		return __( 'Creates a log file in the wp-content folder.', 'task-scheduler' );
	}
	
	/**
	 * Defines the behavior of the action.
	 */
	public function doAction( $isExitCode, $oTask ) {

		static $_iPageLoadID;
		$_iPageLoadID = $_iPageLoadID ? $_iPageLoadID : uniqid();		
		
		$_oCallerInfo = debug_backtrace();
		$_sCallerFunction = isset( $_oCallerInfo[ 1 ]['function'] ) ? $_oCallerInfo[ 1 ]['function'] : '';
		$_sCallerClasss = isset( $_oCallerInfo[ 1 ]['class'] ) ? $_oCallerInfo[ 1 ]['class'] : '';

		file_put_contents( 
			WP_CONTENT_DIR . DIRECTORY_SEPARATOR . get_class() . '_' . date( "Ymd" ) . '.log', 
			date( "Y/m/d H:i:s", current_time( 'timestamp' ) ) . ' ' . "{$_iPageLoadID} {$_sCallerClasss}::{$_sCallerFunction} " . self::getCurrentURL() . PHP_EOL	
			. print_r( $oTask->getMeta(), true ) . PHP_EOL . PHP_EOL,
			FILE_APPEND 
		);					
		
		// sleep( 60 )	; // simulate being hung
		
		return 1;	// the exit code.
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
