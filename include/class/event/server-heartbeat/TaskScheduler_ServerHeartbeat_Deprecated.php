<?php
/**
 * The server heart beat class.
 * 
 * @package     Task Scheduler
 * @copyright   Copyright (c) 2014, <Michael Uno>
 * @author		Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		1.0.0
 */

 /**
  * 
  * @action		schedule		task_scheduler_action_check_server_heartbeat		This action will check if the heartbeat is alive and if not, it resumes it.
  * @filter		apply			task_scheduler_serverheartbeat_target_url
  * @filter		apply			task_scheduler_serverheartbeat_cookies
  * 
  */
final class TaskScheduler_ServerHeartbeat {
		
	/**
	 * The key used for the option and some other array elements.
	 */
	static public $sKey = 'TS_server_heartbeat';
	
	/**
	 * The action name used for WP Cron.
	 */
	static public $sActionHook = 'task_scheduler_action_check_server_heartbeat';
	
	/**
	 * Indicates whether the beat should be stopped.
	 */
	static private $_bStop = false;
	
	/**
	 * Stores the target URL to load in the background.
	 */
	static private $_sTargetURL;
					
	/**
	 * Stores the interval to sleep in seconds.
	 */
	static private $_iInterval; 
			
	/**
	 * Tells whether the beat is alive.
	 */
	static public function isAlive() {
		
		if ( wp_next_scheduled( self::$sActionHook ) ) {
TaskScheduler_Debug::log( 'Heartbeat is Alive because checking event is scheduled.' );
			return true;
		}
		
		if ( self::isSleeping() ) {
TaskScheduler_Debug::log( 'Heartbeat is Alive because it is sleeping.' );			
			return true;
		}
		
		$_iLastBeatTime = self::getLastBeatTime();
		$_iInterval = ( int ) self::getInfo( 'interval' );
		$_bIsAlive = ( $_iLastBeatTime + $_iInterval > microtime( true ) );
TaskScheduler_Debug::log( 'Heartbeat is ' . ( $_bIsAlive ? 'Alive' : 'Dead' ) . ', by comparing the current time and the last run time + interval.' );
		return $_bIsAlive;
			
		
	}
	
	/**
	 * Checks if the server heartbeat is sleeping.
	 */
	static public function isSleeping() {

		$_sHeartbeatID = get_transient( self::$sKey . '_sleeping' );
// $_iExpirationTime = get_option( '_transient_timeout_' . self::$sKey . '_sleeping' );
// TaskScheduler_Debug::log( 
	// array(
		// 'sleep id'	=> $_sHeartbeatID,
		// 'expires'	=> TaskScheduler_WPUtility::getSiteReadableDate( $_iExpirationTime, 'Y/m/d G:i:s', true ),		
	// )
// );

// '_transient_timeout_ts_server_heartbeat_sleeping', '1404953256', 'no'
		return ( $_sHeartbeatID );
	}
	
	/**
	 * Returns the heartbeat ID.
	 */
	static public function getBeatID() {
		
		return isset( $_COOKIE{'server_heartbeat_id'} )
			? $_COOKIE{'server_heartbeat_id'}
			: '';
		
	}
	
	/**
	 * Returns the last beat time.
	 */
	static public function getLastBeatTime() {
		
		$_aOptions = get_transient( self::$sKey, array() );
		return isset( $_aOptions[ 'last_beat_time' ] )
			? $_aOptions[ 'last_beat_time' ]
			: null;
		
	}
	
	/**
	 * Returns the next beat checking time.
	 */
	static public function getNextCheckTime() {
		return wp_next_scheduled( self::$sActionHook );
	}
	
	/**
	 * Checks if the page load is in the background.
	 * 
	 * @remark	If the ID is an empty string, this yields false.
	 * @return	boolean	
	 */
	static public function isBackground() {
		return self::getBeatID() ? true : false;
	}
			
	/**
	 * Starts the background periodical calls with the given interval in seconds.
	 * 
	 * @remark	This method can be called both in the background and the foreground.
	 * @return	bool1	true if it started; otherwise, false.
	 */
	static public function run( $sTargetURL='', $iInterval=0 ) {
		
		$sTargetURL = $sTargetURL ? $sTargetURL : self::getInfo( 'target_url' );
		$iInterval = $iInterval ? $iInterval : self::getInfo( 'interval' );
				
		self::_setProperties( $sTargetURL, $iInterval );
		
		// Check if the beat is alive. This checks the interval set in the transient.
		$_fIsAlive = self::isAlive();
		
		// Override the interval.
		self::_saveInfo();

		// Schedule to check the beat
		self::_scheduleToCheckBeat();
		
		// If it is alive, do nothing.
		if ( $_fIsAlive ) {
TaskScheduler_Debug::log( 'heartbeat is alive.' );

			return false;
		} else {
TaskScheduler_Debug::log( 'heartbeat is dead.' );			
		}
		
		// Otherwise, start the beat.	
		add_action( 'shutdown', array( get_class(), '_replyToBeat' ) );
		return true;
		
	}

	/**
	 * Stop the beat.
	 */
	static public function stop() {
		
		self::$_bStop = true;
		
		delete_transient( self::$sKey );
		delete_transient( self::$sKey . '_sleeping' );
		$_iTimestamp = wp_next_scheduled( self::$sActionHook );
		wp_unschedule_event( $_iTimestamp, self::$sActionHook );
TaskScheduler_Debug::log( 'stopping the heartbeat.' );
	}
	
	/**
	 * Checks the heart beat.
	 */
	static public function check() {

		$_sTargetURL	= self::getInfo( 'target_url' );
		$_iInterval		= self::getInfo( 'interval' );
TaskScheduler_Debug::log( 'checking the heartbeat: ' . $_sTargetURL . ': ' . $_iInterval );

		if ( ! $_sTargetURL || ! $_iInterval ) {
TaskScheduler_Debug::log( 'could not load the settings.' );
			return;
		}
		self::run( $_sTargetURL, $_iInterval );
		
	}
	
	/**
	 * Keeps the beat.
	 *  
	 * It is assumed that every page load calls this method so that the heartbeat keeps going.
	 * 
	 * @remark	If this is called not in the background (the user opened a page), this does nothing. 
	 * However, if this is called in the background, meaning the class triggered the page load, it will re-trigger the background page load.
	 */
	static public function pulsate() {
		
		$_sClassName = get_class();	
		
		// If the page load is not in the background, just check to resume the heartbeat.
		if ( ! self::isBackground() ) {
			add_action( self::$sActionHook, array( $_sClassName, 'check' ) );
			return;
		}		
		
		// At this point, the page is loaded in the background.
		// Tell WordPress this is a background task by setting the cron flag.
		if ( ! defined( 'DOING_CRON' ) ) { define( 'DOING_CRON', true ); }				
		
		// Another heartbeat is already running. (this sometimes occurs but not sure why )
		if ( self::isSleeping() ) {
TaskScheduler_Debug::log( 'Another heartbeat is already running' );			
			return;
		}
				
		// If the transient does not exists, it means the user has stopped the beat.
		if ( ! self::getInfo() ) {
TaskScheduler_Debug::log( self::getBeatID() .  'it seems the user has stopped the beat.' );
			self::stop();
			return;
		}
		
		$_sTargetURL	= self::getInfo( 'target_url' );
		$_iInterval		= self::getInfo( 'interval' );		
		if ( ! $_sTargetURL || ! $_iInterval ) {
TaskScheduler_Debug::log( 'the settings seem to be gone.' );
		}
		self::_setProperties( $_sTargetURL, $_iInterval );
		
		// Store the heartbeat information - this is better done before going to sleep because during sleeping the setting may be changed.
		self::_saveInfo();	// this saves the last beat time besides the interval and heartbeat ID etc.
		
// TaskScheduler_Debug::log( self::getBeatID() . ' going to beat.' );
	
		// By hooking wp_loaded, it let the site load other components.		
		set_transient( self::$sKey . '_sleeping', self::getBeatID(), $_iSleepTransientLifespan = ceil( ( int ) $_iInterval ) + 1 );

		add_action( 'wp_loaded', array( $_sClassName, '_replyToSleepAndExit' ), 20 );
				
	}
		
		/**
		 * Exits the script after sleeping the given interval.
		 * 
		 * @remark		This method is only called from the beat() method so it is in the background.
		 */
		static public function _replyToSleepAndExit() {
						
			$_nElapsedTime = timer_stop( 0, 6 );
			$_nSleepDuration = self::$_iInterval - $_nElapsedTime;
			$_nSleepDuration = $_nSleepDuration < 0 ? 0 : $_nSleepDuration;	// avoid the PHP error by passing a negative value.

			// If the interval is longer then the PHP max-execution time, attempt to override it.
			$_iMaxExecutionTime = function_exists( 'ini_get' ) ? ini_get( 'max_execution_time' ) : 15;
			$_iSecondsToLimit = $_iMaxExecutionTime - ceil( $_nElapsedTime );
			if ( $_iSecondsToLimit < $_nSleepDuration ) {
				// Some servers disable this function.
				if ( function_exists( 'ini_set' ) ) {
					// Make the execution time longer.
					ini_set( 'max_execution_time', ceil( $_nSleepDuration + 10 ) );
				} else {
					// Shorten the sleep time.
					$_nSleepDuration = $_iSecondsToLimit - 1;	
				}		
			} 							
			
			// Give the interval - for example, to wait for 2 seconds, pass 2000000. 
			if ( $_nSleepDuration > 0 ) {
TaskScheduler_Debug::log( self::getBeatID() . ' sleeping: ' . $_nSleepDuration );

				set_transient( self::$sKey . '_sleeping', self::getBeatID(), $_iSleepTransientLifespan = ( int ) ceil( $_nSleepDuration ) + 1 );	// be careful not to set 0 for the cache duration so the ceil() function is used here.
// TaskScheduler_Debug::log( 'set the sleep transient:' . $_iSleepTransientLifespan );						
				usleep( $_nSleepDuration  * 1000000 ); 
				delete_transient( self::$sKey . '_sleeping' );
				
			} else {
TaskScheduler_Debug::log( self::getBeatID() . ' sleep duration has been expired: ' . $_nSleepDuration );
				
			}

			// Schedule the beat.
			add_action( 'shutdown', array( get_class(), '_replyToBeat' ) );
			die();			
			
		}

	/**
	 * Does beat.
	 * 
	 * This should be called with the 'shutdown' hook.
	 */
	static public function _replyToBeat() {
		
		// If this have been enabled by the user
		if ( self::$_bStop ) { return; }
		
		// Ensures the task is done only once in a page load.
		static $_bIsCalled;
		if ( $_bIsCalled ) { return; }
		$_bIsCalled = true;				
				
		// For a safety net
		self::_scheduleToCheckBeat();

TaskScheduler_Debug::log( self::getBeatID() . ' beat!' );
		
		// Load the page in the background
		self::loadPage();

	}
		
	/**
	 * Returns the settings of the heart beat.
	 */
	static public function getInfo( $sKey='' ) {
		
		$_aInfo = get_transient( self::$sKey );
		if ( false === $_aInfo ) {
			return false;
		}
		$_aInfo = is_array( $_aInfo ) ? $_aInfo : array();
		if ( ! $sKey ) {
			return $_aInfo;
		}
		return isset( $_aInfo[ $sKey ] )
			? $_aInfo[ $sKey ]
			: null;
			
	}
		
		/**
		 * Saves the heartbeat settings into a transient.
		 * 
		 * @remark	This is called in both background and foreground.
		 */
		static private function _saveInfo() {
			
			$_aInfo = array(
				'interval'			=>	self::$_iInterval,
				'target_url'		=>	self::$_sTargetURL,
			) + ( array ) get_transient( self::$sKey );
			
			if ( self::isBackground() ) {
				$_aInfo['last_beat_time']	=	microtime( true );
				$_aInfo['id']				=	self::getBeatID();
			}
			$_aInfo = array_filter( $_aInfo ); // drop non-true values.
			set_transient( self::$sKey, $_aInfo );	// made it not vanish by itself
			
// TaskScheduler_Debug::log( 'saving info' );
// TaskScheduler_Debug::log( $_aInfo );
// TaskScheduler_Debug::log( self::getBeatID() . ' saved the heartbeat info.' );
			return $_aInfo;
			
		}			
		
		/**
		 * Sets internal properties.
		 */
		static private function _setProperties( $sTargetURL, $iInterval ) {
					
			self::$_sTargetURL = filter_var( $sTargetURL, FILTER_VALIDATE_URL ) 
				? $sTargetURL 
				: site_url();
				
			self::$_iInterval = $iInterval ? $iInterval : 25;
			
		}

		/**
		 * Schedules the action to run in the background with WP Cron.	
		 */
		static private function _scheduleToCheckBeat() {
			
			// If already scheduled, skip.
			if ( wp_next_scheduled( self::$sActionHook ) ) { return; }
			wp_schedule_single_event( 
				time() + self::$_iInterval + 1,
				self::$sActionHook 	
			);				
TaskScheduler_Debug::log( self::getBeatID() . ' scheduled the heartbeat checking task.' );
		}		
		
	/**
	 * Loads the page in the background.
	 */
	static public function loadPage( $sTagetURL='', $aCookies=array() ) {
		
		$_sTargetURL	= apply_filters( 'task_scheduler_serverheartbeat_target_url', $sTagetURL ? $sTagetURL : ( isset( self::$_sTargetURL ) ? self::$_sTargetURL : site_url() ) );
		$_aCookies		= apply_filters( 'task_scheduler_serverheartbeat_cookies', $aCookies );
		wp_remote_get(
			$_sTargetURL, 
			array( 
				'timeout'	=>	0.01, 
				'sslverify'	=>	false, 
				'cookies'	=>	$_aCookies + array(
					'server_heartbeat_id'	=>	isset(  $_COOKIE[ 'server_heartbeat_id' ] ) 
						? $_COOKIE[ 'server_heartbeat_id' ] 
						: uniqid(),			
				),
			) 
		);			
		
	}
		
}
